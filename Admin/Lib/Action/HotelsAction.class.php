<?php

class HotelsAction extends CommonAction {

    public function index() {
        $id = intval($_GET['id']);
        if ($id) {
            $row = M('member_hotel')->where(array('hotelid' => $id))->find();
            $this->info = $row;
            if ($row['city']) {
                $area = explode(',', $row['city']);
            }
        }
        //城市
        if ($area) {
            $cc = explode('_', R('Area/getAreaDefault', array($area[0], $area[1], $area[2])));
            $this->Prov = $cc[0];
            $this->City = $cc[1];
            $this->Area = $cc[2];
        } else {
            $cc = explode('_', R('Area/getAreaDefault'));
            $this->Prov = $cc[0];
            $this->City = $cc[1];
        }
        $content = M('liansuo')->select();
        $this->assign('liansuo', $content);
        $xing = M('xingji')->limit(12)->select();
        $this->assign('xingji', $xing);

        $this->theme=M('theme')->select();
        $this->display();
    }

    //地图设置
    public function mapset() {
        $ho['hotelid'] = $_GET['hid'];
        $map = M('member_hotel')->where($ho)->field('map')->find();
        if ($map['map']) {
            $this->map = explode(',', $map['map']);
        } else {
            $this->map = array(113.648738, 34.787923);
        }
        $this->hid = $_GET['hid'];
        $this->display();
    }

    //地图保存
    public function savemap() {
        $map = $_GET['lng'] . ',' . $_GET['lat'];
        $res = M('member_hotel')->where('hotelid=' . $_GET['hid'])->setField('map', $map);
        if ($res) {
            echo 1;
        } else {
            echo 0;
        }
    }

    //用户名验证
    public function userCheck() {
        $data['username'] = $this->_post('username');
        $res = M('Member_hotel')->where($data)->count();
        if (!$res) {
            die(json_encode(array('ok' => '用户名可用')));
        } else {
            die(json_encode(array('error' => '已注册')));
        }
    }

    public function register() {
        
        //城市
        if ($area) {
            $cc = explode('_', R('Area/getAreaDefault', array($area[0], $area[1], $area[2])));
            $this->Prov = $cc[0];
            $this->City = $cc[1];
            $this->Area = $cc[2];
        } else {
            $cc = explode('_', R('Area/getAreaDefault'));
            $this->Prov = $cc[0];
            $this->City = $cc[1];
        }
        $content = M('liansuo')->select();
        $this->assign('liansuo', $content);
        $xing = M('xingji')->limit(12)->select();
        $this->assign('xingji', $xing);
        
        if (isset($_POST['btn_submit'])) {
            $data = array();
            $data['username'] = $_POST['username'];
            $data['password'] = md5($_POST['password']);
            $data['hotelname'] = $_POST['hotelname'];
            $data['linkname'] = $_POST['linkname'];
            $data['telephone'] = $_POST['telephone'];
            //$data['email'] = $_POST['email'];
            $data['lspp']=$_POST['lspp'];
            $data['xingji']=$_POST['xingji'];
            $data['fjdb1']=$_POST['fjdb1'];
            $data['fjdb2']=$_POST['fjdb2'];
            $data['fjdb3']=$_POST['fjdb3'];
            $data['fjdb4']=$_POST['fjdb4'];
            $data['fjdb5']=$_POST['fjdb5'];
            $data['syq']=$_POST['fjdb1']. '' .$_POST['fjdb2']. '' .$_POST['fjdb3']. '' .$_POST['fjdb4']. '' .$_POST['fjdb5'];
            $data['fjlv1']=$_POST['fjlv1'];
            $data['fjlv2']=$_POST['fjlv2'];
            $data['fjlv3']=$_POST['fjlv3'];
            $data['fjlv4']=$_POST['fjlv4'];
            $data['fjlv5']=$_POST['fjlv5'];
            $data['fjlv6']=$_POST['fjlv6'];
            $data['fjlv7']=$_POST['fjlv7'];
            $data['fjlv8']=$_POST['fjlv8'];
            $data['fjlv9']=$_POST['fjlv9'];
            $data['fjlv10']=$_POST['fjlv10'];
            $data['fjhotel1']=$_POST['fjhotel1'];
            $data['fjhotel2']=$_POST['fjhotel2'];
            $data['fjhotel3']=$_POST['fjhotel3'];
            $data['fjhotel4']=$_POST['fjhotel4'];
            $data['fjhotel5']=$_POST['fjhotel5'];
            $data['fjhotel6']=$_POST['fjhotel6'];
            $data['fjhotel7']=$_POST['fjhotel7'];
            $data['fjhotel8']=$_POST['fjhotel8'];
            $data['fjhotel9']=$_POST['fjhotel9'];
            $data['fjhotel10']=$_POST['fjhotel10'];
            $data['fjdsxq1']=$_POST['fjdsxq1'];
            $data['fjdsxq2']=$_POST['fjdsxq2'];
            $data['fjdsxq3']=$_POST['fjdsxq3'];
            $data['fjdsxq4']=$_POST['fjdsxq4'];
            $data['fjdsxq5']=$_POST['fjdsxq5'];
            $data['traffic']=$_POST['traffic'];
            $data['city2'] = $_POST['cityfj'];
            $data['city3'] = $_POST['cityfj2'];
            $data['city']=$_POST['province'] . ',' .$_POST['city'] . ',' .$_POST['area'];
            $data['tip']=stripslashes(htmlspecialchars_decode($_POST['tip']));
            $data['introduce']=stripslashes(htmlspecialchars_decode($_POST['introduce']));
            //$data['regip'] = $_SERVER['REMOTE_ADDR'];
            $data['regtime'] = time();
            //print_r($_POST);
            //print_r($data);die;
            $m = M('member_hotel');
            $m->create();
            //print_r($m);die;
           // $m->tip = stripslashes(htmlspecialchars_decode($_POST['tip']));
           // $m->introduce = stripslashes(htmlspecialchars_decode($_POST['introduce']));
            //$m->city = $this->_post('province') . ',' . $this->_post('city') . ',' . $this->_post('area');
           // $data['city']=$_POST['province'] . ',' .$_POST['city'] . ',' .$_POST['area'];
           // $m->city2 = $_POST['cityfj'];
            //$m->city3 = $_POST['cityfj2'];
    
            $res = $m -> data($data) -> add();
            $res ? $this->json() : $this->json(0);
            
        }
        //正则表达式长度标识符和smarty分隔符冲突，只能从控制器中将验证规则输出到模板
        $this->rule = C('FORM_VALIDATE');
        $this->display();
    }

    //添加酒店详细
    public function add() {
        if (!empty($_POST['id'])) {
            $id = $_POST['id'];
        } else {
            $this->json(0);
        }
        $m = M('member_hotel');
        $m->create();
        $m->tip = stripslashes(htmlspecialchars_decode($_POST['tip']));
        $m->introduce = stripslashes(htmlspecialchars_decode($_POST['introduce']));
        $m->city = $this->_post('province') . ',' . $this->_post('city') . ',' . $this->_post('area');
        $m->city2 = $_POST['cityfj'];
        $m->city3 = $_POST['cityfj2'];
        $m->renovation=strtotime($_POST['renovation']);

        $res = $m->where("hotelid = $id")->save();
        $res ? $this->json() : $this->json(0);
    }

    //酒店列表
    public function lists() {
        $m = M('member_hotel');
        $con = trim($_GET['text']);
        $map['is_delete'] = array('neq', 1);
        if ($con) {
            $map['hotelname|username'] = array('like', '%' . $con . '%');
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
        $this->assign('page', $show);
        $this->assign('list', $row);
        $this->display();
    }

    //酒店推荐
    public function tuijian() {
        $map['hotelid'] = intval($_GET['id']);
        $row = M('member_hotel')->where($map)->setField('is_tuijian', 1);
        $row ? $this->json() : $this->json(0);
    }

    //取消推荐
    public function butuijian() {
        $map['hotelid'] = intval($_GET['id']);
        $row = M('member_hotel')->where($map)->setField('is_tuijian', 0);
        $row ? $this->json() : $this->json(0);
    }

    //删除酒店到回收站
    public function del() {
        $map['hotelid'] = intval($_GET['id']);
        $row = M('member_hotel')->where($map)->setField('is_delete', 1);
        $row ? $this->json() : $this->json(0);
    }

    public function jguan() {
        $m = M('member_hotel');
        $map['hotelid'] = intval($_GET['id']);
        $d = intval($_GET['d']);
        if ($d == 0) {
            $row = $m->where($map)->setField('is_delete', 2);
        } else {
            $row = $m->where($map)->setField('is_delete', 0);
        }
        $row ? $this->json() : $this->json(0);
    }

    //回收站列表
    public function huishou() {
        $m = M('member_hotel');
        $con = trim($_GET['text']);
        import("ORG.Util.Page"); // 导入分页类
        $map['is_delete'] = 1;
        if ($con) {
            $map['hotelname|username'] = array('like', '%' . $con . '%');
        }
        $count = $m->where($map)->count();
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $row = $m->where($map)->order('hotelid desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($row as &$v) {
            $area = explode(',', $v['city']);
            $city = '';
            foreach ($area as $va) {
                $ma['id'] = $va;
                $city.=M('area')->where($ma)->getField('name') . ',';
            }
            $v['city'] = rtrim($city, ',');
        }
        $this->assign('page', $show);
        $this->assign('list', $row);
        $this->display();
    }

    //回收站酒店还原
    public function huanyuan() {
        $map['hotelid'] = intval($_GET['id']);
        $row = M('member_hotel')->where($map)->setField('is_delete', 0);
        $row ? $this->json() : $this->json(0);
    }

    //回收站酒店彻底删除
    public function shanchu() {
        $map['hotelid'] = intval($_GET['id']);
        M('room')->where($map)->delete();
        $row = M('member_hotel')->where($map)->delete();
        $row ? $this->json() : $this->json(0);
    }

    //批量操作
    public function pl() {
        $ids = $_GET['ids'];
        $map['hotelid'] = array('in', $ids);
        $m = M('member_hotel');
        switch ($this->_get('type')) {
            case '1':   //批量放入回收站
                $rs = $m->where($map)->setField('is_delete', 1);
                break;
            case '2':   //批量关闭
                $rs = $m->where($map)->setField('is_delete', 2);
                break;
            case '3':   //批量开启、还原
                $rs = $m->where($map)->setField('is_delete', 0);
                break;
            case '4':   //批量推荐
                $rs = $m->where($map)->setField('is_tuijian', 1);
                break;
            case '5':   //批量取消推荐
                $rs = $m->where($map)->setField('is_tuijian', 0);
                break;
        }
        $rs ? $this->json() : $this->json(0);
    }

    //房间列表
    public function room() {
        $m = M('room');
        $con = trim($_GET['text']);
        $map['pchotel_room.is_delete'] = array('neq', 1);
        if ($con) {
            $map['roomtype|hotelname'] = array('like', '%' . $con . '%');
        }
        //床型
        if ($_GET['roomtype']) {
            $map['pchotel_room.fjchuang']= array('like', '%' . $_GET['roomtype'] . '%');
        }
        
        $this->total = $total = $m->where($map)->join('pchotel_member_hotel ON pchotel_room.hotelid = pchotel_member_hotel.hotelid')
                ->field('hotelname,pchotel_room.*')->count();
        $this->pagesize = $pagesize = $this->_get('pageSize', 'intval', $this->pagesizes[0]);
        $this->pageCurrent = $pageCurrent = $this->_get('pageCurrent', 'intval', 1);
        $row = $m->where($map)
                ->join('pchotel_member_hotel ON pchotel_room.hotelid = pchotel_member_hotel.hotelid')
                ->field('hotelname,pchotel_room.*')
                ->order('id desc')
                ->page($pageCurrent . ',' . $pagesize)
                ->select();
                //var_dump($row);
        $this->assign('page', $show);
        $this->assign('list', $row);
        $this->custom=M('custom')->select();
        $this->display();
    }

    public function roomedit() {
        $map['id'] = $_GET['id'];
        $room = M('room')
                ->join('pchotel_member_hotel ON pchotel_room.hotelid = pchotel_member_hotel.hotelid')
                ->field('hotelname,pchotel_room.*')
                ->where($map)
                ->find();
                //dump($room);exit;
        $this->info = $room;
        $this->custom = M('custom')->select();
        $this->display();
    }

    //日历价格
    public function addmoney() {
        $rid = $_GET['id'];
        $ainfo = M('room')->where('id=' . $rid)->field('tjarr')->find();
        if ($ainfo) {
            if ($ainfo['tjarr']) {
                $daydata = explode('|', substr($ainfo['tjarr'], 0, -1));
                foreach ($daydata as $v) {
                    $daydata2 = explode('-', $v);
                    $daydatas[] = array('day' => date('Y-m-d', $daydata2[0]) . '|' . $daydata2[1]);
                }
                $this->assign("daydatas", json_encode($daydatas));
            }
        }
        $this->assign('id', $rid);
        $this->display();
    }

    //特殊价格保存

    /*
    public function savePrice() {
        $roo['id'] = $this->_post('id');
        $days = M('room')->where($roo)->field('tjarr')->find();
        if ($this->_post('starttime') && $this->_post('endtime')) {
            $start = strtotime($this->_post('starttime')); //开始时间
            $end = strtotime($this->_post('endtime')); //结束时间
            //$week = intval($this->_post('week'));  //周几
            $week   =   $_POST['week']; //星期几？
            $price = intval($this->_post('price'));  //价格
            $weekarr = array();
    
            if(count($week)!=7){
                        for($i=$start;$i<=$end;$i=$i+86400){
                            if(in_array(date('w',$i),$week)){
                                $weekarr[$i]=$price;
                            }
                        }
                    }else{
                        for($i=$start;$i<=$end;$i=$i+86400){
                            $weekarr[$i]=$price;
                        }
                    }
        }
        $daydata = ''; //时间 价格字符串
        $newday = array();
        if ($days['tjarr']) {
            $day = explode('|', substr($days['tjarr'], 0, -1));
            foreach ($day as $vo) {
                $oneday = explode('-', $vo);
                $newday[$oneday[0]] = $oneday[1];
            }
            if ($_POST['daymoney']) {
                foreach ($_POST['daymoney'] as $k => $v) {
                    $ks = strtotime($k);
                    $newday[$ks] = $v;
                }
            }
            if ($weekarr) {
                foreach ($weekarr as $k => $v) {
                    $newday[$k] = $v;
                }
            }
        } else {
            if ($_POST['daymoney']) {
                foreach ($_POST['daymoney'] as $k => $v) {
                    $ks = strtotime($k);
                    $newday[$ks] = $v;
                }
            }
            if ($weekarr) {
                foreach ($weekarr as $k => $v) {
                    $newday[$k] = $v;
                }
            }
        }
        $min = 99999999;
        if (count($newday) > 0) {
            //sort($newday);
            foreach ($newday as $k => $v) {
                if ($v != 0 && $k > time() - 86400) {
                    $daydata.= $k . '-' . $v . '|';
                    if ($v < $min) {
                        $min = $v;
                    }
                }
            }
            $data['tjarr'] = $daydata;
            $data['minprice'] = $min;
        }
        $res = M('room')->where($roo)->save($data);
        $res ? $this->json() : $this->json(0);
    }*/



    //特殊价格保存
    public function savePrice() {
        $roo['id'] = $this->_post('id');
        $days = M('room')->where($roo)->field('tjarr')->find();
        if ($this->_post('starttime') && $this->_post('endtime')) {
            $start = strtotime($this->_post('starttime')); //开始时间
            $end = strtotime($this->_post('endtime')); //结束时间
            //$week = intval($this->_post('week'));  //周几
            $week   =   $_POST['week']; //星期几？
            $price = intval($this->_post('price'));  //价格

            $roomnum=empty($_POST['roomnum'])?0:$_POST['roomnum'];

            $weekarr = array();
            /*if ($week != 7) {
                for ($i = $start; $i <= $end; $i+=86400) {
                    if (date('w', $i) == $week) {
                        $k = $i;
                        break;
                    }
                }
                for ($i = $k; $i <= $end; $i+=604800) {
                    $weekarr[$i] = $price;
                }
            } else {
                for ($i = $start; $i <= $end; $i+=86400) {
                    $weekarr[$i] = $price;
                }
            }*/
            if(count($week)!=7){
                        for($i=$start;$i<=$end;$i=$i+86400){
                            if(in_array(date('w',$i),$week)){
                                $weekarr[$i]=$price;
                            }
                        }
                    }else{
                        for($i=$start;$i<=$end;$i=$i+86400){
                            $weekarr[$i]=$price;
                        }
                    }
        }
        $daydata = ''; //时间 价格字符串
        $newday = array();
        if ($days['tjarr']) {
            $day = explode('|', substr($days['tjarr'], 0, -1));
            foreach ($day as $vo) {
                $oneday = explode('-', $vo);
                $newday[$oneday[0]] = $oneday[1];
            }
            if ($_POST['daymoney']) {
                foreach ($_POST['daymoney'] as $k => $v) {
                    $ks = strtotime($k);
                    $newday[$ks] = $v;
                }
            }
            if ($weekarr) {
                foreach ($weekarr as $k => $v) {
                    $newday[$k]=$v.'_'.$roomnum;
                }
            }
        } else {
            if ($_POST['daymoney']) {
                foreach ($_POST['daymoney'] as $k => $v) {
                    $ks = strtotime($k);
                    $newday[$ks] = $v;
                }
            }
            if ($weekarr) {
                foreach ($weekarr as $k => $v) {
                   // $newday[$k] = $v;
                    $newday[$k]=$v.'_'.$roomnum;
                }
            }
        }
        $min = 99999999;
        if (count($newday) > 0) {
            //sort($newday);
            foreach ($newday as $k => $v) {
                if ($v != 0 && $k > time() - 86400) {
                    $daydata.= $k . '-' . $v . '|';
                    if ($v < $min) {
                        $min = $v;
                    }
                }
            }
            $data['tjarr'] = $daydata;
            $data['minprice'] = $min;
        }
        $res = M('room')->where($roo)->save($data);
        $res ? $this->json() : $this->json(0);
    }




    //客房修改
    public function roomadd() {
        $id = $_POST['id'];
        if (!$id) {
            $this->json(300, "错误的操作");
        }
        $namePreg = '/^.{6,150}$/';
        if (preg_match($namePreg, $_POST['roomtype']) == 0) {
            $this->json(300, "房间标题必须在2-50个汉字之间");
        }
        if ($_POST['menshijia'] <= 0) {
            $this->json(300, "门市价格不是有效的的金额");
        }
        $pregPrice = '/^(\d+)(\.\d+)?$/';
        if (preg_match($pregPrice, $_POST['menshijia']) == 0) {
            $this->json(300, "门市价格不是有效的的金额");
        }
        $r = explode(',', $_POST['hotelname']);
        $data['thumb']          = $_POST['thumb'];
        $data['roomtype'] = $_POST['roomtype'];
        $data['fjchuang'] = $_POST['fjchuang'];
        $data['commission'] = $_POST['commission'];
        $data['returnmoney'] = $_POST['returnmoney'];
        $data['status'] = $_POST['status'];
        $data['zaocan'] = $_POST['zaocan'];
        $data['swang']  = $_POST['swang'];
        $data['menshijia'] = $_POST['menshijia'];
        $data['paytype'] = $_POST['paytype'];
        $data['content'] = stripslashes(htmlspecialchars_decode($_POST['content']));
        $row = M('room')->where(array('id' => $id))->save($data);
        $row ? $this->json() : $this->json(0);
    }

    //回收站房间列表
    public function roomhuishou() {
        $m = M('room');
        $con = trim($_GET['text']);
        $map['pchotel_room.is_delete'] = 1;
        if ($con) {
            $map['roomtype|hotelname'] = array('like', '%' . $con . '%');
        }
        //床型
        if ($_GET['roomtype']) {
            $map['pchotel_room.fjchuang']= array('like', '%' . $_GET['roomtype'] . '%');
        }
        $this->total = $total = $m->where($map)->join('pchotel_member_hotel ON pchotel_room.hotelid = pchotel_member_hotel.hotelid')
                ->field('hotelname,pchotel_room.*')->count();
        $this->pagesize = $pagesize = $this->_get('pageSize', 'intval', $this->pagesizes[0]);
        $this->pageCurrent = $pageCurrent = $this->_get('pageCurrent', 'intval', 1);
        $row = $m->where($map)
                ->join('pchotel_member_hotel ON pchotel_room.hotelid = pchotel_member_hotel.hotelid')
                ->field('hotelname,pchotel_room.*')
                ->order('id desc')
                ->page($pageCurrent . ',' . $pagesize)
                ->select();
        $this->assign('list', $row);
         $this->custom=M('custom')->select();
        $this->display();
    }

    //删除客房到回收站
    public function roomdel() {
        $map['id'] = intval($_GET['id']);
        $row = M('room')->where($map)->setField('is_delete', 1);
        $row ? $this->json() : $this->json(0);
    }

    public function rguan() {
        $m = M('room');
        $map['id'] = intval($_GET['id']);
        $d = intval($_GET['d']);
        if ($d == 0) {
            $row = $m->where($map)->setField('is_delete', 2);
        } else {
            $row = $m->where($map)->setField('is_delete', 0);
        }
        $row ? $this->json() : $this->json(0);
    }

    //回收站客房还原
    public function roomhy() {
        $map['id'] = intval($_GET['id']);
        $row = M('room')->where($map)->setField('is_delete', 0);
        $row ? $this->json() : $this->json(0);
    }

    //回收站彻底删除
    public function roomdel2() {
        $map['id'] = intval($_GET['id']);
        $row = M('room')->where($map)->delete();
        $row ? $this->json() : $this->json(0);
    }

    //客房批量操作
    public function roompl() {
        $ids = $_GET['ids'];
        if (!$ids) {
            die(json_encode(array('status' => 0, 'msg' => '请选择你要操作的数据')));
        }
        $m = M('room');
        $map['id'] = array('in', $ids);
        switch ($_GET['type']) {
            case '1':
                $row = $m->where($map)->setField('is_delete', 1);
                break;
            case '2':
                $row = $m->where($map)->setField('is_delete', 0);
                break;
            case '3':
                $row = $m->where($map)->delete();
                break;
            default:
                die(json_encode(array('status' => 0, 'msg' => '错误的操作')));
                break;
        }
        
        $row ? $this->json() : $this->json(0);
    }

    //客房回收站批量操作
    public function roompl2() {
        $id = $_POST['id'];
        if (!$id) {
            die(json_encode(array('status' => 0, 'msg' => '请选择你要操作的数据')));
        }
        $m = M('room');
        $map['id'] = array('in', $id);
        switch ($_POST['btn_submit']) {
            case '客房还原':
                $row = $m->where($map)->setField('is_delete', 0);
                break;
            case '删除产品':
                $row = $m->where($map)->delete();
                break;
            default:
                die(json_encode(array('status' => 1, 'msg' => '错误的操作')));
                break;
        }
        if ($row) {
            die(json_encode(array('status' => 1, 'msg' => '批量操作成功')));
        } else {
            die(json_encode(array('status' => 1, 'msg' => '操作成功')));
        }
    }

    public function tixian() {
        $tixian = M('tixian');
        import('ORG.Util.Page');
        $s['status'] = $_GET['status'];
        $count = $tixian->where($s)->count();
        $page = new Page($count, 8);
        $show = $page->show();
        $content = $tixian->where($s)->order('txid desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        $this->assign('tixian', $content);
        $this->assign('page', $show);
        $this->display();
    }

    //提现审核
    public function tixians() {
        $data['txid'] = $_GET['id'];
        $data['status'] = 1;
        $res = M('tixian')->data($data)->save();
        if ($res) {
            M('tixian')->where($order)->setInc('point', $point);
            echo 1;
        } else {
            echo 0;
        }
    }

    public function txdel() {
        $map['txid'] = $_GET['id'];
        $rs = M('tixian')->where($map)->delete();
        if ($rs) {
            $this->redirect('index');
        }
    }

    //设施列表
    public function sheshi() {
        $model = array(
            1 => M('sheshi'),
            2 => M('canyin'),
            3 => M('yule'),
            4 => M('roomsheshi'),
            5 => M('xingji'),
            6 => M('creditcard')
        );
        $m = $model[$_GET['cid']];
        $row = $m->order('id')->order('id desc')->select();
        $this->assign('list', $row);
        $this->display();
    }

    //设施编辑
    public function sheshiadd() {
        $model = array(
            1 => M('sheshi'),
            2 => M('canyin'),
            3 => M('yule'),
            4 => M('roomsheshi'),
            5 => M('xingji'),
            6 => M('creditcard')
        );
        $m = $model[$_GET['cid']];
        if ($this->isGet()) {
            if ($this->_get('id')) {
                $this->info = $m->where('id=' . $this->_get('id'))->find();
            }
            $this->display();
        } else {
            empty($_POST['names']) && $this->json(300, '名称不能为空');
            if ($this->_get('id')) {
                $rs = $m->where('id=' . $this->_get('id'))->setField('name', $this->_post('names'));
            } else {
                $rs = $m->add(array('name' => $this->_post('names')));
            }
            $rs ? $this->json(200, '保存成功', array('tabid' => "" . ',Hotels-sheshi' . $this->_get('cid'), 'closeCurrent' => true)) : $this->json(0);
        }
    }

    //设施删除
    public function sheshidel() {
        $model = array(
            1 => M('sheshi'),
            2 => M('canyin'),
            3 => M('yule'),
            4 => M('roomsheshi'),
            5 => M('xingji'),
            6 => M('creditcard')
        );
        $m = $model[$_GET['cid']];
        $rs = $m->where('id=' . $this->_get('id'))->delete();
        $rs ? $this->json() : $this->json(0);
    }

    public function liansuo() {
        $m = M('liansuo');
        if ($this->isGet()) {
            $id = $_GET['id'];
            if ($id) {
                $this->brand = $m->where(array('id' => $id))->find();
            }
            $this->display();
        } else {
            if (empty($_POST['name'])) {
                $this->json(300, '品牌名称不能为空');
            } elseif (strlen($_POST['name']) > 90) {
                $this->json(300, '品牌名称不得超过30个汉字');
            }
            if (empty($_POST['zimu'])) {
                $this->json(300, '请选择品牌首大写字母');
            }
            $data['thumb'] = $_POST['thumb'];
            $data['name'] = $_POST['name'];
            $data['zimu'] = $_POST['zimu'];
            $id = $_POST['id'];
            if ($id) {
                $res = $m->where(array('id' => $id))->save($data);
            } else {
                $res = $m->add($data);
            }
            $res ? $this->json(200, '操作成功', array('tabid' => "" . ',Hotels-lslist', 'closeCurrent' => true)) : $this->json(0);
        }
    }

    public function lslist() {
        $m = M('liansuo');
        $con = trim($_GET['text']);
        if ($con) {
            $map['name'] = array('like', '%' . $con . '%');
        }
        $this->total = $total = $m->where($map)->count();
        $this->pagesize = $pagesize = $this->_get('pageSize', 'intval', $this->pagesizes[0]);
        $this->pageCurrent = $pageCurrent = $this->_get('pageCurrent', 'intval', 1);
        $row = $m->where($map)->order('id desc')->page($pageCurrent . ',' . $pagesize)->select();
        $this->assign('list', $row);
        $this->display();
    }

    public function lsdel() {
        $map['id'] = intval($_GET['id']);
        $row = M('liansuo')->where($map)->delete();
        $row ? $this->json(1):$this->json(0);
    }

    public function lspl() {
        $id = $_POST['id'];
        if (!$id) {
            die(json_encode(array('status' => 0, 'msg' => '请选择操作的数据')));
        }
        $map['id'] = array('in', $id);
        $row = M('liansuo')->where($map)->delete();
        if ($row) {
            die(json_encode(array('status' => 1, 'msg' => '操作成功')));
        } else {
            die(json_encode(array('status' => 0, 'msg' => '操作错误')));
        }
    }

    public function theme(){
        $m=M('theme');
        if($this->isGet()){
            $this->total = $total = $m->where($map)->count();
            $this->pagesize = $pagesize = $this->_get('pageSize', 'intval', $this->pagesizes[0]);
            $this->pageCurrent = $pageCurrent = $this->_get('pageCurrent', 'intval', 1);
            $row = $m->where($map)->order('id desc')->page($pageCurrent . ',' . $pagesize)->select();
            $this->assign('list', $row);            
            $this->display();
        }
    }

    public function themeadd(){
        $m=M('theme');
        if($this->isGet()){
            $this->info=$m->where('id='.$_GET['id'])->find();
            $this->display();
        }else{
            $data['thumb'] = $_POST['thumb'];
            $data['name'] = $_POST['name'];
            $id=$_POST['id'];
            if ($id) {
                $res = $m->where(array('id' => $id))->save($data);
            } else {
                $res = $m->add($data);
            }
            $res ? $this->json(200, '操作成功', array('tabid' => "" . ',Hotels-theme', 'closeCurrent' => true)) : $this->json(0);            
        }
    }
    public function themedel() {
        $map['id'] = intval($_GET['id']);
        $row = M('theme')->where($map)->delete();
        $row ? $this->json(1):$this->json(0);
    }

}

?>