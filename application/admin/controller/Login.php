<?php
namespace app\admin\controller;

use think\Loader;
use think\Session;
use think\Url;

/**
 * Class Login
 * 登录控制器
 */
class Login extends Base
{

    /**
     *  登录页面
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 登录操作验证
     */
    public function login()
    {
        if (request()->isPost()) {
                $account  = input("account");
                $password = input("password");
                $ret = Loader::model('User')->login($account, $password);
                return returnRes($ret['code'] == 1,'登录失败',$ret['data']);
        } else {
            $this->error("请求方式错误！");
        }
    }

    /**
     * 退出登录
     */
    public function logOut()
    {
        Session::set("uid", NULL, "admin");
        Session::set('uinfo', NULL, 'admin');
        $this->success("退出成功！", Url::build('/admin/login/index'));
    }



}