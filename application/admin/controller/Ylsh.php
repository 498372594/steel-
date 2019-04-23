<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use app\admin\model\KcRk;
use app\admin\model\{KcDiaoboMx, KcRkMd, KcSpot, StockOut};
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
        $list = $list = \app\admin\model\KcPandian::where('companyid', $this->getCompanyId());
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
            ->where('companyid', $this->getCompanyId())
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
        $list = $list = \app\admin\model\KcDiaobo::with(['createoperatordata','saleoperatordata','udpateoperatordata','checkoperatordata'])->where('companyid',$this->getCompanyId());
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
        $data = \app\admin\model\KcDiaobo::with(['details'=>["jsfsData","specification","storageData","newstorageData","pinmingData","caizhiData","chandiData","customData"],'createoperatordata','saleoperatordata','udpateoperatordata','checkoperatordata'])
            ->where('companyid', $this->getCompanyId())
            ->where('id', $id)
            ->find();
        if (empty($data)) {
            return returnFail('数据不存在');
        } else {
            return returnRes(true, '', $data);
        }
    }

    /**添加调拨
     * @return \think\response\Json
     * @throws \Exception
     */
    public function adddiaobo(){
        if (!request()->isPost()) {
            return returnFail('请求方式错误');
        }

        Db::startTrans();
        try {
            $data = request()->post();

            $validate = new \app\admin\validate\KcDiaobo();
            if (!$validate->check($data)) {
                throw new Exception($validate->getError());
            }

            $addList = [];
            $updateList = [];
            $ja = $data['details'];
            $companyId = $this->getCompanyId();
            if (!empty($ja)) {

                $num = 1;
                $detailsValidate = new \app\admin\validate\KcDiaoboMx();
                foreach ($ja as $object) {

                    $object['companyid'] = $companyId;
                    if (!$detailsValidate->check($object)) {
                        throw new Exception('请检查第' . $num . '行' . $detailsValidate->getError());
                    }
                    $num++;
                    if (empty($object["id"])) {
                        $addList[] = $object;
                    } else {
                        $updateList[] = $object;
                    }
                }
            }

            if (empty($data['id'])) {
                $count = \app\admin\model\KcDiaobo::withTrashed()->whereTime('create_time', 'today')
                    ->where('companyid', $companyId)
                    ->count();

                $data['system_number'] = 'KCDBD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
                $data['create_operator_id'] = $this->getAccountId();
                $data['companyid'] = $companyId;
                $db = new \app\admin\model\KcDiaobo();
                $db->allowField(true)->save($data);
            } else {
                throw new Exception('调拨已入库禁止修改');
//                $db = \app\admin\model\KcQtrk::where('companyid', $companyId)
//                    ->where('id', $data['id'])
//                    ->find();
//                $data['update_operator_id'] = $this->getAccountId();
//                $db->allowField(true)->save($data);
//                $mxList = KcDiaoboMx::where('diaobo_id', $db['id'])->select();
//                if (!empty($mxList)) {
//                    foreach ($mxList as $tbDbMx) {
//                        $rkMdList= KcRkMd::where("data_id",$tbDbMx["id"])->select();
//                        if($rkMdList){
//                            foreach ($rkMdList as $tbRkMd){
//                               KcRk::where("id",$tbRkMd["kc_rk_id"])->save(array("yw_time"=>$db["yw_time"]));
//
//                            }
//                        }
//
//                    }
//                }
            }
            if (!empty($data['deleteMxIds'])) {
                throw new Exception('已入库禁止修改');
            }
//            for (TbKcDiaoboMxObj obj : deleteList) {
//                TbKcDiaoboMx dbmx = (TbKcDiaoboMx)this.mxDao.selectByPrimaryKey(obj.getId());
//       if (dbmx != null)
//       {
//           this.rkDaoImpl.deleteRuku(dbmx.getId(), "1");
//
//           this.ckDaoImpl.deleteChuku(dbmx.getId(), "1");
//           this.mxDao.deleteByPrimaryKey(dbmx);
//       }
//     }


            foreach ($addList as $mjo) {

                $mjo['companyid'] = $companyId;
                $mjo['diaobo_id'] = $db['id'];
                $mx = new KcDiaoboMx();
                $calSpot=(new KcSpot())->calSpot($mjo["changdu"],$mjo["kuandu"],$mjo["jijiafangshi_id"],$mjo["mizhong"],$mjo["jianzhong"],$mjo["counts"],$mjo["zhijian"],$mjo["zhongliang"],$mjo["price"],
                    $mjo["shuiprice"],$mjo["shuie"]);
                $mjo["sumprice"]=$calSpot["sumprice"];
                $mjo["sum_shui_price"]=$calSpot["sum_shui_price"];
                $mx->allowField(true)->save($mjo);
                $rk=(new KcRk())->insertRuku($mx["id"],1,$db["yw_time"],$db["group_id"],$db["system_number"],$db["create_operator_id"],$this->getAccountId(),$companyId);
                $ck=(new StockOut())->insertChuku($mx["id"],1,$db["yw_time"],$db["group_id"],$db["system_number"],$db["create_operator_id"],$this->getAccountId(),$companyId);
                $spot= KcSpot::where("id",$mjo["spot_id"])->find();
                $mjo["cb_price"]=$spot["cb_price"];

                (new KcRk())->insertRkMxMd($rk, $mx["id"], 1, $db["yw_time"], $db["system_number"], null, $mjo["gf_customer_id"], $mx["pinming_id"], $mx["guige_id"], $mx["caizhi_id"], $mx["chandi_id"]
                    , $mx["jijiafangshi_id"], $mx["old_store_id"], $mx["pihao"], $mx["huohao"], null, $mx["beizhu"],null, $mx["houdu"] ?? 0, $mx["kuandu"] ?? 0, $mx["changdu"] ?? 0, $mx["zhijian"], $mx["lingzhi"] ?? 0, $mx["jianshu"] ?? 0,
                    $mx["counts"] ?? 0, $mx["zhongliang"] ?? 0, $mx["price"], $calSpot["sumprice"], $mx["shuiprice"], $calSpot["sum_shui_price"], $calSpot["shuie"], null,null, $this->getAccountId(), $this->getCompanyId());
               (new StockOut())->insertCkMxMd($ck, $mx['kc_spot_id'] ?? '', $mx['id'], "1",
                   $db['yw_time'], $db['system_number'], $mjo['gf_customer_id'], $mx['pinming_id'], $mx['caizhi_id'], $mx['chandi_id'],
                    $mx['jijiafangshi_id'], $mx['old_store_id'], $mx['houdu'] ?? 0, $mx['kuandu'] ?? 0,
                    $mx['changdu'] ?? 0, $mx['zhijian'], $mx['lingzhi'] ?? 0, $mx['jianshu'],
                    $mx['counts'] ?? 0, $mx['zhongliang'], $mx['price'], $mx['sumprice'],
                    $mx['shuiprice'] ?? 0, $mx['sum_shui_price'], $mx['shuie'], null,
                    null, null, '', $this->getAccountId(), $this->getCompanyId());
            }
            Db::commit();
            return returnSuc(['id' => $db['id']]);
        } catch (\Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }
//    public function adddiaobo($data = [], $return = false){
//        if (request()->isPost()) {
//            $companyId =$this->getCompanyId();
//            $count = \app\admin\model\KcDiaobo::whereTime('create_time', 'today')->count();
//            $data = request()->post();
//            $data["status"] = 0;
//            $data['create_operator_id'] =$this->getAccountId();
//            $data['companyid'] = $companyId;
//            $data['system_number'] = 'KCPD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
//            if (!$return) {
//                Db::startTrans();
//            }
//            try {
//                model("KcDiaobo")->allowField(true)->data($data)->save();
//                $id = model("KcDiaobo")->getLastInsID();
//                foreach ($data["detail"] as $c => $v) {
//                    $dat['details'][$c]['id']=$v["spot_id"];
//                    $dat['details'][$c]['counts']=$v["old_counts"]-$v["counts"];
//                    $dat['details'][$c]['zhongliang']=$v["old_zhongliang"]-$v["zhongliang"];
//                    $dat['details'][$c]['jianshu']= intval( floor($dat['details'][$c]['counts']/$v["zhijian"]));
//                    $dat['details'][$c]['lingzhi']= $dat['details'][$c]['counts']%$v["zhijian"];
//                    $data['details'][$c]['companyid'] = $companyId;
//                    $data['details'][$c]['pandian_id'] = $id;
//                    unset($v["id"]);
//                }
//                //修改库存数量
//                model("KcSpot")->saveAll($dat['details']);
//                //添加到
//                model('KcDiaoboMx')->saveAll($data['details']);
//                if (!$return) {
//                    Db::commit();
//                    return returnRes(true, '', ['id' => $id]);
//                } else {
//                    return true;
//                }
//            } catch (Exception $e) {
//                if ($return) {
//                    return $e->getMessage();
//                } else {
//                    Db::rollback();
//                    return returnFail($e->getMessage());
//                }
//            }
//        }
//        if ($return) {
//            return '请求方式错误';
//        } else {
//            return returnFail('请求方式错误');
//        }
//    }
}