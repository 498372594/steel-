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
    public function insertCkMxMd(StockOut $ck, $spotId, $dataId, $chukuType, $ywTime, $dataPnumber, $customerId, $guigeId, $caizhiId, $chandiId, $jijiafangshiId, $storeId, $houdu, $kuandu, $changdu, $zhijian, $lingzhi, $jianshu, $counts, $zhongliang, $price, $sumPrice, $shuiPrice, $sumShuiPrice, $shuie, $mizhong, $jianzhong, $cbPrice, $beizhu, $userId, $companyId)
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
        $mx->shui_price = $shuiPrice;
        $mx->sum_shui_price = $sumShuiPrice;
        $mx->shuie = $shuie;

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

        $addNumberCount = empty($ck['id']) ? 1 : StockOutDetail::where('stock_out_id', $ck['id'])->max('system_number');
        $mx->systemNumber = $addNumberCount;
        $mx->save();
        $spot = KcSpot::get($spotId);
        if (empty($spot) || empty($spotId)) {
            throw new Exception("引用的采购单还未入库，请入库后再操作！");
        }

        $md = new StockOutMd();
        $md->stock_out_id = $ck['id'];
        $md->stock_out_detail_id = $mx['id'];
        $md->kc_spot_id = $spotId;
        $md->data_id = $mx->data_d;
        $md->chuku_type = $mx->chuku_type;
        $md->out_mode = 1;
        $md->pinming_id = $mx->pinming_id;
        $md->caizhi = $mx->caizhi;
        $md->chandi = $mx->chandi;
        $md->jijiafangshi_id = $mx->jijiafangshi_id;
        $md->guige_id = $mx->guige_id;
        $md->houdu = $mx->houdu;
        $md->kuandu = $mx->kuandu;
        $md->counts = $mx->counts;
        $md->jianshu = $mx->jianshu;
        $md->changdu = $mx->changdu;
        $md->lingzhi = $mx->lingzhi;
        $md->zhijian = $mx->zhijian;
        $md->zhongliang = $mx->zhongliang;
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
        $md->mizhong = $mx->mizhong;
        $md->jianzhong = $mx->jianzhong;
        $md->save();
        (new KcSpot())->adjustSpotById($spotId, false, $md['counts'], $md['zhongliang'], $md['jijiafangshi_id'], $shuie);
    }
}