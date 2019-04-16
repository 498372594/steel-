<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class ViewMoneySource extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function custom()
    {
        return $this->belongsTo('Custom', 'customer_id', 'id')
            ->field('id,custom')
            ->bind(['customer_name' => 'custom']);
    }

}