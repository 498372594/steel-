<?php

namespace app\admin\controller;

use app\admin\model\Originarea;
use app\admin\model\Texture;
use think\db\Query;
use think\Model;

/**
 * main区域需要一个模板布局
 * Class Right
 * @package app\admin\controller
 */
class Right extends Signin
{

    /**
     * 搜索条件
     * @param $params
     * @param Query|Model $list
     * @return mixed
     */
    public function getsearch($params, $list)
    {
        //系统单号
        if (!empty($params['system_no'])) {
            $list->where('system_no', $params['system_no']);
        }
        //业务时间
        if (!empty($params['ywsjStart'])) {
            $list->where('service_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('service_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['service_start'])) {
            $list->where('service_time', '>=', $params['service_start']);
        }
        if (!empty($params['service_end'])) {
            $list->where('service_time', '<=', date('Y-m-d', strtotime($params['service_end'] . ' +1 day')));
        }
        //系统单号
        if (!empty($params['zyh'])) {
            $list->where('zyh', $params['zyh']);
        }
        //大类
        if (!empty($params['classname'])) {
            $list->where('classname', $params['classname']);
        }
        //仓库
        if (!empty($params['storage_id'])) {
            $list->where('storage_id', $params['storage_id']);
        }
        //品名
        if (!empty($params['productname'])) {
            $list->where('productname', 'like', '%' . $params['productname'] . '%');
        }
        //规格
        if (!empty($params['specification'])) {
            $list->where('specification', $params['specification']);
        }
        //厚度
        if (!empty($params['houdu_start'])) {
            $list->where('houdu_name', '>=', $params['houdu_start']);
        }
        if (!empty($params['houdu_end'])) {
            $list->where('houdu_name', '<=', $params['houdu_end']);
        }
        //宽度
        if (!empty($params['width_start'])) {
            $list->where('width', '>=', $params['width_start']);
        }
        if (!empty($params['width_end'])) {
            $list->where('width', '<=', $params['width_end']);
        }
        //长度
        if (!empty($params['length_start'])) {
            $list->where('length', '>=', $params['length_start']);
        }
        if (!empty($params['length_end'])) {
            $list->where('length', '<=', $params['length_end']);
        }
        //材质
        if (!empty($params['texture'])) {
            $list->where('texture', $params['texture']);
        }
        //规格
        if (!empty($params['originarea'])) {
            $list->where('originarea', $params['originarea']);
        }
        //规格
        if (!empty($params['supplier_id'])) {
            $list->where('supplier_id', $params['supplier_id']);
        }
        //运输单位
        if (!empty($params['ysdw_id'])) {
            $list->where('ysdw_id', $params['ysdw_id']);
        }
        //收货单位
        if (!empty($params['shdw_id'])) {
            $list->where('shdw_id', $params['shdw_id']);
        }
        //备注
        if (!empty($params['remark'])) {
            $list->where('remark', 'like', '%' . $params['remark'] . '%');
        }
        //部门
        if (!empty($params['department'])) {
            $list->where('department', $params['department']);
        }
        //部门
        if (!empty($params['employer'])) {
            $list->where('employer', $params['employer']);
        }
        return $list;
    }

    /**
     * @param $params
     * @param Query|Model $list
     * @return mixed
     */
    public function getsearchcondition($params, $list)
    {

        //仓库
        if (!empty($params['store_id'])) {
            $list->where('store_id', $params['store_id']);
        }
        //品名
        if (!empty($params['pinming'])) {
            $list->where('pinming', 'like', '%' . $params['pinming'] . '%');
        }
        //规格
        if (!empty($params['guige'])) {
            $list->where('guige', $params['guige']);
        }
        //厚度
        if (!empty($params['houdu_start'])) {
            $list->where('houdu', '>=', $params['houdu_start']);
        }
        if (!empty($params['houdu_end'])) {
            $list->where('houdu', '<=', $params['houdu_end']);
        }
        //宽度
        if (!empty($params['width_start'])) {
            $list->where('kuandu', '>=', $params['width_start']);
        }

        if (!empty($params['width_end'])) {
            $list->where('kuandu', '<=', $params['width_end']);
        }
        //长度
        if (!empty($params['length_start'])) {
            $list->where('changdu', '>=', $params['length_start']);
        }
        if (!empty($params['length_end'])) {
            $list->where('changdu', '<=', $params['length_end']);
        }
        //材质
        if (!empty($params['caizhi_id'])) {
            $list->where('caizhi_id', $params['caizhi_id']);
        }
        //资源号
        if (!empty($params['resource_number'])) {
            $list->where('resource_number', $params['resource_number']);
        }
        //规格
        if (!empty($params['chandi_id'])) {
            $list->where('chandi_id', $params['chandi_id']);
        }
        //业务时间
        if (!empty($params['ywsjStart'])) {
            $list->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $list->where('yw_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
        }
        if (!empty($params['piaoju_id'])) {
            $list->where('piaoju_id', $params['piaoju_id']);
        }
        //往来单位
        if (!empty($params['customer_id'])) {
            $list->where('customer_id', $params['customer_id']);
        }
        //票据类型
        if (!empty($params['piaoju_id'])) {
            $list->where('piaoju_id', $params['piaoju_id']);
        }
        if (!empty($params['status'])) {
            $list->where('status', $params['status']);
        }
        //批号
        if (!empty($params['pihao'])) {
            $list->where('pihao', $params['pihao']);
        }
        //系统单号
        if (!empty($params['system_number'])) {
            $list->where('system_number', 'like', '%' . $params['system_number'] . '%');
        }
        //备注
        if (!empty($params['beizhu'])) {
            $list->where('beizhu', 'like', '%' . $params['beizhu'] . '%');
        }
        //创建时间
        if (!empty($params['time_start'])) {
            $list->where('create_time', '>=', $params['time_start']);
        }
        if (!empty($params['time_end'])) {
            $list->where('create_time', '<=', date('Y-m-d', strtotime($params['time_end'] . ' +1 day')));
        }
        //重量大于0
        if (!empty($params['zhongliang'])) {
            $list->where('zhongliang', '>=', 0);
        }
        //数量
        if (!empty($params['counts'])) {
            $list->where('counts', '>=', 0);
        }

        //应收应付初始化录入
        if (!empty($params['type'])) {
            $list->where('type', $params['type']);
        }
        if (!empty($params['create_operator_id'])) {
            $list->where('create_operator_id', $params['create_operator_id']);
        }
        if (!empty($params['kehu_name'])) {
            $list->where('kehu_name', $params['kehu_name']);
        }
        return $list;
    }

    /**
     * 通过材质名获取材质id
     * @param $caizhi
     * @return mixed
     */
    protected function getCaizhiId($caizhi)
    {
        if (empty($caizhi)) {
            return null;
        }
        $id = Texture::where('id', $caizhi)
            ->where('companyid', $this->getCompanyId())
            ->value('id');
        if (!empty($id)) {
            return $id;
        }
        $id = Texture::where('texturename', $caizhi)
            ->cache(true, 60)
            ->where('companyid', $this->getCompanyId())
            ->value('id');
        if (empty($id)) {
            $model = new Texture();
            $model->texturename = $caizhi;
            $model->companyid = $this->getCompanyId();
            $model->add_name = $this->getAccount()['name'];
            $model->add_id = $this->getAccountId();
            $model->remark = $caizhi;
            $model->zjm = $caizhi;
            $model->save();
            $id = $model->id;
        }
        return $id;
    }

    /**
     * 根据产地名获取产地id
     * @param $chandi
     * @return mixed
     */
    protected function getChandiId($chandi)
    {
        if (empty($chandi)) {
            return null;
        }
        $id = Originarea::where('id', $chandi)
            ->where('companyid', $this->getCompanyId())
            ->value('id');
        if (!empty($id)) {
            return $id;
        }
        $id = Originarea::where('originarea', $chandi)
            ->cache(true, 60)
            ->where('companyid', $this->getCompanyId())
            ->value('id');
        if (empty($id)) {
            $model = new Originarea();
            $model->originarea = $chandi;
            $model->companyid = $this->getCompanyId();
            $model->add_name = $this->getAccount()['name'];
            $model->add_id = $this->getAccountId();
            $model->zjm = $chandi;
            $model->save();
            $id = $model->id;
        }
        return $id;
    }

    public function _empty($name)
    {
        if ($name == 'delete') {
            return returnFail('禁止跳号删除');
        }
        return returnFail('404 not found');
    }
}
