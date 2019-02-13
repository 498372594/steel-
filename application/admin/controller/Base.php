<?php

namespace app\admin\controller;

use think\Controller;

/**
 * Class Base
 * @package app\admin\controller
 * 基类控制器
 */
class Base extends Controller {

    public function __construct()
    {
        parent::__construct();
        $this->assign("sysName", "桥通天下");
    }

    public function _initialize()
    {

    }
}