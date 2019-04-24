<?php

namespace app\admin\controller;

use app\admin\model\{CapitalFy, CgPurchase, CgPurchaseMx, CgTh, InitYskp, KcRk, KcRkTz};
use app\admin\validate\{FeiyongDetails};
use Exception;
use think\{Db, Request};

class Cg extends Right
{
    public function cgth()
    {
        $params = request()->param();
        $list = CgTh::with(["jsfsData", "customData", "pjlxData"])->where('companyid', $this->getCompanyId());
        //往来单位
        if (!empty($params['customer_id'])) {
            $list->where('customer_id', $params['customer_id']);
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

    public function cgthmx($id = 0)
    {
        $data = $list = CgTh::with(['details' => ['specification', 'jsfs', 'storage', 'pinmingData', 'caizhiData', 'chandiData'], "jsfsData", "customData", "pjlxData",
        ])
            ->where('companyid', $this->getCompanyId())
            ->where('id', $id)
            ->find();
        if (empty($data)) {
            return returnFail('数据不存在');
        } else {
            return returnRes(true, '', $data);
        }
    }

    public function addcgth(Request $request, $data = [], $return = false)
    {
        if (request()->isPost()) {
            $companyId = $this->getCompanyId();
            $data = request()->post();
            $count = InitYskp::whereTime('create_time', 'today')->count();
            $data["status"] = 0;
            $data['create_operator_id'] = $this->getAccountId();
            $data['companyid'] = $companyId;
            $data['system_number'] = 'CGTHD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            if (!$return) {
                Db::startTrans();
            }
            try {
                model("CgTh")->allowField(true)->data($data)->save();
                $id = model("CgTh")->getLastInsID();
                $totalMoney = 0;
                $totalWeight = 0;
                foreach ($data["details"] as $c => $v) {
                    $info = db("kc_spot")->where("id", $v["spot_id"])->field("counts,zhongliang")->find();
//                    dump($info);die;
                    if ($v["counts"] > $info["counts"]) {
                        return returnFail('退货数量不得大于' . $info["counts"]);
                    }
                    if ($v["zhongliang"] > $info["zhongliang"]) {
                        return returnFail('退货重量不得大于' . $info["zhongliang"]);
                    }
                    $totalMoney += $v['sum_shui_price'];
                    $totalWeight += $v['zhongliang'];
                    $dat['details'][$c]['counts'] = $info["counts"] - $v["counts"];
                    $dat['details'][$c]['zhongliang'] = $info["zhongliang"] - $v["zhongliang"];
                    $dat['details'][$c]['jianshu'] = intval(floor($dat['details'][$c]['counts'] / $v["zhijian"]));
                    $dat['details'][$c]['lingzhi'] = $dat['details'][$c]['counts'] % $v["zhijian"];
                    $dat["details"][$c]["id"] = $v["spot_id"];
                    $data['details'][$c]['companyid'] = $companyId;
                    $data['details'][$c]['cg_th_id'] = $id;
                }
//                model('KcSpot')->allowField(true)->saveAll($dat['details']);
                model('CgThMx')->allowField(true)->saveAll($data['details']);
                $count = \app\admin\model\Salesorder::whereTime('create_time', 'today')
                    ->where('companyid', $companyId)
                    ->count();

                $systemNumber = 'XSD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
                //自动出库，生成出库单
                $stockOutData = [
                    'remark' => '退货单，' . $systemNumber,
                    'yw_time' => $data['yw_time'],
                    'department' => $data['group_id'],
                    'sale_operator_id' => $data['sale_operator_id'],
                    'details' => [],
                    'data_id' => $id
                ];
                $stockOutDetail = [];
                $index = -1;
                $spotIds = [];
                foreach ($data['details'] as $v) {
                    $v['index'] = $v['index'] ?? $index--;
                    $spotId = $v['spot_id'] ?? $spotIds[$v['index']];
                    $stockOutData['details'][] = [
                        'zhongliang' => $v['zhongliang'] ?? '',
                        'kucun_cktz_id' => $v['index'],
                        'kc_spot_id' => $spotId,
                        'ylsh' => $v['ylsh_id'] ?? 0
                    ];
                    $stockOutDetail[$v['index']] = [
                        'companyid' => $companyId,
                        'chuku_type' => 10,
                        'data_id' => $id,
                        'guige_id' => $v['guige_id'],
                        'caizhi' => $v['caizhi'] ?? '',
                        'chandi' => $v['chandi'] ?? '',
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
                        'sumprice' => $v['sumprice'] ?? '',
                        'shuie' => $v['shuie'] ?? '',
                        'shui_price' => $v['shui_price'] ?? '',
                        'sum_shui_price' => $v['sum_shui_price'] ?? '',
                        'remark' => $v['remark'] ?? '',
                        'car_no' => $v['car_no'] ?? '',
                        'pihao' => $v['pihao'] ?? '',
                        'cache_ywtime' => $data['yw_time'],
                        'cache_data_pnumber' => $data['system_number'],
                        'cache_customer_id' => $data['customer_id'],
                        'store_id' => $v['store_id'],
                        'cache_create_operator' => $data['cache_create_operator'],
                    ];
                }
                $res = (new Chuku())->add($request, $stockOutData, $stockOutDetail, 1, true);
                //其他费用
                $num = 1;
                if (!empty($data['other'])) {
                    $otherValidate = new FeiyongDetails();
                    //处理其他费用
                    foreach ($data['other'] as $c => $v) {
                        $data['other'][$c]['group_id'] = $data['department'] ?? '';
                        $data['other'][$c]['sale_operator_id'] = $data['employer'] ?? '';

                        if (!$otherValidate->check($data['other'][$c])) {
                            throw new Exception('请检查第' . $num . '行' . $otherValidate->getError());
                        }
                        $num++;
                    }
                    $res = (new Feiyong())->addAll($data['other'], 1, $id, $data['yw_time'], false);
                    if ($res !== true) {
                        throw new Exception($res);
                    }
                }
                //向货款单添加数据
                $capitalHkData = [
                    'hk_type' => CapitalHk::PURCHASE_RETURN,
                    'data_id' => $id,
                    'fangxiang' => 2,
                    'customer_id' => $data['customer_id'],
                    'jiesuan_id' => $data['jiesuan_id'],
                    'system_number' => $data['system_number'],
                    'yw_time' => $data['yw_time'],
                    'beizhu' => $data['beizhu'],
                    'money' => (-$totalMoney),
                    'group_id' => $data['group_id'],
                    'sale_operator_id' => $data['sale_operator_id'],
                    'create_operator_id' => $data['create_operator_id'],
                    'zhongliang' => (-$totalWeight),
                    'cache_pjlx_id' => $data['piaoju_id'],
                ];
                (new CapitalHk())->add($capitalHkData);

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


    public function edit(Request $request, $moshi_type = 4, $data = [])
    {
        Db::startTrans();
        try {
            if (empty($data)) {
                $data = $request->post();
            }
            $validate = new \app\admin\validate\CgPurchase();
            if (!$validate->check($data)) {
                return returnFail($validate->getError());
            }

            $addList = [];
            $updateList = [];
            $detailValidate = new \app\admin\validate\CgPurchaseMx();
            $num = 1;
            foreach ($data['details'] as $item) {
                if (!$detailValidate->check($item)) {
                    return returnFail('请检查第' . $num . '行  ' . $data['details']);
                }
                $item['caizhi'] = $this->getCaizhiId($item['caizhi']);
                $item['chandi'] = $this->getChandiId($item['chandi']);
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
                    $rk = (new KcRk())->insertRuku($cg['id'], "4", $cg['yw_time'], $cg['group_id'], $cg['system_number'], $cg['sale_operator_id'], $this->getAccountId(), $this->getCompanyId());
                }
            } else {
                $cg = CgPurchase::where('companyid', $companyId)->where('id', $data['id'])->find();
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

                $mxList = (new CgPurchaseMx())->where("purchase_id", $cg["id"])->select();
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
                        $mx = CgPurchaseMx::where('id', $mjo['id'])->find();
                        $mx->allowField(true)->data($mjo)->isUpdate(true)->save();
                        (new KcRkTz())->updateRukuTz($mx["id"], $mx["ruku_type"], $mx["pinming_id"], $mx["guige_id"], $mx["caizhi_id"], $mx["chandi_id"], $mx["jijiafangshi_id"]
                            , $mx["houdu"], $mx["changdu"], $mx["kuandu"], $mx["counts"], $mx["jianshu"], $mx["lingzhi"], $mx["zhijian"], $mx["zhongliang"], $mx["shui_price"]
                            , $mx["pihao"], $mx["beizhu"], $mx["chehao"], $mx["cache_ywtime"], $mx["cache_data_number"], $mx["cache_data_pnumber"], $mx["cache_customer_Id"]
                            , $mx["store_id"], $mx["cache_piaoju_id"], $mx["mizhong"], $mx["jianzhong"]);
                        (new \app\admin\model\Inv())->updateInv($mx["id"], 2, null, $mx["customerId"], $mx["yw_time"], $mx["changdu"], $mx["kuandu"], $mx["houdu"]
                            , $mx["guige_id"], $mx["jijiafangshi_id"], $mx["piaoju_id"], $mx["pinming_id"], $mx["zhongliang"], $mx["price"], $mx["sum_price"], $mx["sum_shui_price"], $mx["shui_price"]);
                    }
                }
            }
            if (!empty($addList)) {
                $trumpet = CgPurchaseMx::where('purchase_id', $data['id'])->max('trumpet');
                foreach ($addList as $mjo) {
                    $trumpet++;
                    $mjo['trumpet'] = $trumpet;
                    $mjo["purchase_id"] = $purchase_id;
                    $mx = new CgPurchaseMx();
                    $mx->allowField(true)->data($mjo)->save();
                    if ($data["ruku_fangshi"] == 1) {
                        (new KcRk())->insertRkMxMd($rk, $mx["data_id"], 4, $mx["yw_time"], $mx["system_number"], null, $mx["customer_id"], $mx["pinming_id"], $mx["guige_id"], $mx["caizhi_id"], $mx["chandi_id"]
                            , $mx["jijiafangshi_id"], $mx["store_id"], $mx["pihao"], $mx["huohao"], null, $mx["beizhu"], $data["piaoju_id"], $mx["houdu"] ?? 0, $mx["kuandu"] ?? 0, $mx["changdu"] ?? 0, $mx["zhijian"], $mx["lingzhi"] ?? 0, $mx["jianshu"] ?? 0,
                            $mx["counts"] ?? 0, $mx["zhongliang"] ?? 0, $mx["price"], $mx["sumPrice"], $mx["shuiPrice"], $mx["sumShuiPrice"], $mx["shuie"], $mx["mizhong"], $mx["jianzhong"], $this->getAccountId(), $this->getCompanyId());
                    } else {
                        (new KcRkTz())->insertRukuTz($mx["id"], 4, $mx["pinming_id"], $mx["guige_id"], $mx["caizhi_id"], $mx["chandi_id"], $mx["jijiafangshi_id"], $mx["houdu"], $mx["changdu"], $mx["kuandu"],
                            $mx["counts"], $mx["jianshu"], $mx["lingzhi"], $mx["zhijian"], $mx["zhongliang"], $mx["shui_price"], $mx["sumprice"], $mx["sum_shui_price"], $mx["shuie"], $mx["price"], $mx["huohao"],
                            $mx["pihao"], $mx["beizhu"], $mx["chehao"], $mx["cache_ywtime"], null, $mx["cache_data_pnumber"], $mx["cacheCustomer_id"], $mx["store_id"], $this->getAccountId(),
                            $mx["mizhong"], $mx["jianzhong"], $this->getCompanyId());
                    }
                    (new \app\admin\model\Inv())->insertInv($mx["id"], 2, 2, $mx["chagndu"], $mx["kuandu"], $mx["houdu"], $mx["guige_id"], $mx["jijiafangshi_id"], $mx["piaoju_id"], $mx["pinming_id"],
                        $mx["system_number"], $mx["customer_id"], $mx["yw_time"], $mx["price"], $mx["shui_price"], $mx["sum_price"], $mx["sum_shui_price"], $mx["zhongliang"], $this->getCompanyId());
                }

            }

            (new CapitalFy())->fymxSave($data['other'], $data['deleteOtherIds'], $cg['id'], $cg['yw_time'], 1, $cg['group_id'] ?? '', $cg['employer'] ?? '', null, $this->getAccountId(), $this->getCompanyId());
            Db::commit();
            return returnSuc();
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

}