<?php

include APP_PATH."../dysms/SignatureHelper.php";
include 'wxpay.class.php';

use Aliyun\DySDKLite\SignatureHelper;

use Yansongda\Pay\Pay;
use Illuminate\Http\Request;

class CommonwapAction extends ApiAction{

    protected $config_ali = [];



	public function _initialize(){
        $this->LANG=C('order');
        $this->GIFT=C('gift');
		//订单、会员、房东 公用语言包
        $SYS=C('sys');
        $this->ORDERC =$SYS['ordercontent']; //订单部分
        $this->MEMBERC =$SYS['member'];      //会员部分
        $this->LANDLORD= $SYS['landlord'];    //点评部分
        $this->COMMENTC= $SYS['comment'];    //点评部分
		//dump($SYS);
        $this->config_ali['alipay'] = C('alipay');
      //  dump($this->LANG);
	}
	public function _empty(){
		exit('方法不存在');
		
	}


	  //发送短信
    public function sms($mobile=0,$str){

        if($mobile){
            //基础数据
            $row=include 'Admin/Conf/message.php';
            $name=iconv("UTF-8","gb2312",$row['smsname']);
            $pwd=iconv("UTF-8","gb2312",$row['smspwd']);
            $c=iconv("UTF-8","gb2312",$str."【".$row['sign']."】");
            
            $postData=array(
            "accountname"=>"$name",
            "accountpwd"=>"$pwd",
             "mobilecodes"=>"$mobile",
            "msgcontent"=>"$c"
            );
             
        
            $this->curl_post("http://csdk.zzwhxx.com:8002/submitsms.aspx", $postData);
           // echo $result;
            if($result==0){
                return true;
            }
            else{
                return false;
            }
        }else{
            return false;
        }
    }    



    public function curl_post($url,$post_arr,$referer=''){
        $post_str = '';
        foreach ( $post_arr as $k => $v ) {
            $post_str .= $k . '=' . $v . '&';
        }

        $post_str = substr ( $post_str, 0, - 1 );   
        $curl = curl_init ();
        curl_setopt ( $curl, CURLOPT_URL, $url ); //要访问的地址 即要登录的地址页面    
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, 1 ); // 从证书中检查SSL加密算法是否存在
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, false ); // 对认证证书来源的检查
    //  curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header_arr );
        curl_setopt ( $curl, CURLOPT_POST, 1 ); // 发送一个常规的Post请求
        curl_setopt ( $curl, CURLOPT_POSTFIELDS, $post_str ); // Post提交的数据包
        curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, 0 ); // 使用自动跳转
    //  curl_setopt ( $curl, CURLOPT_COOKIEJAR, $cookie_file ); // 存放Cookie信息的文件名称
    //  curl_setopt ( $curl, CURLOPT_COOKIEFILE, $cookie_file ); // 读取上面所储存的Cookie信息
        curl_setopt ( $curl, CURLOPT_REFERER, $referer ); //设置Referer
    //  curl_setopt ( $curl, CURLOPT_USERAGENT, $_SERVER ['HTTP_USER_AGENT'] ); // 模拟用户使用的浏览器
        curl_setopt ( $curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1; rv:9.0.1) Gecko/20100101 Firefox/9.0.1" ); // 模拟用户使用的浏览器
        curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
        curl_setopt ( $curl, CURLOPT_HEADER, false ); //获取header信息
        $result = curl_exec ( $curl );
        return $result;
    }

    // get请求
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
    // post json
    function ch_post($url,$data){

        $curl = curl_init ();
        curl_setopt ( $curl, CURLOPT_URL, $url ); //要访问的地址 即要登录的地址页面    
        curl_setopt ( $curl, CURLOPT_POST, 1 ); // 发送一个常规的Post请求
        curl_setopt ( $curl, CURLOPT_POSTFIELDS, $data ); // Post提交的数据包
        // curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, 0 ); // 使用自动跳转
    //  curl_setopt ( $curl, CURLOPT_USERAGENT, $_SERVER ['HTTP_USER_AGENT'] ); // 模拟用户使用的浏览器
        curl_setopt ( $curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1; rv:9.0.1) Gecko/20100101 Firefox/9.0.1" ); // 模拟用户使用的浏览器
        curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
        curl_setopt ( $curl, CURLOPT_HEADER, false ); //获取header信息
        $result = curl_exec ( $curl );
        curl_close($curl);
        
        return $result;
    }

    

    /**
     * 发送短信
     */
    function sendSms($mobile, $code) {
        $params = array ();

        // *** 需用户填写部分 ***

        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = "LTAIRPdDzzLkSvll";
        $accessKeySecret = "GHnJ4wGMXaY8gX4GJJHZqmhz8zW0iT";

        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $mobile;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = "四灵龙个人博客";

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = "SMS_119093229";

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = Array (
            "code" => $code
            // "product" => "阿里通信"
        );

        // fixme 可选: 设置发送短信流水号
        // $params['OutId'] = "12345";

        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        // $params['SmsUpExtendCode'] = "1234567";


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new SignatureHelper();
        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        );

        return $content;
    }
	
    // 支付
    public function alipay($config_biz)
    {
        $arr_ali = C('alipay');

        $config_ali = [
            'alipay' => [
                'app_id' => $arr_ali['app_id'],
                'notify_url' => $arr_ali['notify_url'],
                // 'return_url' => $arr_ali['return_url'],
                'ali_public_key' => $arr_ali['ali_public_key'],
                'private_key' => $arr_ali['private_key'],
            ],
        ];
        $pay = new Pay($config_ali);
        return $pay->driver('alipay')->gateway('app')->pay($config_biz);
    }

    // 微信
    public function wechatpay($config_biz)
    {
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
        return $pay->driver('wechat')->gateway('app')->pay($config_biz);
    }

    public function wechat($orderid)
    {
        $pay = new Wxpay();
        return $pay->wx_Pay($orderid);
    }
}
