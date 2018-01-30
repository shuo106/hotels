<?php
class BaseAction extends Action{	
	public function __construct(){		
		parent::__construct();
		if(empty($_SESSION['hotel_name']) || !isset($_SESSION['hotel_id'])){			
			// redirect(__ROOT__.'/Hotel/index.php/Login');
			header('Location: ' . __ROOT__.'/Hotel/index.php/Login');	
		} else {
			$this->lang=C('order');		
			$hotelid = session('hotel_id');		
			$map['acc_hotelid'] = $hotelid;		
			$this->count= M("accounts")->where($map)-> order("acc_id desc")->count();	
		}		
	}
		
}
?>