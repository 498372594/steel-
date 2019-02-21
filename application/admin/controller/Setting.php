<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use think\Cache;

class Setting extends Right
{
    use Backend;

    public function clearCache()
    {
        Cache::clear();
        $this->success("数据缓存已清除！");
    }
}