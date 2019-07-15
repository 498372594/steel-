<?php


namespace app\admin\controller;


use app\admin\model\InvCgspHx;
use app\admin\validate\KcQtrk;
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    response\Json};

class Invcgsp extends Right
{
    public function add($data)
    {

    }

    /**获取单据列表
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function invywtype()
    {
        $list = model("inv_ywtype")->select();
        return returnSuc($list);
    }

    public function invcgsp()
    {
        $params = request()->param();
        $list = \app\admin\model\InvCgsp::with(["customData", "pjlxData"])->where('companyid', $this->getCompanyId());
        //业务时间
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('yw_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        //往来单位
        if (!empty($params['customer_id'])) {
            $list->where('gys_id', $params['customer_id']);
        }
        //票据类型
        if (!empty($params['piaoju_id'])) {
            $list->where('piaoju_id', $params['piaoju_id']);
        }
        //系统单号
        if (!empty($params['system_number'])) {
            $list->where('system_number', 'like', '%' . $params['system_number'] . '%');
        }
        //备注
        if (!empty($params['beizhu'])) {
            $list->where('beizhu', 'like', '%' . $params['beizhu'] . '%');
        }
        $list = $list->paginate(10);
        return returnRes(true, '', $list);
    }

    public function invcgspmx($id = 0)
    {
        $data = $list = \app\admin\model\InvCgsp::with(['details' => ['guigeData', 'pinmingData'], 'pjlxData', 'customData'
        ])
            ->where('companyid', $this->getCompanyId())
            ->where('id', $id)
            ->find();
        return returnRes(true, '', $data);
    }

//    public function cgspadd($data = [], $return = false)
//    {
//        if (request()->isPost()) {
//            $companyId = $this->getCompanyId();
//            $data = request()->post();
//            $count = \app\admin\model\InvCgsp::whereTime('create_time', 'today')->where("type", $data["type"])->count();
//            $data["status"] = 0;
//            $data['create_operator_id'] = $this->getAccountId();
//            $data['companyid'] = $companyId;
//            $data['system_number'] = 'CGSP' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
//
//            if (!$return) {
//                Db::startTrans();
//            }
//            try {
//                model("InvCgsp")->allowField(true)->data($data)->save();
//                $id = model("InvCgsp")->getLastInsID();
//                foreach ($data["details"] as $c => $v) {
//                    $dat['details'][$c]['id'] = $v["inv_id"];
//                    $dat['details'][$c]['yhx_zhongliang'] = $v["yhx_zhongliang"] + $v["zhongliang"];
//                    $dat['details'][$c]['yhx_price'] = $v["yhx_zhongliang"] + $v["sum_shui_price"];
//                    $data['details'][$c]['companyid'] = $companyId;
//                    $data['details'][$c]['cgsp_id'] = $id;
//                    $data['details'][$c]['yw_type'] = 2;
//                    $data['details'][$c]['data_id'] = $v["inv_id"];
//                    $data['details'][$c]['system_number'] = $v["system_number"] . "1";
//                }
//                model('Inv')->allowField(true)->saveAll($dat['details']);
//                model('InvCgspHx')->allowField(true)->saveAll($data['details']);
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

    public function cgspadd()
    {
        if (!request()->isPost()) {
            return returnFail('请求方式错误');
        }

        Db::startTrans();
        try {
            $data = request()->post();


            $addList = [];
            $updateList = [];
            $ja = $data['details'];
            $companyId = $this->getCompanyId();
            if (!empty($ja)) {

                $num = 1;
                $detailsValidate = new \app\admin\validate\InvCgspHx();
                foreach ($ja as $object) {

                    $object['companyid'] = $companyId;
                    if (!$detailsValidate->check($object)) {
                        throw new Exception('请检查第' . $num . '行' . $detailsValidate->getError());
                    }
                    $num++;

                    if (empty($object["zhongliang"])) {
                        throw new Exception("重量不能为空");
                    }
                    if (empty($object["id"])) {
                        $addList[] = $object;
                    } else {
                        $updateList[] = $object;
                    }
                }
            }

            if (empty($data["id"])) {
                $count = \app\admin\model\InvCgsp::whereTime('create_time', 'today')->where("companyid", $companyId)->count();
                $data['system_number'] = 'CGSP' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
                $data['create_operator_id'] = $this->getAccountId();
                $data['companyid'] = $companyId;
                $cgsp = new \app\admin\model\InvCgsp();
                $cgsp->allowField(true)->save($data);
            } else {
                $cgsp = \app\admin\model\InvCgsp::where("companyid", $companyId)->where('id', $data['id'])
                    ->find();
                if ($cgsp["status"] == 1) {
                    throw new Exception("该单据已经作废");
                }
                if (empty($cgsp["status"])) {
                    throw new Exception("该单据只读状态,不能修改");
                }
                $data['update_operator_id'] = $this->getAccountId();
                $cgsp->allowField(true)->save($data);
            }
            if (!empty($data['deleteMxIds'])) {
                foreach ($data["deleteMxIds"] as $delete_id) {
                    $mx = InvCgspHx::where("id", $delete_id)->find();
                    \app\admin\model\InvCgsp::destroy(array("id", $delete_id));
                    \app\admin\model\Inv::jianMoney($mx["data_id"], $mx["sum_shui_price"], $mx["zhongliang"]);
                }
            }
            if (!empty($updateList)) {
                foreach ($updateList as $mjo) {
                    $hx = InvCgspHx::where("id", $mjo["id"])->find();
                    if (!empty($mx["data_id"])) {
                        \app\admin\model\Inv::tiaoMoney($hx["data_id"], $hx["sum_shui_price"], $mjo["sum_shui_price"], $hx["zhongliang"], $mjo["zhongliang"]);
                    }
                    $mx = new InvCgspHx();
                    $mx->isUpdate(true)->allowField(true)->save($mjo);
                }
            }
            if (!empty($addList)) {
                foreach ($addList as $mjo) {
                    $mjo["cgsp_id"] = $cgsp["id"];
                    if (!empty($mjo["data_id"])) {
                        $inv = \app\admin\model\Inv::where("id", $mjo["data_id"])->find();
                        $mjo["system_number"] = $inv["system_number"];
                        $mjo["yw_time"] = $inv["yw_time"];

                        (new \app\admin\model\Inv())->addMoney($mjo["data_id"], $mjo["sum_shui_price"], $mjo["zhongliang"]);
                    }
                    $hx = new InvCgspHx();

                    $hx->allowField(true)->save($mjo);
                }
            }
            Db::commit();
            return returnSuc(['id' => $cgsp['id']]);
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**
     * 采购收票作废
     */
    public function cancel($id = 0)
    {
        if (!request()->isPost()) {
            return returnFail('请求方式错误');
        }
        Db::startTrans();
        try {
            $cgsp = \app\admin\model\InvCgsp::get($id);
//            $bank = \app\admin\model\CapitalCqk::get($id);
            if (empty($cgsp)) {
                throw new Exception("对象不存在");
            }
            if ($cgsp["status"] == 1) {
                throw new Exception("该单据已经作废");
            }
            if (!empty($cgsp["jcx_id"])) {
                throw new Exception("该单据只读状态,不能作废");
            }
            $cgsp->status = 1;
            $cgsp->save();
            $list = \app\admin\model\InvCgsp::where("cgsp_id", $cgsp["id"])->select();

            foreach ($list as $hx) {
                if (!empty($hx["data_id"])) {
                    (new \app\admin\model\Inv())->jianMoney($hx["data_id"], $hx["sum_shui_price"], $hx["zhongliang"]);
                }

            }
            Db::commit();
            return returnSuc();
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }
}