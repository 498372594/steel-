<?php
namespace app\admin\controller;

use app\admin\library\traits\Buildparams;
use think\auth\Auth;
use think\Config;
use think\Db;
use think\Session;

class Signin extends Base
{
    use Buildparams;

    /**
     * 无需权限认证部分
     * @var array
     */
    protected $unblock = [
        'index/index',
        'index/main',
        'index/clearcachedata'
    ];

    public function _initialize()
    {
        // 是否登录
        if (!$this->isLogin()) {
            die("<script>window.parent.location.href = '/admin/login/index';</script>");
        }
        // 账号是否被禁用
        if (1 == Session::get("uinfo", "admin")['isdisable']) {
            $this->error("该账号已被禁用！");
        }
        // 是否拥有访问权限(超级管理员除外)
        if (!in_array(Session::get("uid", "admin"), Config::get("supermanager"))) {
            if (!$this->authCheck()) {
                $this->error("无权限访问！");
            }
        }
        // 登录账号信息输出到模板 get_curr_time_section
        $this->assign([
            "account" => Session::get("uinfo", "admin"),
            "time_section" => get_curr_time_section()
        ]);
    }

    /**
     * 验证是否登录
     * @return bool
     */
    protected function isLogin()
    {
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
        // 首页 登出 无需权限检测
        $url = strtolower($controller.'/'.$action);
        if (!in_array($url, $this->unblock)) {
            if (!$auth->check($url, Session::get('uid', "admin"))) {
                return false;
            }
        }
        return true;
    }

    /**
     * 获取菜单
     */
    protected function getMenu ()
    {
        // 所有菜单
        $menu = Db::table("authrule")->field('id,name,title,status,pid,faicon')->select();
        // 拥有权限菜单
        $auth = new Authority();
        $uid  = Session::get("uid", "admin");
        $ruleList = $auth->getAuthList($uid,1);

        if (in_array($uid, Config::get("supermanager"))) {
            // 超级管理员
            return $menu;
        } else {
            // 后台用户
            return $ruleList;
        }
    }

    /**
     * 当前登录用户id
     * @return mixed
     */
    public function getAccountId()
    {
        return Session::get('uid','admin');
    }

    /**
     * 当前登录用户数据
     * @return mixed
     */
    public function getAccount()
    {
        return Session::get('uinfo','admin');
    }

    /**
     * 当前登录用户所属公司的id
     * @return mixed
     */
    public function getCompanyId()
    {
        return Session::get('uinfo','admin')['companyid'];
    }
}
