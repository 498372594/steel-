<?php

namespace app\admin\model;

use traits\model\SoftDelete;

class KcRkMd extends Base
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';

    public static function findIdsByRkid($rkid)
    {
        /*select id from tb_kc_rk_md where  kc_rk_id=#{rkid} */
        return self::where('kc_rk_id',$rkid)->column('id');
    }
}
