<?php


namespace app\admin\controller;


use app\admin\validate\CapitalOtherDetails;
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
            'jsfsData'
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
            'details' => ['szmcData'],
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
     * 添加其他费用单
     * @param Request $request
     * @return Json
     * @throws \think\Exception
     */
    public function add(Request $request)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }
        $data = $request->post();
        $companyid = $this->getCompanyId();
        $count = \app\admin\model\CapitalOther::whereTime('create_time', 'today')
                ->where('fangxiang', $data['fangxiang'])
                ->where('companyid', $companyid)->count() + 1;

        if ($data['fangxiang'] == 1) {
            $systemNumber = 'QTYSK' . date('Ymd') . str_pad($count, 3, 0, STR_PAD_LEFT);
        } elseif ($data['fangxiang'] == 2) {
            $systemNumber = 'QTYFK' . date('Ymd') . str_pad($count, 3, 0, STR_PAD_LEFT);
        } else {
            return returnFail('收付方向错误');
        }

        $data['companyid'] = $companyid;
        $data['system_number'] = $systemNumber;
        $data['create_operator_id'] = $this->getAccountId();
        $validate = new \app\admin\validate\CapitalOther();
        if (!$validate->check($data)) {
            return returnFail($validate->getError());
        }
        Db::startTrans();
        try {
            $model = new \app\admin\model\CapitalOther();
            $model->allowField(true)->data($data)->save();

            $num = 1;
            $detailsValidate = new CapitalOtherDetails();
            $money = 0;
            foreach ($data['details'] as $c => $v) {
                $data['details'][$c]['companyid'] = $companyid;
                $data['details'][$c]['cap_qt_id'] = $model->id;
                if (!$detailsValidate->check($data['details'][$c])) {
                    throw new Exception('请检查第' . $num . '行' . $detailsValidate->getError());
                }
                $num++;
                $money += $data['details'][$c]['money'];
            }
            (new \app\admin\model\CapitalOtherDetails())->allowField(true)->saveAll($data['details']);
            $model->money = $money;
            $model->save();

            Db::commit();
            return returnSuc();
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

}