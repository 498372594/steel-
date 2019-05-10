<?php


namespace app\admin\controller;


use app\admin\model\CapitalFy;
use app\admin\model\Custom;
use app\admin\model\KcSpot;
use app\admin\model\ViewSpecification;
use think\Db;
use think\Request;
use think\response\Json;

class Chart extends Right
{
    /**
     * 业务员销量折线图
     * @param Request $request
     * @return Json
     */
    public function ywySales(Request $request)
    {
        $params = $request->param();
        if (empty($params['ywsjStart'])) {
            return returnFail('请选择业务开始时间');
        }
        if (empty($params['ywsjEnd'])) {
            return returnFail('请选择业务结束时间');
        }
        if (empty($params['sale_operator_id'])) {
            return returnFail('请选择业务员');
        }
        $sqlParams = [];
        $sql = 'select DATE_FORMAT(xs.ywsj, \'%Y-%m-%d\') date,
                       xs.employer,
                       sum(xsmx.weight)                 zhongliang
                from salesorder_details xsmx
                         join salesorder xs on xsmx.order_id = xs.id
                where xs.delete_time is null and xs.ywsj > ? and xs.ywsj < ?';
        $sqlParams[] = $params['ywsjStart'];
        $sqlParams[] = date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day'));
        $sql .= ' and xs.companyid = ' . $this->getCompanyId() . ' and xs.employer in (';
        $in = '';
        foreach ($params['sale_operator_id'] as $id) {
            $in .= '?,';
            $sqlParams[] = $id;
        }
        $in = rtrim($in, ',');
        $sql .= $in . ') group by date,xs.employer';
        $res = Db::query($sql, $sqlParams);

        $legend = \app\admin\model\Admin::where('id', 'in', $params['sale_operator_id'])->column('name', 'id');
        $data = [];
        //第一次处理
        foreach ($res as $item) {
            $data[$item['employer']][$item['date']] = $item['zhongliang'];
        }

        $end = strtotime($params['ywsjEnd'] . ' +1 day');
        $xAxis = [];
        $series = [];
        for ($start = strtotime($params['ywsjStart']); $start < $end; $start += 86400) {
            $currentData = date('Y-m-d', $start);
            $xAxis[] = $currentData;
            foreach ($legend as $id => $name) {
                if (!isset($series[$id])) {
                    $series[$id]['name'] = $name;
                }
                $series[$id]['data'][] = floatval($data[$id][$currentData] ?? 0);
            }
        }
        return returnSuc([
            'legend' => array_merge($legend),
            'xAxis' => $xAxis,
            'series' => array_merge($series, [])
        ]);
    }

    /**
     * 客户销量折线图
     * @param Request $request
     * @return Json
     */
    public function khSales(Request $request)
    {
        $params = $request->param();
        if (empty($params['ywsjStart'])) {
            return returnFail('请选择业务开始时间');
        }
        if (empty($params['ywsjEnd'])) {
            return returnFail('请选择业务结束时间');
        }
        if (empty($params['customer_id'])) {
            return returnFail('请选择客户');
        }
        $sqlParams = [];
        $sql = 'select DATE_FORMAT(xs.ywsj, \'%Y-%m-%d\') date,
                       xs.custom_id,
                       sum(xsmx.weight)                 zhongliang
                from salesorder_details xsmx
                         join salesorder xs on xsmx.order_id = xs.id
                where xs.delete_time is null and xs.ywsj > ? and xs.ywsj < ?';
        $sqlParams[] = $params['ywsjStart'];
        $sqlParams[] = date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day'));
        $sql .= ' and xs.companyid = ' . $this->getCompanyId() . ' and xs.custom_id in (';
        $in = '';
        foreach ($params['customer_id'] as $id) {
            $in .= '?,';
            $sqlParams[] = $id;
        }
        $in = rtrim($in, ',');
        $sql .= $in . ') group by date,xs.custom_id';
        $res = Db::query($sql, $sqlParams);

        $legend = Custom::where('id', 'in', $params['customer_id'])->column('custom', 'id');
        $data = [];
        //第一次处理
        foreach ($res as $item) {
            $data[$item['custom_id']][$item['date']] = $item['zhongliang'];
        }

        $end = strtotime($params['ywsjEnd'] . ' +1 day');
        $xAxis = [];
        $series = [];
        for ($start = strtotime($params['ywsjStart']); $start < $end; $start += 86400) {
            $currentData = date('Y-m-d', $start);
            $xAxis[] = $currentData;
            foreach ($legend as $id => $name) {
                if (!isset($series[$id])) {
                    $series[$id]['name'] = $name;
                }
                $series[$id]['data'][] = floatval($data[$id][$currentData] ?? 0);
            }
        }
        return returnSuc([
            'legend' => array_merge($legend),
            'xAxis' => $xAxis,
            'series' => array_merge($series, [])
        ]);
    }

    /**
     * 货物销量折线图
     * @param Request $request
     * @return Json
     */
    public function hwSales(Request $request)
    {
        $params = $request->param();
        if (empty($params['ywsjStart'])) {
            return returnFail('请选择业务开始时间');
        }
        if (empty($params['ywsjEnd'])) {
            return returnFail('请选择业务结束时间');
        }
        if (empty($params['guige_id'])) {
            return returnFail('请选择产品规格');
        }
        $sqlParams = [];
        $sql = 'select DATE_FORMAT(xs.ywsj, \'%Y-%m-%d\') date,
                       xsmx.wuzi_id,
                       sum(xsmx.weight)                 zhongliang
                from salesorder_details xsmx
                         join salesorder xs on xsmx.order_id = xs.id
                where xs.delete_time is null and xs.ywsj > ? and xs.ywsj < ?';
        $sqlParams[] = $params['ywsjStart'];
        $sqlParams[] = date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day'));
        $sql .= ' and xs.companyid = ' . $this->getCompanyId() . ' and xsmx.wuzi_id in (';
        $in = '';
        foreach ($params['guige_id'] as $id) {
            $in .= '?,';
            $sqlParams[] = $id;
        }
        $in = rtrim($in, ',');
        $sql .= $in . ') group by date,xsmx.wuzi_id';
        $res = Db::query($sql, $sqlParams);

        $legend = ViewSpecification::where('id', 'in', $params['guige_id'])->column('concat(productname,\'-\',specification)', 'id');
        $data = [];
        //第一次处理
        foreach ($res as $item) {
            $data[$item['wuzi_id']][$item['date']] = $item['zhongliang'];
        }

        $end = strtotime($params['ywsjEnd'] . ' +1 day');
        $xAxis = [];
        $series = [];
        for ($start = strtotime($params['ywsjStart']); $start < $end; $start += 86400) {
            $currentData = date('Y-m-d', $start);
            $xAxis[] = $currentData;
            foreach ($legend as $id => $name) {
                if (!isset($series[$id])) {
                    $series[$id]['name'] = $name;
                }
                $series[$id]['data'][] = floatval($data[$id][$currentData] ?? 0);
            }
        }
        return returnSuc([
            'legend' => array_merge($legend),
            'xAxis' => $xAxis,
            'series' => array_merge($series)
        ]);
    }

    public function feiyong(Request $request)
    {
        $params = $request->param();
        if (empty($params['ywsjStart'])) {
            return returnFail('请选择业务开始时间');
        }
        if (empty($params['ywsjEnd'])) {
            return returnFail('请选择业务结束时间');
        }
        $res = CapitalFy::fieldRaw('DATE_FORMAT(yw_time,\'%Y-%m\') as date,sum(money) as money')
            ->where('fang_xiang', 2)
            ->where('companyid', $this->getCompanyId())
            ->where('yw_time', '>', date('Y-m-d', strtotime($params['ywsjStart'])))
            ->where('yw_time', '<', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 month')))
            ->group('date')
            ->select();
        $data = [];
        foreach ($res as $item) {
            $data[$item['date']] = $item['money'];
        }

        $end = strtotime($params['ywsjEnd'] . ' +1 month');
        $xAxis = [];
        $series = [];
        for ($start = strtotime($params['ywsjStart']); $start < $end; $start += 2678400) {
            $currentData = date('Y-m', $start);
            $xAxis[] = $currentData;
            $series[] = floatval($data[$currentData] ?? 0);
        }
        return returnSuc([
            'xAxis' => $xAxis,
            'series' => $series
        ]);
    }

    /**
     * 库存走势
     */
    public function kczs(){
        $params = request()->param();
        if (empty($params['ywsjStart'])) {
            return returnFail('请选择业务开始时间');
        }
        if (empty($params['ywsjEnd'])) {
            return returnFail('请选择业务结束时间');
        }
        $res = KcSpot::fieldRaw('DATE_FORMAT(create_time,\'%Y-%m-%d\') as date,sum(zhongliang) as zhongliang')
            ->where('companyid', $this->getCompanyId())
            ->where('create_time', '>', date('Y-m-d', strtotime($params['ywsjStart'])))
            ->where('create_time', '<', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')))
            ->group('date')
            ->select();
        $res1 = \app\admin\model\SalesorderDetails::alias("a")->join("salesorder b","a.order_id=b.id","left")->fieldRaw('DATE_FORMAT(ywsj,\'%Y-%m-%d\') as date,sum(a.weight) as xiaoliang')
            ->where('a.companyid', $this->getCompanyId())
            ->where('b.ywsj', '>', date('Y-m-d', strtotime($params['ywsjStart'])))
            ->where('b.ywsj', '<', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')))
            ->group('date')
            ->select();
        $legend =[];
        $data = [];
        $legend[0]="重量";
        $legend[1]="销量";
        foreach ($res as $item) {
            $data[0][$item['date']] = $item['zhongliang'];

        }
        foreach ($res1 as $item) {
            $data[1][$item['date']] = $item['xiaoliang'];

        }


        $end = strtotime($params['ywsjEnd'] . ' +1 day');
        $xAxis = [];
        $series = [];
        for ($start = strtotime($params['ywsjStart']); $start < $end; $start += 86400) {
            $currentData = date('Y-m-d', $start);
            $xAxis[] = $currentData;
            $series[0]['name'] = "重量";
            $series[1]['name'] = "销量";
            $series[0]['data'][] = floatval($data[0][$currentData] ?? 0);
            $series[1]['data'][] = floatval($data[1][$currentData] ?? 0);
        }

        return returnSuc([
            'legend' => array_merge($legend),
            'xAxis' => $xAxis,
            'series' => $series
        ]);
    }
}