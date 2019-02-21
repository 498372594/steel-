<?php

namespace app\admin\library\traits;

use think\Exception;
use think\Validate;

trait Backend
{
    protected $model = null;
    public function _initialize()
    {
        parent::_initialize();
        $class = trim(strrchr(__CLASS__, '\\'),'\\');
        $this->model = model($class);
    }

    /**
     * 列表 查看
     */
    public function index()
    {
        $this->request->filter('strip_tags', 'stripslashes');

        list($where, $sort, $order, $pageSize) = $this->buildparams();

        $total = $this->model
            ->where($where)
            ->count();

        $list = $this->model
            ->where($where)
            ->order($sort, $order)
            ->paginate($pageSize);

        // 渲染页面
        $pagelist = $list->render();
        $this->assign([
            'total' => $total,
            'list' => $list,
            'pagelist'=>$pagelist
        ]);

        $this->indexAttach();
        return $this->view->fetch();
    }

    /**
     * 列表附加数据
     */
    protected function indexAttach()
    {
        
    }

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

                // 验证前处理
                $data = $this->beforeAddValidate($data);

                // 数据验证
                if (property_exists($this->model, "rules")) {
                    if (!property_exists($this->model, "msg")) return json_err(-1, "请定义验证器错误信息！");
                    // TODO
//                    var_dump($this->model->validate($this->model->rules, $this->model->msg));die;
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
     * 添加 验证前处理
     */
    protected function beforeAddValidate($data)
    {

    }

    /**
     * 添加 验证后处理
     */
    protected function afterAddValidate($data)
    {

    }

    /**
     * 编辑
     */
    public function edit()
    {
        if (request()->isAjax()) {
            $this->request->filter('strip_tags', 'stripslashes');

            // 编辑 验证前处理
            $data = $this->beforeEditValidate($data);

            // 编辑 验证后处理
            $data = $this->afterEditValidate($data);
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