<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/25
 * Time: 16:36
 */

namespace app\admin\controller;

use app\admin\model\Cgzfd;
use app\admin\validate\{CgzfdDetails, CgzfdOther};
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
        $list = Cgzfd::with([
            'custom',
            'gongyingshang',
            'gfpjData',
            'khpjData',
            'gfjsfsData',
            'khjsfsData',
        ])->where('companyid', Session::get('uinfo.companyid', 'admin'));
        if (!empty($params['ywsjStart'])) {
            $list->where('ywsj', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('ywsj', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['cgpb'])) {
            $list->where('gfpj', $params['cgpb']);
        }
        if (!empty($params['status'])) {
            $list->where('status', $params['status']);
        }
        if (!empty($params['system_no'])) {
            $list->where('system_no', 'like', '%' . $params['system_no'] . '%');
        }
        if (!empty($params['custom_id'])) {
            $list->where('kh_id', $params['custom_id']);
        }
        if (!empty($params['xspb'])) {
            $list->where('khpj', $params['xspb']);
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
        $data = Cgzfd::with([
            'custom',
            'gongyingshang',
            'gfpjData',
            'khpjData',
            'gfjsfsData',
            'khjsfsData',
            'details' => ['specification', 'jsfs', 'storage'],
            'other' => ['szmcData', 'pjlxData', 'custom']
        ])->where('companyid', Session::get('uinfo.companyid', 'admin'))
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
            $count = Cgzfd::whereTime('create_time', 'today')->count();
            $companyId = Session::get('uinfo.companyid', 'admin');

            //获取请求数据
            $data = $request->post();
            $data['add_name'] = Session::get("uinfo.name", "admin");
            $data['add_id'] = Session::get("uid", "admin");
            $data['companyid'] = $companyId;
            $data['system_no'] = 'CGZFD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            $data['ywlx'] = 1;

            //验证数据
            $validate = new \app\admin\validate\Cgzfd();
            if (!$validate->check($data)) {
                return returnFail($validate->getError());
            }

            Db::startTrans();
            try {
                $model = new Cgzfd();
                $model->allowField(true)->data($data)->save();

                //处理明细
                $id = $model->getLastInsID();
                $num = 1;
                $detailsValidate = new CgzfdDetails();
                foreach ($data['details'] as $c => $v) {
                    $data['details'][$c]['companyid'] = $companyId;
                    $data['details'][$c]['order_id'] = $id;

                    if (!$detailsValidate->check($data['details'][$c])) {
                        throw new Exception('请检查第' . $num . '行' . $detailsValidate->getError());
                    }
                    $num++;
                }
                Db::name('CgzfdDetails')->insertAll($data['details']);

                //处理其他费用
                $num = 1;
                $otherValidate = new CgzfdOther();
                $nowDate = date('Y-m-d H:i:s');
                if (!empty($data['other'])) {
                    foreach ($data['other'] as $c => $v) {
                        $data['other'][$c]['order_id'] = $id;
                        $data['other'][$c]['date'] = $nowDate;
                        if (!$otherValidate->check($data['other'][$c])) {
                            throw new Exception('请检查第' . $num . '行' . $otherValidate->getError());
                        }
                        $num++;
                    }
                    Db::name('CgzfdOther')->insertAll($data['other']);
                }

                //添加采购单
                $purchase = [
                    'rkfs' => 2,
                    'supplier_id' => $data['gys_id'],
                    'pjlx' => $data['gfpj'],
                    'remark' => $data['remark'] ?? '',
                    'service_time' => $data['ywsj'],
                    'department' => $data['department'] ?? '',
                    'employer' => $data['employer'] ?? '',
                    'data_id' => $id
                ];
                foreach ($data['details'] as $c => $v) {
                    $purchase['details'][] = [
                        'storage_id' => $v['storage_id'],
                        'productname' => $v['name'] ?? '',
                        'specification' => $v['guige'] ?? '',
                        'texture' => $v['caizhi'] ?? '',
                        'originarea' => $v['chandi'] ?? '',
                        'length' => $v['length'] ?? '',
                        'houdu_name' => $v['houdu'] ?? '',
                        'width' => $v['width'] ?? '',
                        'm_heavy' => $v['mizhong'] ?? '',
                        'jsfs' => $v['jsfs_id'],
                        'lingzhi' => $v['in_lingzhi'] ?? '',
                        'jianshu' => $v['in_jianshu'] ?? '',
                        'jianzhishu' => $v['jzs'] ?? '',
                        'shuliang' => $v['in_number'] ?? '',
                        'unit_price' => $v['in_price'],
                        'total_price' => $v['in_price_and_tax'] ?? '',
                        'remark' => $v['remark'] ?? '',
                        'tax_rate' => $v['in_tax_rate'] ?? '',
                        'tax_price' => $v['in_tax'] ?? '',
                        'heavy' => $v['in_weight'],
                        'productname_id' => $v['wuzi_id'],
                    ];
                }
                $salesRes = (new Purchase())->purchaseadd(2, $purchase, true);
                if ($salesRes !== true) {
                    throw new Exception($salesRes);
                }

                //添加销售单
                $salesOrder = [
                    'custom_id' => $data['kh_id'],
                    'pjlx' => $data['khpj'],
                    'jsfs' => $data['khjsfs'] ?? '',
                    'ckfs' => 1,
                    'contact' => $data['contact'] ?? '',
                    'mobile' => $data['mobile'] ?? '',
                    'remark' => $data['remark'] ?? '',
                    'department' => $data['department'] ?? '',
                    'employer' => $data['employer'] ?? '',
                    'ywsj' => $data['ywsj'],
                    'car_no' => $data['car_no'] ?? '',
                    'data_id' => $id
                ];
                foreach ($data['details'] as $c => $v) {
                    $salesOrder['details'][] = [
                        'storage_id' => $v['storage_id'],
                        'wuzi_id' => $v['wuzi_id'],
                        'caizhi' => $v['caizhi'] ?? '',
                        'chandi' => $v['chandi'] ?? '',
                        'jsfs_id' => $v['jsfs_id'],
                        'length' => $v['length'] ?? '',
                        'lingzhi' => $v['out_lingzhi'] ?? '',
                        'num' => $v['out_jianshu'] ?? '',
                        'jzs' => $v['jzs'] ?? '',
                        'count' => $v['out_number'] ?? '',
                        'weight' => $v['out_weight'],
                        'price' => $v['out_price'],
                        'total_fee' => $v['out_total_fee'] ?? '',
                        'tax_rate' => $v['out_tax_rate'] ?? '',
                        'tax' => $v['out_tax'] ?? '',
                        'price_and_tax' => $v['out_price_and_tax'] ?? '',
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
            $cgzfd = Cgzfd::get($id);
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
            $cgzfd = Cgzfd::get($id);
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
            $cgzfd = Cgzfd::get($id);
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