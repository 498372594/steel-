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

class Zhifa extends Base
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
        $list = SalesMoshi::with([
            'custom',
            'gongyingshang',
            'gfpjData',
            'khpjData',
            'gfjsfsData',
            'khjsfsData',
        ])->where('companyid', Session::get('uinfo.companyid', 'admin'))
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
        ])->where('companyid', Session::get('uinfo.companyid', 'admin'))
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
     * @throws \think\Exception
     */
    public function add(Request $request)
    {
        if ($request->isPost()) {
            $companyId = Session::get('uinfo.companyid', 'admin');
            $count = SalesMoshi::whereTime('create_time', 'today')
                ->where('companyid', $companyId)
                ->where('moshi_type', 1)
                ->count();

            //获取请求数据
            $data = $request->post();
            $data['create_operator_id'] = Session::get("uid", "admin");
            $data['moshi_type'] = 1;
            $data['companyid'] = $companyId;
            $data['system_number'] = 'CGZFD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);

            //验证数据
            $validate = new \app\admin\validate\SalesMoshi();
            if (!$validate->scene('zhifa')->check($data)) {
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

                    if (!$detailsValidate->scene('zhifa')->check($data['details'][$c])) {
                        throw new Exception('请检查第' . $num . '行' . $detailsValidate->getError());
                    }
                    $num++;
                }
                Db::name('SalesMoshiMx')->insertAll($data['details']);

                //添加采购单
                $purchase = [
                    'customer_id' => $data['cg_customer_id'],
                    'jiesuan_id' => $data['cg_jiesuan_id'] ?? '',
                    'piaoju_id' => $data['cg_piaoju_id'],
                    'beizhu' => $data['remark'] ?? '',
                    'yw_time' => $data['yw_time'],
                    'group_id' => $data['department'] ?? '',
                    'sale_operate_id' => $data['employer'] ?? '',
                    'ruku_fangshi' => 1,
                    'data_id' => $id
                ];
                foreach ($data['details'] as $c => $v) {
                    $purchase['details'][] = [
                        'pinming_id' => $v['pinming_id'],
                        'guige_id' => $v['guige_id'],
                        'caizhi_id' => $v['caizhi'] ?? '',
                        'chandi_id' => $v['chandi'] ?? '',
                        'jijiafangshi_id' => $v['jijiafangshi_id'],
                        'houdu' => $v['houdu'] ?? '',
                        'kuandu' => $v['kuandu'] ?? '',
                        'store_id' => $v['store_id'],
                        'changdu' => $v['changdu'] ?? 0,
                        'lingzhi' => $v['cg_lingzhi'] ?? '',
                        'jianshu' => $v['cg_jianshu'] ?? '',
                        'zhijian' => $v['zhijian'] ?? 0,
                        'counts' => $v['cg_counts'] ?? 0,
                        'zhongliang' => $v['cg_zhongliang'] ?? 0,
                        'price' => $v['cg_price'] ?? '',
                        'sumprice' => $v['cg_sumprice'] ?? '',
                        'shuie' => $v['cg_tax'] ?? '',
                        'chehao' => $v['chehao'] ?? '',
                        'huohao' => $v['huohao'] ?? '',
                        'sum_shui_price' => $v['cg_sum_shui_price'] ?? '',
                        'beizhu' => $v['beizhu'] ?? '',
                        'pihao' => $v['pihao'] ?? '',
                        'shui_price' => $v['cg_tax_rate'] ?? '',
                        'mizhong' => $v['mizhong'] ?? 0,
                        'jianzhong' => $v['jianzhong'] ?? '',
                    ];
                }
                $salesRes = (new Purchase())->purchaseadd($request, 2, $purchase, true);
                if ($salesRes !== true) {
                    throw new Exception($salesRes);
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
                $salesRes = (new Salesorder())->add($request, 2, $salesOrder, true);
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
            $cgzfd = SalesMoshi::get($id);
            if (empty($cgzfd)) {
                return returnFail('数据不存在');
            }
            if ($cgzfd->status == 2) {
                return returnFail('此单已作废');
            }
            if ($cgzfd->status == 3) {
                return returnFail('此单已审核');
            }
            $cgzfd->status = 3;
            $cgzfd->auditer = Session::get('uid', 'admin');
            $cgzfd->audit_name = Session::get('uinfo.name', 'admin');
            $cgzfd->save();
            (new Salesorder())->audit($request, $id, 2, false);

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
            $cgzfd->auditer = null;
            $cgzfd->audit_name = '';
            $cgzfd->save();
            (new Salesorder())->unAudit($request, $id, 2, false);

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
            (new Salesorder())->cancel($request, $id, 2, false);

            //todo 作废采购单
            return returnSuc();
        }
        return returnFail('请求方式错误');
    }
}