<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/18
 * Time: 11:26
 */

namespace app\admin\controller;


use app\admin\validate\SalesorderDetails;
use app\admin\validate\SalesorderOther;
use think\Db;
use think\Request;
use think\Session;

class Salesorder extends Base
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
        $list = \app\admin\model\Salesorder::where('companyid', Session::get('uinfo.companyid', 'admin'));
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
        if (!empty($params['remark'])) {
            $list->where('remark', 'like', '%' . $params['remark'] . '%');
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
            ->where('companyid', Session::get('uinfo.companyid', 'admin'))
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
     * @param int $ywlx
     * @param array $data
     * @param bool $return
     * @return bool|string|\think\response\Json|array
     * @throws \think\Exception
     */
    public function add(Request $request, $ywlx = 1, $data = [], $return = false)
    {
        if ($request->isPost()) {
            $count = \app\admin\model\Salesorder::whereTime('create_time', 'today')->count();
            $companyId = Session::get('uinfo.companyid', 'admin');

            //数据处理
            if (empty($data)) {
                $data = $request->post();
            }
            $data['add_name'] = Session::get("uinfo.name", "admin");
            $data['add_id'] = Session::get("uid", "admin");
            $data['companyid'] = $companyId;
            $data['system_no'] = 'XSD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            $data['ywlx'] = $ywlx;

            //数据验证
            $validate = new \app\admin\validate\Salesorder();
            if (!$validate->check($data)) {
                if ($return) {
                    return $validate->getError();
                } else {
                    return returnFail($validate->getError());
                }
            }

            if (!$return) {
                Db::startTrans();
            }
            try {
                $model = new \app\admin\model\Salesorder();
                $model->allowField(true)->data($data)->save();

                //处理明细
                $id = $model->getLastInsID();
                $num = 1;
                $detailsValidate = new SalesorderDetails();
                foreach ($data['details'] as $c => $v) {
                    $data['details'][$c]['companyid'] = $companyId;
                    $data['details'][$c]['order_id'] = $id;
                    if (!$detailsValidate->check($data['details'][$c])) {
                        throw new \Exception('请检查第' . $num . '行' . $detailsValidate->getError());
                    }
                    $num++;
                }
                Db::name('SalesorderDetails')->insertAll($data['details']);

                $num = 1;
                $otherValidate = new SalesorderOther();
                $nowDate = date('Y-m-d H:i:s');
                if (!empty($data['other'])) {
                    //处理其他费用
                    foreach ($data['other'] as $c => $v) {
                        $data['other'][$c]['order_id'] = $id;
                        $data['other'][$c]['date'] = $nowDate;
                        if (!$otherValidate->check($data['other'][$c])) {
                            throw new \Exception('请检查第' . $num . '行' . $otherValidate->getError());
                        }
                        $num++;
                    }
                    Db::name('SalesorderOther')->insertAll($data['other']);
                }
                if (!$return) {
                    Db::commit();
                    return returnRes(true, '', ['id' => $id]);
                } else {
                    return true;
                }
            } catch (\Exception $e) {
                if ($return) {
                    return $e->getMessage();
                } else {
                    Db::rollback();
                    return returnFail($e->getMessage());
                }
            }
        }
        if ($return) {
            return '请求方式错误';
        } else {
            return returnFail('请求方式错误');
        }
    }

    /**
     * 审核
     * @param Request $request
     * @param int $id
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function audit(Request $request, $id = 0)
    {
        if ($request->isPut()) {
            $salesorder = \app\admin\model\Salesorder::get($id);
            if (empty($salesorder) || $salesorder->status == 2) {
                return returnFail('数据不存在或已作废');
            }
            $salesorder->status = 3;
            $salesorder->auditer = Session::get('uid', 'admin');
            $salesorder->save();
            return returnSuc();
        }
        return returnFail('请求方式错误');
    }

    /**
     * 反审核
     * @param Request $request
     * @param int $id
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function unAudit(Request $request, $id = 0)
    {
        if ($request->isPut()) {
            $salesorder = \app\admin\model\Salesorder::get($id);
            if (empty($salesorder) || $salesorder->status == 2) {
                return returnFail('数据不存在或已作废');
            }
            $salesorder->status = 1;
            $salesorder->auditer = null;
            $salesorder->save();
            return returnSuc();
        }
        return returnFail('请求方式错误');
    }

    /**
     * 作废
     * @param Request $request
     * @param int $id
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function cancel(Request $request, $id = 0)
    {
        if ($request->isPost()) {
            $salesorder = \app\admin\model\Salesorder::get($id);
            $salesorder->status = 2;
            $salesorder->save();
            return returnSuc();
        }
        return returnFail('请求方式错误');
    }
}