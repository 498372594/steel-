<?php

namespace app\admin\library\traits;

trait Buildparams
{
    /**
     * 生成查询所需要的条件参数
     * @return array
     */
    protected function buildparams($request)
    {
        $where       = $this->buildWhere($request);
        $sort        = $this->getBaseParam($request, "sort", "id");
        $order       = $this->getBaseParam($request, "order", "DESC");
        $pageSize    = $this->getBaseParam($request, "pageSize", $this->pageSize);
        return [$where, $sort(), $order(), $pageSize()];
    }

    /**
     * 基础参数
     * @param $map
     * @param $name
     * @param string $default
     * @return \Closure
     */
    private function getBaseParam($request, $name, $default=""){
        $func = function () use ($request, $name, $default) {
            if ($request && is_array($request)) {
                return array_key_exists($name, $request)?$request[$name]:$default;
            } else {
                return $this->request->post($name, $default);
            }
        };
        return $func;
    }

    /**
     * 表单提交数据跟数据表的映射
     * @return mixed $where
     */
    protected function buildWhere($request=null)
    {
        $where = "1=1";
        if ($request) {
            $map = [];
            if ($this->model->map) {
                // 如果定义字段映射
                $mapArr = $this->model->map;
                foreach ($mapArr as $k=>$v){
                    $type = array_key_exists(0, $v)?$v[0]:null;
                    $field= array_key_exists(1, $v)?$v[1]:null;
                    $mapField = array_key_exists(2, $v)?$v[2]:null;
                    $map[$field] = ["type"=>$type, "mapField"=>$mapField];
                }
            }

            $keep = ["sort", "order", "pageSize"];

            $aliasName = $this->aliasName;

            foreach ($request as $m=>$n) {
                if (!in_array($m, $keep)) {
                    if ('' !== $n) {
                        if (array_key_exists($m, $map)) {
                            //TODO
                            /*$type = $map[$m]['type'];
                            $field = $m;
                            $mapField = $map[$m]['mapField'];
                            $qureyField = $aliasName.$mapField;
                            switch ($type) {
                                case "Text":
                                    if (array_key_exists($m, $request)) {
                                        if (array_key_exists($m."Condition", $request) && 1 == $request[$m."Condition"]) {
                                            $where .= " AND {$qureyField} = '{$n}'";
                                        } else {
                                            $where .= " AND {$qureyField} LIKE '%{$n}%'";
                                        }
                                    }
                                case "Number":
                                    $where .= " AND {$qureyField} = {$n}";
                                case "Time":
                                    $timeStart = array_key_exists($field."Start", $request)?$mapField:null;
                                    $timeEnd   = array_key_exists($field."End", $request)?$mapField:null;

                                    if ($timeStart || $timeEnd) {
                                        if ($timeStart) $where .= " AND {$timeStart} >= {$n}";
                                        if ($timeEnd) $where .= " AND {$timeEnd} <= {$n}";
                                    }
                                default:
                            }*/
                        }
                    } else {
                        if (in_array($m, $this->model->getTableFields()) && !empty($n)) {
                            $where .= " AND {$key} = '{$n}'";
                        }
                    }
                }
            }
        }
        return $where;
    }
}