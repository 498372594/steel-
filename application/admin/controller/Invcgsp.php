<?php


namespace app\admin\controller;



use think\{db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    Request,
    response\Json};
class Invcgsp extends Right
{
    public function add($data)
    {

    }
    //单据类型为2采购单
    public function getinv(){
        $params = request()->param();
        $list =  model("view_inv")->where(array("companyid"=> $this->getCompanyId()))->where("shui_price",">",0);
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
        $list = $list->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }

    /**获取单据列表
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function invywtype(){
        $list=model("inv_ywtype")->select();
        return returnRes($list, '没有数据，请添加后重试', $list);
    }
    public function invcgsp(){
        $params = request()->param();
        $list = \app\admin\model\InvCgsp::with(["customData","pjlxData"])->where('companyid', $this->getCompanyId());
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
    public function invcgspmx($id=0){
        $data = $list = \app\admin\model\InvCgsp::with([ 'details'=>['guigeData','pinmingData'],'pjlxData','customData'
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
    public function cgspadd($data = [], $return = false)
    {
        if (request()->isPost()) {
            $companyId = $this->getCompanyId();
            $data = request()->post();
            $count = \app\admin\model\InvCgsp::whereTime('create_time', 'today')->where("type",$data["type"])->count();
            $data["status"] = 0;
            $data['create_operator_id'] = $this->getAccountId();
            $data['companyid'] = $companyId;
            $data['system_number'] = 'CGSP' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);

            if (!$return) {
                Db::startTrans();
            }
            try {
                model("InvCgsp")->allowField(true)->data($data)->save();
                $id = model("InvCgsp")->getLastInsID();
                foreach ($data["details"] as $c => $v) {
                    $dat['details'][$c]['id'] = $v["inv_id"];
                    $dat['details'][$c]['yhx_zhongliang'] = $v["yhx_zhongliang"]+$v["zhongliang"];
                    $dat['details'][$c]['yhx_price'] = $v["yhx_zhongliang"]+$v["sum_shui_price"];
                    $data['details'][$c]['companyid'] = $companyId;
                    $data['details'][$c]['cgsp_id'] = $id;
                    $data['details'][$c]['yw_type'] = 2;
                    $data['details'][$c]['data_id'] = $v["inv_id"];
                    $data['details'][$c]['system_number']=$v["system_number"]."1";
                }
                model('Inv')->allowField(true)->saveAll($dat['details']);
                model('InvCgspHx')->allowField(true)->saveAll($data['details']);
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