<?php

namespace app\admin\model;

use traits\model\SoftDelete;

class SalesMoshi extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function details()
    {
        return $this->hasMany('SalesMoshiMx', 'moshi_id', 'id');
    }

    public function other()
    {
        $relation = $this->hasOne('Salesorder', 'data_id', 'id');
        if (!empty($this->data)) {
            $relation->where('ywlx', $this->moshi_type);
        }
        return $relation->field('id,data_id');
    }

    public function gfjsfsData()
    {
        return $this->belongsTo('Jiesuanfangshi', 'cg_jiesuan_id', 'id')->cache(true, 60)
            ->field('id,jiesuanfangshi')->bind(['gfjsfs_name' => 'jiesuanfangshi']);
    }

    public function khjsfsData()
    {
        return $this->belongsTo('Jiesuanfangshi', 'jsfs', 'id')->cache(true, 60)
            ->field('id,jiesuanfangshi')->bind(['khjsfs_name' => 'jiesuanfangshi']);
    }

    public function gongyingshang()
    {
        return $this->belongsTo('Custom', 'cg_customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['gys_name' => 'custom']);
    }

    public function custom()
    {
        return $this->belongsTo('Custom', 'customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['custom_name' => 'custom']);
    }

    public function gfpjData()
    {
        return $this->belongsTo('Pjlx', 'cg_piaoju_id', 'id')->cache(true, 60)
            ->field('id,pjlx')->bind(['gfpj_name' => 'pjlx']);
    }

    public function khpjData()
    {
        return $this->belongsTo('Pjlx', 'piaoju_id', 'id')->cache(true, 60)
            ->field('id,pjlx')->bind(['khpj_name' => 'pjlx']);
    }
}