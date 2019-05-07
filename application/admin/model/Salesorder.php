<?php

namespace app\admin\model;

use PDOStatement;
use think\{Db,
    db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    db\Query,
    Exception,
    exception\DbException,
    Model,
    Paginator};
use traits\model\SoftDelete;

class Salesorder extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    /**
     * @param $dataId
     * @param $moshiType
     * @return array|false|PDOStatement|string|Model
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
    public static function zuofeiSale($dataId, $moshiType)
    {
        $xs = self::where('data_id', $dataId)->where('ywlx', $moshiType)->find();
        if (empty($xs)) {
            throw new Exception("对象不存在");
        }
        $xs->status = 2;
        $xs->save();
        return $xs;
    }

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

    public function employerData()
    {
        return $this->belongsTo('Admin', 'employer', 'id')->cache(true, 60)
            ->field('id,name')->bind(['employer_name' => 'name']);
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
     * @param $pinmingId
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
    public function insertMx(Salesorder $sale, $dataId, $moshiType, $guigeId, $pinmingId, $caizhiId, $chandId, $storeId, $jijiafangshiId,
                             $houdu, $kuandu, $changdu, $lingzhi, $jianshu, $zhijian, $counts, $zhongliang, $jianzhong, $price, $sumPrice,
                             $shuiPrice, $sumShuiPrice, $pihao, $beizhu, $chehao, $shuie, $companyId)
    {
        $trumpet = SalesorderDetails::where('order_id', $sale['id'])->max('trumpet');

        $mx = new SalesorderDetails();
        $mx->companyid = $companyId;
        $mx->data_id = $dataId;
        $mx->moshi_type = $moshiType;
        $mx->order_id = $sale['id'];
        $mx->wuzi_id = $guigeId;
        $mx->pinming_id = $pinmingId;
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
        $mx->jianzhong = $jianzhong;

        $mx->price_and_tax = $sumShuiPrice;
        $mx->storage_id = $storeId;
        $mx->trumpet = $trumpet;
        $mx->tax = $shuie;
        $mx->save();
        return $mx;
    }

    /**
     * @param $dataId
     * @param $moshiType
     * @return array|false|PDOStatement|string|Model
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
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

    /**
     * 客户销量排行榜
     * @param $param
     * @param $pageLimit
     * @param $companyId
     * @return Paginator
     * @throws DbException
     */
    public function khSalesList($param, $pageLimit, $companyId)
    {
        $sqlParams = [];
        $sql = '(SELECT tb_mingxi.customer_id,
       tb_mingxi.customer_name,
       tb_mingxi.short_name,
       tb_mingxi.zjm                                                                    code,
       SUM(IFNULL(tb_mingxi.xszhongliang, 0)) - SUM(IFNULL(thmx.zhongliang, 0))      AS th_zhongliang,
       COUNT(tb_mingxi.xs_saleId)                                                    AS th_cishu,
       SUM(IFNULL(tb_mingxi.price_and_tax, 0)) - SUM(IFNULL(thmx.sum_shui_price, 0)) AS th_sum_shui_price
FROM (SELECT xsmx.weight AS xszhongliang,
             xs.xs_saleId,
             xsmx.id,
             xs.customer_name,
             xs.customer_id,
             xs.zjm,
             xsmx.price_and_tax,
             xs.short_name
      FROM (SELECT custom.zjm, custom.custom customer_name, custom.id AS customer_id, tb_xs_sale.id AS xs_saleId,custom.short_name
            FROM custom
                     LEFT JOIN salesorder tb_xs_sale ON custom.id = tb_xs_sale.custom_id
                WHERE custom.iscustom = 1
                     and custom.delete_time is null
                     AND tb_xs_sale.delete_time is null
                     AND tb_xs_sale.`status` <> 2 
                     and tb_xs_sale.companyid=' . $companyId;
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and tb_xs_sale.ywsj >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and tb_xs_sale.ywsj < :ywsjEnd';
            $sqlParams['ywsjEnd'] = date('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql .= ') AS xs
               INNER JOIN salesorder_details xsmx ON xs.xs_saleId = xsmx.order_id
     ) AS tb_mingxi
         LEFT JOIN sales_return_details thmx ON tb_mingxi.xs_saleId = thmx.xs_sale_mx_id
         left join
     (SELECT mx.xs_sale_mx_id, mx.zhongliang, mx.sum_shui_price
      FROM sales_return_details mx
               INNER JOIN sales_return th ON th.id = mx.xs_th_id WHERE th.delete_time is null AND th.status <> 2) thmx2
     on thmx2.xs_sale_mx_id = tb_mingxi.id
    WHERE 1 = 1 ';
        if (!empty($param['customer_id'])) {
            $sql .= ' and tb_mingxi.customer_id=:customerId';
            $sqlParams['customerId'] = $param['customer_id'];
        }
        $sql .= ' GROUP BY tb_mingxi.customer_id)';
        return Db::table($sql)->alias('t')->bind($sqlParams)->order('th_zhongliang', 'asc')->paginate($pageLimit);
    }

    /**
     * 业务员销量排行榜
     * @param $param
     * @param $pageLimit
     * @param $companyId
     * @return Paginator
     * @throws DbException
     */
    public function ywySalesList($param, $pageLimit, $companyId)
    {
        $sqlParams = [];
        $sql = '(SELECT tb_mingxi.ywy_id,
       tb_mingxi.ywy_name,
       SUM(IFNULL(tb_mingxi.xszhongliang, 0)) - SUM(IFNULL(thmx.zhongliang, 0))      AS th_zhongliang,
       COUNT(tb_mingxi.xs_saleId)                                                    AS th_cishu,
       SUM(IFNULL(tb_mingxi.price_and_tax, 0)) - SUM(IFNULL(thmx.sum_shui_price, 0)) AS th_sum_shui_price
FROM (SELECT xsmx.weight AS xszhongliang,
             xs.xs_saleId,
             xsmx.id,
             xs.ywy_name,
             xs.ywy_id,
             xsmx.price_and_tax
      FROM (SELECT ywy.name ywy_name, ywy.id AS ywy_id, tb_xs_sale.id AS xs_saleId
            FROM admin ywy
                     LEFT JOIN salesorder tb_xs_sale ON ywy.id = tb_xs_sale.employer
                WHERE tb_xs_sale.delete_time is null
                     AND tb_xs_sale.`status` <> 2 
                     and tb_xs_sale.companyid=' . $companyId;
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and tb_xs_sale.ywsj >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and tb_xs_sale.ywsj < :ywsjEnd';
            $sqlParams['ywsjEnd'] = date('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql .= ') AS xs
               INNER JOIN salesorder_details xsmx ON xs.xs_saleId = xsmx.order_id
     ) AS tb_mingxi
         LEFT JOIN sales_return_details thmx ON tb_mingxi.xs_saleId = thmx.xs_sale_mx_id
         left join
     (SELECT mx.xs_sale_mx_id, mx.zhongliang, mx.sum_shui_price
      FROM sales_return_details mx
               INNER JOIN sales_return th ON th.id = mx.xs_th_id WHERE th.delete_time is null AND th.status <> 2) thmx2
     on thmx2.xs_sale_mx_id = tb_mingxi.id
    WHERE 1 = 1 ';
        if (!empty($param['sale_operator_id'])) {
            $sql .= ' and tb_mingxi.ywy_id=:sale_operator_id';
            $sqlParams['sale_operator_id'] = $param['sale_operator_id'];
        }
        $sql .= ' GROUP BY tb_mingxi.ywy_id)';
        return Db::table($sql)->alias('t')->bind($sqlParams)->order('th_zhongliang', 'asc')->paginate($pageLimit);
    }

    /**
     * 货物销量排名
     * @param $param
     * @param $pageLimit
     * @param $companyId
     * @return Paginator
     * @throws DbException
     */
    public function hwSalesList($param, $pageLimit, $companyId)
    {
        $sqlParams = [];
        $sql = '(SELECT tb_mingxi.guige_id,
        tb_mingxi.guige,
        tb_mingxi.pinming,
       SUM(IFNULL(tb_mingxi.xszhongliang, 0)) - SUM(IFNULL(thmx.zhongliang, 0))      AS th_zhongliang,
       COUNT(tb_mingxi.xs_saleId)                                                    AS th_cishu,
       SUM(IFNULL(tb_mingxi.price_and_tax, 0)) - SUM(IFNULL(thmx.sum_shui_price, 0)) AS th_sum_shui_price
FROM (SELECT xsmx.wuzi_id as guige_id,
             xsmx.weight AS xszhongliang,
             tb_xs_sale.id xs_saleId,
             gg.specification guige,
             gg.productname pinming,
             xsmx.id,
             xsmx.price_and_tax
        FROM salesorder tb_xs_sale
           INNER JOIN salesorder_details xsmx ON tb_xs_sale.id = xsmx.order_id
           inner join view_specification gg on gg.id = xsmx.wuzi_id
        WHERE tb_xs_sale.delete_time is null
           AND tb_xs_sale.`status` <> 2 
           and tb_xs_sale.companyid=' . $companyId;
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and tb_xs_sale.ywsj >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and tb_xs_sale.ywsj < :ywsjEnd';
            $sqlParams['ywsjEnd'] = date('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql .= '
     ) AS tb_mingxi
         LEFT JOIN sales_return_details thmx ON tb_mingxi.xs_saleId = thmx.xs_sale_mx_id
         left join
     (SELECT mx.xs_sale_mx_id, mx.zhongliang, mx.sum_shui_price
      FROM sales_return_details mx
               INNER JOIN sales_return th ON th.id = mx.xs_th_id WHERE th.delete_time is null AND th.status <> 2) thmx2
     on thmx2.xs_sale_mx_id = tb_mingxi.id
    WHERE 1 = 1 ';
        if (!empty($param['guige_id'])) {
            $sql .= ' and tb_mingxi.guige_id=:guige_id';
            $sqlParams['guige_id'] = $param['guige_id'];
        }
        $sql .= ' GROUP BY tb_mingxi.guige_id)';
        return Db::table($sql)->alias('t')->bind($sqlParams)->order('th_zhongliang', 'asc')->paginate($pageLimit);
    }

    /**
     * 区域销售排行榜
     * @param $param
     * @param $pageLimit
     * @param $companyId
     * @return Paginator
     * @throws DbException
     */
    public function areaSalesList($param, $pageLimit, $companyId)
    {
        $sqlParams = [];
        $sql = '(SELECT tb_mingxi.province,
       tb_mingxi.city,
       t_province.name as province_name,
       t_city.name as city_name,
       SUM(IFNULL(tb_mingxi.xszhongliang, 0)) - SUM(IFNULL(thmx.zhongliang, 0))      AS th_zhongliang,
       COUNT(tb_mingxi.xs_saleId)                                                    AS th_cishu,
       SUM(IFNULL(tb_mingxi.price_and_tax, 0)) - SUM(IFNULL(thmx.sum_shui_price, 0)) AS th_sum_shui_price
FROM (SELECT xsmx.weight AS xszhongliang,
             xs.xs_saleId,
             xsmx.id,
             xs.province,
             xs.city,
             xsmx.price_and_tax
      FROM (SELECT custom.province, custom.city,tb_xs_sale.id AS xs_saleId
            FROM custom
                     LEFT JOIN salesorder tb_xs_sale ON custom.id = tb_xs_sale.custom_id
                WHERE custom.iscustom = 1
                     and custom.delete_time is null
                     AND tb_xs_sale.delete_time is null
                     AND tb_xs_sale.`status` <> 2 
                     and tb_xs_sale.companyid=' . $companyId;
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and tb_xs_sale.ywsj >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and tb_xs_sale.ywsj < :ywsjEnd';
            $sqlParams['ywsjEnd'] = date('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql .= ') AS xs
               INNER JOIN salesorder_details xsmx ON xs.xs_saleId = xsmx.order_id
     ) AS tb_mingxi
         LEFT JOIN sales_return_details thmx ON tb_mingxi.xs_saleId = thmx.xs_sale_mx_id
         left join
     (SELECT mx.xs_sale_mx_id, mx.zhongliang, mx.sum_shui_price
      FROM sales_return_details mx
               INNER JOIN sales_return th ON th.id = mx.xs_th_id WHERE th.delete_time is null AND th.status <> 2) thmx2
     on thmx2.xs_sale_mx_id = tb_mingxi.id
     inner join area t_city on t_city.id=tb_mingxi.city
     inner join area t_province on t_province.id=tb_mingxi.province
    WHERE 1 = 1 ';
        if (!empty($param['province'])) {
            $sql .= ' and tb_mingxi.province=:province';
            $sqlParams['province'] = $param['province'];
        }
        if (!empty($param['city'])) {
            $sql .= ' and tb_mingxi.city=:city';
            $sqlParams['city'] = $param['city'];
        }
        $sql .= ' GROUP BY tb_mingxi.city)';
        return Db::table($sql)->alias('t')->bind($sqlParams)->order('th_zhongliang', 'asc')->paginate($pageLimit);
    }

    /**
     * @param $param
     * @param $pageLimit
     * @param $companyId
     * @return Paginator
     * @throws DbException
     */
    public function zxkSalesList($param, $pageLimit, $companyId)
    {
        $sqlParams = [];
        $sql = '(SELECT tb_mingxi.guige_id,
        tb_mingxi.guige,
        tb_mingxi.pinming,
       SUM(IFNULL(tb_mingxi.xszhongliang, 0)) - SUM(IFNULL(thmx.zhongliang, 0))      AS th_zhongliang,
       COUNT(tb_mingxi.xs_saleId)                                                    AS th_cishu,
       SUM(IFNULL(tb_mingxi.price_and_tax, 0)) - SUM(IFNULL(thmx.sum_shui_price, 0)) AS th_sum_shui_price
FROM (SELECT xsmx.wuzi_id as guige_id,
             xsmx.weight AS xszhongliang,
             tb_xs_sale.id xs_saleId,
             gg.specification guige,
             gg.productname pinming,
             xsmx.id,
             xsmx.price_and_tax
        FROM salesorder tb_xs_sale
           INNER JOIN salesorder_details xsmx ON tb_xs_sale.id = xsmx.order_id
           inner join view_specification gg on gg.id = xsmx.wuzi_id
        WHERE tb_xs_sale.delete_time is null
           and (tb_xs_sale.ywlx = 1 or tb_xs_sale.ywlx = 2)
           AND tb_xs_sale.`status` <> 2 
           and tb_xs_sale.companyid=' . $companyId;
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and tb_xs_sale.ywsj >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and tb_xs_sale.ywsj < :ywsjEnd';
            $sqlParams['ywsjEnd'] = date('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        $sql .= '
     ) AS tb_mingxi
         LEFT JOIN sales_return_details thmx ON tb_mingxi.xs_saleId = thmx.xs_sale_mx_id
         left join
     (SELECT mx.xs_sale_mx_id, mx.zhongliang, mx.sum_shui_price
      FROM sales_return_details mx
               INNER JOIN sales_return th ON th.id = mx.xs_th_id WHERE th.delete_time is null AND th.status <> 2) thmx2
     on thmx2.xs_sale_mx_id = tb_mingxi.id
    WHERE 1 = 1 ';
        if (!empty($param['guige_id'])) {
            $sql .= ' and tb_mingxi.guige_id=:guige_id';
            $sqlParams['guige_id'] = $param['guige_id'];
        }
        $sql .= ' GROUP BY tb_mingxi.guige_id)';
        return Db::table($sql)->alias('t')->bind($sqlParams)->order('th_zhongliang', 'asc')->paginate($pageLimit);
    }
}