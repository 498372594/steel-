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
    /**采购单添加
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function purchaseadd(){
        if(request()->isPost()){
            $count = \app\admin\model\Purchaselist::whereTime('create_time', 'today')->count();
            $companyId =  Session::get("uinfo", "admin")['companyid'];
            $data = request()->post();
            $data['add_name'] =  Session::get("uinfo", "admin")['name'];
            $data['add_id'] =  Session::get("uinfo", "admin")['uid'];
            $data['companyid'] = $companyId;
            $data['system_no'] = 'CGD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            $data['ywlx'] = 1;
            model("purchaselist")->allowField(true)->data($data)->save();
            $id = model("purchaselist")->id;

            foreach ($data['details'] as $c => $v) {
                $data['details'][$c]['companyid'] = $companyId;
                $data['details'][$c]['purchase_id'] = $id;
                $data['details'][$c]['supplier_id'] =  $data['supplier_id'];
                //未入库
               if($data["rkfs"]==1){
                   $data['details'][$c]['is_finished'] = 1;
               }
               //自动入库
                if($data["rkfs"]==2){
                    $data['details'][$c]['is_finished'] = 2;
                }
            }
            //自动入库
            if($data["rkfs"]==2){
                $count = \app\admin\model\Instoragelist::whereTime('create_time', 'today')->count();
                $dat['add_name'] =  Session::get("uinfo", "admin")['name'];
                $dat['add_id'] =  Session::get("uinfo", "admin")['uid'];
                $dat['companyid'] = $companyId;
                $dat['status'] = 1;
                $dat['type'] = 1;//入库类型（采购入库）
                $dat['rkdh'] = 'RKDH' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
                $dat["service_time"]=date("Y-m-d H:s:i",time());//业务时间
                $dat["remark"]='RKD' .  $data['system_no'];
                $dat['system_no'] = 'CGD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
                model("instoragelist")->allowField(true)->data($dat)->save();
                $instorage_id = model("instoragelist")->id;
                foreach ($data['details'] as $c => $v) {
                    $count = \app\admin\model\InstorageDetails::whereTime('create_time', 'today')->count();
                    $data['details'][$c]['type'] = 1;//入库类型，采购入库
                    $data['details'][$c]['zyh'] = 'KC' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);;//资源号
                    $data['details'][$c]['is_finished'] = 2;//已入库
                    $data['details'][$c]['instorage_time'] = date("Y-m-d H:s:i",time());//入库类型，采购入库
                    $data['details'][$c]['instorage_id'] = $instorage_id;//入库列表的id
                }
                model('InstorageDetails')->allowField(true)->saveAll($data['details']);
                model('InstorageOrder')->allowField(true)->saveAll($data['details']);
            }
            model('purchasedetails')->allowField(true)->saveAll($data['details']);
            foreach ($data['other'] as $c => $v) {
                $data['other'][$c]['purchase_id'] = $id;
            }
            model('purchase_fee')->allowField(true)->saveAll($data['details']);
            return returnRes(true, '', ['id' => $id]);
        }else{
            $purchase_id=request()->param("id");
            $data['purchaselist']=db("purchaselist")->where(array("companyid"=>Session::get("uinfo", "admin")['companyid'],'id'=>$purchase_id))->find();
            $data['detail']=model("purchasedetails")->where(array("companyid"=>Session::get("uinfo", "admin")['companyid'],'id'=>$purchase_id))->select();
            $data["other"]=model("purchase_fee")->where(array("companyid"=>Session::get("uinfo", "admin")['companyid'],'id'=>$purchase_id))->select();
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
    public function getpurchaselist( $pageLimit = 10){
        $params = request()->param();
        $list = \app\admin\model\Purchaselist::where('companyid', Session::get("uinfo", "admin")['companyid']);

        if (!empty($params['ywsjStart'])) {
            $list->where('service_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('service_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['status'])) {
            $list->where('status', $params['status']);
        }
        if (!empty($params['rkfs'])) {
            $list->where('rkfs', $params['rkfs']);
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
        $list = $list->paginate($pageLimit);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**采购单列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function purchaseaddinfo(){
        $companyid= Session::get("uinfo", "admin")['companyid'];
        //往来单位运营商
        $data["custom"] = model("custom")->where(array("companyid"=>$companyid,"issupplier"=>1))->field("id,custom")->select();
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
public function getpurchasedetail(){
    $id=request()->param("id");
    $data["list"]=model("purchaselist")->where(array("id"=>$id))->find();
    $data["detail"]=model("purchasedetails")->where(array("purchase_id"=>$id))->find();
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