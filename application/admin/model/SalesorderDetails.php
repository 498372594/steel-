<?php

namespace app\admin\model;

use think\db\Query;
use think\exception\DbException;
use think\Paginator;
use traits\model\SoftDelete;

class SalesorderDetails extends Base
{
    use SoftDelete;

    public function specification()
    {
        return $this->belongsTo('ViewSpecification', 'wuzi_id', 'id')->cache(true, 60)
            ->field('id,specification,mizhong_name,productname')
            ->bind(['guige' => 'specification', 'mizhong' => 'mizhong_name', 'pinming' => 'productname']);
    }

    public function jsfs()
    {
        return $this->belongsTo('Jsfs', 'jsfs_id', 'id')->cache(true, 60)
            ->field('id,jsfs')->bind(['jsfs_name' => 'jsfs']);
    }

    public function storage()
    {
        return $this->belongsTo('Storage', 'storage_id', 'id')->cache(true, 60)
            ->field('id,storage')->bind(['storage_name' => 'storage']);
    }

    public function salesorder()
    {
        return $this->belongsTo('Salesorder', 'order_id', 'id')
            ->field('id,ywsj,system_no,custom_id,employer,pjlx,ywlx,jsfs,department');
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

    public function spot()
    {
        return $this->belongsTo('KcSpot', 'kc_spot_id', 'id')->cache(true, 60)
            ->field('id,resource_number')->bind('resource_number');
    }

    public function jsfsDataForMx()
    {
        return $this->belongsTo('Jiesuanfangshi', 'jsfs', 'id')->cache(true, 60)
            ->field('id,jiesuanfangshi')->bind(['jisuan_name' => 'jiesuanfangshi']);
    }

    public function customForMx()
    {
        return $this->belongsTo('Custom', 'custom_id', 'id')->cache(true, 60)
            ->field('id,custom,province,city');
    }

    public function pjlxDataForMx()
    {
        return $this->belongsTo('Pjlx', 'pjlx', 'id')->cache(true, 60)
            ->field('id,pjlx')->bind(['pjlx_name' => 'pjlx']);
    }

    public function employerDataForMx()
    {
        return $this->belongsTo('Admin', 'employer', 'id')->cache(true, 60)
            ->field('id,name')->bind(['employer_name' => 'name']);
    }

    /**
     * @param $params
     * @param $pageLimit
     * @param $companyId
     * @return Paginator
     * @throws DbException
     */
    public function getList($params, $pageLimit, $companyId)
    {
        $data = self::hasWhere('salesorder', function (Query $query) use ($params) {
            if (!empty($params['ywlx'])) {
                $query->where('ywlx', $params['ywlx']);
            } elseif (!empty($params['exclude_ywlx'])) {
                $query->where('ywlx', '<>', $params['exclude_ywlx']);
            }
            if (!empty($params['ywsjStart'])) {
                $query->where('ywsj', '>=', $params['ywsjStart']);
            }
            if (!empty($params['ywsjEnd'])) {
                $query->where('ywsj', '<', date('Y-m-d H:i:s', strtotime($params['ywsjEnd'] . ' +1 day')));
            }
            if (!empty($params['system_number'])) {
                $query->where('system_no', $params['system_number']);
            }
            if (!empty($params['customer_id'])) {
                $query->where('custom_id', $params['customer_id']);
            }
            if (!empty($params['piaoju'])) {
                $query->where('pjlx', $params['piaoju']);
            }
            if (!empty($params['department'])) {
                $query->where('department', $params['department']);
            }
            if (!empty($params['employer'])) {
                $query->where('employer', $params['employer']);
            }
            if (!empty($params['create_operator_id'])) {
                $query->where('add_id', $params['create_operator_id']);
            }
            if (!empty($params['status'])) {
                if ($params['status'] != -1) {
                    $query->where('status', $params['status']);
                }
            } else {
                $query->where('status', '<>', 2);
            }
            if (!empty($params['province']) || !empty($params['city'])) {
                $query->where('custom_id', 'in', function (Query $query2) use ($params) {
                    $model = $query2->name('custom')->field('id');
                    if (!empty($params['province'])) {
                        $model->where('province', $params['province']);
                    }
                    if (!empty($params['city'])) {
                        $model->where('city', $params['city']);
                    }
                    return $model;
                });
            }
        })->with([
            'specification',
            'jsfs',
            'storage',
            'caizhiData',
            'chandiData',
            'spot',
            'customForMx' => ['provinceData', 'cityData'],
            'pjlxDataForMx',
            'employerDataForMx',
        ])->field('Salesorder.id as xs_id,Salesorder.status,Salesorder.system_no,Salesorder.ywsj,Salesorder.ywlx,Salesorder.custom_id,Salesorder.pjlx,Salesorder.department,Salesorder.employer,SalesorderDetails.*');
        if (!empty($params['kuanduStart'])) {
            $data->where('width', '>=', $params['kuanduStart']);
        }
        if (!empty($params['kuanduEnd'])) {
            $data->where('width', '<=', $params['kuanduEnd']);
        }
        if (!empty($params['store_id'])) {
            $data->where('storage_id', $params['store_id']);
        }
        if (!empty($params['pinming'])) {
            $data->where('pinming_id', $params['pinming']);
        }
        if (!empty($params['guige'])) {
            $data->where('wuzi_id', $params['guige']);
        }
        if (!empty($params['houduStart'])) {
            $data->where('houdu', '>=', $params['houduStart']);
        }
        if (!empty($params['houduEnd'])) {
            $data->where('houdu', '<=', $params['houduEnd']);
        }
        if (!empty($params['changduStart'])) {
            $data->where('length', '>=', $params['changduStart']);
        }
        if (!empty($params['changduEnd'])) {
            $data->where('length', '>=', $params['changduEnd']);
        }
        if (!empty($params['jsfs'])) {
            $data->where('SalesorderDetails.jsfs_id', $params['jsfs']);
        }
        if (!empty($params['caizhi'])) {
            $data->where('caizhi', $params['caizhi']);
        }
        if (!empty($params['chandi'])) {
            $data->where('chandi', $params['chandi']);
        }
        if (!empty($params['beizhu'])) {
            $data->where('SalesorderDetails.remark', 'like', "%{$params['beizhu']}%");
        }
        $data = $data->where('Salesorder.companyid', $companyId)->order('Salesorder.ywsj', 'desc')->paginate($pageLimit);
        return $data;
    }
}