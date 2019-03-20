<?php
namespace app\admin\controller;

use think\Loader;
use think\Session;
use think\Url;
use app\admin\library\traits\Tree;
/**
 * Class Login
 * 登录控制器
 */
class Purchase extends Base
{
    public function puchaseadd(){
        if(request()->isPost()){

        }else{
            $purchase_id=request()->param("purchase_id");
            $data['purchaselist']=model("view_purchaselist")->where(array("companyid"=>Session::get("uinfo", "admin")['companyid'],'id'=>$purchase_id))->find();
            $data['purchasedetails']=model("purchasedetails")->where(array("companyid"=>Session::get("uinfo", "admin")['companyid'],'id'=>$purchase_id))->select();
            $data["purchaseFee"]=model("purchase_fee")->where(array("companyid"=>Session::get("uinfo", "admin")['companyid'],'id'=>$purchase_id))->select();
            return returnRes($data, '没有数据，请添加后重试', $data);
        }
    }
    /**获取大类列表
     * @return \jsonRPCClient
     */
    public function getclassnamelist(){
        $list = db("classname")->field("pid,id,classname")->where("companyid", Session::get("uinfo", "admin")['companyid'])->select();
        $list = new Tree($list);
        $list = $list->leaf();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }

    /**采购单列表
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function getpurchaselist(){
        $list = model("view_purchaselist")->where("companyid", Session::get("uinfo", "admin")['companyid'])->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }
    public function purchaseaddinfo(){
        $companyid= Session::get("uinfo", "admin")['companyid'];
        //往来单位运营商
        $data["custom"] = model("custom")->where("companyid",$companyid)->field("id,custom")->select();
        //结算方式
        $data["jiesuanfangshi"] = model("jiesuanfangshi")->where("companyid", $companyid)->field("id,jiesuanfangshi")->select();
        //票据类型
        $data["pjlx"] = model("pjlx")->where("companyid", $companyid)->field("id,pjlx")->select();
        //库存列表
        $data["storage"] = model("storage")->where("companyid", $companyid)->field("id,storage")->select();
        //大类
        $data["classname"]=$this->getclassnamelist();
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
        $data["productlist"]=model("view_specification")->where("companyid", $companyid)->select();

        return returnRes($data,"没有相关数据",$data);
    }

    /**根据收支方向获取收支分类
     * @return \think\response\Json|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getpaymentclass(){
        $type=reqeust()->param("type");
        $paymentclass=model("paymentclass")->field("id,name")->where("type",$type)->select();
      return returnRes($paymentclass,"没有相关数据",$paymentclass);
    }

    /**根据收支分类获取收支名称
     * @return \think\response\Json|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getpaymenttype(){
        $class=reqeust()->param("paymentclass");
        $paymentclass=model("paymenttype")->field("id,name")->where("class",$class)->select();
       return returnRes($paymentclass,"没有相关数据",$paymentclass);
    }
    /**
     * 基础列表返回仓库下拉
     */
    public function getstorage(){
        $list = model("storage")->where("companyid", Session::get("uinfo", "admin")['companyid'])->field("id,storage")->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }
    /**
     * 基础列表 票据类型
     */
    public function getpjlx(){
        $list = model("pjlx")->where("companyid", Session::get("uinfo", "admin")['companyid'])->field("id,pjlx,tax_rate")->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }

    /**获取供应商
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getsupplier(){
        $list = model("custom")->where(array("companyid"=>Session::get("uinfo", "admin")['companyid'],"issupplier"=>1))->field("custom,id")->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }

    /**获取结算方式
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getjiesuanfangshi(){
        $list = model("jiesuanfangshi")->where(array("companyid"=>Session::get("uinfo", "admin")['companyid']))->field("id,jiesuanfangshi")->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }

    /**获取材质
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function gettexture(){
        $list = model("texture")->where(array("companyid"=>Session::get("uinfo", "admin")['companyid']))->field("id,texturename")->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }

    /**产地
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getoriginarea(){
        $list = model("originarea")->where(array("companyid"=>Session::get("uinfo", "admin")['companyid']))->field("id,originarea")->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }

    /**品名下拉获取
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getproductname(){
        $list = model("productname")->where(array("companyid"=>Session::get("uinfo", "admin")['companyid']))->field("id,name")->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }
}