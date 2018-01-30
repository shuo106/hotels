<?php

class MemberAction extends CommonAction {

    /**
     * [index 会员添加视图]
     * @return [type] [description]
     */
    public function index() {
        if (isset($_POST['btn_submit'])) {
            if ($_POST['password'] != $_POST['password2']) {
                $this->json(300, '两次密码不一致');
            }
            $m = M('member');
            $data = array();
            $data['username'] = $_POST['username'];
            $data['password'] = md5($_POST['password']);
            $data['truename'] = $_POST['truename'];
            $data['telephone'] = $_POST['telephone'];
            $data['email'] = $_POST['email'];
            $data['qq'] = $_POST['qq'];
            $data['address'] = $_POST['address'];
            $data['regip'] = $_SERVER['REMOTE_ADDR'];
            $data['regtime'] = time();
            $data['lastloginip'] = $_SERVER['REMOTE_ADDR'];
            $data['lastlogintime'] = time();
            $row = $m->data($data)->add();
            $row ? $this->json() : $this->json(0);
        }
        //正则表达式长度标识符和smarty分隔符冲突，只能从控制器中将验证规则输出到模板
        $this->rule = C('FORM_VALIDATE');
        $this->userinfo = M('member')->where('id=' . $_GET['id'])->find();
        $this->assign('glist', M('member_group')->select());
        $this->display();
    }

    public function doreg() {
        header("Content-type:text/html;charset=utf-8");
        $r = M('member_set')->find();
        if ($r['user']) {
            $name = json_decode($r['user']);
            if (in_array($this->_post('cr_mail'), $name)) {
                echo '<script type="text/javascript">alert("本站禁止该用户词注册");window.history.back(-1);</script>';
                //die(json_encode(array('status'=>0,'msg'=>'本站禁止该用户词注册')));
            }
        }
        $user = M('Member');
        if (!$r['isnum']) {
            $row = $user->where('email = ' . $this->_post('email'))->find();
            if ($row) {
                echo '<script type="text/javascript">alert("该邮箱已经注册过");window.history.back(-1);</script>';
                //die(json_encode(array('status'=>0,'msg'=>'该邮箱已经注册过')));
            }
        }
        $isu = $user->where('username = ' . $this->_post('cr_mail'))->find();
        if ($isu) {
            echo '<script type="text/javascript">alert("该帐号已经注册过");window.history.back(-1);</script>';
            //die(json_encode(array('status'=>0,'msg'=>'该帐号已经注册过')));
        }
        $data = array();
        $data['regtime'] = time();
        $data['password'] = md5($this->_post('password'));
        $data['username'] = $this->_post('cr_mail');
        $data['email'] = $this->_post('email');
        $data['groupid'] = $this->_post('groupid');
        $data['Mobile'] = $this->_post('phone') ? $this->_post('phone') : '';
        $data['truename'] = $this->_post('cr_name') ? $this->_post('cr_name') : '';
        $data['regip'] = $_SERVER['REMOTE_ADDR'];
        $res = $user->data($data)->add();
        if ($res) {
            $this->redirect("/Member/member", '', 1, '添加成功');
            //die(json_encode(array('status'=>1,'msg'=>'注册成功！,页面跳转中...')));
        } else {
            $this->redirect("/Member/member", '', 0, '添加失败...');
            //die(json_encode(array('status'=>0,'msg'=>'注册失败')));
        }
    }

    //用户名验证
    public function userCheck() {
        $data['username'] = $this->_post('username');
        $res = M('member')->where($data)->count();
        if (!$res) {
            die(json_encode(array('ok' => '用户名可用')));
        } else {
            die(json_encode(array('error' => '已注册')));
        }
    }

    function password_strength($string) {
        $h = 0;
        $size = strlen($string);
        //print_r(count_chars($string, 1));
        foreach (count_chars($string, 1) as $v) {//count_chars：返回字符串所用字符的信息
            $p = $v / $size;
            $h -= $p * log($p) / log(2);
        }
        $strength = ($h / 4) * 100;
        if ($strength > 100) {
            $strength = 100;
        }
        return $strength;
    }

    /**
     * [member 会员列表视图]
     * @return [type] [description]
     */
    public function member() {
        $m = M('member');
        $this->pagesizes = array(20, 30, 50, 100);
        $con = trim($_GET['text']);
        if ($con) {
            $map['username|truename'] = array('like', '%' . $con . '%');
        }
        $order = 'id desc';
        if ($this->_get('orderField')) {
            $order = $this->_get('orderField') . ' ' . $this->_get('orderDirection') . ',id desc';
        }
        $map['is_delete'] = 0;
        // $map['regtype']="0";
        $this->total = $total = $m->where($map)->count();
        $this->pagesize = $pagesize = $this->_get('pageSize', 'intval', $this->pagesizes[0]);
        $this->pageCurrent = $pageCurrent = $this->_get('pageCurrent', 'intval', 1);
        $list = $m->where($map)->order($order)->page($pageCurrent . ',' . $pagesize)->select();
        // foreach ($list as $k => &$v) {
        // 	$g['id'] = $v['groupid'];
        // 	if ($g['id']) {
        // 		$gname = M('member_group') -> where($g) -> field('gname,discount') -> find();
        // 		$v['group'] = $gname['gname'];
        // 		$v['discount'] = '折扣：' . $gname['discount'] . ' %';
        // 	} else {
        // 		$v['group'] = '普通会员';
        // 		$v['discount'] = '不打折';
        // 	}
        // }

        //dump($list[19]);
        $this->assign('list', $list);
        $this->display();
    }

    public function hotel() {
        $m = M('member_hotel');
        $con = trim($_GET['text']);
        if ($con) {
            $map['hotelname|username|linkname|telephone'] = array('like', '%' . $con . '%');
        }
        $this->total = $total = $m->where($map)->count();
        $this->pagesize = $pagesize = $this->_get('pageSize', 'intval', $this->pagesizes[0]);
        $this->pageCurrent = $pageCurrent = $this->_get('pageCurrent', 'intval', 1);
        if ($this->_get('orderField')) {
            $order = $this->_get('orderField') . ' ' . $this->_get('orderDirection') . ',hotelid desc';
        } else {
            $order = 'hotelid desc';
        }
        $row = $m->where($map)->order($order)->page($pageCurrent . ',' . $pagesize)->select();
        foreach ($row as &$v) {
            $area = explode(',', $v['city']);
            $city = '';
            foreach ($area as $va) {
                $ma['id'] = $va;
                $city.=M('area')->where($ma)->getField('name') . ',';
            }
            $v['city'] = rtrim($city, ',');
        }
        $this->assign('list', $row);
        $this->assign('page', $show);
        $this->display();
    }


    /**
     * [member_change 会员修改]
     * @return [type] [description]
     */
    public function member_hotel() {
        $m = M('member_hotel');
        $data = array();
        if (isset($_POST['btn_submit'])) {
            $pass = '/^.{6,16}$/';
            if (!empty($_POST['password'])) {
                if (preg_match($pass, $_POST['password']) == 0) {
                    $this->json(300, '密码必须在6-16位之间');
                }
                $data['password'] = md5($_POST['password']);
            }
            if (!empty($_POST['linkname'])) {
                if (preg_match('/[\x{4e00}-\x{9fa5}]{2,10}/u', $_POST['linkname']) == 0) {
                    $this->json(300, '真实姓名必须在2-10个汉字之间');
                }
                $data['linkname'] = $_POST['linkname'];
            }
            if (!empty($_POST['telephone'])) {
                $Mobilepreg = '/^[0-9]{11}$/';
                if (preg_match($Mobilepreg, $_POST['telephone']) == 0) {
                    $this->json(300, '手机号或电话号码错误');
                }
                $data['telephone'] = $_POST['telephone'];
            }
            $datas = $m->create();
            $userid = intval($_POST['id']);
            if ($this->_post('password')) {
                $m->password = md5($this->_post('password'));
            } else {
                unset($m->password);
            }
            $row = $m->where("hotelid = $userid")->save();
            $row ? $this->json_die(200, '会员资料修改成功') : $this->json_die(0);
        }
        $this->rule = C('FORM_VALIDATE');
        $id = intval($_GET['id']);
        $this->row = $row = $m->find($id);
        $this->assign('glist', M('member_group')->select());
        $this->display();
    }    

    /**
     * [member_change 会员修改]
     * @return [type] [description]
     */
    public function member_change() {
        $m = M('member');
        $data = array();
        if (isset($_POST['btn_submit'])) {
            $pass = '/^.{6,16}$/';
            if (!empty($_POST['password'])) {
                if (preg_match($pass, $_POST['password']) == 0) {
                    $this->json(300, '密码必须在6-16位之间');
                }
                $data['password'] = md5($_POST['password']);
            }
            if (!empty($_POST['truename'])) {
                if (preg_match('/[\x{4e00}-\x{9fa5}]{2,10}/u', $_POST['truename']) == 0) {
                    $this->json(300, '真实姓名必须在2-10个汉字之间');
                }
                $data['truename'] = $_POST['truename'];
            }
            if (!empty($_POST['telephone'])) {
                $Mobilepreg = '/^[0-9]{11}$/';
                if (preg_match($Mobilepreg, $_POST['telephone']) == 0) {
                    $this->json(300, '手机号或电话号码错误');
                }
                $data['telephone'] = $_POST['telephone'];
            }
            if (!empty($_POST['email'])) {
                $emailpreg = '/\w+@\w+(\.\w+){0,30}(\.\w+)/';
                if (preg_match($emailpreg, $_POST['email']) == 0) {
                    $this->json(300, '邮箱地址不正确');
                }
                $data['email'] = $_POST['email'];
            }
            $data['qq'] = $_POST['qq'];
            $data['address'] = $_POST['address'];
            $datas = $m->create();
            $userid = intval($_POST['id']);
            if ($this->_post('password')) {
                $m->password = md5($this->_post('password'));
            } else {

                unset($m->password);
            }

            $row = $m->where("id = $userid")->save();
            $row ? $this->json_die(200, '会员资料修改成功') : $this->json_die(0);
        }
        $this->rule = C('FORM_VALIDATE');
        $id = intval($_GET['id']);
        $this->row = $row = $m->find($id);
        $this->assign('glist', M('member_group')->select());
        $this->display();
    }

    /**
     * [del 会员删除操作]
     * @return [type] [description]
     */
    public function delete() {
        $id = intval($_GET['id']);
        if ($_GET['hotel']) {
            $m = M('member_hotel');
            $where = "hotelid = $id";
        } else {
            $m = M('member');
            $where = "id = $id";
        }
        $row = $m->where($where)->delete();
        $row ? $this->json_die(1) : $this->json_die(0);
    }

    public function fenghao() {
        $id = intval($_GET['id']);
        if ($_GET['hotel']) {
            $m = M('member_hotel');
            $where = "hotelid = $id";
        } else {
            $m = M('member');
            $where = "id = $id";
        }
        $row = $m->where($where)->setField('status', 1);
        $row ? $this->json_die(200, '锁定成功') : $this->json_die(0);
    }

    public function jiefeng() {
        $id = intval($_GET['id']);
        if ($_GET['hotel']) {
            $m = M('member_hotel');
            $where = "hotelid = $id";
        } else {
            $m = M('member');
            $where = "id = $id";
        }
        $row = $m->where($where)->setField('status', 0);
        $row ? $this->json_die(200, '解锁成功') : $this->json_die(0);
    }

    /**
     * [pl 会员批量操作]
     * @return [type] [description]
     */
    public function pl() {
        $id = $_GET['ids'];
        !$id && $this->json_die(300, '请选择你要操作的数据');
        $type = $this->_get('type');
        if ($this->_get('hotel')) {
            $m = M('member_hotel');
            $map['hotelid'] = array('in', $id);
        } else {
            $map['id'] = array('in', $id);
            $m = M('member');
        }
        switch ($type) {
            case 1 :
                $row = $m->where($map)->setField('status', 1);
                $msg = '批量锁定成功';
                break;
            case 2 :
                $row = $m->where($map)->setField('status', 0);
                $msg = '批量解锁成功';
                break;
            case 3 :
                $row = $m->where($map)->delete();
                break;
            default :
                $this->json_die(0);
                break;
        }
        $row ? $this->json_die(200, $msg) : $this->json_die(0);
    }

    /**
     * [memberset 模块设置操作]
     * @return [type] [description]
     */
    public function memberset() {
        $m = M('member_set');
        if (isset($_POST['btn_submit'])) {
            $data = $m->create();
            $user = explode(',', $_POST['user']);
            $user = json_encode($user);
            $m->user = $user;
            if ($_POST['id']) {
                $m->id = $_POST['id'];
                $row = $m->save();
            } else {
                $row = $m->add($data);
            }
            $row ? $this->json_die(1) : $this->json_die(300, '无任何修改');
        }
        $r = $m->find();
        $r['user'] = json_decode($r['user']);
        $r['user'] = implode(',', $r['user']);
        foreach ($r as $k => $v) {
            $this->$k = $v;
        }
        $this->display();
    }

    public function groupmanage() {
        $glist = M('member_group')->select();
        $this->assign('glist', $glist);
        $this->display();
    }

    public function groupadd() {
        if ($_GET['id']) {
            $ginfo = M('member_group')->where('id=' . $_GET['id'])->find();
            $this->assign('ginfo', $ginfo);
        }
        $this->display();
    }

    public function groupsave() {
        $group = M('member_group');
        $id = $this->_post('id');
        $data['gname'] = $this->_post('gname');
        $data['discount'] = $this->_post('discount');
        if ($id) {
            $row = $group->where('id=' . $id)->save($data);
        } else {
            $row = $group->add($data);
        }
        $row ? $this->json_die(1) : $this->json_die(0);
    }

    public function groupdel() {
        $id = $_GET['id'];
        $row = M('member_group')->where('id=' . $id)->delete();
        $row ? $this->json_die(1) : $this->json_die(0);
    }

}

?>
