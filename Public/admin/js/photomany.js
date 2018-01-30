$(function(){	   
   	    $('select[name=albumid]').change(function(){
   	     	       //$.post(,{aid:})
   	     	       //aid=$(this).val();
   	     	       location.href=upload_page+'/aid/'+$(this).val();
  	    });
})