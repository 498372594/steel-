<?php

namespace app\admin\model;
use traits\model\SoftDelete;
class PriceLog extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = 'datetime';
}
