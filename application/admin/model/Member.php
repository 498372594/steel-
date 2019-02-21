<?php

namespace app\admin\model;

class Member extends Base
{
    // 验证规则
    public $rules = [
        'nickName'  => 'require|max:30',
        'account'   => 'require|checkAccount',
        'password'  => 'require|alphaNum|length:6,16'
    ];

    // 验证错误信息
    public $msg = [
        'name.require' => '请填写昵称！',
        'name.max'     => '昵称最多不能超过30个字符！',
        'account.require'=>'请填写账号！',
        'password.require'=>'请填写密码！',
        'password.alphaNum'=>'密码须是字母或数字！',
        'password.length'=>'密码长度须在6-16之间！'
    ];

    /**
     * 账号格式检测
     */
    protected function checkAccount($account){
        return isPhone($account) ? true : '账号格式错误（请填写手机号）！';
    }
}