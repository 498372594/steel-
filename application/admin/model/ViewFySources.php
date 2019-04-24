<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class ViewFySources extends Base
{
    use SoftDelete;

    public function custom()
    {
        return $this->belongsTo('Custom', 'customer_id', 'id')
            ->field('id,custom')->bind(['customer_name' => 'custom']);
    }
}