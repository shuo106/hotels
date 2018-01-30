<?php
class MapAction extends CommonAction{

	//获取房间 时间段内价格
	public function getRoomDate($tjarr,$start,$end){
		if(!$start){
			$start=strtotime(date('Y-m-d'));
			$end  =$start+86400;
		}
		//房间价格 
		$marray=array();
		for($i=$start;$i<$end;$i+=86400){
			$marray[$i]='满房';
		}
		$daydata=explode('|',substr($tjarr,0,-1));
		$min=9999999;
		foreach($marray as $k2=>&$v2){
			foreach($daydata as $vo){
				$oneday=explode('-',$vo);
				if($oneday[0]==$k2){
					if($oneday[1]<$min){
						$min=$oneday[1];
					}
					$v2='¥'.$oneday[1];
				}
			}
		}
		$price=array();
		$price['tj']=$marray;
		if($min==9999999){
			$min=0;
		}
		$price['min']=$min;
		$tjstr='s';
		foreach($marray as $v){
			$tjstr.=$v;
		}
		if(strpos($tjstr,'满房')){
			$price['status']=0;
		}else{
			$price['status']=1;
		}
		return $price;
	}
	//受欢迎
	public function index(){
		$hotel=M('member_hotel');//酒店表
		$area =M('area');//区域表
		import("@.ORG.Util.Page"); //引用分页类	
		$map['id']=$city=$this->_get('city') ? $this->_get('city'): 100020;
		$this->city=$area->where($map)->getField('name');//当前城市
		$data=array();
		$data['is_delete']=0;
		$data['city']=array('like','%'.$city.'%');
		$keyword=$this->_get('keyword');
		if($keyword){
			$data['hotelname']=array('like','%'.$keyword.'%');
		}
		$count =$hotel->where($data)->count();
		$Page  = new Page($count,7);
		$this->page  = $Page->show();
		$hotels = $hotel->field('map,hotelid,hotelname,telephone,address')
					->where($data)
					->order('hotelid desc')
					->limit($Page->firstRow.','.$Page->listRows)
					->select();
		$points =array();
		foreach($hotels as &$v){
			//客房
			$hote['hotelid']=$v['hotelid'];
			$room = M('room')->where($hote)->order('yudingjia asc')->field('roomtype,yudingjia as minprice,id')->select();
			$v['price']=$room[0]['minprice'];
			$v['rooms']=$room;
			//图片
			$v['photo']=M('photo')->where($hote)->order('isdefault desc')->field('src')->select();
			$v['src']=$v['photo'][0]['src'];
			//标注数组
			$points[] =array('title'=>$v['hotelname'],'addr'=>$v['address'],'point'=>$v['map'],'id'=>$v['hotelid'],'src'=>$v['src'],'rooms'=>$room);
			//评分
			$comment=M('comment')->where($hote)->field('unit')->select();
			if($comment){
				$c=0;
				foreach($comment as $av){
					$com=explode('%',$av['unit']);
					$c+=$com[0];
				}
				$v['comment']=floor($c/count($comment));
				$v['comCount']= M('comment')->where($hote)->count();
			}
		}
		$this->assign('points',json_encode($points));
		$this->hotels=$hotels;
		$this->display();
	}
	//等级
	public function grade(){
		$hotel=M('member_hotel');//酒店表
		$area =M('area');//区域表
		import("@.ORG.Util.Page"); //引用分页类	
		$map['id']=$city=$this->_get('city') ? $this->_get('city'): 100020;
		$this->city=$area->where($map)->getField('name');//当前城市
		$data=array();
		$data['is_delete']=0;
		$data['city']=array('like','%'.$city.'%');
		$keyword=$this->_get('keyword');
		if($keyword){
			$data['hotelname']=array('like','%'.$keyword.'%');
		}
		$count =$hotel->where($data)->count();
		$Page  = new Page($count,7);
		$this->page  = $Page->show();
		$hotels = $hotel->field('map,hotelid,hotelname,telephone,address')
					->where($data)
					->order('xingji desc')
					->limit($Page->firstRow.','.$Page->listRows)
					->select();
		$points =array();
		foreach($hotels as &$v){
			//客房
			$hote['hotelid']=$v['hotelid'];
			$room = M('room')->where($hote)->order('yudingjia asc')->field('roomtype,yudingjia as minprice,id')->select();
			$v['price']=$room[0]['minprice'];
			$v['rooms']=$room;
			//图片
			$v['photo']=M('photo')->where($hote)->order('isdefault desc')->field('src')->select();
			$v['src']=$v['photo'][0]['src'];
			//标注数组
			$points[] =array('title'=>$v['hotelname'],'addr'=>$v['address'],'point'=>$v['map'],'id'=>$v['hotelid'],'src'=>$v['src'],'rooms'=>$room);
			//评分
			$comment=M('comment')->where($hote)->field('unit')->select();
			if($comment){
				$c=0;
				foreach($comment as $av){
					$com=explode('%',$av['unit']);
					$c+=$com[0];
				}
				$v['comment']=floor($c/count($comment));
				$v['comCount']= M('comment')->where($hote)->count();
			}
		}
		$this->assign('points',json_encode($points));
		$this->hotels=$hotels;
		$this->display();
	}
	//价格
	public function price(){
		$hotel=M('member_hotel');//酒店表
		$area =M('area');//区域表
		import("@.ORG.Util.Page"); //引用分页类	
		$map['id']=$city=$this->_get('city') ? $this->_get('city'): 100020;
		$this->city=$area->where($map)->getField('name');//当前城市
		$data=array();
		$data['pchotel_member_hotel.is_delete']=0;
		$data['city']=array('like','%'.$city.'%');
		$keyword=$this->_get('keyword');
		if($keyword){
			$data['pchotel_member_hotel.hotelname']=array('like','%'.$keyword.'%');
		}
		$count =$hotel->where($data)->count();
		$Page  = new Page($count,7);
		$this->page  = $Page->show();
		$hotels = $hotel->field('MIN(yudingjia) as min,map,pchotel_member_hotel.hotelid,pchotel_member_hotel.hotelname,telephone,address')
					->join('pchotel_room ON pchotel_member_hotel.hotelid=pchotel_room.hotelid')
					->where($data)
					->order('min asc')
					->group('pchotel_member_hotel.hotelid')
					->limit($Page->firstRow.','.$Page->listRows)
					->select();
		$points =array();
		foreach($hotels as &$v){
			//客房
			$hote['hotelid']=$v['hotelid'];
			$room = M('room')->where($hote)->order('yudingjia asc')->field('roomtype,yudingjia as minprice,id')->select();
			$v['price']=$room[0]['minprice'];
			$v['rooms']=$room;
			//图片
			$v['photo']=M('photo')->where($hote)->order('isdefault desc')->field('src')->select();
			$v['src']=$v['photo'][0]['src'];
			//标注数组
			$points[] =array('title'=>$v['hotelname'],'addr'=>$v['address'],'point'=>$v['map'],'id'=>$v['hotelid'],'src'=>$v['src'],'rooms'=>$room);
			//评分
			$comment=M('comment')->where($hote)->field('unit')->select();
			if($comment){
				$c=0;
				foreach($comment as $av){
					$com=explode('%',$av['unit']);
					$c+=$com[0];
				}
				$v['comment']=floor($c/count($comment));
				$v['comCount']= M('comment')->where($hote)->count();
			}
		}
		$this->assign('points',json_encode($points));
		$this->hotels=$hotels;
		$this->display();
	}
	//点评分数
	public function score(){
		$hotel=M('member_hotel');//酒店表
		$area =M('area');//区域表
		import("@.ORG.Util.Page"); //引用分页类	
		$map['id']=$city=$this->_get('city') ? $this->_get('city'): 100020;
		$this->city=$area->where($map)->getField('name');//当前城市
		$data=array();
		$data['pchotel_member_hotel.is_delete']=0;
		$data['city']=array('like','%'.$city.'%');
		$keyword=$this->_get('keyword');
		if($keyword){
			$data['pchotel_member_hotel.hotelname']=array('like','%'.$keyword.'%');
		}
		$count =$hotel->where($data)->count();
		$Page  = new Page($count,7);
		$this->page  = $Page->show();
		$hotels = $hotel->field("AVG(TRIM('%' FROM unit)) as score,map,pchotel_member_hotel.hotelid,pchotel_member_hotel.hotelname,pchotel_member_hotel.telephone,address")
					->join('pchotel_comment ON pchotel_member_hotel.hotelid=pchotel_comment.hotelid')
					->where($data)
					->order('score desc')
					->group('pchotel_member_hotel.hotelid')
					->limit($Page->firstRow.','.$Page->listRows)
					->select();
		$points =array();
		foreach($hotels as &$v){
			//客房
			$hote['hotelid']=$v['hotelid'];
			$room = M('room')->where($hote)->order('yudingjia asc')->field('roomtype,yudingjia as minprice,id')->select();
			$v['price']=$room[0]['minprice'];
			$v['rooms']=$room;
			//图片
			$v['photo']=M('photo')->where($hote)->order('isdefault desc')->field('src')->select();
			$v['src']=$v['photo'][0]['src'];
			//标注数组
			$points[] =array('title'=>$v['hotelname'],'addr'=>$v['address'],'point'=>$v['map'],'id'=>$v['hotelid'],'src'=>$v['src'],'rooms'=>$room);
			//评分
			$comment=M('comment')->where($hote)->field('unit')->select();
			if($comment){
				$c=0;
				foreach($comment as $av){
					$com=explode('%',$av['unit']);
					$c+=$com[0];
				}
				$v['comment']=floor($c/count($comment));
				$v['comCount']= M('comment')->where($hote)->count();
			}
		}
		$this->assign('points',json_encode($points));
		$this->hotels=$hotels;
		$this->display();
	}
	public function index2(){
		$hotel=M('member_hotel');//酒店表
		import("@.ORG.Util.Page"); //引用分页类	
		$city=$this->_get('city');
		$data=array();
		$data['is_delete']=0;
		$data['city']=array('like','%'.$city.'%');
		$count =$hotel->where($data)->count();
		$Page  = new Page($count,10);
		$this->page  = $Page->show();
		$hotels = $hotel->field('map,hotelid,hotelname,telephone,address')
					->where($data)
					->order('hotelid desc')
					->limit($Page->firstRow.','.$Page->listRows)
					->select();
					
		$points =array();
		foreach($hotels as &$v){
			//客房
			$hote['hotelid']=$v['hotelid'];
			$room = M('room')->where($hote)->order('id asc')->field('roomtype,tjarr,id')->select();
			$todayStart= strtotime(date('Y-m-d 0:00:01'));
			$min=9999999;
			foreach($room as $k=>&$vo){
				$minprice=array(); //酒店房间最低价数组
				$tjarr=explode('|',substr($vo['tjarr'],0,-1));
				foreach($tjarr as $val){
					$jiage = explode('-',$val);
					if($jiage[0]>$todayStart){
						$vo['minprice'] = $jiage[1];
						if($vo['minprice']<$min){
							$min=$vo['minprice'];
						}
						unset($room[$k]['tjarr']);
						break;
					}
				}
			}
			if($min!=9999999){
				$v['price']=$min;
			}
			$v['rooms']=$room;
			//图片
			$v['photo']=M('photo')->where($hote)->order('isdefault desc')->field('src')->select();
			$v['src']=$v['photo'][0]['src'];
			//标注数组
			$points[] =array('title'=>$v['hotelname'],'addr'=>$v['address'],'point'=>$v['map'],'id'=>$v['hotelid'],'src'=>$v['src'],'rooms'=>$room);
			//评分
			$comment=M('comment')->where($hote)->field('unit')->select();
			if($comment){
				$c=0;
				foreach($comment as $av){
					$com=explode('%',$av['unit']);
					$c+=$com[0];
				}
				$v['comment']=floor($c/count($comment));
				$v['comCount']= M('comment')->where($hote)->count();
			}
		}
		$this->assign('points',json_encode($points));
		$this->hotels=$hotels;
		$this->display();
	}
	/*
		获取酒店所有房间内最高价与最低价 检索使用 $hid 酒店id
	*/
	public function getHotelMaxMin($hid,$jiage){
		$r['hotelid']=$hid;
		$rs= M('room')->where($r)->field('tjarr,hotelid')->select();
		$hids= false;
		foreach($rs as $k=>$v){
			//$tjarr = unserialize ($v['tjarr']);
			$tjarr=explode('|',substr($v['tjarr'],0,-1));
			foreach($tjarr as $key=>$val){
				$price = explode('-',$val);
				if($price[1]>= $jiage[0] && $price[1]<=$jiage[1]){
					$hids = $v['hotelid'];
				}
			}
		}
		return $hids; 
	}
}