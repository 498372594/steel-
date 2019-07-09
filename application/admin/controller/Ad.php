<?php

namespace app\admin\controller;

//use app\admin\model\{};
use app\admin\model\AdGuige;
use app\admin\model\Hangqingqushi;
use app\admin\model\KcSpot;
use app\admin\model\ViewAd;
use Exception;
use think\{Db, Request};

class Ad extends Right
{
    public function addchangjia()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            if (empty(request()->post("id"))) {
                $result = model("AdChangjia")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $id = request()->post("id");
                $result = model("AdChangjia")->allowField(true)->save($data, ['id' => $id]);
                return returnRes($result, '修改失败');
            }

        } else {
            $id = request()->param("id");
            if ($id) {
                $data['info'] = model("AdChangjia")->where("id", $id)->find();
            } else {
                $data = null;
            }
            return returnRes($data, '无相关数据', $data);
        }

    }
    public function changjia(){
        $list = model("ad_changjia")->paginate(10);
        return returnSuc($list);
    }
    public function guige(){
        $list = AdGuige::with(["changjiaData"])->paginate(10);
        return returnSuc($list);
    }

    public function addguige()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            if (empty(request()->post("id"))) {
                $result = model("AdGuige")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $id = request()->post("id");
                $result = model("AdGuige")->allowField(true)->save($data, ['id' => $id]);
                return returnRes($result, '修改失败');
            }

        } else {
            $id = request()->param("id");
            if ($id) {
                $data['info'] = model("AdGuige")->where("id", $id)->find();
            } else {
                $data = null;
            }
            return returnRes($data, '无相关数据', $data);
        }

    }

    public function addchanpin()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            if (empty(request()->post("id"))) {
                $result = model("AdChanpin")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            } else {
                $id = request()->post("id");
                $result = model("AdChanpin")->allowField(true)->save($data, ['id' => $id]);
                return returnRes($result, '修改失败');
            }

        } else {
            $id = request()->param("id");
            if ($id) {
                $data['info'] = model("AdChanpin")->where("id", $id)->find();
            } else {
                $data = null;
            }
            return returnRes($data, '无相关数据', $data);
        }

    }

    /**上传
     * @return \think\response\Json
     */
    public function upload()
    {
        if (request()->isPost()) {
            $file = request()->file('image');
            if (empty($file)) {
                return returnFail('超出php.ini配置中post_max_size的最大值');
            }
            if ($file) {
                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                if ($info) {
                    $ext = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
                    $path =  DS . 'uploads/' . $info->getSaveName();
                    $name = $info->getFilename();
                    $attData = array(
                        'path' => $path,
                        'fileext' => $ext,
                        'create_time' => time(),
                        'update_time' => time(),
                        'user_id' => $this->getAccountId(),
                    );
                    $result = model('Files')->save($attData);
                    // 成功上传后 获取上传信息
                    $id = model('Files')->id;
                    if ($result) {
                        $arr=array();

                       $arr =['path' => $path, 'id' => $id, 'ext' => $ext, 'name' => $name];
                       $inf["data"]=$arr;
                        $inf['success'] = true;
                    } else {
                        $inf['success'] = false;
                        $inf['msg'] = $file->getError();
                    }


                } else {
                    // 上传失败获取错误信息
                    $inf['success'] = false;
                    $inf['msg'] = $file->getError();
                }
                return json($inf);
            }
        }
    }

    public function getchangjiaguige()
    {
        $data = model("ad_changjia")->select();
        if (!empty($data)) {
            foreach ($data as $key => $datum) {
                $guige = db("ad_guige")->where("changjia_id", $datum["id"])->select();
                if (!empty($guige)) {
                    $data[$key]["guigedetails"] = $guige;
                }
            }
        }
        return returnRes($data, '无相关数据', $data);
    }

    public function getData()
    {   $params=request()->param();
        $list=ViewAd::where("1=1");
        if (!empty($params['changjia_id'])) {
            $list->where('changjia_id', $params['changjia_id']);
        }
        if (!empty($params['guige_id'])) {
            $list->where('guige_id', $params['guige_id']);
        }
        $list =$list->paginate(10);
        return returnSuc($list);
    }

    public function addHangqingqueshi()
    {
        if (request()->isPost()) {
            $data = request()->post();
            $data['add_name'] = $this->getAccount()['name'];
            $data['add_id'] = $this->getAccountId();
            if (empty(request()->post("id"))) {
                $result = model("hangqingqushi")->allowField(true)->save($data);
                return returnRes($result, '添加失败');
            }

        }
    }
    public function hqqs()
    {
        $params = request()->param();
        if (empty($params['ywsjStart'])) {
            $params['ywsjStart']=date("Y-m")."-1 00:00:00";
//            return returnFail('请选择业务开始时间');
        }
        if (empty($params['ywsjEnd'])) {
            $params['ywsjEnd']=date("Y-m-d")." 23:59:59";
//            return returnFail('请选择业务结束时间');
        }
        $res = Hangqingqushi::fieldRaw('DATE_FORMAT(yw_time,\'%Y-%m-%d\') as date,shujugangpei,daigang,caogang,jiaogang,qihuo')
            ->where('yw_time', '>=', date('Y-m-d', strtotime($params['ywsjStart'])))
            ->where('yw_time', '<=', date('Y-m-d', strtotime($params['ywsjEnd'] . ' +1 day')))
            ->select();
        $legend = [];
        $data = [];
        $legend[0] = "数据钢坯";
        $legend[1] = "带钢";
        $legend[2] = "槽钢";
        $legend[3] = "角钢";
        $legend[4] = "期货";
        foreach ($res as $item) {
            $data[0][$item['date']] = $item['shujugangpei'];
            $data[1][$item['date']] = $item['daigang'];
            $data[2][$item['date']] = $item['caogang'];
            $data[3][$item['date']] = $item['jiaogang'];
            $data[4][$item['date']] = $item['qihuo'];

        }
        $end = strtotime($params['ywsjEnd'] . ' +1 day');
        $xAxis = [];
        $series = [];
        for ($start = strtotime($params['ywsjStart']); $start < $end; $start += 86400) {
            $currentData = date('Y-m-d', $start);
            $xAxis[] = $currentData;
            $series[0]['name'] = "数据钢坯";
            $series[1]['name'] = "带钢";
            $series[2]['name'] = "槽钢";
            $series[3]['name'] = "角钢";
            $series[4]['name'] = "期货";
            $series[0]['data'][] = floatval($data[0][$currentData] ?? 0);
            $series[1]['data'][] = floatval($data[1][$currentData] ?? 0);
            $series[2]['data'][] = floatval($data[2][$currentData] ?? 0);
            $series[3]['data'][] = floatval($data[3][$currentData] ?? 0);
            $series[4]['data'][] = floatval($data[4][$currentData] ?? 0);

        }
        return returnSuc([
            'legend' => array_merge($legend),
            'xAxis' => $xAxis,
            'series' => $series
        ]);
    }
}