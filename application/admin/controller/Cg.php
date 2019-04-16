<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;

use think\Session;
use app\admin\validate\{FeiyongDetails, SalesorderDetails};
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    Request,
    response\Json};

class Cg extends Right
{
    public function cgth(){
        $params = request()->param();
        $list = \app\admin\model\CgTh::with(["jsfsData","customData","pjlxData"])->where('companyid', $this->getCompanyId());
        //往来单位
        if (!empty($params['customer_id'])) {
            $list->where('customer_id', $params['customer_id']);
        }
        //票据类型
        if (!empty($params['piaoju_id'])) {
            $list->where('piaoju_id', $params['piaoju_id']);
        }
        //系统单号
        if (!empty($params['system_number'])) {
            $list->where('system_number', 'like', '%' . $params['system_number'] . '%');
        }
        //备注
        if (!empty($params['beizhu'])) {
            $list->where('beizhu', 'like', '%' . $params['beizhu'] . '%');
        }
        $list = $list->paginate(10);
        return returnRes(true, '', $list);
    }
    public function cgthmx($id=0){
        $data = $list = \app\admin\model\CgTh::with([ 'details'=>['specification','jsfs','storage','pinmingData','caizhiData','chandiData'],"jsfsData","customData","pjlxData",
        ])
            ->where('companyid',$this->getCompanyId())
            ->where('id', $id)
            ->find();
        if (empty($data)) {
            return returnFail('数据不存在');
        } else {
            return returnRes(true, '', $data);
        }
    }
    public function addcgth(Request $request,$data = [], $return = false)
    {
        if (request()->isPost()) {
            $companyId = $this->getCompanyId();
            $data = request()->post();
            $count = \app\admin\model\InitYskp::whereTime('create_time', 'today')->count();
            $data["status"] = 0;
            $data['create_operator_id'] = $this->getAccountId();
            $data['companyid'] = $companyId;
            $data['system_number'] = 'CGTHD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
            if (!$return) {
                Db::startTrans();
            }
            try {
                model("CgTh")->allowField(true)->data($data)->save();
                $id = model("CgTh")->getLastInsID();
                $totalMoney = 0;
                $totalWeight = 0;
                foreach ($data["details"] as $c => $v) {
                    $info=db("kc_spot")->where("id",$v["spot_id"])->field("counts,zhongliang")->find();
//                    dump($info);die;
                    if($v["counts"]>$info["counts"]){
                        return returnFail('退货数量不得大于'.$info["counts"]);
                    }
                    if($v["zhongliang"]>$info["zhongliang"]){
                        return returnFail('退货重量不得大于'.$info["zhongliang"]);
                    }
                    $totalMoney += $v['sum_shui_price'];
                    $totalWeight += $v['zhongliang'];
                    $dat['details'][$c]['counts']=$info["counts"]-$v["counts"];
                    $dat['details'][$c]['zhongliang']=$info["zhongliang"]-$v["zhongliang"];
                    $dat['details'][$c]['jianshu']= intval( floor($dat['details'][$c]['counts']/$v["zhijian"]));
                    $dat['details'][$c]['lingzhi']= $dat['details'][$c]['counts']%$v["zhijian"];
                    $dat["details"][$c]["id"]=$v["spot_id"];
                    $data['details'][$c]['companyid'] = $companyId;
                    $data['details'][$c]['cg_th_id'] = $id;
                }
//                model('KcSpot')->allowField(true)->saveAll($dat['details']);
                model('CgThMx')->allowField(true)->saveAll($data['details']);
                $count = \app\admin\model\Salesorder::whereTime('create_time', 'today')
                    ->where('companyid', $companyId)
                    ->count();

                $systemNumber = 'XSD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
                //自动出库，生成出库单
                $stockOutData = [
                    'remark' => '退货单，' . $systemNumber,
                    'yw_time' => $data['yw_time'],
                    'department' => $data['group_id'],
                    'sale_operator_id' => $data['sale_operator_id'],
                    'details' => [],
                    'data_id' => $id
                ];
                $stockOutDetail = [];
                $index = -1;
                $spotIds = [];
                foreach ($data['details'] as $v) {
                    $v['index'] = $v['index'] ?? $index--;
                    $spotId = $v['spot_id'] ?? $spotIds[$v['index']];
                    $stockOutData['details'][] = [
                        'zhongliang' => $v['zhongliang'] ?? '',
                        'kucun_cktz_id' => $v['index'],
                        'kc_spot_id' => $spotId,
                        'ylsh' => $v['ylsh_id'] ?? 0
                    ];
                    $stockOutDetail[$v['index']] = [
                        'companyid' => $companyId,
                        'chuku_type' => 10,
                        'data_id' => $id,
                        'guige_id' => $v['guige_id'],
                        'caizhi' => $v['caizhi'] ?? '',
                        'chandi' => $v['chandi'] ?? '',
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
                        'sumprice' => $v['sumprice'] ?? '',
                        'shuie' => $v['shuie'] ?? '',
                        'shui_price' => $v['shui_price'] ?? '',
                        'sum_shui_price' => $v['sum_shui_price'] ?? '',
                        'remark' => $v['remark'] ?? '',
                        'car_no' => $v['car_no'] ?? '',
                        'pihao' => $v['pihao'] ?? '',
                        'cache_ywtime' => $data['yw_time'],
                        'cache_data_pnumber' => $data['system_number'],
                        'cache_customer_id' => $data['customer_id'],
                        'store_id' => $v['store_id'],
                        'cache_create_operator' => $data['cache_create_operator'],
                    ];
                }
                $res = (new Chuku())->add($request, $stockOutData, $stockOutDetail, 1, true);
                //其他费用
                $num = 1;
                if (!empty($data['other'])) {
                    $otherValidate = new FeiyongDetails();
                    //处理其他费用
                    foreach ($data['other'] as $c => $v) {
                        $data['other'][$c]['group_id'] = $data['department'] ?? '';
                        $data['other'][$c]['sale_operator_id'] = $data['employer'] ?? '';

                        if (!$otherValidate->check($data['other'][$c])) {
                            throw new Exception('请检查第' . $num . '行' . $otherValidate->getError());
                        }
                        $num++;
                    }
                    $res = (new Feiyong())->addAll($data['other'], 1, $id, $data['yw_time'], false);
                    if ($res !== true) {
                        throw new Exception($res);
                    }
                }
                //向货款单添加数据
                $capitalHkData = [
                    'hk_type' => CapitalHk::PURCHASE_RETURN,
                    'data_id' => $id,
                    'fangxiang' => 2,
                    'customer_id' => $data['customer_id'],
                    'jiesuan_id' => $data['jiesuan_id'],
                    'system_number' => $data['system_number'],
                    'yw_time' => $data['yw_time'],
                    'beizhu' => $data['beizhu'],
                    'money' => (-$totalMoney),
                    'group_id' => $data['group_id'],
                    'sale_operator_id' => $data['sale_operator_id'],
                    'create_operator_id' => $data['create_operator_id'],
                    'zhongliang' => (-$totalWeight),
                    'cache_pjlx_id' => $data['piaoju_id'],
                ];
                (new CapitalHk())->add($capitalHkData);

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