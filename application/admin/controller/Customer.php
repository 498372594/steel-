<?php


namespace app\admin\controller;


use app\admin\model\Custom;
use app\admin\model\Jsfs;
use app\admin\model\PriceLog;
use app\admin\model\ViewZijinCount;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Request;
use think\response\Json;

class Customer extends Right
{

    /**
     * 获取欠款
     * @param $customer_id
     * @return Json
     */
    public function getQk($customer_id)
    {
        $data = ViewZijinCount::where('customer_id', $customer_id)
            ->group('fangxiang')
            ->column('sum(money)', 'fangxiang');
        $yishou = \app\admin\model\CapitalSk::where('status', '<>', 2)
            ->where('customer_id', $customer_id)
            ->sum('money+msmoney');
        $yifu = \app\admin\model\CapitalFk::where('status', '<>', 2)
            ->where('customer_id', $customer_id)
            ->sum('money+mfmoney');
        return returnSuc(['fee' => ($data[1] ?? 0) - $yishou - ($data[2] ?? 0) + $yifu]);
    }

    /**
     * 获取应收发票余额
     * @param int $customer_id
     * @return Json
     * @throws DbException
     */
    public function getYsInv($customer_id = 0)
    {
        $subSql = \app\admin\model\InvCgsp::where('gys_id', $customer_id)
            ->where('status', '<>', 1)
            ->fieldRaw('ifnull(sum(money+msmoney),0)')
            ->buildSql();

        $value = \app\admin\model\Inv::where('customer_id', $customer_id)
            ->where('fx_type', 1)
            ->where('shui_price', '>', 0)
            ->value('ifnull(sum(sum_shui_price),0)-' . $subSql);
        return returnSuc(['fee' => $value]);
    }

    /**
     * 获取应开发票余额
     * @param int $customer_id
     * @return Json
     * @throws DbException
     */
    public function getYkInv($customer_id = 0)
    {
        $subSql = \app\admin\model\InvXskp::where('customer_id', $customer_id)
            ->where('status', '<>', 2)
            ->fieldRaw('ifnull(sum(money+mkmoney),0)')
            ->buildSql();

        $value = \app\admin\model\Inv::where('customer_id', $customer_id)
            ->where('fx_type', 2)
            ->where('shui_price', '>', 0)
            ->value('ifnull(sum(sum_shui_price),0)-' . $subSql);
        return returnSuc(['fee' => $value]);
    }

    /**
     * 客户利润统计表
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     */
    public function lirun(Request $request, $pageLimit = 10)
    {
        if (!$request->isGet()) {
            return returnFail('请求方式错误');
        }
        $params = $request->param();
        $model = new Custom();
        $data = $model->lirun($params, $pageLimit, $this->getCompanyId());
        return returnSuc($data);
    }

    /**
     * 客户利润率报表
     * @param Request $request
     * @param int $pageLimit
     * @return Json
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function lirunlv(Request $request, $pageLimit = 10)
    {
        $params = $request->param();

        $customers = Custom::with('zhiyuan,provinceData,cityData')
            ->field('id,custom,zjm,short_name,suoshu_department,moren_yewuyuan,province,city')
            ->where('companyid', $this->getCompanyId())
            ->where('iscustom', 1);
        if (!empty($params['customer_id'])) {
            $customers->where('id', $params['customer_id']);
        }
        $customers = $customers->paginate($pageLimit);

        $customerIds = [];
        foreach ($customers as $item) {
            $customerIds[] = $item['id'];
        }

        $sql = '(select sk.customer_id,yw_time,cache_ywtime,hj_money,skhx.data_id
from capital_sk sk
         join capital_skhx skhx on skhx.sk_id = sk.id
where (data_id, yw_time) in (
    select data_id, max(yw_time)
    from capital_sk sk
             join capital_skhx skhx on skhx.sk_id = sk.id
    where skhx.skhx_type = 12
      and skhx.data_id in
          (select id from view_money_source where skhx.companyid = ' . $this->getCompanyId() . ' and type_id = 12 and weihexiao_jine = 0)
    group by skhx.data_id))';
        $sk = Db::table($sql)->alias('t');
        if (!empty($params['ywsjStart'])) {
            $sk->where('yw_time', '>=', $params['ywsjStart']);
        }
        if (!empty($params['ywsjEnd'])) {
            $sk->where('yw_time', '<', $params['ywsjEnd']);
        }
        $sk = $sk->where('customer_id', 'in', $customerIds)->select();

        $dataIds = [];
        foreach ($sk as $item) {
            $dataIds[] = $item['data_id'];
        }

        $hk = \app\admin\model\CapitalHk::with('salesDetailsForLirunlv')->where('id', 'in', $dataIds)->select();

        $hkData = [];
        foreach ($hk as $item) {
            $hkData[$item['id']][] = $item;
        }

        $lirun = [];
        foreach ($sk as $item) {
            $days = ceil((strtotime($item['yw_time']) - strtotime($item['cache_ywtime'])) / 86400);
            foreach ($hkData[$item['data_id']] as $hkItem) {
                $ywTime = $item['cache_ywtime'];
                foreach ($hkItem['sales_details_for_lirunlv'] as $orderItem) {
                    $jsfs = Jsfs::withTrashed()->where('id', $orderItem['jsfs_id'])->cache(true, 60)->find();
                    $price = PriceLog::where('gg_id', $orderItem['wuzi_id'])
                        ->cache(true, 60)
                        ->whereTime('create_time', '<=', $ywTime)
                        ->order('create_time', 'desc')
                        ->find();

                    $str = '';
                    if ($orderItem['tax'] > 0) {
                        $str .= 'hs';
                    } else {
                        $str .= 'qs';
                    }
                    switch ($jsfs['jj_type']) {
                        case 1://理算
                            $str .= 'lsj';
                            $cb = $price[$str] * $orderItem['jianzhong'];
                            break;
                        case 2://磅计
                            $str .= 'gbj';
                            $cb = $price[$str] * $orderItem['weight'];
                            break;
                        case 3://计数
                            $str .= 'dzj';
                            $cb = $price[$str] * $orderItem['count'];
                            break;
                    }

                    $lirun[$hkItem['customer_id']][] = ($orderItem['price_and_tax'] - $cb) / $orderItem['price_and_tax'] / $days;
                }
            }

        }

        $lirunlv = [];
        foreach ($lirun as $customer_id => $arr) {
            $lirunlv[$customer_id] = array_sum($arr) / count($arr);
        }

        foreach ($customers as &$item) {
            $item['lirunlv'] = $lirunlv[$item['id']] ?? 0;
        }

        return returnSuc($customers);
    }
}