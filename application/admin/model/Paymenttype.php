<?php

namespace app\admin\model;

use traits\model\SoftDelete;

class Paymenttype extends Base
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';

    public function classData()
    {
        return $this->belongsTo('Paymentclass', 'classid', 'id')->cache(true, 60)
            ->field('id,name')->bind(['class_name' => 'name']);
    }
}
