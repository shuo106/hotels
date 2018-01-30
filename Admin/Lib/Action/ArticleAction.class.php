<?php
class ArticleAction extends CommonAction{
	public function index() {
		$cates = M('cate') -> where("is_delete != 1")-> select();
		$this -> catelist = formatlev($cates, 0, '┄');
		//文章编辑操作
		if ($_GET['id']) {
			$map2['articleid'] = intval($_GET['id']);
			$arcinfo = M('article') -> where($map2) -> find();
			// p($arcinfo);
			$this->assign('arcinfo',$arcinfo);
		}
		$this -> display();
	}
	
	
		public function update() {
		$cates = M('cate') -> where("is_delete != 1")-> select();
		$this -> catelist = formatlev($cates, 0, '┄');
		//文章编辑操作
		if ($_GET['id']) {
			$map2['articleid'] = intval($_GET['id']);
			$arcinfo = M('article') -> where($map2) -> find();
			// p($arcinfo);
			$this->assign('arcinfo',$arcinfo);
		}
		$this -> display();
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
	//添加文章
	public function add(){
		if(isset($_POST['btn_submit'])){
			$id = intval($_POST['id']);
			$title = '/^.{6,240}$/';
			if(preg_match($title,$_POST['title'])==0){
				$this->json(300,'标题必须在2-20个汉字之间');
			}
			if($_FILES['thumb']['name'] != ''){
				$info = $this->uploadimg('news');
			}
			if($_POST['url'] == 'http://'){
				$key = '/^.{6,900}$/';
				if(preg_match($key,$_POST['keywords'])==0){
					$this->json(300,'关键词必须在2-300个汉字之间');
				}
				$desc = '/^.{30,1500}$/';
				if(preg_match($desc,$_POST['description'])==0){
					$this->json(300,'内容摘要必须在10-500个汉字之间');
				}

			}
			$data = array();
			$data['thumb']			= $_POST['thumb'];
			$data['username'] 		= $_SESSION['admin_name'];
			$data['catid']			= $_POST['catid'];
			$data['title']			= $_POST['title'];
			$data['color']			= $_POST['color'];
			$data['strong']			= $_POST['strong']?$_POST['strong']:0;
			$data['url']			= $_POST['url'];
			$data['status']			= $_POST['status'];
			$data['copyfrom']		= $_POST['copyfrom'];
			$data['keywords']		= $_POST['keywords'];
			$data['description']	= $_POST['description'];
			$data['content']		= stripslashes(htmlspecialchars_decode($_POST['content']));
			$data['addtime']		= time();
			$m = M('article');
			if($id){
				$row = $m->where("articleid = $id")->data($data)->save();
			}else{
				$row = $m->add($data);
			}
			$row?$this->json():$this->json(0);
		}
	}
	//新闻列表
	public function lists(){
		$m = M('article');
		$data['pchotel_article.is_delete']=0;
		if($_GET['catid']){
			$arr = M('cate')->where("is_delete != 1")->select();
			$res = $this->rec($arr,$this->_get('catid'));	
			if($res){
				$ids=$_GET['catid'].',';
				foreach($res as $v){
					$ids.=$v['id'].',';
				}
				$data['catid']=array('in',$ids);
			}else{
				$data['catid']= $this->_get('catid');
			}
		}	
		if($_GET['text']){
			$data['title']= array('like','%'.$this->_get('text').'%');
		}

		$order = 'pchotel_article.sort desc,pchotel_article.addtime desc';
		if ($this -> _get('orderField')) {
			$order ='pchotel_article.'.$this -> _get('orderField') . ' ' . $this -> _get('orderDirection');
		}
		$this->total=$m->where($data)
						->join('pchotel_cate ON pchotel_article.catid = pchotel_cate.id')
						->count();
		$this ->pagesize=$this -> _get('pageSize', 'intval', $this -> pagesizes[0]);
		$this ->pageCurrent=$this -> _get('pageCurrent', 'intval', 1);
		$row = $m->where($data)
				->join('pchotel_cate ON pchotel_article.catid = pchotel_cate.id')
				->field('pchotel_article.*,pchotel_cate.name')
				->order($order) 
				->page($this ->pageCurrent.','.$this ->pagesize)
				->select();
		$this->assign('list',$row);
		$cates = M('cate') -> where("is_delete != 1")-> select();
		$this ->cate = formatlev($cates, 0, '┄');
		$this->display();
	}
	public function sort() {
		$row = 0;
		foreach ($_POST['sort'] as $articleid => $sort) {
			$row += M('article') -> where(array('articleid' => $articleid)) -> setField('sort', $sort);
		}
		$row ? $this -> json_die(1, '排序成功') : $this -> json_die(0);
	}
	
	
	/*	//新闻列表
	public function lists(){
		$m = M('article');
		$data['pchotel_article.is_delete']=0;
		if($_GET['catid']){
			$arr = M('cate')->where("is_delete != 1")->select();
			$res = $this->rec($arr,$this->_get('catid'));	
			if($res){
				$ids=$_GET['catid'].',';
				foreach($res as $v){
					$ids.=$v['id'].',';
				}
				$data['catid']=array('in',$ids);
			}else{
				$data['catid']= $this->_get('catid');
			}
		}	
		if($_GET['text']){
			$data['title']= array('like','%'.$this->_get('text').'%');
		} 

		$this->total=$m->where($data)
						->join('pchotel_cate ON pchotel_article.catid = pchotel_cate.id')
						->count();
		$this ->pagesize=$this -> _get('pageSize', 'intval', $this -> pagesizes[0]);
		$this ->pageCurrent=$this -> _get('pageCurrent', 'intval', 1);		 
		$row = $m->where($data)
				->join('pchotel_cate ON pchotel_article.catid = pchotel_cate.id')
				->order('pchotel_article.articleid desc')
				->page($this ->pageCurrent.','.$this ->pagesize)
				->select();
			//	dump($row); die();
		$this->assign('list',$row);
		$cates = M('cate') -> where("is_delete != 1")-> select();
		$this ->cate = formatlev($cates, 0, '┄');
		$this->display();
	}*/
	
	

	//删除新闻到回收站
	public function del(){
		$id = $_GET['id']?intval($_GET['id']):'';
		$m = M('article');
		$data['is_delete'] = 1;
		$row = $m->where("articleid = $id")->data($data)->save();
		$row?$this->json():$this->json(0);
	}
	public function pi(){
		$id=$_GET['ids'];
		$id||$this->json(300,'请选择你要操作的数据');
		$m = M('article');
		$map['articleid']=array('in',$id);
		switch ($this->_get('type')) {
			case '1':			//彻底删除
				$res=$m->where($map)->delete();
				break;
			case '2':			//移动
				$res=$m->where($map)->setField('catid',$this->_get('cid'));
				break;				
			case '3':			//还原
				$res=$m->where($map)->setField('is_delete',0);
				break;
			case '4':			//放到回收站
				$res=$m->where($map)->setField('is_delete',1);
				break;
		}
		$res?$this->json():$this->json(0);
	}
	//批量操作
	public function piliang(){
		$pl = $_POST['pl'];
		$id = intval($_POST['id']);
		
		$m = M('article');
		if($_POST['btn_submit'] == '更新排序'){
			foreach($_POST['sort'] as $k=>$v){
				$data['sort'] = $v;
				$row = $m->where("articleid = $k")->data($data)->save();
				//$row = $m->query("update pchotel_article set sort = $v where articleid = $k");
			}
		}
		if(!isset($_POST['btn_submit'])){
			if(!$id){
				die(json_encode(array('status'=>0,'msg'=>'请选择你要操作的数据')));
			}
			switch($pl){
				case 0:
					die(json_encode(array('status'=>0,'msg'=>'请选择操作')));
				break;
				case 1:
					foreach($_POST['id'] as $v){
						$data['is_delete'] = 1;
						$row = $m->where("articleid = $v")->data($data)->save();
					}
				break;
				case 2:
					foreach($_POST['id'] as $v){
						$data['catid'] = $_POST['cid'];
						$row = $m->where("articleid = $v")->data($data)->save();
					}
				break;
				default:
					die(json_encode(array('status'=>0,'msg'=>'未知操作')));
				break;
			}
		}
		if($row){
			die(json_encode(array('status'=>1,'msg'=>'批量操作成功')));
		}else{
			die(json_encode(array('status'=>0,'msg'=>'操作成功')));
		}
	}
	
	//新闻回收站
	public function huishou(){
				$m = M('article');
		$data['pchotel_article.is_delete']=1;
		if($_GET['catid']){
			$arr = M('cate')->where("is_delete != 1")->select();
			$res = $this->rec($arr,$this->_get('catid'));	
			if($res){
				$ids=$_GET['catid'].',';
				foreach($res as $v){
					$ids.=$v['id'].',';
				}
				$data['catid']=array('in',$ids);
			}else{
				$data['catid']= $this->_get('catid');
			}
		}	
		if($_GET['text']){
			$data['title']= array('like','%'.$this->_get('text').'%');
		} 
		
		$order = 'pchotel_article.addtime desc';
		if ($this -> _get('orderField')) {
			$order ='pchotel_article.'.$this -> _get('orderField') . ' ' . $this -> _get('orderDirection');
		    //echo($order); 
		}
		
		$this->total=$m->where($data)
						->join('pchotel_cate ON pchotel_article.catid = pchotel_cate.id')
						->count();
		$this ->pagesize=$this -> _get('pageSize', 'intval', $this -> pagesizes[0]);
		$this ->pageCurrent=$this -> _get('pageCurrent', 'intval', 1);		 
		$row = $m->where($data)
				->join('pchotel_cate ON pchotel_article.catid = pchotel_cate.id')
				//->order('pchotel_article.articleid desc')
				->field('pchotel_article.*,pchotel_cate.name')
				->order($order) 
				->page($this ->pageCurrent.','.$this ->pagesize)
				->select();
			//	dump($row); die();
		$this->assign('list',$row);
		$cates = M('cate') -> where("is_delete != 1")-> select();
		$this ->cate = formatlev($cates, 0, '┄');
		$this->display();
		
		
	}
	
	/*//新闻回收站
	public function huishou(){
		$m = M('article');
		import("ORG.Util.Page");// 导入分页类
		$data['pchotel_article.is_delete']=1;
		if($_GET['catid']){
			$data['catid']= $this->_get('catid');
		}	
		if($_GET['text']){
			$data['title']= array('like','%'.$this->_get('text').'%');
		}  
		$this->total= $m->where($data)
					->join('pchotel_cate ON pchotel_article.catid = pchotel_cate.id')
					->count();
		$this ->pagesize=$this -> _get('pageSize', 'intval', $this -> pagesizes[0]);
		$this ->pageCurrent=$this -> _get('pageCurrent', 'intval', 1);						
		$row = $m->where($data)
				->join('pchotel_cate ON pchotel_article.catid = pchotel_cate.id')
				->page($this ->pageCurrent.','.$this ->pagesize)
				->order('pchotel_article.articleid desc')
				->select();
		$arr = M('cate')->where("is_delete != 1")->select();
		$res = $this->rec($arr,$id=1);
		$this->assign('list',$row);
		$this->assign('cate',$res);
		$this->display();
	}*/
	
	
	
	//删除新闻
	public function del2(){
		$id = $_GET['id']?intval($_GET['id']):'';
		$m = M('article');
		$row = $m->where("articleid = $id")->delete();
		/*if($row){
			die(json_encode(array('status'=>1,'msg'=>'删除成功！')));
		}else{
			die(json_encode(array('status'=>0,'msg'=>'操作错误')));
		}*/
		$row?$this->json(1):$this->json(0);
	}
	//还原新闻
	public function huanyuan(){
		$id = $_GET['id']?intval($_GET['id']):'';
		$m = M('article');
		$data['is_delete'] = 0;
		$row = $m->where("articleid = $id")->data($data)->save();
		$row?$this->json():$this->json(0);
	}
	//回收站批量操作
	public function piliang2(){
		$pl = $_POST['pl'];
		$id = intval($_POST['id']);
		if(!$id){
			die(json_encode(array('status'=>0,'msg'=>'请选择你要操作的数据')));
		}
		$m = M('article');
		switch($pl){
			case 0:
				die(json_encode(array('status'=>0,'msg'=>'请选择操作')));
			break;
			case 1:
				foreach($_POST['id'] as $v){
					$row = $m->where("articleid = $v")->delete();
				}
			break;
			case 2:
				foreach($_POST['id'] as $v){
					$data['is_delete'] = 0;
					$row = $m->where("articleid = $v")->data($data)->save();
				}
			break;
			default:
				die(json_encode(array('status'=>0,'msg'=>'未知操作')));
			break;
		}
		if($row){
			die(json_encode(array('status'=>1,'msg'=>'批量操作成功')));
		}else{
			die(json_encode(array('status'=>0,'msg'=>'操作错误')));
		}
	}
}
?>