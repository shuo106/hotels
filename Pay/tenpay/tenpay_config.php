<?php
$config=@include '../../config.inc.php';
	date_default_timezone_set('PRC');
	$con = mysql_connect($config['DB_HOST'],$config['DB_USER'],$config['DB_PWD']);
	if (!$con)
	{
		die('Could not connect: ' . mysql_error());
	}
	mysql_select_db($config['DB_NAME'], $con);
	$result = mysql_query("SELECT pay FROM pchotel_basic");
	$pay=mysql_fetch_row($result);
	$pay = json_decode($pay[0],true); //接口数据
	$tenpay=array();//财付通数据
	foreach($pay as $v){
		if($v['interface'] == 2){
			$tenpay=$v;
			break;
		}
	}
//$spname="自助商户测试帐户";
$partner = $tenpay['AccountId']; //"1900000113"; 测试帐户                                 	//财付通商户号
$key =$tenpay['Key'];			// "e82573dc7e6136ba414f2e2affbe39fa";								//财付通密钥
$return_url = "http://".$_SERVER['SERVER_NAME']."/Pay/tenpay/payReturnUrl.php";			//显示支付结果页面,*替换成payReturnUrl.php所在路径
$notify_url = "http://".$_SERVER['SERVER_NAME']."/Pay/tenpay/payNotifyUrl.php";			//支付完成后的回调处理页面,*替换成payNotifyUrl.php所在路径
?>