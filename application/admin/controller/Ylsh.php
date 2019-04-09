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
        ])->where('companyid', $this->getCompanyId());
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
            $res=model("KcYlSh")->allowField(true)->saveAll($data["data"]);
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
            $res=model("KcYlSh")->allowField(true)->saveAll($data["data"]);
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
            $data['system_number'] = 'XJYHYEQC' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            if (!$return) {
                Db::startTrans();
            }
            try {
                model("init_bank")->allowField(true)->data($data)->save();
                $id = model("init_bank")->getLastInsID();
                foreach ($data["detail"] as $c => $v) {
                    $data['details'][$c]['companyid'] = $companyId;
                    $data['details'][$c]['bank_id'] = $id;
                }
                model('InitBankMx')->saveAll($data['details']);
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