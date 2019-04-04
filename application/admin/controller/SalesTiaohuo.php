<?php

namespace app\admin\controller;

use app\admin\model\SalesMoshi;
use app\admin\validate\{SalesMoshiDetails};
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    Request,
    response\Json,
    Session};

class SalesTiaohuo extends Base
{
    /**
     * 获取采购直发单列表
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function getlist(Request $request, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $params = $request->param();
        $list = SalesMoshi::with(['custom', 'pjlxData', 'jsfsData'])
            ->where('companyid', Session::get('uinfo.companyid', 'admin'))
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
            'pjlxData',
            'jsfsData',
            'details' => ['specification', 'thJsfsData', 'thPjlxData', 'storage', 'xsJsfsData', 'wldwData'],
            'other' => ['other' => ['mingxi' => ['szmcData', 'pjlxData', 'custom']]]
        ])
            ->where('companyid', Session::get('uinfo.companyid', 'admin'))
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
     * 添加采购直发单
     * @param Request $request
     * @return Json
     * @throws \think\Exception
     */
    public function add(Request $request)
    {
        if ($request->isPost()) {
            $companyId = Session::get('uinfo.companyid', 'admin');
            $count = SalesMoshi::whereTime('create_time', 'today')
                ->where('moshi_type', 2)
                ->where('companyid', $companyId)
                ->count();

            //获取请求数据
            $data = $request->post();
            $data['create_operator_id'] = Session::get("uid", "admin");
            $data['moshi_type'] = 2;
            $data['companyid'] = $companyId;
            $data['system_no'] = 'THXSD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);

            //验证数据
            $validate = new \app\admin\validate\SalesMoshi();
            if (!$validate->scene('tiaohuo')->check($data)) {
                return returnFail($validate->getError());
            }

            Db::startTrans();
            try {
                $model = new SalesMoshi();
                $model->allowField(true)->data($data)->save();

                //处理明细
                $id = $model->getLastInsID();
                $num = 1;
                $detailsValidate = new SalesMoshiDetails();
                $now = time();
                foreach ($data['details'] as $c => $v) {
                    $data['details'][$c]['companyid'] = $companyId;
                    $data['details'][$c]['moshi_id'] = $id;
                    $data['details'][$c]['create_time'] = $now;
                    $data['details'][$c]['update_time'] = $now;

                    if (!$detailsValidate->scene('tiaohuo')->check($data['details'][$c])) {
                        throw new Exception('请检查第' . $num . '行' . $detailsValidate->getError());
                    }
                    $num++;
                }
                Db::name('SalesMoshiMx')->insertAll($data['details']);

                //添加采购单
                $purchases = [];
                foreach ($data['details'] as $c => $v) {
                    $index = $v['cg_customer_id'] . 'dw_pj' . $v['cg_piaoju_id'];
                    if (!isset($purchases[$index])) {
                        $purchases[$index] = [
                            'customer_id' => $v['cg_customer_id'],
                            'piaoju_id' => $v['cg_piaoju_id'],
                            'beizhu' => $data['remark'] ?? '',
                            'yw_time' => $data['yw_time'],
                            'group_id' => $data['department'] ?? '',
                            'sale_operate_id' => $data['employer'] ?? '',
                            'ruku_fangshi' => 1,
                            'data_id' => $id
                        ];
                    }
                    $purchases[$index]['details'][] = [
                        'pinming_id' => $v['pinming_id'],
                        'guige_id' => $v['guige_id'],
                        'caizhi_id' => $v['caizhi'] ?? '',
                        'chandi_id' => $v['chandi'] ?? '',
                        'jijiafangshi_id' => $v['jijiafangshi_id'],
                        'houdu' => $v['houdu'] ?? '',
                        'kuandu' => $v['kuandu'] ?? '',
                        'store_id' => $v['store_id'],
                        'changdu' => $v['changdu'] ?? '',
                        'lingzhi' => $v['cg_lingzhi'] ?? '',
                        'jianshu' => $v['cg_jianshu'] ?? '',
                        'zhijian' => $v['zhijian'] ?? '',
                        'counts' => $v['cg_counts'] ?? '',
                        'zhongliang' => $v['cg_zhongliang'] ?? '',
                        'price' => $v['cg_price'] ?? '',
                        'sumprice' => $v['cg_sumprice'] ?? '',
                        'shuie' => $v['cg_tax'] ?? '',
                        'chehao' => $v['chehao'] ?? '',
                        'huohao' => $v['huohao'] ?? '',
                        'sum_shui_price' => $v['cg_sum_shui_price'] ?? '',
                        'beizhu' => $v['beizhu'] ?? '',
                        'pihao' => $v['pihao'] ?? '',
                        'shui_price' => $v['cg_tax_rate'] ?? '',
                        'mizhong' => $v['mizhong'] ?? '',
                        'jianzhong' => $v['jianzhong'] ?? '',
                    ];
                }
                $purchaseObj = new Purchase();
                foreach ($purchases as $v) {
                    $salesRes = $purchaseObj->purchaseadd($request, 1, $v, true);
                    if ($salesRes !== true) {
                        throw new Exception($salesRes);
                    }
                }

                //添加销售单
                $salesOrder = [
                    'custom_id' => $data['customer_id'],
                    'pjlx' => $data['piaoju_id'],
                    'jsfs' => $data['jsfs'] ?? '',
                    'ckfs' => 1,
                    'contact' => $data['contact'] ?? '',
                    'mobile' => $data['telephone'] ?? '',
                    'remark' => $data['remark'] ?? '',
                    'department' => $data['department'] ?? '',
                    'employer' => $data['employer'] ?? '',
                    'ywsj' => $data['yw_time'],
                    'car_no' => $data['chehao'] ?? '',
                    'data_id' => $id
                ];
                foreach ($data['details'] as $c => $v) {
                    $salesOrder['details'][] = [
                        'storage_id' => $v['store_id'],
                        'wuzi_id' => $v['guige_id'],
                        'caizhi' => $v['caizhi'] ?? '',
                        'chandi' => $v['chandi'] ?? '',
                        'jsfs_id' => $v['jijiafangshi_id'],
                        'length' => $v['changdu'] ?? '',
                        'houdu' => $v['houdu'] ?? '',
                        'width' => $v['kuandu'] ?? '',
                        'lingzhi' => $v['lingzhi'] ?? '',
                        'num' => $v['jianshu'] ?? '',
                        'jzs' => $v['zhijian'] ?? '',
                        'count' => $v['counts'] ?? '',
                        'weight' => $v['zhongliang'],
                        'price' => $v['price'],
                        'total_fee' => $v['sumprice'] ?? '',
                        'tax_rate' => $v['tax_rate'] ?? '',
                        'tax' => $v['tax'] ?? '',
                        'price_and_tax' => $v['price_and_tax'] ?? '',
                        'remark' => $v['beizhu'] ?? '',
                        'car_no' => $v['chehao'] ?? '',
                        'batch_no' => $v['pihao'] ?? '',
                    ];
                }
                $salesOrder['other'] = $data['other'] ?? [];
                $salesRes = (new Salesorder())->add($request, 3, $salesOrder, true);
                if ($salesRes !== true) {
                    throw new Exception($salesRes);
                }

                Db::commit();
                return returnRes(true, '', ['id' => $id]);
            } catch (Exception $e) {
                Db::rollback();
                return returnFail($e->getMessage());
            }
        }
        return returnFail('请求方式错误');
    }

    /**
     * 审核
     * @param Request $request
     * @param int $id
     * @return Json
     * @throws DbException
     */
    public function audit(Request $request, $id = 0)
    {
        if ($request->isPut()) {
            $salesTiaohuo = SalesMoshi::get($id);
            if (empty($salesTiaohuo)) {
                return returnFail('数据不存在');
            }
            if ($salesTiaohuo->status == 2) {
                return returnFail('此单已作废');
            }
            if ($salesTiaohuo->status == 3) {
                return returnFail('此单已审核');
            }
            $salesTiaohuo->status = 3;
            $salesTiaohuo->audit_id = Session::get('uid', 'admin');
            $salesTiaohuo->audit_name = Session::get('uinfo.name', 'admin');
            $salesTiaohuo->save();
            (new Salesorder())->audit($request, $id, 3, false);

            //todo 审核采购单
            return returnSuc();
        }
        return returnFail('请求方式错误');
    }

    /**
     * 反审核
     * @param Request $request
     * @param int $id
     * @return Json
     * @throws DbException
     */
    public function unAudit(Request $request, $id = 0)
    {
        if ($request->isPut()) {
            $cgzfd = SalesMoshi::get($id);
            if (empty($cgzfd)) {
                return returnFail('数据不存在');
            }
            if ($cgzfd->status == 2) {
                return returnFail('此单已作废');
            }
            if ($cgzfd->status == 1) {
                return returnFail('此单未审核');
            }
            $cgzfd->status = 1;
            $cgzfd->audit_id = null;
            $cgzfd->audit_name = '';
            $cgzfd->save();
            (new Salesorder())->unAudit($request, $id, 3, false);

            //todo 反审核采购单
            return returnSuc();
        }
        return returnFail('请求方式错误');
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
            $cgzfd->status = 2;
            $cgzfd->save();
            (new Salesorder())->cancel($request, $id, 3, false);

            //todo 作废采购单
            return returnSuc();
        }
        return returnFail('请求方式错误');
    }
}