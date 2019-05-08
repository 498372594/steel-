<?php


namespace app\admin\model;


use Exception;
use think\Db;
use think\exception\DbException;
use think\Paginator;
use traits\model\SoftDelete;

class StockOutMd extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public static function findCountsByDataId($dataId)
    {
        return self::alias('md')
            ->join('__STOCK_OUT__ ck', 'ck.id=md.stock_out_id')
            ->where('md.data_id', $dataId)
            ->where('ck.status', '<>', 2)
            ->max('md.counts');
    }

    public static function findZhongliangByDataId($dataId)
    {
        return self::alias('md')
            ->join('__STOCK_OUT__ ck', 'ck.id=md.stock_out_id')
            ->where('md.data_id', $dataId)
            ->where('ck.status', '<>', 2)
            ->max('md.zhongliang');
    }

    public function specification()
    {
        return $this->belongsTo('ViewSpecification', 'guige_id', 'id')->cache(true, 60)
            ->field('id,specification,mizhong_name,productname')
            ->bind(['guige' => 'specification', 'pinming' => 'productname']);
    }

    public function jsfs()
    {
        return $this->belongsTo('Jsfs', 'jijiafangshi_id', 'id')->cache(true, 60)
            ->field('id,jsfs')->bind(['jsfs_name' => 'jsfs']);
    }

    public function spot()
    {
        return $this->belongsTo('KcSpot', 'kc_spot_id', 'id')->cache(true, 60)
            ->field('id,resource_number')->bind('resource_number');
    }

    public function storage()
    {
        return $this->belongsTo('Storage', 'store_id', 'id')->cache(true, 60)
            ->field('id,storage')->bind(['store_name' => 'storage']);
    }

    public function mainData()
    {
        return $this->belongsTo('StockOut', 'stock_out_id', 'id');
    }

    public function stockOutData()
    {
        return $this->belongsTo('StockOut', 'sstock_out_id', 'id')
            ->field('id')->bind(['kc_ck_id' => 'id']);
    }

    public function caizhiData()
    {
        return $this->belongsTo('Texture', 'caizhi', 'id')->cache(true, 60)
            ->field('id,texturename')->bind('texturename');
    }

    public function chandiData()
    {
        return $this->belongsTo('Originarea', 'chandi', 'id')->cache(true, 60)
            ->field('id,originarea')->bind(['originarea_name' => 'originarea']);
    }

    /**
     * @param $params
     * @param $pageLimit
     * @param $companyId
     * @return Paginator
     * @throws DbException
     */
    public function getListByMxId($params, $pageLimit, $companyId)
    {
        $sqlParams = [];
        $sql = '(SELECT
    ckmd.id ,
    rkmx.cache_customer,
    cu.custom customerName,
    ck.yw_time fh_yw_time,
    ck.chuku_type,
    spot.resource_number,
    gg.productname_id,
    gg.productname pinming_name,
    ckmd.guige_id,
    gg.specification guige_name,
    ckmd.houdu,
    ckmd.kuandu,
    ckmd.changdu,
    ckmd.caizhi,
    cz.texturename caizhi_name,
    ckmd.chandi,
    cd.originarea chandi_name,
    ckmd.jijiafangshi_id,
    jjfs.jsfs jijiafangshi_name,
    ckmd.lingzhi,
    ckmd.jianshu,
    ckmd.counts,
    ckmd.zhongliang,
    ckmd.cb_price,
    ckmd.cb_shuie,
    ckmd.tax_rate,
    ckmd.cb_sum_shuiprice,
    ckmd.store_id,
    store.storage storeName,
    rkmd.huohao,
    rkmd.pihao,
    rk.ruku_type,
    rk.yw_time rk_yw_time,
    CASE jf.jj_type
        WHEN 1 THEN
            spot.lisuan_jianzhong
        WHEN 2 THEN
            spot.guobang_jianzhong
        END jianzhong,
    ck.sale_operator_id,
    op.name sale_operator_name,
    sa.create_time
FROM
    stock_out_md ckmd
        LEFT JOIN salesorder_details samx ON samx.id=ckmd.data_id
        LEFT JOIN salesorder sa ON samx.order_id=sa.id
        LEFT JOIN stock_out ck ON ck.id = ckmd.stock_out_id
        LEFT JOIN pjlx ON pjlx.id=sa.pjlx
        LEFT JOIN admin czy ON czy.id=sa.employer
        LEFT JOIN admin op ON op.id = ck.sale_operator_id
        LEFT JOIN kc_spot spot ON spot.id = ckmd.kc_spot_id
        LEFT JOIN view_specification gg ON gg.id = ckmd.guige_id
        LEFT JOIN texture cz ON cz.id = ckmd.caizhi
        LEFT JOIN originarea cd ON cd.id = ckmd.chandi
        LEFT JOIN storage store ON store.id = ckmd.store_id
        LEFT JOIN jsfs jjfs ON jjfs.id = ckmd.jijiafangshi_id
        LEFT JOIN kc_rk_md rkmd ON rkmd.id = spot.rk_md_id
        LEFT JOIN kc_rk rk ON rkmd.kc_rk_id = rk.id
        LEFT JOIN kc_rk_mx rkmx ON rk.id = rkmx.kc_rk_id
        LEFT JOIN custom cu ON cu.`id` = rkmx.cache_customer
        LEFT JOIN jsfs jf ON rkmd.jijiafangshi_id = jf.id
        LEFT JOIN jsfs jj ON jj.`id` = spot.`jijiafangshi_id`
where
    ckmd.companyid=' . $companyId;
        if (!empty($params['mx_id'])) {
            $sql .= 'AND ckmd.data_id = ?';
            $sqlParams[] = $params['mx_id'];
        }
        if (!empty($params['ywsjStart'])) {
            $sql .= 'and sa.ywsj >= ?';
            $sqlParams[] = $params['ywsjStart'];
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= 'and sa.ywsj <= ?';
            $sqlParams[] = date('Y-m-d H:i:s', strtotime($params['ywsjEnd']));
        }
        if (!empty($params['system_number'])) {
            $sql .= 'and sa.system_no like ?';
            $sqlParams[] = '%' . $params['system_number'] . '%';
        }
        if (!empty($params['status']) && $params['status'] != -1) {
            $sql .= 'and sa.status = ?';
            $sqlParams[] = $params['status'];
        }
        if (!empty($params['customer_id'])) {
            $sql .= 'and sa.custom_id = ?';
            $sqlParams[] = $params['customer_id'];
        }
        if (!empty($params['piaoju_id'])) {
            $sql .= 'and sa.pjlx = ?';
            $sqlParams[] = $params['piaoju_id'];
        }
        if (!empty($params['employer'])) {
            $sql .= 'and sa.employer = ?';
            $sqlParams[] = $params['employer'];
        }
        if (!empty($params['department'])) {
            $sql .= 'and sa.department = ?';
            $sqlParams[] = $params['department'];
        }
        if (!empty($params['create_operator_id'])) {
            $sql .= 'and ck.sale_operator_id = ';
            $sqlParams[] = $params['create_operator_id'];
        }
        if (!empty($params['beizhu'])) {
            $sql .= 'and sa.remark like ?';
            $sqlParams[] = '%' . $params['beizhu'] . '%';
        }
        $sql .= ' GROUP BY ckmd.id)';
        $data = Db::table($sql)->alias('t')->order('create_time', 'desc')->paginate($pageLimit);
        return $data;
    }

    /**
     * @param $ckId
     * @param $ckMxId
     * @param $spotId
     * @param $dataId
     * @param $chukuType
     * @param $pinmingId
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
     * @param $mizhong
     * @param $jianzhong
     * @param $cbPrice
     * @param $beizhu
     * @param $companyId
     * @throws DbException
     * @throws Exception
     */
    public function insertCkMd($ckId, $ckMxId, $spotId, $dataId, $chukuType, $pinmingId, $guigeId, $caizhiId, $chandiId, $jijiafangshiId, $storeId, $houdu, $kuandu, $changdu, $zhijian, $lingzhi, $jianshu, $counts, $zhongliang, $mizhong, $jianzhong, $cbPrice, $beizhu, $shuie, $companyId)
    {

        $spot = KcSpot::get($spotId);
        if (empty($spot) || empty($spotId)) {
            throw new Exception("引用的采购单还未入库，请入库后再操作！");
        }

        $md = new StockOutMd();
        $md->companyid = $companyId;
        $md->stock_out_id = $ckId;
        $md->stock_out_detail_id = $ckMxId;
        $md->kc_spot_id = $spotId;
        $md->data_id = $dataId;
        $md->chuku_type = $chukuType;
        $md->out_mode = 1;
        $md->pinming_id = $pinmingId;
        $md->caizhi = $caizhiId;
        $md->chandi = $chandiId;
        $md->jijiafangshi_id = $jijiafangshiId;
        $md->guige_id = $guigeId;
        $md->houdu = $houdu;
        $md->kuandu = $kuandu;
        $md->counts = $counts;
        $md->jianshu = $jianshu;
        $md->changdu = $changdu;
        $md->lingzhi = $lingzhi;
        $md->zhijian = $zhijian;
        $md->zhongliang = $zhongliang;
        $md->store_id = $storeId;
        $md->price = $spot['price'];
        $md->tax_rate = $spot['shui_price'];
        $md->beizhu = $beizhu;

        if (empty($cbPrice)) {
            $cbPrice = $spot['price'];
        }
        $md->cb_price = $cbPrice;
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
        $md->mizhong = $mizhong;
        $md->jianzhong = $jianzhong;
        $md->save();

        (new KcSpot())->adjustSpotById($spotId, false, $md['counts'], $md['zhongliang'], $md['jijiafangshi_id'], $shuie);
    }
}