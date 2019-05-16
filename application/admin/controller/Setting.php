<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use app\admin\model\BaseSetting;
use think\Cache;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\response\Json;

class Setting extends Right
{
    use Backend;

    /**
     * 清除配置缓存
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function clearCache()
    {
        cacheSettings();
        $this->success("数据缓存已清除！");
    }

    /**
     * 设置配置
     * @param string $key
     * @param string $value
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function set($key = '', $value = '')
    {
        if (empty($key) || !is_string($key)) {
            return returnFail('配置参数错误');
        }
        if (empty($value)) {
            return returnFail('配置值不能为空');
        }
        $data = BaseSetting::where('key', $key)
            ->where('uid', $this->getAccountId())
            ->find();
        if (empty($data)) {
            $data = new BaseSetting();
            $data->uid = $this->getAccountId();
        }
        $data->key = $key;
        $data->value = $value;
        $data->save();
        Cache::tag('user_setting_' . $this->getAccountId())->rm($key);
        return returnSuc();
    }

    /**
     * 获取配置
     * @param string $key
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function get($key = '')
    {
        if (empty($key) || !is_string($key)) {
            return returnFail('配置参数错误');
        }
        $data = Cache::tag('user_setting_' . $this->getAccountId())->get($key);
        if (!empty($data)) {
            return returnSuc($data);
        }
        $data = BaseSetting::where('key', $key)
            ->where('uid', $this->getAccountId())
            ->find();
        if (empty($data)) {
            return returnSuc(null);
        }
        Cache::tag('user_setting_' . $this->getAccountId())->set($key, $data['value'], 0);
        return returnSuc($data['value']);
    }

    /**
     * 删除配置
     * @param string $key
     * @return Json
     */
    public function rm($key = '')
    {
        if (empty($key) || !is_string($key)) {
            return returnFail('配置参数错误');
        }
        BaseSetting::where('key', $key)
            ->where('uid', $this->getAccountId())
            ->delete();
        Cache::tag('user_setting_' . $this->getAccountId())->rm($key);
        return returnSuc();
    }

    /**
     * 清除所有配置
     * @return Json
     */
    public function clear()
    {
        BaseSetting::where('uid', $this->getAccountId())
            ->delete();
        Cache::tag('user_setting_' . $this->getAccountId())->clear();
        return returnSuc();
    }

    /**
     * 编辑附加数据
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
    protected function editAttach()
    {
        $id = input("id");
        if (empty($id)) throw new Exception("未知的id！");

        $data = Db::table("setting")
            ->where("id", $id)
            ->find();
        $this->assign("data", $data);
    }
}