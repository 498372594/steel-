<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-03-11
 * Time: 17:17
 */

namespace app\admin\behavior;


use think\Response;

class CORS
{
    public function appInit()
    {
        if(isset($_SERVER['HTTP_ORIGIN'])){
            $allow_host = ['http://127.0.0.1:8080','http://localhost:8080','http://steelerp.hxc.com:9090','http://www.steel.com:9090'];
            $http_origin = $_SERVER['HTTP_ORIGIN'];
            if(!in_array($http_origin,$allow_host)){
                exit();
            }
            header("Access-Control-Allow-Origin: {$http_origin}");
//            header("Access-Control-Allow-Origin: *");
//            header('Access-Control-Allow-Credentials: true');
            header("Access-Control-Allow-Headers: token, Origin, X-Requested-With, Content-Type, Accept, Authorization");
            header('Access-Control-Allow-Methods:POST, GET, PUT, DELETE, OPTIONS');
            if(request()->isOptions()){
                exit();
            }
        }
    }
}