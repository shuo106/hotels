<?php

class ArticleAction extends CommonAction{

	public function _initialize() {

		parent::_initialize();

		$this->hotList=S('hotList');

	}

	public function category(){

		$this->_menu='article'.$this->_get('id');

		import('@.ORG.Util.Page');

		$cate=M('cate');

		$art=M('article');

		$id=$this->_get('id');	

		

		//通过ID，得到当前的页面信息

		$catename= $cate->where('id='.$id)->find();

		$this->catename=$this->leibie=$catename['name'];

		$this->title2=$catename['name'];

		$this->keywords=$catename['keywords'];

		$this->description=$catename['description'];



		$s= $this->getMap($id);

		$c= $this->checkCate($id);

        $this->assign('c',$c);

		

		$total=$art->where("is_delete=0  and " .$s )->count();

		$Page	= new Page($total,5);// 实例化分页类 传入总记录数和每页显示的记录数

		$this->page  = $Page->show();// 分页显示输出

		$alist=$art->where("pchotel_article.is_delete=0  and " .$s )

						->join('pchotel_cate ON pchotel_article.catid = pchotel_cate.id')

						->limit($Page->firstRow.','.$Page->listRows)

						->field('pchotel_article.strong,pchotel_article.color,articleid,pchotel_article.description,addtime,thumb,title,name as catename,username,pchotel_article.keywords')

						

						->order('pchotel_article.status desc,pchotel_article.sort desc,articleid desc')

						->select();

						

		foreach($alist as $k=>$v){

			

			if($alist[$k]['keywords']){

              if($this->stoa($alist[$k]['keywords'])!=null){

			   $alist[$k]['keywords'] =$this->stoa($alist[$k]['keywords']);

		       }else{

				$alist[$k]['keywords']=array($alist[$k]['keywords']);  

			   }			

			}

		

		} 				

						

		$this->assign('alist',$alist);		

		$hot= $art->field('articleid,title')->where('is_delete=0')->order('status=1')->limit(5)->select();//得到5条热门文章 //dump($hot);

	    $this->assign('hot',$hot);
	    $hotlist = M("area")->where("ishot=1")->select();
	    $this->assign('hotlist',$hotlist);

		$this->display('article_list');



	}

	//通过当前的栏目ID是否有子类（临时用只查找儿子），或者同胞。做成SQL的搜索条件

	public function  getMap($id){

		$cate=M('cate');

		$cateSon= $cate->field('id')->where('is_delete=0 and pid='.$id)->select();//查找出他的子分类

		//dump($cateSon);

		if($cateSon){

			   // dump($cateSon);

			   foreach($cateSon as $key=>$value){

				$s.=" catid =".$cateSon[$key]['id']." or";

				}

				$s=$s." catid=".$id;

				return   $s;

		}else{

			 return  " catid=".$id;

		}	

	}

	

	//判断当前的ID得到栏目ID是否有子类（临时用只查找儿子），或者同胞。，有就返回,没有就为0

	public  function   checkCate($id){

		$cate=M('cate');

		$cateSon= $cate->field('id,name')->where('is_delete=0 and pid='.$id)->select();//查找出他的子分类

	   

	     if($cateSon){

			 return  $cateSon;

		 }else{  //如果没有子分类 查找他是否有同胞

		    

			 $cateBor= $cate->where('id ='.$id)->find();//得到当前的分类信息

			 $cateBor= $cate->field('id,name')->where(' is_delete=0 and  pid<>0 and pid ='.$cateBor['pid'])->select();//得到同胞(顶级分类PID都是0，所以要筛选掉)

			 

			  if($cateBor){

				  return  $cateBor;

			  }else{

				  return  false;

			  }

			  

			  

		 }



	}

	

	//然当前传入的字符串变成数组

	  public   function  stoa($s){

		  

		  if(strpos($s,",") === false){     //使用绝对等于

             

           }else{

              $arr=explode(",",$s);

              return $arr;		  

          }

		  

		 if(strpos($s,"，") === false){     

             

           }else{

              $arr=explode("，",$s);

              return $arr;		  

          }

		  

		  if(strpos($s,"、") === false){    

             

           }else{

              $arr=explode("、",$s);

              return $arr;		  

          }

		  

		  if(strpos($s," ") === false){    

             

           }else{

              $arr=explode(" ",$s);

              return $arr;		  

          }

		  	  

	  }

	  

	public function show(){

		$article=M('article');

		$id=$data['articleid']=$this->_get('id');

		$data['is_delete']=0;

		$art=$article->where($data)->field('title,content,keywords,username,addtime,description,catid,hits')->find();

		

		$art['catid'];

		$cate=M('cate');

		$cate=$cate->where('id='.$art['catid'])->find();

		$this->assign('cate',$cate);

		///dump($cate);

		

		$this->assign('art',$art);

		//上一篇 下一篇

		$sh['is_delete']= 0;

		$sh['articleid']= array('lt',$id);

		$shang=$article->where($sh)->order('articleid DESC')->field('articleid,title')->find();

		$xi['is_delete']= 0;

		$xi['articleid']= array('gt',$id);

		$xia=$article->where($xi)->order('articleid ASC')->field('articleid,title')->find();

		$shangp =$shang ? '<p><span>上一篇：</span><a href="__ROOT__/Article/show-'.$shang['articleid'].'.html">'.$shang['title'].'</a></p>' : '<p><span>上一篇：</span>没有了</p>';

		$xiap =$xia ? '<p><span>下一篇：</span><a href="__ROOT__/Article/show-'.$xia['articleid'].'.html">'.$xia['title'].'</a></p>' : '<p><span>下一篇：</span>没有了</p>';

		$this->assign('shang',$shangp);

		$this->assign('xia',$xiap);

		$this->leibie=$art['title'];

		$this->keywords=$art['keywords'];

		$this->description=$art['description'];

		

		

		$art2=M('article');

		$fd = $art2->field('articleid,title,thumb')->where('catid=12  and status=1  and is_delete=0')->limit(4)->select();//得到4条推荐的房东故事 //dump($fd);

		$this->assign('fd',$fd);

		

		$hot= $art2->field('articleid,title')->where('is_delete=0')->order('status=1')->limit(5)->select();//得到5条热门文章 //dump($hot);

	    $this->assign('hot',$hot);

	    $hotlist = M("area")->where("ishot=1")->select();
	    $this->assign('hotList',$hotlist);

		

		//增加点击量

	      $rs= $this->addhits($id);

		  if($rs){

			 $data2['hits']=$art['hits']+1;

			 $article->where('articleid='.$id)->save($data2);

			// echo $article->getlastsql();

		  }else{

			//  echo "已经增加过了";

		  }

		

		

		$this->display();

	} 

	//文章点击量处理

	public  function  addhits($id){

		  $c = $_SERVER["REMOTE_ADDR"].$id; //将当前IP和文章ID放在一起

          $c = str_replace('.','',$c);

		 

           if(empty($_COOKIE[$c])){

			  cookie($c,$c);

			  	//echo "+1";

		  	return  true; //可以加1

		

		    }else{

			  // echo "==";

              return  false;//已经加过了

			

		   }



	} 

} 