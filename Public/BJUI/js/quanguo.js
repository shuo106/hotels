			$(function() {
			    BJUI.init({
			            JSPATH: public+'/BJUI/', //[可选]框架路径
			            PLUGINPATH: public+'/BJUI/plugins/', //[可选]插件路径
			            loginInfo: {
			                url: 'login_timeout.html',
			                title: '登录',
			                width: 400,
			                height: 200
			            }, // 会话超时后弹出登录对话框
			            statusCode: {
			                ok: 200,
			                error: 300,
			                timeout: 301
			            }, //[可选]
			            ajaxTimeout: 50000, //[可选]全局Ajax请求超时时间(毫秒)
			            pageInfo: {
			                total: 'total',
			                pageCurrent: 'pageCurrent',
			                pageSize: 'pageSize',
			                orderField: 'orderField',
			                orderDirection: 'orderDirection'
			            }, //[可选]分页参数
			            alertMsg: {
			                displayPosition: 'topcenter',
			                displayMode: 'slide',
			                alertTimeout: 3000
			            }, //[可选]信息提示的显示位置，显隐方式，及[info/correct]方式时自动关闭延时(毫秒)
			            keys: {
			                statusCode: 'statusCode',
			                message: 'message'
			            }, //[可选]
			            ui: {
			                windowWidth: 0, //框架显示宽度，0=100%宽，> 600为则居中显示
			                showSlidebar: true, //[可选]左侧导航栏锁定/隐藏
			                clientPaging: true, //[可选]是否在客户端响应分页及排序参数
			                overwriteHomeTab: false //[可选]当打开一个未定义id的navtab时，是否可以覆盖主navtab(我的主页)
			            },
			            debug: false, // [可选]调试模式 [true|false，默认false]
			            theme: 'sky' // 若有Cookie['bjui_theme'],优先选择Cookie['bjui_theme']。皮肤[五种皮肤:default, orange, purple, blue, red, green]
			        })
			        // main - menu
			    $('#bjui-accordionmenu')
			        .collapse()
			        .on('hidden.bs.collapse', function(e) {
			            $(this).find('> .panel > .panel-heading').each(function() {
			                var $heading = $(this),
			                    $a = $heading.find('> h4 > a')
			                if ($a.hasClass('collapsed')) $heading.removeClass('active')
			            })
			        })
			        .on('shown.bs.collapse', function(e) {
			            $(this).find('> .panel > .panel-heading').each(function() {
			                var $heading = $(this),
			                    $a = $heading.find('> h4 > a')
			                if (!$a.hasClass('collapsed')) $heading.addClass('active')
			            })
			        })
			    $(document).on('click', 'ul.menu-items li > a', function(e) {
			        var $a = $(this),
			            $li = $a.parent(),
			            options = $a.data('options').toObj(),
			            $children = $li.find('> .menu-items-children')
			        var onClose = function() {
			            $li.removeClass('active')
			        }
			        $("#locations").html(delHtmlTag($a.html()));
			        var onSwitch = function() {
			            $('#bjui-accordionmenu').find('ul.menu-items li').removeClass('switch')
			            $li.addClass('switch')
			        }
			        $li.addClass('active')
			    })
			    $(document).on('click', 'ul.navtab-tab li', function(e) {
			        $("#locations").html($(e.target)[0].textContent);
			        if ($(e.target)[0].tagName == 'SPAN') {
			            var index = $(e.target).parent('a').parent('li').index();
			        } else if ($(e.target)[0].tagName == 'A') {
			            var index = $(e.target).parent('li').index();
			        }
			        return false;
			        //							console.log(index);
			        if (index == 0) {
			            return false;
			        } else {
			            $(this).navtab('refresh')
			        }
			    });
			    $(document).on('click', 'ul.kjdh li', function() {
			        $(this).addClass('nav_active').siblings().removeClass('nav_active');
			    });
			    //时钟
			    var today = new Date(),
			        time = today.getTime()
			    $('#bjui-date').html(today.formatDate('yyyy/MM/dd'))
			    setInterval(function() {
			        today = new Date(today.setSeconds(today.getSeconds() + 1))
			        $('#bjui-clock').html(today.formatDate('HH:mm:ss'))
			    }, 1000)
			})



			function MainMenuClick(event, treeId, treeNode) {
			    event.preventDefault()
			    if (treeNode.isParent) {
			        var zTree = $.fn.zTree.getZTreeObj(treeId)
			        zTree.expandNode(treeNode, !treeNode.open, false, true, true)
			        return
			    }
			    if (treeNode.target && treeNode.target == 'dialog')
			        $(event.target).dialog({
			            id: treeNode.tabid,
			            url: treeNode.url,
			            title: treeNode.name
			        })
			    else
			        $(event.target).navtab({
			            id: treeNode.tabid,
			            url: treeNode.url,
			            title: treeNode.name,
			            fresh: treeNode.fresh,
			            external: treeNode.external
			        })
			}

			function delHtmlTag(str) {
			    return str.replace(/<[^>]+>/g, ""); //去掉所有的html标记
			}
//刷新当前窗口
function refresh(){
	$(this).navtab('refresh'); 
}
//关闭弹窗
function close_dialog(){
	$(this).dialog('closeCurrent');
}