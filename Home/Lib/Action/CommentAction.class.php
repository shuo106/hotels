<?php

class CommentAction extends CommonAction {

    public function index() {

        $this->showcomments();
        $basic = M('basic');
        $row = $basic->select();
        $this->assign('title', $row[0]['webname']);
        $this->assign('keywords', $row[0]['keywords']);
        $this->assign('copyright', $row[0]['copyright']);
        $this->assign('description', $row[0]['description']);
        //查询所有点评
        $total = M('comment'); //实例化
        import("@.ORG.Util.Page"); // 导入分页类
        $keywords = $this->_get('keywords');
        $map['pchotel_comment.is_delete'] = 0;
        $map['pchotel_comment.status'] = 2;
        if ($keywords) {
            $map['title|content'] = array('like', '%' . $keywords . '%');
        }
        if ($this->_get('cate')) {
            $cate = $this->_get('cate');
            switch ($cate) {
                case 1:
                    $wh = '好评';
                    break;
                case 2:
                    $wh = '中评';
                    break;
                case 3:
                    $wh = '差评';
                    break;
                case 4:
                    $wh = '投诉';
                    break;
            }
            $map['label'] = $wh;
        }
        $count = $total->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 6);
        $show = $Page->show(); // 分页显示输出
        $list = $total
                ->join('pchotel_member_hotel ON pchotel_comment.hotelid = pchotel_member_hotel.hotelid')
                ->join('pchotel_order ON pchotel_comment.orderid = pchotel_order.orderid')
                ->join('pchotel_member ON pchotel_comment.uid = pchotel_member.id')
                ->field('pchotel_member.username as uname,pchotel_member_hotel.hotelname,pchotel_comment.*,pchotel_order.point')
                ->where($map)
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->order('id desc')
                ->select();
        foreach($list as $k=>&$v){
            $v['thumb']&&$v['thumb']=explode(',', rtrim($v['thumb'],','));
            $v['src']=M('photo')->where('hotelid='.$v['hotelid'])->order('isdefault desc')->getField('src');
            $v['head']=M('member')->where('id='.$v['uid'])->getField('icon');
        }
       // var_dump($list);die;
        $this->num=$count;
        $this->assign('row', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    public function _empty() {
        exit('方法不存在');
    }

    public function showcomments() {
        $dosubmit = $_GET['dosubmit'];
        if ($dosubmit) {

            $keywords = $_GET['keywords'];
            $condition = "";
            if (!empty($keywords)) {
                $condition = "and title like '%$keywords%' or msn like '%$keywords%' or email like '%$keywords%') ";
            }
            if (!empty($condition)) {
                $pagesize = 6;
                $page = empty($_GET['p']) ? 1 : $_GET['p'];
                $result1 = mysql_query("select companyid form member_hotel");
                while ($r = mysql_fetch_array($result1)) {
                    $company_id.=$r['companyid'];
                }$company_id.='0';
            }
            $query = "select count(*) as number from comment where status='1' and label='好评' 
			$condition and orderid in(select orderid from order where companyid in ($company_id) ) ";
            $result = mysql_query($query);
            $row = mysql_fetch_array($result);
            $numberbest = $row['number'];
            echo $numberbest . '333333';

            $query = "select count(*) as number from comment where status='1' and label='中评'
			$condition and orderid in (select orderid from order where companyid in ($company_id))";
            $r = mysql_query($query);
            $numbermid = $r['number'];

            $query = "select count(*) as number from comment where status='1' and label='差评'
			$condition and orderid in (select orderid from order where companyid in ($company_id))";
            $r = mysql_query($query);
            $numberlow = $r['number'];


            $query = "select count(*) as number from comment where status='1' and label='投诉'
			$condition and orderid in (select orderid from order where companyid in ($company_id))";
            $r = mysql_query($query);
            $numbercom = $r['number'];

            $query = "select count(*) as number from comment where status='1' 
			$condition and orderid in (select orderid from order where companyid in ($company_id))";
            $r = mysql_query($query);
            $numberall = $r['number'];

            $this->assign('num', $numberall);
        } else {
            $query = "select count(*) as number from pchotel_comment where status='1' and label='好'";
            $result = mysql_query($query);
            $row = mysql_fetch_array($result);
            $numberbest = $row['number'];
            $this->assign('numberbest', $numberbest);

            $query = "select count(*) as number from pchotel_comment where status='1' and label='中'";
            $result = mysql_query($query);
            $row = mysql_fetch_array($result);
            $numbermid = $row['number'];
            $this->assign('numbermid', $numbermid);

            $query = "select count(*) as number from pchotel_comment where status='1' and label='差'";
            $result = mysql_query($query);
            $row = mysql_fetch_array($result);
            $numberblow = $row['number'];
            $this->assign('numberblow', $numberblow);

            $query = "select count(*) as number from pchotel_comment where status='1' and label='投诉'";
            $result = mysql_query($query);
            $row = mysql_fetch_array($result);
            $numbercom = $row['number'];
            $this->assign('numbercom', $numbercom);

            switch ($cit) {
                case '1':$where.=" and label='好评'";
                    break;
                case '2':$where.=" and label='中评'";
                    break;
                case '3':$where.=" and label='差评'";
                    break;
                case '4':$where.=" and label='投诉'";
                    break;
            }
            $comments = M('comment');
            $num = $comments->where("status=1")->count();
            $this->assign('num', $num);
            $rs = $comments->where("status='1'")->
                    $query = "select count(*) as number from " . comment . " where status='1'";
            $rs = mysql_query($query);
            $r = mysql_fetch_array($rs);
            $numberall = $r['number'];
            $pagesize = 3;
            $page = empty($_GET['p']) ? 1 : $_GET['p'];
            $start = ($page - 1) * $pagesize;
            if ($numberall) {
                $result = mysql_query("select * from comment where status='1' $where
				order by id desc limit $start,$pagesize");
                while ($r = mysql_fetch_array($result)) {
                    $r['addtime'] = date('Y-m-d', $r['addtime']);
                    $comments[] = $r;
                }
                $this->assign('comments', $comments);
            }
        }
    }

    public function getPages() {
        $picture = M('pictrue');
        $total = count($picture->select());
        $page = empty($_GET['p']) ? 1 : $_GET['p'];
        $pagesize = 3;
        $start = ($page - 1) * $pagesize;
        $picture_list = $picture->limit("$start,$pagesize")->select();
        import('@.ORG.Util.Page');
        $divide = new Page($total, $pagesize);
        $pageStr = $divide->show();
        $this->assign('pag', $pageStr);
        $this->assign('pictures', $picture_list);
    }

}
