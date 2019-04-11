<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use think\Db;
use think\Exception;
use think\Session;

class Resource extends Right
{
    /**现货报价查询列表
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function  xhbj($pageLimit = 10){
        $params = request()->param();
        $list = \app\admin\model\ViewInstorageDetails::where('companyid', $this->getCompanyId());
        $list=$this->getsearchcondition($params,$list);
        $list=$list->paginate($pageLimit);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }
    /**现货汇总查询
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function xhhz($pageLimit = 10){
        $params = request()->param();
        $list = \app\admin\model\ViewInstorageDetails::where('companyid', $this->getCompanyId());
        $list=$this->getsearchcondition($params,$list);
        $juhe="store_id,pinming_id,guige_id,kuandu,changdu,houdu,classname";
        $juhe=$juhe.$params("juhe");
        $list=$list->field("storage,classname,pinming,guige,caizhi,chandi,houdu,kuandu,changdu,jianshu,sum(jianshu) as total_jianshu,sum(lingzhi) as total_lingzhi,sum(counts) as total_shuliang,sum(zhongliang) as total_zhongliang,sum(lisuanzongzhong) as total_lisuanzongzhong")
            ->group("$juhe")
            ->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**出入库对照表
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function getinout(){
        $params = request()->param();
        $list = \app\admin\model\ViewInstorageOrder::where('companyid', $this->getCompanyId());
        $list=$this->getsearch($params,$list);

        $list=$list
            ->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }

    /**在途库存查询
     * @return \think\response\Json
     */

    public function getenroute()
    {
        $params = request()->param();

        $list = \app\admin\model\KcRkTz::with(['storage','pinmingData','caizhiData','chandiData'])->where('companyid', $this->getCompanyId());
        $list->where("jianshu",">",0)->where("lingzhi",">",0)->where("counts",">",0);

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
        if (!empty($params['cache_data_pnumber'])) {
            $list->where('cache_data_pnumber', 'like', '%' . $params['cache_data_pnumber'] . '%');
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

    /**预留库存列表
     * @return \think\response\Json
     */
    public function  reservedgoods(){
        $params = request()->param();
        $list = \app\admin\model\ViewReserved::where('companyid', $this->getCompanyId());
        $list=$this->getsearch($params,$list);
        $list=$list
            ->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }
}