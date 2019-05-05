<?php


namespace app\admin\model;

use think\db\exception\{DataNotFoundException, ModelNotFoundException};
use think\exception\DbException;
use traits\model\SoftDelete;

class StockOutDetail extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function specification()
    {
        return $this->belongsTo('ViewSpecification', 'guige_id', 'id')->cache(true, 60)
            ->field('id,specification,mizhong_name,productname')
            ->bind(['guige' => 'specification', 'pinming' => 'productname']);
    }

    public function jsfs()
    {
        return $this->belongsTo('Jsfs', 'jijiafangshi_id', 'id')->cache(true, 60)
            ->field('id,jsfs')->bind(['jsfs_name' => 'jsfs']);
    }

    public function custom()
    {
        return $this->belongsTo('Custom', 'cache_customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['custom_name' => 'custom']);
    }

    /**
     * @param StockOut $ck
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
     * @param $beizhu
     * @param $userId
     * @param $companyId
     * @return StockOutDetail
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function insertCkMx(StockOut $ck, $dataId, $chukuType, $ywTime, $dataPnumber, $customerId, $guigeId,
                               $caizhiId, $chandiId, $jijiafangshiId, $storeId, $houdu, $kuandu, $changdu, $zhijian,
                               $lingzhi, $jianshu, $counts, $zhongliang, $price, $sumPrice, $shuiPrice, $sumShuiPrice,
                               $shuie, $mizhong, $jianzhong, $beizhu, $userId, $companyId)
    {

        $mx = new StockOutDetail();
        $mx->companyid = $companyId;
        $mx->stock_out_id = $ck['id'];
        $mx->data_id = $dataId;
        $mx->chuku_type = $chukuType;
        $mx->out_mode = "1";
        $mx->cache_ywtime = $ywTime;
        $mx->cache_data_pnumber = $dataPnumber;
        $mx->cache_customer_id = $customerId;
        $mx->guige_id = $guigeId;
        $mx->caizhi = $caizhiId;
        $mx->chandi = $chandiId;
        $mx->jijiafangshi_id = $jijiafangshiId;
        $mx->store_id = $storeId;
        $mx->cache_create_operator = $userId;
        $mx->remark = $beizhu;
        $mx->changdu = $changdu;
        $mx->houdu = $houdu;
        $mx->kuandu = $kuandu;
        $mx->lingzhi = $lingzhi;
        $mx->jianshu = $jianshu;
        $mx->counts = $counts;
        $mx->zhongliang = $zhongliang;
        $mx->zhijian = $zhijian;
        $mx->price = $price;
        $mx->sumprice = $sumPrice;
        $mx->shuiprice = $shuiPrice;
        $mx->sum_shui_price = $sumShuiPrice;
        $mx->shuie = $shuie;

        if (empty($mizhong)) {
            $gg = ViewSpecification::where('id', $guigeId)->cache(true, 60)->find();
            $mx->pinming_id = $gg['productname_id'] ?? '';
            $mx->mizhong = $gg['mizhong_name'] ?? '';
        } else {
            $mx->mizhong = $mizhong;
            $gg = ViewSpecification::where('id', $guigeId)->cache(true, 60)->find();
            $mx->pinming_id = $gg['productname_id'] ?? '';
        }

        if (empty($jianzhong)) {
            if (!is_null($counts)) {
                if ($counts == 0) {
                    $mx->jianzhong = 0;
                } else {
                    $mx->jianzhong = ((empty($zhongliang) ? 0 : $zhongliang) / ($counts) * (empty($zhijian) ? 0 : $zhijian));
                }
            }
        } else {
            $mx->jianzhong = $jianzhong;
        }

        $addNumberCount = empty($ck['id']) ? 1 : StockOutDetail::where('stock_out_id', $ck['id'])->max('system_number');
        $mx->system_number = $addNumberCount;
        $mx->save();
        return $mx;
    }
}