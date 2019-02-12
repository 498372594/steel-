<?php

/**
 * 不同环境下获取真实的IP
 * @return array|false|string
 */
function get_real_client_ip(){
    // 防止重复运行代码或者重复的来访者IP
    static $realclientip = NULL;
    if($realclientip !== NULL){
        return $realclientip;
    }
    //判断服务器是否允许$_SERVER
    if(isset($_SERVER)){
        if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
            $realclientip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }elseif(isset($_SERVER["HTTP_CLIENT_IP"])) {
            $realclientip = $_SERVER["HTTP_CLIENT_IP"];
        }else{
            $realclientip = $_SERVER["REMOTE_ADDR"];
        }
    }else{
        //不允许就使用getenv获取
        if(getenv("HTTP_X_FORWARDED_FOR")){
            $realclientip = getenv( "HTTP_X_FORWARDED_FOR");
        }elseif(getenv("HTTP_CLIENT_IP")) {
            $realclientip = getenv("HTTP_CLIENT_IP");
        }else{
            $realclientip = getenv("REMOTE_ADDR");
        }
    }

    return $realclientip;
}

