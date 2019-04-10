<?php


namespace app\admin\controller;


use app\admin\model\StockOtherOutDetails;
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    Request,
    response\Json};

class StockOtherOut extends Right
{
    /**
     * 获取其他出库单列表
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
        $list = \app\admin\model\StockOtherOut::with([
            'custom',
            'pjlxData',
            'jsfsData',
        ])->where('companyid', $this->getCompanyId())
            ->order('create_time', 'desc');
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
     * 获取销售单详情
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
        $data = \app\admin\model\StockOtherOut::with([
            'custom',
            'pjlxData',
            'jsfsData',
            'details' => ['specification', 'jsfs', 'storage'],
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
     * 添加其他出库单
     * @param Request $request
     * @return Json
     * @throws \think\Exception
     */
    public function add(Request $request)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }
        $companyId = $this->getCompanyId();
        $count = \app\admin\model\StockOtherOut::whereTime('create_time', 'today')
            ->where('companyid', $companyId)
            ->count();

        //数据处理
        $data = $request->post();

        $systemNumber = 'QTCKD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
        $data['companyid'] = $companyId;
        $data['system_number'] = $systemNumber;
        $data['create_operator_id'] = $this->getAccountId();

        //数据验证
        $validate = new \app\admin\validate\StockOtherOut();
        if (!$validate->check($data)) {
            return returnFail($validate->getError());
        }

        Db::startTrans();
        try {
            $model = new \app\admin\model\StockOtherOut();
            $model->allowField(true)->data($data)->save();

            //处理明细
            $id = $model->id;
            $num = 1;
            $detailsValidate = new \app\admin\validate\StockOtherOutDetails();
            foreach ($data['details'] as $c => $v) {
                $data['details'][$c]['companyid'] = $companyId;
                $data['details'][$c]['stock_other_out_id'] = $id;
                if (!$detailsValidate->check($data['details'][$c])) {
                    throw new Exception('请检查第' . $num . '行' . $detailsValidate->getError());
                }
                $num++;
            }
            (new StockOtherOutDetails())->allowField(true)->saveAll($data['details']);

            //添加出库通知单
            $notify = [];
            foreach ($data['details'] as $c => $v) {
                $notify[] = [
                    'companyid' => $companyId,
                    'chuku_type' => 3,
                    'data_id' => $id,
                    'guige_id' => $v['guige_id'],
                    'caizhi' => $v['caizhi'] ?? '',
                    'chandi' => $v['chandi'] ?? '',
                    'jijiafangshi_id' => $v['jijiafangshi_id'],
                    'houdu' => $v['houdu'] ?? '',
                    'kuandu' => $v['kuandu'] ?? '',
                    'changdu' => $v['changdu'] ?? '',
                    'lingzhi' => $v['lingzhi'] ?? '',
                    'jianshu' => $v['jianshu'] ?? '',
                    'zhijian' => $v['zhijian'] ?? '',
                    'counts' => $v['counts'] ?? '',
                    'zhongliang' => $v['zhongliang'] ?? '',
                    'price' => $v['price'] ?? '',
                    'sumprice' => $v['sumprice'] ?? '',
                    'shuie' => $v['shuie'] ?? '',
                    'shui_price' => $v['shui_price'] ?? '',
                    'sum_shui_price' => $v['sum_shui_price'] ?? '',
                    'remark' => $v['beizhu'] ?? '',
                    'car_no' => $v['chehao'] ?? '',
                    'pihao' => $v['pihao'] ?? '',
                    'cache_ywtime' => $data['yw_time'],
                    'cache_data_pnumber' => $data['system_number'],
                    'cache_customer_id' => $data['customer_id'],
                    'store_id' => $v['store_id'],
                    'cache_create_operator' => $data['create_operator_id'],
                ];
            }
            (new Chuku())->addNotify($notify);

            Db::commit();
            return returnRes(true, '', ['id' => $id]);
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**
     * 审核
     * @param Request $request
     * @param int $id
     * @return Json
     * @throws DbException
     */
    public function audit(Request $request, $id = 0)
    {
        if ($request->isPut()) {
            $salesorder = \app\admin\model\StockOtherOut::where('id', $id)
                ->where('companyid', $this->getCompanyId())
                ->find();
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
            $salesorder->check_operator_id = $this->getAccountId();
            $salesorder->save();
            return returnSuc();
        }
        return returnFail('请求方式错误');
    }

    /**
     * 反审核
     * @param Request $request
     * @param int $id
     * @return Json
     * @throws DbException
     */
    public function unAudit(Request $request, $id = 0)
    {
        if ($request->isPut()) {
            $salesorder = \app\admin\model\StockOtherOut::where('id', $id)
                ->where('companyid', $this->getCompanyId())
                ->find();
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
            $salesorder->check_operator_id = null;
            $salesorder->save();
            return returnSuc();
        }
        return returnFail('请求方式错误');
    }

    /**
     * 作废
     * @param Request $request
     * @param int $id
     * @return Json
     * @throws DbException
     */
    public function cancel(Request $request, $id = 0)
    {
        if ($request->isPost()) {
            $salesorder = \app\admin\model\StockOtherOut::where('id', $id)
                ->where('companyid', $this->getCompanyId())
                ->find();
            if (empty($salesorder)) {
                return returnFail('数据不存在');
            }
            if ($salesorder->status == 3) {
                return returnFail('此单已审核，禁止作废');
            }
            if ($salesorder->status == 2) {
                return returnFail('此单已作废');
            }
            $salesorder->status = 2;
            $salesorder->save();
            return returnSuc();
        }
        return returnFail('请求方式错误');
    }

}