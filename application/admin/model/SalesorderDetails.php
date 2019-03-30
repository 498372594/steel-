<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19
 * Time: 10:59
 */

namespace app\admin\model;
use traits\model\SoftDelete;

class SalesorderDetails extends Base
{    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';
}