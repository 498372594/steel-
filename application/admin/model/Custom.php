<?php

namespace app\admin\model;

use traits\model\SoftDelete;

class Custom extends Base
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';

    public function zhiyuan()
    {
        return $this->belongsTo('Admin', 'moren_yewuyuan', 'id')
            ->cache(true, 60)
            ->field('id,name')
            ->bind(['yewuyuan' => 'name']);
    }
}
