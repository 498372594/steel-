<?php


namespace app\admin\model;


use think\Model;

class BaseSetting extends Model
{
    protected $type = [
        'value' => 'serialize'
    ];
}