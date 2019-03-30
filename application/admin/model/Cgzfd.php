<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/26
 * Time: 16:10
 */

namespace app\admin\model;


use traits\model\SoftDelete;

class Cgzfd extends Base
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';

    public function details()
    {
        return $this->hasMany('CgzfdDetails', 'order_id', 'id');
    }

    public function other()
    {
        return $this->hasMany('CgzfdOther', 'order_id', 'id');
    }
}