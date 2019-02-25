<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use think\Db;
use think\Exception;

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

    /**
     * 编辑附加数据
     */
    protected function editAttach()
    {
        $id = input("id");
        if (empty($id))  throw new Exception("未知的id！");

        $data = Db::table("setting")
            ->where("id", $id)
            ->find();
        $this->assign("data", $data);
    }
}