<?php
class  DataAction extends BaseAction{
	public function index(){
		$this->display();
	}
	//订单来源
	public function from(){
		$config=C('order');
		$data['list']=array_values($config['from']);
		$order=M('order')->where('hotelid='.session('hotel_id'))->field('from')->select();
		foreach ($order as $v) {
			$nums[$v['from']]++;
		}
		foreach ($config['from'] as $k => $v) {
			$data['order'][]=array(
				'value'=>$nums[$k]?$nums[$k]:0,
				'name'=>$v
			);
		}
		die(json_encode($data));
	}
	//月视图
	public function month(){
		$config=C('order');
		$map['hotelid']=session('hotel_id');
		$start=mktime(0,0,0,date('m'),1,date('Y'));
		$end=mktime(23,59,59,date('m'),date('t'),date('Y'));
		$map['start']=array('BETWEEN',array($start,$end));
		$order=M('order')->where($map)->field('status,start')->select();
		foreach ($order as $v) {
			$ordernum[$v['status']][]=$v['start'];
		}
		$data['list']=array_values($config['status']);
		foreach ($data['list'] as $v) {
			$data['show'][$v]=in_array($v,array('总订单','已付款','已入住'));
		}
		$data['show']['总订单']=false;
		for($day = date("t") ; $day > 0 ; $day-- ) {
			$data['month'][$day]=$day.'号';
		}
		$data['month']=array_reverse($data['month']);
		$y=date('Y');
		$m=date('m');
		foreach ($config['status'] as $k1=>$v) {
			$nums=array();
			foreach ($data['month'] as $k => $val) {
				if(!$nums[$k])$nums[$k]=0;
				$time=strtotime($y.'-'.$m.'-'.$k);
				foreach ($ordernum[$k1] as $k2 => $v2) {
                                    if($v2==$time)$nums[$k]++;
				}
			}
			$data['data'][]=array(
				'name'=>$v,
				'type'=>'line',
				'data'=>$nums
			);	
		}
		die(json_encode($data));
	}
}