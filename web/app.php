<?php

//防XSS
function remove_tag($str){
    $key = array(
        "/<(\\/?)(script|i?frame|style|html|body|title|link|meta|object|\\?|\\%)([^>]*?)>/isU",
        "/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU",
    );
    $str = preg_replace($key,"",$str);
    return addslashes($str);
}
  
function replace_input($array){
    if (is_array($array)){
        foreach($array AS $k => $v){
            $array[$k] = replace_input($v);
        }
    }else{
        $array = remove_tag($array);
    }
    return $array;
}
$_REQUEST = replace_input($_REQUEST);
$_GET = replace_input($_GET);
$_POST = replace_input($_POST);


// define('APP_DEBUG',true);//开启调试模式
define('MODE_NAME', 'rest');  // 采用rest模式运行
define('THINK_PATH','../ThinkPHP/');
define('APP_NAME','App');
define('APP_PATH','../App/');
// 设置时区
date_default_timezone_set("Asia/Shanghai");

require_once '../vendor/autoload.php';
session_start(['cookie_lifetime' => 3600]);
try{
    require_once '../ThinkPHP/ThinkPHP.php';
} catch(Exception $e) {
/*     header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode([
        'msg' => 'error',
        'code' => 404,
        'message' => $e
    ]);
    return ; */
}