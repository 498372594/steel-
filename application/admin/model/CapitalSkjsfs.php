<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class CapitalSkjsfs extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function jsfs()
    {
        return $this->belongsTo('Jiesuanfangshi', 'jiesuan_id', 'id')->cache(true, 60)
            ->field('id,jiesuanfangshi')->bind(['jiesuan_name' => 'jiesuanfangshi']);
    }

    public function bank()
    {
        return $this->belongsTo('Bank', 'bank_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['bank_name' => 'name']);
    }

}