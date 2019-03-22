<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/18
 * Time: 11:26
 */

namespace app\admin\controller;


use think\Db;
use think\Request;
use think\Session;

class Salesorder extends Right
{
    /**
     * 获取销售单列表
     * @param Request $request
     * @param int $pageLimit
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function getlist(Request $request, $pageLimit = 10)
    {
        $params = $request->param();
        $list = \app\admin\model\Salesorder::where('companyid', Session::get('uinfo.companyid'));
        if (!empty($params['ywsjStart'])) {
            $list->where('ywsj', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('ywsj', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['status'])) {
            $list->where('status', $params['status']);
        }
        if (!empty($params['custom_id'])) {
            $list->where('custom_id', $params['custom_id']);
        }
        if (!empty($params['employer'])) {
            $list->where('employer', $params['employer']);
        }
        if (!empty($params['pjlx'])) {
            $list->where('pjlx', $params['pjlx']);
        }
        if (!empty($params['system_no'])) {
            $list->where('system_no', 'like', '%' . $params['system_no'] . '%');
        }
        if (!empty($params['ywlx'])) {
            $list->where('ywlx', $params['ywlx']);
        }
        $list = $list->paginate($pageLimit);
        return returnRes(true, '', $list);
    }

    /**
     * 获取销售单详情
     * @param int $id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail($id = 0)
    {
        $data = \app\admin\model\Salesorder::with(['details', 'other'])
            ->where('companyid', Session::get('uinfo.companyid'))
            ->where('id', $id)
            ->find();
        if (empty($data)) {
            return returnFail('数据不存在');
        } else {
            return returnRes(true, '', $data);
        }
    }

    /**
     * 添加销售单
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\Exception
     */
    public function add(Request $request)
    {
        if ($request->isPost()) {
            $count = \app\admin\model\Salesorder::whereTime('create_time', 'today')->count();
            $companyId = Session::get('uinfo.companyid', 'admin');
            $data = $request->post();
            $data['add_name'] = Session::get("uinfo.name", "admin");
            $data['add_id'] = Session::get("uid", "admin");
            $data['companyid'] = $companyId;
            $data['system_no'] = 'XSD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            $data['ywlx'] = 1;
            $model = new \app\admin\model\Salesorder();
            $model->allowField(true)->data($data)->save();
            $id = $model->getLastInsID();
            foreach ($data['details'] as $c => $v) {
                $data['details'][$c]['companyid'] = $companyId;
                $data['details'][$c]['order_id'] = $id;
            }
            Db::name('SalesorderDetails')->insertAll($data['details']);
            foreach ($data['other'] as $c => $v) {
                $data['other'][$c]['order_id'] = $id;
            }
            Db::name('SalesorderOther')->insertAll($data['details']);
            return returnRes(true, '', ['id' => $id]);
        }
        return returnFail('请求错误');
    }

    /**
     * 审核、反审核
     * @param int $id
     * @param int $status
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function audit($id = 0, $status = 3)
    {
        $salesorder = \app\admin\model\Salesorder::get($id);
        $salesorder->status = $status;
        $salesorder->save();
        return returnSuc();
    }
}