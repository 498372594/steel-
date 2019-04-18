<?php


namespace app\admin\model;


use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use traits\model\SoftDelete;

class CapitalHk extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    /**
     * @param $dataId
     * @param $ywType
     * @param $systemNumber
     * @param $beizhu
     * @param $customerId
     * @param $fangxiang
     * @param $ywTime
     * @param $jiesuanId
     * @param $piaojuId
     * @param $money
     * @param $zhongliang
     * @param $groupId
     * @param $saleOperatorId
     * @param $userId
     * @param $companyId
     */
    public function insertHk($dataId, $ywType, $systemNumber, $beizhu, $customerId, $fangxiang, $ywTime, $jiesuanId, $piaojuId, $money, $zhongliang, $groupId, $saleOperatorId, $userId, $companyId)
    {
        $hk = new self();
        $hk->companyid = $companyId;
        $hk->system_number = $systemNumber;
        $hk->beizhu = $beizhu;
        $hk->create_operator_id = $userId;
        $hk->customer_id = $customerId;
        $hk->data_id = $dataId;
        $hk->fangxiang = $fangxiang;
        $hk->group_id = $groupId;
        $hk->hk_type = $ywType;
        $hk->yw_time = $ywTime;
        $hk->jiesuan_id = $jiesuanId;
        $hk->money = $money;
        $hk->zhongliang = $zhongliang;
        $hk->sale_operator_id = $saleOperatorId;
        $hk->cache_pjlx_id = $piaojuId;
        $hk->sale_operator_id = $saleOperatorId;
        $hk->save();
    }

    /**
     * @param $dataId
     * @param $ywType
     * @param $beizhu
     * @param $customerId
     * @param $ywTime
     * @param $jiesuanId
     * @param $piaojuId
     * @param $money
     * @param $zhongliang
     * @param $groupId
     * @param $saleOperatorId
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws Exception
     */
    public function updateHk($dataId, $ywType, $beizhu, $customerId, $ywTime, $jiesuanId, $piaojuId, $money, $zhongliang, $groupId, $saleOperatorId)
    {
        $obj = self::where('data_id', $dataId)->where('hk_type', $ywType)->find();
        if (!empty($obj)) {
            if ($obj->hxmoney != 0 || $obj->yfkhxmoney != 0 || $obj->hxzhongliang != 0) {
                throw new Exception("已经有结算信息!");
            }
            $obj->beizhu = $beizhu;
            $obj->customer_id = $customerId;
            $obj->yw_time = $ywTime;
            $obj->jiesuan_id = $jiesuanId;
            $obj->money = $money;
            $obj->zhongliang = $zhongliang;
            $obj->group_id = $groupId;
            $obj->cache_pjlx_id = $piaojuId;
            $obj->sale_operator_id = $saleOperatorId;
            $obj->save();
        }
    }

    /**
     * @param $dataId
     * @param $ywType
     * @param $beizhu
     * @param $customerId
     * @param $ywTime
     * @param $jiesuanId
     * @param $piaojuId
     * @param $money
     * @param $zhongliang
     * @param $groupId
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function addHk($dataId, $ywType, $beizhu, $customerId, $ywTime, $jiesuanId, $piaojuId, $money, $zhongliang, $groupId)
    {
        $obj = CapitalHk::where('data_id', $dataId)->where('hk_type', $ywType)->select();
        if (!empty($obj)) {
            if ($obj['hxmoney'] > 0 || $obj['yfkhxmoney'] > 0 || $obj['hxzhongliang'] > 0) {
                throw new Exception("已经有结算信息!");
            }

            $obj->beizhu = $beizhu;
            $obj->customer_id = $customerId;
            $obj->yw_time = $ywTime;
            $obj->jiesuan_id = $jiesuanId;
            $obj->money = $money + $obj->money;
            $obj->zhongliang = $zhongliang + $obj->zhongliang;
            $obj->group_id = $groupId;
            $obj->cache_pjlx_id = $piaojuId;
            $obj->save();
        }
    }
}