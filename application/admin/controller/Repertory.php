<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use think\Db;
use think\Exception;
use think\Session;

class Repertory extends Right
{
    use Backend;

    /**
     * 过滤器，要过滤的字段
     * @return array
     */
    protected function filter()
    {
        return [
            'companyid' => $this->getCompanyId()
        ];
    }

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
        $company_id = $this->getCompanyId();
        if ($data['name']) {
            if (Db::table("repertory")->where("companyid = {$company_id} and name = '{$data['name']}'")->find()) {
                throw new Exception("仓库已经存在，避免重复添加！");
            }
        }
        $data['createtime'] = now_datetime();
        $data['companyid'] = $company_id;
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
        $data = Db::table("repertory r")->where("r.id", $id)->find();
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
        $company_id = $this->getCompanyId();
        if ($data['name']) {
            if (Db::table("repertory")->where("companyid = {$company_id} and name = '{$data['name']}'")->find()) {
                throw new Exception("仓库名称已存在！");
            }
        }
        return $data;
    }

}