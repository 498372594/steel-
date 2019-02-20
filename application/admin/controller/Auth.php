<?php
namespace app\admin\controller;

use think\Db;
use think\Exception;

class Auth extends Right
{
    /**
     * 角色组
     */
    public function group()
    {
        $groups = Db::name("authgroup")->select();

        $this->assign("groups", $groups);
        return $this->fetch('auth/group/group');
    }

    /**
     * 添加角色
     */
    public function add()
    {
        if (request()->isAjax()) {
            $title = trim(input("title"));
            if (empty($title)) {
                return json_err(-1, "请填写角色名称！");
            }

            $data = [
                "title" => $title
            ];
            $ret = Db::name("authgroup")->insert($data);

            if ($ret) {
                return json_suc();
            } else {
                return json_err();
            }
        } else {
            return $this->fetch('auth/group/add');
        }
    }

    /**
     * 编辑角色
     */
    public function edit()
    {
        if (request()->isAjax()) {

        } else {
            return $this->fetch('auth/group/edit');
        }
    }

    /**
     * 删除角色
     */
    public function del()
    {

    }

    /**
     * 权限规则列表
     */
    public function rule()
    {
        $rules = Db::name("authrule")->select();
        $formatRule = convert_tree($rules);

        $this->assign("rules", $formatRule);
        return $this->fetch('auth/auth/rule');
    }

    /**
     * 添加权限
     */
    public function addRule()
    {
       if (request()->isAjax()) {
           $pid   = (int)input("pid");
           $title = trim(input("title"));
           $name  = strtolower(trim(input("name")));
           $faicon  = trim(input("faicon"));

           if (0 > $pid)       return json_err(-1, "错误的父级ID！");
           if (empty($title))  return json_err(-1, "请输入标题！");
           if (empty($name))   return json_err(-1, "请输入菜单规则！");
           if (empty($faicon)) return json_err(-1, "请选择图标！");

           $data = [
               "name"      => $name,
               "title"     => $title,
               "pid"       => $pid,
               "faicon"    => $faicon,
           ];

           try {
               $ret = Db::name("authrule")->insert($data);

               if ($ret) {
                   return json_suc(0, "添加成功！");
               } else {
                   return json_err(-1, "添加失败！");
               }
           } catch (Exception $e) {
               return json_err(-1, $e->getMessage());
           }

       } else {
           // 规则列表
           $formatRule = $this->getRuleList();

           $this->assign("lists", [
               "rulelist" => $formatRule
           ]);
           return $this->fetch('auth/auth/add');
       }
    }

    /**
     * 获取格式化的权限规则列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRuleList()
    {
        // 规则列表
        $rules = Db::name("authrule")->select();

        // 这里不要第三层的规则
        $rulesTree = convert_tree_withnolayer($rules);

        $formatRule = [
            "0" => "顶级"
        ];
        if ($rulesTree) {
            foreach($rulesTree as $k=>$v) {
                $formatRule[$v['id']] = $v['title'];
            }
        }

        return $formatRule;
    }

    /**
     * 更改权限
     */
    public function editRule()
    {
        if (request()->isAjax()) {

        } else {
            $id = (int)input("id");
            $ruleDetail = Db::name("authrule")->where("id", $id)->find();
            if (!$ruleDetail) {
                $this->error("数据不存在！");
            }

            $formatRule = $this->getRuleList();

            $this->assign("lists", [
                "rulelist" => $formatRule
            ]);
            $this->assign("data", $ruleDetail);
            return $this->fetch('auth/auth/edit');
        }
    }

    /**
     * 删除权限
     */
    public function delRule()
    {
        if (request()->isAjax()) {
            $id = (int)input("id");

            $ret = Db::name("authrule")->delete($id);

            if ($ret) {
                return json_suc();
            } else {
                return json_err();
            }
        }
    }
}