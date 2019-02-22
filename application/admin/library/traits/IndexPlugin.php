<?php

namespace app\admin\library\traits;

trait IndexPlugin
{
    /**
     * 列表 查看
     */
    public function index()
    {
        $this->request->filter('strip_tags', 'stripslashes');
        $request = $this->request->post();

        $className = strtoupper($this->className);
        if (isset($request['p'])) {
            list($where, $sort, $order, $pageSize) = [get($className."_PARAMS_WHERE"), get($className."_PARAMS_SORT"),  get($className."_PARAMS_ORDER"), get($className."_PARAMS_PAGESIZE")];
        } else {
            list($where, $sort, $order, $pageSize) = $this->buildparams($request);
            set($className."_PARAMS_REQUEST", $request);
            set($className."_PARAMS_WHERE", $where);
            set($className."_PARAMS_SORT", $sort);
            set($className."_PARAMS_ORDER", $order);
            set($className."_PARAMS_PAGESIZE", $pageSize);
        }

        $total = $this->model->where($where)->count();

        $list = $this->searchList($this->model, $where, $sort, $order, $pageSize);

        // 附加数据
        $this->indexAttach();

        set($className."_PARAMS_REQUEST", NULL);
        set($className."_PARAMS_WHERE", NULL);
        set($className."_PARAMS_SORT", NULL);
        set($className."_PARAMS_ORDER", NULL);
        set($className."_PARAMS_PAGESIZE", NULL);

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
     * 列表附加数据
     */
    protected function indexAttach()
    {

    }

    /**
     * 列表查询
     */
    protected function searchList($model, $where, $sort, $order, $pageSize)
    {
        $data = $model->alias("t")->where($where)->order($sort, $order)->paginate($pageSize);
        return $data;
    }
}