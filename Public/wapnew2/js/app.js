var store = $.AMUI.store;


var goorder=function(rid) {
    var par = 'id/' + rid;
    if (start && end) {
        par += '/start/' + start + '/end/' + end;
    }
    window.location.href = root + "/Wap/order/" + par;
}

//农家乐跳转房间预订
var goorderf=function(rid) {
    var par = 'id/' + rid;
    if (start && end) {
        par += '/start/' + start + '/end/' + end;
    }
    window.location.href = root + "/Farmstaywap/orderf/" + par;
}
//农家乐跳转美食预订
var goorderd=function(rid) {
    var par = 'id/' + rid;
    if (start && end) {
        par += '/start/' + start + '/end/' + end;
    }
    window.location.href = root + "/Farmstaywap/orderd/" + par;
}

//美食预订
var goorderm=function(rid) {
    var par = 'id/' + rid;
    if (start && end) {
        par += '/start/' + start + '/end/' + end;
    }
    window.location.href = root + "/Cateringwap/orderm/" + par;
}






var getLocation = function() {
    var geo = new $.AMUI.Geolocation({
        enableHighAccuracy: true,
        timeout: 5000,
        maximumAge: 60000
    });
    geo.get().then(function(position) {
        var url = root + "/wap/location/lat/" + position.coords.latitude + "/lng/" + position.coords.longitude;
        $.get(url, function(rs) {
            if (rs.status == 0) {
                store.set('location', rs.result.location);
                window.location.reload();
            } else {
                console.log('获取地理位置失败');
            }
        }, 'json');
    }, function(err) {
        console.log(err);
    });
}
$(function() {
    //搜索
    $(".w-search .am-dropdown-content li a").click(function() {
        search_obj[$(this).data('type')] = $(this).data('value');
        var url = $(".w-search").data('url');
        for (i in search_obj) {
            url += '/' + i + '/' + (search_obj[i].length == 0 ? '0' : search_obj[i]);
        }
        window.location.href = url;
    });
    $("#w-search").click(function() {
        var url = $(this).data('url');
        var keywords = $("#keywords").val();
        if (!keywords) {
            alert('请填写关键词');
            return false;
        }
        window.location.href = url + keywords;
    });
});
