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

class Zhifa extends Right
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
            'gongyingshang',
            'gfpjData',
            'khpjData',
            'gfjsfsData',
            'khjsfsData',
            'createOperator',
        ])->where('companyid', $this->getCompanyId())
            ->order('create_time', 'desc')
            ->where('moshi_type', 2);
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('yw_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['cgpb'])) {
            $list->where('cg_piaoju_id', $params['cgpb']);
        }
        if (!empty($params['status'])) {
            $list->where('status', $params['status']);
        }
        if (!empty($params['system_no'])) {
            $list->where('system_number', 'like', '%' . $params['system_no'] . '%');
        }
        if (!empty($params['custom_id'])) {
            $list->where('customer_id', $params['custom_id']);
        }
        if (!empty($params['xspb'])) {
            $list->where('piaoju_id', $params['xspb']);
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
            'gongyingshang',
            'gfpjData',
            'khpjData',
            'gfjsfsData',
            'khjsfsData',
            'details' => ['specification', 'jsfs', 'storage', 'caizhiData', 'chandiData'],
            'other' => ['other' => ['mingxi' => ['szmcData', 'pjlxData', 'custom', 'szflData']]],
            'createOperator',
        ])->where('companyid', $this->getCompanyId())
            ->where('moshi_type', 2)
            ->where('id', $id)
            ->find();
        if (empty($data)) {
            return returnFail('数据不存在');
        } else {
            return returnRes(true, '', $data);
        }
    }

    /**
     * 添加采购直发单
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

            $validate = new \app\admin\validate\SalesMoshi();
            if (!$validate->scene('zhifa')->check($data)) {
                throw new Exception($validate->getError());
            }

            $addList = [];
            $updateList = [];
            $ja = $data['details'];
            $jqDataType1 = null;
            $companyId = $this->getCompanyId();
            if (!empty($ja)) {
                $detailsValidate = new SalesMoshiDetails();
                $num = 1;

                foreach ($ja as $object) {
                    $object['companyid'] = $companyId;
                    if (!$detailsValidate->scene('zhifa')->check($object)) {
                        throw new Exception('请检查第' . $num . '行' . $detailsValidate->getError());
                    }
                    $object['caizhi'] = $this->getCaizhiId($object['caizhi'] ?? '');
                    $object['chandi'] = $this->getCaizhiId($object['chandi'] ?? '');
                    if (empty($object['id'])) {
                        $addList[] = $object;
                    } else {
                        $updateList[] = $object;
                    }
                    $num++;
                }
            }

            if (empty($data['id'])) {
                $count = SalesMoshi::withTrashed()->where('companyid', $companyId)
                    ->whereTime('create_time', 'today')
                    ->where('moshi_type', 2)
                    ->count();
                $data['create_operator_id'] = $this->getAccountId();
                $data['moshi_type'] = 2;
                $data['companyid'] = $companyId;
                $data['system_number'] = 'CGZFD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
                $ms = new SalesMoshi();
                $ms->allowField(true)->data($data)->save();

                $cg = (new CgPurchase())->insertCaigou($ms['id'], 2, $ms['yw_time'], $ms['cg_customer_id'],
                    $ms['cg_jiesuan_id'], 1, $ms['cg_piaoju_id'], $ms['remark'], $ms['department'], $ms['employer'], $this->getAccountId(), $companyId);
                $rk = (new KcRk())->insertRuku($cg['id'], 4, $cg['system_number'], $ms['yw_time'], $ms['department'], $ms['employer'], $this->getAccountId(), $companyId);

                $sale = (new \app\admin\model\Salesorder())->insertSale($ms['id'], "2", $ms['yw_time'], $ms['customer_id'],
                    $ms['piaoju_id'], $ms['jsfs'], $ms['remark'], $ms['department'], $ms['employer'], $ms['contact'], $ms['telephone'], $ms['chehao'], $this->getAccountId(), $companyId);
                $ck = (new StockOut())->insertChuku($sale['id'], 4, $ms['yw_time'], $ms['department'], $ms['system_number'], $ms['employer'], $this->getAccountId(), $companyId);
            } else {
                throw new Exception('采购直发单禁止修改');
            }

            if (!empty($data['deleteMxIds']) || !empty($updateList)) {
                throw new Exception('采购直发单禁止修改');
            }

            if (empty($data['id'])) {
                $trumpet = 0;
            } else {
                $trumpet = SalesMoshiMx::where('companyid', $companyId)->where('moshi_id', $data['id'])->max('trumpet');
            }
            foreach ($addList as $obj) {
                $trumpet++;
                $obj['trumpet'] = $trumpet;
                $obj['moshi_id'] = $ms['id'];
                $mx = new SalesMoshiMx();
                $mx->allowField(true)->save($obj);

                $cbPrice = null;
                if (!empty($mx['kc_spot_id'])) {
                    $spot1 = KcSpot::get($mx['kc_spot_id']);
                    if (empty($spot1)) {
                        $cbPrice = null;
                    } else {
                        $cbPrice = $spot1['cb_price'];
                    }
                }
                $cgmx = (new CgPurchase())->insertMx($cg, $mx['id'], 2, $mx['guige_id'], $mx['store_id'],
                    $mx['caizhi'], $mx['chandi'], null, $mx['jijiafangshi_id'], $mx['changdu'], $mx['houdu'],
                    $mx['kuandu'], $mx['cg_tax'], $mx['cg_lingzhi'], $mx['cg_jianshu'], $mx['zhijian'], $mx['cg_counts'],
                    $mx['cg_zhongliang'], $mx['cg_price'], $mx['cg_sumprice'], $mx['cg_tax_rate'], $mx['cg_sum_shui_price'],
                    null, $mx['pihao'], $mx['huohao'] ?? '', $mx['beizhu'], $mx['chehao'], $mx['mizhong'], $mx['jianzhong'], $companyId);
                $spot = (new KcRk())->insertRkMxMd($rk, $cgmx['id'], 4, $ms['yw_time'], $cg['system_number'],
                    null, $cg['customer_id'], null, $mx['guige_id'], $mx['caizhi'], $mx['chandi'],
                    $mx['jijiafangshi_id'], $mx['store_id'], $mx['pihao'], $mx['huohao'] ?? '', $mx['chehao'], $mx['beizhu'],
                    $ms['cg_piaoju_id'], $mx['houdu'], $mx['kuandu'], $mx['changdu'], $mx['zhijian'], $mx['cg_lingzhi'],
                    $mx['cg_jianshu'], $mx['cg_counts'], $mx['cg_zhongliang'], $mx['cg_price'], $mx['cg_sumprice'], $mx['cg_tax_rate'],
                    $mx['cg_sum_shui_price'], $mx['cg_tax'], $mx['mizhong'], $mx['jianzhong'], $this->getAccountId(), $companyId);

                $xsmx = (new \app\admin\model\Salesorder())->insertMx($sale, $mx['id'], 2, $mx['guige_id'], $mx['pinming_id'], $mx['caizhi'],
                    $mx['chandi'], $mx['store_id'], $mx['jijiafangshi_id'], $mx['houdu'], $mx['kuandu'], $mx['changdu'], $mx['lingzhi'],
                    $mx['jianshu'], $mx['zhijian'], $mx['counts'], $mx['zhongliang'], $mx['jianzhong'], $mx['price'], $mx['sumprice'], $mx['tax_rate'], $mx['tax_and_price'],
                    $mx['pihao'], $mx['beizhu'], $mx['chehao'], $mx['tax'], $companyId);

                (new StockOut())->insertCkMxMd($ck, $spot['id'], $xsmx['id'], 4, $ms['yw_time'], $sale['system_no'],
                    $sale['custom_id'], $mx['guige_id'], $mx['caizhi'], $mx['chandi'], $mx['jijiafangshi_id'], $mx['store_id'],
                    $mx['houdu'], $mx['kuandu'], $mx['changdu'], $mx['zhijian'], $mx['lingzhi'], $mx['jianshu'], $mx['counts'],
                    $mx['zhongliang'], $mx['price'], $mx['sumprice'], $mx['tax_rate'], $mx['tax_and_price'], $mx['tax'], $mx['mizhong'],
                    $mx['jianzhong'], $cbPrice, '', $this->getAccountId(), $companyId);

                (new \app\admin\model\Inv())->insertInv($xsmx['id'], 3, 1, $xsmx['length'], $xsmx['houdu'],
                    $xsmx['width'], $xsmx['wuzi_id'], $xsmx['jsfs_id'], $sale['pjlx'], null, $sale['system_no'] . '.' . $xsmx['trumpet'],
                    $sale['custom_id'], $sale['ywsj'], $xsmx['price'], $xsmx['tax_rate'], $xsmx['total_fee'], $xsmx['price_and_tax'], $xsmx['weight'], $companyId);

                (new \app\admin\model\Inv())->insertInv($cgmx['id'], 2, 2, $cgmx['changdu'], $cgmx['houdu'],
                    $cgmx['kuandu'], $cgmx['guige_id'], $cgmx['jijiafangshi_id'], $cg['piaoju_id'], $cgmx['pinming_id'],
                    $cg['system_number'] . '.' . $cgmx['trumpet'], $cg['customer_id'], $cg['yw_time'],
                    $cgmx['price'], $cgmx['shui_price'], $cgmx['sumprice'], $cgmx['sum_shui_price'], $cgmx['zhongliang'], $companyId);
            }

            (new CapitalFy())->fymxSave($data['other'], $data['deleteOtherIds'] ?? [], $sale['id'], $sale['ywsj'], 1, $ms['department'], $ms['employer'], null, $this->getAccountId(), $companyId);

            $mxList = SalesorderDetails::where('order_id', $sale['id'])->select();
            if (!empty($mxList)) {
                foreach ($mxList as $mx) {

                    $spot = KcSpot::get($mx['kc_spot_id']);
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
//                            ckMd . setCbSumPrice(WuziUtil . calSumPrice(ckMd . getCbSumShuiPrice(), md . getShuiprice()));
//                            ckMd . setCbShuie(WuziUtil . calShuie(ckMd . getCbSumShuiPrice(), md . getShuiprice()));
//                            ckMd . setFySz(ckMd . getCbSumShuiPrice() . subtract(md . getSumShuiPrice()));
                                $md->save();
                            }
                        }
                    }
                }
            }
            $sumMoney = CgPurchaseMx::where('purchase_id', $cg['id'])->sum('sum_shui_price');
            $sumZhongliang = CgPurchaseMx::where('purchase_id', $cg['id'])->sum('zhongliang');

            $sumMoney1 = SalesorderDetails::where('order_id', $sale['id'])->sum('price_and_tax');
            $sumZhongliang1 = SalesorderDetails::where('order_id', $sale['id'])->sum('weight');
            if (empty($data['id'])) {
                (new \app\admin\model\CapitalHk())->insertHk($cg['id'], 11, $cg['system_number'], $cg['beizhu'], $cg['customer_id'], 2, $cg['yw_time'], $cg['jiesuan_id'], $cg['piaoju_id'], $sumMoney, $sumZhongliang, $cg['group_id'], $cg['sale_operate_id'], $this->getAccountId(), $companyId);
                (new \app\admin\model\CapitalHk())->insertHk($sale['id'], 12, $sale['system_no'], $sale['remark'], $sale['custom_id'], 1, $sale['ywsj'], $sale['jsfs'], $sale['pjlx'], $sumMoney1, $sumZhongliang1, $sale['department'], $sale['employer'], $this->getAccountId(), $companyId);
            } else {
                throw new Exception('采购直发单禁止修改');
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
            if ($ms['status'] == 2) {
                throw new Exception("该单据已经作废");
            }
            $ms['status'] = 2;
            $ms->save();

            $sale = \app\admin\model\Salesorder::where('data_id', $ms['id'])->where('ywlx', $ms['moshi_type'])->find();

            $tbCg = CgPurchase::where('data_id', $ms['id'])->where('moshi_type', $ms['moshi_type'])->find();

            (new StockOut())->deleteChuku($sale['id'], 4);

            (new KcRk())->deleteRuku($tbCg['id'], 4);

            $cglist = CgPurchaseMx::where('purchase_id', $tbCg['id'])->select();
            $invModel = new \app\admin\model\Inv();
            foreach ($cglist as $mx) {
                CgPurchase::allPanduanByMxId($mx);
                $invModel->deleteInv($mx['id'], 2);
            }

            $xslist = SalesorderDetails::where('order_id', $sale['id'])->select();

            foreach ($xslist as $tbXsSaleMx) {
                $invModel->deleteInv($tbXsSaleMx['id'], 3);

                $thMxList = SalesReturnDetails::where('xs_sale_mx_id', $tbXsSaleMx['id'])->select();
                if (!empty($thMxList)) {
                    throw new Exception("该单据已有退货信息，禁止该操作！");
                }
            }

            \app\admin\model\CapitalHk::deleteHk($tbCg['id'], 11);
            \app\admin\model\CapitalHk::deleteHk($sale['id'], 12);

            (new CapitalFy())->deleteByDataIdAndType($sale['id'], 1);

            (new \app\admin\model\Salesorder())->deleteSale($ms['id'], 2);

            (new CgPurchase())->deleteCaigou($ms['id'], 2);
            Db::commit();
            return returnSuc();
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }

    }
}