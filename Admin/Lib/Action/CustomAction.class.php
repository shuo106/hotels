<?php
class CustomAction extends CommonAction{
	function index(){
		$clist=M('custom')->order('tid asc,id desc')->select();
		$this->assign('list',$clist);
		$this->display();
	}
	/**
	 * [addcustom 自定义添加视图]
	 * @return [type] [description]
	 */
	public function add(){
		if($this->isGet()){
			if($this->_get('id')){
				$this->info=M('custom')->where('id='.$this->_get('id'))->find();
			}
			$this->display();
		}else{
			if(!$_POST['name']){
				$this->json(300,'自定义名称不能为空');
			}
			$data=array();
			$data['name']=$this->_post('name');
	        $data['tid']=$this->_post('tid','intval');
	        if($_POST['id']){
	        	//unset($data['tid']);
	            $rows=M('custom')->where(array('id'=>intval($_POST['id'])))->save($data);
	            $msg='编辑成功';
	        }else{
	        	$rows=M('custom')->data($data)->add();
	            $msg='添加成功';
	        }
	        $rows?$this->json(200,$msg,array('tabid' =>"".',Custom-index'.$this->_get('cid'),'closeCurrent' => true)):$this->json(0);
		}
	}
	/**
	 * [delcustom 自定义删除操作]
	 * @return [type] [description]
	 */
	public function delcustom(){
		if(!IS_AJAX){
			halt('非法操作');
		}
		$id=$this->_get('id','intval');
		if(!$id){
			halt('非法操作');
		}
		$res=M('custom')->where(array('id'=>$id))->delete();
		/*if($res){
			die(json_encode(array('status'=>1,'msg'=>'该属性已删除')));
		}else{
			die(json_encode(array('status'=>0,'msg'=>'操作失败')));
		} */
		$res ? $this->json(1,'删除成功') : $this->json(0,'删除失败');
	}	
}
?>