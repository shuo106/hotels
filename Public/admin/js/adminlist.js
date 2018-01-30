$(function(){
	$('#search').click(function(){
		var con = $('#content').val();
		if(con == ''){
			alert('请输入用户名或者真实姓名');
			return false;
		}
		window.location.href=locateUrl+"/text/"+con;
	})
    $('.suoding').click(function(){
    	    var dang=$(this);  
        $.get($(this).attr('href'),function(data){
                eval('data='+data);
                alert(data.msg);
                if(data.status){
                    location.reload(); 
                }
        });        
        return false;
    });







})