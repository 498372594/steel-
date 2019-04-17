<?php

namespace app\admin\controller;

use app\admin\model\{CapitalFy, Jsfs, KcSpot, SalesMoshi, SalesMoshiMx, SalesorderDetails, StockOut, StockOutMd};
use app\admin\validate\{SalesMoshiDetails};
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    Request,
    response\Json};

class SalesTiaohuo extends Right
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
        $list = SalesMoshi::with(['custom', 'khpjData', 'khjsfsData'])
            ->where('companyid', $this->getCompanyId())
            ->order('create_time', 'desc')
            ->where('moshi_type', 2);
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('yw_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['status'])) {
            $list->where('status', $params['status']);
        }
        if (!empty($params['custom_id'])) {
            $list->where('customer_id', $params['custom_id']);
        }
        if (!empty($params['pjlx'])) {
            $list->where('piaoju_id', $params['pjlx']);
        }
        if (!empty($params['system_no'])) {
            $list->where('system_number', 'like', '%' . $params['system_no'] . '%');
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
            'khpjData',
            'khjsfsData',
            'details' => ['specification', 'cgJsfsData', 'cgPjData', 'storage', 'jsfs', 'wldwData'],
            'other' => ['other' => ['mingxi' => ['szmcData', 'pjlxData', 'custom']]]
        ])
            ->where('companyid', $this->getCompanyId())
            ->where('moshi_type', 2)
            ->where('id', $id)
            ->find();
        if (empty($data)) {
            return returnFail('数据不存在');
        } else {
            return returnRes(true, '', $data);
        }
    }

    /**
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
            //验证数据
            $validate = new \app\admin\validate\SalesMoshi();
            if (!$validate->scene('tiaohuo')->check($data)) {
                return returnFail($validate->getError());
            }
            $addList = [];
            $updateList = [];
            $ja = $data['details'];
            $companyId = $this->getCompanyId();
            if (!empty($ja)) {
                $detailsValidate = new SalesMoshiDetails();
                $num = 1;
                foreach ($ja as $object) {
                    if (!$detailsValidate->scene('tiaohuo')->check($object)) {
                        throw new Exception('请检查第' . $num . '行' . $detailsValidate->getError());
                    }
                    $num++;
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
                $count = SalesMoshi::withTrashed()->where('moshi_type', 1)
                    ->where('create_time', 'today')
                    ->where('companyid', $companyId)
                    ->count();
                $data['moshi_type'] = 1;
                $data['create_operator_id'] = $this->getAccountId();
                $data['system_number'] = "THXSD" . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
                $data['companyid'] = $companyId;

                $ms = new SalesMoshi();
                $ms->allowField(true)->data($data)->save();

                $xs = (new \app\admin\model\Salesorder())->insertSale($ms['id'], 1, $ms['yw_time'], $ms['customer_id'],
                    $ms['piaoju_id'], $ms['jsfs'], $ms['remark'], $ms['department'], $ms['employer'], $ms['contact'], $ms['mobile'], $ms['chehao'], $this->getAccountId(), $companyId);
                $ck = (new StockOut())->insertChuku($xs['id'], 4, $ms['yw_time'], $ms['department'], $ms['system_number'], $ms['employer'], $this->getAccountId(), $companyId);

            } else {
                throw new Exception('调货销售单禁止修改');
//            ms = (TbMoshi) this . moshiDao . selectByPrimaryKey(pid);
//            if (ms == null) {
//                throw new ValidateException("对象不存在");
//            }
//            if (!user . getId() . equals(ms . getUserId())) {
//                throw new ValidateException("对象不存在");
//            }
//            if ("1" . equals(ms . getStatus())) {
//                throw new ValidateException("该单据已经作废");
//            }
//            ms . setCustomerId(customerId);
//            ms . setPiaojuId(piaojuId);
//            ms . setJiesuanId(jiesuanId);
//            ms . setLxr(lxr);
//            ms . setTelephone(telephone);
//            ms . setBeizhu(beizhu);
//            ms . setYwTime(DateUtil . parseDate(ywTime, "yyyy-MM-dd HH:mm:ss"));
//            ms . setGroupId(groupId);
//            ms . setSaleOperatorId(saleOperatorId);
//            ms . setUpdateOperatorId(su . getId());
//            ms . setShouHuoDanWei(shouHuoDanWei);
//            ms . setChengyunfang(chengyunfangId);
//            this . moshiDao . updateByPrimaryKeySelective(ms);
//            xs = this . xsDaoImpl . updateSale(ms . getId(), "1", ms . getYwTime(), chehao, customerId, piaojuId, jiesuanId, groupId, saleOperatorId, lxr, telephone);
//            String xsId = this . saleDAO . findXsIdByMoshiId(ms . getId(), user . getId(), jigou . getId(), zhangtao . getId());
//            TbXsSale xsSale = (TbXsSale) this . saleDAO . selectByPrimaryKey(xsId);
//            xsSale . setFaxiId(fxlx);
//            this . saleDAO . updateByPrimaryKeySelective(xsSale);
//            ck = this . ckDaoImpl . updateChuku(xs . getId(), "4", ms . getYwTime(), ms . getCustomerId(), ms . getGroupId(), ms . getSaleOperatorId(), chehao);
//
//
//            Example eMsMx = new Example(TbMoshiMx .class);
//            eMsMx . createCriteria() . andCondition("moshi_id=", ms . getId());
//            List<TbMoshiMx > mxList = this . mxDao . selectByExample(eMsMx);
//            if (mxList . size() != 0) {
//                for (TbMoshiMx msMx : mxList) {
//                    Example ecg = new Example(TbCgPurchase .class);
//                    ecg . createCriteria() . andCondition("data_id=", msMx . getId());
//                    List<TbCgPurchase > cgList = this . cgDao . selectByExample(ecg);
//                    if (cgList . size() != 0) {
//                        for (TbCgPurchase tbcg : cgList) {
//                            TbCgPurchase cgpu = new TbCgPurchase();
//                            cgpu . setId(tbcg . getId());
//                            cgpu . setYwTime(ms . getYwTime());
//                            cgpu . setPiaojuId(msMx . getCgPiaoJuId());
//                            this . cgDao . updateByPrimaryKeySelective(cgpu);
//                            Example eRk = new Example(TbKcRk .class);
//                            eRk . createCriteria() . andCondition("data_id=", cgpu . getId());
//                            List<TbKcRk > rkList = this . rkDao . selectByExample(eRk);
//                            if (rkList . size() != 0) {
//                                for (TbKcRk tbKcRk : rkList) {
//                                    TbKcRk rk = new TbKcRk();
//                                    rk . setId(tbKcRk . getId());
//                                    rk . setYwTime(ms . getYwTime());
//                                    this . rkDao . updateByPrimaryKeySelective(rk);
//                                }
//                            }
//                        }
//                    }
//
//                    Example eXsMx = new Example(TbXsSaleMx .class);
//                    eXsMx . createCriteria() . andCondition("data_id=", msMx . getId());
//                    List<TbXsSaleMx > xsMxList = this . xsMxDao . selectByExample(eXsMx);
//                    if (xsMxList . size() != 0) {
//                        for (TbXsSaleMx tbXsSaleMx : xsMxList) {
//                            Example eInv = new Example(TbInv .class);
//                            eInv . createCriteria() . andCondition("data_id=", tbXsSaleMx . getId());
//                            List<TbInv > invList = this . invDao . selectByExample(eInv);
//                            if (invList . size() != 0) {
//                                for (TbInv tbInv : invList) {
//                                    TbInv inv = new TbInv();
//                                    inv . setId(tbInv . getId());
//                                    inv . setYwTime(ms . getYwTime());
//                                    this . invDao . updateByPrimaryKeySelective(inv);
//                                }
//                            }
//                        }
//                    }
//                }
//            }
            }

            if (!empty($data['deleteMxIds']) || !empty($updateList)) {
                throw new Exception('调货销售单禁止修改');
//            for (TbMoshiMx_Ex mjo : deleteList) {
//                TbMoshiMx mx = new TbMoshiMx();
//            mx . setId(mjo . getId());
//            TbMoshiMx tbMx = (TbMoshiMx) this . mxDao . selectByPrimaryKey(mjo . getId());
//            BigDecimal money = tbMx . getCgSumShuiPrice();
//            BigDecimal zhongliang = tbMx . getCgZhongliang();
//
//            Example eCgMx = new Example(TbCgPurchaseMx .class);
//            eCgMx . createCriteria() . andCondition("data_id=", tbMx . getId());
//            List<TbCgPurchaseMx > mxList = this . cgMxDao . selectByExample(eCgMx);
//
//
//            if (mxList . size() != 0) {
//                for (TbCgPurchaseMx obj : mxList) {
//                    TbCgPurchaseMx tbCgMx = (TbCgPurchaseMx) this . cgMxDao . selectByPrimaryKey(obj . getId());
//                    this . hkDaoImpl . subByDelHk(tbCgMx . getPurchaseId(), "11", money, zhongliang);
//                    Example eHk = new Example(TbCapitalHk .class);
//                    eHk . createCriteria() . andCondition("data_id=", tbCgMx . getPurchaseId());
//                    List<TbCapitalHk > hkList = this . hkDao . selectByExample(eHk);
//                    TbCapitalHk tbHk = (TbCapitalHk) hkList . get(0);
//                    if ((tbHk . getMoney() . compareTo(new BigDecimal(0)) == 0) && (tbHk . getZhongliang() . compareTo(new BigDecimal(0)) == 0)) {
//                        this . hkDao . deleteByPrimaryKey(tbHk);
//                    }
//                }
//            }
//
//            TbXsSaleMx salemx = this . xsDaoImpl . deleteMx(mx . getId(), "1");
//
//            this . ckDaoImpl . deleteCkMxMd(salemx . getId(), "4");
//
//
//            cgmx = this . cgDaoImpl . deleteCgmxForTh(mx . getId(), "1");
//
//
//            this . rkDaoImpl . deleteRkMxMdForTh(cgmx . getId(), "4");
//
//
//            this . invDaoImpl . deleteInv(salemx . getId(), "3");
//
//            this . invDaoImpl . deleteInv(cgmx . getId(), "2");
//
//            this . mxDao . deleteByPrimaryKey(mx);
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
//
//            BigDecimal xgWeight = eduMx . getZhongliang();
//            this . awDaoImpl . availableTody(saleOperatorId, ywTime . substring(0, 10), obj . getZhongliang(), xgWeight, user, jigou, zhangtao);
//            BigDecimal xgJine = eduMx . getSumShuiPrice();
//
//            this . smsDaoImpl . compYskByOperatorId(customerId, saleOperatorId, user, jigou, obj . getSumShuiPrice(), xgJine, zhangtao);
//            BigDecimal yuanMoney = eduMx . getCgSumShuiPrice();
//            BigDecimal yuanZhongliang = eduMx . getCgZhongliang();
//
//
//            mx . setMoshiId(ms . getId());
//            mx . setId(obj . getId());
//            mx . setStoreId(obj . getStoreId());
//            mx . setCgCustomerId(obj . getCgCustomerId());
//            mx . setPinmingId(obj . getPinmingId());
//            mx . setGuigeId(obj . getGuigeId());
//            mx . setGgBm(obj . getGgBm());
//            mx . setCaizhiId(obj . getCaizhiId());
//            mx . setChandiId(obj . getChandiId());
//            mx . setHoudu(obj . getHoudu());
//            mx . setKuandu(obj . getKuandu());
//            mx . setChangdu(obj . getChangdu());
//            mx . setJijiafangshiId(obj . getJijiafangshiId());
//            mx . setCgJijiafangshiId(obj . getCgJijiafangshiId());
//            mx . setLingzhi(obj . getLingzhi());
//            mx . setJianshu(obj . getJianshu());
//            mx . setZhijian(obj . getZhijian());
//            mx . setCounts(obj . getCounts());
//            mx . setCgPiaoJuId(obj . getCgPiaoJuId());
//            mx . setCgZhongliang(obj . getCgZhongliang());
//            mx . setCgPrice(obj . getCgPrice());
//            mx . setCgSumprice(obj . getCgSumprice());
//
//            mx . setCgSumShuiPrice(obj . getCgSumShuiPrice());
//            mx . setZhongliang(obj . getZhongliang());
//            mx . setPrice(obj . getPrice());
//            mx . setSumprice(obj . getSumprice());
//
//            mx . setSumShuiPrice(obj . getSumShuiPrice());
//            mx . setFySz(obj . getFySz());
//            mx . setBeizhu(obj . getBeizhu());
//            mx . setPihao(obj . getPihao());
//            mx . setChehao(obj . getChehao());
//            mx . setIsDelete("0");
//
//            mx . setShuiprice(obj . getShuiprice());
//            mx . setCgShuiprice(obj . getCgShuiprice());
//
//            mx . setShuie(obj . getShuie());
//            mx . setCgShuie(obj . getCgShuie());
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
//
//            cg = this . cgDaoImpl . updateCaigou(mx . getId(), "1", xs . getYwTime(), mx . getCgCustomerId(), mx . getCgPiaoJuId(), null, beizhu, groupId, su, saleOperatorId);
//
//            if (cg != null) {
//                this . rkDaoImpl . updateRuku(cg . getId(), "4", null, xs . getYwTime(), mx . getCgCustomerId(), cg . getGroupId(), cg . getSaleOperateId());
//            }
//
//            cgmx = this . cgDaoImpl . updateMx(mx . getId(), "1", mx . getGuigeId(), mx . getStoreId(), mx . getCaizhiId(), mx . getChandiId(), mx . getPinmingId(), mx . getCgJijiafangshiId(), mx . getChangdu(), mx . getHoudu(), mx . getKuandu(), mx . getLingzhi(), mx . getJianshu(), mx . getZhijian(), mx . getCounts(), mx . getCgZhongliang(), mx . getCgPrice(), mx . getCgSumprice(), mx . getCgShuiprice(), mx . getCgSumShuiPrice(), mx . getCgShuie(), mx . getFySz(), mx . getPihao(), mx . getHuohao(), mx . getBeizhu(), mx . getChehao(), mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8(), mx . getMizhong(), mx . getJianzhong());
//
//
//            TbKcSpot spot = (TbKcSpot) this . spotDao . selectByPrimaryKey(mx . getKcSpotId());
//            BigDecimal cbPrice = null;
//            if (spot == null) {
//                cbPrice = null;
//            } else {
//                cbPrice = spot . getCbPrice();
//            }
//            this . rkDaoImpl . updateRkMxMd(cgmx . getId(), "4", mx . getChangdu(), mx . getHoudu(), mx . getKuandu(), mx . getLingzhi(), mx . getJianshu(), mx . getCounts(), mx . getCgZhongliang(), mx . getZhijian(), mx . getCgCustomerId(), mx . getPinmingId(), mx . getGuigeId(), mx . getCaizhiId(), mx . getChandiId(), mx . getCgJijiafangshiId(), mx . getStoreId(), mx . getPihao(), mx . getHuohao(), mx . getChehao(), mx . getGgBm(), mx . getBeizhu(), mx . getCgPiaoJuId(), mx . getCgPrice(), mx . getCgSumprice(), mx . getShuiprice(), mx . getCgSumShuiPrice(), mx . getCgShuie(), mx . getMizhong(), mx . getJianzhong(), cbPrice, null, mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8(), zhangtao);
//
//
//            saleMx = this . xsDaoImpl . updateMx(mx . getId(), "1", mx . getGuigeId(), mx . getGgBm(), mx . getCaizhiId(), mx . getChandiId(), mx . getStoreId(), mx . getJijiafangshiId(), mx . getPinmingId(), mx . getHoudu(), mx . getKuandu(), mx . getChangdu(), mx . getLingzhi(), mx . getJianshu(), mx . getZhijian(), mx . getCounts(), mx . getZhongliang(), mx . getPrice(), mx . getSumprice(), mx . getShuiprice(), mx . getSumShuiPrice(), mx . getShuie(), mx . getFySz(), mx . getPihao(), mx . getHuohao(), mx . getBeizhu(), mx . getChehao(), mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8(), mx . getMizhong(), mx . getJianzhong());
//
//
//            ckMx = this . ckDaoImpl . updateCkMx(saleMx . getId(), "4", ms . getYwTime(), mx . getChangdu(), mx . getHoudu(), mx . getKuandu(), mx . getLingzhi(), mx . getJianshu(), mx . getCounts(), mx . getZhongliang(), mx . getZhijian(), mx . getPrice(), mx . getShuiprice(), mx . getSumprice(), mx . getSumShuiPrice(), mx . getMizhong(), mx . getJianzhong(), mx . getStoreId(), mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8(), mx . getJijiafangshiId());
//
//
//            this . ckDaoImpl . updateCkMd(ckMx . getId(), "4", ms . getYwTime(), mx . getChangdu(), mx . getHoudu(), mx . getKuandu(), mx . getLingzhi(), mx . getJianshu(), mx . getCounts(), mx . getCgZhongliang(), mx . getZhijian(), mx . getCgPrice(), mx . getCgShuiprice(), mx . getCgSumprice(), mx . getCgSumShuiPrice(), mx . getMizhong(), mx . getJianzhong(), mx . getStoreId(), mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8(), cbPrice, mx . getCgJijiafangshiId());
//
//
//            this . invDaoImpl . updateInv(saleMx . getId(), "3", null, xs . getCustomerId(), xs . getYwTime(), saleMx . getChangdu(), saleMx . getKuandu(), saleMx . getHoudu(), saleMx . getGuigeId(), saleMx . getJijiafangshiId(), xs . getPiaojuId(), saleMx . getPinmingId(), saleMx . getZhongliang(), saleMx . getPrice(), saleMx . getSumprice(), saleMx . getSumShuiPrice(), saleMx . getShuiprice(), mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8());
//
//
//            if (cgmx != null) {
//                TbCgPurchaseMx tbCgmx = (TbCgPurchaseMx) this . cgMxDao . selectByPrimaryKey(cgmx . getId());
//                TbCgPurchase tbCg = (TbCgPurchase) this . cgDao . selectByPrimaryKey(tbCgmx . getPurchaseId());
//                this . invDaoImpl . updateInv(cgmx . getId(), "2", null, tbCg . getCustomerId(), tbCg . getYwTime(), cgmx . getChangdu(), cgmx . getKuandu(), cgmx . getHoudu(), cgmx . getGuigeId(), cgmx . getJijiafangshiId(), tbCg . getPiaojuId(), cgmx . getPinmingId(), cgmx . getZhongliang(), cgmx . getPrice(), cgmx . getSumprice(), cgmx . getSumShuiPrice(), cgmx . getShuiPrice(), mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8());
//
//
//                this . hkDaoImpl . subHk(tbCg . getId(), "11", tbCg . getBeizhu(), tbCg . getCustomerId(), tbCg . getYwTime(), tbCg . getJiesuanId(), tbCg . getPiaojuId(), yuanMoney, cgmx . getSumShuiPrice(), yuanZhongliang, cgmx . getZhongliang(), tbCg . getGroupId());
//            }
//
//
//            Integer isStartFaxi = this . fxDaoImpl . isStartFaxi(user, zhangtao);
//            if (isStartFaxi . intValue() == 1) {
//                this . fxDaoImpl . setFaxiForQt(fxlx, customerId, jiesuanId, groupId, saleOperatorId, createOperatorId, jigou, user, zhangtao, xs);
//            }
//        }
            }

            if (empty($data['id'])) {
                $trumpet = 0;
            } else {
                $trumpet = SalesMoshiMx::where('moshi_id', $data['id'])->max('trumpet');
            }

            foreach ($addList as $obj) {
                $trumpet++;
                $obj['moshi_id'] = $ms['id'];
                $obj['trumpet'] = $trumpet;
                $mx = new SalesMoshiMx();
                $mx->allowField(true)->data($obj)->save();

                if (!empty($mx['kc_spot_id'])) {
                    $spot1 = KcSpot::get($mx['kc_spot_id']);
                }
                if (empty($spot1)) {
                    $cbPrice = null;
                } else {
                    $cbPrice = $spot1['cb_price'];
                }

                //todo 采购入库相关
//            Integer cgScCounts = this . cgDao . findCgScCountsByMsMxId(ms . getId(), mx . getCgCustomerId(), "1", mx . getCgPiaoJuId());
//            if (cgScCounts . intValue() == 0) {
//                cg = this . cgDaoImpl . insertCaigou(mx . getId(), "1", xs . getYwTime(), mx . getCgCustomerId(), null, "1", mx . getCgPiaoJuId(), beizhu, groupId, saleOperatorId, user, su, jigou, zhangtao);
//            } else {
//                String caigouId = this . cgDao . findCgIdByMsMxId(ms . getId(), mx . getCgCustomerId(), "1", mx . getCgPiaoJuId());
//                cg = (TbCgPurchase) this . cgDao . selectByPrimaryKey(caigouId);
//            }
//            cgmx = this . cgDaoImpl . insertMx(cg, mx . getId(), "1", mx . getGuigeId(), mx . getStoreId(), mx . getCaizhiId(), mx . getChandiId(), mx . getPinmingId(), mx . getCgJijiafangshiId(), mx . getChangdu(), mx . getHoudu(), mx . getKuandu(), mx . getCgShuie(), mx . getLingzhi(), mx . getJianshu(), mx . getZhijian(), mx . getCounts(), mx . getCgZhongliang(), mx . getCgPrice(), mx . getCgSumprice(), mx . getCgShuiprice(), mx . getCgSumShuiPrice(), mx . getFySz(), mx . getPihao(), mx . getHuohao(), mx . getBeizhu(), mx . getChehao(), mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8(), mx . getMizhong(), mx . getJianzhong());
//
//
//            TbKcRk rk = this . rkDaoImpl . insertRuku(cg . getId(), "4", cg . getSystemNumber(), xs . getYwTime(), groupId, user, jigou, zhangtao, su, saleOperatorId);
//            String cgmxDataNumber = null;
                $spot = ['id' => null];//fixme 删除
//            TbKcSpot spot = this . rkDaoImpl . insertRkMxMd(rk, cgmx . getId(), "4", xs . getYwTime(), cg . getSystemNumber(), cgmxDataNumber, mx . getCgCustomerId(), mx . getPinmingId(), mx . getGuigeId(), mx . getCaizhiId(), mx . getChandiId(), mx . getCgJijiafangshiId(), mx . getStoreId(), mx . getPihao(), mx . getHuohao(), mx . getChehao(), mx . getGgBm(), mx . getBeizhu(), mx . getCgPiaoJuId(), mx . getHoudu(), mx . getKuandu(), mx . getChangdu(), mx . getZhijian(), mx . getLingzhi(), mx . getJianshu(), mx . getCounts(), mx . getCgZhongliang(), mx . getCgPrice(), mx . getCgSumprice(), mx . getCgShuiprice(), mx . getCgSumShuiPrice(), mx . getCgSumShuiPrice() . subtract(mx . getCgSumprice()), mx . getMizhong(), mx . getJianzhong(), cbPrice, null, su, user, zhangtao, jigou, mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8());


                $saleMx = (new \app\admin\model\Salesorder())->insertMx($xs, $mx['id'], 1, $mx['guige_id'], $mx['caizhi'],
                    $mx['chandi'], $mx['store_id'], $mx['jijiafangshi_id'], $mx['houdu'], $mx['kuandu'], $mx['changdu'], $mx['lingzhi'],
                    $mx['jianshu'], $mx['zhijian'], $mx['counts'], $mx['zhongliang'], $mx['price'], $mx['sumprice'], $mx['tax_rate'], $mx['tax_and_price'],
                    $mx['pihao'], $mx['beizhu'], $mx['chehao'], $mx['tax'], $companyId);

                (new StockOut())->insertCkMxMd($ck, $spot['id'], $saleMx['id'], 4, $ms['yw_time'], $xs['system_no'],
                    $xs['custom_id'], $mx['guige_id'], $mx['caizhi'], $mx['chandi'], $mx['jijiafangshi_id'], $mx['store_id'],
                    $mx['houdu'], $mx['kuandu'], $mx['changdu'], $mx['zhijian'], $mx['lingzhi'], $mx['jianshu'], $mx['counts'],
                    $mx['zhongliang'], $mx['price'], $mx['sumprice'], $mx['tax_rate'], $mx['tax_and_price'], $mx['tax'], $mx['mizhong'],
                    $mx['jianzhong'], $cbPrice, null, $this->getAccountId(), $companyId);

                (new \app\admin\model\Inv())->insertInv($saleMx['id'], 3, 1, $saleMx['length'], $saleMx['houdu'],
                    $saleMx['width'], $saleMx['wuzi_id'], $saleMx['jsfs_id'], $xs['pjlx'], null, $xs['system_no'] . '.' . $saleMx['trumpet'],
                    $xs['custom_id'], $xs['ywsj'], $saleMx['price'], $saleMx['tax_rate'], $saleMx['total_fee'], $saleMx['price_and_tax'], $saleMx['weight'], $companyId);

                //todo 采购发票
//            this . invDaoImpl . insertInv(cgmx . getId(), "2", "2", cgmx . getChangdu(), cgmx . getHoudu(), cgmx . getKuandu(), cgmx . getGuigeId(), cgmx . getJijiafangshiId(), cg . getPiaojuId(), cgmx . getPinmingId(), cg . getSystemNumber() + "." + cgmx . getTrumpet(), beizhu, cg . getCustomerId(), cg . getYwTime(), cgmx . getPrice(), cgmx . getShuiPrice(), cgmx . getSumprice(), cgmx . getSumShuiPrice(), cgmx . getZhongliang(), cg . getGroupId(), user, jigou, zhangtao, su, mx . getExt1(), mx . getExt2(), mx . getExt3(), mx . getExt4(), mx . getExt5(), mx . getExt6(), mx . getExt7(), mx . getExt8());

                //todo 采购相关
//            if (cgScCounts . intValue() == 0) {
//                this . hkDaoImpl . insertHk(cg . getId(), "11", cg . getSystemNumber(), cg . getBeizhu(), cg . getCustomerId(), "2", cg . getYwTime(), cg . getJiesuanId(), cg . getPiaojuId(), cgmx . getSumShuiPrice(), cgmx . getZhongliang(), cg . getGroupId(), user, jigou, zhangtao, su, cg . getSaleOperateId());
//            } else {
//                this . hkDaoImpl . addHk(cg . getId(), "11", cg . getBeizhu(), cg . getCustomerId(), cg . getYwTime(), cg . getJiesuanId(), cg . getPiaojuId(), cgmx . getSumShuiPrice(), cgmx . getZhongliang(), cg . getGroupId());
//            }
            }

            (new CapitalFy())->fymxSave($data['other'], $data['deleteOtherIds'], $xs['id'], $xs['ywsj'], 1,
                $ms['department'], $ms['employer'], null, $this->getAccountId(), $companyId);


            $mxList = SalesorderDetails::where('order_id', $xs['id'])->select();
            if (!empty($mxList)) {
                foreach ($mxList as $mx) {

                    $mdList = StockOutMd::where('data_id', $mx['id'])->select();
                    if (!empty($mdList)) {
                        foreach ($mdList as $md) {
                            $md->cb_price = $md->price;
                            $jjfs = Jsfs::where('id', $mx['jijiafangshi_id'])->cache(true, 60)->find();
                            if ($jjfs == 1 || $jjfs == 2) {
                                $md->cb_sum_shuiprice = $md->cb_price * $md->zhongliang;
                            } elseif ($jjfs == 3) {
                                $md->cb_sum_shuiprice = $md->cb_price * $md->counts;
                            }
//                        ckMd . setCbSumPrice(WuziUtil . calSumPrice(ckMd . getCbSumShuiPrice(), md . getShuiprice()));
//                        ckMd . setCbShuie(WuziUtil . calShuie(ckMd . getCbSumShuiPrice(), md . getShuiprice()));
//                        ckMd . setFySz(ckMd . getCbSumShuiPrice() . subtract(md . getSumShuiPrice()));
                            $md->save();
                        }
                    }
                }
            }

            $sumMoney1 = SalesorderDetails::where('order_id', $xs['id'])->sum('price_and_tax');
            $sumZhongliang1 = SalesorderDetails::where('order_id', $xs['id'])->sum('weight');
            if (empty($data['id'])) {
                (new \app\admin\model\CapitalHk())->insertHk($xs['id'], 12, $xs['system_no'], $xs['remark'], $xs['custom_id'],
                    1, $xs['ywsj'], $xs['jsfs'], $xs['pjlx'], $sumMoney1, $sumZhongliang1, $xs['department'], $xs['employer'], $this->getAccountId(), $companyId);
            } else {
                throw new Exception('调货销售单禁止修改');
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
                (new Salesorder())->cancel($request, $id, 3, false);

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