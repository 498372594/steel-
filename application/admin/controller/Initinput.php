<?php

namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Exception;
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
//    /**批量操作入库
//     * @return \think\response\Json
//     */
//    public function instorage(){
//        if(request()->isPost()){
//            $ids = request()->param("id");
//            $data["rukdh"]="RKD".time();
//            $data["status"]=1;
//            $data['companyid'] = $this->getCompanyId();
//            $data["clerk"]=request()->post("clerk");
//            $data["department"]=request()->post("department");
//            $data['add_name'] = $this->getAccount()['name'];
//            $data['add_id'] = $this->getAccountId();
//            $KC="KC".time();
//            $re=model("instoragelist")->save($data);
//            $res =model("purchasedetails")->where("id","in",$ids)->update(array("is_finished"=>2,"instorage_id"=>model("instoragelist")->id,"instorage_time"=>date("Y-m-d h:s:i",time())));
//            return returnRes($res,'修改失败');
//        }
//    }
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
}