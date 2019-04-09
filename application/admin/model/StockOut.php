<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class StockOut extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;
}