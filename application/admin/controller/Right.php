<?php
namespace app\admin\controller;

/**
 * main区域需要一个模板布局
 * Class Right
 * @package app\admin\controller
 */
class Right extends Signin
{
    public function __construct()
    {
        parent::__construct();
        $this->view->engine->layout('common/layout');
    }
}
