<?php

namespace app\admin\controller;

use think\Config;
use think\Controller;
use think\Session;

/**
 * Class Base
 * @package app\admin\controller
 * 基类控制器
 */
class Base extends Controller
{
    protected $pageSize;

    public function __construct()
    {
        parent::__construct();

        // 分页
        $configPageSize = Config::get("paginate.list_rows");
        $this->pageSize = $configPageSize;

        // 系统名称
        $siteName = getSettings("site", "siteName");
        $this->assign("sysName", $siteName);
    }
    /**
     * 自动释放
     */
    public function autorelease(){

        $list=model("reserved")->select();

        foreach ($list as $key=>$value){
            if(strtotime($value["reserved_time"])<time()){
                $info=model("purchasedetails")->where("id",$value["purchasedetails_id"])->find();
                $inf["shuliang"]=$info["shuliang"]+$value["reserved_num"];
                $inf["lingzhi"]=$info["lingzhi"]+$value["reserved_lingzhi"];
                $inf["jianshu"]=$info["jianshu"]+$value["reserved_jianshu"];
                $inf["heavy"]=$info["heavy"]+$value["reserved_heavy"];
                $inf["id"]=$info["id"];
//                dump($inf);die;
                $re=model("purchasedetails")->where("id",$inf["id"])->update($inf);
                if($re){
                    model("reserved")->where("id", $value["id"])->delete();
                }
            }

        }
    }
}
