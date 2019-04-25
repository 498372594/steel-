<?php


namespace app\admin\controller;

use app\admin\model\{Bank,
    CapitalBank,
    CapitalFk as CapitalFkModel,
    CapitalFkhx,
    CapitalFkjsfs,
    CapitalFy,
    CapitalHk as CapitalHkModel,
    CapitalOther as CapitalOtherModel};
use app\admin\validate\{CapitalFk as CapitalFkValidate,
    CapitalFkhx as CapitalFkhxValidate,
    CapitalFkJsfs as CapitalFkJsfsValidate};
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    db\Query,
    exception\DbException,
    Request,
    response\Json};

class CapitalFk extends Right
{
    /**
     * 获取付款单列表
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
        $list = CapitalFkModel::with([
            'custom'
        ])->where('companyid', $this->getCompanyId())
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
        if (!empty($params['system_number'])) {
            $list->where('system_number', 'like', '%' . $params['system_number'] . '%');
        }
        if (!empty($params['customer_id'])) {
            $list->where('customer_id', $params['customer_id']);
        }
        if (!empty($params['beizhu'])) {
            $list->where('beizhu', 'like', '%' . $params['beizhu'] . '%');
        }
        $list = $list->paginate($pageLimit);
        return returnSuc($list);
    }

    /**
     * 添加付款单
     * @param Request $request
     * @return Json
     */
    public function add(Request $request)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }
        $number = 0;
        $mxnumber = 0;

        $data = $request->post();
        Db::startTrans();
        try {
            $validate = new CapitalFkValidate();
            if (!$validate->check($data)) {
                return returnFail($validate->getError());
            }

            if (($data['fk_type'] == 3 || $data['fk_type'] == 4) && $data['money'] > 0) {
                throw new Exception("类型是退货款或者退预收款,本次付款必须为负数");
            }


            $addList = [];
            $updateList = [];
            $ja = $data['details'];
            $companyId = $this->getCompanyId();
            if (!empty($ja)) {
                $shoukuanValidate = new CapitalFkJsfsValidate();
                foreach ($ja as $object) {
                    if (($data['fk_type'] == 3 || $data['fk_type'] == 4) && $object['money'] > 0) {
                        throw new Exception("类型是退货款或者退预收款,付款金额必须为负数");
                    }

                    if (!$shoukuanValidate->check($object)) {
                        throw new Exception($shoukuanValidate->getError());
                    }
                    $object['companyid'] = $companyId;

                    $mxnumber += $object['money'];

                    if (empty($object['id'])) {
                        $addList[] = $object;
                    } else {
                        $updateList[] = $object;
                    }
                }
            }


            $addFyList = [];
            $updateFyList = [];
            if (!empty($data['hxDetails'])) {
                $detailsValidate = new CapitalFkhxValidate();

                foreach ($data['hxDetails'] as $object) {

                    if (!$detailsValidate->check($object)) {
                        throw new Exception($detailsValidate->getError());
                    }

                    if (empty($object['hx_money']) && empty($object['hx_zhongliang'])) {
                        throw new Exception("核销重量或者核销金额必须填一项");
                    }

                    $number += $object['hx_money'];
                    if (empty($object['id'])) {
                        $addFyList[] = $object;
                    } else {
                        $updateFyList[] = $object;
                    }
                }
            }


            if (empty($data['id'])) {
                $fk = new CapitalFkModel();

                $count = CapitalFkModel::withTrashed()
                    ->whereTime('create_time', 'today')
                    ->where('companyid', $companyId)
                    ->count();

                $data['system_number'] = 'FKD' . date('Ymd') . str_pad(++$count, 3, 0, STR_PAD_LEFT);
                $data['create_operator_id'] = $this->getAccountId();
                $data['companyid'] = $companyId;

                $fk->allowField(true)->data($data)->save();
                if ($data['fk_type'] == 2) {
                    (new CapitalHkModel())->insertHk($fk['id'], 23, $fk['system_number'], $fk['beizhu'], $fk['customer_id'], 2, $fk['yw_time'], null, null, -$fk['money'] - $fk['msmoney'], null, $fk['group_id'], $fk['sale_operator_id'], $this->getAccountId(), $companyId);
                }
            } else {
                $fk = CapitalFkModel::get($data['id']);
                if (empty($fk)) {
                    throw new Exception("对象不存在");
                }
                if ($fk['status'] == 2) {
                    throw new Exception("该单据已经作废");
                }
                $fk->isUpdate(true)->allowField(true)->save($data);
                if ($data['fk_type'] == 2) {
                    (new CapitalHkModel())->updateHk($data['id'], 23, $fk['beizhu'], $fk['customer_id'], $fk['yw_time'], null, null, $fk['money'] + $fk['mfmoney'], null, $fk['group_id'], $fk['sale_operator_id']);
                }

                $fkjsfsList = CapitalFkjsfs::where('fk_id', $fk['id'])->select();
                if (!empty($fkjsfsList)) {
                    foreach ($fkjsfsList as $obj) {
                        CapitalBank::where('data_id', $obj['id'])->update([
                            'yw_time' => $fk['yw_time'],
                            'cache_customer_id' => $fk['customer_id']
                        ]);
                    }
                }
            }
            if (!empty($data['deleteMxIds'])) {
                if (is_string($data['deleteMxIds'])) {
                    $data['deleteMxIds'] = explode(',', $data['deleteMxIds']);
                }
                foreach ($data['deleteMxIds'] as $string) {
                    Bank::deleteBank($string, 3, 2);
                }
                CapitalFkjsfs::destroy(function (Query $query) use ($data) {
                    $query->where('id', 'in', $data['deleteMxIds']);
                });
            }

            foreach ($updateList as $mjo) {
                $jsfs = CapitalFkjsfs::get($mjo['id']);
                $oldMoney = $jsfs['money'];
                $oldBankId = $jsfs['bank_id'];

                $jsfs->allowField(true)->save($mjo);

                (new Bank())->updateBank($jsfs['id'], $oldBankId, 3, $jsfs['bank_id'], 2, null, $jsfs['money'], $oldMoney);
            }

            foreach ($addList as $mjo) {
                $mjo['sk_id'] = $fk['id'];
                $jsfs = new CapitalFkjsfs();
                $jsfs->allowField(true)->data($mjo)->save();
                (new Bank())->insertBank($jsfs['id'], 3, $jsfs['bank_id'], 2, $fk['yw_time'], $jsfs['money'], $fk['customer_id'], $fk['system_number'], $companyId);
            }

            if (!empty($data['deleteHxIds'])) {
                $hxList = CapitalFkhx::where('id', 'in', $data['deleteHxIds'])->select();
                foreach ($hxList as $hx) {
                    if ($hx->fkhx_type == 1) {
                        CapitalOtherModel::jianMoney($hx['data_id'], $hx['hx_money'], $hx['hx_zhongliang']);
                    } else if ($hx->skhx_type == 2) {
                        CapitalFy::jianMoney($hx['data_id'], $hx['hx_money'], $hx['hx_zhongliang']);
                    } else {
                        CapitalHkModel::jianMoney($hx['data_id'], $hx['hx_money'], $hx['hx_zhongliang']);
                    }
                }
                CapitalFkhx::destroy(function (Query $query) use ($data) {
                    $query->where('id', 'in', $data['deleteHxIds']);
                });
            }

            foreach ($updateFyList as $obj) {
                $fkhx = CapitalFkhx::get($obj['id']);
                if ($fkhx['fkhx_type'] == 1) {
                    (new CapitalOtherModel())->tiaoMoney($fkhx['data_id'], $fkhx['hx_money'], $obj['hx_money'], $fkhx['hx_zhongliang'], $obj['hx_zhongliang']);
                } elseif ($fkhx['fkhx_type'] == 2) {
                    (new CapitalFy())->tiaoMoney($fkhx['data_id'], $fkhx['hx_money'], $obj['hx_money'], $fkhx['hx_zhongliang'], $obj['hx_zhongliang']);
                } else {
                    (new CapitalHkModel())->tiaoMoney($fkhx['data_id'], $fkhx['hx_money'], $obj['hx_money'], $fkhx['hx_zhongliang'], $obj['hx_zhongliang']);
                }
                $fkhx->allowField(true)->isUpdate(true)->save($obj);
            }

            foreach ($addFyList as $obj) {
                $fkhx = new CapitalFkhx();
                if ($obj['skhx_type'] == 1) {
                    $qt = CapitalOtherModel::get($obj['data_id']);
                    if ($obj['hx_money'] > ($qt['money'] - $qt['hxmoney'])) {
                        throw new Exception("核销金额不能大于未核销金额");
                    }
                    $obj['create_time'] = $qt['create_time'];
                    $obj['cache_systemnumber'] = $qt['system_number'];
                    $obj['cache_ywtime'] = $qt['yw_time'];
                    $obj['sk_id'] = $fk['id'];
                    $obj['hj_money'] = $qt['money'];
                    $obj['hj_zhongliang'] = $qt['zhongliang'];
                    $obj['customer_id'] = $qt['customer_id'];
                    (new CapitalOtherModel())->addMoney($qt['id'], $fkhx['hx_money'], $fkhx['hx_zhongliang']);
                } elseif ($obj['skhx_type'] == 2) {
                    $fy = CapitalFy::get($obj['data_id']);

                    if ($obj['hx_money'] > ($fy['money'] - $fy['hxmoney'])) {
                        throw new Exception("核销金额不能大于未核销金额");
                    }
                    $obj['create_time'] = $fy['create_time'];
                    $obj['cache_systemnumber'] = $fy['system_number'];
                    $obj['cache_ywtime'] = $fy['yw_time'];
                    $obj['sk_id'] = $fk['id'];
                    $obj['hj_money'] = $fy['money'];
                    $obj['hj_zhongliang'] = $fy['zhongliang'];
                    $obj['customer_id'] = $fy['customer_id'];
                    (new CapitalFy())->addMoney($fy['id'], $fkhx['hx_money'], $fkhx['hx_zhongliang']);
                } else {
                    $hk = CapitalHkModel::get($obj['data_id']);
                    if (!empty($hk)) {
                        if ($obj['hx_money'] > 0) {
                            if ($obj['hx_money'] > ($hk['money'] - $hk['hxmoney'])) {
                                throw new Exception("核销金额不能大于未核销金额");
                            }
                        } elseif ($obj['hx_money'] < ($hk['money'] - $hk['hxmoney'])) {
                            throw new Exception("核销金额不能大于未核销金额");
                        }
                    } else {
                        $hk = CapitalHkModel::where('data_id', $obj['data_id'])->find();
                    }
                    $obj['create_time'] = $hk['create_time'];
                    $obj['cache_systemnumber'] = $hk['system_number'];
                    $obj['cache_ywtime'] = $hk['yw_time'];
                    $obj['sk_id'] = $fk['id'];
                    $obj['hj_money'] = $hk['money'];
                    $obj['hj_zhongliang'] = $hk['zhongliang'];
                    $obj['customer_id'] = $hk['customer_id'];
                    (new CapitalHkModel())->addMoney($hk['id'], $fkhx['hx_money'], $fkhx['hx_zhongliang']);
                }
                $fkhx->data($data)->allowField(true)->save(0);
            }
            Db::commit();
            return returnSuc(['id' => $fk['id']]);
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**
     * 获取付款单详情
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
        $data = CapitalFkModel::with([
            'custom',
            'mingxi' => ['jsfs', 'bank'],
            'details' => ['custom']
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
     * 作废
     * @param Request $request
     * @param int $id
     * @return Json
     */
    public function cancel(Request $request, $id = 0)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }
        Db::startTrans();
        try {
            $fk = CapitalFkModel::get($id);

            if (empty($fk)) {
                throw new Exception("对象不存在");
            }
            if ($fk['companyid'] != $this->getCompanyId()) {
                throw new Exception("对象不存在");
            }
            if ($fk['status'] == 2) {
                throw new Exception("该单据已经作废");
            }
            if ($fk['fk_type'] == 2) {
                CapitalHkModel::deleteHk($fk['id'], 23);
            }

            $list = CapitalFkjsfs::where('fk_id', $fk['id'])->select();
            foreach ($list as $jsfs) {
                Bank::deleteBank($jsfs['id'], 3, 2);
            }

            $list1 = CapitalFkhx::where('fk_id', $fk['id'])->select();
            foreach ($list1 as $hx) {
                if ($hx['fkhx_type'] == 1) {
                    CapitalOtherModel::jianMoney($hx['data_id'], $hx['hx_money'], $hx['hx_zhongliang']);
                } elseif ($hx['fkhx_type'] == 2) {
                    CapitalFy::jianMoney($hx['data_id'], $hx['hx_money'], $hx['hx_zhongliang']);
                } else {
                    CapitalHkModel::jianMoney($hx['data_id'], $hx['hx_money'], $hx['hx_zhongliang']);
                }
            }
            $fk->status = 2;
            $fk->save();
            Db::commit();
            return returnSuc();
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }
}