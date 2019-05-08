<?php


namespace app\admin\model;


use Exception;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\db\Query;
use think\exception\DbException;
use think\Paginator;
use traits\model\SoftDelete;

class StockOut extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;
    protected $allowOperator = [
        '>',
        '<',
        '>=',
        '<=',
        '=',
        '!='
    ];

    /**
     * @param $dataId
     * @param $chukuType
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public static function cancelChuku($dataId, $chukuType)
    {
        if (empty($dataId)) {
            throw new Exception("请传入dataId");
        }
        if ("1" != $chukuType && "2" != $chukuType && "3" != $chukuType && "4" != $chukuType && "9" != $chukuType && "10" != $chukuType && "11" != $chukuType && "12" != $chukuType && "14" != $chukuType && "16" != $chukuType) {
            throw new Exception("请传入匹配的出库类型[chukuType]");
        }

        $ck = self::where('data_id', $dataId)->where('chuku_type', $chukuType)->find();
        if (empty($ck)) {
            throw new Exception("对象不存在");
        }

        $ck->status = 2;
        $ck->save();
        $ckmd = StockOutMd::where('stock_out_id', $ck['id'])->select();
        foreach ($ckmd as $md) {
            $spot = KcSpot::get($md['kc_spot_id']);
            $rkMd = KcRkMd::get($spot['rk_md_id']);

            if ($md['out_mode'] == 2 && ($rkMd['counts'] != $spot['counts'] || $rkMd['zhongliang'] != $spot['zhongliang'])) {
                throw new Exception("已经有出库信息!");
            }
            (new KcSpot())->adjustSpotById($md['kc_spot_id'], true, $md['counts'], $md['zhongliang'], $md['jijiafangsshi_id'], $md['tax']);
        }
    }

    public function addData()
    {
        return $this->belongsTo('Admin', 'create_operator_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['create_operator_name' => 'name']);
    }

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
        $count = self::withTrashed()
            ->where('companyid', $companyId)
            ->whereTime('create_time', 'today')
            ->count();
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

    /**
     * @param $dataId
     * @param $chukuType
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function deleteChuku($dataId, $chukuType)
    {
        if (empty($dataId)) {
            throw new Exception("请传入dataId");
        }
        if ($chukuType != 1 && $chukuType != 2 && $chukuType != 3 && $chukuType != 4 && $chukuType != 9 && $chukuType != 10 && $chukuType != 11 && $chukuType != 12 && $chukuType != 14 && $chukuType != 16) {
            throw new Exception("请传入匹配的出库类型[chukuType]");
        }

        $ck = StockOut::where('data_id', $dataId)->where('chuku_type', $chukuType)->find();
        if (empty($ck)) {
            throw new Exception("对象不存在");
        }

        $ckmd = StockOutMd::where('stock_out_id', $ck['id'])->select();
        foreach ($ckmd as $md) {
            $spot = KcSpot::get($md['kc_spot_id']);
            $rkMd = KcRkMd::get($spot['rk_md_id']);
            if ($md['out_mode'] == 2 && ($rkMd['counts'] != $spot['counts'] || $rkMd['zhongliang'] != $spot['zhongliang'])) {
                throw new Exception("已经有出库信息!");
            }

            (new KcSpot())->adjustSpotById($md['kc_spot_id'], true, $md['counts'], $md['zhongliang'], $md['jijiafangsshi_id'], $md['tax']);
        }

        StockOutMd::destroy(function (Query $query) use ($ck) {
            $query->where('stock_out_id', $ck['id']);
        });

        StockOutDetail::destroy(function (Query $query) use ($ck) {
            $query->where('stock_out_id', $ck['id']);
        });

        $ck->delete();
    }

    /**
     * 发货情况表
     * @param $params
     * @param $pageLimit
     * @param $companyId
     * @return Paginator
     * @throws DbException
     * @throws Exception
     */
    public function fahuoqingkuang($params, $pageLimit, $companyId)
    {
        $sqlParams = [];
        $sql = '(SELECT t.id,
       t.zbid,
       t.ywTime,
       systemNumber,
       t.status,
       t.piaojuId,
       t.piaojuName,
       t.customerId,
       t.customerName,
       t.cangkuId,
       t.cangkuName,
       t.pinmingId,
       t.pinmingName,
       t.guigeId,
       t.guigeName,
       t.sfguigeId,
       t.sfguigeName,
       t.groupId,
       t.caozuoyuanId,
       t.caozuoyuan,
       t.zhidanrenId,
       t.zhidanren,
       t.houdu,
       t.kuandu,
       t.changdu,
       t.jijiafangshiId,
       t.jijiafangshiname,
       t.pici,
       t.lingzhi,
       t.jianshu,
       t.counts,
       t.zhongliang,
       t.sflingzhi,
       t.sfjianshu,
       t.sfcounts,
       t.sfzhongliang,
       t.isFlag
    FROM
       (SELECT xsmx.`id`                                     id,
               sale.`id`                                     zbid,
               sale.ywsj                                ywTime,
               sale.system_no                          systemNumber,
               sale.`status`                                 STATUS,
               pjlx.`id`                                     piaojuId,
               pjlx.pjlx                                   piaojuName,
               cus.`id`                                      customerId,
               cus.custom                                    customerName,
               st.`id`                                       cangkuId,
               st.storage                                     cangkuName,
               guige.productname_id                                       pinmingId,
               guige.productname                          pinmingName,
               guige.id                                      guigeId,
               guige.specification                                 guigeName,
               sfguige.id                                    sfguigeId,
               GROUP_CONCAT(sfguige.specification) sfguigeName,
               sale.department                                    groupId,
               oper.`id`                                     caozuoyuanId,
               oper.name                             caozuoyuan,
               sys.`id`                                      zhidanrenId,
               sys.name                              zhidanren,
               xsmx.`houdu`                                  houdu,
               xsmx.width                                 kuandu,
               xsmx.length                                changdu,
               jjfs.`id`                                     jijiafangshiId,
               jjfs.jsfs                                   jijiafangshiName,
               xsmx.batch_no                                  pici,
               xsmx.`lingzhi`                                lingzhi,
               xsmx.num                                jianshu,
               xsmx.`count`                                 counts,
               xsmx.`weight`                             zhongliang,
               SUM(ckmd.`lingzhi`)                           sflingzhi,
               SUM(ckmd.`jianshu`)                           sfjianshu,
               SUM(ckmd.`counts`)                            sfcounts,
               SUM(ckmd.`zhongliang`)                        sfzhongliang,
               \'1\'                                           isFlag
            FROM
               salesorder_details xsmx
                   INNER JOIN salesorder sale ON sale.`id` = xsmx.order_id
                   INNER JOIN stock_out_md ckmd ON ckmd.`data_id` = xsmx.`id`
                   INNER JOIN stock_out ck ON ck.id = ckmd.stock_out_id
                   LEFT JOIN view_specification guige ON guige.`id` = xsmx.`wuzi_id`
                   LEFT JOIN view_specification sfguige ON sfguige.`id` = ckmd.`guige_id`
                   LEFT JOIN jsfs jjfs ON xsmx.jsfs_id = jjfs.`id`
                   LEFT JOIN pjlx ON pjlx.`id` = sale.pjlx
                   LEFT JOIN custom cus ON cus.`id` = sale.`custom_id`
                   LEFT JOIN storage st ON st.`id` = xsmx.storage_id
                   LEFT JOIN admin sys ON sys.`id` = sale.add_id
                   LEFT JOIN admin oper ON oper.`id` = sale.employer
            where
               xsmx.delete_time is null
                   and sale.delete_time is null
                   and ck.delete_time is null
                   and ckmd.delete_time is null
                   and xsmx.companyid=' . $companyId . '
            GROUP BY
               xsmx.`id`
            UNION ALL
            SELECT
               qtmx.`id`                                     id,
               ckqt.`id`                                     zbid,
               ckqt.`yw_time`                                ywTime,
               ckqt.`system_number`                          systemNumber,
               ckqt.`status`                                 STATUS,
               pjlx.`id`                                     piaojuId,
               pjlx.pjlx                                   piaojuName,
               cus.`id`                                      customerId,
               cus.custom                                    customerName,
               st.`id`                                       cangkuId,
               st.storage                                     cangkuName,
               guige.productname_id                                       pinmingId,
               guige.productname                                     pinmingName,
               guige.id                                      guigeId,
               guige.specification                                 guigeName,
               sfguige.id                                    sfguigeId,
               GROUP_CONCAT(sfguige.specification) sfguigeName,
               ckqt.department                                   groupId,
               oper.`id`                                     caozuoyuanId,
               oper.name                             caozuoyuan,
               sys.`id`                                      zhidanrenId,
               sys.name                              zhidanren,
               qtmx.`houdu`                                  houdu,
               qtmx.`kuandu`                                 kuandu,
               qtmx.`changdu`                                changdu,
               jjfs.`id`                                     jijiafangshiId,
               jjfs.jsfs                                   jijiafangshiName,
               qtmx.`pihao`                                  pici,
               qtmx.`lingzhi`                                lingzhi,
               qtmx.`jianshu`                                jianshu,
               qtmx.`counts`                                 counts,
               qtmx.`zhongliang`                             zhongliang,
               SUM(ckmd.`lingzhi`)                           sflingzhi,
               SUM(ckmd.`jianshu`)                           sfjianshu,
               SUM(ckmd.`counts`)                            sfcounts,
               SUM(ckmd.`zhongliang`)                        sfzhongliang,
               \'2\'                                           isFlag
            FROM
               stock_other_out ckqt
                   INNER JOIN stock_other_out_details qtmx                   ON ckqt.id = qtmx.stock_other_out_id
                   INNER JOIN stock_out_md ckmd                   ON ckmd.`data_id` = qtmx.`id`
                   INNER JOIN stock_out ck                   ON ck.id = ckmd.stock_out_id
                   LEFT JOIN view_specification guige                   ON guige.`id` = qtmx.`guige_id`
                   LEFT JOIN view_specification sfguige                   ON sfguige.`id` = ckmd.`guige_id`
                   LEFT JOIN jsfs jjfs                   ON qtmx.`jijiafangshi_id` = jjfs.`id`
                   LEFT JOIN  pjlx                   ON pjlx.`id` = ckqt.`piaoju_id`
                   LEFT JOIN custom cus                   ON cus.`id` = ckqt.`customer_id`
                   LEFT JOIN storage st                   ON st.`id` = qtmx.`store_id`
                   LEFT JOIN admin sys                   ON sys.`id` = ckqt.`create_operator_id`
                   LEFT JOIN admin oper                   ON oper.`id` = ckqt.`sale_operator_id`
            where
               ckqt.delete_time is null 
               and qtmx.delete_time is null 
               and ck.delete_time is null 
               and ckmd.delete_time is null
               and ckqt.companyid=' . $companyId . '
            GROUP BY
               qtmx.`id`) t
    where
       1 = 1';
        if (!empty($params['ywsjStart'])) {
            $sql .= 'AND  t.ywTime  >=   ?';
            $sqlParams[] = $params['ywsjStart'];
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= 'AND t.ywtime  <  ?';
            $sqlParams[] = date('Y-m-d H:i:s', strtotime($params['ywsjEnd'] . ' +1 day'));
        }
        if (!empty($params['system_number'])) {
            $sql .= 'AND t.systemNumber LIKE ?';
            $sqlParams[] = '%' . $params['system_number'] . '%';
        }
        if (!empty($params['status'])) {
            $sql .= ' AND t.status = ?';
            $sqlParams[] = $params['status'];
        }
        if (!empty($params['piaoju_id'])) {
            $sql .= ' AND t.piaojuId = ?';
            $sqlParams[] = $params['piaoju_id'];
        }
        if (!empty($params['customer_id'])) {
            $sql .= ' AND t.customerId = ?';
            $sqlParams[] = $params['customer_id'];
        }
        if (!empty($params['store_id'])) {
            $sql .= ' AND t.cangkuId = ?';
            $sqlParams[] = $params['store_id'];
        }
        if (!empty($params['pinming_id'])) {
            $sql .= 'AND t.pinmingId = ?';
            $sqlParams[] = $params['pinming_id'];
        }
        if (!empty($params['guige_id'])) {
            $sql .= ' AND t.guigeId = ?';
            $sqlParams[] = $params['guige_id'];
        }
        if (!empty($params['department'])) {
            $sql .= ' AND t.groupId = ?';
            $sqlParams[] = $params['department'];
        }
        if (!empty($params['employer'])) {
            $sql .= ' AND t.caozuoyuanId = ?';
            $sqlParams[] = $params['employer'];
        }
        if (!empty($params['create_operator_id'])) {
            $sql .= ' AND t.zhidanrenId = ?';
            $sqlParams[] = $params['create_operator_id'];
        }
        if (!empty($params['bjcounts'])) {
            if (!in_array($params['bjcounts'], $this->allowOperator)) {
                throw new Exception('不是允许的操作符');
            }
            $sql .= ' AND  t.sfcounts ' . $params['bjcounts'] . ' t.counts';
        }
        if (!empty($params['bjzhongliang'])) {
            if (!in_array($params['bjzhongliang'], $this->allowOperator)) {
                throw new Exception('不是允许的操作符');
            }
            $sql .= ' AND t.sfzhongliang ' . $params['bjzhongliang'] . ' t.zhongliang';
        }
        if (!empty($params['bjguige'])) {
            if (!in_array($params['bjguige'], $this->allowOperator)) {
                throw new Exception('不是允许的操作符');
            }
            $sql .= ' AND t.id in (
                select tt.id from (
                SELECT
                xsmx.`id`
                FROM salesorder_details xsmx INNER JOIN stock_out_md ckmd ON xsmx.`id`=ckmd.`data_id`
                WHERE ckmd.guige_id ' . $params['bjguige'] . ' xsmx.guige_id and xsmx.companyid=' . $companyId . '
                UNION ALL
                SELECT
                qtmx.`id`
                FROM stock_other_out_details qtmx INNER JOIN kc_ck_md ckmd ON qtmx.`id`=ckmd.`data_id`
                WHERE ckmd.guige_id ' . $params['bjguige'] . ' qtmx.guige_id and qtmx.companyid=' . $companyId . ' ) tt
                )';
        }
        if (!empty($params['bjchangdu'])) {
            if (!in_array($params['bjchangdu'], $this->allowOperator)) {
                throw new Exception('不是允许的操作符');
            }
            $sql .= ' AND t.id in (
                select tt.id from (
                SELECT
                xsmx.`id`
                FROM salesorder_details xsmx INNER JOIN stock_out_md ckmd ON xsmx.`id`=ckmd.`data_id`
                WHERE ckmd.changdu ' . $params['bjchangdu'] . ' xsmx.changdu and xsmx.companyid=' . $companyId . '
                UNION ALL
                SELECT
                qtmx.`id`
                FROM stock_other_out_details qtmx INNER JOIN kc_ck_md ckmd ON qtmx.`id`=ckmd.`data_id`
                WHERE ckmd.changdu ' . $params['bjchangdu'] . ' qtmx.changdu and qtmx.companyid=' . $companyId . ') tt
                )';
        }
        $sql .= ' )';
        $data = Db::table($sql)->alias('t')->bind($sqlParams)->order('ywTime', 'desc')->paginate($pageLimit);
        return $data;
    }
    public function insertPdKcCkMxMd($ck,$dataId,$chukuType,$ywTime,$systemNumber,$dataNumber,$customerId,$pinmingId,$guigeId,$caizhiId,$chandiId,$jijiafangshiId,$storeId,$houdu,$kuandu,$changdu,$zhijian,
                                     $lingzhi,$jianshu,$counts, $zhongliang,$price,$sumprice,$shuiprice,$sumShuiPrice,$mizhong,$jianzhong,$cbPrice,$ykreason,$userId,$companyId){
        $mx=new StockOutDetail();
        $mx->kc_ck_id=$ck["id"];
        $mx->kc_ck_tz_id=null;
        $mx->data_id=$dataId;
        $mx->chuku_type=$chukuType;
        $mx->yw_time=$ywTime;
        $mx->system_number=$systemNumber;
        $mx->data_number=$dataNumber;
        $mx->customer_id=$customerId;
        $mx->pinming_id=$pinmingId;
        $mx->guige_id=$guigeId;
        $mx->caizhi_id=$caizhiId;
        $mx->chandi_id=$chandiId;
        $mx->jijiafangshi_id=$jijiafangshiId;
        $mx->store_id=$storeId;
        $mx->houdu=$houdu;
        $mx->kuandu=$kuandu;
        $mx->changdu=$changdu;
        $mx->lingzhi=$lingzhi;
        $mx->jianshu=$jianshu;
        $mx->counts=$counts;
        $mx->zhongliang=$zhongliang;
        $mx->price=$price;
        $mx->sumprice=$sumprice;
        $mx->shuiprice=$shuiprice;
        $mx->sum_shui_price=$sumShuiPrice;
        $mx->mizhong=$mizhong;
        $mx->jianzhong=$jianzhong;
        $mx->cb_price=$cbPrice;
        $mx->ykreason=$ykreason;
        $mx->user_id=$userId;
        $mx->companyid=$companyId;
//        $count = self::withTrashed()
//            ->where('companyid', $companyId)
//            ->whereTime('create_time', 'today')
//            ->count();
//        $ck->system_number = '' . date('Ymd') . str_pad($count + 1, 3, 0, STR_PAD_LEFT);
        $mx->save();
    }
}