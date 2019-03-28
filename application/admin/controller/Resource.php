<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use think\Db;
use think\Exception;
use think\Session;

class Resource extends Right
{
    /**现货报价查询列表
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function  xhbj(){
        $params = request()->param();
        $list = \app\admin\model\ViewInstorageDetails::where('companyid', Session::get("uinfo", "admin")['companyid']);
//        if (!empty($params['ywsjStart'])) {
//            $list->where('service_time', '>=', $params['ywsjStart']);
//        }
//        if (!empty($params['ywsjEnd'])) {
//            $list->where('service_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
//        }
        //仓库
        if (!empty($params['storage_id'])) {
            $list->where('storage_id', $params['storage_id']);
        }
        //品名
        if (!empty($params['productname'])) {
            $list->where('productname', 'like', '%' . $params['productname'] . '%');
        }
        //规格
        if (!empty($params['specification'])) {
            $list->where('specification', $params['specification']);
        }
        //厚度
        if (!empty($params['houdu_start'])) {
            $list->where('houdu_name', '>=', $params['houdu_start']);
        }
        if (!empty($params['houdu_end'])) {
            $list->where('houdu_name', '<=',$params['houdu_end']);
        }
        //宽度
        if (!empty($params['width_start'])) {
            $list->where('width', '>=', $params['width_start']);
        }
        if (!empty($params['width_end'])) {
            $list->where('width', '<=',$params['width_end']);
        }
        //长度
        if (!empty($params['length_start'])) {
            $list->where('length', '>=', $params['length_start']);
        }
        if (!empty($params['length_end'])) {
            $list->where('length', '<=',$params['length_end']);
        }
        //材质
        if (!empty($params['texture'])) {
            $list->where('texture', $params['texture']);
        }
        //规格
        if (!empty($params['originarea'])) {
            $list->where('originarea', $params['originarea']);
        }
//        if (!empty($params['remark'])) {
//            $list->where('remark', 'like', '% ' . $params['remark'] . '%');
//        }
        $list=$list->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }
    /**现货汇总查询
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function xhhz(){
        $params = request()->param();
        $list = \app\admin\model\ViewInstorageDetails::where('companyid', Session::get("uinfo", "admin")['companyid']);
        //仓库
        if (!empty($params['storage_id'])) {
            $list->where('storage_id', $params['storage_id']);
        }
        //品名
        if (!empty($params['productname'])) {
            $list->where('productname', 'like', '%' . $params['productname'] . '%');$this->getAccountId()
        }
        //规格
        if (!empty($params['specification'])) {
            $list->where('specification', $params['specification']);
        }
        //厚度
        if (!empty($params['houdu_start'])) {
            $list->where('houdu_name', '>=', $params['houdu_start']);
        }
        if (!empty($params['houdu_end'])) {
            $list->where('houdu_name', '<=',$params['houdu_end']);
        }
        //宽度
        if (!empty($params['width_start'])) {
            $list->where('width', '>=', $params['width_start']);
        }
        if (!empty($params['width_end'])) {
            $list->where('width', '<=',$params['width_end']);
        }
        //长度
        if (!empty($params['length_start'])) {
            $list->where('length', '>=', $params['length_start']);
        }
        if (!empty($params['length_end'])) {
            $list->where('length', '<=',$params['length_end']);
        }
        //材质
        if (!empty($params['texture'])) {
            $list->where('texture', $params['texture']);
        }
        //规格
        if (!empty($params['originarea'])) {
            $list->where('originarea', $params['originarea']);
        }

        $list=$list->field("storage,classname,productname,specification,texture,originarea,houdu_name,width,length,jianshu,sum(jianshu) as total_jianshu,sum(lingzhi) as total_lingzhi,sum(shuliang) as total_shuliang,sum(heavy) as total_heavy,sum(lisuanzongzhong) as total_lisuanzongzhong")
            ->group("storage_id,classid,productname,specification,width,length,houdu_name")
            ->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }
}