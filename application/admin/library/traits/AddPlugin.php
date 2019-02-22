<?php

namespace app\admin\library\traits;

use app\admin\controller\Validator;
use think\Exception;

trait AddPlugin
{
    /**
     * 添加
     */
    public function add()
    {
        if (request()->isAjax()) {
            $this->request->filter('strip_tags', 'stripslashes');

            try {
                $data = $this->request->post();
                if ($data && array_key_exists("id", $data)) unset($data['id']);

                // 数据验证
                if (property_exists($this->model, "rules")) {
                    if (!property_exists($this->model, "msg")) return json_err(-1, "请定义验证提示信息！");
                    $validator = new Validator($this->model->rules, $this->model->msg);
                    if (!$validator->check($data)) {
                        return json_err(-1, $validator->getError());
                    }
                }

                // 验证后处理
                $data = $this->afterAddValidate($data);

                // 写入数据
                $ret = $this->model->data($data)->save();
                if ($ret) {
                    return json_suc();
                } else {
                    return json_err();
                }
            } catch (Exception $e) {
                return json_err(-1, $e->getMessage());
            }
        } else {
            $this->addAttach();
            return $this->view->fetch();
        }
    }

    /**
     * 添加附加数据
     */
    protected function addAttach()
    {

    }

    /**
     * 添加 验证后处理
     */
    protected function afterAddValidate($data)
    {

    }
}