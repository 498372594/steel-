<?php

namespace app\admin\controller;

use app\admin\library\tree\Tree;
use app\admin\model\{CapitalFy, CgPurchase, Classname, KcRk, KcRkTz, KcSpot};
use app\admin\validate\{CgPurchaseMx};
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    Request,
    response\Json};

class Purchase extends Right
{
    /**
     * 采购单添加
     * @param Request $request
     * @param int $moshi_type
     * @return array|bool|string|Json
     * @throws Exception
     */
    public function edit(Request $request, $moshi_type = 4)
    {
        Db::startTrans();
        try {
            $data = $request->post();
            $validate = new \app\admin\validate\CgPurchase();
            if (!$validate->check($data)) {
                return returnFail($validate->getError());
            }

            $addList = [];
            $updateList = [];
            $detailValidate = new CgPurchaseMx();
            $num = 1;
            foreach ($data['details'] as $item) {
                if (!$detailValidate->check($item)) {
                    return returnFail('请检查第' . $num . '行  ' . $data['details']);
                }
                $item['caizhi'] = $this->getCaizhiId($item['caizhi_id']);
                $item['chandi'] = $this->getChandiId($item['chandi_id']);
                if (empty($item['id'])) {
                    $addList[] = $item;
                } else {
                    $updateList[] = $item;
                }
                $num++;
            }
            $companyId = $this->getCompanyId();
            if (empty($data['id'])) {
                $count = CgPurchase::whereTime('create_time', 'today')
                    ->where('companyid', $companyId)
                    ->count();

                //数据处理
                $systemNumber = 'CGD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
                $data['add_id'] = $this->getAccountId();
                $data['companyid'] = $companyId;
                $data['system_number'] = $systemNumber;
                $data['moshi_type'] = $moshi_type;

                $cg = new CgPurchase();
                $cg->allowField(true)->data($data)->save();
                $purchase_id = $cg["id"];
                if ($data['ruku_fangshi'] == 1) {
                    $newRk = (new KcRk())->insertRuku($cg['id'], "4", $cg['yw_time'], $cg['group_id'], $cg['system_number'], $cg['sale_operate_id'], $this->getAccountId(), $this->getCompanyId());
                }
            } else {
                $cg = CgPurchase::where('companyid', $companyId)->where('id', $data['id'])->find();
                $purchase_id = $cg["id"];
                if (empty($cg)) {
                    throw new Exception("对象不存在");
                }
                if ($cg["status"] == 1) {
                    throw new Exception("该单据已经作废");
                }
                if (!$cg["moshi_type"] == 6) {
                    if ($cg["moshi_type"] == 1) {
                        throw new Exception("该采购单是由调货销售单自动生成的，禁止直接删除！");
                    }
                    if ($cg["moshi_type"] == 2) {
                        throw new Exception("该采购单是由采购直发单自动生成的，禁止直接删除！");
                    }
                }
                $cg->allowField(true)->data($data)->save();
                if ($data["ruku_fangshi"] == 1) {
                    throw new Exception('自动入库单禁止修改');
                }
                $mxList = (new \app\admin\model\CgPurchaseMx())->where("purchase_id", $cg["id"])->select();
                if (!empty($mxList)) {
                    foreach ($mxList as $mx) {
                        if (db("spot_id")->where("data_id", $mx["id"])->find()) {
                            model("spot_id")->where("data_id", $mx["id"])->save(array("customer_id" => $data["customer_id"]));
                        }
                        if (db("inv")->where("data_id", $mx["id"])->find()) {
                            model("inv")->where("data_id", $mx["id"])->save(array("customer_id" => $cg["customer_id"], "yw_time" => $data["yw_time"], "piaoju_id" => $data["piaoju_id"]));
                        }
                        if (db("kc_rk_tz")->where("data_id", $mx["id"])->find()) {
                            model("kc_rk_tz")->where("data_id", $mx["id"])->save(array("customer_id" => $cg["customer_id"], "yw_time" => $data["yw_time"]));
                        }

                    }
                }
                $rkList = model("kc_rk")->where("data_id", $data["id"])->select();
                if (!empty($rkList)) {
                    foreach ($rkList as $rk) {
                        model("rk_rk")->where("data_id", $data["id"])->save(array("yw_time" => $data["yw_time"]));
                        $rkMxList = model("kc_rk_mx")->where("kc_rk_id", $rk["id"])->select();
                        if (!empty($rkMxList)) {
                            model("kc_rk_mx")->where("kc_rk_id", $rk["id"])->save(array("yw_time" => $data["yw_time"]));
                        }
                    }
                }

            }

            //删除
            if (!empty($data["delete_mx_ids"])) {
                $deleteList = model("cg_purchase_mx")->where('id', 'in', $data["delete_mx_ids"])->select();
                foreach ($deleteList as $cg) {
                    if ($cg["ruku_fangshi"] == 1) {
                        throw new Exception('自动入库单禁止删除');
                    } else {
                        (new KcRkTz())->deleteByDataIdAndRukuType($cg["id"], 4);
                    }
                    (new \app\admin\model\Inv())->deleteInv($mx['id'], 2);
                    $cg->delete();
                }
            }

            //更新
            if (!empty($updateList)) {
                foreach ($updateList as $mjo) {
                    if ($data["ruku_fangshi"] == 1) {
                        throw new Exception('自动入库单禁止修改');
                    } else {
                        $mx = \app\admin\model\CgPurchaseMx::where('id', $mjo['id'])->find();
                        $mx->allowField(true)->data($mjo)->isUpdate(true)->save();
                        (new KcRkTz())->updateRukuTz($mx["id"], $mx["ruku_type"], $mx["pinming_id"], $mx["guige_id"], $mx["caizhi_id"], $mx["chandi_id"], $mx["jijiafangshi_id"]
                            , $mx["houdu"], $mx["changdu"], $mx["kuandu"], $mx["counts"], $mx["jianshu"], $mx["lingzhi"], $mx["zhijian"], $mx["zhongliang"], $mx["sum_shui_price"], $mx["price"],
                            $mx["shui_price"], $mx["huohao"]
                            , $mx["pihao"], $mx["beizhu"], $mx["chehao"], $cg["yw_time"], null, $cg["system_number"], $mx["customer_id"]
                            , $mx["store_id"], $cg["piaoju_id"], $mx["mizhong"], $mx["jianzhong"]);
                        (new \app\admin\model\Inv())->updateInv($mx["id"], 2, null, $mx["customerId"], $mx["yw_time"], $mx["changdu"], $mx["kuandu"], $mx["houdu"]
                            , $mx["guige_id"], $mx["jijiafangshi_id"], $mx["piaoju_id"], $mx["pinming_id"], $mx["zhongliang"], $mx["price"], $mx["sum_price"], $mx["sum_shui_price"], $mx["shui_price"]);
                    }
                }
            }

            if (!empty($addList)) {
                if (!empty($data['id'])) {
                    $trumpet = \app\admin\model\CgPurchaseMx::where('purchase_id', $data['id'])->max('trumpet');
                } else {
                    $trumpet = 0;
                }

                foreach ($addList as $mjo) {
                    $trumpet++;
                    $mjo['trumpet'] = $trumpet;
                    $mjo["purchase_id"] = $purchase_id;
                    $mx = new \app\admin\model\CgPurchaseMx();

                    $mx->allowField(true)->data($mjo)->save();

                    if ($data["ruku_fangshi"] == 1) {

                        (new KcRk())->insertRkMxMd($newRk, $mx["purchase_id"], 4, $data["yw_time"], $data["system_number"], null, $data["customer_id"], $mx["pinming_id"], $mx["guige_id"], $mx["caizhi_id"], $mx["chandi_id"]
                            , $mx["jijiafangshi_id"], $mx["store_id"], $mx["pihao"], $mx["huohao"], null, $mx["beizhu"], $data["piaoju_id"], $mx["houdu"] ?? 0, $mx["kuandu"] ?? 0, $mx["changdu"] ?? 0, $mx["zhijian"], $mx["lingzhi"] ?? 0, $mx["jianshu"] ?? 0,
                            $mx["counts"] ?? 0, $mx["zhongliang"] ?? 0, $mx["price"], $mx["sumprice"], $mx["shui_price"], $mx["sum_shui_price"], $mx["shuie"], $mx["mizhong"], $mx["jianzhong"], $this->getAccountId(), $this->getCompanyId());
                    } else {

                        (new KcRkTz())->insertRukuTz($mx["id"], 4, $mx["pinming_id"], $mx["guige_id"], $mx["caizhi_id"], $mx["chandi_id"], $mx["jijiafangshi_id"], $mx["houdu"], $mx["changdu"], $mx["kuandu"],
                            $mx["counts"], $mx["jianshu"], $mx["lingzhi"], $mx["zhijian"], $mx["zhongliang"], $mx["shui_price"], $mx["sumprice"], $mx["sum_shui_price"], $mx["shuie"], $mx["price"], $mx["huohao"],
                            $mx["pihao"], $mx["beizhu"], $mx["chehao"], $cg["yw_time"], null, $cg["system_number"], $data["customer_id"], $mx["store_id"], $this->getAccountId(),
                            $mx["mizhong"], $mx["jianzhong"], $this->getCompanyId());
                    }
                    (new \app\admin\model\Inv())->insertInv($mx["id"], 2, 2, $mx["changdu"], $mx["kuandu"], $mx["houdu"], $mx["guige_id"], $mx["jijiafangshi_id"], $data["piaoju_id"], $mx["pinming_id"],
                        $data["system_number"] . "." . $trumpet, $data["customer_id"], $data["yw_time"], $mx["price"], $mx["shui_price"], $mx["sumprice"], $mx["sum_shui_price"], $mx["zhongliang"], $this->getCompanyId());
                }

            }
            if (empty($data['delete_other_ids'])) {
                $data['delete_other_ids'] = null;
            }

            (new CapitalFy())->fymxSave($data['other'], $data['delete_other_ids'], $purchase_id, $data['yw_time'], 1, $data['group_id'] ?? '', $data['sale_operate_id'] ?? '', null, $this->getAccountId(), $this->getCompanyId());

            Db::commit();
            return returnSuc(['id' => $cg['id']]);
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    public function cancel($id = 0)
    {
        if (!request()->isPost()) {
            return returnFail('请求方式错误');
        }
        Db::startTrans();
        try {
            $cg = CgPurchase::where("id", $id)->find();
            if (empty($cg)) {
                throw new Exception("对象不存在");
            }
            if ($cg["status"] == 1) {
                throw new Exception("该单据已经作废");
            }
            if ($cg["moshi_type"] != 6) {
                if ($cg["moshi_type"] == 1) {
                    throw new Exception("该采购单是由调货销售单自动生成的，禁止直接作废！");
                }
                if ($cg["moshi_type"] == 2) {
                    throw new Exception("该采购单是由采购直发单自动生成的，禁止直接作废！");
                }
            }
            $cg->isUpdate(true)->allowField(true)->save(array("status" => 1, "id" => $id));
            $mxList = \app\admin\model\CgPurchaseMx::where("purchase_id", $cg["id"])->select();
            if ($cg["ruku_fangshi"] == 1) {
                KcRk::cancelRuku($cg["id"], 4);
            }
            Db::commit();
            return returnSuc(['id' => $cg['id']]);
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }
    /**
     * @param Request $request
     * @param int $moshi_type
     * @param array $data
     * @param bool $return
     * @param array $spotIds
     * @return array|bool|string|Json
     * @throws \think\Exception
     */
//    public function purchaseadd(Request $request, $moshi_type = 4, $data = [], $return = false)
//    {
//        if ($request->isPost()) {
//            $count = CgPurchase::whereTime('create_time', 'today')->count();
//            $companyId = $this->getCompanyId();
//
//            //数据处理
//            if (empty($data)) {
//                $data = $request->post();
//            }
//            $data['create_operator'] = $this->getAccount()['name'];
//            $data['create_operate_id'] = $this->getAccountId();
//            $data['companyid'] = $companyId;
//            $data['system_number'] = 'CGD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
//            $data['moshi_type'] = $moshi_type;
//            $data["delete_mx_id"] = request()->post("delete_id");
//            $data["delete_other_id"] = request()->post("delete_id");
//            // 数据验证
//            $validate = new \app\admin\validate\CgPurchase();
//            if (!$validate->check($data)) {
//                if ($return) {
//                    return $validate->getError();
//                } else {
//                    return returnFail($validate->getError());
//                }
//            }
//
//
//            if (!$return) {
//                Db::startTrans();
//            }
//            try {
//                $model = new CgPurchase();
//                //添加修改采购单列表
//                if (empty($data["id"])) {
//                    $model->allowField(true)->isUpdate(false)->save($data);
//                    $id = $model->getLastInsID();
//                } else {
//                    $model->allowField(true)->save($data, $data["id"]);
//                    $id = $data["id"];
//                }
//                //处理明细
//                $num = 1;
//                $totalMoney = 0;
//                $totalWeight = 0;
//                $detailsValidate = new CgPurchaseMx();
//                //添加修改删除采购单明细
//                foreach ($data['details'] as $c => $v) {
//                    $data['details'][$c]['companyid'] = $companyId;
//                    $data['details'][$c]['purchase_id'] = $id;
//                    $totalMoney += $v['sum_shui_price'];
//                    $totalWeight += $v['zhongliang'];
//                    if (!$detailsValidate->check($data['details'][$c])) {
//                        throw new Exception('请检查第' . $num . '行' . $detailsValidate->getError());
//                    }
//                    $num++;
//                    if (empty($v["id"])) {
//                        model('CgPurchaseMx')->allowField(true)->isUpdate(false)->data($data['details'][$c])->save();
//                    } else {
//                        model('CgPurchaseMx')->allowField(true)->update($data['details'][$c]);
//                    }
//                }
//
//                if (!empty($data["delete_mx_id"])) {
//                    model("CgPurchaseMx")->where("id", "in", $data["delete_mx_id"])->delete();
//                }
//                /******************/
//                $num = 1;
//                if (!empty($data['other'])) {
//                    $otherValidate = new FeiyongDetails();
//                    //处理其他费用
//                    foreach ($data['other'] as $c => $v) {
//                        $data['other'][$c]['group_id'] = $data['group_id'] ?? '';
//                        $data['other'][$c]['sale_operator_id'] = $data['sale_operator_id'] ?? '';
//
//                        if (!$otherValidate->check($data['other'][$c])) {
//                            throw new Exception('请检查第' . $num . '行' . $otherValidate->getError());
//                        }
//                        $num++;
//                    }
//                    $res = (new Feiyong())->addAll($data['other'], 2, $id, $data['yw_time'], false);
//                    if ($res !== true) {
//                        throw new Exception($res);
//                    }
//                }
//                if (model("kc_spot")->where("data_id", $id)->value("id")) {
//                    return returnFail('已生成入库单，禁止修改');
//                }
//
//                if ($data['ruku_fangshi'] == 2) {
//                    //手动入库，添加入库通知单
//                    //判断入库通知是否存在已经入库
//                    model("kc_rk_tz")->where("data_id", $id)->delete();
//                    $notify = [];
//                    foreach ($data['details'] as $c => $v) {
//                        $notify[] = [
//                            'companyid' => $companyId,
//                            'ruku_type' => $moshi_type,
//                            'status' => 0,
//                            'data_id' => $id,
//                            'guige_id' => $v['guige_id'],
//                            'caizhi_id' => $v['caizhi_id'] ?? '',
//                            'chandi_id' => $v['chandi_id'] ?? '',
//                            'cache_piaoju_id' => $v['piaoju_id'] ?? '',
//                            'pinming_id' => $v['pinming_id'] ?? '',
//                            'jijiafangshi_id' => $v['jijiafangshi_id'],
//                            'houdu' => $v['houdu'] ?? '',
//                            'kuandu' => $v['kuandu'] ?? '',
//                            'changdu' => $v['changdu'] ?? '',
//                            'lingzhi' => $v['lingzhi'] ?? '',
//                            'fy_sz' => $v['fy_sz'] ?? '',
//                            'jianshu' => $v['jianshu'] ?? '',
//                            'zhijian' => $v['zhijian'] ?? '',
//                            'counts' => $v['counts'] ?? '',
//                            'zhongliang' => $v['zhongliang'] ?? '',
//                            'price' => $v['price'] ?? '',
//                            'sumprice' => $v['sumprice'] ?? '',
//                            'shuie' => $v['shuie'] ?? '',
////                            'ruku_lingzhi' => $v['lingzhi'] ?? '',
////                            'ruku_jianshu' => $v['jianshu'] ?? '',
////                            'ruku_zhongliang' => $v['zhongliang'] ?? '',
////                            'ruku_shuliang' => $v['counts'] ?? '',
//                            'shui_price' => $v['shui_price'] ?? '',
//                            'sum_shui_price' => $v['sum_shui_price'] ?? '',
//                            'beizhu' => $v['beizhu'] ?? '',
//                            'chehao' => $v['chehao'] ?? '',
//                            'pihao' => $v['pihao'] ?? '',
//                            'huohao' => $v['huohao'] ?? '',
//                            'cache_ywtime' => $data['yw_time'],
//                            'cache_data_pnumber' => $data['system_number'],
//                            'cache_customer_id' => $data['customer_id'],
//                            'store_id' => $v['store_id'],
//                            'cache_create_operator' => $data['create_operate_id'],
//                            'mizhong' => $v['mizhong'] ?? '',
//                            'jianzhong' => $v['jianzhong'] ?? '',
//                            'lisuan_zhongliang' => ($v["counts"] * $v["changdu"] * $v['mizhong'] / 1000),
//                            'guobang_zhongliang' => $v['zhongliang'] ?? '',
//                        ];
//                    }
//                    //添加入库通知
//                    model("KcRkTz")->allowField(true)->saveAll($notify);
//                } elseif ($data['ruku_fangshi'] == 1) {
//                    $this->zidongruku($id, $data, $ruku_type = 4);
//                }
//                //向货款单添加数据
//                $capitalHkData = [
//                    'hk_type' => CapitalHk::PURCHASE,
//                    'data_id' => $id,
//                    'fangxiang' => 1,
//                    'customer_id' => $data['customer_id'],
//                    'jiesuan_id' => $data['jiesuan_id'],
//                    'system_number' => $data['system_number'],
//                    'yw_time' => $data['yw_time'],
//                    'beizhu' => $data['beizhu'],
//                    'money' => $totalMoney,
//                    'group_id' => $data['group_id'],
//                    'sale_operator_id' => $data['sale_operate_id'],
//                    'create_operator_id' => $data['create_operate_id'],
//                    'zhongliang' => $totalWeight,
//                    'cache_pjlx_id' => $data['piaoju_id'],
//                ];
//                (new CapitalHk())->add($capitalHkData);
//                //iniv
//
//                $iniv = [];
//                foreach ($data['details'] as $c => $v) {
//                    $iniv[] = [
//                        'companyid' => $companyId,
//                        'fx_type' => 2,
//                        'yw_type' => 6,
//                        'yw_time' => $v["yw_time"] ?? '',
//                        'system_number' => $data["system_number"] . "1" ?? '',
//                        'pinming_id' => $v["pinming_id"] ?? '',
//                        'guige_id' => $v["guige_id"] ?? '',
//                        'houdu' => $v["houdu"] ?? '',
//                        'changdu' => $v["changdu"] ?? '',
//                        'kuandu' => $v["kuandu"] ?? '',
//                        'zhongliang' => $v["zhongliang"] ?? '',
//                        'price' => $v["price"] ?? '',
//                        'customer_id' => $data["customer_id"] ?? '',
//                        'jijiafangshi_id' => $v["jijiafangshi_id"] ?? '',
//                        'piaoju_id' => $v["piaoju_id"] ?? '',
//                        'yhx_zhongliang' => 0,
//                        'yhx_price' => 0,
//                        'data_id' => $id,
//                        'shui_price' => $v["shui_price"] ?? '',
//                        'sum_price' => $v["sum_price"] ?? '',
//
//                    ];
//                }
//                (new Inv())->add($iniv);
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
//        } else {
//            //判断入库通知是否存在已经入库
//            $id = request()->param("id");
//            if (model("kc_spot")->where("data_id", $id)->value()) {
//                return returnFail('已生成其它入库单，禁止修改');
//            }
//        }
//        if ($return) {
//            return '请求方式错误';
//        } else {
//            return returnFail('请求方式错误');
//        }
//    }

    public function zidongruku($id, $data, $ruku_type)
    {
        //自动入库
        //采购单id

        $data['data_id'] = $id;
        //生成入库单
        $count2 = KcRk::whereTime('create_time', 'today')->count();
        $data["system_number"] = "RKD" . date('Ymd') . str_pad($count2 + 1, 3, 0, STR_PAD_LEFT);
        $data["beizhu"] = $data['system_number'];
        $data["ruku_type"] = $ruku_type;
        model("KcRk")->allowField(true)->data($data)->save();
        $rkid = model("KcRk")->getLastInsID();
        //处理数据
        foreach ($data['details'] as $c => $v) {
            $data['details'][$c]['companyid'] = $this->getCompanyId();
            $data['details'][$c]['kc_rk_id'] = $rkid;
            $data['details'][$c]['data_id'] = $id;
            $data['details'][$c]['cache_ywtime'] = $data['yw_time'];
            $data['details'][$c]['cache_data_pnumber'] = $data['system_number'];
            $data['details'][$c]['cache_customer_id'] = $data['customer_id'];
            $data['details'][$c]['cache_create_operator'] = $data['create_operate_id'];
            $data['details'][$c]['ruku_lingzhi'] = $v['lingzhi'];
            $data['details'][$c]['ruku_jianshu'] = $v['jianshu'];
            $data['details'][$c]['ruku_shuliang'] = $v['counts'];
            $data['details'][$c]['ruku_zhongliang'] = $v['zhongliang'];
        }
        //入库明细
        model('KcRkMx')->allowField(true)->saveAll($data['details']);
        $count1 = KcSpot::whereTime('create_time', 'today')->count();
        //入库库存
        foreach ($data['details'] as $c => $v) {
            $spot = [
                'companyid' => $this->getCompanyId(),
                'ruku_type' => $ruku_type,
                'ruku_fangshi' => $data['ruku_fangshi'],
                'piaoju_id' => $data['piaoju_id'],
                'resource_number' => "KC" . date('Ymd') . str_pad($count1 + 1, 3, 0, STR_PAD_LEFT),
                'guige_id' => $v['guige_id'],
                'data_id' => $id,
                'pinming_id' => $v['pinming_id'],
                'store_id' => $v['store_id'],
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
    }

    /**
     * 获取大类列表
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function getclassnamelist()
    {
        $list = Classname::field("pid,id,classname")->where("companyid", $this->getCompanyId())->select();
        $list = new Tree($list);
        $list = $list->leaf();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }

    /**
     * 采购单列表
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function getpurchaselist($pageLimit = 10)
    {
        $params = request()->param();
        $list = CgPurchase::with([
            'custom',
            'pjlxData',
            'jsfsData',
        ])->where('companyid', $this->getCompanyId());
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('yw_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['status'])) {
            $list->where('status', $params['status']);
        }
        if (!empty($params['ruku_fangshi'])) {
            $list->where('ruku_fangshi', $params['ruku_fangshi']);
        }
        if (!empty($params['customer_id'])) {
            $list->where('customer_id', $params['customer_id']);
        }
        if (!empty($params['piaoju_id'])) {
            $list->where('piaoju_id', $params['piaoju_id']);
        }
        if (!empty($params['system_number'])) {
            $list->where('system_number', 'like', '%' . $params['system_number'] . '%');
        }
        if (!empty($params['beizhu'])) {
            $list->where('beizhu', 'like', '%' . $params['beizhu'] . '%');
        }
        if (!empty($params['moshi_type'])) {
            $list->where('moshi_type', $params['moshi_type']);
        }
        if (!empty($params['shou_huo_dan_wei'])) {
            $list->where('shou_huo_dan_wei', $params['shou_huo_dan_wei']);
        }
        if (!empty($params['shou_huo_dan_wei'])) {
            $list->where('shou_huo_dan_wei', $params['shou_huo_dan_wei']);
        }
        $list = $list->paginate($pageLimit);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**
     * 采购单列表返回数据
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function purchaseaddinfo()
    {
        $companyid = $this->getCompanyId();
        //往来单位运营商
        $data["custom"] = model("custom")->where(array("companyid" => $companyid, "issupplier" => 1))->field("id,custom")->select();
        //结算方式
        $data["jiesuanfangshi"] = model("jiesuanfangshi")->where("companyid", $companyid)->field("id,jiesuanfangshi")->select();
        //票据类型
        $data["pjlx"] = model("pjlx")->where("companyid", $companyid)->field("id,pjlx")->select();
        //库存列表
        $data["storage"] = model("storage")->where("companyid", $companyid)->field("id,storage")->select();
        //大类
        $data["classname"] = $this->getclassnamelist();
        //材质
        $data["texture"] = model("texture")->where("companyid", $companyid)->field("id,texturename")->select();
        //产地
        $data["originarea"] = model("originarea")->where("companyid", $companyid)->field("id,originarea")->select();
        //计算方式
        $data["jsfs"] = model("jsfs")->where("companyid", $companyid)->field("id,jsfs")->select();
        //收入类型
        $data["sr_paymenttype"] = model("paymenttype")->where(array("companyid" => $companyid, "type" => 1))->field("id,name")->select();
        //支出类型
        $data["zc_paymenttype"] = model("paymenttype")->where(array("companyid" => $companyid, "type" => 2))->field("id,name")->select();
        //计算方式
        $data["jsfs"] = model("jsfs")->where("companyid", $companyid)->field("id,jsfs")->select();
        $data["productlist"] = model("view_specification")->where("companyid", $companyid)->select();

        return returnRes($data, "没有相关数据", $data);
    }

    /**
     * @param int $id
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getpurchasedetail($id = 0)
    {
        $data = CgPurchase::with([
            'custom',
            'pjlxData',
            'jsfsData',
            'details' => ['specification', 'jsfs', 'storage', 'pinmingData', 'caizhiData', 'chandiData', 'wuziData', 'jijiafangshiData'],
            'other' => ['mingxi' => ['szmcData', 'pjlxData', 'custom', 'szflData']],
        ])->where('companyid', $this->getCompanyId())
            ->where('id', $id)
            ->find();
        if (empty($data)) {
            return returnFail('数据不存在');
        } else {
            return returnRes(true, '', $data);
        }
    }

    /**
     * 根据收支方向获取收支分类
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function getpaymentclass()
    {
        $type = request()->param("type");
        $paymentclass = model("paymentclass")->field("id,name")->where("type", $type)->select();
        return returnRes($paymentclass, "没有相关数据", $paymentclass);
    }

    /**
     * 根据收支分类获取收支名称
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function getpaymenttype()
    {
        $class = request()->param("classid");
        $paymentclass = model("paymenttype")->field("id,name")->where("classid", $class)->select();
        return returnRes($paymentclass, "没有相关数据", $paymentclass);
    }

    /**
     * 基础列表返回仓库下拉
     * @return Json
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     */
    public function getstorage()
    {
        $list = model("storage")->where("companyid", $this->getCompanyId())->field("id,storage")->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }

    /**
     * 基础列表 票据类型
     * @return Json
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     */
    public function getpjlx()
    {
        $list = model("pjlx")->where("companyid", $this->getCompanyId())->field("id,pjlx,tax_rate")->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }

    /**
     * 获取供应商
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function getsupplier()
    {
        $list = model("custom")->where(array("companyid" => $this->getCompanyId(), "issupplier" => 1))->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }

    /**
     * 获取客户
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function getcustom()
    {
        $list = model("custom")->where(array("companyid" => $this->getCompanyId(), "iscustom" => 1))->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }

    /**
     * 获取往来单位
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function getallcustom()
    {
        $list = model("custom")->where(array("companyid" => $this->getCompanyId()))->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }

    /**
     * 获取结算方式
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function getjiesuanfangshi()
    {
        $list = model("jiesuanfangshi")->where(array("companyid" => $this->getCompanyId()))->field("id,jiesuanfangshi")->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }

    /**
     * 获取材质
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function gettexture()
    {
        $list = model("texture")->where(array("companyid" => $this->getCompanyId()))->field("id,texturename")->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }

    /**
     * 产地
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function getoriginarea()
    {
        $list = model("originarea")->where(array("companyid" => $this->getCompanyId()))->field("id,originarea")->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }

    /**
     * 品名下拉获取
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function getproductname()
    {
        $list = model("productname")->where(array("companyid" => $this->getCompanyId()))->field("id,name")->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }

    public function getzhiyuan()
    {
        $list = model("admin")->where(array("companyid" => $this->getCompanyId()))->field("id,originarea")->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }
    public function cgyfzk($pageLimit=10){
        $param=request()->param();
        $sqlParams=[];
        $sql="(select t2.id             gysid,
       t2.daima,
       t2.wanglai,
       t2.yewu_yuan,
       t2.bumen,
       t2.create_time,
       t2.benqi_yingfu,
       t2.benqi_shifu,
       t2.qichu_yue,
       t2.qimo_yue,
       t2.congying_fu,
       t2.yingshou_yue,
       t2.chYingshouYue,
       t2.gcChPrice,
       t2.yufukuanyue,
       t2.yushoukuanyue,
       t2.qichu_yue1     qichuyingshou,
       t2.qimo_yue1      qimoyingshou,
       t2.benqi_yingshou benqiyingshou,
       t2.benqi_shishou  benqishishou

from (SELECT t1.id,
             t1.daima,
             t1.wanglai,
             t1.yewu_yuan,
             t1.bumen,
             t1.create_time,
             t1.yingfu_yue                                                                           benqi_yingfu,
             t1.shifu_jine                                                                           benqi_shifu,
             t1.qichu_yue,
             (IFNULL(t1.qichu_yue, 0) + (IFNULL(t1.yingfu_yue, 0) - IFNULL(t1.shifu_jine, 0)))       qimo_yue,
             (IFNULL(t1.ys, 0) - IFNULL(t1.yf, 0))                                                   congying_fu,
             ((IFNULL(t1.qichu_yue1, 0) + (IFNULL(t1.yingshou_yue, 0) - IFNULL(t1.shishou_jine, 0)))
                - (IFNULL(t1.qichu_yue, 0) + (IFNULL(t1.yingfu_yue, 0) - IFNULL(t1.shifu_jine, 0)))) yingshou_yue,
             (IFNULL(t1.qichu_yue, 0) + (IFNULL(t1.yingfu_yue, 0) - IFNULL(t1.shifu_jine, 0))) -
             (IFNULL(t1.ys, 0) - IFNULL(t1.yf, 0))                                                   chYingshouYue,
             t1.gcChPrice,
             t1.yingshou_yue                                                                         benqi_yingshou,
             t1.shishou_jine                                                                         benqi_shishou,
             t1.qichu_yue1,
             (IFNULL(t1.qichu_yue1, 0) + (IFNULL(t1.yingshou_yue, 0) - IFNULL(t1.shishou_jine, 0)))  qimo_yue1,
             t1.yushoukuanyue,
             t1.yufukuanyue

      FROM (SELECT c.id,
                   c.`zjm`                    daima,
                   c.custom                    wanglai,
                   c.moren_yewuyuan            yewu_yuan,
                   c.suoshu_department         bumen,
                   c.create_time,
                   (ifnull((SELECT ifnull(SUM(IFNULL(mx.sum_shui_price, 0)), 0)

                            FROM cg_purchase_mx mx
                                   LEFT JOIN cg_purchase se ON mx.purchase_id = se.id
                            WHERE mx.delete_time is null
                              AND se.delete_time is null
                              AND se.customer_id = c.id
                              and se.status != 1";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and se.yw_time >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and se.yw_time < :ywsjEnd';
            $sqlParams['ywsjEnd'] = strtotime('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql .= "), 0) + IFNULL((SELECT ifnull(SUM(IFNULL(fy.money, 0)), 0)
                                                                        FROM capital_fy fy
                                                                        WHERE fy.fang_xiang = 2
                                                                          AND fy.delete_time is null
                                                                          and fy.status != 1
                                                                          AND fy.customer_id = c.id";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and fy.yw_time >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and fy.yw_time < :ywsjEnd';
            $sqlParams['ywsjEnd'] = strtotime('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql.="), 0) + IFNULL(
                              (SELECT ifnull(SUM(IFNULL(mx.money, 0)), 0)
                               FROM init_ysfk_mx mx
                                      LEFT JOIN init_ysfk ysfk ON mx.ysfk_id = ysfk.id
                               WHERE ysfk.type = 1
                                 AND ysfk.delete_time is null
                                 and mx.delete_time is null
                                 AND mx.customer_id = c.id
                                 and ysfk.status != 1";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and ysfk.yw_time >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and ysfk.yw_time < :ywsjEnd';
            $sqlParams['ywsjEnd'] = strtotime('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql.="), 0) + IFNULL((SELECT -ifnull(SUM(IFNULL(mx.sum_shui_price, 0)), 0)
                                      FROM cg_th_mx mx
                                             LEFT JOIN cg_th th ON mx.cg_th_id = th.id
                                      WHERE th.customer_id = c.id
                                        and th.delete_time is null
                                        and th.status != 1
                                        and mx.delete_time is null";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and th.yw_time >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and th.yw_time < :ywsjEnd';
            $sqlParams['ywsjEnd'] = strtotime('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql.="),0) + (SELECT ifnull( sum(ifnull(mx.money, 0)), 0)
                             from capital_other_details mx
                                    LEFT JOIN capital_other qt on mx.cap_qt_id = qt.id
                             where qt.customer_id = c.id
                               and qt.fangxiang = 2
                               and qt.status != 1
                               and mx.delete_time is null
                               and qt.delete_time is null";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and qt.yw_time >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and qt.yw_time < :ywsjEnd';
            $sqlParams['ywsjEnd'] = strtotime('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }

        $sql.=")) yingfu_yue, ( SELECT ifnull(SUM(
                                    ifnull(fk.money, 0) + IFNULL(fk.mfmoney, 0)
                                      ), 0)
                    FROM capital_fk fk
                    WHERE fk.delete_time is null
                      AND fk.status != 1
                      AND fk.customer_id = c.id";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and fk.yw_time >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and fk.yw_time < :ywsjEnd';
            $sqlParams['ywsjEnd'] = strtotime('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql.=")  shifu_jine,";
        if (!empty($param['ywsjStart'])) {
            $sql .= " (
                   (
                   ifnull(	(
                   SELECT
                   SUM(IFNULL(mx.sum_shui_price, 0))
                   FROM
                   cg_purchase_mx mx
                   LEFT JOIN cg_purchase pur ON mx.purchase_id = pur.id
                   WHERE
                   mx.delete_time is null
                   AND pur.delete_time is null
                   AND pur.customer_id = c.id
                   and pur.status!=1
                   and pur.yw_time <:ywsjStart

                   ),0)
                   + IFNULL(
                   (
                   SELECT
                   SUM(IFNULL(fy.money,0))
                   FROM
                   capital_fy fy
                   WHERE
                   fy.fang_xiang = 2
                   AND fy.delete_time is null
                   and fy.status!=1
                   AND fy.customer_id = c.id
                   and fy.yw_time <:ywsjStart
                   ),
                   0
                   ) + IFNULL(
                   (
                   SELECT
                   SUM(IFNULL(mx.money,0))
                   FROM
                   init_ysfk_mx mx
                   LEFT JOIN init_ysfk ysfk ON mx.ysfk_id = ysfk.id
                   WHERE
                   ysfk.type = 1
                   AND ysfk.delete_time is null
                   and mx.delete_time is null
                   AND mx.customer_id = c.id
                   and ysfk.status!=1
                   and ysfk.yw_time <:ywsjStart
                   ),
                   0
                   )
                   )+IFNULL(
                   (
                   SELECT
                   -SUM(IFNULL(mx.sum_shui_price, 0))
                   FROM
                   cg_th_mx  mx
                   LEFT JOIN cg_th th ON mx.cg_th_id = th.id
                   WHERE
                   th.customer_id = c.id
                   and th.delete_time is null
                   and th.status!=1
                  
                   AND th.yw_time  <:ywsjStart

                   ),
                   0
                   ) -
                   ifnull(

                   (
                   SELECT
                   SUM(
                   fk.money + IFNULL(fk.mfmoney,0)
                   )
                   FROM
                   capital_fk fk
                   WHERE
                   fk.delete_time is null
                   AND fk.status!=1
                   AND fk.customer_id = c.id
                   and fk.yw_time <:ywsjStart
                   )
                   ,0)+
                   (SELECT
                   IFNULL(SUM(IFNULL(mx.money, 0)), 0)
                   FROM
                   capital_other_details mx
                   LEFT JOIN capital_other qt
                   ON mx.cap_qt_id = qt.id
                   WHERE
                   qt.customer_id = c.id
                   AND qt.fangxiang = 2
                   AND (qt.`status` = 0
                   OR qt.`status` = 2)
                   AND mx.delete_time is null
                   AND qt.delete_time is null
                   AND qt.yw_time <:ywsjStart


                   )) qichu_yue,";
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }else{
            $sql.=" 0 qichu_yue,";
        }
        $sql.=" (ifnull((SELECT SUM(IFNULL(salemx.price_and_tax, 0))
                          FROM salesorder_details salemx
                                 LEFT JOIN salesorder sale on salemx.order_id = sale.id
                          WHERE sale.delete_time is null
                            and salemx.delete_time is null
                            and sale.status != 1
                            and sale.custom_id = c.id";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and sale.ywsj >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and sale.ywsj < :ywsjEnd';
            $sqlParams['ywsjEnd'] = strtotime('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql.="), 0) +IFNULL(
                         (SELECT SUM(IFNULL(fy.money, 0))
                          FROM capital_fy fy
                          WHERE fy.fang_xiang = 1
                            AND fy.delete_time is null
                            and fy.status != 1
                            and fy.customer_id = c.id";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and fy.yw_time >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and fy.yw_time < :ywsjEnd';
            $sqlParams['ywsjEnd'] = strtotime('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql.="),0) + IFNULL(
                         (SELECT SUM(IFNULL(mx.money, 0))
                          FROM init_ysfk_mx mx
                                 LEFT JOIN init_ysfk ysfk ON mx.ysfk_id = ysfk.id
                          WHERE ysfk.type = 0
                            AND ysfk.delete_time is null
                            and mx.delete_time is null
                            AND mx.customer_id = c.id
                            and ysfk.status != 1";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and ysfk.yw_time >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and ysfk.yw_time < :ywsjEnd';
            $sqlParams['ywsjEnd'] = strtotime('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }

         $sql.="),0 ) + IFNULL(
                                 (SELECT -SUM(
                                            IFNULL(mx.sum_shui_price, 0)
                                     )
                                  FROM sales_return_details mx
                                         LEFT JOIN sales_return th ON mx.xs_th_id = th.id
                                  WHERE th.customer_id = c.id
                                    AND th.delete_time is null
                                    and mx.delete_time is null
                                    AND th.status != 1";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and th.yw_time >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and th.yw_time < :ywsjEnd';
            $sqlParams['ywsjEnd'] = strtotime('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }

        $sql.="),0) + ifnull((SELECT sum(ifnull(qt.money, 0))
                                       FROM capital_other qt
                                       WHERE qt.customer_id = c.id
                                         and qt.fangxiang = 1
                                         and qt.delete_time is null
                                         and qt.status != 1";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and qt.yw_time >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and qt.yw_time < :ywsjEnd';
            $sqlParams['ywsjEnd'] = strtotime('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql.="), 0))ys, ( IFNULL(
                         (SELECT SUM(sk.money + IFNULL(sk.msmoney, 0))
                          FROM capital_sk sk
                          WHERE sk.customer_id = c.id
                            and sk.status != 1
                            and sk.delete_time is null";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and sk.yw_time >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and sk.yw_time < :ywsjEnd';
            $sqlParams['ywsjEnd'] = strtotime('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql.="), 0))yf , 0  gcChPrice,
                   (ifnull(
                         (SELECT SUM(IFNULL(mx.price_and_tax, 0))

                          FROM salesorder_details mx
                                 LEFT JOIN salesorder sale ON mx.order_id = sale.id
                          WHERE mx.delete_time is null
                            and sale.status != 1
                            AND sale.delete_time is null
                            AND sale.custom_id = c.id";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and sale.ywsj >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and sale.ywsj < :ywsjEnd';
            $sqlParams['ywsjEnd'] = strtotime('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql.="), 0)+ IFNULL((SELECT SUM(IFNULL(fy.money, 0))
                              FROM capital_fy fy
                              WHERE fy.fang_xiang = 1
                                AND fy.delete_time is null
                                and fy.status != 1
                                AND fy.customer_id = c.id";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and fy.yw_time >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and fy.yw_time < :ywsjEnd';
            $sqlParams['ywsjEnd'] = strtotime('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql.="),0) + IFNULL((SELECT SUM(IFNULL(mx.money, 0))
                                  FROM init_ysfk_mx mx
                                         LEFT JOIN init_ysfk ysfk ON mx.ysfk_id = ysfk.id
                                  WHERE ysfk.type = 0
                                    and ysfk.delete_time is null
                                    AND mx.delete_time is null
                                    AND mx.customer_id = c.id
                                    and ysfk.status != 1
                                    and ysfk.delete_time is null";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and ysfk.yw_time >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and ysfk.yw_time < :ywsjEnd';
            $sqlParams['ywsjEnd'] = strtotime('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql.="),0) + IFNULL((SELECT -SUM(IFNULL(mx.sum_shui_price, 0))
                                  FROM sales_return_details mx
                                         LEFT JOIN sales_return th ON mx.xs_th_id = th.id
                                  WHERE th.customer_id = c.id
                                    and th.delete_time is null
                                    and th.status != 1
                                    and mx.delete_time is null";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and th.yw_time >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and th.yw_time < :ywsjEnd';
            $sqlParams['ywsjEnd'] = strtotime('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql.="
                               ),
                                 0
                           ) + (SELECT ifnull(sum(ifnull(mx.money, 0)), 0)

                                from capital_other_details mx
                                       LEFT JOIN capital_other qt on mx.cap_qt_id = qt.id
                                where qt.customer_id = c.id
                                  and qt.fangxiang = 1
                                  and qt.status != 1
                                  and qt.delete_time is null
                                  and qt.yw_type != 16";

        if (!empty($param['ywsjStart'])) {
            $sql .= ' and qt.yw_time >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and qt.yw_time < :ywsjEnd';
            $sqlParams['ywsjEnd'] = strtotime('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql.=")+ (SELECT ifnull(sum(ifnull(mx.money, 0)), 0)

                            from capital_other_details mx
                                   LEFT JOIN capital_other qt on mx.cap_qt_id = qt.id
                            where qt.customer_id = c.id
                              and qt.fangxiang = 1
                              and qt.status != 1
                              and qt.delete_time is null
                              and qt.yw_type = 16";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and qt.yw_time >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and qt.yw_time < :ywsjEnd';
            $sqlParams['ywsjEnd'] = strtotime('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql.=")) yingshou_yue,
                   (SELECT ifnull(SUM(
                                    ifnull(sk.money, 0) + IFNULL(sk.msmoney, 0)
                                      ), 0)
                    FROM capital_sk sk
                    WHERE sk.delete_time is null
                      AND sk.status != 1
                      AND sk.customer_id = c.id";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and sk.yw_time >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and sk.yw_time < :ywsjEnd';
            $sqlParams['ywsjEnd'] = strtotime('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql.=") shishou_jine,";
        if (!empty($param['ywsjStart'])) {
            $sql .=" (
                   (
                   SELECT
                   ifnull(sum(mx.price_and_tax),0)
                   FROM
                   salesorder_details mx
                   LEFT JOIN salesorder sale ON mx.order_id = sale.id
                   WHERE
                   mx.delete_time is null
                   AND sale.delete_time is null
                   and sale.status!=1
                   AND sale.custom_id = c.id
                   and sale.ywsj<:ywsjStart
                   )+ IFNULL(
                   (
                   SELECT
                   ifnull(sum(fy.money),0)
                   FROM
                   capital_fy fy
                   WHERE
                   fy.fang_xiang = 1
                   AND fy.delete_time is null
                   and fy.status!=1
                   AND fy.customer_id = c.id
                  
                   and fy.yw_time<:ywsjStart
                   ),
                   0
                   ) + IFNULL(
                   (
                   SELECT
                   SUM(IFNULL(mx.money,0))
                   FROM
                   init_ysfk_mx mx
                   LEFT JOIN init_ysfk ysfk ON mx.ysfk_id = ysfk.id
                   WHERE
                   ysfk.type = 0
                   and ysfk.delete_time is null
                   AND mx.delete_time is null
                   AND mx.customer_id = c.id
                   and ysfk.status!=1
                 
                   and ysfk.yw_time<:ywsjStart
                   ),
                   0
                   )+IFNULL(
                   (
                   SELECT
                   -SUM(IFNULL(mx.sum_shui_price, 0))
                   FROM
                   sales_return_details  mx
                   LEFT JOIN sales_return th ON mx.xs_th_id = th.id
                   WHERE
                   th.customer_id = c.id

                   and th.delete_time is null
                   and mx.delete_time is null
                   and th.status!=1
                 
                   and th.yw_time<:ywsjStart
                   ),
                   0
                   )
                   -
                   ifnull(
                   (
                   SELECT
                   SUM(
                   sk.money + IFNULL(sk.msmoney,0)
                   )
                   FROM
                   capital_sk sk
                   WHERE
                   sk.delete_time is null
                   AND sk.status!=1
                   AND sk.customer_id = c.id
                 
                   and sk.yw_time<:ywsjStart

                   )
                   ,0)
                   ) qichu_yue1,";
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }else{
            $sql.="  0 qichu_yue1,";
        }
              $sql.="
                   (SELECT IFNULL(SUM(IFNULL(sk.money, 0)), 0)  -
                           (SELECT IFNULL(SUM(IFNULL(hk.yfkhxmoney, 0)), 0)
                            FROM capital_hk hk where
                               hk.status != 1
                               AND hk.customer_id = c.id) -
                           (SELECT -IFNULL(SUM(IFNULL(sk1.money, 0)), 0)
                            FROM capital_sk sk1
                            WHERE sk1.delete_time is null
                              AND sk1.status = 0
                              AND sk1.sk_type = 4
                              AND c.id = sk1.customer_id)
                    FROM capital_sk sk
                    WHERE sk.`customer_id` = c.id
                      AND sk.sk_type = 2
                      AND sk.delete_time is null
                      AND sk.status = 0)       yushoukuanyue,
                   (SELECT IFNULL(SUM(IFNULL(fk.money, 0)), 0) -
                           (SELECT IFNULL(SUM(IFNULL(hk.yfkhxmoney, 0)), 0)
                            FROM capital_hk hk
                            WHERE hk.delete_time is null
                              AND hk.status != 1
                              AND hk.customer_id = c.id)  -
                           (SELECT -IFNULL(SUM(IFNULL(fk1.money, 0)), 0)
                            FROM capital_fk fk1
                            WHERE fk1.delete_time is null
                              AND fk1.status != 1
                              AND fk1.fk_type = 4
                              AND fk1.customer_id = c.id)
                    FROM capital_fk fk
                    WHERE fk.customer_id = c.id
                      AND fk.fk_type = 2
                      AND fk.status != 1)      yufukuanyue


            FROM custom c
            where c.delete_time is null
              and c.issupplier = 1) t1)t2
where 1 = 1";
        if (!empty($param['yuEweiLing'])) {
            $sql .= ' and t2.yingshou_yue != :yuEweiLing';
            $sqlParams['yuEweiLing'] = $param['yuEweiLing'];
        }
        if (!empty($param['wufaShengE'])) {
            $sql .= " and (t2.benqi_yingfu >:wufaShengE or t2.benqi_shifu >:wufaShengE or t2.qichu_yue >:wufaShengE
 or t2.qimo_yue >:wufaShengE or t2.congying_fu >:wufaShengE or t2.yingshou_yue >:wufaShengE 
 t2.benqi_yingfu >:wufaShengE and t2.benqi_yingshou>:wufaShengE )";
            $sqlParams['wufaShengE'] = $param['wufaShengE'];
        }
        if (!empty($param['danwei'])) {
            $sql .= ' and t2.wanglai like concat(:danwei)';
            $sqlParams['danwei'] = $param['danwei'];
        }
        $sql.=" order by t2.create_time desc)";

        $data = Db::table($sql)->alias('t')->bind($sqlParams)->paginate($pageLimit);
        return returnSuc($data);
    }

}