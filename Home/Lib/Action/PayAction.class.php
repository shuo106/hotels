<?php
// require_once APP_ROOT.'Pay/alipay/v5/config.php';
require_once USER_ROOT.'Pay/alipay/v5/pagepay/service/AlipayTradeService.php';

use Yansongda\Pay\Pay;
use Illuminate\Http\Request;

class PayAction extends Action{


	//支付选择页面
	public function index(){
		$map['orderid'] = intval(substr($this->_get('id'),10));
		// $map['username']= session('name');
		$map['username']= session('id');
		$map['status']=3;
		$this->total = $total = M('order')->where($map)->getField('shoufei');
		// var_dump($total);exit();
		$pay = M('basic')->getField('pay');
		$pay = json_decode($pay,true);
		foreach($pay as $k=>&$v){
			if(!$v['isUse']){
				unset($pay[$k]);
			}
		}
		$this->from = $this->_get('from');
		$this->pay = $pay;
		$this->assign('orderid', $map['orderid']);
		$this->display();
	}
	//支付跳转 模拟
	public function goPay(){
		$payT = $this->_get('payType'); //支付方式
		$map['orderid']=substr($this->_get('id'),10);
		$map['username']= session('name');
		$map['status']= 6;
		//总金额
		$order = M('order')->where($map)->field('shoufei,orderid')->find(); 
		//支付方式
		$pay = M('basic')->getField('pay');
		$pay = json_decode($pay,true);
		//支付宝比较特殊四个付款方式  
		$alipay=0;
		foreach($pay as $k=>&$v){
			if(!$v['isUse']){
				unset($pay[$k]);
			}else{
				if($v['interface']=1){
					$alipay=$v['isUse'];
				}
			}
		}
		switch($payT){
			case 1: //支付宝
				$postData=array();
				$postData['WIDtotal_fee'] 	=$order['shoufei'];//金额
				$postData['WIDsubject']  	='预订客房'; //订单名称
				$postData['WIDuserid']		= session('id');	//会员id
				$postData['WIDout_trade_no']= $order['orderid']; //商户订单号
				$postData['WIDbody']		= '预订客房'; //订单描述
				//$postData['WIDseller_email']= '预订客房'; //商家签约帐号
				$postData['WIDshow_url']= ''; //商品展示地址
				if($alipay==4){
					$postData['WIDdefaultbank']='wanyin'; //网银
				}
				$url ='http://'.$_SERVER['SERVER_NAME'].'/Pay/alipay/'.$alipay.'/alipayapi.php';
				//$url ='http://localhost/quang/Pay/alipay/'.$alipay.'/alipayapi.php';
				$this->doPOST($postData,$url);
				break;
			case 2: //财富通
				$postData=array();
				$postData['order_price'] 	=$order['shoufei'];//金额
				$postData['product_name']  	='预订客房'; //订单名称
				$postData['order_no']= $order['orderid']; //商户订单号
				$postData['trade_mode']= 1; //交易模式（1.即时到帐模式，2.中介担保模式，3.后台选择（卖家进入支付中心列表选择））
				$postData['remarkexplain']= '预订客房'; // 获取提交的备注信息 
				$url ='http://'.$_SERVER['SERVER_NAME'].'/Pay/tenpay/tenpay.php';
				//$url ='http://www.caiyunhotel.com/alipay/alipayapi.php';
				//$url ='http://localhost/quang/Pay/tenpay/tenpay.php';
				$this->doPOST($postData,$url);
				break;	
			case 3: //银联支付
				
				break;
		}
	}
	//模拟post提交
	public function doPOST($post_data,$url){
		$o="";
		foreach ($post_data as $k=>$v)
		{
			$o.= "$k=".urlencode($v)."&";
		}
		$post_data=substr($o,0,-1);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_URL,$url);
		//为了支持cookie
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		$result = curl_exec($ch);
	}
	public function test(){
		$data=array();
		$data['WIDtotal_fee']=10.5;
		$data['WIDsubject']='预订客房';
		$data['WIDuserid']=10010;
		$data['WIDout_trade_no']=147;
		$data['WIDbody']='测试';
		//$url='http://'.__ROOT__.'/Pay/alipay/'.$alipay.'/alipayapi.php';
		$url='http://www.caiyunhotel.com/alipay/alipayapi.php';
	}
	/**
	 * GET 请求
	 * @param string $url
	 */
	private function http_get($url){
		$oCurl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
		}
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}
	}

	/**
	 * POST 请求
	 * @param string $url
	 * @param array $param
	 * @return string content
	 */
	private function http_post($url,$param){
		$oCurl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
		}
		if(is_string($param)){
			$strPOST = $param;
		}else{
			$aPOST = array();
			foreach($param as $key=>$val){
				$aPOST[] = $key."=".urlencode($val);
			}
			$strPOST =  join("&", $aPOST);
		}
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($oCurl, CURLOPT_POST,true);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}
	}

	public function alipay()
	{
		$id = $this->_get('id');
		$from = $this->_get('from');
		$orderid = intval(substr($this->_get('id'), 10));
		$data = [];
		$data['orderid'] = $orderid;
		$data['status'] = 3;				// 未付款状态
		$data['username'] = session('name');
		$price = M('order')->where($data)->getField('shoufei');
		// echo $price;exit();
	$price = 0.01;
		$config_biz = [
            'out_trade_no' => $id,
			'total_amount' => $price,
			'timeout_express' => '30m',
            'subject'      => '房间预订',
		];
		
        $arr_ali = C('alipay');
        $config_ali = [
            'alipay' => [
                'app_id' => $arr_ali['app_id'],
                'notify_url' => $arr_ali['notify_url'],
                'return_url' => $arr_ali['return_url'],
                'ali_public_key' => $arr_ali['ali_public_key'],
                'private_key' => $arr_ali['private_key'],
            ],
		];
		
        $pay = new Pay($config_ali);
		if ($from == 'wap') {
			echo $pay->driver('alipay')->gateway('wap')->pay($config_biz);
		} elseif ($from == 'app') {
			echo $pay->driver('alipay')->gateway('app')->pay($config_biz);
		} else  {
			echo $pay->driver('alipay')->gateway('web')->pay($config_biz);
		}
	}

	public function wechatpay()
	{
		$id = $this->_get('id');
		if (!$id) {
			die(json_encode(['msg' => '缺少订单id','status' => 0]));
		}
		$from = $this->_get('from');
		$orderid = intval(substr($this->_get('id'), 10));
		$data = [];
		$data['orderid'] = $orderid;
		$data['status'] = 3;				// 未付款状态
		// $data['username'] = session('name');
		$price = M('order')->where($data)->getField('shoufei');
		// echo $price;exit();
		if (!$price) {
			die(json_encode(['msg' => '下单失败','status' => 0]));
		}
/* 		$config_biz = [
            'out_trade_no' => $id,
			'total_fee' => $price,
			'body' => '房价预订',
			'spbill_create_ip' => '127.0.0.1',
			'product_id'      => $orderid,
		];
		// $arr_we = C('wechat');
		
        $config_wechat = [
			'wechat' => [
				'app_id' => $arr_we['appid'],              // APPID
                'mch_id' => $arr_we['mch_id'],             // 微信商户号
                'notify_url' => $arr_we['notify_url'],
                'key' => $arr_we['key'],                // 微信支付签名秘钥
                // 'cert_client' => $arr_we['cert_client'],        // 客户端证书路径，退款时需要用到
                // 'cert_key' => $arr_we['cert_key'],            // 客户端秘钥路径，退款时需要用到
            ],
		]; */
		
		// echo json_encode($config_wechat);exit();
		
		// $pay = new Pay($config_wechat);
		// echo $pay->driver('wechat')->gateway('scan')->pay($config_biz);
		echo $this->wxpay($id);
	}

	public function scorepay()
	{
		$id = $this->_post('id');
		$orderid = intval(substr($id, 10));
		$data['orderid'] = $orderid;
		$data['status'] = 3;			// 未付款状态
		// $data['username'] = session('id');
		$price = M('order')->where($data)->getField('shoufei');
		$url = C('javadomain').'/order-pay/score-pay.do';
		// $data = $_GET;
		$user = M('member')->where(['id'=>session('id')])->field('password, telephone')->find();
		$data = [
			'order_id' => $orderid,
			'order_sn' => $id,
			'pay_amount' => $price,
			'pay_password' => $this->_post('psdone'),
			'client_type' => 'APP',
			'login_mobile' => $user['telephone'],
			'login_password' => $user['password']
		];
		$info = [
			'params' => $this->encrypt(json_encode($data))
		];
		$res = $this->curl_get($url, $info);
		if ($res->result == 1) {
			// header("location:" . __ROOT__ . "/Member/member_order.html");
			$d['status'] = 4;
			$d['payment_plugin_id'] = 3;		// 积分支付
			$r = M('order')->where(['orderid' => $orderid])->save($d);
			die(json_encode(['status' => 1, 'msg' => '您已成功支付跳转至订单页', 'url' => __ROOT__ . '/Member/member_order.html']));
		} else {
			die(json_encode(['status'=> 0,'msg'=>'积分支付失败，请选择其它方式支付']));
		}
	}

	/**
	 * @param	String	$url		请求url
	 * @param	Array	$get_arr	请求参数
	 * @return	Object				json对象
	 */
	public function curl_get($url,$get_arr)
	{
		$get_str = '';
		foreach ( $get_arr as $k => $v ) {
			$get_str .= $k . '=' . $v . '&';
		}
		$query_str = substr($get_str,0,-1);

		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url.'?'.$query_str);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$output = curl_exec($ch);
		curl_close($ch);

		return json_decode($output);
	}

	/**
	 * DES 加密
	 * @param	string	$data	json字符串加密数据
	 * @param	string	$key	加密秘钥
	 * @return	string	16进制字符串
	 */
	function encrypt($str, $key = 'haozhaoli510.com')      
	{      
		$block = mcrypt_get_block_size('des', 'ecb');      
		$pad = $block - (strlen($str) % $block);      
		$str .= str_repeat(chr($pad), $pad);      
		$key = substr($key, 0, 8);
		return bin2hex(mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB));      
	}
	/**
	 * 支付宝回调
	 */
	public function alinotify()
	{

		$arr_ali = C('alipay');
        $config_ali = [
			'alipay' => [
				'app_id' => $arr_ali['app_id'],
                'notify_url' => $arr_ali['notify_url'],
                'return_url' => $arr_ali['return_url'],
                'ali_public_key' => $arr_ali['ali_public_key'],
                'private_key' => $arr_ali['private_key'],
            ],
		];
		$pay = new Pay($config_ali);
		
		$arr = $_POST;
		if ($result = $pay->driver('alipay')->gateway()->verify($arr)) {
			// 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。 
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号； 
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）； 
            // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）； 
            // 4、验证app_id是否为该商户本身。 
			// 5、其它业务逻辑情况

			// 订单号
			$id = strval($result['out_trade_no']);
			// 数据库中订单id
			$orderid = intval(substr($result['out_trade_no'],10));
			$order_total = M('order')->where(['orderid' => $orderid])->field('shoufei')->find();
			// if (intval($order_total['shoufei']) == intval($result['total_amount'])) {
				$data['status'] = 4;
				$data['payment_plugin_id'] = 1;		// 支付宝支付
				// $sql = "UPDATE pchotel_order WHERE orderid={$orderid} SET status=4";
				// 修改订单状态
				$res = M('order')->where(['orderid' => $orderid])->save($data);

			// }
			file_put_contents('./notify.txt', '订单验证成功'."\t", FILE_APPEND);
			file_put_contents('./notify.txt', '成交时间:'.$result['gmt_payment']."\t", FILE_APPEND);
			file_put_contents('./notify.txt','订单号:'.$result['out_trade_no']."\t", FILE_APPEND);
			file_put_contents('./notify.txt','订单金额:'.$result['total_amount']."\r\n", FILE_APPEND);
			echo "success";	//请不要修改或删除
		} else {
			file_put_contents('./notify.txt', '验证失败'.json_encode($arr)."\r\n", FILE_APPEND);
			//验证失败
			echo "fail";
		}
	}

	/**
	 * 微信扫码支付
	 */
	public function wxpay($id)
	{
		$arr_we = C('wechat');

		$appid =        $arr_we['appid'];//小程序的appid
		$body =         '房间预订';//商品描述
		$total_fee =    '1';//总金额,最低为一分钱，必须是整数
		$mch_id =       $arr_we['mch_id'];//商户号
		$KEY = 			$arr_we['key'];//商户号key
		$nonce_str =    $this->nonce_str();//随机字符串
		$notify_url =   $arr_we['notify_url'];//回调地址
		// $openid =       '';
		// $tel =          '';//官网电话
		$out_trade_no = $id;//自定义商户订单号规则
		// $spbill_create_ip = $_SERVER["REMOTE_ADDR"];//服务器ip
		$spbill_create_ip = $_SERVER["REMOTE_ADDR"];//服务器ip
		// $total_fee =    $_GET['total_fee'];//金额最小是1，单位为分
		$trade_type =   'NATIVE';//交易类型 默认

		//这里是按照顺序的，因为下面的签名是按照顺序，排序错误，肯定出错
		

		$post['appid'] = $appid;
		$post['body'] = $body;
		$post['mch_id'] = $mch_id;
		$post['nonce_str'] = $nonce_str;//随机字符串
		$post['notify_url'] = $notify_url;
		// $post['openid'] = $openid;
		$post['out_trade_no'] = $out_trade_no;
		$post['spbill_create_ip'] = $spbill_create_ip;
		$post['total_fee'] = $total_fee;
		$post['trade_type'] = $trade_type;
		$sign = $this->MakeSign($post,$KEY);
		$post['sign'] = $sign;
		$post_xml = $this->toXml($post);
/* 		$post_xml = '<xml>
			   <appid>'.$appid.'</appid>
			   <body>'.$body.'</body>
			   <mch_id>'.$mch_id.'</mch_id>
			   <nonce_str>'.$nonce_str.'</nonce_str>
			   <notify_url>'.$notify_url.'</notify_url>
			   <out_trade_no>'.$out_trade_no.'</out_trade_no>
			   <spbill_create_ip>'.$spbill_create_ip.'</spbill_create_ip>
			   <total_fee>'.$total_fee.'</total_fee>
			   <trade_type>'.$trade_type.'</trade_type>
			   <sign>'.$sign.'</sign>
			</xml> '; */
		//统一接口prepay_id
		// echo $post_xml;exit();
		$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
		$xml = $this->http_request($url,$post_xml);		
		// echo $xml;exit();
	
		// $array = $this->xmlarray($xml);//全要大写
		$array = $this->xmlarray($xml);//全要大写
		if($array['RETURN_CODE'] == 'SUCCESS' && $array['CODE_URL']){
		
/* 			$time = time();

			$tmp='';//临时数组用于签名
			$tmp['appid'] = $appid;
			$tmp['partnerid'] = $array['MCH_ID'];
			$tmp['prepayid'] = $array['PREPAY_ID'];
			$tmp['package'] = 'Sign=WXPay';
			// $tmp['signType'] = 'MD5';
			$tmp['noncestr'] = $this->nonce_str();
			$tmp['timestamp'] = "$time";
			$sign = $this->MakeSign($tmp,$KEY);
			$tmp['sign'] = $sign; */
	
			$data['data'] = $array['CODE_URL'];
			$data['status'] = 1;
			// return $this->toXml($tmp);
		}else{
			$data['status'] = 0;
			$data['text'] = "错误";
			$data['RETURN_CODE'] = $array['RETURN_CODE'];
			$data['RETURN_MSG'] = $array['RETURN_MSG'];
		}
		return json_encode($data);
	}

	/**
	 * 微信回调
	 */
	public function notifywe()
    {
		$arr = $_POST;

		$arr_we = C('wechat');

        $config_wechat = [
            'wechat' => [
                'appid' => $arr_we['appid'],              // APPID
                'mch_id' => $arr_we['mch_id'],             // 微信商户号
                'notify_url' => $arr_we['notify_url'],
                'key' => $arr_we['key'],                // 微信支付签名秘钥
                // 'cert_client' => $arr_we['cert_client'],        // 客户端证书路径，退款时需要用到
                // 'cert_key' => $arr_we['cert_key'],            // 客户端秘钥路径，退款时需要用到
            ],
		];
        $pay = new Pay($config_wechat);
        // $verify = $pay->driver('wechat')->gateway('app')->verify($arr);

        if ($verify = $pay->driver('wechat')->gateway('app')->verify($arr)) {
			// 订单号
			$id = strval($result['out_trade_no']);

			$orderid = intval(substr($result['out_trade_no'],10));
			$order_total = M('order')->where(['orderid' => $orderid])->field('shoufei')->find();
			// if (intval($order_total['shoufei']) == intval($result['total_amount'])) {
				$data['status'] = 4;
				$data['payment_plugin_id'] = 2;		// 微信支付
				// $sql = "UPDATE pchotel_order WHERE orderid={$orderid} SET status=4";
				// 修改订单状态
				$res = M('order')->where(['orderid' => $orderid])->save($data);

				echo "success";	//请不要修改或删除
			// }

            file_put_contents('./notify.txt', "验签成功\t", FILE_APPEND);
            file_put_contents('./notify.txt', '订单号：' . $verify['out_trade_no'] . "\t", FILE_APPEND);
            file_put_contents('./notify.txt', '订单金额：' . $verify['total_fee'] . "\r\n", FILE_APPEND);
        } else {
			file_put_contents(storage_path('./notify.txt'), "验签失败\t ".json_encode($arr)."\r\n", FILE_APPEND);
			
			echo "error";
        }

	}
	
	/**
	 * 接收支付结果通知参数
	 * @return Object 返回结果对象；
	 */
	public function notifywechat() {
		// $postXml = $GLOBALS["HTTP_RAW_POST_DATA"];    // 接受通知参数；
		$postXml = file_get_contents('php://input');
		// $postXml = $_POST;
		if (empty($postXml)) {
			file_put_contents('./notify.txt', "没有收到微信返回的数据\r\n", FILE_APPEND);
			return false;
		}
		$postObj = $this->fromXml($postXml);      // 调用解析方法，将xml数据解析成对象
		if ($postObj === false) {
			file_put_contents('./notify.txt', "xml数据解析错误\r\n", FILE_APPEND);
			return false;
		}
		if (!empty($postObj['return_code'])) {
			if ($postObj['return_code'] == 'FAIL') {
				file_put_contents('./notify.txt', "微信返回错误\r\n", FILE_APPEND);
				return false;
			}
		}
		// 验签
		$this->checksing($postObj);          // 返回结果对象；
	}

	//随机32位字符串
	private function nonce_str(){
		$result = '';
		$str = 'QWERTYUIOPASDFGHJKLZXVBNMqwertyuioplkjhgfdsamnbvcxz';
		for ($i=0;$i<32;$i++){
			$result .= $str[rand(0,48)];
		}
		return $result;
	}
	//获取xml里面数据，转换成array
    private function xmlarray($xml){
        $p = xml_parser_create();
        xml_parse_into_struct($p, $xml, $vals, $index);
        xml_parser_free($p);
        $data = "";
        foreach ($index as $key=>$value) {
            if($key == 'xml' || $key == 'XML') continue;
            $tag = $vals[$value[0]]['tag'];
            $value = $vals[$value[0]]['value'];
            $data[$tag] = $value;
        }
        return $data;
	}

	protected function fromXml($xml)
    {   
        if (!$xml) {
            throw new Exception("convert to array error !invalid xml");
        }

        libxml_disable_entity_loader(true);

        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE), true);        
	}


	public function checksing($obj)
	{
		if ($obj) {
/* 			$data = array(
				'appid'                =>    $obj['appid'],
				'bank_type'            =>    $obj['bank_type'],
				'cash_fee'            =>    $obj['cash_fee'],
				'fee_type'            =>    $obj['fee_type'],
				'is_subscribe'      =>    $obj['is_subscribe'],
				'mch_id'            =>    $obj['mch_id'],
				'nonce_str'            =>    $obj['nonce_str'],
				'openid'            =>    $obj['openid'],
				'out_trade_no'        =>    $obj['out_trade_no'],
				'result_code'        =>    $obj['result_code'],
				'return_code'        =>    $obj['return_code'],
				'time_end'            =>    $obj['time_end'],
				'total_fee'            =>    $obj['total_fee'],
				'trade_type'        =>    $obj['trade_type'],
				'transaction_id'    =>    $obj['transaction_id'],
				); */
				$wx_sign = $obj['sign'];
				unset($obj['sign']);
			// 拼装数据进行第三次签名
			$config_wechat = C('wechat');
			$sign = $this->MakeSign($obj, $config_wechat['key']);        // 获取签名
			
			/** 将签名得到的sign值和微信传过来的sign值进行比对，如果一致，则证明数据是微信返回的。 */
			if ($sign == $wx_sign) {
				file_put_contents('./notify.txt', "微信验签成功\t 成交时间: \t\t".$obj['time_end'], FILE_APPEND);
				$orderid = intval(substr($obj['out_trade_no'],10));
				$order_total = M('order')->where(['orderid' => $orderid])->field('shoufei')->find();
				// if (intval($order_total['shoufei']) == intval($result['total_amount'])) {
					$data['status'] = 4;
					$data['payment_plugin_id'] = 2;		// 微信支付
					// $sql = "UPDATE pchotel_order WHERE orderid={$orderid} SET status=4";
					// 修改订单状态
					$res = M('order')->where(['orderid' => $orderid])->save($data);
	
				// }
				file_put_contents('./notify.txt', '订单号：' . $obj['out_trade_no'] . "\t", FILE_APPEND);
				file_put_contents('./notify.txt', '订单金额：' . $obj['total_fee'] . "\r\n", FILE_APPEND);
				$reply = "<xml>
							<return_code><![CDATA[SUCCESS]]></return_code>
							<return_msg><![CDATA[OK]]></return_msg>
						</xml>";
				echo $reply;      // 向微信后台返回结果。
				exit;
			} else {
				file_put_contents('./notify.txt', '微信签名验证失败：' . json_encode($obj) . "\r\n", FILE_APPEND);
				return false;
			}
		}

	}

	public function toXml($data)
    {
        if (!is_array($data) || count($data) <= 0) {
            throw new Exception("convert to xml error!invalid array!");
        }
        
        $xml = "<xml>";
        foreach ($data as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";

        return $xml; 
	}

	//curl请求啊
	function http_request($url,$data = null,$headers=array()){
		$curl = curl_init();
		if( count($headers) >= 1 ){
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		}
		curl_setopt($curl, CURLOPT_URL, $url);

		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}
	/**
	 * 获取微信签名
	 */
	public function MakeSign($params,$KEY){
        //签名步骤一：按字典序排序数组参数
        ksort($params);
        $string = $this->ToUrlParams($params);  //参数进行拼接key=value&k=v
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$KEY;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
	}
	
	//拼接url
	public function ToUrlParams( $params ){
		$string = '';
		if( !empty($params) ){
			$array = array();
			foreach( $params as $key => $value ){
				$array[] = $key.'='.$value;
			}
			$string = implode("&",$array);
		}
		return $string;
	}
	
}