<?php
class CommentAction extends BaseAction{
	public function index(){
		
		$hotelid=session('hotel_id');
		$com=M('comment');
		import('ORG.Util.Page');
		$this->total=$count=$com->where("hotelid='$hotelid'")->count();
		$page=new Page($count,8);
		$show=$page->show();
		$content=$com->where("hotelid='$hotelid'")->order(" status asc ,id desc")->limit($page->firstRow.','.$page->listRows)->select();
		foreach ($content as &$v) {
			$v['username']=M('member')->where('id='.$v['uid'])->getField('username');
		}

		//dump($content);
		$this->assign('comments',$content);
		$this->assign('page',$show);
		
		$this->display();
	}
	public function edit(){
		$id=$_GET['id'];
		$comment=M('comment');
		$content=$comment->where("id=$id")->find();
		$content['username']=M('member')->where('id='.$content['uid'])->getField('username');
		$content['thumb']&&$content['thumb']=explode(',',trim($content['thumb'],','));
		$this->assign('comment',$content);
				
		$this->display();
	}
	//审核
	public function examine(){
		$map['id']=$this->_get('id');
		$map['hotelid']=session('hotel_id');
		$status=$this->_get('s');
		$res = M('comment')->where($map)->setField('status',$status);
		if($res){
			$this->success('操作成功');
		}else{
			$this->error('操作失败');
		}
	}

	public function update(){
		
		$id=$_GET['id'];
		$comment=M('comment');
		$data=$_POST;
		$data['id']=$id;
		$comment->data($data)->save();
		$this->redirect('index');
		
	}
	public function del(){
		$id=$_GET['id'];
		$comment=M('comment');		
		$rs=$comment->where("id=$id")->delete();
		if($rs){$this->redirect('index');}
	}
	
}