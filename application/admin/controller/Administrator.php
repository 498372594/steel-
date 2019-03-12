<?php

namespace app\admin\controller;

use think\Db;
use think\Exception;

class Administrator extends Right
{
    public function getAuthList()
    {
       $uid=request()->param("uid");
       if(!$uid){
           return json(array("code"=>-1,"msg"=>"参数错误"));
       }
       $data=db("admin")->where("id",$uid)->field("id,rolepath")->find();
       if($data){
           return json(array("code"=>0,"msg"=>"成功","data"=>$data));
       }else{
           return json(array("code"=>-1,"msg"=>"失败"));
       }
    }
    public function saveauth(){
        if(request()->ispost()){
            $uid=request()->post("uid");
            $auth=request()->post("auth");
            $re=db("admin")->where("id".$uid)->update(array("rolepath"=>$auth));
            if($re!==false){
                return json(array("code"=>0,"msg"=>"成功"));
            }else{
                return json(array("code"=>-1,"msg"=>"失败"));
            }
        }else{
            return json(array("code"=>-1,"msg"=>"参数错误"));
        }

    }
}