$(function(){
      if($('select[name=color]')){
           $('select[name=color]').change(function(){
           	   $(this).css('background-color',$(this).val());
           })
      }
})