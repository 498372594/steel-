<?php


namespace app\admin\model;


use Exception;
use think\db\exception\{DataNotFoundException, ModelNotFoundException};
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
     * @throws Exception
     */
    public function addHk($dataId, $ywType, $beizhu, $customerId, $ywTime, $jiesuanId, $piaojuId, $money, $zhongliang, $groupId)
    {
        $obj = CapitalHk::where('data_id', $dataId)->where('hk_type', $ywType)->find();
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

    /**
     * @param $id
     * @param $oldMoney
     * @param $money
     * @param $oldZhongliang
     * @param $zhongliang
     * @throws DbException
     * @throws Exception
     */
    public function tiaoMoney($id, $oldMoney, $money, $oldZhongliang, $zhongliang)
    {
        $money = empty($money) ? 0 : $money;
        $zhongliang = $zhongliang == null ? 0 : $zhongliang;
        $obj = self::get($id);

        if ($money != 0) {
            $obj->hxmoney = $obj['hxmoney'] + $money - $oldMoney;
        }

//        if (yfkMoney . compareTo(BigDecimal . valueOf(0L)) != 0) {
//        obj . setYfkhxmoney(obj . getYfkhxmoney() . add(yfkMoney . subtract(oldYfkMoney)));
//    }

        if ($obj['hxmoney'] > $obj['money']) {
            throw new Exception("核销金额不能大于总金额");
        }
        if ($zhongliang != 0) {
            $obj->hxzhongliang = $obj['hxzhongliang'] + $zhongliang - $oldZhongliang;
        }

        if ($obj['hxzhongliang'] > $obj['zhongliang']) {
            throw new Exception("核销重量不能大于总重量");
        }
        $obj->save();
    }

    /**
     * @param $id
     * @param $money
     * @param $zhongliang
     * @throws DbException
     * @throws Exception
     */
    public function addMoney($id, $money, $zhongliang)
    {
        $money = empty($money) ? 0 : $money;
        $zhongliang = empty($zhongliang) ? 0 : $zhongliang;


        $obj = self::get($id);
        if ($money != 0) {
            $hxmoney = empty($obj['hxmoney']) ? 0 : $obj['hxmoney'];
            $obj->hxmoney = $hxmoney + $money;
        }

        if ($obj['hxmoney'] > $obj['money']) {
            throw new Exception("核销金额不能大于总金额");
        }
        if ($zhongliang != 0) {
            $hxzhongliang = empty($obj['hxzhongliang']) ? 0 : $obj['hxzhongliang'];
            $obj['hxzhongliang'] = $hxzhongliang + $zhongliang;
        }

        if ($obj['hxzhongliang'] > $obj['zhongliang']) {
            throw new Exception("核销重量不能大于总重量");
        }

        $obj->save();
    }

    /**
     * @param $id
     * @param $money
     * @param $yfkMoney
     * @param $zhongliang
     * @throws DbException
     */
    public function jianMoney($id, $money, $zhongliang)
    {
        $money = empty($money) ? 0 : $money;
        $zhongliang = empty($zhongliang) ? 0 : $zhongliang;
        $obj = self::get($id);
        if ($money != 0) {
            $obj['hxmoney'] -= $money;
        }
        if ($zhongliang != 0) {
            $obj['hxzhongliang'] -= $zhongliang;
        }
        $obj->save();
    }

    /**
     * @param $dataId
     * @param $ywType
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function deleteHk($dataId, $ywType)
    {
        $obj = CapitalHk::where('data_id', $dataId)->where('hk_type', $ywType)->find();
        if (empty($obj)) {
            return;
        }
        if ($obj['hxmoney'] != 0 || $obj['hxzhongliang'] != 0) {
            throw new Exception("已经有结算信息!");
        }
        $obj->delete();
    }
}