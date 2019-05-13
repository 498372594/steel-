<?php

namespace app\admin\controller;

use app\admin\model\{CapitalFy,
    CgPurchase,
    CgPurchaseMx,
    Jsfs,
    KcRk,
    KcSpot,
    SalesMoshi,
    SalesMoshiMx,
    SalesorderDetails,
    SalesReturnDetails,
    StockOut,
    StockOutMd};
use app\admin\validate\{SalesMoshiDetails};
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    Request,
    response\Json};

class SalesTiaohuo extends Right
{
    /**
     * 获取采购直发单列表
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
        $list = SalesMoshi::with([
            'custom',
            'khpjData',
            'khjsfsData',
            'createOperator',
        ])->where('companyid', $this->getCompanyId())
            ->order('create_time', 'desc')
            ->where('moshi_type', 1);
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('yw_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['status'])) {
            $list->where('status', $params['status']);
        }
        if (!empty($params['custom_id'])) {
            $list->where('customer_id', $params['custom_id']);
        }
        if (!empty($params['pjlx'])) {
            $list->where('piaoju_id', $params['pjlx']);
        }
        if (!empty($params['system_no'])) {
            $list->where('system_number', 'like', '%' . $params['system_no'] . '%');
        }
        if (!empty($params['remark'])) {
            $list->where('remark', 'like', '%' . $params['remark'] . '%');
        }
        $list = $list->paginate($pageLimit);
        return returnRes(true, '', $list);
    }

    /**
     * 获取采购直发单详情
     * @param Request $request
     * @param int $id
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function detail(Request $request, $id = 0)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $data = SalesMoshi::with([
            'custom',
            'khpjData',
            'khjsfsData',
            'details' => ['specification', 'cgJsfsData', 'cgPjData', 'storage', 'jsfs', 'wldwData', 'caizhiData', 'chandiData'],
            'other' => ['other' => ['mingxi' => ['szmcData', 'pjlxData', 'custom', 'szflData']]],
            'createOperator'
        ])
            ->where('companyid', $this->getCompanyId())
            ->where('moshi_type', 1)
            ->where('id', $id)
            ->find();
        if (empty($data)) {
            return returnFail('数据不存在');
        } else {
            return returnRes(true, '', $data);
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
            //验证数据
            $validate = new \app\admin\validate\SalesMoshi();
            if (!$validate->scene('tiaohuo')->check($data)) {
                return returnFail($validate->getError());
            }
            $addList = [];
            $updateList = [];
            $ja = $data['details'];
            $companyId = $this->getCompanyId();
            if (!empty($ja)) {
                $detailsValidate = new SalesMoshiDetails();
                $num = 1;
                foreach ($ja as $object) {
                    if (!$detailsValidate->scene('tiaohuo')->check($object)) {
                        throw new Exception('请检查第' . $num . '行' . $detailsValidate->getError());
                    }
                    $num++;
                    $object['companyid'] = $companyId;
                    $object['caizhi'] = $this->getCaizhiId($object['caizhi'] ?? '');
                    $object['chandi'] = $this->getChandiId($object['chandi'] ?? '');
                    if (empty($object['id'])) {
                        $addList[] = $object;
                    } else {
                        $updateList[] = $object;
                    }
                }
            }

            if (empty($data['id'])) {
                $count = SalesMoshi::withTrashed()->where('moshi_type', 1)
                    ->where('create_time', 'today')
                    ->where('companyid', $companyId)
                    ->count();
                $data['moshi_type'] = 1;
                $data['create_operator_id'] = $this->getAccountId();
                $data['system_number'] = "THXSD" . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
                $data['companyid'] = $companyId;

                $ms = new SalesMoshi();
                $ms->allowField(true)->data($data)->save();

                $xs = (new \app\admin\model\Salesorder())->insertSale($ms['id'], 1, $ms['yw_time'], $ms['customer_id'],
                    $ms['piaoju_id'], $ms['jsfs'], $ms['remark'], $ms['department'], $ms['employer'], $ms['contact'], $ms['telephone'], $ms['chehao'], $this->getAccountId(), $companyId);
                $ck = (new StockOut())->insertChuku($xs['id'], 4, $ms['yw_time'], $ms['department'], $ms['system_number'], $ms['employer'], $this->getAccountId(), $companyId);

            } else {
                throw new Exception('调货销售单禁止修改');
            }

            if (!empty($data['deleteMxIds']) || !empty($updateList)) {
                throw new Exception('调货销售单禁止修改');
            }

            if (empty($data['id'])) {
                $trumpet = 0;
            } else {
                $trumpet = SalesMoshiMx::where('moshi_id', $data['id'])->max('trumpet');
            }

            foreach ($addList as $obj) {
                $trumpet++;
                $obj['moshi_id'] = $ms['id'];
                $obj['trumpet'] = $trumpet;
                $mx = new SalesMoshiMx();
                $mx->allowField(true)->data($obj)->save();

                if (!empty($mx['kc_spot_id'])) {
                    $spot1 = KcSpot::get($mx['kc_spot_id']);
                }
                if (empty($spot1)) {
                    $cbPrice = null;
                } else {
                    $cbPrice = $spot1['cb_price'];
                }

                $cgScCounts = CgPurchase::findCgScCountsByMsMxId($ms['id'], $mx['cg_customer_id'], 1, $mx['cg_piaoju_id']);
                if ($cgScCounts == 0) {
                    $cg = (new CgPurchase())->insertCaigou($mx['id'], 1, $xs['ywsj'], $mx['cg_customer_id'],
                        null, 1, $mx['cg_piaoju_id'], $ms['remark'], $ms['department'], $ms['employer'], $this->getAccountId(), $companyId);
                } else {
                    $caigouId = CgPurchase::findCgIdByMsMxId($ms['id'], $mx['cg_customer_id'], 1, $mx['cg_piaoju_id']);
                    $cg = CgPurchase::get($caigouId);
                }
                $cgmx = (new CgPurchase())->insertMx($cg, $mx['id'], 1, $mx['guige_id'], $mx['store_id'],
                    $mx['caizhi'], $mx['chandi'], null, $mx['cg_jijiafangshi_id'], $mx['changdu'], $mx['houdu'],
                    $mx['kuandu'], $mx['cg_tax'], $mx['lingzhi'], $mx['jianshu'], $mx['zhijian'], $mx['counts'],
                    $mx['cg_zhongliang'], $mx['cg_price'], $mx['cg_sumprice'], $mx['cg_tax_rate'], $mx['cg_sum_shui_price'],
                    null, $mx['pihao'], $mx['huohao'] ?? '', $mx['beizhu'], $mx['chehao'], $mx['mizhong'], $mx['jianzhong'], $companyId);

                $rk = (new KcRk())->insertRuku($cg['id'], 4, $cg['system_number'], $xs['ywsj'], $ms['department'], $ms['employer'], $this->getAccountId(), $companyId);
                $cgmxDataNumber = null;
                $spot = (new KcRk())->insertRkMxMd($rk, $cgmx['id'], 4, $xs['ywsj'], $cg['system_number'],
                    null, $mx['cg_customer_id'], null, $mx['guige_id'], $mx['caizhi'], $mx['chandi'],
                    $mx['cg_jijiafangshi_id'], $mx['store_id'], $mx['pihao'], $mx['huohao'] ?? '', $mx['chehao'], $mx['beizhu'],
                    $mx['cg_piaoju_id'], $mx['houdu'], $mx['kuandu'], $mx['changdu'], $mx['zhijian'], $mx['lingzhi'],
                    $mx['jianshu'], $mx['counts'], $mx['cg_zhongliang'], $mx['cg_price'], $mx['cg_sumprice'], $mx['cg_tax_rate'],
                    $mx['cg_sum_shui_price'], $mx['tax'], $mx['mizhong'], $mx['jianzhong'], $this->getAccountId(), $companyId);


                $saleMx = (new \app\admin\model\Salesorder())->insertMx($xs, $mx['id'], 1, $mx['guige_id'], $mx['pinming_id'], $mx['caizhi'],
                    $mx['chandi'], $mx['store_id'], $mx['jijiafangshi_id'], $mx['houdu'], $mx['kuandu'], $mx['changdu'], $mx['lingzhi'],
                    $mx['jianshu'], $mx['zhijian'], $mx['counts'], $mx['zhongliang'], $mx['jianzhong'], $mx['price'], $mx['sumprice'], $mx['tax_rate'], $mx['tax_and_price'],
                    $mx['pihao'], $mx['beizhu'], $mx['chehao'], $mx['tax'], $companyId);

                (new StockOut())->insertCkMxMd($ck, $spot['id'], $saleMx['id'], 4, $ms['yw_time'], $xs['system_no'],
                    $xs['custom_id'], $mx['guige_id'], $mx['caizhi'], $mx['chandi'], $mx['jijiafangshi_id'], $mx['store_id'],
                    $mx['houdu'], $mx['kuandu'], $mx['changdu'], $mx['zhijian'], $mx['lingzhi'], $mx['jianshu'], $mx['counts'],
                    $mx['zhongliang'], $mx['price'], $mx['sumprice'], $mx['tax_rate'], $mx['tax_and_price'], $mx['tax'], $mx['mizhong'],
                    $mx['jianzhong'], $cbPrice, '', $this->getAccountId(), $companyId);

                (new \app\admin\model\Inv())->insertInv($saleMx['id'], 3, 1, $saleMx['length'], $saleMx['houdu'],
                    $saleMx['width'], $saleMx['wuzi_id'], $saleMx['jsfs_id'], $xs['pjlx'], null, $xs['system_no'] . '.' . $saleMx['trumpet'],
                    $xs['custom_id'], $xs['ywsj'], $saleMx['price'], $saleMx['tax_rate'], $saleMx['total_fee'], $saleMx['price_and_tax'], $saleMx['weight'], $companyId);

                (new \app\admin\model\Inv())->insertInv($cgmx['id'], 2, 2, $cgmx['changdu'], $cgmx['houdu'],
                    $cgmx['kuandu'], $cgmx['guige_id'], $cgmx['jijiafangshi_id'], $cg['piaoju_id'], $cgmx['pinming_id'],
                    $cg['system_number'] . '.' . $cgmx['trumpet'], $cg['customer_id'], $cg['yw_time'],
                    $cgmx['price'], $cgmx['shui_price'], $cgmx['sumprice'], $cgmx['sum_shui_price'], $cgmx['zhongliang'], $companyId);

                if ($cgScCounts == 0) {
                    (new \app\admin\model\CapitalHk())->insertHk($cg['id'], 11, $cg['system_number'], $cg['beizhu'],
                        $cg['customer_id'], 2, $cg['yw_time'], $cg['jiesuan_id'], $cg['piaoju_id'], $cgmx['sum_shui_price'],
                        $cgmx['zhongliang'], $cg['group_id'], $cg['sale_operate_id'], $this->getAccountId(), $companyId);
                } else {
                    (new \app\admin\model\CapitalHk())->addHk($cg['id'], 11, $cg['beizhu'], $cg['customer_id'], $cg['yw_time'], $cg['jiesuan_id'], $cg['piaoju_id'], $cgmx['sum_shui_price'], $cgmx['zhongliang'], $cg['group_id']);
                }
            }

            (new CapitalFy())->fymxSave($data['other'] ?? [], $data['deleteOtherIds'] ?? [], $xs['id'], $xs['ywsj'], 1,
                $ms['department'], $ms['employer'], null, $this->getAccountId(), $companyId);


            $mxList = SalesorderDetails::where('order_id', $xs['id'])->select();
            if (!empty($mxList)) {
                foreach ($mxList as $mx) {

                    $mdList = StockOutMd::where('data_id', $mx['id'])->select();
                    if (!empty($mdList)) {
                        foreach ($mdList as $md) {
                            $md->cb_price = $md->price;
                            $jjfs = Jsfs::where('id', $mx['jsfs_id'])->cache(true, 60)->value('jj_type');
                            if ($jjfs == 1 || $jjfs == 2) {
                                $md->cb_sum_shuiprice = $md->cb_price * $md->zhongliang;
                            } elseif ($jjfs == 3) {
                                $md->cb_sum_shuiprice = $md->cb_price * $md->counts;
                            }
//                        ckMd . setCbSumPrice(WuziUtil . calSumPrice(ckMd . getCbSumShuiPrice(), md . getShuiprice()));
//                        ckMd . setCbShuie(WuziUtil . calShuie(ckMd . getCbSumShuiPrice(), md . getShuiprice()));
//                        ckMd . setFySz(ckMd . getCbSumShuiPrice() . subtract(md . getSumShuiPrice()));
                            $md->save();
                        }
                    }
                }
            }

            $sumMoney1 = SalesorderDetails::where('order_id', $xs['id'])->sum('price_and_tax');
            $sumZhongliang1 = SalesorderDetails::where('order_id', $xs['id'])->sum('weight');
            if (empty($data['id'])) {
                (new \app\admin\model\CapitalHk())->insertHk($xs['id'], 12, $xs['system_no'], $xs['remark'], $xs['custom_id'],
                    1, $xs['ywsj'], $xs['jsfs'], $xs['pjlx'], $sumMoney1, $sumZhongliang1, $xs['department'], $xs['employer'], $this->getAccountId(), $companyId);
            } else {
                throw new Exception('调货销售单禁止修改');
            }
            Db::commit();
            return returnSuc(['id' => $ms['id']]);
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**
     * 作废
     * @param Request $request
     * @param int $id
     * @return Json
     */
    public function cancel(Request $request, $id = 0)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }
        Db::startTrans();
        try {
            $ms = SalesMoshi::get($id);
            if (empty($ms)) {
                throw new Exception("对象不存在");
            }
            if ($ms->companyid != $this->getCompanyId()) {
                throw new Exception("对象不存在");
            }
            if ($ms['status'] == 2) {
                throw new Exception("该单据已经作废");
            }
            $ms->status = 2;
            $ms->save();

            $sale = \app\admin\model\Salesorder::zuofeiSale($id, 1);

            StockOut::cancelChuku($sale['id'], 4);

            $mxList = SalesMoshiMx::where('moshi_id', $ms['id'])->select();
            $invModel = new \app\admin\model\Inv();
            foreach ($mxList as $mx) {
                $cg = CgPurchase::cancelCaigou($mx['id']);

                $list = CgPurchaseMx::where('data_id', $mx['id'])->where('moshi_type', 1)->select();
                foreach ($list as $tbCgPurchaseMx) {
                    CgPurchase::allPanduanByMxId($tbCgPurchaseMx);
                }

                KcRk::cancelRuku($cg['id'], 4);
                $invModel->deleteInv($list[0]['id'], 2);
                \app\admin\model\CapitalHk::deleteHk($cg['id'], 11);
            }

            $xslist = SalesorderDetails::where('order_id', $sale['id'])->select();
            foreach ($xslist as $tbXsSaleMx) {
                $invModel->deleteInv($tbXsSaleMx['id'], 3);

                $thMxList = SalesReturnDetails::where('xs_sale_mx_id', $tbXsSaleMx['id'])->find();
                if (!empty($thMxList)) {
                    throw new Exception("该单据已有退货信息，禁止该操作！");
                }
            }
            \app\admin\model\CapitalHk::deleteHk($sale['id'], 12);

            CapitalFy::invalidByDataIdAndType($sale['id'], 1);
            Db::commit();
            return returnSuc();
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }
}