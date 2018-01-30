<?php

class LoginAction extends CommonAction {

    public function index() {
        if (isset($_SESSION['id']) && isset($_SESSION['name'])) {
            header("location:" . __APP__ . "/member/index");
            exit();
        }
        $basic = M('basic');
        $row = $basic->select();
        $title = "个人会员登录-" . $row[0]['webname'];
        $this->assign('title', $title);
        $this->assign('keywords', $row[0]['webname']);
        $this->assign('description', $row[0]['description']);
        $this->assign('copyright', $row[0]['copyright']);
        $this->url=$_SERVER['HTTP_REFERER'];
	//var_dump($_SERVER['HTTP_REFERER']);exit;
        $this->display();
    }

    //个人会员登录
    public function login() {
        $name = $this->_post('username');
        $pass = $this->_post('password');

        $m = M('member');
        $uname['username'] = $name;
        $row = $m->where($uname)->find();
        if (!$row) {
            $uphone['telephone'] = $name;
            $row = $m->where($uphone)->find();
        }
        if ($row) {
            if ($row['status'] == 1) {
                die(json_encode(array('status' => 0, 'msg' => '该帐号已被管理员锁定')));
            }
            if ($row['password'] == md5($pass)) {
                $_SESSION['id'] = $row['id'];
                $_SESSION['name'] = $row['username'];
                
               
                $data['loginip'] = $_SERVER['REMOTE_ADDR'];
                $data['logintime'] = time();
               
                //dump($data);
               // $m->where('username =' . $name)->data($data)->save();
                $m->where("username= '$name'")->data($data)->save();
                die(json_encode(array('status' => 1, 'msg' => '登录成功')));
            } else {
                die(json_encode(array('status' => 0, 'msg' => '密码错误')));
            }
        } else {
            die(json_encode(array('status' => 0, 'msg' => '用户名错误')));
        }
    }

    //获取验证码
    public function getVerify(){
        $mobile=$this->_get('mobile');
        $m=$this->_get('type')==1?M('member'):M('landlord');
        $rs=$m->where(array('telephone'=>$mobile))->count();
        $rs||die(json_encode(array('status'=>0,'msg'=>'该手机号尚未注册')));
        //发送短信
        $code=rand(100000,999999);
        // $code=888888;
        $str="您的验证码为".$code.",30分钟内有效!";
        $res=$this->sms($mobile,$str);
        if($res==0){
            //验证码写入session
            $data[$mobile]=$code;
            $data[$mobile.'-time']=time();
            session("{$mobile}_code",serialize($data));         
            die(json_encode(array('status'=>1,'msg'=>'获取成功')));
        }else{
            die(json_encode(array('status'=>0,'msg'=>'获取失败错误码为:'.$res)));
        }   
    }
    //手机号码登录
    public function logins(){
        $mobile = $this->_post('mobile');
        $code = $this->_post('code');
        if(!$mobile||!$code){
            die(json_encode(array('status'=>0,'msg'=>'手机号和验证码不能为空')));
        }        
        if(!$this->checkCode($mobile,$code)){
            die(json_encode(array('status'=>0,'msg'=>'验证码错误')));
        }
        $rs=M('member')->where(array('telephone'=>$mobile))->find();
        if($rs){
            $_SESSION['id'] = $rs['id'];
            $_SESSION['name'] = $rs['username'];
            $data['lastlogintime']= $row['logintime'];
            $data['lastloginip']=$row['loginip'];
            $data['loginip'] = $_SERVER['REMOTE_ADDR'];
            $data['logintime'] = time();
            M('member')->where(array('telephone'=>$mobile))->data($data)->save();           
            die(json_encode(array('status'=>1,'msg'=>'登录成功')));
        }else{
            die(json_encode(array('status'=>0,'msg'=>'登录失败')));
        }
    }    
    //验证码检校
    public function checkCode($mobile,$code){
        $getCode=unserialize($_SESSION["{$mobile}_code"]);
        if($code){
            if($getCode[(string)$mobile]!=$code){
                return false;
            }else{
                return true;
            }
        }else{
            return false;
        }
    }       

    //找回密码
    public function find() {
        if (isset($_SESSION['id']) && isset($_SESSION['name'])) {
            header("location:" . __ROOT__ . "/member/index.html");
            exit();
        }
        $basic = M('basic');
        $row = $basic->select();
        $title = "会员密码找回-" . $row[0]['webname'];
        $this->assign('title', $title);
        $this->assign('keywords', $row[0]['webname']);
        $this->assign('description', $row[0]['description']);
        $this->assign('copyright', $row[0]['copyright']);
        $this->display();
    }

    //找回密码
    public function findpwd(){
        $m=M('member');
        $mobile = $this->_post('mobile');
        $code = $this->_post('code');
        if(!$this->checkCode($mobile,$code)){
            die(json_encode(array('status'=>0,'msg'=>'验证码错误')));
        }
        $rs=$m->where(array('telephone'=>$mobile))->setField('password',md5($this->_post('password')));
        if($rs){        
            die(json_encode(array('status'=>1,'msg'=>'设置密码成功')));
        }else{
            die(json_encode(array('status'=>0,'msg'=>'设置密码失败')));
        }       
    }    

    public function sendEmail($email, $content) {
        require ("Public/PHPMailer_v5.1/class.phpmailer.php");
        require ("Public/PHPMailer_v5.1/class.smtp.php");
        $title = '缘中源酒店邮件系统密码找回';

        $mail = new PHPMailer();

        $mail->CharSet = "UTF-8"; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置为 UTF-8 
        $mail->IsSMTP(); // 设定使用SMTP服务 
        $mail->SMTPAuth = true; // 启用 SMTP 验证功能 
        $mail->SMTPSecure = "ssl"; // SMTP 安全协议 
        $mail->Host = "smtp.163.com"; // SMTP 服务器 
        $mail->Port = 465; // SMTP服务器的端口号 
        $mail->Username = "travel169@163.com"; // SMTP服务器用户名 
        $mail->Password = "pc761288"; // SMTP服务器密码 
        $mail->SetFrom('travel169@163.com', '缘中源酒店邮件系统'); // 设置发件人地址和名称 
        $mail->AddReplyTo(" ", " ");
        // 设置邮件回复人地址和名称 
        $mail->Subject = '缘中源酒店系统密码找回'; // 设置邮件标题 
        $mail->AltBody = "为了查看该邮件，请切换到支持 HTML 的邮件客户端";
        // 可选项，向下兼容考虑 
        $mail->MsgHTML($content); // 设置邮件内容 
        $mail->AddAddress($email, $email);
        if (!$mail->Send()) {
            $out = '发送失败，请重新操作';
        } else {
            $out = '发送成功，请注意查收';
        }

        return $out;
    }

    //找回密码判断 用户名跟邮箱是否正确
    function do_find() {
        $tj['email'] = $_GET['email'];
        $tj['username'] = $_GET['user'];
        $userinfo = M('member')->where($tj)->field('password')->find();
        if ($userinfo) {
            $mm = time();
            $con = '您的新密码：' . $mm;
            $user['username'] = $_GET['user'];
            $pass['password'] = md5($mm);
            M('member')->where($user)->save($pass);

            // 发送邮件   
            echo $this->sendEmail($_GET['email'], $con);
        } else {
            $u['username'] = $_GET['user'];
            $e = M('member')->where($u)->field('userid')->find();
            if (!$e) {
                $out = '无此用户名';
                $i = 1;
            }
            if ($i != 1) {
                $em['email'] = $_GET['email'];
                $e = M('member')->where($em)->field('userid')->find();
                if (!$e) {
                    $out = '邮箱错误';
                }
            }
            echo $out;
        }
    }

    //商家登录
    public function hotel(){
        if($this->isGet()){
            if(session('?hotel_name')){
                redirect(__ROOT__.'/Hotel/');
            }
            $this->display();
        }else{
            $m = M('member_hotel');
            $row = $m->field('hotelid,password,hotelname,status')->where("username = '{$this->_post('username')}'")->select();
            if(!$row){
                die(json_encode(array('status'=>0,'msg'=>'用户名错误')));
            }
            if($row[0]['password'] != md5($this->_post('password'))){
                die(json_encode(array('status'=>0,'msg'=>'密码不正确')));
            }            
            if($row[0]['status'] == 1){
                die(json_encode(array('status'=>0,'msg'=>'该帐号已被管理员锁定')));
            }
            session('hotel_name',$_POST['username']);
            session('hotel_id',$row[0]['hotelid']);
            session('hotel_hotelname',$row[0]['hotelname']);
            die(json_encode(array('status'=>1,'msg'=>'登录成功')));
        }
    }
    //商家手机号码登录
    public function hotels(){
        $mobile = $this->_post('mobile');
        $code = $this->_post('code');
        if(!$mobile||!$code){
            die(json_encode(array('status'=>0,'msg'=>'手机号和验证码不能为空')));
        }        
        if(!$this->checkCode($mobile,$code)){
            die(json_encode(array('status'=>0,'msg'=>'验证码错误')));
        }
        $rs=M('member_hotel')->where(array('telephone'=>$mobile))->find();
        if($rs){
            session('hotel_name',$rs['username']);
            session('hotel_id',$rs['hotelid']);
            session('hotel_hotelname',$rs['hotelname']);
            $data['lastlogintime']= $rs['logintime'];
            $data['lastloginip']=$rs['loginip'];
            $data['loginip'] = $_SERVER['REMOTE_ADDR'];
            $data['logintime'] = time();
            M('member_hotel')->where(array('telephone'=>$mobile))->data($data)->save();         
            die(json_encode(array('status'=>1,'msg'=>'登录成功')));
        }else{
            die(json_encode(array('status'=>0,'msg'=>'登录失败')));
        }
    }    

    //安全退出
    public function loginOut() {
        session_unset();
        session_destroy();
        header("location:" . __ROOT__ . "/login.html");
    }

}
?>