(function($) {
    var chose = function(data) {
        this.data = data;
        this._init(data);
    }
    //初始化
    chose.prototype._init = function(data) { //初始化
        var _self=this;
        if(!_self.data.obj){
            _self._create(data);
        }      
        //获取焦点
        data.self.focus(function(){
            var top=data.self.offset().top?data.self.offset().top:data.self.position().top;
            var left=data.self.offset().left?data.self.offset().left:data.self.position().left;
            data.obj.css({
                top:top+data.self.height()+10,
                left:left
            });
            data.obj.show();
        });
        //失去焦点关闭
        data.self.blur(function(){
            _self._timer=setTimeout(function(){
                _self.close();
            },200);
        });
        //默认事件
        data.obj.find('.am-tab-panel li span').click(function(){
            data.self.val($(this).data('name'));
            _self.close();
        });  
    }
    //创建
    chose.prototype._create=function(data){ 
        var _self=this; 

        data.obj = $("<div/>").appendTo($("body"));//创建容器 
        //设置容器样式
        data.obj.addClass('am-city-chose'); 

        //创建内容
        var $html = $('<div class="am-tabs"><ul class="am-tabs-nav am-nav am-nav-tabs am-nav-justify"></ul><div class="am-tabs-bd"></div></div>');
        data.obj.append($html);
        var $parent = data.obj.find("div");
        var cls = "",
            nav = "",
            bd = "",
            arr, id, name, citydata = data.data;
        //遍历数据
        for (var i = 0; i < citydata.length; i++) {
            //标题
            cls = i == 0 ? 'am-active' : "";
            nav += '<li class="' + cls + '"><a href="#">' + citydata[i].name + '</a></li>';
            //数据
            bd += '<div class="am-tab-panel ' + cls + '">';
            arr = citydata[i].data.split('@');
            for (var j = 1; j < arr.length; j++) {
                id = arr[j].split('|')[0];
                name = arr[j].split('|')[1];
                bd += '<li>';
                bd += '<span class="am-badge am-badge-secondary" data-name="' + name + '" data-id="' + id + '">' + name + '</span>';
                bd += '</li>';
            }
            bd += '</div>';
        }
        $parent.find('ul.am-tabs-nav').html(nav);
        $parent.find('div.am-tabs-bd').html(bd);
        $parent.tabs(); 
        //hover
        _self.data.obj.find('.am-tabs .am-tabs-nav li').mouseover(function(){
            var index=$(this).index();
            $parent.tabs('open', index);
        });     
        return this;        
    }
    chose.prototype.init = function(callback) {
        callback();
        return this;
    }
    //点击事件
    chose.prototype.click = function(callback) {
        var self=this.data.self;
        this.data.obj.find('.am-tabs-bd .am-tab-panel span').click(function(){
            callback($(this),self);
        });
        return this;
    }
    //关闭事件
    chose.prototype.close=function(){
        if(this.data.obj._timer){
            clearTimeout(timer);
        }
        this.data.obj.hide();
    }
    $.fn.chose = function(data) {
        if (!data) data = {};
        data.self=this;
        return new chose(data);
    }
})(jQuery);
