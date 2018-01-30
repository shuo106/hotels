<?php
class LiansuoAction extends CommonAction{
	public function index(){
		$res = M('liansuo')->select();
		$Z=array();
		foreach($res as $v){
			$Z[]=$v['zimu'];
		}
		$all = array_unique($Z);
		sort($all);
		$this->all= $all;
		$this->assign('row',$res);
		$this->display();
	} 
	
}