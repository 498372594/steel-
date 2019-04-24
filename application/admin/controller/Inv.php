<?php


namespace app\admin\controller;


use app\admin\model\ViewInv;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\response\Json;

class Inv extends Right
{
    /**
     * 获取源单，fx，1-销项，2-进项
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function getinv()
    {
        $params = request()->param();
        $list = ViewInv::where('companyid', $this->getCompanyId())->where("shui_price", ">", 0);
        //业务时间
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('yw_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        //单据类型
        if (!empty($params['yw_type'])) {
            $list->where('yw_type', $params['yw_type']);
        }
        //往来单位
        if (!empty($params['customer_id'])) {
            $list->where('customer_id', $params['customer_id']);
        }
        //品名
        if (!empty($params['pin_ming'])) {
            $list->where('pin_ming', 'like', '%' . $params['pin_ming'] . '%');
        }
        //规格
        if (!empty($params['guige'])) {
            $list->where('guige', 'like', '%' . $params['guige'] . '%');
        }
        //未核销金额
        if (!empty($params['weihx_jine'])) {
            $list->where('weihx_jine', $params['weihx_jine']);
        }
        //未核销重量
        if (!empty($params['weihx_zhongliang'])) {
            $list->where('weihx_zhongliang', $params['weihx_zhongliang']);
        }
        if (!empty($params['fx'])) {
            $list->where('fx_type', $params['fx']);
        }
        $list = $list->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }

    public function add($data)
    {
        /*$data = {[ 'companyid' => $companyId,
                        'fx_type'=>2,
                        'yw_type'=>6,
                        'yw_time'=>$v["yw_time"]?? '',
                        'system_number'=>$v["system_number"]."1"?? '',
                        'pinming_id'=>$v["pinming_id"]?? '',
                        'guige_id'=>$v["guige_id"]?? '',
                        'houdu'=>$v["houdu"]?? '',
                        'changdu'=>$v["changdu"]?? '',
                        'kuandu'=>$v["kuandu"]?? '',
                        'zhongliang'=>$v["zhongliang"]?? '',
                        'price'=>$v["price"]?? '',
                        'price'=>$v["price"]?? '',
                        'customer_id'=>$data["customer_id"]?? '',
                        'jijiafangshi_id'=>$v["jijiafangshi_id"]?? '',
                        'piaoju_id'=>$v["piaoju_id"]?? '',
                        'yhx_zhongliang'=>0,
                        'yhx_price'=>0,
                        'data_id'=>$id,
                        'shui_price'=>$v["shui_price"]?? '',
                        'sum_price'=>$v["sum_price"]?? '',};*/
        model("inv")->allowField(true)->saveAll($data);
    }

    public function getjxfpmxhx()
    {
        $params = request()->param();
        $list = model("ViewJxfpmxhx")->where('companyid', $this->getCompanyId());
        //业务时间
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('yw_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        //往来单位
        if (!empty($params['customer_id'])) {
            $list->where('gys_id', $params['customer_id']);
        }
        //票据类型
        if (!empty($params['piaoju_id'])) {
            $list->where('piaoju_id', $params['piaoju_id']);
        }
        if (!empty($params['pinming'])) {
            $list->where('pinming', $params['pinming']);
        }
        if (!empty($params['guige'])) {
            $list->where('guige', $params['guige']);
        }
        if (!empty($params['fapiao_haoma'])) {
            $list->where('fapiao_haoma', $params['fapiao_haoma']);
        }
        if (!empty($params['taitou'])) {
            $list->where('taitou', $params['taitou']);
        }
        //系统单号
        if (!empty($params['caigou_danhao'])) {
            $list->where('caigou_danhao', 'like', '%' . $params['caigou_danhao'] . '%');
        }
        //备注
        if (!empty($params['beizhu'])) {
            $list->where('beizhu', 'like', '%' . $params['beizhu'] . '%');
        }
        $list = $list->paginate(10);
        return returnRes(true, '', $list);
    }
    public function invywtype(){
        $list=model("InvYwtype")->select();
        return returnRes(true, '', $list);
    }
}