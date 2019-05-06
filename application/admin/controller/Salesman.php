<?php


namespace app\admin\controller;


use think\{exception\DbException, Request, response\Json};

class Salesman extends Right
{
    /**
     * 业务员利润汇总
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function lirun(Request $request, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $params = $request->param();
        $model = new \app\admin\model\Admin();
        $data = $model->lirun($params, $pageLimit, $this->getCompanyId());
        return returnSuc($data);
    }
}