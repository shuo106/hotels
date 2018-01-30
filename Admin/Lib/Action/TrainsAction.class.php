<?php
class TrainsAction extends CommonAction{
	public function index(){
		$data=M('train');
		if($_GET['province']){
			$map['province']=$_GET['province'];
		}
		if($_GET['text']){
			$map['train']=array('like','%'.$_GET['text'].'%');
		}
		$this ->total=$data->where($map)->count();
		$this ->pagesize=$this -> _get('pageSize', 'intval', $this -> pagesizes[0]);
		$this ->pageCurrent=$this -> _get('pageCurrent', 'intval', 1);

		$content=$data->where($map)->page($this ->pageCurrent.','.$this ->pagesize)->select();
		$this->assign('trains',$content);		


		//获取省份
		$province = M('area')->where('level=1')->field('name')->select();
		$this->assign('province',$province);				
		$this->display();
	}
	public function edit(){
		$train=M('train');
		if($this->isGet()){
			$id=$_GET['id'];
			$content=$train->where("id=$id")->find();
			$this->assign('train',$content);
			
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
			$data['train']=$_POST['train'];
			$data['province']=$_POST['province'];
			$data['cityname']=$_POST['cityname'];
			if($this->_post('id')){
				$rs=$train->where('id='.$this->_post('id'))->save($data);
			}else{
				$rs=$train->add($data);
			}
			$rs ? $this->json(200, '操作成功', array('tabid' => "" . ',Trains-index', 'closeCurrent' => true)) : $this->json(0);				
		}
	}
	public function del(){
		$id=$_GET['id'];
		$college=M('train');
		$rs=$college->where("id=$id")->delete();
		$rs?$this->json():$this->json(0);
	}
}