<?php

namespace app\admin\model;

use InvalidArgumentException;
use think\Model;

class Base extends Model
{
    const MTXT = "Text";
    const MNUM = "Number";
    const MTIME = "Time";

    /**
     * 获取对象原始数据 如果不存在指定字段返回false,并去除decimal后多余的0
     * @access public
     * @param string $name 字段名 留空获取全部
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getAttr($name)
    {
        $value = parent::getAttr($name);
        $types = $this->getFieldsType();
        if (!isset($this->type[$name]) && isset($types[$name])) {
            if (strpos($types[$name], 'decimal') !== false) {
                $value = floatval($value);
            }
        }
        return $value;
    }

    // 验证规则
    public $rules = [

    ];

    // 验证错误信息
    public $msg = [

    ];

    // 场景
    public $scene = [

    ];

    // 表单-数据表字段映射
    public $map = [

    ];
}