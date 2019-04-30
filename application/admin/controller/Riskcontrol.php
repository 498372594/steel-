<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use app\admin\model\AvaWeight;
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

    /**当天可售重量总额度
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function findValueFortoday(){
        $sql="(SELECT
		distinct round(we.`zhongliang`,3) zhongliang,companyid
		FROM ava_weight we
		WHERE 
		date(we.nowtime) = curdate()) ";
        $data = Db::table($sql)->alias("t")->where("t.companyid",$this->getCompanyId())->find();
        return returnSuc($data);
    }
    public function addAva(){
        $param=request()-post();
        $param["nowtime"]=date("Y-m-d H:s:i",time());
        try{
            $sql="( SELECT 
		(we.`zhongliang` - SUM(we.edu)) leftzhongliang,companyid
		FROM ava_weight we
		WHERE date(we.nowtime) = curdate()
        and we.status!=1) ";
            $leftzhongliang = Db::table($sql)->alias("t")->where("t.companyid",$this->getCompanyId())->value("leftzhongliang");
            if(empty($leftzhongliang)){
                $leftzhongliang=$param["zhongliang"];
            }
            if( $leftzhongliang<$param["edu"]){
                throw new \Exception("今日可售额度为：".$param["zhongliang"].",还剩".$leftzhongliang.",小于设置额度，保存失败！");
            }
            $ava=new AvaWeight();
            $ava->allowField(true)->data($param)->save();
            return returnSuc(['id' => $cg['id']]);
        }
        catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }
    public function getava(){
        $sql="(SELECT we.*
		FROM ava_weight we
		WHERE 
		date(we.nowtime) = curdate())";
        $data = Db::table($sql)->alias("t")->where("t.companyid",$this->getCompanyId())->select();
        return returnSuc($data);
    }
}