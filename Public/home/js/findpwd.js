$(function(){
	$("#sub_btn").click(function(){
		var email = $("#email").val();
		var preg = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/; //匹配Email
		if(email=='' || !preg.test(email)){
			$("#chkmsg").html("请填写正确的邮箱！");
		}else{
			$("#sub_btn").attr("disabled","disabled").val('.....').css("cursor","default");
			$.post(emailtourl,{mail:email},function(msg){
				if(msg=="noreg"){
					$("#chkmsg").html("该邮箱尚未注册！");
					$("#sub_btn").removeAttr("disabled").val('  ').css("cursor","pointer");
				}else{
					$(".demo").html("<h3>"+msg+"</h3>");
				}
			});
		}
	});
})