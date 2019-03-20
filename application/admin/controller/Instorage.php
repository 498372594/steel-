<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use think\Db;
use think\Exception;
use think\Session;

class Instorage extends Right
{
    use Backend;

    /**
     * 列表附加数据
     */
    protected function indexAttach()
    {
        $this->assign("lists", [
            "pageSize"  => getDropdownList("pageSize")
        ]);
    }

    /**
     * 添加 验证前处理
     */
    protected function afterAddValidate($data)
    {
        $data['companyid']=Session::get("uinfo", "admin")['companyid'];
        $data['add_name']=Session::get("uinfo", "admin")['name'];
        $data['add_id']=Session::get("uid", "admin");
        $data['create_time'] = now_datetime();
        return $data;
    }

    /**
     * 编辑附加数据
     */
    protected function editAttach()
    {
        $id = input("id");
        if (empty($id))  throw new Exception("未知的id！");

        $data = Db::table("instorage")

            ->where("id", $id)
            ->find();
        $this->assign("data", $data);
    }

    /**
     * 编辑 验证前处理
     */
    protected function beforeEditValidate($data)
    {
        $data['parentId'] = $parentInfo['id'];
        return $data;
    }


    protected function afterEditValidate($data)
    {

        $data['create_time'] = now_datetime();
        return $data;
    }

    /**入库单列表
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function getinstoragelist(){
        $list = model("instoragelist")->where("companyid", Session::get("uinfo", "admin")['companyid'])->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**入库单明细
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function instoragedetail(){
        $instorage_id=request()->param("instorage_id");
        $list = model("purchasedetails")->where("instorage_id",$instorage_id)->paginate(10);
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
        $instorage_id=request()->param("instorage_id");
        $list = model("purchasedetails")->where(array("companyid"=>Session::get("uinfo", "admin")['companyid'],"is_finished"=>1))->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**批量操作入库
     * @return \think\response\Json
     */
    public function instorage(){
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
            $res =model("purchasedetails")->where("id","in","ids")->update(array("is_finished"=>2,"instorage_id"=>model("instoragelist")->id));
            return returnRes($res,'修改失败');
        }
    }

    /**
     *预留锁货库存列表
     */
    public function lockgoodslist(){
        $list=model("purchasedetails")->where(array("companyid"=>Session::get("uinfo", "admin")['companyid'],"is_finished"=>2))->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**锁货
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function reservedgoods(){
        if(request()->isPost()){
            $data=request()->post();
            foreach($list["reserved"] as $key=>$value){
                $info=model("purchasedetails")->where("id",$value["purchase_id"])->find();
                $inf["shuliang"]=$info["shuliang"]-$value["reserved_num"];
                $inf["jianshu"]=$info["jianshu"]-$value["reserved_jianshu"];
                $inf["jianshu"]=$info["jianshu"]-$value["reserved_jianshu"];
                $inf["heavy"]=$info["heavy"]-$value["reserved_heavy"];
                $inf["id"]=$info["id"];
                model("purchasedetails")->where("id",$info["id"])->update($inf);
            }
            $res =model("reserved")->allowField(true)->saveAll($data["reserved"]);
            return returnRes($res,'锁定');
            }
        }

    /**
     * 预留存量释放
     */
        public function relaselist(){
            $list=model("view_reserved")->where(array("companyid"=>Session::get("uinfo", "admin")['companyid']))->paginate(10);
            return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
        }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
        public function releasegoods(){
            if(request()->isPost()){
                $data=request()->post();
                foreach($list["released"] as $key=>$value){
                    $info=model("purchasedetails")->where("id",$value["purchasedetails_id"])->find();
                    $info1=model("reserved")->where("id",$value["id"])->find();
                    $inf["shuliang"]=$info["shuliang"]+$value["reserved_num"];
                    $inf["lingzhi"]=$info["lingzhi"]+$value["reserved_lingzhi"];
                    $inf["jianshu"]=$info["jianshu"]+$value["reserved_jianshu"];
                    $inf["heavy"]=$info["heavy"]+$value["reserved_heavy"];
                    $inf["id"]=$info["id"];
                    $inf1["id"]=$info1["id"];
                    $inf1["reserved_num"]=$info1["reserved_num"]-$value["reserved_num"];
                    $inf1["reserved_jianshu"]=$info1["reserved_jianshu"]-$value["reserved_jianshu"];
                    $inf1["reserved_heavy"]=$info1["reserved_heavy"]-$value["reserved_heavy"];
                    $inf1["reserved_jianshu"]=$info1["reserved_jianshu"]-$value["reserved_jianshu"];
                    model("purchasedetails")->save($inf);
                    $res=model("reserved")->save($inf1);
                }
                return returnRes($res,'锁定');
            }
        }
}