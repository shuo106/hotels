<?php
class AccountsAction extends BaseAction{
	public function index(){
		$hotelid = session('hotel_id');
		$map['acc_hotelid'] = $hotelid;
		if($_GET['title'] && $_GET['title'] !=''){
			$map['acc_title'] = array('like', '%' . $_GET['title'] . '%');
		}
		import('ORG.Util.Page');
		$count= M("accounts")->where($map)-> order("acc_id desc")->count();
		$this->total=$count;
		$page=new Page($count,8);
		$show=$page->show();
		$list = M("accounts")->where($map)-> order("acc_id desc")->limit($page->firstRow.','.$page->listRows)-> select();
		$this->list = $list;
		$this->assign('page',$show);
		$this->display();				
	}

	//账单下的订单列表
	public function show(){
		$id = $_GET['id'];
		import('ORG.Util.Page');
		$count= M("acc_list")->where("accid=$id")->count();
		$this->total=$count;
		$page=new Page($count,8);
		$show=$page->show();
		$this->list = M("acc_list")->where("accid=$id")->limit($page->firstRow.','.$page->listRows)->select();
		$this->display();	
	}

	//修改订单
	public function show_edit(){
		$id = $_GET['id'];
		$this->info = M("acc_list")->where("id=$id")->find();
		$this->display();	

	}

	public function update_od(){
		$wh['id']= $this->_post('id');
		$data['status'] = $this->_post('status');
		$rs=M('acc_list')->where($wh)->save($data);
		if($rs){
			die(json_encode(array('status'=>1,'msg'=>'操作成功')));
		}else{
			die(json_encode(array('status'=>0,'msg'=>'操作失败')));
		}
	}



	//删除订单
	public function show_del(){
		$id = $_GET['id'];
		$res = M("acc_list")->where("id=$id")->delete();
		if($res){
			die(json_encode(array('status'=>1,'msg'=>'操作成功')));
		}else{
			die(json_encode(array('status'=>0,'msg'=>'操作失败')));
		}
	}
	public function outExcel(){
		import("ORG.excel.PHPExcel");
		$field = array('oid','roomname','yd_name','yd_tel','yd_addtime','yd_start','total','status','yd_from');
		$tableheader=array('订单编号','预订房间','预订姓名','预订电话','预订时间','入住时间','应付金额','订单状态','订单来源');

		$accid = $_GET['id'];
		$info = M("accounts")->where("acc_id=$accid")->find();
		$count = M('acc_list')->where("accid = $accid")->count();
		$data  = M('acc_list')->where("accid = $accid")
				  			  ->field($field)
							  ->order('oid desc')
							  ->select();
		foreach ($data as $k=> $v) {
            if(empty($v['yd_name'])){
                $data[$k]['yd_name'] = "空";
            }
            if(empty($v['roomname'])){
                $data[$k]['roomname'] = "空";
            }
        }
        $newArr = array();
        foreach($data as $key=>$value){
            $newArr[] = $value;
        }
		$excel = new PHPExcel();
        $str='A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';
        $letters = explode(',',$str);
        $length = count($field);
        $letter = array_slice($letters,0,$length);
        $excel->getActiveSheet()->setCellValue('A1', $info['acc_hotelname'].$info['acc_start'].'至'.$info['acc_end'].'账单:订单列表 ( '.$count.'个) (总金额：'.$info['acc_total'].')  导出日期：'.date('Y-m-d',time()));
        $excel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(18)->setBold(true);
        $excel->getActiveSheet()->getStyle( 'A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->getStyle( 'A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //$excel->getActiveSheet()->setCellValue('A2', '导出日期：'.date('Y-m-d',time()));
        $excel->getActiveSheet()->mergeCells('A1:'.end($letter).'1');
        //$excel->getActiveSheet()->mergeCells('A2:'.end($letter).'2');
        $excel->getActiveSheet()->getColumnDimension( 'A')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension( 'B')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension( 'C')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension( 'D')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension( 'E')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension( 'F')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension( 'G')->setWidth(25);
        $excel->getActiveSheet()->getColumnDimension( 'H')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension( 'I')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension( 'J')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension( 'K')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension( 'L')->setWidth(30);
        $excel->getActiveSheet()->getRowDimension(2)->setRowHeight(20);
        $excel->getActiveSheet()->getRowDimension(3)->setRowHeight(30);
        for($i=0;$i<$length;$i++){
            $excel->getActiveSheet()->getStyle( $letter[$i].'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle( $letter[$i].'2')->getFont()->setSize(15)->setBold(true);
        }
        for($i=0;$i<count($tableheader);$i++){
            $excel->getActiveSheet()->setCellValue("$letter[$i]2","$tableheader[$i]");
        }
        for($i = 3 ; $i <= count( $newArr ) + 2 ; $i ++) {
            $j=0;
            $excel->getActiveSheet()->getRowDimension($i)->setRowHeight(30);
            foreach( $newArr[$i - 3] as $k=>$v ) {
                if($v){
                    if($k=='oid'){
                        $tmp=' '.$newArr[$i - 3]['yd_addtime'].$newArr[$i - 3]['oid'];
                        $excel->getActiveSheet()->setCellValue("$letter[$j]$i","$tmp");
                    }elseif($k=='yd_from'){
                        switch ($v) {
                            case 5:
                                $tmp='网站';
                                break;
                            case 1:
                                $tmp='手机';
                                break;
                            case 2:
                                $tmp='微信';
                                break;
                            case 3:
                                $tmp='app';
                                break;
                        }
                    }elseif($k=='status'){
                        $status_arr=array('订单状态','未确定','已确认','未付款','已付款','已入住','已取消','已离店');
                        $tmp=$status_arr[$v];
                    }elseif($k=='yd_addtime'){
                        $tmp=date('Y-m-d',$v);
                    }elseif($k=='yd_start'){
                        $tmp=date('Y-m-d',$v);
                    }else{
                        $tmp=$v;
                    }
                    $excel->getActiveSheet()->setCellValue("$letter[$j]$i","$tmp");
                    $excel->getActiveSheet()->getStyle( $letter[$j].$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $j++;
                }
            }
        }

       $excel->getActiveSheet()->getColumnDimension('B')->setWidth(40); 

         
        $write = new PHPExcel_Writer_Excel5($excel);
        header("Pragma:public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="booking'.date('Y-m-d',time()).'.xls"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
	}
	public function edit(){
		$id = $_GET['id'];
		$this->info = M("accounts")->where("acc_id=$id")->find();
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
			$name = iconv("UTF-8", "gb2312", $smsData['smsname']);
			$pwd = iconv("UTF-8", "gb2312", $smsData['smspwd']);
			
			$con = $cn;
            $str=$this->sendSMS($telephone,$con);
			//$result = $this->curl_post("http://csdk.zzrwkj.com:4002/submitsms.aspx",$postData);
			if($str)
			{header("Content-type:text/html;charset=utf-8");
				echo"发送成功";$this->redirect('index');
			}
			else
			{header("Content-type:text/html;charset=utf-8");
				echo"发送失败，错误码：{$result}";
			}
		}
	public function update(){
		$wh['acc_id']= $this->_post('id');
		$wh['acc_lanid']=session('landlord_id');
		$data['acc_beizhu'] = $this->_post('beizhu');
		$data['acc_status'] = $this->_post('status');
		$rs=M('accounts')->where($wh)->save($data);
		if($rs){
			die(json_encode(array('status'=>1,'msg'=>'操作成功')));
		}else{
			die(json_encode(array('status'=>0,'msg'=>'操作失败')));
		}
	}

	public function  changeNums($roomid,$start,$end,$nums){
     // echo $roomid."-".$start."-".$end."-".$nums."-";  die;  
     $room=M('room')->where('id='.$roomid)->find();
     $tjarr=$room['daydata'];
     $arr = explode('|', substr($tjarr, 0, -1));
     //dump($arr);
     for($i=0;$i<count($arr);$i++){
            $oneday = explode('-', $arr[$i]);
            $onecof=explode('_',$oneday[1]);
      
            if( $oneday[0]>=$start  && $oneday[0]<=$end){
                $arr[$i] = $oneday[0].'-'. $onecof[0]."_".($onecof[1]+$nums);
             }else{
                 // echo $oneday[0]."-";
                $arr[$i] = $oneday[0].'-'. $onecof[0]."_".$onecof[1];   
            }
    
      }
     $data['daydata']=implode('|', $arr);
     //echo $data['tjarr'];
     $room=M('room')->where('id='.$roomid)->save($data);
    }
	public function delete(){
		$map['id']=array('in',$_GET['id']);
		$rs=M('order')->where($map)->delete();	
		if($rs){$this->redirect('index');}
	}
	//取消
	public function doCancel(){
		$data['id']  = $this->_get('id');
		$data['uid'] = session('landlord_id');
		$rs=M('order')->where($data)->setField('status',6);
		if($rs){
			die(json_encode(array('status'=>1,'msg'=>'操作成功')));
		}else{
			die(json_encode(array('status'=>0,'msg'=>'操作失败')));
		}
	}
	//我的点评
	//public function mycomment(){
	//}
	//点评
	public function comment(){
		$data['pchotel_order.id']=$this->_get('id');
		$o=M('order')->where($data)
					->join('pchotel_room ON pchotel_order.rid= pchotel_room.id')
					->field('pchotel_order.id,title,rid,pchotel_order.landlord')
					->find();
		$this->assign('order',$o);
		$this->display();
	}
	//点评保存
	public function doComments(){
		$data=array();
		$data['orderid']=$wh['id']=$this->_post('id');
		$wh['comment']=1;
		$wh['uid']=session('landlord_id');
		$iscom = M('order')->where($wh)->find();
		//判断当前订单是否已经点评过了
		if($iscom){
			die(json_encode(array('status'=>0,'msg'=>'当前订单已经点评过了')));
		}
		$data['rid']		=$this->_post('rid');
		$data['landlord']	=$this->_post('landlord');
		$data['label']		=$this->_post('label');
		$data['title']		=$this->_post('title');
		$data['unit']		=$this->_post('unit');
		$data['content']	=$this->_post('content');
		$data['uid']		=session('landlord_id');
		$data['addtime']	=time();
		$res = M('comment')->add($data);
		if($res){
			unset($wh['comment']);
			//点评成功 修改状态已点评
			M('order')->where($wh)->setField('comment',1);
			die(json_encode(array('status'=>1,'msg'=>'点评成功')));
		}else{
			die(json_encode(array('status'=>0,'msg'=>'点评失败')));
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

	//申请提现
	public function tixian() {
		// $username = $_SESSION['name'];
		$hotelid = session('hotel_id');
		$id = $this->_get('id');
		$map['acc_hotelid'] = intval($hotelid);
		$accounts = M("accounts")->where($map)->field('acc_total')->select();
		// $member = M('member');
		// var_dump($accounts);exit();
		$basic = M('basic');
		$row = $basic->select();
		// $yue = $member->where("username='$username'")->getField('point');
		$this->assign('yue', $accounts[0]['acc_total']);
		$title = "申请提现-" . $row[0]['webname'];
		$this->assign('title', $title);
		$this->assign('keywords', $row[0]['webname']);
		$this->assign('description', $row[0]['description']);
		$this->assign('copyright', $row[0]['copyright']);
		$this->display();
	}
	
	public function tixiantj() {

		$txjine = (int) $_POST['txjine'];
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
			die(json_encode(array('status'=>0,'msg'=>'请输入正确的银行账号')));
		}
		if (empty($_POST['txshenfen'])) {
			die(json_encode(array('status' => 0, 'msg' => '身份证号不能为空')));
		}
		$isIDCard2='/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/';

		if (preg_match($isIDCard2,$_POST['txshenfen']) ==0) {
			die(json_encode(array('status'=>0,'msg'=>'请输入正确的身份证号')));
		}
		if (empty($_POST['txmobile'])) {
			die(json_encode(array('status' => 0, 'msg' => '手机号码不能为空')));
		}
			if (preg_match('/\d{11}/', $_POST['txmobile'])==0) {
				die(json_encode(array('status' => 0, 'msg' => '请输入正确的手机号')));
			}
		$id=session('id');
		$point = M('Member')->where(array('username'=>session('name')))->getField('point');
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
		if($res){
			die(json_encode(array('status' => 1, 'msg' => '申请成功,请等待后台审核')));
		}else{
			die(json_encode(array('status' => 0, 'msg' => '申请失败')));
		}
	}
}