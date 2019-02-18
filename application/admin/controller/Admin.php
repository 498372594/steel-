<?php

namespace app\admin\controller;

use think\Db;

class Admin extends Right
{
    /**
     * 管理员管理
     */
    public function index()
    {
        $admins = Db::query("SELECT
                                    t.id,
                                    t.account,
                                    t.name,
                                    t.isDisable,
                                    t.createTime,
                                    CASE WHEN t.id = 1 
                                    THEN '超级管理员'
                                    ELSE g.title
                                    END groupName
                                FROM
                                    admin t
                                LEFT JOIN authgroupaccess a ON t.id = a.uid
                                LEFT JOIN authgroup g ON a.group_id = g.id");
        $this->assign("admins", $admins);
        return $this->fetch();
    }

    /**
     * 添加管理员
     */
    public function add()
    {
        if (request()->isPost()) {
            //账号
            $account = trim(input("account"));
            //判断此账户是否存在
            $admin = Db::name("admin")
                ->where("account", $account)
                ->find();
            if ($admin) {
                return json_err(-1, "该账户已存在！");
            }

            $password = trim(input("password"));
            if (empty($password)) {
                return json_err(-1, "请填写密码！");
            }
            $name = trim(input("name"));
            if (empty($password)) {
                return json_err(-1, "请填写昵称！");
            }

            $data = [
                "account"    => $account,
                "password"   => md5($password),
                "name"       => $name,
                "isDisable"  => 2,
                "createTime" => now_datetime()
            ];

            // 添加管理员
            $result = Db::name("admin")->insert($data);
            if ($result) {
                return json_suc(0, "添加成功！");
            } else {
                return json_err(-1, "添加失败！");
            }
        } else {
            return $this->fetch();
        }
    }

    /**
     * 编辑管理员信息
     */
    public function edit()
    {
        if (request()->isAjax()) {

            $data = [
                "title"
            ];
        } else {
            return $this->fetch();
        }
    }

    /**
     * 删除管理员
     */
    public function delete()
    {
        if (request()->isPost()) {
            $id = input('id');

            Db::startTrans();
            $ret_del_user     = Db::name("admin")->where("id", $id)->delete();
            $ret_del_relation = Db::name("authgroupaccess")->where("uid", $id)->delete();
            if (false !== $ret_del_user && false !== $ret_del_relation) {
                Db::commit();
                return json_suc();
            } else {
                Db::rollback();
                return json_err();
            }
        }
    }

    /**
     * 放行
     */
    public function enable()
    {
        if (request()->isPost()) {
            $id = input('id');

            $ret = Db::name("admin")->where("id", $id)->update(["isDisable"=>2]);
            if (false !== $ret) {
                return json_suc();
            } else {
                return json_err();
            }
        }
    }

    /**
     * 禁用
     */
    public function disable()
    {
        if (request()->isPost()) {
            $id = input('id');

            $ret = Db::name("admin")->where("id", $id)->update(["isDisable"=>1]);
            if (false !== $ret) {
                return json_suc();
            } else {
                return json_err();
            }
        }
    }
}