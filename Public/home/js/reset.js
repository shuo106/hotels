$(function(){
	$("#sub_btn").click(function(){
		var onepwd = $("input[name=onepwd]").val();
		var twopwd = $("input[name=twopwd]").val();
		/*if(email=='' || !preg.test(email)){
			$("#chkmsg").html("请填写正确的邮箱！");
		}else{
			$("#sub_btn").attr("disabled","disabled").val('提交中..').css("cursor","default");
			$.post(emailtourl,{mail:email},function(msg){
				if(msg=="noreg"){
					$("#chkmsg").html("该邮箱尚未注册！");
					$("#sub_btn").removeAttr("disabled").val('提 交').css("cursor","pointer");
				}else{
					$(".demo").html("<h3>"+msg+"</h3>");
				}
			});
		}*/
		  if(onepwd==''){
             $("#chkmsg").html("请输入新密码！");
             return false;
		  }
		  if(twopwd==''){
             $("#twochkmsg").html("请再次输入！");
             return false;
		  }
		  if(onepwd.length<6){
             $("#chkmsg").html("长度必须大于6个字符");
             return false;
		  } 
		  if(onepwd!=twopwd){
		  	 $("#twochkmsg").html("两次密码不一致！");
             return false;
		  }
		  $.post(emailtourl,{'pwd':onepwd,'uid':uid},function(data){
                        alert(data.msg);                 
                       if(data.status){
                       	  location.href=locationUrl;
                       }
		  },'json');
	});
	$("input[name=onepwd]").focus(function(){
		  	 $("#chkmsg").html("");
    });
    $("input[name=twopwd]").focus(function(){
    	  if($("input[name=onepwd]").val()==''){
               $("#chkmsg").html("请输入新密码！");
               return false;
    	  }
		  	 $("#twochkmsg").html("");
    	  
    });
})