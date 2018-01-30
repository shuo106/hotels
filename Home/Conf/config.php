<?php
$file = 'Home/Conf/configSkin.txt';
$skin = file_get_contents($file);
$config2=array(
		'URL_ROUTER_ON'=>true,
		'URL_ROUTE_RULES'=>array(
			'id/:id'=>'id/id'
		),
		'DATA_CACHE_TIME'=>0,
		'DEFAULT_THEME'=>"$skin",
		'TMPL_DETECT_THEME'=>true,
		'LOAD_EXT_CONFIG'=>'email',//读取外部配置文件
);
$config1=include './config.inc.php';
$config_lang=include './language/lang.php';
$config_sys=include './language/system.php';
return array_merge($config1,$config2,$config_lang,$config_sys);
?>