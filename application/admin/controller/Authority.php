<?php
namespace app\admin\controller;

use think\auth\Auth;
use think\Db;

class Authority extends Auth
{

    /**
     * 获取权限列表
     * @param int $uid
     * @param int $type
     * @return array
     */
    public function getAuthList($uid, $type)
    {
        //读取用户所属用户组
        $groups = $this->getGroups($uid);
        $ids = []; //保存用户所属用户组设置的所有权限规则id
        foreach ($groups as $g) {
            $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
        }
        $ids = array_unique($ids);
        if (empty($ids)) {
            return [];
        }
        $map = array(
            'id' => ['in', $ids],
            'type' => $type,
            'status' => 1,
        );
        //读取用户组所有权限规则
        $rules = Db::table($this->config['auth_rule'])->where($map)->field('id,name,title,status,pid,faicon')->select();

        return $rules;
    }
}