<?php
namespace app\admin\controller;

use think\Loader;
use think\Url;

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
                $account  = input("account");
                $password = input("password");

                $ret = Loader::model('User')->login($account, $password);

                if (1 == $ret['code']) {
                    // 登录成功
                    $this->redirect(Url::build('admin/index/index'));
                } else {
                    $this->error($ret['msg']);
                }

            } else {
                $this->error("图片验证码输入错误！");
            }
        } else {
            $this->error("请求方式错误！");
        }
    }
}