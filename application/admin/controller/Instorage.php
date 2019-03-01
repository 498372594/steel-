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

    /**
     * 编辑 验证后处理
     */
    protected function afterEditValidate($data)
    {

        $data['create_time'] = now_datetime();
        return $data;
    }


}