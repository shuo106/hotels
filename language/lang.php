<?php
//语言包
return array(
	'order'=>array(
		'status'=>array(
		//订单状态
			1=>'未确认',
			2=>'已确认',
			3=>'未付款',
			4=>'已付款',
			5=>'已入住',
			7=>'已离店',
			6=>'已取消'
		),
		'acc_status'=>array(
			1=>'未确认',
			2=>'已确认',
			3=>'质疑单',
			4=>'已结算',
		),
		'from'=>array(
		//订单来源
			5=>'网站',
			2=>'微信',
			1=>'手机',
			3=>'APP',

		),
		'color'=>array(
		//订单状态背景色
			1=>'#ff0000',
			2=>'#8e1d23',
			3=>'#00a16b',
			4=>'#006e4b',
			5=>'#ffa500',
			6=>'#9e48f6',
			7=>'#318cf2'
		)
	),
	'rule'=>array(
		'currency'=>array(
			//暂未启用
			1=>'四舍五入保留整数'
		)
	),
	'form'=>array(
	//表单规则 
	),
	'table'=>array(
		//共用字段名称
		'kename'=>'入住客人',
		'linkman'=>'预订客人',
		'telephone'=>'预订电话',
		'rennums'=>'入住人数',
		'nums'=>'预订间数',
		'total'=>'应付金额',
		'telephone2'=>'入住电话'
		,'status' => '状态'
		//表私有字段名称,在模板或控制器中调用,如:fieldName(C('table.id'),'id')返回'提现ID'
		,'tixian' => array(
			 'id' => '提现ID'
			,'txjine' => '提现金额'
			,'txdate' => '申请日期'
			,'txname' => '开户姓名'
			,'handleDate' => '审核日期'
			,'banliDate' => '办理日期'
			,'txhang' => '提现银行'
			,'txhangnum' => '银行账号'
			,'txshenfen' => '身份证号'
			,'txmobile' => '手机号码'
			,'remainAmount' => '账户余额'
		)
		,'point' => array(
			'id' => '积分流水ID' 
		)
	)
);