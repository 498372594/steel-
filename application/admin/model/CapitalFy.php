<?php


namespace app\admin\model;
use traits\model\SoftDelete;
class CapitalFy extends Base
{
    use SoftDelete;
//    use DeletePlugin;

    protected $autoWriteTimestamp = true;
//    protected $deleteTime = 'delete_time';
//    protected $autoWriteTimestamp = 'datetime';
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

<<<<<<< HEAD
=======
    public function szmcData()
    {
        return $this->belongsTo('Paymenttype', 'shouzhimingcheng_id', 'id')->cache(true, 60)
            ->field('id,class,name')->bind(['szfl_name' => 'class', 'szmc_name' => 'name']);
    }

    public function pjlxData()
    {
        return $this->belongsTo('Pjlx', 'piaoju_id', 'id')->cache(true, 60)
            ->field('id,pjlx')->bind(['pjlx_name' => 'pjlx']);
    }

    public function custom()
    {
        return $this->belongsTo('Custom', 'customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['dfdw_name' => 'custom']);
    }
}
>>>>>>> e61eae20855ccfd15cd0c5fe60f393fcc8b985c4
