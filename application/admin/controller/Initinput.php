<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Exception;
use app\admin\model\{KcSpot};
use think\Session;

class Initinput extends Right
{
    public function instorageinit()
    {
        if (request()->isPost()) {
            $ids = request()->param("id");
            $count = \app\admin\model\Instoragelist::whereTime('create_time', 'today')->count();
            $data["rkdh"] = "RKD" . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            $data["status"] = 1;
            $data['companyid'] = $this->getCompanyId();
            $data["clerk"] = request()->post("clerk");
            $data["department"] = request()->post("department");
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            $data['service_time'] = date("Y-m-d H:s:i", time());
            $data['remark'] = request()->post("remark");
            $data['remark'] = request()->post("remark");
//            $KC="KC".time();
            $re = model("instoragelist")->save($data);
            $purchasedetails = request()->post("purchasedetails");
            $instorage_id = model("instoragelist")->id;
            foreach ($purchasedetails as $key => $value) {
                $purchasedetails["$key"]["instorage_id"] = $instorage_id;

                $count = \app\admin\model\Purchasedetails::whereTime('create_time', 'today')->count();
                $purchasedetails["$key"]["zyh"] = "ZYH" . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            }
            $res = model("purchasedetails")->savaAll($purchasedetails);
//            $res =model("purchasedetails")->where("id","in","ids")->update(array("is_finished"=>2,"instorage_id"=>$instorage_id));
            return returnRes($res, '失败');
        }
    }

    /**条件搜索
     * @param $params
     * @param $list
     * @return mixed
     */
    public function getinitsearch($params, $list)
    {
        //系统单号
        if (!empty($params['system_number'])) {
            $list->where('system_number', $params['system_number']);
        }
        //业务时间
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('yw_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        //制单时间
        if (!empty($params['create_time_start'])) {
            $list->where('create_time', '>=', $params['create_time_start']);
        }
        if (!empty($params['create_time_end'])) {
            $list->where('create_time', '<=', date('Y-m-d', strtotime($params['create_time_end'] . ' +1 day')));
        }
        //制单人
        if (!empty($params['create_operator_id'])) {
            $list->where('create_operator_id', $params['create_operator_id']);
        }
        //修改人
        if (!empty($params['update_operator_id'])) {
            $list->where('update_operator_id', $params['update_operator_id']);
        }
        //修改人
        if (!empty($params['update_operator_id'])) {
            $list->where('update_operator_id', $params['update_operator_id']);
        }
        //状态
        if (!empty($params['status'])) {
            $list->where('status', $params['status']);
        }
        //部门
        if (!empty($params['group_id'])) {
            $list->where('group_id', $params['group_id']);
        }
        //备注
        if (!empty($params['beizhu'])) {
            $list->where('beizhu', 'like', '%' . $params['beizhu'] . '%');
        }
        return $list;
    }

    /**银行账户余额初始录入列表
     * @return \think\response\Json
     */
    public function initbank()
    {
        $params = request()->param();
        $list = $list = \app\admin\model\InitBank::where('companyid', $this->getCompanyId());
        $list = $this->getinitsearch($params, $list);
        $list = $list->paginate(10);
        return returnRes(true, '', $list);
    }

    /**银行账户余额初始录入明细列表
     * @param int $id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function initbankdetail($id = 0)
    {
        $data = \app\admin\model\InitBank::with(['details'])
            ->where('companyid', $this->getCompanyId())
            ->where('id', $id)
            ->find();
        if (empty($data)) {
            return returnFail('数据不存在');
        } else {
            return returnRes(true, '', $data);
        }
    }

    /**银行账户余额初始录入添加修改
     * @param array $data
     * @param bool $return
     * @return string|\think\response\Json
     * @throws \Exception
     */

    public function initbankadd($data = [], $return = false)
    {
        if (request()->isPost()) {
            $companyId = $this->getCompanyId();
            $count = \app\admin\model\Salesorder::whereTime('create_time', 'today')->count();
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

    /**
     *应收账款余额初始录入
     */
    public function initysk(){
        $params = request()->param();
        $list = $list = \app\admin\model\InitYsfk::where(array("companyid"=>$this->getCompanyId(),"type"=>0));
        $list = $this->getinitsearch($params, $list);
        $list = $list->paginate(10);
        return returnRes(true, '', $list);
    }
    /**
     *应付账款余额初始录入
     */
    public function inityfk(){
        $params = request()->param();
        $list = $list = \app\admin\model\InitYsfk::where(array("companyid"=>$this->getCompanyId(),"type"=>1));
        $list = $this->getinitsearch($params, $list);
        $list = $list->paginate(10);
        return returnRes(true, '', $list);
    }

    /**库存初始化录入
     * @param array $data
     * @param bool $return
     * @return string|\think\response\Json
     * @throws \Exception
     */
    public function addkc($data = [], $return = false){
        if (request()->isPost()) {
            $companyId = $this->getCompanyId();
            $count = \app\admin\model\InitKc::whereTime('create_time', 'today')->count();
            $data = request()->post();
            $data["status"] = 0;
            $data['create_operator_name'] = $this->getAccount()['name'];
            $data['create_operator_id'] = $this->getAccountId();
            $data['companyid'] = $companyId;
            $data['system_number'] = 'KCQCYE' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            if (!$return) {
                Db::startTrans();
            }
            try {
                model("InitKc")->allowField(true)->data($data)->save();
                $id = model("InitKc")->getLastInsID();
                foreach ($data["details"] as $c => $v) {
                    $data['details'][$c]['companyid'] = $companyId;
                    $data['details'][$c]['kc_id'] = $id;
                }
                //添加其他入库明细
                model('InitKcMx')->allowField(true)->saveAll($data['details']);
                $count1 = \app\admin\model\KcSpot::whereTime('create_time', 'today')->count();
                //添加到库存

                foreach ($data['details'] as $c => $v) {
                    $spot = [
                        'companyid' => $companyId,
                        'ruku_type' => 8,
                        'piaoju_id' => $data['piaoju_id'],
                        'resource_number' => "KC" . date('Ymd') . str_pad($count1 + 1, 3, 0, STR_PAD_LEFT),
                        'guige_id' => $v['guige_id'],
                        'data_id' => $id,
                        'pinming_id' => $v['pinming_id'],
                        'store_id' => $v['store_id']?? '',
                        'caizhi_id' => $v['caizhi_id'] ?? '',
                        'chandi_id' => $v['chandi_id'] ?? '',
                        'jijiafangshi_id' => $v['jijiafangshi_id'],
                        'houdu' => $v['houdu'] ?? '',
                        'kuandu' => $v['kuandu'] ?? '',
                        'changdu' => $v['changdu'] ?? '',
                        'lingzhi' => $v['lingzhi'] ?? '',
                        'jianshu' => $v['jianshu'] ?? '',
                        'zhijian' => $v['zhijian'] ?? '',
                        'counts' => $v['counts'] ?? '',
                        'zhongliang' => $v['zhongliang'] ?? '',
                        'price' => $v['price'] ?? '',
                        'cb_price' => $v['price'] ?? '',
                        'cb_sumprice' => $v['sumprice'] ?? '',
                        'cb_shuie' => $v['shuie'] ?? '',
                        'cb_shui_price' => $v['shui_price'] ?? '',
                        'shuie' => $v['shuie'] ?? '',
                        'shui_price' => $v['shui_price'] ?? '',
                        'sum_shui_price' => $v['sum_shui_price'] ?? '',
                        'beizhu' => $v['beizhu'] ?? '',
                        'chehao' => $v['chehao'] ?? '',
                        'pihao' => $v['pihao'] ?? '',
                        'sumprice' => $v['sumprice'] ?? '',
                        'huohao' => $v['huohao'] ?? '',
                        'customer_id' => $data['customer_id'],
                        'mizhong' => $v['mizhong'] ?? '',
                        'jianzhong' => $v['jianzhong'] ?? '',
                        'lisuan_zhongliang' => $v["counts"] * $v["changdu"] * $v['mizhong'] / 1000,
                        'guobang_zhizhong' => $v["counts"] == 0 ? 0 : ($v['zhongliang'] / $v["counts"] * $v["zhijian"]),
                        'guobang_zhongliang' => $v["zhongliang"] ?? '',
                        'lisuan_zhizhong' => $v["counts"] == 0 ? 0 : ($v["counts"] * $v["changdu"] * $v['mizhong'] / 1000 / $v["counts"] * $v["zhijian"]),
                        'guobang_jianzhong' => $v["counts"] == 0 ? 0 : ($v['zhongliang'] / $v["counts"]),
                        'lisuan_jianzhong' => $v["counts"] == 0 ? 0 : ($v["counts"] * $v["changdu"] * $v['mizhong'] / 1000 / $v["counts"]),
                        'old_lisuan_zhongliang' => $v["counts"] * $v["changdu"] * $v['mizhong'] / 1000,
                        'old_guobang_zhizhong' => $v["counts"] == 0 ? 0 : ($v['zhongliang'] / $v["counts"] * $v["zhijian"]),
                        'old_lisuan_zhizhong' => $v["counts"] == 0 ? 0 : ($v["counts"] * $v["changdu"] * $v['mizhong'] / 1000 / $v["counts"] * $v["zhijian"]),
                        'old_guobangjianzhong' => $v['counts'] == 0 ? 0 : ($v['zhongliang'] / $v["counts"]),
                        'old_guobangzhongliang' => ($v['zhongliang']) ?? '',
                        'old_lisuan_jianzhong' => $v['counts'] == 0 ? 0 : ($v["counts"] * $v["changdu"] * $v['mizhong'] / 1000 / $v["counts"]),
                        'status' => 0,
                        'guobang_price' => $v['guobang_price'] ?? '',
                        'guobang_shui_price' => $v['guobang_shui_price'] ?? '',
                        'zhi_price' => $v['zhi_price'] ?? '',
                        'zhi_shui_price' => $v['zhi_shui_price'] ?? '',
                        'lisuan_shui_price' => $v['lisuan_shui_price'] ?? '',
                        'lisuan_price' => $v['lisuan_price'] ?? '',
                    ];
                    $spotModel = new KcSpot();
                    $spotModel->allowField(true)->save($spot);
                    $spotIds[$v['index'] ?? -1] = $spotModel->id;
                }
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

    /**库存初始化列表
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function kclist(){
        $params = request()->param();
        $list = $list = \app\admin\model\InitKc::with(['customData','jsfsData','pjlxData','storageData'])
            ->where('companyid', $this->getCompanyId());

       $list=$this->getsearchcondition($params,$list);
        $list = $list->paginate(10);
        return returnRes(true, '', $list);
    }

    /**库存初始化明细
     * @param int $id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function kcmx($id=0){

        $data = $list = \app\admin\model\InitKc::with([ 'details' => ['specification', 'jsfs', 'storage','chandiData','caizhiData','pinmingData'],
            'customData','jsfsData','pjlxData','storageData'])
            ->where('companyid',$this->getCompanyId())
            ->where('id', $id)
            ->find();
        if (empty($data)) {
            return returnFail('数据不存在');
        } else {
            return returnRes(true, '', $data);
        }
    }
    //0为付款，1为收款
    public function ysfk($type=0){
        $params = request()->param();
        $list = $list = \app\admin\model\InitYsfk::where('companyid', $this->getCompanyId());
        $list->where('type',$type);
        $list=$this->getsearchcondition($params,$list);
        $list = $list->paginate(10);
        return returnRes(true, '', $list);
    }
    public function ysfkmx($id=0){
        $data = $list = \app\admin\model\InitYsfk::with([ 'details',
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
    public function addysfk($data = [], $return = false)
    {
        if (request()->isPost()) {
            $companyId = $this->getCompanyId();
            $data = request()->post();
            $count = \app\admin\model\InitYsfk::whereTime('create_time', 'today')->where("type",$data["type"])->count();
            $data["status"] = 0;
            $data['create_operator_id'] = $this->getAccountId();
            $data['companyid'] = $companyId;
            if($data["type"]==0){
                $data['system_number'] = 'YFZKYEQC' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            }
            if($data["type"]=1){
                $data['system_number'] = 'YSZKYEQC' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            }

            if (!$return) {
                Db::startTrans();
            }
            try {
                model("InitYsfk")->allowField(true)->data($data)->save();
                $id = model("InitYsfk")->getLastInsID();
                foreach ($data["details"] as $c => $v) {
                    $data['details'][$c]['companyid'] = $companyId;
                    $data['details'][$c]['ysfk_id'] = $id;
                }
                model('InitYsfkMx')->saveAll($data['details']);
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
    //0为付款，1为收款
    public function yskp($type=0){
        $params = request()->param();
        $list = $list = \app\admin\model\InitYskp::where('companyid', $this->getCompanyId());
        $list->where('type',$type);
        $list=$this->getsearchcondition($params,$list);
        $list = $list->paginate(10);
        return returnRes(true, '', $list);
    }
    public function yskpmx($id=0){
        $data = $list = \app\admin\model\InitYskp::with([ 'details'=>['customData','pjlxData'],
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
    public function yskpadd($data = [], $return = false)
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