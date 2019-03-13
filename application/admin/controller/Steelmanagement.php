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
        $list = model("productname")->where("companyid", Session::get("uinfo", "admin")['companyid'])->select();
        $this->assign("list", $list);
        return view();
    }

    public function addproductname()
    {
        if (request()->post()) {
            if (empty(request()->post("id"))) {
                $data['name'] = request()->post("name");
                $data['sort'] = request()->post("sort");
                $data['zjm'] = request()->post("zjm");
                $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("productname")->save($data);
            } else {
                $id = request()->post("id");
                $data['name'] = request()->post("name");
                $data['sort'] = request()->post("sort");
                $data['zjm'] = request()->post("zjm");
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("productname")->where("id", $id)->update($data);
            }
            if ($result) {
                return json_suc();
            } else {
                return json_err();
            }
        } else {
            $id = request()->param("id");
            if ($id) {
                $info = model("productname")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();
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
        $list = model("texture")->where("companyid", Session::get("uinfo", "admin")['companyid'])->select();
        $this->assign("list", $list);
        return view();
    }

    public function addtexture()
    {
        if (request()->post()) {
            if (empty(request()->post("id"))) {
                $data['texturename'] = request()->post("texturename");
                $data['sort'] = request()->post("sort");
                $data['zjm'] = request()->post("zjm");
                $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("texture")->save($data);
            } else {
                $id = request()->post("id");
                $data['texturename'] = request()->post("texturename");
                $data['sort'] = request()->post("sort");
                $data['zjm'] = request()->post("zjm");
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("texture")->where("id", $id)->update($data);
            }
            if ($result) {
                return json_suc();
            } else {
                return json_err();
            }
        } else {
            $id = request()->param("id");
            if ($id) {
                $info = model("texture")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();
        }
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
        $list = model("originarea")->where("companyid", Session::get("uinfo", "admin")['companyid'])->select();
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
                $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("originarea")->save($data);
            } else {
                $id = request()->post("id");
                $data['originarea'] = request()->post("originarea");
                $data['sort'] = request()->post("sort");
                $data['zjm'] = request()->post("zjm");
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
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
        $list = model("specification")->where("companyid", Session::get("uinfo", "admin")['companyid'])->select();
        $this->assign("list", $list);
        return view();
    }

    public function addspecification()
    {
        if (request()->post()) {
            if (empty(request()->post("id"))) {
                $data['specification'] = request()->post("specification");
                $data['sort'] = request()->post("sort");
                $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("specification")->save($data);
            } else {
                $id = request()->post("id");
                $data['specification'] = request()->post("specification");
                $data['sort'] = request()->post("sort");
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("specification")->where("id", $id)->update($data);
            }
            if ($result) {
                return json_suc();
            } else {
                return json_err();
            }
        } else {
            $id = request()->param("id");
            if ($id) {
                $info = model("specification")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();
        }
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
        $list = model("storage")->where("companyid", Session::get("uinfo", "admin")['companyid'])->select();
        $this->assign("list", $list);
        return view();
    }

    public function addstorage()
    {
        if (request()->post()) {
            if (empty(request()->post("id"))) {
                $data['storage'] = request()->post("storage");
                $data['sort'] = request()->post("sort");
                $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("storage")->save($data);
                $data['address'] = request()->post("address");
                $data['contacts'] = request()->post("contacts");
                $data['phone'] = request()->post("phone");
                $data['fax'] = request()->post("fax");
            } else {
                $id = request()->post("id");
                $data['storage'] = request()->post("storage");
                $data['sort'] = request()->post("sort");
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $data['address'] = request()->post("address");
                $data['contacts'] = request()->post("contacts");
                $data['phone'] = request()->post("phone");
                $data['fax'] = request()->post("fax");
                $result = model("storage")->where("id", $id)->update($data);
            }
            if ($result) {
                return json_suc();
            } else {
                return json_err();
            }
        } else {
            $id = request()->param("id");
            if ($id) {
                $info = model("storage")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();
        }
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
        $list = model("unit")->where("companyid", Session::get("uinfo", "admin")['companyid'])->select();
        $this->assign("list", $list);
        return view();
    }

    public function addunit()
    {
        if (request()->post()) {
            if (empty(request()->post("id"))) {
                $data['unit'] = request()->post("unit");
                $data['sort'] = request()->post("sort");
                $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("unit")->save($data);
            } else {
                $id = request()->post("id");
                $data['unit'] = request()->post("unit");
                $data['sort'] = request()->post("sort");
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("unit")->where("id", $id)->update($data);
            }
            if ($result) {
                return json_suc();
            } else {
                return json_err();
            }
        } else {
            $id = request()->param("id");
            if ($id) {
                $info = model("unit")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();
        }
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
        $list = model("classname")->where("companyid", Session::get("uinfo", "admin")['companyid'])->select();
        $this->assign("list", $list);
        return view();
    }

    public function addclassname()
    {
        if (request()->post()) {
            $data=request()->post();
            if (empty(request()->post("id"))) {
//                $data['classname'] = request()->post("classname");
//                $data['sort'] = request()->post("sort");
                $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("classname")->save($data);
            } else {
                $id = request()->post("id");
//                $data['classname'] = request()->post("classname");
//                $data['sort'] = request()->post("sort");
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("classname")->where("id", $id)->update($data);
            }
            if ($result) {
                return json_suc();
            } else {
                return json_err();
            }
        } else {
            $id = request()->param("id");
            if ($id) {
                $info = model("classname")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();
        }
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
//        $list['value']=model("classname")->where("companyid",Session::get("uinfo", "admin")['companyid'])->field("classname")->select();
//        return json($list);
//    }
    public function product()
    {
        $list = model("product")->alias("a")->join("classname b", "a.classid=b.id", "left")->field("a.*,b.classname")->where("a.companyid", Session::get("uinfo", "admin")['companyid'])->select();
        $this->assign("list", $list);
        return view();
    }

    public function addproduct()
    {
        if (request()->post()) {
//            dump(request()->post());die;
            if (empty(request()->post("id"))) {
                $data['classid'] = request()->post("classid");
                $data['productname'] = request()->post("productname");
                if (!model("productname")->where("name", $data['productname'])->find()) {
                    $class['name'] = $data['productname'];
                    $class['zjm'] = request()->post("zjm");
                    $class['companyid'] = Session::get("uinfo", "admin")['companyid'];
                    $class['add_name'] = Session::get("uinfo", "admin")['name'];
                    $class['add_id'] = Session::get("uid", "admin");
                    model("productname")->save($class);
                }
                $data['texture'] = request()->post("texture");
                if (!model("texture")->where("texturename", $data['texture'])->find()) {
                    $texture['texturename'] = $data['texture'];
                    $texture['companyid'] = Session::get("uinfo", "admin")['companyid'];
                    $texture['add_name'] = Session::get("uinfo", "admin")['name'];
                    $texture['add_id'] = Session::get("uid", "admin");
                    model("texture")->save($texture);
                }
                $data['originarea'] = request()->post("originarea");
                if (!model("originarea")->where("originarea", $data['originarea'])->find()) {
                    $orginarea['originarea'] = $data['originarea'];
                    $orginarea['companyid'] = Session::get("uinfo", "admin")['companyid'];
                    $orginarea['add_name'] = Session::get("uinfo", "admin")['name'];
                    $orginarea['add_id'] = Session::get("uid", "admin");
                    model("originarea")->save($orginarea);
                }
                $data['specification'] = request()->post("specification");
                if (!model("specification")->where("specification", $data['specification'])->find()) {
                    $specification['specification'] = $data['specification'];
                    $specification['companyid'] = Session::get("uinfo", "admin")['companyid'];
                    $specification['add_name'] = Session::get("uinfo", "admin")['name'];
                    $specification['add_id'] = Session::get("uid", "admin");
                    model("specification")->save($specification);
                }
                $data['piece_weight'] = request()->post("piece_weight");
                $data['length'] = request()->post("length");
                $data['width'] = request()->post("width");
                $data['unit'] = request()->post("unit");
                $data['number_alarm_val'] = request()->post("number_alarm_val");
                $data['weight_alarm_val'] = request()->post("weight_alarm_val");
                $data['pack_no'] = request()->post("pack_no");
                $data['sort'] = request()->post("sort");
                $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("product")->save($data);
            } else {
                $id = request()->post("id");
                $data['classid'] = request()->post("classid");
                $data['productname'] = request()->post("productname");
                $data['texture'] = request()->post("texture");
                $data['originarea'] = request()->post("originarea");
                $data['specification'] = request()->post("specification");
                $data['piece_weight'] = request()->post("piece_weight");
                $data['length'] = request()->post("length");
                $data['width'] = request()->post("width");
                $data['number_alarm_val'] = request()->post("number_alarm_val");
                $data['weight_alarm_val'] = request()->post("weight_alarm_val");
                $data['unit'] = request()->post("unit");
                $data['pack_no'] = request()->post("pack_no");
                $data['sort'] = request()->post("sort");
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("product")->where("id", $id)->update($data);
            }
            if ($result) {
                return json_suc();
            } else {
                return json_err();
            }
        } else {
            $id = request()->param("id");
            $classlist = model("classname")->where("companyid", Session::get("uinfo", "admin")['companyid'])->field("id,classname")->select();
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
    }

    public function jsfs()
    {
        $list = model("jsfs")->where("companyid", Session::get("uinfo", "admin")['companyid'])->select();
        $this->assign("list", $list);
        return view();
    }

    public function addjsfs()
    {
        if (request()->post()) {
            if (empty(request()->post("id"))) {
                $data['jsfs'] = request()->post("jsfs");
                $data['jjlx'] = request()->post("jjlx");
                $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("jsfs")->save($data);
            } else {
                $id = request()->post("id");
                $data['jsfs'] = request()->post("jsfs");
                $data['sort'] = request()->post("sort");
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("jsfs")->where("id", $id)->update($data);
            }
            if ($result) {
                return json_suc();
            } else {
                return json_err();
            }
        } else {
            $id = request()->param("id");
            if ($id) {
                $info = model("jsfs")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();
        }
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
        $list = model("custom")->where("companyid", Session::get("uinfo", "admin")['companyid'])->select();
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
                $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
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
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
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
        $list = model("transportation")->where("companyid", Session::get("uinfo", "admin")['companyid'])->select();
        $this->assign("list", $list);
        return view();
    }

    public function addtransportation()
    {
        if (request()->post()) {
            if (empty(request()->post("id"))) {
                $data['transportation'] = request()->post("transportation");
                $data['sort'] = request()->post("sort");
                $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("transportation")->save($data);
            } else {
                $id = request()->post("id");
                $data['transportation'] = request()->post("transportation");
                $data['sort'] = request()->post("sort");
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
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

    public function addbank()
    {
        if (request()->post()) {
            if (empty(request()->post("id"))) {
                $data['zjm'] = request()->post("zjm");
                $data['name'] = request()->post("name");
                $data['banktype_id'] = request()->post("banktype_id");
                $data['kaihuhang'] = request()->post("kaihuhang");
                $data['bank'] = request()->post("bank");
                $data['sort'] = request()->post("sort");
                $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("bank")->save($data);
            } else {
                $id = request()->post("id");
                $data['zjm'] = request()->post("zjm");
                $data['name'] = request()->post("name");
                $data['banktype_id'] = request()->post("banktype_id");
                $data['kaihuhang'] = request()->post("kaihuhang");
                $data['bank'] = request()->post("bank");
                $data['sort'] = request()->post("sort");
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("bank")->where("id", $id)->update($data);
            }
            if ($result) {
                return json_suc();
            } else {
                return json_err();
            }
        } else {
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
        $list = model("$model")->where("companyid", Session::get("uinfo", "admin")['companyid'])->select();
        $this->assign("list", $list);
        return view("$model");
    }
    public function addfaxi()
    {
        if (request()->post()) {
            if (empty(request()->post("id"))) {
                $data['fxfs'] = request()->post("fxfs");
                $data['fxdx'] = request()->post("fxdx");
                $data['fxgz'] = request()->post("fxgz");
                $data['fxxz'] = request()->post("fxxz");
                $data['qjts'] = request()->post("qjts");
                $data['jsgs'] = request()->post("jsgs");
                $data['zjts'] = request()->post("zjts");
                $data['description'] = request()->post("description");
                $data['sort'] = request()->post("sort");
                $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("faxi")->save($data);
            } else {
                $id = request()->post("id");
                $data['fxfs'] = request()->post("fxfs");
                $data['fxdx'] = request()->post("fxdx");
                $data['fxgz'] = request()->post("fxgz");
                $data['fxxz'] = request()->post("fxxz");
                $data['qjts'] = request()->post("qjts");
                $data['jsgs'] = request()->post("jsgs");
                $data['zjts'] = request()->post("zjts");
                $data['description'] = request()->post("description");
                $data['sort'] = request()->post("sort");
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
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
                $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
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
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
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
    public function addjiesuanfangshi()
    {
        if (request()->post()) {
            if (empty(request()->post("id"))) {
                $data['jiesuanfangshi'] = request()->post("jiesuanfangshi");
                $data['sort'] = request()->post("sort");
                $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("jiesuanfangshi")->save($data);
            } else {
                $id = request()->post("id");
                $data['jiesuanfangshi'] = request()->post("jiesuanfangshi");
                $data['sort'] = request()->post("sort");
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("jiesuanfangshi")->where("id", $id)->update($data);
            }
            if ($result) {
                return json_suc();
            } else {
                return json_err();
            }
        } else {
            $id = request()->param("id");
            if ($id) {
                $info = model("jiesuanfangshi")->where("id", $id)->find();

            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();
        }
    }
    public function addpjlx()
    {
        if (request()->post()) {
            if (empty(request()->post("id"))) {
                $data['pjlx'] = request()->post("pjlx");
                $data['tax_rate'] = request()->post("tax_rate");
                $data['second_name'] = request()->post("second_name");
                $data['sort'] = request()->post("sort");
                $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("pjlx")->save($data);
            } else {
                $id = request()->post("id");
                $data['pjlx'] = request()->post("pjlx");
                $data['tax_rate'] = request()->post("tax_rate");
                $data['second_name'] = request()->post("second_name");
                $data['sort'] = request()->post("sort");
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("pjlx")->where("id", $id)->update($data);
            }
            if ($result) {
                return json_suc();
            } else {
                return json_err();
            }
        } else {
            $id = request()->param("id");
            if ($id) {
                $info = model("pjlx")->where("id", $id)->find();
            } else {
                $info = null;
            }
            $this->assign("data", $info);
            return view();
        }
    }
    public function addpaymenttype()
    {
        if (request()->post()) {
            if (empty(request()->post("id"))) {
                $data['name'] = request()->post("name");
                $data['type'] =request()->post("type1");
                $data['class'] = request()->post("class");
                if(!model("paymentclass")->where("name",$data['class'])->find()){
                    $data1['name']=$data['class'];
                    $data1['companyid'] = Session::get("uinfo", "admin")['companyid'];
                    $data1['add_name'] = Session::get("uinfo", "admin")['name'];
                    $data1['add_id'] = Session::get("uid", "admin");
                    model("paymentclass")->save($data1);
                }
                $data['sort'] = request()->post("sort");
                $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("paymenttype")->save($data);
            } else {
                $id = request()->post("id");
                $data['name'] = request()->post("name");
                $data['type'] = request()->post("type");
                $data['class'] = request()->post("class");
                $data['sort'] = request()->post("sort");
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("paymenttype")->where("id", $id)->update($data);
            }
            if ($result) {
                return json_suc();
            } else {
                return json_err();
            }
        } else {
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
    }
    public function paymenttype(){
        $type=request()->param("type");
        $list = model("paymenttype")->where(array("companyid"=>Session::get("uinfo", "admin")['companyid'],'type'=>$type))->select();
        $this->assign("list", $list);
        $this->assign("type", $type);
        return view();
    }
    public function paymentclass(){
        $list['value']=model("paymentclass")->where("companyid",Session::get("uinfo", "admin")['companyid'])->field("id,name")->select();
        return json($list);
    }
}