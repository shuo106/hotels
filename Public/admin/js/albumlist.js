$(function(){
    $('.huifu').click(function(){
    	        var tourl=$(this).attr('href');
				$.get(tourl,function(data){
					  eval('data='+data);
                      if(data.status==2){
                          if(confirm(data.msg)){
                              $.get(tourl+'/is_ok/1',function(data){
                                   eval('data='+data);
                                   alert(data.msg);
                                   if(data.status==1){
								     window.location.reload();
							       }
                              });
                          }
                      }else{
                      	 alert(data.msg);
                      }
				      if(data.status==1){
					     window.location.reload();
				      }
				});
				
		return false;		
    })
    $('.tofengmian').click(function(){
            $.get($(this).attr('href'),function(data){
                   eval('data='+data);
                   alert(data.msg);
                   if(data.status){
                      window.location.reload();
                   }
                   return false;
            });
            return false;
    });

})