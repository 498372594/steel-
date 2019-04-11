<?php

namespace app\admin\controller;

use app\admin\library\tree\Tree;
use app\admin\model\{CgPurchase, KcRk, KcSpot};
use app\admin\validate\{CgPurchaseMx, FeiyongDetails};
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    Request,
    response\Json,
    Session};

class Purchase extends Right
{
    /**
     * 采购单添加
     * @param Request $request
     * @param int $moshi_type
     * @param array $data
     * @param bool $return
     * @param array $spotIds
     * @return array|bool|string|Json
     * @throws \think\Exception
     * @throws Exception
     */
    public function purchaseadd(Request $request, $moshi_type = 4, $data = [], $return = false, &$spotIds = [])
    {
        if ($request->isPost()) {
            $count = CgPurchase::whereTime('create_time', 'today')->count();
            $companyId = $this->getCompanyId();

            //数据处理
            if (empty($data)) {
                $data = $request->post();
            }
            $data['create_operator'] = $this->getAccount()['name'];
            $data['create_operate_id'] = $this->getAccountId();
            $data['companyid'] = $companyId;
            $data['system_number'] = 'CGD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            $data['moshi_type'] = $moshi_type;

            // 数据验证
            $validate = new \app\admin\validate\CgPurchase();
            if (!$validate->check($data)) {
                if ($return) {
                    return $validate->getError();
                } else {
                    return returnFail($validate->getError());
                }
            }

            if (!$return) {
                Db::startTrans();
            }
            try {
                $model = new CgPurchase();
                //添加采购单列表
                $model->allowField(true)->data($data)->save();

                //处理明细
                $id = $model->getLastInsID();
                $num = 1;
                $detailsValidate = new CgPurchaseMx();
                foreach ($data['details'] as $c => $v) {
                    $data['details'][$c]['companyid'] = $companyId;
                    $data['details'][$c]['purchase_id'] = $id;
                    if (!$detailsValidate->check($data['details'][$c])) {
                        throw new Exception('请检查第' . $num . '行' . $detailsValidate->getError());
                    }
                    $num++;
                }
                //t添加采购单明细
                model('CgPurchaseMx')->allowField(true)->saveAll($data['details']);

                $num = 1;
                if (!empty($data['other'])) {
                    $otherValidate = new FeiyongDetails();
                    //处理其他费用
                    foreach ($data['other'] as $c => $v) {
                        $data['other'][$c]['group_id'] = $data['group_id'] ?? '';
                        $data['other'][$c]['sale_operator_id'] = $data['sale_operator_id'] ?? '';

                        if (!$otherValidate->check($data['other'][$c])) {
                            throw new Exception('请检查第' . $num . '行' . $otherValidate->getError());
                        }
                        $num++;
                    }
                    $res = (new Feiyong())->addAll($data['other'], 2, $id, $data['yw_time'], false);
                    if ($res !== true) {
                        throw new Exception($res);
                    }
                }
                if ($data['ruku_fangshi'] == 2) {
                    //手动入库，添加入库通知单
                    $notify = [];
                    foreach ($data['details'] as $c => $v) {
                        $notify[] = [
                            'companyid' => $companyId,
                            'ruku_type' => 4,
                            'status' => 0,
                            'data_id' => $id,
                            'guige_id' => $v['guige_id'],
                            'caizhi_id' => $v['caizhi_id'] ?? '',
                            'chandi_id' => $v['chandi_id'] ?? '',
                            'cache_piaoju_id' => $v['piaoju_id'] ?? '',
                            'pinming_id' => $v['pinming_id'] ?? '',
                            'jijiafangshi_id' => $v['jijiafangshi_id'],
                            'houdu' => $v['houdu'] ?? '',
                            'kuandu' => $v['kuandu'] ?? '',
                            'changdu' => $v['changdu'] ?? '',
                            'lingzhi' => $v['lingzhi'] ?? '',
                            'fy_sz' => $v['fy_sz'] ?? '',
                            'jianshu' => $v['jianshu'] ?? '',
                            'zhijian' => $v['zhijian'] ?? '',
                            'counts' => $v['counts'] ?? '',
                            'zhongliang' => $v['zhongliang'] ?? '',
                            'price' => $v['price'] ?? '',
                            'sumprice' => $v['sumprice'] ?? '',
                            'shuie' => $v['shuie'] ?? '',
//                            'ruku_lingzhi' => $v['lingzhi'] ?? '',
//                            'ruku_jianshu' => $v['jianshu'] ?? '',
//                            'ruku_zhongliang' => $v['zhongliang'] ?? '',
//                            'ruku_shuliang' => $v['counts'] ?? '',
                            'shui_price' => $v['shui_price'] ?? '',
                            'sum_shui_price' => $v['sum_shui_price'] ?? '',
                            'beizhu' => $v['beizhu'] ?? '',
                            'chehao' => $v['chehao'] ?? '',
                            'pihao' => $v['pihao'] ?? '',
                            'huohao' => $v['huohao'] ?? '',
                            'cache_ywtime' => $data['yw_time'],
                            'cache_data_pnumber' => $data['system_number'],
                            'cache_customer_id' => $data['customer_id'],
                            'store_id' => $v['store_id'],
                            'cache_create_operator' => $data['create_operate_id'],
                            'mizhong' => $v['mizhong'] ?? '',
                            'jianzhong' => $v['jianzhong'] ?? '',
                            'lisuan_zhongliang' => ($v["counts"] * $v["changdu"] * $v['mizhong'] / 1000),
                            'guobang_zhongliang' => $v['zhongliang'] ?? '',
                        ];
                    }
                    //添加入库通知
                    model("KcRkTz")->allowField(true)->saveAll($notify);
                } elseif ($data['ruku_fangshi'] == 1) {
                    //自动入库
                    //采购单id
                    $data['data_id'] = $id;
                    //生成入库单
                    $count2 = KcRk::whereTime('create_time', 'today')->count();
                    $data["system_number"] = "RKD" . date('Ymd') . str_pad($count2 + 1, 3, 0, STR_PAD_LEFT);
                    $data["beizhu"] = $data['system_number'];
                    model("KcRk")->allowField(true)->data($data)->save();
                    $rkid = model("KcRk")->getLastInsID();
                    //处理数据
                    foreach ($data['details'] as $c => $v) {
                        $data['details'][$c]['companyid'] = $companyId;
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
                        if (!$detailsValidate->check($data['details'][$c])) {
                            throw new Exception('请检查第' . $num . '行' . $detailsValidate->getError());
                        }
                        $num++;
                    }
                    //入库明细
                    model('KcRkMx')->allowField(true)->saveAll($data['details']);
                    $count1 = KcSpot::whereTime('create_time', 'today')->count();
                    //入库库存
                    foreach ($data['details'] as $c => $v) {
                        $spot = [
                            'companyid' => $companyId,
                            'ruku_type' => 4,
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
     * 获取大类列表
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function getclassnamelist()
    {
        $list = db("classname")->field("pid,id,classname")->where("companyid", $this->getCompanyId())->select();
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
        if (!empty($params['yw_type'])) {
            $list->where('yw_type', $params['yw_type']);
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
            'details' => ['specification', 'jsfs', 'storage', 'pinmingData', 'caizhiData', 'chandiData'],
            'other' => ['mingxi' => ['szmcData', 'pjlxData', 'custom']],
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
        $class = request()->param("paymentclass");
        $paymentclass = model("paymenttype")->field("id,name")->where("class", $class)->select();
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
}