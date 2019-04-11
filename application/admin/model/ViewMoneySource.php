<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class ViewMoneySource extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

}