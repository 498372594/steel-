<?php
namespace app\admin\controller;

/**
 * Class Login
 * 登录控制器
 */
class Login extends Base {

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
            $captcha = input("captcha");
            if (captcha_check($captcha)) {
                // 登录记录
                $log = [];
                $log['ip'] = get_real_client_ip();
                $log['port'] = $_SERVER["SERVER_PORT"];
                $log['browser'] = $_SERVER['HTTP_USER_AGENT'];
                $log['user'] = input("account");
                $log['createTime'] = date('Y-m-d H:i:s');




            } else {
                $this->error("图片验证码输入错误！");
            }
        } else {
            $this->error("请求方式错误！");
        }
    }
}