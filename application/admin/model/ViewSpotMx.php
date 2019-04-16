<?php

namespace app\admin\model;
use traits\model\SoftDelete;
class ViewSpotMx extends Base
{
    // 定义时间戳字段名
    protected $createTime = 'createtime';

    protected $updateTime = false;

    protected $autoWriteTimestamp = 'datetime';
}
