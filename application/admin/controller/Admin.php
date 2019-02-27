<?php

namespace app\admin\controller;

use think\Db;
use think\Exception;

class Admin extends Right
{
    /**
     * 管理员管理
     */
    public function index()
    {
        $admins = Db::table("admin t")
            ->join("authgroupaccess a", "t.id = a.uid")
            ->join("authgroup g", "a.group_id = g.id")
            ->field("t.id,t.account,t.name,t.isDisable,t.createtime,CASE WHEN t.id = 1 THEN '超级管理员' ELSE g.title END groupName")
            ->paginate($this->pageSize);

        $pagelist = $admins->render();
        $this->assign([
            "admins" => $admins,
            "pagelist" => $pagelist
        ]);
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
            $admin = Db::table("admin")
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
            if (empty($name)) {
                return json_err(-1, "请填写昵称！");
            }

            $data = [
                "account"    => $account,
                "password"   => md5($password),
                "name"       => $name,
                "isDisable"  => 2,
                "createtime" => now_datetime()
            ];

            $group_id = (int)input("group_id");

            // 添加管理员
            try {
                Db::startTrans();
                $result = Db::table("admin")->insert($data);
                $ret = Db::table("authgroupaccess")->insert(["uid"=>Db::table('admin')->getLastInsID(), "group_id"=>$group_id]);

                if ($result && $ret) {
                    Db::commit();
                    return json_suc(0, "添加成功！");
                } else {
                    Db::rollback();
                    return json_err(-1, "添加失败！");
                }
            } catch (Exception $e) {
                Db::rollback();
                return json_err(-1, $e->getMessage());
            }
        } else {
            $roles = Db::table("authgroup")->field("id,title")->select();
            $roleArr = [""=>""];
            if ($roles) {
                foreach ($roles as $k=>$v) {
                    $roleArr[$v['id']] = $v['title'];
                }
            } else {
                $this->error("请添加角色！");
            }


            $this->assign([
                "lists" => [
                    "rolelist" => $roleArr
                ]
            ]);
            return $this->fetch();
        }
    }

    /**
     * 编辑管理员信息
     */
    public function edit()
    {
        if (request()->isAjax()) {
            $id = (int)input("id");
            //账号
            $account = trim(input("account"));
            //判断此账户是否存在
            $isExists = Db::table("admin")->query("SELECT
                                                        a.*, 
                                                        agc.group_id,
                                                        g.title
                                                    FROM
                                                        admin a
                                                    LEFT JOIN authgroupaccess agc ON a.id = agc.uid
                                                    LEFT JOIN authgroup g ON agc.group_id = g.id
                                                    WHERE a.id!={$id} AND a.account='{$account}'");

            if ($isExists) {
                return json_err(-1, "该账户已存在！");
            }

            $admin = Db::table("admin")->query("SELECT
            
                                                        a.*, 
                                                        agc.group_id,
                                                        g.title
                                                    FROM
                                                        admin a
                                                    LEFT JOIN authgroupaccess agc ON a.id = agc.uid
                                                    LEFT JOIN authgroup g ON agc.group_id = g.id
                                                    WHERE a.id={$id}");
            $admin = $admin[0];

            $name = trim(input("name"));
            if (empty($name)) {
                return json_err(-1, "请填写昵称！");
            }

            $data = [
                "account"    => $account,
                "name"       => $name,
            ];

            $password = trim(input("password"));
            if (!empty($password)) {
                $data['password'] = md5($password);
            }

            $group_id = (int)input("group_id");

            try {
                Db::startTrans();

                $updateAdmin = Db::table("admin")->where("id", $id)->update($data);

                if ($group_id) {
                    if ($admin['group_id']) {
                        $updateGroup = Db::table("authgroupaccess")->where("uid", $admin['id'])->update(["group_id"=>$group_id]);
                    } else {
                        $updateGroup = Db::table("authgroupaccess")->insert(["uid"=>$admin['id'],"group_id"=>$group_id]);
                    }
                } else {
                    if ($admin['group_id']) {
                        $updateGroup = Db::table("authgroupaccess")->where("uid", $admin['id'])->delete();
                    }
                }

                if (false !== $updateAdmin && false !== $updateGroup) {
                    Db::commit();
                    return json_suc();
                } else {
                    Db::rollback();
                    return json_err();
                }
            } catch (Exception $e) {
                Db::rollback();
                return json_err(-1, $e->getMessage());
            }

        } else {
            $id = (int)input("id");
            $info = Db::table("admin")->query("SELECT
                                                        a.*, 
                                                        agc.group_id,
                                                        g.title
                                                    FROM
                                                        admin a
                                                    LEFT JOIN authgroupaccess agc ON a.id = agc.uid
                                                    LEFT JOIN authgroup g ON agc.group_id = g.id
                                                    WHERE a.id={$id}");

            // 角色
            $roles = Db::table("authgroup")->field("id,title")->select();
            $roleArr = [""=>""];
            if ($roles) {
                foreach ($roles as $k=>$v) {
                    $roleArr[$v['id']] = $v['title'];
                }
            } else {
                $this->error("请添加角色！");
            }


            $this->assign([
                "data" => $info[0],
                "lists" => [
                    "rolelist" => $roleArr
                ]
            ]);
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
            $ret_del_user     = Db::table("admin")->where("id", $id)->delete();
            $ret_del_relation = Db::table("authgroupaccess")->where("uid", $id)->delete();
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

            $ret = Db::table("admin")->where("id", $id)->update(["isDisable"=>2]);
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

            $ret = Db::table("admin")->where("id", $id)->update(["isDisable"=>1]);
            if (false !== $ret) {
                return json_suc();
            } else {
                return json_err();
            }
        }
    }
}