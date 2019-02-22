<?php

namespace app\admin\model;

class Member extends Base
{
    // 验证规则
    public $rules = [
        'nickName'  => 'require|max:30',
        'account'   => 'require|isMobile',
        'password'  => 'require|alphaNum|length:6,16'
    ];

    // 验证错误信息
    public $msg = [
        'nickName.require' => '请填写昵称！',
        'nickName.max'     => '昵称最多不能超过30个字符！',
        'account.require'=>'请填写账号！',
        'password.require'=>'请填写密码！',
        'password.alphaNum'=>'密码须是字母或数字！',
        'password.length'=>'密码长度须在6-16之间！'
    ];

    // 场景
    public $scene = [
        'edit'  =>  ['nickName','account'],
    ];

    // 表单-数据表字段映射
    public $map = [
        [self::MTXT, "account1", "account"],
        [self::MTIME, "createtime", "createTime"]
    ];
}