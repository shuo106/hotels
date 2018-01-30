<?php
/* * 
 * 功能：支付宝页面跳转同步通知页面
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************页面功能说明*************************
 * 该页面可在本机电脑测试
 * 可放入HTML等美化页面的代码、商户业务逻辑程序代码
 * 该页面可以使用PHP开发工具调试，也可以使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyReturn
 */

require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");
function curl_post($url,$post_arr,$referer=''){
	$post_str = '';
	foreach ( $post_arr as $k => $v ) {
		$post_str .= $k . '=' . $v . '&';
	}
	$post_str = substr ( $post_str, 0, - 1 );	
	$curl = curl_init ();
	curl_setopt ( $curl, CURLOPT_URL, $url ); //要访问的地址 即要登录的地址页面	
	curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, 1 ); // 从证书中检查SSL加密算法是否存在
	curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, false ); // 对认证证书来源的检查
	curl_setopt ( $curl, CURLOPT_POST, 1 ); // 发送一个常规的Post请求
	curl_setopt ( $curl, CURLOPT_POSTFIELDS, $post_str ); // Post提交的数据包
	curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, 0 ); // 使用自动跳转
	curl_setopt ( $curl, CURLOPT_REFERER, $referer ); //设置Referer
	curl_setopt ( $curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1; rv:9.0.1) Gecko/20100101 Firefox/9.0.1" ); // 模拟用户使用的浏览器
	curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
	curl_setopt ( $curl, CURLOPT_HEADER, false ); //获取header信息
	$result = curl_exec ( $curl );
	return $result;
}
?>
<!DOCTYPE HTML>
<html>
    <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
//计算得出通知验证结果
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyReturn();
if($verify_result) {//验证成功
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//请在这里加上商户的业务逻辑程序代码
	
	//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
    //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

	//商户订单号
	$out_trade_no = $_GET['out_trade_no'];

	//支付宝交易号
	$trade_no = $_GET['trade_no'];

	//交易状态
	$trade_status = $_GET['trade_status'];
	
	$dotime=time();
	//订单状态改变
	$sql="UPDATE `pchotel_order` SET status = 3,paytime=$dotime WHERE orderid='$out_trade_no'";
	mysql_query($sql);
	$smsData=include '../../../Admin/Conf/message.php';
	mysql_query("SET NAMES UTF8");   
	$result = mysql_query("SELECT isMobile,pchotel_order.username,pchotel_order.addtime,pchotel_order.telephone,nums,shoufei,hotelname,roomtype,ruzhudate,lidiandate FROM pchotel_order  LEFT JOIN pchotel_member_hotel ON pchotel_order.hotelid = pchotel_member_hotel.hotelid  LEFT JOIN  pchotel_room ON pchotel_order.roomid =pchotel_room.id  WHERE orderid='$out_trade_no'");
	$orderData = mysql_fetch_row($result);
	$result3 = mysql_query("SELECT webname FROM pchotel_basic");
	$webname = mysql_fetch_row($result3);
	$starttime=date('Y-m-d',$orderData[8]);
	$endtime=date('Y-m-d',$orderData[9]);
	$phone=$orderData[3];
	if($smsData['hotelPaySend']=='1' && strlen($phone)==11){
		$relArr=array('#WEBNAME#','#LOGINNAME#','#ORDERTIME#','#ORDERNUMS#','#ORDERTOTAL#','#HOTELNAME#','#ROOMNAME#','#STARTTIME#','#ENDTIME#');
		$subArr=array($webname[0],$orderData[1],date('Y-m-d H:i:s',$orderData[2]),$orderData[4],$orderData[5],$orderData[6],$orderData[7],$starttime,$endtime);
		$PaySms=str_replace($relArr,$subArr,$smsData['hotelPaySms']);
		$PaySms.='【'.$smsData['sign'].'】';
		$name=iconv("UTF-8","gb2312",$smsData['smsname']);
		$pwd=iconv("UTF-8","gb2312",$smsData['smspwd']);
		$con=iconv("UTF-8","gb2312",$PaySms);
		$postData=array(
			"accountname"=>"$name",
			"accountpwd"=>"$pwd",
			 "mobilecodes"=>"$phone",
			"msgcontent"=>"$con"
		);
		curl_post("http://csdk.zzrwkj.com:4002/submitsms.aspx",$postData);
	}
	//判断是否是手机端支付
	mysql_close($con);
	if(!$orderData[0]){
		$url='http://'.$_SERVER['SERVER_NAME'].'/Hotels/ordersuccess-'.$out_trade_no.'.html';
	}else{
		$url='http://'.$_SERVER['SERVER_NAME'].'/Wap/orderdetail/id/'.$out_trade_no;
	}
	echo "<script> window.location.href='$url'</script>";  

	//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
else {
    //验证失败
    //如要调试，请看alipay_notify.php页面的verifyReturn函数
    echo "验证失败";
}
?>
        <title>支付宝纯担保交易接口</title>
	</head>
    <body>
    </body>
</html>