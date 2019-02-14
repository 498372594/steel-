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
            $account = input("account");

            //判断此账户是否存在
            $admin = Db::name("admin")
                ->where("account", $account)
                ->find();

            if ($admin) {
                $this->ajaxError("该账户已存在！");
            }

            // 登录账户
            $data = [];
            $data["account"] = $account;
            // 登录密码
            $data["password"] = md5(input("password"));
            // 昵称
            $data["name"] = trim(input("name"));
            // 是否禁用
            $data['isDisable'] = 2;
            // 创建时间
            $data['createTime'] = now_datetime();

            // 添加管理员
//            $result = M("admin")->add($data);
//
//            if ($result) {
//                $this->ajaxSuccess("添加成功");
//            } else {
//                $this->ajaxError("添加失败！");
//            }
        } else {
            $lists = array(
                "roleList" => $this->getRoleList()
            );
            $this->assign("lists", $lists);
            $this->assign("role", $this->getAccount()['role']);
            $this->assign("rolePath", $this->getAccount()['rolePath']);
            $this->display();
        }
    }

    /**
     * 编辑管理员信息
     */
    public function edit()
    {

    }

    /**
     * 删除管理员
     */
    public function delete()
    {

    }

    /**
     * 放行
     */
    public function enable()
    {
        
    }

    /**
     * 禁用
     */
    public function disable()
    {
        
    }
}