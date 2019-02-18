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
            ->pagenate($pageSize);

        $this->assign([
            'total' => $total,
            'list' => $list
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
    public function del()
    {
        if (request()->isAjax()) {
            $this->request->filter('strip_tags', 'stripslashes');
        }
    }
}