<?php

namespace app\admin\library\traits;

use app\admin\controller\Validator;
use think\Exception;

trait EditPlugin
{
    /**
     * 编辑
     */
    public function edit()
    {
        if (request()->isAjax()) {
            $this->request->filter('strip_tags', 'stripslashes');

            try {
                $data = $this->request->post();
                if (!array_key_exists("id", $data)) return json_err(-1, "未知的参数ID！");

                // 编辑 验证前处理
                $data = $this->beforeEditValidate($data);

                // 数据验证
                if (property_exists($this->model, "rules")) {
                    if (!property_exists($this->model, "msg")) return json_err(-1, "请定义验证提示信息！");
                    $validator = new Validator($this->model->rules, $this->model->msg);
                    // 是否定义edit验证场景
                    if ($this->model->scene['edit']){
                        $validator->scene('edit', $this->model->scene['edit']);
                        if (!$validator->scene('edit')->check($data)) {
                            return json_err(-1, $validator->getError());
                        }
                    } else {
                        if (!$validator->check($data)) {
                            return json_err(-1, $validator->getError());
                        }
                    }
                }

                // 编辑 验证后处理
                $data = $this->afterEditValidate($data);

                // 更新数据
                $ret = $this->model->update($data);
                if (false !== $ret) {
                    return json_suc();
                } else {
                    return json_err();
                }
            }catch (Exception $e) {
                return json_err(-1, $e->getMessage());
            }

        } else {
            $this->editAttach();
            return $this->view->fetch();
        }
    }

    /**
     * 编辑附加数据
     */
    protected function editAttach()
    {

    }

    /**
     * 编辑 验证前处理
     */
    protected function beforeEditValidate($data)
    {
        return $data;
    }

    /**
     * 编辑 验证后处理
     */
    protected function afterEditValidate($data)
    {
        return $data;
    }
}