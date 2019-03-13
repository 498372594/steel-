<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use think\Db;
use think\Exception;
use think\Session;
use app\admin\library\traits\Tree;

class Steelmanage extends Right
{
    use Backend;

    /**大类列表接口
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function classname()
    {
        $list = model("classname")->where("companyid", Session::get("uinfo", "admin")['companyid'])->paginate(10);
        return returnRes($list->toArray()['data'], '没有大类数据，请添加后重试', $list);
    }

    public function addclassname()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
            $data['add_name'] = Session::get("uinfo", "admin")['name'];
            $data['add_id'] = Session::get("uid", "admin");
            if (empty(request()->post("id"))) {
                $result = model("classname")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $id = request()->post("id");
                $result = model("classname")->where("id", $id)->allowField(true)->update($data);
                return returnRes($result, '修改失败');
            }

        } else {
            return returnRes("", '参数错误');
        }
    }

    /**删除
     * @return \think\response\Json
     */
    public function delete()
    {
        $model = request()->param('tablename');
        $pk = model("$model")->getPk();
        $ids = $this->request->param($pk);
        $where[$pk] = ["in", $ids];
        $result = model("$model")->where($where)->delete();
        return returnRes($result, '删除失败');
    }

    public function productname()
    {
        if (request()->param("id")) {
            $ids = request()->param("id");
            $where = array(
                'classid' => ['in', $ids],
                'companyid' => Session::get("uinfo", "admin")['companyid']
            );
        } else {
            $where = array(
                'companyid' => Session::get("uinfo", "admin")['companyid']
            );
        }
        $list = model("productname")->where($where)->paginate(10);
        return returnRes($list->toArray()['data'], '没有大类数据，请添加后重试', $list);
    }

    /**添加修改大类
     * @return \think\response\Json
     */
    public function addproductname()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
            $data['add_name'] = Session::get("uinfo", "admin")['name'];
            $data['add_id'] = Session::get("uid", "admin");
            if (empty(request()->post("id"))) {
                $result = model("productname")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $id = request()->post("id");
                $result = model("productname")->allowField(true)->update($data);
                return returnRes($result, '修改失败');
            }
        } else {
            $id = request()->param("id");
            $data['info'] = model("productname")->where("id", $id)->find();
            $data["unit"] = model("unit")->where("companyid", Session::get("uinfo", "admin")['companyid'])->field("id,unit")->select();
            return returnRes($data, '无相关数据', $data);
        }
    }

    /** 规格列表
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function specification()
    {
        if (request()->param("productname_id")) {
            $ids = request()->param("productname_id");
            $where = array(
                'productname_id' => ['in', $ids],
                'companyid' => Session::get("uinfo", "admin")['companyid']
            );
        } else {
            $where = array(
                'companyid' => Session::get("uinfo", "admin")['companyid']
            );
        }
        $list = model("specification")->where($where)->paginate(10);
        return returnRes($list->toArray()['data'], '没有大类数据，请添加后重试', $list);
    }

    /**添加修改规格
     * @return \think\response\Json
     */
    public function addspecification()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['companyid'] = Session::get("uinfo", "admin")['companyid'];
            $data['add_name'] = Session::get("uinfo", "admin")['name'];
            $data['add_id'] = Session::get("uid", "admin");
            if (empty(request()->post("id"))) {
                $result = model("specification")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $id = request()->post("id");
                $result = model("specification")->allowField(true)->update($data);
                return returnRes($result, '修改失败');
            }
        } else {
            $id = request()->param("id");
            $data['info'] = model("specification")->where("id", $id)->find();
            $data["productlist"] = $this->getproductlist();
            $data["originarealist"] = model("originarea")->where("companyid", Session::get("uinfo", "admin")['companyid'])->field("id,originarea")->select();
            $data["texturelist"] = model("texture")->where("companyid", Session::get("uinfo", "admin")['companyid'])->field("id,texturename")->select();
            return returnRes($data, '无相关数据', $data);
        }
    }

    /**获取产品列表附带分类
     * @return \think\response\Json
     */
    public function getproductlist()
    {
        $list = db("classname")->field("pid,id,classname")->where("companyid", Session::get("uinfo", "admin")['companyid'])->select();
        $menutree = new Tree($list);
        $menulist = $menutree->leaf();
//        dump($menulist);
        $digui = $this->productnamedigui($menulist);
        return $digui;
    }

    public function productnamedigui($arr)
    {
        foreach ($arr as $k => $v) {

            $arr[$k]['productname'] = db("productname")->where("companyid", Session::get("uinfo", "admin")['companyid'])->where("classid", $v["id"])->field("id,name")->select();
            if (array_key_exists('child', $v)) {
                $v = $this->productnamedigui($v["child"]);
                $arr[$k]["child"] = $v;
            }
        }
        return $arr;
    }
}