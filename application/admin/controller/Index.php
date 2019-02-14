<?php
namespace app\admin\controller;

class Index extends Signin
{

    public function index()
    {
        $this->assign("roleList", $this->getMenu());
        return $this->fetch();
    }

    public function main()
    {
        return $this->fetch();
    }
}