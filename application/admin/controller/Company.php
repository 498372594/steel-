<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use think\Db;
use think\Exception;

class Company extends Right
{
    use Backend;

    /**
     * 添加 验证前处理
     */
    protected function afterAddValidate($data)
    {
        if ($data['name']) {
            if (Db::table("company")->where("name", $data['name'])->find()) {
                throw new Exception("公司名称已存在！");
            }
        }
        if ($data['phone']){
            if(!isPhone($data['phone'])){
                throw new Exception("手机号码格式有误！");
            }
        }
        $data['create_time'] = now_datetime();
        return $data;
    }
}