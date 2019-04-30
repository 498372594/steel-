<?php

namespace app\admin\model;

use InvalidArgumentException;
use think\Model;

class Base extends Model
{
    const MTXT = "Text";
    const MNUM = "Number";
    const MTIME = "Time";
    public $rules = [

    ];

    public $msg = [

    ];

    public $scene = [

    ];

    public $map = [

    ];

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
            if (strpos($types[$name], 'decimal') !== false && is_numeric($value)) {
                $value = number_format($value, 2, '.', '');
            }
        }
        return $value;
    }
}