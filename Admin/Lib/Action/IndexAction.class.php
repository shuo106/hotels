<?php
// 本类由系统自动生成，仅供测试用途
class IndexAction extends CommonAction {
    public function index(){
		
    	//基础信息
		$row=M('basic')->find();
    	$this->assign('title',$row['webname']);  
		$this->assign('keywords',$row['keywords']); 	
		$this->assign('description',$row['description']);
		$this->assign('copyright',$row['copyright']);    	
        //新订单
        $where['status']=1;
        $this->newCount=M('order')->where($where)->count();
        //已付款订单
        $where['status']=4;
        $this->payCount=M('order')->where($where)->count();
        //客房数量
        $this->roomCount=M('member_hotel')->where('is_delete!=1')->count();
        //个人会员
        $this->memberCount=M('member')->where('is_delete=0')->count();
        //酒店点评
        $this->commentCount=M('comment')->where('is_delete=0')->count();

        $this-> role=M('role')->where('id='.$_SESSION['role_id'])->getField('name');      	
		$this->display();
    }
    Public function left(){
    	$this->display();
    }
    public function top(){
    	$this->display();
    }
    public function bottom(){
    	$this->display();
    }
	
	//欢迎页面
	public function welcome(){
		//读取授权文件
		$auth= 'Admin/Common/authorize.json';
		if(is_file($auth)){
			$this->isauth=1;
			$json_string = file_get_contents($auth);
			$this->ahthArr= json_decode($json_string ,true);
		}else{
			$this->isauth=0;
		}
		//$json_string = file_get_contents($auth);
		//$ahthArr= json_decode($json_string ,true);
		$map['userid'] = $_SESSION['admin_id'];
		$adminUser = M('admin')->where($map)
				  ->join('pchotel_login_info ON pchotel_admin.userid=pchotel_login_info.id')
				  ->join('pchotel_role_user ON pchotel_admin.userid=pchotel_role_user.user_id')
				  ->join('pchotel_role ON pchotel_role_user.role_id = pchotel_role.id')
				  ->field('pchotel_admin.*,name,loginTime,loginIp')
				  ->find();
		if($adminUser['username'] =='admin'){
			$adminUser['name']='超级管理员';
		}
		//新闻
		$news= M('article')->field('is_delete')->select();
		$newsTg=0;
		$newsDel=0;
		foreach($news as $v){
			if($v['is_delete']==0){
				$newsTg++;
			}else{
				$newsDel++;
			}
		}
		//酒店
		$hotel= M('member_hotel')->field('is_delete')->select();
		$hotelTg=0;
		$hotelDel=0;
		foreach($hotel as $v){
			if($v['is_delete']==0){
				$hotelTg++;
			}else{
				$hotelDel++;
			}
		}
		//客房
		$room= M('room')->field('is_delete')->select();
		$roomTg=0;
		$roomDel=0;
		foreach($room as $v){
			if($v['is_delete']==0){
				$roomTg++;
			}else{
				$roomDel++;
			}
		}
		//会员
		$member= M('member')->field('status')->select();
		$memberTg=0;
		$memberDel=0;
		foreach($member as $v){
			if($v['status']==0){
				$memberTg++;
			}else{
				$memberDel++;
			}
		}
		
		//订单
		$order=M('order')->field('status')->select();
		$orderDqr=0;
		$orderQr=0;
		$orderRz=0;
		$orderDfk=0;
		$orderFk=0;
		$orderQx=0;
		foreach($order as $v){
			if($v['status']==4){
				$orderDqr++;
			}elseif($v['status']==5){
				$orderQr++;
			}elseif($v['status']==1){
				$orderRz++;
			}elseif($v['status']==6){
				$orderDfk++;
			}elseif($v['status']==3){
				$orderFk++;
			}elseif($v['status']==2){
				$orderQx++;
			}
		}
		$this->orderTotal=count($order);
		$this->orderDqr =$orderDqr;
		$this->orderQr=$orderQr;
		$this->orderRz=$orderRz;
		$this->orderDfk=$orderDfk;
		$this->orderFk=$orderFk;
		$this->orderQx=$orderQx;
		$this->assign('user',$adminUser);
		$this->assign('newsTotal',count($news));
		$this->assign('newsTg',$newsTg);
		$this->assign('newsDel',$newsDel);
		$this->assign('hotelTotal',count($hotel));
		$this->assign('hotelTg',$hotelTg);
		$this->assign('hotelDel',$hotelDel);
		$this->assign('roomTotal',count($room));
		$this->assign('roomTg',$roomTg);
		$this->assign('roomDel',$roomDel);
		$this->assign('memberTotal',count($member));
		$this->assign('memberTg',$memberTg);
		$this->assign('memberDel',$memberDel);

		$this->display();
    }
	public function exits(){
		session_destroy();
		session_unset($_SESSION['admin_id']);
		session_unset($_SESSION['admin_name']);
		session_unset($_SESSION['superadmin']);
		session_unset($_SESSION['verify']);
		unset($_COOKIE);
		header("location:".__APP__."/Public/login");
		exit();
	}
	//保存版本号
	public function version(){
		if($_POST['btn_submit']){
			$data['version']=$this->_post('v');
			$data['apk']=$this->_post('apk');
			$data['ios']=$this->_post('ios');
	 		$res=M('basic')->where('id=1')->save($data);
			 if($res){
			 	die(json_encode(array('status'=>1,'msg'=>'操作成功')));
			 }else{
			 	die(json_encode(array('status'=>0,'msg'=>'操作失败')));
			 }
		 }else{
		 	$this->appinfo=M('basic')->where('id=1')->field('version,apk,ios')->find();
			$this->display();
		 }
	}
	public function source(){
        $rs=M("order")->field('from')->select();
        $count['pc']=0;
        $count['wap']=0;
        $count['wechat']=0;
        $count['app']=0;
        $count['other']=0;
        foreach($rs as $v){
            switch ($v['from']) {
                case 5:           
                    $count['pc']+=1;
                    break;
                case 1:          
                    $count['wap']+=1;
                    break;                    
                case 2:           
                    $count['wechat']+=1;
                    break; 
                case 3:           
                    $count['app']+=1;
                    break;  
                case 4:           
                    $count['app']+=1;
                    break;                                     
            }
        }
		$this->order_form_str="'".implode("','",C('order.from'))."'";
		$this->order_form_arr=C('order.from');
        $this->count=$count;
        if($_GET['type']=='loudou') {
        	$this -> display('loudou');
        }else {
        	$this -> display();
        }
	}
	public function loudou(){
        $rs=M("order")->field('from')->select();
        $count['pc']=0;
        $count['wap']=0;
        $count['wechat']=0;
        $count['app']=0;
        $count['other']=0;
        foreach($rs as $v){
            switch ($v['from']) {
                case 5:           
                    $count['pc']+=1;
                    break;
                case 1:          
                    $count['wap']+=1;
                    break;                    
                case 2:           
                    $count['wechat']+=1;
                    break; 
                case 3:           
                    $count['app']+=1;
                    break;  
                case 4:           
                    $count['app']+=1;
                    break;                                     
            }
        }
		$this -> order_form_str = "'".implode("','",C('order.from'))."'";
		$this -> order_form_arr = C('order.from');
        $this -> count = $count;
        $this -> display();
	}
	//地域分布统计
    public function mapdate(){
        $rs=M('member')->field("province,id,regip")->select();
        foreach ($rs as &$m) {
        	if(empty($m['province'])) {
        		$province = $m['province'] = $this -> getProvinceByIp($m['regip']);
        		M('member') -> where("id={$m['id']}") -> setField('province', $province);
        	}
        }
        foreach($rs as $v){
            $list[$v['province']]['name']=$v['province'];
            if(!isset($list[$v['province']]['value'])){
                $list[$v['province']]['value']=0;
            }
            $list[$v['province']]['value']+=1;
        }
        foreach($list as $v){
            if(!empty($v['name'])){
                $count[]=$v;
            }
        }
        $this->data=json_encode($count);
        $this->display();
    }
    //订单状态统计
    public function bardata(){
        static $list;
        !$list && $list = M('order') -> select();
        $thisYear = date('Y');
        $thisMonth = date('n');
        $daysOfThisMonth = date('t');
        $status_arr = C('order.status');
        $status_arr = array_merge(array('总订单'), $status_arr);
        if($_GET['type']=='month') {
            $monthDataArr = array();
            $dayHeaderArr = array();
            for ($s=0; $s < 8; $s++) {
                //按天
                for ($d=0; $d < $daysOfThisMonth; $d++) {
                    $month[$s][$d] = 0;
                }
            }
            for ($d=0; $d < $daysOfThisMonth; $d++) {
                $dayHeaderArr[] = ($d+1).'号';
            }
            foreach ($list as &$v) {
                $thatYear = date('Y',$v['addtime']);

                $thatMonth = date('n',$v['addtime']);
                $thatDay = date('j',$v['addtime']);
                if($thatYear==$thisYear){
                    if($thatMonth == $thisMonth) {
                        $month[0][$thatDay] +=1;
                        $month[$v['status']][$thatDay] +=1;
                    }
                }
            }
            foreach ($month as $k => &$d) {
                $monthDataArr[]=array(
                    'name' => $status_arr[$k]
                    ,'type'=>'line'
                    ,'data'=>$d
                );
            }
            $this -> dayHeader = json_encode($dayHeaderArr);
            $this -> monthDataArr = json_encode($monthDataArr);
        }elseif($_GET['type']=='year') {
            $yearDataArr=array();
            for ($s=0; $s < 8; $s++) {
                //按月
                for ($m=0; $m < 12; $m++) {
                    $year[$s][$m] = 0;
                }
        	}
            foreach ($list as &$v) {
                $thatYear = date('Y',$v['addtime']);
                $thatMonth = date('n',$v['addtime']);
                $thatDay = date('j',$v['addtime']);
                if($thatYear==$thisYear){
                    //总订单
                    $year[0][$thatMonth] += 1;
                    $year[$v['status']][$thatMonth] += 1;
                }
            }
            foreach ($year as $k => &$d) {
                $yearDataArr[]=array(
                    'name' => $status_arr[$k]
                    ,'type'=>'line'
                    ,'data'=>$d
                );
            }
        	$this -> yearDataArr = json_encode($yearDataArr);
        }
    	$this -> themeSelected = json_encode(array(
			 $status_arr[0] => false
			,$status_arr[1] => true
			,$status_arr[2] => true
			,$status_arr[3] => true
			,$status_arr[4] => false
			,$status_arr[5] => false
			,$status_arr[6] => true
			,$status_arr[7] => true
		));
    	foreach ($status_arr as &$ss) {
    		$ss = json_encode($ss);
    	}
    	$this -> theme = implode(',',$status_arr);
    	$this -> display('bardata_'.$_GET['type']);
    }
    private function getProvinceByIp($ip){
    	$res = file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip='.$ip);
    	$info = json_decode($res,1);
    	if($info['province']) {
	    	return $info['province'];
    	}else {
    		return '河南';
    	}
    }    			
}