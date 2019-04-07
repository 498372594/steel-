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
    public function getrkmx($id=0){
        $data = \app\admin\model\KcRk::with([
            'custom',
            'details' => ['specification', 'jsfs', 'storage','pinmingData','caizhiData','chandiData'],
        ]) ->where('companyid', Session::get('uinfo.companyid', 'admin'))
            ->where('id', $id)
            ->find();
        if (empty($data)) {
            return returnFail('数据不存在');
        } else {
            return returnRes(true, '', $data);
        }
    }
    public function getrktz(){
        $params = request()->param();
        $list = \app\admin\model\KcRkTz::where('companyid', Session::get("uinfo", "admin")['companyid']);
        $list->where("jianshu",">",0)->where("lingzhi",">",0)->where("counts",">",0)->where("zhongliang",">",0);
        if (!empty($params['beizhu'])) {
            $list->where('beizhu', 'like', '%' . $params['beizhu'] . '%');
        }
        $list=$list->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }
}