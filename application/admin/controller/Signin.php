<?php
namespace app\admin\controller;

use think\auth\Auth;
use think\Session;

class Signin extends Base {

    public function _initialize()
    {
        // 是否登录
        if (!$this->isLogin()) {
            die("<script>window.parent.location.href = '/admin/login/index';</script>");
        }
        // 是否拥有访问权限
        if(!$this->authCheck()) {
            $this->error("无权限访问！");
        }
        // 登录账号信息输出到模板
        $this->assign("account", Session::get("uinfo", "admin"));
    }

    /**
     * 验证是否登录
     * @return bool
     */
    protected function isLogin() {
        $uid = Session::get("uid", "admin");
        if (!$uid) {
            return false;
        }
        return true;
    }

    /**
     * 权限检测
     * @return bool
     */
    protected function authCheck()
    {
        $controller = request()->controller();
        $action     = request()->action();
        $auth = new Auth();
        if (!$auth->check(strtolower($controller.'/'.$action), Session::get('uid', "admin"))) {
            return false;
        }
        return true;
    }
}
