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
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
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
		WHERE  rk.status !=1
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
        return returnRes($list->toArray()['data'], '没有数据，请添加后重试', $list);
    }
}