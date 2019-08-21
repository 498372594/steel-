<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use think\Db;
use think\Exception;
use think\Session;

class Steelmanagement extends Right
{
    use Backend;

    public function index()
    {
        return view();
    }

    public function productname()
    {
        $list = model("productname")->where("companyid", $this->getCompanyId())->select();
        $this->assign("list", $list);
        return view();
    }

    public function addproductname()
    {

            $id = request()->param("id");
            if ($id) {
                $info = model("productname")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();

    }

  public function addproductnames(){
      if (empty(request()->get("id"))) {
          $data['name'] = request()->get("name");
          $data['sort'] = request()->get("sort");
          $data['zjm'] = request()->get("zjm");
          $data['companyid'] = $this->getCompanyId();
          $data['add_name'] = $this->getAccount()['name'];
          $data['add_id'] = $this->getAccountId();
          $result = model("productname")->save($data);
      } else {
          $id = request()->get("id");
          $data['name'] = request()->get("name");
          $data['sort'] = request()->get("sort");
          $data['zjm'] = request()->get("zjm");
          $data['add_name'] = $this->getAccount()['name'];
          $data['add_id'] = $this->getAccountId();
          $result = model("productname")->where("id", $id)->update($data);
      }
      if ($result) {
          return json_suc();
      } else {
          return json_err();
      }
  }
    public function deleteproductname()
    {
        $pk = model("productname")->getPk();
        $ids = $this->request->param($pk);
        $where[$pk] = ["in", $ids];
        if (false === model("productname")->where($where)->delete()) {
            return json_err();
        } else {
            return json_suc();
        }
    }

    public function texture()
    {
        $list = model("texture")->where("companyid", $this->getCompanyId())->select();
        $this->assign("list", $list);
        return view();
    }
   public function addtextures(){

           if (empty(request()->get("id"))) {
               $data['texturename'] = request()->get("texturename");
               $data['sort'] = request()->get("sort");
               $data['zjm'] = request()->get("zjm");
               $data['companyid'] = $this->getCompanyId();
               $data['add_name'] = $this->getAccount()['name'];
               $data['add_id'] = $this->getAccountId();
               $result = model("texture")->save($data);
           } else {
               $id = request()->get("id");
               $data['texturename'] = request()->get("texturename");
               $data['sort'] = request()->get("sort");
               $data['zjm'] = request()->get("zjm");
               $data['add_name'] = $this->getAccount()['name'];
               $data['add_id'] = $this->getAccountId();
               $result = model("texture")->where("id", $id)->update($data);
           }
           if ($result) {
               return json_suc();
           } else {
               return json_err();
           }

   }
    public function addtexture()
    {

            $id = request()->param("id");
            if ($id) {
                $info = model("texture")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();

    }

    public function deletetexture()
    {
        $pk = model("texture")->getPk();
        $ids = $this->request->param($pk);
        $where[$pk] = ["in", $ids];
        if (false === model("texture")->where($where)->delete()) {
            return json_err();
        } else {
            return json_suc();
        }
    }

    public function originarea()
    {
        $list = model("originarea")->where("companyid", $this->getCompanyId())->select();
        $this->assign("list", $list);
        return view();
    }

    public function addoriginarea()
    {
        if (request()->post()) {
            if (empty(request()->post("id"))) {
                $data['originarea'] = request()->post("originarea");
                $data['sort'] = request()->post("sort");
                $data['zjm'] = request()->post("zjm");
                $data['companyid'] = $this->getCompanyId();
                $data['add_name'] = $this->getAccount()['name'];
                $data['add_id'] = $this->getAccountId();
                $result = model("originarea")->save($data);
            } else {
                $id = request()->post("id");
                $data['originarea'] = request()->post("originarea");
                $data['sort'] = request()->post("sort");
                $data['zjm'] = request()->post("zjm");
                $data['add_name'] = $this->getAccount()['name'];
                $data['add_id'] = $this->getAccountId();
                $result = model("originarea")->where("id", $id)->update($data);
            }
            if ($result) {
                return json_suc();
            } else {
                return json_err();
            }
        } else {
            $id = request()->param("id");
            if ($id) {
                $info = model("originarea")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();
        }
    }

    public function deleteoriginarea()
    {
        $pk = model("originarea")->getPk();
        $ids = $this->request->param($pk);
        $where[$pk] = ["in", $ids];
        if (false === model("originarea")->where($where)->delete()) {
            return json_err();
        } else {
            return json_suc();
        }
    }

    public function specification()
    {
        $list = model("specification")->where("companyid", $this->getCompanyId())->select();
        $this->assign("list", $list);
        return view();
    }
public function addspecifications(){

    if (request()->get()) {
        if (empty(request()->post("id"))) {
            $data['specification'] = request()->get("specification");
            $data['sort'] = request()->get("sort");
            $data['companyid'] = $this->getCompanyId();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            $result = model("specification")->save($data);
        } else {
            $id = request()->get("id");
            $data['specification'] = request()->get("specification");
            $data['sort'] = request()->get("sort");
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            $result = model("specification")->where("id", $id)->update($data);
        }
        if ($result) {
            return json_suc();
        } else {
            return json_err();
        }
    }
}
    public function addspecification()
    {

            $id = request()->param("id");
            if ($id) {
                $info = model("specification")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();

    }

    public function deletespecification()
    {
        $pk = model("specification")->getPk();
        $ids = $this->request->param($pk);
        $where[$pk] = ["in", $ids];
        if (false === model("specification")->where($where)->delete()) {
            return json_err();
        } else {
            return json_suc();
        }
    }

    public function storage()
    {
        $list = model("storage")->where("companyid", $this->getCompanyId())->select();
        $this->assign("list", $list);
        return view();
    }

    public function addstorage()
    {

            $id = request()->param("id");
            if ($id) {
                $info = model("storage")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();

    }

    public function deletestorage()
    {
        $pk = model("storage")->getPk();
        $ids = $this->request->param($pk);
        $where[$pk] = ["in", $ids];
        if (false === model("storage")->where($where)->delete()) {
            return json_err();
        } else {
            return json_suc();
        }
    }

    public function unit()
    {
        $list = model("unit")->where("companyid", $this->getCompanyId())->select();
        $this->assign("list", $list);
        return view();
    }
    public function addunits(){

            if (empty(request()->get("id"))) {
                $data['unit'] = request()->get("unit");
                $data['sort'] = request()->get("sort");
                $data['companyid'] = $this->getCompanyId();
                $data['add_name'] = $this->getAccount()['name'];
                $data['add_id'] = $this->getAccountId();
                $result = model("unit")->save($data);
            } else {
                $id = request()->get("id");
                $data['unit'] = request()->get("unit");
                $data['sort'] = request()->get("sort");
                $data['add_name'] = $this->getAccount()['name'];
                $data['add_id'] = $this->getAccountId();
                $result = model("unit")->where("id", $id)->update($data);
            }
            if ($result) {
                return json_suc();
            } else {
                return json_err();
            }

    }
    public function addunit()
    {

            $id = request()->param("id");
            if ($id) {
                $info = model("unit")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();

    }

    public function deleteunit()
    {
        $pk = model("specification")->getPk();
        $ids = $this->request->param($pk);
        $where[$pk] = ["in", $ids];
        if (false === model("specification")->where($where)->delete()) {
            return json_err();
        } else {
            return json_suc();
        }
    }

    public function classname()
    {
        $list = model("classname")->where("companyid", $this->getCompanyId())->select();
        $this->assign("list", $list);
        return view();
    }
 public function addclassnames(){
     $data=request()->get();
     if (empty(request()->get("id"))) {

//                $data['classname'] = request()->post("classname");
//                $data['sort'] = request()->post("sort");
         $data['companyid'] = $this->getCompanyId();
         $data['add_name'] = $this->getAccount()['name'];

         $data['add_id'] = $this->getAccountId();
         unset($data['/admin/steelmanagement/addclassnames_html']);

         $result = model("classname")->save($data);


     } else {
         $id = request()->get("id");
//                $data['classname'] = request()->post("classname");
//                $data['sort'] = request()->post("sort");
         $data['add_name'] = $this->getAccount()['name'];
         $data['add_id'] = $this->getAccountId();
         $result = model("classname")->where("id", $id)->update($data);
     }
     if ($result) {
         return json_suc();
     } else {
         return json_err();
     }
 }
    public function addclassname()
    {

            $id = request()->param("id");
            if ($id) {
                $info = model("classname")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();

    }

    public function deleteclassname()
    {
        $pk = model("classname")->getPk();
        $ids = $this->request->param($pk);
        $where[$pk] = ["in", $ids];
        if (false === model("classname")->where($where)->delete()) {
            return json_err();
        } else {
            return json_suc();
        }
    }
//    public function ceshi(){
//        $list['value']=model("classname")->where("companyid",$this->getCompanyId())->field("classname")->select();
//        return json($list);
//    }
    public function product()
    {
        $list = model("product")->alias("a")->join("classname b", "a.classid=b.id", "left")->field("a.*,b.classname")->where("a.companyid", $this->getCompanyId())->select();
        $this->assign("list", $list);
        return view();
    }

    public function addproduct()
    {


            $id = request()->param("id");
            $classlist = model("classname")->where("companyid", $this->getCompanyId())->field("id,classname")->select();
            $classArr = ["" => ""];
            if ($classlist) {
                foreach ($classlist as $k => $v) {
                    $classArr[$v['id']] = $v['classname'];
                }
            } else {
                $this->error("请添加类型！");
            }
            $this->assign([
                "lists" => [
                    "classlist" => $classArr
                ]
            ]);
            if ($id) {
                $info = model("product")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();

    }
   public function addproducte(){
       if (empty(request()->get("id"))) {

           $data['classid'] = request()->get("classid");
           $data['productname'] = request()->get("productname");
           if (!model("productname")->where("name", $data['productname'])->find()) {
               $class['name'] = $data['productname'];
               $class['zjm'] = request()->get("zjm");
               $class['companyid'] = $this->getCompanyId();
               $class['add_name'] = $this->getAccount()['name'];
               $class['add_id'] = $this->getAccountId();
               model("productname")->save($class);
           }
           $data['texture'] = request()->get("texture");
           if (!model("texture")->where("texturename", $data['texture'])->find()) {
               $texture['texturename'] = $data['texture'];
               $texture['companyid'] = $this->getCompanyId();
               $texture['add_name'] = $this->getAccount()['name'];
               $texture['add_id'] = $this->getAccountId();
               model("texture")->save($texture);
           }
           $data['originarea'] = request()->get("originarea");
           if (!model("originarea")->where("originarea", $data['originarea'])->find()) {
               $orginarea['originarea'] = $data['originarea'];
               $orginarea['companyid'] = $this->getCompanyId();
               $orginarea['add_name'] = $this->getAccount()['name'];
               $orginarea['add_id'] = $this->getAccountId();
               model("originarea")->save($orginarea);
           }
           $data['specification'] = request()->get("specification");
           if (!model("specification")->where("specification", $data['specification'])->find()) {
               $specification['specification'] = $data['specification'];
               $specification['companyid'] = $this->getCompanyId();
               $specification['add_name'] = $this->getAccount()['name'];
               $specification['add_id'] = $this->getAccountId();
               model("specification")->save($specification);
           }
           $data['piece_weight'] = request()->get("piece_weight");
           $data['length'] = request()->get("length");
           $data['width'] = request()->get("width");
           $data['unit'] = request()->get("unit");
           $data['number_alarm_val'] = request()->get("number_alarm_val");
           $data['weight_alarm_val'] = request()->get("weight_alarm_val");
           $data['pack_no'] = request()->get("pack_no");
           $data['sort'] = request()->get("sort");
           $data['companyid'] = $this->getCompanyId();
           $data['add_name'] = $this->getAccount()['name'];
           $data['add_id'] = $this->getAccountId();
           $result = model("product")->save($data);
       } else {
           $id = request()->get("id");
           $data['classid'] = request()->get("classid");
           $data['productname'] = request()->get("productname");
           $data['texture'] = request()->get("texture");
           $data['originarea'] = request()->get("originarea");
           $data['specification'] = request()->get("specification");
           $data['piece_weight'] = request()->get("piece_weight");
           $data['length'] = request()->get("length");
           $data['width'] = request()->get("width");
           $data['number_alarm_val'] = request()->get("number_alarm_val");
           $data['weight_alarm_val'] = request()->get("weight_alarm_val");
           $data['unit'] = request()->get("unit");
           $data['pack_no'] = request()->get("pack_no");
           $data['sort'] = request()->get("sort");
           $data['add_name'] = $this->getAccount()['name'];
           $data['add_id'] = $this->getAccountId();
           $result = model("product")->where("id", $id)->update($data);
       }
       if ($result) {
           return json_suc();
       } else {
           return json_err();
       }
       //
   }
    public function jsfs()
    {
        $list = model("jsfs")->where("companyid", $this->getCompanyId())->select();
        $this->assign("list", $list);
        return view();
    }
    public function addjsfsd(){

            if (empty(request()->get("id"))) {
                $data['jsfs'] = request()->get("jsfs");
                $data['jj_type'] = request()->get("jjlx");
                $data['companyid'] = $this->getCompanyId();
                $data['add_name'] = $this->getAccount()['name'];
                $data['add_id'] = $this->getAccountId();
                $result = model("jsfs")->save($data);
            } else {
                $id = request()->get("id");
                $data['jsfs'] = request()->get("jsfs");
                $data['sort'] = request()->get("sort");
                $data['add_name'] = $this->getAccount()['name'];
                $data['add_id'] = $this->getAccountId();
                $result = model("jsfs")->where("id", $id)->update($data);
            }
            if ($result) {
                return json_suc();
            } else {
                return json_err();
            }

    }
    public function addjsfs()
    {

            $id = request()->param("id");
            if ($id) {
                $info = model("jsfs")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();

    }

    public function deletejsfs()
    {
        $pk = model("jsfs")->getPk();
        $ids = $this->request->param($pk);
        $where[$pk] = ["in", $ids];
        if (false === model("jsfs")->where($where)->delete()) {
            return json_err();
        } else {
            return json_suc();
        }

    }
    public function service()
    {
        return view();
    }
    public function custom()
    {
        $list = model("custom")->where("companyid", $this->getCompanyId())->select();
        $this->assign("list", $list);
        return view();
    }

    public function addcustom()
    {
        if (request()->post()) {
            if (empty(request()->post("id"))) {
                $data['custom'] = request()->post("custom");
                $data['short_name'] = request()->post("short_name");
                $data['lxr'] = request()->post("lxr");
                $data['phone'] = request()->post("phone");
                $data['dwsh'] = request()->post("dwsh");
                $data['zjm'] = request()->post("zjm");
                $data['iscustom'] = request()->post("iscustom");
                $data['issupplier'] = request()->post("issupplier");
                $data['other'] = request()->post("other");
                $data['sort'] = request()->post("sort");
                $data['companyid'] = $this->getCompanyId();
                $data['add_name'] = $this->getAccount()['name'];
                $data['add_id'] = $this->getAccountId();
                $result = model("custom")->save($data);
            } else {
                $id = request()->post("id");
                $data['custom'] = request()->post("custom");
                $data['short_name'] = request()->post("short_name");
                $data['lxr'] = request()->post("lxr");
                $data['phone'] = request()->post("phone");
                $data['dwsh'] = request()->post("dwsh");
                $data['zjm'] = request()->post("zjm");
                $data['iscustom'] = request()->post("iscustom");
                $data['issupplier'] = request()->post("issupplier");
                $data['other'] = request()->post("other");
                $data['sort'] = request()->post("sort");
                $data['add_name'] = $this->getAccount()['name'];
                $data['add_id'] = $this->getAccountId();
                $result = model("custom")->where("id", $id)->update($data);
            }
            if ($result) {
                return json_suc();
            } else {
                return json_err();
            }
        } else {
            $id = request()->param("id");
            if ($id) {
                $info = model("custom")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();
        }
    }

    public function deletecustom()
    {
        $pk = model("classname")->getPk();
        $ids = $this->request->param($pk);
        $where[$pk] = ["in", $ids];
        if (false === model("classname")->where($where)->delete()) {
            return json_err();
        } else {
            return json_suc();
        }
    }
    public function transportation()
    {
        $list = model("transportation")->where("companyid", $this->getCompanyId())->select();
        $this->assign("list", $list);
        return view();
    }

    public function addtransportation()
    {
        if (request()->post()) {
            if (empty(request()->post("id"))) {
                $data['transportation'] = request()->post("transportation");
                $data['sort'] = request()->post("sort");
                $data['companyid'] = $this->getCompanyId();
                $data['add_name'] = $this->getAccount()['name'];
                $data['add_id'] = $this->getAccountId();
                $result = model("transportation")->save($data);
            } else {
                $id = request()->post("id");
                $data['transportation'] = request()->post("transportation");
                $data['sort'] = request()->post("sort");
                $data['add_name'] = $this->getAccount()['name'];
                $data['add_id'] = $this->getAccountId();
                $result = model("transportation")->where("id", $id)->update($data);
            }
            if ($result) {
                return json_suc();
            } else {
                return json_err();
            }
        } else {
            $id = request()->param("id");
            if ($id) {
                $info = model("transportation")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();
        }
    }

    public function deletetransportation()
    {
        $pk = model("transportation")->getPk();
        $ids = $this->request->param($pk);
        $where[$pk] = ["in", $ids];
        if (false === model("transportation")->where($where)->delete()) {
            return json_err();
        } else {
            return json_suc();
        }
    }
    public function financeset(){
        return view();
    }
   public function addbanks(){

           if (empty(request()->get("id"))) {
               $data['zjm'] = request()->get("zjm");
               $data['name'] = request()->get("name");
               $data['banktype_id'] = request()->get("banktype_id");
               $data['kaihuhang'] = request()->get("kaihuhang");
               $data['bank'] = request()->get("bank");
               $data['sort'] = request()->get("sort");
               $data['companyid'] = $this->getCompanyId();
               $data['add_name'] = $this->getAccount()['name'];
               $data['add_id'] = $this->getAccountId();
               $result = model("bank")->save($data);
           } else {
               $id = request()->get("id");
               $data['zjm'] = request()->get("zjm");
               $data['name'] = request()->get("name");
               $data['banktype_id'] = request()->get("banktype_id");
               $data['kaihuhang'] = request()->get("kaihuhang");
               $data['bank'] = request()->get("bank");
               $data['sort'] = request()->get("sort");
               $data['add_name'] = $this->getAccount()['name'];
               $data['add_id'] = $this->getAccountId();
               $result = model("bank")->where("id", $id)->update($data);
           }
           if ($result) {
               return json_suc();
           } else {
               return json_err();
           }

   }
    public function addbank()
    {

            $id = request()->param("id");
            if ($id) {
                $info = model("bank")->where("id", $id)->find();

            } else {
                $info = null;
            }
//            $this->assign("type", $type);
            $this->assign("data", $info);
            return view();

    }

    public function delete()
    {   $model=request()->param('model');
        $pk = model("$model")->getPk();
        $ids = $this->request->param($pk);
        $where[$pk] = ["in", $ids];
        if (false === model("$model")->where($where)->delete()) {
            return json_err();
        } else {
            return json_suc();
        }
    }
    public function bindex(){
        $model=request()->param('model');
        $list = model("$model")->where("companyid", $this->getCompanyId())->select();
        $this->assign("list", $list);
        return view("$model");
    }
    public function addfaxis(){
        if (empty(request()->get("id"))) {
            $data['fxfs'] = request()->get("fxfs");
            $data['fxdx'] = request()->get("fxdx");
            $data['fxgz'] = request()->get("fxgz");
            $data['fxxz'] = request()->get("fxxz");
            $data['qjts'] = request()->get("qjts");
            $data['jsgs'] = request()->get("jsgs");
            $data['zjts'] = request()->get("zjts");
            $data['description'] = request()->get("description");
            $data['sort'] = request()->get("sort");
            $data['companyid'] = $this->getCompanyId();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            $result = model("faxi")->save($data);
        } else {
            $id = request()->get("id");
            $data['fxfs'] = request()->get("fxfs");
            $data['fxdx'] = request()->get("fxdx");
            $data['fxgz'] = request()->get("fxgz");
            $data['fxxz'] = request()->get("fxxz");
            $data['qjts'] = request()->get("qjts");
            $data['jsgs'] = request()->get("jsgs");
            $data['zjts'] = request()->get("zjts");
            $data['description'] = request()->get("description");
            $data['sort'] = request()->get("sort");
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            $result = model("faxi")->where("id", $id)->update($data);
        }
        if ($result) {
            return json_suc();
        } else {
            return json_err();
        }
    }
    public function addfaxi()
    {

            $id = request()->param("id");
            if ($id) {
                $info = model("faxi")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();

    }
    public function addsalesmansetting()
    {
        if (request()->post()) {
            if (empty(request()->post("id"))) {
                $data['guizename'] = request()->post("guizename");
                $data['zljs'] = request()->post("zljs");
                $data['weight_start'] = request()->post("weight_start");
                $data['weight_end'] = request()->post("weight_end");
                $data['ticheng_price'] = request()->post("ticheng_price");
                $data['huikuanxishu'] = request()->post("huikuanxishu");
                $data['jsgs'] = request()->post("jsgs");
                $data['companyid'] = $this->getCompanyId();
                $data['add_name'] = $this->getAccount()['name'];
                $data['add_id'] = $this->getAccountId();
                $result = model("faxi")->save($data);
            } else {
                $id = request()->post("id");
                $data['guizename'] = request()->post("guizename");
                $data['zljs'] = request()->post("zljs");
                $data['weight_start'] = request()->post("weight_start");
                $data['weight_end'] = request()->post("weight_end");
                $data['ticheng_price'] = request()->post("ticheng_price");
                $data['huikuanxishu'] = request()->post("huikuanxishu");
                $data['jsgs'] = request()->post("jsgs");
                $data['add_name'] = $this->getAccount()['name'];
                $data['add_id'] = $this->getAccountId();
                $result = model("faxi")->where("id", $id)->update($data);
            }
            if ($result) {
                return json_suc();
            } else {
                return json_err();
            }
        } else {
            $id = request()->param("id");
            if ($id) {
                $info = model("faxi")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();
        }
    }
    public function addjiesuanfangshid(){
        if (empty(request()->get("id"))) {
            $data['jiesuanfangshi'] = request()->get("jiesuanfangshi");
            $data['sort'] = request()->get("sort");
            $data['companyid'] = $this->getCompanyId();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            $result = model("jiesuanfangshi")->save($data);
        } else {
            $id = request()->get("id");
            $data['jiesuanfangshi'] = request()->get("jiesuanfangshi");
            $data['sort'] = request()->get("sort");
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            $result = model("jiesuanfangshi")->where("id", $id)->update($data);
        }
        if ($result) {
            return json_suc();
        } else {
            return json_err();
        }
    }
    public function addjiesuanfangshi()
    {

            $id = request()->param("id");
            if ($id) {
                $info = model("jiesuanfangshi")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();

    }
   public function addpjlxs(){

           if (empty(request()->get("id"))) {
               $data['pjlx'] = request()->get("pjlx");
               $data['tax_rate'] = request()->get("tax_rate");
               $data['second_name'] = request()->get("second_name");
               $data['sort'] = request()->get("sort");
               $data['companyid'] = $this->getCompanyId();
               $data['add_name'] = $this->getAccount()['name'];
               $data['add_id'] = $this->getAccountId();
               $result = model("pjlx")->save($data);
           } else {
               $id = request()->get("id");
               $data['pjlx'] = request()->get("pjlx");
               $data['tax_rate'] = request()->get("tax_rate");
               $data['second_name'] = request()->get("second_name");
               $data['sort'] = request()->get("sort");
               $data['add_name'] = $this->getAccount()['name'];
               $data['add_id'] = $this->getAccountId();
               $result = model("pjlx")->where("id", $id)->update($data);
           }
           if ($result) {
               return json_suc();
           } else {
               return json_err();

       }
    }
    public function addpjlx()
    {

            $id = request()->param("id");
            if ($id) {
                $info = model("pjlx")->where("id", $id)->find();
            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();

    }
    public function addpaymenttypes(){
        if (empty(request()->get("id"))) {
            $data['name'] = request()->get("name");
            $data['type'] =request()->get("type1");
            $data['class'] = request()->get("class");
            if(!model("paymentclass")->where("name",$data['class'])->find()){
                $data1['name']=$data['class'];
                $data1['companyid'] = $this->getCompanyId();
                $data1['add_name'] = $this->getAccount()['name'];
                $data1['add_id'] = $this->getAccountId();
                model("paymentclass")->save($data1);
            }
            $data['sort'] = request()->get("sort");
            $data['companyid'] = $this->getCompanyId();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            $result = model("paymenttype")->save($data);
        } else {
            $id = request()->get("id");
            $data['name'] = request()->get("name");
            $data['type'] = request()->get("type");
            $data['class'] = request()->get("class");
            $data['sort'] = request()->get("sort");
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            $result = model("paymenttype")->where("id", $id)->update($data);
        }
        if ($result) {
            return json_suc();
        } else {
            return json_err();
        }
    }
    public function addpaymenttype()
    {

            $id = request()->param("id");
            $type = request()->param("type");
            if ($id) {
                $info = model("paymenttype")->where(array("id"=>$id))->find();
            } else {
                $info = null;
            }
            $this->assign("data", $info);
            $this->assign("type", $type);
            return view();

    }
    public function paymenttype(){
        $type=request()->param("type");
        $list = model("paymenttype")->where(array("companyid"=>$this->getCompanyId(),'type'=>$type))->select();
        $this->assign("list", $list);
        $this->assign("type", $type);
        return view();
    }
    public function paymentclass(){
        $list['value']=model("paymentclass")->where("companyid",$this->getCompanyId())->field("id,name")->select();
        return json($list);
    }
}