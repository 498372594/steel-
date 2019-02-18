<?php
/**
 * 不同环境下获取真实的IP
 * @return array|false|string
 */
if (!function_exists('get_real_client_ip'))
{
    function get_real_client_ip()
    {
        // 防止重复运行代码或者重复的来访者IP
        static $realclientip = NULL;
        if ($realclientip !== NULL) {
            return $realclientip;
        }
        //判断服务器是否允许$_SERVER
        if (isset($_SERVER)) {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $realclientip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $realclientip = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $realclientip = $_SERVER["REMOTE_ADDR"];
            }
        } else {
            //不允许就使用getenv获取
            if (getenv("HTTP_X_FORWARDED_FOR")) {
                $realclientip = getenv("HTTP_X_FORWARDED_FOR");
            } elseif (getenv("HTTP_CLIENT_IP")) {
                $realclientip = getenv("HTTP_CLIENT_IP");
            } else {
                $realclientip = getenv("REMOTE_ADDR");
            }
        }

        return $realclientip;
    }
}

if (!function_exists("info")) {
    /**
     * 信息返回
     * @param string $code
     * @param string $msg
     * @param string $data
     * @return array
     */
    function info($code = '', $msg = '', $data= '')
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ];
        return $result;
    }
}

/**
 * 获取下拉框，或者值
 * 没有模板名称返回所有，有模板返回对应下拉框，有code返回对应名称
 *
 * @param string $module 模板名称
 * @param string $code code
 * @param bool $hasEmpty 是否包含空值
 * @return array|mixed|null
 */
function getDropdownList($module = '', $code = '' , $hasEmpty = true)
{
    $dropdown = \think\Cache::get("dropdown");

    // 如果缓存没有数据
    if (empty($dropdown)) {
        $dropdown = loadDropdown();
    }

    if (empty($dropdown)) {
        return null;
    }

    if (empty($module)) {
        return $dropdown;
    }

    // 如果没有code
    if (empty($code)) {

        // 是否包含空值
        if($hasEmpty){

            $dropdownList  = array("" => "");
            $dropdownList = $dropdownList + $dropdown[$module];
            return $dropdownList;
        }else{
            return $dropdown[$module];
        }
    } else {
        if (empty($dropdown[$module])) {
            return null;
        } else {
            return $dropdown[$module][$code];
        }
    }
}

/**
 * 加载下拉框
 */
function loadDropdown()
{
    \think\Cache::set("dropdown", NULL);
    $dropdown = selDropdown();
    \think\Cache::set('dropdown', $dropdown, 0);
    return $dropdown;
}

/**
 * 检索下拉框
 * @return array
 */
function selDropdown()
{
    $modules = \think\Db::name('dropdown')->field("module")->group("module")->select();
    $moduleList = array();
    for ($i = 0; $i < count($modules); $i++) {
        $module = $modules[$i]['module'];
        $configs = array();
        $subModules = \think\Db::name('dropdown')->where(array('module' => $module))->order("sort asc")->select();
        for ($j = 0; $j < count($subModules); $j++) {
            $subModule = $subModules[$j];
            $key = $subModule['code'];
            $configs[$key] = $subModule['val'];
        }
        $moduleList[$module] = $configs;
    }
    return $moduleList;
}

/**
 * 格式化的当前日期
 *
 * @return false|string
 */
function now_datetime()
{
    return date("Y-m-d H:i:s");
}

/**
 * json返回
 * @param $code
 * @param $msg
 * @param $data
 * @return \think\response\Json
 */
function json_return ($code="", $msg="", $data="")
{
    return json(info($code, $msg, $data));
}

/**
 * json成功返回
 * @param int $code
 * @param string $msg
 * @param string $data
 * @return \think\response\Json
 */
function json_suc ($code=0, $msg="操作成功！", $data="")
{
    return json(info($code, $msg, $data));
}

function json_err ($code=-1, $msg="操作失败！", $data="")
{
    return json(info($code, $msg, $data));
}

/**
 * 树结构
 * @param $arr
 * @return array
 */
function convert_tree($arr){
    $refer = array();
    $tree = array();
    foreach($arr as $k => $v){
        $refer[$v['id']] = & $arr[$k]; //创建主键的数组引用
    }
    foreach($arr as $k => $v){
        $pid = $v['pid'];  //获取当前分类的父级id
        if($pid == 0){
            $arr[$k]['lev'] = 0;
            $tree[] = & $arr[$k];  //顶级栏目
        }else{
            if(isset($refer[$pid])){
                $arr[$k]['lev'] = $refer[$pid]['lev'] + 1;
                $arr[$k]['title'] = str_repeat('&nbsp;&nbsp;', $arr[$k]['lev']*5).'├'.$arr[$k]['title'];

                $refer[$pid]['sub'][] = & $arr[$k]; //如果存在父级栏目，则添加进父级栏目的子栏目数组中
            }
        }
    }
    return $tree;
}
