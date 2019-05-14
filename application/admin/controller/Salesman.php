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
    public function salesmansetting()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['companyid'] = $this->getCompanyId();
            $data['add_name'] = $this->getAccount()['name'];
            $data['create_operator_id'] = $this->getAccountId();
            if (empty(request()->post("id"))) {
                $result = model("salesmansetting")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $id = request()->post("id");
                $result = model("salesmansetting")->allowField(true)->save($data, ['id' => $id]);
                return returnRes($result, '修改失败');
            }

        } else {
            $id = request()->param("id");
            if ($id) {
                $data['info'] = model("salesmansetting")->where("id", $id)->find();
            } else {
                $data = null;
            }
            return returnRes($data, '无相关数据', $data);
        }
    }
    public function salesmanHkxsRule()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['companyid'] = $this->getCompanyId();
            $data['add_name'] = $this->getAccount()['name'];
            $data['create_operator_id'] = $this->getAccountId();
            if (empty(request()->post("id"))) {
                $result = model("salesman_hkxs_rule")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $id = request()->post("id");
                $result = model("salesman_hkxs_rule")->allowField(true)->save($data, ['id' => $id]);
                return returnRes($result, '修改失败');
            }

        } else {
            $id = request()->param("id");
            if ($id) {
                $data['info'] = model("salesman_hkxs_rule")->where("id", $id)->find();
            } else {
                $data = null;
            }
            return returnRes($data, '无相关数据', $data);
        }
    }
}