<?php

namespace app\admin\controller;

use app\admin\model\KucunCktz;
use think\{Db, exception\DbException, Request, response\Json, Session};

class Chuku extends Right
{
    /**
     * 添加出库通知单
     * @param array $data
     */
    public function addNotify($data = [])
    {
        if (empty($data)) {
            return;
        }
        $now = time();  
        foreach ($data as $index => $item) {
            $data[$index]['create_time'] = $now;
            $data[$index]['update_time'] = $now;
        }
        Db::name('KucunCktz')->insertAll($data);
    }

    /**
     * 获取出库通知单
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function getNotifyList(Request $request, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $params = $request->param();
        $list = KucunCktz::with([
            'adder',
            'custom',
            'jsfs',
            'specification',
            'storage',
        ])->where('companyid', Session::get('uinfo.companyid', 'admin'));
        if (!empty($params['ywsjStart'])) {
            $list->where('cache_ywtime', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('cache_ywtime', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['system_no'])) {
            $list->where('cache_data_pnumber', 'like', '%' . $params['system_no'] . '%');
        }
        if (!empty($params['custom_id'])) {
            $list->where('cache_customer_id', $params['custom_id']);
        }
        if (!empty($params['add_id'])) {
            $list->where('cache_create_operator', $params['add_id']);
        }
        if (!empty($params['is_done'])) {
            $list->where('is_done', $params['id_done'] - 1);
        }
        if (!empty($params['weight_gt_0'])) {
            $list->where('zhongliang', '>', 0);
        }
        $list = $list->paginate($pageLimit);
        return returnRes(true, '', $list);
    }

    /**
     * 出库通知，完成
     * @param Request $request
     * @param $id
     * @return Json
     * @throws DbException
     */
    public function doneNotify(Request $request, $id = 0)
    {
        if (!$request->isPut()) {
            return returnFail('请求方式错误');
        }
        $data = KucunCktz::get($id);
        if (empty($data)) {
            return returnFail('数据不存在');
        }
        if ($data->is_done == 1) {
            return returnFail('该记录已完成');
        }
        $data->is_done = 1;
        $data->save();
        return returnSuc();
    }

}