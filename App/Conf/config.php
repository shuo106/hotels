<?php
$config2 = array(
	//'配置项'=>'配置值'
	'REST_METHOD_LIST' => 'get,post,put.delete',
	'REST_DEFAULT_METHOD' => 'get',
	'REST_CONTENT_TYPE_LIST' => 'html,xml,json,rss',
	'REST_DEFAULT_TYPE' => 'json',
	'REST_OUTPUT_TYPE' => array(
			'json' => 'application/json'
	),
	'SHOW_ERROR_MSG' => false,
	'ERROR_MESSAGE' => '发生错误！',
	'URL_ROUTER_ON'  => true,     //开启路由
	'URL_MODEL' => 1,      // pathinfo
	// 路由配置
	'URL_ROUTE_RULES_USER' => [
		'Index' => [
			'GET index' => 'index',
			'POST hello' =>'hello',
			'GET test' => 'test'
		],
		'Register' => [
			'POST index' =>'index'
		],
		'City' => [
			'GET citylist' => 'citylist',
			'GET hotelparams' => 'hotelparams',
			'GET arealist' => 'arealist'
		],
		'Hotel' => [
			'GET hotellist' => 'hotellist',
			'GET detail' => 'detail'
		],
		'Wechat' => [
			'GET index' => 'index',
			'GET city' => 'city',
			'GET chains' => 'chains',
			'GET notes' => 'notes',
			'GET artshow' => 'artshow',
			'GET comment' => 'comment',
			'GET detail' => 'detail',
			'POST order' => 'order',
			'GET hotels' => 'hotels',
			'GET orderdetail' => 'orderdetail',
			'POST registeruser' => 'registeruser',
			'POST updateuser' => 'updateuser',
			'GET loginbytoken' => 'loginbytoken',
			'GET gettoken' => 'gettoken',
			'POST getcode' => 'getcode',
			'POST chklogin' => 'chklogin',
			'POST doorder' => 'doorder',
			'POST order' => 'order',
			'GET myorder' => 'myorder',
			'GET loginout' => 'loginout',
			'GET setsessionid' => 'setsessionid',
			'POST docancel' => 'docancel',
			'GET commentorder' => 'commentorder',
			'POST docomment' => 'docomment',
			'POST pay' => 'pay',
			// 'GET pay' => 'pay',
			'POST doupload' => 'doupload',
			'GET mycomment' => 'mycomment',
			'GET getphone' => 'getphone',
		]
	]
);
$config1=include './config.inc.php';
return array_merge($config1, $config2);
?>
