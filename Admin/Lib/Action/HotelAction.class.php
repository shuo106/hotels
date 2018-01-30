<?php
class HotelAction extends CommonAction{
	public function liansuo(){
		$m = M('liansuo');
		$id = $_GET['id'];
		if($id){
			$r = $m->where("id = $id")->select();
			$this->assign('id',$r[0]['id']);
			$this->assign('name',$r[0]['name']);
			$this->assign('zimu',$r[0]['zimu']);
			$this->assign('thumb',$r[0]['thumb']);
		}
		if(isset($_POST['btn_submit'])){
			if(strlen($_POST['name']) > 90){
				die(json_encode(array('status'=>0,'msg'=>'品牌名称不得超过30个汉字')));
			}
			if(empty($_POST['name'])){
				die(json_encode(array('status'=>0,'msg'=>'品牌名称不能为空')));
			}
			if(empty($_POST['zimu'])){
				die(json_encode(array('status'=>0,'msg'=>'请选择品牌首大写字母')));
			}
			if($_FILES['thumb']['name'] != ''){
				$info = $this->uploadimg('brand');
				if($info){
					$data['thumb'] =C('upload_dir').'/brand/'.$info['photo_path'];
				}
			}
			$data['name'] = $_POST['name'];
			$data['zimu'] = $_POST['zimu'];
			$id = $_POST['id'];
			if($id){
				$res = $m->where(array('id'=> $id))->save($data);
			}else{
				$res = $m->add($data);
			}
			if($res){
				die(json_encode(array('status'=>1,'msg'=>'操作成功')));
			}else{
				die(json_encode(array('status'=>0,'msg'=>'无任何更改')));
			}
		}
		$this->display();
	}
	public function lslist(){
		$m = M('liansuo');
		$con = trim($_GET['text']);
		import("ORG.Util.Page");// 导入分页类
		if($con){
			$count = $m->where("name like '%$con%'")->count();
		}else{
			$count = $m->count();
		}
		$Page = new Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
		$show = $Page->show();// 分页显示输出
		if($con){
			$row = $m->where("name like '%$con%'")->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		}else{
			$row = $m->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		}
		$this->assign('page',$show);
		$this->assign('row',$row);
		$this->display();
	}
	public function lsdel(){
		$map['id'] = intval($_GET['id']);
		$row = M('liansuo')->where($map)->delete();
		if($row){
			die(json_encode(array('status'=>1,'msg'=>'操作成功')));
		}else{
			die(json_encode(array('status'=>0,'msg'=>'操作错误')));
		}
	}
	public function lspl(){
		$id = $_POST['id'];
		if(!$id){
			die(json_encode(array('status'=>0,'msg'=>'请选择操作的数据')));
		}
		$map['id']=array('in',$id);
		$row = M('liansuo')->where($map)->delete();
		if($row){
			die(json_encode(array('status'=>1,'msg'=>'操作成功')));
		}else{
			die(json_encode(array('status'=>0,'msg'=>'操作错误')));
		}
	}
	/**
	 * [tixian 提现管理列表页]
	 * @return [type] [description]
	 */
	public function tixian() {
		$map['status']=array('in',$this->_get('status'));
		$this->pagesizes=array(20,30,50,100);
		$order='status asc,id desc';
		if($this->_get('orderField')){
			$order=$this->_get('orderField').' '.$this->_get('orderDirection').',id desc';
		}
		if($this->_get('text')){
			$map['username']=array('like','%'.$this->_get('text').'%');
		}
		$this->total=$total = D('TixianView')-> where($map) -> count();
		$this->pagesize=$pagesize=$this->_get('pageSize','intval',$this->pagesizes[0]);	
		$this->pageCurrent=$pageCurrent=$this->_get('pageCurrent','intval',1);
		$data = D('TixianView') -> order($order) -> where($map)->page($pageCurrent.','.$pagesize)-> select();
		// liu($data);
		//print_r($data);die;
		$this ->assign('tixian',$data);
		$this -> display();
	}
	public function tixian_beizhu() {
		$this -> display();
	}
	public function tixianshenhe() {
		$id = intval($_POST['id']);
		$m = M('tixian');
		$time = time();
		$m -> data(array(
			'id' => $id
			,'beizhu' => $_POST['beizhu']
			,'handleDate' => $time
			,'status' => 1
		)) ->save();
		$info = M('tixian') -> where("id={$id}") -> field('uid,txjine')->find();
		$data = array(
			'foreign_key' => $id
			,'status' => 2
			,'total' => $info['txjine']
			,'ctime' => $time
			,'type' => 1
		);
		$data['username']=M('member')->where('id='.$info['uid'])->getField('username');
		$res = M('point') ->add($data);
		$res ? $this->json_die(1,'审核成功') : $this->json_die(0);
	}
	public function tixian_detail() {
		$id=$this->_get('id');
		$detail=M('tixian')->join('pchotel_member on pchotel_member.id= pchotel_tixian.uid')->where("pchotel_tixian.id={$id}")->field('pchotel_tixian.*,pchotel_member.username')->find();
		$this->assign('detail',$detail);
		$this -> display();
	}
	public function txdel() {
		$id = $_GET['id'] ? intval($_GET['id']) : '';
		$m = M('tixian');
		$res = $m -> where(array('id' => $id)) -> delete();
		$res ? $this -> json_die(1,'删除成功') : $this -> json_die(0);
	}
	public function tixian_banli() {
		if($_POST['btn_submit']){
			$id=$_POST['id'];
			$tx[id]=$id;
			$m = M('tixian');
			$point=$m-> where($tx)->getField('txjine');
			$uname=M('member')->where($tx)->join('pchotel_tixian on pchotel_member.id=pchotel_tixian.uid')->getField('username');
			$row = M('tixian')->where("id={$id}")->setField('blbeizhu',$_POST['blbeizhu']);
			$row *=M('tixian')->where("id={$id}")->setField('status',2);
			$row *=M('tixian')->where("id={$id}")->setField('banliDate',time());
			$row *=M('tixian')->where("id={$id}")->setField('banliren',$_POST['banliren']);
			$row ? $this->json_die(200,'审核成功') : $this->json_die(0);
		}else{
			$this -> display();
		}
	}
	//批量操作
    public function txpl() {
        $ids = $_GET['ids'];
        $map['id'] = array('in', $ids);
        //dump($map);die;
        $m = M('tixian');
        switch ($this->_get('type')) {
            case '1':   //批量放入回收站
                $rs = $m -> where($map) -> delete();
                break;
            case '2':   //批量关闭
                $rs = $m->where($map)->setField('is_delete', 2);
                break;
            
        }
        $rs ? $this->json_die(200) : $this->json_die(0);
    }
}
?>