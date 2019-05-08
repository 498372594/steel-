<?php

namespace app\admin\controller;

use app\admin\model\CapitalFy;
use app\admin\model\CapitalFyhx;
use app\admin\model\CgPurchase;
use app\admin\model\CgTh;
use app\admin\model\ViewFySources;
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    db\Query,
    exception\DbException,
    Paginator,
    Request,
    response\Json};

class Feiyong extends Right
{
    /**
     * 添加费用单
     * @param Request $request
     * @return array|bool|string|Json
     */
    public function add(Request $request)
    {
        if (!$request->isPost()) {
            return returnFail('请求方式错误');
        }

        Db::startTrans();
        try {
            $data = $request->post();

            $validate = new \app\admin\validate\CapitalFy();
            if (!$validate->check($data)) {
                throw new Exception($validate->getError());
            }

            $addFyList = [];
            $updateFyList = [];
            $ja1 = $data['details'];
            $companyid = $this->getCompanyId();
            if (!empty($ja1)) {
                foreach ($ja1 as $object) {
                    $object['companyid'] = $companyid;

                    if (empty($object['id'])) {
                        $addFyList[] = $object;
                    } else {
                        $updateFyList[] = $object;
                    }
                }
            }

            if (empty($data['id'])) {

                $count = CapitalFy::withTrashed()
                    ->whereTime('create_time', 'today')
                    ->where('companyid', $companyid)
                    ->count();

                $data['companyid'] = $companyid;
                $data['system_number'] = 'FYD' . date('Ymd') . str_pad(++$count, 3, 0, STR_PAD_LEFT);
                $data['create_operator_id'] = $this->getAccountId();
                $data['fymx_create_type'] = 2;
                $data['yw_type'] = 2;

                $sk = new CapitalFy();
                $sk->allowField(true)->data($data)->save();

                (new \app\admin\model\Inv())->insertInv($sk['id'], 7, $sk['fang_xiang'], null, null, null, null, null, $sk['piaoju_id'], null, $sk['system_number'], $sk['customer_id'], $sk['yw_time'], $sk['danjia'], $sk['tax_rate'], $sk['money'], $sk['price_and_tax'], $sk['zhongliang'], $companyid);
            } else {
                $sk = CapitalFy::get($data['id']);
                if (empty($sk)) {
                    throw new Exception("对象不存在");
                }
                if ($sk['status'] == 2) {
                    throw new Exception("该单据已经作废");
                }
                if ($sk['fymx_create_type'] == 1) {
                    throw new Exception("业务生成费用单,请修改原单");
                }
                $data['update_operator_id'] = $this->getAccountId();
                $sk->isUpdate(true)->allowField(true)->save($data);

                (new \app\admin\model\Inv())->updateInv($sk['id'], 7, $sk['fang_xiang'], $sk['customer_id'], $sk['yw_time'], null, null, null, null, null, $sk['piaoju_id'], null, $sk['zhongliang'], $sk['danjia'], $sk['money'], $sk['price_and_tax'], $sk['tax_rate']);
            }

            if (!empty($data['deleteFyIds'])) {
                CapitalFyhx::destroy(function (Query $query) use ($data) {
                    $query->where('id', 'in', $data['deleteFyIds']);
                });
            }

            foreach ($updateFyList as $obj) {
                $hx = CapitalFyhx::get($obj['id']);
                $hx->allowField(true)->isUpdate(true)->save($obj);
            }

            foreach ($addFyList as $obj) {

                if ($obj['fyhx_type'] == 0) {
                    $relation = CgPurchase::get($obj['data_id']);
                } elseif ($obj['fyhx_type'] == 1) {
                    $sale = \app\admin\model\Salesorder::get($obj['data_id']);
                } elseif ($obj['fyhx_type'] == 4) {
                    //采购退货
                    $relation = CgTh::get($obj['data_id']);
                } elseif ($obj['fyhx_type'] == 5) {
                    //销售退货
                    $relation = \app\admin\model\SalesReturn::get($obj['data_id']);
                }
                if (!empty($relation)) {
                    $obj['cache_yw_time'] = $relation['yw_time'];
                    $obj['customer_id'] = $relation['customer_id'];
                } elseif (!empty($sale)) {
                    $obj['cache_yw_time'] = $sale['ywsj'];
                    $obj['customer_id'] = $sale['custom_id'];
                }
                $obj['cap_fy_id'] = $sk['id'];

                (new CapitalFyhx())->allowField(true)->data($obj)->save();
            }

            Db::commit();
            return returnSuc(['id' => $sk['id']]);
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**
     * 获取费用单列表
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
        $list = CapitalFy::with([
            'departmentData',
            'custom',
            'pjlxData',
            'szflData',
            'szmcData',
            'saleOperator'
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
        if (!empty($params['group_id'])) {
            $list->where('group_id', $params['group_id']);
        }
        if (!empty($params['sale_operator_id'])) {
            $list->where('sale_operator_id', $params['sale_operator_id']);
        }
        if (!empty($params['beizhu'])) {
            $list->where('beizhu', 'like', '%' . $params['beizhu'] . '%');
        }
        $list = $list->paginate($pageLimit);
        return returnRes(true, '', $list);

    }

    /**
     * 获取费用单详情
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
        $data = CapitalFy::with([
            'custom',
            'pjlxData',
            'szmcData',
            'details' => ['custom'],
            'saleOperator',
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
            $fy = CapitalFy::get($id);
            if (empty($fy)) {
                throw new Exception("对象不存在");
            }
            if ($fy->companyid != $this->getCompanyId()) {
                throw new Exception("对象不存在");
            }
            if ($fy['status'] == 2) {
                throw new Exception("该单据已经作废");
            }

            if ($fy['fymx_create_type'] == 1) {
                throw new Exception("业务生成费用单,请操作原单");
            }

            (new \app\admin\model\Inv())->deleteInv($fy['id'], 7);
            $fy->status = 2;
            $fy->save();

            CapitalFyhx::destroy(function (Query $query) use ($fy) {
                $query->where('cap_fy_id', $fy['id']);
            });
            Db::commit();
            return returnSuc();
        } catch (Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**
     * 获取源单
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function getSources(Request $request, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $params = $request->param();
        $list = ViewFySources::with('custom')
            ->where('companyid', $this->getCompanyId())
            ->where('status', '<>', 2)
            ->order('yw_time', 'desc');
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('yw_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['customer_id'])) {
            $list->where('customer_id', $params['customer_id']);
        }
        if (isset($params['type']) && $params['type'] !== '') {
            $list->where('type_id', $params['type']);
        }
        $list = $list->paginate($pageLimit);
        return returnRes(true, '', $list);
    }

    /**
     * 费用统计表
     * @param Request $request
     * @param int $pageLimit
     * @return Paginator|Json
     * @throws DbException
     */
    public function tongji(Request $request, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $params = $request->param();
        $model = CapitalFy::with(['customForTongji', 'szflData', 'szmcData'])
            ->alias('fy')
            ->join('__CUSTOM__ c', 'c.id=fy.customer_id')
            ->field('customer_id,c.custom,sum(money) as jine,shouzhimingcheng_id,shouzhifenlei_id,moren_yewuyuan,suoshu_department')
            ->where('fy.status', '<>', 2);
        if (!empty($params['sales_operator_id'])) {
            $model->where('c.moren_yewuyuan', $params['sales_operator_id']);
        }
        if (!empty($params['group_id'])) {
            $model->where('c.suoshu_department', $params['group_id']);
        }
        if (!empty($params['ywsjStart'])) {
            $model->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $model->where('yw_time', '<', date('Y-m-d H:i:s', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['customer_id'])) {
            $model->where('customer_id', $params['customer_id']);
        }
        if (!empty($params['szlb'])) {
            $model->where('shouzhifenlei_id', $params['szlb']);
        }
        if (!empty($params['szmc'])) {
            $model->where('shouzhimingcheng_id', $params['szmc']);
        }
        $data = $model->where('fy.companyid', $this->getCompanyId())
            ->group('customer_id,shouzhimingcheng_id')
            ->order('yw_time', 'desc')
            ->paginate($pageLimit);
        return returnSuc($data);
    }

    /**
     * 费用明细表
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function mingxi(Request $request, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $params = $request->param();
        $model = CapitalFy::with(['customForTongji', 'szflData', 'szmcData'])
            ->alias('fy')
            ->join('__CAPITAL_FYHX__ hx', 'hx.cap_fy_id=fy.id')
            ->join('__CUSTOM__ c', 'c.id=fy.customer_id')
            ->fieldRaw('fy.id,ifnull(hx.fyhx_type,99) as fyType,hx.data_id,fy.status,fy.system_number,fy.yw_time,fy.customer_id,fy.money,shouzhimingcheng_id,shouzhifenlei_id,moren_yewuyuan,suoshu_department,fy.beizhu,c.custom');
        if (!empty($params['sales_operator_id'])) {
            $model->where('c.moren_yewuyuan', $params['sales_operator_id']);
        }
        if (!empty($params['group_id'])) {
            $model->where('c.suoshu_department', $params['group_id']);
        }
        if (!empty($params['status'])) {
            $model->where('fy.status', $params['status']);
        }
        if (!empty($params['ywsjStart'])) {
            $model->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $model->where('yw_time', '<', date('Y-m-d H:i:s', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['customer_id'])) {
            $model->where('fy.customer_id', $params['customer_id']);
        }
        if (!empty($params['szlb'])) {
            $model->where('shouzhifenlei_id', $params['szlb']);
        }
        if (!empty($params['szmc'])) {
            $model->where('shouzhimingcheng_id', $params['szmc']);
        }
        if (!empty($params['system_number'])) {
            $model->where('fy.system_number', 'like', '%' . $params['system_number'] . '%');
        }
        $data = $model->where('fy.companyid', $this->getCompanyId())
            ->order('yw_time', 'desc')
            ->paginate($pageLimit);
        return returnSuc($data);
    }
}