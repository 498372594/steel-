<?php

namespace app\admin\controller;

use app\admin\model\CapitalFy;
use app\admin\model\Jsfs;
use app\admin\model\KcSpot;
use app\admin\model\SalesMoshi;
use app\admin\model\SalesMoshiMx;
use app\admin\model\SalesorderDetails;
use app\admin\model\StockOut;
use app\admin\model\StockOutMd;
use app\admin\validate\{SalesMoshiDetails};
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    Request,
    response\Json};

class Zhifa extends Right
{
    /**
     * 获取采购直发单列表
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
        $list = SalesMoshi::with([
            'custom',
            'gongyingshang',
            'gfpjData',
            'khpjData',
            'gfjsfsData',
            'khjsfsData',
        ])->where('companyid', $this->getCompanyId())
            ->order('create_time', 'desc')
            ->where('moshi_type', 1);
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('yw_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['cgpb'])) {
            $list->where('cg_piaoju_id', $params['cgpb']);
        }
        if (!empty($params['status'])) {
            $list->where('status', $params['status']);
        }
        if (!empty($params['system_no'])) {
            $list->where('system_number', 'like', '%' . $params['system_no'] . '%');
        }
        if (!empty($params['custom_id'])) {
            $list->where('customer_id', $params['custom_id']);
        }
        if (!empty($params['xspb'])) {
            $list->where('piaoju_id', $params['xspb']);
        }
        if (!empty($params['remark'])) {
            $list->where('remark', 'like', '%' . $params['remark'] . '%');
        }
        $list = $list->paginate($pageLimit);
        return returnRes(true, '', $list);
    }

    /**
     * 获取采购直发单详情
     * @param Request $request
     * @param int $id
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function detail(Request $request, $id = 0)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $data = SalesMoshi::with([
            'custom',
            'gongyingshang',
            'gfpjData',
            'khpjData',
            'gfjsfsData',
            'khjsfsData',
            'details' => ['specification', 'jsfs', 'storage'],
            'other' => ['other' => ['mingxi' => ['szmcData', 'pjlxData', 'custom']]]
        ])->where('companyid', $this->getCompanyId())
            ->where('moshi_type', 1)
            ->where('id', $id)
            ->find();
        if (empty($data)) {
            return returnFail('数据不存在');
        } else {
            return returnRes(true, '', $data);
        }
    }

    /**
     * 添加采购直发单
     * @param Request $request
     * @return Json
     */
    public function add(Request $request)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }
        Db::startTrans();
        try {
            $data = $request->post();

            $validate = new \app\admin\validate\SalesMoshi();
            if (!$validate->scene('zhifa')->check($data)) {
                throw new Exception($validate->getError());
            }

            $addList = [];
            $updateList = [];
//        $deleteList = [];
            $ja = $data['details'];
//        $ja1 = $data['other'];
            $jqDataType1 = null;
            $companyId = $this->getCompanyId();
            if (!empty($ja)) {
                $detailsValidate = new SalesMoshiDetails();
                $num = 1;

                foreach ($ja as $object) {
                    $object['companyid'] = $companyId;
                    if (!$detailsValidate->scene('zhifa')->check($object)) {
                        throw new Exception('请检查第' . $num . '行' . $detailsValidate->getError());
                    }
                    if (empty($object['id'])) {
                        $addList[] = $object;
                    } else {
                        $updateList[] = $object;
                    }
                    $num++;
                }
            }

            if (empty($data['id'])) {
                $count = SalesMoshi::withTrashed()->where('companyid', $companyId)->where('moshi_type', 2)->count();
                $data['create_operator_id'] = $this->getAccountId();
                $data['moshi_type'] = 2;
                $data['companyid'] = $companyId;
                $data['system_number'] = 'CGZFD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
                $ms = (new SalesMoshi())->allowField(true)->data($data)->save();

                //todo 采购和入库
//            cg = this . cgDaoImpl . insertCaigou(ms . getId(), "2", ms . getYwTime(), cgCustomerId, cgJiesuanId, "1", cgPiaojuId, beizhu, groupId, saleOperatorId, user, su, jigou, zhangtao);
//
//            rk = this . rkDaoImpl . insertRuku(cg . getId(), "4", cg . getSystemNumber(), ms . getYwTime(), groupId, user, jigou, zhangtao, su, saleOperatorId);

                $sale = (new \app\admin\model\Salesorder())->insertSale($ms['id'], "2", $ms['yw_time'], $ms['customer_id'],
                    $ms['piaoju_id'], $ms['jsfs'], $ms['remark'], $ms['department'], $ms['employer'], $ms['contact'], $ms['mobile'], $ms['chehao'], $this->getAccountId(), $companyId);
                $ck = (new StockOut())->insertChuku($sale['id'], 4, $ms['yw_time'], $ms['department'], $ms['system_number'], $ms['employer'], $this->getAccountId(), $companyId);
            } else {
                throw new Exception('采购直发单禁止修改');
//            $ms = SalesMoshi::get($data['id']);
//            if (empty($ms)) {
//                throw new Exception("对象不存在");
//            }
//            if ($ms['status'] == 2) {
//                throw new Exception("该单据已经作废");
//            }
//
//            $ms->allowField(true)->save($data);
//
//            $sale = (new \app\admin\model\Salesorder())->updateSale($ms['id'], 2, $ms['yw_time'], $ms['chehao'],
//                $ms['customer_id'], $ms['piaoju_id'], $ms['jsfs'], $ms['department'], $ms['employer'], $ms['contact'], $ms['mobile']);
//            String xsId = this . saleDao . findXsIdByMoshiId(ms . getId(), user . getId(), jigou . getId(), zhangtao . getId());
//            TbXsSale xsSale = (TbXsSale) this . saleDao . selectByPrimaryKey(xsId);
//            xsSale . setFaxiId(fxlx);
//            this . saleDao . updateByPrimaryKeySelective(xsSale);
//
//            ck = this . ckDaoImpl . updateChuku(sale . getId(), "4", ms . getYwTime(), customerId, ms . getGroupId(), ms . getSaleOperatorId(), msChehao);
//
//            cg = this . cgDaoImpl . updateCaigou(ms . getId(), "2", ms . getYwTime(), cgCustomerId, cgPiaojuId, cgJiesuanId, cgBeizhu, groupId, su, saleOperatorId);
//
//            rk = this . rkDaoImpl . updateRuku(cg . getId(), "4", null, ms . getYwTime(), cgCustomerId, cg . getGroupId(), cg . getSaleOperateId());
//
//            Example esalemx = new Example(TbXsSaleMx .class);
//            esalemx . createCriteria() . andCondition("xs_sale_id=", sale . getId());
//            List<TbXsSaleMx > salemxList = this . xsSaleMxDao . selectByExample(esalemx);
//            if (salemxList . size() != 0) {
//                for (TbXsSaleMx salemx : salemxList) {
//                    Example einv = new Example(TbInv .class);
//                    einv . createCriteria() . andCondition("data_id=", salemx . getId());
//                    List<TbInv > invList = this . invDao . selectByExample(einv);
//                    if (invList . size() != 0) {
//                        for (TbInv tbInv : invList) {
//                            TbInv inv = new TbInv();
//                            inv . setId(tbInv . getId());
//                            inv . setYwTime(ms . getYwTime());
//                            inv . setCustomerId(customerId);
//                            this . invDao . updateByPrimaryKeySelective(inv);
//                        }
//                    }
//                }
//            }
//            Example ecgmx = new Example(TbCgPurchaseMx .class);
//            ecgmx . createCriteria() . andCondition("purchase_id=", cg . getId());
//            List<TbCgPurchaseMx > cgmxList = this . cgMxDao . selectByExample(ecgmx);
//            if (cgmxList . size() != 0) {
//                for (TbCgPurchaseMx cgmx : cgmxList) {
//                    Example einv = new Example(TbInv .class);
//                    einv . createCriteria() . andCondition("data_id=", cgmx . getId());
//                    List<TbInv > invList = this . invDao . selectByExample(einv);
//                    if (invList . size() != 0) {
//                        for (TbInv tbInv : invList) {
//                            TbInv inv = new TbInv();
//                            inv . setId(tbInv . getId());
//                            inv . setYwTime(ms . getYwTime());
//                            inv . setCustomerId(cgCustomerId);
//                            this . invDao . updateByPrimaryKeySelective(inv);
//                        }
//                    }
//                }
//            }
            }

            if (!empty($data['deleteIds']) || !empty($updateList)) {
                throw new Exception('采购直发单禁止修改');
//            for (TbMoshiMx_Ex mjo : deleteList) {
//                TbMoshiMx mx = new TbMoshiMx();
//            mx . setId(mjo . getId());
//            mx . setIsDelete("1");
//            this . mxDao . updateByPrimaryKeySelective(mx);
//
//            TbXsSaleMx saleMx = this . xsDaoImpl . deleteMx(mx . getId(), "2");
//
//            this . ckDaoImpl . deleteCkMxMd(saleMx . getId(), "4");
//
//            TbCgPurchaseMx cgMx = this . cgDaoImpl . deleteMx(mx . getId(), "2");
//
//            this . rkDaoImpl . deleteRkMxMd(cgMx . getId(), "4");
//
//            this . invDaoImpl . deleteInv(saleMx . getId(), "3");
//
//            this . invDaoImpl . deleteInv(cgMx . getId(), "2");
//        }
//        for (TbMoshiMx_Ex obj : updateList) {
//            TbMoshiMx mx = new TbMoshiMx();
//            TbMoshiMx eduMx = (TbMoshiMx) this . mxDao . selectByPrimaryKey(obj . getId());
//            if (khedu . intValue() > 0) {
//                List<TbXsSaleMx_Ex > eduList = this . xsMxDao . findXyedByCustomerId(customerId, user . getId(), jigou . getId(), zhangtao . getId());
//
//                for (TbXsSaleMx_Ex mxList : eduList) {
//                    String customerName = mxList . getCustomerName();
//                    String xyEduxs = mxList . getXinyongeduxs();
//                    String shoukuanEduStr = this . skDAO . findMoneyByCustomerId(customerId, user . getId(), jigou . getId(), zhangtao . getId());
//
//                    String fukuanEduStr = this . fkDao . findMoneyByCustomerId(customerId, user . getId(), jigou . getId(), zhangtao . getId());
//
//                    BigDecimal yiyongEdu = new BigDecimal(shoukuanEduStr) . subtract(new BigDecimal(fukuanEduStr)) . subtract(eduMx . getSumShuiPrice());
//                    BigDecimal xyEdu = new BigDecimal(mxList . getXinyongedu());
//                    BigDecimal bcJine = new BigDecimal(shoukuanjine) . setScale(2, 4);
//                    BigDecimal bcky = xyEdu . subtract(yiyongEdu) . setScale(2, 4);
//                    BigDecimal pdKy = xyEdu . subtract(yiyongEdu) . subtract(bcJine);
//                    String bckyStr = bcky . stripTrailingZeros() . toPlainString();
//                    String bcJineStr = bcJine . stripTrailingZeros() . toPlainString();
//                    if (pdKy . compareTo(new BigDecimal(0)) < 0) {
//                        throw new ValidateException("客户：" + customerName + "的信用额度为：" + xyEduxs + ",还剩：" + bckyStr + ",本次金额：" + bcJineStr + ",大于信用额度，保存失败！");
//                    }
//                }
//            }
//
//            BigDecimal xgWeight = eduMx . getZhongliang();
//            this . awDaoImpl . availableTody(saleOperatorId, ywTime . substring(0, 10), obj . getZhongliang(), xgWeight, user, jigou, zhangtao);
//            BigDecimal xgJine = eduMx . getSumShuiPrice();
//
//            this . smsDaoImpl . compYskByOperatorId(customerId, saleOperatorId, user, jigou, obj . getSumShuiPrice(), xgJine, zhangtao);
//
//            mx . setId(obj . getId());
//            mx . setStoreId(obj . getStoreId());
//            mx . setPinmingId(obj . getPinmingId());
//            mx . setGuigeId(obj . getGuigeId());
//            mx . setCaizhiId(obj . getCaizhiId());
//            mx . setChandiId(obj . getChandiId());
//            mx . setHoudu(obj . getHoudu());
//            mx . setKuandu(obj . getHoudu());
//            mx . setChangdu(obj . getChangdu());
//            mx . setJijiafangshiId(obj . getJijiafangshiId());
//
//            mx . setLingzhi(obj . getLingzhi());
//            mx . setCgLingzhi(obj . getCgLingzhi());
//
//            mx . setZhijian(obj . getZhijian());
//
//            mx . setCgJianshu(obj . getCgJianshu());
//            mx . setJianshu(obj . getJianshu());
//
//            mx . setCounts(obj . getCounts());
//            mx . setCgCounts(obj . getCgCounts());
//
//            mx . setCgZhongliang(obj . getCgZhongliang());
//            mx . setZhongliang(obj . getZhongliang());
//
//            mx . setPrice(obj . getPrice());
//            mx . setCgPrice(obj . getCgPrice());
//
//            mx . setCgSumprice(obj . getCgSumprice());
//            mx . setSumprice(obj . getSumprice());
//
//            mx . setShuiprice(obj . getShuiprice());
//            mx . setCgShuiprice(obj . getCgShuiprice());
//
//            mx . setShuie(obj . getShuie());
//            mx . setCgShuie(obj . getCgShuie());
//
//            mx . setSumShuiPrice(obj . getSumShuiPrice());
//            mx . setCgSumShuiPrice(obj . getCgSumShuiPrice());
//            mx . setBeizhu(obj . getBeizhu());
//            mx . setChehao(obj . getChehao());
//            mx . setPihao(obj . getPihao());
//
//            mx . setExt1(obj . getExt1());
//            mx . setExt2(obj . getExt2());
//            mx . setExt3(obj . getExt3());
//            mx . setExt4(obj . getExt4());
//            mx . setExt5(obj . getExt5());
//            mx . setExt6(obj . getExt6());
//            mx . setExt7(obj . getExt7());
//            mx . setExt8(obj . getExt8());
//            mx . setMizhong(obj . getMizhong());
//            mx . setJianzhong(obj . getJianzhong());
//            this . mxDao . updateByPrimaryKeySelective(mx);
//
//            TbCgPurchaseMx cgmx = this . cgDaoImpl . updateMx(mx . getId(), "2", mx . getGuigeId(), mx . getStoreId(), mx . getCaizhiId(), mx . getChandiId(), mx . getPinmingId(), mx . getJijiafangshiId(), mx . getChangdu(), mx . getHoudu(), mx . getKuandu(), mx . getCgLingzhi(), mx . getCgJianshu(), mx . getZhijian(), mx . getCgCounts(), mx . getCgZhongliang(), mx . getCgPrice(), mx . getCgSumprice(), mx . getCgShuiprice(), mx . getCgSumShuiPrice(), mx . getCgShuie(), mx . getFySz(), mx . getPihao(), mx . getHuohao(), cgBeizhu, mx . getChehao(), mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8(), mx . getMizhong(), mx . getJianzhong());
//
//            TbKcSpot spot = (TbKcSpot) this . spotDao . selectByPrimaryKey(mx . getKcSpotId());
//            BigDecimal cbPrice = null;
//            if (spot == null) {
//                cbPrice = null;
//            } else {
//                cbPrice = spot . getCbPrice();
//            }
//            this . rkDaoImpl . updateRkMxMd(cgmx . getId(), "4", mx . getChangdu(), mx . getHoudu(), mx . getKuandu(), mx . getCgLingzhi(), mx . getCgJianshu(), mx . getCgCounts(), mx . getCgZhongliang(), mx . getCgZhijian(), cg . getCustomerId(), mx . getPinmingId(), mx . getGuigeId(), mx . getCaizhiId(), mx . getChandiId(), mx . getJijiafangshiId(), mx . getStoreId(), mx . getPihao(), mx . getHuohao(), mx . getChehao(), mx . getGgBm(), mx . getBeizhu(), cg . getPiaojuId(), mx . getCgPrice(), mx . getCgSumprice(), mx . getCgShuiprice(), mx . getCgSumShuiPrice(), mx . getCgShuie(), mx . getMizhong(), mx . getJianzhong(), cbPrice, null, mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8(), zhangtao);
//
//            TbXsSaleMx saleMx = this . xsDaoImpl . updateMx(mx . getId(), "2", mx . getGuigeId(), mx . getGgBm(), mx . getCaizhiId(), mx . getChandiId(), mx . getStoreId(), mx . getJijiafangshiId(), mx . getPinmingId(), mx . getHoudu(), mx . getKuandu(), mx . getChangdu(), mx . getLingzhi(), mx . getJianshu(), mx . getZhijian(), mx . getCounts(), mx . getZhongliang(), mx . getPrice(), mx . getSumprice(), mx . getShuiprice(), mx . getSumShuiPrice(), mx . getShuie(), mx . getFySz(), mx . getPihao(), mx . getHuohao(), cgBeizhu, mx . getChehao(), mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8(), mx . getMizhong(), mx . getJianzhong());
//
//            this . ckDaoImpl . updateCkMxMd(saleMx . getId(), "4", ms . getYwTime(), mx . getChangdu(), mx . getHoudu(), mx . getKuandu(), mx . getLingzhi(), mx . getJianshu(), mx . getCounts(), mx . getZhongliang(), mx . getZhijian(), mx . getPrice(), mx . getSumprice(), mx . getShuiprice(), mx . getSumShuiPrice(), mx . getMizhong(), mx . getJianzhong(), mx . getStoreId(), mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8(), cbPrice, mx . getJijiafangshiId());
//
//            this . invDaoImpl . updateInv(saleMx . getId(), "3", null, sale . getCustomerId(), sale . getYwTime(), saleMx . getChangdu(), saleMx . getKuandu(), saleMx . getHoudu(), saleMx . getGuigeId(), saleMx . getJijiafangshiId(), sale . getPiaojuId(), saleMx . getPinmingId(), saleMx . getZhongliang(), saleMx . getPrice(), saleMx . getSumprice(), saleMx . getSumShuiPrice(), saleMx . getShuiprice(), mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8());
//
//            this . invDaoImpl . updateInv(cgmx . getId(), "2", null, cg . getCustomerId(), cg . getYwTime(), cgmx . getChangdu(), cgmx . getKuandu(), cgmx . getHoudu(), cgmx . getGuigeId(), cgmx . getJijiafangshiId(), cg . getPiaojuId(), cgmx . getPinmingId(), cgmx . getZhongliang(), cgmx . getPrice(), cgmx . getSumprice(), cgmx . getSumShuiPrice(), cgmx . getShuiPrice(), mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8());
//
//            Integer isStartFaxi = this . fxDaoImpl . isStartFaxi(user, zhangtao);
//            if (isStartFaxi . intValue() == 1) {
//                this . fxDaoImpl . setFaxiForQt(fxlx, customerId, jiesuanId, groupId, saleOperatorId, sale . getCreateOperatorId(), jigou, user, zhangtao, sale);
//            }
//        }
            }

            if (empty($data['id'])) {
                $trumpet = 0;
            } else {
                $trumpet = SalesMoshiMx::where('companyid', $companyId)->where('moshi_id', $data['id'])->max('trumpet');
            }
            foreach ($addList as $obj) {
                $trumpet++;
                $obj['trumpet'] = $trumpet;
                $obj['moshi_id'] = $ms['id'];
                $mx = new SalesMoshiMx();
                $mx->allowField(true)->save($obj);

                $cbPrice = null;
                if (!empty($mx['kc_spot_id'])) {
                    $spot1 = KcSpot::get($mx['kc_spot_id']);
                    if (empty($spot1)) {
                        $cbPrice = null;
                    } else {
                        $cbPrice = $spot1['cb_price'];
                    }
                }
                //todo 采购明细
//            $cgmx = this . cgDaoImpl . insertMx(cg, mx . getId(), "2", mx . getGuigeId(), mx . getStoreId(), mx . getCaizhiId(), mx . getChandiId(), mx . getPinmingId(), mx . getJijiafangshiId(), mx . getChangdu(), mx . getHoudu(), mx . getKuandu(), mx . getCgShuie(), mx . getCgLingzhi(), mx . getCgJianshu(), mx . getZhijian(), mx . getCgCounts(), mx . getCgZhongliang(), mx . getCgPrice(), mx . getCgSumprice(), mx . getCgShuiprice(), mx . getCgSumShuiPrice(), mx . getFySz(), mx . getPihao(), mx . getHuohao(), mx . getBeizhu(), mx . getChehao(), mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8(), mx . getMizhong(), mx . getJianzhong());
                $spot = ['id' => null];//fixme 删掉
                //todo 入库明细
//            TbKcSpot spot = this . rkDaoImpl . insertRkMxMd(rk, cgmx . getId(), "4", ms . getYwTime(), cg . getSystemNumber(), null, cg . getCustomerId(), mx . getPinmingId(), mx . getGuigeId(), mx . getCaizhiId(), mx . getChandiId(), mx . getJijiafangshiId(), mx . getStoreId(), mx . getPihao(), mx . getHuohao(), mx . getChehao(), mx . getGgBm(), mx . getBeizhu(), ms . getCgPiaojuId(), mx . getHoudu(), mx . getKuandu(), mx . getChangdu(), mx . getZhijian(), mx . getCgLingzhi(), mx . getCgJianshu(), mx . getCgCounts(), mx . getCgZhongliang(), mx . getCgPrice(), mx . getCgSumprice(), mx . getCgShuiprice(), mx . getCgSumShuiPrice(), mx . getCgSumShuiPrice() . subtract(mx . getSumprice()), mx . getMizhong(), mx . getJianzhong(), cbPrice, null, su, user, zhangtao, jigou, mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8());

                $xsmx = (new \app\admin\model\Salesorder())->insertMx($sale, $mx['id'], 2, $mx['guige_id'], $mx['caizhi'],
                    $mx['chandi'], $mx['store_id'], $mx['jijiafangshi_id'], $mx['houdu'], $mx['kuandu'], $mx['changdu'], $mx['lingzhi'],
                    $mx['jianshu'], $mx['zhijian'], $mx['counts'], $mx['zhongliang'], $mx['price'], $mx['sumprice'], $mx['tax_rate'], $mx['tax_and_price'],
                    $mx['pihao'], $mx['beizhu'], $mx['chehao'], $mx['tax'], $companyId, $this->getAccountId());

                (new StockOut())->insertCkMxMd($ck, $spot['id'], $xsmx['id'], 4, $ms['yw_time'], $sale['system_no'],
                    $sale['custom_id'], $mx['guige_id'], $mx['caizhi'], $mx['chandi'], $mx['jijiafangshi_id'], $mx['store_id'],
                    $mx['houdu'], $mx['kuandu'], $mx['changdu'], $mx['zhijian'], $mx['lingzhi'], $mx['jianshu'], $mx['counts'],
                    $mx['zhongliang'], $mx['price'], $mx['sumprice'], $mx['tax_rate'], $mx['tax_and_price'], $mx['tax'], $mx['mizhong'],
                    $mx['jianzhong'], $cbPrice, null, $this->getAccountId(), $companyId);

                (new \app\admin\model\Inv())->insertInv($xsmx['id'], 3, 1, $xsmx['length'], $xsmx['houdu'],
                    $xsmx['width'], $xsmx['wuzi_id'], $xsmx['jsfs_id'], $sale['pjlx'], null, $sale['system_no'] . '.' . $xsmx['trumpet'],
                    $sale['custom_id'], $sale['ywsj'], $xsmx['price'], $xsmx['tax_rate'], $xsmx['total_fee'], $xsmx['price_and_tax'], $xsmx['weight'], $companyId);
                //todo 采购发票
//            this . invDaoImpl . insertInv(cgmx . getId(), "2", "2", cgmx . getChangdu(), cgmx . getHoudu(), cgmx . getKuandu(), cgmx . getGuigeId(), cgmx . getJijiafangshiId(), cg . getPiaojuId(), cgmx . getPinmingId(), cg . getSystemNumber() + "." + cgmx . getTrumpet(), beizhu, cg . getCustomerId(), cg . getYwTime(), cgmx . getPrice(), cgmx . getShuiPrice(), cgmx . getSumprice(), cgmx . getSumShuiPrice(), cgmx . getZhongliang(), cg . getGroupId(), user, jigou, zhangtao, su, mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8());
            }

            (new CapitalFy())->fymxSave($data['other'], $data['deleteOtherIds'], $sale['id'], $sale['ywsj'], 1, $ms['department'], $ms['employer'], null, $this->getAccountId(), $companyId);

            $mxList = SalesorderDetails::where('xs_sale_id', $sale['id'])->select();
            if (!empty($mxList)) {
                foreach ($mxList as $mx) {

                    $spot = KcSpot::get($mx['kc_spot_id']);
                    if (!empty($spot) && !empty($spot['cb_price'])) {
                        $mdList = StockOutMd::where('kc_spot_id', $spot['id'])->select();
                        if (!empty($mdList)) {
                            foreach ($mdList as $md) {
//                            TbKcCkMd ckMd = new TbKcCkMd();
//                            ckMd . setId(md . getId());
//                            ckMd . setCbPrice(spot . getCbPrice());
                                $md->cb_price = $spot['cb_price'];
                                $jjfs = Jsfs::where('id', $mx['jsfs_id'])->cache(true, 60)->value('jj_type');
                                if ($jjfs == 1 || $jjfs == 2) {
                                    $md->cb_sum_shuiprice = $md->cb_price * $md->zhongliang;
                                } elseif ($jjfs == 3) {
                                    $md->cb_sum_shuiprice = $md->cb_price * $md->counts;
                                }
//                            ckMd . setCbSumPrice(WuziUtil . calSumPrice(ckMd . getCbSumShuiPrice(), md . getShuiprice()));
//                            ckMd . setCbShuie(WuziUtil . calShuie(ckMd . getCbSumShuiPrice(), md . getShuiprice()));
//                            ckMd . setFySz(ckMd . getCbSumShuiPrice() . subtract(md . getSumShuiPrice()));
                                $md->save();
                            }
                        }
                    }
                }
            }
            //todo 采购单
//        $sumMoney = CgPurchaseMx::where('purchase_id', $cg['id'])->sum('sum_shui_price');
//        $sumZhongliang = CgPurchaseMx::where('purchase_id', $cg['id'])->sum('zhongliang');

            $sumMoney1 = SalesorderDetails::where('order_id', $sale['id'])->sum('price_and_tax');
            $sumZhongliang1 = SalesorderDetails::where('order_id', $sale['id'])->sum('weight');
            if (empty($data['id'])) {
                //todo 采购单货款
//            (new \app\admin\model\CapitalHk())->insertHk($cg['id'],11,$cg['system_number'],$cg['beizhu'])
//            this . hkDaoImpl . insertHk(cg . getId(), "11", cg . getSystemNumber(), cg . getBeizhu(), cg . getCustomerId(), "2", cg . getYwTime(), cg . getJiesuanId(), cg . getPiaojuId(), sumMoney, sumZhongliang, cg . getGroupId(), user, jigou, zhangtao, su, cg . getSaleOperateId());
                (new \app\admin\model\CapitalHk())->insertHk($sale['id'], 12, $sale['system_no'], $sale['remark'], $sale['custom_id'], 1, $sale['ywsj'], $sale['jsfs'], $sale['pjlx'], $sumMoney1, $sumZhongliang1, $sale['department'], $sale['employer'], $this->getAccountId(), $companyId);
            } else {
                throw new Exception('采购直发单禁止修改');
//            this . hkDaoImpl . updateHk(cg . getId(), "11", cg . getBeizhu(), cg . getCustomerId(), cg . getYwTime(), cg . getJiesuanId(), cg . getPiaojuId(), sumMoney, sumZhongliang, cg . getGroupId(), cg . getSaleOperateId());
//            this . hkDaoImpl . updateHk(sale . getId(), "12", sale . getBeizhu(), sale . getCustomerId(), sale . getYwTime(), sale . getJiesuanId(), sale . getPiaojuId(), sumMoney1, sumZhongliang1, sale . getGroupId(), sale . getSaleOperatorId());
            }

            Db::commit();
            return returnSuc(['id' => $ms['id']]);
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**
     * 作废
     * @param Request $request
     * @param int $id
     * @return Json
     * @throws DbException
     */
    public function cancel(Request $request, $id = 0)
    {
        if ($request->isPost()) {
            $cgzfd = SalesMoshi::get($id);
            if (empty($cgzfd)) {
                return returnFail('数据不存在');
            }
            if ($cgzfd->status == 3) {
                return returnFail('此单已审核，无法作废');
            }
            if ($cgzfd->status == 2) {
                return returnFail('此单已作废');
            }
            Db::startTrans();
            try {
                $cgzfd->status = 2;
                $cgzfd->save();
                (new Salesorder())->cancel($request, $id, 2, false);

                //todo 作废采购单
                Db::commit();
                return returnSuc();
            } catch (Exception $e) {
                Db::rollback();
                return returnFail($e->getMessage());
            }
        }
        return returnFail('请求方式错误');
    }
}