<?php

class CommonAction extends Action {

    public function _initialize() {
        //会话
        session('city_name')||session('city_name','北京');
        session('city_id')||session('city_id','100020');
        session('start')||session('start',date('Y-m-d',time()));
        session('end')||session('start',date('Y-m-d',time()+86400));        
        //网站基础数据
        $content = M('basic')->find();
        $this->assign('logo', $content['thumb']);
        $this->assign('title', $content['webname']);
        $this->assign('keywords', $content['keywords']);
        $this->assign('description', $content['description']);
        $this->assign('copyright', $content['copyright']);
        //城市缓存
        $city = getCity();
        foreach ($city['allzm'] as $k => $v) {
            $this->assign("$k", $v);
        }
        $this->allPY = $city['allPY'];
        $this->hot = $city['hot'];
        $this->hot2 = $city['hot2'];
        //连锁品牌
        $this->brands = M('liansuo')->limit(12)->select();
        //网站导航
        $this->daohang=M('daohang')->where(array('is_show'=>1))->order('sort desc,id asc')->select();
        //配置
        $this->order_config=C('order');
        //友情链接 
        $this->frendlink = M('link')->field(array('name', 'url'))->limit(20)->order('sort ASC')->select();  
        $this->LANG=C('order');
    }

    public function _empty() {
        exit('方法不存在');
    }

    //发送短信
    public function sms($mobile=0,$str){
        if($mobile){
            //基础数据
            Vendor('ChuanglanSmsHelper.ChuanglanSmsApi');
            $SMS = new ChuanglanSmsApi();
            $row=include 'Admin/Conf/message.php';
            $name=iconv("UTF-8","gb2312",$row['smsname']);
            $pwd=iconv("UTF-8","gb2312",$row['smspwd']);
            $con=$str;
            $result=$SMS->sendSMS($name,$pwd,$mobile,$con);
			$strs=$SMS->execResult($result);
			return $strs[1];
        }else{
            return false;
        }
    }    

    //递归调用 获取子分类
    public function getSonCate($pid, $typeid = 0) {
        $li['pid'] = $pid;
        $li['is_delete'] = 0;
        if ($typeid) {
            $li['type'] = $typeid;
        }
        $cate = M('cate')->where($li)->order('sort desc')->field('id,name')->select();
        foreach ($cate as $k => &$v) {
            $ids.=$v['id'] . ',';
            $o = $this->getSonCate($v['id']);
            if ($o) {
                $v['son'] = $o['son'];
                $ids.=$o['ids'];
            }
        }
        $out['son'] = $cate;
        $out['ids'] = $ids;
        return $out;
    }

    //根据分类id获取所有子栏目id 字符串表示
    public function getCateSon($pid) {
        $data['pid'] = $pid;
        $data['is_delete'] = 0;
        $sons = M('cate')->where($data)->field('id')->select();
        foreach ($sons as $k => $v) {
            $nid.=$v['id'] . ',';
            $ids = $this->isSonCate($v['id']);
            if ($ids) {
                foreach ($ids as $kk => $vv) {
                    $nid.=$vv['id'] . ',';
                    $nid.= $this->getCateSon($vv['id']);
                }
            }
        }
        return $nid;
    }

    //判断是否还有子栏目 存在返回子栏目  没有false
    public function isSonCate($id) {
        $data['pid'] = $id;
        $data['is_delete'] = 0;
        return M('cate')->where($data)->field('id')->select();
    }

    public function dan() {
        $data = M('single');
        $zuo = $data->select();
        $this->assign('zuo', $zuo);
    }

    //栏目分类名称获取 $cid 分类id 
    public function getCateName($cid) {
        $n = M('cate')->where('id=' . $cid)->field('name')->find();
        return $n['name'];
    }

    public function getComment($id, $tid) {
        $com['lvyou_comment.itemid'] = $id;
        $com['lvyou_comment.tid'] = $tid;
        $com['lvyou_comment.status'] = 1;
        $comm = M('comment')->where($com)
                ->join('lvyou_member ON lvyou_comment.uid= lvyou_member.userid')
                ->field('lvyou_comment.*,lvyou_member.username')
                ->select();
        $comm2 = array();
        //评论人数
        $cNums = count($comm);
        if ($cNums > 0) {
            //总评分 满意度
            $cTotal = 0;

            //好 中 差
            $good = $general = $poor = 0;
            foreach ($comm as $k => $v) {
                $cTotal+=$v['unit'];
                if ($v['label'] == '好') {
                    $good++;
                }if ($v['label'] == '中') {
                    $general++;
                }if ($v['label'] == '差') {
                    $poor++;
                }
            }

            $comm2['good'] = floor($good / $cNums * 10000) / 100;
            $comm2['general'] = floor($general / $cNums * 10000) / 100;
            $comm2['poor'] = floor($poor / $cNums * 10000) / 100;
            $comm2['cTotal'] = floor($cTotal / 0.2 / $cNums) / 100;
            $comm2['tui'] = floor($cTotal / $cNums * 100) / 100;
            $comm2['cNums'] = $cNums;
        } else {
            $comm2['good'] = 33.33;
            $comm2['general'] = 33.33;
            $comm2['poor'] = 33.33;
            $comm2['cTotal'] = 5;
            $comm2['tui'] = 100;
            $comm2['cNums'] = 0;
        }
        $out['comm'] = $comm;
        $out['comm2'] = $comm2;
        return $out;
    }

    public function curl_post($url,$post_arr,$referer=''){
        $post_str = '';
        foreach ( $post_arr as $k => $v ) {
            $post_str .= $k . '=' . $v . '&';
        }
        $post_str = substr ( $post_str, 0, - 1 );   
        $curl = curl_init ();
        curl_setopt ( $curl, CURLOPT_URL, $url ); //要访问的地址 即要登录的地址页面    
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, 1 ); // 从证书中检查SSL加密算法是否存在
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, false ); // 对认证证书来源的检查
    //  curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header_arr );
        curl_setopt ( $curl, CURLOPT_POST, 1 ); // 发送一个常规的Post请求
        curl_setopt ( $curl, CURLOPT_POSTFIELDS, $post_str ); // Post提交的数据包
        curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, 0 ); // 使用自动跳转
    //  curl_setopt ( $curl, CURLOPT_COOKIEJAR, $cookie_file ); // 存放Cookie信息的文件名称
    //  curl_setopt ( $curl, CURLOPT_COOKIEFILE, $cookie_file ); // 读取上面所储存的Cookie信息
        curl_setopt ( $curl, CURLOPT_REFERER, $referer ); //设置Referer
    //  curl_setopt ( $curl, CURLOPT_USERAGENT, $_SERVER ['HTTP_USER_AGENT'] ); // 模拟用户使用的浏览器
        curl_setopt ( $curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1; rv:9.0.1) Gecko/20100101 Firefox/9.0.1" ); // 模拟用户使用的浏览器
        curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
        curl_setopt ( $curl, CURLOPT_HEADER, false ); //获取header信息
        $result = curl_exec ( $curl );
        return $result;
    }       

    public function js_die($action = '', $msg = '', $query, $script = '') {
        unset($_REQUEST['__hash__']);
        unset($_REQUEST['_URL_']);
        $_REQUEST = array_filter($_REQUEST); //过滤空值
        $action = U($action);
        if (!empty($query)) {
            if (is_string($query)) {
                $queryStr = $query;
            } elseif (is_array($query)) {
                if (end($query) === true) {
                    //从$_REQUEST删除
                    array_pop($query);
                    foreach ($query as $key) {
                        unset($_REQUEST[$key]);
                    }
                    foreach ($_REQUEST as $key => $value) {
                        $queryStr.=($queryStr == '' ? '' : '&') . $key . '=' . rawurlencode($value);
                    }
                } else {
                    //手动添加
                    foreach ($query as $key => $value) {
                        $queryStr.=($queryStr == '' ? '' : '&') . $key . '=' . rawurlencode($value);
                    }
                }
            } elseif ($query === true) {
                foreach ($_REQUEST as $key => $value) {
                    $queryStr.=($queryStr == '' ? '' : '&') . $key . '=' . rawurlencode($value);
                }
            }
        }
        if ($queryStr) {
            $action = $action . '?' . $queryStr;
        }
        $time = 0.1;
        $out = "<script>
        alert('{$msg}');
        " . ($script ? $script : '') . "
        </script>";
        redirect($action, 0.1, $out);
    }    

}
