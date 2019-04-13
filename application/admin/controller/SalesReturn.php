<?php


namespace app\admin\controller;


use app\admin\model\{SalesReturnDetails, StockOutMd};
use app\admin\validate\FeiyongDetails;
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
            'details' => ['specification', 'jsfs', 'storage', 'pinmingData'],
            'other' => ['mingxi' => ['szmcData', 'pjlxData', 'custom']]
        ])->where('companyid', $this->getCompanyId())
            ->where('id', $id)
            ->find();
        if (empty($data)) {
            return returnFail('数据不存在');
        } else {
            return returnRes(true, '', $data);
        }
    }

    /**
     * 添加销售退货单
     * @param Request $request
     * @return Json
     * @throws Exception
     */
    public function add(Request $request)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }

        $companyId = $this->getCompanyId();
        $data = request()->post();
        $count = \app\admin\model\SalesReturn::whereTime('create_time', 'today')->count();
        $data['create_operator_id'] = $this->getAccountId();
        $data['companyid'] = $companyId;
        $data['system_number'] = 'XSTHD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
        Db::startTrans();
        try {
            //保存退货单信息
            $model = new \app\admin\model\SalesReturn();
            $model->allowField(true)->data($data)->save();
            $id = $model->getLastInsID();

            //保存退货单明细
            $totalMoney = 0;
            $totalWeight = 0;
            $num = 1;
            foreach ($data["details"] as $c => $v) {
                $stockOut = StockOutMd::where('id', $v['stock_out_md_id'])
                    ->find();
                if (empty($stockOut) || $stockOut->mainData->status == 2) {
                    throw new \Exception('请检查第' . $num . '行：未找到对应发货单');
                }

                if ($v["counts"] > $stockOut["counts"]) {
                    throw new \Exception('请检查第' . $num . '行：退货数量不得大于' . $stockOut["counts"]);
                }
                if ($v["zhongliang"] > $stockOut["zhongliang"]) {
                    throw new \Exception('请检查第' . $num . '行：退货重量不得大于' . $stockOut["zhongliang"]);
                }
                $totalMoney += $v['sum_shui_price'];
                $totalWeight += $v['zhongliang'];
                $data['details'][$c]['companyid'] = $companyId;
                $data['details'][$c]['xs_th_id'] = $id;
                $data['details'][$c]['spot_id'] = $stockOut->kc_spot_id;
                $data['details'][$c]['create_operate_id'] = $this->getAccountId();
                $data['details'][$c]['caizhi_id'] = $this->getCaizhiId($v['caizhi']);
                $data['details'][$c]['chandi_id'] = $this->getChandiId($v['chandi']);

                $num++;
            }
            (new SalesReturnDetails())->allowField(true)->saveAll($data['details']);
            //执行入库
            $stockInData = [
                'customer_id' => $data['customer_id'],
                'beizhu' => '销售退货单，',
                'yw_time' => $data['yw_time'],
                'group_id' => $data['group_id'],
                'sale_operator_id' => $data['sale_operator_id'],
                'ruku_fangshi' => 1,
                'create_operate_id' => $this->getAccountId(),
                'piaoju_id' => $data['piaoju_id']
            ];
            foreach ($data['details'] as $index => $item) {
                $stockInData['details'][$index] = $item;
                $stockInData['details'][$index]['ruku_type'] = 7;
                $stockInData['details'][$index]['ruku_fangshi'] = 1;
            }
            (new Purchase())->zidongruku($id, $stockInData, 7);
            //其他费用
            $num = 1;
            if (!empty($data['other'])) {
                $otherValidate = new FeiyongDetails();
                //处理其他费用
                foreach ($data['other'] as $c => $v) {
                    $data['other'][$c]['group_id'] = $data['department'] ?? '';
                    $data['other'][$c]['sale_operator_id'] = $data['employer'] ?? '';

                    if (!$otherValidate->check($data['other'][$c])) {
                        throw new Exception('请检查第' . $num . '行' . $otherValidate->getError());
                    }
                    $num++;
                }
                $res = (new Feiyong())->addAll($data['other'], 3, $id, $data['yw_time'], false);
                if ($res !== true) {
                    throw new Exception($res);
                }
            }
            //向货款单添加数据
            $capitalHkData = [
                'hk_type' => CapitalHk::SALES_ORDER_RETURN,
                'data_id' => $id,
                'fangxiang' => 1,
                'customer_id' => $data['customer_id'],
                'jiesuan_id' => $data['jiesuan_id'],
                'system_number' => $data['system_number'],
                'yw_time' => $data['yw_time'],
                'beizhu' => $data['beizhu'],
                'money' => -$totalMoney,
                'group_id' => $data['group_id'],
                'sale_operator_id' => $data['sale_operator_id'],
                'create_operator_id' => $data['create_operator_id'],
                'zhongliang' => -$totalWeight,
                'cache_pjlx_id' => $data['piaoju_id'],
            ];
            (new CapitalHk())->add($capitalHkData);

            Db::commit();
            return returnRes(true, '', ['id' => $id]);
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
            return returnFail($e->getMessage());
        }
    }
}