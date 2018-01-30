<?php
class SubwayAction extends CommonAction{
	public function index(){
		//获取城市
		$this->city=$city=M('subwaycity')->select();
		$this->display();
	}
	public function lists(){
		$map['city']=$_GET['id'];

		$this ->total=M('subway')->where($map)->count();
		$this ->pagesize=$this -> _get('pageSize', 'intval', $this -> pagesizes[0]);
		$this ->pageCurrent=$this -> _get('pageCurrent', 'intval', 1);		
		$subway=M('subway')->where($map)->page($this ->pageCurrent.','.$this ->pagesize)->order('ditie desc')->select();
		$this->assign('subway',$subway);
		$this->display();		
	}
	public function edit(){
		$subway=M('subway');
		if($this->isGet()){
			$id=$_GET['id'];
			$content=$subway->where("id=$id")->find();
			$this->assign('subway',$content);
			
			//获取省份
			$province = M('area')->where('level=1')->field('name')->select();
			$this->assign('province',$province);
			//获取城市
			$map['name']=$content['province'];
			$map['level']=1;
			$pid=M('area')->where($map)->getField('id');
			$city=M('area')->where('pid='.$pid)->field('name')->select();
			$this->assign('city',$city);
			$this->display();
		}else{
			$data['ditie']=$_POST['ditie'];
			$data['zhanpai']=$_POST['zhanpai'];
			$data['city']=$_POST['city'];
			if($this->_post('id')){
				$rs=$subway->where('id='.$this->_post('id'))->save($data);
			}else{
				$rs=$subway->add($data);
			}
			$rs ? $this->json(200, '操作成功', array('tabid' => "" . ',Subway-lists', 'closeCurrent' => true)) : $this->json(0);				
		}
	}
	public function del(){
		$id=$_GET['id'];
		$college=M('subway');
		$rs=$college->where("id=$id")->delete();
		$rs?$this->json():$this->json(0);
	}	
}