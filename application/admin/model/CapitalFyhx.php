<?php

namespace app\admin\model;

use traits\model\SoftDelete;

class CapitalFyhx extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function mingxi()
    {
        return $this->belongsTo('CapitalFy', 'cap_fy_id', 'id')
            ->field('id,customer_id,beizhu,fang_xiang,shouzhifenlei_id,shouzhimingcheng_id,danjia,money,zhongliang,piaoju_id,price_and_tax,tax_rate,tax');
    }

    public function custom()
    {
        return $this->belongsTo('Custom', 'customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['wldw_name' => 'custom']);
    }
}
