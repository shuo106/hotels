<?php

class CommonAction extends Action {

    public function __construct() {
		
        parent::__construct();
        if (!isset($_SESSION['admin_id']) && !isset($_SESSION['admin_name'])) {
            header("location:" . __APP__ . "/Public/login");
            exit();
        } 
        // session('role_id', 16);  //测试
        $this->control();
        $this->bjui();

           //订单、会员、房东 公用语言包
        $SYS=C('sys');
        $this->ORDERC =$SYS['ordercontent']; //订单部分
        $this->MEMBERC =$SYS['member'];      //会员部分
        $this->LANDLORD= $SYS['landlord'];    //点评部分
        $this->COMMENTC= $SYS['comment'];    //点评部分
    }





     //权限控制
    public function control(){
        //获取当前用户角色
        $map['userid']=session('admin_id');
        $role_id=M('admin')->where($map)->getField("grade");
        session("role_id",$role_id);
        
        //获取节点
        if($role_id!="16"){       //非超级管理员
            $map=array();
            $map['role_id']=$role_id;
            $rs=M('access')->where($map)->select();
            foreach($rs as $v){
                $nodeList[]=$v['node_id'];
            }
            //获取操作列表
            $map=array();
            $map['id']=array("in",$nodeList);
            $action=M('node')->where($map)->order('sort desc')->select();
            //获取一级菜单
            $_MENU=$sort=$id=array();
            foreach($action as $v){
                if($v['pid']=="0"){
                    $_MENU[]=$v;
                    $sort[]=$v['sort'];
                    $id[]=$v['id'];
                }
            }
            array_multisort($sort,SORT_DESC,SORT_NUMERIC,$id,SORT_ASC,$list);
            $is=0;                      //权限认证标识
            //获取二级菜单
            foreach($_MENU as $k=>$v){
                $sort1=$id1=array();
                foreach($action as $val){
                    if($val['pid']==$v['id']){
                        $_MENU[$k]['list'][]=$val;
                        $sort1[]=$v['sort'];
                        $id1[]=$v['id'];
                        if($val['model']==MODULE_NAME && $val['action']==ACTION_NAME){
                            $is=1;
                        }
                    }
                }
                array_multisort($sort1,SORT_DESC,SORT_NUMERIC,$id1,SORT_ASC,$_MENU[$k]['list']);
            }
            //输出到模版
            $this->assign("_MENU",$_MENU);
            //进行权限验证
            if(!$is){
                //[强制权限控制]
                //die(json_encode(array("statusCode"=>300,"message"=>"没有操作权限")));
            }
        }
    }    




    public function json($status = 200, $msg = "操作成功", $data = array()) {
        if ($status == 1) {
            $status = 200;
        } elseif ($status == 0) {
            $status = 300;
            $msg = "操作失败";
        }
        $return['statusCode'] = $status;
        $return['message'] = $msg;
        foreach ($data as $key => $value) {
            $return[$key] = $value;
        }
        die(json_encode($return));
    }

    public function json_die($status, $msg = '', $custom) {
        $params = func_get_args();
        $proxy = array('status' => 'statusCode', 'msg' => 'message', 'id' => 'tabid', 'close' => 'closeCurrent', 'to' => 'forward');
        $defaultStatusArr = array(300, 200);
        $defaultMsgArr = array('操作失败', '操作成功');
        if (preg_match('/^0|1$/', $status)) {
            $out['statusCode'] = $defaultStatusArr[$status];
            $out['message'] = $defaultMsgArr[$status];
            if (is_array($params[1])) {
                $custom = $params[1];
            } elseif ($params[2] && is_array($params[2])) {
                $out['message'] = $params[1];
                $custom = $params[2];
            } elseif (!empty($params[1])) {
                $out['message'] = $params[1];
            }
            foreach ($custom as $k => $v) {
                if (isset($proxy[$k])) {
                    $out[$proxy[$k]] = $v;
                } else {
                    $out[$k] = $v;
                }
            }
            die(json_encode($out));
        } elseif (preg_match('/^200|300$/', $status)) {
            $s = array('200' => '1', '300' => '0');
            if (empty($msg)) {
                $msg = $defaultMsgArr[$s[$status]];
            }
            $this->json_die($s[$status], $msg, $custom);
        } else {
            $this->json_die(0, '参数返回有误');
        }
    }

    public function _empty() {
        $this->error('方法错误');
    }

    public function rec($arr, $id, $lev = 0) {
        static $list = array();
        foreach ($arr as $v) {
            if ($v['pid'] == $id) {
                $v['lev'] = $lev;
                $list[] = $v;
                $this->rec($arr, $v['id'], $lev + 1);
            }
        }
        return $list;
    }

    public function bjui() {
        unset($_POST['__hash__']);
        //列表页分页大小
        $this->pagesizes = array(20, 30, 50, 100);
    }

    //添加日志
    public function log($status = "") {
        $data['time'] = time();
        $data['type'] = $status;
        $data['userid'] = session("admin_id");
        $data['ip'] = $_SERVER["REMOTE_ADDR"];
        M('log')->add($data);
    }

    public function  tosms($mobile,$con){
      //发送短信
            //基础数据
            $row=include 'Admin/Conf/message.php';
            Vendor('ChuanglanSmsHelper.ChuanglanSmsApi');
            $SMS = new ChuanglanSmsApi();
            $name=iconv("UTF-8","gb2312",$row['smsname']);
            $pwd=iconv("UTF-8","gb2312",$row['smspwd']);
            //var_dump($con);
            $result=$SMS->sendSMS($name,$pwd,$mobile,$con);
            $result=$SMS->execResult($result);
            //var_dump($result[1]);exit;
            return $result[1];
    }

    public function  tosms2($mobile,$con){
		//发送短信
		$basic= include('./Admin/Conf/message.php');
		Vendor('ChuanglanSmsHelper.ChuanglanSmsApi');
		$SMS = new ChuanglanSmsApi();
		$name=$basic['smsname'];
		$pwd =$basic['smspwd'];
		//dump($basic);
		$name = iconv("UTF-8", "gb2312", $basic['smsname']);
		$pwd  = iconv("UTF-8", "gb2312", $basic['smspwd']);
		$con  = $con;
		//var_dump($con);
		$res=$SMS->sendSMS($name,$pwd,$mobile,$con);
		$result=$SMS->execResult($res);
		//var_dump($result[1]);exit;
		return $result[1];

    }
}
