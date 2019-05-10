<?php

namespace app\admin\controller;

use app\admin\model\PriceLog;
use app\admin\model\Specification;
use app\admin\model\ViewSpecification;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\Queue;
use think\response\Json;

class Price extends Right
{
    public function priceset()
    {
        if (!request()->isPost()) {
            return returnFail('请求方式错误');
        }
        Db::startTrans();
        try {
            $data = request()->post();
            $ids = $data["id"];
            unset($data["id"]);
            $ids = explode(",", $ids);
            foreach ($ids as $id) {
                $dat = $data;
                $dat["id"] = $id;
                $re = (new Specification())->where("id", $id)->save($data);
                (new PriceLog())->allowField(true)->data($dat)->save();
            }
            Db::commit();
            return returnSuc(['id' => $re['id']]);
        } catch (\Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**
     * 单个修改价格
     * @return Json
     */
    public function priceedit()
    {
        if (!request()->isPost()) {
            return returnFail('请求方式错误');
        }
        $data = request()->post();
        $dat["hsgbj"] = $data["hsgbj"];
        $dat["hslsj"] = $data["hslsj"];
        $dat["hsdzj"] = $data["hsdzj"];
        $dat["qsgbj"] = $data["qsgbj"];
        $dat["qslsj"] = $data["qslsj"];
        $dat["qsdzj"] = $data["qsdzj"];
        $dat["gg_id"] = $data["id"];
        $result = model("specification")->allowField(true)->save($data, ['id' => $data["id"]]);
        (new PriceLog())->allowField(true)->data($dat)->save();
        return returnRes($result, '修改失败');
    }

    /**
     * 定时执行
     * @return Json
     */
    public function autoPriceEdit()
    {
        if (!request()->isPost()) {
            return returnFail('请求方式错误');
        }
        Db::startTrans();
        try {
            $data = request()->post();
            $time = $data["time"];
            $heavy = $data["heavy"];
            if ($data["type"] == 1) {
                if (cache("?price_change_time")) {
                    $priceVal = getSettings('price', 'upprice');
                    throw new Exception('已设置' . cache("price_change_time") . "分钟后，执行调价" . $priceVal . '元');
                } else {
                    setSettings('price', ['price_rise' => $heavy, 'price_time_rise' => $time, 'upprice' => $data['upprice']]);
                    cache("price_change_time", $time, $time * 60);
                    Queue::later($time * 60, 'app\admin\job\ChangePrice', $data = $data["upprice"], $queue = null);
                }
            } else {
                setSettings('price', ['price_rise' => $heavy, 'price_time_rise' => $time, 'upprice' => $data['upprice']]);
                cache("price_change_time", $time, $time * 60);
                Queue::push('app\admin\job\ChangePrice', $data["upprice"]);
            }
            Db::commit();
            return returnSuc();
        } catch (\Exception $e) {
            Db::rollback();
            return returnFail($e->getMessage());
        }
    }

    /**
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function getguigelist()
    {
        $params = request()->param();
        $list = ViewSpecification::where('companyid', $this->getCompanyId());
        if (!empty($params['productname_id'])) {
            $list->where('productname_id', $params['productname_id']);
        }
        //规格
        if (!empty($params['guige'])) {
            $list->where('guige', 'like', '%' . $params['specification'] . '%');
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
        $list = $list->select();
        return returnSuc($list);
    }
}