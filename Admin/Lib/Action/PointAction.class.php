<?php
/*
	积分流水帐
*/
class PointAction extends CommonAction{
	function lists(){
		if($_GET['text']){
			$map['username']=$this->_get('text');
		}
		if($_GET['status']){
			$map['status']=$this->_get('status');
		}	
		if ($this -> _get('orderField')) {
			$order = $this -> _get('orderField') . ' ' . $this -> _get('orderDirection') . ',id desc';
		}else{
			$order = 'id desc';
		}		
		$this -> total = $total = M('point') -> where($map) -> count();
		$this -> pagesize = $pagesize = $this -> _get('pageSize', 'intval', $this -> pagesizes[0]);
		$this -> pageCurrent = $pageCurrent = $this -> _get('pageCurrent', 'intval', 1);
		$lists= M('point')->where($map)
						->order('pchotel_point.id desc')
						->page($pageCurrent . ',' . $pagesize)
						->select();
		foreach ($lists as &$v) {
			if($v['type']==0){
				$rs=M('order')->where("orderid={$v['foreign_key']}")->field('addtime,orderid')->find();
				$v['order_no']=$rs['addtime'].$rs['orderid'];
			}
		}		
		$this->lists=$lists;				
		$this->display();
	}
	//删除
	public function del(){
		$map['id']=array('in',$_GET['id']);
		$res= M('point')->where($map)->delete();
		$res?$this->json():$this->json(0);
	}
	//批量删除
	public function pi() {
		if (!IS_AJAX) {
			$this->json(300,'页面不存在');
		}
		$ids = $this -> _get('ids');
		$type=$this->_get('type');
		if (empty($ids) || !is_string($ids)) {
			$this->json(300,'非法操作');
		}
		$map['id'] = array('in', $ids);
		$rows = M('point') -> where($map) -> delete();
		$rows ? $this -> json(1) : $this -> json(0);
	}	
}
?>