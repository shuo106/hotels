// JavaScript Document
$(window).load(function() {
	$('#status').fadeOut();
	$('#preloader').delay(350).fadeOut('slow');
})

$(document).ready(function(){
	
	$('.aBack').click(function(){ //返回上一页
		window.history.back();
	});

	$('.goTop').click(function(){ // 返回顶部
		$(window).scrollTop(0);
	});
});

function dropDown(){ //模拟下拉
	$('.dropMenu input').click(function(event){ //下拉框下拉
		event.stopPropagation(); //取消事件冒泡
		$('.dropMenu dl').hide();
		$(this).next('dl').show();

		//下拉项是左右两栏的情况，不足两栏时候补足
		var num = $(this).next('dl.column').find('dd').length;
		if(num%2 == 1){$(this).next('dl').append('<dd></dd>');}
	});

    $('.dropMenu dd[tag!="0"]').click(function () {//下拉框操作
		var text = $(this).text();
		var val = $(this).attr('val');
		$(this).parent().hide();
		$(this).parent().parent().find('input').val(text);
		$(this).parent().parent().find("input[type='hidden']").val(val);
	});

    $('.dropMenu dt[tag!="0"]').click(function () {//下拉框操作
		$(this).parent().hide();
		$(this).parent().parent().find('input').val('');
		$(this).parent().parent().find("input[type='hidden']").val('0');
	});

	$(document).click(function (event) {$('.dropMenu dl').hide();});  //点击空白处或者自身隐藏下拉层
}

function dateDropDown() { //模拟下拉
    $('.dateDropMenu input').click(function (event) { //下拉框下拉
        event.stopPropagation(); //取消事件冒泡
        $('.dateDMenu dl').hide();
        $(this).next('dl').show();

        //下拉项是左右两栏的情况，不足两栏时候补足
        var num = $(this).next('dl.column').find('dd').length;
        if (num % 2 == 1) { $(this).next('dl').append('<dd></dd>'); }
    });

    $('.dateDropMenu dd[tag!="0"]').click(function () {//下拉框操作
        var text = $(this).text();
        var val = $(this).attr('val');
        $(this).parent().hide();
        $(this).parent().parent().find('input').val(text);
        $(this).parent().parent().find("input[type='hidden']").val(val);
    });

    $('.dateDropMenu dt').click(function () {//下拉框操作
        $(this).parent().hide();
        $(this).parent().parent().find('input').val('');
        $(this).parent().parent().find("input[type='hidden']").val('0');
    });
}

function bindScroll() {
    $(window).bind("scroll", function () {
        scrollTop = $(document).scrollTop();
        bodyHeight = document.body.scrollHeight;
        if (scrollTop + winHeight > bodyHeight - bottom) {//load data
            $(window).unbind("scroll");
            getData();
        }
    });
}
function bindFormResize() {
    $(window).bind("resize", function () {
        winHeight = $(window).height();
        scrollTop = $(document).scrollTop();
        bodyHeight = document.body.scrollHeight;
    });
}

function stopPropagation(e) {//防止Tab切换冒泡
    if (e.stopPropagation) {
        e.stopPropagation();
    } else {
        e.cancelBubble = true;
    }
}