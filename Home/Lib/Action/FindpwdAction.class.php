<?php
      class FindpwdAction EXTENDS CommonAction{
            /**
             * [index 找回密码视图]
             * @return [type] [description]
             */
            public function index(){
                   $seo=array('keywords'=>'找回密码','description'=>'找回密码','title'=>'找回密码');
                   $this->SEO=$seo;
            	 $this ->display();
            }
            /**
             * 邮件发送页面
             */
            public function sendmail(){
            	  if(!IS_POST){
            	  	   halt('页面不存在');
            	  }
                  $email = $this->_post('mail');
                  $email = $this->injectChk($email);//判断是否有注入漏洞
                  $map['email']=$email;
                  $userinfo=M('member')->where($map)->find();
                  if(!is_array($userinfo)||empty($userinfo)){
                  	       echo 'noreg';//该邮箱没有注册
	                       exit;
                  }
				  $token = md5($userinfo['userid'].$userinfo['username'].$userinfo['password']);
				  $url = 'http://'.$_SERVER['SERVER_NAME'].U('reset',array('email'=>$email,'token'=>$token,'promit'=>md5($userinfo['username'])));
				  $time = date('Y-m-d H:i');
				  $result = $this->dosendmail($time,$email,$url,$userinfo['userid']);
					if($result==1){//邮件发送成功
						$msg = '系统已向您的邮箱发送了一封邮件<br/>请登录到您的邮箱及时重置您的密码！';	
					}else{
						$msg = $result;
					}
					echo $msg;
            } 
			/**
             * 邮件发送页面
             */
            public function sendmail2(){
            	  if(!IS_POST){
            	  	   halt('页面不存在');
            	  }
                  $email = $this->_post('mail');
                  $email = $this->injectChk($email);//判断是否有注入漏洞
                  $map['email']=$email;
                  $userinfo=M('member')->where($map)->find();
                  if(!is_array($userinfo)||empty($userinfo)){
                  	       echo 'noreg';//该邮箱没有注册
	                       exit;
                  }
				  $token = md5($userinfo['userid'].$userinfo['username'].$userinfo['password']);
				  $url = 'http://'.$_SERVER['SERVER_NAME'].U('reset',array('email'=>$email,'token'=>$token,'promit'=>md5($userinfo['username'])));
				  $time = date('Y-m-d H:i');
				  $result = $this->dosendmail($time,$email,$url,$userinfo['userid']);
					if($result==1){//邮件发送成功
						$msg = '系统已向您的邮箱发送了一封邮件<br/>请登录到您的邮箱及时重置您的密码！';	
					}else{
						$msg = $result;
					}
					echo $msg;
            }
            /**
             * [sendmail 邮件发送处理]
             * @param  [type] $time  [发送时间]
             * @param  [type] $email [发送的邮箱]
             * @param  [type] $url   [需要激活处理的url]
             * @return [type]        [返回结果信息]
             */
            public function dosendmail($time,$email,$url,$uid){
				import('Class.smtp','./');
				$smtpserver     = C('smtpserver'); //SMTP服务器
			    $smtpserverport = C('smtpserverport'); //SMTP服务器端口
			    $smtpusermail   = C('smtpusermail'); //SMTP服务器的用户邮箱
			    $smtpuser       = C('smtpuser'); //SMTP服务器的用户帐号
			    $smtppass       = C('smtppass'); //SMTP服务器的用户密码
			    $smtp = new smtp($smtpserver, $smtpserverport, true, $smtpuser,$smtppass); //这里面的一个true是表示使用身份验证,否则不使用身份验证.
			    $emailtype = "HTML"; //信件类型，文本:text；网页：HTML
			    $smtpemailto = $email;
			    $smtpemailfrom = $smtpusermail;
			    $emailsubject ="找回密码";
			    $emailbody = "亲爱的".$email."：<br/>您在".$time."提交了找回密码请求。请点击下面的链接重置密码（按钮24小时内有效）。<br/><a href='".$url."' target='_blank'>".$url."</a><br/>如果以上链接无法点击，请将它复制到你的浏览器地址栏中进入访问。<br/>如果您没有提交找回密码请求，请忽略此邮件。";
			    //更新数据发送时间
			    $rows=M('member')->where(array('userid'=>$uid))->setField('getpasstime',time());
			    if(!$rows){
                        die('非法操作');
			    }
			    $rs = $smtp->sendmail($smtpemailto, $smtpemailfrom, $emailsubject, $emailbody, $emailtype);
				return $rs;
			}
			/**
			 * [reset 密码重置视图]
			 * @return [type] [description]
			 */
			public function reset(){
                   $token = trim($this->_get('token'));
                   $email = trim($this->_get('email'));
                   $promit = trim($this->_get('promit'));
                   if(!$token||!$email){
                        die('页面不存在');
                   }
                   $map['email']=$email;
                   $userinfo=M('member')->where($map)->find();
                   header("Content-Type:text/html;charset=utf8");
                   if(md5($userinfo['username'])!=$promit){
                        die('页面不存在');
                   }
	               if(!is_array($userinfo)||empty($userinfo)){
	                  die('非法操作');
	               }
	             $mt = md5($userinfo['userid'].$userinfo['username'].$userinfo['password']);
			     if($mt==$token){
					if((time()-$userinfo['getpasstime'])>24*60*60){
					  die('该链接已过期！');
					}else{
					  $this->uid=$userinfo['userid'];
					  $this->display();//显示页面视图
					}
			     }else{
					  die('无效的链接<br/>'.$mt.'<br/>'.$token);
			     }
			}
			/**
			 * [doreset 更改新密码]
			 * @return [type] [description]
			 */
			public function doreset(){
                   if(!IS_POST){
                   	   halt('页面不存在');
                   }
                   if(strlen($this->_post('pwd'))<6){
                   	   die(json_encode(array('status'=>0,'msg'=>'长度必须大于6个字符')));
                   }
                   if(!$this->_post('uid')){
                   	   die(json_encode(array('status'=>0,'msg'=>'非法操作')));   
                   }
                   $uid=intval($this->_post('uid'));
                   $up=array();
                   $up['password']   =  md5(trim($this->_post('pwd')));
				   $up['getpasstime']=time();
                   $rows=M('member')->where(array('userid'=>$uid))->save($up);
                   if($rows){
                   	   die(json_encode(array('status'=>1,'msg'=>'密码设置成功')));
                   }else{
                   	   die(json_encode(array('status'=>0,'msg'=>'操作失败')));
                   }
			}
            /**
             * [injectChk 判断邮箱地址 是否有注入漏洞]
             * @return [type] [description]
             */
            public function injectChk($sql_str){
            	$check = @eregi('select|insert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile', $sql_str);
				if ($check) {
					echo('非法字符串');
					exit();
				} else {
					return $sql_str;
				}
            }


      }

?>