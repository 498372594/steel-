<?php

namespace app\admin\model;

use Exception;
use think\db\exception\{DataNotFoundException, ModelNotFoundException};
use think\exception\DbException;
use traits\model\SoftDelete;

class KcRk extends Base
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';

    public function details()
    {
        return $this->hasMany('KcRkMx', 'kc_rk_id', 'id');
    }

    public function custom()
    {
        return $this->belongsTo('Custom', 'customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['custom_name' => 'custom']);
    }

    /**
     * 添加入库单
     * @param $dataId
     * @param $rukuType
     * @param $ywTime
     * @param $groupId
     * @param $cacheDataPnumber
     * @param $saleOperatorId
     * @param $userId
     * @param $companyId
     * @return KcRk
     * @throws \think\Exception
     * @throws Exception
     */
    public function insertRuku($dataId, $rukuType, $ywTime, $groupId, $cacheDataPnumber, $saleOperatorId, $userId, $companyId)
    {
        $rk = new self();
        if (empty($rukuType)) {
            throw new Exception("请传入出库类型[chukuType]");
        }
        switch ($rukuType) {
            case 1:
                $rk->remark = "库存调拨单," . $cacheDataPnumber;
                break;
            case 2:
                $rk->remark = "盘盈入库," . $cacheDataPnumber;
                break;
            case 3:
                $rk->remark = "其它出库单," . $cacheDataPnumber;

                break;
            case 4:
                $rk->remark = "采购单," . $cacheDataPnumber;

                break;
            case 7:
                $rk->remark = "销售退货单," . $cacheDataPnumber;

                break;
            case 8:
                $rk->remark = "库存期初余额," . $cacheDataPnumber;

                break;
            case 9:
                $rk->remark = "卷板开平加工," . $cacheDataPnumber;

                break;
            case 10:
                $rk->remark = "卷板纵剪加工," . $cacheDataPnumber;

                break;
            case 13:
                $rk->remark = "卷板切割加工," . $cacheDataPnumber;

                break;
            case 15:
                $rk->remark = "通用加工," . $cacheDataPnumber;
                break;
            default:
                throw new Exception("请传入匹配的出库类型[chukuType]");
        }
        $rk->create_operator_id = $userId;
        $rk->data_id = $dataId;
        $rk->ruku_type = $rukuType;
        $rk->ruku_fangshi = 1;
        $rk->group_id = $groupId;
        $rk->sale_operator_id = $saleOperatorId;
        $count = self::withTrashed()->where('companyid', $companyId)->whereTime('create_time', 'today')->count();
        $rk->system_number = 'RKD' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
        $rk->ywTime = $ywTime;
        $rk->status = 0;
        $rk->save();
        return $rk;
    }

    /**
     * @param $dataId
     * @param $rukuType
     * @param $storeId
     * @param $ywTime
     * @param $cgCustomerId
     * @param $groupId
     * @param $saleOperatorId
     * @return KcRk|false|int|null
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function updateRuku($dataId, $rukuType, $storeId, $ywTime, $cgCustomerId, $groupId, $saleOperatorId)
    {
        $rk = self::where(array("data_id" => $dataId, "ruku_type" => $rukuType))->get(1);
        if (empty($list)) {
            throw new Exception("对象不存在");
        }
        $rk->yw_time = $ywTime;
        $rk->groupId = $groupId;
        $rk->setSaleOperatorId = $saleOperatorId;
        $mxList = KcRkMx::where("kc_rk_id", $rk["id"])->select();
        foreach ($mxList as $mx) {
            if (!empty($storeId)) {
                KcRkMx::where("id", $mx["id"])->save(array("store_id" => $storeId));
            }
            if (!empty($cgCustomerId)) {
                KcRkMx::where("id", $mx["id"])->save(array("cache_customer" => $cgCustomerId));
            }
        }

        $rk = $rk->save();
        return $rk;

    }

    /**
     * @param $rk
     * @param $dataId
     * @param $rukuType
     * @param $ywTime
     * @param $dataPnumber
     * @param $dataNumber
     * @param $customerId
     * @param $pinmingId
     * @param $guigeId
     * @param $caizhiId
     * @param $chandiId
     * @param $jijiafangshiId
     * @param $storeId
     * @param $pihao
     * @param $huohao
     * @param $chehao
     * @param $beizhu
     * @param $pjlx
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
     * @param $userId
     * @param $companyId
     * @return KcSpot
     * @throws DbException
     * @throws Exception
     */
    public function insertRkMxMd($rk, $dataId, $rukuType, $ywTime, $dataPnumber, $dataNumber, $customerId, $pinmingId, $guigeId, $caizhiId, $chandiId, $jijiafangshiId, $storeId, $pihao, $huohao, $chehao
        , $beizhu, $pjlx, $houdu, $kuandu, $changdu, $zhijian, $lingzhi, $jianshu, $counts, $zhongliang, $price, $sumPrice, $shuiPrice, $sumShuiPrice, $shuie, $mizhong, $jianzhong, $userId, $companyId)
    {
        $mx = new KcRkMx();
        if (empty($dataId)) {
            throw new Exception("明细list缺少dataId");
        }
        if (empty($jijiafangshiId)) {
            throw new Exception("计算方式不能为空");
        }
//        if (empty($counts)) {
//            this . log . debug("数量为空");
//        }
//        if (empty($zhongliang)) {
//            this . log . debug("重量为空");
//        }
//        if (empty($price)) {
//            this . log . debug("价格为空");
//        }

        if (empty($pinmingId)) {
            $gg = ViewSpecification::where('id', $guigeId)->cache(true, 60)->find();
            $pinmingId = $gg['productname_id'] ?? '';
        }

        $addNumberCount = empty($rk['id']) ? 1 : CgPurchaseMx::where('kc_rk_id', $rk['id'])->max('system_number');
        $mx->companyid = $companyId;
        $mx->kc_rk_id = $rk["id"];
        $mx->ruku_type = $rukuType;
        $mx->ruku_fangshi = 1;
        $mx->cache_ywtime = $ywTime;
        $mx->cache_data_pnumber = $dataPnumber;
        $mx->cache_data_number = $dataNumber;
        $mx->cache_customer = $customerId;
        $mx->data_id = $dataId;
        $mx->pinming_id = $pinmingId;
        $mx->guige_id = $guigeId;
        $mx->caizhi_id = $caizhiId;
        $mx->chandi_id = $chandiId;
        $mx->jijiafangshi_id = $jijiafangshiId;
        $mx->store_id = $storeId;
        $mx->cache_create_operator = $userId;
        $mx->houdu = $houdu;
        $mx->kuandu = $kuandu;
        $mx->changdu = $changdu;
        $mx->zhijian = $zhijian;
        $mx->lingzhi = $lingzhi;
        $mx->jianshu = $jianshu;
        $mx->counts = $counts;
        $mx->zhongliang = $zhongliang;
        $mx->ruku_lingzhi = $lingzhi;
        $mx->ruku_jianshu = $jianshu;
        $mx->ruku_shuliang = $counts;
        $mx->ruku_zhongliang = $zhongliang;
        $mx->price = $price;
        $mx->sumprice = $sumPrice;
        $mx->shui_price = $shuiPrice;
        $mx->sum_shui_price = $sumShuiPrice;
        $mx->shuie = $shuie;
        $mx->pihao = $pihao;
        $mx->huohao = $huohao;
        $mx->beizhu = $beizhu;
        $mx->chehao = $chehao;
        $mx->system_number = $addNumberCount + 1;
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
        $mx->save();

        $md = new KcRkMd();
        $md->companyid = $companyId;
        $md->kc_rk_id = $rk["id"];
        $md->ruku_mx_id = $mx["id"];
        $md->kc_rk_tz_id = null;
        $md->data_id = $mx["data_id"];
        $md->ruku_type = $mx["ruku_type"];
        $md->ruku_fangshi = $mx["ruku_fangshi"];
        $md->pinming_id = $mx["pinming_id"];
        $md->caizhi_id = $mx["caizhi_id"];
        $md->chandi_id = $mx["chandi_id"];
        $md->jijiafangshi_id = $mx["jijiafangshi_id"];
        $md->guige_id = $mx["guige_id"];
        $md->houdu = $mx["houdu"];
        $md->kuandu = $mx["kuandu"];
        $md->jianshu = $mx["jianshu"];
        $md->counts = $mx["counts"];
        $md->changdu = $mx["changdu"];
        $md->lingzhi = $mx["lingzhi"];
        $md->zhijian = $mx["zhijian"];
        $md->zhongliang = $mx["zhongliang"];
        $md->price = $mx["price"];
        $md->shuiprice = $mx["price"];
        $md->sumprice = $mx["sumprice"];
        $md->sum_shui_price = $mx["sum_shui_price"];
        $md->shuie = $mx["shuie"];
        $md->huohao = $huohao;
        $md->cb_price = $price;
        $md->chehao = $chehao;
        $md->chehao = $chehao;
        $md->cb_shuie = $shuie;
        $md->pihao = $pihao;
        $md->beizhu = $beizhu;
        $md->store_id = $mx["store_id"];
        $md->mizhong = $mx["mizhong"];
        $md->jianzhong = $mx["jianzhong"];
        if (empty($cbPrice)) {
            $cbPrice = $mx['price'];
        }
        $md->cb_price = $cbPrice;
        $jjfs = Jsfs::get($mx['jijiafangshi_id']);
        if ($jjfs['jj_type'] == 1 || $jjfs['jj_type'] == 2) {
            $md->sum_shui_price = $md->price * $md->zhongliang;
            $md->cb_sum_shuiprice = $md->cb_price * $md->zhongliang;
        } else if ($jjfs['jj_type'] == 3) {
            $md->sum_shui_price = $md->price * $md->counts;
            $md->cb_sum_shuiprice = $md->cb_price * $md->counts;
        }
        $md->save();
        return (new KcSpot())->insertSpot(1, $rukuType, $md->jijiafangshi_id, $md->id, $md->data_id, $md->pinming_id, $md->guige_id, $md->caizhi_id, $md->chandi_id, $md->store_id
            , $mx->cache_customer, $pjlx, $md->chehao, $md->beizhu, $md->huohao, $md->pihao, $md->changdu, $md->houdu, $md->kuandu, $md->lingzhi, $md->jianshu, $md->zhijian, $md->counts, $md->zhongliang, $md->price, $md->sumprice,
            $md->shuiprice, $md->sum_shui_price, $md->shuie, $md->mizhong, $md->jianzhong, $md->cb_price, $md->cb_shuie, $md->sumprice, $md->sum_shui_price, $companyId);


    }
}
