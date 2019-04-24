<?php


namespace app\admin\controller;


use app\admin\model\ViewZijinCount;
use think\exception\DbException;
use think\response\Json;

class Customer extends Right
{

    /**
     * 获取欠款
     * @param $customer_id
     * @return Json
     */
    public function getQk($customer_id)
    {
        $data = ViewZijinCount::where('customer_id', $customer_id)
            ->group('fangxiang')
            ->column('sum(money)', 'fangxiang');
        $yishou = \app\admin\model\CapitalSk::where('status', '<>', 2)
            ->where('customer_id', $customer_id)
            ->sum('money+msmoney');
        $yifu = \app\admin\model\CapitalFk::where('status', '<>', 2)
            ->where('customer_id', $customer_id)
            ->sum('money+mfmoney');
        return returnSuc(['fee' => ($data[1] ?? 0) - $yishou - ($data[2] ?? 0) + $yifu]);
    }

    /**
     * 获取应收发票余额
     * @param int $customer_id
     * @return Json
     * @throws DbException
     */
    public function getYsInv($customer_id = 0)
    {
        $subSql = \app\admin\model\InvCgsp::where('gys_id', $customer_id)
            ->where('status', '<>', 1)
            ->fieldRaw('ifnull(sum(money+msmoney),0)')
            ->buildSql();

        $value = \app\admin\model\Inv::where('customer_id', $customer_id)
            ->where('fx_type', 1)
            ->where('shui_price', '>', 0)
            ->value('ifnull(sum(sum_shui_price),0)-' . $subSql);
        return returnSuc(['fee' => $value]);
    }

    /**
     * 获取应开发票余额
     * @param int $customer_id
     * @return Json
     * @throws DbException
     */
    public function getYkInv($customer_id = 0)
    {
        $subSql = \app\admin\model\InvXskp::where('customer_id', $customer_id)
            ->where('status', '<>', 2)
            ->fieldRaw('ifnull(sum(money+mkmoney),0)')
            ->buildSql();

        $value = \app\admin\model\Inv::where('customer_id', $customer_id)
            ->where('fx_type', 2)
            ->where('shui_price', '>', 0)
            ->value('ifnull(sum(sum_shui_price),0)-' . $subSql);
        return returnSuc(['fee' => $value]);
    }
}