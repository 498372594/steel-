<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use think\Db;
use think\Exception;

class Member extends Right
{
    use Backend;

    /**
     * 添加 验证前处理
     */
    protected function beforeAddValidate($data)
    {
        if ($data['parent']) {

            $parentInfo = Db::name("member")->where("account", $data['parent'])->find();
            if (!$parentInfo) {
                throw new Exception("推荐人不存在！");
            }

            unset($data['parent']);
            $data['parentId'] = $parentInfo['id'];
            return $data;
        }
    }

    /**
     * 添加 验证后处理
     */
    protected function afterAddValidate($data)
    {
        if ($data["password"]) {
            $data['password'] = md5($data["password"]);
        }

        $data['createTime'] = now_datetime();
        return $data;
    }
}