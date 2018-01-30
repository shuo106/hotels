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
//防XSS
define('APP_DEBUG',true);//开启调试模式
define('THINK_PATH','../ThinkPHP/');
define('APP_NAME','Admin');
define('APP_PATH','../Admin/');
session_start();
require_once '../ThinkPHP/ThinkPHP.php';