<?php
class MemberAction extends BaseAction{
	public function change_password(){
		if($this->isGet()){
			$this->display();
		}else{
			$use['hotelid']=session('hotel_id');
			$user = M('member_hotel')->where($use)->find();
			if(empty($_POST['old_pwd'])){
				$this->error('原密码不能为空');
			}elseif(md5($_POST['old_pwd'])!=$user['password']){
				$this->error('原密码不正确');
			}
			if(empty($_POST['pwd'])){
				$this->error('新密码不能为空');
			}
			if(empty($_POST['pwd2'])){
				$this->error('确认密码不能为空');
			}
			if($_POST['pwd2']!=$_POST['pwd']){
				$this->error('两次输入的密码不一致');
			}
			$row=M('member_hotel')->where($use)->setField('password',md5($_POST['pwd']));
			if($row){
				 die(json_encode(array('status'=>1,'info'=>'操作成功')));    
			}else{
				 die(json_encode(array('status'=>0,'info'=>'操作失败')));    
			}
		}
	}
	public function change(){
		$use['hotelid']=session('hotel_id');

		$user = M('member_hotel')->where($use)->find();
		//dump($user);
		$this->assign('user',$user);
		if($_POST){
			if(!empty($_POST['password'])){
				$data['password'] = md5($_POST['password']);
			}
			if(empty($_POST['address'])){
				$this->error('地址不能为空');
			}
			if(empty($_POST['linkname'])){
				$this->error('姓名不能为空');
			}
			if (!empty($_POST['linkname'])) {
                if (preg_match('/[\x{4e00}-\x{9fa5}]{2,10}/u', $_POST['linkname']) == 0) {
                    $this->error('真实姓名必须在2-10个汉字之间');
                }
            }
			if(empty($_POST['telephone'])){
				$this->error('手机不能为空');
			}
			if (!empty($_POST['telephone'])) {
                $Mobilepreg = '/^[0-9]{11}$/';
                if (preg_match($Mobilepreg, $_POST['telephone']) == 0) {
                    $this->error( '手机号或电话号码错误');
                }
            }
/* 			if(empty($_POST['chuanzhen'])){
				$this->error('传真不能为空');
			} */
			$data['linkname']	=$this->_post('linkname');
			$data['chuanzhen']		=$this->_post('chuanzhen');
			$data['telephone']	=$this->_post('telephone');
			$data['address']	=$this->_post('address');
			$data['qq']			=$this->_post('qq');
			$data['tuijianren'] = $this->_post('tuijianren');
			//为确保手机号码的唯一性进行验证
			 if($this->_post('telephone') and  $this->_post('telephone') != $user['telephone']){ 
			 		$t=M('member_hotel')->where('telephone = '.$this->_post('telephone'))->find();
			      if($t){
			      	$this->error( '该手机号已经注册过');
			       //die(json_encode(array('status'=>0,'info'=>'该手机号已经注册过')));    
			  }
		   }
			$row = M('member_hotel')->where($use)->data($data)->save();


             

		if($row){

			$res = $this->register_hotel();
			die(json_encode(array('status'=>1,'info'=>'操作成功'.$res)));    
		}else{
			die(json_encode(array('status'=>0,'info'=>'操作失败')));    
		}


		}else{
			$this->display();
		}
	}


	/**
	 * 商城端注册
	 */
	public function register_hotel(){
		$info = M('member_hotel')->where(['username' => $_SESSION['hotel_name']])->field('address,telephone,password,tuijianren')->find();
		$address = str_replace(' ','',$info['address']);
		$data = [
			'mobile' => $info['telephone'],
			'password' => $info['password'],
			'province_id' => null,
			'city_id' => null,
			'region_id' => $this->getmap($address),
			'town_id' => null,
			'referee_mobile' => $info['tuijianren']
		];
		$str = $this->encrypt(json_encode($data));
		$url = C('javadomain').'/api/shop/member/hotel-regist.do?params='.$str;
		$res = $this->curl_get($url);
		// var_dump($str);exit();
		return $res->message;
	}

		/**
	 * GET
	 */
	public function curl_get($url){
		// 百度地图的key
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		$res = curl_exec($ch);
		curl_close($ch);
		return json_decode($res);
	}
	/**
	 * 获取经纬度
	 * @param	string		地址信息
	 */
	public function getmap($str = '广东省广州市番禺大学城南路'){
		$url = 'http://api.map.baidu.com/geocoder/v2/?address='.$str.'&output=json&ak=1WUEFbk93ac9WfceIMKzXGMRywP08Wz2'; //GET请求
		// $key = '35e38873ef563e681945f209da17d9c1';
		// $url = 'http://restapi.amap.com/v3/geocode/geo?key=35e38873ef563e681945f209da17d9c1&address='.$str;
		$info = $this->curl_get($url);
		if ($info->status == 0) {
			$map = $info->result->location->lat.','.$info->result->location->lng;
			// echo $map;
			return $this->getaddr($map);
		}
		return '';
	}
	/**
	 * 根据经纬度获取对应的省市编号
	 * @param	string		经纬度
	 */
	public function getaddr($map = '39.934,116.329'){
		$url = 'http://api.map.baidu.com/geocoder/v2/?location='.$map.'&output=json&pois=1&ak=1WUEFbk93ac9WfceIMKzXGMRywP08Wz2'; //GET请求
		$info = $this->curl_get($url);
		if($info->status == 0){
			$adcode = $info->result->addressComponent->adcode;
			return $adcode;
		}
		return $info;
	}

	public function getmember($arr = ''){
		$arr = [
			'member' => '13333333333',
			'password' => md5('123456'),
			'tuijianren' => '16666666666',
			'adcode' => '440133'
		];
		$res = $this->encrypt(json_encode($arr));

		$info = [
			'result' => 1,
			'data' => $res
		];
		echo json_encode($info);
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
	 * DES 解密
	 * @param	string	$data	加密数据
	 * @param	string	$key	加密秘钥
	 * 
	 */
	function decrypt($str, $key = 'haozhaoli510.com')      
	{ 
		$key = substr($key, 0, 8);
		$str = hex2bin($str);
		$str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);      
		  
		$block = mcrypt_get_block_size('des', 'ecb');      
		$pad = ord($str[($len = strlen($str)) - 1]);      
		return substr($str, 0, strlen($str) - $pad);      
	}

	public function hotel(){
		$this->_menu_acvion='hotel-info';
		$map['hotelid']=session('hotel_id');
		$row = M('member_hotel')->where($map)->find();
		foreach($row as $k=>$v){
			$this->assign("$k",$v);
		}
		if($row['city']){
			$area= explode(',',$row['city']);
		}
		//城市
		if($area){
			$cc= explode('_',R('Area/getAreaDefault',array($area[0],$area[1],$area[2])));
			$this->Prov=$cc[0];
			$this->City=$cc[1];
			$this->Area=$cc[2];
			
		}else{
			$cc=explode('_',R('Area/getAreaDefault'));
			$this->Prov=$cc[0];
			$this->City=$cc[1];
		}
		$content=M('liansuo')->select();
		$this->assign('ls',$content);
		$r = M('sheshi')->select();
		$this->assign('sheshis',$r);
		$cy = M('canyin')->select();
		$this->assign('canyins',$cy);
		$data=M('creditcard')->select();
		$this->assign('creditcards',$data);
		$kfss = M('roomsheshi')->select();
		$this->assign('kfsheshis',$kfss);
		$yule = M('yule')->select();
		$this->assign('yules',$yule);
		$xing=M('xingji')->limit(12)->select();
		$this->assign('xj',$xing);
		$theme=M('theme')->select();
		$this->assign('themes',$theme);		
		$this->display();
	}

	public function add(){
		$m = M('member_hotel');
		$m->create();
		if(!empty($_POST['sheshi'])){
			$m->sheshi = json_encode($_POST['sheshi']);
		}
		if(!empty($_POST['canyin'])){
			$m->canyin = json_encode($_POST['canyin']);
		}
		if(!empty($_POST['kfsheshi'])){
			$m->kfsheshi = json_encode($_POST['kfsheshi']);
		}
		if(!empty($_POST['yule'])){
			$m->yule = json_encode($_POST['yule']);
		}
		if(!empty($_POST['creditcard'])){
			$m->xinyongka = json_encode($_POST['creditcard']);
		}
		$m->introduce = stripslashes(htmlspecialchars_decode($_POST['introduce']));
		$m->tip = stripslashes(htmlspecialchars_decode($_POST['tip']));
		$m->city= $this->_post('province').','.$this->_post('city').','.$this->_post('area');
		$m->city2 	= $_POST['cityfj'];
		$m->city3 	= $_POST['cityfj2'];
		$m->xingji 	= $_POST['xingji'];
		$res = $m->where("username = '{$_SESSION['hotel_name']}'")->save();


			if($row){
				 die(json_encode(array('status'=>1,'info'=>'操作成功')));
			}else{
				 die(json_encode(array('status'=>0,'info'=>'操作失败')));    
			}
	}

	public function dibiao(){
		$this->_menu_acvion='hotel-info';
		$m = M('fjdb');
		$id = session('hotel_id');
		$row = $m->where("hotelid = $id")->select();
		foreach($row as $v){
			foreach($v as $k=>$vv){
				$this->assign($k,$vv);
			}
		}
		$dx = M('college');
		$r1 = $dx->distinct(true)->field('province')->order('id')->select();
		$this->assign('dx',$r1);
		$dt = M('subway');
		$r2 = $dt->distinct(true)->field('city')->select();
		$this->assign('dt',$r2);
		$jc = M('airport');
		$r3 = $jc->distinct(true)->field('province')->select();
		$this->assign('jc',$r3);
		$hc = M('train');
		$r4 = $hc->distinct(true)->field('province')->select();
		$this->assign('hc',$r4);
		$hz = M('exhibit');
		$r5 = $hz->distinct(true)->field('province')->select();
		$this->assign('hz',$r5);
		$this->display();
	}

	public function map(){
		$this->_menu_acvion='hotel-info';
		$m = M('member_hotel');
		$row = $m->where("username = '{$_SESSION['hotel_name']}'")->find();		
		$hotelid=$row['hotelid'];
		$map=$row['map'];
		if(empty($map)){$this->redirect('map2');}else{
		$arr=explode(',',$map);
		$this->assign('x',$arr[0]);
		$this->assign('y',$arr[1]);
		$this->assign('hotelname',$row['hotelname']);
		$this->display();}		
	}
}
?>