			// no-repeat -384px -297px;#background:url(./images/bg.jpg) no-repeat -384px -297px;(./images/bg.jpg) no-repeat -436px -297px;
			var aMonthDays = [31,28,31,30,31,30,31,31,30,31,30,31];
			window.onload=function(){
				var oDate = new Date();
				var iYear = oDate.getFullYear();
				var iMonth = oDate.getMonth();
				var iDay = oDate.getDate();
				var oCalendar = document.getElementById('calendar');
				var aTd = oCalendar.getElementsByTagName('td');
				var oBtnPre = document.getElementById('btnPre');
				var oBtnNext = document.getElementById('btnNext');
				var oTime = document.getElementById('time');
				var aSelect = oTime.getElementsByTagName('select');
				var aSpan = oTime.getElementsByTagName('span');
				// 1  知道本月第一天是星期几
				// 2  知道本月有多天
				// 3 知道上月有多天
				
				/**
				 * 点击选择年份，月份
				 */
				for(var i=0;i<aSpan.length;i++){
					aSpan[i].index = i;
					//点击显示下拉列表
					aSpan[i].onclick=function(){
						var index = this.index;
						aSelect[index].style.display = 'block';
						//选择日期，切换日历
						aSelect[index].onchange=function(){
							if(index == 0){
								iYear = Number(this.value);
							}else{
								iMonth = Number(this.value);
							}
							this.style.display = 'none';
							loadDate();
						}	
					}
				}
				createOption()		//创建选项
				loadDate();
				function loadDate(){
					//当月份小于一月
					if(iMonth<0){
						iMonth = 11;
						iYear--;
					}
					//当月份大于十二月
					if(iMonth>11){
						iMonth =0;
						iYear++;
					}
					var iWeek = new Date(iYear,iMonth,1).getDay();	//获得本月第一天是星期几
					var iCurDays = getMonthDays(iYear,iMonth);	//获得本月天数
					var iPreDays = getMonthDays(iYear,iMonth-1);	//获得上月天数
					document.getElementById('yearNum').innerHTML = 	iYear;
					document.getElementById('monthNum').innerHTML = iMonth+1;		
					var data = [];
					clearClass();		//清除类名
					/**
					 * 压入上一月的数据
					 */
					 if(iWeek==0){
						iWeek=7;
					 }
					for(var i=iWeek-2;i>=0;i--){		
						data.push('<span>'+(iPreDays-i)+'</span>')
					}
					/**
					 * 压入本月的数据
					 */
					for(var i=1;i<=iCurDays;i++){
						if(iDay == i){
							aTd[data.length].className = 'active';
						}
						data.push(i);
					
					}
					/**
					 * 压入下月的数据
					 */
					var iLackNum =  aTd.length - data.length;
					for(var i=1;i<=iLackNum;i++){
						data.push('<span>'+i+'</span>');
					}
					/**
					 * 加载数据
					 */
					 var y=document.getElementById('yearNum').innerHTML; //年
					 var m=document.getElementById('monthNum').innerHTML; //月
					for(var i=0;i<data.length;i++){
						aTd[i].innerHTML = data[i];
						aTd[i].index = i;
						
						if(daydatas){
							//alert(daydatas.length);
							for( var j=0;j< daydatas.length; j++){
								oneday = daydatas[j].day.split("|")[0];   
								money = daydatas[j].day.split("|")[1];   
								ye = oneday.split("-")[0];
								mo = oneday.split("-")[1];
								da = oneday.split("-")[2];
								
								 if(mo.split('')[0]==0){
									mo=mo.split('')[1];
								} 
								if(da.split('')[0]==0){
									var da=da.split('')[1];
								}
								/*if(data[i].indexOf("span")){
									var datai = data[i].split("<span>")[1];
									var dataI = datai.split("</span>")[0];
								}else{
									var dataI =data[i];
								}*/
								
								//alert('年:'+y+'月:'+m+'日:'+data[i]+'\n'+'年:'+ye+'月:'+mo+'日:'+da);
								
								if(y==ye && m==mo && data[i] ==da){
									
									if(mo.length<2){
										mo=0+mo;
									}
										
									aTd[i].innerHTML = '<span id="dhx_month_head">'+da+'</span><br/><span id="dhx_month_body">'+money+'</span><input type="hidden" name="daymoney['+y+mo+da+']" value="'+money+'">';
								}
							} 
						}
						
					
						aTd[i].ondblclick=function(){
								
							d= this.innerHTML; //日
							var firstnode= this.firstChild.innerHTML;
							if(!firstnode){
								firstnode=d;  //日
							}
							var lastnode= this.childNodes[2];
							if(lastnode){
								money= lastnode.innerHTML;
								oldmoney=money;
							}
							var money= prompt("请输入价格",money);
							var reg = new RegExp("^[0-9]*$");
							 if(!reg.test(money)){
								alert("请输入数字!");
								money=oldmoney;
							}
							//var y=document.getElementById('yearNum').innerHTML; //年
							//var m=document.getElementById('monthNum').innerHTML; //月
							if(m.length<2){
								m=0+m;
							}
							if(firstnode.length<2){
								day=0+firstnode;
							}else{
								day=firstnode
							}
							
							this.innerHTML = '<span id="dhx_month_head">'+firstnode+'</span><br/><span id="dhx_month_body">'+money+'</span><input type="hidden" name="daymoney['+y+m+day+']" value="'+money+'">';
							//this.innerHTML = '<span id="dhx_month_head">'+firstnode+'</span><br/><span id="dhx_month_body">'+money+'</span><input type="hidden" name="daymoney[]" value="'+y+m+day+'|'+money+'">';
						}
						//点击切换
						aTd[i].onclick=function(){
							clearClass();
							iDay = this.innerHTML.match(/\d+/);
							//点击切换到上一月
							if(this.index <iWeek-1){
								iMonth--;
								loadDate();
								//点击切换到下一月
							}else if(this.index>iCurDays+iWeek-2){
								iMonth++;
								loadDate();
							}else{
								this.className = 'active';
							}
							//隐藏select
							for(var n=0;n<aSelect.length;n++){
								aSelect[n].style.display = 'none';
							}
						}
						
						//移入移出添加删除类名
						aTd[i].onmouseover=function(){
							for(var n=0;n<aTd.length;n++){
								aTd[n].className = aTd[n].className.replace('hover','');
							}
							this.className +=' hover';
						}
						aTd[i].onmouseout=function(){
							for(var n=0;n<aTd.length;n++){
								aTd[n].className = aTd[n].className.replace('hover','');
							}
						}
					}
				}
				/**
				 * 切换到上一月
				 */
				oBtnPre.onclick=function(){
					iMonth--;
					loadDate();
				}
				/**
				 * 切换到下一月
				 */
				oBtnNext.onclick=function(){
					iMonth++;
					
					loadDate();
				}
				
				/**
				 * 清除类名
				 */
				function clearClass(){
					for(var i=0;i<aTd.length;i++){
						aTd[i].className = '';
					}
				}
				/***
				 * 
				 *	创建选项
				 */
				function createOption(){
					//创建年份的下拉列表
					for(var i=2010;i<=2050;i++){
						var oOption = document.createElement('option');
						oOption.innerHTML = i+'年';
						oOption.value = i;
						//把年份对应选项修改选中状态
						if(i==iYear){
							oOption.selected = true;
						}
						aSelect[0].appendChild(oOption);
					}
					//创建月份的下拉列表
					for(var i=0;i<12;i++){
						var oOption = document.createElement('option');
						oOption.innerHTML = (i+1)+'月';
						oOption.value = i;
						aSelect[1].appendChild(oOption);
					}	
				}
			}									
			//2013年5月
			/***
			 * 获取每月的天数
			 */
			function getMonthDays(y,m){
				if(m<0){
					m=11;
				}
				if(m>11){
					m=0;
				}
				if(m == 1){
					return ((y%4 == 0 && y%100!=0) || y%400 ==0)?29:28;
				}else{
					return aMonthDays[m];
				}
			}
		 	function next(){
				$("#step").html('<input type="hidden" name="step" value="3"/>');
				$('form').submit();
			}
			