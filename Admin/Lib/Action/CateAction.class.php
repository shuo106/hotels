<?php
class CateAction extends CommonAction{
	public function index(){
		$m = M('cate');
		if($_GET['parentId']){
			$this->assign('parentId',$_GET['parentId']);
		}
		$_GET['id']&&$this->cateinfo=$m->where("id = {$_GET['id']} AND is_delete != 1")->find();
		$row = $m->where("is_delete != 1 ")->select();
		$list = $this->rec($row,$id=0);
		$this->assign('list',$list);
		$this->display();
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
	//添加分类
	public function add(){
		if(isset($_POST['btn_submit'])){
			$title = '/^.{6,30}$/';
			if(preg_match($title,$_POST['name'])==0){
				$this->json(300,'栏目名称必须在2-10个汉字之间');
			}
			$key = '/^.{15,900}$/';
			if(preg_match($key,$_POST['keywords'])==0){
				$this->json(300,'栏目关键字必须在5-300个汉字之间');
			}
			$desc = '/^.{30,1500}$/';
			if(preg_match($desc,$_POST['description'])==0){
				$this->json(300,'栏目描述必须在10-500个汉字之间');
			}
			empty($_POST['id'])||$_POST['pid']==$_POST['id']&&$this->json(300,'上级栏目不能是当前栏目');
			$m = M('cate');
			$data = array();
			$data['name'] 			= $_POST['name'];
			$data['pid'] 			= $_POST['pid'];
			$res = $m->query("select name from pchotel_cate where id = {$_POST['pid']} limit 1");
			
			$data['parentname']		= $res[0]['name']?$res[0]['name']:'顶级分类';
			$data['keywords']		= $_POST['keywords'];
			$data['description']	= $_POST['description'];
			$id = intval();
			$row = $_POST['id']?$m->where("id =".$_POST['id'])->save($data):$m->add($data);
			$row?$this->json(200,'添加成功',array('forward'=>U('Cate/lists'))):$this->json(0);
		}
	}
	//分类列表
	public function lists(){
		$m = M('cate');
		$map['is_delete']=array('neq',1);
		$this->_get('keywords')&&$map['name']=array('like','%'.$this->_get('keywords').'%');
		
		$order = 'id asc';
		if ($this -> _get('orderField')) {
			$order =$this -> _get('orderField') . ' ' . $this -> _get('orderDirection');
          //  echo($order); 
		}
		
		
		$rs = $m->where($map)->order($order)->select();
		$row=lev($rs,0,'----',0);
		$this->assign('list',$row);
		$this->display();
	}
	
	/*//分类列表
	public function lists(){
		$m = M('cate');
		$map['is_delete']=array('neq',1);
		$this->_get('keywords')&&$map['name']=array('like','%'.$this->_get('keywords').'%');
		$rs = $m->where($map)->order('id desc')->select();
		$row=lev($rs,0,'----',0);
		$this->assign('list',$row);
		$this->display();
	}*/
	
			

	
	//回收站列表
	public function huishou(){
		$m = M('cate');
		
		$order = 'id asc';
		if ($this -> _get('orderField')) {
	    $order =$this -> _get('orderField') . ' ' . $this -> _get('orderDirection');
          //  echo($order); 
		}

		$row = $m->where("is_delete = 1")->order($order)->select();
		$this->assign('list',$row);
		$this->display();
	}
	
	
	/*//回收站列表
	public function huishou(){
		$m = M('cate');

		$row = $m->where("is_delete = 1")->order('id desc')->select();
		$this->assign('list',$row);
		$this->display();
	}*/
	
	//删除到回收站
	public function recycle(){
		$id = intval($_GET['id']);
		$m = M('cate');
		$data['is_delete'] = 1;
		$row = $m->where("id=$id")->data($data)->save();
		$row?$this->json():$this->json(0);
	}
	//回收站还原分类
	public function reduction(){
		$id = intval($_GET['id']);
		$m = M('cate');
		$data['is_delete'] = 0;
		$row = $m->where("id=$id")->data($data)->save();
		$row?$this->json():$this->json(0);
	}
	//彻底删除分类
	public function del(){
		$id = intval($_GET['id']);
		$m = M('cate');
		$res = $m->where("id = $id")->delete();
		$res?$this->json():$this->json(0);
	}
	//批量删除
    function pi(){
         if(!IS_AJAX){
             halt('页面不存在');
         }
         $ids=$_GET['ids'];
         $type=$this->_get('type','intval');
         if(empty($ids)||!$type){
         	 $this->json(300,"非法操作");
         }
		 $map['id']=array('in',$ids);
		 $m=M('cate');
         switch($type){
            //批量删除
            case 1:
				$rows=$m->where($map)->delete();
            break;
            //批量还原
            case 2:
				$rows=$m->where($map)->setField('is_delete',0);
				break;
			//批量假删除
			case 3:
				$rows=$m->where($map)->setField('is_delete',1);
				break;
         }
		$rows ? $this->json(1) : $this->json(0);
    }	
}
?>