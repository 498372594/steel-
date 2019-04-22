<?php

namespace app\admin\controller;

use app\admin\model\{KcQtrk, KcQtrkMx, KcRkMd, KcRkMx, KcRkTz, KcSpot, ViewQingku};
use app\admin\model\KcRk;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    db\Query,
    exception\DbException,
    Request,
    response\Json};
use think\Exception;
use think\Session;

class Rk extends Right
{
    /**入库单列表
     * @return Json
     * @throws DbException
     */
    public function getrk()
    {
        $params = request()->param();
        $list = KcRk::with([
            'custom',
        ])->where('companyid', $this->getCompanyId());
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('yw_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($instorageorderparams['status'])) {
            $list->where('status', $params['status']);
        }
        if (!empty($params['system_number'])) {
            $list->where('system_number', 'like', '%' . $params['system_number'] . '%');
        }
        if (!empty($params['beizhu'])) {
            $list->where('beizhu', 'like', '%' . $params['beizhu'] . '%');
        }
        $list = $list->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**入库单明细
     * @return Json
     * @throws DbException
     */
    public function getrkmx($id = 0)
    {
        $data = KcRk::with([
            'custom',
            'details' => ['specification', 'jsfs', 'storage', 'pinmingData', 'caizhiData', 'chandiData', 'customData'],
        ])->where('companyid', $this->getCompanyId())
            ->where('id', $id)
            ->find();
        if (empty($data)) {
            return returnFail('数据不存在');
        } else {
            return returnRes(true, '', $data);
        }
    }

    /**获取待入库明细
     * @return Json
     * @throws DbException
     */
    public function getrktz()
    {
        $params = request()->param();

        $list = KcRkTz::with(['storage', 'pinmingData', 'caizhiData', 'chandiData'])->where('companyid', $this->getCompanyId());
        $list->where("jianshu", ">", 0)->where("lingzhi", ">", 0)->where("counts", ">", 0);

        if (!empty($params['ids'])) {
            $list->where("id", "in", $params['ids']);
        }
        if (!empty($params['create_start'])) {
            $list->where('create_time', '>=', $params['create_start']);
        }
        if (!empty($params['create_end'])) {
            $list->where('yw_time', '<=', date('Y-m-d', strtotime($params['create_end'] . ' +1 day')));
        }
        if (!empty($params['store_id'])) {
            $list->where('store_id', $params['store_id']);
        }
        if (!empty($params['customer_id'])) {
            $list->where('customer_id', $params['customer_id']);
        }
        if (!empty($params['pinming_id'])) {
            $list->where('pinming_id', $params['pinming_id']);
        }
        if (!empty($params['system_number'])) {
            $list->where('system_number', 'like', '%' . $params['system_number'] . '%');
        }
        if (!empty($params['cache_data_pnumber'])) {
            $list->where('cache_data_pnumber', 'like', '%' . $params['cache_data_pnumber'] . '%');
        }
        if (!empty($params['guige_id'])) {
            $list->where('guige_id', $params['guige_id']);
        }
        if (!empty($params['cache_customer_id'])) {
            $list->where('cache_customer_id', $params['cache_customer_id']);
        }
        if (!empty($params['is_done'])) {
            $list->where('is_done', $params['is_done']);
        }
        if (!empty($params['beizhu'])) {
            $list->where('remark', $params['remark']);
        }
        if (!empty($params['zhongliang'])) {
            $list->where("zhongliang", ">", 0);
        }
        $list = $list->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**入库
     * @param Request $request
     * @param int $moshi_type
     * @param array $data
     * @param bool $return
     * @return string|Json
     * @throws \Exception
     */
    public function add()
    {
        if (!request()->isPost()) {
            return returnFail('请求方式错误');
        }

        Db::startTrans();
        try {
            $data = request()->post();

            $validate = new \app\admin\validate\KcRk();
            if (!$validate->check($data)) {
                throw new Exception($validate->getError());
            }
            $addMxList = [];
            $updateMxList = [];
            $ja = $data['ckmx'];
            $companyId = $this->getCompanyId();
            if (!empty($ja)) {
                foreach ($ja as $object) {
                    $object['companyid'] = $companyId;
                    if (empty($object['id'])) {
                        $addMxList[] = $object;
                    } else {
                        $updateMxList[] = $object;
                    }
                }
            }
            if (!empty($ja)) {
                foreach ($ja as $object) {
                    if (empty($object['zhongliang'])) {
                        throw new Exception("重量不能为空");
                    }

                    if (empty($object['id'])) {
                        $addMdList[] = $object;
                    } else {
                        $updateMdList[] = $object;
                    }
                }
            }
            if (empty($data["id"])) {
                $tz = model("kc_rk_tz")->where("id", $data["rktz_id"])->find();
                if ($tz["status"] == 1) {
                    throw new Exception("入库源单不存在，请核实后在操作！");
                }
                $count = KcRk::whereTime('create_time', 'today')
                    ->where('companyid', $companyId)
                    ->count();
                $data['companyid'] = $companyId;
                $data['system_number'] = 'CKD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
                $data['create_operator_id'] = $this->getAccountId();
                $data['ruku_fangshi'] = 2;
                $data['ruku_type'] = $tz["ruku_type"];
                $rk = new KcRk();
                $rk->allowField(true)->data($data)->save();
            } else {
                throw new Exception('入库单禁止修改');
//                  rk = (TbKcRk)getDao() . selectByPrimaryKey(id);
//            if (rk == null) {
//                throw new Exception("对象不存在");
//            }
//             if (!rk . getUserId() . equals(rk . getUserId())) {
//                 throw new Exception("对象不存在");
//             }
//             if ("1" . equals(rk . getStatus())) {
//
//                 throw new Exception("该单据已经作废");
//             }
//            if (rk . getDataId() != null) {
//                throw new Exception("当前单据是只读单据,请到关联单据修改");
//            }
//            rk . setBeizhu(beizhu);
//             rk . setCustomerId(gysId);
//                rk . setGroupId(group);
//              rk . setSaleOperatorId(saleOperator);
//                 rk . setUpdateOperatorId(su . getId());
//                rk . setYwTime(DateUtil . parseDate(ywTime, "yyyy-MM-dd HH:mm:ss"));
//               getDao() . updateByPrimaryKeySelective(rk);
            }
            if (!empty($data['delete_mx_ids'])) {
                throw new Exception('入库单禁止修改');
            }
//            for (TbKcRkMx_Ex mx : deleteList)
//     {
//         TbKcRkMx mx1 = (TbKcRkMx)this.mxDao.selectByPrimaryKey(mx.getId());
//       mx1.setId(mx.getId());
//       mx1.setIsDelete("1");
//       this.mxDao.updateByPrimaryKeySelective(mx1);
//
//       Example e = new Example(TbKcRkMd.class);
//       e.selectProperties(new String[] { "id", "counts", "zhongliang", "kcRkTzId" });
//       e.createCriteria().andCondition("ruku_mx_id=", mx.getId());
//       List<TbKcRkMd> mdList = this.mdDao.selectByExample(e);
//       TbKcRkMd md1 = (TbKcRkMd)mdList.get(0);
//       md1.setIsDelete("1");
//       this.mdDao.updateByPrimaryKeySelective(md1);
//
//       this.spotDao.deleteSpotByRkMd(md1.getId());
//
//       this.rkTzDaoImpl.addTzById(md1.getKcRkTzId(), md1.getCounts(), md1.getZhongliang(), zt);
//     }
            if (!empty($addMxList)) {
                $addNumberCount = empty($data['id']) ? 0 : KcRkMx::where('kc_rk_id', $rk['id'])->max('system_number');
                foreach ($addMxList as $mjo) {
                    if (!empty($mjo["rktz_id"])) {
                        $tz = KcRkTz::get($mjo['rktz_id']);
                        if (!empty($tz)) {
                            $addNumberCount++;
                            $mjo['kc_rk_id'] = $rk['id'];
                            $mjo['kc_rk_tz_id'] = $tz['id'];
                            $mjo['ruku_fangshi'] = 2;
                            $mjo['cache_yw_time'] = $tz['cache_ywtime'];
                            $mjo['cache_data_pnumber'] = $tz['cache_data_pnumber'];
                            $mjo['cache_data_number'] = $tz['cache_data_number'];
                            $mjo['cache_customer'] = $tz['cache_customer_id'];
                            $mjo['data_id'] = $tz['data_id'];
                            $mjo['pinming_id'] = $tz['pinming_id'];
                            $mjo['guige_id'] = $tz['guige_id'];
                            $mjo['caizhi_id'] = $tz['caizhi_id'];
                            $mjo['chandi_id'] = $tz['chandi_id'];
                            $mjo['jijiafangshi_id'] = $tz['jijiafangshi_id'];
                            $mjo['store_id'] = $tz['store_id'];
                            $mjo['cache_create_operator'] = $tz['cache_create_operator'];
                            $mjo['changdu'] = $tz['changdu'];
                            $mjo['houdu'] = $tz['houdu'];
                            $mjo['kuandu'] = $tz['kuandu'];
                            $mjo['lingzhi'] = $tz['lingzhi'];
                            $mjo['jianshu'] = $tz['jianshu'];
                            $mjo['counts'] = $tz['counts'];
                            $mjo['zhongliang'] = $tz['zhongliang'];

                        }
                    }
                }
            }
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    public function ruku(Request $request, $moshi_type = 4, $data = [], $return = false)
    {
        if ($request->isPost()) {
            $companyId = $this->getCompanyId();
            //数据处理
            if (empty($data)) {
                $data = $request->post();
            }
            if ($data["id"]) {
                throw new Exception('入库单禁止修改');
            }
            $data['create_operator'] = $this->getAccount()['name'];
            $data['create_operate_id'] = $this->getAccountId();
            $data['companyid'] = $companyId;
            $data['moshi_type'] = $moshi_type;
            if (!$return) {
                Db::startTrans();
            }
            try {
                //入库

                //生成入库单
                $count2 = KcRk::whereTime('create_time', 'today')->count();
                $data["system_number"] = "RKD" . date('Ymd') . str_pad($count2 + 1, 3, 0, STR_PAD_LEFT);
                $data["beizhu"] = $data['beizhu'];

                model("KcRk")->allowField(true)->data($data)->save();

                $rkid = model("KcRk")->getLastInsID();

                //处理数据
                $detailsValidate = new KcRkMx();
                $num = 1;
                $count1 = KcSpot::whereTime('create_time', 'today')->count();
                foreach ($data['details'] as $c => $v) {
                    $dat['details'][$c]['id'] = $v['id'];
                    $dat['details'][$c]['counts'] = $v['old_counts'] - $v["counts"];//剩下的总件数
                    $dat['details'][$c]['jianshu'] = intval(floor($dat['details'][$c]['counts'] / $v["zhijian"]));
                    $dat['details'][$c]['lingzhi'] = $dat['details'][$c]['counts'] % $v["zhijian"];
                    $dat['details'][$c]['zhongliang'] = $v['old_zhongliang'] - $v["zhongliang"];//剩下的总件数
                    $data['details'][$c]['companyid'] = $companyId;
                    $data['details'][$c]['kc_rk_id'] = $rkid;
                    $data['details'][$c]['resource_number'] = "KC" . date('Ymd') . str_pad($count1 + 1, 3, 0, STR_PAD_LEFT);
//                        $data['details'][$c]['data_id'] = $id;
                    $data['details'][$c]['cache_data_number'] = $v['cache_data_number'];
                    $data['details'][$c]['cache_customer_id'] = $v['cache_customer_id'] ?? '';
                    $data['details'][$c]['cache_ywtime'] = $v['cache_ywtime'] ?? '';
                    $data['details'][$c]['cache_piaoju_id'] = $v['cache_piaoju_id'] ?? '';
                    $data['details'][$c]['cache_create_operator'] = $v['cache_create_operator'];
                    $data['details'][$c]['ruku_lingzhi'] = $v['lingzhi'];
                    $data['details'][$c]['ruku_jianshu'] = $v['jianshu'];
                    $data['details'][$c]['ruku_shuliang'] = $v['counts'];
                    $data['details'][$c]['ruku_zhongliang'] = $v['zhongliang'];
                    unset($data['details'][$c]["id"]);
//                        if (!$detailsValidate->check($data['details'][$c])) {
//                            throw new Exception('请检查第' . $num . '行' . $detailsValidate->getError());
//                        }
                    $num++;
                }

                //修改通知记录数量
                model("KcRkTz")->saveAll($dat['details']);

                //入库明细

                model('KcRkMx')->allowField(true)->saveAll($data['details']);

                //入库库存
                $spot = [];
                foreach ($data['details'] as $c => $v) {
                    $spot[] = [
                        'companyid' => $companyId,
                        'ruku_type' => 4,
                        'ruku_fangshi' => 2,
                        'piaoju_id' => $v['cache_piaoju_id'],
                        'resource_number' => "KC" . date('Ymd') . str_pad($count1 + 1, 3, 0, STR_PAD_LEFT),
                        'guige_id' => $v['guige_id'],
                        'data_id' => $v['id'] ?? '',
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
                        'customer_id' => $v['cache_customer_id'],
                        'mizhong' => $v['mizhong'] ?? '',
                        'jianzhong' => $v['jianzhong'] ?? '',
                        'lisuan_zhongliang' => ($v["counts"] * $v["changdu"] * $v['mizhong'] / 1000),
                        'guobang_zhizhong' => ($v['zhongliang'] / $v["counts"] * $v["zhijian"]) ?? '',
                        'guobang_zhongliang' => $v["zhongliang"] ?? '',
                        'lisuan_zhizhong' => ($v["counts"] * $v["changdu"] * $v['mizhong'] / 1000 / $v["counts"] * $v["zhijian"]),
                        'guobang_jianzhong' => ($v['zhongliang'] / $v["counts"]) ?? '',
                        'lisuan_jianzhong' => ($v["counts"] * $v["changdu"] * $v['mizhong'] / 1000 / $v["counts"]),
                        'old_lisuan_zhongliang' => ($v["counts"] * $v["changdu"] * $v['mizhong'] / 1000),
                        'old_guobang_zhizhong' => ($v['zhongliang'] / $v["counts"] * $v["zhijian"]) ?? '',
                        'old_lisuan_zhizhong' => ($v["counts"] * $v["changdu"] * $v['mizhong'] / 1000 / $v["counts"] * $v["zhijian"]),
                        'old_guobangjianzhong' => ($v['zhongliang'] / $v["counts"]) ?? '',
                        'old_guobangzhongliang' => ($v['zhongliang']) ?? '',
                        'old_lisuan_jianzhong' => ($v["counts"] * $v["changdu"] * $v['mizhong'] / 1000 / $v["counts"]),
                        'status' => 0,
                        'guobang_price' => $v['guobang_price'] ?? '',
                        'guobang_shui_price' => $v['guobang_shui_price'] ?? '',
                        'zhi_price' => $v['zhi_price'] ?? '',
                        'zhi_shui_price' => $v['zhi_shui_price'] ?? '',
                        'lisuan_shui_price' => $v['lisuan_shui_price'] ?? '',
                        'lisuan_price' => $v['lisuan_price'] ?? '',
                    ];
                }

                model("KcSpot")->allowField(true)->saveAll($spot);
//                }

                if (!$return) {
                    Db::commit();
                    return returnRes(true, '', ['id' => $rkid]);
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

    /**清库列表
     * @return Json
     * @throws DbException
     */
    public function clearstoragelist()
    {
        $params = request()->param();
        $list = ViewQingku::where(array("companyid" => $this->getCompanyId()));
        $list = $this->getsearchcondition($params, $list);
        if (!empty($params['status'])) {
            $list->where('status', $params['status']);
        } else {
            $list->where('status', 1);
        }
        if (!empty($params['guobang_zhongliang'])) {
            $list->where('guobang_zhongliang', 0);
        }
        if (!empty($params['counts'])) {
            $list->where('counts', 0);
        }
        $list = $list->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**
     * 清库
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws \Exception
     */
    public function clearspot()
    {
        Db::startTrans();
        try {
            $ids = request()->param("ids");
            $type = request()->param("type");
            $ids = explode(",", $ids);
            foreach ($ids as $id) {
                $st = KcSpot::where("id", $id)->find();
                if (empty($st)) {
                    throw new \Exception("没有数据");
                } else {
                    if ($type == 1) {
                        if ($st["status"] == 1) {
                            throw new \Exception("该单据未清库，禁止反清库！");
                        }
                        $st["status"] = 1;
                    } elseif ($type == 2) {
                        if ($st["status"] == 2) {
                            throw new \Exception("该单据已清库，禁止再次清库！");
                        }
                        $st["status"] = 2;
                    } else {
                        throw new \Exception("非法参数");
                    }
                    $st->save();
                }
            }
            Db::commit();
            return returnSuc();
        } catch (\Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    public function qtrklist()
    {
        $params = request()->param();
        try {
            $list = $list = KcQtrk::with(['customData',])->where('companyid', Session::get('uinfo.companyid', 'admin'));
            if (!empty($params['system_number'])) {
                $list->where("system_number", $params['system_number']);
            }
            if (!empty($params['customer_id'])) {
                $list->where("customer_id", $params['customer_id']);
            }
            if (!empty($params['beizhu'])) {
                $list->where("beizhu", $params['beizhu']);
            }
            $list = $list->paginate(10);
            return returnRes(true, '', $list);
        } catch (Exception $e) {

        }

    }

    public function qtrkmx($id = 0)
    {
        $data = KcQtrk::with([
            'customData',
            'details' => ['specification', 'jsfs', 'storage', 'chandiData', 'customData', 'caizhiData', 'pinmingData'],
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

//    public function addqtrk($data = [], $return = false)
//    {
//        if (request()->isPost()) {
//            $companyId = $this->getCompanyId();
//            $count = \app\admin\model\KcQtrk::whereTime('create_time', 'today')->count();
//            $data = request()->post();
//            $data["status"] = 0;
//            $data['create_operator_name'] = $this->getAccount()['name'];
//            $data['create_operator_id'] = $this->getAccountId();
//            $data['companyid'] = $companyId;
//            $data['system_number'] = 'QTRKD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
//            if (!$return) {
//                Db::startTrans();
//            }
//            try {
//                model("KcQtrk")->allowField(true)->data($data)->save();
//                $id = model("KcQtrk")->getLastInsID();
//                foreach ($data["detail"] as $c => $v) {
//                    $data['details'][$c]['companyid'] = $companyId;
//                    $data['details'][$c]['kc_rk_qt_id'] = $id;
//                }
//                //添加其他入库明细
//                model('KcQtrkMx')->saveAll($data['details']);
//                //添加入库通知
//                $notify = [];
//                foreach ($data['details'] as $c => $v) {
//                    $notify[] = [
//                        'companyid' => $companyId,
//                        'ruku_type' => 3,
//                        'status' => 0,
//                        'data_id' => $id,
//                        'guige_id' => $v['guige_id'],
//                        'caizhi_id' => $v['caizhi_id'] ?? '',
//                        'chandi_id' => $v['chandi_id'] ?? '',
//                        'cache_piaoju_id' => $v['piaoju_id'] ?? '',
//                        'pinming_id' => $v['pinming_id'] ?? '',
//                        'jijiafangshi_id' => $v['jijiafangshi_id'],
//                        'houdu' => $v['houdu'] ?? '',
//                        'kuandu' => $v['kuandu'] ?? '',
//                        'changdu' => $v['changdu'] ?? '',
//                        'lingzhi' => $v['lingzhi'] ?? '',
//                        'fy_sz' => $v['fy_sz'] ?? '',
//                        'zhongliang' => $v['zhongliang'] ?? '',
//                        'jianshu' => $v['jianshu'] ?? '',
//                        'zhijian' => $v['zhijian'] ?? '',
//                        'counts' => $v['counts'] ?? '',
//                        'price' => $v['price'] ?? '',
//                        'sumprice' => $v['sumprice'] ?? '',
//                        'shuie' => $v['shuie'] ?? '',
////                            'ruku_lingzhi' => $v['lingzhi'] ?? '',
////                            'ruku_jianshu' => $v['jianshu'] ?? '',
////                            'ruku_zhongliang' => $v['zhongliang'] ?? '',
////                            'ruku_shuliang' => $v['counts'] ?? '',
//                        'shui_price' => $v['shui_price'] ?? '',
//                        'sum_shui_price' => $v['sum_shui_price'] ?? '',
//                        'beizhu' => $v['beizhu'] ?? '',
//                        'chehao' => $v['chehao'] ?? '',
//                        'pihao' => $v['pihao'] ?? '',
//                        'huohao' => $v['huohao'] ?? '',
//                        'cache_ywtime' => $data['yw_time'],
//                        'cache_data_pnumber' => $data['system_number'],
//                        'cache_customer_id' => $data['customer_id'],
//                        'store_id' => $v['store_id'],
//                        'cache_create_operator' => $data['create_operate_id'],
//                        'mizhong' => $v['mizhong'] ?? '',
//                        'jianzhong' => $v['jianzhong'] ?? '',
//                        'lisuan_zhongliang' => ($v["counts"] * $v["changdu"] * $v['mizhong'] / 1000),
//                        'guobang_zhongliang' => $v['zhongliang'] ?? '',
//                    ];
//                }
//                //添加入库通知
//                model("KcRkTz")->allowField(true)->saveAll($notify);
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

    /**
     * @return Json
     * @throws \Exception
     */
    public function addqtrk()
    {

        if (!request()->isPost()) {
            return returnFail('请求方式错误');
        }

        Db::startTrans();
        try {
            $data = request()->post();

            $validate = new \app\admin\validate\KcQtrk();
            if (!$validate->check($data)) {
                throw new Exception($validate->getError());
            }

            $addList = [];
            $updateList = [];
            $ja = $data['details'];
            $companyId = $this->getCompanyId();
            if (!empty($ja)) {

                $num = 1;
                $detailsValidate = new \app\admin\validate\KcQtrkMx();
                foreach ($ja as $object) {

                    $object['companyid'] = $companyId;
                    $object['caizhi'] = empty($v['caizhi']) ? '' : $this->getCaizhiId($v['caizhi']);
                    $object['chandi'] = empty($v['chandi']) ? '' : $this->getChandiId($v['chandi']);
                    if (!$detailsValidate->check($object)) {
                        throw new Exception('请检查第' . $num . '行' . $detailsValidate->getError());
                    }
                    $num++;

                    if ($object['lingzhi'] == 0 && $object['jianshu'] == 0 && $object['zhijian'] != 0) {
                        throw new Exception("不能只输输入件支数");
                    }

                    if ($object['lingzhi'] > 0 || $object['jianshu'] > 0 || $object['zhijian'] > 0) {
                        $jCount = $object['jianshu'] * $object['zhijian'] + $object['lingzhi'];
                        if ($jCount != $object['counts']) {
                            throw new Exception('计算的数量:' . $jCount . ',您实际输入的数量:' . $object['counts'] . ',计算数量与实际数量不相等');
                        }
                        if ($object['zhijian'] > 0 && $object['lingzhi'] >= $object['zhijian']) {
                            throw new Exception('您输入的零支为:' . $object['lingzhi'] . ',您输入的件支数为:' . $object['zhijian'] . ',零支不能大于或者等于件支数');
                        }
                    }

                    if (empty($object["id"])) {
                        $addList[] = $object;
                    } else {
                        $updateList[] = $object;
                    }
                }
            }

            if (empty($data['id'])) {
                $count = KcQtrk::withTrashed()->whereTime('create_time', 'today')
                    ->where('companyid', $companyId)
                    ->count();

                $data['system_number'] = 'QTRKD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
                $data['create_operator_id'] = $this->getAccountId();
                $data['companyid'] = $companyId;
                $qt = new KcQtrk();
                $qt->allowField(true)->save($data);
            } else {
                $qt = KcQtrk::where('companyid', $companyId)
                    ->where('id', $data['id'])
                    ->find();
                $data['update_operator_id'] = $this->getAccountId();
                $qt->allowField(true)->save($data);
                $mxList = KcQtrkMx::where('kc_rk_qt_id', $qt['id'])->select();
                if (!empty($mxList)) {
                    foreach ($mxList as $obj) {
                        KcRkTz::where('data_id', $obj['id'])->update([
                            'cache_customer_id' => $qt['customer_id']
                        ]);
                    }
                }
            }
            if (!empty($data['deleteMxIds'])) {
                foreach ($data['deleteMxIds'] as $obj) {
                    KcRkTz::destroy(function (Query $query) use ($obj) {
                        $query->where('data_id', $obj);
                    });
                    KcQtrkMx::destroy(function (Query $query) use ($obj) {
                        $query->where('id', $obj);
                    });

                }
            }

            foreach ($updateList as $mjo) {
                $mx = new KcQtrkMx();
                $mx->isUpdate(true)->allowField(true)->save($mjo);
                (new KcRkTz())->updateRukuTz($mx['id'], "3", $mx["pinming_id"], $mx['guige_id'], $mx['caizhi_id'], $mx['chandi_id'],
                    $mx['jijiafangshi_id'], $mx['houdu'], $mx['changdu'], $mx['kuandu'], $mx['counts'],
                    $mx['jianshu'], $mx['lingzhi'], $mx['zhijian'], $mx['zhongliang'], $mx['sum_shui_price'], $mx["price"], $mx["shuiprice"], $mx["huohao"], $mx["pihao"], $qt["beizhu"], $mx["chehao"],
                    $qt["yw_time"], null, $qt['system_number'], $qt['customer_id'], $mx["store_id"], $data["piaoju_id"], $mx["mizhong"], $mx["jianzhong"]);
            }

            foreach ($addList as $mjo) {

                $mjo['companyid'] = $companyId;
                $mjo['kc_rk_qt_id'] = $qt['id'];
                $mx = new KcQtrkMx();
                $mx->allowField(true)->save($mjo);
                (new KcRkTz())->insertRukuTz($mx['id'], "3", $mx["pinming_id"], $mx['guige_id'], $mx['caizhi_id'], $mx['chandi_id'],
                    $mx['jijiafangshi_id'], $mx['houdu'], $mx['changdu'], $mx['kuandu'], $mx['counts'],
                    $mx['jianshu'], $mx['lingzhi'], $mx['zhijian'], $mx['zhongliang'], $mx['shuiprice'], $mx["sumprice"], $mx["sum_shui_price"], $mx["shuie"], $mx["price"], $mx["huohao"], $mx["pihao"], $qt["beizhu"], $mx["chehao"],
                    $qt["yw_time"], null, $qt['system_number'], $qt['customer_id'], $mx["store_id"], $this->getAccountId(), $mx["mizhong"], $mx["jianzhong"], $this->getCompanyId());
            }

            Db::commit();
            return returnSuc(['id' => $qt['id']]);
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**
     * 入库单作废
     * @param Request $request
     * @param $id
     * @return Json
     */
    public function cancel(Request $request, $id)
    {
        Db::startTrans();
        try {
            $rk = KcRk::get($id);
            if (empty($rk)) {
                throw new Exception("对象不存在");
            }
            if ($rk->companyid != $this->getCompanyId()) {
                throw new Exception("对象不存在");
            }
            if (!empty($rk['data_id'])) {
                throw new Exception("当前单据是只读单据,请到关联单据作废");
            }
            if ($rk['status'] == 1) {
                throw new Exception("该单据已经作废");
            }
            $rk->status = 1;
            $rk->save();

            $mdList = KcRkMd::where('kc_rk_id', $rk['id'])->select();
            foreach ($mdList as $tbKcRkMd) {

                KcRk::allPanduanByMxId($tbKcRkMd);

                KcSpot::deleteSpotByRkMd($tbKcRkMd['id']);
                KcRkTz::addTzById($tbKcRkMd['kc_rk_tz_id'], $tbKcRkMd['counts'], $tbKcRkMd['zhongliang']);
            }
            Db::commit();
            return returnSuc();
        } catch (\Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }
}