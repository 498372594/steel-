<?php

namespace app\admin\model;

use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use traits\model\SoftDelete;

class KcSpot extends Base
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';

    /**
     * @param $mdid
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public static function deleteSpotByRkMd($mdid)
    {
        $ss = self::where('rk_md_id', $mdid)->find();
        if (empty($ss)) {
            throw new Exception("库存未找到");
        }

        self::ifCkMd($ss['id']);

        $ss->delete();
    }

    /**
     * @param $id
     * @throws \think\Exception
     * @throws Exception
     */
    private static function ifCkMd($id)
    {
        $c = StockOutMd::alias('md')
            ->join('__STOCK_OUT__ ck', 'ck.id=md.stock_out_id')
            ->where('md.kc_spot_id', $id)
            ->where('ck.status', '<>', 2)
            ->count();

        if ($c > 0) {
            throw new Exception("已有发货记录,操作终止");
        }
    }

    public function specification()
    {
        return $this->belongsTo('ViewSpecification', 'guige_id', 'id')->cache(true, 60)
            ->field('id,specification,mizhong_name,productname');
    }

    public function guigeData()
    {
        return $this->belongsTo('ViewSpecification', 'guige_id', 'id')->cache(true, 60)
            ->field('id,specification')->bind(['guige' => 'specification']);
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

    public function getRealcountsAttr($value, $data)
    {
        $count = model("KcYlsh")->where("spot_id", $data['id'])->sum("counts");
        $count = $data["counts"] - $count;
        return $count;
    }

    public function getReallingzhiAttr($value, $data)
    {
        $count = model("KcYlsh")->where("spot_id", $data['id'])->sum("counts");
        $count = $data["counts"] - $count;
        $lingzhi = $count / $data["zhijian"];
        return $lingzhi;
    }

    public function getRealjianshuAttr($value, $data)
    {
        $count = model("KcYlsh")->where("spot_id", $data['id'])->sum("counts");
        $count = $data["counts"] - $count;
        $jianshu = intval(floor($count / $data["zhijian"]));
        return $jianshu;
    }

    public function getRealzhongliangAttr($value, $data)
    {
        $zhongliang = model("KcYlsh")->where("spot_id", $data['id'])->sum("zhongliang");
        $zhongliang = $data["zhongliang"] - $zhongliang;
        return $zhongliang;
    }

    /**
     * @param $spotId
     * @param $isJia
     * @param $counts
     * @param $zhongliang
     * @param $jijiafangshiId
     * @param $shuie
     * @throws DbException
     * @throws Exception
     */
    public function adjustSpotById($spotId, $isJia, $counts, $zhongliang, $jijiafangshiId, $shuie)
    {
        $counts *= 1;
        $zhongliang *= 1;
        if (empty($counts) && empty($zhongliang)) {
            throw new Exception("请传入数量,重量等");
        }

        $counts = empty($counts) ? 0 : $counts;
        $zhongliang = empty($zhongliang) ? 0 : $zhongliang;

        $spot = self::get($spotId);

        if (empty($spot)) {
            throw new Exception("无spot");
        }
//        $ggObj = ViewSpecification::get($spot['guige_id']);
//        $ggObj=Productname::where('id',$spot['guige_id'])->select();
//        $p=
//        TbBasePinming p = (TbBasePinming) this . pmDao . selectByPrimaryKey(ggObj . getPinmingId());

        if (empty($jijiafangshiId)) {
            $jijiafangshiId = $spot['jijiaffangshi_id'];
        }
//        $jjfsObj = Jsfs::get($jijiafangshiId);
//        dump($jjfsObj);
        $calSpot = self::calSpot($spot['changdu'], $spot['kuandu'],$jijiafangshiId, $spot['mizhong'], $spot['jianzhong'],
            $spot['counts'] + ($isJia ? $counts : -$counts), $spot['zhijian'],
            ($isJia ? $zhongliang : -$zhongliang) + ($spot['zhongliang']), $spot['price'], $spot['shui_price'], $shuie);

        if ($counts != 0) {

            $spot->lingzhi = $calSpot->lingzhi;
            $spot->jianshu = $calSpot->jianshu;
            $spot->counts = $calSpot->counts;
            if ($spot->counts < 0) {
                throw new Exception("不允许出现负库存!");
            }
        }

        if ($zhongliang != 0) {
            $spot->zhongliang = $calSpot->zhongliang;
            $spot->guobang_zhongliang = $calSpot->guobang_zhongliang;
            $spot->lisuan_zhongliang = $calSpot->lisuan_zhongliang;
            if ($spot['zhongliang'] < 0) {
                throw new Exception("不允许出现负库存!");
            }
        }

        $spot->sumprice = $calSpot->sumprice;
        $spot->sum_shui_price = $calSpot->sum_shui_price;
        $spot->shuie = $calSpot->shuie;
        $spot->save();
    }

    /**
     * @param $changdu
     * @param $kuandu
     * @param $baseJijialeixingId
     * @param $mizhong
     * @param $jianzhong
     * @param $counts
     * @param $zhijian
     * @param $zhongliang
     * @param $price
     * @param $shuilv
     * @param $shuie
     * @return KcSpot
     * @throws Exception
     */
    public static function calSpot($changdu, $kuandu, $baseJijialeixingId, $mizhong, $jianzhong, $counts, $zhijian, $zhongliang, $price, $shuilv, $shuie)
    {
//        if (empty($p)) {
//            throw new Exception("请传入品名对象");
//        }
        if (empty($baseJijialeixingId)) {
            throw new Exception("请传入计价方式中的计价类型");
        }
        if (is_null($counts)) {
            throw new Exception("请传入数量");
        }
        if (is_null($zhongliang)) {
            throw new Exception("请传入重量");
        }
        if (is_null($price)) {
            throw new Exception("请传入单价");
        }
        if (empty($mizhong)) {
            $mizhong = 0;
        }
        if (empty($jianzhong)) {
            $jianzhong = 0;
        }
        if (empty($shuilv)) {
            $shuilv = 0;
        }
        if (empty($changdu)) {
            $changdu = 0;
        }
        if (empty($kuandu)) {
            $kuandu = 0;
        }
        if (empty($zhijian)) {
            $zhijian = 0;
        }
        $s = new self();

        if (empty($zhijian * 1)) {
            $s->lingzhi = $counts;
            $s->jianshu = 0;
        } else {
            $bresult = floor($counts / $zhijian);
            $s->lingzhi = $counts - $bresult * $zhijian;
            $s->jianshu = $bresult;
        }

        $lisuanZhongliang = 0;

        $s->shuie = $shuie;
        $s->zhongliang = $zhongliang;
        if ($baseJijialeixingId == 3) {
            $s->sum_shui_price = $price * $counts;
            $s->sumprice = $price * $counts - $shuie;
        } elseif ($baseJijialeixingId == 1) {
            $s->sum_shui_price = $price * $lisuanZhongliang;
            $s->sumprice = $price * $lisuanZhongliang - $shuie;
        } else {
            $s->sum_shui_price = $price * $zhongliang;
            $s->sumprice = $price * $zhongliang - $shuie;
        }
        $s->price = $price;
        $s->shui_price = $shuilv;

        $s->lisuan_zhongliang = $lisuanZhongliang;
        $s->old_lisuan_zhongliang = $lisuanZhongliang;
        if ($s->lisuan_zhongliang == 0) {
            $s->lisuan_price = 0;
            $s->lisuan_shui_price = 0;
        } else {
            $s->lisuan_price = $s->sumprice / $lisuanZhongliang;
            $s->lisuan_shui_price = $s->sum_shui_price / $lisuanZhongliang;
        }

        if ($counts != 0) {
            $s->lisuan_zhizhong = $lisuanZhongliang / $counts;
            $s->zhi_price = $s->sumprice / $counts;
            $s->zhi_shui_price = $s->sum_shui_price / $counts;
        } else {
            $s->lisuan_zhizhong = 0;
            $s->zhi_price = 0;
            $s->zhi_shui_price = 0;
        }
        $s->lisuan_jianzhong = $s->lisuan_zhizhong * $zhijian;
        if ($baseJijialeixingId == 2) {
            if ($counts != 0) {
                $s->guobang_zhizhong = $zhongliang / $counts;
            } else {
                $s->guobang_zhizhong = 0;
            }
            $s->guobang_zhongliang = $zhongliang;
            $s->guobang_jianzhong = $s->guobang_zhizhong * $zhijian;
            $s->guobang_price = round($s->price / (1 + $shuilv / 100), 4);
            $s->guobang_shui_price = $s->price;
            $s->old_guobang_zhongliang = $zhongliang;
        } else {
            $s->guobang_zhizhong = $s->lisuan_zhizhong;
            $s->guobang_zhongliang = $s->lisuan_zhongliang;
            $s->guobang_jianzhong = $s->lisuan_jianzhong;
            $s->guobang_price = round($s->price / (1 + $shuilv / 100), 4);
            $s->guobang_price = $s->lisuan_price;
            $s->guobang_shui_price = $s->lisuan_shui_price;
            $s->old_guobang_zhongliang = $s->lisuan_zhongliang;
        }
        $s->counts = $counts;
        $s->old_guobang_jianzhong = $s->guobang_jianzhong;
        $s->old_lisuan_jianzhong = $s->lisuan_jianzhong;
        $s->old_guobang_zhizhong = $s->guobang_zhizhong;
        $s->old_lisuan_zhizhong = $s->lisuan_zhizhong;

        return $s;
    }

    /**
     * 插入资源单
     * @param $rukuFangshi
     * @param $rukuType
     * @param $jijiafangshiId
     * @param $rkMdId
     * @param $dataId
     * @param $pinmingId
     * @param $guigeId
     * @param $caizhiId
     * @param $chandiId
     * @param $storeId
     * @param $customerId
     * @param $piaojuId
     * @param $chehao
     * @param $beizhu
     * @param $huohao
     * @param $pihao
     * @param $changdu
     * @param $houdu
     * @param $kuandu
     * @param $lingzhi
     * @param $jianshu
     * @param $zhijian
     * @param $counts
     * @param $zhongliang
     * @param $price
     * @param $sumprice
     * @param $shuiprice
     * @param $sumShuiPrice
     * @param $shuie
     * @param $mizhong
     * @param $jianzhong
     * @param $cbPrice
     * @param $cbShuie
     * @param $cbSumPrice
     * @param $cbSumShuiPrice
     * @param $companyId
     * @return KcSpot
     * @throws DbException
     * @throws \think\Exception
     * @throws Exception
     */
    public function insertSpot($rukuFangshi, $rukuType, $jijiafangshiId, $rkMdId, $dataId, $pinmingId, $guigeId, $caizhiId, $chandiId, $storeId, $customerId, $piaojuId, $chehao, $beizhu, $huohao, $pihao, $changdu
        , $houdu, $kuandu, $lingzhi, $jianshu, $zhijian, $counts, $zhongliang, $price, $sumprice, $shuiprice, $sumShuiPrice, $shuie, $mizhong, $jianzhong, $cbPrice, $cbShuie, $cbSumPrice, $cbSumShuiPrice, $companyId)
    {
        $spot = new self();
        if (empty($jijiafangshiId)) {
            throw new Exception("计算方式必输入");
        }
        if (empty($guigeId)) {
            throw new Exception("规格必输入");
        }
        $spot->companyid = $companyId;
        $spot->rk_md_id = $rkMdId;
        $spot->ruku_fangshi = $rukuFangshi;
        $spot->ruku_type = $rukuType;
        $spot->changdu = $changdu;
        $spot->houdu = $houdu;
        $spot->kuandu = $kuandu;
        $spot->lingzhi = $lingzhi;
        $spot->jianshu = $jianshu;
        $spot->zhijian = $zhijian;
        $spot->counts = $counts;
        $spot->price = $price;
        $spot->sumprice = $sumprice;
        $spot->zhongliang = $zhongliang;
        $spot->shui_price = $shuiprice;
        $spot->sum_shui_price = $sumShuiPrice;
        $spot->shuie = $shuie;
        $spot->cb_shuie = $cbShuie;
        $spot->cb_price = $cbPrice;
        $spot->cb_sumprice = $cbSumPrice;
        $spot->cb_sum_shuiprice = $cbSumShuiPrice;
        $spot->jianzhong = $jianzhong;
        if (empty($mizhong)) {
            $gg = ViewSpecification::get($guigeId);
            $spot->pinming_id = $gg['productname_id'] ?? '';
            $spot->mizhong = $gg['mizhong_name'] ?? '';
        } else {
            $spot->mizhong = $mizhong;
        }

        $calSpot = self::calSpot($changdu, $kuandu, $jijiafangshiId, $spot->mizhong, $jianzhong, $counts, $zhijian, $zhongliang, $price, $shuiprice, $shuie);
        $spot->lisuan_zhongliang = $calSpot->lisuan_zhongliang;
        $spot->lisuan_price = $calSpot->lisuan_price;
        $spot->lisuan_shui_price = $calSpot->lisuan_shui_price;
        $spot->lisuan_zhizhong = $calSpot->lisuan_zhizhong;
        $spot->zhi_price = $calSpot->zhi_price;
        $spot->zhi_shui_price = $calSpot->zhi_shui_price;
        $spot->lisuan_jianzhong = $calSpot->lisuan_jianzhong;
        $spot->guobang_zhizhong = $calSpot->guobang_zhizhong;
        $spot->guobang_zhongliang = $calSpot->guobang_zhongliang;
        $spot->guobang_jianzhong = $calSpot->guobang_jianzhong;
        $spot->guobang_price = $calSpot->guobang_price;
        $spot->guobang_shui_price = $calSpot->guobang_shui_price;
        $spot->old_guobang_zhongliang = $calSpot->old_guobang_zhongliang;
        $count = self::withTrashed()->where('companyid', $companyId)->whereTime('create_time', 'today')->count();
        $spot->resource_number = 'kc' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
        $spot->data_id = $dataId;
        $spot->pinming_id = $pinmingId;
        $spot->caizhi_id = $caizhiId;
        $spot->chandi_id = $chandiId;
        $spot->guige_id = $guigeId;
        $spot->store_id = $storeId;
        $spot->customer_id = $customerId;
        $spot->jijiafangshi_id = $jijiafangshiId;
        $spot->piaoju_id = $piaojuId;
        $spot->beizhu = $beizhu;
        $spot->huohao = $huohao;
        $spot->pihao = $pihao;
        $spot->chehao = $chehao;
        $spot->status = 1;
        $spot->old_guobang_jianzhong = $calSpot->old_guobang_jianzhong;
        $spot->old_lisuan_jianzhong = $calSpot->old_lisuan_jianzhong;
        $spot->old_guobang_zhizhong = $calSpot->old_guobang_zhizhong;
        $spot->old_lisuan_zhizhong = $calSpot->old_lisuan_zhizhong;
        $spot->chehao = $chehao;
        $spot->save();

        return $spot;
    }
}
