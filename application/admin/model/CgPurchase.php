<?php

namespace app\admin\model;
use traits\model\SoftDelete;
class CgPurchase extends Base
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';
    public function details()
    {
        return $this->hasMany('CgPurchaseMx', 'purchase_id', 'id');
    }
    public function other()
    {
        return $this->hasMany('CapitalFyhx', 'data_id', 'id');
    }
    public function jsfsData()
    {
        return $this->belongsTo('Jiesuanfangshi', 'jiesuan_id', 'id')->cache(true, 60)
            ->field('id,jiesuanfangshi')->bind(['jiesuan_name' => 'jiesuanfangshi']);
    }
    public function custom()
    {
        return $this->belongsTo('Custom', 'customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['custom_name' => 'custom']);
    }
    public function pjlxData()
    {
        return $this->belongsTo('Pjlx', 'piaoju_id', 'id')->cache(true, 60)
            ->field('id,pjlx')->bind(['piaoju_name' => 'pjlx']);
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
//
//    public static function findCgScCountsByMsMxId(){
//        /*SELECT  COUNT(1) FROM
//        tb_cg_purchase pu
//         LEFT JOIN tb_moshi_mx mx ON pu.`data_id`=mx.`id`
//        LEFT JOIN tb_moshi moshi ON moshi.`id`=mx.`moshi_id`
//        WHERE moshi.`id`=#{dataId}
//        AND pu.`customer_id`=#{cgCustomerId}
//        AND pu.`moshi_type`=#{moshiType} a
//        nd pu.`piaoju_id`=#{cgPjlx}*/
//
//        self::alias('pu')
//            ->join('__SALES_MOSHI_MX__ ')
//    }
}
