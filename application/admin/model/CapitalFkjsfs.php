<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class CapitalFkjsfs extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function jsfs()
    {
        return $this->belongsTo('Jsfs', 'jiesuan_id', 'id')->cache(true, 60)
            ->field('id,jsfs')->bind(['jiesuan_name' => 'jsfs']);
    }

    public function bank()
    {
        return $this->belongsTo('Bank', 'bank_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['bank_name' => 'name']);
    }

}