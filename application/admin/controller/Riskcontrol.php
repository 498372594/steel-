<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use app\admin\model\BaseJiesuanqixian;
use app\admin\model\BaseXinyongedu;
use app\admin\model\SalesEdu;
use think\Db;
use think\Exception;
use think\Session;

class Riskcontrol extends Right
{
    /**结算期限添加
     * @param array $data
     * @param bool $return
     * @return bool|string|Json
     * @throws Exception
     * @throws \Exception
     */
    public function addjiesuanqixian($return = false)
    {
        if (request()->isPost()) {
            $companyId = $this->getCompanyId();
            $data = request()->post();
            $data["status"] = 0;
            $data['create_operator_id'] = $this->getAccountId();
            $data['companyid'] = $companyId;
            if (!$return) {
                Db::startTrans();
            }
            try {
                if (empty($data["id"])) {
                    model("base_jiesuanqixian")->allowField(true)->isUpdate(false)->save($data);
                    $id = model("base_jiesuanqixian")->getLastInsID();
                } else {
                    model("base_jiesuanqixian")->allowField(true)->save($data, $data["id"]);
                    $id = $data["id"];
                }
                if (!$return) {
                    Db::commit();
                    return returnRes(true, '', ['id' => $id]);
                } else {
                    return true;
                }
            } catch (\Exception $e) {
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
    public function jiesuanqixianlist(){
        $params = request()->param();
        $list = BaseJiesuanqixian::with(['createoperatordata', 'udpateoperatordata'])
            ->where('companyid', $this->getCompanyId());
        $list = $this->getsearchcondition($params, $list)->paginate(10);
        return returnRes(true, '', $list);
    }
    public function addxinyongedu($return = false)
    {
        if (request()->isPost()) {
            $companyId = $this->getCompanyId();
            $data = request()->post();
            $data["status"] = 0;
            $data['create_operator_id'] = $this->getAccountId();
            $data['companyid'] = $companyId;
            if (!$return) {
                Db::startTrans();
            }
            try {
                if (empty($data["id"])) {
                    model("base_xinyongedu")->allowField(true)->isUpdate(false)->save($data);
                    $id = model("base_xinyongedu")->getLastInsID();
                } else {
                    model("base_xinyongedu")->allowField(true)->save($data, $data["id"]);
                    $id = $data["id"];
                }
                if (!$return) {
                    Db::commit();
                    return returnRes(true, '', ['id' => $id]);
                } else {
                    return true;
                }
            } catch (\Exception $e) {
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
    public function xinyongedulist(){
        $params = request()->param();
        $list = BaseXinyongedu::with(['createoperatordata', 'udpateoperatordata'])
            ->where('companyid', $this->getCompanyId());
        $list = $this->getsearchcondition($params, $list)->paginate(10);
        return returnRes(true, '', $list);
    }
    public function addsalesedu($return = false)
    {
        if (request()->isPost()) {
            $companyId = $this->getCompanyId();
            $data = request()->post();
            $data["status"] = 0;
            $data['create_operator_id'] = $this->getAccountId();
            $data['companyid'] = $companyId;
            if (!$return) {
                Db::startTrans();
            }
            try {
                if (empty($data["id"])) {
                    model("sales_edu")->allowField(true)->isUpdate(false)->save($data);
                    $id = model("sales_edu")->getLastInsID();
                } else {
                    model("sales_edu")->allowField(true)->save($data, $data["id"]);
                    $id = $data["id"];
                }
                if (!$return) {
                    Db::commit();
                    return returnRes(true, '', ['id' => $id]);
                } else {
                    return true;
                }
            } catch (\Exception $e) {
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
    public function salesedulist(){
        $params = request()->param();
        $list = SalesEdu::with(['createoperatordata', 'udpateoperatordata'])
            ->where('companyid', $this->getCompanyId());
        $list = $this->getsearchcondition($params, $list)->paginate(10);
        return returnRes(true, '', $list);
    }
}