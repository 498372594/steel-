<?php


namespace app\admin\model;


use Exception;
use think\exception\DbException;
use traits\model\SoftDelete;

class StockOutMd extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public static function findCountsByDataId($dataId)
    {
        return self::alias('md')
            ->join('__STOCK_OUT__ ck', 'ck.id=md.stock_out_id')
            ->where('md.data_id', $dataId)
            ->where('ck.status', '<>', 2)
            ->max('md.counts');
    }

    public static function findZhongliangByDataId($dataId)
    {
        return self::alias('md')
            ->join('__STOCK_OUT__ ck', 'ck.id=md.stock_out_id')
            ->where('md.data_id', $dataId)
            ->where('ck.status', '<>', 2)
            ->max('md.zhongliang');
    }

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

    public function spot()
    {
        return $this->belongsTo('KcSpot', 'kc_spot_id', 'id')->cache(true, 60)
            ->field('id,resource_number')->bind('resource_number');
    }

    public function storage()
    {
        return $this->belongsTo('Storage', 'store_id', 'id')->cache(true, 60)
            ->field('id,storage')->bind(['storage_name' => 'storage']);
    }

    public function mainData()
    {
        return $this->belongsTo('StockOut', 'stock_out_id', 'id');
    }

    public function stockOutData()
    {
        return $this->belongsTo('StockOut', 'sstock_out_id', 'id')
            ->field('id')->bind(['kc_ck_id' => 'id']);
    }

    public function caizhiData()
    {
        return $this->belongsTo('Texture', 'caizhi', 'id')->cache(true, 60)
            ->field('id,texturename')->bind('texturename');
    }

    public function chandiData()
    {
        return $this->belongsTo('Originarea', 'chandi', 'id')->cache(true, 60)
            ->field('id,originarea')->bind(['originarea_name' => 'originarea']);
    }

    /**
     * @param $ckId
     * @param $ckMxId
     * @param $spotId
     * @param $dataId
     * @param $chukuType
     * @param $pinmingId
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
     * @param $mizhong
     * @param $jianzhong
     * @param $cbPrice
     * @param $beizhu
     * @param $companyId
     * @throws DbException
     * @throws Exception
     */
    public function insertCkMd($ckId, $ckMxId, $spotId, $dataId, $chukuType, $pinmingId, $guigeId, $caizhiId, $chandiId, $jijiafangshiId, $storeId, $houdu, $kuandu, $changdu, $zhijian, $lingzhi, $jianshu, $counts, $zhongliang, $mizhong, $jianzhong, $cbPrice, $beizhu, $shuie, $companyId)
    {

        $spot = KcSpot::get($spotId);
        if (empty($spot) || empty($spotId)) {
            throw new Exception("引用的采购单还未入库，请入库后再操作！");
        }

        $md = new StockOutMd();
        $md->companyid = $companyId;
        $md->stock_out_id = $ckId;
        $md->stock_out_detail_id = $ckMxId;
        $md->kc_spot_id = $spotId;
        $md->data_id = $dataId;
        $md->chuku_type = $chukuType;
        $md->out_mode = 1;
        $md->pinming_id = $pinmingId;
        $md->caizhi = $caizhiId;
        $md->chandi = $chandiId;
        $md->jijiafangshi_id = $jijiafangshiId;
        $md->guige_id = $guigeId;
        $md->houdu = $houdu;
        $md->kuandu = $kuandu;
        $md->counts = $counts;
        $md->jianshu = $jianshu;
        $md->changdu = $changdu;
        $md->lingzhi = $lingzhi;
        $md->zhijian = $zhijian;
        $md->zhongliang = $zhongliang;
        $md->store_id = $storeId;
        $md->price = $spot['price'];
        $md->shuiprice = $spot['shui_price'];
        $md->beizhu = $beizhu;

        if (empty($cbPrice)) {
            $cbPrice = $spot['price'];
        }
        $md->cbPrice = $cbPrice;
        $jjfs = Jsfs::get($spot['jijiafangshi_id']);
        if ($jjfs['jj_type'] == 1 || $jjfs['jj_type'] == 2) {
            $md->sum_shui_price = $md->price * $md->zhongliang;
            $md->cb_sum_shuiprice = $md->cb_price * $md->zhongliang;
        } else if ($jjfs['jj_type'] == 3) {
            $md->sum_shui_price = $md->price * $md->counts;
            $md->cb_sum_shuiprice = $md->cb_price * $md->counts;
        }

//        $md->cbSumPrice(WuziUtil . calSumPrice(md . getCbSumShuiPrice(), md . getShuiprice()));
//        $md->cbShuie(WuziUtil . calShuie(md . getCbSumShuiPrice(), md . getShuiprice()));
//        $md->fySz(md . getCbSumShuiPrice() . subtract(md . getSumShuiPrice()));

//        $md->sumprice(WuziUtil . calSumPrice(md . getSumShuiPrice(), md . getShuiprice()));
//        $md->shuie(WuziUtil . calShuie(md . getSumShuiPrice(), md . getShuiprice()));
        $md->data_id = $dataId;
        $md->huohao = $spot['huohao'];
        $md->chehao = $spot['chehao'];
        $md->pihao = $spot['pihao'];
        $md->mizhong = $mizhong;
        $md->jianzhong = $jianzhong;
        $md->save();

        (new KcSpot())->adjustSpotById($spotId, false, $md['counts'], $md['zhongliang'], $md['jijiafangshi_id'], $shuie);
    }
}