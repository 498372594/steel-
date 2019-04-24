<?php

namespace app\admin\model;

use traits\model\SoftDelete;

class InvXskp extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function details()
    {
        return $this->hasMany('InvXskpHx', 'inv_xskp_id', 'id');
    }

    public function customData()
    {
        return $this->belongsTo('Custom', 'customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['custom_name' => 'custom']);
    }

    public function pjlxData()
    {
        return $this->belongsTo('Pjlx', 'piaoju_id', 'id')->cache(true, 60)
            ->field('id,pjlx')->bind(['piaoju_name' => 'pjlx']);
    }
}