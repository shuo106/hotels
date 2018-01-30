function complete(orderid) {
    $.ajax({
        url: app + '/Order/complete',
        data: {
            id: orderid
        },
        method: 'GET',
        success: function(data) {
            var data = JSON.parse(data);
            alert(data.msg);
        }
    })
}
!
function($) {
    'use strict';
    //获取图片流
    function getObjectURL(file) {
        var url = null;
        if (window.createObjectURL != undefined) { // basic
            url = window.createObjectURL(file);
        } else if (window.URL != undefined) { // mozilla(firefox)
            url = window.URL.createObjectURL(file);
        } else if (window.webkitURL != undefined) { // webkit or chrome
            url = window.webkitURL.createObjectURL(file);
        }
        return url;
    }
    $(function() {
        //执行页面函数
    	if(typeof ready=='function')ready();
        //表单提交
        var $ajaxform = $(".ajaxform");
        $ajaxform.ajaxForm(function(data) {
            callback(JSON.parse(data));
        });
        //图片即时展现
        $("#uploadimg").change(function() {
            var $img = $(this).data('for');
            var objUrl = getObjectURL(this.files[0]);
            if (objUrl) {
                $($img).attr("src", objUrl);
                $("#imgss").css('display','block'); 
            };
        });
        //注册amaze select插件  
        AMUI.plugin('mySelected', AMUI.selected);
        $('.am-select').mySelected();
        //订单类别
        $("#ordertype").change(function() {
            var status = $(this).val();
            window.location.href = app + "/Order/index/status/" + status;
        });
        //房间状态
        $(".room-status a").click(function() {
            if($(this).hasClass('no-ajax')){
                window.location.href=$(this).attr('href');
                return false;
            }
            if ($(this).data('confirm') && !confirm($(this).data('confirm'))) {
                return false;
            }
            $.get($(this).attr('href'), function(rs) {
                alert(rs.info);
                window.location.reload();
            }, 'json');
            return false;
        });
        //图片上传
        $('#file_upload').uploadify({
            'formData': (typeof data)!="undefined"?data:{},
            'swf': publics+'/uploadify/uploadify.swf',
            //'uploader' : '__PUBLIC__/uploadify/uploadify.php',
            'uploader': app+'/Upload/hotel',
            'buttonText':"浏览上传",
            'onInit': function() {
                document.cookie = "pids =; expires=Fri, 31 Dec 1999 23:59:59 GMT;";
            },
            'onUploadSuccess': function(file, data) {
                console.log('文件上传初始化');
                window.location.reload();
            },
            'onQueueComplete': function() {
                window.location.reload();
            }
        });
        //图片点击放大
        $("[data-popup]").click(function(){
            var html='<div><img style="width:100%;height:100%;" src="'+$(this).attr('src')+'"></div>';
            $(html).popup({
                width:800,
                height:600
            }).show();
        });
        // 结算订单
        $('#complete').click(function(){
            alert('{$vo.orderid}');
            // href="__APP__/Order/complete/id/{$vo.orderid}"
            $.ajax({
                url: '__APP__/Order/complete/id/{$vo.orderid}',
                method: 'GET',
                success: function(data) {
                    // var data = JSON.parse(data);
                    // console.log(data);
                }

            });
        })

    });
}(jQuery);
