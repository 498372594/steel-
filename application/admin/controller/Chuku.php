<?php

namespace app\admin\controller;

use app\admin\model\{Jsfs,
    KcSpot,
    KucunCktz,
    SalesorderDetails,
    StockOut,
    StockOutDetail,
    StockOutMd,
    ViewSpecification};
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    db\Query,
    exception\DbException,
    Request,
    response\Json};

class Chuku extends Right
{

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
            'caizhiData',
            'chandiData'
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
        $list = StockOut::with(['addData'])
            ->where('companyid', $this->getCompanyId())
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

    /**
     * 添加出库单
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

            $validate = new \app\admin\validate\StockOut();
            if (!$validate->check($data)) {
                throw new Exception($validate->getError());
            }

            $addMxList = [];
            $updateMxList = [];

            $addMdList = [];
            $updateMdList = [];

            $ja = $data['details'];
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
            }

            if (!empty($data['deleteMxIds']) || !empty($data['deleteMdIds'])) {
                throw new Exception('出库单禁止修改');
            }

            foreach ($addMxList as &$mx) {
                foreach ($addMdList as $md) {
                    if ($mx['kucun_cktz_id'] == $md['kucun_cktz_id']) {
                        $mx['mdList'][] = $md;
                    }
                }
            }
            unset($mx, $md);

            if (!empty($addMxList)) {
                $addNumberCount = empty($data['id']) ? 0 : StockOutDetail::where('kc_ck_id', $ck['id'])->max('system_number');
                foreach ($addMxList as $mjo) {
                    $addNumberCount++;
                    $tz = KucunCktz::get($mjo['kucun_cktz_id']);
                    $mjo['stock_out_id'] = $ck['id'];
                    $mjo['chuku_type'] = $tz['chuku_type'];
                    $mjo['out_mode'] = 2;
                    $mjo['cache_ywtime'] = $tz['cache_ywtime'];
                    $mjo['cache_data_pnumber'] = $tz['cache_data_pnumber'];
                    $mjo['cache_customer_id'] = $tz['cache_customer_id'];
                    $mjo['data_id'] = $tz['data_id'];
                    $gg = ViewSpecification::where('id', $tz['guige_id'])->cache(true, 60)->find();
                    $mjo['pinming_id'] = $gg['productname_id'] ?? '';
                    $mjo['mizhong'] = $gg['mizhong_name'] ?? '';
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
                    $mjo['batch_no'] = $tz['pihao'];

                    $mx = new StockOutDetail();
                    $mx->allowField(true)->data($mjo)->save();

                    if (!empty($mjo['mdList'])) {
                        foreach ($mjo['mdList'] as $tmd) {
                            $s = KcSpot::get($tmd['kc_spot_id']);

                            $tmd['stock_out_id'] = $ck['id'];
                            $tmd['stock_out_detail_id'] = $mx['id'];
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
                            $tmd['jianzhong'] = $s['jianzhong'];
                            $tmd['zhijian'] = $s['zhijian'];
                            $tmd['cb_price'] = $s['cb_price'];
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
                            $md->allowField(true)->data($tmd)->save();

                            (new KcSpot())->adjustSpotById($md['kc_spot_id'], false, $md['counts'], $md['zhongliang'], $md['jijiafangshi_id'], $md['cb_shuie'] ?? 0);

                            (new KucunCktz())->subtractTzById($md['kucun_cktz_id'], $md['counts'], $md['zhongliang']);
                        }
                    }
                }
            }
            if (!empty($updateMdList)) {
                throw new Exception('出库单禁止修改');
            }

            Db::commit();
            return returnSuc(['id' => $ck['id']]);
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
            if ($ck->companyid != $this->getCompanyId()) {
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

    /**
     * 发货情况查询表
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     */
    public function fahuo(Request $request, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $params = $request->param();
        try {
            $model = new StockOut();
            $data = $model->fahuoqingkuang($params, $pageLimit);
            return returnSuc($data);
        } catch (Exception $e) {
            return returnFail($e->getMessage());
        }
    }

    /**
     * 发货明细清单
     * @param string $system_number
     * @return Json
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function mingxi($system_number = '')
    {
        $salesDeatils = SalesorderDetails::hasWhere('salesorder', ['system_no' => $system_number])->with([
            'salesorder' => ['custom'],
            'specification',
            'jsfs',
            'caizhiData',
            'chandiData'
        ])->select();

        $stockOutMd = StockOutMd::with([
            'stockOutData',
            'spot',
            'specification',
            'caizhiData',
            'chandiData',
            'jsfs',
            'storage'
        ])
            ->where('chuku_type', 4)
            ->where('data_id', 'in', function (Query $query) use ($system_number) {
                $query->name('SalesorderDetails')
                    ->alias('salemx')
                    ->join('__SALESORDER__ sale', 'sale.id=salemx.order_id')
                    ->where('sale.delete_time', null)
                    ->where('sale.status', '<>', 2)
                    ->where('sale.system_no', $system_number)
                    ->where('salemx.delete_time', null)
                    ->field('salemx.id');
            })->select();
        return returnSuc([
            'salesDetails' => $salesDeatils,
            'stockOut' => $stockOutMd
        ]);
    }

    public function chengben(Request $request, $pageLimit)
    {
        $params = $request->post();
        $md = (new StockOutMd())->getListByMxId($params, $pageLimit, $this->getCompanyId());
        return returnSuc($md);
    }

}