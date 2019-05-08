<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-04-11
 * Time: 13:36
 */

namespace app\admin\controller;


use think\Request;
use app\admin\model\Role as RoleModel;

class Role extends Right
{
    protected $role = 4;//限制当前控制器只能那些角色访问

    public function index(Request $request)
    {
        if($request->isGet()){
            $data = RoleModel::where(['companyid' => $this->getCompanyId()])->order('id desc')->select();
            return returnSuc($data);
        }
    }

    public function update(Request $request)
    {
        if($request->isPut()){
            $companyid = $this->getCompanyId();
            $id = $request->param('id');
            $authority = implode(',',json_decode($request->param('authority'),true));
            $res = RoleModel::where(['companyid' => $companyid,'id' => $id])->update(['authority' => $authority]);
            return returnRes($res,'保存失败');
        }
    }
}