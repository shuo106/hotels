(function($){
	var popup=function(option){
		if(!option)var option=new Object();
		option.width=option.width?option.width:500;
		option.height=option.height?option.height:400;
		option.content=(typeof option.content=="object")?option.content.html():option.content;
		option.id=option.id?option.id:"my_popup";
		this.option=option;
	}
	popup.prototype.show=function(){
		var style="'width:"+this.option.width+"px;height:"+this.option.height+"px;position:absolute;z-index:10000;display:none;'";
		if(this.option.url){
			var html="<iframe id="+this.option.id+" class='dm-popup' style="+style+"  frameborder='no' marginheight='0' marginwidth='0' allowTransparency='true'></iframe>";
		}else{
			var html="<div id="+this.option.id+" class='dm-popup' style="+style+"  frameborder='no' marginheight='0' marginwidth='0' allowTransparency='true'>asdf</div>";
		}
		$(html).prependTo('body');
	    var st=document.documentElement.scrollTop|| document.body.scrollTop;
	    var sl=document.documentElement.scrollLeft|| document.body.scrollLeft;
	    var ch=document.documentElement.clientHeight;
	    var cw=document.documentElement.clientWidth;
	    var obj=$("#"+this.option.id);
	    var objH=obj.height();
	    var objW=obj.width();
	    var objT=Number(st)+(Number(ch)-Number(objH))/2;
	    var objL=Number(sl)+(Number(cw)-Number(objW))/2;
	    obj.css('left',objL);
	    obj.css('top',objT);
	 	obj.css('background-color',"#FFF");
	 	obj.html(this.option.content);
	    if(this.option.url)obj.attr("src", this.option.url);
	    obj.fadeIn();
	    this.mask();
	}
	popup.prototype.mask=function(){
		var option=this.option;
	    $("<div id='"+this.option.id+"_bg' style='background-color: Gray;display:block;z-index:9999;position:absolute;left:0px;top:0px;filter:Alpha(Opacity=30);/* IE */-moz-opacity:0.4;/* Moz + FF */opacity: 0.4; '/>").prependTo('body'); 
	    var bgWidth = Math.max($("body").width(),document.documentElement.clientWidth);
	    var bgHeight = Math.max($("body").height(),document.documentElement.clientHeight);
	    $("#"+option.id+"_bg").css({width:bgWidth,height:bgHeight});
	    $("#"+option.id+"_bg").click(function() {
	        $("#"+option.id).remove();
	        $("#"+option.id+"_bg").remove();
	    });
	}
	$.extend({
	    popup: function(option){
	    	return new popup(option);
	    }
	});

	$.fn.popup=function(data){
		data.content=this.html();
		return new popup(data);
	}
})(jQuery);