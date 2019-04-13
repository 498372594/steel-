<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class Inv extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

}