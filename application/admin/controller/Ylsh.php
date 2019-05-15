<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use app\admin\model\KcRk;
use app\admin\model\{KcDiaoboMx, KcPandianMx, KcRkMd, KcSpot, KcYlSh, KcYlShRelease, StockOut};
use app\admin\validate\KcPandian;
use think\{Db, Request, Validate};
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
        $list = $this->getsearchcondition($params, $list);
        $list = $list->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }


    /**锁货
     * @return \think\response\Json
     * @throws \Exception
     */
    public function lock()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $detailValidate = new \app\admin\validate\KcYlSh();
            $num = 1;
            foreach ($data as $k => $v) {
                if (!$detailValidate->check($v)) {

                    return returnFail('请检查第' . $num . '行  ' . $detailValidate->getError());
                }
                $data[$k]['yuliu_type'] = "已预留";
                $data[$k]['companyid'] = $this->getCompanyId();
                $data[$k]['create_operator_id'] = $this->getAccountId();
                $num++;

            }
            $res = model("KcYlSh")->allowField(true)->saveAll($data);
            return returnRes($res, '锁定');
        }
    }

    /**获取锁货信息
     * @return \think\response\Json
     */
    public function getlock()
{
$params = request()->param();
$list = db("ViewKcYlsh")->where('companyid', $this->getCompanyId());
if (!empty($params['ids'])) {
$list->where('id', 'in', $params['ids']);
}
    if (!empty($params['is_pass'])) {
        $list->where('is_pass', 1);
    }
$list = $this->getsearchcondition($params, $list);
$list = $list->paginate(10);
return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
}
    /**延期
     * @return \think\response\Json
     * @throws \Exception
     */
    public function postpone()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $res = model("KcYlSh")->allowField(true)->saveAll($data);
            return returnRes($res, '锁货延迟失败');
        }
    }

    /**根据仓库id查询库存
     * @param int $store_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function storespot($store_id = 0)
    {
        $list = model("ViewSpotMx")->where(array("companyid" => $this->getCompanyId(), "store_id" => $store_id))->select();
        return returnRes($list, '没有数据，请添加后重试', $list);

    }

//

    /**盘点列表
     * @return \think\response\Json
     */
    public function pandianlist()
    {
        $params = request()->param();
        $list = $list = \app\admin\model\KcPandian::where('companyid', $this->getCompanyId());
        $list = $this->getsearchcondition($params, $list);
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
        $data = \app\admin\model\KcPandian::with(['details' => ['specification', 'jsfs', 'storage', 'chandiData', 'caizhiData', 'pinmingData', 'pjlxData', 'createoperatordata', 'saleoperatordata', 'udpateoperatordata', 'checkoperatordata'], 'createoperatordata', 'saleoperatordata', 'udpateoperatordata', 'checkoperatordata', 'storageData'])
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
        $list = $list = \app\admin\model\KcDiaobo::with(['createoperatordata', 'saleoperatordata', 'udpateoperatordata', 'checkoperatordata'])->where('companyid', $this->getCompanyId());
        if (!empty($params['system_number'])) {
            $list->where("system_number", $params['system_number']);
        }
        if (!empty($params['beizhu'])) {
            $list->where("beizhu", $params['beizhu']);
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
        $data = \app\admin\model\KcDiaobo::with(['details' => ["jsfsData", "specification", "storageData", "newstorageData", "pinmingData", "caizhiData", "chandiData", "customData"], 'createoperatordata', 'saleoperatordata', 'udpateoperatordata', 'checkoperatordata'])
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
    public function adddiaobo()
    {
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
                $calSpot = (new KcSpot())->calSpot($mjo["changdu"], $mjo["kuandu"], $mjo["jijiafangshi_id"], $mjo["mizhong"], $mjo["jianzhong"], $mjo["counts"], $mjo["zhijian"], $mjo["zhongliang"], $mjo["price"],
                    $mjo["shuiprice"], $mjo["shuie"]);
                $mjo["sumprice"] = $calSpot["sumprice"];
                $mjo["sum_shui_price"] = $calSpot["sum_shui_price"];
                $mx->allowField(true)->save($mjo);
                $rk = (new KcRk())->insertRuku($mx["id"], 1, $db["yw_time"], $db["group_id"], $db["system_number"], $db["create_operator_id"], $this->getAccountId(), $companyId);
                $ck = (new StockOut())->insertChuku($mx["id"], 1, $db["yw_time"], $db["group_id"], $db["system_number"], $db["create_operator_id"], $this->getAccountId(), $companyId);
                $spot = KcSpot::where("id", $mjo["spot_id"])->find();
                $mjo["cb_price"] = $spot["cb_price"];

                (new KcRk())->insertRkMxMd($rk, $mx["id"], 1, $db["yw_time"], $db["system_number"], null, $mjo["gf_customer_id"], $mx["pinming_id"], $mx["guige_id"], $mx["caizhi_id"], $mx["chandi_id"]
                    , $mx["jijiafangshi_id"], $mx["old_store_id"], $mx["pihao"], $mx["huohao"], null, $mx["beizhu"], null, $mx["houdu"] ?? 0, $mx["kuandu"] ?? 0, $mx["changdu"] ?? 0, $mx["zhijian"], $mx["lingzhi"] ?? 0, $mx["jianshu"] ?? 0,
                    $mx["counts"] ?? 0, $mx["zhongliang"] ?? 0, $mx["price"], $calSpot["sumprice"], $mx["shuiprice"], $calSpot["sum_shui_price"], $calSpot["shuie"], null, null, $this->getAccountId(), $this->getCompanyId());
//                dump($mx);die;
                (new StockOut())->insertCkMxMd($ck, $mx['spot_id'] ?? '', $mx['id'], "1",
                    $db['yw_time'], $db['system_number'], $mjo['gf_customer_id'], $mx['pinming_id'], $mx['caizhi_id'], $mx['chandi_id'],
                    $mx['jijiafangshi_id'], $mx['old_store_id'], $mx['houdu'] ?? 0, $mx['kuandu'] ?? 0,
                    $mx['changdu'] ?? 0, $mx['zhijian'], $mx['lingzhi'] ?? 0, $mx['jianshu'],
                    $mx['counts'] ?? 0, $mx['zhongliang'], $mx['price'], $mx['sumprice'],
                    $mx['shuiprice'] ?? 0, $mx['sum_shui_price'], $mx['shuie'], $mx['mizhong'],
                    $mx['jianzhong'], null, '', $this->getAccountId(), $this->getCompanyId());
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
    public function addpandian()
    {
        Db::startTrans();
        try {
            $data = request()->post();
            $validate = new \app\admin\validate\KcPandian();

            if (!$validate->check($data)) {

                return returnFail($validate->getError());
            }

            $addList = [];
            $updateList = [];
            $detailValidate = new \app\admin\validate\KcPandianMx();
            $num = 1;

//            dump($data['details']);die;
            foreach ($data['detail'] as $item) {
                if (!$detailValidate->check($item)) {

                    return returnFail('请检查第' . $num . '行  ' . $detailValidate->getError());
                }

                if (empty($item['id'])) {
                    $addList[] = $item;
                } else {
                    $updateList[] = $item;
                }
                $num++;
            }

            $companyId = $this->getCompanyId();

            if (empty($data['id'])) {
                $count = \app\admin\model\KcPandian::whereTime('create_time', 'today')
                    ->where('companyid', $companyId)
                    ->count();

                //数据处理
                $systemNumber = 'KCPD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
                $data['add_id'] = $this->getAccountId();
                $data['companyid'] = $companyId;
                $data['system_number'] = $systemNumber;
                $pd = new \app\admin\model\KcPandian();
                $pd->allowField(true)->data($data)->save();
                $pd_id = $pd["id"];

            } else {
                $pd = \app\admin\model\KcPandian::where('companyid', $companyId)->where('id', $data['id'])->find();
                $pd_id = $pd["id"];
                if (empty($pd)) {
                    throw new Exception("对象不存在");
                }
                if ($pd["status"] == 1) {
                    throw new Exception("该单据已经作废");
                }

                $pd->allowField(true)->data($data)->save();
            }

            //删除
            if (!empty($data["delete_mx_ids"])) {
                $deleteList = model("kc_pandian_mx")->where('id', 'in', $data["delete_mx_ids"])->select();
                foreach ($deleteList as $pd) {
                    if ($pd["pandian_type"] == 1) {
                        (new KcRk())->deleteRuku($pd["id"], 2);
                    } else if ($pd["pandian_type"] == "盘亏") {
                        (new  StockOut())->deleteChuku($pd["id"], 2);
                    }
                    $pd->delete();
                }
            }

            //更新
            if (!empty($updateList)) {
                throw new Exception('盘点已入库禁止修改');
            }

            if (!empty($addList)) {
                foreach ($addList as $mjo) {

                    $mjo['companyid'] = $companyId;
                    $mjo['pandian_id'] = $pd_id;
                    $mx = new KcPandianMx();
                    $mx->allowField(true)->isUpdate(false)->save($mjo);
                    if ($mx["pandian_type"] == "盘盈") {
                        $rkCount = KcRk::where("data_id", $pd["id"])->count();
                        if ($rkCount == 0) {

                            $rk = (new KcRk())->insertRuku($pd["id"], 2, $pd["yw_time"], $pd["group_id"], $pd["system_number"], $pd["sale_operator_id"], $this->getAccountId(), $this->getCompanyId());

                        } else {

                            $rk = (new KcRk())->where("data_id", $pd["id"])->find();
                        }

                        (new KcRk())->insertRkMxMd($rk, $mx["id"], 1, $pd["yw_time"], $pd["system_number"], null, null, $mx["pinming_id"], $mx["guige_id"], $mx["caizhi_id"], $mx["chandi_id"]
                            , $mx["jijiafangshi_id"], $mx["store_id"], $mx["pihao"], $mx["huohao"], null, $mx["beizhu"], null, $mx["houdu"] ?? 0, $mx["kuandu"] ?? 0, $mx["changdu"] ?? 0, $mx["zhijian"], $mx["lingzhi"] ?? 0, $mx["jianshu"] ?? 0,
                            $mx["counts"] ?? 0, $mx["zhongliang"] ?? 0, $mx["price"], $mx["sumprice"], $mx["shuiprice"], $mx["sum_shui_price"], $mx["shuie"], null, null, $this->getAccountId(), $this->getCompanyId());
                    } elseif ($mx["pandian_type"] == "盘亏") {
                        $ckCount = StockOut::where("data_id", $pd["id"])->count();
                        if ($ckCount == 0) {
                            $ck = (new StockOut())->insertChuku($pd["id"], 2, $pd["yw_time"], $pd["group_id"], $pd["system_number"], $pd["sale_operator_id"], $this->getAccountId(), $this->getCompanyId());
                        } else {
                            $ck = (new StockOut())->where("data_id", $pd["id"])->find();
                        }
                        (new StockOut())->insertCkMxMd($ck, $mx["spot_id"], $mx["id"], 2, $ck["yw_time"], null, null,
                            $mx["guige_id"], $mx["caizhi_id"], $mx["chandi_id"], $mx["jijiafangshi_id"], $mx["store_id"], $mx["houdu"], $mx["kuandu"], $mx["changdu"], $mx["zhijian"]
                            , $mx["lingzhi"], $mx["jianshu"], $mx["counts"], $mx["zhongliang"], $mx["price"], $mx["sumprice"], $mx["shuiprice"], $mx["sum_shui_price"], $mx["shuie"], $mx["mizhong"], $mx["jianzhong"], null
                            , $mx["ykreason"], $this->getAccountId(), $this->getCompanyId());
                    }
                }

            }
            Db::commit();
            return returnSuc(['id' => $pd['id']]);
        } catch (\Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    public function pandiancancel($id = 0)
    {
        Db::startTrans();
        try {
            $pd = \app\admin\model\KcPandian::get($id);
            if (empty($pd)) {
                throw new Exception("对象不存在");
            }
            if ($pd->companyid != $this->getCompanyId()) {
                throw new Exception("对象不存在");
            }
            if (!empty($pd['data_id'])) {
                throw new Exception("当前单据是只读单据,请到关联单据作废");
            }
            if ($pd['status'] == 1) {
                throw new Exception("该单据已经作废");
            }
            $pd->status = 1;
            $pd->save();

            $mxList = KcPandianMx::where('pandian_id', $pd['id'])->select();
            foreach ($mxList as $kcPandianMx) {
                if ($kcPandianMx["pandian_type"] == "盘盈") {
                    (new KcRk())->deleteRuku($kcPandianMx["id"], 2);
                } else if ($kcPandianMx["pandian_type"] == "盘亏") {
                    (new StockOut())->deleteChuku($kcPandianMx["id"], 2);
                }

            }
            Db::commit();
            return returnSuc();
        } catch (\Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**延期
     * @return \think\response\Json
     * @throws \Exception
     */
    public function ylshedit()
    {
        if (request()->isPost()) {
            $data = request()->post();
            Db::startTrans();
            try {
                foreach ($data as $key => $item) {
                    $dat[$key]["id"] = $item["id"];
                    $data[$key]["ylsh_id"] = $item["id"];
                    unset($data[$key]["id"]);
                    $dat[$key]["is_pass"] = 1;
                    $dat[$key]["update_operator_id"] = $this->getAccountId();
                }
                model("KcYlShLog")->isUpdate(false)->allowField(true)->saveAll($data);
                $res = model("KcYlSh")->isUpdate(true)->allowField(true)->saveAll($dat);
                Db::commit();
                return returnRes($res, '锁货延迟修改审核提交失败');
            } catch (\Exception $e) {
                Db::rollback();
                return returnFail($e->getMessage());
            }
        }
    }
    public function ylshpass(){

            $ids = request()->param("ids");
            $ids=explode(",",$ids);
            $list=db("kc_yl_sh_log")->where("ylsh_id","in",$ids)->field("zhongliang,zhijian,jianshu,baoliu_time,kehu_name,sale_operator_id,ylsh_id")->select();

            Db::startTrans();
            try {
                foreach ($list as $key => $item) {
                    $list[$key]["id"] = $item["ylsh_id"];
                    unset($list[$key]["id"]);
                    $list[$key]["id"] = $item["ylsh_id"];
                    $list[$key]["is_pass"] = 2;
                }

                $res = model("KcYlSh")->isUpdate(true)->allowField(true)->saveAll($list);
                Db::commit();
                return returnRes($res, '锁货延迟修改审核通过失败');
            } catch (\Exception $e) {
                Db::rollback();
                return returnFail($e->getMessage());
            }

    }
    public function ylshdeny(){
            $ids = request()->param("ids");
            $reason=request()->param("reason");
            $res=model("KcYlSh")->where("id","in",$ids)->update(array("reason"=>$reason,"is_pass"=>3));
            return returnRes($res, '锁货延迟修改提交失败');

    }
    public function release(){
        if (request()->isPost()) {
            $data = request()->post();
            Db::startTrans();
            try {
                foreach ($data as $key => $ja) {

                  if(empty($ja["zhongliang"])){
                      throw new Exception("释放重量不能为空");
                  }
                  $ylsh=new KcYlSh();
                  $ylsh= $ylsh->where("id",$ja["id"])->find();
                  if($ylsh["zhongliang"]<$ja["zhongliang"]){
                      throw new Exception("释放数量不能大于预留数量");
                  }
                    if($ylsh["shuliang"]<$ja["shuliang"]){
                        throw new Exception("释放数量不能大于预留数量");
                    }
                    if(empty($ylsh["data_id"])){
                        throw new Exception("此数据为销售预订,不能释放");
                    }
                    $ylsh->shuliang=$ylsh["shuliang"]-$ja["shuliang"];
                    $ylsh->zhongliang=$ylsh["zhongliang"]-$ja["zhongliang"];
                    $ylsh->guobang_zhongliang=$ylsh["zhongliang"]-$ja["zhongliang"];
                    $ylsh->jianshu=intval(($ylsh["shuliang"])/$ja["zhijian"]);
                    $ylsh->lingzhi=($ylsh["shuliang"])%$ja["zhijian"];
                    unset($ja["id"]);
                    $ja["spot_id"]=$ylsh["spot_id"];
                    $ja["kehu_name"]=$ylsh["kehu_name"];
                    $ja["baoliu_time"]=$ylsh["baoliu_time"];
                    $ja["data_id"]=$ylsh["data_id"];
                    $ja["price"]=$ylsh["price"];
                    $ja["companyid"]=$this->getCompanyId();
                    $ja["yuliu_type"]="已撤销";
//                    dump($ja);die;
                    model("kc_yl_sh_release")->isUpdate(false)->allowField(true)->save($ja);
                  $ylsh->isUpdate(true)->allowField(true)->save($ylsh);
                }
                Db::commit();
                return returnRes($ylsh->id, '锁货延迟修改提交失败');
            } catch (\Exception $e) {
                Db::rollback();
                return returnFail($e->getMessage());
            }
        }
    }

    /**撤销报表
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function getrelease(){
        $params = request()->param();
        $list = $list = \app\admin\model\KcYlShRelease::where('companyid', $this->getCompanyId());
        $list=$this->getsearchcondition($params,$list);
        $list = $list->paginate(10);
        return returnRes(true, '', $list);
    }
}