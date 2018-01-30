<?php

class LinkAction extends CommonAction {

    public function index() {
        $map['id'] = $_GET['id'];
        if ($map) {
            $row = M('link')->where($map)->find();
            foreach ($row as $key => $value) {
                $this->assign($key, $value);
            }
        }
        $this->display();
    }

    //图片上传
    public function ajax_img_upload() {
        if ($_FILES['file']['name'] != '') {
            $dirname = 'link';
            $fileinfo = $this->uploadimg($dirname, 1, '40', '30', 'thumb');
            //执行上传函数 不开启缩略图
            if ($fileinfo['photo_path'] && $fileinfo['thumb_path']['thumb']) {
                //$this -> json_die(200, '上传成功', array('thumb' => $fileinfo['thumb_path']['thumb'], 'yuantu' => $fileinfo['photo_path']));
                $this->json_die(200, '上传成功', array('thumb' => $fileinfo['thumb_path']['thumb'], 'yuantu' => $fileinfo['photo_path']));
            } else {
                $this->json_die(300, '上传失败');
            }
        }
    }

    public function sort() {
        $sorts = $_POST['sort'];
        $res = 0;
        foreach ($sorts as $id => $sort) {
            $res+=M('link')->where(array('id' => $id))->setField('sort', $sort);
        }
        $res ? $this->json_die(1, '排序成功') : $this->json_die(0);
    }

    public function add() {
        if (isset($_POST['btn_submit'])) {
            $id = intval($_POST['id']);
            $title = '/^.{6,60}$/';
            if (preg_match($title, $_POST['name']) == 0) {
                //$this->json_die(300,'网站名称必须在2-20个汉字之间');
                $this->json(300, '网站名称必须在2-20个汉字之间');
            }
//			$url = '/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i';
//			if (preg_match($url, $_POST['url']) == 0) {
//				$this->json_die(300,'请填写正确的网站url连接地址');
//			}
            if (!$id) {
                if ($_POST['linktype'] == 2 && $_POST['logo'] == '') {
                    $this->json(300, '网站logo不能为空');
                }
            }
            if (!empty($_POST['introduce'])) {
                $key = '/^.{15,900}$/';
                if (preg_match($key, $_POST['introduce']) == 0) {
                    $this->json(300, '网站介绍必须在5-300个汉字之间');
                }
            }
            //print_r($_POST);die;
            $data = array();
            $data['linktype'] = $_POST['linktype'];
            $data['listorder'] = $_POST['listorder'];
            $data['passed'] = $_POST['passed'];
            $data['name'] = $_POST['name'];
            $data['url'] = $_POST['url'];
            if ($_POST['logo']) {
                $data['logo'] = $_POST['logo'];
            }
            $data['introduce'] = $_POST['introduce'];
            $m = M('link');

            if ($id) {
                $row = $m->where("id = $id")->data($data)->save();
            } else {
                $data['addtime'] = time();
                $row = $m->data($data)->add();
            }
            $row ? $this->json(1, '成功', array('id' => 'Link-lists', 'close' => true)) : $this->json_die(0, '失败', array('msg' => '数据无变化'));
        }
    }

    public function lists() {
        $con = $_GET['text'];
        $m = M('link');
        if ($con) {
            $map['name'] = array('like', '%' . $con . '%');
        }
        //设置排序
        if ($this->_get('orderField')) {
            $order = $this->_get('orderField') . ' ' . $this->_get('orderDirection');
        }else{
            $order = 'sort asc,id desc';
        }
        $row = $m->where($map)->order($order)->select();
        $this->assign('list', $row);
        $this->display();
    }

    public function pl() {
        $id = $_POST['id'];
        if ($_POST['btn_submit'] != '更新排序') {
            if (!$id) {
                die(json_encode(array('status' => 0, 'msg' => '请选择你要操作的数据')));
            }
        }
        $m = M('link');
        $map['id'] = array('in', $id);
        switch ($_POST['btn_submit']) {
            case '推荐链接' :
                $row = $m->where($map)->setField('listorder', 1);
                break;
            case '更新排序' :
                foreach ($_POST['sort'] as $k => $v) {
                    $row = $m->where(array('id' => $k))->setField('sort', $v);
                }
                break;
            case '取消推荐' :
                $row = $m->where($map)->setField('listorder', 0);
                break;
            case '批准链接' :
                $row = $m->where($map)->setField('passed', 1);
                break;
            case '取消批准' :
                $row = $m->where($map)->setField('passed', 2);
                break;
            case '删除链接' :
                $row = $m->where($map)->delete();
                break;
            default :
                die(json_encode(array('status' => 1, 'msg' => '错误的操作')));
                break;
        }
        if ($row) {
            die(json_encode(array('status' => 1, 'msg' => '批量操作成功')));
        } else {
            die(json_encode(array('status' => 1, 'msg' => '操作成功')));
        }
    }

    public function del() {
        $map['id'] = $_GET['id'];
        $res = M('link')->where($map)->delete();
        $res ? $this->json_die(1) : $this->json_die(0);
    }
    public function pld() {
        $id = $_GET['ids'];
        if (!$id) {
            $this->json_die(0,"请选择操作的数据");
        }
        $map['id'] = array('in',$id);
        $row = M('link')->where($map)->delete();
        if ($row) {
            $this->json_die(1);
        } else {
            $this->json_die(0);
        }
    }
    public function quxiao() {
        $map['id'] = $_GET['id'];
        $row = M('link')->where($map)->setField('passed', 2);
        $row ? $this->json_die(1) : $this->json_die(0);
    }

    public function zhengchang() {
        $map['id'] = $_GET['id'];
        $row = M('link')->where($map)->setField('passed', 1);
        $row ? $this->json_die(1) : $this->json_die(0);
    }
}

?>