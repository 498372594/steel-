<?php

namespace app\admin\model;
use traits\model\SoftDelete;
class ViewTotalSpot extends Base
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';

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
}
