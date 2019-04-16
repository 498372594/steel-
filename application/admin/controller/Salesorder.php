<?php

namespace app\admin\controller;

use app\admin\model\{Jsfs, KcSpot, KucunCktz, SalesReturnDetails, StockOut, StockOutMd};
use app\admin\validate\{FeiyongDetails, SalesorderDetails};
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    Request,
    response\Json};

class Salesorder extends Right
{
    /**
     * 获取销售单列表
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
        $list = \app\admin\model\Salesorder::with([
            'custom',
            'pjlxData',
            'jsfsData',
        ])->where('companyid', $this->getCompanyId())
            ->order('create_time', 'desc');
        if (!empty($params['ywsjStart'])) {
            $list->where('ywsj', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('ywsj', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['status'])) {
            $list->where('status', $params['status']);
        }
        if (!empty($params['custom_id'])) {
            $list->where('custom_id', $params['custom_id']);
        }
        if (!empty($params['employer'])) {
            $list->where('employer', $params['employer']);
        }
        if (!empty($params['pjlx'])) {
            $list->where('pjlx', $params['pjlx']);
        }
        if (!empty($params['system_no'])) {
            $list->where('system_no', 'like', '%' . $params['system_no'] . '%');
        }
        if (!empty($params['ywlx'])) {
            $list->where('ywlx', $params['ywlx']);
        }
        if (!empty($params['remark'])) {
            $list->where('remark', 'like', '%' . $params['remark'] . '%');
        }
        $list = $list->paginate($pageLimit);
        return returnRes(true, '', $list);
    }

    /**
     * 获取销售单详情
     * @param int $id
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function detail($id = 0)
    {
        $data = \app\admin\model\Salesorder::with([
            'custom',
            'pjlxData',
            'jsfsData',
            'details' => ['specification', 'jsfs', 'storage'],
            'other' => ['mingxi' => ['szmcData', 'pjlxData', 'custom', 'szflData']]
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

    /**
     * 添加销售单
     * @param Request $request
     * @param int $ywlx
     * @param array $data
     * @param bool $return
     * @param array $spotIds
     * @return bool|string|Json|array
     * @throws \think\Exception
     */
    public function add(Request $request, $ywlx = 1, $data = [], $return = false, $spotIds = [])
    {
        if (!$request->isPost()) {
            if ($return) {
                return '请求方式错误';
            } else {
                return returnFail('请求方式错误');
            }
        }
        $companyId = $this->getCompanyId();
        $count = \app\admin\model\Salesorder::whereTime('create_time', 'today')
            ->where('companyid', $companyId)
            ->count();

        //数据处理
        if (empty($data)) {
            $data = $request->post();
        }
        $systemNumber = 'XSD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
        $data['add_name'] = $this->getAccount()['name'];
        $data['add_id'] = $this->getAccountId();
        $data['companyid'] = $companyId;
        $data['system_no'] = $systemNumber;
        $data['ywlx'] = $ywlx;

        //数据验证
        $validate = new \app\admin\validate\Salesorder();
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
            $model = new \app\admin\model\Salesorder();
            $model->allowField(true)->data($data)->save();

            //处理明细
            $id = $model->getLastInsID();
            $num = 1;
            $detailsValidate = new SalesorderDetails();
            $totalMoney = 0;
            $totalWeight = 0;
            foreach ($data['details'] as $c => $v) {
                $data['details'][$c]['companyid'] = $companyId;
                $data['details'][$c]['order_id'] = $id;
                $data['details'][$c]['caizhi'] = empty($v['caizhi']) ? '' : $this->getCaizhiId($v['caizhi']);
                $data['details'][$c]['chandi'] = empty($v['chandi']) ? '' : $this->getChandiId($v['chandi']);
                $totalMoney += $v['total_fee'];
                $totalWeight += $v['weight'];
                if (!$detailsValidate->check($data['details'][$c])) {
                    throw new Exception('请检查第' . $num . '行' . $detailsValidate->getError());
                }
                $num++;
            }
            (new \app\admin\model\SalesorderDetails())->allowField(true)->saveAll($data['details']);

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
                $res = (new Feiyong())->addAll($data['other'], 1, $id, $data['ywsj'], false);
                if ($res !== true) {
                    throw new Exception($res);
                }
            }

            if ($data['ckfs'] == 2) {
                //手动出库，添加出库通知单
                $notify = [];
                foreach ($data['details'] as $c => $v) {
                    $notify[] = [
                        'companyid' => $companyId,
                        'chuku_type' => 4,
                        'data_id' => $id,
                        'guige_id' => $v['wuzi_id'],
                        'caizhi' => empty($v['caizhi']) ? '' : $this->getCaizhiId($v['caizhi']),
                        'chandi' => empty($v['chandi']) ? '' : $this->getChandiId($v['chandi']),
                        'jijiafangshi_id' => $v['jsfs_id'],
                        'houdu' => $v['houdu'] ?? '',
                        'kuandu' => $v['width'] ?? '',
                        'changdu' => $v['length'] ?? '',
                        'lingzhi' => $v['lingzhi'] ?? '',
                        'jianshu' => $v['num'] ?? '',
                        'zhijian' => $v['jzs'] ?? '',
                        'counts' => $v['count'] ?? '',
                        'zhongliang' => $v['weight'] ?? '',
                        'price' => $v['price'] ?? '',
                        'sumprice' => $v['total_fee'] ?? '',
                        'shuie' => $v['tax'] ?? '',
                        'shui_price' => $v['tax_rate'] ?? '',
                        'sum_shui_price' => $v['price_and_tax'] ?? '',
                        'remark' => $v['remark'] ?? '',
                        'car_no' => $v['car_no'] ?? '',
                        'pihao' => $v['batch_no'] ?? '',
                        'cache_ywtime' => $data['ywsj'],
                        'cache_data_pnumber' => $data['system_no'],
                        'cache_customer_id' => $data['custom_id'],
                        'store_id' => $v['storage_id'],
                        'cache_create_operator' => $data['add_id'],
                    ];
                }
                (new Chuku())->addNotify($notify);
            } elseif ($data['ckfs'] == 1) {
                //自动出库，生成出库单
                $stockOutData = [
                    'remark' => '销售单，' . $systemNumber,
                    'yw_time' => $data['ywsj'],
                    'department' => $data['department'],
                    'sale_operator_id' => $data['add_id'],
                    'details' => [],
                    'data_id' => $id
                ];
                $stockOutDetail = [];
                $index = -1;
                foreach ($data['details'] as $v) {
                    $v['index'] = $v['index'] ?? $index--;
                    $spotId = $v['spot_id'] ?? $spotIds[$v['index']];
                    $stockOutData['details'][] = [
                        'zhongliang' => $v['weight'] ?? '',
                        'kucun_cktz_id' => $v['index'],
                        'kc_spot_id' => $spotId,
                        'ylsh' => $v['ylsh_id'] ?? 0
                    ];
                    $stockOutDetail[$v['index']] = [
                        'companyid' => $companyId,
                        'chuku_type' => 4,
                        'data_id' => $id,
                        'guige_id' => $v['wuzi_id'],
                        'caizhi' => empty($v['caizhi']) ? '' : $this->getCaizhiId($v['caizhi']),
                        'chandi' => empty($v['chandi']) ? '' : $this->getChandiId($v['chandi']),
                        'jijiafangshi_id' => $v['jsfs_id'],
                        'houdu' => $v['houdu'] ?? '',
                        'kuandu' => $v['width'] ?? '',
                        'changdu' => $v['length'] ?? '',
                        'lingzhi' => $v['lingzhi'] ?? '',
                        'jianshu' => $v['num'] ?? '',
                        'zhijian' => $v['jzs'] ?? '',
                        'counts' => $v['count'] ?? '',
                        'zhongliang' => $v['weight'] ?? '',
                        'price' => $v['price'] ?? '',
                        'sumprice' => $v['total_fee'] ?? '',
                        'shuie' => $v['tax'] ?? '',
                        'shui_price' => $v['tax_rate'] ?? '',
                        'sum_shui_price' => $v['price_and_tax'] ?? '',
                        'remark' => $v['remark'] ?? '',
                        'car_no' => $v['car_no'] ?? '',
                        'pihao' => $v['batch_no'] ?? '',
                        'cache_ywtime' => $data['ywsj'],
                        'cache_data_pnumber' => $data['system_no'],
                        'cache_customer_id' => $data['custom_id'],
                        'store_id' => $v['storage_id'],
                        'cache_create_operator' => $data['add_id'],
                    ];
                }
                $res = (new Chuku())->add($request, $stockOutData, $stockOutDetail, 1, true);
                if ($res !== true) {
                    throw new Exception($res);
                }
            } else {
                throw new Exception('出库方式错误');
            }
            //向货款单添加数据
            $capitalHkData = [
                'hk_type' => CapitalHk::SALES_ORDER,
                'data_id' => $id,
                'fangxiang' => 1,
                'customer_id' => $data['custom_id'],
                'jiesuan_id' => $data['jsfs'],
                'system_number' => $data['system_no'],
                'yw_time' => $data['ywsj'],
                'beizhu' => $data['remark'],
                'money' => $totalMoney,
                'group_id' => $data['department'],
                'sale_operator_id' => $data['employer'],
                'create_operator_id' => $data['add_id'],
                'zhongliang' => $totalWeight,
                'cache_pjlx_id' => $data['pjlx'],
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

    /**
     * 审核
     * @param Request $request
     * @param int $id
     * @param int $ywlx
     * @param boolean $isWeb
     * @return Json
     * @throws DbException
     */
    public function audit(Request $request, $id = 0, $ywlx = 1, $isWeb = true)
    {
        if ($request->isPut()) {
            if ($ywlx != 1 && $isWeb) {
                return returnFail('此销售单禁止直接审核');
            }
            if ($isWeb) {
                $salesorder = \app\admin\model\Salesorder::where('id', $id)
                    ->where('ywlx', $ywlx)
                    ->find();
            } else {
                $salesorder = \app\admin\model\Salesorder::where('data_id', $id)
                    ->where('ywlx', $ywlx)
                    ->find();
            }
            if (empty($salesorder)) {
                return returnFail('数据不存在');
            }
            if ($salesorder->status == 3) {
                return returnFail('此单已审核');
            }
            if ($salesorder->status == 2) {
                return returnFail('此单已作废');
            }
            $salesorder->status = 3;
            $salesorder->auditer = $this->getAccountId();
            $salesorder->audit_name = $this->getAccount()['name'];
            $salesorder->save();
            return returnSuc();
        }
        return returnFail('请求方式错误');
    }

    /**
     * 反审核
     * @param Request $request
     * @param int $id
     * @param int $ywlx
     * @param boolean $isWeb
     * @return Json
     * @throws DbException
     */
    public function unAudit(Request $request, $id = 0, $ywlx = 1, $isWeb = true)
    {
        if ($request->isPut()) {
            if ($ywlx != 1 && $isWeb) {
                return returnFail('此销售单禁止直接反审核');
            }
            if ($isWeb) {
                $salesorder = \app\admin\model\Salesorder::where('id', $id)
                    ->where('ywlx', $ywlx)
                    ->find();
            } else {
                $salesorder = \app\admin\model\Salesorder::where('data_id', $id)
                    ->where('ywlx', $ywlx)
                    ->find();
            }
            if (empty($salesorder)) {
                return returnFail('数据不存在或已作废');
            }
            if ($salesorder->status == 1) {
                return returnFail('此单未审核');
            }
            if ($salesorder->status == 2) {
                return returnFail('此单已作废');
            }
            $salesorder->status = 1;
            $salesorder->auditer = null;
            $salesorder->audit_name = '';
            $salesorder->save();
            return returnSuc();
        }
        return returnFail('请求方式错误');
    }

    /**
     * 作废
     * @param Request $request
     * @param int $id
     * @param int $ywlx
     * @param boolean $isWeb
     * @return Json|string
     * @throws DbException
     */
    public function cancel(Request $request, $id = 0, $ywlx = 1, $isWeb = true)
    {
        if ($request->isPost()) {
            if ($ywlx != 1 && $isWeb) {
                return returnFail('此销售单禁止直接作废');
            }
            if ($isWeb) {
                $salesorder = \app\admin\model\Salesorder::where('id', $id)
                    ->where('ywlx', $ywlx)
                    ->find();
            } else {
                $salesorder = \app\admin\model\Salesorder::where('data_id', $id)
                    ->where('ywlx', $ywlx)
                    ->find();
            }
            if (empty($salesorder)) {
                return returnFail('数据不存在');
            }
            if ($salesorder->status == 3) {
                return returnFail('此单已审核，禁止作废');
            }
            if ($salesorder->status == 2) {
                return returnFail('此单已作废');
            }
            if ($isWeb) {
                Db::startTrans();
            }
            try {
                $salesorder->status = 2;
                $salesorder->save();
                //货款单作废
                (new CapitalHk())->cancel($id, CapitalHk::SALES_ORDER);
                //费用单作废
                (new Feiyong())->cancelByRelation($id, 1);
                //出库单作废
                (new Chuku())->cancel($request, $id, false);
                //清理出库通知
                (new Chuku())->cancelNotify($id, 4);
                Db::commit();
                return returnSuc();
            } catch (Exception $e) {
                if ($isWeb) {
                    Db::rollback();
                    return returnFail($e->getMessage());
                } else {
                    return $e->getMessage();
                }
            }
        }
        return returnFail('请求方式错误');
    }

    /**
     * @param Request $request
     * @param array $data
     * @param int $ywlx
     * @return \app\admin\model\Salesorder|array|false|\PDOStatement|string|\think\Model|Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws \think\Exception
     * @throws \Exception
     */
    public function edit(Request $request, $data = [], $ywlx = 1)
    {
        if (empty($data)) {
            $data = $request->post();
        }

        $validate = new \app\admin\validate\Salesorder();
        if (!$validate->check($data)) {
            return returnFail($validate->getError());
        }

        $addList = [];
        $updateList = [];

        $detailValidate = new SalesorderDetails();
        $num = 1;
        foreach ($data['details'] as $item) {
            if (!$detailValidate->check($item)) {
                return returnFail('请检查第' . $num . '行' . $data['details']);
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
            $count = \app\admin\model\Salesorder::whereTime('create_time', 'today')
                ->where('companyid', $companyId)
                ->count();

            //数据处理
            $systemNumber = 'XSD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            $data['add_id'] = $this->getAccountId();
            $data['companyid'] = $companyId;
            $data['system_no'] = $systemNumber;
            $data['ywlx'] = $ywlx;

            $xs = new \app\admin\model\Salesorder();
            $xs->allowField(true)->data($data)->save();

            if ($data['ckfs'] == 1) {
                $ck = (new StockOut())->insertChuku($xs['id'], "4", $xs['ywsj'], $xs['department'], $xs['system_no'], $xs['employer'], $this->getAccountId(), $this->getCompanyId());
            }
        } else {
            $xs = \app\admin\model\Salesorder::where('companyid', $companyId)->where('id', $data['id'])->find();
            if (empty($xs)) {
                throw new Exception('数据不存在');
            }
            if ($xs['status'] == 2) {
                throw new Exception('该单据已作废');
            }
            if ($xs['ywlx'] != 1) {
                throw new Exception('此销售单是由其他单据自动生成的，禁止直接修改！');
            }
            if ($xs['ckfs'] == 2) {
                $mxList = \app\admin\model\SalesorderDetails::where('order_id', $data['id'])->select();
                if (!empty($mxList)) {
                    foreach ($mxList as $item) {
                        $mdList = StockOutMd::where('data_id', $item['id'])->count();
                        if ($mdList > 0) {
                            throw new Exception("该单据已经出库，禁止操作！");
                        }
                    }
                }
            }
            $data['changer'] = $this->getAccountId();
            $xs->allowField(true)->data($data)->save();
            if ($data['ckfs'] == 1) {
                throw new Exception('自动出库单禁止修改');
            }
            $mxList = \app\admin\model\SalesorderDetails::where('order_id', $data['id'])->select();
            if (!empty($mxList)) {
                foreach ($mxList as $mx) {
                    $invList = \app\admin\model\Inv::where('data_id', $mx['id'])
                        ->where('yw_type', 3)
                        ->select();
                    foreach ($invList as $item) {
                        $item->yw_time = $xs['ywsj'];
                        $item->piaoju_id = $xs['pjlx'];
                        $item->customer_id = $xs['custom_id'];
                        $item->save();
                    }
                    $tzList = KucunCktz::where('data_id', $mx['id'])->select();
                    foreach ($tzList as $tz) {
                        $tz->cache_ywtime = $xs['ywsj'];
                        $tz->cache_customer_id = $xs['custom_id'];
                        $tz->save();
                    }
                }
            }
        }
        if (empty($deleteIds)) {
            $deleteIds = $request->post('deleteIds');
        }
        $deleteList = \app\admin\model\SalesorderDetails::where('id', 'in', $deleteIds)->select();
        foreach ($deleteList as $mx) {
            if ($xs['ckfs'] == 1) {
                throw new Exception('自动出库单禁止修改');
            } else {
                (new KucunCktz())->deleteByDataIdAndChukuType($mx['id'], 4);
            }
            (new \app\admin\model\Inv())->deleteInv($mx['id'], 3);
            $mx->delete();
        }
        foreach ($updateList as $mjo) {
            $thMxList = SalesReturnDetails::where('xs_sale_mx_id', $mjo['id'])->select();
            if (!empty($thMxList)) {
                throw new Exception("该销售单已有退货信息，禁止该操作！");
            }
            $jjfs = \app\admin\model\SalesorderDetails::alias('mx')
                ->join('__JSFS__ jjfs', 'mx.jsfs=jjfs.id')
                ->where('id', $mjo['id'])
                ->value('jj_type');
            if (($jjfs != 2) && empty($mjo['count'])) {
                throw new Exception("数量必须大于“0”！");
            }
            $mx = \app\admin\model\SalesorderDetails::where('id', $mjo['id'])->find();
            $mx->allowField(true)->data($mjo)->isUpdate(true)->save();
            if (1 == $xs['ckfs']) {
                throw new Exception('自动出库单禁止修改');
            } else {
                (new KucunCktz())->updateChukuTz($mx['id'], "4", $mx['wuzi_id'], $mx['caizhi'], $mx['chandi'], $mx['jsfs_id'], $mx['storage_id'], $mx['houdu'],
                    $mx['length'], $mx['width'], $mx['count'], $mx['num'], $mx['lingzhi'], $mx['jzs'], $mx['weight'], $mx['tax_rate'], $mx['total_fee'], $mx['price_and_tax'],
                    $mx['price'], $mx['batch_no'], $xs['remark'], $mx['car_no'], $xs['ywsj'], $xs['system_no'], $xs['custom_id']);
            }
            (new \app\admin\model\Inv())->updateInv($mx['id'], "3", null, $xs['custom_id'], $xs['ywsj'], $mx['length'], $mx['width'], $mx['houdu'],
                $mx['wuzi_id'], $mx['jsfs_id'], $xs['pjlx'], '', $mx['weight'], $mx['price'], $mx['total_fee'], $mx['price_and_tax'], $mx['tax_rate']);
        }
        if (!empty($addList)) {
            $trumpet = \app\admin\model\SalesorderDetails::where('order_id', $xs['id'])->max('trumpet');
            foreach ($addList as $mjo) {
                $trumpet++;
                $jjfs = Jsfs::where('id', $mjo['jsfs_id'])->value('jj_type');
                if ($jjfs != 2 && empty($mjo['count'])) {
                    throw new Exception("数量必须大于“0”！");
                }
                $mjo['trumpet'] = $trumpet;
                $mx = new \app\admin\model\SalesorderDetails();
                $mx->allowField(true)->data($mjo)->save();
                $spot = KcSpot::get($mx['kc_spot_id']);
                $cbPrice = null;
                if (empty($spot)) {
                    $cbPrice = null;
                } else {
                    $cbPrice = $spot['cb_price'];
                }
                if ($xs['ckfs'] == 1) {
                    (new StockOut())->insertCkMxMd($ck, $mx['kc_spot_id'], $mx['id'], "4", $xs['ywsj'],
                        $xs['system_no'], $xs['custom_id'], $mx['wuzi_id'], $mx['caizhi'], $mx['chandi'], $mx['jsfs_id'],
                        $mx['storage_id'], $mx['houdu'], $mx['width'], $mx['length'], $mx['jzs'], $mx['lingzhi'], $mx['num'], $mx['count'],
                        $mx['weight'], $mx['price'], $mx['total_fee'], $mx['tax_rate'], $mx['price_and_tax'], $mx['tax'],
                        null, null, $cbPrice, '', $this->getAccount(), $this->getCompanyId());
                } else {
                    (new KucunCktz())->insertChukuTz($mx['id'], 4, $mx['wuzi_id'], $mx['caizhi'], $mx['chandi'],
                        $mx['jsfs_id'], $mx['storage_id'], $mx['houdu'], $mx['length'], $mx['width'], $mx['count'], $mx['num'],
                        $mx['lingzhi'], $mx['jzs'], $mx['weight'], $mx['tax_rate'], $mx['total_fee'], $mx['price_and_tax'],
                        $mx['price'], $mx['pihao'], $mx['remark'], $mx['car_no'], $xs['ywsj'], $xs['system_no'], $xs['custom_id'], $this->getAccountId(), $this->getCompanyId());
                }
                (new \app\admin\model\Inv())->insertInv($mx['id'], 3, 1, $mx['length'], $mx['houdu'],
                    $mx['width'], $mx['wuzi_id'], $mx['jsfs_id'], $xs['pjlx'],
                    $xs['system_no'] . "." . $mx['trumpet'], $xs['custom_id'], $xs['ywsj'], $mx['price'],
                    $mx['tax_rate'], $mx['total_fee'], $mx['price_and_tax'], $mx['weight'], $this->getCompanyId());
            }
        }
        //todo 费用明细
//        this . fymxDaoImpl . fymxSave(fyJson, xs . getId(), xs . getYwTime(), "1", xs . getGroupId(), xs . getSaleOperatorId(), user, jigou, zhangtao, su, null);
        $mxList = \app\admin\model\SalesorderDetails::where('order_id', $xs['id'])->select();

        if (!empty($mxList)) {
            foreach ($mxList as $mx) {
                $spot = KcSpot::where('id', $mx['kc_spot_id'])->select();
                if (!empty($spot) && !empty($spot['cb_price'])) {
                    $mdList = StockOutMd::where('kc_spot_id', $spot['id'])->select();
                    if (!empty($mdList)) {
                        foreach ($mdList as $md) {
                            $md->cb_price = $spot['cb_price'];
                            $jjfs = Jsfs::get($mx['jsfs_id']);
                            if ($jjfs['jj_type'] == 1 || $jjfs['jj_type'] == 2) {
                                $md->cb_sum_shuiprice = $md->cb_price * $md->zhongliang;
                            } elseif ($jjfs['jj_type'] == 3) {
                                $md->cb_sum_shuiprice = $md->cb_price * $md->counts;
                            }
                            $md->save();
                        }
                    }
                }
            }
        }
        $sumMoney = \app\admin\model\SalesorderDetails::where('order_id', $xs['id'])->sum('price_and_tax');
        $sumZhongliang = \app\admin\model\SalesorderDetails::where('order_id', $xs['id'])->sum('weight');
        if (empty($data['id'])) {
            (new \app\admin\model\CapitalHk())->insertHk($xs['id'], "12", $xs['system_no'], $xs['remark'],
                $xs['custom_id'], "1", $xs['ywsj'], $xs['jsfs'], $xs['pjlx'], $sumMoney, $sumZhongliang, $xs['department'], $xs['employer'], $this->getAccountId());
        } else {
            (new \app\admin\model\CapitalHk())->updateHk($xs['id'], "12", $xs['remark'], $xs['custom_id'], $xs['ywsj'],
                $xs['jsfs'], $xs['pjlx'], $sumMoney, $sumZhongliang, $xs['department'], $xs['employer']);
        }
        return $xs;
    }
}