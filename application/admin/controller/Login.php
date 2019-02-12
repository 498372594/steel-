<?php
namespace app\admin\controller;

use think\Controller;

/**
 * Class Login
 * 登录控制器
 */
class Login extends Controller {

    /**
     *  登录页面
     */
    public function index()
    {
        return $this->view->fetch();
    }
}