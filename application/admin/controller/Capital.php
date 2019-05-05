<?php


namespace app\admin\controller;


use think\exception\DbException;
use think\Request;
use think\response\Json;

class Capital extends Right
{
    /**
     * 应收账款汇总表
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function summaryYs(Request $request, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $model = new \app\admin\model\CapitalSk();
        $data = $model->getTongjiHuizongList($request->param(), $pageLimit, $this->getCompanyId());
        return returnSuc($data);
    }

    /**
     * 应收账款明细表
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function detailsYs(Request $request, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $params = $request->param();
        if (empty($params['customer_id'])) {
            return returnFail('请选择供应商');
        }
        $model = new \app\admin\model\CapitalSk();
        $data = $model->getTongjiMxList($params['customer_id'], $params, $pageLimit, $this->getCompanyId());
        return returnSuc($data);
    }
}