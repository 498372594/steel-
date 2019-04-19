<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class CapitalBank extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

}