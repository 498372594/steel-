<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use think\Db;
use think\Exception;
use think\Session;

class Reportform extends Right
{
    /**
     * 采购单列表admin/purchase/getpurchaselist
     */

    /**
     * 采购单明细admin/purchase/getpurchaselist
     */
    public function getpurchasedetails( $pageLimit = 10){
        $params = request()->param();
        $list = \app\admin\model\Purchasedetails::where('companyid', Session::get("uinfo", "admin")['companyid']);

        if (!empty($params['ywsjStart'])) {
            $list->where('service_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('service_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['storage_id'])) {
            $list->where('storage_id', $params['storage_id']);
        }
        if (!empty($params['status'])) {
            $list->where('status', $params['status']);
        }
        if (!empty($params['rkfs'])) {
            $list->where('rkfs', $params['rkfs']);
        }
        if (!empty($params['jsfs'])) {
            $list->where('jsfs', $params['jsfs']);
        }
        if (!empty($params['supplier_id'])) {
            $list->where('supplier_id', $params['supplier_id']);
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
        if (!empty($params['shdw_id'])) {
            $list->where('shdw_id', $params['shdw_id']);
        }
        if (!empty($params['ysdw_id'])) {
            $list->where('ysdw_id', $params['ysdw_id']);
        }
        if (!empty($params['remark'])) {
            $list->where('remark', $params['remark']);
        }
        $list = $list->paginate($pageLimit);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }
    /**
     * 采购磅差统计
     */
    public function getbangcha($pageLimit=10){
        $params = request()->param();
        $list = \app\admin\model\ViewPurchasedetails::where('companyid', Session::get("uinfo", "admin")['companyid']);
        $list=$this->getsearch($params,$list);
        $list = $list->paginate($pageLimit);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**入库成本明细表
     * @param int $pageLimit
     * @return \think\response\Json
     */
    public function rkcbmx($pageLimit=10){
        $params = request()->param();
        $list = \app\admin\model\ViewInstorageOrder::where(array("companyid"=>Session::get("uinfo", "admin")['companyid'],"in_out"=>1));
        $list=$this->getsearch($params,$list);
        $list = $list->paginate($pageLimit);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }
    /**出库成本明细表
     * @param int $pageLimit
     * @return \think\response\Json
     */
    public function ckcbmx($pageLimit=10){
        $params = request()->param();
        $list = \app\admin\model\ViewInstorageOrder::where(array("companyid"=>Session::get("uinfo", "admin")['companyid'],"in_out"=>2));
        $list=$this->getsearch($params,$list);
        $list = $list->paginate($pageLimit);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }
    /**入库单历史记录
     * @param int $pageLimit
     * @return \think\response\Json
     */
    public function getinstoragelist($pageLimit=10){
        $params = request()->param();
        $list = \app\admin\model\ViewInstorageOrder::where(array("companyid"=>Session::get("uinfo", "admin")['companyid'],"in_out"=>2));
        $list=$this->getsearch($params,$list);
        $list = $list->paginate($pageLimit);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }
}