<?php


namespace app\admin\controller;


use app\admin\model\{KucunCktz, StockOtherOutDetails};
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    db\Query,
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
     */
    public function add(Request $request)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }

        Db::startTrans();
        try {
            $data = $request->post();

            $validate = new \app\admin\validate\StockOtherOut();
            if (!$validate->check($data)) {
                throw new Exception($validate->getError());
            }

            $addList = [];
            $updateList = [];
            $ja = $data['details'];
            $companyId = $this->getCompanyId();
            if (!empty($ja)) {
                $num = 1;
                $detailsValidate = new \app\admin\validate\StockOtherOutDetails();
                foreach ($ja as $object) {

                    $object['companyid'] = $companyId;
                    $object['caizhi'] = empty($v['caizhi']) ? '' : $this->getCaizhiId($v['caizhi']);
                    $object['chandi'] = empty($v['chandi']) ? '' : $this->getChandiId($v['chandi']);
                    if (!$detailsValidate->check($object)) {
                        throw new Exception('请检查第' . $num . '行' . $detailsValidate->getError());
                    }
                    $num++;

                    if ($object['lingzhi'] == 0 && $object['jianshu'] == 0 && $object['zhijian'] != 0) {
                        throw new Exception("不能只输输入件支数");
                    }

                    if ($object['lingzhi'] > 0 || $object['jianshu'] > 0 || $object['zhijian'] > 0) {
                        $jCount = $object['jianshu'] * $object['zhijian'] + $object['lingzhi'];
                        if ($jCount != $object['counts']) {
                            throw new Exception('计算的数量:' . $jCount . ',您实际输入的数量:' . $object['counts'] . ',计算数量与实际数量不相等');
                        }
                        if ($object['zhijian'] > 0 && $object['lingzhi'] >= $object['zhijian']) {
                            throw new Exception('您输入的零支为:' . $object['lingzhi'] . ',您输入的件支数为:' . $object['zhijian'] . ',零支不能大于或者等于件支数');
                        }
                    }

                    if (empty($object['id'])) {
                        $addList[] = $object;
                    } else {
                        $updateList[] = $object;
                    }
                }
            }

            if (empty($data['id'])) {
                $count = \app\admin\model\StockOtherOut::withTrashed()->whereTime('create_time', 'today')
                    ->where('companyid', $companyId)
                    ->count();
                $data['system_number'] = 'QTCKD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
                $data['create_operator_id'] = $this->getAccountId();
                $data['companyid'] = $companyId;
                $qt = new \app\admin\model\StockOtherOut();
                $qt->allowField(true)->data($data)->save();

            } else {
                $qt = \app\admin\model\StockOtherOut::where('companyid', $companyId)
                    ->where('id', $data['id'])
                    ->find();
                $data['update_operator_id'] = $this->getAccountId();
                $qt->allowField(true)->save($data);
                $mxList = StockOtherOutDetails::where('stock_other_out_id', $qt['id'])->select();
                if (!empty($mxList)) {
                    foreach ($mxList as $obj) {
                        KucunCktz::where('data_id', $obj['id'])->update([
                            'cache_customer_id' => $qt['customer_id']
                        ]);
                    }
                }
            }

            if (!empty($data['deleteMxIds'])) {
                if (is_string($data['deleteMxIds'])) {
                    $data['deleteMxIds'] = explode(',', $data['deleteMxIds']);
                }
                foreach ($data['deleteMxIds'] as $obj) {
                    (new KucunCktz())->deleteByDataIdAndChukuType($obj, 3);

                    StockOtherOutDetails::destroy(function (Query $query) use ($obj, $companyId) {
                        $query->where('id', $obj)->where('companyid', $companyId);
                    });
                }
            }

            foreach ($updateList as $mjo) {
                $mx = new StockOtherOutDetails();
                $mx->isUpdate(true)->allowField(true)->save($mjo);
                (new KucunCktz())->updateChukuTz($mx['id'], "3", $mx['guige_id'], $mx['caizhi'], $mx['chandi'],
                    $mx['jijiafangshi_id'], $mx['store_id'], $mx['houdu'], $mx['changdu'], $mx['kuandu'], $mx['counts'],
                    $mx['jianshu'], $mx['lingzhi'], $mx['zhijian'], $mx['zhongliang'], $mx['shuiprice'], $mx['sumprice'],
                    $mx['sum_shui_price'], $mx['price'], $mx['pihao'], $qt['remark'], $mx['chehao'], $qt['yw_time'],
                    $qt['system_number'], $qt['customer_id']);
            }

            foreach ($addList as $mjo) {
                $mjo['companyid'] = $companyId;
                $mjo['stock_other_out_id'] = $qt['id'];
                $mx = new StockOtherOutDetails();
                $mx->allowField(true)->save($mjo);
                (new KucunCktz())->insertChukuTz($mx['id'], 3, $mx['guige_id'], $mx['caizhi'], $mx['chandi'],
                    $mx['jijiafangshi_id'], $mx['store_id'], $mx['houdu'] ?? '', $mx['changdu'] ?? '',
                    $mx['kuandu'] ?? 0, $mx['counts'] ?? 0, $mx['jianshu'], $mx['lingzhi'] ?? 0,
                    $mx['zhijian'], $mx['zhongliang'], $mx['shuiprice'] ?? 0, $mx['sumprice'] ?? 0,
                    $mx['sum_shui_price'] ?? 0, $mx['price'], $mx['pihao'] ?? 0, $mx['beizhu'] ?? 0,
                    $mx['chehao'] ?? 0, $qt['yw_time'], $qt['system_number'], $qt['customer_id'], $this->getAccountId(),
                    $companyId);
            }

            Db::commit();
            return returnSuc(['id' => $qt['id']]);
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
            $rk = \app\admin\model\StockOtherOut::get($id);
            if (empty($rk)) {
                throw new Exception("没有此对象");
            }
            if ($rk->companyid != $this->getCompanyId()) {
                throw new Exception("没有此对象");
            }

            $rk->status = 2;
            $rk->save();

            $mxList = StockOtherOutDetails::where('stock_other_out_id', $rk['id'])->select();
            $cktzModel = new KucunCktz();
            foreach ($mxList as $mx) {
                $cktzModel->deleteByDataIdAndChukuType($mx['id'], 3);
            }
            Db::commit();
            return returnSuc();
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

}