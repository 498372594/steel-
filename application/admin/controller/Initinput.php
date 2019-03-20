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
            $data["rukdh"]="RKD".time();
            $data["status"]=1;
            $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
            $data["clerk"]=request()->post("clerk");
            $data["department"]=request()->post("department");
            $data['add_name'] = Session::get("uinfo", "admin")['name'];
            $data['add_id'] = Session::get("uid", "admin");
            $KC="KC".time();
            $re=model("instoragelist")->save($data);

            $purchasedetails=request()->post("purchasedetails");
            $instorage_id=model("instoragelist")->id;
            foreach ($purchasedetails as $key=>$value){
                $purchasedetails["$key"]["instorage_id"]=$instorage_id;
                $purchasedetails["$key"]["zyh"]="ZYH".time();
            }
            $res =model("purchasedetails")->savaAll($purchasedetails);
//            $res =model("purchasedetails")->where("id","in","ids")->update(array("is_finished"=>2,"instorage_id"=>$instorage_id));
            return returnRes($res,'修改失败');
        }
    }
}