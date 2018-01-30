var param='<div class="param">标题：<input type="text" name="title[]" style="width:220px;" value="" /><br/>描述：<input type="text" style="width:320px;" name="description[]" value=""/><br/>';
function addday(){
	param2='';
	var shownums1 = parseInt($("#shownums").val());
	var shownums = shownums1+1;
	$("#shownums").val(shownums);
	var nodesdel=$(".daynum").children('.nodedel');
		nodesdel.html('');
	var h4= '<h4 class="daynum" style="width:99% height:20px;background-color: #2A7BC7;"><a class="nodedel" href="javascript:delnode();" >删除</a></h4>';   
	param2 = param+ '封面：<input type="file" name="thumb[]"/><br/>正文：</div>';
	$('#day'+shownums).html(h4+param2);
	$(".content"+shownums).html("");
	$("#show"+shownums).css("display","block");
}
function delnode(){
	var shownums = parseInt($("#shownums").val());
	var lastdel = shownums-1;
	$("#shownums").val(lastdel);
	$("#show"+shownums).css("display","none");
	$("#day"+shownums).html("");
	$(".content"+shownums).html("");
	$("#day"+lastdel).find(".nodedel").html('删除');
	
}
//下一步 表单提交
function next(){
	$("#step").html('<input type="hidden" name="step" value="3"/>');
	if(checkform()){
		$('form1').submit();
	}
}
 $(function(){
	$('form').ajaxForm(function(data){
		eval('data='+data);
		alert(data.msg);
		if(data.status==1){
			window.location.href=url+"/imagetextreply";
		}
	});
	return false;
}) 