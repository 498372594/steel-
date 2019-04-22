<?php


namespace app\admin\model;


use Exception;
use think\db\exception\{DataNotFoundException, ModelNotFoundException};
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
     * @throws Exception
     */
    public function updateInv($dataId, $ywType, $fangxiang, $customerId, $ywTime, $changdu, $kuandu, $houdu, $guigeId,
                              $jijiafangshiId, $piaojuId, $pinmingId, $zhongliang, $price, $sumPrice, $sumShuiPrice, $shuiPrice)
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
            if (empty($pinmingId) && !empty($guigeId)) {
                $gg = ViewSpecification::where('id', $guigeId)->cache(true, 60)->find();
                $obj->pinming_id = $gg['productname_id'] ?? '';
            } else {
                $obj->pinming_id = $pinmingId;
            }
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

    /**
     * @param $dataId
     * @param $ywType
     * @param $fangxiang
     * @param $changdu
     * @param $houdu
     * @param $kuandu
     * @param $guigeId
     * @param $jijiafangshiId
     * @param $piaojuId
     * @param $pinmingId
     * @param $systemNumber
     * @param $customerId
     * @param $ywTime
     * @param $price
     * @param $shuiPrice
     * @param $sumPrice
     * @param $sumShuiPrice
     * @param $zhongliang
     * @param $companyId
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function insertInv($dataId, $ywType, $fangxiang, $changdu, $houdu, $kuandu, $guigeId, $jijiafangshiId, $piaojuId, $pinmingId,
                              $systemNumber, $customerId, $ywTime, $price, $shuiPrice, $sumPrice, $sumShuiPrice, $zhongliang, $companyId)
    {
        $i = new self();
        $i->system_number = $systemNumber;
        $i->customer_id = $customerId;
        $i->yw_time = $ywTime;
        $i->yw_type = $ywType;
        $i->changdu = $changdu;
        $i->kuandu = $kuandu;
        $i->fx_type = $fangxiang;
        $i->guige_id = $guigeId;
        $i->houdu = $houdu;
        $i->jijiafangshi_id = $jijiafangshiId;
        $i->piaoju_id = $piaojuId;
        if (empty($pinmingId) && !empty($guigeId)) {
            $gg = ViewSpecification::where('id', $guigeId)->cache(true, 60)->find();
            $i->pinming_id = $gg['productname_id'] ?? '';
        } else {
            $i->pinming_id = $pinmingId;
        }

        $i->price = $price;
        $i->shui_price = $shuiPrice;
        $i->sum_price = $sumPrice;
        $i->sum_shui_price = $sumShuiPrice;
        $i->zhongliang = $zhongliang;
        $i->yhx_price = 0;
        $i->yhx_zhongliang = 0;
        $i->data_id = $dataId;
        $i->companyid = $companyId;
        $i->save();
    }

    /**
     * @param $dataId
     * @param $money
     * @param $zhongliang
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function jianMoney($dataId,$money,$zhongliang){
        $money=$money==null?0:$money;
        $zhongliang=$money==null?0:$zhongliang;
        $inv=new self();
        $obj=$inv::where("id",$dataId)->field("id,yhx_zhongliang,yhx_price")->find();
        if($obj){
            if($money!=0){
                $fhMoney=$obj["yhx_price"]-$money;
                if($fhMoney<0){
                    $obj["yhx_price"]=0;
                }else{
                    $obj["yhx_price"]=$obj["yhx_price"]-$money;
                }
            }
            if($zhongliang!=0){
                $fhzhongliang=$obj["yhx_zhongliang"]-$money;
                if($fhzhongliang<0){
                    $obj["yhx_zhongliang"]=0;
                }else{
                    $obj["yhx_zhongliang"]=$obj["yhx_zhongliang"]-$money;
                }
            }
            $inv->save($obj);
        }
    }
    public function tiaoMoney($id,$oldMoney,$money,$oldZhongliang,$zhongliang){
        $money=$money==null?0:$money;
        $zhongliang=$money==null?0:$zhongliang;
        $oldMoney=$oldMoney==null?0:$oldMoney;
        $oldZhongliang=$oldZhongliang==null?0:$oldZhongliang;
        $inv=new self();
        $obj=$inv::where("id",$id)->field("id,yhx_zhongliang,yhx_price")->find();
        if($money!=0){
            $obj["yhx_price"]= $obj["yhx_price"]+($money-$oldMoney);
        }
        if($zhongliang!=0){
            $obj["yhx_zhongliang"]= $obj["yhx_zhongliang"]+($zhongliang-$oldZhongliang);
        }
        $inv->save($obj);
    }
    public function addMoney($dataId,$money,$zhongliang){
        $money=$money==null?0:$money;
        $zhongliang=$money==null?0:$zhongliang;
        $inv=new self();
        $obj=$inv::where("id",$id)->field("id,yhx_zhongliang,yhx_price")->find();
        if($money!=0){
            $obj["yhx_price"]= $obj["yhx_price"]+($money);
        }
        if($zhongliang!=0){
            $obj["yhx_zhongliang"]= $obj["yhx_zhongliang"]+($zhongliang);
        }
        $inv->save($obj);
    }
}