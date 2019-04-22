<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-04-12
 * Time: 18:46
 */

namespace app\admin\controller;

use app\admin\model\Admin as adminModel;
use think\Request;


class Common extends  Right
{
    /**
     * 获取角色名称
     * @return \think\response\Json
     */
    public function roleName()
    {
        $data = getDropdownList('department','',0);
        return returnRes($data,'没有数据，请联系管理员',$data);
    }

    /**
     * 获取当前公司职员
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOfficeWorker()
    {
        $companyid = $this->getCompanyId();
        $admin = adminModel::field('id,name,department_id')
            ->where(['companyid' => $companyid,'department_id' => ['in','1,2'],'isdisable' => 2])
            ->with('role')
            ->select();
        return returnRes($admin,'没有职员，请添加后重试',$admin);
    }

    /**
     * 修改密码
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function changePwd(Request $request)
    {
        if($request->isPut()){
            $params = $request->param();
            $id = $this->getAccountId();
            $admin = AdminModel::where(['id' => $id])->find();
            if($admin['password'] != md5($params['old_pwd'])){
                return returnFail('旧密码不对应');
            }
            if($params['new_pwd'] != $params['confirm_pwd']){
                return returnFail('两次密码不对应');
            }
            if($admin['password'] == md5($params['new_pwd'])){
                return returnFail('新密码与旧密码相同');
            }
            $res = AdminModel::where(['id' => $id])->update(['password' => md5($params['new_pwd'])]);
            return returnRes($res,'密码修改失败');
        }
    }
}