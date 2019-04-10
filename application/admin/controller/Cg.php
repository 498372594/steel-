<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use think\Db;
use think\Exception;
use think\Session;

class Cg extends Right
{
    public function cgth(){
        $params = request()->param();
        $list = $list = \app\admin\model\CgTh::where('companyid', $this->getCompanyId());
        $list=$this->getsearchcondition($params,$list);
        $list = $list->paginate(10);
        return returnRes(true, '', $list);
    }
    public function cgthmx($id=0){
        $data = $list = \app\admin\model\InitYskp::with([ 'details',
        ])
            ->where('companyid',$this->getCompanyId())
            ->where('id', $id)
            ->find();
        if (empty($data)) {
            return returnFail('数据不存在');
        } else {
            return returnRes(true, '', $data);
        }
    }
    public function addcgth($data = [], $return = false)
    {
        if (request()->isPost()) {
            $companyId = $this->getCompanyId();
            $data = request()->post();
            $count = \app\admin\model\InitYskp::whereTime('create_time', 'today')->where("type",$data["type"])->count();
            $data["status"] = 0;
            $data['create_operator_id'] = $this->getAccountId();
            $data['companyid'] = $companyId;
            if($data["type"]==0){
                $data['system_number'] = 'YSJXFPYEQC' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            }
            if($data["type"]=1){
                $data['system_number'] = 'YKXXFPYEQC' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            }

            if (!$return) {
                Db::startTrans();
            }
            try {
                model("InitYskp")->allowField(true)->data($data)->save();
                $id = model("InitYskp")->getLastInsID();
                foreach ($data["details"] as $c => $v) {
                    $data['details'][$c]['companyid'] = $companyId;
                    $data['details'][$c]['ysfk_id'] = $id;
                }
                model('InitYskpMx')->saveAll($data['details']);
                if (!$return) {
                    Db::commit();
                    return returnRes(true, '', ['id' => $id]);
                } else {
                    return true;
                }
            } catch (Exception $e) {
                if ($return) {
                    return $e->getMessage();
                } else {
                    Db::rollback();
                    return returnFail($e->getMessage());
                }
            }
        }
        if ($return) {
            return '请求方式错误';
        } else {
            return returnFail('请求方式错误');
        }
    }
}