<?php
namespace app\admin\controller;

use think\Loader;
use think\Session;
use think\Url;

/**
 * Class Login
 * 登录控制器
 */
class Purchase extends Base
{
    public function puchaseadd(){
        if(request()->ispost()){

        }else{
            $data=[];

            $companyid= Session::get("uinfo", "admin")['companyid'];
            //往来单位运营商
            $data["custom"] = model("custom")->where("companyid",$companyid)->field("id,custom")->select();
            //结算方式
            $data["jiesuanfangshi"] = model("jiesuanfangshi")->where("companyid", $companyid)->field("id,jiesuanfangshi")->select();
            //票据类型
            $data["pjlx"] = model("pjlx")->where("companyid", $companyid)->field("id,pjlx")->select();
            //库存列表
            $data["storage"] = model("storage")->where("companyid", $companyid)->field("id,storage")->select();
            //产品列表
            $data["product"] = model("product")->where("companyid", $companyid)->select();
            //材质
            $data["texture"] = model("texture")->where("companyid", $companyid)->field("id,texturename")->select();
            //产地
            $data["originarea"] = model("originarea")->where("companyid", $companyid)->field("id,originarea")->select();
            //计算方式
            $data["jsfs"] = model("jsfs")->where("companyid", $companyid)->field("id,jsfs")->select();
            //收入类型
            $data["sr_paymenttype"] = model("paymenttype")->where(array("companyid"=>$companyid,"type"=>1))->field("id,name")->select();
            //支出类型
            $data["zc_paymenttype"] = model("paymenttype")->where(array("companyid"=>$companyid,"type"=>2))->field("id,name")->select();
            //计算方式
            $data["jsfs"] = model("jsfs")->where("companyid", $companyid)->field("id,jsfs")->select();
            return returnRes($data,"没有相关数据",$data);
        }
    }
}