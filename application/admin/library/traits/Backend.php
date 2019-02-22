<?php
/**
 * 后台trait
 * 除了增删改查，其余方法不要放大权限
 */
namespace app\admin\library\traits;

trait Backend
{
    use InitMod,IndexPlugin,AddPlugin,EditPlugin,DeletePlugin;
}