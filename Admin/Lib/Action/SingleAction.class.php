<?php
class SingleAction extends CommonAction{
	public function index(){
		$m = M('single');
		if($this->isGet()){
			$this->list=$m->where('pid=0')->select();
			empty($_GET['id'])||$this->daninfo=$m->where("id =".$_GET['id'])->find();
			$this->display();
		}else{
			$id = intval($_POST['id']);
			$title = '/^.{6,120}$/';
			preg_match($title,$_POST['title'])==0&&$this->json(300,'标题必须在2-40个汉字之间');
			$key = '/^.{6,900}$/';
			preg_match($key,$_POST['keywords'])==0&&$this->json(300,'关键词必须在2-300个汉字之间');
			$_POST['content']=stripslashes(htmlspecialchars_decode($_POST['content']));
			//echo  $_POST['content'];
			$row=$id?$m->where("id = $id")->save($_POST):$m->add($_POST);
			$row?$this->json(200,'操作成功',array('tabid' => $_GET['dialogId'] . ',Single-lists', 'closeCurrent' => true)):$this->json(300,'操作失败');
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
		$order = 'sort asc';
		if ($this -> _get('orderField')) {
			$order =$this -> _get('orderField') . ' ' . $this -> _get('orderDirection');
            //echo($order); 
		}
		$rs=M('single')->order($order)->select();
		$arr=lev($rs,0,'----',0);
		$this->list=$arr;
		$this->display();
	  }	
	
	
		
	/*//列表
	public function lists(){
	  
		$rs=M('single')->order("sort asc")->select();
		$arr=lev($rs,0,'----',0);
		$this->list=$arr;
		$this->display();
	}*/
	
	//删除
	public function del(){
		$id = $_GET['id']?intval($_GET['id']):'';
		$m = M('single');
		$row = $m->where("id = $id")->delete();
		$row?$this->json():$this->json(0);
	}
			public function sort(){
				$res=0;
		        if(isset($_POST['btn_submit'])){
		        	foreach($_POST['sort'] as $id=>$sort){
		              $res+=M('single')->where(array('id'=>$id))->setField('sort',$sort);
		            }
		        }
				$res ? $this->json_die(1) : $this->json_die(0);
		    }	
	
}
?>