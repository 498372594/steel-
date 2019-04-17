<?php

namespace app\admin\controller;

use app\admin\model\{CapitalFy, Jsfs, KcSpot, KucunCktz, SalesReturnDetails, StockOut, StockOutMd};
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
     * 审核
     * @param Request $request
     * @param int $id
     * @param int $ywlx
     * @param boolean $isWeb
     * @return Json
     * @throws DbException
     */
    public function audit(Request $request, $id = 0, $ywlx = 1, $isWeb = true)
    {
        if ($request->isPut()) {
            if ($ywlx != 1 && $isWeb) {
                return returnFail('此销售单禁止直接审核');
            }
            if ($isWeb) {
                $salesorder = \app\admin\model\Salesorder::where('id', $id)
                    ->where('ywlx', $ywlx)
                    ->find();
            } else {
                $salesorder = \app\admin\model\Salesorder::where('data_id', $id)
                    ->where('ywlx', $ywlx)
                    ->find();
            }
            if (empty($salesorder)) {
                return returnFail('数据不存在');
            }
            if ($salesorder->status == 3) {
                return returnFail('此单已审核');
            }
            if ($salesorder->status == 2) {
                return returnFail('此单已作废');
            }
            $salesorder->status = 3;
            $salesorder->auditer = $this->getAccountId();
            $salesorder->audit_name = $this->getAccount()['name'];
            $salesorder->save();
            return returnSuc();
        }
        return returnFail('请求方式错误');
    }

    /**
     * 反审核
     * @param Request $request
     * @param int $id
     * @param int $ywlx
     * @param boolean $isWeb
     * @return Json
     * @throws DbException
     */
    public function unAudit(Request $request, $id = 0, $ywlx = 1, $isWeb = true)
    {
        if ($request->isPut()) {
            if ($ywlx != 1 && $isWeb) {
                return returnFail('此销售单禁止直接反审核');
            }
            if ($isWeb) {
                $salesorder = \app\admin\model\Salesorder::where('id', $id)
                    ->where('ywlx', $ywlx)
                    ->find();
            } else {
                $salesorder = \app\admin\model\Salesorder::where('data_id', $id)
                    ->where('ywlx', $ywlx)
                    ->find();
            }
            if (empty($salesorder)) {
                return returnFail('数据不存在或已作废');
            }
            if ($salesorder->status == 1) {
                return returnFail('此单未审核');
            }
            if ($salesorder->status == 2) {
                return returnFail('此单已作废');
            }
            $salesorder->status = 1;
            $salesorder->auditer = null;
            $salesorder->audit_name = '';
            $salesorder->save();
            return returnSuc();
        }
        return returnFail('请求方式错误');
    }

    /**
     * 作废
     * @param Request $request
     * @param int $id
     * @param int $ywlx
     * @param boolean $isWeb
     * @return Json|string
     * @throws DbException
     */
    public function cancel(Request $request, $id = 0, $ywlx = 1, $isWeb = true)
    {
        if ($request->isPost()) {
            if ($ywlx != 1 && $isWeb) {
                return returnFail('此销售单禁止直接作废');
            }
            if ($isWeb) {
                $salesorder = \app\admin\model\Salesorder::where('id', $id)
                    ->where('ywlx', $ywlx)
                    ->find();
            } else {
                $salesorder = \app\admin\model\Salesorder::where('data_id', $id)
                    ->where('ywlx', $ywlx)
                    ->find();
            }
            if (empty($salesorder)) {
                return returnFail('数据不存在');
            }
            if ($salesorder->status == 3) {
                return returnFail('此单已审核，禁止作废');
            }
            if ($salesorder->status == 2) {
                return returnFail('此单已作废');
            }
            if ($isWeb) {
                Db::startTrans();
            }
            try {
                $salesorder->status = 2;
                $salesorder->save();
                //货款单作废
                (new CapitalHk())->cancel($id, CapitalHk::SALES_ORDER);
                //费用单作废
                (new Feiyong())->cancelByRelation($id, 1);
                //出库单作废
                (new Chuku())->cancel($request, $id, false);
                //清理出库通知
                (new Chuku())->cancelNotify($id, 4);
                Db::commit();
                return returnSuc();
            } catch (Exception $e) {
                if ($isWeb) {
                    Db::rollback();
                    return returnFail($e->getMessage());
                } else {
                    return $e->getMessage();
                }
            }
        }
        return returnFail('请求方式错误');
    }

    /**
     * @param Request $request
     * @param array $data
     * @param int $ywlx
     * @return Json
     */
    public function add(Request $request, $data = [], $ywlx = 1)
    {
        Db::startTrans();
        try {
            if (empty($data)) {
                $data = $request->post();
            }

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
                $count = \app\admin\model\Salesorder::whereTime('create_time', 'today')
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
            (new CapitalFy())->fymxSave($data['other'], $data['deleteOtherIds'], $xs['id'], $xs['ywsj'], 1, $xs['department'] ?? '', $xs['employer'] ?? '', null, $this->getAccountId(), $this->getCompanyId());
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
}