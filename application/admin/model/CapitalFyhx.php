<?php

namespace app\admin\model;
<<<<<<< HEAD
use traits\model\SoftDelete;
class CapitalFyhx extends Base
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';

    public function specification()
    {
        return $this->belongsTo('ViewSpecification', 'wuzi_id', 'id')->cache(true, 60)
            ->field('id,specification,mizhong_name,productname');
    }

    public function jsfs()
    {
        return $this->belongsTo('Jsfs', 'jiesuan_id', 'id')->cache(true, 60)
            ->field('id,jsfs')->bind(['jiesuan_name' => 'jsfs']);
    }

    public function storage()
    {
        return $this->belongsTo('Storage', 'storage_id', 'id')->cache(true, 60)
            ->field('id,storage')->bind(['storage_name' => 'storage']);
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
=======

use traits\model\SoftDelete;

class CapitalFyhx extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function mingxi()
    {
        return $this->belongsTo('CapitalFy', 'cap_fy_id', 'id')
            ->field('id,customer_id,beizhu,fang_xiang,shouzhifenlei_id,shouzhimingcheng_id,danjia,money,zhongliang,piaoju_id,price_and_tax,tax_rate,tax');
    }
}
>>>>>>> e61eae20855ccfd15cd0c5fe60f393fcc8b985c4
