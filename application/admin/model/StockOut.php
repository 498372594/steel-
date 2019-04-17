<?php


namespace app\admin\model;


use Exception;
use think\exception\DbException;
use traits\model\SoftDelete;

class StockOut extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function wait()
    {
        return $this->hasMany('StockOutDetail', 'stock_out_id', 'id');
    }

    public function already()
    {
        return $this->hasMany('StockOutMd', 'stock_out_id', 'id');
    }

    /**
     * @param $dataId
     * @param $chukuType
     * @param $ywTime
     * @param $groupId
     * @param $cacheDataPnumber
     * @param $saleOperatorId
     * @param $userId
     * @param $companyId
     * @return StockOut
     * @throws Exception
     */
    public function insertChuku($dataId, $chukuType, $ywTime, $groupId, $cacheDataPnumber, $saleOperatorId, $userId, $companyId)
    {
        $ck = new self();
        if (empty($chukuType)) {
            throw new Exception("请传入出库类型[chukuType]");
        }
        switch ($chukuType) {
            case 1:
                $ck->remark = "库存调拨单," . $cacheDataPnumber;
                break;
            case 2:
                $ck->remark = "盘亏出库," . $cacheDataPnumber;
                break;
            case 3:
                $ck->remark = "其它出库单," . $cacheDataPnumber;

                break;
            case 4:
                $ck->remark = "销售单," . $cacheDataPnumber;

                break;
            case 9:
                $ck->remark = "清库出库单," . $cacheDataPnumber;

                break;
            case 10:
                $ck->remark = "采购退货单," . $cacheDataPnumber;

                break;
            case 11:
                $ck->remark = "卷板开平加工," . $cacheDataPnumber;

                break;
            case 12:
                $ck->remark = "卷板纵剪加工," . $cacheDataPnumber;

                break;
            case 14:
                $ck->remark = "卷板切割加工," . $cacheDataPnumber;

                break;
            case 16:
                $ck->remark = "通用加工," . $cacheDataPnumber;
                break;
            default:
                throw new Exception("请传入匹配的出库类型[chukuType]");
        }

        $ck->create_operator_id = $userId;
        $ck->department = $groupId;
        $ck->sale_operator_id = $saleOperatorId;
        $count = self::withTrashed()->where('companyid', $companyId)->whereTime('create_time', 'today')->count();
        $ck->system_number = 'CKD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
        $ck->yw_time = $ywTime;
        $ck->out_mode = "1";
        $ck->data_id = $dataId;
        $ck->chuku_type = $chukuType;
        $ck->save();
        return $ck;
    }

    /**
     * @param StockOut $ck
     * @param $spotId
     * @param $dataId
     * @param $chukuType
     * @param $ywTime
     * @param $dataPnumber
     * @param $customerId
     * @param $guigeId
     * @param $caizhiId
     * @param $chandiId
     * @param $jijiafangshiId
     * @param $storeId
     * @param $houdu
     * @param $kuandu
     * @param $changdu
     * @param $zhijian
     * @param $lingzhi
     * @param $jianshu
     * @param $counts
     * @param $zhongliang
     * @param $price
     * @param $sumPrice
     * @param $shuiPrice
     * @param $sumShuiPrice
     * @param $shuie
     * @param $mizhong
     * @param $jianzhong
     * @param $cbPrice
     * @param $beizhu
     * @param $userId
     * @param $companyId
     * @throws DbException
     * @throws Exception
     */
    public function insertCkMxMd(StockOut $ck, $spotId, $dataId, $chukuType, $ywTime, $dataPnumber, $customerId, $guigeId,
                                 $caizhiId, $chandiId, $jijiafangshiId, $storeId, $houdu, $kuandu, $changdu, $zhijian,
                                 $lingzhi, $jianshu, $counts, $zhongliang, $price, $sumPrice, $shuiPrice, $sumShuiPrice,
                                 $shuie, $mizhong, $jianzhong, $cbPrice, $beizhu, $userId, $companyId)
    {
        $mx = (new StockOutDetail())->insertCkMx($ck, $dataId, $chukuType, $ywTime, $dataPnumber, $customerId,
            $guigeId, $caizhiId, $chandiId, $jijiafangshiId, $storeId, $houdu, $kuandu, $changdu, $zhijian, $lingzhi,
            $jianshu, $counts, $zhongliang, $price, $sumPrice, $shuiPrice, $sumShuiPrice, $shuie, $mizhong, $jianzhong,
            $beizhu, $userId, $companyId);

        (new StockOutMd())->insertCkMd($ck['id'], $mx['id'], $spotId, $dataId, $chukuType, $mx['pinming_id'], $guigeId,
            $caizhiId, $chandiId, $jijiafangshiId, $storeId, $houdu, $kuandu, $changdu, $zhijian, $lingzhi, $jianshu,
            $counts, $zhongliang, $mizhong, $jianzhong, $cbPrice, $beizhu, $shuie, $companyId);
        $spot = KcSpot::get($spotId);
        if (empty($spot) || empty($spotId)) {
            throw new Exception("引用的采购单还未入库，请入库后再操作！");
        }
    }
}