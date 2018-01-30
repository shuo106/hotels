$(function(){
        $('.check-app').click(function(){
        	  var inputs=$(this).parents('.app').find('input');
        	  $(this).attr('checked')?inputs.attr('checked','checked'):inputs.removeAttr('checked');
        })
        $('.check-control').click(function(){
        	  var inputs=$(this).parents('.control').find('input');;
        	  $(this).attr('checked')?inputs.attr('checked','checked'):inputs.removeAttr('checked');
        })
})