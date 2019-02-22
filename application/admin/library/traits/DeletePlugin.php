<?php

namespace app\admin\library\traits;

trait DeletePlugin
{
    /**
     * 删除
     */
    public function delete()
    {
        if (request()->isAjax()) {
            $this->request->filter('strip_tags', 'stripslashes');

            $pk = $this->model->getPk();
            $ids = $this->request->param($pk);
            $where[$pk] = ["in", $ids];

            // 附加处理
            $this->deleteAttach();

            if (false === $this->model->where($where)->delete()) {
                return json_err();
            } else {
                return json_suc();
            }
        }
    }

    /**
     * 删除Ajax附加处理
     */
    protected function deleteAttach()
    {

    }
}