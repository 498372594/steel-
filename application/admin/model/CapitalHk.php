<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class CapitalHk extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function insertHk($dataId, $ywType, $systemNumber, $beizhu, $customerId, $fangxiang, $ywTime, $jiesuanId, $piaojuId, $money, $zhongliang, $groupId, $saleOperatorId, $userId)
    {
        $hk = new self();
        $hk->systemNumber = $systemNumber;
        $hk->beizhu = $beizhu;
        $hk->createOperatorId = $userId;
        $hk->customerId = $customerId;
        $hk->dataId = $dataId;
        $hk->fangxiang = $fangxiang;
        $hk->groupId = $groupId;
        $hk->hkType = $ywType;
        $hk->ywTime = $ywTime;
        $hk->jiesuanId = $jiesuanId;
        $hk->money = $money;
        $hk->zhongliang = $zhongliang;
        $hk->saleOperatorId = $saleOperatorId;
        $hk->cachePjlxId = $piaojuId;
        $hk->saleOperatorId = $saleOperatorId;
        $hk->save();
    }


    public function updateHk($dataId, $ywType, $beizhu, $customerId, $ywTime, $jiesuanId, $piaojuId, $money, $zhongliang, $groupId, $saleOperatorId)
    {
        $obj = self::where('data_id', $dataId)->where('hk_type', $ywType)->find();
        if (!empty($obj)) {
            if ($obj->hxmoney != 0 || $obj->yfkhxmoney != 0 || $obj->hxzhongliang != 0) {
                throw new Exception("已经有结算信息!");
            }
            $obj->beizhu = $beizhu;
            $obj->customerId = $customerId;
            $obj->ywTime = $ywTime;
            $obj->jiesuanId = $jiesuanId;
            $obj->money = $money;
            $obj->zhongliang = $zhongliang;
            $obj->groupId = $groupId;
            $obj->cachePjlxId = $piaojuId;
            $obj->saleOperatorId = $saleOperatorId;
            $obj->save();
        }
    }
}