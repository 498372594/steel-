<?php

namespace app\admin\controller;

use app\admin\model\Role;
use think\Db;
use think\Request;
use app\admin\model\Company as CompanyModel;
use app\admin\model\Admin as AdminModel;

class Company extends Right
{
    protected $role = 3;
    /**
     * 查
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $data = AdminModel::where(['department_id' => 4])->with('company')->order('id desc')->paginate(10);
        return returnRes($data->toArray()['data'],'没有公司数据，请添加后重试',$data);
    }

    /**
     * 增
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add(Request $request)
    {
        if($request->isPost()){
            $params = $request->param();
            if(Db::table('company')->where(['name' => $params['company']['name']])->find()){
                return returnFail('企业名称不能重复');
            }
            if($params['password'] != $params['password_confirm']){
                return returnFail('两次密码不对应');
            }
            if(Db::table('admin')->where(['account' => $params['account']])->find()){
                return returnFail('登录账号已存在');
            }
            Db::startTrans();
            $company = new CompanyModel($params['company']);
            //添加公司账号
            $res1 = $company->allowField(true)->save();
            if(!$res1){
                Db::rollback();
                return returnFail('创建失败：公司信息创建失败');
            }
            $admin_data = $params;
            $admin_data['companyid'] = $company->id;
            $admin_data['department_id'] = 4;//公司管理员
            $admin_data['create_time'] = now_datetime();
            $admin_data['update_time'] = now_datetime();
            $admin_data['name'] = $params['name'];
            $admin = new AdminModel($admin_data);
            $res2 = $admin->allowField(true)->save();
            if(!$res2){
                Db::rollback();
                return returnFail('创建失败：账号信息创建失败');
            }
            $role_data = [
                [
                    'companyid' => $company->id,
                    'department_id' => 1
                ],
                [
                    'companyid' => $company->id,
                    'department_id' => 2
                ]
            ];
            $role = new Role();
            $res3 = $role->saveAll($role_data);
            if(!$res3){
                Db::rollback();
                return returnFail('创建失败：角色创建失败');
            }
            Db::commit();
            return returnSuc();
        }
    }

    /**
     * 改
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function update(Request $request)
    {
        if($request->isPut()){
            $params = $request->param();
            $company_id = $params['company']['id'];
            if(Db::table('company')->where(['name' => $params['company']['name']])->where("id != {$company_id}")->find()){
                return returnFail('企业名称不能重复');
            }
            if(Db::table('admin')->where(['account' => $params['account']])->where("id != {$params['id']}")->find()){
                return returnFail('登录账号已存在');
            }
            Db::startTrans();
            $company = new CompanyModel();
            //添加公司账号
            $res1 = $company->allowField(true)->save($params['company'],['id' => $company_id]);
            if($res1 === false){
                Db::rollback();
                return returnFail('修改失败：公司信息修改失败');
            }
            $admin_data = $params;
            $admin_data['companyid'] = $company_id;
            $admin_data['department_id'] = 4;//公司管理员
            $admin_data['update_time'] = now_datetime();
            $admin_data['name'] = $params['name'];
            $admin = new AdminModel();
            $res2 = $admin->allowField(true)->save($admin_data,['id' => $params['id']]);
            if($res2 === false){
                Db::rollback();
                return returnFail('修改失败：账号信息修改失败');
            }
            Db::commit();
            return returnSuc();
        }
    }

    /**
     * 删
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function delete(Request $request)
    {
        if($request->isPost()){
            $id = $request->param('id');
            $admin = AdminModel::find($id);
            if($admin){
                CompanyModel::destroy($admin['companyid']);
            }
            $res = AdminModel::destroy($id);
            return returnRes($res,'数据删除失败');
        }
    }


}