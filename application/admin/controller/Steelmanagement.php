<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use think\Db;
use think\Exception;
use think\Session;

class Steelmanagement extends Right
{
    use Backend;
    public function index(){
        return view();
    }
    public function productname(){
        $list=model("productname")->where("companyid",Session::get("uinfo", "admin")['companyid'])->select();
        $this->assign("list",$list);
        return view();
    }
    public function addproductname(){
    if(request()->post()){
        if(empty(request()->post("id"))){
            $data['name']=request()->post("name");
            $data['sort']=request()->post("sort");
            $data['zjm']=request()->post("zjm");
            $data['companyid']=Session::get("uinfo", "admin")['companyid'];
            $data['add_name']=Session::get("uinfo", "admin")['name'];
            $data['add_id']=Session::get("uid", "admin");
            $result=model("productname")->save($data);
        }else{
            $id=request()->post("id");
            $data['name']=request()->post("name");
            $data['sort']=request()->post("sort");
            $data['zjm']=request()->post("zjm");
            $data['add_name']=Session::get("uinfo", "admin")['name'];
            $data['add_id']=Session::get("uid", "admin");
            $result=model("productname")->where("id",$id)->update($data);
        }
        if ($result) {
            return json_suc();
        } else {
            return json_err();
        }
    }else{
        $id=request()->param("id");
        if($id){
            $info=model("productname")->where("id",$id)->find();

        }else{
            $info=null;
        }
        $this->assign("data",$info);
        return view();
    }
    }
    public function deleteproductname(){
        $pk = model("productname")->getPk();
        $ids = $this->request->param($pk);
        $where[$pk] = ["in", $ids];
        if (false ===model("productname")->where($where)->delete()) {
            return json_err();
        } else {
            return json_suc();
        }
    }

    public function texture(){
        $list=model("texture")->where("companyid",Session::get("uinfo", "admin")['companyid'])->select();
        $this->assign("list",$list);
        return view();
    }
    public function addtexture(){
        if(request()->post()){
            if(empty(request()->post("id"))){
                $data['texturename']=request()->post("texturename");
                $data['sort']=request()->post("sort");
                $data['zjm']=request()->post("zjm");
                $data['companyid']=Session::get("uinfo", "admin")['companyid'];
                $data['add_name']=Session::get("uinfo", "admin")['name'];
                $data['add_id']=Session::get("uid", "admin");
                $result=model("texture")->save($data);
            }else{
                $id=request()->post("id");
                $data['texturename']=request()->post("texturename");
                $data['sort']=request()->post("sort");
                $data['zjm']=request()->post("zjm");
                $data['add_name']=Session::get("uinfo", "admin")['name'];
                $data['add_id']=Session::get("uid", "admin");
                $result=model("texture")->where("id",$id)->update($data);
            }
            if ($result) {
                return json_suc();
            } else {
                return json_err();
            }
        }else{
            $id=request()->param("id");
            if($id){
                $info=model("texture")->where("id",$id)->find();

            }else{
                $info=null;
            }
            $this->assign("data",$info);
            return view();
        }
    }
    public function deletetexture(){
        $pk = model("texture")->getPk();
        $ids = $this->request->param($pk);
        $where[$pk] = ["in", $ids];
        if (false ===model("texture")->where($where)->delete()) {
            return json_err();
        } else {
            return json_suc();
        }
    }

public function originarea(){
    $list=model("originarea")->where("companyid",Session::get("uinfo", "admin")['companyid'])->select();
    $this->assign("list",$list);
    return view();
}
public function addoriginarea(){
    if(request()->post()){
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

    public function specification(){
        $list=model("specification")->where("companyid",Session::get("uinfo", "admin")['companyid'])->select();
        $this->assign("list",$list);
        return view();
    }
    public function addspecification(){
        if(request()->post()){
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
    public function storage(){
        $list=model("storage")->where("companyid",Session::get("uinfo", "admin")['companyid'])->select();
        $this->assign("list",$list);
        return view();
    }
    public function addstorage(){
        if(request()->post()){
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
    public function unit(){
        $list=model("unit")->where("companyid",Session::get("uinfo", "admin")['companyid'])->select();
        $this->assign("list",$list);
        return view();
    }
    public function addunit(){
        if(request()->post()){
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
    public function classname(){
        $list=model("classname")->where("companyid",Session::get("uinfo", "admin")['companyid'])->select();
        $this->assign("list",$list);
        return view();
    }
    public function addclassname(){
        if(request()->post()){
            if (empty(request()->post("id"))) {
                $data['classname'] = request()->post("classname");
                $data['sort'] = request()->post("sort");
                $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
                $data['add_name'] = Session::get("uinfo", "admin")['name'];
                $data['add_id'] = Session::get("uid", "admin");
                $result = model("classname")->save($data);
            } else {
                $id = request()->post("id");
                $data['classname'] = request()->post("classname");
                $data['sort'] = request()->post("sort");
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
}