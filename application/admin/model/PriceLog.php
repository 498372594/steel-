<?php

namespace app\admin\model;
use traits\model\SoftDelete;
class PriceLog extends Base
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';
}
