<?php

namespace app\admin\model;

use traits\model\SoftDelete;

class SalesMoshi extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;
}