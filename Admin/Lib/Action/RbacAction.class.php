<?php

class RbacAction extends CommonAction {

    /**
     * [rolelist 角色列表视图]
     * @return [type] [description]
     */
    public function rolelist() {
        $rolelist = M('role')
                ->field("pchotel_role.*,pchotel_depa.value")
                ->join("__DEPA__ ON __ROLE__.depa=__DEPA__.id")
                ->select();
        $this -> rolelist = $rolelist;
        $this -> display();
    }

    /**
     * [addrole 添加角色]
     * @return [type] [description]
     */
    public function addrole() {
        //部门列表
        $list=M('depa')->select();
        $this->list=$list;          
        if (isset($_GET['id']) && $_GET['id']) {
            $id = intval($_GET['id']);
            $this->roleinfo = M('role')->where(array('id' => $id))->find();
        }
        $this->display();
    }

    //处理添加角色
    public function doaddrole() {
        if (isset($_POST['btn_submit'])) {
            if (!$_POST['name']) {
                $this->json_die(300,'角色名称不能为空');
            }
            if (!$_POST['remark']) {
                $this->json_die(300,'角色描述不能为空');
            }
            $data = array('name' => $this -> _post('name'), 'remark' => $this -> _post('remark'), 'status' => intval($_POST['status']),'depa'=>$this->_post('depa') );
            if ($_POST['id']) {
                $rows = M('role') -> where(array('id' => intval($_POST['id']))) -> data($data) -> save();
                $msg = '角色编辑成功';
            } else {
                $rows = M('role') -> data($data) -> add();
                $this->log('添加职位');
                $msg = '角色添加成功';
            }
            $rows? $this->json_die(200,$msg,array('tabid' => $_GET['dialogId'] . ',Rbac-rolelist', 'closeCurrent' => true)) : $this->json_die(0);
        }
    }    

    public function delrole() {
        if (!IS_AJAX) {
            halt('非法操作');
        }
        if ($_GET['id']) {
            $id = intval($_GET['id']);
            $rows = M('role') -> where(array('id' => $id)) -> delete();
            $rows ? $this->json_die(1) : $this->json_die(0);
        } else {
            halt('非法操作');
        }
    }

    /**
     * [access 给角色配置权限视图]
     * @return [type] [description]
     */
    public function access() {
        $id=$this->_get('rid');
        //获取角色权限
        $info=M('access')->where('role_id='.$id)->select();
        foreach($info as $v){
            $ids[]=$v['node_id'];
        }
        //获取全部权限列表
        $rs=M('node')->select();
        foreach($rs as $k=>$v){
            if(in_array($v['id'],$ids)){
                $rs[$k]['is_check']=1;
            }
        }
        //获取一级菜单
        $list=array();
        foreach($rs as $v){
            if($v['pid']=="0"){
                $list[]=$v;
            }
        }
            //获取二级菜单
        $url=array();
        foreach($list as $k=>$v){
            foreach($rs as $key=>$val){         
                if($val['pid']==$v['id']){
                    if(in_array($val['url'],$url)){
                        $action[$val['url']][]=$val;
                    }else{
                        $list[$k]['list'][]=$val;
                        $url[]=$val['url'];
                    }   
                }
            }
        }
            //输出到模版
        $this->assign("list",$list);
        $this->assign("action",$action);
        $this->display();
    }

    //处理权限配置
    public function doaccess(){
        $ids=$_POST['ids'];
        if(empty($ids)){
            $msg = '没有选中任何权限';
        }else{
            $data['role_id']=$this->_post('id');
            M("access")->where($data)->delete();
            foreach($ids as $v){
                $data['node_id']=$v;
                $rs=M('access')->add($data);
                if(!$rs){
                    $msg = '操作失败！';
                }
            }
        }
        if(empty($msg)){
            $this->log('配置管理员权限');
            $this->json(200,"操作成功");
        }else{
            $this->json(300,$msg);
        }
    }

    /**
     * [pidel 对角色进行批量操作]
     * @return [type] [description]
     */
    function pi() {
        $map['id'] = array('in', $_GET['ids']);
        $rows = M('role')->where($map)->delete();
        if ($rows) {
            M('node')->where($map)->delete();
            die(json_encode(array('status' => 1, 'msg' => '操作成功')));
        } else {
            die(json_encode(array('status' => 0, 'msg' => '操作失败')));
        }
    }
    public function pl() {
        $type=$_GET['type'];
        if ($type==2) {
            $id = $_GET['ids'];
            if (!$id) {
                $this->json_die(0,"请选择操作的数据");
            }
            $map['userid'] = array('in',$id);
            $row = M('admin')->where($map)->delete();
            if ($row) {
                $this->json_die(1);
            } else {
                $this->json_die(0);
            }
        }elseif ($type==1) {
            $id = $_GET['ids'];
            if (!$id) {
                $this->json_die(0,"请选择操作的数据");
            }
            $map['userid'] = array('in',$id);
           // $data['status'] = 1;
            //$res=M('member')->where($map)->setField($data);
            $res = M('admin')->where($map)->setField('disabled', 0);
            if ($res) {
                $this->json_die(1,'操作成功');
            } else {
                $this->json_die(0,'操作失败');
            }
        }elseif ($type==0) {
            $id = $_GET['ids'];
            if (!$id) {
                $this->json_die(0,"请选择操作的数据");
            }
            $map['userid'] = array('in',$id);
           // $data['status'] = 1;
            //$res=M('member')->where($map)->setField($data);
            $res = M('admin')->where($map)->setField('disabled', 1);
            if ($res) {
                $this->json_die(1,'操作成功');
            } else {
                $this->json_die(0,'操作失败');
            }
        }
        
    }

    /**
     * [nodelist 节点列表视图]
     * @return [type] [description]
     */
    public function nodelist() {
        $rs=M('node')->select();
            //获取一级菜单
        $list=$sort=$id=array();
        foreach($rs as $v){
            if($v['pid']=="0"){
                $list[]=$v;
                $sort[]=$v['sort'];
                $id[]=$v['id'];
            }
        }
        array_multisort($sort,SORT_DESC,SORT_NUMERIC,$id,SORT_ASC,$list);
        //获取二级菜单
        $url=array();
        foreach($list as $k=>$v){
            $sort=$id=array();
            $sort2=$id2=array();
            foreach($rs as $val){
                if($val['pid']==$v['id']){
                    if(in_array($val['url'],$url)){
                        $action[$val['url']][]=$val;
                        $sort[]=$val['sort'];
                        $id[]=$val['id'];
                    }else{
                        $list[$k]['list'][]=$val;
                        $url[]=$val['url'];
                        $sort2[]=$val['sort'];
                        $id2[]=$val['id'];
                    }
                }
            }
            array_multisort($sort2,SORT_DESC,SORT_NUMERIC,$id2,SORT_ASC,$list[$k]['list']);
        }
            //输出到模版
        $this->assign("list",$list);
        $this->assign("action",$action);
        $this -> display();
    }

    /**
     * [addnode 节点添加]
     * @return [type] [description]
     */
    public function addnode() {
        //查询定级栏目
        $where['pid']="0";
        $rs=M('node')->where($where)->field("id,title")->select();
        $this -> nodelist = $rs;
        //编辑状态
        if($this->_get('id')){
            $info=M('node')->where('id='.$this->_get('id'))->find();
            $this->info=$info;
        }
        $this -> display();
    }
    //添加权限操作    
    public function doaddnode() {
        $data['title']=$this->_post('title');
        if(!$this->_post('title')){
             $this->json_die(0,"权限名称不能为空");
        }
        $data['url']=$this->_post('url');
        $data['name']=$this->_post('cname');
        $data['model']=$this->_post('model');
        $data['pid']=$this->_post('pid');
        $data['icon']=$this->_post('icon');
        $data['action']=$this->_post('action');
        $data['is_show']=$this->_post('is_show');
        if($this->_post('id')){
            $rs=M('node')->where('id='.$this->_post('id'))->save($data);
        }else{
            $rs=M('node')->data($data)->add();
        }
        if($rs){
            $this->log('添加权限');
            $msg = '权限添加成功';
        }
        $rs? $this->json_die(200,$msg) : $this->json_die(0);
    }

    /**
     * [nodedel 节点删除操作]
     * @return [type] [description]
     */
    function delnode() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0; //父级id
        $ids=M('node')->where('pid='.$id)->field('id')->select();
        $idarr[]=$id;
        if($ids){
            foreach($ids as $v){
                $idarr[]=$v['id'];
            }
        }
        $map['id']=array('in',$idarr);
        $rows = M('node')->where($map)->delete();
        $rows?$this->json():$this->json(0);
    }

    /**
     * [addadmin 管理员添加]
     * @return [type] [description]
     */
    public function addadmin() {
        $map['status'] = 1;
        $field = array('id', 'name');
        $roles = M('role')->where($map)->select(); //调出网站的角色
        if ($_GET['uid']) {
            $map = array();
            $map['userid'] = intval($_GET['uid']);
            $userinfo = D('AdminRelation')->relation(true)->where($map)->find(); //调出用户的基本信息
            $this->uid = $_GET['uid'];
			$this->userinfo = $userinfo;
        }
        $this->roles = $roles;
        $this->display();
    }

    public function doaddadmin() {
        if (!IS_AJAX) {
            halt('页面不存在');
        }
        $id = intval($_POST['id']);
        //编辑用户信息的时候传递过来的用户id
        $db = M('admin');
        //验证用户名
//      $username =C('FORM_VALIDATE.account');
//      if (preg_match('/^([a-zA-Z0-9_]{3,15}|[\u4e00-\u9fa5]{1,5})$/', $this->_post['username']) == 0) {
//          $this->json_die(300,'账号由字母数字以及下划线组成，长度3-15位');
//      }
        if (!$id) {
            //判断注册的用户名是否存在
            $result = $db -> where(array('username' => trim($_POST['username']))) -> getField('userid');
            if ($result) {
                $this->json(300,'用户名已存在，请更换其它');
            }
        }
        //验证密码
        if (!$id) {
            $pass = '/^.{6,16}$/';
            if (preg_match($pass, $_POST['pwd']) == 0) {
                $this->json(300,'密码必须在6-16位之间');
            }
        }
        /*//验证邮箱
        $emailpreg = '/\w+@\w+(\.\w+){0,300}(\.\w+)/';
        if (preg_match($emailpreg, $_POST['email']) == 0) {
            $this->json(300,'邮箱地址不正确');
        }*/
        /*//验证手机
        $Mobilepreg = '/^(\+86)?\d{11}$|^(\d{3,4}-?)?\d{6,8}$/';
        if (preg_match($Mobilepreg, $_POST['mobile']) == 0) {
            $this->json(300,'手机号或电话号码错误');
        }*/
        $data = array();
        $data['username'] = $this -> _post('username');

        if(!empty($_POST['pwd'])){
           $data['password'] = $this -> _post('pwd', 'md5'); 
        }
        
     
        
        $data['truename'] = $this -> _post('truename');
        $data['mobile'] = $this -> _post('mobile');
        $data['email'] = $this -> _post('email');
        if($_SESSION['role_id'] ==16){
            $data['grade'] = $this -> _post('grade');
        }
        $data['lasttime'] = time();
        $data['regip'] = get_client_ip();
        //得到客户端ip
        $data['regtime'] = time();
        if($id){
            //unset($data['password']);
            unset($data['regtime']);
            unset($data['regip']);
            $row = $db -> where(array('userid' => $id)) -> data($data) -> save();
            $row1 = M('role_user') -> where(array('user_id' => $id)) -> setField('role_id', $_POST['grade'][0]);
            //只更改权限
            ($row||$row1) ? $this->json_die(1):$this->json_die(0);
        } else {
            $row = $db -> data($data) -> add();
        }
        if ($row) {
            $this->log('添加管理员');
            $this->json_die(1);
        } else {
            $this->json_die(0);
        }
    }

    /**
     * [userlist 管理员列表视图]
     * @return [type] [description]
     */
    public function adminlist() {
        $field = 'password,regip,regtime,errortime';
        $map = array();
        if ($_GET['text']) {
            $text = trim($_GET['text']);
            $this->text = $text;
            $map['_string'] = '(username like "%' . $text . '%")  OR ( truename like "%' . $text . '%")';
        }
        $this->total = D('AdminRelation')->relation(true)->where($map)->count();
        $this->pagesize = $this->_get('pageSize', 'intval', $this->pagesizes[0]);
        $this->pageCurrent = $this->_get('pageCurrent', 'intval', 1);
         //加入排序功能
         $order = 'userid desc';
         if ($this -> _get('orderField')) {
         $order =$this -> _get('orderField') . ' ' . $this -> _get('orderDirection');
         }        
        $adminlist = D('AdminRelation')
        ->relation(true)
        ->where($map)->field($field, true)
        ->page($this->pageCurrent . ',' . $this->pagesize)
        ->order($order)
        ->select();
        foreach($adminlist as $k=>&$v){
          $v['role']=M('role')->where('id='.$v['grade'])->getField('name');
        }
        $this->adminlist = $adminlist;
        $this->display();
    }

  //添加权限
  public function adddepa() {
    //编辑状态
    if($this->_get('id')){
      $info=M('depa')->where('id='.$this->_get('id'))->find();
      $this->info=$info;
    }
    $this -> display();
  }

    //添加权限操作  
    public function doadddepa() {
      $data['value']=$this->_post('depa');
      if(!$this->_post('depa')){
         $this->json_die(0,"名称不能为空");
      }
      if($this->_post('id')){
        $rs=M('depa')->where('id='.$this->_post('id'))->save($data);
      }else{
        $rs=M('depa')->data($data)->add();
      }
      if($rs){
        $msg = '添加成功';
      }
      $rs? $this->json_die(200,$msg,array('tabid' => $_GET['dialogId'] . ',Rbac-rolelist', 'closeCurrent' => true)) : $this->json_die(300,"操作失败",array('tabid' => $_GET['dialogId'] . ',Rbac-rolelist', 'closeCurrent' => true));
    }
    //删除权限操作
    public function deldepa(){
      $rs=M('depa')->where('id='.$this->_get('id'))->delete();
      $rs? $this->json_die(200,'删除成功') : $this->json_die(0);
    }
    /**
     * [suoding 管理员锁定操作]
     * @return [type] [description]
     */
    public function suoding() {
        if (!IS_AJAX) {
            halt('页面不存在');
        }
        if ($_GET['uid']) {
            $uid = intval($_GET['uid']);
            $type = $_GET['type'] ? 1 : 0;
            if ($type) {
                $rows = M('admin')->where(array('userid' => $uid))->setField('disabled', 0);
                $msg = '解除锁定';
            } else {
                $rows = M('admin')->where(array('userid' => $uid))->setField('disabled', 1);
                $msg = '已锁定';
            }
            $rows?$this->json():$this->json(0);
        } else {
            halt('非法操作');
        }
    }

    /**
     * [del 管理员删除操作]
     * @return [type] [description]
     */
    public function del() {
        if (!IS_AJAX) {
            halt('页面不存在');
        }
        if ($_GET['uid']) {
            $uid = intval($_GET['uid']);
            $rows = M('admin')->where(array('userid' => $uid))->delete();
            $rows?$this->json():$this->json(0);
        } else {
            halt('非法操作');
        }
    }

    /**
     * [adminpi 对管理员进行批量操作]
     * @return [type] [description]
     */
    function adminpi() {
        $ids = $_POST['ids'];
        $type = $this->_post('type', 'intval');
        switch ($type) {
            //批量删除
            case 1:
                $map['userid'] = array('in', $ids);
                echo M('admin')->where($map)->delete();
                break;
            //default:
        }
    }

    //部门列表
    public function depalist() {
        $list = M('depa')->select();
        $this->list = $list;
        $this->display();
    }

    //登录日志
    public function logs() {
        //可选分页大小
        $this->pagesizes = array(20, 30, 50, 100);
        $this->total = $total = M('log')->where($map)->count();
      //  $this ->pagesize = $pagesize = $this->_get('pageSize', 'intval', $this->pagesizes[0]);
        $this ->pagesize=$this -> _get('pageSize', 'intval', $this -> pagesizes[0]);
        $this->pageCurrent = $pageCurrent = $this->_get('pageCurrent', 'intval', 1);
        $list = M('log')
                ->join("__ADMIN__ ON __LOG__.userid=__ADMIN__.userid")
                ->field("pchotel_admin.username,pchotel_log.*")
                ->page($this ->pageCurrent.','.$this ->pagesize)
                ->order('id desc')
                ->select();
        $this->list = $list;
        $this->display();
    }

    public function sort(){
        $sort_arr=$_POST['sort'];
        $res=0;
        foreach ($sort_arr as $id => $sort) {
            $res+=M('node')->where("id={$id}")->setField('sort',$sort);
        }
        $res ? $this -> json_die(1) : $this -> json_die(0);
    }    

}

?>