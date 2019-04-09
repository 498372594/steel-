<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use think\Db;
use think\Exception;
use think\Session;

class Instorage extends Right
{
    use Backend;
    /**入库单列表
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function getrk(){
        $params = request()->param();
        $list = \app\admin\model\KcRk::where('companyid', Session::get("uinfo", "admin")['companyid']);
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('yw_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($instorageorderparams['status'])) {
            $list->where('status', $params['status']);
        }
        if (!empty($params['system_number'])) {
            $list->where('system_number', 'like', '%' . $params['system_number'] . '%');
        }
        if (!empty($params['beizhu'])) {
            $list->where('beizhu', 'like', '%' . $params['beizhu'] . '%');
        }
        $list=$list->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**入库单明细
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function instorageorder(){
        $instorage_id=request()->param("id");
        $list = model("ViewInstorageOrder")->where("instorage_id",$instorage_id)->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**修改入库单明细
     * @return \think\response\Json
     * @throws \Exception
     */
//    public function updatedetail(){
//        if(request()->isPut()){
//            $list = request()->param();
//            $res =model("instoragelist")->allowField(true)->save($list["purchasedetails"],['id' => $list["purchasedetails"]["id"]]);
//            $res =model("purchasedetails")->allowField(true)->saveAll($list["purchasedetails"]);
//            return returnRes($res,'修改失败');
//        }
//    }

    /**待入库单明细
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function waitinstorage(){
        $params = request()->param();
        $list = \app\admin\model\Purchasedetails::where(array("companyid"=>Session::get("uinfo", "admin")['companyid'],"is_finished"=>1));
        if (!empty($params['time_start'])) {
            $list->where('create_time', '>=', $params['time_start']);
        }
        if (!empty($params['time_end'])) {
            $list->where('create_time', '<=', date('Y-m-d', strtotime($params['time_end'] . ' +1 day')));
        }
        //是否完成
        if (!empty($params['status'])) {
            $list->where('status', $params['status']);
        }
        //供应商
        if (!empty($params['supplier_id'])) {
            $list->where('supplier_id', $params['supplier_id']);
        }
        //仓库
        if (!empty($params['storage_id'])) {
            $list->where('storage_id', $params['storage_id']);
        }
        //系统单号
        if (!empty($params['system_no'])) {
            $list->where('system_no', 'like', '%' . $params['system_no'] . '%');
        }
        if (!empty($params['specification'])) {
            $list->where('remark', 'like', '%' . $params['specification'] . '%');
        }
        $list = $list->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**批量操作入库
     * @return \think\response\Json
     */
    public function instorage(){
        if(request()->isPost()){
            $count = \app\admin\model\Instoragelist::whereTime('create_time', 'today')->count();
            //生成入库单列表
            $data["status"]=1;
            if(request()->param("id")){
                $data["id"]=request()->post("id");
            }
            $data["department"]=request()->post("department");
            $data["clerk"]=request()->post("clerk");
            $data["service_time"]=date("Y-m-d H:s:i",time());
            $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
            $data["clerk"]=request()->post("clerk");
            $data["type"]=1;
            $data["department"]=request()->post("department");
            $data['add_name'] = Session::get("uinfo", "admin")['name'];
            $data['add_id'] = Session::get("uid", "admin");
            $count = \app\admin\model\Instoragelist::whereTime('create_time', 'today')->count();
            $data["system_no"]='RKD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
//            $data["rukdh"]='RKD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            model("instoragelist")->allowField(true)->save($data);
            if($data["id"]){
                $instorage_id =$data["id"];
            }else{
                $instorage_id = model("instoragelist")->getLastInsID();
            }


            foreach ($data['details'] as $c => $v) {
                $count = \app\admin\model\InstorageDetails::whereTime('create_time', 'today')->count();
                if(isset($v["instorage_id"])){
                    $instorage_id=$v["instorage_id"];
                }
                $data['details'][$c]['in_out'] = 1;//入库，2出库
                $data['details'][$c]['type'] = 1;//入库类型，采购入库
                $data['details'][$c]['zyh'] = 'KC' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);;//资源号
                $data['details'][$c]['is_finished'] = 2;//已入库
                $data['details'][$c]['instorage_time'] = date("Y-m-d H:s:i",time());//入库类型，采购入库
                $data['details'][$c]['instorage_id'] = $instorage_id;//入库列表的id
                $data['details'][$c]['purchasedetail_id'] = $v["id"];//入库列表的id
                $change[$c]["id"]=$v["id"];
                $change[$c]["actual_shuliang"]=$v["shuliang"];
                $change[$c]["actual_heavy"]=$v["heavy"];
                $change[$c]['is_finished'][$c]['is_finished'] = 2;//已入库
                unset($v["id"]);
            }
            //库存
            model('InstorageDetails')->allowField(true)->saveAll($data['details']);
            //入库单
            model('InstorageOrder')->allowField(true)->saveAll($data['details']);
            $res =model("purchasedetails")->update($change);
            return returnRes($res,'修改失败');
        }
    }



    /**库存盘点
     * @return \think\response\Json
     */
        public function checkstoragelist(){
            $storage_id=request()->param();
            $list=db("InstorageDetails")
                ->field("*,sum(total_price) as total,sum(lingzhi) as book_lingzhi,sum(zhishu) as book_zhishu,sum(heavy) as book_heavy")
                ->group("productname,specification,width,length,houdu_name,texture,originarea,jianzhishu,")
                ->where("storage_id",$storage_id)
                ->paginate(10);
            return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
        }
        public function checkstorage(){
            if(request()->isPost()){
                $data=request()->post();
                $dat["service_time"]=$data["service_time"];
                $dat["remark"]=$data[" remark"];
                $count = \app\admin\model\Checkstoragelist::whereTime('create_time', 'today')->count();
                $dat["xtdh"]='KCPD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
                $dat['companyid'] = Session::get("uinfo", "admin")['companyid'];
                $dat['add_name'] = Session::get("uinfo", "admin")['name'];
                $dat['add_id'] = Session::get("uid", "admin");
                $re=model("checkstoragelist")->save($dat);
                $check=$data["check"];
                $id=model("checkstoragelist")->id;
                foreach ($check as $key=>$val){
                    $check["$key"]["check_id"]=$id;

                }
               $res= model("checkstoragedetail")->saveAll($check);
                return returnRes($res,'清库失败');
            }
        }

    /**库存盘点单
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
        public function checkstoragedetails(){
            $check_id=request()->param();
            $list=model("checkstoragedetail")->where(array("companyid"=>Session::get("uinfo", "admin")['companyid'],"check_id"=>$check_id))->paginate(10);
            return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
        }

    /**
     * 库存调拨单列表(条件筛选)
     */
        public function getpurchaselist(){
            $where['companyid']=array('eq',Session::get("uinfo", "admin")['companyid']);
            $where['is_finished']=array('eq',2);

        if (request()->param('starttime') !== "" ||request()->param('endtime') !== "") {
            $starttime = request()->param('starttime') ? request()->param('starttime') : ''; //开始时间
            $endtime = request()->param('endtime') ? date("Y-m-d H:s:i",(strtotime(request()->param('endtime')) + 60 * 60 * 24 - 1 )): date("Y-m-d H:s:i",time()); //结束时间
            if ($starttime && $endtime) {
                $where['create_time'] = array('between', array($starttime, $endtime));
            }
            if ($starttime == '' && $endtime) {
                $where['create_time'] = array('elt', $endtime);
            }
            if ($starttime && $endtime == '') {
                $where['create_time'] = array('gt', $starttime);
            }

        }
            if (request()->param('begin_length') !== "" ||request()->param('end_length') !== "") {
                $begin_length = request()->param('begin_length') ? request()->param('begin_length') : '';
                $end_length = request()->param('end_length') ? request()->param('end_length') : '';
                if ($begin_length && $end_length) {
                    $where['length'] = array('between', array($begin_length, $end_length));
                }
                if ($begin_length == '' && $end_length) {
                    $where['length'] = array('elt', $end_length);
                }
                if ($begin_length && $end_length == '') {
                    $where['length'] = array('gt', $begin_length);
                }

            }
            if (request()->param('begin_width') !== "" ||request()->param('end_width') !== "") {
                $begin_width = request()->param('begin_width') ? request()->param('begin_width') : '';
                $end_width = request()->param('end_width') ? request()->param('end_width') : '';
                if ($begin_width && $end_width) {
                    $where['width'] = array('between', array($begin_width, $end_width));
                }
                if ($begin_width == '' && $end_width) {
                    $where['width'] = array('elt', $end_width);
                }
                if ($begin_width && $end_width == '') {
                    $where['width'] = array('gt', $end_width);
                }

            }
            if(request()->param('originarea')){
                $originarea=request()->param('originarea');
                $where['originarea']=array('like','%'. $originarea.'%');
            }
            if(request()->param('pjlx')){
                $pjlx=request()->param('pjlx');
                $where['pjlx']=array('like','%'. $pjlx.'%');
            }
            if(request()->param('productname')){
                $productname=request()->param('productname');
                $where['productname']=array('like','%'. $productname.'%');
            }
            if(request()->param('spcification')){
                $spcification=request()->param('spcification');
                $where['spcification']=array('like','%'. $spcification.'%');
            }
            if(request()->param('texture')){
                $texture=request()->param('texture');
                $where['texture']=array('like','%'. $texture.'%');
            }
            if(request()->param('productname_id')){
                $productname_id=request()->param('productname_id');
                $where['productname_id']=array('like','%'. $productname_id.'%');
            }
            $list=model("InstorageDetails")->where($where)->paginate(10);
            return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
        }

    /**
     * 调拨货物
     */
        public function transfergoods(){
            if(request()->isPost()){
                $data=request()->post();
                foreach($data as $key=>$val){
                    $info=model("InstorageDetails")->where("id",$val["id"])->find();
                    //调出之后的货物

                    $dat["lingzhi"]=$info["lingzhi"]-$val["lingzhi"];
                    $dat["jianshu"]=$info["jianshu"]-$val["jianshu"];
                    $dat["shuliang"]=$info["shuliang"]-$val["shuliang"];
                    $dat["heavy"]=$info["heavy"]-$val["heavy"];
                        model("InstorageDetails")->where("id",$val["id"])->update($dat);
                        //入库单列表
                    $count = \app\admin\model\Instoragelist::whereTime('create_time', 'today')->count();
                    $data1["rkdh"]="RKD".date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
                    $data1["remark"]="库存调拨单,KCDBD".date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
                    $data1["service_time"]=date("Y-m-d H:s:m",time());
                    $data1['companyid'] = Session::get("uinfo", "admin")['companyid'];
                    $data1['add_name'] = Session::get("uinfo", "admin")['name'];
                    $data1['add_id'] = Session::get("uid", "admin");
                    $re=model("instoragelist")->save($data1);
                    //转以后生成的入库明细
                    $info["instorage_id"]=model("instoragelist")->id;
                     $info["storage_id"]=$val["storage_id"];
                    $info["lingzhi"]=$val["lingzhi"];
                    $info["jianshu"]=$val["jianshu"];
                    $info["shuliang"]=$val["shuliang"];
                    $info["heavy"]=$info["heavy"];
                    $count = \app\admin\model\InstorageDetails::whereTime('create_time', 'today')->count();
                    $info["system_no"]="RKD".date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);

                    $data1["instorage_time"]=date("Y-m-d H:s:m",time());
                    unset($info["id"]);
                    $res=model("InstorageDetails")->save($info);
                    return returnRes($res,'转库失败');

                }
            }
        }
//        public function ceshi(){
//            $arr=model("unit")->update(array("id"=>11,"unit"=>"吨1"));
//
//            dump($arr);
//        }
}