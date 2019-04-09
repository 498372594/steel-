<?php

namespace app\admin\model;

use think\Cache;
use think\Config;
use think\Db;
use think\Session;

class User extends Base
{

    /**
     * 用户登录
     */
    public function Login($account="", $password="")
    {
        // 登录频率限制（如果登录失败，记录session，频率超过一定数值直接打回）
//        $last_login_fail = Session::get("last_login_fail", "admin");
//        $login_fail_times = Session::get("login_fail_times", "admin");
//
//        $login_fail_limit = Config::get("login_fail_times");
//        $login_fail_unblock = Config::get("login_fail_unblock");
//        $login_fail_unblock_seconds = $login_fail_unblock*60*60;
//
//        if ($login_fail_times && $login_fail_limit < $login_fail_times && $login_fail_unblock_seconds > time()-$last_login_fail) {
//            return info(-1, "您已连续登录失败{$login_fail_limit}次，请{$login_fail_unblock}小时后重试！");
//        }

        if (!$account || !$password) {
            return info(-1, "登录参数错误！");
        }

        $code = -1;
        $msg  = "";

        // 登录记录
        $log = [];
        $log['ip'] = get_real_client_ip();
        $log['port'] = $_SERVER["SERVER_PORT"];
        $log['browser'] = $_SERVER['HTTP_USER_AGENT'];
        $log['user'] = input("account");
        $log['createTime'] = date('Y-m-d H:i:s');
        $log['status'] = 0;

        // 登录
        $admin = Db::table("admin")
            ->where("account", $account)
            ->find();

        if ($admin) {
            if (1 == $admin['isdisable']) {
                $msg = "该账号已被禁用！";
            } else {
                if ($admin['password'] == md5($password)) {
                    $log['status'] = 1;
                    $code = 1;
                    $msg  = "登录成功！";
                    $authorization = request()->header('Authorization');
                    if($authorization){
                        $token = $this->getToken([
                            'id' => $admin['id'],
                            'account' => $admin['account']
                        ]);
                        $token_expire = config('token.expire');
                        Cache::set($token,$admin,$token_expire);
                        $data = $token;
                    }else{
                        Session::set('uid', $admin['id'], 'admin');
                        Session::set('uinfo', $admin, 'admin');
                        //清除连续登录失败次数
                        Session::set('login_fail_times', NULL, 'admin');
                        $data = $admin;
                    }
                } else {
                    $msg = "密码错误！";
                }
            }
        } else {
            $msg = "账号不存在！";
        }

//        if (-1 == $code) {
//            // 最近登录失败时间
//            Session::set("last_login_fail", time(), "admin");
//            // 累计连续登录失败次数（登录成功会清零）
//            $times = Session::get("login_fail_times", "admin");
//            if ($times) {
//                Session::set("login_fail_times", $times+1, "admin");
//            } else {
//                Session::set("login_fail_times", 1, "admin");
//            }
//        }

        $log['note'] = $msg;
        $insertLog = Db::table("adminloginlog")->insert($log);
        if (!$insertLog) {
            $code = -1;
            $msg  = "登录日志写入失败！";
        }
        unset($admin['password']);
        unset($admin['reserved_pwd']);
        return info($code, $msg,$data);
    }

    protected function getToken($params)
    {
        //用户id-用户账号-有效期-登录时间
        $expire = config('token.expire')==0?0:time()+config('token.expire');
        return base64_encode($params['id'] . '-' . $params['account'] . '-'. $expire . '-' . time());
    }
}
