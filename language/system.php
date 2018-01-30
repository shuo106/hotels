<?php

//语言包

 $sys= array(

	   'sys'=>array(

		    //订单部分

			'ordercontent'=>array(		

			'ordernumber'=>'订单编号',  // {$ORDERC['ordernumber']}

			'src'=>'公寓照片',          // {$ORDERC['src']}

			'hotel'=>'预订酒店',        // {$ORDERC['hotelname']}

			'title'=>'预订房间',        // {$ORDERC['roomtype']}

			'nums'=>'预订间数',         // {$ORDERC['nums']}

			'rennums'=>'入住人数',      // {$ORDERC['rennums']}

			'total'=>'应付金额',        // {$ORDERC['shoufei']}

			'point'=>'赠送积分',        // {$ORDERC['point']}

			'from'=>'订单来源',         // {$ORDERC['from']}

			'linkman'=>'预订姓名',      // {$ORDERC['linkman']}

			'telephone'=>'预订电话',    // {$ORDERC['telephone']}

			'kename'=>'入住姓名',       // {$ORDERC['kename']}

			'telephone2'=>'入住电话',	// {$ORDERC['telephone2']}	

			'addtime'=>'预订时间',      // {$ORDERC['addtime']}

			'start'=>'入住时间',        // {$ORDERC['ruzhudate']}

			'end'=>'离店时间',          // {$ORDERC['lidiandate']}

			'remark'=>'其他要求',       // {$ORDERC['beizhu']}

			'status' => '订单状态',     // {$ORDERC['status']}

			'orderlog' => '操作说明'    // {$ORDERC['orderlog']}

			),

		

			//个人会员部分

			'member' => array(

			'id' => '会员ID',			//{$MEMBERC['id']}

			'username' => '会员账号', 	//{$MEMBERC['username']}

			'point' => '会员积分',		//{$MEMBERC['point']}

			'icon' => '会员头像',		//{$MEMBERC['icon']}

			'truename' => '真实姓名',	//{$MEMBERC['truename']}

			'qq' => '会员QQ',			//{$MEMBERC['qq']}

			'address' => '会员地址',	//{$MEMBERC['address']}

			'telephone' => '会员手机',	//{$MEMBERC['telephone']}

			'email' => '会员邮箱'		//{$MEMBERC['email']}

			),



			//酒店会员部分

			'landlord' => array(

			'id' => '酒店ID',			//{$LANDLORD['id']}

			'username' => '酒店账号',	//{$LANDLORD['username']}

			'truename' => '酒店姓名',	//{$LANDLORD['truename']}

			'qq' => '酒店QQ',			//{$LANDLORD['qq']}

			'address' => '酒店地址',	//{$LANDLORD['address']}

			'telephone' => '酒店手机',	//{$LANDLORD['telephone']}

			'email' => '酒店邮箱'		//{$LANDLORD['email']}

			),



			//点评部分

			'comment' => array(          

			'title' => '入住客房',       // {$COMMENTC['title']}

			'username' => '点评客人',    // {$COMMENTC['username']}

			'content' => '点评内容',     // {$COMMENTC['content']}

			'reply' => '房东回复',       // {$COMMENTC['reply']}

			'label' => '评价等级',       // {$COMMENTC['label']}

			'addtime' => '点评时间'      // {$COMMENTC['addtime]}

			)

			

		)

		

);



return $sys;





?>