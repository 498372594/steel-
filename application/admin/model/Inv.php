<?php


namespace app\admin\model;


use Exception;
use think\Db;
use think\db\exception\{DataNotFoundException, ModelNotFoundException};
use think\exception\DbException;
use think\Paginator;
use traits\model\SoftDelete;

class Inv extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    /**
     * @param $dataId
     * @param $ywType
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws Exception
     */
    public function deleteInv($dataId, $ywType)
    {
        $item = self::where('data_id', $dataId)
            ->where('yw_type', $ywType)
            ->find();
        if (!empty($item)) {
            if ($item['yhx_price'] != 0 || $item['yhx_zhongliang'] != 0) {
                throw new Exception("已经有发票结算信息!");
            }
            $item->delete();
        }
    }

    /**
     * @param $dataId
     * @param $ywType
     * @param $fangxiang
     * @param $customerId
     * @param $ywTime
     * @param $changdu
     * @param $kuandu
     * @param $houdu
     * @param $guigeId
     * @param $jijiafangshiId
     * @param $piaojuId
     * @param $pinmingId
     * @param $zhongliang
     * @param $price
     * @param $sumPrice
     * @param $sumShuiPrice
     * @param $shuiPrice
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function updateInv($dataId, $ywType, $fangxiang, $customerId, $ywTime, $changdu, $kuandu, $houdu, $guigeId,
                              $jijiafangshiId, $piaojuId, $pinmingId, $zhongliang, $price, $sumPrice, $sumShuiPrice, $shuiPrice)
    {
        $obj = self::where('data_id', $dataId)->where('yw_type', $ywType)->find();
        if (!empty($list)) {
            $cgmx = CgPurchaseMx::get($dataId);
            if (!empty($cgmx)) {
                $cg = CgPurchase::get($cgmx['purchase_id']);
                if (empty($customerId)) {
                    $customerId = $cg['customer_id'];
                }
                if (empty($ywTime)) {
                    $ywTime = $cg['yw_time'];
                }
                if (empty($piaojuId)) {
                    $piaojuId = $cg['piaoju_id'];
                }
            }
            if ($obj['yhx_price'] != 0 || $obj['yhx_zhongliang'] != 0) {
                throw new Exception("已经有发票结算信息!");
            }
            $obj->customer_id = $customerId;
            $obj->yw_time = $ywTime;
            $obj->changdu = $changdu;
            $obj->kuandu = $kuandu;
            $obj->guige_id = $guigeId;
            $obj->houdu = $houdu;
            $obj->jijiafangshi_id = $jijiafangshiId;
            $obj->piaoju_id = $piaojuId;
            if (empty($pinmingId) && !empty($guigeId)) {
                $gg = ViewSpecification::where('id', $guigeId)->cache(true, 60)->find();
                $obj->pinming_id = $gg['productname_id'] ?? '';
            } else {
                $obj->pinming_id = $pinmingId;
            }
            $obj->price = $price;
            $obj->shui_price = $shuiPrice;
            $obj->sum_price = $sumPrice;
            $obj->sum_shui_price = $sumShuiPrice;
            $obj->zhongliang = $zhongliang;
            if ($fangxiang != null) {
                $obj->fx_type = $fangxiang;
            }
            $obj->save();
        }
    }

    /**
     * @param $dataId
     * @param $ywType
     * @param $fangxiang
     * @param $changdu
     * @param $houdu
     * @param $kuandu
     * @param $guigeId
     * @param $jijiafangshiId
     * @param $piaojuId
     * @param $pinmingId
     * @param $systemNumber
     * @param $customerId
     * @param $ywTime
     * @param $price
     * @param $shuiPrice
     * @param $sumPrice
     * @param $sumShuiPrice
     * @param $zhongliang
     * @param $companyId
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function insertInv($dataId, $ywType, $fangxiang, $changdu, $houdu, $kuandu, $guigeId, $jijiafangshiId, $piaojuId, $pinmingId,
                              $systemNumber, $customerId, $ywTime, $price, $shuiPrice, $sumPrice, $sumShuiPrice, $zhongliang, $companyId)
    {
        $i = new self();
        $i->system_number = $systemNumber;
        $i->customer_id = $customerId;
        $i->yw_time = $ywTime;
        $i->yw_type = $ywType;
        $i->changdu = $changdu;
        $i->kuandu = $kuandu;
        $i->fx_type = $fangxiang;
        $i->guige_id = $guigeId;
        $i->houdu = $houdu;
        $i->jijiafangshi_id = $jijiafangshiId;
        $i->piaoju_id = $piaojuId;
        if (empty($pinmingId) && !empty($guigeId)) {
            $gg = ViewSpecification::where('id', $guigeId)->cache(true, 60)->find();
            $i->pinming_id = $gg['productname_id'] ?? '';
        } else {
            $i->pinming_id = $pinmingId;
        }

        $i->price = $price;
        $i->shui_price = $shuiPrice;
        $i->sum_price = $sumPrice;
        $i->sum_shui_price = $sumShuiPrice;
        $i->zhongliang = $zhongliang;
        $i->yhx_price = 0;
        $i->yhx_zhongliang = 0;
        $i->data_id = $dataId;
        $i->companyid = $companyId;
        $i->save();
    }

    /**
     * @param $dataId
     * @param $money
     * @param $zhongliang
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function jianMoney($dataId, $money, $zhongliang)
    {
        $money = $money == null ? 0 : $money;
        $zhongliang = $money == null ? 0 : $zhongliang;
        $inv = new self();
        $obj = $inv::where("id", $dataId)->field("id,yhx_zhongliang,yhx_price")->find();
        if ($obj) {
            if ($money != 0) {
                $fhMoney = $obj["yhx_price"] - $money;
                if ($fhMoney < 0) {
                    $obj["yhx_price"] = 0;
                } else {
                    $obj["yhx_price"] = $obj["yhx_price"] - $money;
                }
            }
            if ($zhongliang != 0) {
                $fhzhongliang = $obj["yhx_zhongliang"] - $money;
                if ($fhzhongliang < 0) {
                    $obj["yhx_zhongliang"] = 0;
                } else {
                    $obj["yhx_zhongliang"] = $obj["yhx_zhongliang"] - $money;
                }
            }
            $inv->save($obj);
        }
    }

    /**
     * @param $id
     * @param $oldMoney
     * @param $money
     * @param $oldZhongliang
     * @param $zhongliang
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function tiaoMoney($id, $oldMoney, $money, $oldZhongliang, $zhongliang)
    {
        $money = $money == null ? 0 : $money;
        $zhongliang = $money == null ? 0 : $zhongliang;
        $oldMoney = $oldMoney == null ? 0 : $oldMoney;
        $oldZhongliang = $oldZhongliang == null ? 0 : $oldZhongliang;
        $inv = new self();
        $obj = $inv::where("id", $id)->field("id,yhx_zhongliang,yhx_price")->find();
        if ($money != 0) {
            $obj["yhx_price"] = $obj["yhx_price"] + ($money - $oldMoney);
        }
        if ($zhongliang != 0) {
            $obj["yhx_zhongliang"] = $obj["yhx_zhongliang"] + ($zhongliang - $oldZhongliang);
        }
        $inv->save($obj);
    }

    /**
     * @param $dataId
     * @param $money
     * @param $zhongliang
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function addMoney($dataId, $money, $zhongliang)
    {
        $money = $money == null ? 0 : $money;
        $zhongliang = $money == null ? 0 : $zhongliang;
        $inv = new self();
        $obj = $inv::where("id", $dataId)->field("id,yhx_zhongliang,yhx_price")->find();
        if ($money != 0) {
            $obj["yhx_price"] = $obj["yhx_price"] + ($money);
        }
        if ($zhongliang != 0) {
            $obj["yhx_zhongliang"] = $obj["yhx_zhongliang"] + ($zhongliang);
        }
        $inv->save($obj);
    }

    /**
     * @param $params
     * @param $pageLimit
     * @return Paginator
     * @throws DbException
     */
    public function getYkfpHuizong($params, $pageLimit)
    {
        $ywsjStart = '';
        $ywsjEnd = '';
        if (!empty($params['ywsjStart'])) {
            $ywsjStart = $params['ywsjStart'];
        }
        if (!empty($params['ywsjEnd'])) {
            $ywsjEnd = date('Y-m-d H:i:s', strtotime($params['ywsjEnd'] . ' +1 day'));
        }

        $sqlParams = [];
        $sql = '(select t2.id customer_id,
       t2.daima,
       t2.gongying_shang customer_name,
       t2.qichu_yingkai,
       t2.benqi_yingkai,
       t2.benqi_yikai,
       t2.moren_yewuyuan,
       t2.suoshu_department,
       t2.create_time,
       t2.qimo_yue
    from
       (SELECT t1.id,
               t1.daima,
               t1.gongying_shang,
               t1.qichu_yingkai,
               t1.benqi_yingkai,
               t1.benqi_yikai,
               t1.moren_yewuyuan,
               t1.suoshu_department,
               t1.create_time,
               IFNULL(t1.qichu_yingkai, 0) + (IFNULL(t1.benqi_yingkai, 0) - IFNULL(t1.benqi_yikai, 0)) qimo_yue
            FROM
               (SELECT cus.id,
                       cus.zjm             daima,
                       cus.custom          gongying_shang,
                       cus.moren_yewuyuan,
                       cus.create_time,
                       cus.suoshu_department,';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' ((ifnull((SELECT SUM(IFNULL(mx.price_and_tax, 0))
                                     FROM salesorder_details mx
                                            LEFT JOIN salesorder se ON mx.order_id = se.id
                                     WHERE se.custom_id = cus.id
                                            and se.status != 2
                                            and mx.delete_time is null
                                            and se.delete_time is null
                                            and mx.tax_rate > 0
                                            AND se.ywsj < ?';
            $sqlParams[] = $ywsjStart;
            $sql .= ' ), 0))
                            + IFNULL((SELECT SUM(IFNULL(mx.money, 0))
                                          FROM init_yskp_mx mx
                                                 LEFT JOIN init_yskp yskp ON mx.yskp_id = yskp.id
                                          WHERE mx.customer_id = cus.id
                                                 AND yskp.type = 1
                                                 and mx.delete_time is null
                                                 and yskp.delete_time is null
                                                 and yskp.status != 1
                                                 AND yskp.yw_time < ?';
            $sqlParams[] = $ywsjStart;
            $sql .= ' ), 0)
                            + IFNULL((SELECT -SUM(IFNULL(mx.sum_shui_price, 0))
                                          FROM sales_return_details mx
                                                 LEFT JOIN sales_return th ON mx.xs_th_id = th.id
                                          WHERE th.customer_id = cus.id
                                                 and th.delete_time is null
                                                 and th.status != 2
                                                 and mx.delete_time is null
                                                 and mx.shuiprice > 0
                                                 AND th.yw_time < ?';
            $sqlParams[] = $ywsjStart;
            $sql .= ' ), 0)
                            - IFNULL((SELECT SUM(IFNULL(kp.money, 0) + IFNULL(kp.mkmoney, 0))
                                          FROM inv_xskp kp
                                          WHERE kp.customer_id = cus.id
                                                 and kp.status != 2
                                                 and kp.delete_time is null
                                                 AND kp.yw_time < ?';
            $sqlParams[] = $ywsjStart;
            $sql .= ' ), 0)
                           + ifnull((select sum(ifnull(qtmx.money, 0))
                                         from capital_other_details qtmx
                                                left join capital_other qt on qt.id = qtmx.cap_qt_id
                                         where qt.yw_type = 16
                                                and qt.status != 2
                                                and qt.fangxiang = 1
                                                and qtmx.delete_time is null
                                                and qt.delete_time is null
                                                AND qt.yw_time < ?';
            $sqlParams[] = $ywsjStart;
            $sql .= ' and qt.customer_id = cus.id
                                    ), 0)
                           )               qichu_yingkai,';
        } else {
            $sql .= ' 0                   qichu_yingkai,';
        }
        $sql .= ' ((ifnull((SELECT SUM(IFNULL(mx.price_and_tax, 0))
                                     FROM salesorder_details mx
                                            LEFT JOIN salesorder se ON mx.order_id = se.id
                                     WHERE se.custom_id = cus.id
                                            and se.status != 2
                                            and mx.delete_time is null
                                            and mx.tax_rate > 0
                                            and se.delete_time is null';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and se.ywsj >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and se.ywsj < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= ' ), 0)
                            )
                           + IFNULL((SELECT SUM(IFNULL(mx.money, 0))
                                         FROM init_yskp_mx mx
                                                LEFT JOIN init_yskp yskp ON mx.yskp_id = yskp.id
                                         WHERE mx.customer_id = cus.id
                                                and yskp.status != 1
                                                AND yskp.type = 1
                                                and mx.delete_time is null
                                                and yskp.delete_time is null';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and yskp.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and yskp.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= ' ), 0)
                           + IFNULL((SELECT -SUM(IFNULL(mx.sum_shui_price, 0))
                                         FROM sales_return_details mx
                                                LEFT JOIN sales_return th ON mx.xs_th_id = th.id
                                         WHERE th.customer_id = cus.id
                                                and th.delete_time is null
                                                and th.status != 2
                                                and mx.delete_time is null
                                                and mx.shuiprice > 0';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and th.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and th.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= ' ), 0)
                           + ifnull((select sum(ifnull(qtmx.money, 0))
                                         from capital_other_details qtmx
                                                left join capital_other qt on qt.id = qtmx.cap_qt_id
                                         where qt.yw_type = 16
                                                and qt.delete_time is null
                                                and qtmx.delete_time is null
                                                and qt.status != 2
                                                and qt.fangxiang = 1
                                                and qt.customer_id = cus.id';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and qt.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and qt.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= ' ), 0)) benqi_yingkai,
                       (IFNULL((SELECT SUM(IFNULL(kp.money, 0) + IFNULL(kp.mkmoney, 0))
                                    FROM inv_xskp kp
                                    WHERE kp.customer_id = cus.id
                                           and kp.status != 2
                                           and kp.delete_time is null';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and kp.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and kp.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= ' ), 0))      benqi_yikai
                    FROM
                       custom              cus
                    where
                       cus.delete_time is null
                           and cus.iscustom = 1
               )                                                                                       t1
       )     t2
    where
       1 = 1';
        if (!empty($params['hide_zero'])) {
            $sql .= ' and t2.qimo_yue != 0';
        }
        if (!empty($params['hide_no_happend'])) {
            $sql .= ' and (t2.qichuyingkai > 0 or t2.benqi_yingkai > 0 or t2.benqi_yingkai > 0 or t2.qimo_yue > 0)';
        }
        if (!empty($params['customer_id'])) {
            $sql .= ' and t2.customer_id = ?';
            $sqlParams[] = $params['customer_id'];
        }
        if (!empty($params['employer'])) {
            $sql .= ' and t2.moren_yewuyuan = ?';
            $sqlParams[] = $params['employer'];
        }
        if (!empty($params['department'])) {
            $sql .= ' and t2.soushu_department = ?';
            $sqlParams[] = $params['department'];
        }
        $sql .= ' )';
        $data = Db::table($sql)->alias('t')->bind($sqlParams)->order('create_time', 'asc')->paginate($pageLimit);
        return $data;
    }
}