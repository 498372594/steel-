<?php

namespace app\admin\model;
use traits\model\SoftDelete;
class KcRkTz extends Base
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';
    // 验证规则
    public $rules = [

    ];

    // 验证错误信息
    public $msg = [

    ];

    // 场景
    public $scene = [

    ];

    // 表单-数据表字段映射
    public $map = [

    ];
}
