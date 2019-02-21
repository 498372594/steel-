<?php

namespace app\admin\library\traits;

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
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if (request()->isAjax()) {
            $this->request->filter('strip_tags', 'stripslashes');
        }
    }

    /**
     * 编辑
     */
    public function edit()
    {
        if (request()->isAjax()) {
            $this->request->filter('strip_tags', 'stripslashes');
        }
    }

    /**
     * 删除
     */
    public function delete()
    {
        if (request()->isAjax()) {
            $this->request->filter('strip_tags', 'stripslashes');
        }
    }
}