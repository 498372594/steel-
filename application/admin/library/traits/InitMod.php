<?php

namespace app\admin\library\traits;

trait InitMod
{
    protected $className=null;
    protected $model = null;
    protected $aliasName = "t";
    public function _initialize()
    {
        parent::_initialize();
        $class = trim(strrchr(__CLASS__, '\\'),'\\');
        $this->className = $class;
        $this->model = model($class);
    }
}