<?php

class MemberAction extends CommonAction {

    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION['id']) && !isset($_SESSION['name'])) {
            header("location:" . __ROOT__ . "/login.html");
            exit();
        }
    }

    //会员中心
    public function index() {
        $name = $_SESSION['name'];
        $m = M('member');
        $row = $m->where("username='{$name}'")->find();
        //dump($row);die;
        if ($row['qq']) {
            $num[]=$row['qq'];
        }
        if ($row['truename']) {
            $num[]=$row['truename'];
        }
        if ($row['telephone']) {
            $num[]=$row['telephone'];
        }
        if ($row['email']) {
            $num[]=$row['email'];
        }
        if ($row['address']) {
            $num[]=$row['address'];
        }
     switch (count($num)) {
        case '0':
            $baifen="0%";
            break;
        case '1':
            $baifen="20%";
            break;
        case '2':
            $baifen="40%";
            break;
        case '3':
            $baifen="60%";
            break;
        case '4':
            $baifen="80%";
            break;
        case '5':
            $baifen="100%";
            break;          
     }
     $this->assign('baifen', $baifen);
        foreach ($row as $k => $v) {
            $this->assign($k, $v);
        }
        $basic = M('basic');
        $row = $basic->find();

        //订单
        // $name = $_SESSION['name'];
        $name = $_SESSION['id'];
        $map['pchotel_order.username']=$name;        
        $this->order = M('order')
                ->field('pchotel_order.*,pchotel_room.returnmoney,pchotel_member_hotel.hotelname,pchotel_room.roomtype')
                ->join('pchotel_member_hotel ON pchotel_order.hotelid = pchotel_member_hotel.hotelid')
                ->join('pchotel_room ON pchotel_order.roomid = pchotel_room.id')
                ->where($map)
                ->limit(5)
                ->order('pchotel_order.orderid desc')
                ->select();
        $title = "会员中心-" . $row['webname'];
        $this->assign('title', $title);
        $this->assign('keywords', $row['webname']);
        $this->assign('description', $row['description']);
        $this->assign('copyright', $row['copyright']);
        $this->display();
    }

    function tuancomment() {
        $map['pchotel_tuanorder.id'] = $this->_get('id');
        $map['uid'] = session('id');
        $this->details = M('tuanorder')->where($map)
                ->join('pchotel_tuan ON pchotel_tuanorder.tid = pchotel_tuan.id')
                ->field('title,thumb,pchotel_tuanorder.*')
                ->find();
        $this->display();
    }

    //会员订单
    public function member_order() {
        import("ORG.Util.Page"); // 导入分页类     
        // $name = $_SESSION['name'];
        $name = $_SESSION['id'];
        $map['pchotel_order.username']=$name;
        //状态
        if($this->_get('status')){
            $map['pchotel_order.status']=array('in',$this->_get('status'));
        }
        //未点评订单
        if($this->_get('comment')){
            $map['pchotel_order.status']=array('in','4,5,7');
            $map['pchotel_order.is_comment']=0;
			$this->_title='未点评订单';
        }
        $count = M('order')
                ->join('pchotel_member_hotel ON pchotel_order.hotelid = pchotel_member_hotel.hotelid')
                ->join('pchotel_room ON pchotel_order.roomid = pchotel_room.id')
                ->where($map)
                ->count();
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $this->page = $Page->show(); // 分页显示输出 
        $this->row = M('order')
                ->field('pchotel_order.*,pchotel_room.returnmoney,pchotel_member_hotel.hotelname,pchotel_room.roomtype')
                ->join('pchotel_member_hotel ON pchotel_order.hotelid = pchotel_member_hotel.hotelid')
                ->join('pchotel_room ON pchotel_order.roomid = pchotel_room.id')
                ->where($map)
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->order('pchotel_order.orderid desc')
                ->select();
        //print_r($this->row);
        $basic = M('basic');
        $row = $basic->select();
        $title = "我的酒店订单-" . $row[0]['webname'];
        $this->assign('title', $title);
        $this->assign('keywords', $row[0]['webname']);
        $this->assign('description', $row[0]['description']);
        $this->assign('copyright', $row[0]['copyright']);
        $this->display();
    }

    //团购点评
    function doComments2() {
        $com = M('comment');
        $data = array();
        $data['oid'] = $map['id'] = $this->_post('oid');
        $map['is_comment'] = 1;
        $iscom = M('tuanorder')->where($map)->find();
        if ($iscom) {
            die(json_encode(array('status' => 0, 'msg' => '此订单已经点评过了')));
        }
        $data['tid'] = $this->_post('tid');
        $data['username'] = $this->_post('username');
        if (!$data['username']) {
            die(json_encode(array('status' => 0, 'msg' => '请输入您的名字')));
        }
        $data['hotelid'] = $this->_post('itemid');
        $data['orderid'] = $this->_post('oid');
        $data['label'] = $this->_post('label');
        $data['title'] = $this->_post('title');
        if (!$data['title']) {
            die(json_encode(array('status' => 0, 'msg' => '请输入您对此次团购的印象')));
        }
        $data['unit'] = $this->_post('unit');
        $data['content'] = $this->_post('content');
        $data['uid'] = session('id');
        $data['addtime'] = time();
        $data['status'] = 1;
        $res = $com->add($data);
        if ($res) {
            $oo['id'] = $data['oid'];
            M('tuanorder')->where($oo)->setField('is_comment', 1);
            die(json_encode(array('status' => 1, 'msg' => '点评成功')));
        } else {
            die(json_encode(array('status' => 0, 'msg' => '操作失败')));
        }
    }

    //订单详细页面
    public function order_content() {
        if (isset($_POST['btn_submit'])) {
            $data['status'] = 2;
            $o = M('order');
            if ($o->where("orderid = {$_POST['orderid']}")->save($data)) {
                die(json_encode(array('status' => 1, 'msg' => '操作成功！')));
            }
        }
        // $name = $_SESSION['name'];
        $name = $_SESSION['id'];
        $pid = intval($_GET['pid']);
        $this->info = M('order')
                ->field('pchotel_order.*,pchotel_photo.src,pchotel_room.yudingjia,pchotel_member_hotel.hotelname,pchotel_room.roomtype')
                ->join('pchotel_photo ON pchotel_order.hotelid = pchotel_photo.hotelid')
                ->join('pchotel_member_hotel ON pchotel_order.hotelid = pchotel_member_hotel.hotelid')
                ->join('pchotel_room ON pchotel_order.roomid = pchotel_room.id')
                ->where('pchotel_order.hotelid= pchotel_room.hotelid and pchotel_order.orderid=' . $pid . " AND pchotel_order.username ='$name'")
                ->order('pchotel_photo.isdefault desc')
                ->find();
        $basic = M('basic');
        $row = $basic->select();
        $title = "订单详细-" . $row[0]['webname'];
        $this->assign('title', $title);
        $this->assign('oid', $pid);
        $this->assign('keywords', $row[0]['webname']);
        $this->assign('description', $row[0]['description']);
        $this->assign('copyright', $row[0]['copyright']);
        $this->display();
    }

    //交易返回
    function endpay() {
        $up['status'] = 3;
        $order = M('order')->where('orderid=' . $_GET['id'])->save($up);
        $this->display('member_order');
    }

    //资料修改
    public function member_change() {
        $name = $_SESSION['name'];
        $m = M('member');
        if ($this->isPost()) {
            $data = array();
            $daan = '/^.{6,30}$/';
            if (preg_match($daan, $_POST['truename']) == 0) {
                die(json_encode(array('status' => 0, 'msg' => '真实姓名长度必须在2-10个汉字之间')));
            }
            $daan1 = '/^.{3,600}$/';
            if(preg_match($daan1, $_POST['address']) == 0){
                die(json_encode(array('status' => 0, 'msg' => '请输入您的地址')));
            }
            $zhengze = '/^\w+@\w+(\.\w+){0,1}(\.\w+)$/';
            if (preg_match($zhengze, $_POST['email']) == 0) {
                die(json_encode(array('status' => 0, 'msg' => '请填写正确的邮箱')));
            }
            //$zhengze2 = '/^(\+86)*13[0-9]\d{8}|14[0-9]\d{8}|15[0-9]\d{8}|17[0-8]\d{8}|18[0-9]\d{8}$/';
            $zhengze2 = '/^[0-9]{11}$/';
 /*            if (preg_match($zhengze2, $_POST['mobile']) == 0) {
                die(json_encode(array('status' => 0, 'msg' => '请填写正确的手机号码')));
            } */


           //为确保邮箱和手机号码的唯一性进行验证
  
           $usermsg=M('member')->where('id='.$_SESSION['id'])->find();
 
           if($this->_post('email') &&  $this->_post('email')!= $usermsg['email']){
                $row = M('member')->where("email = '".$this->_post('email')."'")->find();
                if($row){
                    die(json_encode(array('status'=>0,'msg'=>'该邮箱已经注册过'))); 
                }
            }

             if($this->_post('mobile') &&  $this->_post('mobile') != $usermsg['telephone']){ 
                 if(M('member')->where('telephone = '.$this->_post('mobile'))->find()){
                        die(json_encode(array('status'=>0,'msg'=>'该手机号已经注册过')));    
                 }
             }

            if ($_FILES['icon']['name']) {
                //获的服务器路径
                $time = time();
                //获得当前时间戳
                $file = $_FILES['icon']['tmp_name'];
                if (!file_exists('uploads/icon')) {
                    @mkdir('uploads/icon', 0777);
                }
                if (!file_exists('uploads/icon/' . $dir)) {
                    @mkdir('uploads/icon/' . $dir, 0777);
                }
                $fp = "./uploads/icon/" . $dir . '/webapp_' . $time . ".png";
                //确定图片文件位置及名称
                if (move_uploaded_file($file, $fp)) {
                    $data['icon'] = ltrim($fp, '.');
                }
            }
            $data['question'] = $_POST['question'];
            $data['answer'] = $_POST['answer'];
            $data['truename'] = $_POST['truename'];
            $data['email'] = $_POST['email'];
            $data['telephone'] = $_POST['mobile'];
            $data['address'] = $_POST['address'];
            $data['linkname'] = json_encode($_POST['linkname']);
            $data['linkphone'] = json_encode($_POST['linkphone']);
            $data['linkaddress'] = json_encode($_POST['linkaddress']);
            $data['qq'] = $_POST['qq'];            
            $row = $m->where("username = '$name'")->save($data);
            if ($row) {
                die(json_encode(array('status' => 1, 'msg' => '修改成功！')));
            } else {
                die(json_encode(array('status' => 0, 'msg' => '无任何更改')));
            }
        }else{
            $this->user = $m->where("username='{$name}'")->find();
            $basic = M('basic');
            $row = $basic->find();
            $title = "修改资料-" . $row['webname'];
            $this->assign('title', $title);
            $this->assign('keywords', $row['webname']);
            $this->assign('description', $row['description']);
            $this->assign('copyright', $row['copyright']);
            $this->display();
        }
    }

    //密码修改
    public function change_password() {
        $name = $_SESSION['name'];
        if ($this->isPost()) {
            $m = M('member');
            $row = $m->where("username='{$name}'")->find();
            if ($row['password'] != md5($_POST['oldpassword'])) {
                die(json_encode(array('status' => 0, 'msg' => '原密码错误')));
            }
            $pass = '/^.{6,20}$/';
            if (preg_match($pass, $_POST['password']) == 0) {
                die(json_encode(array('status' => 0, 'msg' => '新密码长度必须在6-20位之间')));
            }
            if ($_POST['password'] != $_POST['pwdconfirm']) {
                die(json_encode(array('status' => 0, 'msg' => '重复密码错误')));
            }
            $data['password'] = md5($_POST['password']);
            $row = $m->where("username = '$name'")->save($data);
            if ($row) {
                session_unset();
                session_destroy();
                die(json_encode(array('status' => 1, 'msg' => '密码修改成功')));
            }
        }else{
            $basic = M('basic');
            $row = $basic->find();
            $title = "修改密码-" . $row['webname'];
            $this->assign('title', $title);
            $this->assign('keywords', $row['webname']);
            $this->assign('description', $row['description']);
            $this->assign('copyright', $row['copyright']);
            $this->display();
        }
    }

    //常住酒店
    public function member_permanent() {
        import("ORG.Util.Page"); // 导入分页类
        //收藏
        $list = M('app_collection')->where('uid=' . $_SESSION['id'])->select();
        foreach ($list as $v) {
            $ids[] = $v['hotelid'];
        }
        $map['hotelid'] = array('in', array_unique($ids));
        $collection=M('member_hotel')->where($map)->select();
        foreach ($collection as &$v) {
            $v['src']=M('photo')->where('hotelid='.$v['hotelid'])->order('isdefault desc')->getField('src');
            $v['price']=M('room')->where('hotelid='.$v['hotelid'])->order('yudingjia asc')->getField('yudingjia');
        }
        $this->collection=$collection;
        $basic = M('basic');
        $row = $basic->select();
        $title = "我的常住酒店-" . $row[0]['webname'];
        $this->assign('title', $title);
        $this->assign('keywords', $row[0]['webname']);
        $this->assign('description', $row[0]['description']);
        $this->assign('copyright', $row[0]['copyright']);
        $this->display();
    }

    //我要点评
    public function member_comments_want() {
        $name = $_SESSION['name'];
        $this->row = $o = M('order')->field('pchotel_order.status,pchotel_order.orderid,pchotel_room.hotelname,pchotel_order.addtime,pchotel_room.address,pchotel_room.hotelid,pchotel_room.introduce,pchotel_photo.src')//
                ->join('pchotel_photo ON pchotel_order.hotelid = pchotel_photo.hotelid')
                ->join('pchotel_room ON pchotel_order.hotelid = pchotel_room.hotelid')
                ->where("pchotel_order.username= '$name' and pchotel_order.is_comment= 0 and (pchotel_order.status = 1 or pchotel_order.status = 3)")// and pchotel_photo.isdefault=1
                ->order('pchotel_order.orderid desc')
                ->group('pchotel_order.orderid')
                ->select();
        $basic = M('basic');
        $row = $basic->select();
        $title = "我的常住酒店-" . $row[0]['webname'];
        $this->assign('title', $title);
        $this->assign('keywords', $row[0]['webname']);
        $this->assign('description', $row[0]['description']);
        $this->assign('copyright', $row[0]['copyright']);
        $this->display();
    }

    //酒店点评
    public function member_comments() {
        $o = M('order');
        if($this->isGet()){
            // $username = $_SESSION['name'];
            $username = $_SESSION['id'];
            $id = intval($_GET['oid']);
            $where = array(
                'pchotel_order.orderid' => $id,
                'pchotel_order.username' => $username
            );
            $row = $o->join('pchotel_room ON pchotel_order.roomid =pchotel_room.id ')
                    ->join('pchotel_member_hotel ON pchotel_order.hotelid =pchotel_member_hotel.hotelid ')
                    ->where($where)
                    ->field('pchotel_room.roomtype,pchotel_order.orderid,pchotel_order.hotelid,pchotel_order.telephone,pchotel_order.roomid,pchotel_member_hotel.hotelname')
                    ->find();
            $this->assign('row', $row);
            $basic = M('basic');
            $row = $basic->select();
            $title = "我的酒店点评-" . $row[0]['webname'];
            $this->assign('title', $title);
            $this->assign('keywords', $row[0]['webname']);
            $this->assign('description', $row[0]['description']);
            $this->assign('copyright', $row[0]['copyright']);
            $this->display();            
        }else{
            $content = '/^.{30,600}$/';
            if (preg_match($content, $_POST['content']) == 0) {
                die(json_encode(array('status' => 0, 'msg' => '详细评价必须在10-200个汉字之间')));
            }
            $map['orderid'] = $_POST['oid'];
            $map['username'] = $_SESSION['id'];
            $res2 = M('order')->where($map)->field('is_comment,status')->find();
            if ($res2['status'] == 4 || $res2['status'] == 5 || $res2['status'] == 7) {
                
            } else {
                die(json_encode(array('status' => 0, 'msg' => '请您付款或入住后再来点评'.$res2['status'])));
            }
            if ($res2['is_comment'] == 1) {
                die(json_encode(array('status' => 0, 'msg' => '对不起，您已点评过该酒店！')));
            }
            $datao['is_comment']=1;
            M('order')->where($map)->save($datao);
            $m = M('comment');
            $data = array();
            $data['hotelid'] = $this->_post('itemid');
            $data['orderid'] = $this->_post('oid');
            $data['label'] = $this->_post('label');
            $data['title'] = $this->_post('title');
            $data['unit'] = $this->_post('unit');
            $data['content'] = $this->_post('content');
            $data['uid'] = session('id');
            $data['thumb']=$this->_post('thumb');
            $data['addtime'] = time();
            $data['status'] = 1;

            $res = $m->data($data)->add();
            if ($res) {
                $o->where("orderid = {data['orderid']}")->setField('comment', 1);
                die(json_encode(array('status' => 1, 'msg' => '恭喜您，点评成功！')));
            } else {
                die(json_encode(array('status' => 0, 'msg' => '操作失败')));
            }
        }
    }

    //已点评酒店
    public function member_comments_manage() {
        $name = $_SESSION['name'];
        $uid = $_SESSION['id'];
        $map['pchotel_comment.uid'] = $uid;
        //$map['pchotel_comment.status'] = 2;
        $map['pchotel_comment.is_delete'] = array('neq', 1);
        import("ORG.Util.Page"); // 导入分页类
        $count = M('comment')->where($map)->count();
        $Page = new Page($count, 2); // 实例化分页类 传入总记录数和每页显示的记录数
        $this->page = $Page->show(); // 分页显示输出
        $row = M('comment')
                ->join('pchotel_member on pchotel_member.id=pchotel_comment.uid')
                ->join('pchotel_member_hotel h on h.hotelid=pchotel_comment.hotelid')
                ->where($map)
                ->field("pchotel_comment.*,pchotel_member.username,pchotel_member.icon as head,h.hotelname")
                ->order('pchotel_comment.id desc')
                ->group("pchotel_comment.id")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        foreach ($row as &$v) {
            $v['thumb']&&$v['thumb']=explode(',', rtrim($v['thumb'],','));
            $v['src']=M('photo')->where('hotelid='.$v['hotelid'])->order('isdefault desc')->getField('src');
        }   
        unset($v);
        $this->row=$row;
        $basic = M('basic');
        $row = $basic->select();
        $title = "我的酒店点评-" . $row[0]['webname'];
        $this->assign('title', $title);
        $this->assign('keywords', $row[0]['webname']);
        $this->assign('description', $row[0]['description']);
        $this->assign('copyright', $row[0]['copyright']);
        $this->display();
    }    

    //最近访问
    public function member_recently() {

        $this->display();
    }

    public function tixian_log() {
        import('ORG.Util.Page');
        //目前提现金额的积分比例是固定的1:1
        $map['uid'] = session('id');
        $field = array('*', 'concat("-",format(txjine,0))' => 'point', 'concat(txdate,id)' => 'tid', 'id' => 'tixian_id',
            'NULLIF(banliDate,0)' => 'banliDate', 'NULLIF(handleDate,0)' => 'handleDate', 'NULLIF(txdate,0)' => 'txdate',
            '(case status
                when 0 then "待审核" 
                when 1 then "待办理"
                when 2 then "已办理"
            end)' => 'status');
        $count = M('tixian')->where($map)->count();
        $page = new Page($count, 10);
        $this->pages = $page->show();
        $list = M('tixian')
                ->where($map)
                ->field($field)
                ->limit($page->firstRow . ',' . $page->listRows)
                ->order('id desc')
                ->select();
        $this->list = $list;
        $this->display();
    }

    public function tixian_detail() {
        $id = $this->_get('tid');
        $field = array('*', 'concat(txdate,id)' => 'tid',
            '(case status
                when 0 then "待审核" 
                when 1 then "待办理"
                when 2 then "已办理"
            end)' => 'status');
        $info = M('tixian')->field($field)->find($id);
        $this->info = $info;
        $this->display();
    }

    //申请提现
    public function tixian() {
        $username = $_SESSION['name'];
        $member = M('member');
        $yue = $member->where("username='$username'")->getField('point');
        $this->assign('yue', $yue);
        $basic = M('basic');
        $row = $basic->select();
        $title = "申请提现-" . $row[0]['webname'];
        $this->assign('title', $title);
        $this->assign('keywords', $row[0]['webname']);
        $this->assign('description', $row[0]['description']);
        $this->assign('copyright', $row[0]['copyright']);
        $this->display();
    }

    public function tixiantj() {

        $txjine = (int) $_POST['txjine'];
        if ($txjine < 200) {
            die(json_encode(array('status' => 0, 'msg' => '提现金额不能小于200')));
        }
        if (empty($_POST['txhang'])) {
            die(json_encode(array('status' => 0, 'msg' => '提现银行不能为空')));
        }
        if (empty($_POST['txname'])) {
           die(json_encode(array('status' => 0, 'msg' => '开户姓名不能为空')));
        }
        $daan = '/^.{6,30}$/';
            if (preg_match($daan, $_POST['txname']) == 0) {
                die(json_encode(array('status' => 0, 'msg' => '开户姓名长度必须在2-10个汉字之间')));
            }
        if (empty($_POST['txhangnum'])) {
            die(json_encode(array('status' => 0, 'msg' => '银行账号不能为空')));
        }
        $r = $this->luhm($_POST['txhangnum']);
        if ($r) {
            die(json_encode(array('status'=>0,'msg'=>'请输入正确的银行账号')));
        }
        if (empty($_POST['txshenfen'])) {
            die(json_encode(array('status' => 0, 'msg' => '身份证号不能为空')));
        }
        $isIDCard2='/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/';

        if (preg_match($isIDCard2,$_POST['txshenfen']) ==0) {
            die(json_encode(array('status'=>0,'msg'=>'请输入正确的身份证号')));
        }
        if (empty($_POST['txmobile'])) {
            die(json_encode(array('status' => 0, 'msg' => '手机号码不能为空')));
        }
         if (preg_match('/\d{11}/', $_POST['txmobile'])==0) {
                die(json_encode(array('status' => 0, 'msg' => '请输入正确的手机号')));
            }
        $id=session('id');
        $point = M('Member')->where(array('username'=>session('name')))->getField('point');
        if ($point < $txjine) {
            die(json_encode(array('status' => 0, 'msg' => '余额不足')));
        }
        M('member')->where("id={$id}")->setDec('point', $txjine); //1:1
        $remainAmount = M('member')->where("id={$id}")->getField('point');
        $txdate = time();
        $t = M('tixian');
        $t->create();
        $t->txdate = $txdate;
        $t->uid = $id;
        $t->remainAmount = $remainAmount;
        $res = $t->add();
        if($res){
            die(json_encode(array('status' => 1, 'msg' => '申请成功,请等待后台审核')));
        }else{
            die(json_encode(array('status' => 0, 'msg' => '申请失败')));
        }
    }
    //验证银行卡号
function luhm($s) {
    $n = 0;
    for ($i = strlen($s); $i >= 1; $i--) {
        $index=$i-1;
        //偶数位
        if ($i % 2==0) {
            $n += $s{$index};
        } else {//奇数位
            $t = $s{$index} * 2;
            if ($t > 9) {
                $t = (int)($t/10)+ $t%10;
            }
            $n += $t;
        }
    }
    return ($n % 10) == 0;
}


//订单取消
  function docancel() {

     $orderid=$_GET['oid'];
     $row=M('order')->where('orderid='.$orderid)->field('hotelid,roomid,ruzhudate,lidiandate,nums,shoufei')->find();
     //预定成功后若要取消或更改房间数的请与入住当天24小时前修改
     
     /* if(time()-86400 >$row['ruzhudate'])
       {
         die(json_encode(array('status'=>0,'msg'=>'现在已经过了取消订单的时间')));
       } */
      
       $data['status']=6;
       $rs=M('order')->where('orderid='.$orderid)->save($data);
       if($rs){
         //订单取消成功后，改变相关日期Room的房间数量
         $this->changeNums($row['roomid'],$row['ruzhudate'],$row['lidiandate'],$row['nums']);
        die(json_encode(array('status'=>1,'msg'=>'订单取消成功')));  
        }else{
        die(json_encode(array('status'=>0,'msg'=>'订单取消失败')));  
        }

    }





    //订单取消成功后，改变相关日期Room的房间数量
    public function  changeNums($roomid,$start,$end,$nums){
     // echo $roomid."-".$start."-".$end."-".$nums."-";  die;  
     $room=M('room')->where('id='.$roomid)->find();
     $tjarr=$room['tjarr'];
     $arr = explode('|', substr($tjarr, 0, -1));
     //dump($arr);
     for($i=0;$i<count($arr);$i++)
     {
            $oneday = explode('-', $arr[$i]);
            $onecof=explode('_',$oneday[1]);
      
            if( $oneday[0]>=$start  && $oneday[0]<=$end)
            {
                $arr[$i] = $oneday[0].'-'. $onecof[0]."_".($onecof[1]+$nums);
             }else{
                 // echo $oneday[0]."-";
                $arr[$i] = $oneday[0].'-'. $onecof[0]."_".$onecof[1];   
            }
           
      }
     $data['tjarr']=implode('|', $arr);
     //echo $data['tjarr'];
     $room=M('room')->where('id='.$roomid)->save($data);
    }


    //积分流水帐
    public function point() {
        $p = M('point');

        $map['username'] = session('name');


        $this->point=M('member')->where($map)->getField('point'); 

        $count = $p->where($map)->count();
        import("ORG.Util.Page");
        $page = new Page($count, 8);
        $this->pages = $page->show();
      
       
       $lists= $p->where($map)
                        ->order('pchotel_point.id desc')
                        ->limit($page->firstRow . ',' . $page->listRows)
                        ->select();
        
 
        foreach ($lists as &$v) {
            if($v['type']==0){
                $rs=M('order')->where("orderid={$v['foreign_key']}")->field('addtime,orderid')->find();
                $v['order_no']=$rs['addtime'].$rs['orderid'];
            }
        }       
         
    
        $this->list = $lists;
        $this->display();
    }

    //团购订单列表
    public function tuan() {
        $map['uid'] = session('id');
        $count = M('tuanorder')->where($map)->count();
        import("ORG.Util.Page"); // 导入分页类
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $this->page = $Page->show(); // 分页显示输出
        $this->tuans = M('tuanorder')->where($map)
                ->join('pchotel_tuan ON pchotel_tuanorder.tid = pchotel_tuan.id')
                ->field('pchotel_tuan.title,pchotel_tuanorder.*')
                ->order('pchotel_tuanorder.id desc')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        $this->display();
    }

    //团购订单详情
    function tuandetails() {
        $vou['oid'] = $map['pchotel_tuanorder.id'] = $this->_get('id');
        $map['uid'] = session('id');
        $details = M('tuanorder')->where($map)
                ->join('pchotel_tuan ON pchotel_tuanorder.tid = pchotel_tuan.id')
                ->field('title,thumb,pchotel_tuanorder.*')
                ->find();
        $guid = M('voucher')->where($vou)->select();
        if ($guid) {
            $tgj = '';
            foreach ($guid as $v) {
                $tgj.=$v['var'] . ',';
            }
            $details['guid'] = rtrim($tgj, ',');
        }
        $this->details = $details;
        $this->display();
    }

}

?>