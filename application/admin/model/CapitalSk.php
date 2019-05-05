<?php


namespace app\admin\model;


use think\Db;
use think\exception\DbException;
use think\Paginator;
use traits\model\SoftDelete;

class CapitalSk extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function details()
    {
        return $this->hasMany('CapitalSkhx', 'sk_id', 'id');
    }

    public function mingxi()
    {
        return $this->hasMany('CapitalSkjsfs', 'sk_id', 'id');
    }

    public function custom()
    {
        return $this->belongsTo('Custom', 'customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['custom_name' => 'custom']);
    }

    /**
     * @param $params
     * @param int $pageLimit
     * @param $companyId
     * @return Paginator
     * @throws DbException
     */
    public function getTongjiHuizongList($params, $pageLimit, $companyId)
    {
        $ywsjStart = '';
        if (!empty($params['ywsjStart'])) {
            $ywsjStart = $params['ywsjStart'];
        }
        $ywsjEnd = '';
        if (!empty($params['ywsjEnd'])) {
            $ywsjEnd = date('Y-m-d H:i:s', strtotime($params['ywsjEnd'] . ' +1 day'));
        }
        $sqlParams = [];
        $sql = '(select t2.id           customer_id,
       t2.daima,
       t2.wanglai,
       t2.yewu_yuan,
       t2.bumen,
       t2.create_time,
       t2.benqi_yingshou,
       t2.benqi_shishou,
       t2.qichu_yue,
       t2.qimo_yue,
       t2.congying_fu,
       t2.yushoukuanyue,
       t2.yufukuanyue,
       t2.yingshouYue  yingshouYue,
       t2.qichu_yue1   qichuyingfu,
       t2.benqi_yingfu benqiyingfu,
       t2.benqi_shifu  benqishifu,
       t2.qimo_yue1    qimoyingfu
from (SELECT t1.id,
             t1.daima,
             t1.wanglai,
             t1.yewu_yuan,
             t1.bumen,
             t1.create_time,
             t1.yingshou_yue                                                                       benqi_yingshou,
             t1.shishou_jine                                                                       benqi_shishou,
             t1.qichu_yue,
             (IFNULL(t1.qichu_yue, 0) + (IFNULL(t1.yingshou_yue, 0) - IFNULL(t1.shishou_jine, 0))) qimo_yue,
             (IFNULL(t1.yf, 0) - IFNULL(t1.ys, 0))                                                 congying_fu,
             t1.yingfu_yue                                                                         benqi_yingfu,
             t1.shifu_jine                                                                         benqi_shifu,
             t1.qichu_yue1,
             (IFNULL(t1.qichu_yue1, 0) + (IFNULL(t1.yingfu_yue, 0) - IFNULL(t1.shifu_jine, 0)))    qimo_yue1,
             ((IFNULL(t1.qichu_yue, 0) + (IFNULL(t1.yingshou_yue, 0) - IFNULL(t1.shishou_jine, 0))) -
              (IFNULL(t1.qichu_yue1, 0) + (IFNULL(t1.yingfu_yue, 0) - IFNULL(t1.shifu_jine, 0))))  yingshouYue,
             t1.yushoukuanyue,
             t1.yufukuanyue
      FROM (SELECT c.id,
                   c.zjm                         daima,
                   c.custom                      wanglai,
                   c.moren_yewuyuan              yewu_yuan,
                   c.suoshu_department           bumen,
                   c.create_time,
                   (ifnull((SELECT SUM(IFNULL(mx.price_and_tax, 0))
                            FROM salesorder_details mx
                                     LEFT JOIN salesorder sale ON mx.order_id = sale.id
                                WHERE mx.delete_time is null
                                     and sale.status != 2
                                     AND sale.delete_time is null
                                     AND sale.custom_id = c.id';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and sale.ywsj >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and sale.ywsj < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= '), 0)
                       +IFNULL((SELECT SUM(IFNULL(fy.money, 0))
                                 FROM capital_fy fy
                                     WHERE fy.fang_xiang = 1
    AND fy.delete_time is null
    and fy.status != 1
    AND fy.customer_id = c.id';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and fy.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and fy.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= ' ), 0)
                       +IFNULL((SELECT SUM(IFNULL(mx.money, 0))
                                 FROM init_ysfk_mx mx
                                          LEFT JOIN init_ysfk ysfk ON mx.ysfk_id = ysfk.id
                                     WHERE ysfk.type = 0
    and ysfk.delete_time is null
    AND mx.delete_time is null
    AND mx.customer_id = c.id
    and ysfk.status != 1
    and ysfk.delete_time is null';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and ysfk.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and ysfk.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= '), 0)
                       +IFNULL((SELECT - SUM(IFNULL(mx.sum_shui_price, 0))
                                 FROM sales_return_details mx
                                          LEFT JOIN sales_return th ON mx.xs_th_id = th.id
                                     WHERE th.customer_id = c.id
    and th.delete_time is null
    and th.status != 2
    and mx.delete_time is null';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and th.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and th.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= '), 0) +(SELECT ifnull(sum(ifnull(mx.money, 0)), 0)
                                         from capital_other_details mx
                                                  LEFT JOIN capital_other qt on mx.cap_qt_id = qt.id
                                             where qt.customer_id = c.id
    and qt.fangxiang = 1
    and qt.status != 2
    and qt.delete_time is null
    and qt.yw_type != 16';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and qt.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and qt.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= ') +(SELECT ifnull(sum(ifnull(mx.money, 0)), 0)
                         from capital_other_details mx
                                  LEFT JOIN capital_other qt on mx.cap_qt_id = qt.id
                             where qt.customer_id = c.id
    and qt.fangxiang = 1
    and qt.status != 2
    and qt.delete_time is null
    and qt.yw_type = 16';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and qt.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and qt.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= '))                           yingshou_yue,
                   (SELECT ifnull(SUM(ifnull(sk.money, 0) + IFNULL(sk.msmoney, 0)), 0)
                    FROM capital_sk sk
                        WHERE sk.delete_time is null
    AND sk.status != 2
    AND sk.customer_id = c.id';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and sk.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and sk.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= ')                             shishou_jine,';
        if (!empty($params['ywsjStart'])) {
            $sql .= '((SELECT ifnull(sum(mx.price_and_tax), 0)
            FROM salesorder_details mx
                              LEFT JOIN salesorder sale ON mx.order_id = sale.id
                         WHERE mx.delete_time is null
            AND sale.delete_time is null
            and sale.status != 1
            AND sale.custom_id = c.id
            and sale.ywsj < ?)';
            $sqlParams[] = $ywsjStart;
            $sql .= ' + IFNULL((SELECT ifnull(sum(fy.money), 0)
                                  FROM capital_fy fy
                                      WHERE fy.fang_xiang = 1
            AND fy.delete_time is null
            and fy.status != 1
            AND fy.customer_id = c.id
            and fy.yw_time < ?), 0';
            $sqlParams[] = $ywsjStart;
            $sql .= ') + IFNULL((SELECT SUM(IFNULL(mx.money, 0))
                                    FROM init_ysfk_mx mx
                                             LEFT JOIN init_ysfk ysfk ON mx.ysfk_id = ysfk.id
                                        WHERE ysfk.type = 0
            and ysfk.delete_time is null
            AND mx.delete_time is null
            AND mx.customer_id = c.id
            and ysfk.status != 1
            and ysfk.yw_time < ?), 0)';
            $sqlParams[] = $ywsjStart;
            $sql .= '+ IFNULL((SELECT -SUM(IFNULL(mx.sum_shui_price, 0))
                                  FROM sales_return_details mx
                                           LEFT JOIN sales_return th ON mx.xs_th_id = th.id
                                      WHERE th.customer_id = c.id
            and th.delete_time is null
            and mx.delete_time is null
            and th.status != 1
            and th.yw_time < ?), 0)';
            $sqlParams[] = $ywsjStart;
            $sql .= '- ifnull((SELECT SUM(sk.money + IFNULL(sk.msmoney, 0))
                                 FROM capital_sk sk
                                     WHERE sk.delete_time is null
            AND sk.status != 1
            AND sk.customer_id = c.id
            and sk.yw_time < ?), 0)';
            $sqlParams[] = $ywsjStart;
            $sql .= ')                         qichu_yue,';
        } else {
            $sql .= ' 0 qichu_yue,';
        }
        $sql .= ' (ifnull((SELECT SUM(IFNULL(semx.sum_shui_price, 0))
                            FROM cg_purchase_mx semx
                                     LEFT JOIN cg_purchase se on semx.purchase_id = se.id
                                WHERE se.delete_time is null
    and semx.delete_time is null
    and se.status != 1
    and se.customer_id = c.id';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and se.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and se.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= ' ), 0)+IFNULL((SELECT SUM(IFNULL(fy.money, 0))
                                 FROM capital_fy fy
                                     WHERE fy.fang_xiang = 2
    AND fy.delete_time is null
    and fy.status != 2
    and fy.customer_id = c.id';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and fy.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and fy.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= '), 0)
                       +IFNULL((SELECT SUM(IFNULL(mx.money, 0))
                                 FROM init_ysfk_mx mx
                                          LEFT JOIN init_ysfk ysfk ON mx.ysfk_id = ysfk.id
                                     WHERE ysfk.type = 1
    AND mx.delete_time is null
    AND mx.customer_id = c.id
    and ysfk.status != 1
    and ysfk.delete_time is null';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and ysfk.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and ysfk.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= '), 0)
                       +IFNULL((SELECT - SUM(IFNULL(mx.sum_shui_price, 0))
                                 FROM cg_th_mx mx
                                          LEFT JOIN cg_th th ON mx.cg_th_id = th.id
                                     WHERE th.customer_id = c.id
    and th.delete_time is null
    and th.status != 1
    and mx.delete_time is null';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and th.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and th.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= '), 0) +ifnull((SELECT sum(ifnull(qt.money, 0))
                                 FROM capital_other qt
                                     WHERE qt.customer_id = c.id
    and qt.fangxiang = 2
    and qt.delete_time is null
    and qt.status != 2';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and qt.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and qt.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= '), 0))           yf,
                   (IFNULL((SELECT SUM(fk.money + IFNULL(fk.mfmoney, 0))
                            FROM capital_fk fk
                                WHERE fk.customer_id = c.id
    and fk.status != 2
    and fk.delete_time is null
                           ), 0))                ys,
                   (ifnull((SELECT ifnull(SUM(IFNULL(mx.sum_shui_price, 0)), 0)
                            FROM cg_purchase_mx mx
                                     LEFT JOIN cg_purchase se ON mx.purchase_id = se.id
                                WHERE mx.delete_time is null
    AND se.delete_time is null
    AND se.customer_id = c.id
    and se.status != 1';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and se.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and se.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= ' ), 0)
                       +IFNULL((SELECT ifnull(SUM(IFNULL(fy.money, 0)), 0)
                                 FROM capital_fy fy
                                     WHERE fy.fang_xiang = 2
    AND fy.delete_time is null
    and fy.status != 2
    AND fy.customer_id = c.id';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and fy.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and fy.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= '), 0)
                       +IFNULL((SELECT ifnull(SUM(IFNULL(mx.money, 0)), 0)
                                 FROM init_ysfk_mx mx
                                          LEFT JOIN init_ysfk ysfk ON mx.ysfk_id = ysfk.id
                                     WHERE ysfk.type = 1
    AND ysfk.delete_time is null
    and mx.delete_time is null
    AND mx.customer_id = c.id
    and ysfk.status != 1';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and ysfk.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and ysfk.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= '), 0)
                       +IFNULL((SELECT - ifnull(SUM(IFNULL(mx.sum_shui_price, 0)), 0)
                                 FROM cg_th_mx mx
                                          LEFT JOIN cg_th th ON mx.cg_th_id = th.id
                                     WHERE th.customer_id = c.id
    and th.delete_time is null
    and th.status != 1
    and mx.delete_time is null';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and th.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and th.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= '), 0)
                       +(SELECT ifnull(sum(ifnull(mx.money, 0)), 0)
                          from capital_other_details mx
                                   LEFT JOIN capital_other qt on mx.cap_qt_id = qt.id
                              where qt.customer_id = c.id
    and qt.fangxiang = 2
    and qt.status != 2
    and mx.delete_time is null
    and qt.delete_time is null';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and qt.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and qt.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= '))                           yingfu_yue,
                   (SELECT ifnull(SUM(ifnull(fk.money, 0) + IFNULL(fk.mfmoney, 0)), 0)
                    FROM capital_fk fk
                        WHERE fk.delete_time is null
    AND fk.status != 2
    AND fk.customer_id = c.id';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and fk.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and fk.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        $sql .= ' )                             shifu_jine,';
        if (!empty($params['ywsjStart'])) {
            $sql .= ' ((ifnull((SELECT SUM(IFNULL(mx.sum_shui_price, 0))
                             FROM cg_purchase_mx mx
                                      LEFT JOIN cg_purchase pur ON mx.purchase_id = pur.id
                                 WHERE mx.delete_time is null
                                      AND pur.delete_time is null
                                      AND pur.customer_id = c.id
                                      and pur.status != 1
                                      and pur.yw_time < ?), 0)';
            $sqlParams[] = $ywsjStart;
            $sql .= '+ IFNULL((SELECT SUM(IFNULL(fy.money, 0))
                                 FROM capital_fy fy
                                     WHERE fy.fang_xiang = 2
                                          AND fy.delete_time is null
                                          and fy.status != 1
                                          AND fy.customer_id = c.id
                                          and fy.yw_time < ?), 0)';
            $sqlParams[] = $ywsjStart;
            $sql .= '+ IFNULL((SELECT SUM(IFNULL(mx.money, 0))
                                 FROM init_ysfk_mx mx
                                          LEFT JOIN init_ysfk ysfk ON mx.ysfk_id = ysfk.id
                                     WHERE ysfk.type = 1
                                          AND ysfk.delete_time is null
                                          and mx.delete_time is null
                                          AND mx.customer_id = c.id
                                          and ysfk.status != 1
                                          and ysfk.yw_time < ?), 0))';
            $sqlParams[] = $ywsjStart;
            $sql .= '+ IFNULL((SELECT -SUM(IFNULL(mx.sum_shui_price, 0))
                                  FROM cg_th_mx mx
                                           LEFT JOIN cg_th th ON mx.cg_th_id = th.id
                                      WHERE th.customer_id = c.id
                                           and th.is_delete = 0
                                           and th.status != 1
                                           and mx.is_delete = 0
                                           AND th.yw_time < ?), 0)';
            $sqlParams[] = $ywsjStart;
            $sql .= '- ifnull((SELECT SUM(fk.money + IFNULL(fk.mfmoney, 0))
                                 FROM capital_fk fk
                                     WHERE fk.delete_time is null
                                          AND fk.status != 1
                                          AND fk.customer_id = c.id
                                          and fk.yw_time < ?), 0)';
            $sqlParams[] = $ywsjStart;
            $sql .= ')                         qichu_yue1,';
        } else {
            $sql .= ' 0 qichu_yue1,';
        }
        $sql .= '(SELECT IFNULL(SUM(IFNULL(sk.money, 0)), 0) -
    (SELECT - IFNULL(SUM(IFNULL(sk1.money, 0)), 0)
                            FROM capital_sk sk1
                                WHERE sk1.delete_time is null
    AND sk1.status = 1
    AND sk1.sk_type = 4
    AND c.id = sk1.customer_id)
                    FROM capital_sk sk
                        WHERE sk.`customer_id` = c.id
    AND sk.sk_type = 3
    AND sk.delete_time is null
    AND sk.status = 0
                   )                             yushoukuanyue,
                   (SELECT IFNULL(SUM(IFNULL(fk.money, 0)), 0) -
    (SELECT - IFNULL(SUM(IFNULL(fk.money, 0)), 0)
                            FROM capital_fk fk1
                                WHERE fk1.delete_time is null
    AND fk1.status != 2
    AND fk1.fk_type = 4
    AND fk1.customer_id = c.id)
                    FROM capital_fk fk
                        WHERE fk.customer_id = c.id
    AND fk.fk_type = 2
    AND fk.status != 2) yufukuanyue
            FROM custom c
                where c.delete_time is null
    and c.iscustom = 1
    and c.companyid=' . $companyId . '
           ) t1
     ) t2
    where 1 = 1';
        if (!empty($params['hide_zero'])) {
            $sql .= ' and t2.yingshouYue != 0';
        }
        if (!empty($params['hide_no_happen'])) {
            $sql .= ' and t2.benqi_yingshou > 0';
        }
        if (!empty($params['customer_id'])) {
            $sql .= ' and t2.id = ?';
            $sqlParams[] = $params['customer_id'];
        }
        if (!empty($params['group_id'])) {
            $sql .= ' t2.bumen = ?';
            $sqlParams[] = $params['group_id'];
        }
        $sql .= ' )';
        $data = Db::table($sql)->alias('t')->bind($sqlParams)->order('create_time', 'desc')->paginate($pageLimit);
        return $data;

#                 <if test="param.yewuyuan!=null and param.yewuyuan != \'\'"> and
#             t2.yewu_yuan like concat (\'%\',#{param.yewuyuan},\'%\')
#                 </if>
    }

    /**
     * @param $customer_id
     * @param $params
     * @param int $pageLimit
     * @param $companyId
     * @return Paginator
     * @throws DbException
     */
    function getTongjiMxList($customer_id, $params, $pageLimit, $companyId)
    {
        $ywsjStart = '';
        if (!empty($params['ywsjStart'])) {
            $ywsjStart = $params['ywsjStart'];
        }
        $ywsjEnd = '';
        if (!empty($params['ywsjEnd'])) {
            $ywsjEnd = $params['ywsjEnd'];
        }
        $sqlParams = [];
        $sql = '(select null          id,
       \'\'            status,
       null          yw_time,
       basecu.custom wanglai,
       \'期初\'          danju_leixing,
       null          bu_men,
       null          yewu_yuan,
       null          bian_hao,
       \'0.00\'        yingshou_jine,
       \'0.00\'        shishou_jine,
       ((SELECT IFNULL(SUM(mx.price_and_tax), 0)
         FROM salesorder_details mx
                  LEFT JOIN salesorder sale ON mx.order_id = sale.id
         WHERE mx.delete_time is null
           AND sale.delete_time is null
           AND sale.status != 2
           AND sale.custom_id = ?
           AND sale.ywsj <= ?';
        $sqlParams[] = $customer_id;
        $sqlParams[] = $ywsjStart;
        $sql .= ') + IFNULL((SELECT IFNULL(SUM(fy.money), 0)
                    FROM capital_fy fy
                    WHERE fy.fang_xiang = 1
                      AND fy.delete_time is null
                      AND fy.status != 2
                      AND fy.customer_id = ?
                      AND fy.yw_time <= ?), 0) +';
        $sqlParams[] = $customer_id;
        $sqlParams[] = $ywsjStart;
        $sql .= 'IFNULL((SELECT SUM(IFNULL(mx.money, 0))
                FROM init_ysfk_mx mx
                         LEFT JOIN init_ysfk ysfk ON mx.ysfk_id = ysfk.id
                WHERE ysfk.type = 0
                  and ysfk.delete_time is null
                  AND mx.delete_time is null
                  AND mx.customer_id = ?
                  AND ysfk.status != 1
                  AND ysfk.yw_time <= ?), 0) + IFNULL(';
        $sqlParams[] = $customer_id;
        $sqlParams[] = $ywsjStart;
        $sql .= '(SELECT - SUM(IFNULL(mx.sum_shui_price, 0))
                 FROM sales_return_details mx
                          LEFT JOIN sales_return th ON mx.xs_th_id = th.id
                 WHERE th.customer_id = ?
                   AND th.delete_time is null
                   AND th.status != 1
                   AND th.yw_time <= ?), 0) -';
        $sqlParams[] = $customer_id;
        $sqlParams[] = $ywsjStart;
        $sql .= 'IFNULL((SELECT SUM(sk.money + IFNULL(sk.msmoney, 0))
                FROM capital_sk sk
                WHERE sk.delete_time is null
                  AND sk.status != 2
                  AND sk.customer_id = ?
                  AND sk.yw_time <= ?), 0)';
        $sqlParams[] = $customer_id;
        $sqlParams[] = $ywsjStart;
        $sql .= ')         yue,
       basecu.id     customer_id,
       null          beizhu,
       \'\'            signPerson
FROM custom basecu
where basecu.companyid= ' . $companyId . ' 
and basecu.id = ?';
        $sqlParams[] = $customer_id;
        $sql .= ' GROUP BY basecu.`id`
    union all
    select t3.id
    , t3.status
    , t3.yw_time
    , t3.wanglai
    , t3.danju_leixing
    , t3.bu_men
    , t3.yewu_yuan
    , t3.bian_hao
    , t3.yingshou_jine yingshou_jine
    , t3.shishou_jine shishou_jine
    , t3.yue
    , t3.customer_id
    , t3.beizhu
    , t3.signPerson from (
    select t2.id
         , t2.status
         , t2.yw_time
         , t2.wanglai
         , t2.danju_leixing
         , t2.bu_men
         , t2.yewu_yuan
         , t2.bian_hao
         , t2.yingshou_jine yingshou_jine
         , t2.shishou_jine  shishou_jine
         , t2.yue
         , t2.customer_id
         , t2.beizhu
         , t2.signPerson
    from (select t1.id
               , t1.status
               , t1.yw_time
               , t1.wanglai
               , t1.danju_leixing
               , t1.bu_men
               , t1.yewu_yuan
               , t1.bian_hao
               , sum(t1.yingshou_jine) yingshou_jine
               , sum(t1.shishou_jine)  shishou_jine
               , sum(t1.yue)           yue
               , t1.customer_id
               , t1.beizhu
               , t1.signPerson
          from (SELECT sale.id
                     , sale.`status`
                     , sale.ywsj         yw_time
                     , cus.custom        wanglai
                     , \'销售单\'             danju_leixing
                     , sale.department   bu_men
                     , op.name           yewu_yuan
                     , sale.system_no    bian_hao
                     , mx.price_and_tax  yingshou_jine
                     , null              shishou_jine
                     , null              yue
                     , sale.custom_id as customer_id
                     , sale.remark    as beizhu
                     , \'\'                signPerson
                FROM salesorder sale
                         LEFT JOIN salesorder_details mx on mx.order_id = sale.id
                         LEFT JOIN custom cus on sale.custom_id = cus.id
                         LEFT JOIN admin op on sale.employer = op.id
                where mx.delete_time is null
                  and sale.delete_time is null
                  and sale.companyid=' . $companyId . '
                    union all
                    SELECT qt.id
                    , qt.status
                    , qt.yw_time
                    , cus.custom wang_lai
                    , ywtype.name danju_leixing
                    , qt.group_id bumen
                    , op.name yewu_yuan
                    , qt.system_number bian_hao
                    , qtmx.money yingfu_jine
                    , NULL shifu_jine
                    , null yue
                    , qt.customer_id
                    , qt.beizhu beizhu
                    , \'\' signPerson
                    from capital_other_details qtmx
                    LEFT JOIN capital_other qt on qtmx.cap_qt_id = qt.id
                    LEFT JOIN custom cus ON qt.customer_id = cus.id
                    LEFT JOIN admin op ON qt.sale_operator_id = op.id
                    LEFT JOIN capital_yw_type ywtype ON qt.`yw_type` = ywtype.`id`
                    WHERE qt.fangxiang = 1
                  AND qt.`yw_type` = 16
                  AND qt.`money` > 0
                  and qt.companyid=' . $companyId . '
                    union all
                    SELECT sk.id
                    , sk.`status`
                    , sk.yw_time yw_time
                    , cus.custom wanglai
                    , \'收款单\' danju_leixing
                    , sk.group_id bu_men
                    , op.name yewu_yuan
                    , sk.system_number bian_hao
                    , null yingshou_jine
                    , ifnull(sk.money, 0) shishou_jine
                    , null yue
                    , sk.customer_id
                    , sk.beizhu
                    , \'\' signPerson
                    from capital_sk sk
                    LEFT JOIN custom cus on sk.customer_id = cus.id
                    LEFT JOIN admin op on sk.sale_operator_id = op.id
                    where sk.delete_time is null
                    and sk.companyid=' . $companyId . '
                    union ALL
                    SELECT th.id
                    , th.`status`
                    , th.yw_time yw_time
                    , cus.custom wanglai
                    , \'销售退货单\' danju_leixing
                    , th.group_id bu_men
                    , op.name yewu_yuan
                    , th.system_number bian_hao
                    , -mx.sum_shui_price yingfu_jine
                    , null shifu_jine
                    , null yue
                    , th.customer_id
                    , th.beizhu
                    , \'\' signPerson
                    FROM sales_return th
                    LEFT JOIN sales_return_details mx ON mx.xs_th_id = th.id
                    LEFT JOIN custom cus on th.customer_id = cus.id
                    LEFT JOIN admin op on th.sale_operator_id = op.id
                    WHERE mx.delete_time is null
                  and th.delete_time is null
                  and th.companyid=' . $companyId . '
                    UNION all
                    SELECT fy.id
                    , fy.`status`
                    , fy.yw_time yw_time
                    , cus.custom wanglai
                    , \'费用单(收款)\' danju_leixing
                    , fy.group_id bu_men
                    , op.name yewu_yuan
                    , fy.system_number bian_hao
                    , fy.money yingshou_jine
                    , null shishou_jine
                    , null yue
                    , fy.customer_id
                    , fy.beizhu
                    , \'\' signPerson
                    from capital_fy fy
                    LEFT JOIN custom cus on fy.customer_id = cus.id
                    LEFT JOIN admin op on fy.sale_operator_id = op.id
                    where fy.fang_xiang = 1
                  and fy.delete_time is null
                  and fy.companyid=' . $companyId . '
                    union all
                    SELECT qt.id
                    , qt.`status`
                    , qt.yw_time
                    , cus.custom wang_lai
                    , \'其它应收款\' danju_leixing
                    , qt.group_id bumen
                    , op.name yewu_yuan
                    , qt.system_number bian_hao
                    , mx.money yingfu_jine
                    , null shifu_jine
                    , null yue
                    , qt.customer_id
                    , qt.beizhu
                    , \'\' signPerson
                    from capital_other_details mx
                    LEFT JOIN capital_other qt on mx.cap_qt_id = qt.id
                    LEFT JOIN custom cus on qt.customer_id = cus.id
                    LEFT JOIN admin op on qt.sale_operator_id = op.id
                    where qt.fangxiang = 1
                  and mx.delete_time is null
                  and qt.delete_time is null
                  and qt.yw_type != 16
                  and qt.companyid=' . $companyId . '
               ) t1
          GROUP BY t1.id
          union all
          SELECT sk.id
               , sk.`status`
               , sk.yw_time            yw_time
               , cus.custom            wanglai
               , \'收款单\'                 danju_leixing
               , sk.group_id           bu_men
               , op.name               yewu_yuan
               , sk.system_number      bian_hao
               , null                  yingshou_jine
               , ifnull(sk.msmoney, 0) shishou_jine
               , null                  yue
               , sk.customer_id
               , \'收款优惠，红字冲减应收款\'        beizhu
               , \'\'                    signPerson
          from capital_sk sk
                   LEFT JOIN custom cus on sk.customer_id = cus.id
                   LEFT JOIN admin op on sk.sale_operator_id = op.id
          where sk.delete_time is null
            and sk.msmoney != 0
            and sk.companyid=' . $companyId . '
              UNION ALL
              SELECT ysfk.id
              , ysfk.`status`
              , ysfk.yw_time yw_time
              , cus.custom wanglai
              , \'期初应收\' danju_leixing
              , ysfk.group_id bu_men
              , op.name yewu_yuan
              , ysfk.system_number bian_hao
              , sum(mx.money) yingshou_jine
              , null shishou_jine
              , null yue
              , mx.customer_id
              , ysfk.beizhu
              , \'\' signPerson
              FROM init_ysfk ysfk
              LEFT JOIN init_ysfk_mx mx on mx.ysfk_id = ysfk.id
              LEFT JOIN custom cus on mx.customer_id = cus.id
              LEFT JOIN admin op on ysfk.sale_operator_id = op.id
              WHERE ysfk.type = 0
            and mx.delete_time is null
            and ysfk.delete_time is null
            and ysfk.companyid=' . $companyId . '
            and mx.customer_id = ?';
        $sqlParams[] = $customer_id;
        $sql .= ' GROUP BY mx.customer_id
              , ysfk.id
         ) t2
    where 1 = 1';
        if (!empty($params['customer_id'])) {
            $sql .= ' and t2.customer_id= ?';
            $sqlParams[] = $params['customer_id'];
        }
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and t2.yw_time >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and t2.yw_time < ?';
            $sqlParams[] = $ywsjEnd;
        }
        if (!empty($params['status'])) {
            $sql .= ' and t2.status = ?';
            $sqlParams[] = $params['status'];
        }
        if (!empty($params['djlx'])) {
            $sql .= ' and t2.danju_leixing like ?';
            $sqlParams[] = '%' . $params['djlx'] . '%';
        }
        if (!empty($params['group_id'])) {
            $sql .= ' and t2.bu_men = ?';
            $sqlParams[] = $params['group_id'];
        }
        if (!empty($params['yewuyuan'])) {
            $sql .= ' and t2.yewu_yuan like ?';
            $sqlParams[] = '%' . $params['yewuyuan'] . '%';
        }
        if (!empty($params['system_no'])) {
            $sql .= ' and t2.bian_hao like ?';
            $sqlParams[] = '%' . $params['system_no'] . '%';
        }
        $sql .= ') t3)';
        $data = Db::table($sql)->alias('t')->bind($sqlParams)->order('yw_time')->paginate($pageLimit);
        return $data;
    }
}