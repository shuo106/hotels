<?php
class AccountsAction extends CommonAction{
	public function index(){
		if($_GET['title'] && $_GET['title'] !=''){
			$where['acc_title'] = array('like', '%' . $_GET['title'] . '%');
		}
		$where['acc_hotelid'] = $_GET['id'];
		$this->id = $_GET['id'];
		// dump($where);exit;
		$this -> pagesizes = array(20, 30, 50, 100);
		$this -> total = M("accounts")->where($where)->count();
		$this -> pagesize = $pagesize = $this -> _get('pageSize', 'intval', $this -> pagesizes[0]);
		$this -> pageCurrent = $pageCurrent = $this -> _get('pageCurrent', 'intval', 1);
		$val = M("accounts")->where($where)->order("acc_id desc")->page($pageCurrent . ',' . $pagesize)->select();
		$this->list = $val;
		$this ->display();
	}
	public function accounts(){
		$id = $_GET['id'];
		$this->info = M("member_hotel")->where("hotelid=$id")->find();
		$this->display();
	}
	public function saveaccounts(){
		if(isset($_POST['btn_submit'])){
			//判断选择的年月是否存在订单，如果不存在不能生成订单 
			$id = $_POST['id'];
			$starts = $_POST['start'];
			$ends = $_POST['end'];
			if($starts ==''){
				$this->json(300,'请选择开始时间');
			}
			if($ends ==''){
				$this->json(300,'请选择结束时间');
			}
			$hotelname = $_POST['hotelname'];

			// $time = $year.'-'.$month;
			// $times = strtotime($time);
			// $firstday = date('Y-m-01', $times);
	  		// $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
			//获取选择月份的开始和结束
			//转化时间戳
			$start = strtotime($starts);
			$end = strtotime($ends);
			//判断选择的年月是否存在账单，如果存在，不能再次生成
			$val = M("accounts")->where("acc_hotelid=$id and acc_start=$starts and acc_end=$ends")->find();
			if($val){
				$this->json(300,'已经存在账单，无法再次生成！');
			}else{
				//根据条件获取订单
				$orders = M("order")->where("hotelid=$id and lidiandate >=$start and lidiandate <=$end and status = 7")->select();
				if($orders){
					$oid = array();
					foreach($orders as $k=>$v){
						$oid[] = $v['addtime'].$v['orderid'];
					}
					$orderid = implode(",",$oid); 
					$data['acc_title']  = $starts.'至'.$ends."对账单";
					$data['acc_hotelid'] = $id;
					$data['acc_start']  = $starts;
					$data['acc_end']    = $ends;
					$data['acc_order']  = $orderid;
					$data['acc_total']  = M("order")->where("hotelid=$id and lidiandate >=$start and lidiandate <=$end and status = 7")->sum('shoufei');
					$data['acc_hotelname'] = $hotelname;
					$data['acc_addtime'] = time();
					$res = M("accounts")->add($data);
					if($res){
						foreach($orders as $ks=>$vs){
							$map['accid']      = $res;
							$map['oid']        = $vs['orderid'];
							$map['yd_start']   = $vs['ruzhudate'];
							$map['yd_end']     = $vs['lidiandate'];
							$map['status']     = $vs['status'];
							$map['total']      = $vs['shoufei'];
							$map['nums']       = $vs['nums'];
							$map['yd_name']    = $vs['linkman'];
							$map['roomid']     = $vs['roomid'];
							$map['roomname']   = M("room")->where("id={$vs['roomid']}")->getField('roomtype');
							$map['yd_tel']     = $vs['telephone'];
							$map['yd_addtime'] = $vs['addtime'];
							$map['yd_from']    = $vs['from'];
							M("acc_list")->add($map);
						}
						$this->json(200,'操作成功',array('tabid' => $_GET['dialogId'] . ',Accounts-accounts', 'closeCurrent' => true));
					}else{
						$this->json(0);
					}
				}else{
					$this->json(300,'没有该时间短完成的订单，无法生成账单！');
				}
			}
		}

	}
	//账单列表
	public function lists(){
		if ($_GET['text']) {
			$map['acc_hotelname'] = array('like', '%' . $_GET['text'] . '%');
		}
		if($_GET['title']){
			$map['acc_title'] = array('like', '%' . $_GET['title'] . '%');
		}
		if ($_GET['status'] !=0) {
			$map['acc_status'] = $_GET['status'];
		}
		$this -> pagesizes = array(20, 30, 50, 100);
		$this -> total = M("accounts")->where($map)->count();
		$this -> pagesize = $pagesize = $this -> _get('pageSize', 'intval', $this -> pagesizes[0]);
		$this -> pageCurrent = $pageCurrent = $this -> _get('pageCurrent', 'intval', 1);
		$list = M("accounts")->where($map)->order("acc_id desc")->page($pageCurrent . ',' . $pagesize)->select();
		$this->list = $list;
		$this ->display('list');
	}

	//查看账单
	public function chakan(){
		$id = $_GET['id'];
		if($_GET['id']){
			$where['accid'] = $id;
		}
		if($_GET['text']){
			$where['oid']=substr($_GET['text'],10);
		}
		$this->info = M("accounts")->where("acc_id=$id")->find();
		$this->nums = M("acc_list")->where($where)->count();
		$this -> pagesizes = array(20, 30, 50, 100);
		$this -> total = M("acc_list")->where("accid=$id")->count();
		$this -> pagesize = $pagesize = $this -> _get('pageSize', 'intval', $this -> pagesizes[0]);
		$this -> pageCurrent = $pageCurrent = $this -> _get('pageCurrent', 'intval', 1);
		$list = M("acc_list")->where($where)->page($pageCurrent . ',' . $pagesize)->select();
		$this->list = $list;
		$this->totals = M("acc_list")->where($where)->sum('total');
		$this ->display();
	}

	//修改账单
	public function edit(){
		$id = $_GET['id'];
		$this->info = M("accounts")->where("acc_id=$id")->find();
		// dump($this->info);exit;
		$this ->display();
	}

	public function update(){
		$wh['acc_id']= $this->_post('id');
		$data['acc_beizhu'] = $this->_post('beizhu');
		$data['acc_status'] = $this->_post('status');
		$rs=M('accounts')->where($wh)->save($data);
		if($rs){
			$this->json(200);
		}else{
			$this->json(0);
		}
	}

	//删除账单
	public function delete(){
		$id = $_GET['id'];
		$res = M("accounts")->where("acc_id=$id")->delete();
		$val = M("acc_list")->where("accid=$id")->delete();
		if($res && $val){
			$this->json(200);
		}else{
			$this->json(0);
		}

	}

	//编辑账单下面的订单
	public function edit_od(){
		$id = $_GET['id'];
		$this->info = M("acc_list")->where("id=$id")->find();
		$this ->display();
	}

	public function update_od(){
		$wh['id']= $this->_post('id');
		$accid = M('acc_list')->where($wh)->getField('accid');
		$data['yd_addtime'] = strtotime($this->_post('addtime'));
		$data['yd_start'] = strtotime($this->_post('start'));
		$data['yd_end'] = strtotime($this->_post('end'));
		$data['nums'] = $this->_post('nums');
		$data['total'] = $this->_post('total');
		$data['yd_status'] = 1;
		$rs = M('acc_list')->where($wh)->save($data);

		//获取账单id
		$data2['acc_total'] = M("acc_list")->where("accid=$accid")->sum('total');
		if($rs){
			M("accounts")->where("acc_id=$accid")->save($data2);
			$this->json(200);
		}else{
			$this->json(0);
		}
	}

	//删除账单下面的订单
	public function delete_od(){
		$id = $_GET['id'];
		$res = M("acc_list")->where("id=$id")->delete();
		if($res){
			$this->json(200);
		}else{
			$this->json(0);
		}
	}

	public function outExcel(){
		import("ORG.excel.PHPExcel");
		$field = array('oid','roomname','yd_name','yd_tel','yd_addtime','yd_start','total','status','yd_from');
		$tableheader=array('订单编号','预订房间','预订姓名','预订电话','预订时间','入住时间','应付金额','订单状态','订单来源');

		$accid = $_GET['id'];
		$info = M("accounts")->where("acc_id=$accid")->find();
		$count = M('acc_list')->where("accid = $accid")->count();
		$data  = M('acc_list')->where("accid = $accid")
				  			  ->field($field)
							  ->order('oid desc')
							  ->select();
		foreach ($data as $k=> $v) {
            if(empty($v['yd_name'])){
                $data[$k]['yd_name'] = "空";
            }
            if(empty($v['roomname'])){
                $data[$k]['roomname'] = "空";
            }
        }
        $newArr = array();
        foreach($data as $key=>$value){
            $newArr[] = $value;
        }
		$excel = new PHPExcel();
        $str='A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';
        $letters = explode(',',$str);
        $length = count($field);
        $letter = array_slice($letters,0,$length);
        $excel->getActiveSheet()->setCellValue('A1', $info['acc_hotelname'].$info['acc_start'].'至'.$info['acc_end'].'账单:订单列表 ( '.$count.'个) (总金额：'.$info['acc_total'].')  导出日期：'.date('Y-m-d',time()));
        $excel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(18)->setBold(true);
        $excel->getActiveSheet()->getStyle( 'A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $excel->getActiveSheet()->getStyle( 'A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //$excel->getActiveSheet()->setCellValue('A2', '导出日期：'.date('Y-m-d',time()));
        $excel->getActiveSheet()->mergeCells('A1:'.end($letter).'1');
        //$excel->getActiveSheet()->mergeCells('A2:'.end($letter).'2');
        $excel->getActiveSheet()->getColumnDimension( 'A')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension( 'B')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension( 'C')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension( 'D')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension( 'E')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension( 'F')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension( 'G')->setWidth(25);
        $excel->getActiveSheet()->getColumnDimension( 'H')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension( 'I')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension( 'J')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension( 'K')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension( 'L')->setWidth(30);
        $excel->getActiveSheet()->getRowDimension(2)->setRowHeight(20);
        $excel->getActiveSheet()->getRowDimension(3)->setRowHeight(30);
        for($i=0;$i<$length;$i++){
            $excel->getActiveSheet()->getStyle( $letter[$i].'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $excel->getActiveSheet()->getStyle( $letter[$i].'2')->getFont()->setSize(15)->setBold(true);
        }
        for($i=0;$i<count($tableheader);$i++){
            $excel->getActiveSheet()->setCellValue("$letter[$i]2","$tableheader[$i]");
        }
        for($i = 3 ; $i <= count( $newArr ) + 2 ; $i ++) {
            $j=0;
            $excel->getActiveSheet()->getRowDimension($i)->setRowHeight(30);
            foreach( $newArr[$i - 3] as $k=>$v ) {
                if($v){
                    if($k=='oid'){
                        $tmp=' '.$newArr[$i - 3]['yd_addtime'].$newArr[$i - 3]['oid'];
                        $excel->getActiveSheet()->setCellValue("$letter[$j]$i","$tmp");
                    }elseif($k=='yd_from'){
                        switch ($v) {
                            case 5:
                                $tmp='网站';
                                break;
                            case 1:
                                $tmp='手机';
                                break;
                            case 2:
                                $tmp='微信';
                                break;
                            case 3:
                                $tmp='app';
                                break;
                        }
                    }elseif($k=='status'){
                        $status_arr=array('订单状态','未确定','已确认','未付款','已付款','已入住','已取消','已离店');
                        $tmp=$status_arr[$v];
                    }elseif($k=='yd_addtime'){
                        $tmp=date('Y-m-d',$v);
                    }elseif($k=='yd_start'){
                        $tmp=date('Y-m-d',$v);
                    }else{
                        $tmp=$v;
                    }
                    $excel->getActiveSheet()->setCellValue("$letter[$j]$i","$tmp");
                    $excel->getActiveSheet()->getStyle( $letter[$j].$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $j++;
                }
            }
        }

       $excel->getActiveSheet()->getColumnDimension('B')->setWidth(40); 

         
        $write = new PHPExcel_Writer_Excel5($excel);
        header("Pragma:public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="booking'.date('Y-m-d',time()).'.xls"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
	}
}
?>