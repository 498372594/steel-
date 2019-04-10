<?php

namespace app\admin\controller;

use app\admin\model\CapitalFy;
use app\admin\model\CapitalFyhx;
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    Request,
    response\Json};

class Feiyong extends Signin
{
    /**
     * 添加费用单
     * @param Request $request
     * @param array $data
     * @param int $count
     * @param bool $return
     * @param bool $useTrans
     * @return array|bool|string|Json
     * @throws \think\Exception
     */
    public function add(Request $request, $data = [], $count = 0, $return = false, $useTrans = true)
    {
        $companyid = $this->getCompanyId();
        if (empty($count)) {
            $count = CapitalFy::whereTime('create_time', 'today')->where('companyid', $companyid)->count() + 1;
        }

        if (empty($data)) {
            $data = $request->post();
        }
        $data['companyid'] = $companyid;
        $data['system_number'] = 'FYD' . date('Ymd') . str_pad($count, 3, 0, STR_PAD_LEFT);
        $data['create_operator_id'] = $this->getAccountId();
        $data['fymx_create_type'] = $return ? 1 : 2;
        $validate = new \app\admin\validate\CapitalFy();
        if (!$validate->check($data)) {
            if ($return) {
                return $validate->getError();
            } else {
                return returnFail($validate->getError());
            }
        }
        if ($useTrans) {
            Db::startTrans();
        }
        try {
            $model = new CapitalFy();
            $model->allowField(true)->data($data)->save();
            $id = $model->getLastInsID();
            foreach ($data['details'] as $c => $v) {
                $data['details'][$c]['companyid'] = $companyid;
                $data['details'][$c]['cap_fy_id'] = $id;
            }
            (new CapitalFyhx())->allowField(true)->saveAll($data['details']);

            if ($useTrans) {
                Db::commit();
            }
            if ($return) {
                return true;
            }
            return returnSuc();
        } catch (Exception $e) {
            if ($useTrans) {
                Db::rollback();
            }
            if ($return) {
                return $e->getMessage();
            }
            return returnFail($e->getMessage());
        }
    }

    /**
     * 添加多条费用单
     * @param array $data 费用单数据
     * $data = [
     *     'customer_id' => '对方单位',
     *     'beizhu' => '备注',
     *     'group_id' => '部门',
     *     'sale_operator_id' => '职员',
     *     'fang_xiang' => '方向，1-应收，2-应付',
     *     'shouzhifenlei_id' => '收支分类',
     *     'shouzhimingcheng_id' => '收支名称',
     *     'danjia' => '单价',
     *     'money' => '金额',
     *     'zhongliang' => '重量',
     *     'piaoju_id' => '票据类型',
     *     'price_and_tax' => '价税合计',
     *     'tax_rate' => '税率',
     *     'tax' => '税额'
     * ];
     * @param int $type 单据类型，1-销售单,2-采购单
     * @param int $data_id 关联数据id
     * @param string $yw_time 业务时间
     * @param bool $useTrans 是否使用事务
     * @return bool|string
     * @throws \think\Exception
     */
    public function addAll($data = [], $type = 0, $data_id = 0, $yw_time = '', $useTrans = true)
    {
        $request = Request::instance();
        $companyid = $this->getCompanyId();
        $count = CapitalFy::whereTime('create_time', 'today')->where('companyid', $companyid)->count();
        if ($useTrans) {
            Db::startTrans();
        }
        try {
            foreach ($data as $item) {
                //处理核销数据
                $item['details'] = [[
                    'fyhx_type' => $type,
                    'data_id' => $data_id,
                    'cache_yw_time' => $yw_time,
                    'hx_money' => $item['money'] ?? 0,
                    'heji_zhongliang' => $item['zhongliang'] ?? 0,
                    'customer_id' => $item['customer_id']
                ]];
                $item['yw_time'] = $yw_time;

                //添加费用单
                $res = $this->add($request, $item, ++$count, true, false);
                if ($res !== true) {
                    throw new Exception($res);
                }
            }
            if ($useTrans) {
                Db::commit();
            }
            return true;
        } catch (Exception $e) {
            if ($useTrans) {
                Db::rollback();
            }
            return $e->getMessage();
        }
    }

    /**
     * 获取费用单列表
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
        $list = CapitalFy::with([
            'custom',
            'pjlxData',
            'szmcData',
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
        if (!empty($params['group_id'])) {
            $list->where('group_id', $params['group_id']);
        }
        if (!empty($params['sale_operator_id'])) {
            $list->where('sale_operator_id', $params['sale_operator_id']);
        }
        if (!empty($params['beizhu'])) {
            $list->where('beizhu', 'like', '%' . $params['beizhu'] . '%');
        }
        $list = $list->paginate($pageLimit);
        return returnRes(true, '', $list);

    }

    /**
     * 获取费用单详情
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
        $data = CapitalFy::with([
            'custom',
            'pjlxData',
            'szmcData',
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
     * 审核
     * @param Request $request
     * @param int $id
     * @return Json
     * @throws DbException
     */
    public function audit(Request $request, $id = 0)
    {
        if (!$request->isPut()) {
            return returnFail('请求方式错误');
        }
        $capitalFy = CapitalFy::where('id', $id)
            ->where('companyid', $this->getCompanyId())
            ->find();
        if (empty($capitalFy)) {
            return returnFail('数据不存在');
        }
        if ($capitalFy->status == 3) {
            return returnFail('此单已审核');
        }
        if ($capitalFy->status == 2) {
            return returnFail('此单已作废');
        }
        $capitalFy->status = 3;
        $capitalFy->check_operator_id = $this->getAccountId();
        $capitalFy->save();
        return returnSuc();
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
            return returnFail('请求方式错误');

        }
        $capitalFy = CapitalFy::where('id', $id)
            ->where('companyid', $this->getCompanyId())
            ->find();
        if (empty($capitalFy)) {
            return returnFail('数据不存在或已作废');
        }
        if ($capitalFy->status == 1) {
            return returnFail('此单未审核');
        }
        if ($capitalFy->status == 2) {
            return returnFail('此单已作废');
        }
        $capitalFy->status = 1;
        $capitalFy->check_operator_id = null;
        $capitalFy->save();
        return returnSuc();
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
            $salesorder = CapitalFy::where('id', $id)
                ->where('companyid', $this->getCompanyId())
                ->find();
            if (empty($salesorder)) {
                return returnFail('数据不存在');
            }
            if ($salesorder->fymx_create_type != 2) {
                return returnFail('业务生成费用单，请操作原单');
            }
            if ($salesorder->status == 3) {
                return returnFail('此单已审核，禁止作废');
            }
            if ($salesorder->status == 2) {
                return returnFail('此单已作废');
            }
            $salesorder->status = 2;
            $salesorder->save();
            (new Chuku())->cancel($request, $id, false);
            return returnSuc();
        }
        return returnFail('请求方式错误');
    }
}