<?php
/*
*微信模块
*
*/
class WechatAction extends CommonAction
{
   	private $weObj;
 	public function _initialize(){
		import("ORG.Util.Wechat");// 导入微信类
		$wxconfig = M('wxconfig')->find();
		  $options = array(
			'token'=>$wxconfig['Token'], 			//填写你设定的key
			'appid'=>$wxconfig['Appid'], 				//填写高级调用功能的app id
			'appsecret'=>$wxconfig['AppSecret'], 		//填写高级调用功能的密钥
		);
		$this->weObj = new Wechat($options); 
	} 
	//微信配置
	public function index(){
		if ($this->isPost()) {
			$wh['id']			=	1;
			$data['Token']		= trim($this->_post('Token'));
			$data['Appid']		= trim($this->_post('Appid'));
			$data['AppSecret']	= trim($this->_post('AppSecret'));
			$data['issend']		= $this->_post('issend');
			$data['smsname']	= $this->_post('smsname');
			$data['smspwd']		= $this->_post('smspwd');
			$data['mobile']		= $this->_post('mobile');
			$data['smscon']		= trim($this->_post('smscon'));
			$res = M('wxconfig')->where($wh)->save($data);
			$res?$this->json(1):$this->json(0);
		}else{
			$this->config = M('wxconfig')->find();
			$this->display('config');
		}
	}	
	//微信配置
	public function msgconfig(){
		$this->config = M('wxconfig')->find();
		$this->display();
	}
	//文字回复
	public function textreply(){
		//可选分页大小
		$this -> assign('pagesizes', array(20, 30, 50, 100));
		$order = 'a.id desc';
		if ($this -> _get('orderField')) {
			$order = $this -> _get('orderField') . ' ' . $this -> _get('orderDirection');
		}
		$total = M('wxrule') -> count();
		$this -> assign('total', M('wxrule') -> count());
		$pagesize = $this -> _get('pageSize', 'intval', $this -> pagesizes[0]);
		$this -> assign('pagesize', $this -> _get('pageSize', 'intval', $this -> pagesizes[0]));
		$pageCurrent = $this -> _get('pageCurrent', 'intval', 1);
		$this -> assign('pageCurrent', $this -> _get('pageCurrent', 'intval', 1));
		$where['a.type'] = 1;
		$this -> textreply = M('wxrule a') -> where($where) -> join('pchotel_wxtreply b ON  a.id = b.rid') -> order($order) -> page($pageCurrent . ',' . $pagesize) -> field('a.*,b.content,b.rid') -> select();
		$this -> display();
	}	
	//执行保存文字规则
	public function doSaveTextReply() {
		$rule = M('wxrule');
		$treply = M('wxtreply');
		$id = $this -> _post('id');
		$content = $this -> _post('content');
		$rid = $rid ? $rid : $id;
		if ($id) {
			if($_POST['name'] ==''){
				$this -> json(300, '规则名称不能为空');
			}
			if($_POST['key'] ==''){
				$this -> json(300, '触发关键字不能为空');
			}
			$data['key'] = trim($this -> _post('key'));
			$data['id'] = array('neq', $id);
			$iskey = $rule -> where($data) -> find();
			$iskey && $this->json(300, '触发关键字重复');
			$rdata['name'] = $this -> _post('name');
			$re = $rule -> where('id=' . $id) -> save($rdata);
			$tdata['content'] = $content;
			if ($treply -> where('rid=' . $rid) -> find()) {
				$res = $treply -> where('rid=' . $rid) -> save($tdata);
			} else {
				$tdata['rid'] = $rid;
				$res = $treply -> where('rid=' . $rid) -> add($tdata);
			}
			($res || $re) ? $this->json(200, '操作成功', array('tabid' => $_GET['dialogId'] . ',Wechat-textreply', 'closeCurrent' => true)) : $this->json(0);
		} else {
			if($_POST['name'] ==''){
				$this -> json(300, '规则名称不能为空');
			}
			if($_POST['key'] ==''){
				$this -> json(300, '触发关键字不能为空');
			}
			$data['key'] = trim($this -> _post('key'));
			$iskey = $rule -> where($data) -> find();
			$iskey && $this->json(300, '触发关键字重复');
			$data['name'] = $this -> _post('name');
			$data['type'] = 1;
			$rid = $rule -> add($data);
			if ($content) {
				$data = array();
				$data['rid'] = $rid;
				$data['content'] = $content;
				$row = $treply -> where('rid=' . $rid) -> add($data);
				!$row ? $this->json(0) : $this->json(200, '操作成功', array('tabid' => $_GET['dialogId'] . ',Wechat-textreply', 'closeCurrent' => true));
			} else {
				!$rid ? $this->json(0) : $this->json(200, '操作成功', array('tabid' => $_GET['dialogId'] . ',Wechat-textreply', 'closeCurrent' => true));
			}
		}
	}	
	//图文回复
	public function imagetextreply(){
		$this->reply = M('wxrule')
					->where('type =2')
					->join('pchotel_wxitreply ON pchotel_wxrule.id = pchotel_wxitreply.rid')
					->field('pchotel_wxrule.*,content,rid,title,content,url,thumb,description')
					->group('pchotel_wxrule.id')
					->select();
		$this->display();
	}	
	//自定义菜单
	public function menu(){
		$mfrom = M('wxmenu');
		$menus=$mfrom->where('pid=0')->select();
		if(!$menus){
			$menu =$this->weObj->getMenu();
			foreach($menu['menu']['button'] as $k=>$v){
				$m1['pid']=0;
				$m1['name']=$v['name'];
				$pid = $mfrom->add($m1);
				if($v['sub_button']){
					foreach($v['sub_button'] as $k2=>$v2){
						$m2['pid']=$pid;
						$m2['name']=$v2['name'];
						$m2['type']=$v2['type'];
						if($v2['type'] =='click'){
							$m2['value']= $v2['key'];
						}else{
							$m2['value']= $v2['url'];
						}
						$mfrom->add($m2);
					}
				}
			}
			$menus=$mfrom->where('pid=0')->select();
		}
		foreach($menus as $k=>&$v){
			$map['pid']= $v['id'];
			$son=$mfrom->where($map)->select();
			if($son){
				$v['son']=$son;
			}
		}
		$this->menu=$menus;
		$this->display();
	}	
	//粉丝管理
	public function fans(){
		import("ORG.Util.Page");// 导入分页类
		$fans = $this->weObj->getUserList();
		$getGroup = $this->weObj->getGroup();
		$this->groups=$getGroup['groups'];
		$newfans=array();
		foreach($fans['data']['openid'] as $v){
			$f=$this->weObj->getUserInfo($v);
			$groupid =$this->weObj->getUserGroup($v);
			$f['groupid']=$groupid['groupid'];
			if($f['headimgurl']){
				$f['headimgurl']=substr($f['headimgurl'],0,-1).'46';
			}
			$newfans[]= $f;
		}
		$Page = new Page(count($newfans),10);// 实例化分页类 传入总记录数和每页显示的记录数
		$show = $Page->show();// 分页显示输出
		$ff= array_slice($newfans,$Page->firstRow,$Page->listRows);
		$this->fans=$ff;
		$this->assign('page',$show);
		$this->display();
	}	
	//消息管理
	public function message(){
		$this->display();
	}	
	//分组设置
	public function setGroup(){
		$uid=$this->_get('uid');
		$gid=$this->_get('gid');
		$res = $this->weObj->updateGroupMembers($gid,$uid);
		if($res){
			die(json_encode(array('status'=>1,'msg'=>'操作成功！')));
		}else{
			die(json_encode(array('status'=>0,'msg'=>'操作失败')));
		}
	}
	//自定义菜单创建与删除
	public function setMenu(){
				$m=M('wxmenu');
		$menu =$m->where('pid=0')->select();
		$nmenu=array();
		foreach($menu as $k=>$v){
			$map['pid']=$v['id'];
			$son = $m->where($map)->select();
			if($son){
				$nmenu[$k]['name']=$v['name'];
				$sub_button=array();
				foreach($son as $k2=>$v2){
					$sub_button[$k2]['type']=$v2['type'];
					$sub_button[$k2]['name']=$v2['name'];
					if($v2['type']=='view'){
						$sub_button[$k2]['url']=$v2['value'];
					}else{
						$sub_button[$k2]['key']=$v2['value'];
					}
				}
				$nmenu[$k]['sub_button']=$sub_button;
			}else{
				$nmenu[$k]['type']=$v['type'];
				$nmenu[$k]['name']=$v['name'];
				if($v['type']=='view'){
					$nmenu[$k]['url']=$v['value'];
				}else{
					$nmenu[$k]['key']=$v['value'];
				}	
			}
		}
		$newmenu = array();
		$newmenu ['button']=$nmenu;
		if($menu){
			$res = $this->weObj->createMenu($newmenu);
		}else{ //如果没有数据则认为是删除操作
			$res = $this->weObj->deleteMenu();
		}
		$res?$this>json(1):$this->json(0);
	}
	//菜单删除
	public function delMenu() {
		$map['id|pid'] = $this -> _get('id');
		$res = M('wxmenu') -> where($map) -> delete();
		$res ? $this->json(1) : $this->json(0);
	}
	private function getAllMenus($cate, $pid) {
		$arr = $son = array();
		foreach ($cate as &$v) {
			if ($v['pid'] == $pid) {
				$son= $this -> getAllMenus($cate, $v['id']);
				$son && $v['son']=$son;
				$arr[] = $v;
			}
		}
		return $arr;
	}	
	public function addmenu() {
		$id = $this -> _get('id','intval');
		if($_GET['btn_submit']){
			$data['value']=$_GET['value'];
			$data['type']=$_GET['type'];
			$data['pid']=intval($_GET['pid']);
			$data['name']=$_GET['names'];
			if(!$data['value']||!$data['type']||!$data['name']){
				$this->json(300,'请填写完整！');
			}
			if($id){
				$res=M('wxmenu')->where("id={$id}")->save($data);
			}else{
				$res=M('wxmenu')->add($data);
			}
			$res? $this->json(200,'操作成功',array('tabid' => $_GET['dialogId'] . ',Wechat-menu', 'closeCurrent' => true)) : $this->json(0);
		}else{
			if ($id) {
				$p['pid'] = $m['id'] = $id;
				$this -> assign('menu', M('wxmenu') -> where($m) -> find());
				$this -> assign('son', M('wxmenu') -> where($p) -> count());
			}
			$menu=M('wxmenu') -> select();
			$this -> assign('cate', $this->getAllMenus($menu, 0));
			$this -> display();
		}
	}
	//是否还可以添加菜单
	public function isOk(){
		$m=M('wxmenu');
		$id=$this->_get('id');
		if($id==0){
			$count = $m->where('pid=0')->count();
			if($count == 3){
				die(json_encode(array('status'=>0,'msg'=>'一级菜单最多只能三个')));
			}
		}else{
			$map['pid']=$id;
			$count = $m->where($map)->count();
			if($count == 5){
				die(json_encode(array('status'=>0,'msg'=>'二级菜单最多只能五个')));
			}
		}
		die(json_encode(array('status'=>1,'msg'=>'ok')));	
	}
	//菜单添加执行
	public function addSonMenu(){
		$id=$this->_get('id');
		$map['pid']=$this->_get('pid');
		$map['name']=$this->_get('name');
		$map['type']=$this->_get('type');
		$map['value']=$this->_get('value');
		if($id){
			$wh['id']=$id;
			$res= M('wxmenu')->where($wh)->save($map);
		}else{
			$res= M('wxmenu')->add($map);
		}
		$res?$this->json(1):$this->json(0);	
	}
	public function respNews(array $news) {
		$news = array_change_key_case($news);
		if (!empty($news['title'])) {
			$news = array($news);
		}
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'news';
		$response['ArticleCount'] = count($news);
		$response['Articles'] = array();
		foreach ($news as $row) {
			$response['Articles'][] = array(
				'Title' => $row['title'],
				'Description' => ($response['ArticleCount'] > 1) ? '' : $row['description'],
				'PicUrl' => !empty($row['picurl']) && !strexists($row['picurl'], 'http://') ? $GLOBALS['_W']['attachurl'] . $row['picurl'] : $row['picurl'],
				'Url' => $this->buildSiteUrl($row['url']),
				'TagName' => 'item',
			);
		}
		return $response;
	}
	
	//文字规则
	public function addtextreply() {
		$id = $this -> _get('id');
		if ($id) {
			$map['a.id'] = $id;
			$this -> reply = M('wxrule a') -> where($map) -> join('pchotel_wxtreply b ON a.id=b.rid') -> field('a.*,b.content,b.rid') -> find();
		}
		$this -> display();
	}
	
	//图文规则
	public function imagetext(){
		$id=$this->_get('id');
		if($id){
			$data['id']=$data2['rid']=$id;
			$this->reply =M('wxrule')->where($data)->find();
			$reply =M('wxitreply')->where($data2)->order('id asc')->select();
			$this->shownums=$nums= count($reply);
			$nums2=$nums-1;
			$this->assign("oneinfo",$reply[0]);			
			for($i=1;$i<$nums;$i++){
				$n=$i+1;
				$day='day'.$n;
				$content='content'.$n;
				$url='url'.$n;
				if($i==$nums2){
					$del='删除';
				}else{
					$del='';
				}
				$out='<h4 class="daynum" style="width:99% height:20px;background-color: #B3F570;"><a class="nodedel" href="javascript:delnode();" >'.$del.'</a></h4>
					<div class="param">
					标题：<input type="text" name="title[]" style="width:220px;" value="'.$reply[$i]['title'].'" /><br/> 
					描述：<input type="text" name="description[]" value="'.$reply[$i]['description'].'" >
					<br/>
					封面：<input type="file" name="thumb[]" >';
				if ($reply[$i]['thumb'])
				{
					$out.='<img src="'.__ROOT__.$reply[$i]['thumb'].'" width="100px" height="100px"/>';
				}
				$out.='<br/>正文：</div><input type="hidden" name="thumbval[]" value="'.$reply[$i]['thumb'].'"/>';
				$this->assign("$day",$out);	
				$this->assign("$content",$reply[$i]['content']);	
				$this->assign("$url",$reply[$i]['url']);	
			}
		}else{
			$this->shownums=1;
		}
		$this->display();
	}
	//文字回复删除
	public function delTextReply() {
		$map['id'] = $this -> _get('id');
		$res = M('wxrule') -> where($map) -> delete();
		if ($res) {
			$map['rid'] = $this -> _get('rid');
			$re = M('wxtreply') -> where($map) -> delete();
		} else {
			json_die(0);
		}
		($re || $res) ? $this->json(1) : $this->json(0);
	}
	//设置欢迎信息
	public function setReplyWelcome(){
		$map['id']=$this->_get('id');
		$val = $this->_get('v');
		$reply = M('wxrule');
		if($val==1){
			//设置所有为 非欢迎
			$reply->where('id>0')->setField('welcome',0);
			$res= $reply->where($map)->setField('welcome',1);
		}else{
			$res= $reply->where($map)->setField('welcome',0);
		}
		$res?$this->json(1):$this->json(0);	
	}	
	public function addimagetext() {
		if($_POST['edit']){
			if($_POST['name'] ==''){
				$this -> json(300, '规则名称不能为空');
			}
			if($_POST['key'] ==''){
				$this -> json(300, '触发关键字不能为空');
			}
			$rid['rid']=$id['id']=$_POST['id'];
			$rule['name']=$_POST['name'];
			$rule['key']=$_POST['key'];
			$data['url']=$_POST['url'];
			$data['description']=$_POST['description'];
			$data['content']=$_POST['content'];
			$data['thumb']=$_POST['thumb'];
			if($this->_post('id')){
				$res=M('wxrule')->where($id)->save($rule);
				$res+=M('wxitreply')->where($rid)->save($data);
			}else{
				$rule['type']=2;
				$res=M('wxrule')->add($rule);
				$data['rid']=$res;
				$data['title']=$post['name'];
				$res+=M('wxitreply')->add($data);
			}
			$res ? $this->json(200,'保存成功',array('tabid' => $_GET['dialogId'] . ',Wechat-imagetextreply', 'closeCurrent' => true)) : $this->json(0);
		}else{
			$id = $this -> _get('id');
			if ($id) {
				$map['a.id'] = $id;
				$this -> reply = M('wxrule a') -> where($map) -> join('pchotel_wxitreply b ON a.id=b.rid') -> field('a.*,b.content,b.rid,b.description,b.url,b.thumb') -> find();
			}
			$this -> display();
		}
	}	
	//设置默认信息
	public function setReplyDefault(){
		$map['id']=$this->_get('id');
		$val = $this->_get('v');
		$reply = M('wxrule');
		if($val==1){
			//设置所有为 非欢迎
			$reply->where('id>0')->setField('default',0);
			$res= $reply->where($map)->setField('default',1);
		}else{
			$res= $reply->where($map)->setField('default',0);
		}
		$res?$this->json(1):$this->json(0);		
	}	
}
?>