<?php
class HotelAction extends BaseAction{
	public function index(){
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
		$this->display();
	}
	public function lxinfo(){
		$m = M('member_hotel');
		$row = $m->where("username = '{$_SESSION['hotel_name']}'")->find();	
		$this->assign('zhiwu',$row['zhiwu']);
		$this->assign('linkname',$row['linkname']);
		$this->assign('guhua',$row['guhua']);
		$this->assign('telephone',$row['telephone']);
		$this->assign('qq',$row['qq']);
		$this->assign('email',$row['email']);
		$this->assign('chuanzhen',$row['chuanzhen']);		
		$this->display();
	}
	public  function map(){
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
	public function map2(){
		
		$m = M('member_hotel');
		$row = $m->where("username = '{$_SESSION['hotel_name']}'")->find();		
		$hotelid=$row['hotelid'];
		
	
		$this->assign('x',116.404);
		$this->assign('y',39.915);
		$this->assign('hotelname',$row['hotelname']);
		$this->display();
	}
	
	public function biaozhu(){
		$x=$_GET['x'];
		$y=$_GET['y'];
		$map=$x.','.$y;	
		$rs=M('member_hotel')->where('hotelid='.session('hotel_id'))->setField('map',$map);
		   if($rs){
				 die(json_encode(array('status'=>1,'info'=>'操作成功')));    
			}else{
				 die(json_encode(array('status'=>0,'info'=>'操作失败')));    
			}
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

		if($res){
			 die(json_encode(array('status'=>1,'info'=>'操作成功')));
		}else{
			 die(json_encode(array('status'=>0,'info'=>'操作失败')));
		}
			
	}
	/*****************************************房间*********************************************************/
	public function room(){
		$id = intval($_GET['id']);
		if($id){
			$m = M('room');
			$row = $m->where("id = $id AND hotelid = {$_SESSION['hotel_id']} AND is_delete != 1")->select();	
			foreach($row as $v){
				foreach($v as $k=>$vv){
					$this->assign($k,$vv);
				}
			}
		}
		//var_dump($row);exit;

		$this->custom=M('custom')->select();
		$this->display();
	}
	public function adds(){
		$m = M('room');
		// var_dump($_POST);exit();
		$m->content = stripslashes(htmlspecialchars_decode($_POST['content']));
		if($_FILES['thumb1']['name'] != ''){
			//var_dump($_FILES['thumb1']);
			$res = $this->upfile('thumb1');
			//var_dump($res);exit;
			if($res['status'] == 1){
				// $_POST['thumb'] = substr($res['url'],3);
				$_POST['thumb'] = substr($res['url'],1);
			}else{
				$this->error($res['msg']);
			}
		}
		if (empty($_POST['roomtype'])) {
			die(json_encode(array('status'=>0,'info'=>'请设置房间类型')));
		}
		if (empty($_POST['fjchuang'])) {
			die(json_encode(array('status'=>0,'info'=>'请选择床型')));
		}
		if (empty($_POST['paytype'])) {
			die(json_encode(array('status'=>0,'info'=>'请选择支付方式')));
		}
		
		$m->create();
		$m->hotelid = session('hotel_id');
		$m->hotelname = session('hotel_hotelname');
		$id = $_POST['id'];
		if($id){
			$row = $m->where("id = $id")->save();
		}else{
			$row = $m->add();
		}
			if($row){
				 die(json_encode(array('status'=>1,'info'=>'操作成功','data'=>$_POST))); 
			}else{
				 die(json_encode(array('status'=>0,'info'=>'操作失败')));    
			}
	}
	public function roomlist(){
		$m = M('room');
		import("ORG.Util.Page");
		$this->total=$count = $m->where("hotelid = {$_SESSION['hotel_id']} AND is_delete != 1")->count();
		$Page  = new Page($count,10);
		$show  = $Page->show();
		$row = $m->where("hotelid = {$_SESSION['hotel_id']} AND is_delete != 1")->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('row',$row);		
		$this->assign('page',$show);
		$this->display();
	}
	public function huishou(){
		$m = M('room');
		import("ORG.Util.Page");
		$this->total=$count = $m->where("hotelid = {$_SESSION['hotel_id']} AND is_delete = 1")->count();
		$Page  = new Page($count,10);
		$show  = $Page->show();
		$row = $m->where("hotelid = {$_SESSION['hotel_id']} AND is_delete = 1")->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('row',$row);		
		$this->assign('page',$show);
		$this->display();		
	}

	public function huanyuan(){
		$m = M('room');
		$id = intval($_GET['id']);
		$row = $m->where("id = $id")->setField('is_delete',0);
			if($row){
				 die(json_encode(array('status'=>1,'info'=>'操作成功')));    
			}else{
				 die(json_encode(array('status'=>0,'info'=>'操作失败')));    
			}

	}	

	public function roomdel(){
		$m = M('room');
		$id = intval($_GET['id']);
		$row = $m->where("id = $id")->setField('is_delete',1);
			if($row){
				 die(json_encode(array('status'=>1,'info'=>'操作成功')));    
			}else{
				 die(json_encode(array('status'=>0,'info'=>'操作失败')));    
			}

	}

	public function roomdel2(){
		$m = M('room');
		$id = intval($_GET['id']);
		$row = $m->where("id = $id")->delete();
			if($row){
				 die(json_encode(array('status'=>1,'info'=>'操作成功')));    
			}else{
				 die(json_encode(array('status'=>0,'info'=>'操作失败')));    
			}

	}	
		/******2014-04-17*******/
	
	//自定义价格添加
	public function addmoney(){
		$rid=$_GET['id'];
		//$ainfo=M('room')->where('id='.$rid)->setField('tjarr','');
		$ainfo=M('room')->where('id='.$rid)->field('tjarr')->find();
		if($ainfo){
			if($ainfo['tjarr']){
				$daydata=explode('|',substr($ainfo['tjarr'],0,-1));
				foreach($daydata as $v){
					$daydata2=explode('-',$v);
					$daydatas[]=array('day'=>date('Y-m-d',$daydata2[0]).'|'.$daydata2[1]);
				}
				$this->assign("daydatas",json_encode($daydatas));
			}
		}
		
		$this->assign('id',$rid);
		$this->display();
	}

	//特殊价格保存
	public function savePrice(){
		$roo['id']=$this->_post('id');
		$days=M('room')->where($roo)->field('tjarr')->find();
		if($this->_post('starttime') && $this->_post('endtime')){
			$start	=	strtotime($this->_post('starttime')); //开始时间
			$end  	=	strtotime($this->_post('endtime'));	//结束时间
			//$week   =	intval($this->_post('week'));		//周几
			$price  =	intval($this->_post('price'));		//价格

            $roomnum=empty($_POST['roomnum'])?0:$_POST['roomnum'];

			$weekarr=array();
			/*if($week!=7){
				for($i=$start;$i<=$end;$i+=86400){
					if(date('w',$i) == $week){;
                        $num    =
						$k=$i;
						break;
					}
				}
				for($i=$k;$i<=$end;$i+=604800){
					$weekarr[$i]=$price;
				}
			}else{
				for($i=$start;$i<=$end;$i+=86400){
					$weekarr[$i]=$price;
				}
			}*/
			$week   =   $_POST['week']; //星期几？
			if(count($week)!=7){
                        for($i=$start;$i<=$end;$i=$i+86400){
                            if(in_array(date('w',$i),$week)){
                                $weekarr[$i]=$price;
                            }
                        }
                    }else{
                        for($i=$start;$i<=$end;$i=$i+86400){
                            $weekarr[$i]=$price;
                        }
                    }
		}
		$daydata='';//时间 价格字符串
		$newday=array();
		if($days['tjarr']){
			$day=explode('|',substr($days['tjarr'],0,-1));
			foreach($day as $vo){
				$oneday=explode('-',$vo);
				$newday[$oneday[0]]=$oneday[1];
			}
			if($_POST['daymoney']){
				foreach($_POST['daymoney'] as $k=>$v){
					$ks= strtotime($k);
					$newday[$ks]=$v;
				} 
			}
			if($weekarr){
				foreach($weekarr as $k=>$v){
					$newday[$k]=$v.'_'.$roomnum;
				}
			}
		}else{
			if($_POST['daymoney']){
				foreach($_POST['daymoney'] as $k=>$v){
					$ks= strtotime($k);
					$newday[$ks]=$v;
				} 
			}
			if($weekarr){
				foreach($weekarr as $k=>$v){
					$newday[$k]=$v.'_'.$roomnum;
				}
			}
		}
		$min=99999999;
		if(count($newday)>0){
			//sort($newday);
			foreach($newday as $k=>$v){
				if($v!=0 && $k>time()-86400 ){
					$daydata.= $k.'-'.$v.'|';
					if($v<$min){
						$min=$v;
					}
				}
			}
			$data['tjarr']=$daydata;
			$data['yudingjia']=$min;
		}
		
		$res=M('room')->where($roo)->save($data);
		if($res){
			die(json_encode(array('status'=>1,'msg'=>'操作成功')));
		}else{
			die(json_encode(array('status'=>0,'msg'=>'无修改')));
		}
	}


	/*
	//特殊价格保存
	public function savePrice(){
		$roo['id']=$this->_post('id');
		$days=M('room')->where($roo)->field('tjarr')->find();
		if($this->_post('starttime') && $this->_post('endtime')){
			$start	=	strtotime($this->_post('starttime')); //开始时间
			$end  	=	strtotime($this->_post('endtime'));	//结束时间
			//$week   =	intval($this->_post('week'));		//周几
			$week   =   $_POST['week']; //星期几？
			$price  =	intval($this->_post('price'));		//价格
			$weekarr=array();

			if(count($week)!=7){
                        for($i=$start;$i<=$end;$i=$i+86400){
                            if(in_array(date('w',$i),$week)){
                                $weekarr[$i]=$price;
                            }
                        }
                    }else{
                        for($i=$start;$i<=$end;$i=$i+86400){
                            $weekarr[$i]=$price;
                        }
                    }
		}
		$daydata='';//时间 价格字符串
		$newday=array();
		if($days['tjarr']){
			$day=explode('|',substr($days['tjarr'],0,-1));
			foreach($day as $vo){
				$oneday=explode('-',$vo);
				$newday[$oneday[0]]=$oneday[1];
			}
			if($_POST['daymoney']){
				foreach($_POST['daymoney'] as $k=>$v){
					$ks= strtotime($k);
					$newday[$ks]=$v;
				} 
			}
			if($weekarr){
				foreach($weekarr as $k=>$v){
					$newday[$k]=$v;
				}
			}
		}else{
			if($_POST['daymoney']){
				foreach($_POST['daymoney'] as $k=>$v){
					$ks= strtotime($k);
					$newday[$ks]=$v;
				} 
			}
			if($weekarr){
				foreach($weekarr as $k=>$v){
					$newday[$k]=$v;
				}
			}
		}
		$min=99999999;
		if(count($newday)>0){
			//sort($newday);
			foreach($newday as $k=>$v){
				if($v!=0 && $k>time()-86400 ){
					$daydata.= $k.'-'.$v.'|';
					if($v<$min){
						$min=$v;
					}
				}
			}
			$data['tjarr']=$daydata;
			$data['yudingjia']=$min;
		}

		//dump($data);
		$res=M('room')->where($roo)->save($data);
		if($res){
			die(json_encode(array('status'=>1,'msg'=>'操作成功')));
		}else{
			die(json_encode(array('status'=>0,'msg'=>'无修改')));
		}
	}
	*/
	
/*****************************************地标管理*********************************************************/
	public function dibiao(){
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
	public function edit(){
		$m = M('fjdb');
		$m->create();
		if(!empty($_POST['dx_1_1'])){
			if(empty($_POST['dx_1_2'])){
				$this->error('请选择大学名1城市');
			}else{
				if(empty($_POST['dx_1_3'])){
					$this->error('请选择大学名1学校');
				}else{
					$m->dx1 = $_POST['dx_1_3'];
				}
			}
			
		}
		if(!empty($_POST['dx_2_1'])){
			if(empty($_POST['dx_2_2'])){
				$this->error('请选择大学名1城市');
			}else{
				if(empty($_POST['dx_2_3'])){
					$this->error('请选择大学名2学校');
				}else{
					$m->dx2 = $_POST['dx_2_3'];
				}
			}
			
		}
		if(!empty($_POST['dx_3_1'])){
			if(empty($_POST['dx_3_2'])){
				$this->error('请选择大学名1城市');
			}else{
				if(empty($_POST['dx_3_3'])){
					$this->error('请选择大学名3学校');
				}else{
					$m->dx3 = $_POST['dx_3_3'];
				}
			}
			
		}
		if(!empty($_POST['dt_1_1'])){
			if(empty($_POST['dt_1_2'])){
				$this->error('请选择地铁1名称');
			}else{
				if(empty($_POST['dt_1_3'])){
					$this->error('请选择地铁1站牌');
				}else{
					$m->dt1 = $_POST['dt_1_3'];
				}
			}
			
		}
		if(!empty($_POST['dt_2_1'])){
			if(empty($_POST['dt_2_2'])){
				$this->error('请选择地铁2名称');
			}else{
				if(empty($_POST['dt_2_3'])){
					$this->error('请选择地铁2站牌');
				}else{
					$m->dt2 = $_POST['dt_2_3'];
				}
			}
			
		}
		if(!empty($_POST['dt_3_1'])){
			if(empty($_POST['dt_3_2'])){
				$this->error('请选择地铁3名称');
			}else{
				if(empty($_POST['dt_3_3'])){
					$this->error('请选择地铁3站牌');
				}else{
					$m->dt3 = $_POST['dt_3_3'];
				}
			}
			
		}
		if(!empty($_POST['jc_1_1'])){
			if(empty($_POST['jc_1_2'])){
				$this->error('请选择机场1名称');
			}else{
				$m->jc1 = $_POST['jc_1_2'];
			}
		}
		if(!empty($_POST['jc_2_1'])){
			if(empty($_POST['jc_2_2'])){
				$this->error('请选择机场2名称');
			}else{
				$m->jc2 = $_POST['jc_2_2'];
			}
		}
		if(!empty($_POST['hc_1_1'])){
			if(empty($_POST['hc_1_2'])){
				$this->error('请选择火车站1城市');
			}else{
				if(empty($_POST['hc_1_3'])){
					$this->error('请选择火车站1名称');
				}else{
					$m->hc1 = $_POST['hc_1_3'];
				}
			}
			
		}
		if(!empty($_POST['hc_1_1'])){
			if(empty($_POST['hc_1_2'])){
				$this->error('请选择火车站1城市');
			}else{
				if(empty($_POST['hc_1_3'])){
					$this->error('请选择火车站1名称');
				}else{
					$m->hc1 = $_POST['hc_1_3'];
				}
			}
			
		}
		if(!empty($_POST['hc_2_1'])){
			if(empty($_POST['hc_2_2'])){
				$this->error('请选择火车站2城市');
			}else{
				if(empty($_POST['hc_2_3'])){
					$this->error('请选择火车站2名称');
				}else{
					$m->hc2 = $_POST['hc_2_3'];
				}
			}
			
		}
		if(!empty($_POST['hz_1_1'])){
			if(empty($_POST['hz_1_2'])){
				$this->error('请选择会展1城市');
			}else{
				if(empty($_POST['hz_1_3'])){
					$this->error('请选择会展1名称');
				}else{
					$m->hz1 = $_POST['hz_1_3'];
				}
			}
			
		}
		if(!empty($_POST['hz_2_1'])){
			if(empty($_POST['hz_2_2'])){
				$this->error('请选择会展2城市');
			}else{
				if(empty($_POST['hz_2_3'])){
					$this->error('请选择会展2名称');
				}else{
					$m->hz2 = $_POST['hz_2_3'];
				}
			}
			
		}
		$m->hotelid = session('hotel_id');
		$id = intval($_POST['id']);
		if($id){
			$res = $m->where("id = $id")->save();
		}else{
			$res = $m->add();
		}
		if($res){
			$this->success('操作成功');
		}else{
			$this->error('无任何更改');
		}
	}
	public function getprov(){
		$m = M('college');
		if(strstr('北京,上海,天津,重庆',$_POST['city'])){
			$row[0]['cityname']=$_POST['city'];
			echo json_encode(array('status'=>1,'msg'=>$row));
		}else{
			$row = $m->distinct(true)->field('cityname')->where("province  = '{$_POST['city']}'")->select();
			if(is_array($row)){
				echo json_encode(array('status'=>1,'msg'=>$row));
			}else{
				echo json_encode(array('status'=>0,'msg'=>$row));
			}
		}
	}
	public function getcity(){
		$m = M('college');
		if(strstr('北京,上海,天津,重庆',$_POST['city'])){
			$row = $m->field('college')->where("province  = '{$_POST['city']}'")->select();
			if(is_array($row)){
				echo json_encode(array('status'=>1,'msg'=>$row));
			}else{
				echo json_encode(array('status'=>0,'msg'=>$row));
			}
		}else{
			$row = $m->field('college')->where("cityname  = '{$_POST['city']}'")->select();
			if(is_array($row)){
				echo json_encode(array('status'=>1,'msg'=>$row));
			}else{
				echo json_encode(array('status'=>0,'msg'=>$row));
			}
		}
	}
	public function dt(){
		$m = M('subway');
		$row = $m->distinct(true)->field('ditie')->where("city  = '{$_POST['city']}'")->select();
		if(is_array($row)){
			echo json_encode(array('status'=>1,'msg'=>$row));
		}else{
			echo json_encode(array('status'=>0,'msg'=>$row));
		}
	}
	public function dtzhanpai(){
		$m = M('subway');
		$row = $m->field('zhanpai')->where("ditie  = '{$_POST['city']}'")->select();
		if(is_array($row)){
			echo json_encode(array('status'=>1,'msg'=>$row));
		}else{
			echo json_encode(array('status'=>0,'msg'=>$row));
		}
	}
	public function jc(){
		$jc = M('airport');
		$row = $jc->where("province = '{$_POST['city']}'")->field('airport')->select();
		if(is_array($row)){
			echo json_encode(array('status'=>1,'msg'=>$row));
		}else{
			echo json_encode(array('status'=>0,'msg'=>$row));
		}
	}
	public function hccity(){
		$hc = M('train');
		$row = $hc->where("province = '{$_POST['city']}'")->field('cityname')->select();
		if(is_array($row)){
			echo json_encode(array('status'=>1,'msg'=>$row));
		}else{
			echo json_encode(array('status'=>0,'msg'=>$row));
		}
	}
	public function gethc(){
		$hc = M('train');
		$row = $hc->where("cityname = '{$_POST['city']}'")->field('train')->select();
		if(is_array($row)){
			echo json_encode(array('status'=>1,'msg'=>$row));
		}else{
			echo json_encode(array('status'=>0,'msg'=>$row));
		}
	}
	public function hz(){
		$hz = M('exhibit');
		$row = $hz->distinct(true)->where("province = '{$_POST['city']}'")->field('cityname')->select();
		if(is_array($row)){
			echo json_encode(array('status'=>1,'msg'=>$row));
		}else{
			echo json_encode(array('status'=>0,'msg'=>$row));
		}
	}
	public function hzexhibit (){
		$hz = M('exhibit');
		$row = $hz->where("cityname = '{$_POST['city']}'")->field('exhibit ')->select();
		if(is_array($row)){
			echo json_encode(array('status'=>1,'msg'=>$row));
		}else{
			echo json_encode(array('status'=>0,'msg'=>$row));
		}
	}
	/*****************************************上传类*********************************************************/
	public function upfile($fujian){
		$extsion = array('jpg','gif','jpeg','png','bmp');
		$fileSize = 1000*1024;
		$fileName = $fujian;
		if(isset($_FILES[$fileName])){
			if($_FILES[$fileName]['error'] != 0){
				switch($_FILES[$fileName]['error']){
					case 1:
						return array('status'=>0,'url'=>'','msg'=>'超过了php.ini上传的大小');
					break;
					case 2:
						return array('status'=>0,'url'=>'','msg'=>'上传文件大小');
					break;
					case 3:
						return array('status'=>0,'url'=>'','msg'=>'文件只有部分被上传');
					break;
					case 4:
						return array('status'=>0,'url'=>'','msg'=>'图片不能为空');
					break;
					case 6:
						return array('status'=>0,'url'=>'','msg'=>'找不到临时文件夹');
					break;
					case 7:
						return array('status'=>0,'url'=>'','msg'=>'文件写入失败');
					break;
				}
			}
			$ext = explode('.',$_FILES[$fileName]['name']);
			$extInfo = $ext[count($ext)-1];
			if(in_array($extInfo,$extsion)==0){
				return array('status'=>0,'url'=>'','msg'=>'上传错误，本站允许上传'.implode('|',$extsion));
			}
			if($_FILES[$fileName]['size'] > $fileSize){
				return array('status'=>0,'url'=>'','msg'=>'上传失败，本站允许上传大小'.($fileSize/1024).'M');
			}
			$newName = $this->filenewName($extInfo);
			$res = move_uploaded_file($_FILES[$fileName]['tmp_name'],dirname(USER_ROOT).$newName);
			if($res){
				return array('status'=>1,'url'=>$newName,'msg'=>'上传成功');
			}else{
				return array('status'=>0,'url'=>'','msg'=>'移动临时文件出错');
			}
		}
	}	
	public function filenewName($extInfo){
		$str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$text = $_SESSION['hotel_name'].date('His').'-';
		for($i=0;$i<4;$i++){
			$text .= substr($str,mt_rand(0,strlen($str)-1),1);	
		}
		$dir = "/uploads/room/".date('Y-m-d');
		if (!is_dir(dirname(USER_ROOT).$dir)) {
			mkdir(dirname(USER_ROOT).$dir, 0777);
		}
		$new = $dir."/$text".'.'."$extInfo"; //9-27号修改
		//$new = "./uploads/Hotel/"."$text".'.'."$extInfo";
		if(file_exists($new)){
			$this->filenewName($new);	
		}else{
			return $new;	
		}
	}
	public function jguan() {
        $m = M('room');
        $id=intval($_GET['id']);
        $d = intval($_GET['d']);
        
            $row = $m->where("id = $id")->setField('is_delete',$d);
        
        $s=M('room')->getLastSql();
			if($row){
				 die(json_encode(array('status'=>1,'info'=>'操作成功')));    
			}else{
				 die(json_encode(array('status'=>0,'info'=>'操作失败'.$s)));    
			}
    }

}
?>