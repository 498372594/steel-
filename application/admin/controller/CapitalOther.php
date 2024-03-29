<?php


namespace app\admin\controller;


use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    Request,
    response\Json};

class CapitalOther extends Right
{

    /**
     * 获取其他费用单列表
     * @param Request $request
     * @param int $pageLimit
     * @param int $fangxiang
     * @return Json
     * @throws DbException
     */
    public function getList(Request $request, $fangxiang = 1, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $params = $request->param();
        $list = \app\admin\model\CapitalOther::with([
            'custom',
            'jsfsData',
            'saleOperate',
            'createOperate',
            'updateOperate'
        ])->where('companyid', $this->getCompanyId())
            ->where('fangxiang', $fangxiang)
            ->order('create_time', 'desc');
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('yw_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['create_operator_id'])) {
            $list->where('create_operator_id', $params['create_operator_id']);
        }
        if (!empty($params['status'])) {
            $list->where('status', $params['status']);
        }
        if (!empty($params['group_id'])) {
            $list->where('group_id', $params['group_id']);
        }
        if (!empty($params['customer_id'])) {
            $list->where('customer_id', $params['customer_id']);
        }
        if (!empty($params['system_number'])) {
            $list->where('system_number', 'like', '%' . $params['system_number'] . '%');
        }
        if (!empty($params['createTimeStart'])) {
            $list->where('create_time', '>=', $params['createTimeStart']);
        }
        if (!empty($params['createTimeEnd'])) {
            $list->where('create_time', '<=', date('Y-m-d', strtotime($params['createTimeEnd'] . ' +1 day')));
        }
        if (!empty($params['update_operator_id'])) {
            $list->where('update_operator_id', $params['update_operator_id']);
        }
        if (!empty($params['check_operator_id'])) {
            $list->where('check_operator_id', $params['check_operator_id']);
        }
        if (!empty($params['sale_operator_id'])) {
            $list->where('sale_operator_id', $params['sale_operator_id']);
        }
        if (!empty($params['jiesuan_id'])) {
            $list->where('jiesuan_id', $params['jiesuan_id']);
        }
        if (!empty($params['beizhu'])) {
            $list->where('beizhu', 'like', '%' . $params['beizhu'] . '%');
        }
        $list = $list->paginate($pageLimit);
        return returnRes(true, '', $list);
    }

    /**
     * 获取其他费用单详情
     * @param Request $request
     * @param int $id
     * @return Json
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function detail(Request $request, $id = 0)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $data = \app\admin\model\CapitalOther::with([
            'custom',
            'jsfsData',
            'saleOperate',
            'createOperate',
            'updateOperate',
            'details' => ['szmcData', 'szflData'],
        ])
            ->where('companyid', $this->getCompanyId())
            ->where('id', $id)
            ->find();
        return returnRes(true, '', $data);
    }

    /**
     * 其他款项作废
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
            $qt = \app\admin\model\CapitalOther::get($id);
            if (empty($qt)) {
                throw new Exception("对象不存在");
            }
            if ($qt->companyid != $this->getCompanyId()) {
                throw new Exception("对象不存在");
            }
            if ($qt['status'] == 2) {
                throw new Exception("该单据已经作废");
            }
            \app\admin\model\CapitalOther::ifHx($qt);
            $qt->status = 1;
            $qt->save();
            Db::commit();
            return returnSuc();
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }
}