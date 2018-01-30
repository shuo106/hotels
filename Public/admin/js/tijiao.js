//表单表单异步提交
$(function(){
		$('form').ajaxForm(function(data){
			eval('data='+data);
			alert(data.msg);
			if(data.status==1){
				if(data.url){
                    locateUrl=data.url; 
				}
				window.location.href=locateUrl;
			}
	  });
	return false;
})