<META http-equiv=Content-Type content="text/html; charset=utf-8">
<?php

//---------------------------------------------------------
//财付通即时到帐支付页面回调示例，商户按照此文档进行开发即可
//---------------------------------------------------------
require_once ("./classes/ResponseHandler.class.php");
require_once ("./classes/function.php");
require_once ("./tenpay_config.php");
log_result("进入前台回调页面");

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
/* 创建支付应答对象 */
$resHandler = new ResponseHandler();
$resHandler->setKey($key);
//判断签名
if($resHandler->isTenpaySign()) {
	
	//通知id
	$notify_id = $resHandler->getParameter("notify_id");
	//商户订单号
	$out_trade_no = $resHandler->getParameter("out_trade_no");
	//财付通订单号
	$transaction_id = $resHandler->getParameter("transaction_id");
	//金额,以分为单位
	$total_fee = $resHandler->getParameter("total_fee");
	//如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
	$discount = $resHandler->getParameter("discount");
	//支付结果
	$trade_state = $resHandler->getParameter("trade_state");
	//交易模式,1即时到账
	$trade_mode = $resHandler->getParameter("trade_mode");
	
	
	if("1" == $trade_mode ) {
		if( "0" == $trade_state){ 
			$isOk=1;
			
			//echo "<br/>" . "即时到帐支付成功" . "<br/>";
	
		} else {
			//当做不成功处理
			echo "<br/>" . "即时到帐支付失败" . "<br/>";
		}
	}elseif( "2" == $trade_mode  ) {
		if( "0" == $trade_state) {
		
			$isOk=1;
			//echo "<br/>" . "中介担保支付成功" . "<br/>";
		
		} else {
			//当做不成功处理
			echo "<br/>" . "中介担保支付失败" . "<br/>";
		}
	}
	if($isOk){
		$dotime=time();
		//订单状态改变
		$sql="UPDATE `pchotel_order` SET status = 3,paytime=$dotime WHERE orderid='$out_trade_no'";
		mysql_query($sql);
		//判断是否是手机端支付
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
		mysql_close($con);
		if($orderData[0] == 0){
			$url='http://'.$_SERVER['SERVER_NAME'].'/Hotels/ordersuccess-'.$out_trade_no.'.html';
		}else{
			$url='http://'.$_SERVER['SERVER_NAME'].'/Wap/orderdetail/id/'.$out_trade_no;
		}
		echo "<script> window.location.href='$url'</script>";  
		}
	
	} else {
		echo "<br/>" . "认证签名失败" . "<br/>";
		echo $resHandler->getDebugInfo() . "<br>";
	}

?>