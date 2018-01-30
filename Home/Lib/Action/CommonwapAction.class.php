<?php
class CommonwapAction extends Action{

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
        $telephone = M('basic')->getField('glsj');
        $this->assign('telephone',$telephone);
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

    


	
	
}