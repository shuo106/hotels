<?php

// 本类由系统自动生成，仅供测试用途

class IndexAction extends CommonAction {

    public function index() {
        header("Content-Type:text/html;charset=utf-8");
        //北京
        $map['city']=array('like','%100020%');
        $jdtj[] = M('member_hotel')->where($map)->order('is_tuijian desc,hotelid desc')->field('city,city2,city3,hotelid,hotelname')->limit(6)->select();
        //上海
        $map['city']=array('like','%100019%');
        $jdtj[] = M('member_hotel')->where($map)->order('is_tuijian desc,hotelid desc')->field('city,city2,city3,hotelid,hotelname')->limit(6)->select();
		//深圳
        $map['city']=array('like','%101484%');
        $jdtj[] = M('member_hotel')->where($map)->order('is_tuijian desc,hotelid desc')->field('city,city2,city3,hotelid,hotelname')->limit(6)->select();
        //广州
        $map['city']=array('like','%100038%');
        $jdtj[] = M('member_hotel')->where($map)->order('is_tuijian desc,hotelid desc')->field('city,city2,city3,hotelid,hotelname')->limit(6)->select();
        //南京
        $map['city']=array('like','%100218%');
        $jdtj[] = M('member_hotel')->where($map)->order('is_tuijian desc,hotelid desc')->field('city,city2,city3,hotelid,hotelname')->limit(6)->select();
        //西安
        $map['city']=array('like','%100319%');
        $jdtj[] = M('member_hotel')->where($map)->order('is_tuijian desc,hotelid desc')->field('city,city2,city3,hotelid,hotelname')->limit(6)->select();                                
        foreach($jdtj as &$val){
            foreach ($val as $k => &$v) {
                $ro['hotelid'] = $v['hotelid'];
                //最低房价
                $v['jiage'] = M('room')->where($ro)->order('yudingjia asc')->getField('yudingjia');
                //缩略图
                $v['thumb'] = M('photo')->where($ro)->order('isdefault desc')->getField('src');
            }
            unset($v);
        }
        unset($val);
        //首页轮播图广告位
        $ads = M("ads")->where("placeid=22")->select();

        $this->assign('ads', $ads);

        $this->assign('jdtj', $jdtj);

        //特色主题
        $this->theme=M('theme')->select();
        if ($token = $this->_get('token')) {
            $user = [];
            $data = json_decode($this->decrypt($token));
            $user['telephone'] = $data->mobile;
            $user['password'] = $data->password;
            $res = M('member')->where($user)->field('id,username,telephone')->find();
            if ($res) {
/*                 session_unset();
                session_destroy();
                $this->setsessionid(); */
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
/*                     session_unset();
                    session_destroy();
                    $this->setsessionid(); */
                    session('id', $data->mobile);
                    session('name', $data->mobile);
                    $return = [
                        'result' => 1,
                        'message' => '登录成功',
                        // 'token' => session('name'),
                        'code' => 200
                    ]; 
                }
            }    
        }
        $this->display();
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

    //返现

    public function getfanxian() {

        $list = M('comment')->field('pchotel_order.point,pchotel_comment.*')
                ->join('pchotel_order ON pchotel_comment.orderid=pchotel_order.orderid')
                ->where('pchotel_comment.status=2')
                ->limit(6)
                ->select();



        $gd = array();

        foreach ($list as $k => $v) {

            $gd[$k]['addtime'] = floor((time() - $v['addtime']) / 86400) > 0 ? floor((time() - $v['addtime']) / 86400) . '天前' : '刚刚';  //预订时间

            $gd[$k]['username'] = substr($v['username'], 0, 3) . '****' . substr($v['username'], -3, 3); //用户名

            $gd[$k]['fanxian'] = $v['point']; //返现
        }

        $this->assign('fx', $gd);
    }

    //订单

    public function getorder() {

        $order = M('order');

        $gd = $order->field('pchotel_order.*,pchotel_room.returnmoney,pchotel_member_hotel.hotelname,pchotel_room.roomtype')
                        ->join('pchotel_room ON pchotel_order.hotelid = pchotel_room.hotelid')
                        ->join('pchotel_member_hotel ON pchotel_order.hotelid = pchotel_member_hotel.hotelid')
                        ->where('pchotel_order.nums != 0 and pchotel_order.roomid=pchotel_room.id')
                        ->order('pchotel_order.orderid desc')
                        ->group('pchotel_order.hotelid')->limit(6)->select();

        foreach ($gd as $k => $v) {

            $gd[$k]['addtime'] = floor((time() - $v['addtime']) / 86400) > 0 ? floor((time() - $v['addtime']) / 86400) . '天前' : '刚刚';  //预订时间

            $gd[$k]['username'] = substr($v['username'], 0, 3) . '****' . substr($v['username'], -3, 3); //用户名

            $gd[$k]['wan'] = $v['lidiandate'] - $v['ruzhudate'] ? floor(($v['lidiandate'] - $v['ruzhudate']) / 86400) : 1; //预定住店天数

            $gd[$k]['fanxian'] = $v['returnmoney'] * (($v['lidiandate'] - $v['ruzhudate']) / 86400 + 1) * $v['nums']; //$v['fanxian'] ? $v['fanxian'] : 0; //返现
        }

        $this->assign('gd', $gd);
    }





}
