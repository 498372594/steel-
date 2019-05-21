<?php

namespace app\admin\controller;

use app\admin\model\{Bank,
    InitBank,
    InitBankMx,
    InitKc,
    InitYsfk,
    InitYsfkMx,
    InitYskp,
    InitYskpMx,
    Instoragelist,
    KcRk,
    KcSpot,
    Purchasedetails};
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\Model;
use think\response\Json;

class Initinput extends Right
{
    /**
     * @return Json
     * @throws Exception
     * @throws \Exception
     */
    public function instorageinit()
    {
        if (request()->isPost()) {
//            $ids = request()->param("id");
            $count = Instoragelist::whereTime('create_time', 'today')->count();
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
            model("instoragelist")->save($data);
            $purchasedetails = request()->post("purchasedetails");
            $instorage_id = model("instoragelist")->id;
            foreach ($purchasedetails as $key => $value) {
                $purchasedetails["$key"]["instorage_id"] = $instorage_id;

                $count = Purchasedetails::whereTime('create_time', 'today')->count();
                $purchasedetails["$key"]["zyh"] = "ZYH" . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            }
            $model = new Purchasedetails();
            $res = $model->allowField(true)->saveAll($purchasedetails);
//            $res =model("purchasedetails")->where("id","in","ids")->update(array("is_finished"=>2,"instorage_id"=>$instorage_id));
            return returnRes($res, '失败');
        }
        return returnFail('请求方式错误');
    }

    /**
     * 银行账户余额初始录入列表
     * @return Json
     * @throws DbException
     */
    public function initbank()
    {
        $params = request()->param();
        $list = InitBank::where('companyid', $this->getCompanyId());
        $list = $this->getinitsearch($params, $list);
        $list = $list->paginate(10);
        return returnRes(true, '', $list);
    }

    /**
     * 条件搜索
     * @param $params
     * @param Model $list
     * @return Model
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

    /**
     * 银行账户余额初始录入明细列表
     * @param int $id
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function initbankdetail($id = 0)
    {
        $data = InitBank::with(['details'])
            ->where('companyid', $this->getCompanyId())
            ->where('id', $id)
            ->find();
        return returnRes(true, '', $data);
    }


    /**
     * 银行账户余额初始录入添加修改
     * @param array $data
     * @param bool $return
     * @return string|Json
     * @throws \Exception
     */
    public function initbankadd($data = [], $return = false)
    {
        if (request()->isPost()) {

            $companyId = $this->getCompanyId();
            $count = \app\admin\model\Salesorder::whereTime('create_time', 'today')->count();
            $data = request()->post();
            $data["status"] = 0;

            $data['create_operator_id'] = $this->getAccountId();
            $data['companyid'] = $companyId;
            $data['system_number'] = 'XJYHYEQC' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            if (!$return) {
                Db::startTrans();
            }
            try {
                if (empty($data["id"])) {
                    model("init_bank")->allowField(true)->isUpdate(false)->save($data);
                    $id = model("init_bank")->getLastInsID();
                } else {
                    model("init_bank")->allowField(true)->save($data, $data["id"]);
                    $id = $data["id"];
                }
                if (!empty($data["delete_id"])) {
                    model("InitBankMx")->where("id", "in", $data["delete_id"])->delete();
                }
                foreach ($data["details"] as $c => $v) {
                    $data['details'][$c]['companyid'] = $companyId;
                    $data['details'][$c]['bank_id'] = $id;
                    if (empty($v["id"])) {
                        model('InitBankMx')->allowField(true)->isUpdate(false)->data($data['details'][$c])->save();
                    } else {
                        model('InitBankMx')->allowField(true)->update($data['details'][$c]);
                    }
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

    /**
     * 应收账款余额初始录入
     * @return Json
     * @throws DbException
     */
    public function initysk()
    {
        $params = request()->param();
        $list = InitYsfk::where(array("companyid" => $this->getCompanyId(), "type" => 0));
        $list = $this->getinitsearch($params, $list);
        $list = $list->paginate(10);
        return returnRes(true, '', $list);
    }

    /**
     * 应付账款余额初始录入
     * @return Json
     * @throws DbException
     */
    public function inityfk()
    {
        $params = request()->param();
        $list = InitYsfk::where(array("companyid" => $this->getCompanyId(), "type" => 1));
        $list = $this->getinitsearch($params, $list);
        $list = $list->paginate(10);
        return returnRes(true, '', $list);
    }
//public function kcadd()
//{
//    if (!request()->isPost()) {
//        return returnFail('请求方式错误');
//    }
//
//    Db::startTrans();
//    try {
//        $data = request()->post();
//
//        $validate = new \app\admin\validate\InitKc();
//        if (!$validate->check($data)) {
//            throw new Exception($validate->getError());
//        }
//        $addMxList = [];
//        $updateMxList = [];
//        $ja = $data['details'];
//        $companyId = $this->getCompanyId();
//        if (!empty($ja)) {
//            foreach ($ja as $object) {
//                $object['companyid'] = $companyId;addkc
//                if (empty($object['id'])) {
//                    $addMxList[] = $object;
//                } else {
//                    $updateMxList[] = $object;
//                }
//            }
//        }
//        if (!empty($ja)) {
//            foreach ($ja as $object) {
//                if (empty($object['zhongliang'])) {
//                    throw new Exception("重量不能为空");
//                }
//
//                if (empty($object['id'])) {
//                    $addMdList[] = $object;
//                } else {
//                    $updateMdList[] = $object;
//                }
//            }
//        }
//        if (empty($data["id"])) {
//
//            $count = KcRk::whereTime('create_time', 'today')
//                ->where('companyid', $companyId)
//                ->count();
//            $data['companyid'] = $companyId;
//            $data['yw_time'] = date("Y-m-d H:s:i",time());
//            $data['system_number'] = 'KCQCYE' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
//            $data['create_operator_id'] = $this->getAccountId();
////            $data['ruku_fangshi'] = 2;
//            $data['ruku_type'] = 8;
//            $ck = new InitKc();
//            $ck->allowField(true)->data($data)->save();
//            $rk=new KcRk();
//            $rk->insertRuku($ck["id"],8,$ck["yw_time"],$ck["group_id"],$data["system_number"],$data["sale_operator_id"],$this->getAccountId(),$companyId);
//        } else {
//            throw new Exception('入库单禁止修改');
////                  rk = (TbKcRk)getDao() . selectByPrimaryKey(id);
////            if (rk == null) {
////                throw new Exception("对象不存在");
////            }
////             if (!rk . getUserId() . equals(rk . getUserId())) {
////                 throw new Exception("对象不存在");
////             }
////             if ("1" . equals(rk . getStatus())) {
////
////                 throw new Exception("该单据已经作废");
////             }
////            if (rk . getDataId() != null) {
////                throw new Exception("当前单据是只读单据,请到关联单据修改");
////            }
////            rk . setBeizhu(beizhu);
////             rk . setCustomerId(gysId);
////                rk . setGroupId(group);
////              rk . setSaleOperatorId(saleOperator);
////                 rk . setUpdateOperatorId(su . getId());
////                rk . setYwTime(DateUtil . parseDate(ywTime, "yyyy-MM-dd HH:mm:ss"));
////               getDao() . updateByPrimaryKeySelective(rk);
//        }
//        if (!empty($data['delete_mx_ids'])) {
//            throw new Exception('入库单禁止修改');
//        }
////            for (TbKcRkMx_Ex mx : deleteList)
////     {
////         TbKcRkMx mx1 = (TbKcRkMx)this.mxDao.selectByPrimaryKey(mx.getId());
////       mx1.setId(mx.getId());
////       mx1.setIsDelete("1");
////       this.mxDao.updateByPrimaryKeySelective(mx1);
////
////       Example e = new Example(TbKcRkMd.class);
////       e.selectProperties(new String[] { "id", "counts", "zhongliang", "kcRkTzId" });
////       e.createCriteria().andCondition("ruku_mx_id=", mx.getId());
////       List<TbKcRkMd> mdList = this.mdDao.selectByExample(e);
////       TbKcRkMd md1 = (TbKcRkMd)mdList.get(0);
////       md1.setIsDelete("1");
////       this.mdDao.updateByPrimaryKeySelective(md1);
////
////       this.spotDao.deleteSpotByRkMd(md1.getId());
////
////       this.rkTzDaoImpl.addTzById(md1.getKcRkTzId(), md1.getCounts(), md1.getZhongliang(), zt);
////     }
//        if (!empty($addMxList)) {
//            $addNumberCount = empty($data['id']) ? 0 : KcRkMx::where('kc_rk_id', $rk['id'])->max('system_number');
//            foreach ($addMxList as $mjo) {
//                if (!empty($mjo["rktz_id"])) {
//                    $tz = KcRkTz::get($mjo['rktz_id']);
//                    if (!empty($tz)) {
//                        $addNumberCount++;
//                        $mjo['kc_rk_id'] = $rk['id'];
//                        $mjo['kc_rk_tz_id'] = $tz['id'];
//                        $mjo['ruku_fangshi'] = 2;
//                        $mjo['cache_yw_time'] = $tz['cache_ywtime'];
//                        $mjo['cache_data_pnumber'] = $tz['cache_data_pnumber'];
//                        $mjo['cache_data_number'] = $tz['cache_data_number'];
//                        $mjo['cache_customer'] = $tz['cache_customer_id'];
//                        $mjo['data_id'] = $tz['data_id'];
//                        $mjo['pinming_id'] = $tz['pinming_id'];
//                        $mjo['guige_id'] = $tz['guige_id'];
//                        $mjo['caizhi_id'] = $tz['caizhi_id'];
//                        $mjo['chandi_id'] = $tz['chandi_id'];
//                        $mjo['jijiafangshi_id'] = $tz['jijiafangshi_id'];
//                        $mjo['store_id'] = $tz['store_id'];
//                        $mjo['cache_create_operator'] = $tz['cache_create_operator'];
//                        $mjo['changdu'] = $tz['changdu'];
//                        $mjo['houdu'] = $tz['houdu'];
//                        $mjo['kuandu'] = $tz['kuandu'];
//                        $mjo['lingzhi'] = $tz['lingzhi'];
//                        $mjo['jianshu'] = $tz['jianshu'];
//                        $mjo['counts'] = $tz['counts'];
//                        $mjo['zhongliang'] = $tz['zhongliang'];
//
//                    }
//                }
//            }
//        }
//    } catch (Exception $e) {
//        Db::rollback();
//        return returnFail($e->getMessage());
//    }
//}
    /**
     * 库存初始化录入
     * @param array $data
     * @param bool $return
     * @return string|Json
     * @throws \Exception
     */
    public function addkc($return = false)
    {
        if (request()->isPost()) {
            $companyId = $this->getCompanyId();
            $count = InitKc::whereTime('create_time', 'today')->count();
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
                $count1 = KcSpot::whereTime('create_time', 'today')->count();
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
                        'store_id' => $v['store_id'] ?? '',
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
                        'customer_id' => null,
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

    /**
     * 库存初始化列表
     * @return Json
     * @return Json
     */
    public function kclist()
    {
        $params = request()->param();
        $list = InitKc::with(['customData', 'jsfsData', 'pjlxData', 'storageData', 'createoperatordata', 'saleoperatordata', 'udpateoperatordata', 'checkoperatordata'])
            ->where('companyid', $this->getCompanyId());

        $list = $this->getsearchcondition($params, $list);
        $list = $list->paginate(10);
        return returnRes(true, '', $list);
    }

    /**
     * 库存初始化明细
     * @param int $id
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function kcmx($id = 0)
    {
        $data = InitKc::with(['details' => ['specification', 'jsfs', 'storage', 'chandiData', 'caizhiData', 'pinmingData'], 'createoperatordata', 'saleoperatordata', 'udpateoperatordata', 'checkoperatordata',
            'customData', 'jsfsData', 'pjlxData', 'storageData'])
            ->where('companyid', $this->getCompanyId())
            ->where('id', $id)
            ->find();
        return returnRes(true, '', $data);
    }

    /**
     * @param int $type 0为付款，1为收款
     * @return Json
     */
    public function ysfk($type = 0)
    {
        $params = request()->param();
        $list = InitYsfk::with(['createoperatordata', 'saleoperatordata', 'udpateoperatordata', 'checkoperatordata'])
            ->where('companyid', $this->getCompanyId())
            ->where('type', $type);
        $list = $this->getsearchcondition($params, $list)->paginate(10);
        return returnRes(true, '', $list);
    }

    /**
     * @param int $id
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function ysfkmx($id = 0)
    {
        $data = InitYsfk::with(['details', 'createoperatordata', 'saleoperatordata', 'udpateoperatordata', 'checkoperatordata'
        ])
            ->where('companyid', $this->getCompanyId())
            ->where('id', $id)
            ->find();
        return returnSuc($data);
    }

    /**
     * @param array $data
     * @param bool $return
     * @return bool|string|Json
     * @throws Exception
     */
    public function addysfk($data = [], $return = false)
    {
        if (request()->isPost()) {
            $companyId = $this->getCompanyId();
            $data = request()->post();
            $count = InitYsfk::whereTime('create_time', 'today')->where("type", $data["type"])->count();
            $data["status"] = "0";
            $data['create_operator_id'] = $this->getAccountId();
            $data['companyid'] = $companyId;
            if ($data["type"] == "0") {
                $data['system_number'] = 'YFZKYEQC' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            }
            if ($data["type"] == "1") {
                $data['system_number'] = 'YSZKYEQC' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            }
            if (!$return) {
                Db::startTrans();
            }
            try {

                if (empty($data["id"])) {
                    model("InitYsfk")->allowField(true)->isUpdate(false)->save($data);
                    $id = model("InitYsfk")->getLastInsID();
                } else {
                    model("InitYsfk")->allowField(true)->save($data, $data["id"]);
                    $id = $data["id"];
                }
                if (!empty($data["delete_id"])) {
                    model("InitYsfkMx")->where("id", "in", $data["delete_id"])->delete();
                }
                foreach ($data["details"] as $c => $v) {
                    $data['details'][$c]['companyid'] = $companyId;
                    $data['details'][$c]['ysfk_id'] = $id;
                    if (empty($v["id"])) {
                        model('InitYsfkMx')->allowField(true)->isUpdate(false)->data($data['details'][$c])->save();
                    } else {
                        model('InitYsfkMx')->allowField(true)->update($data['details'][$c]);
                    }

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

    /**
     * @param int $type 0为付款，1为收款
     * @return Json
     */
    public function yskp($type = 0)
    {
        $params = request()->param();
        $list = InitYskp::with(['createoperatordata', 'saleoperatordata', 'udpateoperatordata', 'checkoperatordata'])
            ->where('companyid', $this->getCompanyId())
            ->where('type', $type);
        $list = $this->getsearchcondition($params, $list)->paginate(10);
        return returnRes(true, '', $list);
    }

    /**
     * @param int $id
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function yskpmx($id = 0)
    {
        $data = InitYskp::with(['details' => ['customData', 'pjlxData'], 'createoperatordata', 'saleoperatordata', 'udpateoperatordata', 'checkoperatordata'
        ])
            ->where('companyid', $this->getCompanyId())
            ->where('id', $id)
            ->find();
        return returnSuc($data);
    }

    /**
     * @param array $data
     * @param bool $return
     * @return bool|string|Json
     * @throws Exception
     * @throws \Exception
     */
    public function yskpadd($data = [], $return = false)
    {
        if (request()->isPost()) {
            $companyId = $this->getCompanyId();
            $data = request()->post();
            $count = InitYskp::whereTime('create_time', 'today')->where("type", $data["type"])->count();
            $data["status"] = 0;
            $data['create_operator_id'] = $this->getAccountId();
            $data['companyid'] = $companyId;
            if ($data["type"] == 0) {
                $data['system_number'] = 'YSJXFPYEQC' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            }
            if ($data["type"] == 1) {
                $data['system_number'] = 'YKXXFPYEQC' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            }

            if (!$return) {
                Db::startTrans();
            }
            try {
                if (empty($data["id"])) {
                    model("InitYskp")->allowField(true)->isUpdate(false)->save($data);
                    $id = model("InitYskp")->getLastInsID();
                } else {
                    model("InitYskp")->allowField(true)->save($data, $data["id"]);
                    $id = $data["id"];
                }
                if (!empty($data["delete_id"])) {
                    model("InitYskpMx")->where("id", "in", $data["delete_id"])->delete();
                }

                foreach ($data["details"] as $c => $v) {
                    $data['details'][$c]['companyid'] = $companyId;
                    $data['details'][$c]['yskp_id'] = $id;
                }
                model('InitYskpMx')->saveAll($data['details']);
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

    /**
     * @param int $id
     * @return Json
     */
    public function kcCancel($id = 0)
    {
        if (!request()->isPost()) {
            return returnFail('请求方式错误');
        }
        Db::startTrans();
        try {
            $kc = InitKc::get($id);
            if (empty($kc)) {
                throw new Exception("对象不存在");
            }
            if ($kc["status"] == 1) {
                throw new Exception("该单据已经作废");
            }
            $kc->status = 1;
            $kc->save();
            (new KcRk())->cancelRuku($kc->id, 8);
            Db::commit();
            return returnSuc();
        } catch (\Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**
     * @param int $id
     * @return Json
     */
    public function yskcancel($id = 0)
    {
        if (!request()->isPost()) {
            return returnFail('请求方式错误');
        }
        Db::startTrans();
        try {
            $ysfk = InitYsfk::get($id);
            if (empty($ysfk)) {
                throw new Exception("对象不存在");
            }
            if ($ysfk["status"] == 1) {
                throw new Exception("该单据已经作废");
            }
            $ysfk->status = 1;
            $ysfk->save();
            $list = InitYsfkMx::where("ysfk_id", $ysfk["id"])->select();

            foreach ($list as $mx) {
                (new \app\admin\model\CapitalHk())->deleteHk($mx["id"], 26);
            }
            Db::commit();
            return returnSuc();
        } catch (\Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**
     * @param int $id
     * @return Json
     */
    public function yfkcancel($id = 0)
    {
        if (!request()->isPost()) {
            return returnFail('请求方式错误');
        }
        Db::startTrans();
        try {
            $ysfk = InitYsfk::get($id);
            if (empty($ysfk)) {
                throw new Exception("对象不存在");
            }
            if ($ysfk["status"] == 1) {
                throw new Exception("该单据已经作废");
            }
            $ysfk->status = 1;
            $ysfk->save();
            $list = InitYsfkMx::where("ysfk_id", $ysfk["id"])->select();

            foreach ($list as $mx) {
                (new \app\admin\model\CapitalHk())->deleteHk($mx["id"], 27);
            }
            Db::commit();
            return returnSuc();
        } catch (\Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    public function yspcancel($id = 0)
    {
        if (!request()->isPost()) {
            return returnFail('请求方式错误');
        }
        Db::startTrans();
        try {
            $ysfp = InitYskp::get($id);
            if (empty($yskp)) {
                throw new Exception("对象不存在");
            }
            if ($yskp["status"] == 1) {
                throw new Exception("该单据已经作废");
            }
            $yskp->status = 1;
            $yskp->save();
            $list = InitYskpMx::where("ysfp_id", $yskp["id"])->select();

            foreach ($list as $mx) {
                (new \app\admin\model\Inv())->deleteInv($mx["id"], 1);
            }
            Db::commit();
            return returnSuc();
        } catch (\Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    public function ykpcancel($id = 0)
    {
        if (!request()->isPost()) {
            return returnFail('请求方式错误');
        }
        Db::startTrans();
        try {
            $yskp = InitYskp::get($id);
            if (empty($ysfp)) {
                throw new Exception("对象不存在");
            }
            if ($ysfp["status"] == 1) {
                throw new Exception("该单据已经作废");
            }
            $yskp->status = 1;
            $yskp->save();

            $list = \app\admin\model\InitYkpMx::where("yskp_id", $yskp["id"])->select();
            $list = InitYskpMx::where("yskp_id", $yskp["id"])->select();


            foreach ($list as $mx) {
                (new \app\admin\model\Inv())->deleteInv($mx["id"], 4);
            }
            Db::commit();
            return returnSuc();
        } catch (\Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    public function bankcancel($id = 0)
    {
        if (!request()->isPost()) {
            return returnFail('请求方式错误');
        }
        Db::startTrans();
        try {
            $bank = InitBank::get($id);
//            $bank = \app\admin\model\CapitalCqk::get($id);
            if (empty($bank)) {
                throw new Exception("对象不存在");
            }
            if ($bank["status"] == 1) {
                throw new Exception("该单据已经作废");
            }
            $bank->status = 1;
            $bank->save();
            $list = InitBankMx::where("bank_id", $bank["id"])->select();

            foreach ($list as $mx) {
                (new Bank())->deleteBank($mx["id"], 0, 1);
            }
            Db::commit();
            return returnSuc();
        } catch (\Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }
}