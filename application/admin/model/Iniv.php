<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class Iniv extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

}