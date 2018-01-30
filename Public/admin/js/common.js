$(function(){
        $('form').ajaxForm(function(data){
              eval('data='+data);
              alert(data.msg);
              if(data.status==1){
                window.location.href=locateUrl;
              }
              return false;
        });
        //删除操作
        $('.del').click(function(){
        	    //所有的删除操作都用这个 特殊操作需要罗列条件
				if(!$(this).attr('type')&&confirm('你确定删除吗？')){
					$.get($(this).attr('href'),function(data){
						eval('data='+data);
						alert(data.msg);
						if(data.status==1){
							window.location.reload();
						}
					});
					//特别的删除操作在下面罗列(如同页面回收站里面的操作)
				}else if($(this).attr('type')&&confirm('该操作不可恢复,您确定删除吗？')){
					  var type=$(this).attr('type');
                      $.get($(this).attr('href'),{'type':type},function(data){
						eval('data='+data);
						alert(data.msg);
						if(data.status==1){
							window.location.reload();
						}
					});
				}
				return false;
	   });
       //还原操作
       $('.huanyuan').click(function(){
				if(confirm('您确定还原？')){
					$.get($(this).attr('href'),function(data){
						eval('data='+data);
						alert(data.msg);
						if(data.status==1){
							window.location.reload();
						}
					});
				}
				return false;
	    });
       //全选操作 
		$('.quanxuan').click(function(){
			if($(this).attr('checked')=='checked'){
				$("input[type=checkbox][name='id[]']").attr('checked','checked');
			}else{
				$("input[type=checkbox][name='id[]']").removeAttr('checked','checked');
			}
		});
    //批量删除操作 针对于单按钮的形式
    $('.pi-del').click(function(){ 
    	var ids=getid($('.ids'),'checked');
    	var type=1;
        if(ids.length==0){
    		alert('请选择需要操作的数据');
            return false;
    	}
    	$.post(piUrl,{'type':type,'ids':ids},function(data){
            alert(data.msg);
            if(data.status){
               location.href=locateUrl;
            }
            return false;
        },'json');
    });
//批量操作 针对于下拉框的形式
    if(document.getElementById("piliang")){
        $('#piliang').val(0);
        $('#piliang').change(function(){
              var type=$(this).val(); 
              var ids=getid($('.ids'),'checked');
              //选择批量移动的时候
              if(type!=0){
                if(ids.length==0){
                  alert('请选择需要操作的数据');
                  $(this).val(0);
                      return false;
                }
                //批量移动操作 当增加其他批量功能的时候 只需要复制if判断就行（特殊的批量操作）
                if(type==2){
                  $('.Select_two').show();
                  $('.Select_two').change(function(){
                            var cid=$('.Select_two').val();//选择移动的栏目id
                      if(cid==0){
                                   alert('请选择栏目');
                                   $(this).val(0);
                                   return false;
                      }
                      $.post(piUrl,{'type':type,'ids':ids,'cid':cid},function(data){
                                   alert(data.msg);
                              if(data.status){
                                 location.href=locateUrl;
                              }
                          return false;
                              },'json');
                      });
                  $('.Select_two').val(0);
                  return false; 
                }
                
                 //一般性的批量操作
                $.post(piUrl,{'type':type,'ids':ids},function(data){
                     alert(data.msg);
                  if(data.status){
                     location.href=locateUrl;
                  }
                  return false;
                },'json');

              }
        $(this).val(0); //还原表单
      });
    }


    
		//点击新闻栏目显示下面的新闻
		$('.Select_one').change(function(){
			 var cid=$(this).val();
			 location.href=locateUrl+'/catid/'+cid;
		});
		//查询
		$('.cx').click(function(){
				var con = $('#content').val();
        var oc=$(this).attr('c');
				var msg;
        if(!con){
             if(oc=='canting'){
                msg='查询的餐厅名称不能为空'
             }
             alert(msg);
             return false;
				}
				window.location.href=locateUrl+"/text/"+con;
	  });
    $('.isshow').click(function(){
          $.get($(this).attr('href'),function(data){
            eval('data='+data);
            alert(data.msg);
            if(data.status==1){
              window.location.reload();
            }
          });
          return false;
    });
    //得到选中状态的id
    function getid(obj,status){
                var id=[];
                for(var i=0 in obj){
                   if(obj.eq(i).attr(status)){
                    id.push(obj.eq(i).val()); 
                   }
                }
                return id;
    }
});