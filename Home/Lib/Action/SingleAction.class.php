<?php
class SingleAction extends CommonAction{
	public function show(){
		$data=M('single');
		$id=$_GET['id'];		
		$content=$data->where("id=$id")->find();
		$zuo=$data->select();
		$this->leibie=$content['title'];
		$this->assign('zuo',$zuo);
		$this->assign('single',$content);
		$this->display();
	}
}