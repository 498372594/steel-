<?php


namespace app\admin\controller;


use app\admin\model\ViewMoneySource;
use think\{db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    Request,
    response\Json};

class CapitalHk extends Right
{
    const CAPITAL_OTHER = 1;//其他
    const PURCHASE = 11;//采购单
    const SALES_ORDER = 12;//销售单
    const PURCHASE_RETURN = 13;//采购退货单
    const SALES_ORDER_RETURN = 14;//销售退货单
    const FAXI = 16;//应收罚息
    const CAPITAL_COST = 2;//费用
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

    /**
     * 获取收款单、付款单源单
     * @param Request $request
     * @param int $pageLimit
     * @param int $fangxiang
     * @return Json
     * @throws DbException
     */
    public function getList(Request $request, $pageLimit = 10, $fangxiang = 1)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $params = $request->param();
        $list = ViewMoneySource::where('companyid', $this->getCompanyId())
            ->order('yw_time', 'desc')
            ->where('fangxiang', $fangxiang)
            ->where('status', '<>', 2);
        if ($fangxiang == 1) {
            switch ($params['sklx']) {
                case 1:
                case 2:
                    $list->where('type_id', self::SALES_ORDER);
                    break;
                case 3:
                    $list->where('type_id', self::SALES_ORDER_RETURN);
                    break;
                case 4:
                    $list->where('type_id', self::RECEIPT);
                    break;
                case 5:
                    $list->where('type_id', self::CAPITAL_OTHER);
                    break;
                case 6:
                    $list->where('type_id', self::INIT_RECEIVABLE);
                    break;
                case 7:
                    $list->where('type_id', self::CAPITAL_COST);
                    break;
            }
        } elseif ($fangxiang == 2) {
            switch ($params['fklx']) {
                case 1:
                case 2:
                    $list->where('type_id', self::PURCHASE);
                    break;
                case 3:
                    $list->where('type_id', self::PURCHASE_RETURN);
                    break;
                case 4:
                    $list->where('type_id', self::PAYMENT);
                    break;
                case 5:
                    $list->where('type_id', self::CAPITAL_OTHER);
                    break;
                case 6:
                    $list->where('type_id', self::INIT_RECEIVABLE);
                    break;
                case 7:
                    $list->where('type_id', self::CAPITAL_COST);
                    break;
            }
        } else {
            return returnFail('收付方向错误');
        }
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('yw_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['type'])) {
            $list->where('type_id', $params['type']);
        }
        if (!empty($params['customer_id'])) {
            $list->where('customer_id', $params['customer_id']);
        }
        if (!empty($params['whx_money'])) {
            $list->where('weihexiao_jine', $params['whx_money']);
        }
        if (!empty($params['whx_zhongliang'])) {
            $list->where('weihexiao_zhongliang', $params['whx_zhongliang']);
        }
        $data = $list->paginate($pageLimit);
        return returnSuc($data);
    }
}