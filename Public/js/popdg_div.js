var tr01_01 = "#ff0000"; //第一行背景颜色
var tr01_02 = "#cccccc"; //第二行背景颜色
var tr01_03 = "#FFFFFF";
//var tab01 = "#656565";  //边框颜色
var tab01 = "#2F7495";  //边框颜色
var kongj;
var kongj_code;
var clicObj;

//tile
var style1 = 'style="color: #656565;BACKGROUND-COLOR:#e7f1fd;font-size: 9pt;"';

//title:城市拼音首字母
var style2 = 'style="BACKGROUND-COLOR: #c8e3fc; color: #656565;background-repeat:repeat-x; background-position:bottom; font-size: 9pt;"';

var style3 = 'style="position: relative; left: 0px; top: 0px; width: 100%;overflow:hidden; text-overflow:ellipsis;"';

//北京|PEK|B  城市|三字代码|拼音首字母
var shcs = new Array("深圳|SZX|S","北京|PEK|B","上海|SHA|S","天津|TSN|T","成都|CTU|C","重庆|CKG|C","太原|TYN|T","呼和浩特|HET|H","沈阳|SHE|S","长春|CGQ|C","哈尔滨|HRB|H","南京|NKG|N","杭州|HGH|H","合肥|HFE|H","福州|FOC|F","南昌|KHN|N","济南|TNA|J","郑州|CGO|Z","武汉|WUH|W","长沙|CSX|C","广州|CAN|G","南宁|NNG|N","海口|HAK|H","贵阳|KWE|G","昆明|KMG|K","西安|SIA|X","兰州|LHW|L","银川|INC|Y","西宁|XNN|X","乌鲁木齐|URC|W","石家庄|SJW|S","拉萨|LXA|L");
var qtcs = new Array("阿勒泰|AAT|A","安庆|AQG|A","阿克苏|AKU|A","安康|AKA|A","鞍山|IOB|A","保山|BSD|B","包头|BAV|B","北海|BHY|B","北京|PEK|B","长沙|CSX|C","长春|CGQ|C","常德|CGD|C","长治|CIH|C","常州|CZX|C","昌都|BPX|C","朝阳|CHG|C","成都|CTU|C","赤峰|CIF|C","重庆|CKG|C","敦煌|DNH|D","大理|DLU|D","大连|DLC|D","大同|DAT|D","丹东|DDG|D","达县|DAX|D","德宏芒市|LUM|D","东营|DOY|D","恩施|ENH|E","鄂尔多斯|DSN|E","阜阳|FUG|F","福州|FOC|F","赣州|KOW|G","格尔木|GOQ|G","广元|GYS|G","广州|CAN|G","广汉|GHN|G","桂林|KWL|G","贵阳|KWE|G","怀化|HJJ|H","邯郸|HDG|H","哈尔滨|HRB|H","海口|HAK|H","海拉尔|HLD|H","哈密|HMI|H","汉中|HZG|H","杭州|HGH|H","合肥|HFE|H","衡阳|HNY|H","和田|HTN|H","呼和浩特|HET|H","黄山|TXN|H","黄岩|HYN|H","惠阳|AHE|H","黑河|HEK|H","吉林|JIL|J","济南|TNA|J","济宁|JNG|J","吉安|KNC|J","锦州|JNZ|J","景德镇|JDZ|J","荆州、沙市|SHS|J","九江|JIU|J","九寨沟|JZH|J","佳木斯|JMU|J","库尔勒|KRL|K","库车|KCA|K","喀什|KHG|K","克拉玛依|KRY|K","昆明|KMG|K","喀纳斯|KJI|K","拉萨|LXA|L","兰州|LHW|L","连云港|LYG|L","梁平|LIA|L","临沂|LYI|L","临沧|LNJ|L","柳州|LZH|L","泸州|LZO|L","洛阳|LYA|L","满州里|NZH|M","梅县|MXZ|M","绵阳|MIG|M","牡丹江|MDG|M","南京|NKG|N","南昌|KHN|N","南充|NAO|N","南宁|NNG|N","南通|NTG|N","南阳|NNY|N","宁波|NGB|N","那拉提|NLT|N","鄂尔多斯|DSN|O","攀枝花|PZI|P","齐齐哈尔|NDG|Q","且末|IQM|Q","秦皇岛|SHP|Q","青岛|TAO|Q","庆阳|IQN|Q","衢州|JUZ|Q","泉州晋江|JJN|Q","三亚|SYX|S","汕头|SWA|S","上海|SHA|S","鄯善|SXJ|S","韶关|SHG|S","沈阳|SHE|S","深圳|SZX|S","石家庄|SJW|S","思茅|SYM|S","塔城|TCG|T","太原|TYN|T","天津|TSN|T","铜仁|TEN|T","万州|WXN|W","潍坊|WEF|W","威海|WEH|W","温州|WNZ|W","武汉|WUH|W","乌鲁木齐|URC|W","武夷山|WUS|W","无锡|WUX|W","梧州|WUZ|W","文山|WNH|W","锡林浩特|XIL|X","西安|SIA|X","香格里拉|DIG|X","西宁|XNN|X","西双版纳|JHG|X","厦门|XMN|X","襄樊|XFN|X","西昌|XIC|X","兴义|ACX|X","徐州|XUZ|X","盐城|YNZ|Y","宜昌|YIH|Y","银川|INC|Y","运城|YCU|Y","永州|LLF|Y","延吉|YNJ|Y","烟台|YNT|Y","延安|ENY|Y","宜宾|YBP|Y","义乌|YIW|Y","伊宁|YIN|Y","榆林|UYN|Y","湛江|ZHA|Z","张家界|DYG|Z","昭通|ZAT|Z","郑州|CGO|Z","舟山|HSN|Z","珠海|ZUH|Z","遵义|ZYI|Z","丽江|LJG|L","漠河|OHE|M");
var ywzm = new Array("A","B","C","D","E","F","G","H","J","K","L","M","N","O","P","Q","S","T","W","X","Y","Z") 
var popup_gd=25;  //数据显示高度
var popup_i; 
var popup_int0=0;
var popup_int1=0;
var popup_int2=0;
var popup_int3=0;

function Split(popup_str,popup_n,popup_s){ //字符串,取第几个数据,分割字符
	var popup_split=popup_str.split(popup_s);
	return popup_split[popup_n];
}

var objPopup;//城市展示容器层

function popUp(abc,code){
	
    if(typeof(abc)=='string')
    {
    	kongj=document.getElementById(abc);
    	kongj_code=document.getElementById(code);
    }
    else
    {
    	kongj=abc;
    	kongj_code=code;
    }
    
	var objBody = document.getElementById("mainbody");
	if( !document.getElementById("city_popup") )
	{
	    objPopup = document.createElement("div");
	}
	
	objPopup.style.visibility="hidden";
	objPopup.className="popdiv"; 
    
    kongj.value="";
    
    popup_int0=0;
    popup_int1=0;
    popup_int2=0;
    popup_int3=0;
    var tab;
    tab = '<div id="_div_tmp" style="width:25px; height:15px; position:absolute; font-size:14px; font-weight:bold; top: 32px; cursor:pointer; background-color:#FFD373; display:none" align="center">1</div>';
    tab+= '<table width="290" border="0" cellpadding="0" cellspacing="1" bgcolor="' +tab01+ '">';
    tab+= '<tr>';
    tab+= '<td>';
    tab+= '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color:' +tr01_02+ ';">';
    tab+= '  <tr align="center">';
    tab+= '    <td height="22"'+style1+'>请选择城市或输入城市名称的拼音</td>';
    tab+= '  </tr>';
    tab+= '  <tr align="left">';
    tab+= '    <td height="20"'+style2+'>';
    tab+= '      <table width="100%" align="left" border="0" cellspacing="0" cellpadding="0" style="font-size: 9pt"><tr><td width="2"></td><td><table width="100%" border="0" cellpadding="0" cellspacing="0" style="font-size: 10pt"><tr align="left">';
    tab+= '        <td id="H_SH" width="13%"><label id="SH" style="color:#656565; cursor:hand">省会</label>|</td>';
    for(var ywzm_i=0;ywzm_i<26;ywzm_i++){
    	if(!ywzm[ywzm_i])break;
    	tab+= '<td id=H_"'+ywzm[ywzm_i]+'"><label id="syy_'+ywzm[ywzm_i]+'" style="color:#656565; cursor:hand" >'+ywzm[ywzm_i]+'</label></td>';
    }
    
    tab+= '      </tr></table></td></tr></table>';
    tab+= '    </td>';
    tab+= '  </tr>';
    tab+= '  <tr align="center">';
    tab+= '   <td id="Popup_Tab">';
    tab+= '    <table width="100%" border="0" cellspacing="1" cellpadding="0" style="font-size: 9pt">';
    
    for(popup_i=0;popup_i<36;popup_i++){
    	if(popup_i==0 || popup_i % 6 ==0){
    		tab+= '  <tr align="center" style="background-color:' +tr01_03+ ';">';
    	}
    	if(shcs[popup_i]){
    		tab+= '    <td height="'+popup_gd+'" width="14.3%" ID="popup_td_cszm_'+popup_int0+'" title="'+Split(shcs[popup_i],0,"|")+'" style="font-size: 9pt;color: #21407D"><NOBR '+style3+' ID="popup_NOBR_cszm_'+Split(shcs[popup_i],1,"|")+'_'+popup_int0+'">'+Split(shcs[popup_i],0,"|")+'</NOBR></td>';
    		popup_int0++;
    	}else{
    		tab+= '    <td height="'+popup_gd+'" width="14.3%">&nbsp;</td>';
    	}
    	popup_int3 = popup_i+1;
    	if(popup_int3 % 6 ==0){
    		tab+= '  </tr>';
    	}
    }
    
    tab+= '    </table>';
    tab+= '   </td>';
    tab+= '  </tr>';
    tab+= '</table>';
    tab+= '</td>';
    tab+= '</tr>';
    tab+= '</table>';

	objPopup.innerHTML = tab;
    objPopup.onclick = Htc_OnClick;
    objPopup.onmousemove = Htc_onmousemove;
   
	objPopup.setAttribute('id','city_popup');
    objPopup.setAttribute('width','290px');
    objPopup.setAttribute('height','202px');
    objPopup.style.zIndex="100000";
	objBody.appendChild(objPopup);
	fix_div_coordinate(kongj);
	objPopup.style.visibility="visible"; 
	
	objPopup.style.position="absolute"; 
	
}

function fix_div_coordinate(obj)
{
		var leftpos=0;
		var toppos=0;
        aTag = obj;
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
        //alert("leftpos=["+leftpos+"]--toppos=["+toppos+"]--obj.offsetTop=["+obj.offsetTop+"]--obj.offsetLeft=["+obj.offsetLeft+"]--obj.offsetHeight=["+obj.offsetHeight+"]");
		if(document.layers){
			document.getElementById("city_popup").style.left = obj.offsetLeft	+ parseInt(leftpos) + "px";
			document.getElementById("city_popup").style.top = obj.offsetTop +	parseInt(toppos) + obj.offsetHeight + 2 + "px";
		}else{
			document.getElementById("city_popup").style.left =obj.offsetLeft	+ parseInt(leftpos)  +"px";
			document.getElementById("city_popup").style.top = obj.offsetTop +	parseInt(toppos) + obj.offsetHeight + 2 + "px";
		}
		//alert("left=["+document.getElementById("city_popup").style.left+"] top=["+document.getElementById("city_popup").style.top+"]");
}

function popup_hide()
{
    if( objPopup.style.visibility == "visible" )objPopup.style.visibility="hidden";
}

function Htc_OnClick(){  //鼠标点击事件
  var Htc_str;
  var obj=document.parentWindow||document.defaultView;
  var e =obj.GetEvent().srcElement||obj.GetEvent().target;
  
  clicObj= e;
  if (e.tagName == "LABEL")  {
	  if(e.id!=""){
		  yc_dt(e.id);
	  }
  }
  if (e.tagName == "NOBR")  {
	  f_z(document.getElementById(e.id).innerHTML);
	  objPopup.style.visibility="hidden"; 
  }
  
  if (e.tagName == "TD"){//H_
	  if(e.id!=""){
		  yc_dt(Split(e.id,1,"_"));
	  }
  }
  if (e.tagName == "DIV") {
	  if(e.innerHTML != "") yc_dt(e.innerHTML);
  }
}

//  获取event对象的一个方法，兼容IE、FF
function GetEvent(obj)
{
   if(document.all) // IE
   {
       return window.event;
   }

   func = GetEvent.caller; // 返回调用本函数的函数
   while(func != null)
   {
       // Firefox 中一个隐含的对象 arguments，第一个参数为 event 对象 
       var arg0 = func.arguments[0];
       //alert('参数长度：' + func.arguments.length);
       if(arg0)
       {
           if((arg0.constructor == Event || arg0.constructor == MouseEvent)
               ||(typeof(arg0) == "object" && arg0.preventDefault && arg0.stopPropagation))
           {
               return arg0;
           }
       }
       func = func.caller;
   }
   return null;
}

var ll="popup_td_cszm_0";
function Htc_onmousemove(){   //鼠标移动事件
  var obj=document.parentWindow||document.defaultView;
  var e =obj.GetEvent().srcElement||obj.GetEvent().target;
  if (e.tagName == "TD" && e.id.split("_")[0]!="H")  {
	  if(e.id!=""){turnrowcolor(e.id,ll);MYopoupmovOut();}
  }
  if (e.tagName == "NOBR")  {
	  if(e.id!=""){turnrowcolor("popup_td_cszm_"+Split(e.id,4,"_"),ll);MYopoupmovOut();}
  }
  if (e.tagName == "LABEL") {
	  //MYopoupmov(e.id);
  }
}

function turnrowcolor(ss,ls){   //鼠标移动TD背景颜色
  var bc="#e7f1fd";
  if(document.getElementById(ls))document.getElementById(ls).style.backgroundColor="";
  if(document.getElementById(ss))document.getElementById(ss).style.backgroundColor=bc;
  if(document.getElementById(ss))document.getElementById(ss).style.cursor="hand";
  ll=ss;	
}

var yc_dt_cs="SH";
function yc_dt(int){ 
	if(int!=yc_dt_cs){
		if(int.length=3)int = int.replace(/"/g,"");
		if(document.getElementById("syy_" + int))document.getElementById("syy_" + int).style.color="#21407D";
		if(document.getElementById("syy_" + yc_dt_cs))document.getElementById("syy_" + yc_dt_cs).style.color="#21407D";
		yc_dt_cs=int;
		if(int=="SH" || int=="省会"){
			Popup_tab(int,1);
		}else{
			Popup_tab(int,0);
		}
	}
}

function Popup_tab(str,lx){ //生成数据
	var Popup_dat_i=0;
	var Popup_dat_n;
	var Popup_dat_tab="";
	var Popup_dat =new Array();
	
	if( Split(str,0,'_')=="syy" )str=Split(str,1,'_');

	if(lx==0){
		for(Popup_dat_n=0;Popup_dat_n<qtcs.length;Popup_dat_n++){
			if(Split(qtcs[Popup_dat_n],2,"|")==str){
				Popup_dat[Popup_dat_i++]=qtcs[Popup_dat_n];
			}
		}
	}else{
		Popup_dat=shcs;
	}
	popup_int0=0;
	
	Popup_dat_tab+= '<table width="100%" border="0" cellspacing="1" cellpadding="0" style="font-size: 9pt;color: #21407D">';
	for(Popup_dat_n=0;Popup_dat_n<36;Popup_dat_n++){
		if(Popup_dat_n==0 || Popup_dat_n % 6 ==0){
			Popup_dat_tab+= '  <tr align="center" style="background-color:' +tr01_03+ ';">';
		}
		if(Popup_dat[Popup_dat_n]){
			
			Popup_dat_tab+= '    <td height="'+popup_gd+'" width="14.3%" ID="popup_td_cszm_'+popup_int0+'" title="'+Split(Popup_dat[Popup_dat_n],0,"|")+'" style="font-size: 9pt;color: #21407D"><NOBR '+style3+' ID="popup_NOBR_cszm_'+Split(Popup_dat[Popup_dat_n],1,"|")+'_'+popup_int0+'">'+Split(Popup_dat[Popup_dat_n],0,"|")+'</NOBR></td>';
			popup_int0++;
		}else{
			Popup_dat_tab+= '    <td height="'+popup_gd+'" width="14.3%">&nbsp;</td>';
		}
		popup_int3 = Popup_dat_n+1;
		if(popup_int3 % 6 ==0){
			Popup_dat_tab+= '  </tr>';
		}
	}
	Popup_dat_tab+= '    </table>';
	document.getElementById("Popup_Tab").innerHTML=Popup_dat_tab;
}

function f_z(temp){   //赋值给控件
  kongj.value=temp;
  setCodevalue(temp);
  kongj.style.color="#000000";
}
function setCodevalue(temp){
	var length=qtcs.length;
	
	for(i=0;i<length;i++){
		var tempArray=qtcs[i].split("|");
		if(tempArray[0]==temp){
			//kongj.codevalue=tempArray[1];
			kongj_code.value=tempArray[1];
			break;
		}
	}
}

function MYopoupmov(obj){
	var _div = document.getElementById("_div_tmp");
	obj = document.getElementById(obj);

	var leftpos=0;
	var toppos=0;
    aTag = obj;
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
	if(document.layers){
		CitySelectnewX = obj.offsetLeft	+ parseInt(leftpos) - 7 + "px";
		CitySelectnewY = obj.offsetTop +	parseInt(toppos) + obj.offsetHeight - 16 + "px";
	}else{
		CitySelectnewX =obj.offsetLeft	+ parseInt(leftpos) - 7 +"px";
		CitySelectnewY = obj.offsetTop +	parseInt(toppos) + obj.offsetHeight - 16 + "px";
	}

	var CitySelectneww = obj.offsetWidth + 14;

	_div.style.left  = CitySelectnewX;
	_div.style.top   = CitySelectnewY;
	_div.style.width = CitySelectneww + "px";
	_div.innerHTML = obj.innerHTML;
	_div.style.display = "";
}

function MYopoupmovOut(){
	if(document.getElementById("_div_tmp").style.display=="")document.getElementById("_div_tmp").style.display = "none";
}

var posLib = {
    getClientLeft:function (el) {
      var r = el.getBoundingClientRect();
      return r.left - this.getBorderLeftWidth(this.getCanvasElement(el));
    },

    getClientTop:    function (el) {
      var r = el.getBoundingClientRect();
      return r.top - this.getBorderTopWidth(this.getCanvasElement(el));
    },

    getLeft:    function (el) {
      return this.getClientLeft(el) + this.getCanvasElement(el).scrollLeft;
    },

    getTop:    function (el) {
      return this.getClientTop(el) + this.getCanvasElement(el).scrollTop;
    },

    getInnerLeft:    function (el) {
      return this.getLeft(el) + this.getBorderLeftWidth(el);
    },

    getInnerTop:    function (el) {
      return this.getTop(el) + this.getBorderTopWidth(el);
    },

    getWidth:    function (el) {
      return el.offsetWidth;
    },

    getHeight:    function (el) {
      return el.offsetHeight;
    },

    getCanvasElement:    function (el) {
      var doc = el.ownerDocument || el.document;    // IE55 bug
      if (doc.compatMode == "CSS1Compat")
        return doc.documentElement;
      else
        return doc.body;
    },

    getBorderLeftWidth:    function (el) {
      return el.clientLeft;
    },

    getBorderTopWidth:    function (el) {
      return el.clientTop;
    },

    getScreenLeft:    function (el) {
      var doc = el.ownerDocument || el.document;    // IE55 bug
      var w = doc.parentWindow;
      return w.screenLeft + this.getBorderLeftWidth(this.getCanvasElement(el)) + this.getClientLeft(el);
    },

    getScreenTop:    function (el) {
      var doc = el.ownerDocument || el.document;    // IE55 bug
      var w = doc.parentWindow;
      return w.screenTop  + this.getClientTop(el);//+ this.getBorderTopWidth(this.getCanvasElement(el))
    }
  }
  
document.onclick=function(e)
{
    e = window.event || e;
    var srcElement = e.srcElement || e.target;
    /**********时间选择窗口操作**********/
    /**********时间选择窗口操作**********/
    if( document.getElementById("city_popup") )
    {
        if((objPopup.style.display=="" || objPopup.style.visibility=="visible") && srcElement!=kongj && srcElement!=objPopup && srcElement!=clicObj){
            if( objPopup.style )objPopup.style.visibility="hidden";
        }
    }
}