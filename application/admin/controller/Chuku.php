<?php

namespace app\admin\controller;

use app\admin\model\KcSpot;
use app\admin\model\KucunCktz;
use app\admin\model\StockOut;
use app\admin\model\StockOutDetail;
use app\admin\model\StockOutMd;
use Exception;
use think\{Db, exception\DbException, Request, response\Json, Session};

class Chuku extends Right
{
    /**
     * 添加出库通知单
     * @param array $data
     */
    public function addNotify($data = [])
    {
        if (empty($data)) {
            return;
        }
        $now = time();
        foreach ($data as $index => $item) {
            $data[$index]['create_time'] = $now;
            $data[$index]['update_time'] = $now;
        }
        Db::name('KucunCktz')->insertAll($data);
    }

    /**
     * 获取出库通知单
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
        ])->where('companyid', Session::get('uinfo.companyid', 'admin'));
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
     * 添加出库单
     * @param Request $request
     * @param array $data 出库数据
     * @param array $stockOutDetails 出库明细
     * @param int $outType 出库类型
     * @param int $outMode 出库方式
     * @param bool $return 是否返回
     * @return array|bool|string|Json
     * @throws \think\Exception
     */
    public function add(Request $request, $data = [], $stockOutDetails = [], $outType = 4, $outMode = 2, $return = false)
    {
        if (!$request->isPost()) {
            if ($return) {
                return '请求方式错误';
            } else {
                return returnFail('请求方式错误');
            }
        }
        $companyId = Session::get('uinfo.companyid', 'admin');
        $count = StockOut::whereTime('create_time', 'today')
            ->where('companyid', $companyId)
            ->count();

        //数据处理
        if (empty($data)) {
            $data = $request->post();
        }
        $data['companyid'] = $companyId;
        $data['system_number'] = 'CKD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
        $data['create_operator_id'] = Session::get("uid", "admin");
        $data['out_type'] = $outType;
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
                    $detailsData['out_type'] = $outType;
                    $detailsData['out_mode'] = $outMode;
                    $detailModel = new StockOutDetail();
                    $detailModel->allowField(true)->data($detailsData)->save();

                    $details[$v['kucun_cktz_id']] = $detailModel->id;
                }
                //根据码单内资源单id获取资源单
                $resource = KcSpot::get($v['kc_spot_id']);
                if (empty($resource)) {
                    throw new Exception('未找到库存资源');
                }
                if ($resource['zhongliang'] - $v['zhongliang'] < 0) {
                    throw new Exception('不允许出现负库存');
                }
                //更新库存
                $resource->zhongliang -= $v['zhongliang'];
                $resource->save();

                //生成码单
                $madan = $resource->getData();
                unset($madan['id'], $madan['create_time'], $madan['update_time'], $madan['delete_time']);
                $madan['stock_out_id'] = $id;
                $madan['out_type'] = $outType;
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

}