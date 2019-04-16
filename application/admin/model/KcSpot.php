<?php

namespace app\admin\model;
use traits\model\SoftDelete;
class KcSpot extends Base
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';
    public function specification()
    {
        return $this->belongsTo('ViewSpecification', 'guige_id', 'id')->cache(true, 60)
            ->field('id,specification,mizhong_name,productname');
    }
    public function guigeData()
    {
        return $this->belongsTo('ViewSpecification', 'guige_id', 'id')->cache(true, 60)
            ->field('id,specification')->bind(['guige' => 'specification']);;
    }
    public function storage()
    {
        return $this->belongsTo('Storage', 'store_id', 'id')->cache(true, 60)
            ->field('id,storage')->bind(['storage_name' => 'storage']);
    }
    public function pinmingData()
    {
        return $this->belongsTo('Productname', 'pinming_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['pinming' => 'name']);
    }
    public function caizhiData()
    {
        return $this->belongsTo('texture', 'caizhi_id', 'id')->cache(true, 60)
            ->field('id,texturename')->bind(['caizhi' => 'texturename']);
    }
    public function chandiData()
    {
        return $this->belongsTo('Originarea', 'chandi_id', 'id')->cache(true, 60)
            ->field('id,originarea')->bind(['chandi' => 'originarea']);
    }
    public function getRealcountsAttr($value,$data){
        $count=model("KcYlsh")->where("spot_id",$data['id'])->sum("counts");
        $count=$data["counts"]-$count;
        return $count;
    }
    public function getReallingzhiAttr($value,$data){
        $count=model("KcYlsh")->where("spot_id",$data['id'])->sum("counts");
        $count=$data["counts"]-$count;
        $lingzhi=$count/$data["zhijian"];
        return $lingzhi;
    }
    public function getRealjianshuAttr($value,$data){
        $count=model("KcYlsh")->where("spot_id",$data['id'])->sum("counts");
        $count=$data["counts"]-$count;
        $jianshu=intval(floor($count / $data["zhijian"]));
        return $jianshu;
    }
    public function getRealzhongliangAttr($value,$data){
        $zhongliang=model("KcYlsh")->where("spot_id",$data['id'])->sum("zhongliang");
        $zhongliang=$data["zhongliang"]-$zhongliang;
        return $zhongliang;
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
