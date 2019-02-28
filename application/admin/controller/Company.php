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
     * @param $data
     * @return mixed
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
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
        $data['createtime'] = now_datetime();
        return $data;
    }

    /**
     * 编辑附加数据
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function editAttach()
    {
        $id = input("id");
        if (empty($id))  throw new Exception("未知的id！");

        $data = Db::table("company m")->where("m.id", $id)->find();
        $this->assign("data", $data);
    }

    /**
     * 编辑 验证前处理
     * @param $data
     * @return mixed
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function beforeEditValidate($data)
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
        return $data;
    }
}