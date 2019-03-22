<?php

namespace app\admin\model;

use think\Model;

class Base extends Model
{
    const MTXT  = "Text";
    const MNUM  = "Number";
    const MTIME = "Time";

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