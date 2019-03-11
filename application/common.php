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
 * 根据小时判断早上 中午 下午 傍晚 晚上
 * @param  date $h [1-24]
 * @return string
 */
function get_curr_time_section($h = ''){
    date_default_timezone_set('Asia/Shanghai');

    //如果没有传入参数，则取当前时间的小时
    if (empty($h)) {
        $h = date("H");
    }

    $str = '';

    if($h<11) $str = "早上好";
    else if($h<13) $str = "中午好";
    else if($h<17) $str = "下午好";
    else if($h<19) $str = "傍晚好";
    else $str = "晚上好";

    return $str;
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

/**
 * json失败返回
 * @param int $code
 * @param string $msg
 * @param string $data
 * @return \think\response\Json
 */
function json_err ($code=-1, $msg="操作失败！", $data="")
{
    return json(info($code, $msg, $data));
}

/**
 * 是否移动端访问
 * @return bool
 */
function isMobile()
{
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    {
        return true;
    }
    if (isset ($_SERVER['HTTP_VIA']))
    {
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    if (isset ($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords = array ('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        );
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
        {
            return true;
        }
    }
    if (isset ($_SERVER['HTTP_ACCEPT']))
    {
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;
        }
    }
    return false;
}

/**
 * 清除模版缓存 不删除 temp目录
 */
function clear_temp_cache() {
    $temp_files = (array)glob(TEMP_PATH . DS . '/*.php');
    array_map(function($v){ if(file_exists($v)) @unlink($v); }, $temp_files);
    return true;
}

/**
 * 重新加载配置缓存信息
 */
function loadCache ()
{
    $settings = \think\Db::table("setting")->select();
    $refer = [];
    if ($settings) {
        foreach ($settings as $k=>$v) {
            $refer[$v['module']][$v['code']] = $v['val'];
        }
    }
    return $refer;
}

/**
 * 配置缓存
 * 加载系统配置并缓存
 */
function cacheSettings ()
{
    \think\Cache::set("settings", NULL);
    $settings = loadCache();
    \think\Cache::set("settings", $settings, 0);
    return $settings;
}

/**
 * 获取配置缓存信息
 */
function getSettings($module="", $code="")
{
    $settings = \think\Cache::get("settings");
    if (empty($settings)) {
        $settings = cacheSettings();
    }

    if (empty($settings)) {
        return NULL;
    }

    if (empty($code)) {
        if (array_key_exists($module, $settings)) {
            return $settings[$module];
        }
    } else {
        if (array_key_exists($module, $settings) && array_key_exists($code, $settings[$module])) {
            return $settings[$module][$code];
        } else {
            return NULL;
        }
    }
}

/**
 * 重新加载下拉表缓存信息
 */
function loadDropdownList ()
{
    $data = \think\Db::table("dropdown")->select();
    $refer = [];
    if ($data) {
        foreach ($data as $k=>$v) {
            $refer[$v['module']][$v['code']] = $v['val'];
        }
    }
    return $refer;
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
    \think\Cache::set('dropdown',NULL);
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
    $data = \think\Db::table('dropdown')->select();
    $refer = [];
    if ($data) {
        foreach ($data as $k=>$v) {
            $refer[$v['module']][$v['code']] = $v['val'];
        }
    }
    return $refer;
}

/**
 * 手机号格式检测
 * @param $str
 * @return bool
 */
function isPhone($str)
{
    return (preg_match("/^1[3456789]\d{9}$/", $str)) ? true : false;
}

/**
 * @param $key
 * @param $val
 */
function set($key, $val)
{
    // 获取token
    $token = trim(input("token"));

    // 如果没有token，应该是web登录
    if (empty($token)) {
        session($key, $val);
    } else {
        // 获取保持的数据
        $session = cache($token);

        // 如果没有数据
        if ($session) $session = array();
        $session[$key] = $val;

        // 保存缓存
        cache($token, $session);
    }
}

/**
 * @param $key
 * @return mixed
 */
function get($key)
{
    // 获取token
    $token = trim(input("token"));

    // 如果没有token，应该是web登录
    if (empty($token)) {
        return session($key);
    } else {
        // 获取保持的数据
        $session = cache($token);

        // 如果没有数据
        if ($session) $session = array();
        return $session[$key];
    }
}

/**
 * 通用返回结果
 * @param $res @desc 判断依据
 * @param $errors @desc 错误提示
 * @param $successData @desc 成功时要返回的数据，可选
 * @return \think\response\Json
 */
function returnRes($res,$errors,$successData = '')
{
    if(!$res){
        $res = [
            'code' => 0,
            'msg' => $errors
        ];
    }else{
        $res = [
            'code' => 1,
        ];
        if($successData){
            $res['data'] = $successData;
        }
    }
    return json($res);
}

/**
 * 错误返回
 * @param $errors
 * @return \think\response\Json
 */
function returnFail($errors)
{
    return json(['code' => 0, 'msg' => $errors]);
}