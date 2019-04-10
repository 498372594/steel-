<?php

namespace app\admin\controller;

use app\admin\model\CapitalFy;
use Exception;
use think\{Db, Request, response\Json, Session};

class Feiyong extends Signin
{
    /**
     * 添加费用单
     * @param Request $request
     * @param array $data
     * @param int $count
     * @param bool $return
     * @param bool $useTrans
     * @return array|bool|string|Json
     * @throws \think\Exception
     */
    public function add(Request $request, $data = [], $count = 0, $return = false, $useTrans = true)
    {
        $companyid = $this->getCompanyId();
        if (empty($count)) {
            $count = CapitalFy::whereTime('create_time', 'today')->where('companyid', $companyid)->count() + 1;
        }

        if (empty($data)) {
            $data = $request->post();
        }
        $data['companyid'] = $companyid;
        $data['system_number'] = 'FYD' . date('Ymd') . str_pad($count, 3, 0, STR_PAD_LEFT);
        $data['create_operator_id'] = $this->getAccountId();
        $data['fymx_create_type'] = $return ? 1 : 2;
        $validate = new \app\admin\validate\CapitalFy();
        if (!$validate->check($data)) {
            if ($return) {
                return $validate->getError();
            } else {
                return returnFail($validate->getError());
            }
        }
        if ($useTrans) {
            Db::startTrans();
        }
        try {
            $model = new CapitalFy();
            $model->allowField(true)->data($data)->save();
            $id = $model->getLastInsID();
            $now = time();
            foreach ($data['details'] as $c => $v) {
                $data['details'][$c]['companyid'] = $companyid;
                $data['details'][$c]['cap_fy_id'] = $id;
                $data['details'][$c]['create_time'] = $now;
                $data['details'][$c]['update_time'] = $now;
            }
            Db::name('CapitalFyhx')->insertAll($data['details']);

            if ($useTrans) {
                Db::commit();
            }
            if ($return) {
                return true;
            }
            return returnSuc();
        } catch (Exception $e) {
            if ($useTrans) {
                Db::rollback();
            }
            if ($return) {
                return $e->getMessage();
            }
            return returnFail($e->getMessage());
        }
    }

    /**
     * 添加多条费用单
     * @param array $data 费用单数据
     * $data = [
     *     'customer_id' => '对方单位',
     *     'beizhu' => '备注',
     *     'group_id' => '部门',
     *     'sale_operator_id' => '职员',
     *     'fang_xiang' => '方向，1-应收，2-应付',
     *     'shouzhifenlei_id' => '收支分类',
     *     'shouzhimingcheng_id' => '收支名称',
     *     'danjia' => '单价',
     *     'money' => '金额',
     *     'zhongliang' => '重量',
     *     'piaoju_id' => '票据类型',
     *     'price_and_tax' => '价税合计',
     *     'tax_rate' => '税率',
     *     'tax' => '税额'
     * ];
     * @param int $type 单据类型，1-销售单,2-采购单
     * @param int $data_id 关联数据id
     * @param string $yw_time 业务时间
     * @param bool $useTrans 是否使用事务
     * @return bool|string
     * @throws \think\Exception
     */
    public function addAll($data = [], $type = 0, $data_id = 0, $yw_time = '', $useTrans = true)
    {
        $request = Request::instance();
        $companyid = $this->getCompanyId();
        $count = CapitalFy::whereTime('create_time', 'today')->where('companyid', $companyid)->count();
        if ($useTrans) {
            Db::startTrans();
        }
        try {
            foreach ($data as $item) {
                //处理核销数据
                $item['details'] = [[
                    'fyhx_type' => $type,
                    'data_id' => $data_id,
                    'cache_yw_time' => $yw_time,
                    'hx_money' => $item['money'] ?? 0,
                    'heji_zhongliang' => $item['zhongliang'] ?? 0,
                    'customer_id' => $item['customer_id']
                ]];
                $item['yw_time'] = $yw_time;
                $item['hxmoney'] = $item['money'] ?? 0;
                $item['hxzhongliang'] = $item['zhongliang'] ?? 0;

                //添加费用单
                $res = $this->add($request, $item, ++$count, true, false);
                if ($res !== true) {
                    throw new Exception($res);
                }
            }
            if ($useTrans) {
                Db::commit();
            }
            return true;
        } catch (Exception $e) {
            if ($useTrans) {
                Db::rollback();
            }
            return $e->getMessage();
        }
    }
}