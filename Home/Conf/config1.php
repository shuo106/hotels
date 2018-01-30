<?php
$config2=array(
		//路由
		'URL_ROUTER_ON'=>true,
		'URL_ROUTE_RULES'=>array(

		),
		'DATA_CACHE_TIME'=>3600,

);
$config1=include __ROOT__.'./config.inc.php';
return array_merge($config1,$config2);
?>