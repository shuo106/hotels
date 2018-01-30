<?php
class AdsAction extends CommonAction{
	public function addplace(){
		
		$this->display();
	}
	//广告位添加
	public function adspadd(){
		$ad=M('ads_place');
		$ad->create();
		$rs = $ad->placeid ? $ad->save() : $ad->add();		
		$rs ? $this->json_die(200,'操作成功',array('tabid' => $_GET['dialogId'] . ',Ads-index', 'closeCurrent' => true)):$this->json(0);
	}
	public function index(){
		$place=M('ads_place');
	
		import('ORG.Util.Page');
		$count=$place->count();
		$page=new Page($count,10);
		$show=$page->show();
		$content=$place->/*limit($page->firstRow.','.$page->listRows)->*/select();
		$this->assign('place',$content);
		$this->assign('page',$show);
		$this->display();
		
		
	}
	//广告位修改
	public function edit(){
		$this->id = $id=$_GET['id'];
		$place=M('ads_place');
		$content=$place->where("placeid=$id")->find();
		$this->assign('place',$content);
		$this->display();
		
	}
	public function placeupdate(){
		$id=$_GET['id'];
		$place=M('ads_place');
		$data=$_POST;		
		$data['placeid']=$id;
		$place->data($data)->save();
		$this->redirect('index');
	}
	//添加页面
	public function addads() {
		$_GET['placeid'] && $this->placeid=$_GET['placeid'];
		if($_GET['id']){
			$this->adsid=$_GET['id'];
			$place=M('ads')->where('adsid='.$_GET['id'])->find();
			$this->placeid=$place['placeid'];
			$this->place=$place;
		}
		$this -> display();
	}
	//广告添加操作过程
	public function addadsin() {
		!preg_match('/^.{6,60}$/', $_POST['adsname']) && $this -> json(300, '广告名称必须在2-20个汉字之间');
		$ads = M('ads');
		$ads -> create();
		if($ads->adsid){
			// $ads->passed=1;
			$rs=$ads -> save();
		}else{
			$rs=$ads -> add();
		}
		$rs ? $this -> json(200,'操作成功',array('tabid' => $_GET['dialogId'] . ',Ads-adslist', 'closeCurrent' => true)) : $this -> json(0);
	}
	public function adslist(){
		$this ->ads = M('ads a') -> where("a.placeid=" . $_GET['id']) -> join("pchotel_ads_place b on a.placeid=b.placeid") -> field("a.*,b.placename") -> select();
		$this -> display();
	}
	public function adsedit(){
		$id=$_GET['id'];
		$place=M('ads_place');
		$placename=$place->select();
		
		$this->assign('placename',$placename);
		
		
		$ads=M('ads');
		$content=$ads->where("adsid=$id")->find();
		$this->assign('ads',$content);
		$this->display();
		
	}
	public function adsupdate(){
		$id=$_GET['id'];
	
		$ads=M('ads');
			$data['placeid'] 	= $_POST['placeid'];
			$data['adsname'] 	= $_POST['adsname'];
			$data['introduce'] 	= $_POST['introduce'];
			$data['name'] 		= $_POST['name'];
			$data['linkurl']	= $_POST['linkurl'];
			if($_FILES['imageurl']['name'] != ''){
				$info = $this->uploadimg('ads');
				if($info){
					$data['imageurl']='/'.C('upload_dir').'/ads/'.$info['photo_path'];
				}
			}	
		$rs=$ads->where("adsid=$id")->data($data)->save();
		$rs?$this->json():$this->json(0);
		
	}
	public function adsdelete(){
		$id=$_GET['id'];
		$ads=M('ads');
		$rs=$ads->where("adsid=$id")->delete();
		$rs?$this->json():$this->json(0);
	}
	public function placedel(){
		$id=$_GET['id'];
		$ads=M('ads_place');
		$rs=$ads->where("placeid=$id")->delete();
		$rs?$this->json():$this->json(0);
	}
	
}