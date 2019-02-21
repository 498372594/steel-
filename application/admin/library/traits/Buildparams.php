<?php

namespace app\admin\library\traits;

trait Buildparams
{
    /**
     * 生成查询所需要的条件参数
     * @return array
     */
    protected function buildparams()
    {
        $where    = $this->buildWhere();
        $sort     = $this->request->get("sort", "id");
        $order    = $this->request->get("order", "DESC");
        $pageSize = $this->request->get("pageSize", $this->pageSize);

        return [$where, $sort, $order, $pageSize];
    }

    /**
     * 筛选条件过滤
     * @return mixed $where
     */
    protected function buildWhere()
    {
        $where = "";
        return $where;
    }
}