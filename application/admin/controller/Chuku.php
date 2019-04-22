<?php

namespace app\admin\controller;

use app\admin\model\{Jsfs, KcSpot, KucunCktz, StockOut, StockOutDetail, StockOutMd};
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    Request,
    response\Json};

class Chuku extends Right
{
    /**
     * 添加出库通知单
     * @param array $data
     * @throws Exception
     */
    public function addNotify($data = [])
    {
        if (empty($data)) {
            return;
        }
        (new KucunCktz())->allowField(true)->saveAll($data);
    }

    /**
     * 获取出库通知单列表
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function getNotifyList(Request $request, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $params = $request->param();
        $list = KucunCktz::with([
            'adder',
            'custom',
            'jsfs',
            'specification',
            'storage',
        ])->where('companyid', $this->getCompanyId());
        if (!empty($params['id'])) {
            $list->where('id', $params['id']);
        }
        if (!empty($params['ywsjStart'])) {
            $list->where('cache_ywtime', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('cache_ywtime', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['system_no'])) {
            $list->where('cache_data_pnumber', 'like', '%' . $params['system_no'] . '%');
        }
        if (!empty($params['custom_id'])) {
            $list->where('cache_customer_id', $params['custom_id']);
        }
        if (!empty($params['add_id'])) {
            $list->where('cache_create_operator', $params['add_id']);
        }
        if (!empty($params['is_done'])) {
            $list->where('is_done', $params['is_done'] - 1);
        }
        if (!empty($params['weight_gt_0'])) {
            $list->where('zhongliang', '>', 0);
        }
        $list = $list->paginate($pageLimit);
        return returnRes(true, '', $list);
    }

    /**
     * 出库通知标记为完成
     * @param Request $request
     * @param $id
     * @return Json
     * @throws DbException
     */
    public function doneNotify(Request $request, $id = 0)
    {
        if (!$request->isPut()) {
            return returnFail('请求方式错误');
        }
        $data = KucunCktz::get($id);
        if (empty($data)) {
            return returnFail('数据不存在');
        }
        if ($data->is_done == 1) {
            return returnFail('该记录已完成');
        }
        $data->is_done = 1;
        $data->save();
        return returnSuc();
    }

    /**
     * 获取出库单列表
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
        $list = StockOut::where('companyid', $this->getCompanyId())
            ->order('create_time', 'desc');
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('yw_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['status'])) {
            $list->where('status', $params['status']);
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

    public function add(Request $request)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }

        Db::startTrans();
        try {
            $data = $request->post();

            $validate = new \app\admin\validate\StockOut();
            if (!$validate->check($data)) {
                throw new Exception($validate->getError());
            }

            $addMxList = [];
            $updateMxList = [];

            $addMdList = [];
            $updateMdList = [];

            $ja = $data['ckmx'];
            $ja1 = $data['ckmd'];

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
            if (!empty($ja1)) {
                foreach ($ja1 as $object) {
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

            if (empty($data['id'])) {
                $count = StockOut::whereTime('create_time', 'today')
                    ->where('companyid', $companyId)
                    ->count();

                $data['companyid'] = $companyId;
                $data['system_number'] = 'CKD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
                $data['create_operator_id'] = $this->getAccountId();
                $data['out_mode'] = 2;

                $ck = new StockOut();
                $ck->allowField(true)->data($data)->save();
            } else {
                throw new Exception('出库单禁止修改');
//            ck = (TbKcCk) getDao() . selectByPrimaryKey(id);
//            if (ck == null) {
//                throw new Exception("对象不存在");
//            }
//            if (!ck . getUserId() . equals(ck . getUserId())) {
//                throw new Exception("对象不存在");
//            }
//            if ("1" . equals(ck . getStatus())) {
//                throw new Exception("该单据已经作废");
//            }
//            if ("1" . equals(ck . getChukuFangshi())) {
//                throw new Exception("自动出库单据不允许修改");
//            }
//            ck . setBeizhu(beizhu);
//            ck . setCustomerId(customerId);
//            ck . setGroupId(group);
//            ck . setSaleOperatorId(saleOperator);
//            ck . setUpdateOperatorId(su . getId());
//            ck . setYwTime(DateUtil . parseDate(ywTime, "yyyy-MM-dd HH:mm:ss"));
//            getDao() . updateByPrimaryKeySelective(ck);
            }

            if (!empty($data['deleteMxIds']) || !empty($data['deleteMdIds'])) {
                throw new Exception('出库单禁止修改');
//            for (TbKcCkMx_Ex mx : deleteMxList) {
//                TbKcCkMx mx1 = new TbKcCkMx();
//            mx1 . setId(mx . getId());
//
//            this . mxDao . deleteByPrimaryKey(mx1);
//        }
//        for (TbKcCkMd tmd : deleteMdList) {
//            Example e = new Example(TbKcCkMd .class);
//            e . selectProperties(new String[]{
//            "id", "kcSpotId", "lingzhi", "jianshu", "jianzhishu", "counts", "zhongliang", "kcCkTzId"});
//            e . createCriteria() . andCondition("id=", tmd . getId());
//            List<TbKcCkMd > mdList = this . mdDao . selectByExample(e);
//            TbKcCkMd md = (TbKcCkMd) mdList . get(0);
//            this . spotDao . adjustSpotById(md . getKcSpotId(), true, md . getCounts(), md . getZhongliang(), md . getJijiafangshiId());
//            this . ckTzDao . addTzById(md . getKcCkTzId(), md . getCounts(), md . getZhongliang());
//            this . mdDao . deleteByPrimaryKey(md);
//        }
            }

            foreach ($addMxList as &$mx) {
                foreach ($addMdList as $md) {
                    if ($mx['kucun_cktz_id'] == $md['kucun_cktz_id']) {
                        $mx['mdList'][] = $md;
                    }
                }
            }
            unset($mx, $md);

//        TbKcCkMx_Ex mx;
//        $newMdList = [];
//        foreach ($addMdList as $md1){
//            $flag = true;
//            foreach ($addMxList as $mx){
//                if ($mx['kucun_cktz_id']==$md1['kucun_cktz_id']) {
//                    $flag = false;
//                    break;
//                }
//            }
//            if ($flag) {
//                $newMdList[]=$md1;
//            }
//        }
//
//        foreach($newMdList as $tmd){
//            TbKcCkMx mx = (TbKcCkMx) this . mxDao . selectByPrimaryKey(tmd . getChukuMxId());
//            $s=KcSpot::get($tmd['kc_spot_id']);
//
//            $tmd['stock_out_id']=$ck['id'];
////            md . setChukuMxId(tmd . getChukuMxId());
//            md . setKcSpotId(tmd . getKcSpotId());
//            md . setDataId(mx . getDataId());
//            md . setChukuType(mx . getChukuType());
//            md . setChukuFangshi("2");
//
//            md . setPinmingId(s . getPinmingId());
//            md . setCaizhiId(s . getCaizhiId());
//            md . setChandiId(s . getChandiId());
//            md . setJijiafangshiId(s . getJijiafangshiId());
//            md . setGuigeId(s . getGuigeId());
//            md . setHoudu(s . getHoudu());
//            md . setKuandu(s . getKuandu());
//            md . setChangdu(s . getChangdu());
//            md . setCounts(tmd . getCounts());
//            md . setJianshu(tmd . getJianshu());
//            md . setLingzhi(tmd . getLingzhi());
//            md . setZhijian(tmd . getZhijian());
//            md . setZhongliang(tmd . getZhongliang());
//
//            md . setPrice(s . getPrice());
//            md . setCbPrice(s . getCbPrice());
//            md . setShuiprice(s . getShuiprice());
//            md . setMizhong(mx . getMizhong());
//            md . setJianzhong(mx . getJianzhong());
//            TbBaseJijiafangshi jjfs = (TbBaseJijiafangshi) this . jjfsDao . selectByPrimaryKey(mx . getJijiafangshiId());
//            if ((jjfs . getBaseJijialeixingId() . equals(Globals . DICT_BASE_JIJIALEIXING_1)) || (jjfs . getBaseJijialeixingId() . equals(Globals . DICT_BASE_JIJIALEIXING_2))) {
//                md . setSumShuiPrice(md . getPrice() . multiply(md . getZhongliang()));
//                md . setCbSumShuiPrice(md . getCbPrice() . multiply(md . getZhongliang()));
//            } else if (jjfs . getBaseJijialeixingId() . equals(Globals . DICT_BASE_JIJIALEIXING_3)) {
//                md . setSumShuiPrice(md . getPrice() . multiply(md . getCounts()));
//                md . setCbSumShuiPrice(md . getCbPrice() . multiply(md . getCounts()));
//            }
//            md . setSumprice(WuziUtil . calSumPrice(md . getSumShuiPrice(), md . getShuiprice()));
//            md . setShuie(WuziUtil . calShuie(md . getSumShuiPrice(), md . getShuiprice()));
//            md . setCbSumPrice(WuziUtil . calSumPrice(md . getCbSumShuiPrice(), md . getShuiprice()));
//            md . setCbShuie(WuziUtil . calShuie(md . getCbSumShuiPrice(), md . getShuiprice()));
//            md . setFySz(md . getCbSumShuiPrice() . subtract(md . getSumShuiPrice()));
//
//            md . setHuohao(s . getHuohao());
//            md . setChehao(s . getChehao());
//            md . setPihao(s . getPihao());
//            md . setIsDelete("0");
//            md . setBeizhu(s . getBeizhu());
//
//            this . mdDao . insertSelective(md);
//
//            this . spotDao . adjustSpotById(md . getKcSpotId(), false, md . getCounts(), md . getZhongliang(), md . getJijiafangshiId());
//
//            this . ckTzDao . subtractTzById(md . getKcCkTzId(), md . getCounts(), md . getZhongliang());
//        }

            if (!empty($addMxList)) {
                $addNumberCount = empty($data['id']) ? 0 : StockOutDetail::where('kc_ck_id', $ck['id'])->max('system_number');
                foreach ($addMxList as $mjo) {
                    $addNumberCount++;
//                TbKcCkTz tz = (TbKcCkTz) this . tzDao . selectByPrimaryKey(mjo . getKcCkTzId());
                    $tz = KucunCktz::get($mjo['kucun_cktz_id']);
                    $mjo['stock_out_id'] = $ck['id'];
                    $mjo['out_mode'] = 2;
                    $mjo['cache_ywtime'] = $tz['cache_ywtime'];
                    $mjo['cache_data_pnumber'] = $tz['cache_data_pnumber'];
                    $mjo['cache_customer_id'] = $tz['cache_customer_id'];
                    $mjo['data_id'] = $tz['data_id'];
//                $mjo['pinming_id'] = $tz['pinming_id'];
                    $mjo['guige_id'] = $tz['guige_id'];
                    $mjo['caizhi'] = $tz['caizhi'];
                    $mjo['chandi'] = $tz['chandi'];
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
                    $mjo['zhijian'] = $tz['zhijian'];
                    $mjo['price'] = $tz['price'];
                    $mjo['sumprice'] = $tz['sumprice'];
                    $mjo['shuiprice'] = $tz['shui_price'];
                    $mjo['sum_shui_price'] = $tz['sum_shui_price'];
                    $mjo['shuie'] = $tz['shuie'];
                    $mjo['system_number'] = $addNumberCount;

                    $mx = new StockOutDetail();
                    $mx->allowField(true)->data($mjo)->save();

                    foreach ($mjo['mdList'] as $tmd) {
                        $s = KcSpot::get($tmd['kc_spot_id']);

                        $tmd['stock_out_id'] = $ck['id'];
                        $tmd['chuku_mx_id'] = $mx['id'];
                        $tmd['data_id'] = $mx['data_id'];
                        $tmd['chuku_type'] = $mx['chuku_type'];
                        $tmd['out_mode'] = 2;

                        $tmd['pinming_id'] = $s['pinming_id'];
                        $tmd['caizhi'] = $s['caizhi_id'];
                        $tmd['chandi'] = $s['chandi_id'];
                        $tmd['guige_id'] = $s['guige_id'];
                        $tmd['houdu'] = $s['houdu'];
                        $tmd['kuandu'] = $s['kuandu'];
                        $tmd['changdu'] = $s['changdu'];
                        $tmd['tax_rate'] = $s['shui_price'];
                        $tmd['mizhong'] = $mx['mizhong'];
                        $tmd['jianzhong'] = $mx['jianzhong'];
                        $jjfs = Jsfs::where('id', $tmd['jijiafangshi_id'])->cache(true, 60)->value('jj_type');
                        if ($jjfs == 1 || $jjfs == 2) {
                            $tmd['sum_shui_price'] = $tmd['price'] * $tmd['zhongliang'];
                            $tmd['cb_sum_shuiprice'] = $tmd['cb_price'] * $tmd['zhongliang'];
                        } elseif ($jjfs == 3) {
                            $tmd['sum_shui_price'] = $tmd['price'] * $tmd['counts'];
                            $tmd['cb_sum_shuiprice'] = $tmd['cb_price'] * $tmd['counts'];
                        }
//                    $tmd['sumprice'](WuziUtil . calSumPrice(md . getSumShuiPrice(), md . getShuiprice()));
//                    $tmd['shuie'](WuziUtil . calShuie(md . getSumShuiPrice(), md . getShuiprice()));
//                    $tmd['cb_sum_price'](WuziUtil . calSumPrice(md . getCbSumShuiPrice(), md . getShuiprice()));
//                    $tmd['cb_shuie'](WuziUtil . calShuie(md . getCbSumShuiPrice(), md . getShuiprice()));
//                    $tmd['fy_sz'](md . getCbSumShuiPrice() . subtract(md . getSumShuiPrice()));

                        $tmd['huohao'] = $s['huohao'];
                        $tmd['chehao'] = $s['chehao'];
                        $tmd['pihao'] = $s['pihao'];
                        $tmd['beizhu'] = $s['beizhu'];
                        $tmd['store_id'] = $mx['store_id'];
                        $md = new StockOutMd();
                        $md->allowField(true)->data($data)->save();

                        (new KcSpot())->adjustSpotById($md['kc_spot_id'], false, $md['counts'], $md['zhongliang'], $md['jijiafangshi_id'], $md['cb_shuie'] ?? 0);
//                    this . spotDao . adjustSpotById(md . getKcSpotId(), false, md . getCounts(), md . getZhongliang(), md . getJijiafangshiId());

                        (new KucunCktz())->subtractTzById($md['kucun_cktz_id'], $md['counts'], $md['zhongliang']);
                    }
                }
            }
            if (!empty($updateMdList)) {
                throw new Exception('出库单禁止修改');
            }
//        TbKcCkMx mx;
//        for (TbKcCkMd tmd : updateMdList) {
//            TbKcCkMd md = (TbKcCkMd) this . mdDao . selectByPrimaryKey(tmd . getId());
//            BigDecimal mdCounts = md . getCounts();
//            BigDecimal mdZhongliang = md . getZhongliang();
//            md . setCounts(tmd . getCounts());
//            md . setJianshu(tmd . getJianshu());
//            md . setLingzhi(tmd . getLingzhi());
//            md . setZhijian(tmd . getZhijian());
//            md . setZhongliang(tmd . getZhongliang());
//            $tmd['jijiafangshi_id'](tmd . getJijiafangshiId());
//
//            TbBaseJijiafangshi jjfs = (TbBaseJijiafangshi) this . jjfsDao . selectByPrimaryKey(tmd . getJijiafangshiId());
//            if ((jjfs . getBaseJijialeixingId() . equals(Globals . DICT_BASE_JIJIALEIXING_1)) || (jjfs . getBaseJijialeixingId() . equals(Globals . DICT_BASE_JIJIALEIXING_2))) {
//                $tmd['sum_shui_price'](md . getPrice() . multiply(md . getZhongliang()));
//                md . setCbSumShuiPrice(md . getCbPrice() . multiply(md . getZhongliang()));
//            } else if (jjfs . getBaseJijialeixingId() . equals(Globals . DICT_BASE_JIJIALEIXING_3)) {
//                $tmd['sum_shui_price'](md . getPrice() . multiply(md . getCounts()));
//                md . setCbSumShuiPrice(md . getCbPrice() . multiply(md . getCounts()));
//            }
//            md . setSumprice(WuziUtil . calSumPrice(md . getSumShuiPrice(), md . getShuiprice()));
//            md . setShuie(WuziUtil . calShuie(md . getSumShuiPrice(), md . getShuiprice()));
//            $tmd['cb_sum_price'](WuziUtil . calSumPrice(md . getCbSumShuiPrice(), md . getShuiprice()));
//            $tmd['cb_shuie'](WuziUtil . calShuie(md . getCbSumShuiPrice(), md . getShuiprice()));
//            $tmd['fy_sz'](md . getCbSumShuiPrice() . subtract(md . getSumShuiPrice()));
//
//            this . mdDao . updateByPrimaryKeySelective(md);
//
//            this . spotDao . upjustSpotByCk(md . getId(), mdCounts, md . getCounts(), mdZhongliang, md . getZhongliang());
//
//            this . ckTzDao . upjustTzById(md . getKcCkTzId(), mdCounts, md . getCounts(), mdZhongliang, md . getZhongliang());
//        }

            Db::commit();
            return returnSuc();
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**
     * 获取出库单
     * @param Request $request
     * @param $id
     * @return Json
     * @throws DbException
     */
    public function detail(Request $request, $id)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $data = StockOut::with([
            'wait' => ['specification', 'jsfs', 'custom'],
            'already' => ['specification', 'jsfs', 'spot', 'storage']
        ])
            ->where('id', $id)
            ->where('companyid', $this->getCompanyId())
            ->find();
        return returnRes(!empty($data), '出库单不存在', $data);
    }

    /**
     * 作废
     * @param Request $request
     * @param int $id
     * @return bool|Json
     * @throws Exception
     */
    public function cancel(Request $request, $id = 0)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }
        Db::startTrans();
        try {
            $ck = StockOut::get($id);
            if (empty($ck)) {
                throw new Exception("对象不存在");
            }
            if ($ck->companyid == $this->getCompanyId()) {
                throw new Exception("对象不存在");
            }
            if (!empty($ck['data_id'])) {
                throw new Exception("当前单据是只读单据,请到关联单据作废");
            }
            if ($ck['status'] == 1) {
                throw new Exception("该单据已经作废");
            }

            $ckmd = StockOutMd::where('stock_out_id', $ck['id'])->select();
            $spotModel = new KcSpot();
            foreach ($ckmd as $md) {
                $spotModel->adjustSpotById($md['kc_spot_id'], true, $md['counts'], $md['zhongliang'], $md['jijiafangshi_id'], $md['cb_shuie']);
                KucunCktz::addTzById($md['kucun_cktz_id'], $md['counts'], $md['zhongliang']);
            }

            $ck->status = 2;
            $ck->save();
            Db::commit();
            return returnSuc();
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

}