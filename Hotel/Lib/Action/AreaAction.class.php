<?php
class AreaAction extends BaseAction{
	private $area; //区域表
	public function _initialize(){
		$this->area = M('area');
	}
	//获取省份列表
	 public function getProvinceList($prov){
		$plist=$this->area->where(array('level'=>1))->field('id,name')->select();
		$out='<select class="am-select" name="province" id="prov" onchange="loadcity(this.value);" >';
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
		$citylist='<select class="am-select" name="city" onchange="loadarea(this.value);" >';
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
			$citylist.='<select class="am-select" name="area">';
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
			$out='<select class="am-select" name="area">';
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
		$out='<select class="am-select" name="province" id="prov" onchange="loadcity(this.value);" >';
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
			$out.='<select class="am-select" name="city"  onchange="loadarea(this.value);" >';
			foreach($clist as $k=>$v){
				if($v['id']==$level2){
					$level2=$v['id'];
					$out.='<option  selected value="'.$v['id'].'">'.$v['name'].'</option>';
				}else{
					$out.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
				}
			}
			$out.='</select>_';
		}
		$cc['pid']=$level2;
		$alist=$this->area->where($cc)->field('id,name')->select();
		if($alist){
			$out.='<select class="am-select" name="area">';
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
}