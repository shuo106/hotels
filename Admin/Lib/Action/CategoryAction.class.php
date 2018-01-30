<?php
/**
 * 网站栏目控制器
 */
class CategoryAction extends CommonAction {
	/**
	 * [index 网站导航添加视图]
	 * @return [type] [description]
	 */
	public function index() {
		$arr = M('daohang') -> select();
		$clist = $this -> rec($arr, 0);
		//执行递归函数重组数组 获得各个导航之间的关系
		//p($clist);
		//编辑操作
		if ($_GET['id']) {
			$id = intval($_GET['id']);
			$this->assign('id',$id);
			$fields = array('name', 'description', 'pid');
			$map['id'] = $id;
			$this->assign('pid',M('daohang') -> where($map) -> getField('pid'));
			$this->assign('cateinfo',M('daohang') -> where($map) -> find());
		}
		//添加子级栏目操作
		if ($_GET['pid']) {
			$pid = intval($_GET['pid']);
			$this->assign('pid',$pid);
		}
		$this->assign('list',$clist);
		$this -> display();
	}

	/**
	 * [add 网站导航添加]
	 */
	public function add() {
		if (isset($_POST['btn_submit'])) {
			$title = '/^.{1,50000}$/';
			if (preg_match($title, $_POST['name']) == 0) {
				$this -> json(300, '名称不能为空');
			}
			if ($_POST['type'] == 'no') {
				$this -> json(300, '请选择导航类型');
			}
			$m = M('daohang');
			$data = array();
			$data['name'] = $this -> _post('name');
			$data['pid'] = $this -> _post('pid', 'intval');
			$data['description'] = $this -> _post('description');
			$data['url'] = $_POST['url'];
			$data['type'] = $_POST['type'];
			//区分单页面,导航,新闻栏目的字段
			if ($_POST['id']) {
				$id = intval($_POST['id']);
				$msg = '编辑成功';
				$rows = $m -> where(array('id' => $id)) -> data($data) -> save();
			} else {
				$data['is_show'] = 1;
				$rows = $m -> data($data) -> add();
				$msg = '添加成功';
			}
					
			$rows ? $this -> json_die(1, $msg, array('close'=>1,'id'=>"Category-chanellists")) : $this -> json(0);
		} else {
			halt('非法操作');
		}
	}

	/**
	 * [lists 网站栏目列表视图]
	 * @return [type] [description]
	 */
	public function chanellists() {
		$arr = M('daohang') -> order('type asc,sort desc') -> select();
		//重组子父级关系
		$arr = formatlev($arr, 0, '--', 0);
		if ($this -> _get('orderField')) {
			$mode = ($this -> _get('orderDirection') == 'asc') ? 1 : 0;
			$arr = mutisort($arr, $this -> _get('orderField'), $mode);
		}
		import('ORG.Util.Page');
		$total ==count($arr);
		$this ->assign('total',count($arr));
		$this->assign('pagesizes',array(20, 30, 50, 100));
		$pagesize ==$this -> _get('pageSize', 'intval', $this -> pagesizes[0]);
		$this ->assign('pagesize',$this -> _get('pageSize', 'intval', $this -> pagesizes[0]));
		$pageCurrent ==$this -> _get('pageCurrent', 'intval', 1);
		$this ->assign('pageCurrent',$this -> _get('pageCurrent', 'intval', 1));
		$page = new Page($total, $pagesize);
		$start = ($pageCurrent - 1) * $pagesize;
		$arr = array_slice($arr, $start, $pagesize);
		$this->assign('list',$arr);
		$this -> display();
	}

	/**
	 * [childlist 子级栏目列表]
	 * @return [type] [description]
	 */
	public function childlist() {
		echo '赞未开通此功能';
	}

	/**
	 * [sort 栏目排序]
	 * @return [type] [description]
	 */
	public function sort() {
		$res=0;
		foreach ($_POST['sort'] as $id => $sort) {
			$res+=M('daohang') -> where(array('id' => $id)) -> setField('sort', $sort);
		}
		$res ? $this -> json(1) : $this -> json(0);
	}

	/**
	 * [isshow 网站导航是否显示]
	 * @return [type] [description]
	 */
	public function isshow() {
		if (!IS_AJAX) {
			halt('页面不存在');
		}
		if (isset($_GET['id']) && $_GET['id']) {
			$temp = explode('_', $_GET['id']);
			$id = intval($temp[0]);
			$map['id'] = $id;
			if ($temp[1]) {
				$rows = M('daohang') -> where($map) -> setField('is_show', 1);
				$msg = '该导航已显示';
			} else {
				$rows = M('daohang') -> where($map) -> setField('is_show', 0);
				$msg = '该导航已关闭';
			}
			$rows ? $this -> json(200, $msg) : $this -> json(0);
		} else {
			halt('非法操作');
		}
	}

	/**
	 * [tochanel 单页面设为导航的操作]
	 * @return [type] [description]
	 */
	public function tochanel() {
		if (!IS_AJAX) {
			halt('页面不存在');
		}
		if ($_GET['id']) {
			$temp = array();
			$temp = explode('_', $_GET['id']);
			$id = intval($temp[0]);
			$type = intval($temp[1]);
			//p(M('category'));
			$rows = M('category') -> where(array('id' => $id)) -> setField('is_menu', $type);
			$rows ? $this -> json(1) : $this -> json(0);
		} else {
			halt('非法操作');
		}
	}

	public function del() {
		if (!IS_AJAX) {
			halt('页面不存在');
		}
		if ($_GET['id']) {
			$id = intval($_GET['id']);
			$result = $this->rec($this->chanel, $id);
			//通过递归函数得到该栏目的所有子类
			$ids = array();
			$ids[] = $id;
			for ($i = 0; $i < count($result); $i++) {
				$ids[] = $result[$i]['id'];
			}
			$map['id'] = array('in', $ids);
			$rows = M('daohang') -> where(array('id' => $id)) -> delete();
			$rows ? $this -> json(1) : $this -> json(0);
		}
	}

}
?>