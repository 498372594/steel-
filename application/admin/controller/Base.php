<?php

namespace app\admin\controller;

use think\Config;
use think\Controller;
use think\Session;

/**
 * Class Base
 * @package app\admin\controller
 * 基类控制器
 */
class Base extends Controller
{
    protected $pageSize;

    public function __construct()
    {
        parent::__construct();

        // 分页
        $configPageSize = Config::get("paginate.list_rows");
        $this->pageSize = $configPageSize;

        // 系统名称
        $siteName = getSettings("site", "siteName");
        $this->assign("sysName", $siteName);
    }
}
