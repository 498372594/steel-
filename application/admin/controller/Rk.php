<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use app\admin\model\KcRk;
use app\admin\model\{CgPurchase, KcRkMx, KcSpot};
use think\{Db, Request};
use think\Exception;
use think\Session;

class Rk extends Right
{
    /**入库单列表
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function getrk()
    {
        $params = request()->param();
        $list = \app\admin\model\KcRk::with([
            'custom',
        ])->where('companyid', Session::get("uinfo", "admin")['companyid']);
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('yw_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($instorageorderparams['status'])) {
            $list->where('status', $params['status']);
        }
        if (!empty($params['system_number'])) {
            $list->where('system_number', 'like', '%' . $params['system_number'] . '%');
        }
        if (!empty($params['beizhu'])) {
            $list->where('beizhu', 'like', '%' . $params['beizhu'] . '%');
        }
        $list = $list->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**入库单明细
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function getrkmx($id = 0)
    {
        $data = \app\admin\model\KcRk::with([
            'custom',
            'details' => ['specification', 'jsfs', 'storage', 'pinmingData', 'caizhiData', 'chandiData'],
        ])->where('companyid', Session::get('uinfo.companyid', 'admin'))
            ->where('id', $id)
            ->find();
        if (empty($data)) {
            return returnFail('数据不存在');
        } else {
            return returnRes(true, '', $data);
        }
    }

    /**获取待入库明细
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function getrktz()
    {
        $params = request()->param();
        $list = \app\admin\model\KcRkTz::where('companyid', Session::get("uinfo", "admin")['companyid']);
        $list->where("jianshu", ">", 0)->where("lingzhi", ">", 0)->where("counts", ">", 0);

        if (!empty($params['ids'])) {
            $list->where("id", "in", $params['ids']);
        }
        if (!empty($params['create_start'])) {
            $list->where('create_time', '>=', $params['create_start']);
        }
        if (!empty($params['create_end'])) {
            $list->where('yw_time', '<=', date('Y-m-d', strtotime($params['create_end'] . ' +1 day')));
        }
        if (!empty($params['store_id'])) {
            $list->where('store_id', $params['store_id']);
        }
        if (!empty($params['customer_id'])) {
            $list->where('customer_id', $params['customer_id']);
        }
        if (!empty($params['pinming_id'])) {
            $list->where('pinming_id', $params['pinming_id']);
        }
        if (!empty($params['system_number'])) {
            $list->where('system_number', 'like', '%' . $params['system_number'] . '%');
        }
        if (!empty($params['guige_id'])) {
            $list->where('guige_id', $params['guige_id']);
        }
        if (!empty($params['cache_customer_id'])) {
            $list->where('cache_customer_id', $params['cache_customer_id']);
        }
        if (!empty($params['is_done'])) {
            $list->where('is_done', $params['is_done']);
        }
        if (!empty($params['beizhu'])) {
            $list->where('remark', $params['remark']);
        }
        if (!empty($params['zhongliang'])) {
            $list->where("zhongliang", ">", 0);
        }
        $list = $list->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**入库
     * @param Request $request
     * @param int $moshi_type
     * @param array $data
     * @param bool $return
     * @return string|\think\response\Json
     * @throws \Exception
     */
    public function ruku(Request $request, $moshi_type = 4, $data = [], $return = false)
    {
        if ($request->isPost()) {
            $companyId = Session::get('uinfo.companyid', 'admin');
            //数据处理
            if (empty($data)) {
                $data = $request->post();
            }
            $data['create_operator'] = Session::get("uinfo.name", "admin");
            $data['create_operate_id'] = Session::get("uid", "admin");
            $data['companyid'] = $companyId;
            $data['moshi_type'] = $moshi_type;
            if (!$return) {
                Db::startTrans();
            }
            try {
                //入库

                //生成入库单
                $count2 = KcRk::whereTime('create_time', 'today')->count();
                $data["system_number"] = "RKD" . date('Ymd') . str_pad($count2 + 1, 3, 0, STR_PAD_LEFT);
                $data["beizhu"] = $data['beizhu'];

                model("KcRk")->allowField(true)->data($data)->save();

                $rkid = model("KcRk")->getLastInsID();

                //处理数据
                $detailsValidate = new KcRkMx();
                $num = 1;
                foreach ($data['details'] as $c => $v) {
                    $dat['details'][$c]['id'] = $v['id'];
                    $dat['details'][$c]['counts'] = $v['old_counts'] - $v["counts"];//剩下的总件数
                    $dat['details'][$c]['jianshu'] = intval(floor($dat['details'][$c]['counts'] / $v["zhijian"]));
                    $dat['details'][$c]['lingzhi'] = $dat['details'][$c]['counts'] % $v["zhijian"];
                    $dat['details'][$c]['zhongliang'] = $v['old_zhongliang'] - $v["zhongliang"];//剩下的总件数
                    $data['details'][$c]['companyid'] = $companyId;
                    $data['details'][$c]['kc_rk_id'] = $rkid;
//                        $data['details'][$c]['data_id'] = $id;
                    $data['details'][$c]['cache_data_number'] = $v['cache_data_number'];
                    $data['details'][$c]['cache_customer_id'] = $v['cache_customer_id'] ?? '';
                    $data['details'][$c]['cache_ywtime'] = $v['cache_ywtime'] ?? '';
                    $data['details'][$c]['cache_piaoju_id'] = $v['cache_piaoju_id'] ?? '';
                    $data['details'][$c]['cache_create_operator'] = $v['cache_create_operator'];
                    $data['details'][$c]['ruku_lingzhi'] = $v['lingzhi'];
                    $data['details'][$c]['ruku_jianshu'] = $v['jianshu'];
                    $data['details'][$c]['ruku_shuliang'] = $v['counts'];
                    $data['details'][$c]['ruku_zhongliang'] = $v['zhongliang'];
                    unset($data['details'][$c]["id"]);
//                        if (!$detailsValidate->check($data['details'][$c])) {
//                            throw new Exception('请检查第' . $num . '行' . $detailsValidate->getError());
//                        }
                    $num++;
                }

                //修改通知记录数量
                model("KcRkTz")->saveAll($dat['details']);

                //入库明细

                model('KcRkMx')->allowField(true)->saveAll($data['details']);
                $count1 = KcSpot::whereTime('create_time', 'today')->count();
                //入库库存
                $spot = [];
                foreach ($data['details'] as $c => $v) {
                    $spot[] = [
                        'companyid' => $companyId,
                        'ruku_type' => 4,
                        'ruku_fangshi' => 2,
                        'piaoju_id' => $v['cache_piaoju_id'],
                        'resource_number' => "KC" . date('Ymd') . str_pad($count1 + 1, 3, 0, STR_PAD_LEFT),
                        'guige_id' => $v['guige_id'],
                        'data_id' => $v['id'] ?? '',
                        'pinming_id' => $v['pinming_id'],
                        'store_id' => $v['store_id'],
                        'caizhi_id' => $v['caizhi_id'] ?? '',
                        'chandi_id' => $v['chandi_id'] ?? '',
                        'jijiafangshi_id' => $v['jijiafangshi_id'],
                        'houdu' => $v['houdu'] ?? '',
                        'kuandu' => $v['kuandu'] ?? '',
                        'changdu' => $v['changdu'] ?? '',
                        'lingzhi' => $v['lingzhi'] ?? '',
                        'jianshu' => $v['jianshu'] ?? '',
                        'zhijian' => $v['zhijian'] ?? '',
                        'counts' => $v['counts'] ?? '',
                        'zhongliang' => $v['zhongliang'] ?? '',
                        'price' => $v['price'] ?? '',
                        'cb_price' => $v['price'] ?? '',
                        'cb_sumprice' => $v['sumprice'] ?? '',
                        'cb_shuie' => $v['shuie'] ?? '',
                        'cb_shui_price' => $v['shui_price'] ?? '',
                        'shuie' => $v['shuie'] ?? '',
                        'shui_price' => $v['shui_price'] ?? '',
                        'sum_shui_price' => $v['sum_shui_price'] ?? '',
                        'beizhu' => $v['beizhu'] ?? '',
                        'chehao' => $v['chehao'] ?? '',
                        'pihao' => $v['pihao'] ?? '',
                        'sumprice' => $v['sumprice'] ?? '',
                        'huohao' => $v['huohao'] ?? '',
                        'customer_id' => $v['cache_customer_id'],
                        'mizhong' => $v['mizhong'] ?? '',
                        'jianzhong' => $v['jianzhong'] ?? '',
                        'lisuan_zhongliang' => ($v["counts"] * $v["changdu"] * $v['mizhong'] / 1000),
                        'guobang_zhizhong' => ($v['zhongliang'] / $v["counts"] * $v["zhijian"]) ?? '',
                        'guobang_zhongliang' => $v["zhongliang"] ?? '',
                        'lisuan_zhizhong' => ($v["counts"] * $v["changdu"] * $v['mizhong'] / 1000 / $v["counts"] * $v["zhijian"]),
                        'guobang_jianzhong' => ($v['zhongliang'] / $v["counts"]) ?? '',
                        'lisuan_jianzhong' => ($v["counts"] * $v["changdu"] * $v['mizhong'] / 1000 / $v["counts"]),
                        'old_lisuan_zhongliang' => ($v["counts"] * $v["changdu"] * $v['mizhong'] / 1000),
                        'old_guobang_zhizhong' => ($v['zhongliang'] / $v["counts"] * $v["zhijian"]) ?? '',
                        'old_lisuan_zhizhong' => ($v["counts"] * $v["changdu"] * $v['mizhong'] / 1000 / $v["counts"] * $v["zhijian"]),
                        'old_guobangjianzhong' => ($v['zhongliang'] / $v["counts"]) ?? '',
                        'old_guobangzhongliang' => ($v['zhongliang']) ?? '',
                        'old_lisuan_jianzhong' => ($v["counts"] * $v["changdu"] * $v['mizhong'] / 1000 / $v["counts"]),
                        'status' => 0,
                        'guobang_price' => $v['guobang_price'] ?? '',
                        'guobang_shui_price' => $v['guobang_shui_price'] ?? '',
                        'zhi_price' => $v['zhi_price'] ?? '',
                        'zhi_shui_price' => $v['zhi_shui_price'] ?? '',
                        'lisuan_shui_price' => $v['lisuan_shui_price'] ?? '',
                        'lisuan_price' => $v['lisuan_price'] ?? '',
                    ];
                }

                model("KcSpot")->allowField(true)->saveAll($spot);
//                }

                if (!$return) {
                    Db::commit();
                    return returnRes(true, '', ['id' => $rkid]);
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

    /**清库列表
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function clearstoragelist()
    {
        $params = request()->param();
        $list = \app\admin\model\KcRkTz::where(array("companyid" => Session::get("uinfo", "admin")['companyid'], "zhongliang" => 0));
        $list = $this->getsearchcondition($params, $list);
        $list = $list->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**
     * 清库
     */
    public function clearstorage()
    {
        $id = request()->param("ids");
        $res = model("KcSpot")->where("id", "in", $id)->update(array("status" => 2));
        return returnRes($res, '清库失败');
    }

    public function qtrklist()
    {
        $params = request()->param();
        $list = $list = \app\admin\model\KcQtrk::with(['customData',])->where('companyid', Session::get('uinfo.companyid', 'admin'));
        if (!empty($params['system_number'])) {
            $list->where("system_number",$params['system_number']);
        }
        if (!empty($params['customer_id'])) {
            $list->where("customer_id",$params['customer_id']);
        }
        if (!empty($params['beizhu'])) {
            $list->where("beizhu",$params['beizhu']);
        }
        $list = $list->paginate(10);
        return returnRes(true, '', $list);
    }
    public function qtrkmx($id=0){
        $data = \app\admin\model\KcQtrk::with([
            'customData',
            'details' => ['specification', 'jsfs', 'storage','chandiData','customData','caizhiData','pinmingData'],
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
    public function addqtrk($data = [], $return = false){
        if (request()->isPost()) {
            $companyId = $this->getCompanyId();
            $count = \app\admin\model\KcDiaobo::whereTime('create_time', 'today')->count();
            $data = request()->post();
            $data["status"] = 0;
            $data['create_operator_name'] = $this->getAccount()['name'];
            $data['create_operator_id'] = $this->getAccountId();
            $data['companyid'] = $companyId;
            $data['system_number'] = 'KCPD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            if (!$return) {
                Db::startTrans();
            }
            try {
                model("KcDiaobo")->allowField(true)->data($data)->save();
                $id = model("KcPandian")->getLastInsID();
                foreach ($data["detail"] as $c => $v) {
                    $dat['details'][$c]['id']=$v["id"];
                    $dat['details'][$c]['counts']=$v["old_counts"]-$v["counts"];
                    $dat['details'][$c]['jianshu']= intval( floor($dat['details'][$c]['counts']/$v["zhijian"]));
                    $dat['details'][$c]['lingzhi']= $dat['details'][$c]['counts']%$v["zhijian"];
                    $data['details'][$c]['companyid'] = $companyId;
                    $data['details'][$c]['pandian_id'] = $id;
                    unset($v["id"]);
                }
                //修改库存数量
                model("KcSpot")->saveAll($dat['details']);
                //添加到
                model('KcPandianMx')->saveAll($data['details']);
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
}