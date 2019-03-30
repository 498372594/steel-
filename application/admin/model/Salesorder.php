<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19
 * Time: 8:52
 */

namespace app\admin\model;

use traits\model\SoftDelete;

class Salesorder extends Base
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';

    public function details()
    {
        return $this->hasMany('SalesorderDetails', 'order_id', 'id');
    }

    public function other()
    {
        return $this->hasMany('SalesorderOther', 'order_id', 'id');
    }
}