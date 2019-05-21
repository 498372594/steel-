<?php


namespace app\admin\controller;


use app\admin\model\{CapitalFy, Jsfs, KcRk, KcSpot, SalesReturnDetails, StockOutMd};
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    Exception,
    exception\DbException,
    Request,
    response\Json};

class SalesReturn extends Right
{
    /**
     * 获取销售退货单列表
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
        $list = $list = \app\admin\model\SalesReturn::with(['jsfsData', 'custom', 'pjlxData'])
            ->where('companyid', $this->getCompanyId());
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->whereTime('yw_time', '<', strtotime($params['ywsjEnd'] . ' +1 day'));
        }
        if (!empty($params['status'])) {
            $list->where('status', $params['status']);
        }
        //往来单位
        if (!empty($params['customer_id'])) {
            $list->where('customer_id', $params['customer_id']);
        }
        //票据类型
        if (!empty($params['piaoju_id'])) {
            $list->where('piaoju_id', $params['piaoju_id']);
        }
        //系统单号
        if (!empty($params['system_number'])) {
            $list->where('system_number', 'like', '%' . $params['system_number'] . '%');
        }
        //备注
        if (!empty($params['beizhu'])) {
            $list->where('beizhu', 'like', '%' . $params['beizhu'] . '%');
        }
        $list = $list->paginate($pageLimit);
        return returnRes(true, '', $list);
    }

    /**
     * 获取销售退货单详情
     * @param int $id
     * @return Json
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function details($id = 0)
    {
        $data = $list = \app\admin\model\SalesReturn::with([
            "jsfsData",
            "custom",
            "pjlxData",
            'details' => ['jsfs', 'storage', 'pinmingData', 'caizhi', 'chandi'],
            'other' => ['mingxi' => ['szmcData', 'pjlxData', 'custom']]
        ])->where('companyid', $this->getCompanyId())
            ->where('id', $id)
            ->find();
        return returnRes(true, '', $data);
    }

    /**
     * 添加销售退货单
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
            $addList = [];
            $updateList = [];
            $ja = $data['details'];
            $companyId = $this->getCompanyId();
            if (!empty($ja)) {
                foreach ($ja as $object) {
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

                $count = \app\admin\model\SalesReturn::withTrashed()->whereTime('create_time', 'today')
                    ->where('companyid', $companyId)
                    ->count();
                $data['companyid'] = $companyId;
                $data['system_number'] = 'XSTHD' . date('Ymd') . str_pad(++$count, 3, 0, STR_PAD_LEFT);
                $data['create_operator_id'] = $this->getAccountId();
                $th = new \app\admin\model\SalesReturn();
                $th->allowField(true)->data($data)->save();

                $rk = (new KcRk())->insertRuku($th['id'], 7, $th['system_number'], $th['yw_time'], $th['group_id'], $th['sale_operator_id'], $this->getAccountId(), $companyId);
            } else {
                throw new \Exception('销售退货单禁止修改');
            }

            if (!empty($data['deleteMxIds']) || !empty($updateList)) {
                throw new \Exception('销售退货单禁止修改');
            }

            $trumpet = 0;

            if (!empty($addList)) {
                if (!empty($data['id'])) {
                    $trumpet = SalesReturnDetails::where('xs_th_id', $data['id'])->max('trumpet');
                }
                foreach ($addList as $map) {
                    $trumpet++;
                    $mx = new SalesReturnDetails();

                    $maxCounts = StockOutMd::findCountsByDataId($map['xs_sale_mx_id']);
                    $thCounts = SalesReturnDetails::findCountsByXsSaleMxId($map['xs_sale_mx_id']);
                    $maxZhongliang = StockOutMd::findZhongliangByDataId($map['xs_sale_mx_id']);
                    $thZhongliang = SalesReturnDetails::findZhongliangByXsSaleMxId($map['xs_sale_mx_id']);

                    if ($maxCounts - $thCounts < $map['counts']) {
                        throw new Exception('本次退货数量大于销售出库数量(销售出库数量为：' . ($maxCounts - $thCounts) . ')');
                    }

                    if ($maxZhongliang - $thZhongliang < $map['zhongliang']) {
                        throw new Exception('本次退货重量大于销售出库重量(销售出库重量为：' . ($maxZhongliang - $thZhongliang) . ')');
                    }

                    $map['xs_th_id'] = $th['id'];

                    $mx->allowField(true)->data($map)->save();

                    $spot = KcSpot::get($mx['spot_id']);

                    $price = $spot['price'];
                    $sumShuiPrice = 0;
                    $sumPrice = 0;
                    $shuie = 0;
                    $jjfs = Jsfs::where('id', $spot['jijiafangshi_id'])->cache(true, 60)->find();

                    if ($jjfs == 1 || $jjfs == 2) {
                        $sumShuiPrice = $price * $mx['zhongliang'];
                    } elseif ($jjfs == 3) {
                        $sumShuiPrice = $price * $mx['counts'];
                    }
//                sumPrice = WuziUtil . calSumPrice(sumShuiPrice, price);
//                shuie = WuziUtil . calShuie(sumShuiPrice, spot . getShuiprice());

                    (new KcRk())->insertRkMxMd($rk, $mx['id'], 7, $th['yw_time'], $th['system_number'],
                        null, $spot['customer_id'], $mx['pinming_id'], $mx['guige_id'], $mx['caizhi_id'],
                        $mx['chandi_id'], $mx['jijiafangshi_id'], $mx['store_id'], $mx['pihao'], $mx['huohao'], $mx['chehao'],
                        $mx['beizhu'], $th['piaoju_id'], $mx['houdu'], $mx['kuandu'], $mx['changdu'], $mx['zhijian'],
                        $mx['lingzhi'], $mx['jianshu'], $mx['counts'], $mx['zhongliang'], $price, $sumPrice, $spot['shui_price'],
                        $sumShuiPrice, $shuie, $mx['mizhong'], $mx['jianzhong'], $this->getAccountId(), $companyId);

                    (new \app\admin\model\Inv())->insertInv($mx['id'], 6, 1, $mx['changdu'], $mx['houdu'],
                        $mx['kuandu'], $mx['guige_id'], $mx['jijiafangshi_id'], $mx['piaoju_id'], $mx['pinming_id'],
                        $th['system_number'] . '.' . $mx['trumpet'], $th['customer_id'], $th['yw_time'],
                        $mx['price'], $mx['shuiprice'], -$mx['sumprice'], -$mx['sum_shui_price'], -$mx['zhongliang'], $companyId);
                }
            }

            $sumMoney = SalesReturnDetails::getSumJiashuiHejiByPid($th['id']);
            $sumZhongliang = SalesReturnDetails::getSumZhongliangByPid($th['id']);

            if (empty($data['id'])) {
                (new \app\admin\model\CapitalHk())->insertHk($th['id'], 14, $th['system_number'], $th['beizhu'], $th['customer_id'],
                    1, $th['yw_time'], $th['jiesuan_id'], $th['piaoju_id'], $sumMoney, $sumZhongliang, $th['group_id'], $th['sale_operator_id'], $this->getAccountId(), $companyId);
            } else {
                throw new \Exception('销售退货单禁止修改');
//            this . hkDaoImpl . updateHk(th . getId(), "14", th . getBeizhu(), th . getCustomerId(), th . getYwTime(), th . getJiesuanId(), th . getPiaojuId(), new BigDecimal(0) . subtract(sumMoney), new BigDecimal(0) . subtract(sumZhongliang), th . getGroupId(), th . getSaleOperatorId());
            }

            $thBeizhu = "销售退货费用";
            (new CapitalFy())->fymxSave($data['other'] ?? [], $data['deleteOtherIds'] ?? [], $th['id'], $th['yw_time'], 5,
                $th['group_id'], $th['sale_operator_id'], $thBeizhu, $this->getAccountId(), $companyId);
            Db::commit();
            return returnSuc(['id' => $th['id']]);
        } catch (\Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**
     * 作废销售退货单
     * @param Request $request
     * @param int $id
     * @return Json
     */
    public function cancel(Request $request, $id = 0)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }
        try {
            $th = \app\admin\model\SalesReturn::get($id);
            if (empty($th)) {
                throw new Exception("对象不存在");
            }
            if ($th->companyid != $this->getCompanyId()) {
                throw new Exception("对象不存在");
            }
            if ($th['status'] == 2) {
                throw new Exception("该单据已经作废");
            }
            $th->status = 2;
            $th->save();

            $mxList = SalesReturnDetails::where('order_id', $th['id'])->select();
            if (!empty($mxList)) {
                $invModel = new \app\admin\model\Inv();
                foreach ($mxList as $tbXsThMx) {
                    $invModel->deleteInv($tbXsThMx['id'], 6);
                }
            }

            KcRk::cancelRuku($th['id'], 7);

            \app\admin\model\CapitalHk::deleteHk($th['id'], 14);

            (new CapitalFy())->deleteByDataIdAndType($th['id'], 5);
            Db::commit();
            return returnSuc();
        } catch (\Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }
}