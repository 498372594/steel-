<?php


namespace app\admin\controller;


use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

class CapitalHk extends Right
{
    const PURCHASE = 11;//采购单
    const SALES_ORDER = 12;//销售单
    const PURCHASE_RETURN = 13;//采购退货单
    const SALES_ORDER_RETURN = 14;//销售退货单
    const FAXI = 16;//应收罚息
    const RECEIPT = 22;//收款单
    const PAYMENT = 23;//付款单
    const INIT_RECEIVABLE = 26;//应收账款余额期初
    const INIT_PAYABLE = 27;//应付账款余额期初

    /**
     * 添加单据
     * @param $data
     */
    public function add($data)
    {
        /*$data = [
            'hk_type' => '',
            'data_id' => '',
            'fangxiang' => '',
            'customer_id' => '',
            'jiesuan_id' => '',
            'system_number' => '',
            'yw_time' => '',
            'beizhu' => '',
            'money' => '',
            'group_id' => '',
            'sale_operator_id' => '',
            'create_operator_id' => '',
            'zhongliang' => '',
            'cache_pjlx_id' => '',
        ];*/
        (new \app\admin\model\CapitalHk())->allowField(true)->save($data);
    }

    /**
     * 单据作废
     * @param $id
     * @param $type
     * @return bool|string
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function cancel($id, $type)
    {
        $capitalHK = \app\admin\model\CapitalHk::where('data_id', $id)
            ->where('type', $type)
            ->find();
        if (empty($capitalHK)) {
            return true;
        }
        if ($capitalHK->hxmoney != 0 || $capitalHK->hxzhongliang != 0) {
            return '此单据已有结算信息！';
        }
        return true;
    }
}