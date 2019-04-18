<?php

namespace app\admin\model;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use traits\model\SoftDelete;

class CgPurchase extends Base
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';

    public function details()
    {
        return $this->hasMany('CgPurchaseMx', 'purchase_id', 'id');
    }

    public function other()
    {
        return $this->hasMany('CapitalFyhx', 'data_id', 'id');
    }

    public function jsfsData()
    {
        return $this->belongsTo('Jiesuanfangshi', 'jiesuan_id', 'id')->cache(true, 60)
            ->field('id,jiesuanfangshi')->bind(['jiesuan_name' => 'jiesuanfangshi']);
    }

    public function custom()
    {
        return $this->belongsTo('Custom', 'customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['custom_name' => 'custom']);
    }

    public function pjlxData()
    {
        return $this->belongsTo('Pjlx', 'piaoju_id', 'id')->cache(true, 60)
            ->field('id,pjlx')->bind(['piaoju_name' => 'pjlx']);
    }

    /**
     * @param $dataId
     * @param $cgCustomerId
     * @param $moshiType
     * @param $cgPjlx
     * @return int|string
     * @throws Exception
     */
    public static function findCgScCountsByMsMxId($dataId, $cgCustomerId, $moshiType, $cgPjlx)
    {
        return self::alias('pu')
            ->join('__SALES_MOSHI_MX__ mx', 'pu.data_id=mx.id', 'LEFT')
            ->join('__SALES_MOSHI__ moshi', 'moshi.id=mx.moshi_id', 'LEFT')
            ->where('moshi.id', $dataId)
            ->where('pu.customer_id', $cgCustomerId)
            ->where('pu.moshi_type', $moshiType)
            ->where('pu.piaoju_id', $cgPjlx)
            ->count();
    }

    public static function findCgIdByMsMxId($dataId, $cgCustomerId, $moshiType, $cgPjlx)
    {
        return self::alias('pu')
            ->join('__SALES_MOSHI_MX__ mx', 'pu.data_id=mx.id', 'LEFT')
            ->join('__SALES_MOSHI__ moshi', 'moshi.id=mx.moshi_id', 'LEFT')
            ->where('moshi.id', $dataId)
            ->where('pu.customer_id', $cgCustomerId)
            ->where('pu.moshi_type', $moshiType)
            ->where('pu.piaoju_id', $cgPjlx)
            ->value('pu.id');
    }

    /**
     * @param $dataId
     * @param $moshiType
     * @param $ywtime
     * @param $gysId
     * @param $jsfs
     * @param $rkfs
     * @param $pjlx
     * @param $beizhu
     * @param $bumen
     * @param $zhiyuan
     * @param $userId
     * @param $companyId
     * @return CgPurchase
     * @throws Exception
     */
    public function insertCaigou($dataId, $moshiType, $ywtime, $gysId, $jsfs, $rkfs, $pjlx, $beizhu, $bumen, $zhiyuan, $userId, $companyId)
    {
        $count = self::withTrashed()->where('companyid', $companyId)->whereTime('create_time', 'today')->count();

        $cg = new self();
        $cg->yw_time = $ywtime;
        $cg->system_number = "CGD" . date('Ymd') . str_pad(++$count, 3, 0, STR_PAD_LEFT);
        $cg->customer_id = $gysId;
        $cg->ruku_fangshi = $rkfs;
        $cg->group_id = $bumen;
        $cg->piaoju_id = $pjlx;
        $cg->jiesuan_id = $jsfs;
        $cg->beizhu = $beizhu;
        $cg->create_operate_id = $userId;
        $cg->sale_operate_id = $zhiyuan;
        $cg->data_id = $dataId;
        $cg->moshi_type = $moshiType;
        $cg->save();
        return $cg;
    }

    /**
     * @param CgPurchase $cg
     * @param $dataId
     * @param $moshiType
     * @param $guigeId
     * @param $storeId
     * @param $caizhiId
     * @param $chandiId
     * @param $pinmingId
     * @param $jijiafangshiId
     * @param $changdu
     * @param $houdu
     * @param $kuandu
     * @param $shuie
     * @param $lingzhi
     * @param $jianshu
     * @param $zhijian
     * @param $counts
     * @param $zhongliang
     * @param $price
     * @param $sumPrice
     * @param $shuiPrice
     * @param $sumShuiPrice
     * @param $fysz
     * @param $pihao
     * @param $huohao
     * @param $beizhu
     * @param $chehao
     * @param $mizhong
     * @param $jianzhong
     * @param $companyId
     * @return CgPurchaseMx
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function insertMx(CgPurchase $cg, $dataId, $moshiType, $guigeId, $storeId, $caizhiId, $chandiId, $pinmingId, $jijiafangshiId, $changdu, $houdu, $kuandu, $shuie, $lingzhi, $jianshu, $zhijian, $counts, $zhongliang, $price, $sumPrice, $shuiPrice, $sumShuiPrice, $fysz, $pihao, $huohao, $beizhu, $chehao, $mizhong, $jianzhong, $companyId)
    {
        $fysz = empty($fysz) ? 0 : $fysz;
        $trumpet = 0;
        if (!empty($cg['id'])) {
            $trumpet = CgPurchaseMx::where('purchasse_id', $cg['id'])->max('trumpet');
        }

        $mx = new CgPurchaseMx();
        $mx->companyid = $companyId;
        $mx->data_id = $dataId;
        $mx->moshi_type = $moshiType;
        $mx->purchase_id = $cg['id'];
        $mx->guige_id = $guigeId;
        $mx->store_id = $storeId;
        $mx->caizhi_id = $caizhiId;
        $mx->chandi_id = $chandiId;
        if (empty($pinmingId)) {
            $gg = ViewSpecification::where('id', $guigeId)->cache(true, 60)->find();
            $mx->pinming_id = $gg['productname_id'] ?? '';
        } else {
            $mx->pinming_id = $pinmingId;
        }
        $mx->changdu = $changdu;
        $mx->houdu = $houdu;
        $mx->kuandu = $kuandu;
        $mx->jijiafangshi_id = $jijiafangshiId;
        $mx->lingzhi = $lingzhi;
        $mx->jianshu = $jianshu;
        $mx->zhijian = $zhijian;
        $mx->counts = $counts;
        $mx->zhongliang = $zhongliang;
        $mx->price = $price;
        $mx->sumprice = $sumPrice;
        $mx->shuie = $shuie;
        $mx->shui_price = $shuiPrice;
        $mx->sum_shui_price = $sumShuiPrice;
        $mx->fy_sz = $fysz;
        $mx->pihao = $pihao;
        $mx->huohao = $huohao;
        $mx->beizhu = $beizhu;
        $mx->chehao = $chehao;
        $mx->mizhong = $mizhong;
        $mx->jianzhong = $jianzhong;
        $mx->trumpet = $trumpet;
        $mx->save();
        return $mx;
    }
}
