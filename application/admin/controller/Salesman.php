<?php


namespace app\admin\controller;


use app\admin\model\SalesmanHkxsRule;
use app\admin\model\Salesmansetting;
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
    /**业务提成规则1添加
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
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
    /**业务提成规则2添加
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
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
    /**业务提成规则1列表
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function getSalesmansetting($pageLimit = 10)
    {
        $list = Salesmansetting::where("companyid", $this->getCompanyId())->paginate($pageLimit);

        return returnRes(true, '', $list);
    }

    /**业务提成规则2列表
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function getSalesmanHkxsRule($pageLimit = 10)
    {
        $list = SalesmanHkxsRule::where("companyid", $this->getCompanyId())->paginate($pageLimit);
        return returnRes(true, '', $list);
    }

    public function salesmanstat()
    {
        $params = request()->param();

        $tc_type = model("company")->where("id", $this->getCompanyId())->value("tc_type");
        if ($tc_type = 1) {
            $setList = Salesmansetting::where("companyid", $this->getCompanyId())->select();
            if (!empty($params['sales_operator_id'])) {
                $sales_operator_id = $params['sales_operator_id'];
            }
            $ywsjStart = '';
            if (!empty($params['ywsjStart'])) {
                $ywsjStart = $params['ywsjStart'];
            }
            $ywsjEnd = '';
            if (!empty($params['ywsjEnd'])) {
                $ywsjEnd = $params['ywsjEnd'];
            }
            $sqlParams = [];
            $sql = "(SELECT
       oper.`name` salesOperatorName,
       SUM(IFNULL(md.`zhongliang`, 0)) benqiSalesZhongliang,
       SUM(
         CASE
           WHEN jjfs.id = 3
                   THEN IFNULL(md.`counts`, 0) * IFNULL(mx.price, 0)
           ELSE IFNULL(md.`zhongliang`, 0) * IFNULL(mx.price, 0)
             END
           ) benqiSalesSumPrice,
       SUM(mx.sum_shui_price),
       (SELECT SUM(
                 IFNULL(sk.`money`, 0) + IFNULL(sk.`msmoney`, 0)
                   ) FROM capital_sk sk WHERE sk.`sale_operator_id` = oper.`id` and sk.delete_time is null and sk.status != 1) benqihuikuanSumPrice,
       '' huikuanXishu,
       '' benqitichengPrice,
       '' benqikoukuanPrice,
       '' benqitichengSumPrice
FROM
     stock_out_md md
       LEFT JOIN stock_out_detail mx
         ON mx.id = md.`stock_out_detail_id`
       LEFT JOIN stock_out ck
         ON ck.`id` = mx.`stock_out_id`
       LEFT JOIN jsfs jjfs
         ON jjfs.`id` = mx.`jijiafangshi_id`
       LEFT JOIN admin oper
         ON oper.`id` = ck.`sale_operator_id`
WHERE 1 = 1 AND ck.delete_time is null and ck.status!=2 and mx.companyid=" . $this->getCompanyId();
            if (!empty($param['create_operator_id'])) {
                $sql .= ' and ck.create_operator_id =?';
                $sqlParams[] = $param['create_operator_id'];
            }
            if (!empty($param['ywsjStart'])) {
                $sql .= ' and ck.yw_time >=?';
                $sqlParams[] = $ywsjStart;
            }
            if (!empty($param['ywsjEnd'])) {
                $sql .= ' and ck.yw_time < ?';
                $sqlParams[] = $ywsjEnd;
            }
            $sql .= "GROUP BY oper.`id` ORDER BY ck.yw_time)";
            $list = Db::table($sql)->alias('t')->bind($sqlParams)->order('yw_time')->select();
            if (!empty($list)) {
                foreach ($list as $settingEx) {
                    if (!empty($setList)) {
                        foreach ($setList as $setting) {
                            if ($setting["weight_start"] <= $settingEx["benqiSalesZhongliang"] && $settingEx["benqiSalesZhongliang"] < $setting["weight_end"]) {
                                $settingEx["benqitichengSumPrice"] = $setting["ticheng_price"] * $settingEx["benqiSalesZhongliang"];
                            }
                        }
                    }
                }
            }

        } else {


        }
    }
//    public function ceshi(){
//        $list=model("capital_hk")->alias("b")->join("salesorder a","a.id=b.data_id","left")
//            ->where(" b.hk_type=12 and b.money=b.hxmoney and b.money !=0")->field("b.id,b.yw_time")->select();
//        foreach ($list as $item){
//            $list=model("salesorder_details")->where("order_id",$item["id"])->field("")->select();
//        }
//    }
    /**规则默认查询
     * @return Json
     * @throws DbException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getmoren(){
        $data=model("company")->where("id",$this->getCompanyId())->field("id,tc_type")->find();
        return returnRes($data, '无相关数据', $data);
    }
}