<?php
namespace app\admin\controller;

use think\Cache;

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

    /**
     * 清除所有缓存（数据缓存、模板缓存）
     */
    public function clearCacheData()
    {
        // 清除数据缓存
        Cache::clear();
        // 清除模板缓存
        clear_temp_cache();
        // 重新加载缓存
        cacheSettings();

        $this->success("缓存信息已刷新！");
    }
}