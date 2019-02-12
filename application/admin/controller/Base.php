<?php
namespace app\admin\controller;

use think\auth\Auth;
use think\Controller;

class Base extends Controller {

    public function _initialize()
    {
        // 是否登录
        if (!$this->isLogin()) {
            die("<script>window.parent.location.href = '/admin/login/index';</script>");
        }
        // 是否拥有访问权限
        if(!$this->auth()) {
            $this->error("无权限访问！");
        }
    }

    /**
     * 验证是否登录
     */
    protected function isLogin() {
        $uid = session("uid");
        if (!$uid) {
            return false;
        }
        return true;
    }

    /**
     * 权限检测
     */
    protected function auth()
    {
        $controller = request()->controller();
        $action     = request()->action();
        $auth = new Auth();
        if (!$auth->check(strtolower($controller.'/'.$action), session('uid'))) {
            return false;
        }
        return true;
    }
}
