	//酒店搜索 1城市 2区域 3价格区间 4酒店品牌 5位置 6 酒店名称
	function search(k, v) {
	    var url = this.location.href;
	    if (url.indexOf('?&p=') > 0) {
	        purl = url.split('?&p=');
	        url = purl[0];
	    }
	    url = url.replace(/.html/, "");
	    var k1 = url.split('search-');
	    var k2 = k1[1].split('-');
	    k2[k] = v;
	    window.location.href = k1[0] + 'search-' + k2[0] + '-' + k2[1] + '-' + k2[2] + '-' + k2[3] + '-' + k2[4] + '-' + k2[5] + '-' + k2[6] + '-' + k2[7] + '-' + k2[8] + '.html';
	}
	/*
		表单提交城市搜索
	*/
	function search2() {
	    var city = $("#cityid").val();
	    if (city == '') {
	        alert('请选择城市！');
	        return false;
	        city = 0;
	    }
	    var ruzhu = $("#ruzhu").val();
	    var likai = $("#likai").val();
	    if (ruzhu == '' || likai == '') {
	        start = 0;
	        end = 0;
	    } else {
	        start = ruzhu.replace(new RegExp(/(-)/g), '');
	        end = likai.replace(new RegExp(/(-)/g), '');
	        //start = new Date(ruzhu).getTime()/1000;
	        //end   = new Date(likai).getTime()/1000;
	    }
	    var hotelnear = $("#hotelnear").val();
	    if (hotelnear == '') {
	        hotelnear = 0;
	    }
	    var company = $("#company").val();
	    if (company == '') {
	        company = 0;
	    }
	    window.location.href = hotelurl + 'search-' + city + '-0-' + start + '-' + end + '-' + hotelnear + '-' + company + '-0-0-0.html';
	}
