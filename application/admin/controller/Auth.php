<?php
namespace app\admin\controller;

use think\Db;

class Auth extends Right
{
    /**
     * 角色组
     */
    public function group()
    {
        $groups = Db::name("authgroup")->select();

        $this->assign("groups", $groups);
        return $this->fetch('auth/group/group');
    }

    /**
     * 添加角色
     */
    public function add()
    {
        if (request()->isAjax()) {
            $title = trim(input("title"));
            if (empty($title)) {
                return json_err(-1, "请填写角色名称！");
            }

            $data = [
                "title" => $title
            ];
            $ret = Db::name("authgroup")->insert($data);

            if ($ret) {
                return json_suc();
            } else {
                return json_err();
            }
        } else {
            return $this->fetch('auth/group/add');
        }
    }

    /**
     * 编辑角色
     */
    public function edit()
    {
        if (request()->isAjax()) {

        } else {
            return $this->fetch('auth/group/edit');
        }
    }

    /**
     * 删除角色
     */
    public function del()
    {

    }

    /**
     * 权限规则列表
     */
    public function rule()
    {
        $rules = Db::name("authrule")->select();
        $formatRule = convert_tree($rules);

        $this->assign("rules", $formatRule);
        return $this->fetch('auth/auth/rule');
    }

    /**
     * 添加权限
     */
    public function addRule()
    {
       if (request()->isAjax()) {

       } else {
           return $this->fetch('auth/auth/add');
       }
    }

    /**
     * 更改权限
     */
    public function editRule()
    {
        if (request()->isAjax()) {

        } else {
            return $this->fetch('auth/auth/edit');
        }
    }

    /**
     * 删除权限
     */
    public function delRule()
    {
        
    }
}