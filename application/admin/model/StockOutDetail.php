<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class StockOutDetail extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

}