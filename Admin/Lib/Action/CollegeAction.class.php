<?php
class CollegeAction extends CommentAction{
	public function index(){
		
		$data=M('college');	
		if($_GET['province']){
			$map['province']=$_GET['province'];
		}
		if($_GET['text']){
			$map['college']=array('like','%'.$_GET['text'].'%');
		}
		$this ->total=$data->where($map)->count();
		$this ->pagesize=$this -> _get('pageSize', 'intval', $this -> pagesizes[0]);
		$this ->pageCurrent=$this -> _get('pageCurrent', 'intval', 1);

		$content=$data->where($map)->page($this ->pageCurrent.','.$this ->pagesize)->select();
		$this->assign('college',$content);

		//获取省份
		$province = M('area')->where('level=1')->field('name')->select();
		$this->assign('province',$province);		
		$this->display();
	}
	public function edit(){
		$college=M('college');
		if($this->isGet()){
			$id=$_GET['id'];
			$content=$college->where("id=$id")->find();
			$this->assign('college',$content);
			
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
			$data['college']=$_POST['college'];
			$data['province']=$_POST['province'];
			$data['cityname']=$_POST['cityname'];
			if($this->_post('id')){
				$rs=$college->where('id='.$this->_post('id'))->save($data);
			}else{
				$rs=$college->add($data);
			}
			$rs ? $this->json(200, '操作成功', array('tabid' => "" . ',College-index', 'closeCurrent' => true)) : $this->json(0);				
		}
	}
	public function del(){
		$id=$_GET['id'];
		$college=M('college');
		$rs=$college->where("id=$id")->delete();
		$rs?$this->json():$this->json(0);
	}
	
}