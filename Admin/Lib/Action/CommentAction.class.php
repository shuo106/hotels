<?php
class CommentAction extends CommonAction{
	public function index(){
		$this-> username= $username=$this->_get('username');
		if($username){
			$map['pchotel_member.username']=array('like','%'.$username.'%');
		}
		$this-> title=$title=$this->_get('title');
		if($title){
			$map['pchotel_member_hotel.hotelname']=array('like','%'.$title.'%');
		}
		$this-> label=$label=$this->_get('label');
		if($label){
				$map['pchotel_comment.label']=array('like','%'.$label.'%');
			}
		$map['pchotel_comment.is_delete'] = array('neq',1);
		$comment=M('comment');
		$this ->total= $comment->where($map)
		->join('pchotel_member_hotel ON pchotel_comment.hotelid = pchotel_member_hotel.hotelid')
			->join('pchotel_member ON pchotel_comment.uid = pchotel_member.id')
			->count();		
		$this -> pagesize = $pagesize = $this -> _get('pageSize', 'intval', $this -> pagesizes[0]);
		$this -> pageCurrent = $pageCurrent = $this -> _get('pageCurrent', 'intval', 1);
		if ($this -> _get('orderField')) {
			$order = $this -> _get('orderField') . ' ' . $this -> _get('orderDirection') . ',id desc';
		}else{
			$order = 'status asc,id desc';
		}		
		$content=$comment->join('pchotel_member_hotel ON pchotel_comment.hotelid = pchotel_member_hotel.hotelid')
			->join('pchotel_member ON pchotel_comment.uid = pchotel_member.id')
			->field('pchotel_member.username as uname,pchotel_member_hotel.hotelname as hotelName,pchotel_comment.*')
			->where($map)
			-> page($pageCurrent . ',' . $pagesize)
			->order($order)
			->select();
		/*foreach ($content as &$v) {
			$v['username']=M('member')->where('id='.$v['uid'])->getField('username');
		}*/
		$this->assign('comments',$content);
		$this->assign('page',$show);
		$this->display();
	}
	public function edit(){
		$map['id']=$_GET['id'];
		$content=M('comment')->where($map)->find();
		$content['username']=M('member')->where('id='.$content['uid'])->getField('username');
		$content['hotelname']=M('member_hotel')->where('hotelid='.$content['hotelid'])->getField('hotelname');
		$content['thumb']&&$content['thumb']=explode(',',trim($content['thumb'],','));
		$this->assign('comment',$content);
		$this->display();
	}
	public function update(){
		$data['id']=$_POST['id'];
		$data['reply']=$_POST['reply'];
		$res= M('comment')->save($data);
		$res?$this->json(200, '操作成功', array('tabid' => "" . ',Comment-index', 'closeCurrent' => true)):$this->json(300,'没有任何修改，操作失败！');
	}
	//审核
	public function shenhe(){
		$wh['id']=$_GET['id'];
		$order['orderid']=$oid=$_GET['oid'];
		$res= M('comment')->where($wh)->setField('status',2);
		if($res){
			$odata=M('order')->where("pchotel_order.orderid='$oid'")
							 ->find();
			$we['username']=$odata['username'];
			$point =$odata['point'];
			M('member')->where($we)->setInc('point',$point);
             
               
			if($point){

				$data2['username']=$odata['username'];
				$data2['status']=1;
				$data2['foreign_key']=$odata['orderid'];
				$data2['total']=$point;
				//$data2['orderid']=;
				$data2['ctime']=time();
				//$data2['ordertime']=$odata['addtime'];

				//dump($data2);
				M('point')->add($data2);					
			}
			$this->json(1);
		}else{
			$this->json(0);
		}
	}	
	public function delete(){		
		$map['id']=$_GET['id'];
		$rs=M('comment')->where($map)->setField('is_delete',1);
		$rs?$this->json():$this->json(0);
	}
	public function chedidel(){
		$map['id']=$_GET['id'];	
		$rs=M('comment')->where($map)->delete();
		$rs?$this->json():$this->json(0);
	}
	public function huanyuan(){
		$map['id']=$_GET['id'];
		$rs=M('comment')->where($map)->setField('is_delete',0);
		$rs?$this->json():$this->json(0);
	}
	public function huishou(){
		$this-> username= $username=$this->_get('username');
		if($username){
			$map['pchotel_member.username']=array('like','%'.$username.'%');
		}
		$this-> title=$title=$this->_get('title');
		if($title){
			$map['pchotel_member_hotel.hotelname']=array('like','%'.$title.'%');
		}
		$this-> label=$label=$this->_get('label');
		if($label){
				$map['pchotel_comment.label']=array('like','%'.$label.'%');
			}
		$map['pchotel_comment.is_delete'] = 1;
		$comment=M('comment');
		$this ->total= $comment->where($map)
		->join('pchotel_member_hotel ON pchotel_comment.hotelid = pchotel_member_hotel.hotelid')
			->join('pchotel_member ON pchotel_comment.uid = pchotel_member.id')
			->count();		
		$this -> pagesize = $pagesize = $this -> _get('pageSize', 'intval', $this -> pagesizes[0]);
		$this -> pageCurrent = $pageCurrent = $this -> _get('pageCurrent', 'intval', 1);
		if ($this -> _get('orderField')) {
			$order = $this -> _get('orderField') . ' ' . $this -> _get('orderDirection') . ',id desc';
		}else{
			$order = 'status asc,id desc';
		}		
		$content=$comment->join('pchotel_member_hotel ON pchotel_comment.hotelid = pchotel_member_hotel.hotelid')
			->join('pchotel_member ON pchotel_comment.uid = pchotel_member.id')
			->field('pchotel_member.username as uname,pchotel_member_hotel.hotelname as hotelName,pchotel_comment.*')
			->where($map)
			->page($pageCurrent . ',' . $pagesize)
			->order($order)
			->select();
		$this->assign('comments',$content);
		$this->display();
	}
	public function pi(){
		$comment=M('comment');
		$map['id']=array('in',$_GET['ids']);
		$m=M('comment');
		switch ($this->_get('type')) {
			case '1':		//批量放入回收站
				$rs=$m->where($map)->setField('is_delete',1);
				break;
			case '2':		//批量还原
				$rs=$m->where($map)->setField('is_delete',0);
				break;
			case '3':		//批量彻底删除
				$rs=$m->where($map)->delete();
				break;								
		}
		$rs?$this->json():$this->json(0);
	}
}