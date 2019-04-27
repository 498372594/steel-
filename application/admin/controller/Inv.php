<?php


namespace app\admin\controller;


use app\admin\model\ViewInv;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Request;
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

    /**
     * @param $data
     * @throws Exception
     */
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

    /**
     * @return Json
     * @throws DbException
     */
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

    /**
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function invywtype()
    {
        $list = model("InvYwtype")->select();
        return returnRes(true, '', $list);
    }

    /**
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function summaryYk(Request $request, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $model = new \app\admin\model\Inv();
        $data = $model->getYkfpHuizong($request->param(), $pageLimit);
        return returnSuc($data);
    }

    public function detailsYk(Request $request, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $params = $request->param();
        if (empty($params['customer_id'])) {
            return returnFail('请选择客户');
        }

        $model = new \app\admin\model\Inv();
        $data = $model->getYkfpMx($params, $pageLimit);
        $data = $data->toArray();
        $tmp = $data['data'];
        foreach ($tmp as $i => &$item) {
            if ($i == 0) {
                $yue = empty($item['yue']) ? 0 : floatval($item['yue']);
                $yue = number_format($yue, 2, '.', '');
                $item['yue'] = $yue;
            } else {
                $bckpje = empty($item['kaipiao_jine']) ? 0 : floatval($item['kaipiao_jine']);
                $bcjshj = empty($item['jiashui_heji']) ? 0 : floatval($item['jiashui_heji']);
                $syue = empty($tmp[$i - 1]['yue']) ? 0 : floatval($tmp[$i - 1]['yue']);
                $yue = $syue + $bcjshj - $bckpje;
                $yue = number_format($yue, 2, '.', '');
                $item['yue'] = $yue;
            }
            $item['danjia'] = empty($item['danjia']) ? '' : number_format($item['danjia'], 2, '.', '');
            $item['zhong_liang'] = empty($item['zhong_liang']) ? '' : number_format($item['zhong_liang'], 2, '.', '');
            $item['jiashui_heji'] = empty($item['jiashui_heji']) ? '' : number_format($item['jiashui_heji'], 2, '.', '');
            $item['kaipiao_jine'] = empty($item['kaipiao_jine']) ? '' : number_format($item['kaipiao_jine'], 2, '.', '');
        }
        $data['data'] = $tmp;
        return returnSuc($data);
    }
}