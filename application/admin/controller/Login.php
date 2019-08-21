<?php
namespace app\admin\controller;

use think\Cache;
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




            $account  = input("account");

            $password = input("password");
            $ret = Loader::model('User')->login($account, $password);

            if($ret['code'] == 1){
                return $this->success('登陆成功','index/index');
            }else{
//                return returnRes($ret['code'] == 1,'登录失败',$ret["data"]);
                return $this->error('登陆失败','index/index',$ret["data"]);
            }


    }

    /**
     * 退出登录
     */
    public function logOut()
    {
        $authorization = request()->header('Authorization');
        if($authorization){
            if(request()->isPost()){
                $token = explode(' ',$authorization)[1];
                if($token){
                    Cache::rm($token);
                }
                return returnSuc('退出成功');
            }
        }else{
            Session::set("uid", NULL, "admin");
            Session::set('uinfo', NULL, 'admin');
            $this->success("退出成功！", Url::build('/admin/login/index'));
        }
    }
}