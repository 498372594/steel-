<?php


namespace app\admin\model;


use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use traits\model\SoftDelete;

class Inv extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    /**
     * @param $dataId
     * @param $ywType
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws Exception
     */
    public function deleteInv($dataId, $ywType)
    {
        $item = self::where('data_id', $dataId)
            ->where('yw_type', $ywType)
            ->find();
        if (!empty($item)) {
            if ($item['yhx_price'] != 0 || $item['yhx_zhongliang'] != 0) {
                throw new Exception("已经有发票结算信息!");
            }
            $item->delete();
        }
    }

    /**
     * @param $dataId
     * @param $ywType
     * @param $fangxiang
     * @param $customerId
     * @param $ywTime
     * @param $changdu
     * @param $kuandu
     * @param $houdu
     * @param $guigeId
     * @param $jijiafangshiId
     * @param $piaojuId
     * @param $pinmingId
     * @param $zhongliang
     * @param $price
     * @param $sumPrice
     * @param $sumShuiPrice
     * @param $shuiPrice
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function updateInv($dataId, $ywType, $fangxiang, $customerId, $ywTime, $changdu, $kuandu, $houdu, $guigeId, $jijiafangshiId, $piaojuId, $pinmingId, $zhongliang, $price, $sumPrice, $sumShuiPrice, $shuiPrice)
    {
        $obj = self::where('data_id', $dataId)->where('yw_type', $ywType)->find();
        if (!empty($list)) {
            $cgmx = CgPurchaseMx::get($dataId);
            if (!empty($cgmx)) {
                $cg = CgPurchase::get($cgmx['purchase_id']);
                if (empty($customerId)) {
                    $customerId = $cg['customer_id'];
                }
                if (empty($ywTime)) {
                    $ywTime = $cg['yw_time'];
                }
                if (empty($piaojuId)) {
                    $piaojuId = $cg['piaoju_id'];
                }
            }
            if ($obj['yhx_price'] != 0 || $obj['yhx_zhongliang'] != 0) {
                throw new Exception("已经有发票结算信息!");
            }
            $obj->customer_id = $customerId;
            $obj->yw_time = $ywTime;
            $obj->changdu = $changdu;
            $obj->kuandu = $kuandu;
            $obj->guige_id = $guigeId;
            $obj->houdu = $houdu;
            $obj->jijiafangshi_id = $jijiafangshiId;
            $obj->piaoju_id = $piaojuId;
            $obj->pinming_id = $pinmingId;
            $obj->price = $price;
            $obj->shui_price = $shuiPrice;
            $obj->sum_price = $sumPrice;
            $obj->sum_shui_price = $sumShuiPrice;
            $obj->zhongliang = $zhongliang;
            if ($fangxiang != null) {
                $obj->fx_type = $fangxiang;
            }
            $obj->save();
        }
    }

    public function insertInv($dataId, $ywType, $fangxiang, $changdu, $houdu, $kuandu, $guigeId, $jijiafangshiId, $piaojuId,
                               $systemNumber, $customerId, $ywTime, $price, $shuiPrice, $sumPrice, $sumShuiPrice, $zhongliang, $companyId)
    {
        $i = new self();
        $i->systemNumber = $systemNumber;
        $i->customerId = $customerId;
        $i->ywTime = $ywTime;
        $i->ywType = $ywType;
        $i->changdu = $changdu;
        $i->kuandu = $kuandu;
        $i->fxType = $fangxiang;
        $i->guigeId = $guigeId;
        $i->houdu = $houdu;
        $i->jijiafangshiId = $jijiafangshiId;
        $i->piaojuId = $piaojuId;
        $i->price = $price;
        $i->shuiPrice = $shuiPrice;
        $i->sumPrice = $sumPrice;
        $i->sumShuiPrice = $sumShuiPrice;
        $i->zhongliang = $zhongliang;
        $i->yhxPrice = 0;
        $i->yhxZhongliang = 0;
        $i->dataId = $dataId;
        $i->companyid = $companyId;
        $i->save();
    }
}