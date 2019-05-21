<?php

namespace app\admin\controller;

use app\admin\model\{CapitalFy,
    Jsfs,
    KcSpot,
    KcYlSh,
    KucunCktz,
    SalesReturnDetails,
    StockOut,
    StockOutMd,
    ViewSpecification};
use app\admin\validate\{SalesorderDetails};
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    db\Query,
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
            'employerData',
            'createOperator',
            'updateOperator',
            'departmentData'
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
            'details' => ['specification', 'jsfs', 'storage', 'caizhiData', 'chandiData'],
            'other' => ['mingxi' => ['szmcData', 'pjlxData', 'custom', 'szflData']],
            'employerData',
            'createOperator',
            'updateOperator'
        ])
            ->where('companyid', $this->getCompanyId())
            ->where('id', $id)
            ->find();
        return returnRes(true, '', $data);
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
     * @return Json
     */
    public function add(Request $request)
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
                    throw new Exception('请检查第' . $num . '行' . $detailValidate->getError());
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
                $data['ywlx'] = 7;

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
                if ($xs['ywlx'] != 7) {
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
                    ->join('__JSFS__ jjfs', 'mx.jsfs_id=jjfs.id')
                    ->where('mx.id', $mjo['id'])
                    ->value('jj_type');
                if (($jjfs != 2) && empty($mjo['count'])) {
                    throw new Exception("数量必须大于“0”！");
                }
                $guige = ViewSpecification::where('id', $mjo['wuzi_id'])->cache(true, 60)->find();
                if (empty($guige)) {
                    throw new Exception('物资不存在');
                }
                $mjo['pinming_id'] = $guige['productname_id'];
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
                    $guige = ViewSpecification::where('id', $mjo['wuzi_id'])->cache(true, 60)->find();
                    if (empty($guige)) {
                        throw new Exception('物资不存在');
                    }
                    $mjo['pinming_id'] = $guige['productname_id'];
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
                            null, $mx['jianzhong'], $cbPrice, '', $this->getAccount(), $this->getCompanyId());
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
            (new CapitalFy())->fymxSave($data['other'] ?? [], $data['deleteOtherIds'] ?? [], $xs['id'], $xs['ywsj'], 1, $xs['department'] ?? '', $xs['employer'] ?? '', null, $this->getAccountId(), $this->getCompanyId());
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
            (new CapitalFy())->fymxSave($data['other'] ?? [], $data['deleteOtherIds'] ?? [], $xs['id'], $xs['ywsj'], 1, $xs['department'] ?? '', $xs['employer'] ?? '', null, $this->getAccountId(), $this->getCompanyId());

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
        $model = new \app\admin\model\Salesorder();
        $data = $model->khSalesList($param, $pageLimit, $this->getCompanyId());
        return returnSuc($data);
    }

    /**
     * 业务员销量排行榜
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function ywySalesList(Request $request, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $param = $request->param();
        $model = new \app\admin\model\Salesorder();
        $data = $model->ywySalesList($param, $pageLimit, $this->getCompanyId());
        return returnSuc($data);
    }

    /**
     * 货物销量排行榜
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function hwSalesList(Request $request, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $param = $request->param();
        $model = new \app\admin\model\Salesorder();
        $data = $model->hwSalesList($param, $pageLimit, $this->getCompanyId());
        return returnSuc($data);
    }

    /**
     * 区域销售排行榜
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function areaSalesList(Request $request, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $param = $request->param();
        $model = new \app\admin\model\Salesorder();
        $data = $model->areaSalesList($param, $pageLimit, $this->getCompanyId());
        return returnSuc($data);
    }

    /**
     * 直销库销量排行榜
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function zxkSalesList(Request $request, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $param = $request->param();
        $model = new \app\admin\model\Salesorder();
        $data = $model->zxkSalesList($param, $pageLimit, $this->getCompanyId());
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
        $model = new \app\admin\model\SalesorderDetails();
        $data = $model->getList($params, $pageLimit, $this->getCompanyId());
        return returnSuc($data);
    }

    /**
     * 获取某一天销量
     * @param string $date
     * @return float|int
     */
    public function getSalesVolume($date = '')
    {
        if ($date == 'today') {
            $date = date('Y-m-d');
        } elseif ($date == 'yesterday') {
            $date = date('Y-m-d', strtotime('-1 day'));
        }
        $number = \app\admin\model\SalesorderDetails::hasWhere('salesorder', function (Query $query) use ($date) {
            $query->where('salesorder.companyid', $this->getCompanyId())
                ->where('ywsj', 'between', [$date, date('Y-m-d', strtotime($date . ' +1 day'))])
                ->where('status', '<>', 2);
        })->sum('weight');
        return returnSuc($number);
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