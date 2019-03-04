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
        $filter = $this->filter();

        if(!empty($filter)){
            foreach ($filter as $k => $v){
                if(is_array($v)){
                    $symbol = isset($v[1])?$v[1]:'=';
                    $val = $v[0];
                }else{
                    $symbol = '=';
                    $val = $v;
                }
                $where .= " AND {$k} {$symbol} {$val}";
            }
        }

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

            $aliasName = $this->aliasName.".";

            foreach ($request as $m=>$n) {
                if (!in_array($m, $keep)) {
                    if ('' !== $n) {
                        $oldKey = $m;
                        $m = $this->preprocessing($m);
                        if (array_key_exists($m, $map)) {
                            $type = isset($map[$m]['type'])?$map[$m]['type']:'';
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
                                    break;
                                case "Number":
                                    $where .= " AND {$qureyField} = {$n}";
                                    break;
                                case "Time":
                                    $timeStart = '';
                                    $timeEnd   = '';
                                    if (false !== strstr($oldKey, "Start") && array_key_exists($field."Start", $request) && $n) {
                                        $timeStart = $qureyField;
                                    } else if (false !== strstr($oldKey, "End") && array_key_exists($field."End", $request) && $n) {
                                        $timeEnd = $qureyField;
                                    }
                                    if ($timeStart || $timeEnd) {
                                        if ($timeStart) $where .= " AND {$timeStart} >= '{$n}'";
                                        if ($timeEnd) $where .= " AND {$timeEnd} <= '{$n}'";
                                    }
                                    break;
                                default:

                                    break;
                            }
                        } else {
                            if (in_array($oldKey, $this->model->getTableFields())) {
                                $where .= " AND {$oldKey} LIKE '%{$n}%'";
                            }
                        }
                    }
                }
            }
        }
        return $where;
    }


    /**
     * 参数与处理
     * 将带有特殊后缀的处理一下
     * Preprocessing
     */
    protected function preprocessing($str="")
    {
        $special = ["Start", "End"];
        foreach ($special as $v) {
            if (false !== strstr($str, $v)) {
                $ret = substr($str,0,strpos($str, $v));
                return $ret;
            } else {
                $ret = $str;
            }
        }
        return $ret;
    }
}