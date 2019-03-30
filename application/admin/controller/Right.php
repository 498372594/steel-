<?php
namespace app\admin\controller;

/**
 * main区域需要一个模板布局
 * Class Right
 * @package app\admin\controller
 */
class Right extends Signin
{
    public function __construct()
    {
        parent::__construct();
        $this->view->engine->layout('common/layout');
    }

    /**搜索条件
     * @param $params
     * @param $list
     * @return mixed
     */
    public function getsearch($params,$list){
        //系统单号
        if (!empty($params['system_no'])) {
            $list->where('system_no', $params['system_no']);
        }
        //业务时间
        if (!empty($params['ywsjStart'])) {
            $list->where('service_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('service_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['service_start'])) {
            $list->where('service_time', '>=', $params['service_start']);
        }
        if (!empty($params['service_end'])) {
            $list->where('service_time', '<=', date('Y-m-d', strtotime($params['service_end'] . ' +1 day')));
        }
        //系统单号
        if (!empty($params['zyh'])) {
            $list->where('zyh', $params['zyh']);
        }
        //大类
        if (!empty($params['classname'])) {
            $list->where('classname', $params['classname']);
        }
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
        //规格
        if (!empty($params['supplier_id'])) {
            $list->where('supplier_id', $params['supplier_id']);
        }
        //运输单位
        if (!empty($params['ysdw_id'])) {
            $list->where('ysdw_id', $params['ysdw_id']);
        }
        //收货单位
        if (!empty($params['shdw_id'])) {
            $list->where('shdw_id', $params['shdw_id']);
        }
        //备注
        if (!empty($params['remark'])) {
            $list->where('remark', 'like', '%' . $params['remark'] . '%');
        }
        //部门
        if (!empty($params['department'])) {
            $list->where('department', $params['department']);
        }
        //部门
        if (!empty($params['employer'])) {
            $list->where('employer', $params['employer']);
        }
        return $list;
    }

}
