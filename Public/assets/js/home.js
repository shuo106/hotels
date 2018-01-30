!
function($) {
    'use strict';
    var store = $.AMUI.store; //本地存储
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
        //注册select
        AMUI.plugin('mySelected', AMUI.selected)
        $("select[data-am-selected]").mySelected();
        //表单提交
        $("[ajaxform]").on('click', function() {
            var $form = $($(this).data('form'));
            var call = $(this).data('callback') ? $(this).data('callback') : 'callback'; //回调
            $form.ajaxForm(function(rs) {
                try {
                    eval(call + '(' + rs + ')');
                } catch (e) {
                    console.error(e);
                }
            });
            $form.submit();
        });
        //图片即时展现
        $("#uploadimg").change(function() {
            var $img = $(this).data('for');
            var objUrl = getObjectURL(this.files[0]);
            if (objUrl) {
                $($img).attr("src", objUrl);
            };
        });
        //获取验证码
        $("#getCode").click(function() {
            var self = $(this);
            var url = $(this).data('url');
            var mobile = $(self.data('mobile')).val();
            if (!mobile || mobile.length != 11) {
                alert('请填写正确的手机号');
                return false;
            }
            $.get(url + mobile, function(data) {
                var data=eval('('+data+')');
                //var data = JSON.parse(data);//这个为什么会提示错误
                alert(data.msg)
                if (data.status == 1) {
                    self.attr("disabled", "disabled");
                    var time = 60;
                    var timer = setInterval(function() { //注册定时器
                        time--;
                        self.html(time + "秒后重新获取");
                        if (time == 0) {
                            self.html('获取验证码');
                            self.removeAttr("disabled");
                            clearInterval(timer); //释放定时器
                        }
                    }, 1000);
                }
            });
        });
        //回车提交表单
        $(document).keydown(function(event) {
            if (event.keyCode == 13) {
                $('[ajaxform]:visible').trigger('click');
            }
        });
        //图片点击放大
        $("[data-popup]").click(function() {
            var html = '<div><img style="width:100%;height:100%;" src="' + $(this).attr('src') + '"></div>';
            $(html).popup({
                width: 800,
                height: 600
            }).show();
        });
        //选项卡切换
        if ($("[data-item]")) { $("[data-item]").addClass('am-util-item') }
        if ($("[data-item-header]")) { $("[data-item-header]").addClass('am-util-item-header') }
        if ($("[data-item-body]")) { $("[data-item-body]").addClass('am-util-item-body') }
        $("[data-item-header] ul li").mouseover(function() {
            var i = $(this).index();
            var $parent = $(this).closest('[data-item]');
            var $head = $parent.find('[data-item-header]');
            var $body = $parent.find("[data-item-body]");

            $head.find('ul li').removeClass('active');
            $head.find('dl dd').removeClass('active');

            $head.find('ul li').eq(i).addClass('active');
            $head.find('dl dd').eq(i).addClass('active');

            $body.removeClass('active');
            $body.eq(i).addClass('active');
        });
        //会员中心导航栏事件
        $("#collapase-nav-1 li a[data-am-collapse]").click(function() {
            var index = $(this).parent('li').index();
            store.set('menu_index', index);
        });
        if (store.get('menu_index')) {
            $("#collapase-nav-1 > li").eq(store.get('menu_index')).find('ul').addClass('am-in');
        }
        //收藏
        $("#shoucang").on('click', function() {
            var $self = $(this);
            var id =$self.data('id');
            $.get(root+'/index.php/Hotels/shoucang/id/'+id,function(rs){
                if(rs.status==-1){
                     alert(rs.msg);
                     window.location.href=root+"/login.html";
                }
                if(rs.status==0){
                    alert(rs.msg);
                    return false;
                }
                if(rs.status==1){
                    $self.find('span').html('已收藏');
                    $self.attr('class','xq_ysc');
                }
                if(rs.status==2){
                    $self.find('span').html('收藏');
                    $self.attr('class','xq_sc');
                }
            },'json');
        });
        //减
        $("[am-minus]").click(function(){
            var $input=$($(this).attr('am-minus'));
            var v=$input.val();
            var minus=$(this).data('minus');
            var call = $(this).data('callback'); //回调
            if(v>minus){
                v--;
                $input.val(v);
                try {
                    eval(call + '()');
                }catch(e){
                    console.log(e);
                }
            }
        });
        //加
        $("[am-plus]").click(function(){
            var $input=$($(this).attr('am-plus'));
            var v=$input.val();
            var call = $(this).data('callback'); //回调
            v++;
            $input.val(v);
            try {
                eval(call + '()');
            }catch(e){
                console.log(e);
            }
        });        
    });
}(jQuery);
