<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use think\Db;
use think\Exception;
use think\Session;

class Rk extends Right
{
    /**入库单列表
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function getrk(){
        $params = request()->param();
        $list = \app\admin\model\KcRk::with([
            'custom',
        ]) ->where('companyid', Session::get("uinfo", "admin")['companyid']);
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('yw_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($instorageorderparams['status'])) {
            $list->where('status', $params['status']);
        }
        if (!empty($params['system_number'])) {
            $list->where('system_number', 'like', '%' . $params['system_number'] . '%');
        }
        if (!empty($params['beizhu'])) {
            $list->where('beizhu', 'like', '%' . $params['beizhu'] . '%');
        }
        $list=$list->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }
    /**入库单明细
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function instorageorder(){
        $instorage_id=request()->param("id");
        $list = model("ViewInstorageOrder")->where("instorage_id",$instorage_id)->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }
}