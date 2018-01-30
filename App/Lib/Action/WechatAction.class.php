<?php
/*微信版
*	2016-9-30
*/


class WechatAction extends CommonwapAction {

	const BANNER_SRC = '/Public/wap/images/banner.jpg';

	public function index() {
		$today = date('Y-m-d');
		$tomorrow = date('Y-m-d', strtotime('1 day'));
		$days = strtotime($today);
		if ($_GET['start']) {
			$days = (strtotime($_GET['end']) - strtotime($_GET['start'])) / 86400;
		}
		if ($_GET['city']) {
			$city = M('area')->where(array('id' => $this->_get('city')))->getField('name');
			$map['city'] = array('like', '%' . $_GET['city'] . ('DB_HOST'));exit();

		} else {
			$map['city'] = array('like', '%100020%');
		}
		if (intval($_GET['pagesize'])) {
			$pagesize = intval($_GET['pagesize']);
		} else {
			$pagesize = 5;
		}
		if (intval($_GET['page'])) {
			$pageindex = intval($_GET['page']);
		} else {
			$page = 1;
		}
		$map['is_delete'] = 0;
		$map['is_tuijian'] = 1;
		$begin = ($page -1)*$pagesize;

		$tui = M('member_hotel')->where($map)->limit($begin.','.$pagesize)->select();
		if (empty($tui)) {
			$map['city'] = array('like', '%100020%');
			$tui = M('member_hotel')->where($map)->limit($pagesize)->select();
		}
		foreach ($tui as $k => $v) {
			$tui["$k"]['xingji'] = M('xingji')->where('id=' . $v['xingji'])->getField('id');
			$tui["$k"]['src'] = M('photo')->where('hotelid=' . $v['hotelid'])->getField('src');
			//价格
			$tui["$k"]['src'] = C('webroot').$tui["$k"]['src'];
			$rooms = M('room')->where('hotelid=' . $v['hotelid'])->field('id')->select();
			$min = 9999999;
			unset($tui["$k"]['username']);
			unset($tui["$k"]['password']);
			foreach ($rooms as $vv) {
				$price = $this->getLowestPrices($vv['id']);
				if ($min > $price) {
					$min = $price;
				}
			}
			if ($min != 9999999) {
				$price = explode('_', $min);
				$tui["$k"]['price'] = $price[0];
			}
			if (!$tui["$k"]['price']) {
				$tui["$k"]['price'] = '';
			}
		}
		// $this->tui = $tui;
		$base = M('basic')->find();
		if (base) {
			$phone = $base['glsj'];
		} else {
			$phone = '400-0791-955';
		}
		$res = [
			'msg' => 'ok',
			'code' => 200,
			'data' => [
				'today' => $today,
				'tomorrow' => $tomorrow,
				'days' => 1,
				'phone' => $phone,
				'banner' => C('webroot').self::BANNER_SRC,
				'tuijian' => $tui,
			]
		];
		$this->response($res, 200);
		//print_r($map);die;
		// $this->display();
	}
	//登陆
	public function login() {
		if ($_SESSION['id']) {
			header("location:" . __ROOT__ . "/Wechat/center");
			exit();
		}
		$this->display();
	}
	//安全退出
	public function loginout() {
		session_unset();
		session_destroy();
		// header("location:" . __ROOT__ . "/Wechat/login.html");
		$this->response(['result' => 2, 'code' => 200, 'message' => '您已成功退出']);
	}
	//找回密码
	public function findpwd() {
		if ($this->isGet()) {
			$this->display();
		} else {
			$Proof = $this->_post('Proof');
			$password = $this->_post('password');
			$password2 = $this->_post('password2');
			$telephone = $this->_post('telephone');
			if (!$this->checkCode($telephone, $this->_post('code'))) {
				die(json_encode(array('status' => 0, 'msg' => '验证码错误！')));
			}
			if (!$password || !$telephone) {
				die(json_encode(array('status' => 0, 'msg' => '手机号和密码不能为空')));
			}
			if ($password != $password2) {
				die(json_encode(array('status' => 0, 'msg' => '两次密码输入不一致！')));
			}
			$one = M('member')->where(array('telephone' => $telephone))->count();
			if ($one) {
				$res = M('member')->where(array('telephone' => $telephone))->setField('password', md5($password));
				if ($res) {
					die(json_encode(array('status' => 1, 'msg' => '修改密码成功，请使用新密码登录')));
				} else {
					die(json_encode(array('status' => 0, 'msg' => '修改密码失败')));
				}
			} else {
				die(json_encode(array('status' => 0, 'msg' => '该手机号没有注册')));
			}
		}
	}
	//用户资料
	public function myhome() {
		$uid = session('id');
		if (!$uid) {
			header("location:" . __ROOT__ . "/Wechat/login.html");
		}
		$this->uinfo = M('member')->where(array('id' => $uid))->find();
		$this->display();
	}
	//用户密码
	public function mypass() {
		$uid = session('id');
		if (!$uid) {
			header("location:" . __ROOT__ . "/Wechat/login.html");
		}
		$this->uinfo = M('member')->where(array('id' => $uid))->find();
		$this->display();
	}
	//用户中心
	public function center() {
		$uid = session('id');
		$name = session('name');
		if (!$uid) {
			header("location:" . __ROOT__ . "/Wechat/login.html");
		}
		$this->uinfo = $u = M('member')->where(array('id' => $uid))->find();
		//print_r($u);die;
		$this->ordernums = M('order')->where(array('username' => $name))->count();
		$this->commentnums = M('comment')->where(array('uid' => $uid))->count();
		$this->tnums = M('tixian')->where('uid=' . $uid)->count();
		$this->display();
	}
	//用户信息保存
	Public function upInfo() {
		$info = array();
		$info['truename'] = $this->_post('truename');
		$info['telephone'] = $this->_post('telephone');
		$info['qq'] = $this->_post('qq');
		$info['address'] = $this->_post('address');
		$info['email'] = $this->_post('email');
		//判断当前邮箱是否其他用户再用，如果存在，则不能保存
		$flag = M('member')->where('id<>' . session('id') . " and email='" . $info['email'] . "'")->find();
		// echo M('member')->getlastsql();
		if ($flag) {
			die(json_encode(array('status' => 0, 'msg' => '该邮箱已经被注册')));
		}
		//判断当前手机号码其他用户是否再用，如果存在则不能保存
		$flag = M('member')->where('id<>' . session('id') . ' and telephone=' . $info['telephone'])->find();
		if ($flag) {
			die(json_encode(array('status' => 0, 'msg' => '该手机号已经被注册')));
		}
		$res = M('member')->where(array('id' => session('id')))->save($info);
		if ($res) {
			die(json_encode(array('status' => 1, 'msg' => '保存成功')));
		} else {
			die(json_encode(array('status' => 0, 'msg' => '操作失败')));
		}
	}
	//用户密码保存
	Public function upPassW() {
		$password = $this->_post('password');
		$user['id'] = session('id');
		$user['password'] = MD5($password);
		$isOk = M('member')->where($user)->count();
		if (!$isOk) {
			die(json_encode(array('status' => 0, 'msg' => '原密码输入错误！')));
		}
		$password2 = $this->_post('password2');
		$password3 = $this->_post('password3');
		if (!$password2 || $password3 != $password2) {
			die(json_encode(array('status' => 0, 'msg' => '两次密码输入不一致！')));
		}
		$newPW = MD5($password2);
		$res = M('member')->where(array('id' => session('id')))->setField('password', $newPW);
		if ($res) {
			//unset($_SESSION['id']);
			//unset($_SESSION['name']);
			die(json_encode(array('status' => 1, 'msg' => '保存成功')));
		} else {
			die(json_encode(array('status' => 0, 'msg' => '操作失败')));
		}
	}
	public function register() {
		$this->display();
	}
	//积分
	//积分流水帐
	public function point() {
		$p = M('point p');
		$map['username'] = session('name');
		$count = $p->where($map)->count();
		import("ORG.Util.Page");
		$page = new Page($count, 8);
		$this->page = $page->show();
		$ofield = array('concat(o.addtime,o.id)' => 'oid', 'o.id' => 'order_id');
		$pfield = array('p.total', 'p.type', 'p.ctime', 'concat(p.ctime,p.id)' => 'pid');
		$tfield = array('concat(t.txdate,t.id)' => 'tid', 't.id' => 'tixian_id');
		$field = array_merge($ofield, $pfield, $tfield);
		$list = $p->where($map)
		//->join('pchotel_order o ON p.foreign_key = o.id and p.type=0')
		//->join('pchotel_tixian t ON p.foreign_key = t.id and p.type=1')
		//->field($field)
		->order('p.id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		//var_dump($list);die;
		$this->point = M('Member')->where($map)->getField('point');
		$this->list = $list;
		$this->display();
	}
	//注册
	public function doRegister() {
		$Proof = $this->_post('Proof');
		$password = $this->_post('password');
		$password2 = $this->_post('password2');
		$user = $this->_post('username');
		$err = [];
		if (md5($Proof) != $_SESSION['verify']) {
			// die(json_encode(array('status' => 0, 'msg' => '验证码错误！')));
			$err = [
				'result' => 0,
				'msg' => 'error',
				'message' => '验证码错误！'
			];
		}
		if (!$password || !$user) {
			// die(json_encode(array('status' => 0, 'msg' => '请认真填写注册信息！')));
			$err = [
				'result' => 0,
				'msg' => 'error',
				'message' => '请认真填写注册信息！'
			];
		}
		if ($password != $password2) {
			// die(json_encode(array('status' => 0, 'msg' => '两次密码输入不一致！')));
			$err = [
				'result' => 0,
				'msg' => 'error',
				'message' => '两次密码输入不一致！',
				'code' => 500
			];
		}
		$one = M('member')->where(array('username' => $user))->count();
		$two = M('member_hotel')->where(array('username' => $user))->count();
		if ($one >= 1 || $two >= 1) {
			// die(json_encode(array('status' => 0, 'msg' => '用户名已经注册，请修改用户名。')));
			$err = [
				'result' => 0,
				'msg' => 'error',
				'message' => '用户名已经注册，请修改用户名。',
				'code' => 500
			];
		}
		$info = array();
		$info['username'] = $user;
		$info['password'] = md5($password);
		if (preg_match("/^1[34578]\d{9}$/", $user)) {
			$info['Mobile'] = $user;
		}
		$res = M('member')->add($info);
		if ($res) {
			// session('id', $res);
			// session('name', $user);
			// die(json_encode(array('status' => 1, 'msg' => '注册成功')));
			$result = [
				'result' => -1,
				'code' => 200,
				'message' => '注册成功'
			];
			// $this->response($res, $res['code']);
			// $this->response(['code' => 200])
		} else {
			// die(json_encode(array('status' => 0, 'msg' => '注册失败')));
			$err = ['code' => 500, 'message' => '注册失败'];
		}

		if($err) {
			$this->response($err);
		} else {
			$this->response($result);
		}
	}
	/**
	 * 注册接口 供java调用
	 */
	public function registeruser()
	{
		$str = file_get_contents('php://input');
		$data= json_decode($str);
		// 时间戳
		$timestamp = $data->timestamp;
		// 签名
		$sign = $data->sign;
		// 用户数据
		if (!($this->checkTime($timestamp, $sign))) {
			$err = [
				'result' => 0,
				'msg' => 'error',
				'message' => '请求超时',
				'code' => 500	
			];
			$this->response($err);
		}
		$res = $data->data;
		$user = [];
		$user['username'] = $res->username;
		$user['password'] = $res->password;
		$user['telephone'] = $res->telephone;
		$user['email'] = $res->email;
		$user['regip'] = $res->regip;
		$user['regtime'] = $res->regtime;
		$user['province'] = $res->province;
		$user['address'] = $res->address;
		$user['sex'] = $res->sex;
		if (!$user['username'] || !$user['telephone']) {
			$err = [
				'result' => 0,
				'msg' => 'error',
				'message' => '缺少参数',
				'code' => 500
			];
			// echo json_encode($err);
			$this->response($err);
		}
		$res = M('member')->add($user);
		if ($res) {
			$res = [
				'result' => 1,
				'msg' => 'ok',
				'message' => '注册成功',
				'code' => 200
			];
		} else {
			$res = [
				'result' => 0,
				'msg' => 'error',
				'message' => '注册失败',
				'code' => 500
			];
		}
		$this->response($res);
		// return ;
	}
	/**
	 * 用户修改
	 */
	public function updateuser()
	{
		$str = file_get_contents('php://input');
		$data= json_decode($str);
		// 时间戳
		$timestamp = $data->timestamp;
		// 签名
		$sign = $data->sign;
		// 用户数据
		$res = $data->data;
		$user = [];
		$user['username'] = $res->username;
		$user['password'] = $res->password;
		$user['telephone'] = $res->telephone;
		$user['email'] = $res->email;
		$user['regip'] = $res->regip;
		$user['regtime'] = $res->regtime;
		$user['province'] = $res->province;
		$user['address'] = $res->address;
		$user['sex'] = $res->sex;
		$oldtelephone = $res->oldtelephone;
		// 校验时间戳
		if(!($this->checkTime($timestamp, $sign))) {
			$this->response(['status' => 0, 'message' => '超时'], 500);
		} else {
			if (!$user['username'] || !$user['telephone']) {
				$this->response(['status' => 0, 'message' => '缺少参数'], 500);
			}
			$model = M('member')->where(['telephone' => $oldtelephone])->select();
			if($model) {
				$res = M('member')->where(['telephone' => $oldtelephone])->save($user);
			} else {
				$res = M('member')->add($user);
			}
		}

		if($res) {
			$return = [
				'result' => 1,
				'message' => '用户修改信息成功',
				'code' => 200
			];
		} else {
			$return = [
				'result' => 0,
				'message' => '修改信息失败',
				'code' => 500
			];
		}
		$this->response($return);
		// return ;
	}
	/**
	 * 设置session
	 */
	public function setsessionid()
	{
		session_start();

		$value = session_id();
		$name = PHPSESSID;
		$expire = time() + 86400;
		setcookie($name, $value, $expire);
	}
	/**
	 * token登录
	 */
	public function loginbytoken()
	{
		// $str = file_get_contents('php://input');
		// $data= json_decode($str);
		// sessionid = uniqueid()

		$token = $this->_get('token');
		if (!$token) {
			$err = [
				'result' => 0,
				'message' => '缺少token',
				'code' => 500
			];
			$this->response($err);
		}
		$data = json_decode($this->decrypt($token));
		$user = [];
		if (empty($data->mobile) || empty($data->password)) {
			$err = [
				'result' => 0,
				'message' => 'token不合法',
				'code' => 500
			];
			$this->response($err);
		}
		$user['telephone'] = $data->mobile;
		$user['password'] = $data->password;
		// $model = M('member');
		$res = M('member')->where($user)->field('id,username,telephone')->find();
		// $sql = "SELECT id,username,telephone FROM pchotel_member WHERE password='{$password}' AND telephone='{$mobile}' LIMIT 1";
		// $res = $model->query($sql);
		if ($res) {
			session_unset();
			session_destroy();
			$this->setsessionid();
			session('id', $res['id']);
			session('name', $res['username']);
			$return = [
				'result' => 1,
				'message' => '登录成功',
				// 'token' => session('name'),
				'code' => 200
			];
		} else {
			$user = [];
			$user['telephone'] = $data->mobile;
			$user['username'] = $data->mobile;
			$user['password'] = $data->password;
			$model = M('member')->data($user)->add();
			if ($model) {
				session_unset();
				session_destroy();
				$this->setsessionid();
				session('id', $data->mobile);
				session('name', $data->mobile);
				$return = [
					'result' => 1,
					'message' => '登录成功',
					// 'token' => session('name'),
					'code' => 200
				];
			} else {
				$return = [
					'result' => 0,
					'message' => '登录失败',
					'code' => 500,
				];
			}
		}
		// echo json_encode($res);
		$this->response($return);
		// echo $token;
	}
	/**
	 * 时间戳校验
	 * 
	 */
	private function checkTime($timestamp, $sign)
	{
		$now = time();
		if($timestamp + 60 >= $now || $timestamp - 60 <= $now && md5(base64_encode(md5($timestamp))) == $sign) {
			return true;
		} else {
			return false;
		}
	}
	// 获取登录token 
	public function gettoken()
	{
		// $this->checkUser();
		$key = 'haozhaoli510.com';
		// $model = M('')
		// session('id', 26411);
		$userid = session('id');

		$user = M('member')->where(['id' => intval($userid)])->select();
		$data = [];
		if ($user) {
			// $data['mobile'] = $user['telephone'];
			// $data['password'] = $user['password'];
			$data = [
				'mobile' => $user[0]['telephone'],
				'password' => $user[0]['password']
			];
			$token = $this->encrypt(json_encode($data));
			$res = [
				'result' => 1,
				'data' => $token,
				'code' => 200,
				'message' => '获取token成功'
			];
			
		} else {
			$res = [
				'result' => 0,
				'data' => '',
				'message' => '获取token失败',
				'code' => 500
			];
		}
		$this->response($res);

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
	//获取短信验证码
	public function getcode() {
		$mobile = $this->_post('mobile');
		$getCode = unserialize($_SESSION["{$mobile}_code"]);
		if ((time() - $getCode[$mobile . '-time']) < 60) {
			$this->response(array('result' => 0, 'message' => '不能频繁获取'));
		}
		strlen($mobile) == 11 || die(json_encode(array('reault' => 0, 'message' => '请输入正确的手机号码')));
		$id = M('member')->where('telephone=' . $mobile)->count();
		$id || die(json_encode(array('result' => 0, 'message' => '该手机号没有注册')));
		//六位随机数
		$code = rand(100000, 999999);
		//$code=888888;
		$str = "您的验证码为【" . $code . "】，30分钟内有效!";
		// $rs = $this->sms($mobile, $str);
		$rs = $this->sendSms($mobile, $code);
		if ($rs) {
			//验证码写入session
			$data[$mobile] = $code;
			$data[$mobile . '-time'] = time();
			session("{$mobile}_code", serialize($data));
			$this->response(['result' => 0, 'message' => '获取成功']);
		} else {
			$this->reponse(array('result' => 0, 'message' => '获取失败'));
		}
	}
	//验证码检校
	public function checkCode($mobile, $code) {
		$getCode = unserialize($_SESSION["{$mobile}_code"]);
		if ($code) {
			if ($getCode[(string)$mobile] != $code) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
	//登陆验证
	public function chklogin() {
		// 账号登录
		if ($this->_post('login') == 1) {
			$map['username'] = $this->_post('username');
			$info = M('member')->where($map)->field('password,id,username')->find();
			// $info['password'] || die(json_encode(array('status' => 0, 'msg' => '帐号不存在')));
			// $info['password'] == md5($this->_post('password')) || die(json_encode(array('status' => 0, 'msg' => '密码不正确')));
			if($info['password'] !== md5($this->_post('password'))) {
				$err = [
					'result' => 2,
					'code' => 500,
					'message' => '登录失败'
				];
				$this->response($err);
			}
		} else if ($this->_post('login') == 2) {
			// 手机号登录
			$mobile = $this->_post('mobile');
			$user = M('member')->where(['telephone' => $mobile])->field('id,username')->find();

			$this->checkCode($this->_post('mobile'), $this->_post('code')) || die(json_encode(array('status' => 0, 'msg' => '验证码不正确')));
			$info = M('member')->where('telephone=' . $this->_post('mobile'))->field('id,username')->find();
			//echo M('member')->getlastsql();
			// $info || die(json_encode(array('result' => 0, 'msg' => '帐号不存在')));
			if (!$info) {
				$err = [
					'result' => 2,
					'code' => 500,
					'message' => '帐号不存在'
				];
				$this->response($err);
			}
		}
		if ($info) {
			session('id', $info['id']);
			session('name', $info['username']);
			$this->response(array('result' => 1, 'msg' => '登录成功'));
		} else {
			$return = [
				'result' => 2,
				'message' => '登录失败',
				'code' => 500
			];
			$this->response($return);
		}
	}
	//搜索
	public function search() {
		if ($_GET['start']) {
			$this->days = (strtotime($_GET['end']) - strtotime($_GET['start'])) / 86400;
		}
		if ($_GET['city']) {
			$this->city = M('area')->where(array('id' => $this->_get('city')))->getField('name');
		}
		$this->display();
	}
	//酒店列表
	public function hotels() {
		$tomorrow = date('Y-m-d', strtotime('1 day'));
		$today = date('Y-m-d', time());
		//获取星级类型数据
		$xingji = M('xingji')->select();
		$chain = M('liansuo')->order('id DESC')->select();
		$hotel = M('member_hotel');
		// import("@.ORG.Util.Page");
		$data = array();
		$data['is_delete'] = 0;
		//推荐
		if ($_GET['tui']) {
			$data['is_tuijian'] = 1;
		}
		//星级
		if ($_GET['xingji']) {
			$data['xingji'] = array('in', '1,2,3,4,5');
		}
		//城市
		$city = $this->_get('city') ? $this->_get('city') : '';
		if ($city) {
			//echo $city;
			$cityshuzi = $city;
			$data['city'] = array('like', '%' . $city . '%');
			$city = M('area')->where(array('id' => $city))->getField('name');
			//获取当前区域
			$area = M('area')->where('pid=' . $cityshuzi)->select();
			if (!$area) {
				$pid = M('area')->where(array('id' => $cityshuzi))->getField('pid');
				$area = [];
				array_unshift($area,['id' => $pid, 'name' => '不限']);
			} else {
				array_unshift($area,['id' => $cityshuzi, 'name' => '不限']);
			}
			if ($cityshuzi == 100002) {
				//获取区
				$data['city'] = array('like', '%100020%');
				//获取当前区域
				$area = M('area')->where('pid = 100020')->select();
				$city = '北京';
			}
		} else {
/* 			$data['city'] = array('like', '%100020%');
			//获取当前区域
			$area = M('area')->where('pid=100020')->select();
			$city = '北京'; */
		}
		if ($_GET['area']) {
			$data['city'] = array('like', '%' . $_GET['area'] . '%');
		}
		//连锁
		if ($_GET['chain']) {
			$data['lspp'] = array('like', '%' . $this->_get('chain') . '%');
		}
		//关键字
		if ($_GET['key']) {
			$data['hotelname|address'] = array('like', '%' . $this->_get('key') . '%');
		}
		//星级
		if ($_GET['star']) {
			$data['xingji'] = array('in', $this->_get('star'));
		}
		/*//价格
		if($_GET['price']){
			$price = explode('_',$this->_get('price'));
			$hids= $hotel->where($data)->field('hotelid')->select();
			$ids=array();
			foreach($hids as $k=>$v){
				$roo['hotelid']=$v['hotelid'];
				$rids= M('room')->where($roo)->field('id')->select();
				foreach($rids as $vv){
					$money=$this->getLowestPrices($vv['id']);
					if($money>$price[0] && $money<=$price[1]){
						$ids[]=$v['hotelid'];
					}
				}
			}
			$data['hotelid']=array('in',array_unique($ids));
		}*/
		//价格
		if ($_GET['price']) {
			$price = explode('_', $this->_get('price'));
			$hids = $hotel->where($data)->field('hotelid')->select();
			$hidss = '';
			foreach ($hids as $k => $v) {
				$isok = $this->getHotelMaxMin($v['hotelid'], $price);
				if ($isok) {
					$hidss.= $isok . ',';
				}
			}
			unset($data);
			$data['hotelid'] = array('in', rtrim($hidss, ','));
		}
		//print_r($data);die;
		$count = $hotel->where($data)->count();
		// $Page = new Page($count, 4);
		// $Page->setConfig('theme', '%upPage% %linkPage% %downPage%');
		// $this->page = $Page->show();
		$page = intval($_GET['page']) ? intval($_GET['page']) : 1;
		$pagesize = intval($_GET['pagesize']) ? intval($_GET['pagesize']) : 5;
		$begin = ($page - 1)*$pagesize;
		$hotels = $hotel->where($data)->order('is_tuijian desc,sort desc,hotelid desc')->limit($begin . ',' . $pagesize)->select();
		foreach ($hotels as $k => & $v) {
			//设置。
			$hotels["$k"]['sheshi'] = $sheshi = json_decode($hotels["$k"]['sheshi']); //服务设施
			$hotels["$k"]['canyin'] = $canyin = json_decode($hotels["$k"]['canyin']); //餐饮设施
			$hotels["$k"]['xinyongka'] = $xinyongka = json_decode($hotels["$k"]['xinyongka']); //信用卡
			$hotels["$k"]['kfsheshi'] = $kfsheshi = json_decode($hotels["$k"]['kfsheshi']); //客房设施
			$hotels["$k"]['yule'] = $yule = json_decode($hotels["$k"]['yule']); //娱乐设施
			//有无电脑
			unset($hotels["$k"]['username']);
			unset($hotels["$k"]['password']);
			if (in_array('电脑', $kfsheshi)) {
				$hotels["$k"]['d'] = 1;
			}
			//有无WiFi
			if (in_array('Wifi', $kfsheshi)) {
				$hotels["$k"]['wifi'] = 1;
			}
			//有无停车场
			if (in_array('停车场', $sheshi)) {
				$hotels["$k"]['p'] = 1;
			}
			//行李运送
			if (in_array('行李运送', $sheshi)) {
				$hotels["$k"]['yunsong'] = 1;
			}
			//健身房
			if (in_array('健身房', $yule)) {
				$hotels["$k"]['jiesong'] = 1;
			}
			//餐饮服务
			if ($canyin) {
				$hotels["$k"]['can'] = 1;
			}
			//星级
			//$hotels["$k"]['xingji']=M('xingji')->where('id='.$v['xingji'])->getField('name');
			$hote['hotelid'] = $v['hotelid'];
			//地址
			$areas = explode(',', $v['city']);
			$area1 = M('area')->where('id=' . $areas[0])->getField('name');
			$area2 = M('area')->where('id=' . $areas[1])->getField('name');
			$area3 = M('area')->where('id=' . $areas[2])->getField('name');
			$hotels["$k"]['city'] = $area1 . $area2 . $area3;
			$pho = M('photo')->where($hote)->order('isdefault desc')->field('src')->find();
			$v['src'] = C('webroot').$pho['src'];
			$comment = M('comment')->where($hote)->field('unit')->select();
			$nums = 0;
			$total = 0;
			foreach ($comment as $c) {
				$nums++;
				$total+= rtrim($c['unit'], '%');
				$v['pjnums'] = $nums;
			}
			if ($nums == 0) {
				$v['fen'] = 5;
				$v['pjnums'] = 0;
			} else {
				$v['fen'] = floor($total / $nums / 20 * 100) / 100;
			}
			$rooms = M('room')->where($hote)->field('id')->select();
			$min = 9999999;
			foreach ($rooms as $vv) {
				$price = $this->getLowestPrices($vv['id']);
				if ($min > $price) {
					$min = $price;
				}
			}
			if ($min != 9999999) {
				$price = explode('_', $min);
				$v['price'] = $price[0];
			}
			if (!$v['price']) {
				$v['price'] = '';
			}
		}
		// echo json_encode($hotels);exit();
		// $area = $area?
		$result = [
			'msg' => 'ok',
			'code' => 200,
			'data' => [
				'today' => $today,
				'tomorrow' => $tomorrow,
				'total' => intval($count),
				'days' => 1,
				'area' => $area ? $area : [],
				'xingji' => $xingji,
				'chain' => $chain,
				'info' => $hotels ? $hotels : []
			]
		];
		$this->response($result);
		// $this->hotels = $hotels;
		// $this->display();
	}
	public function getHotelMaxMin($hid, $jiage) {
		$r['hotelid'] = $hid;
		$rs = M('room')->where($r)->field('tjarr,hotelid')->select();
		$hids = false;
		foreach ($rs as $k => $v) {
			$tjarr = explode('|', substr($v['tjarr'], 0, -1));
			//得到当前最小值
			$min = 9999999;
			foreach ($tjarr as $key => $val) {
				$price2 = explode('-', $val);
				$price = explode('_', $price2[1]);
				if ($price[0] < $min) {
					$min = $price[0];
				}
			}
			//如果当前的最小值在这个范围之间,那么就返回hotelid
			if ($min >= $jiage[0] && $min <= $jiage[1]) {
				$hids = $v['hotelid'];
			}
		}
		return $hids;
	}
	//品牌酒店列表
	public function hotelchain() {
		$this->tomorrow = date('Y-m-d', strtotime('1 day'));
		//获取星级类型数据
		$this->xingji = M('xingji')->select();
		$this->chain = M('liansuo')->order('id DESC')->select();
		$hotel = M('member_hotel');
		import("@.ORG.Util.Page");
		$data = array();
		$data['is_delete'] = 0;
		//推荐
		if ($_GET['tui']) {
			$data['is_tuijian'] = 1;
		}
		//星级
		if ($_GET['xingji']) {
			$data['xingji'] = array('in', '1,2,3,4,5');
		}
		//城市
		$city = $this->_get('city') ? $this->_get('city') : '';
		if ($city) {
			//echo $city;
			$this->cityshuzi = $city;
			//$data['city']=array('like','%'.$city.'%');
			$this->city = M('area')->where(array('id' => $city))->getField('name');
			//获取当前区域
			$this->area = $area = M('area')->where('pid=' . $city)->select();
			if ($city == 100002) {
				//获取区
				//$data['city']=array('like','%100020%');
				//获取当前区域
				$this->area = $area = M('area')->where('pid=100020')->select();
				$this->city = '北京';
			}
		} else {
			//$data['city']=array('like','%100020%');
			//获取当前区域
			$this->area = $area = M('area')->where('pid=100020')->select();
			$this->city = '北京';
		}
		if ($_GET['area']) {
			$data['city'] = array('like', '%' . $_GET['area'] . '%');
		}
		//连锁
		if ($_GET['chain']) {
			$data['lspp'] = array('like', '%' . $this->_get('chain') . '%');
		}
		//关键字
		if ($_GET['key']) {
			$data['hotelname|address'] = array('like', '%' . $this->_get('key') . '%');
		}
		//星级
		if ($_GET['star']) {
			$data['xingji'] = array('in', $this->_get('star'));
		}
		/*//价格
		if($_GET['price']){
			$price = explode('_',$this->_get('price'));
			$hids= $hotel->where($data)->field('hotelid')->select();
			$ids=array();
			foreach($hids as $k=>$v){
				$roo['hotelid']=$v['hotelid'];
				$rids= M('room')->where($roo)->field('id')->select();
				foreach($rids as $vv){
					$money=$this->getLowestPrices($vv['id']);
					if($money>$price[0] && $money<=$price[1]){
						$ids[]=$v['hotelid'];
					}
				}
			}
			$data['hotelid']=array('in',array_unique($ids));
		}*/
		//价格
		if ($_GET['price']) {
			$price = explode('_', $this->_get('price'));
			$hids = $hotel->where($data)->field('hotelid')->select();
			foreach ($hids as $v) {
				$hidarr[] = $v['hotelid'];
			}
			$map['hotelid'] = array('in', $hidarr);
			$map['yudingjia'] = array('BETWEEN', $price[0] . ',' . $price[1]);
			$rooms = M('room')->where($map)->field('hotelid,yudingjia')->select();
			foreach ($rooms as $v) {
				$ids[] = $v['hotelid'];
			}
			//$data=array();
			$data['hotelid'] = array('in', array_unique($ids));
		}
		//print_r($data);die;
		$count = $hotel->where($data)->count();
		$Page = new Page($count, 4);
		$Page->setConfig('theme', '%upPage% %linkPage% %downPage%');
		$this->page = $Page->show();
		$hotels = $hotel->where($data)->order('is_tuijian desc,sort desc,hotelid desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($hotels as $k => & $v) {
			//设置。
			$hotels["$k"]['sheshi'] = $sheshi = json_decode($hotels["$k"]['sheshi']); //服务设施
			$hotels["$k"]['canyin'] = $canyin = json_decode($hotels["$k"]['canyin']); //餐饮设施
			$hotels["$k"]['xinyongka'] = $xinyongka = json_decode($hotels["$k"]['xinyongka']); //信用卡
			$hotels["$k"]['kfsheshi'] = $kfsheshi = json_decode($hotels["$k"]['kfsheshi']); //客房设施
			$hotels["$k"]['yule'] = $yule = json_decode($hotels["$k"]['yule']); //娱乐设施
			//有无电脑
			if (in_array('电脑', $kfsheshi)) {
				$hotels["$k"]['d'] = 1;
			}
			//有无WiFi
			if (in_array('Wifi', $kfsheshi)) {
				$hotels["$k"]['wifi'] = 1;
			}
			//有无停车场
			if (in_array('停车场', $sheshi)) {
				$hotels["$k"]['p'] = 1;
			}
			//行李运送
			if (in_array('行李运送', $sheshi)) {
				$hotels["$k"]['yunsong'] = 1;
			}
			//健身房
			if (in_array('健身房', $yule)) {
				$hotels["$k"]['jiesong'] = 1;
			}
			//餐饮服务
			if ($canyin) {
				$hotels["$k"]['can'] = 1;
			}
			//星级
			//$hotels["$k"]['xingji']=M('xingji')->where('id='.$v['xingji'])->getField('name');
			$hote['hotelid'] = $v['hotelid'];
			//地址
			$area = explode(',', $v['city']);
			$area1 = M('area')->where('id=' . $area[0])->getField('name');
			$area2 = M('area')->where('id=' . $area[1])->getField('name');
			$area3 = M('area')->where('id=' . $area[2])->getField('name');
			$hotels["$k"]['city'] = $area1 . $area2 . $area3;
			$pho = M('photo')->where($hote)->order('isdefault desc')->field('src')->find();
			$v['src'] = $pho['src'];
			$comment = M('comment')->where($hote)->field('unit')->select();
			$nums = 0;
			$total = 0;
			foreach ($comment as $c) {
				$nums++;
				$total+= rtrim($c['unit'], '%');
				$v['pjnums'] = $nums;
			}
			if ($nums == 0) {
				$v['fen'] = 5;
				$v['pjnums'] = 0;
			} else {
				$v['fen'] = floor($total / $nums / 20 * 100) / 100;
			}
			$rooms = M('room')->where($hote)->field('id')->select();
			$min = 9999999;
			foreach ($rooms as $vv) {
				$price = $this->getLowestPrices($vv['id']);
				if ($min > $price) {
					$min = $price;
				}
			}
			if ($min != 9999999) {
				$price = explode('_', $min);
				$v['price'] = $price[0];
			}
		}
		// dump($hotels);die;
		// $this->hotels = $hotels;
		// $this->display();
		$this->response($hotels);
	}
	function getLowestPrices($id) {
		$da['id'] = $id;
		$ldaydata = M('room')->where($da)->field('tjarr')->find();
		if ($ldaydata['tjarr']) {
			$lowest = 99999999;
			$max = 0;
			$daydata = explode('|', rtrim($ldaydata['tjarr'], '|'));
			$daydatas = array();
			foreach ($daydata as $v) {
				$daydata2 = explode('-', $v);
				if ($daydata2[0] > time()) {
					if ($daydata2[1] < $lowest) {
						$lowest = $daydata2[1];
					}
					//if($daydata2[1]>$max){
					//	$max=$daydata2[1];
					//}
				}
			}
			if ($lowest != 99999999) {
				return $lowest;
			} else {
				return 0;
			}
		} else {
			return 0;
		}
	}
	/**
	 * 获取酒店可预订日期
	 * @param	int		hotelid
	 */
	private function getorderday($id)
	{
		$tjstr = M('room')->where(['hotelid' => $id])->getField('tjarr');
	}
	//酒店详细页
	public function detail() {
		$error = [];
		$tomorrow = date('Y-m-d', strtotime('1 day'));
		if ($_GET['start']) {
			$days = (strtotime($_GET['end']) - strtotime($_GET['start'])) / 86400;
		}
		$today = strtotime(date('Ymd'));
		$start = $this->_get('start') ? strtotime($this->_get('start')) : $today;
		$end = $this->_get('end') ? strtotime($this->_get('end')) : $today + 86400;
		// $start = date('Y-m-d', $start);
		// $end = date('Y-m-d', $end);
		//$data['is_delete']=0;
		$data['hotelid'] = intval($this->_get('id'));
		if(!$data['hotelid']) {
			$error = [
				'msg' => 'error',
				'code' => 500,
				'data' => [],
				'message' => '缺少对应的参数id'
			];
		}

		$hotel = M('member_hotel')->where($data)->join('pchotel_xingji ON pchotel_member_hotel.xingji=pchotel_xingji.id')
		//->field('name,address,hotelname,sheshi,hotelid,introduce')
		->find();
		if (!empty($hotel['username'])) {
			unset($hotel['username']);
		}
		if (!empty($hotel['password'])) {
			unset($hotel['password']);
		}

		$sheshi = json_decode($hotel['sheshi']);
		$sh = '';
		foreach ($sheshi as $k => $v) {
			if ($k < 3) {
				$sh.= $v . '&nbsp;';
			} else {
				break;
			}
		}
		$hotel['shi'] = $sh;
		$comment = M('comment')->where($data)->field('unit')->select();
		$nums = 0;
		$total = 0;
		foreach ($comment as $c) {
			$nums++;
			$total+= rtrim($c['unit'], '%');
		}
		if ($nums == 0) {
			$hotel['fen'] = 5;
		} else {
			$hotel['fen'] = floor($total / $nums / 20 * 100) / 100;
		}
		$hotel['comment'] = $nums;
		// $data['tjarr'] = array('like', '%' . $start. '%');
		//$rooms = M('room')->where($data)->field('tjarr,fjchuang,swang,zaocan,thumb,roomtype,id')->select();
		$sql = 'SELECT tjarr,fjchuang,swang,zaocan,thumb,roomtype,id FROM pchotel_room WHERE hotelid='.$data['hotelid']." AND tjarr LIKE '%".$start."%' AND tjarr LIKE '%".$end."%'";
		$rooms = M('room')->query($sql);
		// $today = strtotime(date('Ymd'));
		// $start = $this->_get('start') ? strtotime($this->_get('start')) : $today;
		// $end = $this->_get('end') ? strtotime($this->_get('end')) : $today + 86400;
		foreach ($rooms as $k => & $v) {
			$daydata = explode('|', rtrim($v['tjarr'], '|'));
			$d = 0;
			$d2 = 0;
			$total = 0;
			for ($i = $start;$i < $end;$i+= 86400) {
				$d++;
				foreach ($daydata as $key => $val) {
					$day = explode('-', $val);
					if ($day[0] == $i && $day[0] > (time() - 86400)) {
						$d2++;
						$total+= $day[1];
					}
				}
			}
			$v['total'] = $total / $d;
			if (!$v['total']) {
				$v['total'] = '';
			}
			if ($d2 == $d) {
				$v['status'] = 1;
			} else {
				$v['status'] = 0;
			}
			if($v['thumb']) {
				$v['thumb'] = C('webroot').'/'.$v['thumb'];
			}
			if (!empty($v['tjarr'])) {
				unset($v['tjarr']);
			}
		}
		$hotel['room'] = $rooms;
		//相册
		$album = M('photo')->where($data)->field('src')->order('isdefault desc')->select();
		foreach ($album as $key => $value) {
			$album[$key] = C('webroot').$value['src'];
		}
		$hotel['picnums'] = count($album);
		if (empty($album)) {
			$album = [];
		}
		$hotel['src'] = $album;
		//地址
		$area = explode(',', $hotel['city']);
		$area1 = M('area')->where('id=' . $area[0])->getField('name');
		$area2 = M('area')->where('id=' . $area[1])->getField('name');
		$area3 = M('area')->where('id=' . $area[2])->getField('name');
		$hotel['city'] = $area1 . $area2 . $area3;
		//星级
		$hotel['xingji'] = M('xingji')->where('id=' . $hotel['xingji'])->getField('id');
		$hotel['sheshi'] = $sheshi = json_decode($hotel['sheshi']); //服务设施
		$hotel['canyin'] = $canyin = json_decode($hotel['canyin']); //餐饮设施
		$hotel['xinyongka'] = $xinyongka = json_decode($hotel['xinyongka']); //信用卡
		$hotel['kfsheshi'] = $kfsheshi = json_decode($hotel['kfsheshi']); //客房设施
		$hotel['yule'] = $yule = json_decode($hotel['yule']); //娱乐设施
		//有无电脑
		if (in_array('电脑', $kfsheshi)) {
			$hotel['d'] = 1;
		}
		//有无WiFi
		if (in_array('Wifi', $kfsheshi)) {
			$hotel['wifi'] = 1;
		}
		//有无停车场
		if (in_array('停车场', $sheshi)) {
			$hotel['p'] = 1;
		}
		//行李运送
		if (in_array('行李运送', $sheshi)) {
			$hotel['yunsong'] = 1;
		}
		//健身房
		if (in_array('健身房', $yule)) {
			$hotel['jiesong'] = 1;
		}
		//餐饮服务
		if ($canyin) {
			$hotel['can'] = 1;
		}
		//print_r($hotel);die;
		//$this-> hotel=$hotel;
		// $this->assign('hotel', $hotel);
		// $this->display();
		$hotel['today'] = date('Y-m-d', $start);
		$hotel['tomorrow'] = date('Y-m-d', $end);
		$hotel['days'] = ($end-$start)/86400;
		if($error) {
			$this->response($error);
		} elseif($hotel['room']) {
			$result = [
				'message' => 'ok',
				'code' => 200,
				'data' => $hotel,
			];
			$this->response($result);
		} else {
			$hotel['room'] = [];
			$result = [
				'message' => 'ok',
				'code' => 200,
				'data' => $hotel
			];
			$this->response($result);
		}
	}
	//酒店地图
	public function map() {
		$mm['hotelid'] = $this->_get('id');
		$map = M('member_hotel')->where($mm)->field('map,hotelname')->find();
		$map['map'] = explode(',', $map['map']);
		$this->map = $map;
		$this->display();
	}
	//酒店信息
	public function info() {
		$in['hotelid'] = $this->_get('id');
		$this->db = M('fjdb')->where($in)->find();
		$hotel = M('member_hotel')->where($in)->field('hotelname,introduce,sheshi,traffic')->find();
		$sheshi = json_decode($hotel['sheshi']);
		$sh = '';
		foreach ($sheshi as $v) {
			$sh.= $v . '&nbsp;';
		}
		$hotel['sheshi'] = $sh;
		$this->hotel = $hotel;
		$this->display();
	}
	//日历选择
	public function rili() {
		$start = $this->_post('start') ? strtotime($this->_post('start')) : strtotime($this->_get('start'));
		$end = $this->_post('end') ? strtotime($this->_post('end')) : strtotime($this->_get('end'));
		if (empty($end)) {
			//天数
			$ds = intval($this->_post('days'));
			$end = $start + 86400 * $ds;
		}
		//城市
		$this->city = $city = $this->_get('city') ? $this->_get('city') : $this->_post('city');
		if ($_POST['changedate'] == '+') {
			$start = strtotime($this->_post('start'));
			$end = strtotime($this->_post('end')) + 86400;
		} elseif ($_POST['changedate'] == '-') {
			$start = strtotime($this->_post('start'));
			$end = strtotime($this->_post('end')) - 86400;
		}
		//来自那儿 列表还是房间
		$this->from = $from = $this->_get('from') ? $this->_get('from') : $this->_post('from');
		//房间id
		$this->id = $id = $this->_get('id') ? $this->_get('id') : $this->_post('id');
		if ($_POST['ok'] == '完成') {
			$par = '/Wechat/' . $from . '/start/' . date('Y-m-d', $start) . '/end/' . date('Y-m-d', $end);
			if ($id) {
				$par.= '/id/' . $id;
			}
			if ($city) {
				$par.= '/city/' . $city;
			}
			echo '<script>window.location.href="http://"+window.location.host+"' . __ROOT__ . $par . '";</script>';
		}
		$m = 0;
		for ($i = $start;$i < $end;$i+= 86400) {
			$daydatas[] = date('Y-m-d', $i);
			if ($m == 0) {
				$m = date('n', $i);
			}
		}
		if ($m != 0) {
			$this->month = $m - 1;
		}
		$this->assign("daydatas", json_encode($daydatas));
		//天数
		$this->d = ($end - $start) / 86400;
		$this->start = date('Y-m-d', $start);
		$this->end = date('Y-m-d', $end);
		$this->display();
	}
	//点评
	public function comment() {
		// import("@.ORG.Util.Page");
		// $data['pchotel_comment.id'] = array('neq', 0);
		if ($_GET['id']) {
			$data['pchotel_comment.hotelid'] = $this->_get('id');
			$data['pchotel_comment.status'] = 2;
		}
/* 		$uid = $this->_get('uid');
		if ($uid) {
			$data['pchotel_comment.uid'] = $uid;
		}else{
			$data['pchotel_comment.status'] = 2;	
		} */
		$nums = $count = M('comment')->where($data)->count();
		// $Page = new Page($count, 5);
		// $Page->setConfig('theme', '%upPage% %linkPage% %downPage%');
		// $this->page = $Page->show();
		$page = intval($_GET['page']) ? $_GET['page'] : 1;
		$pagesize = intval($_GET['pagesize']) ? $_GET['pagesize'] : 10;
		$begin = ($page - 1)*$pagesize;
		$comments = M('comment')->field('pchotel_member.username as uname,pchotel_member.icon,pchotel_member_hotel.hotelname,pchotel_comment.*')
		->join('pchotel_member_hotel ON pchotel_comment.hotelid=pchotel_member_hotel.hotelid')
		->join('pchotel_member ON pchotel_comment.uid=pchotel_member.id')
		->where($data)->order('pchotel_comment.id desc')
		->limit($begin . ',' . $pagesize)->select();
		foreach ($comments as $k => & $c) {
			$c['unit'] = rtrim($c['unit'], '%') / 20;
			$c['addtime'] = date('Y-m-d', $c['addtime']);
			if ($c['thumb']) {
				$t = trim($c['thumb'], ',');
				$c['thumb'] = explode(',', $t);
				foreach($c['thumb'] as &$value) {
					$value = C('webroot').$value;
				}
			} else {
				$c['thumb'] = [];
			}
		}
		//print_r($comments);die;
		// $this->comments = $comments;
		// $this->display();
		if($comments) {
			$result = [
				'message' => 'ok',
				'code' => 200,
				'data' => [
					'total' => intval($nums),
					'info' => $comments
				]
			];
		} else {
			$result = [
				'msg' => 'error',
				'code' => 500,
				'data' => [],
				'message' => '未找到相应数据'
			];
		}
		$this->response($result);
	}
	/**
	 * 检查用户是否已登录
	 */
	private function checkUser()
	{
		$err = [];
		if (empty($_SESSION['id'])) {
			// echo "<script>alert('请您先登录!');window.location.href='" . __ROOT__ . "/Wechat/login.html';</script>";
			$err = [
				'result' => 2,
				'code' => 501,
				'message' => '用户未登录',
			];
/* 			session('id', '26411');
			session('name', 'spring'); */
			$this->response($err);
		}
	}
	//预订页面
	public function order() {
		// $this->checkUser();

/* 		$member = M('member')->where('id=' . session('id'))->find();
		//print_r($_SESSION);die;
		$user['username'] = $member['truename'];
		$user['telephone'] = $member['telephone']; */
		if (!$this->_post('end') || !$this->_post('start')) {
			$err = [
				'result' => 0,
				'code' => 500,
				'msg' => 'error',
				'data' => [],
				'message' => '请选择日期'
			];
			$this->response($err);
		}
		$tomorrow = strtotime($this->_post('end'));
		$today = strtotime($this->_post('start'));
		$data['pchotel_room.id'] = $this->_post('id');
		$room = M('room')->where($data)->join('pchotel_member_hotel ON pchotel_room.hotelid = pchotel_member_hotel.hotelid')
		// ->field('pchotel_member_hotel.hotelname,pchotel_member_hotel.hotelid,roomtype,rtype,pchotel_room.hotelid,tjarr')
		->field()
		->find();

		if (!$room) {
			$err = [
				'result' => 0,
				'code' => 500,
				'msg' => 'error',
				'data' => [],
				'message' => '为查找到对应的房间'
			];
			$this->response($err);
		}
		//print_r($room);die;
		$room['start'] = $start = $this->_post('start') ? $this->_post('start') : date('Y-m-d', $today + 86400);
		$room['end'] = $end = $this->_post('end') ? $this->_post('end') : date('Y-m-d', $today + 86400*2);
		$start = strtotime($this->_post('start'));
		$end = strtotime($this->_post('end'));
		$days = ($end - $start) / 86400;
		$daydata = explode('|', substr($room['tjarr'], 0, -1));
		$total = 0;
		$day = 0;
		for ($i = $start;$i < $end;$i+= 86400) {
			foreach ($daydata as $vo) {
				$oneday = explode('-', $vo);
				if ($oneday[0] == $i) {
					$total+= $oneday[1];
					$day++;
				}
			}
		}
		foreach ($daydata as $v) {
			$date = explode('-', $v);
			$ma = explode('_', $date[1]);
			$daydatas[]['day'] = date('Y-m-d', $date[0]) . '|' . $ma[0] . '_' . $ma[1];
		}
		//print_r($daydatas);die;
		$daydatas = json_encode($daydatas);
		if ($days == $day) {
			$room['status'] = 1;
		} else {
			$room['status'] = 0;
		}
		$room['total'] = $total;
		//print_r($room);die;
		// $this->room = $room;
		// $this->display();
		$return = [
			'result' => 1,
			'code' => 200,
			'message' => 'ok',
			'data' => [
				// 'room' => $room,
				'total' => $total
			]
		];
		$this->response($return);
	}
	//房间间数选择
	public function nums() {
		$this->nums = $this->_get('num');
		$this->display();
	}
	//订单预订
	public function doorder() {
		$this->checkUser();
		// $user = session('name'); //用户名
		$user = session('id'); //用户名
		$err = [];
		$pay = $this->_post('pay'); //支付方式
		$mobile = $this->_post('mobile'); //预订手机号
		if (!$user) {
			$isM = M('member')->where(array('username' => $mobile))->count();
			if ($isM) {
				die(json_encode(array('result' => 2, 'message' => '手机号已经注册过，请登陆后预订')));
				//die(json_encode(array('status'=>0,'msg'=>'手机号已经注册过，请登陆后预订')));
			} else {
				die(json_encode(array('result' => 2, 'message' => '请登陆后预订')));
			}
		}
		if ($this->_post('linkman') == '') {
			$err = array('result' => 1, 'message' => '联系人不能为空', 'code' => 500);
			$this->response($err);
		}
		if (strlen($mobile) < 11) {
			$err = array('result' => 1, 'message' => '请认真填写联系电话', 'code' => 500);
			$this->response($err);
		}
		$dotime = $this->_post('start') ? strtotime($this->_post('start')) : '';
		$map2['id'] = $this->_post('rid');
		$map2['tjarr'] = array('like', '%' . $dotime . '%');
		$p = M('room')->where($map2)->count();
		if (!$p) {
			$err = array('status' => 1, 'message' => '该日期无房，请选择其他日期', 'code' => 500);
		}
		$data = array();
		$data['telephone'] = $mobile;
		$data['username'] = $user;
		$data['roomid'] = intval($this->_post('rid')); //房间id
		$data['hotelid'] = intval($this->_post('hid')); //酒店id
		$data['ruzhudate'] = $start = strtotime($this->_post('start')); //入住日期
		$data['lidiandate'] = $end = strtotime($this->_post('end')); //离店日期

		$data['nums'] = intval($this->_post('nums')) ? intval($this->_post('nums')): 1; //预订间数
		$data['rennums'] = intval($this->_post('rennums')); //预订人数


		$data['linkman'] = $this->_post('linkman'); //预订人
		$data['kename'] = $this->_post('kename'); //入住人
		$data['isMobile'] = 1; //手机端预订支付
		$data['from'] = 1; //手机来源
		$overplus = $this->overplus(intval($this->_post('rid')), $start, $end, $data['nums']);
		if (!$overplus) {
			$err = array('result' => 1, 'message' => '房间余量不足，请调整预定数量', 'code' => 500);
		}
		$totalprice = $this->totalPrice(intval($this->_post('rid')), $start, $end, $data['nums']);
		// $data['shoufei'] = intval($this->_post('total')); //总金额
		$data['shoufei'] = $totalprice; //总金额
		if ($pay == 1) {
			$data['status'] = 3; //未支付
		} else {
			$data['status'] = 1; //未确认
		}
		$data['beizhu'] = $this->_post('remark'); //备注
		$data['addtime'] = time(); //预订时间

		$sql = "SELECT orderid,ruzhudate,lidiandate FROM pchotel_order WHERE kename='{$data['kename']}' AND roomid={$data['roomid']} LIMIT 1";
		$model = M('order');
		$order = $model->query($sql);
/* 		if ($data['ruzhudate'] >= intval($order[0]['ruzhudate']) && $data['ruzhudate'] <= intval($order[0]['lidiandate'])) {
			$err = [
				'result' => 1,
				'message' => '不能重复预订同一房间',
				'code' => 500
			];
		} else { */
			$res = M('order')->add($data);
			if ($res) {
				//调整余量
				M('room')->where('id=' . $this->_post('rid'))->setField('tjarr', $overplus);
				$return = array('result' => 1, 'data' => ['orderid' =>$res,'totalprice' => $totalprice], 'message' => '预订成功', 'code'=>200);
			} else {
				$err = array('result' => $data, 'message' => '预定失败', 'code'=>500);
			}
		// }

		if ($err) {
			$this->response($err);
		} else {
			$this->response($return);
		}
	}
	// 计算预订房间价格
	private function totalPrice($id, $start, $end, $num)
	{
		$tjstr = M('room')->where('id='.$id)->getField('tjarr');
		$tjarr = explode('|', $tjstr);
		$arr = [];
		$totalprice = 0;
		foreach ($tjarr as $v) {
			$arr = explode('-', $v);
			if (intval($arr[0]) >= intval($start) && intval($arr[0]) < intval($end)) {
				if (strpos($arr[1], '_' )) {
					$p = explode('_', $arr[1]);
					$totalprice += intval($p[0]);
				}
			}
		}
		$price = $totalprice*intval($num);
		return $price;
	}
	//处理库存
	public function overplus($id, $start, $end, $num) {
		$tjarr = M('room')->where('id=' . $id)->getField('tjarr');
		$tjarr = explode('|', $tjarr);
		foreach ($tjarr as & $v) {
			$arr = explode('-', $v);
			$arr[0] = intval($arr[0]);
			if ($arr[0] >= $start && $arr[0] < $end) {
				if (strpos($arr[1], '_')) {
					$jiage = explode('_', $arr[1]);
					if (intval($jiage[1]) - $num < 0) {
						return false;
					}
					$v = $arr[0] . '-' . $jiage[0] . '_' . ($jiage[1] - $num);
				}
			}
		}
		return implode("|", $tjarr);
	}
	//订单提示页面
	public function sucess() {
		if (!session('id')) {
			$this->username = M('order')->wherer(array('orderid' => $this->_get('oid')))->getField('telephone');
			$_SESSION['id'] = $uid;
			$_SESSION['name'] = $user;
			$this->isReg = 1;
		}
		$this->display();
	}
	//订单详情页
	public function orderdetail() {
		$this->checkUser();
		$arr = [
			//订单状态
			1=>'未确认',
			2=>'已确认',
			3=>'未付款',
			4=>'已付款',
			5=>'已入住',
			7=>'已离店',
			6=>'已取消'
		];
		$map = array();
		//$map['pchotel_order.username'] =session('name');
		if (!intval($this->_get('id'))) {
			$err = [
				'result' => 0,
				'code' => 500,
				'data' => [],
				'message' => '缺少对应的参数订单id'
			];
			$this->response($err);
		}
		$map['pchotel_order.orderid'] = $this->_get('id');
		$order = M('order')->join('pchotel_room ON pchotel_order.roomid = pchotel_room.id')->join('pchotel_member_hotel ON pchotel_order.hotelid = pchotel_member_hotel.hotelid')->field('pchotel_room.roomtype,pchotel_member_hotel.hotelname,pchotel_member_hotel.telephone as tel,pchotel_member_hotel.address,pchotel_order.*')->where($map)->find();
		//print_r($or);die;
		$order['map'] = M('member_hotel')->where(['hotelid' => intval($order['hotelid'])])->getField('map');
		// $this->display();
		if (!$order) {
			$err = [
				'result' => 0,
				'code' => 500,
				'data' => [],
				'message' => '未找到对应订单数据'
			];
			$this->response($err);
		}
		$order['ordernumber'] = $order['addtime'].$order['orderid'];
		$order['addtime'] = date('Y-m-d', $order['addtime']);
		$order['lidiandate'] = date('Y-m-d', $order['lidiandate']);
		$order['ruzhudate'] = date('Y-m-d', $order['ruzhudate']);
		$arrnum = [1,2,3];
		// $order['cancel'] = in_array(intval($order['status'])) ? 1 : 0;
		if (in_array(intval($order['status']), $arrnum)) {
			$order['cancel'] = 1;	// 取消订单
		} elseif (intval($order['status']) == 7 && $order['is_comment'] == 0) {
			$order['cancel'] = 2;	// 点评订单
		} elseif (intval($order['is_comment']) == 1) {
			$order['cancel'] = 3;
		} else {
			$order['cancel'] = 0;	// 空
		}
		// $order['status'] = $arr[intval($order['status'])];
		if ($order) {
			$res = [
				'result' => 1,
				'message' => 'ok',
				'code' => 200,
				'data' => $order
			];
		} else {
			$res = [
				'result' => 0,
				'message' => '未找到对应的订单',
				'data' => [],
				'code' => 500
			];
		}
		$this->response($res);
	}
	//酒店相册
	public function album() {
		$data['hotelid'] = $this->_get('id');
		$this->photos = M('photo')->where($data)->order('photoid desc')->select();
		$this->hotelname = M('member_hotel')->where($data)->getField('hotelname');
		$this->display();
	}
	//我的订单
	public function myorder() {
		// session_destroy();
		$arr = [
			//订单状态
				1=>'未确认',
				2=>'已确认',
				3=>'未付款',
				4=>'已付款',
				5=>'已入住',
				7=>'已离店',
				6=>'已取消'
		];
		$this->checkUser();
		// $uid = session('name');
		$uid = session('id');
		// import('ORG.Util.Page');
		if($this->_get('comment')){
			$map['pchotel_order.status']=array('in','4,5,7');
			$map['pchotel_order.is_comment']=0;
			// $this->_title='未点评订单';
		}
		$map['pchotel_order.username'] = $uid;
		$count = M('order')->where($map)->count();
		$page = intval($this->_get('page')) ? intval($this->_get('page')) : 1;
		$pagesize = intval($this->_get('pagesize')) ? intval($this->_get('pagesize')) : 10;
		$begin = ($page-1)*$pagesize;

		$myorder = M('order')->join('pchotel_room ON pchotel_order.roomid = pchotel_room.id')
			->join('pchotel_member_hotel ON pchotel_order.hotelid = pchotel_member_hotel.hotelid')
			->field('roomtype,hotelname,city,city2,city3,pchotel_order.*')
			->order('pchotel_order.orderid desc')->where($map)->limit($begin . ',' . $pagesize)->select();
		if (!$myorder) {
			$res = [
				'result' => 0,
				'message' => '没有订单信息',
				'data' => [],
				'code' => 200
			];
		} else {
			$arrnum = [1,2,3];
			// $order['cancel'] = in_array(intval($order['status'])) ? 1 : 0;
			foreach ($myorder as $k => $v) {
				//地址
				$area = explode(',', $v['city']);
				$area1 = M('area')->where('id=' . $area[0])->getField('name');
				$area2 = M('area')->where('id=' . $area[1])->getField('name');
				$area3 = M('area')->where('id=' . $area[2])->getField('name');
				$myorder["$k"]['city'] = $area1 . $area2 . $area3;
				$myorder["$k"]['days'] = ($v['lidiandate']-$v['ruzhudate'])/86400;
				$myorder["$k"]['addtime'] = date('Y-m-d',$v['addtime']);
				$myorder["$k"]['ruzhudate'] = date('Y-m-d',$v['ruzhudate']);
				$myorder["$k"]['lidiandate'] = date('Y-m-d',$v['lidiandate']);
				
				if (in_array(intval($myorder["$k"]['status']),$arrnum)) {
					$myorder["$k"]['cancel'] = 1;	// 取消订单
				} elseif (intval($myorder["$k"]['status']) == 7 && $myorder["$k"]['is_comment'] == 0) {
					$myorder["$k"]['cancel'] = 2;	// 点评订单
				} elseif ($myorder["$k"]['is_comment'] == 1) {
					$myorder["$k"]['cancel'] = 3;	// 已点评
				} else {
					$myorder["$k"]['cancel'] = 0;
				}
				
				// $myorder["$k"]['status'] = $arr[intval($v['status'])];
				$member_hotel = M('member_hotel')->where(['hotelid'=>intval($myorder["$k"]['hotelid'])])->field('map,telephone')->find();
				// $myorder["$k"]['map'] = $member_hotel;
				$myorder["$k"]['map'] = $member_hotel['map'];
				$myorder["$k"]['tel'] = $member_hotel['telephone'];
			}
			$res = [
				'result' => 1,
				'code' => 200,
				'sessionid' => session_id(),
				'msg' => 'ok',
				'data' => [
					'total' => $count,
					'info' => $myorder
				]
			];
		}
		$this->response($res);
	}
	// 未点评订单
	public function commentorder() {
		// import("ORG.Util.Page"); // 导入分页类 
		$this->checkUser();    
		$name = $_SESSION['name'];
		$map['pchotel_order.username']=$name;
		//状态
		if($this->_get('status')){
			$map['pchotel_order.status']=array('in',$this->_get('status'));
		}
		//未点评订单
		if($this->_get('comment')){
			$map['pchotel_order.status']=array('in','4,5,7');
			$map['pchotel_order.is_comment']=0;
			// $this->_title='未点评订单';
		}
		$count = M('order')
				->join('pchotel_member_hotel ON pchotel_order.hotelid = pchotel_member_hotel.hotelid')
				->join('pchotel_room ON pchotel_order.roomid = pchotel_room.id')
				->where($map)
				->count();
		// $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
		$page = intval($this->_get('page')) ? intval($this->_get('page')) : 1; // 分页显示输出 
		$pagesize = intval($this->_get('pagesize')) ? intval($this->_get('pagesize')) : 10; // 分页显示输出 
		$begin = ($page-1)*pagesize;
		$row = M('order')
				->field('pchotel_order.*,pchotel_room.returnmoney,pchotel_member_hotel.hotelname,pchotel_room.roomtype')
				->join('pchotel_member_hotel ON pchotel_order.hotelid = pchotel_member_hotel.hotelid')
				->join('pchotel_room ON pchotel_order.roomid = pchotel_room.id')
				->where($map)
				->limit($begin . ',' . $pagesize)
				->order('pchotel_order.orderid desc')
				->select();
		//print_r($this->row);
		// $basic = M('basic');
		// $row = $basic->select();
		// $title = "我的酒店订单-" . $row[0]['webname'];
/* 		$this->assign('title', $title);
		$this->assign('keywords', $row[0]['webname']);
		$this->assign('description', $row[0]['description']);
		$this->assign('copyright', $row[0]['copyright']); */
		// $this->display();
		if ($row) {
			$res = [
				'result' => 1,
				'code' => 200,
				'msg' => 'ok',
				'data' => [
					'total' => $count,
					'info' => $row
				]
			];
		} else {
			$res = [
				'result' => 0,
				'code' => 500,
				'msg' => 'error',
				'data' => [],
				'message' => '未找到对应的数据'
			];
		}
		$this->response($res);
	}
	//订单取消
	public function docancel() {
		$this->checkUser();
		$data = array();
		//$data['uid'] 	=session('id');
		$data['orderid'] = $this->_post('id');
		$res = M('order')->where($data)->setField('status', 6);
		if ($res) {
			$return = array('result' => 1, 'message' => '取消成功','code' => 200);
		} else {
			$return = array('result' => 0, 'message' => '系统出错', 'code' => 500);
		}
		$this->response($return);
	}
	//城市选择
	public function city() {
		$hotarea = M('area')->where(array('ishot' => 1))->order('sort desc,id asc')->limit(15)->select();
		$area = M('area')->where(array('level' => 2))->order('first asc')->select();
		$Zm = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$all = array();
		for ($i = 0;$i < 26;$i++) {
			$k = substr($Zm, $i, 1);
			$data = M('area')->where(array('level' => 2, 'first' => $k))->select();
			$all[] = [
				'name' => $k,
				'data' => $data
			];
		}
		// $this->area = $all;
		// $this->display();
		$result = [
			'msg' => 'ok',
			'code' => 200,
			'data' => [
				'hotarea' => $hotarea,
				'area' => $all
			]
		];
		$this->response($result);
	}
	//首页城市选择
	public function cityindex() {
		$hotarea = M('area')->where(array('ishot' => 1))->order('sort desc,id asc')->limit(15)->select();
		$area = M('area')->where(array('level' => 2))->order('first asc')->select();
		$Zm = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$all = array();
		for ($i = 0;$i < 26;$i++) {
			$k = substr($Zm, $i, 1);
			$all[$k] = M('area')->where(array('level' => 2, 'first' => $k))->select();
		}
		// $this->area = $all;
		$result = [
			'msg' => 'ok',
			'code' => 200,
			'hotarea' => $hotarea,
			'area' => $all
		];
		$this->response($result);
		// $this->display();
	}
	//连锁
	public function chain() {
		$ls = array();
		$chain = M('liansuo')->field('name,zimu')->order('zimu asc')->select();
		foreach ($chain as $v) {
			$ls[$v['zimu']][] = $v['name'];
		}
		$this->chain = $ls;
		$this->display();
	}
	//预订须知
	public function notes() {
		// import('ORG.Util.Page');
		$data['is_delete'] = 0;
		//$data['catid'] = $_GET['catid']?$_GET['catid']:6;
		$cid = $_GET['catid'];
		if ($cid) {
			$data['catid'] = $cid;
			$cate = M('cate')->where("id=$cid")->getField('name');
			// $this->response(['msg'=> 'error']);
		} else {
			$data['catid'] = 6;
			$cate = '预定帮助';
		}

		$page = intval($_GET['page']) ? $_GET['page'] : 1;
		$pagesize = intval($_GET['pagesize']) ? $_GET['pagesize'] : 6;
		$begin = ($page-1)*$pagesize;

		$count = M('article')->where($data)->count();
		// $page = new Page($count, 6);
		// $page->setConfig('theme', '%upPage% %linkPage% %downPage%');
		// $this->page = $page->show();
		$news = M('article')->where($data)->order("sort DESC,articleid DESC")->field('articleid,title,description,thumb')->limit($begin. ',' . $pagesize)->select();
		// $this->display();
		foreach ($news as $key => &$value) {
			if ($value['thumb']) {
				$value['thumb'] = C('webroot').$value['thumb'];
			}
		}
		if($news) {
			$result = [
				'msg' => 'ok',
				'code' => 200,
				'data' => $news
			];
		} else {
			$result = [
				'msg' => 'error',
				'code' => 500,
				'data' => [],
				'message' => '未找到相应的数据'
			];
		}
		$this->response($result);
	}
	//预订须知详细页
	public function artshow() {
		$art['articleid'] = $this->_get('id');
		if(!$art['articleid']) {
			$error = [
				'msg' => 'error',
				'code' => 404,
				'data' => [],
				'message' => '缺少参数id'
			];
			$this->response($error);
		}
		$art = M('article')->where($art)->field('title,username,addtime,content')->find();
		// $this->display();
		$art['addtime'] = date("Y-m-d", $art['addtime']);
		if($art) {
			$result = [
				'msg' => 'ok',
				'code' => 200,
				'data' => $art
			];
		} else {
			$result = [
				'msg' => 'error',
				'code' => 500,
				'data' => [],
				'message' => '未找到相应的数据'
			];
		}
		$this->response($result);
	}
	//单页
	public function ashow() {
		$art['id'] = $this->_get('id');
		$this->art = M('single')->where($art)->find();
		$this->display();
	}
	/*
	    //发送短信[创蓝系统短信]
	   public function sms($mobile=0,$str){
	       //创蓝短信接口
	       Vendor('ChuanglanSmsHelper.ChuanglanSmsApi');
	       $clapi  = new ChuanglanSmsApi();
	       $result = $clapi->sendSMS($mobile, $str,'true');
	       $result = $clapi->execResult($result);
	       return $result[1]==0;
	   }  
	*/
	//积分列表
	public function gift() {
		//得到积分商品分类
		$this->giftcate = M('gift_cate')->where('pid = 267 and  is_delete=0')->select();
		$gift = M('gift');
		$map = array();
		//关键字
		if ($_GET['key']) {
			$map['name'] = array('like', '%' . $this->_get('key') . '%');
		}
		$this->order = " is_put desc";
		//排序
		if ($_GET['location'] && $_GET['location'] == "2") {
			$this->order = " is_put asc ";
		}
		//通过当前分类，得到商品
		if ($_GET['cate']) {
			$arr = M('gift_cate')->where('pid=' . $_GET['cate'])->Field('id')->select();
			$ids = array();
			foreach ($arr as $k => $v) {
				$ids[] = $v['id'];
			}
			// dump($ids);
			$map['cateid'] = array('in', $ids);
		}
		//价格
		if ($_GET['point']) {
			$price = explode('_', $this->_get('point'));
			$hids = M('gift_cate')->where($map)->field('id')->select();
			foreach ($hids as $v) {
				$hidarr[] = $v['id'];
			}
			$map['cateid'] = array('in', $hidarr);
			$map['point'] = array('BETWEEN', $price[0] . ',' . $price[1]);
			$giftlists = M('gift')->where($map)->field('id,point')->select();
			foreach ($giftlists as $v) {
				$idss[] = $v['id'];
			}
			$map['id'] = array('in', array_unique($idss));
		}
		$map['status'] = 0;
		import("@.ORG.Util.Page");
		$count = $gift->where($map)->count();
		$Page = new Page($count, 5);
		$this->page = $Page->show();
		$giftlist = $gift->where($map)->order($this->order)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		//为商品加上图片
		foreach ($giftlist as $k => $v) {
			//dump($v);
			$giftlist[$k]['src'] = M('gift_photo')->where('aid=' . $v['id'])->order('isdefault desc')->getField('src');
		}
		$this->giftlist = $giftlist;
		$this->display();
	}
	//积分列表详情页
	public function giftdetail() {
		//echo $_GET['id'];
		$hot['id'] = $id = $this->_get('id');
		$this->details = $content = M('gift')->where($hot)->find();
		//图片
		$pho['aid'] = $id;
		$this->pics = M('gift_photo')->where($pho)->select();
		$this->display();
	}
	//积分商品订单页
	public function giftorder() {
		$this->member = $member = M('member')->where('id=' . session('id'))->find();
		$map['pchotel_gift.id'] = intval($this->_get('id')); //积分上商品的ID
		$gift = M('gift')->where($map)->join('pchotel_gift_photo ON pchotel_gift.id = pchotel_gift_photo.aid')->field('pchotel_gift_photo.src,name,point,pchotel_gift.id,pchotel_gift.cateid')->find();
		$this->assign('gift', $gift);
		$this->display();
	}
	// 积分商品订单提交
	public function dogiftorder() {
		$user = session('id'); //用户id
		$mobile = $this->_post('mobile'); //预订手机号
		if (M('gift')->where('id=' . $this->_post('tid'))->getField('nums') < $this->_post('nums')) {
			die(json_encode(array('status' => 3, 'msg' => '商品库存不足，无法订购!')));
		}
		if (!$user) {
			$isM = M('member')->where(array('username' => $mobile))->count();
			if ($isM) {
				die(json_encode(array('status' => - 2, 'msg' => '手机号已经注册过，请登陆后预订')));
				//die(json_encode(array('status'=>0,'msg'=>'手机号已经注册过，请登陆后预订')));
			} else {
				die(json_encode(array('status' => - 2, 'msg' => '请登陆后预订')));
			}
		}
		if ($this->_post('linkman') == '') {
			die(json_encode(array('status' => 0, 'msg' => '联系人不能为空')));
		}
		if (strlen($mobile) < 11) {
			die(json_encode(array('status' => 0, 'msg' => '请认真填写联系电话')));
		}
		$_POST['status'] = 1; //订单状态
		$_POST['tpay'] = 1; //兑换方式
		$_POST['point'] = $_POST['point'] * $_POST['nums'];
		$_POST['addtime'] = time();
		$_POST['uid'] = $user;
		$mem['point'] = array('lt', $_POST['point']);
		$mem['id'] = $user;
		$member = M('member')->where($mem)->field('point')->find();
		if ($member) {
			die(json_encode(array('status' => 0, 'msg' => '您的积分余额不够！')));
		}
		$res = M('giftorder')->add($_POST);
		if ($res) {
			$m['id'] = $point2['uid'] = session('id');
			$point2['orderid'] = $res;
			$point2['total'] = $_POST['point'];
			$point2['ctime'] = time();
			M('gift_point')->add($point2); //增加积分消费记录
			M('member')->where($m)->setDec('point', $point); //相应帐号减少积分
			//$or['id']=$res;
			//M('giftorder')->where($or)->setField('status',1);
			M('gift')->where($_POST['tid'])->setDec('nums', $nums); //减掉相应的预订数量
			die(json_encode(array('status' => 1, 'oid' => $res, 'msg' => '预订成功')));
		} else {
			die(json_encode(array('status' => 0, 'msg' => '操作失败')));
		}
		//dump($_POST);
	}
	//订单预订
	public function mygiftorder() {
		$user = session('id');
		if (!$user) {
			echo '<script> alert("请登陆后查看!"); window.location.href="./login.html";</script>';
		}
		import('ORG.Util.Page');
		$map['pchotel_giftorder.uid'] = $user;
		$count = M('giftorder')->where($map)->count();
		$page = new Page($count, 10);
		$page->setConfig('theme', '%upPage% %linkPage% %downPage%');
		$this->page = $page->show();
		$this->myorder = M('giftorder')->join('pchotel_gift ON pchotel_giftorder.tid=pchotel_gift.id ')->order('pchotel_giftorder.id desc')->where($map)->limit($page->firstRow . ',' . $page->listRows)->field('pchotel_gift.name,pchotel_giftorder.*')->select();
		$this->display();
	}
	public function giftorderdetail() {
		$map['pchotel_giftorder.id'] = $gid = $this->_get('id');
		$map['uid'] = session('id');
		$this->order = M('giftorder')->where($map)->join('pchotel_gift ON pchotel_giftorder.tid = pchotel_gift.id')->field('name,pchotel_giftorder.*')->find();
		$this->display();
	}
	// 酒店品牌
	public function chains() {
		$chain = M('liansuo')->order('id DESC')->select();
		foreach ($chain as &$value) {
			$value['thumb'] = C('webroot').$value['thumb'];
		}
		if($chain) {
			$result = [
				'msg' => 'ok',
				'code' => 200,
				'data' => $chain
			];
		} else {
			$result = [
				'msg' => 'error',
				'code' => 500,
				'data' => [],
				'message' => '未找到对应的数据'
			];
		}
		$this->response($result);
	}
	//积分提现列表页
	public function tixians() {
		$tixian = M('tixian');
		import('ORG.Util.Page');
		$s['uid'] = session('id');
		$count = $tixian->where($s)->count();
		$page = new Page($count, 10);
		$this->page = $page->show();
		$content = $tixian->where($s)->order('txdate desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->assign('list', $content);
		$this->display();
	}
	//点评
	public function mycomment(){
		$this->checkUser();
		if ($_GET['id']) {
			$data['pchotel_comment.hotelid'] = $this->_get('id');
			$data['pchotel_comment.status'] = 2;
		}
		$uid = session('id');
		if ($uid) {
			$data['pchotel_comment.uid'] = $uid;
		}else{
			$data['pchotel_comment.status'] = 2;	
		}
		$nums = $count = M('comment')->where($data)->count();
		// $Page = new Page($count, 5);
		// $Page->setConfig('theme', '%upPage% %linkPage% %downPage%');
		// $this->page = $Page->show();
		$page = intval($_GET['page']) ? $_GET['page'] : 1;
		$pagesize = intval($_GET['pagesize']) ? $_GET['pagesize'] : 10;
		$begin = ($page - 1)*$pagesize;
		$comments = M('comment')->field('pchotel_member.username as uname,pchotel_member.icon,pchotel_member_hotel.hotelname,pchotel_comment.*')
		->join('pchotel_member_hotel ON pchotel_comment.hotelid=pchotel_member_hotel.hotelid')
		->join('pchotel_member ON pchotel_comment.uid=pchotel_member.id')
		->where($data)->order('pchotel_comment.id desc')
		->limit($begin . ',' . $pagesize)->select();
		foreach ($comments as $k => & $c) {
			$c['unit'] = rtrim($c['unit'], '%') / 20;
			$c['addtime'] = date('Y-m-d', $c['addtime']);
			if ($c['thumb']) {
				$t = trim($c['thumb'], ',');
				$c['thumb'] = explode(',', $t);
				foreach($c['thumb'] as &$value) {
					$value = C('webroot').$value;
				}
			} else {
				$c['thumb'] = [];
			}
		}
		//print_r($comments);die;
		// $this->comments = $comments;
		// $this->display();
		if($comments) {
			$result = [
				'result' => 1,
				'code' => 200,
				'data' => [
					'total' => intval($nums),
					'info' => $comments
				]
			];
		} else {
			$result = [
				'result' => 1,
				'code' => 200,
				'data' => [],
				'message' => '未找到相应数据'
			];
		}
		$this->response($result);
	}

	public function docomment(){
		$this->checkUser();
		$name = '/^.{6,30}$/';
		$content = '/^.{30,6000}$/';
		if (preg_match($content, $_POST['comment']) == 0) {
			$err = array('result' => $_POST['comment'], 'message' => '详细评价必须在10-2000个汉字之间', 'code' => 500);
			//$this->display(0, '详细评价必须在10-2000个汉字之间');
			$this->response($err);
		}
		$map['orderid'] = intval($_POST['orderid']);
		$map['is_comment'] = 0;
		//$map['uid'] = session('id');
		$res2 = M('order')->where($map)->find();

		if (!$res2) {
			$err = [
				'result' => 1,
				'message' => '没有要点评的订单',
				'data' => [],
				'code' => 200,
			];
			$this->response($err);
		}
		// 判断订单状态是否可评论 5入住 7离店
		if (intval($res2['status']) !== 5 && intval($res2['status']) !== 7) {
			$err = [
				'result' => intval($res2['status']),
				'code' => 500,
				'data' => [],
				'message' => '当前订单不可以评论',
			];
			$this->response($err);
		}

		$m = M('comment');
		$data = array();
		$data['title'] = $_POST['tname'];
		$data['orderid'] = $_POST['orderid'];
		// $data['rid'] = $res2['rid'];
		// $data['landlord'] = $res2['landlord'];
		$data['hotelid'] = $res2['hotelid'];
		$data['content'] = $_POST['comment'];
		$data['unit'] = $_POST['manyi'];
		$data['label'] = $_POST['dengji'];
		$data['status'] = 1;
		$data['uid'] = session('id');

		if (is_array($_POST['thumb'])) {
			$arr_thumb = $_POST['thumb'];
			$data['thumb'] = implode(',', $arr_thumb);

		} else {
			$data['thumb'] = trim($_POST['thumb'],'"');
			$data['thumb'] = trim($_POST['thumb'],"'");
		}
		// $data['']
		// 判断当前用户是否已评论
/* 		$user = M('order')->where(['uid' => intval($data['uid'])])->select();
		if ($user) {
			$err = [
				'result' => 1,
				'code' => 500,
				'message' => '不能重复评论'
			];
			$this->response($err);
		} */
		$data['addtime'] = time();
		$res = $m->data($data)->add();

		if ($res) {
			$data2['is_comment'] = 1;
			M('order')->where("orderid = {$data['orderid']}")->save($data2);
			$return = [
				'result' => 1,
				'code' => 200,
				'message' => '恭喜您，点评成功',
				'data' => $_POST
			];
			//$this->display(1, '恭喜您，点评成功');
		} else {
			$return = [
				'result' => 1,
				'code' => 500,
				'data' => [],
				'message' => '点评失败',
			];
		}
		$this->response($return);
	}
	//积分提现详情页面
	public function tixiandetail() {
		$tixian = M('tixian');
		$info = $tixian->where('id=' . $_GET['id'])->find();
		$map['username'] = session('name');
		$info['point'] = M('member')->where($map)->getField('point');
		$mapp['id'] = $info['uid'];
		$info['username'] = M('member')->where($mapp)->getField('username');
		$info['txdateday'] = date('Y-m-d', $info['txdate']);
		$info['handleDateday'] = $info['handleDate'] ? date('Y-m-d', $info['handleDate']) : '';
		$info['banliDateday'] = $info['banliDate'] ? date('Y-m-d', $info['banliDate']) : '';
		//$this->sql=$tixian->getlastsql();
		$this->info = $info;
		//dump($info);
		$this->display();
	}
	//积分提现
	public function tixian() {
		if (empty($_POST)) {
			$where['id'] = session('id');
			$this->member = M('member')->where($where)->find();
			$this->display();
		} else {
			$txjine = (int)$_POST['txjine'];
			if ($txjine < 200) {
				die(json_encode(array('status' => 0, 'msg' => '提现金额不能小于200')));
			}
			if (empty($_POST['txhang'])) {
				die(json_encode(array('status' => 0, 'msg' => '提现银行不能为空')));
			}
			if (empty($_POST['txname'])) {
				die(json_encode(array('status' => 0, 'msg' => '开户姓名不能为空')));
			}
			$daan = '/^.{6,30}$/';
			if (preg_match($daan, $_POST['txname']) == 0) {
				die(json_encode(array('status' => 0, 'msg' => '开户姓名长度必须在2-10个汉字之间')));
			}
			if (empty($_POST['txhangnum'])) {
				die(json_encode(array('status' => 0, 'msg' => '银行账号不能为空')));
			}
			$r = $this->luhm($_POST['txhangnum']);
			if ($r) {
				die(json_encode(array('status' => 0, 'msg' => '请输入正确的银行账号')));
			}
			if (empty($_POST['txshenfen'])) {
				die(json_encode(array('status' => 0, 'msg' => '身份证号不能为空')));
			}
			$isIDCard2='/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/';
	        if (preg_match($isIDCard2,$_POST['txshenfen']) ==0) {
	            die(json_encode(array('status' => 0, 'msg' => '身份证号不能为空')));
	        }
			if (empty($_POST['txmobile'])) {
				die(json_encode(array('status' => 0, 'msg' => '手机号码不能为空')));
			}
			if (preg_match('/\d{11}/', $_POST['txmobile']) == 0) {
				die(json_encode(array('status' => 0, 'msg' => '请输入正确的手机号')));
			}
			$id = session('id');
			$point = M('Member')->where(array('username' => session('name')))->getField('point');
			if ($point < $txjine) {
				die(json_encode(array('status' => 0, 'msg' => '余额不足')));
			}
			M('member')->where("id={$id}")->setDec('point', $txjine); //1:1
			$remainAmount = M('member')->where("id={$id}")->getField('point');
			$txdate = time();
			$t = M('tixian');
			$t->create();
			$t->txdate = $txdate;
			$t->uid = $id;
			$t->remainAmount = $remainAmount;
			$res = $t->add();
			if ($res) {
				die(json_encode(array('status' => 1, 'msg' => '申请成功,请等待后台审核')));
			} else {
				die(json_encode(array('status' => 0, 'msg' => '申请失败')));
			}
		}
	}
	//验证银行卡号
	function luhm($s) {
		$n = 0;
		for ($i = strlen($s);$i >= 1;$i--) {
			$index = $i - 1;
			//偶数位
			if ($i % 2 == 0) {
				$n+= $s{$index};
			} else { //奇数位
				$t = $s{$index} * 2;
				if ($t > 9) {
					$t = (int)($t / 10) + $t % 10;
				}
				$n+= $t;
			}
		}
		return ($n % 10) == 0;
	}
	// 支付宝
	public function pay()
	{
		$this->checkUser();
		// 订单编号
		$id = $this->_get('sn');
		// 订单id
		$orderid = intval($this->_post('ordernumber'));
		$data = [];
		$data['orderid'] = intval($this->_post('ordernumber'));
		$data['status'] = 3;			// 未付款状态
		// $data['username'] = session('name');
		$order = M('order')->where($data)->field('shoufei,orderid,addtime')->find();
		$id = $order['addtime'].$order['orderid'];
		$price = $order['shoufei'];
		// $price = 0.01;
		if (!$order) {
			$err = [
				'result'	=> 1,
				'message' 	=> '不存在该订单或该订单已支付',
				'code' 		=> $data
			];
			$this->response($err);
		}
		// 支付方式type=1 alipay type=2 wechatpay type=3积分支付
		$type = intval($this->_post('type'));
		if ($type == 1) {
			$config_biz = [
				'out_trade_no' => $id,
				'total_amount' => $price,
				'timeout_express' => '30m',
				'subject'      => '房间预订',
			];
			$order_string = $this->alipay($config_biz);
			$this->response(['result' => 1, 'data' => $order_string, 'code' => 200]);
			// echo $order_string;
 
		} elseif ($type == 2) {
			$price = intval($price)*100;
			$config_biz = [
				'out_trade_no' => $id,
				'total_fee' => "$price", // **单位：分**
				'body' => '房间预订',
				// 'spbill_create_ip' => $_SERVER["REMOTE_ADDR"],
				'spbill_create_ip' => '192.168.1.125',
				// 'openid' => 'onkVf1FjWS5SBIihS-123456_abc',
			];
			// $order_string = $this->wechatpay($config_biz);
			$order_string = $this->wechat($id, $price);
			$this->response(['result' => 1, 'data' => $order_string, 'code' => 200]);
		} elseif ($type == 3) {
			$url = '/order-pay/score-pay.do';
			// $data = $_GET;
			$user = M('member')->where(['id'=>session('id')])->field('password, telephone')->find();
			$data = [
				'order_id' => $orderid,
				'order_sn' => $id,
				'pay_amount' => $price,
				'pay_password' => $this->_post('paypassword'),
				'client_type' => 'APP',
				'login_mobile' => $user['telephone'],
				'login_password' => $user['password']
			];
			$info = [
				'params' => $this->encrypt(json_encode($data))
			];
			$res = $this->curl_get(C('javadomain').$url, $info);
			if ($res->result == 1) {
				$ord['status'] = 4;
				$res = M('order')->where(['orderid' => $orderid])->save($ord);
				if ($res) {
					$message = '积分支付成功';
					$code = 200;
				}
			} else {
				$code = 500;
				$message = '积分支付失败或订单已支付';
				// 'data' => $data
			}
			$return = [
				'result' => 1,
				'code' => $code,
				'data' => $_POST,
				'message' => $message
			];
			// echo json_encode($res);
			$this->response($return);

		} else {
			$res = [
				'result' => 1,
				'message' => '请选择支付方式',
				'code' => 500
			];
			$this->response($res);
		}
	}
	/**
	 * 积分退款
	 */
	public function refund()
	{
		$this->checkUser();
		// 订单编号
		$id = $this->_get('sn');
		// 订单id
		$orderid = intval(substr($id, 10));
	}
	/**
	 * 文件上传
	 */
	public function doupload()
	{
		$this->checkUser();
/* 		'upload_dir'=>'uploads', //上传文件夹的名称
		'upload_filesize'=>6145728,//赏花攒文件保存的大小
		'upload_saveRule'=>'uniqid',//上传文件名称保存的方式
		'upload_type'=>array('jpg','gif','png','jpeg'),//上传文件的类型 */
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Method:POST,GET');
		// $str = file_get_contents('php://input');
		// $data = json_decode($str);
		$file = $_POST['file'];
		// $length = $data->length;
		$err = [];

		$pattern = "/data:\s*image\/(\w+)/";
		if (preg_match($pattern, $file, $result)) {
			$type = $result[1];
			$pos = strpos($file,'base64,');
			$file = substr($file, $pos+7);
		} else {
			$type = 'jpg';
		}
		if (!in_array($type, C('upload_type'))) {
			$err = [
				'result' => 1,
				'message' => '不支持该文件类型',
				'code' => 500,
			];
		}
		if (strlen($file) > C('upload_filesize')) {
			$err = [
				'result' => 1,
				'message' => '上传文件过大',
				'code' => 500
			];
		} 

		$name = 'comm_'.time().'.'.$type;
		$dir = date('Y-m-d', time());
		$path = C('upload_dir').'/webapp/'.$dir;
		if (!is_dir($path)) {
			mkdir($path);
		}
		if ($err) {
			$this->response($err);
		} else {
			$res = file_put_contents($path.'/'.$name, base64_decode($file));
			if ($res) {
				$return = [
					'result' => 1,
					'code' => 200,
					'data' => [
						'url' => C('webroot').'/'.$path.'/'.$name,
						'thumb' => '/'.$path.'/'.$name
					]
				];
			} else {
				$return = [
					'result' => 1,
					'code' => 500,
					'message' => '图片上传失败',
				];
				unlink($path.'/'.$name);
			}
			$this->response($return);
		}
		// $data = json_decode($_POST);
	}

	public function getphone()
	{
		$base = M('basic')->find();
		if (base) {
			$phone = $base['glsj'];
		} else {
			$phone = '400-0791-955';
		}
		$res = [
			'code' => 200,
			'data' => $phone,
			'message' => 'ok'
		];
		$this->response($res);
	}
}
?>
