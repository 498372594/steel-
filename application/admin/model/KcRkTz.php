<?php

namespace app\admin\model;

use traits\model\SoftDelete;

class KcRkTz extends Base
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';

    public function specification()
    {
        return $this->belongsTo('ViewSpecification', 'guige_id', 'id')->cache(true, 60)
            ->field('id,specification,mizhong_name,productname');
    }

    public function jsfs()
    {
        return $this->belongsTo('Jsfs', 'jiesuan_id', 'id')->cache(true, 60)
            ->field('id,jsfs')->bind(['jiesuan_name' => 'jsfs']);
    }

    public function storage()
    {
        return $this->belongsTo('Storage', 'store_id', 'id')->cache(true, 60)
            ->field('id,storage')->bind(['storage_name' => 'storage']);
    }

    public function pinmingData()
    {
        return $this->belongsTo('Productname', 'pinming_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['pinming' => 'name']);
    }

    public function caizhiData()
    {
        return $this->belongsTo('texture', 'caizhi_id', 'id')->cache(true, 60)
            ->field('id,texturename')->bind(['caizhi' => 'texturename']);
    }

    public function chandiData()
    {
        return $this->belongsTo('Originarea', 'chandi_id', 'id')->cache(true, 60)
            ->field('id,originarea')->bind(['chandi' => 'originarea']);
    }

    public function deleteByDataIdAndRukuType($dataId, $rukuType)
    {
        $this->ifRkMdMxExists($dataId, $rukuType);
        $list = self::where('data_id', $dataId)
            ->where('ruku_type', $rukuType)
            ->select();
        if (empty($list)) {
            throw new ValidateException("对象不存在");
        }
        self::destroy(array("data_id" => $dataId, "ruku_type" => $rukuType));

    }

    private function ifRkMdMxExists($dataId, $rukuType)
    {
        $count = KcRkMx::alias("mx")->join("kc_rk rk", "rk.id=mx.kc_rk_id", "left")
            ->join("kc_rk_tz tz", "tz.id=mx.kc_rk_tz_id", "left")
            ->where(array("tz.ruku_type" => $rukuType, "tz.data_id" => $dataId))
            ->where('ck.status', '<>', 1)
            ->count();
        $count1 = KcRkMd::alias("mx")->join("kc_rk rk", "rk.id=mx.kc_rk_id", "left")
            ->join("kc_rk_tz tz", "tz.id=mx.kc_rk_tz_id", "left")
            ->where(array("tz.ruku_type" => $rukuType, "tz.data_id" => $dataId))
            ->where('ck.status', '<>', 1)
            ->count();
        if ($count > 0) {
            throw new Exception("已有入库记录,操作终止");
        }

        if ($count1 > 0) {
            throw new Exception("已有入库记录,操作终止");
        }

    }

    public function updateRukuTz($dataId, $rukuType, $pinmingId, $guigeId, $caizhiId, $chandiId, $jijiafangshiId, $houdu, $changdu, $kuandu,
                                 $counts, $jianshu, $lingzhi, $zhijian, $zhongliang, $shuiPrice, $huohao, $pihao, $beizhu, $chehao, $cacheYwTime, $cacheDataNumber, $cacheDataPnumber
        , $cacheCustomerId, $storeId, $cachePiaojuId, $mizhong, $jianzhong)
    {
        $this->ifRkMdMxExists($dataId, $rukuType);
        $rktz = self::where('data_id', $dataId)->where('ruku_type', $rukuType)->find();
        $rktz->pinming_id = $pinmingId;
        $rktz->guige_id = $guigeId;
        $rktz->caizhi_id = $caizhiId;
        $rktz->chandi_id = $chandiId;
        $rktz->jijiafangshi_id = $jijiafangshiId;
        $rktz->houdu = $houdu;
        $rktz->kuandu = $kuandu;
        $rktz->changdu = $changdu;
        $rktz->counts = $counts;
        $rktz->lingzhi = $lingzhi;
        $rktz->zhijian = $zhijian;
        $rktz->zhongliang = $zhongliang;
        $rktz->shui_price = $shuiPrice;
        $rktz->huohao = $huohao;
        $rktz->pihao = $pihao;
        $rktz->beizhu = $beizhu;
        $rktz->chehao = $chehao;
        $rktz->cache_ywtime = $cacheYwTime;
        $rktz->cache_data_number = $cacheDataNumber;
        $rktz->cache_data_pnumber = $cacheDataPnumber;
        $rktz->cache_customer_id = $cacheCustomerId;
        $rktz->store_id = $storeId;
        $rktz->cache_piaoju_id = $cachePiaojuId;
        $rktz->mizhong = $mizhong;
        $rktz->jianzhong = $jianzhong;
        $rktz->save();

    }

    public function insertRukuTz($dataId,$rukuType, $pinmingId, $guigeId, $caizhiId, $chandId, $jijiafangshiId, $houdu, $changdu, $kuandu, $counts, $jianshu, $lingzhi, $zhijian, $zhongliang, $shuiPrice, $sumprice, $sumShuiPrice
        ,$shuie, $price, $huohao, $pihao, $beizhu, $chehao, $cacheYwtime, $cacheDataNumber, $cacheDataPnumber, $cacheCustomerId, $storeId, $cacheCreateOperator,$cachePiaojuId, $mizhong, $jianzhong, $companyId)
    {
        $tz = new self();
        $tz->ruku_type = $rukuType;
        $tz->data_id = $dataId;
        $tz->pinming_id = $pinmingId;
        $tz->guige_id = $guigeId;
        $tz->caizhi_id = $caizhiId;
        $tz->chandi_id = $chandId;
        $tz->store_id = $storeId;
        $tz->jijiafangshi_id = $jijiafangshiId;
        $tz->houdu = $houdu;
        $tz->changdu = $changdu;
        $tz->kuandu = $kuandu;
        $tz->counts = $counts;
        $tz->jianshu = $jianshu;
        $tz->lingzhi = $lingzhi;
        $tz->zhijian = $zhijian;
        $tz->zhongliang = $zhongliang;
        $tz->shui_price = $shuiPrice;
        $tz->sumprice = $sumprice;
        $tz->sum_shui_price = $sumShuiPrice;
        $tz->shuie = $shuie;
        $tz->price = $price;
        $tz->huohao = $huohao;
        $tz->pihao = $pihao;
        $tz->huohao = $huohao;
        $tz->beizhu = $beizhu;
        $tz->chehao = $chehao;
        $tz->cache_ywtime = $cacheYwtime;
        $tz->cache_data_number = $cacheDataNumber;
        $tz->cache_data_pnumber = $cacheDataPnumber;
        $tz->cache_customer_id = $cacheCustomerId;
        $tz->cache_create_operator =$cacheCreateOperator ;
        if (empty($mizhong)) {
            $gg = ViewSpecification::get($guigeId);
            $mx->pinming_id = $gg['productname_id'] ?? '';
            $mx->mizhong = $gg['mizhong_name'] ?? '';
        } else {
            $mx->mizhong = $mizhong;
        }
        if (empty($jianzhong)) {
            if (!empty($counts)) {
                if ($counts == 0) {
                    $mx->jianzhong = 0;
                } else {
                    $mx->jianzhong = ((empty($zhongliang) ? 0 : $zhongliang) / ($counts) * (empty($zhijian) ? 0 : $zhijian));
                }
            }
        } else {
            $mx->jianzhong = $jianzhong;
        }
       $calSpot= KcSpot::calSpot($changdu,$kuandu,$jijiafangshiId,$mx->jianzhong,$mx->jianzhong,$counts,$zhijian,$zhongliang,$price,$shuiPrice,$shuie);
        $tz->guobang_zhongliang = $calSpot->guobang_zhongliang;
        $tz->lisuan_zhongliang = $calSpot->lisuan_zhongliang;;
        $tz->cache_data_number = $cacheDataNumber;
        $tz->save();
        return $tz;

    }

    // 验证规则
    public $rules = [

    ];

    // 验证错误信息
    public $msg = [

    ];

    // 场景
    public $scene = [

    ];

    // 表单-数据表字段映射
    public $map = [

    ];
}
