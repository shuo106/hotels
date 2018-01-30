		  var map = new BMap.Map("setmap");
		  var pp =new BMap.Point(city);    // 定义一个中心点坐标
		//   console.log(city);
		  map.centerAndZoom(city,13);
		//   map.centerAndZoom(new BMap.Point(116.404, 39.915), 11);
/* 		  setTimeout(function(){
			map.setZoom(14);
		  },2000); */
		  map.setCurrentCity(city); 
		  map.enableScrollWheelZoom(true);   
		  //var ctrl_nav = new BMap.NavigationControl({$config});   //定义向地图中添加缩放控件
		  //mapddControl(ctrl_nav);   //向地图中添加缩放控件
		  var point=new Array();     //存放标注点经纬信息的数组
		  var marker=new Array();    //存放标注点对象的数组
		  var info=[];       //存放提示信息窗口对象的数组
		for(var i=0;i<markerArr.length;i++){
			p0 = markerArr[i].point.split(",")[0];   
		　　p1 = markerArr[i].point.split(",")[1];        //按照原数组的point格式将地图点坐标的经纬度分别提出来
			point[i] = new BMap.Point(p0,p1);       	//循环生成新的地图点
			marker[i]=new BMap.Marker(point[i]);      //按照地图点坐标生成标记
			marker[i].setTitle(markerArr[i].title);               //为label添加鼠标提示
			var label = new BMap.Label(markerArr[i].title,{"offset":new BMap.Size(10,-15)});
			marker[i].setLabel(label);   
			map.addOverlay(marker[i]);               //在地图上循环添加标记
			var rooms ='';
			for(var r=0;r<markerArr[i].rooms.length;r++){
				if(r<3){
					rooms+="<div class='iw_poi_content' style=' line-height:25px;'>"+markerArr[i].rooms[r].roomtype+" &nbsp;&nbsp;￥"+markerArr[i].rooms[r].minprice+"   <a href='"+root+"/Hotels/order-"+markerArr[i].rooms[r].id+".html' style='color:orange; float:right; font-weight:bold'>预订</a></div>";
				}else{
					break;
				}
			}
			info[i] = new BMap.InfoWindow("<div class='iw_poi_content' style='padding-top:10px;'><img src='"+root+markerArr[i].src+"' width='400' heigth='200' /></div><div class='iw_poi_content' style=' line-height:30px; font-size:14px;font-weight:bold;'><a href='/Hotels/show-"+markerArr[i].id+".html' style='color:orange;'>酒店名称："+markerArr[i].title+"</a></div><div class='iw_poi_content' style=' line-height:25px;font-weight:bold'>"+markerArr[i].addr+"</div>"+rooms);
			info[i].setWidth(400); //设置宽度
			info[i].setHeight(450);  //设置高度
	   }
		 for(var k in info){
			marker[k].onclick=aaaaa;
		 }
		 function aaaaa(){
		 	   for(var k in marker){
		 	   	   if(this==marker[k]){
		 	   	   	 this.openInfoWindow(info[k]); 
		 	   	   }
		 	   }
		 }
