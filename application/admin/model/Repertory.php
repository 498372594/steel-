<?php

namespace app\admin\model;

class Repertory extends Base
{
    // 验证规则
    public $rules = [
        'name'  => 'require|max:30',
    ];

    // 验证错误信息
    public $msg = [
        'name.require' => '请填写仓库名称！',
    ];

    // 场景
    public $scene = [
        'edit'  =>  ['name'],
    ];

    // 表单-数据表字段映射
    public $map = [
        [self::MTIME, "createtime", "create_time"]
    ];
}
