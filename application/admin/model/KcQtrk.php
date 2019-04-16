<?php

namespace app\admin\model;
use traits\model\SoftDelete;
class KcQtrk extends Base
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';
    public function details()
    {
        return $this->hasMany('KcQtrkMx', 'kc_rk_qt_id', 'id');
    }
    public function customData()
    {
        return $this->belongsTo('custom', 'customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['custom' => 'custom']);
    }
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
