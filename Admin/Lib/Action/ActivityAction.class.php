<?php
/*
 *微信 大转盘、刮刮卡模块
 *
 */
class ActivityAction extends CommonAction {
	//大转盘
	public function bigwheel() {
		$big = M('bigwheel') -> where('id=1') -> find();
		$bigdata = json_decode($big['data'], TRUE);
		$this -> bigdata = mutisort($bigdata, 'sort', 1);
		$this -> click = $big['click'];
		$this -> display();
	}

	//大转盘参数保存
	public function SaveBigWheel() {
		if($click=$_GET['click']){
			$res=M('bigwheel') -> where("id=1") -> setField('click',$_POST['click']);
			$res ? $this -> json_die(1) : $this -> json_die(300,'无修改');
		}
		$temp=array();
		foreach ($_POST as $key => $value) {
			$temp[$key]=$value[0];
		}
		$data_0 = M('bigwheel') -> where('id=1') -> find();
		$data0 = json_decode($data_0['data'], 1);
		foreach ($data0 as $k => $v) {
			if ($v['id']==$temp['id']) {
				$data[$k]=$temp;
			}else{
				$data[$k]=$v;
			}
		}
		//print_r($_POST);die;
		$save['data'] = json_encode($data);
		$res = M('bigwheel') -> where("id=1") -> save($save);
		$res ? $this -> json_die(1) : $this -> json_die(0);
	}

	//删除大转盘
	public function delBigWheel(){
		$id=$_GET['id'];
		$big = M('bigwheel') -> where('id=1') -> find();
		$scratchdata = json_decode($big['data'], TRUE);
		foreach ($scratchdata as $k => $v) {
			if ($v['id']!=$id) {
				$data[]=$v;
			}
		}
		$save['data'] = json_encode($data);
		$res = M('bigwheel') -> where('id=1') -> save($save);
		$res ? $this -> json_die(1) : $this -> json_die(0);
	}	

	//大转盘测试
	public function bigwheeltest() {
		$bigwheel = M('bigwheel') -> find();
		$prize_arr = json_decode($bigwheel['data'], TRUE);
		foreach ($prize_arr as $key => $val) {
			$arr[$val['id']] = $val['v'];
		}
		$rid = $this -> getRand($arr);
		//根据概率获取奖项id
		$res = $prize_arr[$rid - 1];
		//中奖项
		$min = $res['min'];
		$max = $res['max'];
		if ($res['id'] == 7) {//七等奖
			$i = mt_rand(0, 5);
			$result['angle'] = mt_rand($min[$i], $max[$i]);
		} else {
			$result['angle'] = mt_rand($min, $max);
			//随机生成一个角度
		}
		$result['prize'] = $res['prize'];
		$result['id'] = $res['id'];
		echo json_encode($result);
	}

	function getRand($proArr) {
		$result = '';

		//概率数组的总概率精度
		$proSum = array_sum($proArr);

		//概率数组循环
		foreach ($proArr as $key => $proCur) {
			$randNum = mt_rand(1, $proSum);
			if ($randNum <= $proCur) {
				$result = $key;
				break;
			} else {
				$proSum -= $proCur;
			}
		}
		unset($proArr);

		return $result;
	}

	//获奖人
	public function winners() {
		$map['id'] = array('neq', 0);
		if ($_GET['type']) {
			$map['type'] = $this -> _get('type');
		}
		if ($_GET['wechatid']) {
			$map['wechatid'] = array('like', '%' . $this -> _get('wechatid') . '%');
		}
		if ($_GET['status']) {
			$map['status'] = $this -> _get('status');
		}
		//可选分页大小
		$this -> pagesizes = array(20, 30, 50, 100);
		$order = 'id desc';
		if ($this -> _get('orderField')) {
			$order = $this -> _get('orderField') . ' ' . $this -> _get('orderDirection') . ',id desc';
		}
		$this -> total = $total = M('winners') -> where($map) -> count();
		$this -> pagesize = $pagesize = $this -> _get('pageSize', 'intval', $this -> pagesizes[0]);
		$this -> pageCurrent = $pageCurrent = $this -> _get('pageCurrent', 'intval', 1);
		$this -> winners = M('winners') -> where($map) -> order($order) -> page($pageCurrent . ',' . $pagesize) -> select();
		$this -> display();
	}

	//刮刮卡
	public function scratchcard() {
		$big = M('bigwheel') -> where('id=7') -> find();
		$scratchdata = json_decode($big['data'], TRUE);
		$this -> scratchdata = mutisort($scratchdata, 'sort', 1);
		$this -> click = $big['click'];
		$this -> display();
	}

	//大转盘参数保存
	public function SaveScratchCard() {
		if($click=$_GET['click']){
			$res=M('bigwheel') -> where("id=7") -> setField('click',$_POST['click']);
			$res ? $this -> json_die(1) : $this -> json_die(300,'无修改');
		}
		$temp=array();
		foreach ($_POST as $key => $value) {
			$temp[$key]=$value[0];
		}
		$data_0 = M('bigwheel') -> where('id=7') -> find();
		$data0 = json_decode($data_0['data'], 1);
		foreach ($data0 as $k => $v) {
			if ($v['id']==$temp['id']) {
				$data[$k]=$temp;
			}else{
				$data[$k]=$v;
			}
		}
		//print_r($_POST);die;
		$save['data'] = json_encode($data);

		$res = M('bigwheel') -> where("id=7") -> save($save);
		$res ? $this -> json_die(1) : $this -> json_die(0);
	}

	//刮刮乐参数保存
	public function SaveScratchCards() {
		//转化成json格式真蛋疼，array_map转置后，键名没了，再foreach给加上
		$trans_arr = array_map(null, $_POST['id'], $_POST['sort'], $_POST['prize'], $_POST['award'], $_POST['nums'], $_POST['v'], $_POST['click']);
		$keys_arr = array('id', 'sort', 'prize', 'award', 'nums', 'v', 'click');
		$data = array();
		foreach ($trans_arr as &$v) {
			$thisid = $v[0];
			foreach ($v as $kk => &$vv) {
				$data[$thisid][$keys_arr[$kk]] = $v[$kk];
			}
		}
		$save['data'] = json_encode($data);
		$res = M('bigwheel') -> where('id=7') -> save($data);
		$res ? json_die(1) : json_die(0);
	}

	//刮刮乐次数保存
	public function SaveScratchCardnum() {
		$id=$_POST['id'];
		$data['click'] = $this -> _post('click');
		$res = M('bigwheel') -> where('id='.$id) -> save($data);
		$res ? json_die(1) : json_die(0);
	}
	//刮刮乐规则删除
	public function delScratchCard() {
		$id=$_GET['id'];
		$big = M('bigwheel') -> where('id=7') -> find();

		$scratchdata = json_decode($big['data'], TRUE);
		foreach ($scratchdata as $k => $v) {
			if ($v['id']!=$id) {
				$data[]=$v;
			}
		}
		$save['data'] = json_encode($data);
		$res = M('bigwheel') -> where('id=7') -> save($save);
		$res ? json_die(1) : json_die(0);
	}
	//奖项删除
	public function delete() {
		$map['id'] = $this -> _get('id');
		$res = M('winners') -> where($map) -> delete();
		$res ? json_die(1) : json_die(0);
	}
	public function change_status(){
		$status_arr=array('得奖使用状态',2,1);
		$data['id']=$_GET['id'];
		$data['status']=$status_arr[$_GET['status']];
		$res=M('winners')->save($data);
		$res ? json_out(1) : json_out(0);
	}
	public function pl() {
		$id = $_GET['ids'];
		if (!$id) {
			$this->json_die(0,"请选择批量的数据");
		}
		$map['id'] = array('in',$id);
		$row = M('winners') -> where($map) -> delete();
		if ($row) {
			$this->json_die(1);
		} else {
			$this->json_die(0);
		}
	}
	public function add(){
		if ($_POST) {
			if($_POST['prize'] ==''){
				$this -> json_die(0, '中奖等级不能为空');
			}
			if($_POST['award'] ==''){
				$this -> json_die(0, '奖品名称不能为空');
			}
			$id=$_POST['tid'];
			$b=M('bigwheel')->where('id='.$id)->getField('data');
			if ($b) {
				$c=json_decode($b,true);
				$data = array();
				foreach ($c as $k => $v) {
					$k=$k+1;
					if ($k==$v['id']) {
						$data[]= $v;
					}
				}
				$num=count($c);
			}
			$num=$num?$num+1:1;
			$_POST['id']=$num;
			$data[]= $_POST;
			//print_r($data);die; 
			$save['data'] = json_encode($data);
			$res = M('bigwheel') -> where('id='.$id) -> save($save);
			//$res ? $this -> json_die(1) : $this -> json_die(0);
			if ($id==1) {
				$res ? $this->json_die(200,'操作成功',array('tabid' => $_GET['dialogId'] . ',Activity-bigwheel', 'closeCurrent' => true)) : $this->json_die(0);
			}else{
				$res ? $this->json_die(200,'操作成功',array('tabid' => $_GET['dialogId'] . ',Activity-scratchcard', 'closeCurrent' => true)) : $this->json_die(0);
			}
			
		}
		$this->display();
	}
}
?>