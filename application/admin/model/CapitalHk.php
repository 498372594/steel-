<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class CapitalHk extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;
}