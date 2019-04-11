<?php


namespace app\admin\controller;

use app\admin\model\{CapitalFy as CapitalFyModel,
    CapitalHk as CapitalHkModel,
    CapitalOther as CapitalOtherModel,
    CapitalSk as CapitalSkModel,
    CapitalSkhx as CapitalSkhxModel,
    CapitalSkjsfs as CapitalSkjsfsModel};
use app\admin\validate\{CapitalSk as CapitalSkValidate,
    CapitalSkhx as CapitalSkhxValidate,
    CapitalSkJsfs as CapitalSkJsfsValidate};
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    Request,
    response\Json};

class CapitalSk extends Right
{
    /**
     * 获取收款单列表
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
        $list = CapitalSkModel::with([
            'custom'
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
        if (!empty($params['system_number'])) {
            $list->where('system_number', 'like', '%' . $params['system_number'] . '%');
        }
        if (!empty($params['customer_id'])) {
            $list->where('customer_id', $params['customer_id']);
        }
        if (!empty($params['beizhu'])) {
            $list->where('beizhu', 'like', '%' . $params['beizhu'] . '%');
        }
        $list = $list->paginate($pageLimit);
        return returnSuc($list);
    }

    /**
     * 添加收款单
     * @param Request $request
     * @return Json
     * @throws \think\Exception
     */
    public function add(Request $request)
    {
        $companyid = $this->getCompanyId();
        $count = CapitalSkModel::whereTime('create_time', 'today')->where('companyid', $companyid)->count() + 1;

        $data = $request->post();
        $data['companyid'] = $companyid;
        $data['system_number'] = 'SKD' . date('Ymd') . str_pad($count, 3, 0, STR_PAD_LEFT);
        $data['create_operator_id'] = $this->getAccountId();
        $data['fymx_create_type'] = 2;
        $validate = new CapitalSkValidate();
        if (!$validate->check($data)) {
            return returnFail($validate->getError());
        }
        Db::startTrans();
        try {
            $model = new CapitalSkModel();
            $model->allowField(true)->data($data)->save();
            $id = $model->getLastInsID();

            //核销明细
            $detailsValidate = new CapitalSkhxValidate();
            foreach ($data['details'] as $c => $v) {
                $v['companyid'] = $companyid;
                $v['sk_id'] = $id;
                if (!$detailsValidate->check($v)) {
                    throw new Exception($detailsValidate->getError());
                }
                if ($v['skhx_type'] == CapitalHk::CAPITAL_OTHER) {
                    $relation = CapitalOtherModel::where('fangxiang', 1)
                        ->where('id', $v['data_id'])
                        ->where('status', '<>', '2')
                        ->find();
                } elseif ($v['skhx_type'] == CapitalHk::CAPITAL_COST) {
                    $relation = CapitalFyModel::where('fang_xiang', 1)
                        ->where('id', $v['data_id'])
                        ->where('status', '<>', '2')
                        ->find();
                } else {
                    $relation = CapitalHkModel::where('id', $v['data_id'])
                        ->where('fangxiang', 1)
                        ->where('status', '<>', '2')
                        ->find();
                }
                if (empty($relation)) {
                    throw new Exception('未找到对应源单');
                }
                if ($v['hx_money'] > $relation->money - $relation->hxmoney) {
                    throw new Exception('核销金额不能大于总金额');
                }
                if ($v['hx_zhongliang'] > $relation->zhongliang - $relation->hxzhongliang) {
                    throw new Exception('核销重量不能大于总金额');
                }
                $relation->hxmoney += $v['hx_money'];
                $relation->hxzhongliang += $v['hx_zhongliang'];
                $relation->save();

                $v['customer_id'] = $relation->customer_id;
                $v['cache_ywtime'] = $relation->yw_time;
                $v['cache_systemnumber'] = $relation->system_number;
                $v['hj_money'] = $relation->money;
                $v['hj_zhongliang'] = $relation->zhongliang;

                (new CapitalSkhxModel())->allowField(true)->save($v);
            }

            //款项明细
            $shoukuanValidate = new CapitalSkJsfsValidate();
            $totalMoney = 0;
            foreach ($data['mingxi'] as $c => $v) {
                $data['mingxi'][$c]['companyid'] = $companyid;
                $data['mingxi'][$c]['sk_id'] = $id;
                if (!$shoukuanValidate->check($data['mingxi'][$c])) {
                    throw new Exception($shoukuanValidate->getError());
                }
                $totalMoney += $v['money'];
            }
            if ($totalMoney != $data['money']) {
                throw new Exception('收款金额必须等于本次收款');
            }
            (new CapitalSkjsfsModel())->allowField(true)->saveAll($data['mingxi']);

            Db::commit();
            return returnSuc();
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**
     * 获取收款单详情
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
        $data = CapitalSkModel::with([
            'custom',
            'mingxi' => ['jsfs','bank'],
            'details' => ['custom']
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
     * @return Json
     * @throws DbException
     */
    public function cancel(Request $request, $id = 0)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }
        $shoukuan = CapitalSkModel::where('id', $id)
            ->where('companyid', $this->getCompanyId())
            ->find();
        if (empty($shoukuan)) {
            return returnFail('数据不存在');
        }
        if ($shoukuan->status == 3) {
            return returnFail('此单已审核，禁止作废');
        }
        if ($shoukuan->status == 2) {
            return returnFail('此单已作废');
        }
        Db::startTrans();
        try {
            $shoukuan->status = 2;
            $shoukuan->save();
            //核销记录退回
            foreach ($shoukuan->details as $item) {
                if ($item['skhx_type'] == CapitalHk::CAPITAL_OTHER) {
                    $relation = CapitalOtherModel::where('fangxiang', 1)
                        ->where('id', $item['data_id'])
                        ->where('status', '<>', '2')
                        ->find();
                } elseif ($item['skhx_type'] == CapitalHk::CAPITAL_COST) {
                    $relation = CapitalFyModel::where('fang_xiang', 1)
                        ->where('id', $item['data_id'])
                        ->where('status', '<>', '2')
                        ->find();
                } else {
                    $relation = CapitalHkModel::where('id', $item['data_id'])
                        ->where('fangxiang', 1)
                        ->where('status', '<>', '2')
                        ->find();
                }
                if (empty($relation)) {
                    throw new Exception('未知错误');
                }
                $relation->hxmoney -= $item->hx_money;
                $relation->hxzhongliang -= $item->hx_zhongliang;
                $relation->save();
            }
            Db::commit();
            return returnSuc();
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

}