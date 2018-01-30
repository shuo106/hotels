<?php
class BasicAction extends CommonAction{
	//基础设置
	public function index(){
		if($this->isGet()){
			$this->basicinfo= M('basic')->find();
			$this->display();
		}else{
			$title = '/^.{6,300}$/';
			if(preg_match($title,$_POST['webname'])==0){
				$this -> json_die(0,'网站名称必须在2-100个汉字之间');
			}
			$key = '/^.{15,900}$/';
			if(preg_match($key,$_POST['keywords'])==0){
				$this -> json_die(0,'关键词必须在5-300个汉字之间');
			}
			$desc = '/^.{30,1500}$/';
			if(preg_match($desc,$_POST['description'])==0){
				$this -> json_die(0,'内容摘要必须在10-500个汉字之间');
			}
			$rs=M('basic')->where('id=1')->save($_POST);
			$rs?$this->json():$this->json(300,'操作失败');
		}
	}
	//安全设置
	public function safety(){
		$m = M('safety');
		if($this->isGet()){
			$row = $m->select();
			$ip = unserialize($row[0]['ip']);
			$ip = implode("\r\n",$ip);
			$this->assign('id',$row[0]['id']);
			$this->assign('number',$row[0]['number']);
			$this->assign('locktime',$row[0]['locktime']);
			$this->assign('ip',$ip);
			$this->display();
		}else{
			if(!empty($_POST['ip'])){
				$ip = preg_split('/\s+/s',$_POST['ip']);
				$ip = serialize($ip);
			}
			$key = '/^\d$/';
			if(preg_match($key,$_POST['number'])==0){
				$this -> json_die(0, '限次数请输入0-9');
			}
			if(preg_match($key,$_POST['locktime'])==0){
				$this -> json_die(0, 'IP锁定时间请填写0-9');
			}
			if($_POST['locktime'] < 0 ){
				$this -> json_die(0, 'IP锁定时间请填写0-9');
			}
			$data = array();
			$data['ip'] 		= $ip;
			$data['number']		= $_POST['number'];
			$data['locktime']	= $_POST['locktime'];
			$id = intval($_POST['id']);
			if($id){
				$row = $m->where("id = $id")->data($data)->save();
			}else{
				$row = $m->data($data)->add();
			}
			$row?$this->json():$this->json(300,'保存失败');
		}
	}
	//支付设置
	public function pay(){
		$m= M('basic');
		if($this->isGet()){
			$pay =$m->getField('pay');
			$this->pay= json_decode($pay,true);
			$this->display();
		}else{
			$pay = array();
			foreach ($_POST['interface'] as $k => $v) {
				$pay[$k]['interface'] 	= $v;
				$pay[$k]['isUse'] 		= $_POST['isUse'][$k] 		? $_POST['isUse'][$k] 		: 0;
				$pay[$k]['Account'] 	= $_POST['Account'][$k] 	? $_POST['Account'][$k] 	: 0;
				$pay[$k]['AccountId'] 	= $_POST['AccountId'][$k] 	? $_POST['AccountId'][$k] 	: 0;
				$pay[$k]['Key'] 		= $_POST['Key'][$k] 		? $_POST['Key'][$k] 		: 0;
			}
			$data['pay'] = json_encode($pay);
			$res = $m-> where('id=1') -> save($data);
			$res?$this->json():$this->json(300,'保存失败');
		}
	}
	//短信设置
	public function sms(){
		$sms=include 'Admin/Conf/message.php';
		if($this->isGet()){
			$this->sms= is_array($sms)?$sms:0;
			$this->display();
		}else{
			$data=array();
			$data['smsname']  			= $this->_post('smsname'); //短信接口用户名
			$data['smspwd']	  			= $this->_post('smspwd');	//短信接口密码
			$data['sign']	  			= $this->_post('sign');	//短信签名
			$data['glsj']				= $this->_post('glsj');	//管理手机
			$data['regSend']  			= $this->_post('regSend'); //注册是否发送短信
			$data['regSms']	  			= $this->_post('regSms');	//注册短信内容
			
			$data['hotelOrderSend']  	= $this->_post('hotelOrderSend'); //酒店预订后是否发送短信
			$data['hotelOrderSms'] 		= $this->_post('hotelOrderSms'); //酒店预订短信
			$data['hotelConfirmSend'] 	= $this->_post('hotelConfirmSend'); //酒店确认后是否发送短信
			$data['hotelConfirmSms'] 	= $this->_post('hotelConfirmSms'); //酒店确认后短信
			$data['hotelPaySend']  		= $this->_post('hotelPaySend');	 //酒店支付后是否发送短信
			$data['hotelPaySms']  		= $this->_post('hotelPaySms'); 	//酒店支付后短信
			if($data){
				$reArr='<?php';
				$reArr.="\n 	return array(\n";
				foreach($data as $k=>$v){
					$reArr.='		\''.$k.'\'=>\''.$v."',\n";
				}
				$reArr.="	);\n".'?>';
			}
			if($reArr){
				file_put_contents('Admin/Conf/message.php',$reArr);
				$this->json();
			}else{
				$this->json(300,'保存失败');
			}
		}
	}
	//邮箱设置
	public function email(){
		if($this->isGet()){
			$m = M('email');
			$r = $m->select();
			foreach($r as $v){
				foreach($v as $k=>$vv){
					$this->assign($k,$vv);
				}
			}
			$this->display();			
		}else{
			$smtp = '/^.{5,60}$/';
			if(preg_match($smtp,$_POST['smtp'])==0){
				$this->json(300,'SMTP服务必须在5-60位之间');
			}
			$email = '/\w+@\w+(\.\w+){0,30}(\.\w+)/';
			if(preg_match($email,$_POST['user'])==0){
				$this->json(300,'邮箱帐号不正确');
			}
			if(preg_match($smtp,$_POST['password'])==0){
				$this->json(300,'密码必须在5-60位之间');
			}
			$port = '/^\d{2,5}$/';
			if(preg_match($port,$_POST['port'])==0){
				$this->json(300,'端口必须在2-5之间的一个正整数');
			}
			if(!empty($_POST['content'])){
				$con = '/^.{6,3000}$/';
				if(preg_match($con,$_POST['content'])==0){
					$this->json(300,'签名必须在2-1000汉字之间');
				}
			}
			$m = M('email');
			$m->create();
			if(!$_POST['id']){
				$row = $m->add();
			}else{
				$row = $m->where("id = {$_POST['id']}")->save();
			}
			$row?$this->json():$this->json(300,'保存失败');
		}
	}
	//客服设置
	public function qq(){
		if($this->isGet()){
			$this->QQ = M('basic')->where('id=1')->field('isqq,qq')->find();
			$this->display();
		}else{
			$data['qq']= $this->_post('qq');
			$data['isqq']= $this->_post('isqq');
			$row =M('basic')->where('id=1')->save($data);
			$row?$this->json():$this->json(300,'保存失败');			
		}
	}
	//授权信息
	public function authorize(){
		if($this->isGet()){
			$auth= 'Admin/Common/authorize.json';
			if(is_file($auth)){
				$this->isauth=1;
				$json_string = file_get_contents($auth);
				$this->ahthArr= json_decode($json_string ,true);
			}else{
				$this->isauth=0;
			}
			$this->display();			
		}else{
			$url  =$this->_get('url');
			$code =$this->_get('code');
			$get_url ='http://www.pengchengsoft.com/authorize.php?action=auth&url='.$url.'&code='.$code;
			$res = $this->http_get($get_url);
			$reArr=json_decode($res,true);
			if($reArr['status']!==1){
				$putStr='{';
				foreach($reArr as $k=>$v){
					$putStr.='"'.$k.'":"'.$v.'",';
				}
				$putStr=rtrim($putStr,',');
				$putStr.='}';
				$authFile= 'Admin/Common/authorize.json';
				file_put_contents($authFile,$putStr);
				$this->json(200,"授权信息更新成功！");
			}else{
				$this->json(200,"您填写的域名或授权信息有误，无法获得信息！");
			}			
		}
	}
	//服务器信息
	public function server(){
		$this->assign('servername',$_SERVER['SERVER_NAME']);
		$this->assign('serverip',$_SERVER['SERVER_ADDR']);
		$this->assign('serverport',$_SERVER['SERVER_PORT']);
		$this->assign('serversoftware',$_SERVER['SERVER_SOFTWARE']);
		$this->assign('serversoftware',$_SERVER['SERVER_SOFTWARE']);
		$this->assign('serveros',PHP_OS);
		$this->assign('timeout',get_cfg_var("max_execution_time"));
		$this->assign('server_patch',$_SERVER['DOCUMENT_ROOT']);
		$this->assign('server_up',get_cfg_var ("upload_max_filesize"));
		$this->assign('server_post',get_cfg_var ("post_max_size"));
		$this->display();		
	}
	public function preview(){
		$id = intval($_GET['id']);
		$m = M('basic');
		$row = $m->field('thumb')->where("id = $id")->select();
		$this->assign('thumb',$row[0]['thumb']);
		$this->display();
	}
	private function http_get($url){
		$oCurl = curl_init();
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
}
?>