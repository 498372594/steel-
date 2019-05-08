<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use think\Db;
use think\Exception;
use think\Session;

class Reportform extends Right
{
    /**
     * 采购单列表admin/purchase/getpurchaselist
     */

    /**
     * 采购单明细admin/purchase/getpurchaselist
     */
    public function getpurchasemx( $pageLimit = 10){
        $params = request()->param();
        $list = \app\admin\model\ViewCgPurchaseMx::where('companyid', $this->getCompanyId());

        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['yw_time'])) {
            $list->where('service_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['storage_id'])) {
            $list->where('storage_id', $params['storage_id']);
        }
        if (!empty($params['status'])) {
            $list->where('status', $params['status']);
        }
        if (!empty($params['ruku_fangshi_id'])) {
            $list->where('ruku_fangshi_id', $params['ruku_fangshi_id']);
        }
        if (!empty($params['jsfs'])) {
            $list->where('jsfs', $params['jsfs']);
        }
        if (!empty($params['supplier_id'])) {
            $list->where('supplier_id', $params['supplier_id']);
        }
        if (!empty($params['pjlx'])) {
            $list->where('pjlx', $params['pjlx']);
        }
        if (!empty($params['system_no'])) {
            $list->where('system_no', 'like', '%' . $params['system_no'] . '%');
        }
        if (!empty($params['ywlx'])) {
            $list->where('ywlx', $params['ywlx']);
        }
        if (!empty($params['shdw_id'])) {
            $list->where('shdw_id', $params['shdw_id']);
        }
        if (!empty($params['ysdw_id'])) {
            $list->where('ysdw_id', $params['ysdw_id']);
        }
        if (!empty($params['remark'])) {
            $list->where('remark', $params['remark']);
        }
        $list = $list->paginate($pageLimit);
        return returnSuc($list);
    }
    /**
     * 采购磅差统计
     */
    public function getbangcha($pageLimit=10){
      $param=request()->param();
      $sqlParams=[];
      $sql="(SELECT
		mx.id,
		st.`storage` cangku,
		cus.`custom` wanglai,
		pm.`name` pin_ming,
		gg.specification gui_ge,
		se.system_number,
		cz.`texturename` cai_zhi,
		cd.originarea chan_di,
		mx.houdu hou_du,
		mx.kuandu kuan_du,
		pjlx.pjlx piaoju_name,
		mx.changdu chang_du,
		se.status,
		jjfs.`jsfs` jijia_fangshi,
		mx.lingzhi dinghuo_lingzhi,
		mx.jianshu dinghuo_jianshu,
		mx.counts dinghuo_shuliang,
		IFNULL(rkmx.counts, 0) daohuo_shuliang,
		mx.zhongliang dinghuo_zhongliang,
		IFNULL(rkmx.zhongliang, 0) daohuo_zhongliang,
		mx.price dan_jia,
		(
		IFNULL(mx.zhongliang,0) - IFNULL(rkmx.zhongliang, 0)
		) bangcha_zhongliang,
		case when (IFNULL(mx.zhongliang,0) - IFNULL(rkmx.zhongliang, 0))>0 then '亏磅'
			 when (IFNULL(mx.zhongliang,0) - IFNULL(rkmx.zhongliang, 0))=0 then '平磅'
			 when (IFNULL(mx.zhongliang,0) - IFNULL(rkmx.zhongliang, 0))<0 then '涨磅'
	    end bangchaFangxiang,
		(
		    (
		      IFNULL(mx.zhongliang,0) - IFNULL(rkmx.zhongliang, 0)
		    ) * IFNULL(mx.price,0)
		  ) bangcha_jiashuiheji,
		  (
		    (
		      IFNULL(mx.zhongliang,0) - IFNULL(rkmx.zhongliang, 0)
		    ) *IFNULL(rkmx.`price`,0)/(1+mx.shui_price/100)* mx.shui_price/100
		  ) bangcha_shuie,
		  (
		    (
		      IFNULL(mx.zhongliang,0) - IFNULL(rkmx.zhongliang, 0)
		    ) * IFNULL(mx.price,0) - (
		      IFNULL(mx.zhongliang,0) - IFNULL(rkmx.zhongliang, 0)
		    ) *IFNULL(rkmx.`price`,0)/(1+mx.shui_price/100)* mx.shui_price/100
		  ) bangcha_jine,
		mx.zhijian zhi_jian,
		mx.beizhu,mx.companyid,mx.delete_time,
			`mx`.`pinming_id` AS `pinming_id`,
	`mx`.`guige_id` AS `guige_id`,
	`mx`.`caizhi_id` AS `caizhi_id`,
	`mx`.`chandi_id` AS `chandi_id`,
	mx.jijiafangshi_id,se.customer_id
		FROM
		cg_purchase_mx mx
		LEFT JOIN `storage` st
		ON mx.store_id = st.id
		LEFT JOIN cg_purchase se
		ON mx.purchase_id = se.id
		LEFT JOIN custom cus
		ON se.customer_id = cus.id
		LEFT JOIN specification gg
		ON mx.guige_id = gg.id
		LEFT JOIN pjlx pjlx
		ON se.piaoju_id = pjlx.id
		LEFT JOIN productname pm
		ON mx.pinming_id = pm.id
		LEFT JOIN texture cz
		ON mx.caizhi_id = cz.id
		LEFT JOIN originarea cd
		ON mx.chandi_id = cd.id
		LEFT JOIN jsfs jjfs
		ON mx.jijiafangshi_id = jjfs.id
		LEFT JOIN kc_rk_mx rkmx
		ON rkmx.data_id = mx.id
		left join kc_rk rk on rk.id=rkmx.kc_rk_id
		WHERE  rk.status !=1  and mx.companyid=".$this->getCompanyId()."
		AND (
		mx.zhongliang - IFNULL(rkmx.zhongliang, 0)
		) != 0
		AND rkmx.data_id = mx.id

";
        if (!empty($param['ywsjStart'])) {
            $sql .= ' and mx.ywsj >=:ywsjStart';
            $sqlParams['ywsjStart'] = $param['ywsjStart'];
        }
        if (!empty($param['ywsjEnd'])) {
            $sql .= ' and mx.ywsj < :ywsjEnd';
            $sqlParams['ywsjEnd'] = strtotime('Y-m-d H:i:s', strtotime($param['ywsjEnd'] . ' +1 day'));
        }
        if (!empty($param['store_id'])) {
            $sql .= ' and mx.store_id =:store_id';
            $sqlParams['store_id'] = $param['store_id'];
        }
        if (!empty($param['piaoju_id'])) {
            $sql .= ' and mx.piaoju_id =:piaoju_id';
            $sqlParams['piaoju_id'] = $param['piaoju_id'];
        }
        if (!empty($param['jijiafangshi_id'])) {
            $sql .= ' and mx.jijiafangshi_id =:jijiafangshi_id';
            $sqlParams['jijiafangshi_id'] = $param['jijiafangshi_id'];
        }
        if (!empty($param['caizhi_id'])) {
            $sql .= ' and mx.caizhi_id =:caizhi_id';
            $sqlParams['caizhi_id'] = $param['caizhi_id'];
        }
        if (!empty($param['chandi_id'])) {
            $sql .= ' and mx.chandi_id =:chandi_id';
            $sqlParams['chandi_id'] = $param['chandi_id'];
        }
        if (!empty($param['status'])) {
            $sql .= ' and mx.status =:status';
            $sqlParams['status'] = $param['status'];
        }
        if (!empty($param['system_number'])) {
            $sql .= ' and mx.system_number =:system_number';
            $sqlParams['system_number'] = $param['system_number'];
        }
        if (!empty($param['customer_id'])) {
            $sql .= ' and se.customer_id =:customer_id';
            $sqlParams['customer_id'] = $param['customer_id'];
        }
        if (!empty($param['system_number'])) {
            $sql .= ' and mx.system_number =:system_number';
            $sqlParams['system_number'] = $param['system_number'];
        }
        if (!empty($param['pin_ming'])) {
            $sql .= ' and mx.pin_ming =:pin_ming';
            $sqlParams['pin_ming'] = $param['pin_ming'];
        }
        if (!empty($param['gui_ge'])) {
            $sql .= ' and gg.specification =:gui_ge';
            $sqlParams['gui_ge'] = $param['gui_ge'];
        }
        if (!empty($param['beizhu'])) {
            $sql .= ' and mx.beizhu =:beizhu';
            $sqlParams['beizhu'] = $param['beizhu'];
        }
        $sql.=")";
        $data = Db::table($sql)->alias('t')->bind($sqlParams)->paginate($pageLimit);
        return returnSuc($data);
    }

    /**入库成本明细表
     * @param int $pageLimit
     * @return \think\response\Json
     */

    public function rkcbmx($pageLimit=10){
        $params = request()->param();
        $list = \app\admin\model\ViewRkcbmx::where('companyid', $this->getCompanyId());
       $list=$this->getsearchcondition($params,$list);
        $list = $list->paginate($pageLimit);
        return returnSuc($list);
    }

    /**出入库对照表
     * @param int $pageLimit
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function inandoutlist($pageLimit = 10){
        $params=request()->param();
        $ywsjStart = '';
        if (!empty($params['ywsjStart'])) {
            $ywsjStart = $params['ywsjStart'];
        }
        $ywsjEnd = '';
        if (!empty($params['ywsjEnd'])) {
            $ywsjEnd = $params['ywsjEnd'];
        }
        $sqlParams = [];
        $sql="(
            SELECT
			 t.osId,
			 t.id tzid,
			 t.zbid,
			 t.systemNumber,
			 t.resourceNumber,
			 t.crkType,
			 t.crkTypeName,
			 t.ywTime,
			 t.customerName,
			 t.cate,
			 t.pinmingId,
			 t.pinmingName,
			 t.guigeId,
			 t.guigeName,
			 t.houdu,
			 t.changdu,
			 t.kuandu,
			 t.caizhiId,
			 t.caizhiName,
			 t.chandiId,
			 t.chandiName,
			 t.jijiafangshiId,
			 t.jijiafangshiName,
			 t.rklingzhi,
			 t.rkjianshu,
			 t.rkcounts,
			 t.rkzhongliang,
			 t.rkLisuanZhongliang,
			 t.rkGuobangZhongliang,
			 t.lingzhi,
			 t.jianshu,
			 t.counts,
			 t.zhongliang,
			 t.lisuanZhongliang,
			 t.guobangZhongliang,
			 t.zhijian,
			 t.pihao,
			 t.storeId,
			 t.storeName,
			 t.zhidanren,
			 t.isFlag,

			 t.rkmizhong,
			 t.mizhong,
			 t.shuiprice,
			 t.price,
			 t.jianzhong,
			 t.rkJianzhong,
			 t.rkZhijian,
			 t.rkPrice,
			 t.rkShuiPrice

FROM
		 (SELECT
						 (
								 CASE
									 WHEN rkmd.ruku_type = 1
													 THEN
										 (SELECT
														 dbmx.`diaobo_id`
											FROM
													 kc_diaobo_mx dbmx
											WHERE dbmx.`id` = spot.data_id
											LIMIT 1)
									 WHEN rkmd.ruku_type = 2
													 THEN
										 (SELECT
														 pdmx.`pandian_id`
											FROM
													 kc_pandian_mx pdmx
											WHERE pdmx.`id` = spot.data_id
											LIMIT 1)
									 WHEN rkmd.ruku_type = 3
													 THEN
										 (SELECT
														 qtmx.kc_rk_qt_id
											FROM
													 kc_qtrk_mx qtmx
											WHERE qtmx.id = spot.data_id
											LIMIT 1)
									 WHEN rkmd.ruku_type =4
													 THEN
										 (SELECT
														 cgmx.purchase_id
											FROM
													 cg_purchase_mx cgmx
											WHERE cgmx.id = spot.data_id
											LIMIT 1)
									 WHEN rkmd.ruku_type =7
													 THEN
										 (SELECT
														 thmx.`xs_th_id`
											FROM
													 sales_return_details thmx
											WHERE thmx.`id` = spot.data_id
											LIMIT 1)
									 WHEN rkmd.ruku_type = 8
													 THEN
										 (SELECT
														 kcmx.`kc_id`
											FROM
													 `init_kc_mx` kcmx
											WHERE kcmx.`id` = spot.data_id
											LIMIT 1)


									 ELSE NULL
										 END
								 ) osId,
						 rk.id id,
						 spot.`id` zbid,
						 (
								 CASE
									 WHEN rkmd.ruku_type = 1
													 THEN
										 (SELECT
														 db.system_number
											FROM
													 kc_diaobo_mx dbmx
														 LEFT JOIN kc_diaobo db
															 ON db.id = dbmx.diaobo_id
											WHERE dbmx.`id` = spot.data_id
											LIMIT 1)
									 WHEN rkmd.ruku_type = 2
													 THEN
										 (SELECT
														 pd.system_number
											FROM
													 kc_pandian_mx pdmx
														 LEFT JOIN kc_pandian pd
															 ON pd.id = pandian_id
											WHERE pdmx.`id` = spot.data_id
											LIMIT 1)
									 WHEN rkmd.ruku_type = 3
													 THEN
										 (SELECT
														 qtrk.system_number
											FROM
													 kc_qtrk_mx qtmx
														 LEFT JOIN kc_qtrk qtrk
															 ON qtrk.id = qtmx.kc_rk_qt_id
											WHERE qtmx.id = spot.data_id
											LIMIT 1)
									 WHEN rkmd.ruku_type =4
													 THEN
										 (SELECT
														 pu.system_number
											FROM
													 cg_purchase_mx cgmx
														 LEFT JOIN cg_purchase pu
															 ON pu.id = cgmx.purchase_id
											WHERE cgmx.id = spot.data_id
											LIMIT 1)
									 WHEN rkmd.ruku_type =7
													 THEN
										 (SELECT
														 th.system_number
											FROM
													 sales_return_details thmx
														 LEFT JOIN sales_return th
															 ON th.id = thmx.xs_th_id
											WHERE thmx.`id` = spot.data_id
											LIMIT 1)
									 WHEN rkmd.ruku_type =8
													 THEN
										 (SELECT
														 kc.system_number
											FROM
													 `init_kc_mx` kcmx
														 LEFT JOIN init_kc kc
															 ON kc.id = kcmx.kc_id
											WHERE kcmx.`id` = spot.data_id
											LIMIT 1)

									 ELSE NULL
										 END
								 ) systemNumber,
						 spot.`resource_number` resourceNumber,
						 rktype.`id` crkType,
						 rktype.`name` crkTypeName,
						 rk.`yw_time` ywTime,
						 cu.`custom` customerName,
						 cate.classname cate,
						 pm.`id` pinmingId,
						 pm.`name` pinmingName,
						 gg.`id` guigeId,
						 gg.`specification` guigeName,
						 spot.`houdu` houdu,
						 spot.`changdu` changdu,
						 spot.`kuandu` kuandu,
						 cz.`id` caizhiId,
						 cz.`texturename` caizhiName,
						 cd.`id` chandiId,
						 cd.`originarea` chandiName,
						 jjfs.`id` jijiafangshiId,
						 jjfs.`jsfs` jijiafangshiName,
						 rkmd.`lingzhi` rklingzhi,
						 rkmd.`jianshu` rkjianshu,
						 rkmd.`counts` rkcounts,
						 rkmd.`zhongliang` rkzhongliang,
						 spot.`old_lisuan_zhongliang` rkLisuanZhongliang,
						 spot.`old_guobang_zhongliang` rkGuobangZhongliang,
						 '' lingzhi,
						 '' jianshu,
						 '' counts,
						 '' zhongliang,
						 '' lisuanZhongliang,
						 '' guobangZhongliang,
						 rkmd.`zhijian` zhijian,
						 rkmd.`pihao` pihao,
						 store.`id` storeId,
						 store.`storage` storeName,
						 sys.`name` zhidanren,
						 '1' isFlag,

						 rkmd.mizhong rkmizhong,
						 '' mizhong,
						 '' shuiprice,
						 '' price,
						 '' jianzhong,
						 rkmd.jianzhong rkJianzhong,
						 rkmd.zhijian rkZhijian,
						 rkmd.price rkPrice,
						 rkmd.shuiprice rkShuiPrice

			FROM
					 kc_spot spot
						 LEFT JOIN kc_rk_md rkmd
							 ON spot.`rk_md_id` = rkmd.`id`
						 LEFT JOIN kc_rk_type rktype
							 ON rktype.`id` = rkmd.`ruku_type`
						 LEFT JOIN kc_rk rk
							 ON rk.`id` = rkmd.`kc_rk_id`
						 LEFT JOIN custom cu
							 ON cu.`id` = spot.`customer_id`
						 LEFT JOIN specification gg
							 ON gg.`id` = rkmd.`guige_id`
						 LEFT JOIN  productname pm
							 ON pm.`id` = gg.`productname_id`
						 LEFT JOIN classname cate
							 ON cate.`id` = pm.`classid`
						 LEFT JOIN texture cz
							 ON cz.`id` = rkmd.`caizhi_id`
						 LEFT JOIN originarea cd
							 ON cd.`id` = rkmd.`chandi_id`
						 LEFT JOIN jsfs jjfs
							 ON jjfs.`id` = rkmd.`jijiafangshi_id`
						 LEFT JOIN storage store
							 ON store.`id` = rkmd.`store_id`
						 LEFT JOIN admin sys
							 ON sys.`id` = rk.`create_operator_id`
			WHERE rkmd.delete_time is null and rk.delete_time is null and rk.status!=1 and spot.companyid=".$this->getCompanyId()."
			UNION
			ALL
			SELECT
						 (
								 CASE
									 WHEN ckmd.`chuku_type` = 1
													 THEN
										 (SELECT
														 dbmx.`diaobo_id`
											FROM
													 kc_diaobo_mx dbmx
											WHERE dbmx.id = ckmd.`data_id`
											LIMIT 1)
									 WHEN ckmd.chuku_type = 2
													 THEN
										 (SELECT
														 pdmx.`pandian_id`
											FROM
													 kc_pandian_mx pdmx
											WHERE pdmx.id = ckmd.`data_id`
											LIMIT 1)
									 WHEN ckmd.chuku_type =3
													 THEN
										 (SELECT
														 qtmx.stock_other_out_id
											FROM
													 stock_other_out_details qtmx
											WHERE qtmx.id = ckmd.`data_id`
											LIMIT 1)
									 WHEN ckmd.chuku_type = 4
													 THEN
										 (SELECT
														 xsmx.order_id
											FROM
													 salesorder_details xsmx
											WHERE xsmx.id = ckmd.`data_id`
											LIMIT 1)
									 WHEN ckmd.`chuku_type` =9
													 THEN
										 (SELECT
														 tbspot.id
											FROM
													 kc_spot tbspot
											WHERE tbspot.`id` = ckmd.`data_id`
												AND tbspot.status = 2
											LIMIT 1)
									 WHEN ckmd.chuku_type = 10
													 THEN
										 (SELECT
														 thmx.cg_th_id
											FROM
													 cg_th_mx thmx
											WHERE thmx.id = ckmd.`data_id`
											LIMIT 1)

									 ELSE NULL
										 END
								 ) osId,
						 ck.id,
						 spot.`id` zbid,
						 (
								 CASE
									 WHEN ckmd.`chuku_type` =1
													 THEN
										 (SELECT
														 db.system_number
											FROM
													 kc_diaobo_mx dbmx
														 LEFT JOIN kc_diaobo db
															 ON db.id = dbmx.diaobo_id
											WHERE dbmx.id = ckmd.`data_id`
											LIMIT 1)
									 WHEN ckmd.chuku_type =2
													 THEN
										 (SELECT
														 pd.system_number
											FROM
													 kc_pandian_mx pdmx
														 LEFT JOIN kc_pandian pd
															 ON pd.id = pdmx.pandian_id
											WHERE pdmx.id = ckmd.`data_id`
											LIMIT 1)
									 WHEN ckmd.chuku_type = 3
													 THEN
										 (SELECT
														 qtck.system_number
											FROM
													 stock_other_out_details qtmx
														 LEFT JOIN stock_other_out qtck
															 ON qtck.id = qtmx.stock_other_out_id
											WHERE qtmx.id = ckmd.`data_id`
											LIMIT 1)
									 WHEN ckmd.chuku_type = 4
													 THEN
										 (SELECT
														 sale.system_no
											FROM
													 salesorder_details xsmx
														 LEFT JOIN salesorder sale
															 ON sale.id = xsmx.order_id
											WHERE xsmx.id = ckmd.`data_id`
											LIMIT 1)
									 WHEN ckmd.`chuku_type` = 9
													 THEN ''
									 WHEN ckmd.chuku_type = 10
													 THEN
										 (SELECT
														 th.system_number
											FROM
													 cg_th_mx thmx
														 LEFT JOIN cg_th th
															 ON th.id = thmx.cg_th_id
											WHERE thmx.id = ckmd.`data_id`
											LIMIT 1)



									 ELSE NULL
										 END
								 ) systemNumber,
						 spot.`resource_number` resourceNumber,
						 cktype.id crkType,
						 cktype.`name` crkTypeName,
						 ck.`yw_time` ywTime,
						 (
								 CASE
									 WHEN ckmd.`chuku_type` = 1
													 THEN
										 (SELECT
														 cu.custom
											FROM
													 kc_diaobo_mx dbmx
														 LEFT JOIN kc_diaobo db
															 ON db.id = dbmx.diaobo_id
														 LEFT JOIN custom cu
															 ON dbmx.gf_customer_id = cu.id
											WHERE dbmx.id = ckmd.`data_id`
											LIMIT 1)
									 WHEN ckmd.chuku_type = 2
													 THEN ''
									 WHEN ckmd.chuku_type =3
													 THEN
										 (SELECT
														 cu.custom
											FROM
													 stock_other_out_details qtmx
														 LEFT JOIN stock_other_out qtck
															 ON qtck.id = qtmx.stock_other_out_id
														 LEFT JOIN custom cu
															 ON qtck.customer_id = cu.id
											WHERE qtmx.id = ckmd.`data_id`
											LIMIT 1)
									 WHEN ckmd.chuku_type = 5
													 THEN
										 (SELECT
														 cu.custom
											FROM
													 salesorder_details xsmx
														 LEFT JOIN salesorder sale
															 ON sale.id = xsmx.order_id
														 LEFT JOIN custom cu
															 ON sale.custom_id = cu.id
											WHERE xsmx.id = ckmd.`data_id`
											LIMIT 1)
									 WHEN ckmd.`chuku_type` = 9
													 THEN ''
									 WHEN ckmd.chuku_type = 10
													 THEN
										 (SELECT
														 cu.custom
											FROM
													 cg_th_mx thmx
														 LEFT JOIN cg_th th
															 ON th.id = thmx.cg_th_id
														 LEFT JOIN custom cu
															 ON th.customer_id = cu.id
											WHERE thmx.id = ckmd.`data_id`
											LIMIT 1)



									 ELSE NULL
										 END
								 ) customerName,
						 cate.classname cate,
						 pm.`id` pinmingId,
						 pm.`name` pinmingName,
						 gg.`id` guigeId,
						 gg.`specification` guigeName,
						 spot.`houdu` houdu,
						 spot.`changdu` changdu,
						 spot.`kuandu` kuandu,
						 cz.`id` caizhiId,
						 cz.`texturename` caizhiName,
						 cd.`id` chandiId,
						 cd.`originarea` chandiName,
						 jjfs.`id` jijiafangshiId,
						 jjfs.`jsfs` jijiafangshiName,
						 '' rklingzhi,
						 '' rkjianshu,
						 '' rkcounts,
						 '' rkzhongliang,
						 '' rkLisuanZhongliang,
						 '' rkGuobangZhongliang,
						 ckmd.`lingzhi` lingzhi,
						 ckmd.`jianshu` jianshu,
						 ckmd.`counts` counts,
						 ckmd.`zhongliang` zhongliang,
						 '' lisuanZhongliang,
						 '' guobangZhongliang,
						 ckmd.`zhijian` zhijian,
						 ckmd.`pihao` pihao,
						 store.`id` storeId,
						 store.`storage` storeName,
						 sys.`name` zhidanren,
						 '2' isFlag,

						 '' rkmizhong,
						 ckmd.mizhong mizhong,
						 ckmd.tax_rate shuiprice,
						 ckmd.cb_price price,
						 ckmd.jianzhong jianzhong,
						 '' rkJianzhong,
						 '' rkZhijian,
						 '' rkPrice,
						 '' rkShuiPrice
			FROM
					 stock_out_md ckmd
						 LEFT JOIN kc_spot spot
							 ON spot.`id` = ckmd.`kc_spot_id`
						 LEFT JOIN kc_ck_type cktype
							 ON cktype.`id` = ckmd.`chuku_type`
						 LEFT JOIN stock_out ck
							 ON ck.`id` = ckmd.`stock_out_id`
						 LEFT JOIN specification gg
							 ON gg.`id` = ckmd.`guige_id`
						 LEFT JOIN  productname pm
							 ON pm.`id` = gg.`productname_id`
						 LEFT JOIN classname cate
							 ON cate.`id` = pm.`classid`
						 LEFT JOIN texture cz
							 ON cz.`id` = ckmd.`caizhi`
						 LEFT JOIN originarea cd
							 ON cd.`id` = ckmd.`chandi`
						 LEFT JOIN jsfs jjfs
							 ON jjfs.`id` = ckmd.`jijiafangshi_id`
						 LEFT JOIN storage store
							 ON store.`id` = ckmd.`store_id`
						 LEFT JOIN admin sys
							 ON sys.`id` = ck.`create_operator_id`
			WHERE ck.status!=1 and ck.delete_time is null and ckmd.delete_time is null and ckmd.companyid=".$this->getCompanyId()."
		 ) t
where 1=1 
";
        if (!empty($params['system_number'])) {
            $sql .= ' and t.systemNumber= ?';
            $sqlParams[] = $params['system_number'];
        }
        if (!empty($params['ywsjStart'])) {
            $sql .= ' and t.ywTime >= ?';
            $sqlParams[] = $ywsjStart;
        }
        if (!empty($params['ywsjEnd'])) {
            $sql .= ' and t.ywTime < ?';
            $sqlParams[] = $ywsjEnd;
        }
        if (!empty($params['houdu_start'])) {
            $sql .= ' and t.houdu >= ?';
            $sqlParams[] = $params['houdu_start'];
        }
        if (!empty($params['houdu_end'])) {
            $sql .= ' and t.houdu < ?';
            $sqlParams[] = $params['houdu_end'];
        }
        if (!empty($params['width_start'])) {
            $sql .= ' and t.kuandu >= ?';
            $sqlParams[] = $params['width_start'];
        }
        if (!empty($params['width_end'])) {
            $sql .= ' and t.kuandu < ?';
            $sqlParams[] = $params['width_end'];
        }
        if (!empty($params['length_start'])) {
            $sql .= ' and t.changdu >= ?';
            $sqlParams[] = $params['length_start'];
        }
        if (!empty($params['length_end'])) {
            $sql .= ' and t.changdu < ?';
            $sqlParams[] = $params['length_end'];
        }
        if (!empty($params['resource_number'])) {
            $sql .= ' and t.resourceNumber = ?';
            $sqlParams[] = $params['resource_number'];
        }
        if (!empty($params['pinming'])) {
            $sql .= ' and t.pinmingName = ?';
            $sqlParams[] = $params['pinming'];
        }
        if (!empty($params['guige'])) {
            $sql .= ' and t.guigeName = ?';
            $sqlParams[] = $params['guige'];
        }
        if (!empty($params['cate'])) {
            $sql .= ' and t.cate = ?';
            $sqlParams[] = $params['cate'];
        }
        if (!empty($params['store_id'])) {
            $sql .= ' and t.storeId = ?';
            $sqlParams[] = $params['store_id'];
        }
        if (!empty($params['caizhi_id'])) {
            $sql .= ' and t.caizhiId = ?';
            $sqlParams[] = $params['caizhi_id'];
        }
        if (!empty($params['pihao'])) {
            $sql .= ' and t.pihao = ?';
            $sqlParams[] = $params['pihao'];
        }
        $sql.=" order by t.zbid,t.ywTime)";
        $data = Db::table($sql)->alias('t')->bind($sqlParams)->order('ywTime')->paginate($pageLimit);
        return returnSuc($data);
    }
}