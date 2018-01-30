<?php
class MemberModel extends Model{
	protected $_validate=array(
		array('username','','用户名已存在',1,'unique',1),
		array('username','/^[A-z]{1}\w{5,19}$/','用户名不正确'),
		array('password','/\w{6,20}$/','密码不合法'),
		array('pwdconfirm','password','确认密码不正确',0,'confirm'),
		array('email','email','邮箱不合法'),
		array('mobile','/^\d{11}$/','手机号不正确')
	
	);
	
}