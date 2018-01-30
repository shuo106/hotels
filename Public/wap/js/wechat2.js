$(function(){
	//获取地理位置
	$(".getlocation").click(function(){
		var geo = new jQuery.AMUI.Geolocation({
		enableHighAccuracy: true,
		timeout: 5000,
		maximumAge: 60000
		});
		geo.get().then(function(position){
			var url=root+"/wechat/location/lat/"+position.coords.latitude+"/lng/"+position.coords.longitude;
			$.get(url,function(rs){
				if(rs.status==0){
					var city=rs.result.addressComponent.city;
					city=city.substring(0,city.length-1);
					window.location.href=root+"/wechat/hotels/city/"+city;
				}else{
					alert('获取地理位置信息失败');
				}
			},'json')
		},function(err){
			alert('您的设备不支持获取地理位置或您拒绝了位置请求');
		});	
	});
	
	//登录
	$("#login").click(function(){
		var $login=$(".am-tabs-bd .am-tab-panel").toArray();
		if($($login[0]).hasClass('am-active')){		//帐号密码登录
			var data={
				username:$("#U_Name").val(),
				password:$("#U_Pass").val(),
				login:1
			}
			if(!data.username||!data.password){
				alert('帐号或密码不能为空');
				return false;
			}
		}else{										//手机动态密码登录
			var data={
				mobile:$("#U_Mobile").val(),
				code:$("#code").val(),
				login:2
			}
			if(!data.mobile||!data.code){
				alert('手机号码或验证码不能为空');
				return false;
			}
		}
		$.post(root+'/index.php/wechat/chklogin',data,function(rs){
			if(rs.status==1){
				window.location.href=root+'/index.php/wechat/center';
			}else{
				alert(rs.msg);
			}
		},'json');
	});
	//获取验证码
	$("#getCode").click(function(){
		var self=$(this);
		if(self.attr('disabled')=='disabled'){
			return false;
		}
		var mobile=$("#U_Mobile").val();
		if(!mobile){
			alert('手机号码不能为空');
			return false;
		}
		$.get(root+"/index.php/wechat/getCode/mobile/"+mobile,function(rs){
			if(rs.status==1){
				var time = 60;
				self.attr("disabled", "disabled");
                var timer = setInterval(function() { //注册定时器
                    time--;
                    self.html(time + "后重新获取验证码");
                    if (time == 0) {
                        self.html('动态密码');
                        self.removeAttr("disabled");
                        clearInterval(timer); //释放定时器
                    }
                },1000);				
			}else{
				alert(rs.msg);
			}
		},'json');
	});
	$("#findpwd").click(function(){
		var data={};
		data.telephone=$("#U_Mobile").val();
		if(!data.telephone){
            $("#U_Mobile").popover({ theme:"warning sm",content: '手机号不能为空'});
            $("#U_Mobile").popover('open');
            return false; 			
		}
        data.password=$("#password").val();
        if(data.password.length<6||data.password.length>15){
            $("#password").popover({ theme:"warning sm",content: '密码长度为6-15位'});
            $("#password").popover('open');
            return false;       
        }
        data.password2=$("#password2").val();
        if(data.password2!=data.password){
            $("#password2").popover({ theme:"warning sm",content: '两次密码不一致'});
            $("#password2").popover('open');
            return false;             
        }
        data.code=$("#code").val();
        if(!data.code){
            $("#code").popover({ theme:"warning sm",content: '验证码不能为空'});
            $("#code").popover('open');
            return false;            
        }
        $.post(root+'/wechat/findpwd',data,function(rs){
        	alert(rs.msg);
        	if(rs.status==1){
        		window.location.href=root+'/index.php/wechat/login';
        	}
        },'json');
	});
});