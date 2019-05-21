<?php


namespace app\admin\controller;


use app\admin\model\Bank;
use app\admin\model\CapitalBank;
use app\admin\model\CapitalShouruMx;
use Exception;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\db\Query;
use think\exception\DbException;
use think\Request;
use think\response\Json;

class CapitalShouru extends Right
{
    /**
     * 收入单列表
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
        $list = \app\admin\model\CapitalShouru::with([
            'custom',
            'createOperator',
            'updateOperator',
            'departmentData'
        ])->where('companyid', $this->getCompanyId())
            ->order('create_time', 'desc');
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('yw_time', '<', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['createTimeStart'])) {
            $list->whereTime('create_time', '>=', $params['createTimeStart']);
        }
        if (!empty($params['createTimeEnd'])) {
            $list->whereTime('create_time', '<', strtotime($params['createTimeEnd'] . ' +1 day'));
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
        if (!empty($params['create_operator_id'])) {
            $list->where('create_operator_id', $params['create_operator_id']);
        }
        if (!empty($params['group_id'])) {
            $list->where('group_id', $params['group_id']);
        }
        if (!empty($params['update_operator_id'])) {
            $list->where('update_operator_id', $params['update_operator_id']);
        }
        if (!empty($params['sale_operator_id'])) {
            $list->where('sale_operator_id', $params['sale_operator_id']);
        }
        $list = $list->paginate($pageLimit);
        return returnSuc($list);
    }

    /**
     * 获取收入单详情
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
        $data = \app\admin\model\CapitalShouru::with([
            'custom',
            'details' => ['bankData', 'szflData', 'szmcData'],
            'createOperator',
            'updateOperator'
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
     * 添加/修改收入单
     * @param Request $request
     * @return Json
     */
    public function add(Request $request)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }
        $companyid = $this->getCompanyId();
        Db::startTrans();
        try {
            $data = $request->post();

            $validate = new \app\admin\validate\CapitalShouru();
            if (!$validate->check($data)) {
                return returnFail($validate->getError());
            }

            $addList = [];
            $updateList = [];
            $ja = $data['details'];

            if (!empty($ja)) {
                $shouruMxValidate = new \app\admin\validate\CapitalShouruMx();
                foreach ($ja as $object) {
                    if (!$shouruMxValidate->check($object)) {
                        throw new Exception($shouruMxValidate->getError());
                    }
                    if (empty($object['id'])) {
                        $object['company_id'] = $companyid;
                        $addList[] = $object;
                    } else {
                        $updateList[] = $object;
                    }
                }
            }

            if (empty($data['id'])) {
                $sr = new \app\admin\model\CapitalShouru();
                $count = \app\admin\model\CapitalShouru::withTrashed()
                    ->where('companyid', $companyid)
                    ->whereTime('create_time', 'today')
                    ->count();
                $data['create_operator_id'] = $this->getAccountId();
                $data['system_number'] = 'SRD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
                $data['companyid'] = $companyid;
                $sr->allowField(true)->data($data)->save();

            } else {
                $sr = \app\admin\model\CapitalShouru::get($data['id']);
                if (empty($sr)) {
                    throw new Exception("对象不存在");
                }
                if ($sr['status'] == 2) {
                    throw new Exception("该单据已经作废");
                }
                $data['update_operator_id'] = $this->getAccountId();
                $sr->isUpdate(true)->allowField(true)->save($data);

                $srmxList = CapitalShouruMx::where('shouru_id', $sr['id'])->select();
                if (!empty($srmxList)) {
                    foreach ($srmxList as $obj) {
                        $bankList = CapitalBank::where('data_id', $obj['id'])->select();
                        if (!empty($bankList)) {
                            foreach ($bankList as $tbBank) {
                                $tbBank['yw_time'] = $sr['yw_time'];
                                $tbBank->save();
                            }
                        }
                    }
                }
            }

            if (!empty($data['deleteMxIds'])) {
                $deleteList = CapitalShouruMx::where('id', 'in', $data['deleteMxIds'])
                    ->where('shouru_id', $sr['id'])
                    ->select();
                foreach ($deleteList as $e) {
                    Bank::deleteBank($e['id'], 5, 1);
                }
                CapitalShouruMx::destroy(function (Query $query) use ($data, $sr) {
                    $query->where('id', 'in', $data['deleteMxIds'])
                        ->where('shouru_id', $sr['id']);
                });
            }

            foreach ($addList as $mjo) {
                $mx = new CapitalShouruMx();
                $mjo['shouru_id'] = $sr['id'];
                $mx->allowField(true)->data($mjo, true)->save();
                (new Bank)->insertBank($mx['id'], 5, $mx['bank_id'], 1, $sr['yw_time'], $mx['money'], $sr['customer_id'], $sr['system_number'], $this->getCompanyId());
            }

            foreach ($updateList as $mjo) {
                $mx = CapitalShouruMx::get($mjo['id']);
                $oldMoney = $mx['money'];
                $oldBankId = $mx['bank_id'];
                $mx->allowField(true)->save($mjo);
                (new Bank)->updateBank($mx['id'], $oldBankId, 5, $mx['bank_id'], 1, null, $mx['money'], $oldMoney);
            }

            Db::commit();
            return returnSuc(['id' => $sr['id']]);

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
     */
    public function cancel(Request $request, $id = 0)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }
        Db::startTrans();
        try {
            $sr = \app\admin\model\CapitalShouru::where('id', $id)->where('company_id', $this->getCompanyId())->find();
            if (empty($sr)) {
                throw new Exception("对象不存在");
            }
            if ($sr['status'] == 2) {
                throw new Exception("该单据已经作废");
            }

            $list = CapitalShouruMx::where('shouru_id', $sr['id'])->select();
            foreach ($list as $mx) {
                Bank::deleteBank($mx['id'], 5, 1);
            }
            $sr['status'] = 1;
            $sr->save();
            Db::commit();
            return returnSuc();
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }
}