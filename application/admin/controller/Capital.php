<?php


namespace app\admin\controller;


use app\admin\model\Custom;
use app\admin\model\ViewMoneySource;
use think\Db;
use think\db\Query;
use think\exception\DbException;
use think\Request;
use think\response\Json;

class Capital extends Right
{
    /**应收（付）账款汇总表
     * @param Request $request
     * @param int $fangxiang
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function summary(Request $request, $fangxiang = 1, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $model = Custom::order('create_time', 'asc');
        if ($fangxiang == 1) {
            //应收，查客户
            $model->where('iscustom', 1);
        } elseif ($fangxiang == 2) {
            //应付，查供应商
            $model->where('issupplier', 1);
        } else {
            return returnFail('参数错误');
        }
        $params = $request->param();
        //本期数据查询条件
        $benqiWhere = function (Query $query) use ($params) {
            if (!empty($params['ywsjStart'])) {
                $query->where('yw_time', '>=', $params['ywsjStart']);
            }
            if (!empty($params['ywsjEnd'])) {
                $query->where('yw_time', '<', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')));
            }
            $query->where('status', '<>', 1)
                ->where('customer_id', Db::raw('Custom.id'));
        };
        //期初数据查询条件
        $qichuWhere = function (Query $query) use ($params) {
            if (!empty($params['ywsjStart'])) {
                $query->where('yw_time', '<', $params['ywsjStart']);
            }
            $query->where('status', '<>', 1);
        };
        //期初应付
        if (empty($params['ywsjStart'])) {
            $qichuyingfuSql = 0;
        } else {
            $qichuyingfuSql = '(' . ViewMoneySource::fieldRaw('ifnull(sum(hj_jine),0)')
                    ->where('fangxiang', 2)
                    ->where($qichuWhere)
                    ->buildSql(true)
                . '-' .
                \app\admin\model\CapitalFk::fieldRaw('ifnull(sum(money+mfmoney),0)')
                    ->where($qichuWhere)
                    ->buildSql(true) . ')';
        }
        //本期应付
        $benqiyingfuSql = ViewMoneySource::fieldRaw('ifnull(sum(hj_jine),0)')
            ->where('fangxiang', 2)
            ->where($benqiWhere)
            ->buildSql(true);
        //本期实付
        $benqishifuSql = \app\admin\model\CapitalFk::fieldRaw('ifnull(sum(money+mfmoney),0)')
            ->where($benqiWhere)
            ->buildSql(true);

        //期初应收
        if (empty($params['ywsjStart'])) {
            $qichuyingshouSql = 0;
        } else {
            $qichuyingshouSql = '(' . ViewMoneySource::fieldRaw('ifnull(sum(hj_jine),0)')
                    ->where('fangxiang', 1)
                    ->where($qichuWhere)
                    ->buildSql(true)
                . '-' .
                \app\admin\model\CapitalSk::fieldRaw('ifnull(sum(money+msmoney),0)')
                    ->where($qichuWhere)
                    ->buildSql(true) . ')';
        }
        //本期应收
        $benqiyingshouSql = ViewMoneySource::fieldRaw('ifnull(sum(hj_jine),0)')
            ->where('fangxiang', 1)
            ->where($benqiWhere)
            ->buildSql(true);
        //本期实收
        $benqishishouSql = \app\admin\model\CapitalSk::fieldRaw('ifnull(sum(money+msmoney),0)')
            ->where($benqiWhere)
            ->buildSql(true);

        if (!empty($params['customer_id'])) {
            $model->where('id', 'customer_id');
        }

        $sql = $model->fieldRaw("$qichuyingfuSql as qichuyingfu,$benqiyingfuSql as benqiyingfu," .
            "$benqishifuSql as benqishifu,$qichuyingshouSql as qichuyingshou,$benqiyingshouSql as benqiyingshou," .
            "$benqishishouSql as benqishishou,custom,id"
        )->buildSql();

        $data = Db::table($sql)->alias('t1')->fieldRaw('qichuyingfu,benqiyingfu,benqishifu,' .
            'qichuyingfu+benqiyingfu-benqishifu as qimoyingfu,qichuyingshou,benqiyingshou,benqishishou,' .
            'qichuyingshou+benqiyingshou-benqiyingshou as qimoyingshou,' .
            '(qichuyingshou+benqiyingshou-benqiyingshou) - (qichuyingfu+benqiyingfu-benqishifu) as cxyue,custom,id');
        if (!empty($params['hide_no_happen'])) {
            //无发生额不显示
            if ($fangxiang == 1) {
                $data->where('benqingyingshou', '<>', 0);
            } else {
                $data->where('benqingyingfu', '<>', 0);
            }
        }
        if (!empty($params['hide_zero'])) {
            //余额为零不显示
            $data->where('(qichuyingshou+benqiyingshou-benqiyingshou) - (qichuyingfu+benqiyingfu-benqishifu)<>0');
        }
        $data = $data->paginate($pageLimit);

        return returnSuc($data);
    }

    /**
     * 应收（付）账款明细表
     * @param Request $request
     * @param int $fangxiang
     * @param int $paginate
     * @return Json
     * @throws DbException
     */
    public function details(Request $request, $fangxiang = 1, $paginate = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $params = $request->param();
        if (empty($params['customer_id'])) {
            return returnFail('请选择供应商');
        }
        $model = ViewMoneySource::with('custom')
            ->where('companyid', $this->getCompanyId())
            ->field('id,yw_time,dan_hao,hj_jine,yihx_jine,customer_id,fangxiang,type_id,status,group_id,sale_operator_id,beizhu')
            ->where('fangxiang', 1)
            ->where('customer_id', $params['customer_id'])
            ->where('fangxiang', $fangxiang);
        if (!empty($params['ywsjStart'])) {
            $model->whereTime('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $model->whereTime('yw_time', '<', $params['ywsjEnd']);
        }
        if (!empty($params['status'])) {
            $model->where('status', $params['status']);
        }
        if (!empty($params['type'])) {
            $model->where('type_id', $params['type']);
        }
        if (!empty($params['system_number'])) {
            $model->where('dan_hao', $params['system_number']);
        }
        $data = $model->order('create_time', 'asc')->paginate($paginate);
        return returnSuc($data);
    }
}