<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use think\Db;
use think\Exception;
use think\Session;

class Initinput extends Right
{
    use Backend;
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

}