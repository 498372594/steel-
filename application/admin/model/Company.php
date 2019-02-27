<?php

namespace app\admin\model;

class Company extends Base
{
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
        [self::MTIME, "createtime", "create_time"]
    ];
}
