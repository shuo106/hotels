<?php
class EmailAction{
	 /**
      +----------------------------------------------------------
     * 邮件发送类
      +----------------------------------------------------------
     * @static public
      +----------------------------------------------------------
     * $email 对方邮箱地址
     * $name  发送邮件帐号名称
     * $title 邮件标题
     * $content  内容
	 * $url  超链接url
      +----------------------------------------------------------
     * @return void
      +----------------------------------------------------------
     */ 
	public function sendEmail($email,$name='鹏程酒店邮件系统',$title,$content,$url=''){
		require ("Public/Swift-4.3.0/lib/swift_required.php");
		$m = M('email');
		$row = $m->select();
		if($row[0][status]){
			$con = "<a href='{$url}'>$content<br /></a>{$row[0]['content']}";
		}else{
			$con = "<a href='{$url}'>$content<br /></a>";
		}
		
		$transport = Swift_SmtpTransport::newInstance($row[0]['smtp'],$row[0]['port'])
							->setUsername($row[0]['user'])
							->setPassword($row[0]['password']);
		$mailer = Swift_Mailer::newInstance($transport);
		$message = Swift_Message::newInstance()
					->setSubJect($title)
					->setFrom(array($row[0]['user']=>$name))
					->setTo(array($email))
					->setContentType('text/html')
					->setCharset('utf-8')
					->setBody($con);
			return $mailer->send($message);
	}
}
?>