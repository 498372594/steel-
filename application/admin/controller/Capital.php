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
    /**
     * @param Request $request
     * @param int $fangxiang
     * @return Json
     * @throws DbException
     */
    public function summary(Request $request, $fangxiang = 1)
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
        $data = $data->paginate(10);

        return returnSuc($data);
    }
}