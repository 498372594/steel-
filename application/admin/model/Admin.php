<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-03-30
 * Time: 14:45
 */

namespace app\admin\model;


use think\Db;
use think\exception\DbException;
use think\model\relation\HasOne;
use think\Paginator;

class Admin extends Base
{
    protected $table = 'admin';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'create_time';

    protected $updateTime = 'update_time';

    protected $hidden = ['password'];

    public function setPasswordAttr($value)
    {
        return md5($value);
    }

    public function getDepartmentIdAttr($value)
    {
        return (string)$value;
    }

    /**
     * @return HasOne
     */
    public function company()
    {
        return $this->hasOne('company', 'id', 'companyid');
    }

    /**
     * @return HasOne
     */
    public function role()
    {
        return $this->hasOne(Dropdown::class, 'code', 'department_id')->where(['module' => 'role'])->field('val,code');
    }

    /**
     * 业务员利润汇总
     * @param $params
     * @param $pageLimit
     * @param $companyId
     * @return Paginator
     * @throws DbException
     */
    public function lirun($params, $pageLimit, $companyId)
    {
        $ywsjEnd = '';
        if (!empty($params['ywsjEnd'])) {
            $ywsjEnd = date('Y-m-d H:i:s', strtotime($params['ywsjEnd'] . ' +1 day'));
        }
        $sqlParams = [];
        $sql = '(SELECT ywy.id,
       ywy.name,
       sale.xiaoShouZhongLiang,
       sale.xiaoShouSumPrice,
       sale.xiaoShouShuiPrice,
       ifnull(th.tuiHuoZhongLiang, 0)                                        tuiHuoZhongLiang,
       (IFNULL(sale.xiaoShouZhongLiang, 0) - IFNULL(th.tuiHuoZhongLiang, 0)) shiXiaoZhongLiang,
       (IFNULL(sale.xiaoShouSumPrice, 0) +
        IFNULL(sale.xiaoShouShuiPrice, 0) - IFNULL(fyt.xiaoShouFySumPrice, 0) - IFNULL(fyt.xiaoShouFyShuiPrice, 0) -
        IFNULL(ck.chengBenSumPrice, 0) -
        IFNULL(ck.chengBenShuiPrice, 0))                                     maolirun,
       ifnull(th.tuiHuoSumPrice, 0)                                          tuiHuoSumPrice,
       ifnull(th.tuiHuoShuiPrice, 0)                                         tuiHuoShuiPrice,
       IFNULL(ck.chengBenSumPrice, 0) - ifnull(th.chengBenSumPrice1, 0)      chengBenSumPrice,
       IFNULL(ck.chengBenShuiPrice, 0) - ifnull(th.chengBenShuiPrice1, 0)    chengBenShuiPrice,
       ifnull(fyt.xiaoShouFySumPrice, 0)                                     xiaoShouFySumPrice,
       ifnull(fyt.xiaoShouFyShuiPrice, 0)                                    xiaoShouFyShuiPrice
FROM admin ywy
         inner JOIN (SELECT';
        if (!empty($params['shuiType']) && $params['shuiType'] == 1) {//含税
            $sql .= ' sale1.ywsj,
sale1.employer                  xsYwyId,
SUM(ifnull(saleMx.weight, 0))    xiaoShouZhongLiang,
SUM(ifnull(saleMx.total_fee, 0)) xiaoShouSumPrice,
SUM(ifnull(saleMx.tax, 0))       xiaoShouShuiPrice';
        } else {
            $sql .= ' sale1.ywsj,
 sale1.employer                  xsYwyId,
 SUM(ifnull(saleMx.weight, 0))    xiaoShouZhongLiang,
 SUM(ifnull(saleMx.total_fee, 0)) xiaoShouSumPrice,
 0                                xiaoShouShuiPrice';
        }
        $sql .= ' FROM salesorder_details saleMx
                              LEFT JOIN salesorder sale1 ON sale1.id = saleMx.order_id
                     WHERE sale1.delete_time is null
                       AND sale1.status != 2
                       AND saleMx.delete_time is null
                       and sale1.companyid = ' . $companyId;
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and sale1.ywsj >= ?';
            $sqlParams[] = $params['ywsjStart'];
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and sale1.ywsj < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= ' GROUP BY sale1.employer
) sale ON ywy.id = sale.xsYwyId
         LEFT JOIN (SELECT';
        if (!empty($params['shuiType']) && $params['shuiType'] == 1) {
            $sql .= ' xsth.yw_time,
xsth.sale_operator_id                 xsThYwyId,
SUM(ifnull(thmx.zhongliang, 0))  tuiHuoZhongLiang,
SUM(ifnull(thmx.sumprice, 0))    tuiHuoSumPrice,
SUM(ifnull(thmx.shuie, 0))       tuiHuoShuiPrice,
SUM(IFNULL(md.`cb_sumprice`, 0)) chengBenSumPrice1,
SUM(IFNULL(md.`cb_shuie`, 0))    chengBenShuiPrice1';
        } else {
            $sql .= ' xsth.yw_time,
 xsth.sale_operator_id                 xsThYwyId,
 SUM(ifnull(thmx.zhongliang, 0))  tuiHuoZhongLiang,
 SUM(ifnull(thmx.sumprice, 0))    tuiHuoSumPrice,
 \'0\'                              tuiHuoShuiPrice,
 SUM(IFNULL(md.`cb_sumprice`, 0)) chengBenSumPrice1,
 \'0\'                              chengBenShuiPrice1';
        }
        $sql .= ' FROM sales_return_details thmx
                             LEFT JOIN sales_return xsth ON xsth.id = thmx.xs_th_id
                             left join kc_rk_md md on md.data_id = thmx.id
                    WHERE xsth.delete_time is null
                      AND xsth.status != 2
                      and xsth.companyid = ' . $companyId;
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and xsth.yw_time >= ?';
            $sqlParams[] = $params['ywsjStart'];
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and xsth.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= ' GROUP BY xsth.sale_operator_id
) th ON ywy.id = th.xsThYwyId
         LEFT JOIN (SELECT';
        if (!empty($params['shuiType']) && $params['shuiType'] == 1) {
            $sql .= ' kcck.yw_time,
kcck.sale_operator_id            kcYwyId,
SUM(ifnull(ckmd.cb_sumprice, 0)) chengBenSumPrice,
SUM(ifnull(ckmd.cb_shuie, 0))    chengBenShuiPrice';
        } else {
            $sql .= ' kcck.yw_time,
 kcck.sale_operator_id            kcYwyId,
 SUM(ifnull(ckmd.cb_sumprice, 0)) chengBenSumPrice,
 \'0\'                              chengBenShuiPrice';
        }
        $sql .= ' FROM stock_out_detail ckmx
                             LEFT JOIN stock_out kcck ON kcck.id = ckmx.stock_out_id
                             LEFT JOIN stock_out_md ckmd ON ckmd.stock_out_detail_id = ckmx.id
                    WHERE kcck.status != 2
                      AND kcck.delete_time is null
                      AND ckmx.delete_time is null
                      and kcck.companyid = ' . $companyId;
        if (!empty($params['ywsjStart'])) {
            $sql .= 'and kcck.yw_time >= ?';
            $sqlParams[] = $params['ywsjStart'];
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and kcck.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= ' GROUP BY kcck.sale_operator_id
) ck ON ywy.id = ck.kcYwyId
         LEFT JOIN (SELECT';
        if (!empty($params['shuiType']) && $params['shuiType'] == 1) {
            $sql .= ' sale.ywsj,
 sale.employer                    fyYwyId,
 SUM(IFNULL(case fy.fang_xiang when 2 then fy.price_and_tax else (0-fy.price_and_tax) end, 0)) xiaoShouFySumPrice,
 SUM(IFNULL(case fy.fang_xiang when 2 then fy.tax else (0-fy.tax) end, 0))           xiaoShouFyShuiPrice';
        } else {
            $sql .= ' sale.ywsj,
sale.employer                    fyYwyId,
SUM(IFNULL(case fy.fang_xiang when 2 then fy.price_and_tax else (0-fy.price_and_tax) end, 0)) xiaoShouFySumPrice,
\'0\'                              xiaoShouFyShuiPrice';
        }
        $sql .= ' FROM capital_fy fy
                             LEFT JOIN capital_fyhx fyhx ON fyhx.`cap_fy_id` = fy.`id`
                             LEFT JOIN salesorder sale ON sale.`id` = fyhx.`data_id`
                             LEFT JOIN salesorder_details mx ON mx.order_id = sale.`id`
                    WHERE fy.delete_time is null
                      AND fy.`status` != 2
                      AND fyhx.delete_time is null
                      AND sale.delete_time is null
                      AND sale.`status` != 1
                      AND mx.delete_time is null
                      and fyhx.fyhx_type = 0
                      and fy.companyid=' . $companyId;
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and fy.yw_time >= ?';
            $sqlParams[] = $params['ywsjStart'];
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and fy.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= ' GROUP BY sale.employer
) fyt ON ywy.id = fyt.fyYwyId
WHERE ywy.companyid = ' . $companyId;
        if (!empty($params['sale_operator_id'])) {
            $sql .= ' and ywy.id = ?';
            $sqlParams[] = $params['sale_operator_id'];
        }
        if (!empty($params['hide_no_happened'])) {
            $sql .= ' and sale.xiaoShouZhongLiang > 0';
        }
        $sql .= ' GROUP BY ywy.id)';

        return Db::table($sql)->alias('t')->bind($sqlParams)->paginate($pageLimit);
    }
}