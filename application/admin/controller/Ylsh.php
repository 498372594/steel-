<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use app\admin\model\KcRk;
use app\admin\model\{ KcSpot};
use think\{Db,Request};
use think\Exception;
use think\Session;

class Ylsh extends Right
{
    /**入库单列表
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function kcspot()
    {
        $params = request()->param();
        $list = \app\admin\model\KcSpot::with([
            'specification', 'storage','pinmingData','caizhiData','chandiData','guigeData',
        ])->where('companyid', Session::get("uinfo", "admin")['companyid']);
        $list =$this->getsearchcondition($params,$list);
        $list = $list->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }



    /**锁货
     * @return \think\response\Json
     * @throws \Exception
     */
    public function lock(){
        if(request()->isPost()){
            $data=request()->post();
            foreach($data as $k=>$v){
                $data[$k]['yuliu_type']="已预留";
                $data[$k]['companyid']= Session::get('uinfo.companyid', 'admin');

            }
            $res=model("KcYlSh")->allowField(true)->saveAll($data);
            return returnRes($res,'锁定');
        }
    }
    public function release(){
        if(request()->isPost()){
            $data=request()->post();
            $res=model("KcYlSh")->allowField(true)->saveAll($data["data"]);
            return returnRes($res,'锁货释放失败');
        }
    }

    /**获取锁货信息
     * @return \think\response\Json
     */
    public function getlock(){
        $params = request()->param();
        $list=db("ViewKcYlsh")->where('companyid', Session::get("uinfo", "admin")['companyid']);
        if (!empty($params['ids'])) {
           $list->where('id', 'in',$params['ids']);
        }
        $list =$this->getsearchcondition($params,$list);
        $list = $list->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**延期
     * @return \think\response\Json
     * @throws \Exception
     */
    public function postpone(){
        if(request()->isPost()){
            $data=request()->post();
            $res=model("KcYlSh")->allowField(true)->saveAll($data["data"]);
            return returnRes($res,'锁货延迟失败');
        }
    }
}