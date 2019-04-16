<?php


namespace app\admin\model;

use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use traits\model\SoftDelete;

class KucunCktz extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;
    protected $type = [
        'houdu' => 'float',
        'kuandu' => 'float',
        'changdu' => 'float',
        'lingzhi' => 'float',
        'jianshu' => 'float',
        'zhijian' => 'float',
        'counts' => 'float',
        'zhongliang' => 'float',
        'price' => 'float',
        'sumprice' => 'float',
        'shuie' => 'float',
        'shui_price' => 'float',
        'sum_shui_price' => 'float',
    ];

    public function custom()
    {
        return $this->belongsTo('Custom', 'cache_customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['custom_name' => 'custom']);
    }

    public function specification()
    {
        return $this->belongsTo('ViewSpecification', 'guige_id', 'id')->cache(true, 60)
            ->field('id,specification,mizhong_name,productname');
    }

    public function jsfs()
    {
        return $this->belongsTo('Jsfs', 'jijiafangshi_id', 'id')->cache(true, 60)
            ->field('id,jsfs')->bind(['jsfs_name' => 'jsfs']);
    }

    public function storage()
    {
        return $this->belongsTo('Storage', 'store_id', 'id')->cache(true, 60)
            ->field('id,storage')->bind(['storage_name' => 'storage']);
    }

    public function adder()
    {
        return $this->belongsTo('Admin', 'cache_create_operator', 'id')->cache(true, 60)
            ->field('id,name')->bind(['add_name' => 'name']);
    }

    /**
     * @param $dataId
     * @param $chukuType
     * @param $guigeId
     * @param $caizhiId
     * @param $chandiId
     * @param $jijiafangshiId
     * @param $storeId
     * @param $houdu
     * @param $changdu
     * @param $kuandu
     * @param $counts
     * @param $jianshu
     * @param $lingzhi
     * @param $zhijian
     * @param $zhongliang
     * @param $shuiPrice
     * @param $sumprice
     * @param $sumShuiPrice
     * @param $price
     * @param $pihao
     * @param $beizhu
     * @param $chehao
     * @param $cacheYwtime
     * @param $cacheDataPnumber
     * @param $cacheCustomerId
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function updateChukuTz($dataId, $chukuType, $guigeId, $caizhiId, $chandiId, $jijiafangshiId, $storeId, $houdu, $changdu, $kuandu, $counts, $jianshu, $lingzhi, $zhijian, $zhongliang, $shuiPrice, $sumprice, $sumShuiPrice, $price, $pihao, $beizhu, $chehao, $cacheYwtime, $cacheDataPnumber, $cacheCustomerId)
    {
        $this->ifCkMdMxExists($dataId, $chukuType);

        $cktz = self::where('data_id', $dataId)->where('chuku_type', $chukuType)->find();
        $cktz->guige_id = $guigeId;
        $cktz->caizhi = $caizhiId;
        $cktz->chandi = $chandiId;
        $cktz->store_id = $storeId;
        $cktz->jijiafangshi_id = $jijiafangshiId;
        $cktz->houdu = $houdu;
        $cktz->changdu = $changdu;
        $cktz->kuandu = $kuandu;
        $cktz->counts = $counts;
        $cktz->jianshu = $jianshu;
        $cktz->lingzhi = $lingzhi;
        $cktz->zhijian = $zhijian;
        $cktz->zhongliang = $zhongliang;
        $cktz->shuiprice = $shuiPrice;
        $cktz->sumprice = $sumprice;
        $cktz->sum_shui_price = $sumShuiPrice;
        $cktz->price = $price;
        $cktz->shuie = $cktz->sum_shui_price - $cktz->sumprice;
        $cktz->pihao = $pihao;
        $cktz->remark = $beizhu;
        $cktz->car_no = $chehao;
        $cktz->cache_ywtime = $cacheYwtime;
        $cktz->cacheData_pnumber = $cacheDataPnumber;
        $cktz->cacheCustomer_id = $cacheCustomerId;
        $cktz->save();
    }

    /**
     * 根据data id和chuku type删除出库通知
     * @param $dataId
     * @param $chukuType
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws Exception
     */
    public function deleteByDataIdAndChukuType($dataId, $chukuType)
    {
        $this->ifCkMdMxExists($dataId, $chukuType);
        $list = self::where('data_id', $dataId)
            ->where('chuku_type', $chukuType)
            ->select();
        if (empty($list)) {
            throw new Exception("对象不存在");
        }
        self::where('data_id', $dataId)
            ->where('chuku_type', $chukuType)
            ->delete();
    }

    /**
     * 判断是否已有出库
     * @param $dataId
     * @param $chukuType
     * @throws Exception
     */
    private function ifCkMdMxExists($dataId, $chukuType)
    {
        $count = StockOutDetail::alias('mx')
            ->join('__KUCUN_CKTZ__ tz', 'tz.id=mx.kucun_cktz_id')
            ->join('__STOCK_OUT_MD__ md', 'md.stock_out_detail_id=mx.id')
            ->join('__STOCK_OUT__ ck', 'ck.id=mx.stock_out_id')
            ->where('md.chuku_type', $chukuType)
            ->where('tz.data_id', $dataId)
            ->where('ck.status', '<>', 2)
            ->count();
        $count1 = StockOutMd::alias('md')
            ->join('__KUCUN_CKTZ__ tz', 'tz.id=md.kucun_cktz_id')
            ->join('__STOCK_OUT__ ck', 'ck.id=md.kucun_cktz_id')
            ->where('md.chuku_type', $chukuType)
            ->where('tz.data_id', $dataId)
            ->where('ck.status', '<>', 2)
            ->count();
        if ($count > 0) {
            throw new Exception("已有发货记录,操作终止");
        }

        if ($count1 > 0) {
            throw new Exception("已有发货记录,操作终止");
        }
    }

    public function insertChukuTz($dataId, $chukuType, $guigeId, $caizhiId, $chandiId, $jijiafangshiId,
                                  $storeId, $houdu, $changdu, $kuandu, $counts, $jianshu, $lingzhi, $zhijian,
                                  $zhongliang, $shuiPrice, $sumprice, $sumShuiPrice, $price, $pihao, $beizhu,
                                  $chehao, $cacheYwtime, $cacheDataPnumber, $cacheCustomerId, $userId, $companyId)
    {
        $cktz = new self();
        $cktz->data_id = $dataId;
        $cktz->chuku_type = $chukuType;
        $cktz->guige_id = $guigeId;
        $cktz->caizhi = $caizhiId;
        $cktz->chandi = $chandiId;
        $cktz->store_id = $storeId;
        $cktz->jijiafangshi_id = $jijiafangshiId;
        $cktz->houdu = $houdu;
        $cktz->changdu = $changdu;
        $cktz->kuandu = $kuandu;
        $cktz->counts = $counts;
        $cktz->jianshu = $jianshu;
        $cktz->lingzhi = $lingzhi;
        $cktz->zhijian = $zhijian;
        $cktz->zhongliang = $zhongliang;
        $cktz->shui_price = $shuiPrice;
        $cktz->sumprice = $sumprice;
        $cktz->sum_shui_price = $sumShuiPrice;
        $cktz->price = $price;
        $cktz->shuie = $cktz->sum_shui_price - $cktz->sumprice;
        $cktz->pihao = $pihao;
        $cktz->beizhu = $beizhu;
        $cktz->chehao = $chehao;
        $cktz->cache_ywtime = $cacheYwtime;
        $cktz->cache_data_pnumber = $cacheDataPnumber;
        $cktz->cache_customer_id = $cacheCustomerId;
        $cktz->cache_create_operator = $userId;
        $cktz->companyid = $companyId;

        $cktz->save();
    }
}