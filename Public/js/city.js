// Java Documen


//酒店根据城市查找三字码 
function getThreeWordByCitysFlight(cityName) 
{ 
        var threeWord = ""; 
        for(var i = 0,len = citysFlight.length;i<len;i++) 
        { 
                if(cityName == citysFlight[i][1]) 
                { 
                        threeWord = citysFlight[i][0]; 
                        break; 
                } 
        } 
        return threeWord; 
} 


//初始化常用城市
var commoncitys,citys;

var commoncitysHotel = new Array();
var citysHotel = new Array();


var commoncitysFlight = new Array();
commoncitysFlight[0]=new Array('SZX','深圳','Shenzhen','SZ');
commoncitysFlight[1]=new Array('PEK','北京','Beijing','BJ');
commoncitysFlight[2]=new Array('SHA','上海','Shanghai','SH');
commoncitysFlight[3]=new Array('CGO','郑州','Zhengzhou','ZZ');
commoncitysFlight[4]=new Array('CAN','广州','Guangzhou','GZ');
commoncitysFlight[5]=new Array('HGH','杭州','Hangzhou','HZ');
commoncitysFlight[6]=new Array('CKG','重庆','Chongqing','CQ');
commoncitysFlight[7]=new Array('SIA','西安','Xian','XA');
commoncitysFlight[8]=new Array('WUH','武汉','Wuhan','WH');
commoncitysFlight[9]=new Array('NKG','南京','Nanjing','NJ');
commoncitysFlight[10]=new Array('SYX','三亚','Sanya','SY');

//初始化各个城市
var citysFlight=new Array();
// A
citysFlight[0]=new Array('AKU','阿克苏','Akesu','AKS');
citysFlight[1]=new Array('AAT','阿勒泰','Aletai','ALT');
citysFlight[2]=new Array('AQG','安庆','Anqing','AQ');
citysFlight[3]=new Array('AKA','安康','Ankang','AK');
citysFlight[4]=new Array('IOB','鞍山','Anshan','AS');
citysFlight[5]=new Array('BAV','包头','Baotou','BT');
citysFlight[6]=new Array('BSD','保山','Baoshan','BS');
citysFlight[7]=new Array('BHY','北海','Beihai','BH');
citysFlight[8]=new Array('PEK','北京','Beijing','BJ');
citysFlight[9]=new Array('CGQ','长春','Changchun','CC');
citysFlight[10]=new Array('CSX','长沙','Changsha','CS');
citysFlight[11]=new Array('CIH','长治','Changzhi','CZ');
citysFlight[12]=new Array('CGD','常德','Changde','CD');
citysFlight[13]=new Array('CZX','常州','Changzhou','CZ');
citysFlight[14]=new Array('CTU','成都','Chengdu','CD');
citysFlight[15]=new Array('CKG','重庆','Chongqing','CQ');
citysFlight[16]=new Array('BPX','昌都','Changdu','CD');
citysFlight[17]=new Array('DLU','大理','Dali','DDL');
citysFlight[18]=new Array('DLC','大连','Dalian','DL');
citysFlight[19]=new Array('DTG','大同','Datong','DT');
citysFlight[20]=new Array('DDG','丹东','Dandong','DD');
citysFlight[21]=new Array('DNH','敦煌','Dunhuang','DH');
citysFlight[22]=new Array('ENH','恩施','Enshi','ES');
citysFlight[23]=new Array('FOC','福州','Fuzhou','FZ');
citysFlight[24]=new Array('KOW','赣州','Ganzhou','GZ');
citysFlight[25]=new Array('CAN','广州','Guangzhou','GZ');
citysFlight[26]=new Array('KWE','贵阳','Guiyang','GY');
citysFlight[27]=new Array('KWL','桂林','Guilin','GL');
citysFlight[28]=new Array('HRB','哈尔滨','Haerbin','HEB');
citysFlight[29]=new Array('HAK','海口','Haikou','HK');
citysFlight[30]=new Array('HLD','海拉尔','Hailaer','HLE');
citysFlight[31]=new Array('HGH','杭州','Hangzhou','HZ');
citysFlight[32]=new Array('HFE','合肥','Hefei','HF');
citysFlight[33]=new Array('HTN','和田','Hetian','HT');
citysFlight[34]=new Array('HET','呼和浩特','Huhehaote','HHHT');
citysFlight[35]=new Array('HJJ','怀化','Huaihua','HH');
citysFlight[36]=new Array('AVA','黄果树','Huangguoshu','HGS');
citysFlight[37]=new Array('TXN','黄山','Huangshan','HS');
citysFlight[38]=new Array('TNA','济南','Jinan','JN');
citysFlight[39]=new Array('JMU','佳木斯','Jiamusi','JMS');
citysFlight[40]=new Array('JDZ','景德镇','Jindezhen','JDZ');
citysFlight[41]=new Array('KJI','喀纳斯','Kanasi','KNS');
citysFlight[42]=new Array('KHG','喀什','Kashi','KS');
citysFlight[43]=new Array('KRY','克拉玛依','Kelamayi','KLMY');
citysFlight[44]=new Array('KCA','库车','Kuche','KC');
citysFlight[45]=new Array('KRL','库尔勒','Kuerle','KEL');
citysFlight[46]=new Array('KMG','昆明','Kunming','KM');
citysFlight[47]=new Array('LXA','拉萨','Lasha','LS');
citysFlight[48]=new Array('LHW','兰州','Lanzhou','LZ');
citysFlight[49]=new Array('LJG','丽江','Lijiang','LJ');
citysFlight[50]=new Array('LYG','连云港','Lianyungang','LYG');
citysFlight[51]=new Array('LYA','洛阳','Luoyang','LY');
citysFlight[52]=new Array('MXZ','梅县','Meixian','MX');
citysFlight[53]=new Array('MDG','牡丹江','Mudanjiang','MDJ');
citysFlight[54]=new Array('NAO','南充','Nanchong','NC');
citysFlight[55]=new Array('NLT','那里提','Naliti','NLT');
citysFlight[56]=new Array('KHN','南昌','Nanchang','NC');
citysFlight[57]=new Array('NKG','南京','Nanjing','NJ');
citysFlight[58]=new Array('NNG','南宁','Nanning','NN');
citysFlight[59]=new Array('NTG','南通','Nantong','NT');
citysFlight[60]=new Array('NNY','南阳','Nanyang','NY');
citysFlight[61]=new Array('NGB','宁波','Ningbo','NB');
citysFlight[62]=new Array('NDG','齐齐哈尔','Qiqihaer','QQHE');
citysFlight[63]=new Array('IQM','且末','Qiemo','QM');
citysFlight[64]=new Array('TAO','青岛','Qingdao','QD');
citysFlight[65]=new Array('IQN','庆阳','Qingyang','QY');
citysFlight[66]=new Array('JJN','泉州','Quanzhou','QZ');
citysFlight[67]=new Array('SYX','三亚','Sanya','SY');
citysFlight[68]=new Array('SWA','汕头','Shantou','ST');
citysFlight[69]=new Array('SHA','上海','Shanghai','SH');
citysFlight[70]=new Array('PVG','石狮','Shishi','SS');
citysFlight[71]=new Array('SZX','深圳','Shenzhen','SZ');
citysFlight[72]=new Array('SHE','沈阳','Shenyang','SY');
citysFlight[73]=new Array('SJW','石家庄','Shijiazhuang','SJZ');
citysFlight[74]=new Array('TCG','塔城','Tacheng','TC');
citysFlight[75]=new Array('TYN','太原','Taiyuan','TY');
citysFlight[76]=new Array('TSN','天津','Tianjin','TJ');
citysFlight[77]=new Array('TEN','铜仁','Tongren','TR');
citysFlight[78]=new Array('WEH','威海','Weihai','WH');
citysFlight[79]=new Array('WNZ','温州','Wenzhou','WZ');
citysFlight[80]=new Array('WNH','文山','Wenshan','WS');
citysFlight[81]=new Array('URC','乌鲁木齐','Wulumuqi','WLMQ');
citysFlight[82]=new Array('WUX','无锡','Wuxi','WX');
citysFlight[83]=new Array('WUH','武汉','Wuhan','WH');
citysFlight[84]=new Array('SIA','西安','Xian','XA');
citysFlight[85]=new Array('XNN','西宁','Xining','XN');
citysFlight[86]=new Array('JHG','西双版纳','Xishuangbanna','XSBN');
citysFlight[87]=new Array('XMN','厦门','Xiamen','XM');
citysFlight[88]=new Array('DIG','香格里拉','Xianggelila','XGLL');
citysFlight[89]=new Array('XFN','襄樊','Xiangfan','XF');
citysFlight[90]=new Array('XUZ','徐州','Xuzhou','XZ');
citysFlight[91]=new Array('YNT','烟台','Yantai','YT');
citysFlight[92]=new Array('YNJ','延吉','Yanji','YJ');
citysFlight[93]=new Array('YNZ','盐城','Yancheng','YC');
citysFlight[94]=new Array('YIN','伊宁','Yining','YN');
citysFlight[95]=new Array('YIW','义乌','Yiwu','YW');
citysFlight[96]=new Array('YIH','宜昌','Yichang','YC');
citysFlight[97]=new Array('INC','银川','Yinchuan','YC');
citysFlight[98]=new Array('ZHA','湛江','Zhanjiang','ZJ');
citysFlight[99]=new Array('DYG','张家界','Zhangjiajie','ZJJ');
citysFlight[100]=new Array('CGO','郑州','Zhengzhou','ZZ');
citysFlight[101]=new Array('ZUH','珠海','Zhuhai','ZH');
citysFlight[102]=new Array('JUZ','衢州','Quzhou','QZ');
citysFlight[103]=new Array('CHG','朝阳','Zhaoyang','ZY');
citysFlight[104]=new Array('CIF','赤峰','Chifeng','CF');
citysFlight[105]=new Array('DAX','达县','Daxian','DX');
citysFlight[106]=new Array('LUM','德宏芒市','Dehongmangshi','DHMS');
citysFlight[107]=new Array('DOY','东营','Dongying','DY');
citysFlight[108]=new Array('DSN','鄂尔多斯','Eerduosi','EEDS');
citysFlight[109]=new Array('FUG','阜阳','Fuyang','FY');
citysFlight[110]=new Array('GOQ','格尔木','Geermu','GEM');
citysFlight[111]=new Array('GYS','广元','Guangyuan','GY');
citysFlight[112]=new Array('GHN','广汉','Guanghan','GH');
citysFlight[113]=new Array('HMI','哈密','Hami','HM');
citysFlight[114]=new Array('HZG','汉中','Hanzhong','HZ');
citysFlight[115]=new Array('HNY','衡阳','Hengyang','HY');
citysFlight[116]=new Array('HYN','黄岩','Huangyan','HY');
citysFlight[117]=new Array('AHE','惠阳','Huiyang','HY');
citysFlight[118]=new Array('HEK','黑河','Heihe','HH');
citysFlight[119]=new Array('JIL','吉林','Jilin','JL');
citysFlight[120]=new Array('KNC','吉安','Jian','JA');
citysFlight[121]=new Array('JNZ','锦州','Jinzhou','JZ');
citysFlight[122]=new Array('SHS','荆州','Jingzhou','JZ');
citysFlight[123]=new Array('JIU','九江','Jiujiang','JJ');
citysFlight[124]=new Array('JZH','九寨沟','Jiuzhaigou','JZG');
citysFlight[125]=new Array('LIA','梁平','Liangping','LP');
citysFlight[126]=new Array('LYI','临沂','Linyi','LY');
citysFlight[127]=new Array('LNJ','临沧','Lincang','LC');
citysFlight[128]=new Array('LZH','柳州','Liuzhou','LZ');
citysFlight[129]=new Array('LZO','泸州','Luzhou','LZ');
citysFlight[130]=new Array('NZH','满州里','Manzhouli','MZL');
citysFlight[131]=new Array('MIG','绵阳','Mianyang','MY');
citysFlight[132]=new Array('NLT','那拉提','Nalati','NLT');
citysFlight[133]=new Array('PZI','攀枝花','Panzhihua','PZH');
citysFlight[134]=new Array('SHP','秦皇岛','Qinhuangdao','QHD');
citysFlight[135]=new Array('JUZ','衢州','Quzhou','QZ');
citysFlight[136]=new Array('SXJ','鄯善','Shanshan','SS');
citysFlight[137]=new Array('SHG','韶关','Shaoguan','SG');
citysFlight[138]=new Array('SYM','思茅','Simao','SM');
citysFlight[139]=new Array('WXN','万州','Wanzhou','WZ');
citysFlight[140]=new Array('WEF','潍坊','Weifang','WF');
citysFlight[141]=new Array('WUS','武夷山','Wuyishan','WYS');
citysFlight[142]=new Array('WUZ','梧州','Wuzhou','WZ');
citysFlight[143]=new Array('XIC','西昌','Xichang','XC');
citysFlight[144]=new Array('ACX','兴义','Xingyi','XY');
citysFlight[145]=new Array('LLF','永州','Yongzhou','YZ');
citysFlight[146]=new Array('ENY','延安','Yanan','YA');
citysFlight[147]=new Array('YBP','宜宾','Yibin','YB');
citysFlight[148]=new Array('UYN','榆林','Yulin','YL');
citysFlight[149]=new Array('ZAT','昭通','Zhaotong','ZT');
citysFlight[150]=new Array('HSN','舟山','Zhoushan','ZS');
citysFlight[151]=new Array('ZYI','遵义','Zunyi','ZY');
citysFlight[152]=new Array('OHE','漠河','Mohe','MH');
citysFlight[153]=new Array('HDG','邯郸','Handan','HD');
citysFlight[154]=new Array('JNG','济宁','JiNing','JN');
citysFlight[155]=new Array('YCU','运城','YunCheng','YC');
commoncitys = commoncitysFlight;
citys = citysFlight;


//根据三字码查找城市
function getCityByThreeWord(threeWord)
{
	var cityCn = "";
	for(var i = 0,len = citys.length;i<len;i++)
	{
		if(threeWord == citys[i][0])
		{
			cityCn = citys[i][1];
			break;
		}
	}
	return cityCn;
}
//根据城市查找三字码
function getThreeWordByCity(cityName)
{
	var threeWord = "";
	for(var i = 0,len = citys.length;i<len;i++)
	{
		if(cityName == citys[i][1])
		{
			threeWord = citys[i][0];
			break;
		}
	}
	return threeWord;
}
var parentbject;
var city_suggest = function(){
	this.Remoreurl = ''; // 远程URL地址
	this.object = '';
	this.id2 = '';
	this.taskid = 0;
	this.delaySec = 100; // 默认延迟多少毫秒出现提示框
	this.lastkeys_val= 0;
	var lastkeys_val= 0;
	this.lastinputstr = '';	
	/**
	* 初始化类库
	*/
	this.init_zhaobussuggest=  function(){
		var objBody = document.getElementById("mainbody");
		var objiFrame = document.createElement("iframe");
		var objplatform = document.createElement("div");
                 
		objiFrame.setAttribute('id','getiframe');
		objiFrame.style.zindex='100';		
		objiFrame.style.position = 'absolute';
		objiFrame.style.display = 'none';
		objplatform.setAttribute('id','getplatform');
		objplatform.setAttribute('align','left');
		objplatform.style.zindex='10000';
		objBody.appendChild(objiFrame);
		objBody.appendChild(objplatform);
		var win=objBody || window
                
		if(!document.all) {
			objBody.addEventListener("click",this.hidden_suggest,false);
			
		}else{
			win.document.attachEvent("onclick",this.hidden_suggest);
			
		}
	}

	/***************************************************fill_div()*********************************************/
	//函数功能：动态填充div的内容，该div显示所有的提示内容
	//函数参数：allplat 一个字符串数组，包含了所有可能的提示内容
	this.fill_div = function(allplat){
		var msgplat = '';
		var all = '';
		var spell = '';
		var chinese = '';
		var platkeys = this.object.value;
        platkeys=this.ltrim(platkeys);
		if(!platkeys){
			msgplat += '<table class="hint" width="190"><tr align="left"><td class="tdleft" height="10" align="left">输入中文/拼音或&uarr;&darr;选择</td></tr></table><table width="190" class="mout" height="2"><tr><td></td></tr></table>';
			for(i=0;i<allplat.length;i++){
			    all=allplat[i].split(",");
				spell=all[0];
				chinese=all[1];
				szm=all[2];
				msgplat += '<table class="mout" width="190"><tr onclick="parentbject.add_input_text(\'' + chinese + '\',\'' + szm + '\')"><td class="tdleft" height="10" align="left">'+ spell +
				       '</td><td class="tdright" align="right">' + chinese + '</td><td style="display:none">' + szm + '</td></tr></table>';
			}
        }
		else {
			if(allplat.length < 1 || !allplat[0]){
				msgplat += '<table class="hint" width="190"><tr align="left"><td class="tdleft" height="10" align="left">对不起，找不到：'+platkeys+'</td></tr></table><table width="190" class="mout" height="2"><tr><td></td></tr></table>';

			}
			else{
			   msgplat += '<table class="hint" width="190"><tr align="left"><td class="tdleft" height="10" align="left">'+platkeys+'，按拼音排序</td></tr></table><table width="190" class="mout" height="2"><tr><td></td></tr></table>';
			   for(i=0;i<allplat.length;i++){
					all=allplat[i].split(",");
					spell=all[0];
					chinese=all[1];
					szm=all[2];
					msgplat += '<table class="mout" width="190"><tr onclick="parentbject.add_input_text(\'' + chinese + '\',\'' + szm + '\')"><td class="tdleft" height="10" align="left">'+ spell +
				       '</td><td class="tdright" align="right">' + chinese + '</td><td style="display:none">' + szm + '</td></tr></table>';
				}
			}
		}
		document.getElementById("getplatform").innerHTML =  msgplat;
		var nodes = document.getElementById("getplatform").childNodes;
		nodes[0].className = "hint";
		if(allplat.length >= 1 && allplat[0]){
			nodes[2].className = "selected";
		}
		//this.lastkeys_val = 0;
		for(var i=2;i<nodes.length;i++){
			nodes[i].onmouseover = function(){
				this.className = "mover";
				}
			nodes[i].onmouseout = function(){
				if(parentbject.lastkeys_val==(parentIndexOf(this)-2)){this.className = "selected";}
				else{this.className = "mout";}
			}
		}
		document.getElementById("getiframe").style.width = document.getElementById("getplatform").clientWidth+2;
        document.getElementById("getiframe").style.height = document.getElementById("getplatform").clientHeight+2;		
	}

	/***************************************************fix_div_coordinate*********************************************/
	//函数功能：控制提示div的位置，使之刚好出现在文本输入框的下面
	this.fix_div_coordinate = function(){
		var leftpos=0;
		var toppos=0;
		var testtmp=this.object.value;
		var testtmp1=this.object.id;
		aTag = this.object;
		do {
			if( aTag.offsetParent )
			{
			    aTag = aTag.offsetParent;
			}
			else
			{
			    leftpos += aTag.style.left;
			    toppos += aTag.style.top;
			    break;
			}
			leftpos	+= aTag.offsetLeft;
			toppos += aTag.offsetTop;
		}while(aTag.id!="mainbody");
		//alert("leftpos=["+leftpos+"]--toppos=["+toppos+"]--this.object.offsetTop=["+this.object.offsetTop+"]--this.object.offsetLeft=["+this.object.offsetLeft+"]--this.object.offsetHeight=["+this.object.offsetHeight+"]");
		document.getElementById("getiframe").style.width = this.object.offsetWidth + 'px';
		
		if(document.layers){
			document.getElementById("getiframe").style.left = this.object.offsetLeft	+ parseInt(leftpos) + "px";
			document.getElementById("getiframe").style.top = this.object.offsetTop +	parseInt(toppos) + this.object.offsetHeight + 2 + "px";
		}else{
			document.getElementById("getiframe").style.left =this.object.offsetLeft	+ parseInt(leftpos)  +"px";
			document.getElementById("getiframe").style.top = this.object.offsetTop +	parseInt(toppos) + this.object.offsetHeight + 2 + "px";
		}
		//document.getElementById("getplatform").style.width = this.object.offsetWidth + 'px';
		//document.getElementById("getiframe").style.width= this.object.offsetWidth + 'px';
		if(document.layers){
			document.getElementById("getplatform").style.left = this.object.offsetLeft	+ parseInt(leftpos) + "px";
			document.getElementById("getplatform").style.top = this.object.offsetTop +	parseInt(toppos) + this.object.offsetHeight + 2 + "px";
		}else{
			document.getElementById("getplatform").style.left =this.object.offsetLeft	+ parseInt(leftpos)  +"px";
			document.getElementById("getplatform").style.top = this.object.offsetTop +	parseInt(toppos) + this.object.offsetHeight + 2 + "px";
		}
		//alert("getiframe.left=["+document.getElementById("getiframe").style.left+"]--getiframe.top=["+document.getElementById("getiframe").style.top+"]--getplatform.left=["+document.getElementById("getplatform").style.left+"]--getplatform.top=["+document.getElementById("getplatform").style.top+"]");
	}

    /***************************************************hidden_suggest*********************************************/
	//函数功能：隐藏提示框
	this.hidden_suggest = function (){
		this.lastkeys_val = 0;		 
		document.getElementById("getiframe").style.visibility = "hidden";
		document.getElementById("getplatform").style.visibility = "hidden";
	}

	/***************************************************show_suggest*********************************************/
	//函数功能：显示提示框
	this.show_suggest = function (){
		document.getElementById("getiframe").style.visibility = "visible";
		document.getElementById("getplatform").style.visibility = "visible";
	}
	this.is_showsuggest= function (){
		if(document.getElementById("getplatform").style.visibility == "visible") return true;else return false;
	}

	this.sleep = function(n){
		var start=new Date().getTime(); //for opera only
		while(true) if(new Date().getTime()-start>n) break;
	}
	this.ltrim = function (strtext){
		return strtext.replace(/[\$&\|\^*%#@! ]+/, '');
	}

    /***************************************************add_input_text*********************************************/
	//函数功能：当用户选中时填充相应的城市名字

	this.add_input_text = function (keys,szm){
		 
		keys=this.ltrim(keys)
		this.object.value = keys;
		var id=this.object.id;		
		document.getElementById(this.id2.id).value = szm;
		document.getElementById(id).style.color="#000000";
		document.getElementById(id).value=keys;
     }

	/***************************************************keys_handleup*********************************************/
	//函数功能：用于处理当用户用向上的方向键选择内容时的事件
	this.keys_handleup = function (){
		if(this.lastkeys_val > 0) this.lastkeys_val--;
		var nodes = document.getElementById("getplatform").childNodes;
		if(this.lastkeys_val < 0) this.lastkeys_val = nodes.length-1;
		var b = 0;
		for(var i=2;i<nodes.length;i++){
			if(b == this.lastkeys_val){
				nodes[i].className = "selected";
				this.add_input_text(nodes[i].childNodes[0].childNodes[0].childNodes[1].innerHTML,nodes[i].childNodes[0].childNodes[0].childNodes[2].innerHTML);
			}else{
				nodes[i].className = "mout";
			}
			b++;
		}
	}

	/***************************************************keys_handledown*********************************************/
	//函数功能：用于处理当用户用向下的方向键选择内容时的事件
	this.keys_handledown = function (){
		
		this.lastkeys_val++;
		
		var nodes = document.getElementById("getplatform").childNodes;
		
		if(this.lastkeys_val >= nodes.length-2) {
			
			this.lastkeys_val--;
			return;
		}
		
		var b = 0;
		for(var i=2;i<nodes.length;i++){
			
			if(b == this.lastkeys_val){
				
				nodes[i].className = "selected";
				this.add_input_text(nodes[i].childNodes[0].childNodes[0].childNodes[1].innerHTML,nodes[i].childNodes[0].childNodes[0].childNodes[2].innerHTML);
			}else{
				nodes[i].className = "mout";
			}
			b++;
		}
	}

	this.ajaxac_getkeycode = function (e)
	{
		var code;
		if (!e) var e = window.event;
		if (e.keyCode) code = e.keyCode;
		else if (e.which) code = e.which;
		
		return code;
		
	}

	/***************************************************keys_enter*********************************************/
	//函数功能：用于处理当用户回车键选择内容时的事件
	this.keys_enter = function (){
		  
		var nodes = document.getElementById("getplatform").childNodes;
		for(var i=2;i<nodes.length;i++){
			if(nodes[i].className == "selected"){
				
			  this.add_input_text(nodes[i].childNodes[0].childNodes[0].childNodes[1].innerHTML,nodes[i].childNodes[0].childNodes[0].childNodes[2].innerHTML);
			}
		}
		this.hidden_suggest();
	}

 
function getEvent()
{
 if(document.all)    return window.event;//如果是ie
 func=getEvent.caller;
        while(func!=null){
            var arg0=func.arguments[0];
            if(arg0){if((arg0.constructor==Event || arg0.constructor ==MouseEvent) || (typeof(arg0)=="object" && arg0.preventDefault && arg0.stopPropagation)){return arg0;}            }
            func=func.caller;
        }
       return null;
}

    /***************************************************display*********************************************/
	//函数功能：入口函数，将提示层div显示出来
	//输入参数：object 当前输入所在的对象，如文本框
	//输入参数：e IE事件对象
	this.display = function (object,id2,e){
		this.object = document.getElementById(object);
		this.id2 = document.getElementById(id2);
		if(!document.getElementById("getplatform")) this.init_zhaobussuggest();
		e = e || window.event;
		//var e=getEvent();
		
		e.stopPropagation;
		e.cancelBubble = true;
		if (e.target) targ = e.target;  else if (e.srcElement) targ = e.srcElement;
		if (targ.nodeType == 3)  targ = targ.parentNode;

		var inputkeys = this.ajaxac_getkeycode(e);
		switch(inputkeys){
			case 38: //向上方向键
				this.keys_handleup(this.object.id);
			    return;break;
			case 40: //向下方向键
			  
				if(this.is_showsuggest()) this.keys_handledown(this.object.id); else this.show_suggest();
			    return;break;
			case 39: //向右方向键
				return;break;
			case 37: //向左方向键
				return;break;
			case 13: //对应回车键
			 
			    this.keys_enter();
			    return;break;
			case 18: //对应Alt键
				this.hidden_suggest();
			    return;break;
			case 27: //对应Esc键
				this.hidden_suggest();
			    return;break;
		}

		//object.value = this.ltrim(object.value);
		
		//if(object.value == this.lastinputstr) return;else this.lastinputstr = object.value;
		if(window.opera) this.sleep(100);//延迟0.1秒
		parentbject = this;
		if(this.taskid) window.clearTimeout(this.taskid);
        this.taskid=setTimeout("parentbject.localtext();" , this.delaySec)
		//this.taskid = setTimeout("parentbject.remoteurltext();" , this.delaySec);
		
	}

	//函数功能：从本地js数组中获取要填充到提示层div中的文本内容
	this.localtext = function(){
		var id=this.object.id;
        var suggestions="";
        suggestions=this.getSuggestionByName();
		suggestions=suggestions.substring(0,suggestions.length-1);

		parentbject.show_suggest();
		parentbject.fill_div(suggestions.split(';'));
		parentbject.fix_div_coordinate();
	}

	/***************************************************getSuggestionByName*********************************************/
	//函数功能：从本地js数组中获取要填充到提示层div中的城市名字
	this.getSuggestionByName = function(){
		platkeys = this.object.value;
		var str="";
        platkeys=this.ltrim(platkeys);
		if(!platkeys){
			for(i=0;i<commoncitys.length;i++){
				str+=commoncitys[i][2]+","+commoncitys[i][1]+","+commoncitys[i][0]+";";
			}
			return str;
        }
		else{
		   platkeys=platkeys.toUpperCase();
			for(i=0;i<citys.length;i++){
			    if(//this.getLeftStr(citys[i][0],platkeys.length).toUpperCase()==platkeys||
				   (citys[i][1].toUpperCase().indexOf(platkeys)!=-1)||
				   this.getLeftStr(citys[i][2],platkeys.length).toUpperCase()==platkeys||
				   this.getLeftStr(citys[i][3],platkeys.length).toUpperCase()==platkeys)
					str+=citys[i][2]+","+citys[i][1]+","+citys[i][0]+";";
			}
			return str;
		}
	}

	/***************************************************getLeftStr************* *************************************/
    //函数功能：得到左边的字符串
    this.getLeftStr = function(str,len){

        if(isNaN(len)||len==null){
            len = str.length;
        }
        else{
            if(parseInt(len)<0||parseInt(len)>str.length){
                len = str.length;
             }
        }
        return str.substr(0,len);
    }

	/***************************************************parentIndexOf************* *************************************/
    //函数功能：得到子结点在父结点的位置
	function parentIndexOf(node){
	  for (var i=0; i<node.parentNode.childNodes.length; i++){
			if(node==node.parentNode.childNodes[i]){return i;}
	  }
   }


}

function showSearch(obj,type){
	
	
    if(type==1){
        if(document.getElementById(obj).value==""){
			document.getElementById(obj).style.color="#C1C1C1";
			document.getElementById(obj).value="中文/拼音";
		}
    }else{
        if(document.getElementById(obj).value=="中文/拼音"){
			document.getElementById(obj).style.color="#000000";
            document.getElementById(obj).value="";
		}
    }
}


 
 var suggest = new city_suggest();
 function search(){
	if(!checkform()){
	   return;
	}
	InlandForm.submit();

}

function change_iframe(idname,urlcity){
	idname.location.href=urlcity;
}
//改变搜索框文字
function changetext(thisid){
	if(thisid == "search1"){
		commoncitys = commoncitysHotel;
		citys = citysHotel;
		document.getElementById("hCity").value = "中文/拼音";
	}else if(thisid == "search2"){
		commoncitys = commoncitysFlight;
		citys = citysFlight;
		document.getElementById("fromcity").value = "中文/拼音";
	}

	for(i=1; i<=3; i++)
	{
		var tdid="search"+i;
		document.getElementById(tdid).className="searchitem_b";
	}
	
	document.getElementById(thisid).className="searchitem_r";
}
<!-- 搜索框更换-->
function closeothers(thisid) {
  if (thisid.style.display=="") { 
	hotel.style.display="none";
	plane.style.display="none";
	pkg.style.display="none";
	
    thisid.style.display="";
  }
  else{
  thisid.style.display="";
  }
}