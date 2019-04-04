<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/18
 * Time: 11:26
 */

namespace app\admin\controller;

use app\admin\validate\{FeiyongDetails, SalesorderDetails};
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    Request,
    response\Json,
    Session};

class Salesorder extends Base
{
    /**
     * 获取销售单列表
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function getlist(Request $request, $pageLimit = 10)
    {
        $params = $request->param();
        $list = \app\admin\model\Salesorder::with([
            'custom',
            'pjlxData',
            'jsfsData',
        ])->where('companyid', Session::get('uinfo.companyid', 'admin'));
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
            'other' => ['mingxi' => ['szmcData', 'pjlxData', 'custom']]
        ])
            ->where('companyid', Session::get('uinfo.companyid', 'admin'))
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
     * @return bool|string|Json|array
     * @throws \think\Exception
     */
    public function add(Request $request, $ywlx = 1, $data = [], $return = false)
    {
        if ($request->isPost()) {
            $companyId = Session::get('uinfo.companyid', 'admin');
            $count = \app\admin\model\Salesorder::whereTime('create_time', 'today')
                ->where('companyid', $companyId)
                ->count();

            //数据处理
            if (empty($data)) {
                $data = $request->post();
            }
            $data['add_name'] = Session::get("uinfo.name", "admin");
            $data['add_id'] = Session::get("uid", "admin");
            $data['companyid'] = $companyId;
            $data['system_no'] = 'XSD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
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
                foreach ($data['details'] as $c => $v) {
                    $data['details'][$c]['companyid'] = $companyId;
                    $data['details'][$c]['order_id'] = $id;
                    if (!$detailsValidate->check($data['details'][$c])) {
                        throw new Exception('请检查第' . $num . '行' . $detailsValidate->getError());
                    }
                    $num++;
                }
                Db::name('SalesorderDetails')->insertAll($data['details']);

                $num = 1;
                $otherValidate = new FeiyongDetails();
                if (!empty($data['other'])) {
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
                            'caizhi' => $v['caizhi'] ?? '',
                            'chandi' => $v['chandi'] ?? '',
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
                }

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
        if ($return) {
            return '请求方式错误';
        } else {
            return returnFail('请求方式错误');
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
            $salesorder->auditer = Session::get('uid', 'admin');
            $salesorder->audit_name = Session::get('uinfo.name', 'admin');
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
     * @return Json
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
            $salesorder->status = 2;
            $salesorder->save();
            return returnSuc();
        }
        return returnFail('请求方式错误');
    }
}