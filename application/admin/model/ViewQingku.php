<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class ViewQingku extends Base
{
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';

}