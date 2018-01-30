<?php
define('APP_DEBUG',true);//开启调试模式
define('THINK_PATH','./ThinkPHP/');
define('APP_NAME','Home');
define('USER_ROOT', __DIR__.'/');
define('APP_PATH','./Home/');

require_once './vendor/autoload.php';
// 设置默认时区
date_default_timezone_set("Asia/Shanghai");
session_start();
require_once './ThinkPHP/ThinkPHP.php';
