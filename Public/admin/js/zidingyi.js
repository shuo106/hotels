$(function(){
   $(".showdiv").click(function(){

	    var type=$(this).attr('type');
		var box =300;//出现的位置
		var bw=300;//窗口的宽度
		var bh=200;//窗口的高度
		var th= $(window).scrollTop()+$(window).height()/2-box/2+58;
		
		var h =document.body.clientHeight;
		
		var rw =$(window).width()/2-box;
		
		$(".showbox").animate({top:th,opacity:'show',width:bw,height:bh,right:rw+bw/2},500);
		
		$("body").prepend("<div class='mask'></div>");
		
		$(".mask").css({opacity:"0.5"}).css("height",h);
        $('input[name=type]').val(type);//类型赋值
		var text;
		if($(this).parent().find('p').text()){//编辑操作
			text=rltrim($(this).parent().find('p').text().replace(/^\n+|\n+$/g,""));//自定义名称
			$('input[name=name]').val(text);
			$('input[name=id]').val($(this).attr('ids'));
            text=' 一 '+text+' 一 编辑';
        }else{
        	$('input[name=name]').val('');
        	$('input[name=id]').val($(this).attr(''));
            text='';
        }
		if(type==1){			
            $('.showbox').find('h2').find('span').text('床型'+text);
	    }else if(type==2){
            $('.showbox').find('h2').find('span').text('早餐'+text);
	    }else if(type==3){
            $('.showbox').find('h2').find('span').text('上网'+text);
	    }
		return false;
	});
	
	$(".showbox .close").click(function(){
		$(this).parents(".showbox").animate({top:0,opacity: 'hide',width:0,height:0,right:0},500);
		$(".mask").fadeOut("fast");
		
	});
	/**
	 * [rltrim 字符串去除左右空格]
	 * @return {[type]} [description]
	 */
	function rltrim(s){
        return rtrim(ltrim(s));
	}
	//去左空格;包括半角空格，全角空格。
	function ltrim(s){
	return s.replace( /^[" "|"　"]*/,"");
	}
	//去右空格;包括半角空格，全角空格。
	function rtrim(s){
	return s.replace( /[" "|"　"]*$/, "");
	}



})