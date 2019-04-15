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
        $list = model("ViewKcSpot")->where('companyid', $this->getCompanyId());
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
                $data[$k]['companyid']= $this->getCompanyId();

            }
            $res=model("KcYlSh")->allowField(true)->saveAll($data);
            return returnRes($res,'锁定');
        }
    }
    public function release(){
        if(request()->isPost()){
            $data=request()->post();
            $res=model("KcYlSh")->allowField(true)->saveAll($data);
            return returnRes($res,'锁货释放失败');
        }
    }

    /**获取锁货信息
     * @return \think\response\Json
     */
    public function getlock(){
        $params = request()->param();
        $list=db("ViewKcYlsh")->where('companyid', $this->getCompanyId());
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
            $res=model("KcYlSh")->allowField(true)->saveAll($data);
            return returnRes($res,'锁货延迟失败');
        }
    }

    /**根据仓库id查询库存
     * @param int $store_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function storespot($store_id=0){
        $list=model("KcSpot")->where(array("companyid"=> $this->getCompanyId(),"store_id"=>$store_id))->select();
        return returnRes($list, '没有数据，请添加后重试', $list);

    }
    public function addpandian($data = [], $return = false){
        if (request()->isPost()) {
            $companyId = $this->getCompanyId();
            $count = \app\admin\model\KcPandian::whereTime('create_time', 'today')->count();
            $data = request()->post();
            $data["status"] = 0;
            $data['create_operator_name'] = $this->getAccount()['name'];
            $data['create_operator_id'] = $this->getAccountId();
            $data['companyid'] = $companyId;
            $data['system_number'] = 'KCPD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            if (!$return) {
                Db::startTrans();
            }
            try {
                model("KcPandian")->allowField(true)->data($data)->save();
                $id = model("KcPandian")->getLastInsID();
                foreach ($data["detail"] as $c => $v) {
                    $data['details'][$c]['companyid'] = $companyId;
                    $data['details'][$c]['pandian_id'] = $id;
                }
                model('KcPandianMx')->saveAll($data['details']);
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
    /**盘点列表
     * @return \think\response\Json
     */
    public function pandianlist()
    {
        $params = request()->param();
        $list = $list = \app\admin\model\KcPandian::where('companyid', Session::get('uinfo.companyid', 'admin'));
        if (!empty($params['system_number'])) {
            $list->where("system_number",$params['system_number']);
        }
        if (!empty($params['beizhu'])) {
            $list->where("beizhu",$params['beizhu']);
        }
        $list = $list->paginate(10);
        return returnRes(true, '', $list);
    }

    /**盘点明细列表
     * @param int $id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function pandianmx($id = 0)
    {
        $data = \app\admin\model\KcPandian::with(['details'])
            ->where('companyid', Session::get('uinfo.companyid', 'admin'))
            ->where('id', $id)
            ->find();
        if (empty($data)) {
            return returnFail('数据不存在');
        } else {
            return returnRes(true, '', $data);
        }
    }
    /**调拨列表
     * @return \think\response\Json
     */
    public function diaobolist()
    {
        $params = request()->param();
        $list = $list = \app\admin\model\KcDiaobo::where('companyid', Session::get('uinfo.companyid', 'admin'));
        if (!empty($params['system_number'])) {
            $list->where("system_number",$params['system_number']);
        }
        if (!empty($params['beizhu'])) {
            $list->where("beizhu",$params['beizhu']);
        }
        $list = $list->paginate(10);
        return returnRes(true, '', $list);
    }

    /**调拨明细列表
     * @param int $id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function diaobomx($id = 0)
    {
        $data = \app\admin\model\KcDiaobo::with(['details'])
            ->where('companyid', Session::get('uinfo.companyid', 'admin'))
            ->where('id', $id)
            ->find();
        if (empty($data)) {
            return returnFail('数据不存在');
        } else {
            return returnRes(true, '', $data);
        }
    }
    public function adddiaobo($data = [], $return = false){
        if (request()->isPost()) {
            $companyId = Session::get('uinfo.companyid', 'admin');
            $count = \app\admin\model\KcDiaobo::whereTime('create_time', 'today')->count();
            $data = request()->post();
            $data["status"] = 0;
            $data['create_operator_name'] = Session::get("uinfo.name", "admin");
            $data['create_operator_id'] = Session::get("uid", "admin");
            $data['companyid'] = $companyId;
            $data['system_number'] = 'KCPD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            if (!$return) {
                Db::startTrans();
            }
            try {
                model("KcDiaobo")->allowField(true)->data($data)->save();
                $id = model("KcPandian")->getLastInsID();
                foreach ($data["detail"] as $c => $v) {
                    $dat['details'][$c]['id']=$v["id"];
                    $dat['details'][$c]['counts']=$v["old_counts"]-$v["counts"];
                    $dat['details'][$c]['zhongliang']=$v["old_zhongliang"]-$v["zhongliang"];
                    $dat['details'][$c]['jianshu']= intval( floor($dat['details'][$c]['counts']/$v["zhijian"]));
                    $dat['details'][$c]['lingzhi']= $dat['details'][$c]['counts']%$v["zhijian"];
                    $data['details'][$c]['companyid'] = $companyId;
                    $data['details'][$c]['pandian_id'] = $id;
                    unset($v["id"]);
                }
                //修改库存数量
                model("KcSpot")->saveAll($dat['details']);
                //添加到
                model('KcPandianMx')->saveAll($data['details']);
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