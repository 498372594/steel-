<?php

namespace app\admin\controller;

use app\admin\model\{CapitalFy, Jsfs, KcSpot, KcYlSh, KucunCktz, SalesReturnDetails, StockOut, StockOutMd};
use app\admin\validate\{SalesorderDetails};
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    Request,
    response\Json};

class Salesorder extends Right
{
    /**
     * 获取销售单列表
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function getList(Request $request, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $params = $request->param();
        $list = \app\admin\model\Salesorder::with([
            'custom',
            'pjlxData',
            'jsfsData',
        ])->where('companyid', $this->getCompanyId())
            ->order('create_time', 'desc');
        if (!empty($params['ywsjStart'])) {
            $list->where('ywsj', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('ywsj', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['status'])) {
            $list->where('status', $params['status']);
        }
        if (!empty($params['custom_id'])) {
            $list->where('custom_id', $params['custom_id']);
        }
        if (!empty($params['employer'])) {
            $list->where('employer', $params['employer']);
        }
        if (!empty($params['pjlx'])) {
            $list->where('pjlx', $params['pjlx']);
        }
        if (!empty($params['system_no'])) {
            $list->where('system_no', 'like', '%' . $params['system_no'] . '%');
        }
        if (!empty($params['ywlx'])) {
            $list->where('ywlx', $params['ywlx']);
        }
        if (!empty($params['remark'])) {
            $list->where('remark', 'like', '%' . $params['remark'] . '%');
        }
        $list = $list->paginate($pageLimit);
        return returnRes(true, '', $list);
    }

    /**
     * 获取销售单详情
     * @param int $id
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function detail($id = 0)
    {
        $data = \app\admin\model\Salesorder::with([
            'custom',
            'pjlxData',
            'jsfsData',
            'details' => ['specification', 'jsfs', 'storage'],
            'other' => ['mingxi' => ['szmcData', 'pjlxData', 'custom', 'szflData']]
        ])
            ->where('companyid', $this->getCompanyId())
            ->where('id', $id)
            ->find();
        if (empty($data)) {
            return returnFail('数据不存在');
        } else {
            return returnRes(true, '', $data);
        }
    }

    /**
     * 作废
     * @param Request $request
     * @param int $id
     * @return Json|string
     */
    public function cancel(Request $request, $id = 0)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }

        Db::startTrans();
        try {
            $xs = \app\admin\model\Salesorder::get($id);
            if (empty($xs)) {
                throw new Exception("对象不存在");
            }
            if ($xs['status'] == 2) {
                throw new Exception("该单据已经作废");
            }
            if ($xs['ywlx'] != 1) {
                throw new Exception("该销售单是由其他单据自动生成的，禁止直接作废！");
            }
            if ($xs['ywlx'] != 7) {
                if ($xs['ywlx'] == 1)
                    throw new Exception("该销售单是由调货销售单自动生成的，禁止直接作废！");
                if ("2" == $xs['ywlx'])
                    throw new Exception("该销售单是由采购直发单自动生成的，禁止直接作废！");
                if ("4" == $xs['ywlx'])
                    throw new Exception("该销售单是由销售预订单自动生成的，禁止直接作废！");
                if ("5" == $xs['ywlx']) {
                    throw new Exception("该销售单是由销售预订实销单自动生成的，禁止直接作废！");
                }
            }
            $xs->status = 2;
            $xs->save();
            $mxList = \app\admin\model\SalesorderDetails::where('order_id', $xs['id'])->select();
            if ($xs['ckfs'] == 1) {
                StockOut::cancelChuku($xs['id'], 4);
            } else {
                $ckTzDaoTmpl = new KucunCktz();
                foreach ($mxList as $mx) {
                    $ckTzDaoTmpl->deleteByDataIdAndChukuType($mx['id'], 4);
                }
            }
            $invDaoImpl = new \app\admin\model\Inv();
            foreach ($mxList as $mx) {
                $invDaoImpl->deleteInv($mx['id'], 3);
                $thMxList = SalesReturnDetails::where('xs_sale_mx_id', $mx['id'])->count();
                if ($thMxList > 0) {
                    throw new Exception("该销售单已有退货信息，禁止该操作！");
                }
            }
            (new CapitalFy())->deleteByDataIdAndType($xs['id'], 1);
            \app\admin\model\CapitalHk::deleteHk($xs['id'], 12);

            Db::commit();
            return returnSuc();
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param int $ywlx
     * @return Json
     */
    public function add(Request $request, $ywlx = 7)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }

        Db::startTrans();
        try {
            $data = $request->post();

            $validate = new \app\admin\validate\Salesorder();
            if (!$validate->check($data)) {
                throw new Exception($validate->getError());
            }

            $addList = [];
            $updateList = [];

            $detailValidate = new SalesorderDetails();
            $num = 1;
            $companyId = $this->getCompanyId();
            foreach ($data['details'] as $item) {
                if (!$detailValidate->check($item)) {
                    throw new Exception('请检查第' . $num . '行' . $data['details']);
                }
                $item['caizhi'] = $this->getCaizhiId($item['caizhi'] ?? '');
                $item['chandi'] = $this->getChandiId($item['chandi'] ?? '');
                $item['companyid'] = $companyId;
                if (empty($item['id'])) {
                    $addList[] = $item;
                } else {
                    $updateList[] = $item;
                }
                $num++;
            }
            if (empty($data['id'])) {
                $count = \app\admin\model\Salesorder::withTrashed()
                    ->whereTime('create_time', 'today')
                    ->where('companyid', $companyId)
                    ->count();

                //数据处理
                $systemNumber = 'XSD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
                $data['add_id'] = $this->getAccountId();
                $data['companyid'] = $companyId;
                $data['system_no'] = $systemNumber;
                $data['ywlx'] = $ywlx;

                $xs = new \app\admin\model\Salesorder();
                $xs->allowField(true)->data($data)->save();

                if ($data['ckfs'] == 1) {
                    $ck = (new StockOut())->insertChuku($xs['id'], "4", $xs['ywsj'], $xs['department'], $xs['system_no'], $xs['employer'], $this->getAccountId(), $this->getCompanyId());
                }
            } else {
                $xs = \app\admin\model\Salesorder::where('companyid', $companyId)->where('id', $data['id'])->find();
                if (empty($xs)) {
                    throw new Exception('数据不存在');
                }
                if ($xs['status'] == 2) {
                    throw new Exception('该单据已作废');
                }
                if ($xs['ywlx'] != 1) {
                    throw new Exception('此销售单是由其他单据自动生成的，禁止直接修改！');
                }
                if ($xs['ckfs'] == 2) {
                    $mxList = \app\admin\model\SalesorderDetails::where('order_id', $data['id'])->select();
                    if (!empty($mxList)) {
                        foreach ($mxList as $item) {
                            $mdList = StockOutMd::where('data_id', $item['id'])->count();
                            if ($mdList > 0) {
                                throw new Exception("该单据已经出库，禁止操作！");
                            }
                        }
                    }
                }
                $data['changer'] = $this->getAccountId();
                $xs->allowField(true)->save($data);
                if ($data['ckfs'] == 1) {
                    throw new Exception('自动出库单禁止修改');
                }
                $mxList = \app\admin\model\SalesorderDetails::where('order_id', $data['id'])->select();
                if (!empty($mxList)) {
                    foreach ($mxList as $mx) {
                        $invList = \app\admin\model\Inv::where('data_id', $mx['id'])
                            ->where('yw_type', 3)
                            ->select();
                        foreach ($invList as $item) {
                            $item->yw_time = $xs['ywsj'];
                            $item->piaoju_id = $xs['pjlx'];
                            $item->customer_id = $xs['custom_id'];
                            $item->save();
                        }
                        $tzList = KucunCktz::where('data_id', $mx['id'])->select();
                        foreach ($tzList as $tz) {
                            $tz->cache_ywtime = $xs['ywsj'];
                            $tz->cache_customer_id = $xs['custom_id'];
                            $tz->save();
                        }
                    }
                }
            }
            if (!empty($data['deleteMxIds'])) {
                $deleteList = \app\admin\model\SalesorderDetails::where('id', 'in', $data['deleteMxIds'])->select();
                foreach ($deleteList as $mx) {
                    if ($xs['ckfs'] == 1) {
                        throw new Exception('自动出库单禁止修改');
                    } else {
                        (new KucunCktz())->deleteByDataIdAndChukuType($mx['id'], 4);
                    }
                    (new \app\admin\model\Inv())->deleteInv($mx['id'], 3);
                    $mx->delete();
                }
            }
            foreach ($updateList as $mjo) {
                $thMxList = SalesReturnDetails::where('xs_sale_mx_id', $mjo['id'])->select();
                if (!empty($thMxList)) {
                    throw new Exception("该销售单已有退货信息，禁止该操作！");
                }
                $jjfs = \app\admin\model\SalesorderDetails::alias('mx')
                    ->join('__JSFS__ jjfs', 'mx.jsfs=jjfs.id')
                    ->where('id', $mjo['id'])
                    ->value('jj_type');
                if (($jjfs != 2) && empty($mjo['count'])) {
                    throw new Exception("数量必须大于“0”！");
                }
                $mx = \app\admin\model\SalesorderDetails::where('id', $mjo['id'])->find();
                $mx->allowField(true)->data($mjo)->isUpdate(true)->save();
                if (1 == $xs['ckfs']) {
                    throw new Exception('自动出库单禁止修改');
                } else {
                    (new KucunCktz())->updateChukuTz($mx['id'], "4", $mx['wuzi_id'], $mx['caizhi'], $mx['chandi'], $mx['jsfs_id'], $mx['storage_id'], $mx['houdu'],
                        $mx['length'], $mx['width'], $mx['count'], $mx['num'], $mx['lingzhi'], $mx['jzs'], $mx['weight'], $mx['tax_rate'], $mx['total_fee'], $mx['price_and_tax'],
                        $mx['price'], $mx['batch_no'], $xs['remark'], $mx['car_no'], $xs['ywsj'], $xs['system_no'], $xs['custom_id']);
                }
                (new \app\admin\model\Inv())->updateInv($mx['id'], "3", null, $xs['custom_id'], $xs['ywsj'], $mx['length'], $mx['width'], $mx['houdu'],
                    $mx['wuzi_id'], $mx['jsfs_id'], $xs['pjlx'], '', $mx['weight'], $mx['price'], $mx['total_fee'], $mx['price_and_tax'], $mx['tax_rate']);
            }
            if (!empty($addList)) {
                $trumpet = \app\admin\model\SalesorderDetails::where('order_id', $xs['id'])->max('trumpet');
                foreach ($addList as $mjo) {
                    $trumpet++;
                    $jjfs = Jsfs::where('id', $mjo['jsfs_id'])->cache(true, 60)->value('jj_type');
                    if ($jjfs != 2 && empty($mjo['count'])) {
                        throw new Exception("数量必须大于“0”！");
                    }
                    $mjo['trumpet'] = $trumpet;
                    $mjo['order_id'] = $xs['id'];
                    $mx = new \app\admin\model\SalesorderDetails();
                    $mx->allowField(true)->data($mjo)->save();
                    if (!empty($mx['kc_spot_id'])) {
                        $spot = KcSpot::get($mx['kc_spot_id']);
                    }
                    if (empty($spot)) {
                        $cbPrice = null;
                    } else {
                        $cbPrice = $spot['cb_price'];
                    }
                    if ($xs['ckfs'] == 1) {
                        (new StockOut())->insertCkMxMd($ck, $mx['kc_spot_id'] ?? '', $mx['id'], "4", $xs['ywsj'],
                            $xs['system_no'], $xs['custom_id'], $mx['wuzi_id'], $mx['caizhi'], $mx['chandi'], $mx['jsfs_id'],
                            $mx['storage_id'], $mx['houdu'] ?? 0, $mx['width'] ?? 0, $mx['length'] ?? 0, $mx['jzs'], $mx['lingzhi'] ?? 0, $mx['num'], $mx['count'] ?? 0,
                            $mx['weight'], $mx['price'], $mx['total_fee'], $mx['tax_rate'] ?? 0, $mx['price_and_tax'], $mx['tax'],
                            null, null, $cbPrice, '', $this->getAccount(), $this->getCompanyId());
                    } else {
                        (new KucunCktz())->insertChukuTz($mx['id'], 4, $mx['wuzi_id'], $mx['caizhi'], $mx['chandi'],
                            $mx['jsfs_id'], $mx['storage_id'], $mx['houdu'] ?? '', $mx['length'] ?? '', $mx['width'] ?? 0, $mx['count'] ?? 0, $mx['num'],
                            $mx['lingzhi'] ?? 0, $mx['jzs'], $mx['weight'], $mx['tax_rate'] ?? 0, $mx['total_fee'] ?? 0, $mx['price_and_tax'] ?? 0,
                            $mx['price'], $mx['batch_no'] ?? 0, $mx['remark'] ?? 0, $mx['car_no'] ?? 0, $xs['ywsj'], $xs['system_no'], $xs['custom_id'], $this->getAccountId(), $this->getCompanyId());
                    }
                    (new \app\admin\model\Inv())->insertInv($mx['id'], 3, 1, $mx['length'] ?? '', $mx['houdu'] ?? '',
                        $mx['width'] ?? 0, $mx['wuzi_id'], $mx['jsfs_id'], $xs['pjlx'],
                        null, $xs['system_no'] . "." . $mx['trumpet'], $xs['custom_id'], $xs['ywsj'], $mx['price'],
                        $mx['tax_rate'] ?? 0, $mx['total_fee'] ?? 0, $mx['price_and_tax'] ?? 0, $mx['weight'], $this->getCompanyId());
                }
            }
            (new CapitalFy())->fymxSave($data['other'], $data['deleteOtherIds'] ?? [], $xs['id'], $xs['ywsj'], 1, $xs['department'] ?? '', $xs['employer'] ?? '', null, $this->getAccountId(), $this->getCompanyId());
            $mxList = \app\admin\model\SalesorderDetails::where('order_id', $xs['id'])->select();

            if (!empty($mxList)) {
                foreach ($mxList as $mx) {
                    $spot = KcSpot::where('id', $mx['kc_spot_id'])->select();
                    if (!empty($spot) && !empty($spot['cb_price'])) {
                        $mdList = StockOutMd::where('kc_spot_id', $spot['id'])->select();
                        if (!empty($mdList)) {
                            foreach ($mdList as $md) {
                                $md->cb_price = $spot['cb_price'];
                                $jjfs = Jsfs::where('id', $mx['jsfs_id'])->cache(true, 60)->value('jj_type');
                                if ($jjfs == 1 || $jjfs == 2) {
                                    $md->cb_sum_shuiprice = $md->cb_price * $md->zhongliang;
                                } elseif ($jjfs == 3) {
                                    $md->cb_sum_shuiprice = $md->cb_price * $md->counts;
                                }
                                $md->save();
                            }
                        }
                    }
                }
            }
            $sumMoney = \app\admin\model\SalesorderDetails::where('order_id', $xs['id'])->sum('price_and_tax');
            $sumZhongliang = \app\admin\model\SalesorderDetails::where('order_id', $xs['id'])->sum('weight');
            if (empty($data['id'])) {
                (new \app\admin\model\CapitalHk())->insertHk($xs['id'], "12", $xs['system_no'], $xs['remark'] ?? '',
                    $xs['custom_id'], "1", $xs['ywsj'], $xs['jsfs'] ?? null, $xs['pjlx'], $sumMoney,
                    $sumZhongliang, $xs['department'] ?? 0, $xs['employer'] ?? 0, $this->getAccountId(), $this->getCompanyId());
            } else {
                (new \app\admin\model\CapitalHk())->updateHk($xs['id'], "12", $xs['remark'] ?? '', $xs['custom_id'], $xs['ywsj'],
                    $xs['jsfs'] ?? null, $xs['pjlx'], $sumMoney, $sumZhongliang, $xs['department'] ?? 0, $xs['employer'] ?? 0);
            }
            Db::commit();
            return returnSuc(['id' => $xs['id']]);
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**
     * 预留货物销售释放
     * @param Request $request
     * @return Json
     */
    public function ylAdd(Request $request)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }

        Db::startTrans();
        try {
            $data = $request->post();
            $validate = new \app\admin\validate\Salesorder();
            if (!$validate->check($data)) {
                throw new Exception($validate->getError());
            }
            $companyId = $this->getCompanyId();

            $addList = [];
            $ja = $data['details'];
            if (!empty($ja)) {
                $num = 1;
                $detailValidate = new SalesorderDetails();
                foreach ($ja as $object) {
                    if (!$detailValidate->check($object)) {
                        throw new Exception('请检查第' . $num . '行' . $data['details']);
                    }
                    if (empty($object['id'])) {
                        $addList[] = $object;
                    }
                }
            }
            $count = \app\admin\model\Salesorder::withTrashed()
                ->whereTime('create_time', 'today')
                ->where('companyid', $companyId)
                ->count();
            $data['add_id'] = $this->getAccountId();
            $data['companyid'] = $companyId;
            $data['system_no'] = 'XSD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);;
            $data['ywlx'] = 7;
            $xs = new \app\admin\model\Salesorder();
            $xs->allowField(true)->data($data)->save();
            if ($data['ckfs'] == 1) {
                $ck = (new StockOut())->insertChuku($xs['id'], "4", $xs['ywsj'], $xs['department'], $xs['system_no'], $xs['employer'], $this->getAccountId(), $this->getCompanyId());
            }
            if (!empty($addList)) {
                $trumpet = \app\admin\model\SalesorderDetails::where('order_id', $xs['id'])->max('trumpet');
                foreach ($addList as $mjo) {
                    $ylsh = KcYlSh::get($mjo['ylsh_id']);
                    if ($ylsh['shuliang'] < $mjo['count']) {
                        throw new Exception("销售数量不得大于预留数量");
                    }
                    if ($ylsh['zhongliang'] < $mjo['weight']) {
                        throw new Exception("销售重量不得大于预留重量");
                    }
                    $counts = $ylsh['shuliang'] - $mjo['count'];
                    $ylsh->shuliang = $ylsh['shuliang'] - $mjo['count'];
                    $ylsh->zhongliang = $ylsh['zhongliang'] - $mjo['weight'];
                    $spot = KcSpot::get($ylsh['spot_id']);
                    $jjfs = Jsfs::where('id', $spot['jijiafangshi_id'])->cache(true, 60)->value('jj_type');
                    $calSpot = KcSpot::calSpot($ylsh['changdu'], $spot['kuandu'], $jjfs, $spot['mizhong'], $spot['jianzhong'], $ylsh['shuliang'], $ylsh['zhijian'], $ylsh['zhongliang'], $spot['price'], $spot['shuiprice'], 0);
                    $ylsh->guobang_zhongliang = $calSpot['guobang_zhongliang'];
                    $ylsh->lisuan_zhongliang = $calSpot['lisuan_zhongliang'];
                    $lingzhi = $counts % $ylsh['zhijian'];
                    $jianshu = floor($count / $ylsh['zhijian']);
                    $ylsh->lingzhi = $lingzhi;
                    $ylsh->jianshu = $jianshu;
                    $ylsh->save();
                    $trumpet++;
                    $mjo['kc_spot_id'] = $ylsh['spot_id'];
                    $mjo['trumpet'] = $trumpet;
                    $mjo['order_id'] = $xs['id'];
                    $mx = new \app\admin\model\SalesorderDetails();
                    $mx->allowField(true)->data($mjo)->save();
                    if ($xs['ckfs'] == 1) {
                        (new StockOut())->insertCkMxMd($ck, $mx['kc_spot_id'] ?? '', $mx['id'], "4",
                            $xs['ywsj'], $xs['system_no'], $xs['custom_id'], $mx['wuzi_id'], $mx['caizhi'], $mx['chandi'],
                            $mx['jsfs_id'], $mx['storage_id'], $mx['houdu'] ?? 0, $mx['width'] ?? 0,
                            $mx['length'] ?? 0, $mx['jzs'], $mx['lingzhi'] ?? 0, $mx['num'],
                            $mx['count'] ?? 0, $mx['weight'], $mx['price'], $mx['total_fee'],
                            $mx['tax_rate'] ?? 0, $mx['price_and_tax'], $mx['tax'], null,
                            null, null, '', $this->getAccount(), $this->getCompanyId());
                    }
                    (new \app\admin\model\Inv())->insertInv($mx['id'], 3, 1, $mx['length'] ?? '', $mx['houdu'] ?? '',
                        $mx['width'] ?? 0, $mx['wuzi_id'], $mx['jsfs_id'], $xs['pjlx'], null,
                        $xs['system_no'] . "." . $mx['trumpet'], $xs['custom_id'], $xs['ywsj'], $mx['price'],
                        $mx['tax_rate'] ?? 0, $mx['total_fee'] ?? 0, $mx['price_and_tax'] ?? 0, $mx['weight'], $this->getCompanyId());
                }
            }
            (new CapitalFy())->fymxSave($data['other'], $data['deleteOtherIds'] ?? [], $xs['id'], $xs['ywsj'], 1, $xs['department'] ?? '', $xs['employer'] ?? '', null, $this->getAccountId(), $this->getCompanyId());

            $sumMoney = \app\admin\model\SalesorderDetails::where('order_id', $xs['id'])->sum('price_and_tax');
            $sumZhongliang = \app\admin\model\SalesorderDetails::where('order_id', $xs['id'])->sum('weight');
            if (empty($data['id'])) {
                (new \app\admin\model\CapitalHk())->insertHk($xs['id'], "12", $xs['system_no'], $xs['remark'] ?? '',
                    $xs['custom_id'], "1", $xs['ywsj'], $xs['jsfs'] ?? null, $xs['pjlx'], $sumMoney,
                    $sumZhongliang, $xs['department'] ?? 0, $xs['employer'] ?? 0, $this->getAccountId(), $this->getCompanyId());
            } else {
                (new \app\admin\model\CapitalHk())->updateHk($xs['id'], "12", $xs['remark'] ?? '', $xs['custom_id'], $xs['ywsj'],
                    $xs['jsfs'] ?? null, $xs['pjlx'], $sumMoney, $sumZhongliang, $xs['department'] ?? 0, $xs['employer'] ?? 0);
            }
            Db::commit();
            return returnSuc(['id' => $xs['id']]);
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**
     * 客户销量排行榜
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function khxlList(Request $request, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $param = $request->param();
        $sqlParams = [];
        $sql = '(SELECT tb_mingxi.customer_id,
       tb_mingxi.customer_name,
       tb_mingxi.short_name,
       tb_mingxi.zjm                                                                    code,
       SUM(IFNULL(tb_mingxi.xszhongliang, 0)) - SUM(IFNULL(thmx.zhongliang, 0))      AS th_zhongliang,
       COUNT(tb_mingxi.xs_saleId)                                                    AS th_cishu,
       SUM(IFNULL(tb_mingxi.price_and_tax, 0)) - SUM(IFNULL(thmx.sum_shui_price, 0)) AS th_sum_shui_price
FROM (SELECT xsmx.weight AS xszhongliang,
             xs.xs_saleId,
             xsmx.id,
             xs.customer_name,
             xs.customer_id,
             xs.zjm,
             xsmx.price_and_tax,
             xs.short_name
      FROM (SELECT custom.zjm, custom.custom customer_name, custom.id AS customer_id, tb_xs_sale.id AS xs_saleId,custom.short_name
            FROM custom
                     LEFT JOIN salesorder tb_xs_sale ON custom.id = tb_xs_sale.custom_id
                WHERE custom.iscustom = 1
                     and custom.delete_time is null
                     AND tb_xs_sale.delete_time is null
                     AND tb_xs_sale.`status` <> 2 ';
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and tb_xs_sale.ywsj >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and tb_xs_sale.ywsj < :ywsjEnd';
            $sqlParams['ywsjEnd'] = date('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql .= ') AS xs
               INNER JOIN salesorder_details xsmx ON xs.xs_saleId = xsmx.order_id
     ) AS tb_mingxi
         LEFT JOIN sales_return_details thmx ON tb_mingxi.xs_saleId = thmx.xs_sale_mx_id
         left join
     (SELECT mx.xs_sale_mx_id, mx.zhongliang, mx.sum_shui_price
      FROM sales_return_details mx
               INNER JOIN sales_return th ON th.id = mx.xs_th_id WHERE th.delete_time is null AND th.status <> 2) thmx2
     on thmx2.xs_sale_mx_id = tb_mingxi.id
    WHERE 1 = 1 ';
        if (!empty($param['customer_id'])) {
            $sql .= ' and tb_mingxi.customer_id=:customerId';
            $sqlParams['customerId'] = $param['customer_id'];
        }
        $sql .= ' GROUP BY tb_mingxi.customer_id)';
        $data = Db::table($sql)->alias('t')->bind($sqlParams)->order('th_zhongliang', 'asc')->paginate($pageLimit);
        return returnSuc($data);
    }

    /**
     * 销售明细表
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function mxList(Request $request, $pageLimit = 10)
    {
        $params = $request->param();
        $sqlParams = [];
        $sql = '(SELECT mx.id,
       mx.id              xsSaleMxId,
       mx.order_id,
       s.resource_number,
       z.ywsj,
       z.system_no,
       z.status,
       z.ywlx,
       gg.productname_id,
       gg.productname,
       mx.wuzi_id,
       gg.`specification` guige_name,
       mx.houdu,
       mx.width,
       mx.length,
       mx.caizhi,
       cz.`texturename`   caizhi_name,
       mx.chandi,
       cd.`originarea`    chandi_name,
       mx.jsfs_id,
       jjfs.`jsfs`        jijiafangshi_name,
       mx.lingzhi,
       mx.num,
       mx.jzs,
       mx.count,
       mx.weight,
       mx.batch_no,
       mx.storage_id,
       ck.`storage`       store_name,
       mx.price,
       mx.total_fee,
       mx.tax_rate,
       mx.tax,
       mx.price_and_tax,
       mx.remark,
       mx.car_no,
       sale.name          zhiyuan,
       gg.mizhong_name,
       cu.`custom`        customerName,
       pjlx.pjlx          piaoju_name
FROM salesorder_details mx
         LEFT JOIN kc_spot s ON s.id = mx.kc_spot_id
         LEFT JOIN salesorder z ON z.id = mx.order_id
         LEFT JOIN view_specification gg ON gg.id = mx.wuzi_id
         LEFT JOIN texture cz ON cz.id = mx.caizhi
         LEFT JOIN originarea cd ON cd.id = mx.chandi
         LEFT JOIN storage ck ON ck.id = mx.storage_id
         LEFT JOIN jsfs jjfs ON jjfs.id = mx.jsfs_id
         LEFT JOIN custom cu ON cu.`id` = z.`custom_id`
         LEFT JOIN admin sale ON sale.id = z.employer
         left join pjlx on pjlx.id = z.pjlx
    WHERE mx.delete_time is null
         and z.delete_time is null';
        if (!empty($params['ywlx'])) {
            $sql .= ' and z.ywlx=:ywlx';
            $sqlParams['ywlx'] = $params['ywlx'];
        }
        if (!empty($params['exclude_ywlx'])) {
            $sql .= ' and z.ywlx!=:excludeYwlx';
            $sqlParams['excludeYwlx'] = $params['exclude_ywlx'];
        }
        if (!empty($params['employer'])) {
            $sql .= ' and sale.id=:employer';
            $sqlParams['employer'] = $params['employer'];
        }
        if (!empty($params['department'])) {
            $sql .= ' and z.department=:department';
            $sqlParams['department'] = $params['department'];
        }
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and z.ywsj >= :ywsjStart';
            $sqlParams['ywsjStart'] = $params['ywsjStart'];
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and z.ywsj <:ywsjEnd';
            $sqlParams['ywsjEnd'] = date('Y-m-d H:i:s', strtotime($params['ywsjEnd'] . ' +1 day'));
        }
        if (!empty($params['kuanduStart'])) {
            $sql .= ' and mx.width >= :kuanduStart';
            $sqlParams['kuanduStart'] = $params['kuanduStart'];
        }
        if (!empty($params['kuanduEnd'])) {
            $sql .= ' and mx.width <= :kuanduEnd';
            $sqlParams['kuanduEnd'] = $params['kuanduEnd'];
        }
        if (!empty($params['store_id'])) {
            $sql .= ' and ck.id=:storeId';
            $sqlParams['storeId'] = $params['store_id'];
        }
        if (!empty($params['pinming'])) {
            $sql .= ' and gg.productname_id = :pinming';
            $sqlParams['pinming'] = $params['pinming'];
        }
        if (!empty($params['guige'])) {
            $sql .= ' and gg.id = :guige';
            $sqlParams['guige'] = $params['guige'];
        }
        if (!empty($params['houduStart'])) {
            $sql .= ' and mx.houdu >= :houduStart';
            $sqlParams['houduStart'] = $params['houduStart'];
        }
        if (!empty($params['houduEnd'])) {
            $sql .= ' and mx.houdu <= :houduEnd';
            $sqlParams['houduEnd'] = $params['houduEnd'];
        }
        if (!empty($params['changduStart'])) {
            $sql .= ' and mx.length >=:changduStart';
            $sqlParams['changduStart'] = $params['changduStart'];
        }
        if (!empty($params['changduEnd'])) {
            $sql .= ' and mx.length <= :changduEnd';
            $sqlParams['changduEnd'] = $params['changduEnd'];
        }
        if (!empty($params['jsfs'])) {
            $sql .= ' and mx.jsfs=:jsfs';
            $sqlParams['jsfs'] = $params['jsfs'];
        }
        if (!empty($params['caizhi'])) {
            $sql .= ' and mx.caizhi=:caizhi';
            $sqlParams['caizhi'] = $params['caizhi'];
        }
        if (!empty($params['chandi'])) {
            $sql .= ' and mx.chandi=:chandi';
            $sqlParams['chandi'] = $params['chandi'];
        }
        if (!empty($params['status'])) {
            $sql .= ' and z.status=:status';
            $sqlParams['status'] = $params['status'];
        }
        if (!empty($params['customer_id'])) {
            $sql .= ' and cu.id=:customerId';
            $sqlParams['customerId'] = $params['customer_id'];
        }
        if (!empty($params['piaoju'])) {
            $sql .= ' and z.pjlx=:piaoju';
            $sqlParams['piaoju'] = $params['piaoju'];
        }
        if (!empty($params['system_number'])) {
            $sql .= ' and z.system_no like :systemNumber';
            $sqlParams['systemNumber'] = '%' . $params['systemNumber'] . '%';
        }
        if (!empty($params['beizhu'])) {
            $sql .= ' and mx.remark like :beizhu';
            $sqlParams['beizhu'] = '%' . $params['beizhu'] . '%';
        }
        $sql .= ' )';
        $data = Db::table($sql)->alias('t')->bind($sqlParams)->order('ywsj', 'desc')->paginate($pageLimit);
        return returnSuc($data);
    }

    /**
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function bangcha(Request $request, $pageLimit = 10)
    {
        $params = $request->param();
        $sqlParams = [];
        $sql = '(SELECT mx.id,
       st.storage                                                                  cangku,
       cus.custom                                                                  wanglai,
       gg.productname                                                              pinming,
       gg.specification                                                            guige,
       se.system_no,
       cz.texturename                                                              caizhi,
       cd.originarea                                                               chandi,
       mx.houdu                                                                    houdu,
       mx.width                                                                    kuandu,
       pjlx.pjlx                                                                   piaoju_name,
       mx.length                                                                   changdu,
       se.status,
       jjfs.jsfs                                                                   jijiafangshi,
       mx.lingzhi                                                                  kaidan_lingzhi,
       mx.num                                                                      kaidan_jianshu,
       mx.count                                                                    kaidan_shuliang,
       IFNULL(ckmd.counts, 0)                                                      fahuo_shuliang,
       mx.weight                                                                   kaidan_zhongliang,
       IFNULL(ckmd.zhongliang, 0)                                                  fahuo_zhongliang,
       mx.price                                                                    danjia,
       (IFNULL(mx.weight, 0) - IFNULL(ckmd.zhongliang, 0))                         bangcha_zhongliang,
       CASE
           WHEN (IFNULL(mx.weight, 0) - IFNULL(ckmd.zhongliang, 0)) > 0
               THEN \'涨磅\'
           WHEN (IFNULL(mx.weight, 0) - IFNULL(ckmd.zhongliang, 0)) = 0
               THEN \'平磅\'
           WHEN (IFNULL(mx.weight, 0) - IFNULL(ckmd.zhongliang, 0)) < 0
               THEN \'亏磅\'
           END                                                                     bangcha_fangxiang,
       ((IFNULL(mx.weight, 0) - IFNULL(ckmd.zhongliang, 0)) * IFNULL(mx.price, 0)) bangcha_jiashuiheji,
       ((IFNULL(mx.weight, 0) - IFNULL(ckmd.zhongliang, 0)) * IFNULL(mx.price, 0) / (1 + mx.tax_rate / 100) *
        mx.tax_rate / 100)                                                         bangcha_shuie,
       ((IFNULL(mx.weight, 0) - IFNULL(ckmd.zhongliang, 0)) * IFNULL(mx.price, 0) -
        (IFNULL(mx.weight, 0) - IFNULL(ckmd.zhongliang, 0)) * IFNULL(mx.price, 0) / (1 + mx.tax_rate / 100) *
        mx.tax_rate / 100)                                                         bangcha_jine,
       mx.jzs                                                                      jianzhishu,
       mx.remark,
       se.ywsj
FROM salesorder_details mx
         LEFT JOIN storage st ON mx.storage_id = st.id
         LEFT JOIN salesorder se ON mx.order_id = se.id
         LEFT JOIN custom cus ON se.custom_id = cus.id
         LEFT JOIN view_specification gg ON mx.wuzi_id = gg.id
         LEFT JOIN pjlx pjlx ON se.pjlx = pjlx.id
         LEFT JOIN texture cz ON mx.caizhi = cz.id
         LEFT JOIN originarea cd ON mx.chandi = cd.id
         LEFT JOIN jsfs jjfs ON mx.jsfs_id = jjfs.id
         LEFT JOIN stock_out_md ckmd ON ckmd.data_id = mx.id
         LEFT JOIN stock_out ck ON ck.id = ckmd.stock_out_id
WHERE se.delete_time is null
  AND mx.delete_time is null
  AND ck.delete_time is null
  AND ckmd.delete_time is null
  AND ck.status != 2
   AND (mx.weight - IFNULL(ckmd.zhongliang, 0)) != 0
  AND ckmd.data_id = mx.id';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and se.ywsj>=:ywsjStart';
            $sqlParams['ywsjStart'] = $params['ywsjStart'];
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and se.ywsj<=:ywsjEnd';
            $sqlParams['ywsjEnd'] = date('Y-m-d H:i:s', strtotime($params['ywsjEnd'] . ' +1 day'));
        }
        if (!empty($params['customer_id'])) {
            $sql .= ' and cus.id = :customerId';
            $sqlParams['customerId'] = $params['customer_id'];
        }
        if (!empty($params['system_number'])) {
            $sql .= ' and se.system_no like :systemNumber';
            $sqlParams['systemNumber'] = $params['system_number'];
        }
        if (!empty($params['status'])) {
            $sql .= ' and se.status = :status';
            $sqlParams['status'] = $params['status'];
        }
        if (!empty($params['pjlx'])) {
            $sql .= ' and pjlx.id=:pjlx';
            $sqlParams['pjlx'] = $params['pjlx'];
        }
        if (!empty($params['store_id'])) {
            $sql .= ' and st.id=:storeId';
            $sqlParams['storeId'] = $params['store_id'];
        }
        if (!empty($params['pinming_id'])) {
            $sql .= ' and gg.productname_id=:pingmingId';
            $sqlParams['pinmingId'] = $params['pinming_id'];
        }
        if (!empty($params['guige_id'])) {
            $sql .= ' and gg.id=:guigeId';
            $sqlParams['guigeId'] = $params['guige_id'];
        }
        if (!empty($params['caizhi_id'])) {
            $sql .= ' and cz.id=:caizhiId';
            $sqlParams['caizhiId'] = $params['caizhi_id'];
        }
        if (!empty($params['chandi_id'])) {
            $sql .= ' and cd.id=:chandiId';
            $sqlParams['chandiId'] = $params['chandi_id'];
        }
        if (!empty($params['jjfs'])) {
            $sql .= ' and jjfs.id=:jjfsId';
            $sqlParams['jjfsId'] = $params['jjfs'];
        }
        if (!empty($params['beizhu'])) {
            $sql .= ' and mx.remark=:beizhu';
            $sqlParams['beizhu'] = $params['beizhu'];
        }
        $sql .= ')';
        $data = Db::table($sql)->alias('t')->bind($sqlParams)->order('ywsj', 'desc')->paginate($pageLimit);
        return returnSuc($data);
    }
}