<?php
class AreaAction extends CommonAction{
	private $area; //区域表
	public function _initialize(){
		$this->area = M('area');
	}
	//区域列表
	public function index(){
		//$c=$this->getSonArea(0);
		$pid=$this->_get('id')?$this->_get('id'):0;
		if ($pid==100002 or $pid==100018 or $pid==100003 or $pid==100043 ) {
			$q=$this->getSonArea($pid);
			$c=$q[0]['son'];
			foreach ($c as $k => $v) {
				$c["$k"]['ppid']=$pid;
			}
		}else{
			$c=M('area')->where('pid='.$pid)->order('sort desc')->select();
			//如果没有ppid 怎么找？
			foreach ($c as $k => $v) {
				$c["$k"]['ppid']=M('area')->where('id='.$v['pid'])->getField('pid');
			}
		}
		
		//dump($c);die;
		$this->assign('lists',$c);
		$this->display();
	}
	//递归调用 省份
	public function getSonArea($pid=0){
		$map['pid']=$pid;
		$areas=$this->area->where($map)->order('sort desc')->select();
		foreach($areas as &$vo){
			$vo['son']=$this->area->where(array('pid'=>$vo['id']))->select();
			foreach($vo['son'] as &$v){
				$map2['pid']=$v['id'];
				$v['son']=$this->area->where($map2)->order('sort desc')->select();
			}
		}
		return $areas;
	}
	//调用 省份id
	public function getSonId($pid){
		$ids=array();
		$ids[]=$pid;
		$map=array();
		$map['pid']=$pid;
		$areas=$this->area->where($map)->field('id')->select();
		foreach($areas as $vo){
			$ids[]=$vo['id'];
			$map2=array();
			$map2['pid']=$vo['id'];
			$areas2=$this->area->where($map)->field('id')->select();
			if($areas2){
				foreach($areas2 as $v){
					$ids[]=$v['id'];
				}
			}
		}
		return $ids;
	}
	
	//区域添加 编辑
	public function add(){
		//详情
		if($this->_get('id')){
			$this->info=M('area')->where('id='.$this->_get('id'))->find();
		}
		if($this->_get('pid')){
			$info=M('area')->where('id='.$this->_get('pid'))->find();
			$this->pid=$info['id'];
			$this->level=$info['level']+1;
		}
		$this->display();
	}	
	//区域删除
	public function areaDel(){
		$map['id']=array('in',$this->getSonId($this->_get('id')));
		$res=$this->area->where($map)->delete();
		$res?$this->json():$this->json(0);
		
	}	
	//设为热门
	public function hot(){
		$hot=$this->_get('hot')?1:0;
		$res=M('area')->where('id='.$this->_get('id'))->setField('ishot',$hot);
		$res?$this->json():$this->json(0);
	}
	//区域添加或修改执行
	public function doAdd(){
		$id=$this->_post('id');
		$name =$this->_post('names');
		$level=$this->_post('level')?$this->_post('level'):1;
		$pid=$this->_post('pid')?$this->_post('pid'):0;
		$data['name']	=$name;
		if($pid){
			$data['pid']	=$pid;	//上级id
		}
		if($level){
			$data['level']	=$level;	//级别
		}
		$data['Pinyin']	=$Py=ucfirst($this->Pinyin($name));	//全拼
		$data['first']	=substr($Py,0,1);	//首字母
		$Ustr= iconv("UTF-8","gb2312", $name);	
		$str='';
		for($i=0;$i<strlen($Ustr);$i+=2){
			$gstr = iconv("gb2312","UTF-8", substr($Ustr,$i,2));
			$str.=substr(ucfirst($this->Pinyin($gstr)),0,1);
		}
		$data['search']	=$str;	//全部汉字首字母
		$data['sort']	=$this->_post('sort');	//排序
		if(!$id){
			$res= $this->area->add($data);
		}else{
			$map['id']=$id;
			$res=$this->area->where($map)->save($data);
		}
		$res ? $this->json(200, '操作成功', array('tabid' => "" . ',Area-index', 'closeCurrent' => true)) : $this->json(0);		
	}
	//更新排序
	public function upSort(){
		foreach($_POST['sort'] as $k=>$v){
			$data['sort']=$v;
			$data['id']= $k;
			$this->area->save($data);
		}
		$this->json();
		
	}
	//转换拼音
	function Pinyin($_String){
		$_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha".
		"|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|".
		"cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er".
		"|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui".
		"|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang".
		"|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang".
		"|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue".
		"|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne".
		"|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen".
		"|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang".
		"|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|".
		"she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|".
		"tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu".
		"|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you".
		"|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|".
		"zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";
		$_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990".
		"|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725".
		"|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263".
		"|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003".
		"|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697".
		"|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211".
		"|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922".
		"|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468".
		"|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664".
		"|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407".
		"|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959".
		"|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652".
		"|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369".
		"|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128".
		"|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914".
		"|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645".
		"|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149".
		"|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087".
		"|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658".
		"|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340".
		"|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888".
		"|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585".
		"|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847".
		"|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055".
		"|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780".
		"|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274".
		"|-10270|-10262|-10260|-10256|-10254";
		$_TDataKey = explode('|', $_DataKey);
		$_TDataValue = explode('|', $_DataValue);
		$_Data = (PHP_VERSION>='5.0') ? array_combine($_TDataKey, $_TDataValue) : $this->_Array_Combine($_TDataKey, $_TDataValue);
		arsort($_Data);
		reset($_Data);
		if($_Code != 'gb2312') $_String = $this->_U2_Utf8_Gb($_String);
		$_Res = '';
		for($i=0; $i<strlen($_String); $i++)
		{
		$_P = ord(substr($_String, $i, 1));
		if($_P>160) { $_Q = ord(substr($_String, ++$i, 1)); $_P = $_P*256 + $_Q - 65536; }
		$_Res .= $this->_Pinyin($_P, $_Data);
		}
		return preg_replace("/[^a-z0-9]*/", '', $_Res);
	}
	function _Pinyin($_Num, $_Data){
		if ($_Num>0 && $_Num<160 ) return chr($_Num);
			elseif($_Num<-20319 || $_Num>-10247) return '';
		else {
			foreach($_Data as $k=>$v){ if($v<=$_Num) break; }
			return $k;
		}
	}
	function _U2_Utf8_Gb($_C){
		$_String = '';
		if($_C < 0x80) $_String .= $_C;
		elseif($_C < 0x800)
		{
		$_String .= chr(0xC0 | $_C>>6);
		$_String .= chr(0x80 | $_C & 0x3F);
		}elseif($_C < 0x10000){
		$_String .= chr(0xE0 | $_C>>12);
		$_String .= chr(0x80 | $_C>>6 & 0x3F);
		$_String .= chr(0x80 | $_C & 0x3F);
		} elseif($_C < 0x200000) {
		$_String .= chr(0xF0 | $_C>>18);
		$_String .= chr(0x80 | $_C>>12 & 0x3F);
		$_String .= chr(0x80 | $_C>>6 & 0x3F);
		$_String .= chr(0x80 | $_C & 0x3F);
		}
		return iconv('UTF-8', 'GB2312', $_String);
	}
	function _Array_Combine($_Arr1, $_Arr2){
		for($i=0; $i<count($_Arr1); $i++) $_Res[$_Arr1[$i]] = $_Arr2[$i];
		return $_Res;
	}
	
	
	//获取省份列表
	 public function getProvinceList($prov){
		$plist=$this->area->where(array('level'=>1))->field('id,name')->select();
		$out='<select name="province" id="prov" onchange="loadcity(this.value);" >';
		foreach($plist as $v){
			if($v['id'] == $prov){
				$out.='<option  selected value="'.$v['id'].'">'.$v['name'].'</option>';
			}else{
				$out.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
			}
		}
		$out.='</select>';
		return $out;
	}
	//获取城市列表
	 public function getCityList($prov){
		$pp['pid']=$prov;
		$clist=$this->area->where($pp)->field('id,name')->select();
		$citylist='<select name="city" onchange="loadarea(this.value);" >';
		foreach($clist as $k=>$v){
			if($k==0){
				$city = $v['id'];
				$citylist.='<option  selected value="'.$v['id'].'">'.$v['name'].'</option>';
			}else{
				$citylist.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
			}
		}
		$citylist.='</select>_';
		$cc['pid']=$city;
		$alist=$this->area->where($cc)->field('id,name')->select();
		if($alist){
			$citylist.='<select name="area">';
			foreach($alist as $k=>$v){
				if($k==0){
					$citylist.='<option  selected value="'.$v['id'].'">'.$v['name'].'</option>';
				}else{
					$citylist.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
				}
			}
			$citylist.='</select>';
		}
		return $citylist;
	}
	
	//获取县区列表
	 public function getAreaList($city){
		$cc['pid']=$city;
		$alist=$this->area->where($cc)->field('id,name')->select();
		$out='';
		if($alist){
			$out='<select name="area">';
			foreach($alist as $k=>$v){
				if($k==0){
					$out.='<option  selected value="'.$v['id'].'">'.$v['name'].'</option>';
				}else{
					$out.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
				}
			}
			$out.='</select>';
		}
		return $out;
	}
	//默认 或指定 省市区列表
	 public function getAreaDefault($level1=0,$level2=0,$level3=0){
		$plist=$this->area->where(array('level'=>1))->field('id,name')->select();
		$out='<select name="province" id="prov" onchange="loadcity(this.value);" >';
		if($level1){
			foreach($plist as $v){
				if($v['id']==$level1){
					$out.='<option value="'.$v['id'].'" selected >'.$v['name'].'</option>';
				}else{
					$out.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
				}
			}
		}else{
			foreach($plist as $k=>$v){
				if($k==0){
					$level1=$v['id'];	//二级id
					$out.='<option value="'.$v['id'].'" selected >'.$v['name'].'</option>';
				}else{
					$out.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
				}
			}
		}
		$out.='</select>_';
		$pp['pid']= $level1;
		$clist=$this->area->where($pp)->field('id,name')->select();
		if($clist){
			$out.='<select name="city"  onchange="loadarea(this.value);" >';
			if($level2){
				foreach($clist as $k=>$v){
					if($level2 == $v['id']){
						$out.='<option  selected value="'.$v['id'].'">'.$v['name'].'</option>';
					}else{
						$out.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
					}
				}
			}else{
				foreach($clist as $k=>$v){
					if($k == 0){
						$out.='<option  selected value="'.$v['id'].'">'.$v['name'].'</option>';
					}else{
						$out.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
					}
				}
			}
			$out.='</select>_';
		}
		$cc['pid']=$level2;
		$alist=$this->area->where($cc)->field('id,name')->select();
		if($alist){
			$out.='<select name="area">';
			if($level3){
				foreach($alist as $v){
					if($v['id']==$level3){
						$out.='<option  selected value="'.$v['id'].'">'.$v['name'].'</option>';
					}else{
						$out.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
					}
				}
			}else{
				foreach($alist as $k=>$v){
					if($k==0){
						$out.='<option  selected value="'.$v['id'].'">'.$v['name'].'</option>';
					}else{
						$out.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
					}
				}
			}
			$out.='</select>';
		}
		return $out;
	}
	//根据省份获取 城市 列表 js使用
	public function getCity(){
		echo $this->getCityList($_GET['p']);
	}
	//根据城市获取 县区 列表 js使用
	public function getArea(){
		echo $this->getAreaList($_GET['c']);
	}	
	public function getAreaDef(){
		echo $this->getAreaDefault();
	}
	//清除缓存
	public function cityCacheDel(){
		$dir=$_SERVER['DOCUMENT_ROOT'].__ROOT__.'/Home/Runtime/Temp';
		$dir_arr = scandir($dir);
        foreach($dir_arr as $key=>$val){
             if($val == '.' || $val == '..'){}
             else {
                if(is_dir($dir.'/'.$val))    
                 {                            
                    if(@rmdir($dir.'/'.$val) == 'true'){
					}    //去掉@您看看                
                     else
                     rmdirs($dir.'/'.$val);                    
                 }
                 else                
                 unlink($dir.'/'.$val);
             }
         } 
		$this->json();
	}

	//获取城市列表
	public function getCitys(){
		$map['name']=$_GET['province'];
		$map['level']=1;
		$pid=M('area')->where($map)->getField('id');
		$city=M('area')->where('pid='.$pid)->field('name')->select();
		foreach ($city as $v) {
			$list[]=array(
				'value'=>$v['name'],
				'label'=>$v['name']
			);
		}
		die(json_encode($list));
	}
}