<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class StockOutMd extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

}