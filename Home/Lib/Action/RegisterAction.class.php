<?php
class RegisterAction extends CommonAction{
	//用户注册
	public function index(){
		header("Content-type:text/html;charset=utf-8");
		$set = M('member_set');
		$r = $set->select();
		if(isset($_POST['btn_submit'])){
			if($r[0]['iscode']){
				if(md5($_POST['verify'])!=$_SESSION['verify']) {
					exit('验证码错误！');		
				}
			}
			if($r[0]['user']){
				$name = json_decode($r[0]['user']);
				if(in_array($_POST['username'],$name)){
					exit('本站禁止该用户词注册');
				}
			}
			header("Content-type:text/html;charset=utf-8");
			if(isset($_POST['hot'])){
				$user=M('member_hotel');
				if(!$r[0]['isnum']){
					$row = $user->where("email = '{$_POST['email']}'")->select();
					if(is_array($row)){
						exit('该邮箱已经注册过');
					}
				}
				$user->hotelname = $_POST['hotelname'];	
			}else{
				$user=M('Member');
				if(!$r[0]['isnum']){
					$row = $user->where("email = '{$_POST['email']}'")->select();
					if(is_array($row)){
						exit('该邮箱已经注册过');
					}
				}
			}
			$user->create();	
			$user->regtime = time();	
			$user->regip = $_SERVER['REMOTE_ADDR'];			
			$user->password=md5($_POST['password']);
			if($r[0]['xieyi']){
				if(!$_POST['xieyi']){
					exit('你还没有同意注册协议');
				}
			}
			if(!$user->create()){
				exit($user->getError());
			}else{
				$user->password=md5($_POST['password']);
				$user->regip = $_SERVER['REMOTE_ADDR'];	
				$user->regtime = time();
					
				$result=$user->add();
				if($result){
					if($r[0]['isemail']){
						EmailAction::sendEmail($_POST['email'],'鹏程酒店系统','鹏程酒店用户注册激活邮件','单击激活账户','http://www.baidu.com');
					}
					if(isset($_POST['hot'])){
						redirect(__ROOT__."/Hotel/index.php/Login/index",2,'会员注册成功!页面跳转中!');
					}else{
						$this->redirect("Login/index",'',2,'会员注册成功!页面跳转中!');
					}
				}
			}
		}
		if($r[0]['iscode']){
			$this->assign('code',1);
		}
		if($r[0]['xieyi']){
			$this->assign('xieyi',$r[0]['xieyi']);
		}
		if($r[0]['isreg']){
			$basic=M('basic');
			$row=$basic->select();		
			$title="个人会员注册-".$row[0]['webname'];
			$this->assign('title',$title);  
			$this->assign('keywords',$row[0]['webname']); 	
			$this->assign('description',$row[0]['description']);
			$this->assign('copyright',$row[0]['copyright']);			
			$this->display();
		}else{
			echo '本站停止新会员注册';
		}
		
	}
	
	public function doreg(){
	header("Content-type:text/html;charset=utf-8");
		$r = M('member_set')->find();
		if($r['user']){
			$name = json_decode($r['user']);
			if(in_array($this->_post('username'),$name)){
				die(json_encode(array('status'=>0,'msg'=>'本站禁止该用户词注册')));		
		   }
		}
		$preg='/^[\w\_]{3,16}$/u';
		if(!preg_match($preg,$this->_post('username'))){
				die(json_encode(array('status' => 0, 'msg' => '用户名只能为3-16位字符，数字和字母组成')));
		}		
		$user=M('Member');
		if(!$r['isnum']){
			$row = $user->where('email = '.$this->_post('email'))->find();
			if($row){
				die(json_encode(array('status'=>0,'msg'=>'该邮箱已经注册过')));	
			}
		}

		 if($this->_post('Mobile')){ 
		     if($user->where('telephone = '.$this->_post('Mobile'))->find()){
		     		die(json_encode(array('status'=>0,'msg'=>'该手机号已经注册过')));	
		     }
	     }

		$iss['username']=$this->_post('username');
		$isu = $user->where($iss)->find();
		if($isu){
			die(json_encode(array('status'=>0,'msg'=>'该帐号已经注册过')));	
		}
		if($r['xieyi']){
			if(!$_POST['xieyi']){
				die(json_encode(array('status'=>0,'msg'=>'你还没有同意注册协议')));
			}
		}
		
		$password=$this->_post('password');
		$username=$this->_post('username');
		$data=array();
		$data['regtime'] 	=$regtime= time();			
		$data['password']	= md5($password);
		$data['username']	= $username;
		$data['email']		= $this->_post('email');
        $data['logintime'] = time();
		$data['loginip']=$_SERVER['REMOTE_ADDR'];


		if(!$this->_post('email')){
			die(json_encode(array('status'=>0,'msg'=>'邮箱不能为空')));		
		}
		$data['telephone']		=$phone= $this->_post('Mobile')? $this->_post('Mobile') :'';
		if(strlen($phone)!=11){
			die(json_encode(array('status'=>0,'msg'=>'手机号码格式错误')));
		}
		if($r['iscode']){
			if(md5($_POST['verify'])!=$_SESSION['verify']) {
				die(json_encode(array('status'=>0,'msg'=>'验证码错误')));		
			}
		}
		$data['truename']	= $this->_post('cr_name') ? $this->_post('cr_name') : '';
		$data['regip']		= $_SERVER['REMOTE_ADDR'];	
		$res=$user->data($data)->add();
		if($res){
			Vendor('ChuanglanSmsHelper.ChuanglanSmsApi');
			$SMS = new ChuanglanSmsApi();
			$smsData=include 'Admin/Conf/message.php';
			if($smsData['regSend']=='1' && strlen($phone)==11){
				$relArr = array('#WEBNAME#', '#LOGINNAME#', '#PASSWORD#', '#REGTIME#');
				$subArr = array($this ->keywords, $username, $password, date('Y-m-d H:i:s', $regtime));
				$regSms = str_replace($relArr, $subArr, $smsData['regSms']);
				$name=iconv("UTF-8","gb2312",$smsData['smsname']);
				$pwd=iconv("UTF-8","gb2312",$smsData['smspwd']);
				$con=$regSms;
				//var_dump($con);
				$str=$SMS->sendSMS($name,$pwd,$phone,$con);
				//var_dump($str);exit;
			} 
			die(json_encode(array('status'=>1,'msg'=>'会员注册成功!页面跳转中!')));	
		}else{
			die(json_encode(array('status'=>0,'msg'=>'注册失败')));	
		}
	}
	/*
		酒店注册
	*/
	public function dohotelreg(){
		header("Content-type:text/html;charset=utf-8");
		$r = M('member_set')->find();
		if($r['user']){
			$name = json_decode($r['user']);
			if(in_array($this->_post('username'),$name)){
				die(json_encode(array('status'=>0,'msg'=>'本站禁止该用户注册')));	
		   }
		}
		$user=M('member_hotel');
		$iss['username']=$this->_post('username');
		$isu = $user->where($iss)->find();
		if($isu){
			die(json_encode(array('status'=>0,'msg'=>'该帐号已经注册过')));	
		}
		//$daan = '/^.{6,30}$/';
           // if (preg_match($daan, $_POST['linkman']) == 0) {
              //  die(json_encode(array('status' => 0, 'msg' => '联系人长度必须在2-10个汉字之间')));
          //  }
		$issm['telephone']=$this->_post('Mobile');

		$ism = $user->where($issm)->find();
		if($ism){
			die(json_encode(array('status'=>0,'msg'=>'该手机号码已经注册过')));	
		}		
		
		$row = $user->where("email = '".$this->_post('email')."'")->find();
		if($row){
			die(json_encode(array('status'=>0,'msg'=>'该邮箱已经注册过')));	
		}
		


		if($r['xieyi']){
			if(!$_POST['xieyi']){
				die(json_encode(array('status'=>0,'msg'=>'你还没有同意注册协议')));			
			}
		}
		$data=array();
		$data['regtime'] 	= time();			
		$data['password']	= md5($this->_post('password'));
		$data['username']	= $this->_post('username');
		//$data['email']	= $this->_post('email');
		$data['telephone']	= $this->_post('Mobile')? $this->_post('Mobile') :'';
		//$data['linkname']	= $this->_post('linkman') ? $this->_post('linkman') : '';
		$data['hotelname']	= $this->_post('hotelname');
		$data['regip']		= $_SERVER['REMOTE_ADDR'];
		$res=$user->data($data)->add();
		//echo $user -> getLastSql();
		if($res){
			die(json_encode(array('status'=>1,'msg'=>'会员注册成功!页面跳转中!')));	
		}else{
			die(json_encode(array('status'=>0,'msg'=>'注册失败')));	
		}
	}
	//验证码验证
	public function verifyCheck(){
		if(md5($_GET['pars']) == $_SESSION['verify']){
			echo 1;
		}else{
			echo 0;
		}
	}	
	//用户名验证
	public function userCheck(){
		$data['username']  = $this->_get('user');
		$res  = M('member')->where($data)->count();
		if($res > 0){
			echo 1;
		}else{
			echo 0;
		}
	}	
	//酒店用户名验证
	public function hotelUserCheck(){
		$data['username']  = $this->_get('user');
		$res  = M('member_hotel')->where($data)->count();
		if($res > 0){
			echo 1;
		}else{
			echo 0;
		}
	}

	//酒店会员注册
	public function hotelreg(){
		$this->getbasic();
		$set = M('member_set');
		$r = $set->select();
		if($r[0]['iscode']){
			$this->assign('code',1);
		}
		if($r[0]['xieyi']){
			$this->assign('xieyi',$r[0]['xieyi']);
		}
		$basic=M('basic');
    	$row=$basic->select();
		$title="酒店会员注册-".$row[0]['webname'];
    	$this->assign('title',$title);  
		$this->assign('keywords',$row[0]['webname']); 	
		$this->assign('description',$row[0]['description']);
		$this->assign('copyright',$row[0]['copyright']);
		$this->display();
	}
	//验证码
	Public function verify(){
		import('@.ORG.Util.Image');
		import('@.ORG.Util.String');
		Image::buildImageVerify(4,1);
	}
	public function getbasic(){
    	$basic=M('basic');
    	$row=$basic->select();
    	$this->assign('title',$row[0]['webname']);  
		$this->assign('keywords',$row[0]['webname']); 	
		$this->assign('description',$row[0]['description']);
		$this->assign('copyright',$row[0]['copyright']);
    }
    public function xieyi(){
    	$xieyi=M('member_set');
    	$content=$xieyi->select();
		$basic=M('basic');
    	$row=$basic->select();
		$title="注册协议-".$row[0]['webname'];
    	$this->assign('title',$title);  
		$this->assign('keywords',$row[0]['webname']); 	
		$this->assign('description',$row[0]['description']);
		$this->assign('copyright',$row[0]['copyright']);
    	$this->assign('xieyi',$content[0]['xieyi']);
    	$this->display();
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
	//	curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header_arr );
		curl_setopt ( $curl, CURLOPT_POST, 1 ); // 发送一个常规的Post请求
		curl_setopt ( $curl, CURLOPT_POSTFIELDS, $post_str ); // Post提交的数据包
		curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, 0 ); // 使用自动跳转
	//	curl_setopt ( $curl, CURLOPT_COOKIEJAR, $cookie_file ); // 存放Cookie信息的文件名称
	//	curl_setopt ( $curl, CURLOPT_COOKIEFILE, $cookie_file ); // 读取上面所储存的Cookie信息
		curl_setopt ( $curl, CURLOPT_REFERER, $referer ); //设置Referer
	//	curl_setopt ( $curl, CURLOPT_USERAGENT, $_SERVER ['HTTP_USER_AGENT'] ); // 模拟用户使用的浏览器
		curl_setopt ( $curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1; rv:9.0.1) Gecko/20100101 Firefox/9.0.1" ); // 模拟用户使用的浏览器
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
		curl_setopt ( $curl, CURLOPT_HEADER, false ); //获取header信息
		$result = curl_exec ( $curl );
		return $result;
	}
		//新增验证代码
	//用户名
	public function checkname(){
		$data['username'] =$_POST['username'];
		$ism = M('member') ->where($data)->select();
		if($ism){
			echo 1;
		}else{
			echo 2;
		}

	}
	//手机号
	public function checkMobile(){
		$data['telephone'] =$_POST['Mobile'];
		$ism = M('member') ->where($data)->select();
		if($ism){
			echo 1;
		}else{
			echo 2;
		}

	}

	//邮箱
	public function checkemails(){
		$row = M('member')-> where("email = '{$_POST['email']}'") -> select();
		if($row){
			echo 1;
		}else{
			echo 2;
		}

	}
	public function checkverify(){
		if(md5($_POST['verify']) == $_SESSION['verify']){
			echo 1;
		}else{
			echo 2;
		}

	}
}
?>