<?php

class HotelsAction extends CommonAction {

    protected $hotel; //酒店信息表
    protected $room; //酒店房间表
    protected $article; //文章表

    public function _initialize() {
        parent::_initialize();
        $this->hotel = M('member_hotel');
        $this->room = M('room');
        $this->article = M('article');
    }

    public function index() {
        redirect(__ROOT__.'/Hotels/search-0-0-0-0-0-0-0-0-0.html');
        $this->assign('title', '全国酒店列表');
        $map['pid'] = 0;
        $area = M('area')->where($map)->order('sort desc')->field('id,name')->select();
        foreach ($area as &$vo) {
            $vo['son'] = M('area')->where(array('pid' => $vo['id']))->field('id,name')->select();
        }
        $this->citys = $area;
        $this->display();
    }

    public function show() {
        //详情
        $id = $this->_get('id');
        $a = $this->_get('aa');
        $map['hotelid'] = $id;
        $map['is_delete'] = 0;
        $content = $this->hotel->where($map)->find();
        $area = explode(',', $content['city']);
        $this->prov = M('area')->where(array('id' => $area[0]))->find();
        $this->city = M('area')->where(array('id' => $area[1]))->find();
        $this->city1 = M('area')->where(array('id' => $area[2]))->find();
        $content['map']=explode(',',$content['map']);
        $content['xj']=M('xingji')->where('id='.$content['xingji'])->getField('name');
        $this->assign('hotelinfo', $content);
        $this->assign('title', $content['hotelname']);
        $this->assign('titles', $content['title']);
        $this->assign('keywords', $content['keywords']);
        $this->assign('description', $content['description']);
        $this->assign('peitao', json_decode($content['sheshi'], true));
        $this->assign('jiaotong', $content['traffic']);
        $this->assign('canyin', json_decode($content['canyin'], true));
        $this->assign('kfsheshi', json_decode($content['kfsheshi'], true));
        $this->assign('yule', json_decode($content['yule'], true));
        $this->assign('xinyongka', json_decode($content['xinyongka'], true)); 

        //判断是否收藏
        if(session('id')){
            $this->collection=M('app_collection')->where(array('uid'=>session('id'),'hotelid'=>$id))->count();
        }

        //附近酒店
        $nearby=$this->near_hotel($content);
        foreach ($nearby as &$v) {
            $v['thumb']=M('photo')->where('hotelid='.$v['hotelid'])->order('isdefault desc')->getField('src');
            $v['price']=M('room')->where('hotelid='.$v['hotelid'])->order('yudingjia asc')->getField('yudingjia');
            $v['price']||$v['price']=0;
            $v['xingji']=M('xingji')->where('id='.$v['xingji'])->getField('name');
        }

        unset($v);
        $this->nearby=$nearby;
        //点评
        $comm['hotelid'] = $id;
        $comm['status'] = 2;
        $comments = M('comment')->where($comm)->order('id desc')->limit(10)->select();
        $unit=0;
        foreach($comments as &$v){
            $v['userinfo']=M('member')->where('id='.$v['uid'])->field('icon,username')->find();
            $unit+=$v['unit'];
        }
        unset($v);
       
        $this->comment_total=count($comments);
        $this->unit=$unit?$unit/count($comments):100;
        $this->fen=$this->unit/20;


        foreach($comments as $k =>$v){
             $arr=explode(',', $v['thumb']);
             $comments[$k]['photo']=array_filter ($arr);
        }
        
        $this->comments=$comments;
        
        //图集
        $p = M('photo');
        $zhu = $p->where("hotelid='$id' AND isdefault=1")->find();
        $this->assign('da', $zhu['src']);
        $photo = $p->where("hotelid='$id' AND src!=''")->order('photoid desc')->limit(6)->select();
        $this->assign('fu', $photo);

            $rArr = $this->room->where('hotelid=' . $_GET['id'])->select();
            $today = strtotime(date('Ymd'));
            $start = $_GET['start'] ? strtotime($this->_get('start')) : $today; //开始时间戳
            $end = $_GET['end'] ? strtotime($this->_get('end')) : $today + 86400; //结束时间戳  
            $this->start=date('Y-m-d',$start);
            $this->end=date('Y-m-d',$end);    
            foreach ($rArr as $ke => &$vo) {
                $min = 9999999; //最低价
                $minprice = array(); //酒店房间最低价数组
                $tjarr = explode('|', substr($vo['tjarr'], 0, -1));
                $price = array();

                for ($i = $start; $i < $end; $i+=86400) {
                    $day = date('Ymd', $i);
                    $price[$day] = '满房';
                    foreach ($tjarr as $key => $val) {
                        $jiage = explode('-', $val);
                        $oneday = explode('_', $jiage[1]); //得到房间数量和价格

                        if ($oneday[0] < $min) {
                            $min = $oneday[0];
                        }
                        $vo['yudingjia'] = $oneday[0];

                        if ($day == date('Ymd', $jiage[0])) {
                            $price[$day] = $oneday[0];
                            if ($jiage[0] == $today) {
                                $vo['yudingjia'] = $oneday[0];
                            }
                        }
                    }
                }
                if (!in_array('满房', $price)) {
                    $vo['statuss'] = 1;
                }
                if ($min != 9999999) {
                    $minprice[] = $min;
                }
                $vo['tj'] = $price;
            }
            $this->assign('roomtype', $rArr);
            $this->assign('url', $url);
            $this->assign('site_title', $content['hotelname']);
            $this->display('show');        
    }


    //收藏
    public function shoucang(){
        if(!session('id')){
            die(json_encode(array('status' => -1, 'msg' => '请登录后操作')));            
        }
        $data['uid']=session('id');
        $data['hotelid']=$_GET['id'];
        $is=M("app_collection")->where($data)->count();
        $status=0;
        if($is){
            $status=2;
            $rs=M("app_collection")->where($data)->delete();
        }else{
            $status=1;
            $rs=M("app_collection")->add($data);
        }
        if($rs){
            die(json_encode(array('status' => $status, 'msg' => '操作成功')));
        }else{
            die(json_encode(array('status' => $status, 'msg' => '收藏失败')));
        }
    }

    //附近房源
    public function near_hotel($content,$limit=5){
        $lng=$content['map'][0];
        $lat=$content['map'][1];
        $id=$content['hotelid'];
        return M()->query("SELECT hotelid,hotelname,xingji,
            ROUND(6378.138*2*ASIN(SQRT(POW(SIN(({$lat}*PI()/180-SUBSTRING_INDEX(map,',',-1)*PI()/180)/2),2)+COS({$lat}*PI()/180)*COS(SUBSTRING_INDEX(map,',',-1)*PI()/180)*POW(SIN(({$lng}*PI()/180-SUBSTRING_INDEX(map,',',1)*PI()/180)/2),2)))*1000) AS juli 
            from pchotel_member_hotel where hotelid!={$id} ORDER BY juli asc limit {$limit}");
    }    

    public function order() {


      if(empty($_SESSION['id'])){
         echo "<script>alert('请您先登录!');window.location.href='".__ROOT__."/login.html';</script>";
        }

        $this->minfo=M('member')->where('id='.$_SESSION['id'])->find();

       //  dump($this->minfo);

        $start = strtotime(session('start'));
        $end = strtotime(session('end'));


       // $start =$this->_get('ruzhu') ? strtotime($this->_get('ruzhu')) : $today;
       // $end   =$this->_get('likai') ? strtotime($this->_get('likai')) : $today+86400;


        $roo['pchotel_room.id'] = $id = $this->_get('id');
        $this->roominfo = $roominfo = $this->room->join('pchotel_member_hotel ON pchotel_room.hotelid = pchotel_member_hotel.hotelid')
                ->join('pchotel_photo ON pchotel_room.hotelid = pchotel_photo.hotelid')
                ->field('pchotel_member_hotel.address,pchotel_member_hotel.xingji,pchotel_member_hotel.hotelname,pchotel_photo.src,pchotel_room.*')
                ->order('pchotel_photo.isdefault desc')
                ->where($roo)
                ->find();
        $this->money = $this->getPriceList($id, $start, $end);
        $this->company = $roominfo['hotelname'];
        $this->display();
    }

    //获取指定 房间 指定时间价格
    public function getroommoney() {
        $start = strtotime($_GET['ru']);
        $end = strtotime($_GET['li']);
        $id = intval($_GET['id']);
        echo $this->getPriceList($id, $start, $end);
    }





    //获取时间段内价格列表
    function getPriceList($id, $start, $end) {
        //获得该房间的信息
        $map['id'] = $id;
        $roominfo = M('room')->where($map)->field('tjarr')->find();
        //总金额
        $total=0;         
        //房间价格        
        $marray = array();
        for ($i = $start; $i < $end; $i+=86400) {
            $marray[$i] = '满房';
        }

       $daydata = explode('|', substr($roominfo['tjarr'], 0, -1));
     
        foreach ($marray as $k => $v) {
            foreach ($daydata as $vo) {
                $oneday = explode('-', $vo);

                if ($oneday[0] == $k) {

                    //修改开始 
                    /* $total+=floor($oneday[1]);
                     $marray[$k] = floor($oneday[1]) . '元';*/
                    
                    $onecof=explode('_',$oneday[1]);
                     //dump($onecof);
                    $total+=$onecof[0];
                    $marray[$k] =$onecof[0]. '元|'.$onecof[1]."间";

                     //如果当天当前房间的库存==0，那么当天应该是满房
                     if((int)$onecof[1]<=0){
                      $marray[$k] = '满房';   
                     }

                }

            }
        }
         
        $man = '';
        $money = '<ul>';
        foreach ($marray as $k => $v) {
            $man.=$v . ',';
            $money.='<li><h3>' . date('m月d日', $k) . '</h3><p>' . $v . '</p></li>';
        }
        $money.='</ul>  <input type="hidden" name="isman" value="s' . $man . '">';
        $money.='<input type="hidden"  name="total" value="'.$total.'"><input type="hidden" name="isman" value="'.$isman.'">';        
        return $money;
    }



    /*
    //获取时间段内价格列表
    function getPriceList($id, $start, $end) {
        //获得该房间的信息
        $map['id'] = $id;
        $roominfo = M('room')->where($map)->field('tjarr')->find();
        //总金额
        $total=0;         
        //房间价格        
        $marray = array();
        for ($i = $start; $i < $end; $i+=86400) {
            $marray[$i] = '满房';
        }
        $daydata = explode('|', substr($roominfo['tjarr'], 0, -1));
        foreach ($marray as $k => $v) {
            foreach ($daydata as $vo) {
                $oneday = explode('-', $vo);
                if ($oneday[0] == $k) {
                    $total+=floor($oneday[1]);
                    $marray[$k] = floor($oneday[1]) . '元';
                }
            }
        }
        $man = '';
        $money = '<ul>';
        foreach ($marray as $k => $v) {
            $man.=$v . ',';
            $money.='<li><h3>' . date('m月d日', $k) . '</h3><p>' . $v . '</p></li>';
        }
        $money.='</ul>	<input type="hidden" name="isman" value="s' . $man . '">';
        $money.='<input type="hidden" name="total" value="'.$total.'"><input type="hidden" name="isman" value="'.$isman.'">';        
        return $money;
    }*/

    //订单提交
    public function ordertijiao() {
        $roomid = $ro['id'] = $_POST['id'];
        $roominfo = M('room')->where($ro)->field('hotelid,returnmoney,paytype,tjarr')->find();
        $ruzhudate = strtotime($_POST['ruzhudate']);
        $lidiandate = strtotime($_POST['lidiandate']);
        $nums = $_POST['nums'] ? $_POST['nums'] : 1; //房间数
        $linkname = $_POST['linkname'];
        $telephone = $_POST['telephone'];
        $beizhu = $_POST['beizhu'];

        if($ruzhudate>$lidiandate){
            die(json_encode(array('status' => 0, 'msg' => '入住时间不能大于离店时间')));  
        }
       
       // $price = $this->getRoomDate($roominfo['tjarr'], $ruzhudate, $lidiandate);

        $price = $this->getRoomDate2($roominfo['tjarr'], $ruzhudate, $lidiandate);


       
       // dump($price);die;
        
        if (!$price['status']) {
            die(json_encode(array('status' => 0, 'msg' => '选择日期中有房间为满房状态')));
        }

        
        if ((int)$price['min']<$nums) {
            die(json_encode(array('status' => 0, 'msg' => '选择日期中有房间的剩余房间不足以订购')));
        }


        if (!$linkname) {
            die(json_encode(array('status' => 0, 'msg' => '请填写联系人')));
        }

        if (!$telephone) {
            die(json_encode(array('status' => 0, 'msg' => '请填写联系电话')));
        }

        $money = 0;
         foreach ($price['tj'] as $k => $v) {
            $money += substr($v, 2);
        }
        // $name = $_SESSION['name'];
        $name = $_SESSION['id'];
        $data = array();
        if (!$name) {
            $us['username'] = $telephone;
            $isuser = M('member')->where($us)->count();
            if ($isuser > 0) {
                die(json_encode(array('status' => -1, 'msg' => '当前使用联系电话已经注册了会员，请先登陆再来预订')));
            }
            $data['username'] = $username = $telephone;
        } else {
            $data['username'] = $username = $name;
        }
        $data['shoufei'] = $total = $money * $nums; //总金额
        $data['hotelid'] = $roominfo['hotelid'];
        $data['roomid'] = $roomid;
        $data['addtime'] = $ordertime = time();
        $data['ruzhudate'] = $ruzhudate; //$_POST['ruzhudate'];
        $data['lidiandate'] = $lidiandate; //$_POST['lidiandate'];		
        $data['nums'] = $nums;
        $data['linkman'] = $linkname;
        $data['kename'] = $_POST['kename'];
      
        $data['telephone'] = $telephone;
        $data['beizhu'] = $beizhu;
        $data['point'] = $roominfo['returnmoney'] * $nums * ($lidiandate - $ruzhudate) / 86400;
        $data['status'] = ($roominfo['paytype'] == 1) ? 3 : 1; //未支付
        $data['rennums'] = $_POST['rennums'];
        $data['zuizaotime'] = $_POST['zuizaotime'];
        $data['zuiwantime'] = $_POST['zuiwantime'];
        $res = M('order')->add($data);
        if ($res) {
            $smsData = include 'Admin/Conf/message.php';
            Vendor('ChuanglanSmsHelper.ChuanglanSmsApi');
            $SMS = new ChuanglanSmsApi();
            if ($smsData['hotelOrderSend'] == '1' && strlen($telephone) == 11) {
                $roomdata = M('room')->where(array('pchotel_room.id' => $roomid))
                        ->join('pchotel_member_hotel ON pchotel_room.hotelid =pchotel_member_hotel.hotelid')
                        ->field('roomtype,hotelname')
                        ->find();
                $relArr = array('#WEBNAME#', '#LOGINNAME#', '#ORDERTIME#', '#ORDERNUMS#', '#ORDERTOTAL#', '#HOTELNAME#', '#ROOMNAME#', '#STARTTIME#', '#ENDTIME#');
                $subArr = array($this->basicinfo['webname'], $username, date('Y-m-d H:i:s', $ordertime), $nums, $total, $roomdata['hotelname'], $roomdata['roomtype'], $this->_post('ruzhudate'), $this->_post('lidiandate'));
                $regSms = str_replace($relArr, $subArr, $smsData['hotelOrderSms']);
                $name = iconv("UTF-8", "gb2312", $smsData['smsname']);
                $pwd = iconv("UTF-8", "gb2312", $smsData['smspwd']);
                $con =$regSms;
               // var_dump($con);
                $str=$SMS->sendSMS($name,$pwd,$telephone,$con);
            }
                        //如果订单提交成功，在这里要让订购成功的房间-1;   
            $this->changeNums($roomid,$ruzhudate,$lidiandate,$data['nums']);
            die(json_encode(array('status' => $res, 'msg' => '订单提交成功')));
        } else {
            die(json_encode(array('status' => 0, 'msg' => '操作失败')));
        }
    }




    //订单提交成功后，改变相关日期Room的库存数量
    public function  changeNums($roomid,$start,$end,$nums){
     // echo $roomid."-".$start."-".$end."-".$nums."-";  die;  
     $room=M('room')->where('id='.$roomid)->find();
     $tjarr=$room['tjarr'];
     $arr = explode('|', substr($tjarr, 0, -1));
     //dump($arr);
     for($i=0;$i<count($arr);$i++)
     {
            $oneday = explode('-', $arr[$i]);
            $onecof=explode('_',$oneday[1]);
      
            if( $oneday[0]>=$start  && $oneday[0]<=$end)
            {
                $arr[$i] = $oneday[0].'-'. $onecof[0]."_".($onecof[1]-$nums);
             }else{
                 // echo $oneday[0]."-";
                $arr[$i] = $oneday[0].'-'. $onecof[0]."_".$onecof[1];   
            }
    
      }
     $data['tjarr']=implode('|', $arr);
     //echo $data['tjarr'];
     $room=M('room')->where('id='.$roomid)->save($data);
    }




//获取房间 时间段内价格  
    public function getRoomDate2($tjarr, $start, $end) {
        if (!$start) {
            $start = strtotime(date('Y-m-d'));
            $end = $start + 86400;
        }
        //房间价格 
        $marray = array();
        for ($i = $start; $i < $end; $i+=86400) {
            $marray[$i] = '满房';
        }
        $daydata = explode('|', substr($tjarr, 0, -1));
        $min = 9999999;
        foreach ($marray as $k2 => &$v2) {
            foreach ($daydata as $vo) {
                $oneday = explode('-', $vo);
                if ($oneday[0] == $k2) {

                     $onecof=explode('_',$oneday[1]); 
                      $v2 = '¥' . $onecof[0];

                    // 由于之前很多之前的数据中并没有进行房间数量管理，所以这里把没有房间数量都看成是0
                     if(!isset($onecof[1])){
                         $onecof[1]='0';
                     }
                    
                    
                    //用冒泡的形式得到这段时间内，最多的最低的剩余房间
                    if(isset($onecof[1])){
                        if ( (int)$onecof[1] < $min) { 
                            $min = $onecof[1];
                        }
                     }

                    //如果当天当前的剩余房间==0，那么当天应该是满房
                     if(isset($onecof[1])){
                         if($onecof[1]<=0){
                          $v2 = '满房';   
                         }
                     }
            
                }
            }
        }


        $price = array();
        $price['tj'] = $marray;

        $price['min'] = $min; //当前剩余房间数最少的房间数的数量
        $tjstr = 's';
        foreach ($marray as $v) {
            $tjstr.=$v;
        }
        if (strpos($tjstr, '满房')) {
            $price['status'] = 0;
        } else {
            $price['status'] = 1;
        }

        return $price;

    }




    //订单详情
    public function ordersuccess() {
        $map['orderid'] = $this->_get('id');
        if (session('id')) {
            $map['pchotel_order.username'] = session('id');
        }
        $o = M('order')->join('pchotel_member_hotel ON pchotel_order.hotelid = pchotel_member_hotel.hotelid')
                ->join('pchotel_room ON pchotel_order.roomid = pchotel_room.id')
                ->join('pchotel_photo ON pchotel_order.hotelid = pchotel_photo.hotelid')
                ->order('pchotel_photo.isdefault desc')
                ->where($map)
                ->field('pchotel_member_hotel.xingji,pchotel_member_hotel.address,pchotel_member_hotel.telephone as tel,pchotel_room.tjarr,pchotel_member_hotel.hotelname,pchotel_room.roomtype,pchotel_room.fjchuang,pchotel_room.zaocan,pchotel_room.swang,pchotel_room.paytype,pchotel_photo.src,pchotel_order.*')
                ->find();
        $this->company = $o['hotelname'];
        if (!session('id')) {
            $data['username'] = $o['username'];
            $data['password'] = md5($o['username']);
            $data['truename'] = $o['linkman'];
            $data['Mobile'] = $o['telephone'];
            $data['regip'] = $_SERVER['REMOTE_ADDR'];
            $userid = M('member')->add($data);
            $_SESSION['id'] = $userid;
            $_SESSION['name'] = $o['username'];
        }
        $price = $this->getRoomDate2($o['tjarr'], $o['ruzhudate'], $o['lidiandate']);
        $m = array();
        $i = 0;
        foreach ($price['tj'] as $k => $v) {
            $m[$i]['date'] = date('Y-m-d', $k);
            switch (date('w', $k)) {
                case 0;
                    $w = '星期天';
                    break;
                case 1;
                    $w = '星期一';
                    break;
                case 2;
                    $w = '星期二';
                    break;
                case 3;
                    $w = '星期三';
                    break;
                case 4;
                    $w = '星期四';
                    break;
                case 5;
                    $w = '星期五';
                    break;
                case 6;
                    $w = '星期六';
                    break;
            }
            $m[$i]['week'] = $w;
            $m[$i]['money'] = substr($v, 2);
            $i++;
        }
        $this->m = $m;
        $this->orderdata = $o;
        $this->display();
    }

    //支付
    public function dopay() {
        $data['orderid'] = $this->_get('id');
        if (session('name')) {
            $data['pchotel_order.username'] = session('name');
        }
        $this->orderdata = M('order')->join('pchotel_member_hotel ON pchotel_order.hotelid = pchotel_member_hotel.hotelid')
                ->join('pchotel_room ON pchotel_order.roomid = pchotel_room.id')
                ->join('pchotel_photo ON pchotel_order.hotelid = pchotel_photo.hotelid')
                ->where($data)
                ->field('pchotel_member_hotel.xingji,pchotel_member_hotel.address,pchotel_member_hotel.telephone as tel,pchotel_room.roomtype,pchotel_room.fjchuang,pchotel_room.hotelname,pchotel_room.zaocan,pchotel_room.swang,pchotel_photo.src,pchotel_order.*')
                ->find();
        $this->display();
    }

    //获取房间 时间段内价格
    public function getRoomDate($tjarr, $start, $end) {
        if (!$start) {
            $start = strtotime(date('Y-m-d'));
            $end = $start + 86400;
        }
        //房间价格 
        $marray = array();
        for ($i = $start; $i < $end; $i+=86400) {
            $marray[$i] = '满房';
        }
        $daydata = explode('|', substr($tjarr, 0, -1));
        $min = 9999999;
        foreach ($marray as $k2 => &$v2) {
            foreach ($daydata as $vo) {
                $oneday = explode('-', $vo);
                if ($oneday[0] == $k2) {
                    if ($oneday[1] < $min) {
                        $min = $oneday[1];
                    }
                    $v2 = '¥' . $oneday[1];
                }
            }
        }
        $price = array();
        $price['tj'] = $marray;
        if ($min == 9999999) {
            $min = 0;
        }
        $price['min'] = $min;
        $tjstr = 's';
        foreach ($marray as $v) {
            $tjstr.=$v;
        }
        if (strpos($tjstr, '满房')) {
            $price['status'] = 0;
        } else {
            $price['status'] = 1;
        }
        return $price;
    }

    public function getacity($s) {
        $arr = explode('/', $s);
        $arrA = $arr[1];
        return $arrA;
    }

    public function getbcity($s) {
        $arr = explode('/', $s);
        $arrA = $arr[2];
        return $arrA;
    }
    //连锁酒店列表
    public function liansuo(){
        //获取酒店品牌列表
        $field=',(SELECT COUNT(*) as count FROM pchotel_member_hotel  WHERE pchotel_member_hotel.lspp=pchotel_liansuo.name AND pchotel_member_hotel.is_delete=0 LIMIT 1) as count';
        $this->lspp=M('liansuo')->field('name'.$field)->order('count desc')->select();
        import("@.ORG.Util.Page"); //引用分页类  
        $data = array();
        $data['is_delete'] = 0;
        //连锁品牌 
        if ($_GET['brand']) {
            $_leibie.=$this->_get('brand').'连锁品牌';
            $data['lspp'] = $this->_get('brand');
        }
        //排序
        $order='pchotel_member_hotel.hotelid desc';             //默认排序
        $field=",(SELECT yudingjia from pchotel_room WHERE pchotel_room.hotelid=pchotel_member_hotel.hotelid ORDER BY yudingjia ASC LIMIT 1) as jiage";         //价格
        $field.=",(SELECT AVG(unit) FROM pchotel_comment WHERE pchotel_comment.hotelid=pchotel_member_hotel.hotelid) as unit";                                  //评分
        $field.=",(SELECT count(*) FROM pchotel_comment WHERE pchotel_comment.hotelid=pchotel_member_hotel.hotelid) as comment_total";                          //评论人数
        if($this->_get('orderby')){
            switch ($this->_get('orderby')) {
                case 'price':       //按价格排序
                    $order='jiage asc';
                    break;
                case 'unit':       //按评分排序
                    $order='unit desc';
                    break;                    
            }
        }
        //开始时间 结束时间
        $start = $this->_get('ruzhu') ? strtotime($this->_get('ruzhu')) : strtotime(date('Ymd'));
        $end = $this->_get('likai') ? strtotime($this->_get('likai')) : strtotime(date('Ymd')) + 86400;
        session('start',date('Y-m-d',$start));
        session('end',date('Y-m-d',$end));
        $count = $this->hotel->where($data)->count();
        $this->total=$count;
        $Page = new Page($count, 10);
        $this->page = $Page->show();
        $res = M('member_hotel')
                ->join('pchotel_photo ON pchotel_member_hotel.hotelid = pchotel_photo.hotelid')
                ->field('pchotel_photo.src,pchotel_member_hotel.*'.$field)
                ->where($data)
                ->order($order)
                ->group('pchotel_member_hotel.hotelid')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
                //dump($data);die;
        foreach ($res as $k => &$v) {
            $area = explode(',', $v['city']);
            $v['hcity'] = M('area')->where(array('id' => $area[1]))->getField('name');
            $v['qcity'] = M('area')->where(array('id' => $area[2]))->getField('name');
            //客房
            $hote['hotelid'] = $v['hotelid'];
            //最近两天预定人数
            $hote['addtime']=array('EGT',time()-86400*2);     //最近两天
            $v['order_tltal']=M('order')->where($hote)->count();
        }
        $this->assign('hotellist', $res);
        $x = M('xingji')->limit(12)->select();
        $this->assign('x', $x);
        $con = M('ads')->where('placeid=23')->order('adsid desc')->find();
        $this->assign('ads', $con);
        //status=1 推荐  sort排序 促销信息
        $cx = $this->article->where("catid=4")->order('sort desc,addtime desc')->field('articleid,title,addtime')->limit(6)->select();
        $this->assign('cuxiao', $cx);
        $this->assign('leibie',$_leibie."酒店列表");

        $this->display();        
    }
    //检索列表
    public function searchlist() {
        //获取酒店品牌列表
        $field=',(SELECT COUNT(*) as count FROM pchotel_member_hotel  WHERE pchotel_member_hotel.lspp=pchotel_liansuo.name LIMIT 1) as count';
        $this->lspp=M('liansuo')->field('name'.$field)->order('count desc')->limit(14)->select();

        import("@.ORG.Util.Page"); //引用分页类	
        $data = array();
        $data['is_delete'] = 0;
        //城市
        $city=$this->_get('from') ? $this->_get('from') : 100020;
        session('city_id',$city);
        $pro['pid'] = $city;
        $this->qus = M('area')->where($pro)->field('name,id')->select();
        $dbright = $this->hotel->where("city like '%$city%'")->field('fjdb1,fjdb2,syq')->limit(12)->select();
        $this->assign('dbright', $dbright);
        //城市
        $this->city = M('area')->where(array('id' => $city))->getField('name');
        session('city_name',$this->city);
        $_leibie=$this->city;
        $data['city'] = array('like', '%' . $city . '%');
        //城市县区
        $qu = $this->_get('qu');
        if($qu){
            if(strpos($qu,'_')!==false){
                $dibiao=explode('_',$qu);
                $this->dibiao=$dibiao;
                switch ($dibiao[1]) {
                    case 1:         //行政区
                        $this->qu = M('area')->where(array('id' => $dibiao[0]))->getField('name');
                        $_leibie.=$this->qu;
                        $data['city'] = array('like', '%' . $dibiao[0] . '%');
                        break;
                    case 2:         //车站
                        $_dibiao['hc1'] = $dibiao[0];
                        $_dibiao['hc2'] = $dibiao[0];  
                         break;                  
                    case 3:         //机场
                        $_dibiao['jc1'] = $dibiao[0];
                        $_dibiao['jc2'] = $dibiao[0];
                         break;
                    case 4:
                        $ditie=explode('|',$dibiao[0]);
                        $this->ditie=$ditie;
                        $_dibiao['dt1'] = $ditie[1];
                        $_dibiao['dt2'] = $ditie[1];
                        $_dibiao['dt3'] = $ditie[1];                        
                    case 5:
                        $_dibiao['dx1'] = $dibiao[0];
                        $_dibiao['dx2'] = $dibiao[0];
                        $_dibiao['dx3'] = $dibiao[0];
                         break;
                    case 6:
                        $_dibiao['hz1'] = $dibiao[0];
                        $_dibiao['hz2'] = $dibiao[0];
                         break;         
                }
                if($_dibiao){
                    $_dibiao['_logic'] = 'or';
                    $fjdb=M('fjdb')->where(array('_complex'=>$_dibiao))->field('hotelid')->select();
                    foreach($fjdb as $v){
                        $fids[]=$v['hotelid'];
                    }
                    unset($data);
                    $data['pchotel_member_hotel.hotelid'] = array('in',$fids);                    
                }
            }else{
                $this->dibiao=array(0,1);
                $this->qu = M('area')->where(array('id' => $qu))->getField('name');
                $_leibie.=$this->qu;
                $data['city'] = array('like', '%' . $qu . '%');
            }
        }
        //酒店位置
        if ($_GET['hotelnear']) {
            $this->hotelnear = $_GET['hotelnear'];
            $wz = $this->_get('hotelnear');
            $where['address'] = array('like', '%' . $wz . '%');
            $where['fjdb1'] = array('like', '%' . $wz . '%');
            $where['fjdb2'] = array('like', '%' . $wz . '%');
            $where['fjdb3'] = array('like', '%' . $wz . '%');
            $where['fjdb4'] = array('like', '%' . $wz . '%');
            $where['fjdb5'] = array('like', '%' . $wz . '%');
            $where['_logic'] = 'or';
            $data['_complex'] = $where;
        }
        //酒店名称
        if ($_GET['company']) {
            $this->company = $_GET['company'];
            $_leibie.=$this->company;
            $data['pchotel_member_hotel.hotelname'] = array('like', '%' . $this->_get('company') . '%');
        }
        //价格
        if ($_GET['jiage']) {
            $jiage = explode('_', $this->_get('jiage'));
            $_leibie.=$jiage[0]."元到".$jiage[1]."元";
            $hids = M('member_hotel')->where($data)->field('hotelid')->select();
            $hidss = '';
            foreach ($hids as $k => $v) {
                $isok = $this->getHotelMaxMin($v['hotelid'], $jiage);
                if ($isok) {
                    $hidss.=$isok . ',';
                }
            }
            unset($data);
            $data['pchotel_member_hotel.hotelid'] = array('in', rtrim($hidss, ','));
        }
        //连锁品牌 
        if ($_GET['brand']) {
            $_leibie.=$this->_get('brand');
            $data['lspp'] = $this->_get('brand');
        }
        //星级 
        if ($_GET['xing']) {
            $data['xingji'] = $this->_get('xing');
            $xingji=M('xingji')->where('id='.$_GET['xing'])->getField('name');
            $_leibie.=$xingji;
        }
        //主题
        if($_GET['theme']){
            $data['theme']=$this->_get('theme');
        }
        //排序
        $order='pchotel_member_hotel.hotelid desc';             //默认排序
        $field=",(SELECT yudingjia from pchotel_room WHERE pchotel_room.hotelid=pchotel_member_hotel.hotelid ORDER BY yudingjia ASC LIMIT 1) as jiage";         //价格
        $field.=",(SELECT AVG(unit) FROM pchotel_comment WHERE pchotel_comment.hotelid=pchotel_member_hotel.hotelid) as unit";                                  //评分
        $field.=",(SELECT count(*) FROM pchotel_comment WHERE pchotel_comment.hotelid=pchotel_member_hotel.hotelid) as comment_total";                          //评论人数
        if($this->_get('orderby')){
            switch ($this->_get('orderby')) {
                case 'price':       //按价格排序
                    $order='jiage asc';
                    break;
                case 'unit':       //按评分排序
                    $order='unit desc';
                    break;                    
            }
        }
        //开始时间 结束时间
        $start = $this->_get('ruzhu') ? strtotime($this->_get('ruzhu')) : strtotime(date('Ymd'));
        $end = $this->_get('likai') ? strtotime($this->_get('likai')) : strtotime(date('Ymd')) + 86400;
        session('start',date('Y-m-d',$start));
        session('end',date('Y-m-d',$end));
        $count = $this->hotel->where($data)->count();
        $this->total=$count;
        $Page = new Page($count, 10);
        $this->page = $Page->show();
        $res = M('member_hotel')
                ->join('pchotel_photo ON pchotel_member_hotel.hotelid = pchotel_photo.hotelid')
                ->field('pchotel_photo.src,pchotel_member_hotel.*'.$field)
                ->where($data)
                ->order($order)
                ->group('pchotel_member_hotel.hotelid')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
                //dump($res);die;
        foreach ($res as $k => &$v) {
            $area = explode(',', $v['city']);
            $v['city']=$area;
            $v['hcity'] = M('area')->where(array('id' => $area[1]))->getField('name');
            $v['qcity'] = M('area')->where(array('id' => $area[2]))->getField('name');
            //客房
            $hote['hotelid'] = $v['hotelid'];
            //最近两天预定人数
            $hote['addtime']=array('EGT',time()-86400*2);     //最近两天
            $v['order_tltal']=M('order')->where($hote)->count();
            //星级
            $v['xj']=M('xingji')->where('id='.$v['xingji'])->getField('name');
        }
        unset($v);
        $this->assign('hotellist', $res);
        $x = M('xingji')->limit(12)->select();
        $this->assign('x', $x);
        $con = M('ads')->where('placeid=23')->order('adsid desc')->find();
        $this->assign('ads', $con);
        //status=1 推荐  sort排序 促销信息
        $cx = $this->article->where("catid=4")->order('sort desc,addtime desc')->field('articleid,title,addtime')->limit(6)->select();
        $this->assign('cuxiao', $cx);
        $this->assign('leibie',$_leibie."酒店列表");
        //获取当前城市地标信息
        $subway=M('subway')->where(array('city'=>session('city_name')))->select();     //地铁
        foreach ($subway as $v) {
            $subwaylist[$v['ditie']][]=$v['zhanpai'];
        }

        $this->subway=$subwaylist;

        $where['cityname']  = session('city_name');
        $where['province']  = session('city_name');
        $where['_logic'] = 'or';
        $map['_complex'] = $where;
        $this->train=M('train')->where($map)->field('train as name')->select();             //车站
        $this->airport=M('airport')->where($map)->field('airport as name')->select();       //机场
        $this->college=M('college')->where($map)->field('college as name')->select();       //高校
        $this->exhibit=M('exhibit')->where($map)->field('exhibit as name')->select();       //会展

        $this->display();
    }

    public function search() {
        import("@.ORG.Util.Page"); //引用分页类	
        $data = array();
        $data['is_delete'] = 0;
        $citys = $this->_get('from');
        $this->city = $city = M('area')->where(array('name' => $citys))->getField('id');
        $data['city'] = array('like', '%' . $city . '%');
        $pro['pid'] = array('like', '%' . $city . '%');
        $this->qus = M('area')->where($pro)->field('name,id')->limit(14)->select();
        $dbright = $this->hotel->where("city like '%$city%'")->field('fjdb1,fjdb2,syq')->limit(12)->select();
        $this->assign('dbright', $dbright);
        //附近地标
        $fjdb = M('fjdb');
        //地标			
        if ($_GET['marks']) {
            $company = $marks = $this->_get('marks');
            $data['fjdb1|fjdb2|fjdb3|fjdb4|fjdb5'] = array('like', '%' . $marks . '%');
        }
        //商业圈
        if ($_GET['store']) {
            $company = $store = $this->_get('store');
            $data['syq'] = array('like', '%' . $store . '%');
        }
        //飞机场
        if ($_GET['jc']) {
            $company = $jc = $this->_get('jc');
            $map['jc1|jc2'] = array('like', '%' . $jc . '%');
            $hotels = $fjdb->where($map)->field('hotelid')->select();
            $ids = array();
            foreach ($hotels as $v) {
                $ids[] = $v['hotelid'];
            }
            $data['pchotel_member_hotel.hotelid'] = array('in', array_unique($ids));
        }
        //大学
        if ($_GET['dx']) {
            $company = $dx = $this->_get('dx');
            $map['dx1|dx2|dx3'] = array('like', '%' . $dx . '%');
            $hotels = $fjdb->where($map)->field('hotelid')->select();
            $ids = array();
            foreach ($hotels as $v) {
                $ids[] = $v['hotelid'];
            }
            $data['pchotel_member_hotel.hotelid'] = array('in', array_unique($ids));
        }
        //景区
        if ($_GET['jing']) {
            $company = $jing = $this->_get('jing');
            $data['fjlv1|fjlv2|fjlv3|fjlv4|fjlv5|fjlv6|fjlv7|fjlv8|fjlv9|fjlv10'] = array('like', '%' . $jing . '%');
        }
        //会展
        if ($_GET['hz']) {
            $company = $hz = $this->_get('hz');
            $map['hz1|hz2'] = array('like', '%' . $hz . '%');
            $hotels = $fjdb->where($map)->field('hotelid')->select();
            $ids = array();
            foreach ($hotels as $v) {
                $ids[] = $v['hotelid'];
            }
            $data['pchotel_member_hotel.hotelid'] = array('in', array_unique($ids));
        }
        //火车站 
        if ($_GET['hc']) {
            $company = $hc = $this->_get('hc');
            $map['hc1|hc2'] = array('like', '%' . $hc . '%');
            $hotels = $fjdb->where($map)->field('hotelid')->select();
            $ids = array();
            foreach ($hotels as $v) {
                $ids[] = $v['hotelid'];
            }
            $data['pchotel_member_hotel.hotelid'] = array('in', array_unique($ids));
        }
        //地铁 
        if ($_GET['dt']) {
            $company = $dt = $this->_get('dt');
            $map['dt1|dt2|dt3'] = array('like', '%' . $dt . '%');
            $hotels = $fjdb->where($map)->field('hotelid')->select();
            $ids = array();
            foreach ($hotels as $v) {
                $ids[] = $v['hotelid'];
            }
            $data['pchotel_member_hotel.hotelid'] = array('in', array_unique($ids));
        }
        $this->company = $company . '附近酒店';
        $start = strtotime(date('Ymd'));
        $count = $this->hotel->where($data)->count();
        $Page = new Page($count, 10);
        $this->page = $Page->show();
        $data['pchotel_photo.isdefault'] = 1;
        $res = M('member_hotel')->join('pchotel_photo ON pchotel_member_hotel.hotelid = pchotel_photo.hotelid')
                ->field('pchotel_photo.src,pchotel_member_hotel.*')
                ->where($data)
                ->order('pchotel_member_hotel.hotelid desc')
                ->group('pchotel_member_hotel.hotelid')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        $today = date('Ymd');
        foreach ($res as $k => &$v) {
            $v['hcity'] = M('area')->where(array('id' => $area[1]))->getField('name');
            $v['qcity'] = M('area')->where(array('id' => $area[2]))->getField('name');
            //客房
            $hote['hotelid'] = $v['hotelid'];
            $room = M('room')->where($hote)->order('id asc')->field('roomtype,paytype,dingjin,zaocan,fjchuang,swang,thumb,tjarr,menshijia,id')->select();
            foreach ($room as $ke => &$vo) {
                $min = 9999999; //最低价
                $minprice = array(); //酒店房间最低价数组
                $tjarr = explode('|', substr($vo['tjarr'], 0, -1));
                $price = array();
                $price[$today] = '满房';
                $tod = strtotime($today);
                foreach ($tjarr as $key => $val) {
                    $jiage = explode('-', $val);
                    if ($jiage[1] < $min) {
                        $min = $jiage[1];
                    }
                    $vo['yudingjia'] = $jiage[1];
                    if ($tod == $jiage[0]) {
                        $price[$today] = $jiage[1];
                        $vo['yudingjia'] = $jiage[1];
                    }
                }
                if (!in_array('满房', $price)) {
                    $vo['status'] = 1;
                }
                if ($min != 9999999) {
                    $minprice[] = $min;
                }
                $vo['tj'] = $price;
            }
            $min2 = 9999999;
            foreach ($minprice as $m) {
                if ($m < $min2) {
                    $min2 = $m;
                }
            }
            unset($minprice);
            if ($min2 != 9999999) {
                $v['jiage'] = $min2;
            }
            $v['fangjian'] = $room;

            //评分
            $comment = M('comment')->where($hote)->field('unit')->select();
            if ($comment) {
                $c = 0;
                foreach ($comment as $av) {
                    $com = explode('%', $av['unit']);
                    $c+=$com[0];
                }
                $v['comment'] = floor($c / count($comment));
            }
        }
        $this->assign('hotellist', $res);
        $x = M('xingji')->limit(12)->select();
        $this->assign('x', $x);
        $con = M('ads')->where('placeid=23')->order('adsid desc')->find();
        $this->assign('ads', $con);
        //status=1 推荐  sort排序 促销信息
        $cx = $this->article->where("catid=4")->order('sort desc,addtime desc')->field('articleid,title,addtime')->limit(6)->select();
        $this->assign('cuxiao', $cx);
        $this->display();
    }

    /*
      获取酒店所有房间内最高价与最低价 检索使用 $hid 酒店id
     */

    public function getHotelMaxMin($hid, $jiage) {
        $r['hotelid'] = $hid;
        $rs = M('room')->where($r)->field('tjarr,hotelid')->select();
        $hids = false;
        foreach ($rs as $k => $v) {
            $tjarr = explode('|', substr($v['tjarr'], 0, -1));
            foreach ($tjarr as $key => $val) {
                $price = explode('-', $val);
                if ($price[1] >= $jiage[0] && $price[1] <= $jiage[1]) {
                    $hids = $v['hotelid'];
                    return $hids;
                }
            }
        }
        return false;
    }

    public function dxfj() {
        $db = "北京高校附近"; //大学
        $this->assign('db', $db);
        $data = M('city');
        $pro = $data->group('province')->order('cityid asc')->field('province')->limit('1,30')->select();
        $this->assign('pro', $pro);
        $college = M('college');
        foreach ($pro as $v) {
            $dx = $college->where("province='$v[province]'")->select();
            $newdx["$v[province]"] = $dx;
        }
        $bj = $college->where("province='北京'")->select();
        $this->assign('bj', $bj);
        //print_r($newdx);
        $this->assign('newdx', $newdx);
        $this->display();
    }

    public function dtfj() {
        $db = "北京地铁附近"; //地铁
        $this->assign('db', $db);
        $data = M('subway');
        $dtcity = $_GET['dtcity'];
        switch ($dtcity) {
            case "上海":
                $xian = $data->where("city='上海'")->group('ditie')->order('id asc')->field('ditie')->select();

                $this->assign('xian', $xian);
                foreach ($xian as $v) {
                    $dt = $data->where("ditie='$v[ditie]'")->limit(50)->select();
                    $newdt["$v[ditie]"] = $dt;
                }
                $this->assign('newdt', $newdt);
                break;
            case "广州":
                $xian = $data->where("city='广州'")->group('ditie')->order('id asc')->field('ditie')->select();
                $this->assign('xian', $xian);
                foreach ($xian as $v) {
                    $dt = $data->where("ditie='$v[ditie]'")->limit(50)->select();
                    $newdt["$v[ditie]"] = $dt;
                }
                $this->assign('newdt', $newdt);
                break;
            case "深圳":
                $xian = $data->where("city='深圳'")->group('ditie')->order('id asc')->field('ditie')->select();
                $this->assign('xian', $xian);
                foreach ($xian as $v) {
                    $dt = $data->where("ditie='$v[ditie]'")->limit(50)->select();
                    $newdt["$v[ditie]"] = $dt;
                }
                $this->assign('newdt', $newdt);
                break;
            case "天津":
                $xian = $data->where("city='天津'")->group('ditie')->order('id asc')->field('ditie')->select();
                $this->assign('xian', $xian);
                foreach ($xian as $v) {
                    $dt = $data->where("ditie='$v[ditie]'")->limit(50)->select();
                    $newdt["$v[ditie]"] = $dt;
                }
                $this->assign('newdt', $newdt);
                break;
            case "南京":
                $xian = $data->where("city='南京'")->group('ditie')->order('id asc')->field('ditie')->select();
                $this->assign('xian', $xian);
                foreach ($xian as $v) {
                    $dt = $data->where("ditie='$v[ditie]'")->limit(50)->select();
                    $newdt["$v[ditie]"] = $dt;
                }
                $this->assign('newdt', $newdt);
                break;
            case "成都":
                $xian = $data->where("city='成都'")->group('ditie')->order('id asc')->field('ditie')->select();
                foreach ($xian as $v) {
                    $dt = $data->where("ditie='$v[ditie]'")->limit(50)->select();
                    $newdt["$v[ditie]"] = $dt;
                }
                $this->assign('newdt', $newdt);
                break;
            default:
                $xian = $data->where("city='北京'")->group('ditie')->order('id asc')->field('ditie')->select();
                $this->assign('xian', $xian);
                foreach ($xian as $v) {
                    $dt = $data->where("ditie='$v[ditie]'")->limit(50)->select();
                    $newdt["$v[ditie]"] = $dt;
                }
                $this->assign('newdt', $newdt);
        }
        $this->display();
    }

    public function jcfj() {
        $db = "北京机场附近"; //机场
        $this->assign('db', $db);
        $data = M('airport');
        $pro = $data->group('province')->order('id asc')->field('province')->select();
        $this->assign('pro', $pro);
        foreach ($pro as $v) {
            $jc = $data->where("province='$v[province]'")->select();
            $newjc["$v[province]"] = $jc;
        }
        $this->assign('newjc', $newjc);
        $this->display();
    }

    public function hzfj() {
        $db = "北京会展附近"; //会展
        $this->assign('db', $db);
        $data = M('exhibit');
        $pro = $data->group('province')->order('id asc')->field('province')->select();
        $this->assign('pro', $pro);
        foreach ($pro as $v) {
            $hz = $data->where("province='$v[province]'")->select();
            $newhz["$v[province]"] = $hz;
        }
        $this->assign('newhz', $newhz);
        $this->display();
    }

    public function tra() {
        $db = "北京火车站附近"; //火车站
        $this->assign('db', $db);
        $data = M('train');
        $pro = $data->group('province')->order('id asc')->field('province')->select();
        $this->assign('pro', $pro);
        foreach ($pro as $v) {
            $train = $data->where("province='$v[province]'")->select();
            $newtrain["$v[province]"] = $train;
        }
        $this->assign('newtrain', $newtrain);
        $this->display();
    }

    //景区
    public function jing() {
        $map['level'] = 1;
        $map['id'] = array('neq', 100002);
        $newp = M('area')->where($map)->field('id,name')->select();
        $newjd = array();
        foreach ($newp as $v) {
            $cid = $v['id'];
            $con['city'] = array('like', '%' . $cid . '%');
            $jd = $this->hotel->where($con)->field('fjlv1,fjlv2,fjlv3,fjlv4,fjlv5,fjlv6,fjlv7,fjlv8,fjlv9,fjlv10')->select();
            $jd2 = array();
            foreach ($jd as $v) {
                $jd2[] = $v['fjlv1'];
                $jd2[] = $v['fjlv2'];
                $jd2[] = $v['fjlv3'];
                $jd2[] = $v['fjlv4'];
                $jd2[] = $v['fjlv5'];
                $jd2[] = $v['fjlv6'];
                $jd2[] = $v['fjlv7'];
                $jd2[] = $v['fjlv8'];
                $jd2[] = $v['fjlv9'];
                $jd2[] = $v['fjlv10'];
            }
            $jd2 = array_filter($jd2);
            $newjd[$cid] = $jd2;
        }
        $cond['city'] = array('like', '%100002%');
        $ngd = array();
        $gd = $this->hotel->where($cond)->field('fjlv1')->select();
        foreach ($gd as $k => $v) {
            if ($v['fjlv1']) {
                $ngd[]['fjlv1'] = $v['fjlv1'];
            }
        }
        $this->assign('pro', $newp); //省级
        $this->assign('gd', $ngd);
        $this->assign('newjd', $newjd);
        $this->display();
    }

    public function curl_post($url, $post_arr, $referer = '') {
        $post_str = '';
        foreach ($post_arr as $k => $v) {
            $post_str .= $k . '=' . $v . '&';
        }
        $post_str = substr($post_str, 0, - 1);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url); //要访问的地址 即要登录的地址页面	
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        //	curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header_arr );
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_str); // Post提交的数据包
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0); // 使用自动跳转
        //	curl_setopt ( $curl, CURLOPT_COOKIEJAR, $cookie_file ); // 存放Cookie信息的文件名称
        //	curl_setopt ( $curl, CURLOPT_COOKIEFILE, $cookie_file ); // 读取上面所储存的Cookie信息
        curl_setopt($curl, CURLOPT_REFERER, $referer); //设置Referer
        //	curl_setopt ( $curl, CURLOPT_USERAGENT, $_SERVER ['HTTP_USER_AGENT'] ); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1; rv:9.0.1) Gecko/20100101 Firefox/9.0.1"); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        curl_setopt($curl, CURLOPT_HEADER, false); //获取header信息
        $result = curl_exec($curl);
        return $result;
    }

}
