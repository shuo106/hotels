<?php
// 本类由系统自动生成，仅供测试用途
class IndexAction extends BaseAction {
    public function index(){
    	//订单数量
    	$list=M('order')->where('hotelid='.session('hotel_id'))->field('status')->select();
    	foreach ($list as $v) {
    		$nums[$v['status']]++;
    	}
    	$this->ordernums=$nums;
    	//最新订单
    	$data['pchotel_order.hotelid']=session('hotel_id');
		$content=M('order')->join('pchotel_room ON pchotel_order.roomid = pchotel_room.id')
						->field('pchotel_room.roomtype,pchotel_order.*')
						->where($data)
						->order('orderid desc')
						->limit(5)
						->select();
		$this->neworder=$content;    
		//最新两条点评
	
	  $content=M('comment')
	            ->where("hotelid='".session('hotel_id')."'")
	            ->order("id DESC")
	            ->limit(2,0)
	            ->select();
		foreach ($content as &$v) {
			$v['username']=M('member')->where('id='.$v['uid'])->getField('username');
			$v['src']=M('member')->where(array('id'=>$v['uid']))->getField('icon');
		}
	


		$this->assign('newcomment',$content);				
		$this->display();
    }
	public function loginout(){
		session('hotel_name',null);
		session_destroy();
		redirect(__ROOT__.'/Hotel/index.php/Login');
	}
}