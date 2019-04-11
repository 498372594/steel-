<?php

namespace app\admin\controller;

use app\admin\model\{KcSpot, KcYlSh, KucunCktz, StockOut, StockOutDetail, StockOutMd};
use Exception;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    Request,
    response\Json};

class Chuku extends Right
{
    /**
     * 添加出库通知单
     * @param array $data
     * @throws Exception
     */
    public function addNotify($data = [])
    {
        if (empty($data)) {
            return;
        }
        (new KucunCktz())->allowField(true)->saveAll($data);
    }

    /**
     * 清理出库通知
     * @param $dataId
     * @param $type
     */
    public function cancelNotify($dataId, $type)
    {
        KucunCktz::where('kucun_type', $type)->where('data_id', $dataId)->delete();
    }

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
            $list->where('is_done', $params['id_done'] - 1);
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
        $list = StockOut::where('companyid', $this->getCompanyId())
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
     * @param array $data 出库数据
     * @param array $stockOutDetails 出库明细
     * @param int $outMode 出库方式
     * @param bool $return 是否返回
     * @return array|bool|string|Json
     * @throws \think\Exception
     */
    public function add(Request $request, $data = [], $stockOutDetails = [], $outMode = 2, $return = false)
    {
        if (!$request->isPost()) {
            if ($return) {
                return '请求方式错误';
            } else {
                return returnFail('请求方式错误');
            }
        }
        $companyId = $this->getCompanyId();
        $count = StockOut::whereTime('create_time', 'today')
            ->where('companyid', $companyId)
            ->count();

        //数据处理
        if (empty($data)) {
            $data = $request->post();
        }
        $data['companyid'] = $companyId;
        $data['system_number'] = 'CKD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
        $data['create_operator_id'] = $this->getAccountId();
        $data['out_mode'] = $outMode;

        //数据验证
        $validate = new \app\admin\validate\StockOut();
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
            $model = new StockOut();
            $model->allowField(true)->data($data)->save();

            //处理明细
            $id = $model->getLastInsID();

            //明细单id列表
            $details = [];
            foreach ($data['details'] as $c => $v) {
                if (empty($v['zhongliang'])) {
                    throw new Exception('请填写重量');
                }

                //前端上传码单数据，包括出库通知单id
                if (empty($stockOutDetails[$v['kucun_cktz_id']])) {
                    //获取出库通知单
                    $stockOutNotify = KucunCktz::get($v['kucun_cktz_id']);
                    if (empty($stockOutNotify)) {
                        throw new Exception('未找到出库通知单');
                    }
                    //判断是否超重出库
                    if ($v['zhongliang'] > $stockOutNotify['zhongliang']) {
                        throw new Exception('禁止超重出库');
                    }
                    //减少待出库重量
                    $stockOutNotify->zhongliang -= $v['zhongliang'];
                    $stockOutNotify->save();
                    $stockOutDetails[$v['kucun_cktz_id']] = $stockOutNotify->getData();
                }

                if (!isset($details[$v['kucun_cktz_id']])) {
                    //根据出库通知单数据生成明细单
                    $detailsData = $stockOutDetails[$v['kucun_cktz_id']];
                    unset($detailsData['id'], $detailsData['create_time'], $detailsData['update_time'], $detailsData['delete_time']);
                    $detailsData['stock_out_id'] = $id;
                    $detailsData['kucun_cktz_id'] = $v['kucun_cktz_id'] <= 0 ? null : $v['kucun_cktz_id'];
                    $detailsData['out_type'] = $detailsData['chuku_type'];
                    $detailsData['out_mode'] = $outMode;
                    $detailModel = new StockOutDetail();
                    $detailModel->allowField(true)->data($detailsData)->save();

                    $details[$v['kucun_cktz_id']] = $detailModel->id;
                }

                //判断库存

                //根据码单内资源单id获取资源单
                $resource = KcSpot::get($v['kc_spot_id']);
                if (empty($resource)) {
                    throw new Exception('未找到库存资源');
                }
                if (!empty($v['ylsh_id'])) {
                    //锁货资源释放
                    $ylsh = KcYlSh::get($v['ylsh_id']);
                    if (empty($ylsh)) {
                        throw new Exception('未找到预留资源');
                    }
                    if ($ylsh->zhongliang < $v['zhongliang']) {
                        throw new Exception('销售重量不得大于预留重量');
                    }
                    if ($ylsh->shuliang < $v['counts']) {
                        throw new Exception('销售数量不得大于预留数量');
                    }
                    //更新预留数据
                    $ylsh->zhongliang -= $v['zhongliang'];
                    if ($v['counts'] != 0) {
                        $ylsh->shuliang -= $v['counts'];
                        if ($ylsh->zhijian != 0) {
                            $ylsh->jianshu = floor($ylsh->shuliang / $ylsh->zhijian);
                            $ylsh->lingzhi = $ylsh->shuliang - $ylsh->zhijian * $ylsh->jianshu;
                        } else {
                            $ylsh->lingzhi = $ylsh->shuliang;
                        }
                    }
                    $ylsh->save();
                } else {
                    //判断库存
                    $ylsh = KcYlSh::where('spot_id', $v['kc_spot_id'])
                        ->whereTime('baoliu_time', '>', time())
                        ->fieldRaw('sum(shuliang) as shuliang,sum(zhongliang) as zhongliang')
                        ->find();
                    if ($resource['zhongliang'] - $ylsh['zhongliang'] - $v['zhongliang'] < 0) {
                        throw new Exception('不允许出现负库存');
                    }

                    if ($resource['counts'] - $ylsh['shuliang'] - $v['counts'] < 0) {
                        throw new Exception('不允许出现负库存');
                    }
                }
                //更新库存
                $resource->zhongliang -= $v['zhongliang'];
                if ($v['counts'] != 0) {
                    $resource->counts -= $v['counts'];
                    if ($resource->zhijian != 0) {
                        $resource->jianshu = floor($resource->counts / $resource->zhijian);
                        $resource->lingzhi = $resource->counts - $resource->zhijian * $resource->jianshu;
                    } else {
                        $resource->lingzhi = $resource->counts;
                    }
                }
                $resource->save();

                //生成码单
                $madan = $resource->getData();
                unset($madan['id'], $madan['create_time'], $madan['update_time'], $madan['delete_time']);
                $madan['stock_out_id'] = $id;
                $madan['out_type'] = $detailsData['chuku_type'];
                $madan['out_mode'] = $outMode;
                $madan['caizhi'] = $resource['caizhi_id'];
                $madan['chandi'] = $resource['chandi_id'];
                $madan['stock_out_detail_id'] = $details[$v['kucun_cktz_id']];
                $madan = array_merge($madan, $v);
                $madanModel = new StockOutMd();
                $madanModel->allowField(true)->data($madan)->save();
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
     * 审核
     * @param Request $request
     * @param int $id
     * @return Json
     * @throws DbException
     */
    public function audit(Request $request, $id = 0)
    {
        if ($request->isPut()) {
            $stockOut = StockOut::where('id', $id)
                ->where('companyid', $this->getCompanyId())
                ->find();
            if (empty($stockOut)) {
                return returnFail('数据不存在');
            }
            if ($stockOut->status == 3) {
                return returnFail('此单已审核');
            }
            if ($stockOut->status == 2) {
                return returnFail('此单已作废');
            }
            $stockOut->status = 3;
            $stockOut->check_operator_id = $this->getAccountId();
            $stockOut->save();
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
            $stockOut = StockOut::where('id', $id)
                ->where('companyid', $this->getCompanyId())
                ->find();
            if (empty($stockOut)) {
                return returnFail('数据不存在或已作废');
            }
            if ($stockOut->status == 1) {
                return returnFail('此单未审核');
            }
            if ($stockOut->status == 2) {
                return returnFail('此单已作废');
            }
            $stockOut->status = 1;
            $stockOut->check_operator_id = null;
            $stockOut->save();
            return returnSuc();
        }
        return returnFail('请求方式错误');
    }

    /**
     * 作废
     * @param Request $request
     * @param int $id
     * @param bool $isWeb
     * @return bool|Json
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function cancel(Request $request, $id = 0, $isWeb = true)
    {
        if ($request->isPost()) {

            if ($isWeb) {
                $stockOut = StockOut::where('id', $id)
                    ->where('companyid', $this->getCompanyId())
                    ->find();
            } else {
                $stockOut = StockOut::where('data_id', $id)
                    ->where('companyid', $this->getCompanyId())
                    ->find();
            }
            if (empty($stockOut)) {
                return returnFail('数据不存在');
            }
            if (!empty($stockOut->data_id)) {
                if ($isWeb) {
                    return returnFail('此销售单禁止直接作废');
                } else {
                    $stockOut->status = 2;
                    $stockOut->check_operator_id = null;
                    $stockOut->save();
                    return true;
                }
            } elseif (!$isWeb) {
                throw new Exception('此单已有出库信息，禁止作废');
            }
            if ($stockOut->status == 3) {
                return returnFail('此单已审核，禁止作废');
            }
            if ($stockOut->status == 2) {
                return returnFail('此单已作废');
            }
            $stockOut->status = 2;
            $stockOut->save();
            return returnSuc();
        }
        return returnFail('请求方式错误');
    }

}