<?php

namespace app\admin\model;

use traits\model\SoftDelete;

class BaseXinyongedu extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = 'datetime';
    protected $deleteTime = 'delete_time';
    public function createoperatordata()
    {
        return $this->belongsTo('admin', 'create_operator_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['create_operator' => 'name']);
    }

    public function udpateoperatordata()
    {
        return $this->belongsTo('admin', 'update_operator_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['update_operator' => 'name']);
    }
}