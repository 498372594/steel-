<?php

namespace app\admin\controller;

use think\Db;
use think\Request;
use app\admin\model\Admin as AdminModel;

class Account extends Right
{
    /**
     * @param Request $request
     * @return \think\response\Json
     */
    public function departmentList(Request $request)
    {
        $data = getDropdownList('department');
        return returnSuc($data);
    }

    /**
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $company_id = $this->getCompanyId();
        if(!$company_id){
            return returnFail('参数错误');
        }
        $data = AdminModel::where(['companyid' => $company_id,'department_id' => ['in','1,2']])->order('id desc')->paginate(10);
        return returnRes($data->toArray()['data'],'没有公司数据，请添加后重试',$data);
    }

    /**
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
            if($params['password'] != $params['password_confirm']){
                return returnFail('两次密码不对应');
            }
            if(Db::table('admin')->where(['account' => $params['account']])->find()){
                return returnFail('登录账号已存在');
            }
            $params['companyid'] = $this->getCompanyId();
            unset($params['id']);
            $admin = new AdminModel($params);
            $admin->password = $params['password'];
            $res = $admin->allowField(true)->save();
            return returnRes($res,'账号创建失败');
        }
    }

    /**
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
            $id = $params['id'];
            if(Db::table('admin')->where(['account' => $params['account']])->where("id != {$id}")->find()){
                return returnFail('登录账号已存在');
            }
            $admin = new AdminModel();
            $res = $admin->allowField(true)->save($params,['id' => $id]);
            if($res === false){
                return returnFail('修改失败');
            }else{
                return returnSuc();
            }
        }
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     */
    public function delete(Request $request)
    {
        if($request->isPost()){
            $res = AdminModel::destroy($request->param('id'));
            return returnRes($res,'数据删除失败');
        }
    }
}