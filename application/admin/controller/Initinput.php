<?php

namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Exception;
use think\Session;

class Initinput extends Right
{
    public function instorageinit(){
        if(request()->isPost()){
            $ids = request()->param("id");
            $count = \app\admin\model\Instoragelist::whereTime('create_time', 'today')->count();
            $data["rkdh"]="RKD".date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            $data["status"]=1;
            $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
            $data["clerk"]=request()->post("clerk");
            $data["department"]=request()->post("department");
            $data['add_name'] = Session::get("uinfo", "admin")['name'];
            $data['add_id'] = Session::get("uid", "admin");
            $data['service_time'] = date("Y-m-d H:s:i",time());
            $data['remark'] = request()->post("remark");
            $data['remark'] = request()->post("remark");
//            $KC="KC".time();
            $re=model("instoragelist")->save($data);
            $purchasedetails=request()->post("purchasedetails");
            $instorage_id=model("instoragelist")->id;
            foreach ($purchasedetails as $key=>$value){
                $purchasedetails["$key"]["instorage_id"]=$instorage_id;

                $count = \app\admin\model\Purchasedetails::whereTime('create_time', 'today')->count();
                $purchasedetails["$key"]["zyh"]="ZYH".date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            }
            $res =model("purchasedetails")->savaAll($purchasedetails);
//            $res =model("purchasedetails")->where("id","in","ids")->update(array("is_finished"=>2,"instorage_id"=>$instorage_id));
            return returnRes($res,'失败');
        }
    }
//    /**批量操作入库
//     * @return \think\response\Json
//     */
//    public function instorage(){
//        if(request()->isPost()){
//            $ids = request()->param("id");
//            $data["rukdh"]="RKD".time();
//            $data["status"]=1;
//            $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
//            $data["clerk"]=request()->post("clerk");
//            $data["department"]=request()->post("department");
//            $data['add_name'] = Session::get("uinfo", "admin")['name'];
//            $data['add_id'] = Session::get("uid", "admin");
//            $KC="KC".time();
//            $re=model("instoragelist")->save($data);
//            $res =model("purchasedetails")->where("id","in",$ids)->update(array("is_finished"=>2,"instorage_id"=>model("instoragelist")->id,"instorage_time"=>date("Y-m-d h:s:i",time())));
//            return returnRes($res,'修改失败');
//        }
//    }
    public function getinitsearch($params,$list){
        //系统单号
        if (!empty($params['system_number'])) {
            $list->where('system_number', $params['system_number']);
        }
        //业务时间
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('yw_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        //制单时间
        if (!empty($params['create_time_start'])) {
            $list->where('create_time', '>=', $params['create_time_start']);
        }
        if (!empty($params['create_time_end'])) {
            $list->where('create_time', '<=', date('Y-m-d', strtotime($params['create_time_end'] . ' +1 day')));
        }
        //制单人
        if (!empty($params['create_operator_id'])) {
            $list->where('create_operator_id', $params['create_operator_id']);
        }
        //修改人
        if (!empty($params['update_operator_id'])) {
            $list->where('update_operator_id', $params['update_operator_id']);
        }
        //修改人
        if (!empty($params['update_operator_id'])) {
            $list->where('update_operator_id', $params['update_operator_id']);
        }
        //状态
        if (!empty($params['status'])) {
            $list->where('status', $params['status']);
        }
        //部门
        if (!empty($params['group_id'])) {
            $list->where('group_id',$params['group_id']);
        }
        return $list;
    }
    public function initbank(){
        $params=request()->param();
        $list= $list = \app\admin\model\InitBank::where('companyid', Session::get('uinfo.companyid', 'admin'));
        $list=$this->getinitsearch($params,$list);
        $list = $list->paginate(10);
        return returnRes(true, '', $list);
    }

}