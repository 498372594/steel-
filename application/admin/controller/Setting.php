<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;

class Setting extends Right
{
    use Backend;

    /**
     * 清除配置缓存
     */
    public function clearCache()
    {
        cacheSettings();
        $this->success("数据缓存已清除！");
    }
}