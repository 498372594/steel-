<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use app\admin\model\PriceLog;
use app\admin\model\Specification;
use app\admin\model\ViewSpecification;
use think\Db;
use think\Exception;
use think\Session;

class Price extends Right
{
    public function priceset(){
        if(request()->isPost()){
            Db::startTrans();
            try {
                $data=request()->post();
                $ids=$data["id"];
                unset($data["id"]);
                $ids=explode(",",$ids);
                foreach ($ids as $id){
                    $dat=$data;
                    $dat["id"]=$id;
                    $re=(new Specification())->where("id",$id)->save($data);
                    (new PriceLog())->allowField(true)->data($dat)->save();
                }
                return returnSuc(['id' => $re['id']]);
            } catch (\Exception $e) {
                Db::rollback();
                return returnFail($e->getMessage());
            }
        }
    }

    /**单个修改价格
     * @return \think\response\Json
     */
    public function priceedit(){
        if (request()->isPost()) {
            $data = request()->post();
            $dat["hsgbj"]=$data["hsgbj"];
            $dat["hslsj"]=$data["hslsj"];
            $dat["hsdzj"]=$data["hsdzj"];
            $dat["qsgbj"]=$data["qsgbj"];
            $dat["qslsj"]=$data["qslsj"];
            $dat["qsdzj"]=$data["qsdzj"];
            $dat["gg_id"]=$data["id"];
            $result = model("specification")->allowField(true)->save($data, ['id' => $data["id"]]);
            (new PriceLog())->allowField(true)->data($dat)->save();
            return returnRes($result, '修改失败');
        }

    }

    /**定时执行
     * @return \think\response\Json
     */
    public function autoPriceEdit(){
        if (request()->isPost()) {
            try{
                $data=request()->post();
                $time=$data["time"];
                $heavy=$data["heavy"];
                if($data["type"]==1){
                    if(!empty(cache("price_change_time"))){
                        $priceVal=db("setting")->where("code","price_time_rise")->value("val");
                        throw new Exception('已设置'.cache("price_change_time")."后，执行涨价".$priceVal);
                    }else{
                        db("setting")->where("code","price_rise")->udpate(array("val"=>$heavy));
                        db("setting")->where("code","price_time_rise")->udpate(array("val"=>$time));
                        cache("price_change_time",$time,$time*60);
                        \think\Queue::later($time*60,'app\admin\job\ChangePrice',$data=$data["upprice"],$queue = null);
                    }
                }else{
                    db("setting")->where("code","price_rise")->udpate(array("val"=>$heavy));
                    db("setting")->where("code","price_time_rise")->udpate(array("val"=>$time));
                    cache("price_change_time",$time,$time*60);
                    \think\Queue::later($time*60,'app\admin\job\ChangePrice',$data=$data["upprice"],$queue = null);
                }


            }catch (\Exception $e) {
                Db::rollback();
                return returnFail($e->getMessage());
            }
        }
    }
    public function getguigelist(){
        $params = request()->param();
        $list = ViewSpecification::where('companyid', $this->getCompanyId());
        if (!empty($params['productname_id'])) {
            $list->where('productname_id', $params['productname_id']);
        }
        //规格
        if (!empty($params['guige'])) {
            $list->where('guige', 'like', '%' . $params['specification'] . '%');
        }
        //厚度
        if (!empty($params['houdu_start'])) {
            $list->where('houdu', '>=', $params['houdu_start']);
        }
        if (!empty($params['houdu_end'])) {
            $list->where('houdu', '<=', $params['houdu_end']);
        }
        //宽度
        if (!empty($params['width_start'])) {
            $list->where('kuandu', '>=', $params['width_start']);
        }

        if (!empty($params['width_end'])) {
            $list->where('kuandu', '<=', $params['width_end']);
        }
        //长度
        if (!empty($params['length_start'])) {
            $list->where('changdu', '>=', $params['length_start']);
        }
        if (!empty($params['length_end'])) {
            $list->where('changdu', '<=', $params['length_end']);
        }
        $list = $list->select();
        return returnSuc($list);
    }
}