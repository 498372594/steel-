<?php


namespace app\admin\controller;


use app\admin\model\ViewMoneySource;
use think\exception\DbException;
use think\Request;
use think\response\Json;

class Capital extends Right
{
    /**应收（付）账款汇总表
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     */
    public function summaryYs(Request $request, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $model = new \app\admin\model\CapitalSk();
        $data = $model->getTongjiHuizongList($request->param(), $pageLimit);
        return returnSuc($data);
    }

    /**
     * 应收（付）账款明细表
     * @param Request $request
     * @param int $fangxiang
     * @param int $paginate
     * @return Json
     * @throws DbException
     */
    public function details(Request $request, $fangxiang = 1, $paginate = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $params = $request->param();
        if (empty($params['customer_id'])) {
            return returnFail('请选择供应商');
        }
        $model = ViewMoneySource::with('custom')
            ->where('companyid', $this->getCompanyId())
            ->field('id,yw_time,dan_hao,hj_jine,yihx_jine,customer_id,fangxiang,type_id,status,group_id,sale_operator_id,beizhu')
            ->where('fangxiang', 1)
            ->where('customer_id', $params['customer_id'])
            ->where('fangxiang', $fangxiang);
        if (!empty($params['ywsjStart'])) {
            $model->whereTime('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $model->whereTime('yw_time', '<', $params['ywsjEnd']);
        }
        if (!empty($params['status'])) {
            $model->where('status', $params['status']);
        }
        if (!empty($params['type'])) {
            $model->where('type_id', $params['type']);
        }
        if (!empty($params['system_number'])) {
            $model->where('dan_hao', $params['system_number']);
        }
        $data = $model->order('create_time', 'asc')->paginate($paginate);
        return returnSuc($data);
    }
}