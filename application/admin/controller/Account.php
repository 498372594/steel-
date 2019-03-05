<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use think\Db;
use think\Exception;

class Account extends Right
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
            $company_id = (int)input("company_id");//企业id
            if(!$company_id){
                return json_err(-1, "参数有误！");
            }
            //判断企业是否存在
            if(!Db::table('company')->find($company_id)){
                return json_err(-1, "企业不存在！");
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
                "isdisable"  => 2,
                "createtime" => now_datetime(),
                'companyid'  => $company_id
            ];

            $group_id = (int)input("group_id");

            $adminRole = Db::table('authgroup')->where(['id' => $group_id,'title' => '管理员'])->find();
            if($adminRole){
                if(Db::table('admin')->alias('a')->where(['a.companyid' => $company_id])->join('authgroupaccess aga',"aga.uid = a.id and aga.group_id = {$group_id}")->find()){
                    //当前企业是否存在管理员
                    return json_err(-1, "每个企业仅能创建一个管理员！");
                }
            }
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
            $companies = Db::table('company')->field('id,name')->select();
            $companyArr = [""=>""];
            if(!empty($companies)){
                foreach ($companies as $k => $v){
                    $companyArr[$v['id']] = $v['name'];
                }
            }else{
                $this->error("请添加企业！");
            }

            $this->assign([
                "lists" => [
                    "rolelist" => $roleArr,
                    'companyList' => $companyArr
                ]
            ]);
            return $this->fetch();
        }
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
}