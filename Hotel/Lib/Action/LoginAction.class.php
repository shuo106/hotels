<?php
class LoginAction extends Action{
	public function __construct(){
		parent::__construct();
		if(session('?hotel_name')){
			$this->redirect('Index/index');
		}
	}
    public function index(){
		$basic=M('basic');
		$row=$basic->select();		
		$title="酒店会员登录-".$row[0]['webname'];
		$this->assign('title',$title);  
		$this->assign('keywords',$row[0]['webname']); 	
		$this->assign('description',$row[0]['description']);
		$this->assign('copyright',$row[0]['copyright']);
		$this->display();
    }
	public function login(){
		if($_SESSION['verify'] != md5($_POST['verify'])) {
			$this->error('验证码错误！');
		}
		$m = M('member_hotel');
		$row = $m->field('hotelid,password,hotelname,status')->where("username = '{$this->_post('username')}'")->select();
		if(!$row){
			$this->error('用户名错误');
		}
		if($row[0]['status'] == 1){
			$this->error('该帐号已被管理员锁定');
		}
		if($row[0]['password'] != md5($this->_post('password'))){
			$this->error('密码不正确');
		}
		$data['logintime']=time();
		$data['loginip']=$_SERVER['REMOTE_ADDR'];
		$m->where("username='{$this->_post('username')}'")->data($data)->save();
		session('hotel_name',$_POST['username']);
		session('hotel_id',$row[0]['hotelid']);
		session('hotel_hotelname',$row[0]['hotelname']);
		$this->success('登录成功','../Index/index');
	}
	public function verify(){
		import("ORG.Util.Image");
		Image::buildImageVerify();
	}

}