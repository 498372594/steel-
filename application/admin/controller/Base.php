<?php

namespace app\admin\controller;

use think\Controller;

/**
 * Class Base
 * @package app\admin\controller
 * 基类控制器
 */
class Base extends Controller {

    public function _initialize()
    {
        $this->assign("sysName", "桥通天下");
    }
}