<?php

namespace app\admin\controller;

use app\admin\library\tree\Tree;
use app\admin\model\{CapitalFy, CgPurchase, Classname, KcRk, KcRkTz, KcSpot, Paymentclass};
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
//            dump($data['details']);die;
            foreach ($data['details'] as $item) {
                if (!$detailValidate->check($item)) {

                    return returnFail('请检查第' . $num . '行  ' . $detailValidate->getError());
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
                    $newRk = (new KcRk())->insertRuku($cg['id'], "4", $data['yw_time'], $cg['group_id'], $cg['system_number'], $cg['sale_operate_id'], $this->getAccountId(), $this->getCompanyId());
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
            if (!empty($data['other'])) {
                (new CapitalFy())->fymxSave($data['other'], $data['delete_other_ids'], $purchase_id, $data['yw_time'], 1, $data['group_id'] ?? '', $data['sale_operate_id'] ?? '', null, $this->getAccountId(), $this->getCompanyId());

            }

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
     * 采购单列表
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function getpurchaselist($pageLimit = 10)
    {

        $params = request()->param();
//         var_dump($params);die;
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
        $list = $list->paginate($pageLimit)->toArray();
        $list=$list['data'];
        $data=Db::table('c_salesman')->select();
        $res=Db::table('kc_rk_type')->select();
      $kh=Db::table('custom')->select();
        $jsfs=Db::table('jsfs')->select();

     $dat=Db::table('c_product_type')->join('c_purchase','c_product_type.id =c_purchase.c_leibie_id')->select();
        $type=Db::table('c_product_type')->select();

       return    $this->fetch('purchase/getpurchaselist',['data'=>$data,'res'=>$res,'kh'=>$kh,'jsfs'=>$jsfs,'dat'=>$dat,'type'=>$type]);
//        return returnSuc($list);
    }
    function cha(){
        $id=input("get.cont_id");
       $content=Db::table('c_product_type')->join('c_purchase','c_product_type.id =c_purchase.c_leibie_id')->where("c_leibie_id","=","$id")->select();
       echo json_encode($content);
    }
    function zt(){
        $data=Db::table('xs')->find();

        if($data['static']==0){

          Db::table('xs')->where('id','=',1)->update(['static'=>1]);
          echo 1;
        }else{

          Db::table('xs')->where('id','=',1)->update(['static'=>0]);
          echo 2;
        }

    }
    function pin(){
        $pm=input("get.pm");
        $content=Db::table('c_product_type')->join('c_purchase','c_product_type.id =c_purchase.c_leibie_id')->where("c_pinming","=","$pm")->select();
        echo json_encode($content);
    }
    function shan(){
        $id=input("get.id");
       $res= Db::table('c_purchase')->where("id",'in',"$id")->delete();

       if($res){
           echo 1;
       }
    }

    function cg_shan(){

        $id=input("get.id");

        $res= Db::table('cg_purchase_mx')->where("id",'in',"$id")->delete();

        if($res){
            echo 1;
        }
    }
    function cg_count(){
        $id=input("get.id");

        $res= Db::table('cg_purchase_mx')->where('id','in',"$id")->sum('cgzz');
        $sl= Db::table('cg_purchase_mx')->where('id','in',"$id")->sum('counts');
        echo json_encode(['cgzz'=>$res,'sl'=>$sl]);
    }
function cgtj(){
    $data=Db::table("c_city")->where("pid='0'")->select();
    return  $this->fetch('purchase/cust',['data'=>$data]);
}
function custadd(){
    $data=input('get.');
    unset($data['/admin/purchase/custadd']);

   $res=Db::table('c_customer')->insert($data);
     if($res){
         echo 1;
     }else{
         echo 2;
     }
}
function purchadd(){

       $data=Db::table('texture')->select();
        $cj=Db::table('ad_changjia')->select();
        $cp=Db::table('ad_chanpin')->select();
        $gg=Db::table('ad_guige')->select();
    $jsfs=Db::table('jsfs')->select();

        return $this->fetch('purchadd',['data'=>$data,'cj'=>$cj,'cp'=>$cp,'gg'=>$gg,'jsfs'=>$jsfs]);
}
function dopurchase(){
        $data=input('get.');
        unset($data['/admin/purchase/dopurchase']);

       $res=Db::table('cg_purchase_mx')->insert($data);
       if($res){
           echo 1;
       }

}
    function gb(){
        $id=input("get.id");

        $re=Db::table("c_city")->where("pid",'=',"$id")->select();
        echo  json_encode($re);

    }
    function addall(){
         $id=input('get.id');
         $data=Db::table('c_product_type')->join('c_purchase','c_product_type.id =c_purchase.c_leibie_id')->where('c_purchase.id','in',"$id")->select();

       $res= Db::table('c_purchase')->where('c_purchase.id','in',"$id")->sum('c_caigouzongjine');
        $count= Db::table('c_purchase')->where('c_purchase.id','in',"$id")->sum('c_shuliang');
        $zhong= Db::table('c_purchase')->where('c_purchase.id','in',"$id")->sum('c_caiguozongzhong');
         echo json_encode(['data'=>$data,'sum'=>$res,'count'=>$count,'zong'=>$zhong]);
    }
    function xiazai(){

include("Classes/PHPExcel.php");
$exce=new \PHPExcel();

$exce->setActiveSheetIndex(0)->setCellValue("A1","id")->setCellValue("B1","c_leibie_id")->setCellValue("C1","c_biaozhun")->setCellValue("D1","c_pinming")->setCellValue("F1","c_guige")->setCellValue("G1","c_caizhi")
    ->setCellValue("H1","c_chandi")->setCellValue("I1","c_lijidanzhong")->setCellValue("J1","c_changdu")->setCellValue("K1","c_shuliangdanwei")->setCellValue("L1","c_jianshu");


$data=Db::table('c_purchase')->select();

foreach($data as $k=>$v){
    $exce->setActiveSheetIndex(0)->setCellValue("A".($k+2),$v['id'])
        ->setCellValue("B".($k+2),$v['c_leibie_id'])
        ->setCellValue("C".($k+2),$v['c_biaozhun'])
        ->setCellValue("D".($k+2),$v['c_pinming'])
        ->setCellValue("E".($k+2),$v['c_guige'])
        ->setCellValue("F".($k+2),$v['c_caizhi'])
    ->setCellValue("F".($k+2),$v['c_chandi'])
    ->setCellValue("F".($k+2),$v['c_lijidanzhong'])
    ->setCellValue("F".($k+2),$v['c_changdu'])
    ->setCellValue("F".($k+2),$v['c_shuliangdanwei'])
    ->setCellValue("F".($k+2),$v['c_jianshu']);

}

header('Content-Disposition: attachment;filename="01simple.xls"');
header ('Pragma: public'); // HTTP/1.0


$objWriter=\PHPExcel_IOFactory::createWriter($exce,'Excel5');
$objWriter->save('php://output');



    }
  function typesear(){
        $sear=input('get.new_val');

      $data=Db::table('c_product_type')->join('c_purchase','c_product_type.id =c_purchase.c_leibie_id')->where('c_purchase.c_guige|c_purchase.c_caizhi|c_purchase.c_chandi','like',"%$sear%")
      ->select();


        echo json_encode($data);

  }
    function shangchuan()
    {
        include("PHPExcel-1.8/Classes/PHPExcel/IOFactory.php");
        $exec = \PHPExcel_IOFactory::load("01simple.xls");
        $ex = $exec->getSheet(0);
        $row = $ex->getHighestDataRow();

        for ($i = 2; $i <= $row; $i++) {
            $data['id'] = $ex->getCell("A" . $i)->getValue();
            $data['c_leibie_id'] = $ex->getCell("B" . $i)->getValue();
            $data['c_biaozhun'] = $ex->getCell("C" . $i)->getValue();
            $data['c_pinming'] = $ex->getCell("D" . $i)->getValue();
        Db::table('c_purchase')->insert($data);

        }
    }
    function dadd(){
        $data=Db::table('c_product_type')->select();
      return  $this->fetch('purchase/doadd',['data'=>$data]);
    }

    function puradd(){
       $data=input('get.');

        unset($data['/admin/purchase/puradd']);
      if(Db::table('c_purchase')->insert($data)){
          echo 1;
      } else{
          echo 2;
      }
    }
function kdbt(){
        $data=input('get.');
        unset($data['/admin/purchase/kdbt']);

        $res=Db::table('c_kaidanbt')->insert($data);
        if($res){
            echo 1;
        }else{
            echo 2;
        }
}
   function sear($pageLimit = 10){
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
       $list = $list->paginate($pageLimit)->toArray();

      return $list['data'][0];
   }
    /**
     * 采购单列表返回数据
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function demand(){


        return $this->fetch('demand');
    }
    function detai(){
        $data=Db::table('cg_purchase')->field('*,cg_purchase_mx.id ')->
        join('cg_purchase_mx','cg_purchase_mx.purchase_id=cg_purchase.id')
            ->join('jsfs','cg_purchase_mx.jijiafangshi_id=jsfs.id')
            ->join('ad_guige','cg_purchase_mx.guige_id=ad_guige.id')
            ->join('ad_chanpin','cg_purchase_mx.pinming_id=ad_chanpin.id')
            ->join('ad_changjia','ad_guige.changjia_id=ad_changjia.id')
            ->join('texture','cg_purchase_mx.caizhi_id=texture.id')

            ->select();
        echo json_encode($data);
    }
    function updet(){
        $id=input("get.id");
//        echo json_encode($id);die;
        $fd=input("get.fd");
        $new_val=input("get.new_val");
        if(Db::table("cg_purchase_mx")->where("id",$id)->update([$fd=>$new_val])){
            return 1;
        }else{
            return 2;
        }
    }
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

        $data["productlist"] = model("product")->where("companyid", $companyid)->select();
         $json=json_encode($data);
         $data=json_decode($json,1);


    return returnRes($data, "没有相关数据", $data);
    }
     function purinfo_add(){
        $data=input('get.');
        unset($data['/admin/purchase/purinfo_add']);

//        $custom=$data['custom'];
//        $jisuan=$data['jiesuanfangshi'];
//        $pjlx=$data['pjlx'];
//        $storage=$data['storage'];
//        $texture=$data['storage'];
//        $orgin=$data['originarea'];
//        Db::insert("insert into cg_purchase(custom,jiesuanfangshi,pjlx,storage,storage,originarea)values('$custom','$jisuan','$pjlx','$storage','$texture','$orgin')");
            Db::table('cg_purchase')->insert($data);
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
        return returnSuc($list);
    }

    /**
     * @param int $id
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getpurchasedetail($id =0)
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


        return returnRes(true, '', $data);
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
        $paymentclass = Paymentclass::field("id,name")
            ->where('companyid', $this->getCompanyId());
        if (!empty($type)) {
            $paymentclass->where("type", $type);
        }
        $paymentclass = $paymentclass->select();
        return returnRes($paymentclass, "没有相关数据", $paymentclass);
    }
    function detail(){

        $data =  Db::table('cg_purchase_mx')->field('*,cg_purchase_mx.id as a')->
        JOIN('productname','cg_purchase_mx.pinming_id = productname.id')
            ->JOIN('ad_guige','cg_purchase_mx.guige_id = ad_guige.id')
           ->JOIN('texture','cg_purchase_mx.caizhi_id = texture.id')
            ->JOIN('originarea','cg_purchase_mx.chandi_id = originarea.id')
            ->JOIN('jsfs','cg_purchase_mx.jijiafangshi_id=jsfs.id')
            ->select()
        ;


         return $this->fetch('detail',['list'=>$data]);
    }
    function cgcx(){
        $guige=input('get.guige');
        $chandi=input('get.chandi');
        $caizhi=input('get.caizhi');
        $rq=input('get.rq');
        $data =Db::table('cg_purchase_mx')->field('*,cg_purchase_mx.id as a')->
        JOIN('productname','cg_purchase_mx.pinming_id = productname.id')
            ->JOIN('ad_guige','cg_purchase_mx.guige_id = ad_guige.id')
            ->JOIN('texture','cg_purchase_mx.caizhi_id = texture.id')
            ->JOIN('originarea','cg_purchase_mx.chandi_id = originarea.id')
            ->JOIN('jsfs','cg_purchase_mx.jijiafangshi_id=jsfs.id');
        if(!empty($guige)){

               $data->where('ad_guige.guige','like',"%$guige%");


        }

         if(!empty($chandi)){
              $data->where('originarea.originarea','like',"%$chandi%");

         }
        if(!empty($caizhi)){
            $data->where('texture.texturename','like',"%$caizhi%");

        }
        if(!empty($riqi)){
            $data->where('originarea.create_time','like',"%$riqi%");

        }
         $arr=$data->select();
         echo json_encode($arr);




    }

    function jdjg(){
        $id=input('get.id');
        $fd=input('get.fd');
        if($fd==0){
            $str='否';
            Db::table('c_purchase')->where('id','=',"$id")->update(['static'=>1]);
            echo json_encode(['code'=>1,'str'=>$str]);
        }else{
            $str='是';
            Db::table('c_purchase')->where('id','=',"$id")->update(['static'=>0]);
            echo json_encode(['code'=>2,'str'=>$str]);
        }
    }
    function single(){
        $dat=Db::table('c_product_type')->join('c_purchase','c_product_type.id =c_purchase.c_leibie_id')->select();
        return $this->fetch('single',['dat'=>$dat]);
    }
    /**
     * 根据收支分类获取收支名称
     * @return Json
     * @throws DataNotFoundExceptiontail
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
        return returnSuc($list);
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
        return returnSuc($list);
    }
    function tdmx(){
        $dat=Db::table('c_product_type')->join('c_purchase','c_product_type.id =c_purchase.c_leibie_id')->where('c_purchase.static','=',0)->select();
       echo json_encode($dat);
    }
    function djsq(){
        $data=Db::table('c_supplier')->select();
        $jsfs=Db::table('jsfs')->select();
        $pjlx=Db::table('paymenttype')->select();
        $company=Db::table('company')->select();
        return $this->fetch('djsq',['data'=>$data,'jsfs'=>$jsfs,'paymenttype'=>$pjlx,'company'=>$company]);
    }
function other(){
      $data=input("get.");
        unset($data['/admin/purchase/other']);
      $res=Db::table('purchase_fee')->insert($data);
      if($res){
          echo 1;
      }
}
function getpurchase_fee(){
       $data= Db::table('purchase_fee')->join('c_supplier','purchase_fee.supplier_id=c_supplier.id')
           ->join('paymenttype','purchase_fee.type=paymenttype.id')
           ->join('company','purchase_fee.companyid=company.id')
           ->select();

       echo json_encode($data);
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
        return returnSuc($list);
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
        return returnSuc($list);
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
        return returnSuc($list);
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
        return returnSuc($list);
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
        return returnSuc($list);
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
        return returnSuc($list);
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

        return returnSuc($list);
    }

    public function getzhiyuan()
    {
        $list = model("admin")->where(array("companyid" => $this->getCompanyId()))->field("id,originarea")->select();
        return returnSuc($list);
    }

    /**应付账款汇总表
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function cgyfzk($pageLimit = 10)
    {
        $ywsjStart = '';
        $param = request()->param();
        if (!empty($param['ywsjStart'])) {
            $ywsjStart = $param['ywsjStart'];
        }
        $ywsjEnd = '';
        if (!empty($param['ywsjEnd'])) {
            $ywsjEnd = date('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sqlParams = [];
        $sql = "(select t2.id             gysid,
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
                   c.companyid,
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
                              and se.companyid=" . $this->getCompanyId() . "
                              and se.status != 1";

        if (!empty($param['ywsjStart'])) {

            $sql .= ' and se.yw_time >= ?';
            $sqlParams[] = $ywsjStart;

        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and se.yw_time <  ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= "), 0) + IFNULL((SELECT ifnull(SUM(IFNULL(fy.money, 0)), 0)
                                                                        FROM capital_fy fy
                                                                        WHERE fy.fang_xiang = 2
                                                                          AND fy.delete_time is null
                                                                          and fy.status != 1
                                                                          and fy.companyid=" . $this->getCompanyId() . "
                                                                          AND fy.customer_id = c.id";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and fy.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and fy.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= "), 0) + IFNULL(
                              (SELECT ifnull(SUM(IFNULL(mx.money, 0)), 0)
                               FROM init_ysfk_mx mx
                                      LEFT JOIN init_ysfk ysfk ON mx.ysfk_id = ysfk.id
                               WHERE ysfk.type = 1
                                 AND ysfk.delete_time is null
                                 and mx.delete_time is null
                                 and mx.companyid=" . $this->getCompanyId() . "
                                 AND mx.customer_id = c.id
                                 and ysfk.status != 1";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and ysfk.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and ysfk.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= "), 0) + IFNULL((SELECT -ifnull(SUM(IFNULL(mx.sum_shui_price, 0)), 0)
                                      FROM cg_th_mx mx
                                             LEFT JOIN cg_th th ON mx.cg_th_id = th.id
                                      WHERE th.customer_id = c.id
                                        and th.delete_time is null
                                        and th.status != 1
                                        and mx.companyid=" . $this->getCompanyId() . "
                                        and mx.delete_time is null";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and th.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and th.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= "),0) + (SELECT ifnull( sum(ifnull(mx.money, 0)), 0)
                             from capital_other_details mx
                                    LEFT JOIN capital_other qt on mx.cap_qt_id = qt.id
                             where qt.customer_id = c.id
                               and qt.fangxiang = 2
                               and qt.status != 1
                               and mx.delete_time is null
                               and mx.companyid=" . $this->getCompanyId() . "
                               and qt.delete_time is null";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and qt.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and qt.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }

        $sql .= ")) yingfu_yue, ( SELECT ifnull(SUM(
                                    ifnull(fk.money, 0) + IFNULL(fk.mfmoney, 0)
                                      ), 0)
                    FROM capital_fk fk
                    WHERE fk.delete_time is null
                      AND fk.status != 1
                       and fk.companyid=" . $this->getCompanyId() . "
                      AND fk.customer_id = c.id";

        if (!empty($param['ywsjStart'])) {
            $sql .= ' and fk.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and fk.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= ")  shifu_jine,";
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
                   and mx.companyid=" . $this->getCompanyId() . "
                   AND pur.delete_time is null
                   AND pur.customer_id = c.id
                   and pur.status!=1
                   and pur.yw_time <?";
            $sqlParams[] = $ywsjStart;
            $sql .= "),0)
                   + IFNULL(
                   (
                   SELECT
                   SUM(IFNULL(fy.money,0))
                   FROM
                   capital_fy fy
                   WHERE
                   fy.fang_xiang = 2
                   AND fy.delete_time is null
                    c
                   and fy.status!=1
                   AND fy.customer_id = c.id
                   and fy.yw_time <?";
            $sqlParams[] = $ywsjStart;
            $sql .= "
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
                   and mx.companyid=" . $this->getCompanyId() . "
                   AND mx.customer_id = c.id
                   and ysfk.status!=1
                   and ysfk.yw_time <?";
            $sqlParams[] = $ywsjStart;
            $sql .= "
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
                    and th.companyid=" . $this->getCompanyId() . "
                   and th.status!=1
                  
                   AND th.yw_time  <?";
            $sqlParams[] = $ywsjStart;
            $sql .= "
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
                   and fk.companyid=" . $this->getCompanyId() . "
                   AND fk.status!=1
                   AND fk.customer_id = c.id
                   and fk.yw_time <?";
            $sqlParams[] = $ywsjStart;
            $sql .= "
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
                   and mx.companyid=" . $this->getCompanyId() . "
                   AND mx.delete_time is null
                   AND qt.delete_time is null
                   AND qt.yw_time <?";
            $sqlParams[] = $ywsjStart;
            $sql .= "
                   )) qichu_yue,";
        } else {
            $sql .= " 0 qichu_yue,";
        }
        $sql .= " (ifnull((SELECT SUM(IFNULL(salemx.price_and_tax, 0))
                          FROM salesorder_details salemx
                                 LEFT JOIN salesorder sale on salemx.order_id = sale.id
                          WHERE sale.delete_time is null
                            and salemx.delete_time is null
                             and salemx.companyid=" . $this->getCompanyId() . "
                            and sale.status != 1
                            and sale.custom_id = c.id";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and sale.ywsj >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and sale.ywsj < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= "), 0) +IFNULL(
                         (SELECT SUM(IFNULL(fy.money, 0))
                          FROM capital_fy fy
                          WHERE fy.fang_xiang = 1
                          and fy.companyid=" . $this->getCompanyId() . "
                            AND fy.delete_time is null
                            and fy.status != 1
                            and fy.customer_id = c.id";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and fy.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and fy.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= "),0) + IFNULL(
                         (SELECT SUM(IFNULL(mx.money, 0))
                          FROM init_ysfk_mx mx
                                 LEFT JOIN init_ysfk ysfk ON mx.ysfk_id = ysfk.id
                          WHERE ysfk.type = 0
                            AND ysfk.delete_time is null
                             and mx.companyid=" . $this->getCompanyId() . "
                            and mx.delete_time is null
                            AND mx.customer_id = c.id
                            and ysfk.status != 1";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and ysfk.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and ysfk.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }

        $sql .= "),0 ) + IFNULL(
                                 (SELECT -SUM(
                                            IFNULL(mx.sum_shui_price, 0)
                                     )
                                  FROM sales_return_details mx
                                         LEFT JOIN sales_return th ON mx.xs_th_id = th.id
                                  WHERE th.customer_id = c.id
                                    AND th.delete_time is null
                                     and mx.companyid=" . $this->getCompanyId() . "
                                    and mx.delete_time is null
                                    AND th.status != 1";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and th.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and th.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }

        $sql .= "),0) + ifnull((SELECT sum(ifnull(qt.money, 0))
                                       FROM capital_other qt
                                       WHERE qt.customer_id = c.id
                                         and qt.fangxiang = 1
                                          and qt.companyid=" . $this->getCompanyId() . "
                                         and qt.delete_time is null
                                         and qt.status != 1";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and qt.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and qt.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= "), 0))ys, ( IFNULL(
                         (SELECT SUM(sk.money + IFNULL(sk.msmoney, 0))
                          FROM capital_sk sk
                          WHERE sk.customer_id = c.id
                            and sk.status != 1
                            and sk.companyid=" . $this->getCompanyId() . "
                            and sk.delete_time is null";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and sk.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and sk.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= "), 0))yf , 0  gcChPrice,
                   (ifnull(
                         (SELECT SUM(IFNULL(mx.price_and_tax, 0))
                          FROM salesorder_details mx
                                 LEFT JOIN salesorder sale ON mx.order_id = sale.id
                          WHERE mx.delete_time is null
                            and sale.status != 1
                             and mx.companyid=" . $this->getCompanyId() . "
                            AND sale.delete_time is null
                            AND sale.custom_id = c.id";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and sale.ywsj >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and sale.ywsj < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= "), 0)+ IFNULL((SELECT SUM(IFNULL(fy.money, 0))
                              FROM capital_fy fy
                              WHERE fy.fang_xiang = 1
                                AND fy.delete_time is null
                                and fy.status != 1
                                and fy.companyid=" . $this->getCompanyId() . "
                                AND fy.customer_id = c.id";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and fy.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and fy.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= "),0) + IFNULL((SELECT SUM(IFNULL(mx.money, 0))
                                  FROM init_ysfk_mx mx
                                         LEFT JOIN init_ysfk ysfk ON mx.ysfk_id = ysfk.id
                                  WHERE ysfk.type = 0
                                    and ysfk.delete_time is null
                                    AND mx.delete_time is null
                                    AND mx.customer_id = c.id
                                    and ysfk.status != 1
                                     and mx.companyid=" . $this->getCompanyId() . "
                                    and ysfk.delete_time is null";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and ysfk.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and ysfk.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= "),0) + IFNULL((SELECT -SUM(IFNULL(mx.sum_shui_price, 0))
                                  FROM sales_return_details mx
                                         LEFT JOIN sales_return th ON mx.xs_th_id = th.id
                                  WHERE th.customer_id = c.id
                                    and th.delete_time is null
                                    and th.status != 1
                                     and mx.companyid=" . $this->getCompanyId() . "
                                    and mx.delete_time is null";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and th.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and th.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= "
                               ),
                                 0
                           ) + (SELECT ifnull(sum(ifnull(mx.money, 0)), 0)

                                from capital_other_details mx
                                       LEFT JOIN capital_other qt on mx.cap_qt_id = qt.id
                                where qt.customer_id = c.id
                                  and qt.fangxiang = 1
                                  and qt.status != 1
                                   and mx.companyid=" . $this->getCompanyId() . "
                                  and qt.delete_time is null
                                  and qt.yw_type != 16";

        if (!empty($param['ywsjStart'])) {
            $sql .= ' and qt.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and qt.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= ")+ (SELECT ifnull(sum(ifnull(mx.money, 0)), 0)

                            from capital_other_details mx
                                   LEFT JOIN capital_other qt on mx.cap_qt_id = qt.id
                            where qt.customer_id = c.id
                              and qt.fangxiang = 1
                              and qt.status != 1
                               and mx.companyid=" . $this->getCompanyId() . "
                              and qt.delete_time is null
                              and qt.yw_type = 16";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and qt.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and qt.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= ")) yingshou_yue,
                   (SELECT ifnull(SUM(
                                    ifnull(sk.money, 0) + IFNULL(sk.msmoney, 0)
                                      ), 0)
                    FROM capital_sk sk
                    WHERE sk.delete_time is null
                      AND sk.status != 1
                       and sk.companyid=" . $this->getCompanyId() . "
                      AND sk.customer_id = c.id";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and sk.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and sk.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= ") shishou_jine,";
        if (!empty($param['ywsjStart'])) {
            $sql .= " (
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
                    and mx.companyid=" . $this->getCompanyId() . "
                   and sale.ywsj<?";
            $sqlParams[] = $ywsjStart;
            $sql .= "
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
                   and fy.companyid=" . $this->getCompanyId() . "
                   and fy.yw_time<?";
            $sqlParams[] = $ywsjStart;
            $sql .= "
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
                    and mx.companyid=" . $this->getCompanyId() . "
                   AND mx.delete_time is null
                   AND mx.customer_id = c.id
                   and ysfk.status!=1
                 
                   and ysfk.yw_time<?";
            $sqlParams[] = $ywsjStart;
            $sql .= "
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
                    and mx.companyid=" . $this->getCompanyId() . "
                   and th.delete_time is null
                   and mx.delete_time is null
                   and th.status!=1
                 
                   and th.yw_time<?";
            $sqlParams[] = $ywsjStart;
            $sql .= "
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
                    and sk.companyid=" . $this->getCompanyId() . "
                   AND sk.status!=1
                   AND sk.customer_id = c.id
                 
                   and sk.yw_time<?";
            $sqlParams[] = $ywsjStart;
            $sql .= "

                   )
                   ,0)
                   ) qichu_yue1,";

        } else {
            $sql .= "  0 qichu_yue1,";
        }
        $sql .= "
                   (SELECT IFNULL(SUM(IFNULL(sk.money, 0)), 0)  -
                           (SELECT IFNULL(SUM(IFNULL(hk.yfkhxmoney, 0)), 0)
                            FROM capital_hk hk where
                               hk.status != 1
                                and hk.companyid=" . $this->getCompanyId() . "
                               AND hk.customer_id = c.id) -
                           (SELECT -IFNULL(SUM(IFNULL(sk1.money, 0)), 0)
                            FROM capital_sk sk1
                            WHERE sk1.delete_time is null
                              AND sk1.status = 0
                              AND sk1.sk_type = 4
                              and sk1.companyid=" . $this->getCompanyId() . "
                              AND c.id = sk1.customer_id)
                    FROM capital_sk sk
                    WHERE sk.`customer_id` = c.id
                      AND sk.sk_type = 2
                      AND sk.delete_time is null
                      and sk.companyid=" . $this->getCompanyId() . "
                      AND sk.status = 0)       yushoukuanyue,
                   (SELECT IFNULL(SUM(IFNULL(fk.money, 0)), 0) -
                           (SELECT IFNULL(SUM(IFNULL(hk.yfkhxmoney, 0)), 0)
                            FROM capital_hk hk
                            WHERE hk.delete_time is null
                              AND hk.status != 1
                               and hk.companyid=" . $this->getCompanyId() . "
                              AND hk.customer_id = c.id)  -
                           (SELECT -IFNULL(SUM(IFNULL(fk1.money, 0)), 0)
                            FROM capital_fk fk1
                            WHERE fk1.delete_time is null
                              AND fk1.status != 1
                              AND fk1.fk_type = 4
                               and fk1.companyid=" . $this->getCompanyId() . "
                              AND fk1.customer_id = c.id)
                    FROM capital_fk fk
                    WHERE fk.customer_id = c.id
                      AND fk.fk_type = 2
                      AND fk.status != 1)      yufukuanyue


            FROM custom c
            where c.delete_time is null
             and c.companyid=" . $this->getCompanyId() . "
              and c.issupplier = 1) t1)t2
where 1 = 1";
        if (!empty($param['yuEweiLing'])) {
            $sql .= ' and t2.yingshou_yue != :yuEweiLing';
            $sqlParams['yuEweiLing'] = $param['yuEweiLing'];
        }

        if (!empty($params['wufaShengE'])) {
            $sql .= ' and t2.benqi_yingshou > 0';
        }
        if (!empty($param['danwei'])) {
            $sql .= ' and t2.wanglai like concat(:danwei)';
            $sqlParams['danwei'] = $param['danwei'];
        }
        $sql .= " order by t2.create_time desc)";

        $data = Db::table($sql)->alias('t')->bind($sqlParams)->paginate($pageLimit);
        return returnSuc($data);
    }

    /**应付账款明细表
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function getYfzkTongjiMxList($pageLimit = 10)
    {
        $params = request()->param();
        if (!empty($params['customer_id'])) {
            $customer_id = $params['customer_id'];
        }

        $ywsjStart = '';
        if (!empty($params['ywsjStart'])) {
            $ywsjStart = $params['ywsjStart'];
        }
        $ywsjEnd = '';
        if (!empty($params['ywsjEnd'])) {
            $ywsjEnd = $params['ywsjEnd'];
        }
        $sqlParams = [];
        $sql = "(SELECT
    NULL id,
       '' STATUS,
       NULL yw_time,
       basecu.custom wanglai,
       '期初' danju_leixing,
       NULL yewu_yuan,
       NULL bian_hao,
       '0.00' yingfu_jine,
       '0.00' shifu_jine,
       ((IFNULL(
           (SELECT
                   SUM(IFNULL(mx.sum_shui_price, 0))
            FROM
                 cg_purchase_mx mx
                   LEFT JOIN cg_purchase pur
                     ON mx.purchase_id = pur.id
            WHERE mx.delete_time is null
              AND pur.delete_time is null
              and mx.companyid=" . $this->getCompanyId() . "
              AND pur.status != 1
             ";
        if (!empty($param['customer_id'])) {
            $sql .= " AND pur.customer_id = ?";
            $sqlParams[] = $customer_id;
        }
        if (!empty($param['yw_time'])) {
            $sql .= "  AND pur.yw_time <=?";
            $sqlParams[] = $ywsjStart;
        }

        $sql .= "
               ),
               0) + IFNULL(
                      (SELECT
                              SUM(IFNULL(fy.money, 0))
                       FROM
                            capital_fy fy
                       WHERE fy.fang_xiang = 2
                         AND fy.delete_time is null
                         AND fy.status != 1
                         and fy.companyid=" . $this->getCompanyId() . "
                         ";
        if (!empty($param['customer_id'])) {
            $sql .= " AND fy.customer_id = ?";
            $sqlParams[] = $customer_id;
        }
        if (!empty($param['yw_time'])) {
            $sql .= "  AND fy.yw_time <=?";
            $sqlParams[] = $ywsjStart;
        }
        $sql .= "
                          ),
                          0) + IFNULL(
                                 (SELECT
                                         SUM(IFNULL(mx.money, 0))
                                  FROM
                                       init_ysfk_mx mx
                                         LEFT JOIN init_ysfk ysfk
                                           ON mx.ysfk_id = ysfk.id
                                  WHERE ysfk.type = 1
                                    AND ysfk.delete_time is null
                                    and mx.companyid=" . $this->getCompanyId() . "
                                    and mx.delete_time is null
                         
                                    AND ysfk.status != 1

                                    ";
        if (!empty($param['customer_id'])) {
            $sql .= " AND ysfk.customer_id = ?";
            $sqlParams[] = $customer_id;
        }
        if (!empty($param['yw_time'])) {
            $sql .= "  AND ysfk.yw_time <=?";
            $sqlParams[] = $ywsjStart;
        }
        $sql .= "
                                     ),
                                     0)
                          ) + IFNULL(
                                (SELECT
                                        - SUM(IFNULL(mx.sum_shui_price, 0))
                                 FROM
                                      cg_th_mx mx
                                        LEFT JOIN cg_th th
                                          ON mx.cg_th_id = th.id
                                 WHERE th.delete_time is null
                                   and mx.delete_time is null
                                    and mx.companyid=" . $this->getCompanyId() . "
                                   AND th.status != 1
                               
                                               ";
        if (!empty($param['customer_id'])) {
            $sql .= " AND th.customer_id = ?";
            $sqlParams[] = $customer_id;
        }
        if (!empty($param['yw_time'])) {
            $sql .= "  AND th.yw_time <=?";
            $sqlParams[] = $ywsjStart;
        }
        $sql .= "
                                    ),
                                    0) - IFNULL(
                                           (SELECT
                                                   SUM(fk.money + IFNULL(fk.mfmoney, 0))
                                            FROM
                                                 capital_fk fk
                                            WHERE fk.delete_time is null
                                             and fk.companyid=" . $this->getCompanyId() . "
                                              AND fk.status != 1
                                              ";
        if (!empty($param['customer_id'])) {
            $sql .= " AND fk.customer_id = ?";
            $sqlParams[] = $customer_id;
        }
        if (!empty($param['yw_time'])) {
            $sql .= "  AND fk.yw_time <=?";
            $sqlParams[] = $ywsjStart;
        }
        $sql .= "
                                               ),
                                               0)
                                    ) yue,
                                basecu.id customer_id,
                                NULL beizhu,'' signPerson FROM custom basecu
                                where basecu.iscustom=1
                               ";
        if (!empty($param['customer_id'])) {
            $sql .= " AND basecu.id=?";
            $sqlParams[] = $customer_id;
        }
        $sql .= "
                                       
                                                      GROUP BY basecu.`id`
                                                      union all

                                                      select t3.id,
                                                             t3.status,
                                                             t3.yw_time,
                                                             t3.wanglai,
                                                             t3.danju_leixing,
                                                             t3.yewu_yuan,
                                                             t3.bian_hao,
                                                             t3.yingfu_jine yingfu_jine,
                                                             t3.shifu_jine shifu_jine,
                                                             t3.yue,
                                                             t3.customer_id,
                                                             t3.beizhu,
                                                             t3.signPerson
from
                                                      (
                                                      select t2.id,t2.status,t2.yw_time,t2.wanglai,t2.danju_leixing,
                                                      t2.yewu_yuan,t2.bian_hao,t2.yingfu_jine yingfu_jine,t2.shifu_jine shifu_jine,t2.yue,t2.customer_id,t2.beizhu,t2.signPerson from
                                                      (
                                                      select
                                                      t1.id,t1.status,t1.yw_time,
                                                      t1.wanglai,t1.danju_leixing,
                                                      t1.yewu_yuan,t1.bian_hao,
                                                      sum(t1.yingfu_jine) yingfu_jine,sum(t1.shifu_jine)shifu_jine,sum(t1.yue) yue,t1.customer_id,t1.beizhu,t1.signPerson
                                                      from (
                                                      SELECT
                                                      se.id,
                                                      se.`status`,
                                                      se.yw_time yw_time,
                                                      cus.`custom` wanglai,
                                                      '采购单' danju_leixing,

                                                      op.name yewu_yuan,
                                                      se.system_number bian_hao,
                                                      mx.sum_shui_price yingfu_jine,
                                                      null shifu_jine,null yue,
                                                      se.customer_id,se.beizhu,'' signPerson
                                                      FROM
                                                      cg_purchase se
                                                      LEFT JOIN cg_purchase_mx mx on mx.purchase_id =se.id
                                                      LEFT JOIN custom cus on se.customer_id =cus.id

                                                      LEFT JOIN admin op on se.sale_operate_id =op.id
                                                      where
                                                      mx.delete_time is null
                                                       and mx.companyid=" . $this->getCompanyId() . "
                                                      and cus.iscustom=1


                                                      and se.delete_time is null

                                                   

                                                      union all
                                                      SELECT
                                                      fk.id,
                                                      fk.`status`,
                                                      fk.yw_time yw_time ,
                                                      cus.`custom` wanglai,
                                                      '付款单' danju_leixing,
                                                      op.name yewu_yuan,
                                                      fk.system_number bian_hao,
                                                      null yingfu_jine,
                                                      ifnull(fk.money,0) shifu_jine,null yue,
                                                      fk.customer_id,fk.beizhu,'' signPerson
                                                      from
                                                      capital_fk fk
                                                      LEFT JOIN custom cus on fk.customer_id =cus.id

                                                      LEFT JOIN admin op on fk.sale_operator_id =op.id
                                                      where
                                                      fk.delete_time is null 
                                                      and fk.companyid=" . $this->getCompanyId() . "

                                                      and fk.money != 0
                                                      and cus.iscustom=1

                                                      UNION all
                                                      SELECT
                                                      fy.id,
                                                      fy.`status`,
                                                      fy.yw_time yw_time,
                                                      cus.`custom` wanglai,
                                                      '费用单(付款)' danju_leixing,

                                                      op.name yewu_yuan,
                                                      fy.system_number bian_hao,
                                                      fy.money yingfu_jine,
                                                      null shifu_jine,null yue,
                                                      fy.customer_id,fy.beizhu,'' signPerson
                                                      from
                                                      capital_fy fy
                                                      LEFT JOIN custom cus on fy.customer_id =cus.id

                                                      LEFT JOIN admin op on fy.sale_operator_id =op.id
                                                      where
                                                      fy.fang_xiang=2
                                                      and fy.delete_time is null
                                                      and fy.companyid=" . $this->getCompanyId() . "

                                                      and cus.iscustom=1

                                                      union ALL
                                                      SELECT
                                                      th.id,
                                                      th.`status`,
                                                      th.yw_time yw_time,
                                                      cus.`custom` wanglai,
                                                      '采购退货单' danju_leixing,

                                                      op.name yewu_yuan,
                                                      th.system_number bian_hao,
                                                      -mx.sum_shui_price yingfu_jine,
                                                      null shifu_jine,null yue,
                                                      th.customer_id,th.beizhu,'' signPerson
                                                      FROM
                                                      cg_th th
                                                      LEFT JOIN cg_th_mx mx ON mx.cg_th_id = th.id
                                                      LEFT JOIN custom cus on th.customer_id =cus.id

                                                      LEFT JOIN admin op on th.sale_operate_id =op.id
                                                      WHERE

                                                      mx.delete_time is null
                                                      and th.delete_time is null
                                                      and cus.iscustom=1
                                                      and th.companyid=" . $this->getCompanyId() . "

                                                      union all
                                                      SELECT
                                                      qt.id,
                                                      qt.`status`,
                                                      qt.yw_time ,
                                                      cus.`custom` wang_lai,
                                                      '其它应付款' danju_leixing,
                                                      op.name yewu_yuan,
                                                      qt.system_number bian_hao,
                                                      sum(mx.money) yingfu_jine,
                                                      null shifu_jine,null yue,
                                                      qt.customer_id,
                                                      qt.beizhu,'' signPerson
                                                      from  capital_other_details mx
                                                      LEFT JOIN capital_other qt on mx.cap_qt_id=qt.id
                                                      LEFT JOIN custom cus on qt.customer_id =cus.id
                                                      


                                                      LEFT JOIN admin op on qt.sale_operator_id = op.id
                                                      where
                                                      qt.fangxiang=2
                                                      and cus.iscustom=1
                                                        and mx.companyid=" . $this->getCompanyId() . "
                                                      and mx.delete_time is null
                                                      and qt.delete_time is null
                                                      group by qt.id

                                                      )t1 GROUP BY
                                                      t1.id
                                                      union all
                                                      SELECT
                                                      fk.id,
                                                      fk.`status`,
                                                      fk.yw_time yw_time ,
                                                      cus.`custom` wanglai,
                                                      '付款单' danju_leixing,
                                                      op.name yewu_yuan,
                                                      fk.system_number bian_hao,
                                                      null yingfu_jine,
                                                      ifnull(fk.mfmoney,0) shifu_jine,null yue,
                                                      fk.customer_id,'付款优惠，红字冲减应付款' beizhu,'' signPerson
                                                      from
                                                      capital_fk fk
                                                      LEFT JOIN custom cus on fk.customer_id =cus.id

                                                      LEFT JOIN admin op on fk.sale_operator_id =op.id
                                                      where
                                                      fk.delete_time is null
                                                      and fk.companyid=" . $this->getCompanyId() . "

                                                      and ifnull(fk.mfmoney,0)>0
                                                      and cus.iscustom=1

                                                      UNION ALL
                                                      SELECT
                                                      ysfk.id,
                                                      ysfk.`status`,
                                                      ysfk.yw_time yw_time,
                                                      cus.`custom` wanglai,
                                                      '期初应付' danju_leixing,

                                                      op.name  yewu_yuan,
                                                      ysfk.system_number bian_hao,
                                                      sum(ifnull(mx.money,0)) yingfu_jine,
                                                      null shifu_jine,null yue,
                                                      mx.customer_id,ysfk.beizhu,'' signPerson
                                                      FROM   init_ysfk ysfk
                                                      LEFT JOIN init_ysfk_mx mx  on mx.ysfk_id =ysfk.id
                                                      LEFT JOIN custom cus on mx.customer_id =cus.id

                                                      LEFT JOIN admin op on ysfk.sale_operator_id =op.id
                                                      WHERE
                                                      ysfk.type=\"1\"
                                                      and ysfk.delete_time is null
                                                      and ysfk.companyid=" . $this->getCompanyId() . "

                                                      and ysfk.delete_time is null
                                                      and cus.iscustom=1

                                                      GROUP BY mx.customer_id,ysfk.id
                                                      ) t2
                                                      where
                                                      1=1";
        if (!empty($params['customer_id'])) {
            $sql .= ' and t2.customer_id= ?';
            $sqlParams[] = $params['customer_id'];
        }
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and t2.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and t2.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        if (!empty($params['status'])) {
            $sql .= ' and t2.status = ?';
            $sqlParams[] = $params['status'];
        }
        if (!empty($params['djlx'])) {
            $sql .= ' and t2.danju_leixing like ?';
            $sqlParams[] = '%' . $params['djlx'] . '%';
        }

        if (!empty($params['yewuyuan'])) {
            $sql .= ' and t2.yewu_yuan like ?';
            $sqlParams[] = '%' . $params['yewuyuan'] . '%';
        }
        if (!empty($params['system_number'])) {
            $sql .= ' and t2.bian_hao like ?';
            $sqlParams[] = '%' . $params['system_number'] . '%';
        }
        $sql .= ') t3)';

        $data = Db::table($sql)->alias('t')->bind($sqlParams)->order('yw_time')->paginate($pageLimit);
        return returnSuc($data);
    }

    public function ysjxfp($pageLimit = 10)
    {
        $ywsjStart = '';
        $param = request()->param();
        if (!empty($param['ywsjStart'])) {
            $ywsjStart = $param['ywsjStart'];
        }
        $ywsjEnd = '';
        if (!empty($param['ywsjEnd'])) {
            $ywsjEnd = date('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sqlParams = [];
        $sql = "(select
       t2.id gysid,
       t2.daima,
       t2.gongying_shang,
       t2.qichu_yingshou,
       t2.benqi_yingshou,
       t2.benqi_yishou,
       t2.moren_yewuyuan,
       t2.suoshu_department,
       t2.create_time,
       t2.qimo_yue
from (
     SELECT
            t1.id,
            t1.daima,
            t1.gongying_shang,
            t1.qichu_yingshou,
            t1.benqi_yingshou,
            t1.benqi_yishou,
            t1.moren_yewuyuan,
            t1.suoshu_department,
            t1.create_time,
            IFNULL(t1.qichu_yingshou,0)+(IFNULL(t1.benqi_yingshou,0)-IFNULL(t1.benqi_yishou,0))
         qimo_yue
     FROM
          (
          SELECT
                 cus.id,
                 cus.`zjm` daima,
                 cus.`custom` gongying_shang,
                 cus.moren_yewuyuan,
                 cus.create_time,
                 cus.suoshu_department,";
        if (!empty($param['ywsjStart'])) {
            $sql .= "(
              (
              ifnull( (SELECT
              SUM(
              IFNULL(mx.sum_shui_price, 0)
              )

              FROM
              cg_purchase_mx mx
              LEFT JOIN cg_purchase se ON mx.purchase_id = se.id
              WHERE
              se.customer_id = cus.id
              and se.status!=1
               and mx.companyid=" . $this->getCompanyId() . "
              and se.delete_time is null
              and mx.delete_time is null
              and mx.shui_price > 0

              AND se.yw_time <?";
            $sqlParams[] = $ywsjStart;
            $sql .= "
              ),0)

              )+ IFNULL(
              (
              SELECT
              SUM(IFNULL(mx.money, 0))
              FROM
              init_yskp_mx mx
              LEFT JOIN init_yskp yskp ON mx.yskp_id = yskp.id
              WHERE
              mx.customer_id = cus.id
              AND yskp.type = 0
              and yskp.delete_time is null
              and yskp.status!=1
              and mx.companyid=" . $this->getCompanyId() . "
              and mx.delete_time is null
              AND yskp.yw_time  <?";
            $sqlParams[] = $ywsjStart;
            $sql .= "

              ),
              0)
              +IFNULL(
              (
              SELECT
              -SUM(IFNULL(mx.sum_shui_price, 0))
              FROM
              cg_th_mx mx
              LEFT JOIN cg_th th ON mx.cg_th_id = th.id
              WHERE
              th.customer_id = cus.id
              and mx.shui_price>0
              and th.delete_time is null
              and mx.companyid=" . $this->getCompanyId() . "
              and th.status!=1
              and mx.delete_time is null
              AND th.yw_time  <?";
            $sqlParams[] = $ywsjStart;
            $sql .= "

              ),
              0
              ) - IFNULL(
              (
              SELECT
              SUM(IFNULL(sp.money, 0)+IFNULL(sp.msmoney, 0))
              FROM
              inv_cgsp sp
              WHERE
              sp.gys_id = cus.id
              and sp.status!=1
              and sp.delete_time is null
              and sp.companyid=" . $this->getCompanyId() . "

              AND sp.yw_time  <?";
            $sqlParams[] = $ywsjStart;
            $sql .= "
              ),
              0
              )
              ) qichu_yingshou,";

        } else {

            $sql .= "  0 qichu_yingshou,";
        }
        $sql .= "
        
              (
              (
              ifnull(
              (
              SELECT
              SUM(
              IFNULL(mx.sum_shui_price, 0)
              )
              FROM
              cg_purchase_mx mx
              LEFT JOIN cg_purchase se ON mx.purchase_id = se.id

              WHERE
              se.customer_id = cus.id
              and mx.shui_price > 0
              and se.status!=1
              and mx.companyid=" . $this->getCompanyId() . "
              and se.delete_time is null
              and mx.delete_time is null";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and se.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and se.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= "

             
		),0)

		)+ IFNULL(
		(
		SELECT
		SUM(IFNULL(mx.money, 0))
		FROM
		init_yskp_mx mx
		LEFT JOIN init_yskp yskp ON mx.yskp_id = yskp.id
		WHERE
		mx.customer_id = cus.id
		and yskp.status!=1
		and mx.companyid=" . $this->getCompanyId() . "
		and mx.delete_time is null
		AND yskp.type = 0
		and yskp.delete_time is null";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and yskp.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and yskp.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= "
		),
		0
		)+IFNULL(
		(
		SELECT
		-SUM(IFNULL(mx.sum_shui_price, 0))
		FROM
		cg_th_mx mx
		LEFT JOIN cg_th th ON mx.cg_th_id = th.id
		WHERE
		th.customer_id = cus.id
		and mx.shui_price>0
		and mx.companyid=" . $this->getCompanyId() . "
		and th.delete_time is null
		and mx.delete_time is null
		and th.status!=1";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and th.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and th.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= "

		),
		0
		)
		) benqi_yingshou,
		(
		IFNULL(
		(
		SELECT
		SUM(IFNULL(sp.money, 0)+IFNULL(sp.msmoney, 0))
		FROM
		inv_cgsp sp

		WHERE
		sp.gys_id = cus.id
		and sp.status!=1
		and sp.companyid=" . $this->getCompanyId() . "
		and sp.delete_time is null";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and sp.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and sp.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= "

		),
		0
		)
		) benqi_yishou
		FROM
		custom cus
		where
    cus.delete_time is null
    and cus.companyid=" . $this->getCompanyId() . "
		and cus.`iscustom`=1
		) t1
		)t2
		where
		1=1";
        if (!empty($params['yuEweiLing'])) {
            $sql .= ' and t2.benqi_yingshou > 0';
        }
        if (!empty($params['wufaShengE'])) {
            $sql .= ' and t2.benqi_yingshou > 0';
        }
        if (!empty($param['customer_id'])) {
            $sql .= ' and t2.gysid =:customer_id';
            $sqlParams['customer_id'] = $param['customer_id'];
        }
        $sql .= " order by t2.create_time desc)";
        $data = Db::table($sql)->alias('t')->bind($sqlParams)->paginate($pageLimit);
        return returnSuc($data);
    }

    public function ysjxfpmx($customer_id, $pageLimit = 10)
    {
        $params = request()->param();
        $ywsjStart = '';
        if (!empty($params['ywsjStart'])) {
            $ywsjStart = $params['ywsjStart'];
        }
        $ywsjEnd = '';
        if (!empty($params['ywsjEnd'])) {
            $ywsjEnd = $params['ywsjEnd'];
        }
        $sqlParams = [];
        $sql = "(SELECT
			 '' id,'' zbid,'' danhao,'' caigou_danhao,'' beizhu,null yw_time,'' piaoju,'' gys_id,tbcu.custom danwei,'' guige,'' danjia,'' pin_ming,'' `STATUS`,'' zhong_liang,'' jiashui_heji,'' shoupiao_jine,'' fapiao_taitou,'' signPerson,
			 SUM(inv.sum_shui_price) - IFNULL(
																	 (SELECT
																					 SUM(sp.money) + IFNULL(SUM(sp.msmoney), 0)
																		FROM
																				 inv_cgsp sp
																		WHERE sp.gys_id = inv.customer_id
																		 and sp.companyid=" . $this->getCompanyId() . "

																			AND sp.delete_time is null
																			AND sp.status != 1
																			and sp.yw_time  <=?";
        $sqlParams[] = $ywsjStart;
        $sql .= "

																			 ),
																			 0
																			 ) + IFNULL(
																			 (SELECT
																			 - SUM(hk.money) money
																			 FROM
																			 capital_hk hk
																			 WHERE hk.customer_id = inv.customer_id
																			 and hk.companyid=" . $this->getCompanyId() . "

																			 AND hk.delete_time is null
																			 AND hk.status != 1
																			 AND hk.fangxiang = 2
																			 AND hk.hk_type = 13

																			 and hk.yw_time  <=?";
        $sqlParams[] = $ywsjStart;
        $sql .= "
																			 ),
																			 0
																			 ) yue
																			 FROM
																			 inv inv
																			 left join custom tbcu on tbcu.id=inv.customer_id
																			 WHERE inv.customer_id = ?";
        $sqlParams[] = $customer_id;
        $sql .= "

																			 AND inv.shui_price > 0
																			 and inv.companyid=" . $this->getCompanyId() . "
																			 AND inv.fx_type = 2
																			 AND inv.delete_time is null
																			 and inv.yw_time <=?";
        $sqlParams[] = $ywsjStart;
        $sql .= "
union all
																			 SELECT
																			 t2.id,t2.zbid,t2.danhao,t2.caigou_danhao,t2.beizhu,t2.yw_time,t2.piaoju,t2.gys_id,t2.danwei,t2.guige,t2.danjia,t2.pin_ming,t2.`STATUS`,t2.zhong_liang,t2.jiashui_heji,t2.shoupiao_jine,t2.fapiao_taitou,t2.signPerson,'' yue
																			 from (
																			 SELECT
																			 t1.id,t1.zbid,t1.danhao,t1.yw_time,t1.piaoju,t1.gys_id,t1.danwei,t1.caigou_danhao,t1.guige,t1.danjia,t1.pin_ming,t1.`STATUS`,t1.zhong_liang,t1.jiashui_heji,t1.shoupiao_jine,t1.fapiao_taitou,t1.beizhu,t1.signPerson
																			 from (

																			 SELECT
																			 mx.id,
																			 se.id zbid,
																			 se.customer_id gys_id,
																			 cus. custom danwei,
																			 null danhao,
																			 se.yw_time,
																			 pjlx.`pjlx` piaoju,
																			 se.system_number caigou_danhao,
																			 gg.specification guige,
																			 mx.price danjia,
																			 pm.`name` pin_ming,
																			 se. STATUS,
																			 mx.zhongliang zhong_liang,
																			 mx.sum_shui_price jiashui_heji,
																			 null shoupiao_jine,
																			 null fapiao_taitou,
																			 mx.beizhu,'' signPerson
																			 FROM
																			 cg_purchase_mx mx
																			 LEFT JOIN cg_purchase se ON mx.purchase_id = se.id
																			 LEFT JOIN pjlx pjlx ON se.piaoju_id = pjlx.id
																			 LEFT JOIN custom cus ON se.customer_id = cus.id
																			 LEFT JOIN specification gg ON mx.guige_id = gg.id
																			 LEFT JOIN productname pm ON gg.productname_id = pm.id
																			 WHERE
																			 se.delete_time is null
																			 and mx.companyid=" . $this->getCompanyId() . "

																			 and mx.shui_price>0
																			 and mx.sum_shui_price>0
																			 and mx.delete_time is null


																		union ALL
																			 SELECT
																			 cgsp.id,
																			 cgsp.id zbid,
																			 cgsp.gys_id,
																			 cus. custom danwei,
																			 cgsp.system_number danhao,
																			 cgsp.yw_time,
																			 pjlx.`pjlx` piaoju,
																			 null caigou_danhao,
																			 null guige,
																			 null danjia,
																			 null pin_ming,
																			 cgsp. STATUS,
																			 null zhong_liang,
																			 null jiashui_heji,
																			 ifnull(cgsp.money,0) shoupiao_jine,
																			 cgsp.taitou fapiao_taitou,
																			 cgsp.beizhu,'' signPerson
																			 FROM
																			 inv_cgsp cgsp
																			 LEFT JOIN custom cus ON cgsp.gys_id = cus.id
																			 LEFT JOIN pjlx pjlx ON cgsp.piaoju_id = pjlx.id
																			 WHERE

																			 cgsp.money!=0 and cgsp.companyid=" . $this->getCompanyId() . "  and
																			 cgsp.delete_time is null";


        if (!empty($param['ywsjStart'])) {
            $sql .= ' and cgsp.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and cgsp.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= "

		union ALL
		SELECT
		cgsp.id,
		cgsp.id zbid,
		cgsp.gys_id,
		cus. custom danwei,
		cgsp.system_number danhao,
		cgsp.yw_time,
		null piaoju,
		null caigou_danhao,
		pjlx.pjlx guige,
		null danjia,
		null pin_ming,
		cgsp. STATUS,
		null zhong_liang,
		null jiashui_heji,
		IFNULL(cgsp.msmoney,0) shoupiao_jine,
		cgsp.taitou fapiao_taitou,
		'收票优惠，红字冲减应收进项票' beizhu,'' signPerson
		FROM
		inv_cgsp cgsp
		LEFT JOIN custom cus ON cgsp.gys_id = cus.id
		LEFT JOIN pjlx pjlx ON cgsp.piaoju_id = pjlx.id
		WHERE

		cgsp.msmoney!=0  and cgsp.companyid=" . $this->getCompanyId() . " and
		cgsp.delete_time is null";

        if (!empty($param['ywsjStart'])) {
            $sql .= ' and cgsp.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and cgsp.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= "

		union ALL
		SELECT
		mx.id,
		th.id zbid,
		th.customer_id gys_id,
		cus. custom danwei,
		null danhao,
		th.yw_time,
		pjlx.pjlx piaoju,
		th.system_number caigou_danhao,
		gg.specification guige,
		mx.price danjia,
		pm.name pin_ming,
		th. STATUS,
		-mx.zhongliang zhong_liang,
		-mx.sum_shui_price jiashui_heji,
		null shoupiao_jine,
		null fapiao_taitou,
		mx.beizhu,'' signPerson
		FROM
		cg_th_mx mx
		LEFT JOIN cg_th th ON mx.cg_th_id = th.id
		LEFT JOIN pjlx pjlx ON th.piaoju_id = pjlx.id
		LEFT JOIN custom cus ON th.customer_id = cus.id
		LEFT JOIN specification gg ON mx.guige_id = gg.id
		LEFT JOIN productname pm ON gg.productname_id = pm.id
		WHERE
		th.delete_time is null
        and mx.companyid=" . $this->getCompanyId() . "
		and mx.shui_price>0
		and mx.sum_shui_price>0
		and mx.delete_time is null";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and th.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and th.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= "

		union ALL
		SELECT
		mx.id,
		yskp.id zbid,
		mx.customer_id gys_id,
		cus. custom danwei,
		null danhao,
		yskp.yw_time,
		pjlx.pjlx piaoju,
		yskp.system_number caigou_danhao,
		null guige,
		mx.price danjia,
		null pin_ming,
		yskp. STATUS,
		mx.zhongliang zhong_liang,
		mx.money jiashui_heji,
		null shoupiao_jine,
		null fapiao_taitou,
		mx.beizhu,'' signPerson
		FROM
		init_yskp_mx mx
		LEFT JOIN init_yskp yskp ON mx.yskp_id = yskp.id
		LEFT JOIN custom cus ON mx.customer_id = cus.id
		LEFT JOIN pjlx pjlx ON mx.piaoju_id = pjlx.id
		WHERE
		yskp.delete_time is null
		and mx.delete_time is null
		and mx.companyid=" . $this->getCompanyId() . "
		and yskp.type=0";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and yskp.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and yskp.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= "
		)t1
		where
		1=1";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and t1.yw_time >=?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and t1.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }


        if (!empty($params['customer_id'])) {
            $sql .= ' and  t1.gys_id= ?';
            $sqlParams[] = $params['customer_id'];
        }
        if (!empty($params['status'])) {
            $sql .= ' and  t1.status = ?';
            $sqlParams[] = $params['status'];
        }
        if (!empty($params['piaoju'])) {
            $sql .= ' and  t1.piaoju = ?';
            $sqlParams[] = $params['piaoju'];
        }

        if (!empty($params['status'])) {
            $sql .= ' and t1.status = ?';
            $sqlParams[] = $params['status'];
        }
        if (!empty($params['status'])) {
            $sql .= 'and
			t1.beizhu like %' . $params['status'] . '%';
        }

        $sql .= ' order by t1.yw_time )t2)';

        $data = Db::table($sql)->alias('t')->bind($sqlParams)->order('yw_time')->paginate($pageLimit);
        return returnSuc($data);
    }

}