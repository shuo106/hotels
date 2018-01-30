<?php
function getCity(){

    $area =M('area');//区域表
   
    if(!S('all')){


    $allArea = $area->where('level =2')->order('first asc')->select();  //全部城市
    $allHot  = $area->where('level =2 and ishot =1 ')->order('first asc')->select();  //热门城市



    $hot=''; //热门  var hot="@80|北京@321|上海";


    $Zm='ABCDEFGHIJKLMNOPQRSTUVWXYZ';

      $zmArr=array();

      for($i=0;$i<26;$i+=3){

        $zmArr[]=substr($Zm,$i,3);

      }
      
      //热门城市
      

       foreach($allHot as $k=>$v){
           $hot.='@'.$v['id'].'|'.$v['name'];
      }

  
      //所有城市
        foreach($zmArr as $z){
            foreach($allArea as $v)
              if(strpos('*'.$z,$v['first'])){ 
                $all[$z].='@'.$v['id'].'|'.$v['name'];
              }
        }

      // 缓存全部城市
      S('all',$all,25920000);
      //缓存热门
      S('hot',$hot,25920000);
     
   }


  
  
    //调用所有城市
    $city['allzm']=S('all');
    $city['hot']  =S('hot');

    return  $city;
  
}
	function getCityTwo(){
		$hotel=M('member_hotel');//酒店表
		$area =M('area');//区域表
		//全部城市
		//字母 'DEF':"@64|定西@78|东莞@125|定安县@126|东方@138|儋州@168|大庆@169|大兴安岭@248|大连@249|丹东@284|德州@285|东营@301|大同@325|达州@326|德阳@369|大理@370|德宏@371|迪庆@181|鄂州@182|恩施@263|鄂尔多斯@41|阜阳@54|福州@79|佛山@101|防城港@234|抚州@250|抚顺@251|阜新",
		$allArea = $area->where('level =2')->order('first asc')->select();
		if(!S('all')){
			$hot=''; //热门  var hot="@80|北京@321|上海";
			$hot2=''; //提示热门  //@Beijing|北京|53
			$allPinYin=''; //全部拼音  "@Anqing|安庆|36|
			$hotList=array();
			$Zm='ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$zmArr=array();
			for($i=0;$i<26;$i+=3){
				$zmArr[]=substr($Zm,$i,3);
			}
			$i=0;
			foreach($allArea as $k=>$v){
				$cou=array();
				$cou['is_delete']=0;
				$cou['city']=array('like','%'.$v['id'].'%');
				$cityCount = $hotel->where($cou)->count();
				if($cityCount>0){
					if($i<15&&$v['ishot']==1){
						$hotList[]=$v;
						$i++;
						$hot.='@'.$v['id'].'|'.$v['name'];
						$hot2.='@'.$v['Pinyin'].' '.$cityCount.' 家|'.$v['name'].'|'.$v['id'];
					}
					$allPinYin.='@'.$v['Pinyin'].' '.$cityCount.' 家|'.$v['name'].'|'.$v['id'].'|';
				}
			}
			$all=array();
			foreach($zmArr as $z){
				foreach($allArea as $v){
					$cou=array();
					$cou['is_delete']=0;
					$cou['city']=array('like','%'.$v['id'].'%');
					$cityCount = $hotel->where($cou)->count();
					if(strpos('*'.$z,$v['first']) && $cityCount>0){ 
						$all[$z].='@'.$v['id'].'|'.$v['name'];
					}
				}
			}
			// 缓存字母
			S('all',$all,25920000);
			//缓存全部拼音
			S('allPY',$allPinYin,25920000);
			//缓存热门
			S('hot',$hot,25920000);
			//缓存热门提示
			S('hot2',$hot2,25920000);
			//缓存热门城市列表
			S('hotList',$hotList);
		}
		$city=array();
		$city['allzm']=S('all');
		$city['allPY']=S('allPY');
		$city['hot']  =S('hot');
		$city['hot2'] =S('hot2');
		return $city;
	}
	 //城市
 	function  getArtName($id){
	$cate= M('cate');
	$arr= $cate->where('id='.$id)->find();
	if($arr['pid']==0){
	echo $arr['name'];
	}else{
	$arr =$cate->where('id='.$arr['pid'])->find();
	echo $arr['name'];
	}
	
 }

 /**
 * 第三方登录 调用java API
 * @param   array       用户信息
 */

function curl_request($url, $post_data)
{
    $ch = curl_init();
    // 设置url
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
    // 设置获取信息以文件流形式返回，而不是直接输出
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //设置post数据

    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    // 执行命令
    $data = curl_exec($ch);
    // 关闭
    return json_decode($data);
}
?>