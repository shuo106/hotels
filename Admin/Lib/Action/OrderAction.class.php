<?php

class OrderAction extends CommonAction {

    public function index() {
        $order = M('order');
        $status = empty($_GET['status']) ? '' : $_GET['status'];
        $where['pchotel_order.status'] = array('neq', 0);
        //订单状态
        if (!empty($status)) {
            $where['pchotel_order.status'] = $status;
        }
        //下单时间
        if ($this->_get('addtime')) {
            $where['pchotel_order.addtime'] = strtotime($this->_get('addtime'));
        }
        //入住时间
        if ($this->_get('ruzhudate')) {
            $where['pchotel_order.ruzhudate'] = array('egt', strtotime($this->_get('ruzhudate')));
        }
        //离店时间
        if ($this->_get('lidiandate')) {
            $where['pchotel_order.lidiandate'] = array('elt', strtotime($this->_get('lidiandate')));
        }
        //酒店名称
        if ($this->_get('hotelname')) {
            $where['hotelname'] = array('like', '%' . $this->_get('hotelname') . '%');
        }
        //客人姓名
        if ($this->_get('kename')) {
            $where['pchotel_order.linkman'] = array('like', '%' . $this->_get('kename') . '%');
        }
        //电话
        if ($this->_get('telephone')) {
            $where['pchotel_order.telephone'] = array('like', '%' . $this->_get('telephone') . '%');
        }
        //订单号
        if ($this->_get('order_no')) {
            $where['pchotel_order.orderid'] = substr($this->_get('order_no'), 10);
        }
        //来源
        if ($this->_get('from')) {
            $where['pchotel_order.from'] = $this->_get('from');
        }
        $this->order_no = $this->_get('order_no');
        $this->pagesizes = array(20, 30, 50, 100);
        $this->pagesize = $pagesize = $this->_get('pageSize', 'intval', $this->pagesizes[0]);
        $this->pageCurrent = $pageCurrent = $this->_get('pageCurrent', 'intval', 1);
        //加入排序功能
        if ($this->_get('orderField')) {
            $orderby = "pchotel_order." . $this->_get('orderField') . ' ' . $this->_get('orderDirection');
        } else {
            $orderby = 'pchotel_order.orderid desc';
        }
        $this->sum=$order
                ->where($where)
                ->join('pchotel_room ON pchotel_order.roomid = pchotel_room.id')
                ->join('pchotel_member_hotel ON pchotel_order.hotelid = pchotel_member_hotel.hotelid')
                ->sum('shoufei');
        $this->total = $order
                ->where($where)
                ->join('pchotel_room ON pchotel_order.roomid = pchotel_room.id')
                ->join('pchotel_member_hotel ON pchotel_order.hotelid = pchotel_member_hotel.hotelid')
                ->count();
        $content = $order->join('pchotel_room ON pchotel_order.roomid = pchotel_room.id')
                ->join('pchotel_member_hotel ON pchotel_order.hotelid = pchotel_member_hotel.hotelid')
                ->order('pchotel_order.orderid desc')
                ->field('pchotel_member_hotel.hotelname,pchotel_room.roomtype,pchotel_order.*')
                ->where($where)
                ->order($orderby)
                ->page($pageCurrent . ',' . $pagesize)
                ->select();
        $this->assign('orderlist', $content);
        $this->display();
    }

    /*
      导出Excel
     */

    public function outExcel() {
        
        /*header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-Disposition:attachment;filename=booking".date('Y-m-d',time()).".xls");
        header("Content-type:application/vnd.ms-excel;charset=utf-8");
        header("Pragma: no-cache");
        header("Expires: 0");
         
        $where['pchotel_order.status'] = array('neq', 0);
        if ($this->_get('status')) {
            $where['pchotel_order.status'] = $this->_get('status');
        }
        if ($this->_get('addtime')) {
            $where['pchotel_order.addtime'] = strtotime($this->_get('addtime'));
        }
        if ($this->_get('ruzhudate')) {
            $where['ruzhudate'] = array('egt', strtotime($this->_get('ruzhudate')));
        }
        if ($this->_get('lidiandate')) {
            $where['lidiandate'] = array('elt', strtotime($this->_get('lidiandate')));
        }
        if ($this->_get('hotelname')) {
            $roo['hotelname'] = array('like', '%' . $this->_get('hotelname') . '%');
            $ho = M('room')->where($roo)->field('hotelid')->select();
            $hotels = array();
            foreach ($ho as $v) {
                $hotels[] = $v['hotelid'];
            }
            $where['pchotel_order.hotelid'] = array('in', $hotels);
        }
        if ($this->_get('kename')) {
            $where['linkman'] = array('like', '%' . $this->_get('kename') . '%');
        }
        if ($this->_get('telephone')) {
            $where['telephone'] = array('like', '%' . $this->_get('telephone') . '%');
        }
        if ($this->_get('order_no')) {
            $where['pchotel_order.orderid'] = substr($this->_get('order_no'), 10);
        }
        $olist = M('order')->where($where)
                ->join('pchotel_room ON pchotel_order.roomid = pchotel_room.id')
                ->join('pchotel_member_hotel ON pchotel_room.hotelid=pchotel_member_hotel.hotelid')
                ->field('pchotel_order.*,pchotel_room.roomtype,pchotel_member_hotel.hotelname')
                ->order('orderid desc')
                ->select();
        $title = array('订单号', '酒店名称', '房型', '预订人', '预订人电话', '入住时间', '离店时间', '房价', '订单状态');
        $data = array();
        foreach ($olist as $k => $v) {
            $data[$k]['orderid'] = $v['orderid'];
            $data[$k]['hotelname'] = $v['hotelname'];
            $data[$k]['roomtype'] = $v['roomtype'];
            $data[$k]['orderid'] = $v['addtime'] . $v['orderid'];
            $data[$k]['linkman'] = $v['linkman'];
            $data[$k]['telephone'] = $v['telephone'];
            $data[$k]['ruzhudate'] = date('Y-m-d', $v['ruzhudate']);
            $data[$k]['lidiandate'] = date('Y-m-d', $v['lidiandate']);
            $data[$k]['shoufei'] = $v['shoufei'] . '元';
            switch ($v['status']) {
                case 4:
                    $data[$k]['status'] = '待确认';
                    break;
                case 5:
                    $data[$k]['status'] = '已确认';
                    break;
                case 6:
                    $data[$k]['status'] = '待付款';
                    break;
                case 3:
                    $data[$k]['status'] = '已付款';
                    break;
                case 1:
                    $data[$k]['status'] = '已入住';
                    break;
                case 2:
                    $data[$k]['status'] = '已取消';
                    break;
            }
        }
        //导出xls 开始
        foreach ($title as $k => $v) {
            $title[$k] = $v;
        }
        $title = implode("\t", $title);
        echo iconv('utf-8', 'gb2312', "$title\n");
        if (!empty($data)) {
            foreach ($data as $k => &$v) {
                $v = implode("\t", $v);
            }
            echo iconv('utf-8', 'gb2312//IGNORE', implode("\n", $data));
        }
        echo iconv('utf-8', 'gb2312', "\n");
        $total = M('order')->where($where)->sum('shoufei');
        $nums = count($olist);
        $tot = '订单总数：' . $nums . ' 金额总数：' . $total . '元';
        echo iconv('utf-8', 'gb2312', "$tot");*/
        import("ORG.excel.PHPExcel");
        $field = array('a.orderid','h.hotelname','b.roomtype','a.linkman','a.telephone','a.kename','a.addtime','a.ruzhudate','a.shoufei','a.status','a.from');
        $tableheader=array('订单编号','预订酒店','预订房间','预订姓名','预订电话','入住客人','预订时间','入住时间','应付金额','订单状态','订单来源');


        if($_GET['order_no'] !=1){
            $where['a.orderid'] = substr($_GET['order_no'],10);
        }
        if($_GET['status'] !=0){
            $where['a.status'] = $_GET['status'];
        }
        if($_GET['form'] !=0){
            $where['a.from'] = $_GET['form'];
        }

        $data = M('order a')
                ->join('pchotel_room b on a.roomid=b.id')
                ->join('pchotel_member c on a.username=c.username')
                ->join('pchotel_member_hotel h ON h.hotelid = a.hotelid')
                ->where($where)
                ->field($field)
                ->order('a.orderid desc')
                ->select();
                //dump($data);die;
        foreach ($data as $k=> $v) {
            if(!$v['roomtype']){
                unset($data[$k]);
            }
            if(empty($v['linkman'])){
                $data[$k]['linkman'] = "空";
            }
            if(empty($v['kename'])){
                $data[$k]['kename'] = "空";
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
        $excel->getActiveSheet()->setCellValue('A1', '订单列表   导出日期：'.date('Y-m-d',time()));
        $excel->getActiveSheet()->getStyle( 'A1')->getFont()->setSize(20)->setBold(true);
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
                    if($k=='orderid'){
                        $tmp=' '.$newArr[$i - 3]['addtime'].$newArr[$i - 3]['orderid'];
                        $excel->getActiveSheet()->setCellValue("$letter[$j]$i","$tmp");
                    }elseif($k=='from'){
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
                    }elseif($k=='addtime'){
                        $tmp=date('Y-m-d',$v);
                    }elseif($k=='ruzhudate'){
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

    //插入变量
    public function variable() {
        $map['orderid'] = $this->_get('id');
        $this->order = M('order')->where($map)->find();
        $this->display();
    }

    public function edit() {
        $id = $_GET['id'];
        $content = M('order')->join('pchotel_room ON pchotel_order.roomid = pchotel_room.id')
                ->join('pchotel_member_hotel ON pchotel_order.hotelid = pchotel_member_hotel.hotelid')
                ->field('pchotel_member_hotel.hotelname,pchotel_room.roomtype,pchotel_order.*')
                ->where("orderid=$id")
                ->find();
        $this->assign('order', $content);
        //查看操作记录
        $log = M('order_log')->where("type=1 and orderid=" . $_GET['id'])->select();
        $this->assign("log", $log);
        $this->display();
    }

 public function sms() {
        $map['orderid'] = $_GET['id'];
        $content = M('order')
                ->where($map)
                ->join('pchotel_room ON pchotel_order.roomid = pchotel_room.id')
                ->field('pchotel_room.roomtype,pchotel_order.*')
                ->find();
        $this->assign('order', $content);
        $this->display();
    }


/*public function smsok(){
                         
   $r= $this->tosms($_POST['smsphohe'],$_POST['content']);
   
   $r?$this->json(0,'发送失败，错误码：'.$r):$this->json(1,'发送成功');

}*/
 public function smsok(){               
   $r= $this->tosms($_POST['smsphohe'],$_POST['content']);

   //var_dump($r);exit;
   if($r ==0){
        $this->json(1,'发送成功');
   }else{
        $this->json(0,'发送失败，错误码：'.$r);
   }
}



    public function update() {
        $wh['orderid'] = $this->_get('id');
        $status = $this->_get('status');
        $res = M('order')->where($wh)->setField('status', $status);
       // echo M('order')->getlastsql();
        if ($res) {
            $smsData = include 'Admin/Conf/message.php';
            Vendor('ChuanglanSmsHelper.ChuanglanSmsApi');
            $SMS = new ChuanglanSmsApi();
            if ($status == 2 && $smsData) {
                $orderData = M('order')->where($wh)
                        ->join('pchotel_room ON pchotel_order.roomid =pchotel_room.id')
                        ->join('pchotel_member_hotel ON pchotel_order.hotelid =pchotel_member_hotel.hotelid')
                        ->field('ruzhudate,lidiandate,roomtype,pchotel_order.username,hotelname,pchotel_order.addtime,pchotel_order.telephone,shoufei,nums')
                        ->find();
                $starttime = date('Y-m-d', $orderData['ruzhudate']);
                $ordertime = date('Y-m-d', $orderData['addtime']);
                $endtime = date('Y-m-d', $orderData['lidiandate']);
                $webname = M('basic')->getField('webname');
                $telephone = $orderData['telephone'];
                //是否发送短信
                if ($smsData['hotelConfirmSend'] == '1') {
                    $relArr = array('#WEBNAME#', '#LOGINNAME#', '#ORDERTIME#', '#ORDERNUMS#', '#ORDERTOTAL#', '#HOTELNAME#', '#ROOMNAME#', '#STARTTIME#', '#ENDTIME#');
                    $subArr = array($webname, $orderData['username'], date('Y-m-d H:i:s', $ordertime), $orderData['nums'], $orderData['shoufei'], $orderData['hotelname'], $orderData['roomtype'], $starttime, $endtime);
                    $SendSms = str_replace($relArr, $subArr, $smsData['hotelConfirmSms']);
                    $name = iconv("UTF-8", "gb2312", $smsData['smsname']);
                    $pwd = iconv("UTF-8", "gb2312", $smsData['smspwd']);
                    $con = $SendSms;
                    $res=$SMS->sendSMS($name,$pwd,$telephone,$con);
                   // var_dump($res);
                }
            }
            $this->log($this->_get('id'), $status, $this->_get('remark'));
            $this->json(1);
        } else {
            $this->json(0);
        }
    }

    //写入记录
    public function log($id, $status, $str) {
        $data['orderid'] = $id;
        $data['addtime'] = time();
        $order = C('order');
        $data['username'] = session("admin_name");
        $data['text'] = $order['status'][$status];
        $data['remark'] = $str;
        $data['type'] = 1;
        M('order_log')->add($data);
    }

    public function delete() {
        $map['orderid'] = $_GET['id'];
        $rs = M('order')->where($map)->delete();
        $rs ? $this->json() : $this->json(0);
    }

    public function pl() {
        $id = $_GET['ids'];
        if (!$id) {
            $this->json(300, '请选择你要操作的数据');
        }
        $m = M('order');
        $map['orderid'] = array('in', $id);
        $row = $m->where($map)->delete();
        $row ? $this->json() : $this->json(0);
    }

/*
    public function smsok() {
        $row = M('basic')->find();
        $telephone = $_POST['smsphohe'];
        $cn = $_POST['smscon'];
        $name = iconv("UTF-8", "gb2312", $row[smsname]);
        $pwd = iconv("UTF-8", "gb2312", $row[smspwd]);
        $c = iconv("UTF-8", "gb2312", $cn);

        $con = "$c"; //短信内容
        $postData = array(
            "accountname" => "$name",
            "accountpwd" => "$pwd",
            "mobilecodes" => "$telephone",
            "msgcontent" => $con
        );
        $rs = $this->curl_post("http://csdk.zzrwkj.com:4002/submitsms.aspx", $postData);
        $rs == 0 ? $this->json(200, '发送成功') : $this->json(300, '发送失败，错误码：' . $result);
    }*/

    public function curl_post($url, $post_arr, $referer = '') {
        $post_str = '';
        foreach ($post_arr as $k => $v) {
            $post_str .= $k . '=' . $v . '&';
        }
        $post_str = substr($post_str, 0, - 1);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url); //要访问的地址 即要登录的地址页面	
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        //	curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header_arr );
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_str); // Post提交的数据包
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0); // 使用自动跳转
        //	curl_setopt ( $curl, CURLOPT_COOKIEJAR, $cookie_file ); // 存放Cookie信息的文件名称
        //	curl_setopt ( $curl, CURLOPT_COOKIEFILE, $cookie_file ); // 读取上面所储存的Cookie信息
        curl_setopt($curl, CURLOPT_REFERER, $referer); //设置Referer
        //	curl_setopt ( $curl, CURLOPT_USERAGENT, $_SERVER ['HTTP_USER_AGENT'] ); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1; rv:9.0.1) Gecko/20100101 Firefox/9.0.1"); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        curl_setopt($curl, CURLOPT_HEADER, false); //获取header信息
        $result = curl_exec($curl);
        return $result;
    }

}
