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
    public function  xhbj(Request $request, $pageLimit = 10){
        $params = request()->param();
        $list = \app\admin\model\ViewInstorageDetails::where('companyid', $this->getCompanyId());
//        if (!empty($params['ywsjStart'])) {
//            $list->where('service_time', '>=', $params['ywsjStart']);
//        }
//        if (!empty($params['ywsjEnd'])) {
//            $list->where('service_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
//        }

        $list=$this->getsearch($params,$list);
        $list=$list->paginate(10);
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }
    /**现货汇总查询
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function xhhz(Request $request, $pageLimit = 10){
        $params = request()->param();
        $list = \app\admin\model\ViewInstorageDetails::where('companyid', $this->getCompanyId());
        $list=$this->getsearch($params,$list);
        $list=$list->field("storage,classname,productname,specification,texture,originarea,houdu_name,width,length,jianshu,sum(jianshu) as total_jianshu,sum(lingzhi) as total_lingzhi,sum(shuliang) as total_shuliang,sum(heavy) as total_heavy,sum(lisuanzongzhong) as total_lisuanzongzhong")
            ->group("storage_id,classid,productname,specification,width,length,houdu_name")
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
    public function getenroute(){
        $params = request()->param();
        $list = \app\admin\model\Purchasedetails::where('companyid', $this->getCompanyId());
        $list->where('is_finished',1);
        $list=$this->getsearch($params,$list);
        $list=$list
            ->paginate(10);
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