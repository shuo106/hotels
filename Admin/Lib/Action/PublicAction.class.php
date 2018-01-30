<?php
header("Content-type:text/html;charset=utf-8");
class PublicAction extends Action{
	public function __construct(){
		parent::__construct();
		if(isset($_SESSION['admin_id']) && isset($_SESSION['admin_name'])){
			header("location:".__APP__."/Index/index");
			exit();
		}
	}
	
	public function login(){
		$basic=M('basic');
    	$row=$basic->select();
    	$this->assign('title',$row[0]['webname']);  
		$this->assign('keywords',$row[0]['keywords']); 	
		$this->assign('description',$row[0]['description']);
		$this->assign('copyright',$row[0]['copyright']);
		
		if(isset($_POST['dosubmit'])){
			if(md5($_POST['checkcodestr'])!=$_SESSION['verify']) {
				die(json_encode(array('status'=>0,'msg'=>'验证码错误')));
			}
			$name = $_POST['username'];
			if(empty($_POST['yourname'])){
				if(!empty($_COOKIE['name'])){
					setCookie('name',$name,time()-1,'/');
				}
			}else{
				setCookie('name',$name,time()+604800,'/');
			}
			$m = M('admin');
			$error = M('safety');
			$err = $error->select();
			//限制IP
			if($err[0]['ip']){
				$ip=unserialize($err[0]['ip']);
				if(in_array($_SERVER['REMOTE_ADDR'],$ip)){
					die(json_encode(array('status'=>0,'msg'=>'该ip被禁止登录')));
				}
			}			
			if($err[0]['number'] > 0){
				if($_COOKIE['loginError_number'] >= $err[0]['number']){
					$data['errortime'] = strtotime("+{$err[0]['locktime']} hours");
					$m->where("username = '$name'")->data($data)->save();
					setCookie('loginError_number','',time()-1,'/');
				}
			}
			$row = $m->where("username = '$name'")->limit(1)->select();
			if($err[0]['number'] > 0){
				if($row[0]['errortime'] > time()){
					die(json_encode(array('status'=>0,'msg'=>'今天登录错误次数已达到')));
				}
			}	
			if(!$row){
				die(json_encode(array('status'=>0,'msg'=>'用户名不存在')));
			}
			if($row[0]['password'] != md5($_POST['password'])){
				if(isset($_COOKIE['loginError_number'])){
					setCookie('loginError_number',$_COOKIE['loginError_number']+1,time()+3600,'/');
				}else{
					setCookie('loginError_number',1,time()+3600,'/');
				}
				die(json_encode(array('status'=>0,'msg'=>'用户名密码错误')));
			}
			//登录限制
			if($row[0]['disabled']==1){
				die(json_encode(array('status'=>0,'msg'=>'该帐号已被禁止登录')));
			}				
			$data['number'] = $row[0]['number']+1;
			$data['lasttime'] = time();
			$m->where("username = '$name'")->data($data)->save();
			$loginInfo = M('login_info');
			$data = array();
			$data['id'] = $row[0]['userid'];
			$data['username'] = $name;
			$data['authority'] = $row[0]['grade'];
			$data['loginTime'] = time();
			$data['loginIp'] = $_SERVER['REMOTE_ADDR'];
			$loginInfo->data($data)->add('','',$replace=true);
			$_SESSION['admin_id'] = $row[0]['userid'];
			$_SESSION['admin_name'] = $name;
		   //用来识别超级管理员  如果是超级管理员 那么  不进行判断
			 if(C('RBAC_SUPERADMIN')==$name){
			 	   $_SESSION[C('ADMIN_AUTH_KEY')]=true;//这个是控制rbac执行的标志 为0则执行为1则不进行验证
			 }
		     import('ORG.Util.RBAC');//引入think的RBAC类  
             RBAC::saveAccessList();//执行该类里面的静态方法 将权限写入session中
			die(json_encode(array('status'=>1,'msg'=>'登录成功')));
		}
		$this->display();
	}

	//忘记密码
	public function miss_pwd(){
		$this->display();
	}

	function getcode(){
		$mobile = $_GET['tel'];
		// $mobile = '13938586846';
		$code=rand(100000,999999);
		$str="尊敬的客户您好，您的重置密码的验证码为【".$code."】,30分钟内有效!";
        $res=$this->sms($mobile,$str);
        // var_dump($res);exit;
        if($res==0){
        	//验证码写入session
        	$_SESSION["phone_code"] = $code;        	
        	die(json_encode(array('status' => 1, 'msg' => '获取成功')));
        }else{
        	die(json_encode(array('status' => 0, 'msg' => '获取失败错误代码：'.$res)));
        }	
	}

	//发送短信
    public function sms($mobile,$str){
        if($mobile){
            //基础数据
            Vendor('ChuanglanSmsHelper.ChuanglanSmsApi');
            $SMS = new ChuanglanSmsApi();
            $row=include 'Admin/Conf/message.php';
            $name=iconv("UTF-8","gb2312",$row['smsname']);
            $pwd=iconv("UTF-8","gb2312",$row['smspwd']);
            $c=$str;
            $result=$SMS->sendSMS($name,$pwd,$mobile,$c);
            $strs=$SMS->execResult($result);
            return $strs[1];
        }else{
            return false;
        }
    }

    public function docode(){
    	$tel = $_GET['tel'];
    	$code = $_GET['code'];
    	$codes = $_SESSION['phone_code'];
    	// var_dump($code);
    	// var_dump($codes);exit;
    	if($code == $codes){
    		$this->assign("tel",$tel);
    		$this->display();
    	}else{
    		echo"<script>alert('验证码不正确!');history.go(-1)</script>";
    	}
    } 

    //重置密码
    public function reset_pwd(){
    	$code = $_GET['code'];
    	$tel = $_GET['tel'];
    	$pwd = $_GET['pwd'];
    	$res = M("admin")->where("mobile=$tel")->find();
    	$codes = $_SESSION['phone_code'];
    	if($code !=$codes){
    		echo"<script>alert('验证码不正确!');history.go(-1)</script>";exit;
    	}
    	if($res){
    		$pwds = md5($pwd);
    		$data['password'] = $pwds;
    		$val = M("admin")->where("mobile=$tel")->save($data);
    		if($val){
    			echo"<script>alert('密码重置成功!');history.go(-2)</script>";exit;
    		}else{
    			echo"<script>alert('密码重置失败!');history.go(-1)</script>";exit;
    		}
    	}else{
    		echo"<script>alert('管理员不存在!');history.go(-2)</script>";exit;
    	}
    } 
}