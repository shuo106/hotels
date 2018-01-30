<?php
class OrderAction extends BaseAction{
	
	public function index(){
		$order=M('order');
		import('ORG.Util.Page');
		//状态
		$data['pchotel_order.hotelid']=session('hotel_id');
		if($_GET['status']){
			$data['pchotel_order.status']=$_GET['status'];
		}
		//名称
		if($_GET['title']){
			$data['pchotel_room.title']=array('like','%'.$_GET['title'].'%');
		}
		//来源
		if($_GET['from']){
			$data['pchotel_order.from']=$_GET['from'];
		}
		//订单号
		if($_GET['orderid']){
			$data['pchotel_order.orderid']=substr($_GET['orderid'], 10);
		}
		//预定人
		if($_GET['linkman']){
			$data['pchotel_order.linkman']=array('like','%'.$_GET['linkman'].'%');
		}
		$this->total=$count=$order->where($data)->count();
		$this->totalprice=$order->where($data)->sum('shoufei');
		$page=new Page($count,15);
		$show=$page->show();
		$content=$order->join('pchotel_room ON pchotel_order.roomid = pchotel_room.id')
						->field('pchotel_room.roomtype,pchotel_order.*')
						->where($data)->order('orderid desc')
						->limit($page->firstRow.','.$page->listRows)
						->select();
		
		$this->assign('orders',$content);
		$this->assign('page',$show);
		$this->display();
		
	}
	public function chaxun(){
					
		$this->display();
	}
	public function search(){
		$name=$_POST['linkman'];
		$order_no=$_POST['order_no'];
		$telephone=$_POST['telephone'];
		$fangtype=$_POST['fangtype'];
		$condition['hotelid']=session('hotel_id');
		
		if(!empty($name)){
			$condition['linkman']="$name";
		}
		if(!empty($order_no)){
			$condition['id']=substr($order_no,10);
		}
		if(!empty($telephone)){
			$condition['telephone']="$telephone";
		}
		if(!empty($fangtype)){
			$condition['_string']="fangtype like '%$fangtype%'";
		}
		$order=M('order');
		$content=$order->where($condition)->select();
		$this->assign('orders',$content);		
		$this->display();
	}
	public function edit(){
		$id=$_GET['id'];
		$order=M('order');
		$content=$order->join('pchotel_room ON pchotel_order.roomid = pchotel_room.id')
						->field('pchotel_order.*,pchotel_room.roomtype')
						->where("orderid=$id")->find();
		$this->assign('order',$content);
		$this->display();
	}
	public function sms(){
		$id=$_GET['id'];
		$order=M('order');
		$content=$order->where("orderid=$id")->find();
		$this->assign('order',$content);
		$this->display();
	}
	public function smsok(){
				Vendor('ChuanglanSmsHelper.ChuanglanSmsApi');
				$SMS = new ChuanglanSmsApi();
				$smsData =include 'Admin/Conf/message.php';
				$b=M('basic');
				$row=$b->select();
				$telephone=$_POST['smsphohe'];
				$cn=$_POST['smscon'];
				$name=iconv("UTF-8","gb2312",$smsData['smsname']);
				$pwd=iconv("UTF-8","gb2312",$smsData['smspwd']);
				$c=$cn;
				
				$con = "$c";	//短信内容
				$result=$SMS->sendSMS($name,$pwd,$telephone,$con);
				if(!$result)
				{header("Content-type:text/html;charset=utf-8");
					echo"发送成功";$this->redirect('index');
				}
				else
				{header("Content-type:text/html;charset=utf-8");
					echo"发送失败，错误码：{$result}";
				}
		}
		
	public function update(){
		$wh['orderid']=$id=$this->_request('id');
		$status=$this->_request('status');
		$res=M('order')->where($wh)->setField('status',$status);
		if($res){
			Vendor('ChuanglanSmsHelper.ChuanglanSmsApi');
			$SMS = new ChuanglanSmsApi();
			$smsData=include '../Admin/Conf/message.php';
			if($status == 2 && $smsData){
				$orderData= M('order')->where(array('pchotel_order.orderid'=>$id))
									  ->join('pchotel_room ON pchotel_order.roomid =pchotel_room.id')
									  ->join('pchotel_member_hotel ON pchotel_order.hotelid =pchotel_member_hotel.hotelid')
									  ->field('ruzhudate,lidiandate,roomtype,pchotel_order.username,hotelname,pchotel_order.addtime,pchotel_order.telephone,shoufei,nums')
									  ->find();
				$starttime=date('Y-m-d',$orderData['ruzhudate']);					  
				$ordertime=date('Y-m-d',$orderData['addtime']);		
				$endtime=date('Y-m-d',$orderData['lidiandate']);
				$webname=M('basic')->getField('webname');
				$telephone=$orderData['telephone'];
				//是否发送短信
				if($smsData['hotelConfirmSend']=='1'){
					$relArr=array('#WEBNAME#','#LOGINNAME#','#ORDERTIME#','#ORDERNUMS#','#ORDERTOTAL#','#HOTELNAME#','#ROOMNAME#','#STARTTIME#','#ENDTIME#');
					$subArr=array($webname,$orderData['username'],date('Y-m-d H:i:s',$ordertime),$orderData['nums'],$orderData['shoufei'],$orderData['hotelname'],$orderData['roomtype'],$starttime,$endtime);
					$SendSms=str_replace($relArr,$subArr,$smsData['hotelConfirmSms']);
					$name=iconv("UTF-8","gb2312",$smsData['smsname']);
					$pwd=iconv("UTF-8","gb2312",$smsData['smspwd']);
					$con=$SendSms;
					$res=$SMS->sendSMS($name,$pwd,$telephone,$con);
				} 
			}
			die(json_encode(array('status'=>1,'msg'=>'操作成功')));
		}else{
			die(json_encode(array('status'=>0,'msg'=>'操作错误')));
		}
	}
	public function delete(){
		$id=$_GET['id'];
		$order=M('order');
		$rs=$order->where("orderid=$id")->delete();	
		if($rs){$this->redirect('index');}
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

	/**
	 * 客人离店后，结束订单
	 * ajax GET
	 * @param	$id		订单id
	 */
	public function complete()
	{
		$orderid = intval($this->_get('id'));
		// $orderid = 3774;
		$order = M('order')->where(['orderid' => $orderid])->field('orderid,username,addtime,shoufei,telephone,status,payment_plugin_id')->find();
		if ($order['status'] == 7 && $order['payment_plugin_id'] > 0) {
			// 用户注册时的电话
			$tel = M('member')->where(['username' => $order['username']])->getField('telephone');
			
			// M()
			$plugin_id = [1 => 'alipayDirectPlugin', 2 => 'weixinPayPlugin', 3 => 'scorepayPlugin'];
			$ordernumber = $order['orderid'].$order['addtime'];
			$param = [
				'order_id' => intval($order['orderid']),
				'pay_amount' => $order['shoufei'],
				'mobile' => $tel,
				'payment_plugin_id' => $plugin_id[$order['payment_plugin_id']]
			];
			$info = $this->encrypt(json_encode($param));
			$url = C('javadomain').'/api/agent/manager/order-complete.do?params='.$info;
			$res = $this->curl_get($url);
			if ($res->result == 1) {
				$data['payment_plugin_id'] = -1;
				$num = M('order')->where(['orderid' => $orderid])->save($data);
				$return = [
					'code' => 200,
					'msg' => '您已成功结算该订单',
					'data' => $num
				];
			} else {
				$return = [
					'code' => 500,
					'msg' => '订单结算失败',
				];
			}
		} elseif ($order['payment_plugin_id'] == -1) {
			$return = [
				'code' => 500,
				'msg' => '该订单已结算'
			];
		} else {
			$return = [
				'code' => 500,
				'msg' => '订单结算失败',
				'data' => $order
			];
		}
		echo json_encode($return);
		return ;

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

	// get请求
	public function curl_get($url)
	{


		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$output = curl_exec($ch);
		curl_close($ch);

		return json_decode($output);
	}
}