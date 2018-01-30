<?php
/*微信版
*	2016-9-30
*/
class WechatAction extends CommonwapAction {
	//首页
	public function index() {
		$this->tomorrow = date('Y-m-d', strtotime('1 day'));
		if ($_GET['start']) {
			$this->days = (strtotime($_GET['end']) - strtotime($_GET['start'])) / 86400;
		}
		if ($_GET['city']) {
			$this->city = M('area')->where(array('id' => $this->_get('city')))->getField('name');
			$map['city'] = array('like', '%' . $_GET['city'] . '%');
		} else {
			$map['city'] = array('like', '%100020%');
		}
		$map['is_delete'] = 0;
		$map['is_tuijian'] = 1;
		$tui = M('member_hotel')->where($map)->limit(3)->select();
		if (empty($tui)) {
			$map['city'] = array('like', '%100020%');
			$tui = M('member_hotel')->where($map)->limit(3)->select();
		}
		foreach ($tui as $k => $v) {
			$tui["$k"]['xingji'] = M('xingji')->where('id=' . $v['xingji'])->getField('name');
			$tui["$k"]['src'] = M('photo')->where('hotelid=' . $v['hotelid'])->getField('src');
			//价格
			$rooms = M('room')->where('hotelid=' . $v['hotelid'])->field('id')->select();
			$min = 9999999;
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
		}
		$this->tui = $tui;
		//print_r($map);die;
		$this->display();
	}
	//登陆
	public function login() {
		// session('id', 26415);
		// echo session_id();exit();
		if ($_SESSION['id']) {
			header("location:" . __ROOT__ . "/Wechat/center");
			exit();
		}
		$this->display();
	}
	//安全退出
	public function loginOut() {
		session_unset();
		session_destroy();
		header("location:" . __ROOT__ . "/Wechat/login.html");
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
		if (md5($Proof) != $_SESSION['verify']) {
			die(json_encode(array('status' => 0, 'msg' => '验证码错误！')));
		}
		if (!$password || !$user) {
			die(json_encode(array('status' => 0, 'msg' => '请认真填写注册信息！')));
		}
		if ($password != $password2) {
			die(json_encode(array('status' => 0, 'msg' => '两次密码输入不一致！')));
		}
		$one = M('member')->where(array('username' => $user))->count();
		$two = M('member_hotel')->where(array('username' => $user))->count();
		if ($one >= 1 || $two >= 1) {
			die(json_encode(array('status' => 0, 'msg' => '用户名已经注册，请修改用户名。')));
		}
		$info = array();
		$info['username'] = $user;
		$info['password'] = md5($password);
		if (preg_match("/^1[34578]\d{9}$/", $user)) {
			$info['Mobile'] = $user;
		}
		$res = M('member')->add($info);
		if ($res) {
			session('id', $res);
			session('name', $user);
			die(json_encode(array('status' => 1, 'msg' => '注册成功')));
		} else {
			die(json_encode(array('status' => 0, 'msg' => '注册失败')));
		}
	}
	//获取短信验证码
	public function getCode() {
		$mobile = $this->_get('mobile');
		$getCode = unserialize($_SESSION["{$mobile}_code"]);
		if ((time() - $getCode[$mobile . '-time']) < 60) {
			die(json_encode(array('status' => 0, 'msg' => '不能频繁获取')));
		}
		strlen($mobile) == 11 || die(json_encode(array('status' => 0, 'msg' => '请输入正确的手机号码')));
		$id = M('member')->where('telephone=' . $mobile)->count();
		$id || die(json_encode(array('status' => 0, 'msg' => '该手机号没有注册')));
		//六位随机数
		$code = rand(100000, 999999);
		//$code=888888;
		$str = "您的验证码为【" . $code . "】，30分钟内有效!";
		$rs = $this->sms($mobile, $str);
		if ($rs) {
			//验证码写入session
			$data[$mobile] = $code;
			$data[$mobile . '-time'] = time();
			session("{$mobile}_code", serialize($data));
			die(json_encode(array('status' => 1, 'msg' => '获取成功')));
		} else {
			die(json_encode(array('status' => 0, 'msg' => '获取失败')));
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
		if ($this->_post('login') == 1) {
			$map['username'] = $this->_post('username');
			$info = M('member')->where($map)->field('password,id,username')->find();
			$info['password'] || die(json_encode(array('status' => 0, 'msg' => '帐号不存在')));
			$info['password'] == md5($this->_post('password')) || die(json_encode(array('status' => 0, 'msg' => '密码不正确')));
		} else {
			$this->checkCode($this->_post('mobile'), $this->_post('code')) || die(json_encode(array('status' => 0, 'msg' => '验证码不正确')));
			$info = M('member')->where('telephone=' . $this->_post('mobile'))->field('id,username')->find();
			//echo M('member')->getlastsql();
			$info || die(json_encode(array('status' => 0, 'msg' => '帐号不存在')));
		}
		session('id', $info['id']);
		session('name', $info['username']);
		die(json_encode(array('status' => 1, 'msg' => '登录成功')));
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
			$data['city'] = array('like', '%' . $city . '%');
			$this->city = M('area')->where(array('id' => $city))->getField('name');
			//获取当前区域
			$this->area = $area = M('area')->where('pid=' . $city)->select();
			if ($city == 100002) {
				//获取区
				$data['city'] = array('like', '%100020%');
				//获取当前区域
				$this->area = $area = M('area')->where('pid=100020')->select();
				$this->city = '北京';
			}
		} else {
			$data['city'] = array('like', '%100020%');
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
		//dump($hotels);die;
		$this->hotels = $hotels;
		$this->display();
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
		//dump($hotels);die;
		$this->hotels = $hotels;
		$this->display();
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
	//酒店详细页
	public function detail() {
		$this->tomorrow = date('Y-m-d', strtotime('1 day'));
		if ($_GET['start']) {
			$this->days = (strtotime($_GET['end']) - strtotime($_GET['start'])) / 86400;
		}
		$today = strtotime(date('Ymd'));
		$start = $this->_get('start') ? strtotime($this->_get('start')) : $today;
		$end = $this->_get('end') ? strtotime($this->_get('end')) : $today + 86400;
		$this->start = date('Y-m-d', $start);
		$this->end = date('Y-m-d', $end);
		//$data['is_delete']=0;
		$data['hotelid'] = $this->_get('id');
		$hotel = M('member_hotel')->where($data)->join('pchotel_xingji ON pchotel_member_hotel.xingji=pchotel_xingji.id')
		//->field('name,address,hotelname,sheshi,hotelid,introduce')
		->find();
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
		$rooms = M('room')->where($data)->field('tjarr,fjchuang,swang,zaocan,thumb,roomtype,id')->select();
		$today = strtotime(date('Ymd'));
		$start = $this->_get('start') ? strtotime($this->_get('start')) : $today;
		$end = $this->_get('end') ? strtotime($this->_get('end')) : $today + 86400;
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
			if ($d2 == $d) {
				$v['status'] = 1;
			} else {
				$v['status'] = 0;
			}
		}
		$hotel['room'] = $rooms;
		//相册
		$album = M('photo')->where($data)->order('isdefault desc')->select();
		$hotel['picnums'] = count($album);
		$hotel['src'] = $album;
		//地址
		$area = explode(',', $hotel['city']);
		$area1 = M('area')->where('id=' . $area[0])->getField('name');
		$area2 = M('area')->where('id=' . $area[1])->getField('name');
		$area3 = M('area')->where('id=' . $area[2])->getField('name');
		$hotel['city'] = $area1 . $area2 . $area3;
		//星级
		$hotel['xingji'] = M('xingji')->where('id=' . $hotel['xingji'])->getField('name');
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
		$this->assign('hotel', $hotel);
		$this->display();
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
		import("@.ORG.Util.Page");
		$data['pchotel_comment.id'] = array('neq', 0);
		if ($_GET['id']) {
			$data['pchotel_comment.hotelid'] = $this->_get('id');
			$data['pchotel_comment.status'] = 2;
		}
		$uid = $this->_get('uid');
		if ($uid) {
			$data['pchotel_comment.uid'] = $uid;
		}else{
			$data['pchotel_comment.status'] = 2;	
		}
		$this->nums = $count = M('comment')->where($data)->count();
		$Page = new Page($count, 5);
		$Page->setConfig('theme', '%upPage% %linkPage% %downPage%');
		$this->page = $Page->show();
		$comments = M('comment')->field('pchotel_member.username as uname,pchotel_member.icon,pchotel_member_hotel.hotelname,pchotel_comment.*')->join('pchotel_member_hotel ON pchotel_comment.hotelid=pchotel_member_hotel.hotelid')->join('pchotel_member ON pchotel_comment.uid=pchotel_member.id')->where($data)->order('pchotel_comment.id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($comments as $k => & $c) {
			$c['unit'] = rtrim($c['unit'], '%') / 20;
			if ($c['thumb']) {
				$t = trim($c['thumb'], ',');
				$c['thumb'] = explode(',', $t);
			}
		}
		//print_r($comments);die;
		$this->comments = $comments;
		$this->display();
	}
	//预订页面
	public function order() {
		if (empty($_SESSION['id'])) {
			echo "<script>alert('请您先登录!');window.location.href='" . __ROOT__ . "/Wechat/login.html';</script>";
		}
		$this->member = $member = M('member')->where('id=' . session('id'))->find();
		//print_r($_SESSION);die;
		$this->tomorrow = date('Y-m-d', strtotime('1 day'));
		$today = strtotime(date('Ymd'));
		$data['pchotel_room.id'] = $this->_get('id');
		$room = M('room')->where($data)->join('pchotel_member_hotel ON pchotel_room.hotelid = pchotel_member_hotel.hotelid')
		//->field('hotelname,id,roomtype,rtype,pchotel_room.hotelid,tjarr')
		->find();
		//print_r($room);die;
		$room['start'] = $start = $this->_get('start') ? $this->_get('start') : date('Y-m-d', $today);
		$room['end'] = $end = $this->_get('end') ? $this->_get('end') : date('Y-m-d', $today + 86400);
		$start = strtotime($start);
		$end = strtotime($end);
		$this->days = $days = ($end - $start) / 86400;
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
		$this->daydatas = json_encode($daydatas);
		if ($days == $day) {
			$room['status'] = 1;
		} else {
			$room['status'] = 0;
		}
		$room['total'] = $total;
		//print_r($room);die;
		$this->room = $room;
		$this->display();
	}
	//房间间数选择
	public function nums() {
		$this->nums = $this->_get('num');
		$this->display();
	}
	//订单预订
	public function doorder() {
		// $user = session('name'); //用户id
		$user = session('id'); //用户id
		$pay = $this->_post('pay'); //支付方式
		$mobile = $this->_post('mobile'); //预订手机号
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
		$dotime = $this->_post('start') ? strtotime($this->_post('start')) : '';
		$map2['id'] = $this->_post('rid');
		$map2['tjarr'] = array('like', '%' . $dotime . '%');
		$p = M('room')->where($map2)->count();
		if (!$p) {
			die(json_encode(array('status' => 0, 'msg' => '使用日期无法购买，请选择其他日期')));
		}
		$data = array();
		$data['telephone'] = $mobile;
		$data['username'] = $user;
		$data['roomid'] = $this->_post('rid'); //房间id
		$data['hotelid'] = $this->_post('hid'); //酒店id
		$data['ruzhudate'] = $start = strtotime($this->_post('start')); //入住日期
		$data['lidiandate'] = $end = strtotime($this->_post('end')); //离店日期
		$data['nums'] = $nums = $this->_post('nums'); //预订间数
		$data['rennums'] = $nums = $this->_post('rennums'); //预订人数
		$data['shoufei'] = $this->_post('total'); //总金额
		$data['linkman'] = $this->_post('linkman'); //预订人
		$data['kename'] = $this->_post('kename'); //入住人
		$data['isMobile'] = 1; //手机端预订支付
		$data['from'] = 1; //手机来源
		$overplus = $this->overplus($this->_post('rid'), $start, $end, $nums);
		if (!$overplus) {
			die(json_encode(array('status' => 0, 'msg' => '房间余量不足，请调整预定数量')));
		}
		if ($pay == 1) {
			$data['status'] = 3; //未支付
		} else {
			$data['status'] = 1; //未确认
		}
		$data['beizhu'] = $this->_post('remark'); //备注
		$data['addtime'] = time(); //预订时间
		$res = M('order')->add($data);
		if ($res) {
			//调整余量
			M('room')->where('id=' . $this->_post('rid'))->setField('tjarr', $overplus);
			die(json_encode(array('status' => 1, 'oid' => $res, 'msg' => '预订成功')));
		} else {
			die(json_encode(array('status' => 0, 'msg' => '系统出错')));
		}
	}
	//处理库存
	public function overplus($id, $start, $end, $num) {
		$tjarr = M('room')->where('id=' . $id)->getField('tjarr');
		$tjarr = explode('|', $tjarr);
		foreach ($tjarr as & $v) {
			$arr = explode('-', $v);
			if ($arr[0] >= $start && $arr[0] < $end) {
				if (strpos($arr[1], '_')) {
					$jiage = explode('_', $arr[1]);
					if ($jiage[1] - $num < 0) {
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
		$map = array();
		//$map['pchotel_order.username'] =session('name');
		$map['pchotel_order.orderid'] = $this->_get('id');
		$this->order = $or = M('order')->join('pchotel_room ON pchotel_order.roomid = pchotel_room.id')->join('pchotel_member_hotel ON pchotel_order.hotelid = pchotel_member_hotel.hotelid')->field('pchotel_room.roomtype,pchotel_member_hotel.hotelname,pchotel_member_hotel.telephone as tel,pchotel_member_hotel.address,pchotel_order.*')->where($map)->find();
		//print_r($or);die;
		$this->display();
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
		// $uid = session('name');
		$uid = session('id');
		if (!$uid) {
			header("location:" . __ROOT__ . "/Wechat/login");
		}
		import('ORG.Util.Page');
		$map['pchotel_order.username'] = $uid;
		$count = M('order')->where($map)->count();
		$page = new Page($count, 10);
		$page->setConfig('theme', '%upPage% %linkPage% %downPage%');
		$this->page = $page->show();
		$myorder = M('order')->join('pchotel_room ON pchotel_order.roomid = pchotel_room.id')->join('pchotel_member_hotel ON pchotel_order.hotelid = pchotel_member_hotel.hotelid')->field('roomtype,hotelname,city,city2,city3,pchotel_order.*')->order('pchotel_order.orderid desc')->where($map)->limit($page->firstRow . ',' . $page->listRows)->select();
		foreach ($myorder as $k => $v) {
			//地址
			$area = explode(',', $v['city']);
			$area1 = M('area')->where('id=' . $area[0])->getField('name');
			$area2 = M('area')->where('id=' . $area[1])->getField('name');
			$area3 = M('area')->where('id=' . $area[2])->getField('name');
			$myorder["$k"]['city'] = $area1 . $area2 . $area3;
		}
		//print_r($myorder);die;
		$this->myorder = $myorder;
		$this->display();
	}
	//订单取消
	public function docancel() {
		$data = array();
		//$data['uid'] 	=session('id');
		$data['orderid'] = $this->_get('id');
		$res = M('order')->where($data)->setField('status', 6);
		if ($res) {
			die(json_encode(array('status' => 1, 'msg' => '取消成功')));
		} else {
			die(json_encode(array('status' => 0, 'msg' => '系统出错')));
		}
	}
	//城市选择
	public function city() {
		$this->hotarea = M('area')->where(array('ishot' => 1))->order('sort desc,id asc')->limit(15)->select();
		$area = M('area')->where(array('level' => 2))->order('first asc')->select();
		$Zm = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$all = array();
		for ($i = 0;$i < 26;$i++) {
			$k = substr($Zm, $i, 1);
			$all[$k] = M('area')->where(array('level' => 2, 'first' => $k))->select();
		}
		$this->area = $all;
		$this->display();
	}
	//首页城市选择
	public function cityindex() {
		$this->hotarea = M('area')->where(array('ishot' => 1))->order('sort desc,id asc')->limit(15)->select();
		$area = M('area')->where(array('level' => 2))->order('first asc')->select();
		$Zm = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$all = array();
		for ($i = 0;$i < 26;$i++) {
			$k = substr($Zm, $i, 1);
			$all[$k] = M('area')->where(array('level' => 2, 'first' => $k))->select();
		}
		$this->area = $all;
		$this->display();
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
		import('ORG.Util.Page');
		$data['is_delete'] = 0;
		//$data['catid'] = $_GET['catid']?$_GET['catid']:6;
		$cid = $_GET['catid'];
		if ($cid) {
			$data['catid'] = $cid;
			$this->cate = M('cate')->where("id=$cid")->getField('name');
		} else {
			$data['catid'] = 6;
			$this->cate = '预定帮助';
		}
		$count = M('article')->where($data)->count();
		$page = new Page($count, 6);
		$page->setConfig('theme', '%upPage% %linkPage% %downPage%');
		$this->page = $page->show();
		$this->news = M('article')->where($data)->order("sort DESC,articleid DESC")->field('articleid,title,description,thumb')->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->display();
	}
	//预订须知详细页
	public function artshow() {
		$art['articleid'] = $this->_get('id');
		$this->art = M('article')->where($art)->field('title,username,addtime,content')->find();
		$this->display();
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
	public function chains() {
		$this->chain = M('liansuo')->order('id DESC')->select();
		$this->display();
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
	public function comments(){
		$oid = $_GET['id'];
		$info = M("order")->where("orderid=$oid")->field("hotelid,roomid")->find();
		$hotelname = M("member_hotel")->where("hotelid={$info['hotelid']}")->getField("hotelname");
		$roomname = M("room")->where("id={$info['roomid']}")->getField("roomtype");
		$this->assign('hotelname', $hotelname);
		$this->assign('roomname', $roomname);
		$this->assign('id', $oid);
		$this->display();
	}
	public function docomment(){
		$name = '/^.{6,30}$/';
		$content = '/^.{30,6000}$/';
		$err = [];
		if (preg_match($content, $_POST['comment']) == 0) {
			die(json_encode(array('status' => 0, 'msg' => '详细评价必须在10-2000个汉字之间')));
			//$this->display(0, '详细评价必须在10-2000个汉字之间');
		}
		$map['orderid'] = $_POST['orderid'];
		//$map['uid'] = session('id');
		$res2 = M('order')->where($map)->find();
		$m = M('comment');
		$data = array();
		$data['title'] = $_POST['tname'];
		$data['orderid'] = $_POST['orderid'];
		$data['rid'] = $res2['rid'];
		$data['landlord'] = $res2['landlord'];
		$data['hotelid'] = $res2['hotelid'];
		$data['content'] = $_POST['comment'];
		$data['unit'] = $_POST['manyi'];
		$data['label'] = $_POST['dengji'];
		$data['status'] = 1;
		$data['uid'] = session('id');
		$data['addtime'] = time();
		$res = $m->data($data)->add();
		if ($res) {
			$data2['is_comment'] = 1;
			M('order')->where("orderid = {$_POST['orderid']}")->save($data2);
			die(json_encode(array('status' => 1, 'msg' => '恭喜您，点评成功')));
			//$this->display(1, '恭喜您，点评成功');
		} else {
			die(json_encode(array('status' => 0, 'msg' => '点评失败')));
		}
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
}
?>