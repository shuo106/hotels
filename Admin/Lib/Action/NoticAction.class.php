<?php
class NoticAction extends CommonAction{
	public function index(){
		$m = M('notic');
		if($this->isGet()){
			$this->list=$m->where('pid=0')->select();
			empty($_GET['id'])||$this->daninfo=$m->where("id =".$_GET['id'])->find();
			 //dump($_SESSION);
			$this->display();
		}else{
			$id = intval($_POST['id']);
          
            $_POST['addtime']=time();
            $_POST['username']=$_SESSION["admin_name"];
			//dump($_POST); die;
			$row=$id?$m->where("id = $id")->save($_POST):$m->add($_POST);
			$row?$this->json(200,'操作成功',array('tabid' => $_GET['dialogId'] . ',Notic-lists', 'closeCurrent' => true)):$this->json(300,'操作失败');
		}
	}
	//递归分类
	public function rec($arr,$id,$lev = 0){
        static $list = array();
        foreach($arr as $v){
            if($v['pid'] == $id){
                $v['lev']=$lev;
                $list[] = $v;
                $this->rec($arr,$v['id'],$lev+1);
            }
        }
        return $list;
    }
	
	
	
	//列表
	public function lists(){					
		$order = 'id desc';
		if ($this -> _get('orderField')) {
			$order =$this -> _get('orderField') . ' ' . $this -> _get('orderDirection');
            //echo($order); 
		}
		  
		$rs=M('notic')->order($order)->select();
		$arr=lev($rs,0,'----',0);
		$this->list=$arr;
		$this->display();
	  }	
	
	
	//删除
	public function del(){
		$id = $_GET['id']?intval($_GET['id']):'';
		$m = M('notic');
		$row = $m->where("id = $id")->delete();
		$row?$this->json():$this->json(0);
	}
			public function sort(){
				$res=0;
		        if(isset($_POST['btn_submit'])){
		        	foreach($_POST['sort'] as $id=>$sort){
		              $res+=M('notic')->where(array('id'=>$id))->setField('sort',$sort);
		            }
		        }
				$res ? $this->json_die(1) : $this->json_die(0);
		    }	
	
}
?>