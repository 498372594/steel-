<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/26
 * Time: 16:10
 */

namespace app\admin\model;


use traits\model\SoftDelete;

class Cgzfd extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function details()
    {
        return $this->hasMany('CgzfdDetails', 'order_id', 'id');
    }

    public function other()
    {
        return $this->hasMany('CgzfdOther', 'order_id', 'id');
    }

    public function gfjsfsData()
    {
        return $this->belongsTo('Jiesuanfangshi', 'gfjsfs', 'id')->cache(true, 60)
            ->field('id,jiesuanfangshi')->bind(['gfjsfs_name' => 'jiesuanfangshi']);
    }

    public function khjsfsData()
    {
        return $this->belongsTo('Jiesuanfangshi', 'khjsfs', 'id')->cache(true, 60)
            ->field('id,jiesuanfangshi')->bind(['khjsfs_name' => 'jiesuanfangshi']);
    }

    public function gongyingshang()
    {
        return $this->belongsTo('Custom', 'gys_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['gys_name' => 'custom']);
    }

    public function custom()
    {
        return $this->belongsTo('Custom', 'kh_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['custom_name' => 'custom']);
    }

    public function gfpjData()
    {
        return $this->belongsTo('Pjlx', 'gfpj', 'id')->cache(true, 60)
            ->field('id,pjlx')->bind(['gfpj_name' => 'pjlx']);
    }

    public function khpjData()
    {
        return $this->belongsTo('Pjlx', 'khpj', 'id')->cache(true, 60)
            ->field('id,pjlx')->bind(['khpj_name' => 'pjlx']);
    }
}