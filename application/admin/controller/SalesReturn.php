<?php


namespace app\admin\controller;


use app\admin\model\{CapitalFy, SalesReturnDetails, StockOutMd};
use app\admin\validate\FeiyongDetails;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    Exception,
    exception\DbException,
    Request,
    response\Json};

class SalesReturn extends Right
{
    /**
     * 获取销售退货单列表
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function getList(Request $request, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $params = $request->param();
        $list = $list = \app\admin\model\SalesReturn::with(['jsfsData', 'custom', 'pjlxData'])
            ->where('companyid', $this->getCompanyId());
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->whereTime('yw_time', '<', strtotime($params['ywsjEnd'] . ' +1 day'));
        }
        if (!empty($params['status'])) {
            $list->where('status', $params['status']);
        }
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
        $list = $list->paginate($pageLimit);
        return returnRes(true, '', $list);
    }

    /**
     * 获取销售退货单详情
     * @param int $id
     * @return Json
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function details($id = 0)
    {
        $data = $list = \app\admin\model\SalesReturn::with([
            "jsfsData",
            "custom",
            "pjlxData",
            'details' => ['jsfs', 'storage', 'pinmingData', 'caizhi', 'chandi'],
            'other' => ['mingxi' => ['szmcData', 'pjlxData', 'custom']]
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
     * 添加销售退货单
     * @param Request $request
     * @return Json
     * @throws Exception
     */
    public function add(Request $request)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }

        $companyId = $this->getCompanyId();
        $data = request()->post();
        $count = \app\admin\model\SalesReturn::whereTime('create_time', 'today')->count();
        $data['create_operator_id'] = $this->getAccountId();
        $data['companyid'] = $companyId;
        $data['system_number'] = 'XSTHD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
        Db::startTrans();
        try {
            //保存退货单信息
            $model = new \app\admin\model\SalesReturn();
            $model->allowField(true)->data($data)->save();
            $id = $model->getLastInsID();

            //保存退货单明细
            $totalMoney = 0;
            $totalWeight = 0;
            $num = 1;
            foreach ($data["details"] as $c => $v) {
                $stockOut = StockOutMd::where('id', $v['stock_out_md_id'])
                    ->find();
                if (empty($stockOut) || $stockOut->mainData->status == 2) {
                    throw new \Exception('请检查第' . $num . '行：未找到对应发货单');
                }

                if ($v["counts"] > $stockOut["counts"]) {
                    throw new \Exception('请检查第' . $num . '行：退货数量不得大于' . $stockOut["counts"]);
                }
                if ($v["zhongliang"] > $stockOut["zhongliang"]) {
                    throw new \Exception('请检查第' . $num . '行：退货重量不得大于' . $stockOut["zhongliang"]);
                }
                $totalMoney += $v['sum_shui_price'];
                $totalWeight += $v['zhongliang'];
                $data['details'][$c]['companyid'] = $companyId;
                $data['details'][$c]['xs_th_id'] = $id;
                $data['details'][$c]['spot_id'] = $stockOut->kc_spot_id;
                $data['details'][$c]['create_operate_id'] = $this->getAccountId();
                $data['details'][$c]['caizhi_id'] = $this->getCaizhiId($v['caizhi']);
                $data['details'][$c]['chandi_id'] = $this->getChandiId($v['chandi']);

                $num++;
            }
            (new SalesReturnDetails())->allowField(true)->saveAll($data['details']);
            //执行入库
            $stockInData = [
                'customer_id' => $data['customer_id'],
                'beizhu' => '销售退货单，',
                'yw_time' => $data['yw_time'],
                'group_id' => $data['group_id'],
                'sale_operator_id' => $data['sale_operator_id'],
                'ruku_fangshi' => 1,
                'create_operate_id' => $this->getAccountId(),
                'piaoju_id' => $data['piaoju_id']
            ];
            foreach ($data['details'] as $index => $item) {
                $stockInData['details'][$index] = $item;
                $stockInData['details'][$index]['ruku_type'] = 7;
                $stockInData['details'][$index]['ruku_fangshi'] = 1;
            }
            (new Purchase())->zidongruku($id, $stockInData, 7);
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
                $res = (new Feiyong())->addAll($data['other'], 3, $id, $data['yw_time'], false);
                if ($res !== true) {
                    throw new Exception($res);
                }
            }
            //向货款单添加数据
            $capitalHkData = [
                'hk_type' => CapitalHk::SALES_ORDER_RETURN,
                'data_id' => $id,
                'fangxiang' => 1,
                'customer_id' => $data['customer_id'],
                'jiesuan_id' => $data['jiesuan_id'],
                'system_number' => $data['system_number'],
                'yw_time' => $data['yw_time'],
                'beizhu' => $data['beizhu'],
                'money' => -$totalMoney,
                'group_id' => $data['group_id'],
                'sale_operator_id' => $data['sale_operator_id'],
                'create_operator_id' => $data['create_operator_id'],
                'zhongliang' => -$totalWeight,
                'cache_pjlx_id' => $data['piaoju_id'],
            ];
            (new CapitalHk())->add($capitalHkData);

            Db::commit();
            return returnRes(true, '', ['id' => $id]);
        } catch (\Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    public function doAdd(Request $request)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }
        Db::startTrans();
        try {
            $data = $request->post();
            $addList = [];
            $updateList = [];
            $ja = $data['details'];
            $companyId = $this->getCompanyId();
            if (!empty($ja)) {
                foreach ($ja as $object) {
                    $object['companyid'] = $companyId;
                    $object['caizhi'] = $this->getCaizhiId($object['caizhi'] ?? '');
                    $object['chandi'] = $this->getChandiId($object['chandi'] ?? '');
                    if (empty($object['id'])) {
                        $addList[] = $object;
                    } else {
                        $updateList[] = $object;
                    }
                }
            }

            if (empty($data['id'])) {

                $count = \app\admin\model\SalesReturn::withTrashed()->whereTime('create_time', 'today')
                    ->where('companyid', $companyId)
                    ->count();
                $data['companyid'] = $companyId;
                $data['system_number'] = 'XSTHD' . date('Ymd') . str_pad($count, 3, 0, STR_PAD_LEFT);
                $data['create_operator_id'] = $this->getAccountId();
                $th = new \app\admin\model\SalesReturn();
                $th->allowField(true)->data($data)->save();

                //todo 入库
//            rk = this . rkDaoImpl . insertRuku(th . getId(), "7", th . getSystemNumber(), th . getYwTime(), th . getGroupId(), user, jigou, zhangtao, su, th . getSaleOperatorId());
            } else {
                throw new \Exception('销售退货单禁止修改');
//            th = (TbXsTh) getBeanDAO() . selectByPrimaryKey(pid);
//            if (th == null) {
//                throw new ValidateException("对象不存在");
//            }
//            if (!user . getId() . equals(th . getUserId())) {
//                throw new ValidateException("对象不存在");
//            }
//            if ("1" . equals(th . getStatus())) {
//                throw new ValidateException("该单据已经作废");
//            }
//            th . setCustomerId(customerId);
//            th . setPiaojuId(piaojuId);
//            th . setPriceId(priceId);
//            th . setJiesuanId(jiesuanId);
//            th . setLxr(lxr);
//            th . setTelephone(telephone);
//            th . setChengyunfang(chengyunfang);
//            th . setChehao(chehao);
//            th . setYunjia(yunjia);
//            th . setYunfei(yunfei);
//            th . setOfflineNumber(offlineNumber);
//            th . setBeizhu(beizhu);
//            th . setYwTime(DateUtil . parseDate(ywTime, "yyyy-MM-dd HH:mm:ss"));
//            th . setGroupId(groupId);
//            th . setSaleOperatorId(saleOperatorId);
//            th . setUpdateOperatorId(su . getId());
//            th . setYfhs(yfhs);
//            getBeanDAO() . updateByPrimaryKeySelective(th);
//            rk = this . rkDaoImpl . updateRuku(th . getId(), "7", null, th . getYwTime(), th . getCustomerId(), th . getGroupId(), th . getSaleOperatorId());
            }

            if (!empty($data['deleteMxIds']) || !empty($updateList)) {
                throw new \Exception('销售退货单禁止修改');
//        for (Map < String, String > map : deleteList) {
//            TbXsThMx mx = new TbXsThMx();
//            mx . setId((String)map . get("id"));
//            mx . setIsDelete("1");
//            this . mxDao . updateByPrimaryKeySelective(mx);
//            this . rkDaoImpl . deleteRkMxMd(mx . getId(), "7");
//
//            this . invDaoImpl . deleteInv(mx . getId(), "6");
//        }
//
//        for (Map < String, String > map : updateList) {
//            TbXsThMx mx = new TbXsThMx();
//
//            BigDecimal maxCounts = this . mdDAO . findCountsByDataId((String)map . get("xsmxId"));
//            BigDecimal thCounts = this . mxDao . findCountsByXsSaleMxId((String)map . get("xsmxId"));
//            BigDecimal maxZhongliang = this . mdDAO . findZhongliangByDataId((String)map . get("xsmxId"));
//            BigDecimal thZhongliang = this . mxDao . findZhongliangByXsSaleMxId((String)map . get("xsmxId"));
//            if (maxCounts == null) {
//                maxCounts = BigDecimal . valueOf(0L);
//            }
//            if (thCounts == null) {
//                thCounts = BigDecimal . valueOf(0L);
//            }
//            if (maxZhongliang == null) {
//                maxZhongliang = BigDecimal . valueOf(0L);
//            }
//            if (thZhongliang == null) {
//                thZhongliang = BigDecimal . valueOf(0L);
//            }
//
//            if (maxCounts . subtract(thCounts) . compareTo(new BigDecimal((String)map . get("counts")) . subtract(thCounts)) < 0) {
//                throw new ValidateException("本次退货数量大于销售出库数量(剩余未退货重量为：" + maxCounts . subtract(thCounts) . stripTrailingZeros() . toPlainString() + ")");
//            }
//
//            if (maxZhongliang . subtract(thZhongliang) . compareTo(new BigDecimal((String)map . get("zhongliang")) . subtract(thZhongliang)) < 0) {
//                throw new ValidateException("本次退货重量大于销售出库重量(剩余未退货重量为：" + maxZhongliang . subtract(thZhongliang) . stripTrailingZeros() . toPlainString() + ")");
//            }
//
//            mx . setId((String)map . get("id"));
//            mx . setStoreId((String)map . get("storeId"));
//            mx . setPinmingId((String)map . get("pinmingId"));
//            mx . setGuigeId((String)map . get("guigeId"));
//            mx . setCaizhiId((String)map . get("caizhiId"));
//            mx . setHoudu(new BigDecimal((String)map . get("houdu")));
//            mx . setKuandu(new BigDecimal((String)map . get("kuandu")));
//            mx . setChangdu(new BigDecimal((String)map . get("changdu")));
//            mx . setLingzhi(new BigDecimal((String)map . get("lingzhi")));
//            mx . setJianshu(new BigDecimal((String)map . get("jianshu")));
//            mx . setZhijian(new BigDecimal((String)map . get("zhijian")));
//            mx . setCounts(new BigDecimal((String)map . get("counts")));
//            mx . setZhongliang(new BigDecimal((String)map . get("zhongliang")));
//            mx . setPrice(new BigDecimal((String)map . get("price")));
//            mx . setSumprice(new BigDecimal((String)map . get("sumprice")));
//            mx . setShuiprice(new BigDecimal((String)map . get("shuiprice")));
//            mx . setShuie(new BigDecimal((String)map . get("shuie")));
//            mx . setSumShuiprice(new BigDecimal((String)map . get("sumShuiprice")));
//
//            mx . setBeizhu((String)map . get("beizhu"));
//            mx . setExt1((String)map . get("ext1"));
//            mx . setExt2((String)map . get("ext2"));
//            mx . setExt3((String)map . get("ext3"));
//            mx . setExt4((String)map . get("ext4"));
//            mx . setExt5((String)map . get("ext5"));
//            mx . setExt6((String)map . get("ext6"));
//            mx . setExt7((String)map . get("ext7"));
//            mx . setExt8((String)map . get("ext8"));
//            mx . setGgBm((String)map . get("ggBm"));
//            mx . setChandiId((String)map . get("chandiId"));
//            mx . setJijiafangshiId((String)map . get("jijiafangshiId"));
//            mx . setPihao((String)map . get("pihao"));
//            mx . setChehao((String)map . get("chehao"));
//            mx . setHuohao((String)map . get("huohao"));
//            mx . setMizhong(new BigDecimal((String)map . get("mizhong")));
//            mx . setJianzhong(new BigDecimal((String)map . get("jianzhong")));
//            this . mxDao . updateByPrimaryKeySelective(mx);
//
//            TbXsThMx tbXsThMx = (TbXsThMx) this . mxDao . selectByPrimaryKey(mx . getId());
//            TbKcSpot spot = (TbKcSpot) this . spotDao . selectByPrimaryKey(tbXsThMx . getSpotId());
//            BigDecimal price = spot . getPrice();
//            BigDecimal sumShuiPrice = new BigDecimal(0);
//            BigDecimal sumPrice = new BigDecimal(0);
//            BigDecimal shuie = new BigDecimal(0);
//            TbBaseJijiafangshi jjfs = (TbBaseJijiafangshi) this . jjfsDao . selectByPrimaryKey(spot . getJijiafangshiId());
//            if ((jjfs . getBaseJijialeixingId() . equals(Globals . DICT_BASE_JIJIALEIXING_1)) || (jjfs . getBaseJijialeixingId() . equals(Globals . DICT_BASE_JIJIALEIXING_2))) {
//                sumShuiPrice = price . multiply(mx . getZhongliang());
//            } else if (jjfs . getBaseJijialeixingId() . equals(Globals . DICT_BASE_JIJIALEIXING_3)) {
//                sumShuiPrice = price . multiply(mx . getCounts());
//            }
//            sumPrice = WuziUtil . calSumPrice(sumShuiPrice, price);
//            shuie = WuziUtil . calShuie(sumShuiPrice, spot . getShuiprice());
//            BigDecimal fySz = new BigDecimal(0);
//
//            this . rkDaoImpl . updateRkMxMd(mx . getId(), "7", mx . getChangdu(), mx . getHoudu(), mx . getKuandu(), mx . getLingzhi(), mx . getJianshu(), mx . getCounts(), mx . getZhongliang(), mx . getZhijian(), spot . getCustomerId(), mx . getPinmingId(), mx . getGuigeId(), mx . getCaizhiId(), mx . getChandiId(), mx . getJijiafangshiId(), mx . getStoreId(), mx . getPihao(), mx . getHuohao(), mx . getChehao(), mx . getGgBm(), mx . getBeizhu(), th . getPiaojuId(), price, sumPrice, spot . getShuiprice(), sumShuiPrice, shuie, mx . getMizhong(), mx . getJianzhong(), price, fySz, mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8(), zhangtao);
//
//            this . invDaoImpl . updateInv(mx . getId(), "3", null, th . getCustomerId(), th . getYwTime(), mx . getChangdu(), mx . getKuandu(), mx . getHoudu(), mx . getGuigeId(), mx . getJijiafangshiId(), th . getPiaojuId(), mx . getPinmingId(), new BigDecimal(0) . subtract(mx . getZhongliang()), mx . getPrice(), new BigDecimal(0) . subtract(mx . getSumprice()), new BigDecimal(0) . subtract(mx . getSumShuiprice()), mx . getShuiprice(), mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8());
//        }
            }

            $trumpet = 0;

            if (!empty($addList)) {
                if (!empty($data['id'])) {
                    $trumpet = SalesReturnDetails::where('xs_th_id', $data['id'])->max('trumpet');
                }
                foreach ($addList as $map) {
                    $trumpet++;
                    $mx = new SalesReturnDetails();

                    $maxCounts = StockOutMd::findCountsByDataId($map['xs_sale_mx_id']);
                    $thCounts = SalesReturnDetails::findCountsByXsSaleMxId($map['xs_sale_mx_id']);
                    $maxZhongliang = StockOutMd::findZhongliangByDataId($map['xs_sale_mx_id']);
                    $thZhongliang = SalesReturnDetails::findZhongliangByXsSaleMxId($map['xs_sale_mx_id']);

                    if ($maxCounts - $thCounts < $map['counts']) {
                        throw new Exception('本次退货数量大于销售出库数量(销售出库数量为：' . ($maxCounts - $thCounts) . ')');
                    }

                    if ($maxZhongliang - $thZhongliang < $map['zhongliang']) {
                        throw new Exception('本次退货重量大于销售出库重量(销售出库重量为：' . ($maxZhongliang - $thZhongliang) . ')');
                    }

                    $map['xs_th_id'] = $th['id'];

                    $mx->allowField(true)->data($map)->save();

//                    $spot = KcSpot::get($mx['spot_id']);

//                    $price = $spot['price'];
//                    $sumShuiPrice = 0;
//                    $sumPrice = 0;
//                    $shuie = 0;
//                    $jjfs = Jsfs::where('id', $spot['jijiafangshi_id'])->cache(true, 60)->find();

//                    if ($jjfs == 1 || $jjfs == 2) {
//                        $sumShuiPrice = $price * $mx['zhongliang'];
//                    } elseif ($jjfs == 3) {
//                        $sumShuiPrice = $price * $mx['counts'];
//                    }
//                sumPrice = WuziUtil . calSumPrice(sumShuiPrice, price);
//                shuie = WuziUtil . calShuie(sumShuiPrice, spot . getShuiprice());

                    //todo 入库
//                this . rkDaoImpl . insertRkMxMd(rk, mx . getId(), "7", th . getYwTime(), th . getSystemNumber(), null, spot . getCustomerId(), mx . getPinmingId(), mx . getGuigeId(), mx . getCaizhiId(), mx . getChandiId(), mx . getJijiafangshiId(), mx . getStoreId(), mx . getPihao(), mx . getHuohao(), mx . getChehao(), mx . getGgBm(), mx . getBeizhu(), piaojuId, mx . getHoudu(), mx . getKuandu(), mx . getChangdu(), mx . getZhijian(), mx . getLingzhi(), mx . getJianshu(), mx . getCounts(), mx . getZhongliang(), price, sumPrice, spot . getShuiprice(), sumShuiPrice, shuie, mx . getMizhong(), mx . getJianzhong(), price, fySz, su, user, zhangtao, jigou, mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8());

                    (new \app\admin\model\Inv())->insertInv($mx['id'], 6, 1, $mx['changdu'], $mx['houdu'], $mx['kuandu'], $mx['guige_id'], $mx['jijiafangshi_id'], $mx['piaoju_id'], $mx['pinming_id'], $th['system_number'] . '.' . $mx['trumpet'], $th['customer_id'], $th['yw_time'], $mx['price'], $mx['shuiprice'], -$mx['sumprice'], -$mx['sum_shui_price'], -$mx['zhongliang'], $companyId);
                }
            }

            $sumMoney = SalesReturnDetails::getSumJiashuiHejiByPid($th['id']);
            $sumZhongliang = SalesReturnDetails::getSumZhongliangByPid($th['id']);

            if (empty($data['id'])) {
                (new \app\admin\model\CapitalHk())->insertHk($th['id'], 14, $th['system_number'], $th['beizhu'], $th['customer_id'],
                    1, $th['yw_time'], $th['jiesuan_id'], $th['piaoju_id'], $sumMoney, $sumZhongliang, $th['group_id'], $th['sale_operator_id'], $this->getAccountId(), $companyId);
            } else {
                throw new \Exception('销售退货单禁止修改');
//            this . hkDaoImpl . updateHk(th . getId(), "14", th . getBeizhu(), th . getCustomerId(), th . getYwTime(), th . getJiesuanId(), th . getPiaojuId(), new BigDecimal(0) . subtract(sumMoney), new BigDecimal(0) . subtract(sumZhongliang), th . getGroupId(), th . getSaleOperatorId());
            }

            $thBeizhu = "销售退货费用";
            (new CapitalFy())->fymxSave($data['other'], $data['deleteOtherIds'], $th['id'], $th['yw_time'], 5,
                $th['group_id'], $th['sale_operator_id'], $thBeizhu, $this->getAccountId(), $companyId);
            Db::commit();
            return returnSuc(['id' => $th['id']]);
        } catch (\Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }
}