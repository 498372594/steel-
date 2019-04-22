<?php

namespace app\admin\model;

use PDOStatement;
use think\{db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    db\Query,
    Exception,
    exception\DbException,
    Model};
use traits\model\SoftDelete;

class Salesorder extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function details()
    {
        return $this->hasMany('SalesorderDetails', 'order_id', 'id');
    }

    public function other()
    {
        return $this->hasMany('CapitalFyhx', 'data_id', 'id')
            ->where('fyhx_type', 1)->field('id,cap_fy_id,data_id');
    }

    public function jsfsData()
    {
        return $this->belongsTo('Jiesuanfangshi', 'jsfs', 'id')->cache(true, 60)
            ->field('id,jiesuanfangshi')->bind(['jisuan_name' => 'jiesuanfangshi']);
    }

    public function custom()
    {
        return $this->belongsTo('Custom', 'custom_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['custom_name' => 'custom']);
    }

    public function pjlxData()
    {
        return $this->belongsTo('Pjlx', 'pjlx', 'id')->cache(true, 60)
            ->field('id,pjlx')->bind(['pjlx_name' => 'pjlx']);
    }

    /**
     * @param $dataId
     * @param $moshiType
     * @param $ywTime
     * @param $customerId
     * @param $piaojuId
     * @param $jiesuanId
     * @param $beizhu
     * @param $groupId
     * @param $saleOperatorId
     * @param $lxr
     * @param $telephone
     * @param $chehao
     * @param $userId
     * @param $companyId
     * @return Salesorder
     * @throws Exception
     */
    public function insertSale($dataId, $moshiType, $ywTime, $customerId, $piaojuId, $jiesuanId, $beizhu, $groupId, $saleOperatorId, $lxr, $telephone, $chehao, $userId, $companyId)
    {
        $count = self::withTrashed()
            ->whereTime('create_time', 'today')
            ->where('companyid', $companyId)
            ->count();

        $xs = new self();
        $xs->companyid = $companyId;
        $xs->data_id = $dataId;
        $xs->ywlx = $moshiType;
        $xs->custom_id = $customerId;
        $xs->pjlx = $piaojuId;
        $xs->jsfs = $jiesuanId;
        $xs->remark = $beizhu;
        $xs->ywsj = $ywTime;
        $xs->department = $groupId;
        $xs->employer = $saleOperatorId;
        $xs->system_no = "XSD" . date('Ymd') . str_pad(++$count, 3, '0', STR_PAD_LEFT);
        $xs->add_id = $userId;
        $xs->car_no = $chehao;
        $xs->ckfs = 1;

        $xs->contact = $lxr;
        $xs->mobile = $telephone;
        $xs->save();
        return $xs;
    }

    /**
     * @param $dataId
     * @param $moshiType
     * @param $ywTime
     * @param $chehao
     * @param $customerId
     * @param $piaojuId
     * @param $jiesuanId
     * @param $groupId
     * @param $saleOperatorId
     * @param $lxr
     * @param $telephone
     * @return array|false|PDOStatement|string|Model
     * @throws Exception
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function updateSale($dataId, $moshiType, $ywTime, $chehao, $customerId, $piaojuId, $jiesuanId, $groupId, $saleOperatorId, $lxr, $telephone)
    {
        $xs = self::where('data_id', $dataId)->where('ywlx', $moshiType)->find();
        if (empty($xs)) {
            throw new Exception("对象不存在");
        }
        $xs->yw_time = $ywTime;
        $xs->custom_id = $customerId;
        $xs->jsfs = $jiesuanId;
        $xs->department = $groupId;
        $xs->employer = $saleOperatorId;
        $xs->pjlx = $piaojuId;
        $xs->car_no = $chehao;
        $xs->contact = $lxr;
        $xs->mobile = $telephone;
        $xs->save();
        return $xs;
    }

    /**
     * @param Salesorder $sale
     * @param $dataId
     * @param $moshiType
     * @param $guigeId
     * @param $caizhiId
     * @param $chandId
     * @param $storeId
     * @param $jijiafangshiId
     * @param $houdu
     * @param $kuandu
     * @param $changdu
     * @param $lingzhi
     * @param $jianshu
     * @param $zhijian
     * @param $counts
     * @param $zhongliang
     * @param $price
     * @param $sumPrice
     * @param $shuiPrice
     * @param $sumShuiPrice
     * @param $pihao
     * @param $beizhu
     * @param $chehao
     * @param $shuie
     * @param $companyId
     * @return SalesorderDetails
     */
    public function insertMx(Salesorder $sale, $dataId, $moshiType, $guigeId, $caizhiId, $chandId, $storeId, $jijiafangshiId,
                             $houdu, $kuandu, $changdu, $lingzhi, $jianshu, $zhijian, $counts, $zhongliang, $price, $sumPrice,
                             $shuiPrice, $sumShuiPrice, $pihao, $beizhu, $chehao, $shuie, $companyId)
    {
        $trumpet = SalesorderDetails::where('order_id', $sale['id'])->max('trumpet');

        $mx = new SalesorderDetails();
        $mx->companyid = $companyId;
        $mx->data_id = $dataId;
        $mx->moshi_type = $moshiType;
        $mx->order_id = $sale['id'];
        $mx->wuzi_id = $guigeId;
        $mx->caizhi = $caizhiId;
        $mx->chandi = $chandId;
        $mx->length = $changdu;
        $mx->houdu = $houdu;
        $mx->width = $kuandu;
        $mx->jsfs_id = $jijiafangshiId;
        $mx->lingzhi = $lingzhi;
        $mx->num = $jianshu;
        $mx->jzs = $zhijian;
        $mx->count = $counts;
        $mx->weight = $zhongliang;
        $mx->price = $price;
        $mx->batch_no = $pihao;
        $mx->remark = $beizhu;
        $mx->car_no = $chehao;
        $mx->total_fee = $sumPrice;
        $mx->tax_rate = $shuiPrice;

        $mx->price_and_tax = $sumShuiPrice;
        $mx->storage_id = $storeId;
        $mx->trumpet = $trumpet;
        $mx->tax = $shuie;
        $mx->save();
        return $mx;
    }

    public function deleteSale($dataId, $moshiType)
    {
        $xs = self::where('data_id', $dataId)->where('ywlx', $moshiType)->find();
        if (empty($xs)) {
            throw new Exception("对象不存在");
        }
        SalesorderDetails::destroy(function (Query $query) use ($xs) {
            $query->where('order_id', $xs['id']);
        });
        $xs->delete();
        return $xs;
    }
}