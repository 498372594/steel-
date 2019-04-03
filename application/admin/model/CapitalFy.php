<?php


namespace app\admin\model;


use app\admin\library\traits\DeletePlugin;

class CapitalFy extends Base
{
    use DeletePlugin;
    protected $autoWriteTimestamp = true;

}