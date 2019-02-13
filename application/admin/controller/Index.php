<?php
namespace app\admin\controller;

use think\Session;

class Index extends Signin {

    public function index()
    {
//        Session::set("uid", null, 'admin');die;
        $this->assign("roleList", $this->getRoleList());
        return $this->fetch();
    }
}