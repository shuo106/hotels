<?php 

class Wxpay {

	//微信支付
	public function wx_Pay($tel, $price){
		$arr_we = C('wechat');

		$appid =        $arr_we['appid'];//小程序的appid
		$body =         '房间预订';//商品描述
		$total_fee =    $price;//总金额,最低为一分钱，必须是整数
		$mch_id =       $arr_we['mch_id'];//商户号
		$KEY = 			$arr_we['key'];//商户号key
		$nonce_str =    $this->nonce_str();//随机字符串
		$notify_url =   $arr_we['notify_url'];//回调地址
		// $openid =       '';
		// $tel =          '';//官网电话
		$out_trade_no = $tel;//自定义商户订单号规则
		// $spbill_create_ip = $_SERVER["REMOTE_ADDR"];//服务器ip
		$spbill_create_ip = $_SERVER["REMOTE_ADDR"];//服务器ip
		// $total_fee =    $_GET['total_fee'];//金额最小是1，单位为分
		// $trade_type =   'JSAPI';//交易类型 默认
		$trade_type =   'APP';//交易类型 默认

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
		$post_xml = '<xml>
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
			</xml> ';
		//统一接口prepay_id
		// echo $post_xml;exit();
		$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
		$xml = $this->http_request($url,$post_xml);		
		// echo $xml;exit();
	
		// $array = $this->xmlarray($xml);//全要大写
		$array = $this->xmlarray($xml);//全要大写
		// var_dump($array);exit();
		if($array['RETURN_CODE'] == 'SUCCESS'){
		
			$time = time();

			$tmp='';//临时数组用于签名
			$tmp['appid'] = $appid;
			$tmp['partnerid'] = $array['MCH_ID'];
			$tmp['prepayid'] = $array['PREPAY_ID'];
			$tmp['package'] = 'Sign=WXPay';
			// $tmp['signType'] = 'MD5';
			$tmp['noncestr'] = $this->nonce_str();
			$tmp['timestamp'] = "$time";
			$sign = $this->MakeSign($tmp,$KEY);
			$tmp['sign'] = $sign;
/* 			$app_xml = '<xml>
			<appid>'.$appid.'</appid>
			<partnerid>'.$arr_we['mch_id'].'</partnerid>
			<prepayid>'.$array['PREPAY_ID'].'</prepayid>
			<noncestr>'.$this->nonce_str().'</noncestr>
			<package>'.'Sign=WXPay'.'</package>
			<timestamp>'.$time.'</timestamp>
			<sign>'.$sign.'</sign>
		 </xml> '; */
			return $this->toXml($tmp);
		}else{
			$data['state'] = 0;
			$data['text'] = "错误";
			$data['RETURN_CODE'] = $array['RETURN_CODE'];
			$data['RETURN_MSG'] = $array['RETURN_MSG'];
		}
		return $data;
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

	//生成商户订单号，自定义规则
	private function order_number($tel = ''){
/* 		$data = date('YmdHis',time());//14位时间
		//生成8位随机数
		$a_mt = mt_rand(1000,9999);
		$b_mt = mt_rand(1000,9999);
		$c_mt = $a_mt.$b_mt;
		return $tel.$data.$c_mt;//凑齐32位商户订单号 */
		$data = [];
		$data['orderid'] = intval($tel);
		$data['status'] = 3;			// 未付款状态
		// $data['username'] = session('name');
		$order = M('order')->where($data)->field('shoufei,orderid,addtime')->find();
		return $order['addtime'].$order['orderid'];
	}
	//签名
	public function MakeSign( $params,$KEY){
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
	
	protected function fromXml($xml)
    {   
        if (!$xml) {
            throw new Exception("convert to array error !invalid xml");
        }

        libxml_disable_entity_loader(true);

        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE), true);        
	}
	

	
}