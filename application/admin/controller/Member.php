<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use think\Db;
use think\Exception;

class Member extends Right
{
    use Backend;

    /**
     * 列表附加数据
     */
    protected function indexAttach()
    {
        $this->assign("lists", [
            "isDisable" => getDropdownList("isDisable"),
            "pageSize"  => getDropdownList("pageSize")
        ]);
    }

    /**
     * 添加 验证前处理
     */
    protected function afterAddValidate($data)
    {
        if ($data['parent']) {
            $parentInfo = Db::table("member")->where("account", $data['parent'])->find();
            if (!$parentInfo) {
                throw new Exception("推荐人不存在！");
            }

            unset($data['parent']);
            $data['parentId'] = $parentInfo['id'];
        }

        if ($data["password"]) {
            $data['password'] = md5($data["password"]);
        }

        $data['createTime'] = now_datetime();
        return $data;
    }

    /**
     * 编辑附加数据
     */
    protected function editAttach()
    {
        $id = input("id");
        if (empty($id))  throw new Exception("未知的id！");

        $data = Db::table("member m")
            ->field("m.id,m.nickName,m.account,p.account parent")
            ->join("member p", "m.parentId=p.id")
            ->where("m.id", $id)
            ->find();
        $this->assign("data", $data);
    }

    /**
     * 编辑 验证前处理
     */
    protected function beforeEditValidate($data)
    {
        if ($data['parent']) {
            $parentInfo = Db::table("member")->where("account", $data['parent'])->find();
            if (!$parentInfo) {
                throw new Exception("推荐人不存在！");
            }

            unset($data['parent']);
            $data['parentId'] = $parentInfo['id'];
        }
        return $data;
    }

    /**
     * 编辑 验证后处理
     */
    protected function afterEditValidate($data)
    {
        if ('' != $data["password"]) {
            $data['password'] = md5($data["password"]);
        } else {
            unset($data['password']);
        }

        $data['createTime'] = now_datetime();
        return $data;
    }

    /**
     * 禁用
     */
    public function disable()
    {
        if (request()->isPost()) {
            $id = input('id');

            $ret = Db::table("member")->where("id", $id)->update(["isDisable"=>1]);
            if (false !== $ret) {
                return json_suc();
            } else {
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

            $ret = Db::table("member")->where("id", $id)->update(["isDisable"=>2]);
            if (false !== $ret) {
                return json_suc();
            } else {
                return json_err();
            }
        }
    }
}