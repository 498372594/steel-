<?php


namespace app\admin\controller;

use app\admin\model\{Bank,
    CapitalBank,
    CapitalFy,
    CapitalHk as CapitalHkModel,
    CapitalOther as CapitalOtherModel,
    CapitalSk as CapitalSkModel,
    CapitalSkhx,
    CapitalSkjsfs,
    Custom};
use app\admin\validate\{CapitalSk as CapitalSkValidate, CapitalSkJsfs as CapitalSkJsfsValidate};
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    db\Query,
    exception\DbException,
    Request,
    response\Json};

class CapitalSk extends Right
{
    /**
     * 获取收款单列表
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
        $list = CapitalSkModel::with([
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
        if (!empty($params['hide_no_ms'])) {
            $list->where('msmoney', '>', 0);
        }
        $list = $list->paginate($pageLimit);
        return returnSuc($list);
    }

    /**
     * 添加收款单
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
            $number = 0;
            $mxnumber = 0;

            $data = $request->post();

            $validate = new CapitalSkValidate();
            if (!$validate->check($data)) {
                return returnFail($validate->getError());
            }
            if (($data['sk_type'] == 3 || $data['sk_type'] == 4) && $data['money'] > 0) {
                throw new Exception("类型是退货款或者退预收款,本次收款必须为负数");
            }

            if (!empty($data['msmoney']) && ($data['sk_type'] == 3 || $data['sk_type'] == 4) && $data['msmoney'] > 0) {
                throw new Exception("类型是退货款或者退预收款,免收金额必须为负数");
            }

            $addList = [];
            $updateList = [];
            $ja = $data['details'];
            $companyid = $this->getCompanyId();
            if (!empty($ja)) {
                $shoukuanValidate = new CapitalSkJsfsValidate();
                foreach ($ja as $object) {
                    $object['companyid'] = $companyid;
                    if (!$shoukuanValidate->check($object)) {
                        throw new Exception($shoukuanValidate->getError());
                    }
                    if (($data['sk_type'] == 3 || $data['sk_type'] == 4) && $object['money'] > 0) {
                        throw new Exception("类型是退货款或者退预收款,收款金额必须为负数");
                    }
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
                foreach ($data['hxDetails'] as $object) {
                    $object['companyid'] = $companyid;

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
                $sk = new CapitalSkModel();
                $count = CapitalSkModel::withTrashed()
                    ->where('companyid', $companyid)
                    ->whereTime('create_time', 'today')
                    ->count();

                $data['companyid'] = $companyid;
                $data['system_number'] = 'SKD' . date('Ymd') . str_pad($count, 3, 0, STR_PAD_LEFT);
                $data['create_operator_id'] = $this->getAccountId();
                $sk->allowField(true)->data($data)->save();

                if ($data['sk_type'] == 2) {
                    (new CapitalHkModel())->insertHk($sk['id'], 22, $sk['system_number'], $sk['beizhu'], $sk['customer_id'], 1, $sk['yw_time'], null, null, -$sk['money'] - $sk['msmoney'], null, $sk['group_id'], $sk['sale_operator_id'], $this->getAccountId(), $companyid);
                }
            } else {
                $sk = CapitalSkModel::where('companyid', $companyid)->where('id', $data['id'])->find();

                if (empty($sk)) {
                    throw new Exception("对象不存在");
                }
                if ($sk['status'] == 2) {
                    throw new Exception("该单据已经作废");
                }
                $data['update_operator_id'] = $this->getAccountId();

                $sk->isUpdate(true)->allowField(true)->save($data);

                if ($data['sk_type'] == 2) {
                    (new CapitalHkModel())->updateHk($data['id'], 22, $sk['beizhu'], $sk['customer_id'], $sk['yw_time'], null, null, $sk['money'], null, $sk['group_id'], $sk['sale_operator_id']);
                }

                $skjsfsList = CapitalSkjsfs::where('sk_id', $sk['id'])->select();
                if (!empty($skjsfsList)) {
                    $dataIds = [];
                    foreach ($skjsfsList as $obj) {
                        $dataIds[] = $obj['id'];
                    }
                    CapitalBank::where('data_id', 'in', $dataIds)->update([
                        'yw_time' => $sk['yw_time']
                    ]);
                    unset($dataIds);
                }
            }

            if (!empty($data['deleteMxIds'])) {
                if (is_string($data['deleteMxIds'])) {
                    $data['deleteMxIds'] = explode(',', $data['deleteMxIds']);
                }
                foreach ($data['deleteMxIds'] as $string) {
                    Bank::deleteBank($string, 4, 1);
                }
                CapitalSkjsfs::destroy(function (Query $query) use ($data) {
                    $query->where('id', 'in', $data['deleteMxIds']);
                });
            }

            foreach ($updateList as $mjo) {
                $jsfs = CapitalSkjsfs::get($mjo['id']);
                $oldMoney = $jsfs['money'];
                $oldBankId = $jsfs['bank_id'];

                $jsfs->allowField(true)->save($mjo);

                (new Bank())->updateBank($jsfs['id'], $oldBankId, 4, $jsfs['bank_id'], 1, null, $jsfs['money'], $oldMoney);
            }

            foreach ($addList as $mjo) {
                $mjo['sk_id'] = $sk['id'];
                $jsfs = new CapitalSkjsfs();
                $jsfs->allowField(true)->data($mjo)->save();
                (new Bank())->insertBank($jsfs['id'], 4, $jsfs['bank_id'], 1, date('Y-m-d H:i:s'), $jsfs['money'], $sk['customer_id'], $sk['system_number'], $companyid);
            }

            if (!empty($data['deleteHxIds'])) {
                $hxList = CapitalSkhx::where('id', 'in', $data['deleteHxIds'])->select();
                foreach ($hxList as $hx) {
                    if ($hx->skhx_type == 1 || $hx->skhx_type == 16) {
                        CapitalOtherModel::jianMoney($hx['data_id'], $hx['hx_money'], $hx['hx_zhongliang']);
                    } else if ($hx->skhx_type == 2) {
                        CapitalFy::jianMoney($hx['data_id'], $hx['hx_money'], $hx['hx_zhongliang']);
                    } else {
                        CapitalHkModel::jianMoney($hx['data_id'], $hx['hx_money'], $hx['hx_zhongliang']);
                    }
                }
                CapitalSkhx::destroy(function (Query $query) use ($data) {
                    $query->where('id', 'in', $data['deleteHxIds']);
                });
            }

            foreach ($updateFyList as $obj) {
                $skhx = CapitalSkhx::get($obj['id']);
                if ($skhx['skhx_type'] == 1 || $skhx['skhx_type'] == 16) {
                    (new CapitalOtherModel())->tiaoMoney($skhx['data_id'], $skhx['hx_money'], $obj['hx_money'], $skhx['hx_zhongliang'], $obj['hx_zhongliang']);
                } elseif ($skhx['skhx_type'] == 2) {
                    (new CapitalFy())->tiaoMoney($skhx['data_id'], $skhx['hx_money'], $obj['hx_money'], $skhx['hx_zhongliang'], $obj['hx_zhongliang']);
                } else {
                    (new CapitalHkModel())->tiaoMoney($skhx['data_id'], $skhx['hx_money'], $obj['hx_money'], $skhx['hx_zhongliang'], $obj['hx_zhongliang']);
                }
                $skhx->allowField(true)->isUpdate(true)->save($obj);
            }

            foreach ($addFyList as $obj) {
                $skhx = new CapitalSkhx();
                if ($obj['skhx_type'] == 1 || $obj['skhx_type'] == 16) {
                    $qt = CapitalOtherModel::get($obj['data_id']);
                    if ($obj['hx_money'] > ($qt['money'] - $qt['hxmoney'])) {
                        throw new Exception("核销金额不能大于未核销金额");
                    }
                    $obj['create_time'] = $qt->getData('create_time');
                    $obj['cache_systemnumber'] = $qt['system_number'];
                    $obj['cache_ywtime'] = $qt['yw_time'];
                    $obj['sk_id'] = $sk['id'];
                    $obj['hj_money'] = $qt['money'];
                    $obj['hj_zhongliang'] = $qt['zhongliang'];
                    $obj['customer_id'] = $qt['customer_id'];
                    (new CapitalOtherModel())->addMoney($qt['id'], $obj['hx_money'], $obj['hx_zhongliang']);
                } elseif ($obj['skhx_type'] == 2) {
                    $fy = CapitalFy::get($obj['data_id']);

                    if ($obj['hx_money'] > ($fy['money'] - $fy['hxmoney'])) {
                        throw new Exception("核销金额不能大于未核销金额");
                    }
                    $obj['create_time'] = $fy->getData('create_time');
                    $obj['cache_systemnumber'] = $fy['system_number'];
                    $obj['cache_ywtime'] = $fy['yw_time'];
                    $obj['sk_id'] = $sk['id'];
                    $obj['hj_money'] = $fy['money'];
                    $obj['hj_zhongliang'] = $fy['zhongliang'];
                    $obj['customer_id'] = $fy['customer_id'];
                    (new CapitalFy())->addMoney($fy['id'], $obj['hx_money'], $obj['hx_zhongliang']);
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
                    $obj['create_time'] = $hk->getData('create_time');
                    $obj['cache_systemnumber'] = $hk['system_number'];
                    $obj['cache_ywtime'] = $hk['yw_time'];
                    $obj['sk_id'] = $sk['id'];
                    $obj['hj_money'] = $hk['money'];
                    $obj['hj_zhongliang'] = $hk['zhongliang'];
                    $obj['customer_id'] = $hk['customer_id'];
                    (new CapitalHkModel())->addMoney($hk['id'], $obj['hx_money'], $obj['hx_zhongliang']);
                }
                $skhx->data($obj)->allowField(true)->save();

            }
            Db::commit();
            return returnSuc(['id' => $sk['id']]);
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**
     * 获取收款单详情
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
        $data = CapitalSkModel::with([
            'custom',
            'mingxi' => ['jsfs', 'bank'],
            'details' => ['custom'],
            'createOperator',
            'updateOperator'
        ])
            ->where('companyid', $this->getCompanyId())
            ->where('id', $id)
            ->find();
        return returnRes(true, '', $data);
    }

    /**
     * 作废
     * @param Request $request
     * @param int $id
     * @return Json
     */
    public function cancel(Request $request, $id = 0)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        Db::startTrans();
        try {
            $sk = CapitalSkModel::get($id);
            if (empty($sk)) {
                throw new Exception("对象不存在");
            }
            if ($sk['companyid'] != $this->getCompanyId()) {
                throw new Exception("对象不存在");
            }
            if ($sk['status'] == 2) {
                throw new Exception("该单据已经作废");
            }
            if ($sk['sk_type'] == 2) {
                CapitalHkModel::deleteHk($sk['id'], 22);
            }

            $list = CapitalSkjsfs::where('sk_id', $sk['id'])->select();
            foreach ($list as $jsfs) {
                Bank::deleteBank($jsfs['id'], 4, 1);
            }

            $list1 = CapitalSkhx::where('sk_id', $sk['id'])->select();
            foreach ($list1 as $hx) {
                if ($hx['skhx_type'] == 1 || $hx['skhx_type'] == 16) {
                    CapitalOtherModel::jianMoney($hx['data_id'], $hx['hx_money'], $hx['hx_zhongliang']);
                } elseif ($hx['skhx_type'] == 2) {
                    CapitalFy::jianMoney($hx['data_id'], $hx['hx_money'], $hx['hx_zhongliang']);
                } else {
                    CapitalHkModel::jianMoney($hx['data_id'], $hx['hx_money'], $hx['hx_zhongliang']);
                }
            }
            $sk->status = 2;
            $sk->save();
            Db::commit();
            return returnSuc();
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**
     * 客户少打钱列表
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function getLessMoneyHuizong(Request $request, $pageLimit = 10)
    {
        $params = $request->param();
        $model = CapitalSkModel::fieldRaw('sum(msmoney) as msmoney,customer_id')
            ->where('companyid', $this->getCompanyId())
            ->where('status', '<>', 2);
        if (!empty($params['ywsjStart'])) {
            $model->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $model->where('yw_time', '<', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['customer_id'])) {
            $model->where('customer_id', $params['customer_id']);
        }
        $subSql = $model->group('customer_id')->buildSql(true);
        $data = Custom::alias('c')
            ->fieldRaw('ifnull(sk.msmoney,0) as msmoney,c.id,c.custom,c.zjm,c.short_name')
            ->join($subSql . ' sk', 'sk.customer_id=c.id', 'LEFT')
            ->where('iscustom', 1)
            ->paginate($pageLimit);
        return returnSuc($data);
    }

}