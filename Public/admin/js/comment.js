$(function(){
   /**
    * [评论审核操作]
    * @return {[type]} [description]
    */
   $('.shenhe').click(function(){
   	      var dang=$(this);
          $.get($(this).attr('tourl'),function(data){
               eval('data='+data);
               alert(data.msg);
               if(data.status){
               	   dang.unbind("click");
                    
                   dang.html('[已审核]');
                   
               } 
               return false;
          })
    return false;
   });



})