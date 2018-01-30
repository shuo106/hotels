<?php

class UploadifyAction extends Action {

    public function index() {
        $verifyToken = md5('unique_salt' . $_POST['timestamp']);
        if (!empty($_FILES)) {
            $targetFolder = 'room/' . $_POST['room'];
            $fileinfo = $this->uploadimg($targetFolder); //执行上传函数 不开启缩略图
        }
        if ($fileinfo['photo_path']) {
            echo $fileinfo['photo_path'];
        } else {
            echo 'Invalid file type.';
        }
    }

    public function fileupload()
    {
        if (!empty($_FILES)) {
            $targetFolder = 'room/' . $_POST['room'];
            $fileinfo = $this->uploadimg($targetFolder); //执行上传函数 不开启缩略图
            if ($fileinfo['photo_path']) {
                echo $fileinfo['photo_path'];
            } else {
                echo 'Invalid file type.';
            }
        }
    }

    //图片集 异步取得数据
    public function getphotolist() {
        $p['id'] = array('in', $_GET['pids']);
        $plist = M('photo')->where($p)->select();
        $rid = $plist[0]['rid'];
        if ($plist) {
            $out = '<ul>';
            foreach ($plist as $k => $v) {
                $out.='<li id="d' . $v['id'] . '"><img src="' . __ROOT__ . $v['src'] . '" width="140px"><br/> </li>';
            }
            echo $out . '</ul>';
        } else {
            echo 0;
        }
    }

    //图片编辑修改
    public function editphoto() {
        $u['id'] = $_GET['pid'];
        $t['title'] = $_GET['title'];
        echo M('photo')->where($u)->save($t);
    }

    public function delphoto() {
        $u['id'] = $_GET['pid'];
        $file = M('photo')->where($u)->field('src')->find();
        unlink('.' . $file['src']);
        echo M('photo')->where($u)->delete();
    }

    //设置缩略图
    public function setThumb() {
        $wh['rid'] = $_GET['rid'];
        M('photo')->where($wh)->setField('isthumb', 0);
        $whe['id'] = $_GET['pid'];
        $res = M('photo')->where($whe)->setField('isthumb', 1);
        if ($res) {
            die(json_encode(array('status' => 1, 'msg' => '设置成功')));
        } else {
            die(json_encode(array('status' => 0, 'msg' => '操作失败')));
        }
    }

    /**
     * [uploadimg 文件上传函数单独上传]
     * @param  [type]  $dirname  [文件保存在upload文件夹下的文件名]
     * @param  boolean $thumb_on [是否开启缩略图处理功能]
     * @param  string  $width    [缩略图宽度]
     * @param  string  $height   [缩略图高度]
     * @param  string  $fix      [缩略图前缀]
     * @return [type]            [返回上传后的信息]
     */
    public function uploadimg($dirname, $thumb_on = 0, $width = '200,300,400', $height = '200,300,400', $fix = 'Max_,Medium_,Mini_') {
        import("ORG.Net.UploadFile");
        $upload = new UploadFile(); // 实例化上传类
        $upload->saveRule = C('upload_saveRule'); //上传文件名称保存的方式
        $upload->maxSize = C('upload_filesize'); // 设置附件上传大小 
        $upload->allowExts = C('upload_type'); // 设置附件上传类型
        //判断指定上传的文件夹是否存在

        if (!is_dir(C('upload_dir'))) {
            mkdir(C('upload_dir'), 0777);
        }
        $upload->savePath = C('upload_dir') . '/' . $dirname . '/'; // 设置附件上传目录
        $upload->uploadReplace = 0; //上传同名文件不进行覆盖

        $upload->thumb = $thumb_on; //是否开启缩略图处理功能
        $upload->thumbMaxWidth = $width; //指定缩略图宽度
        $upload->thumbMaxHeight = $height; //指定缩略图的高度
        $upload->thumbPrefix = $fix; //默认的缩略图的前缀名称
        $upload->thumbPath = $upload->savePath . date("Y-m-d") . "/"; //缩略图的保存路径
        $upload->autoSub = true; //使用子目录保存上传文件 
        $upload->subType = "date"; //指定子目录的上传文件的类型为日期
        $upload->dateFormat = "Y-m-d"; //日期的格式是年-月-日
        $upload->thumbRemoveOrigin = 0; //默认不删除原图

        if (!$upload->upload()) {// 上传错误提示错误信息
            die(json_encode(array('status' => 0, 'msg' => $upload->getErrorMsg())));
        } else {// 上传成功 获取上传文件信息
            $picInfo = $upload->getUploadFileInfo();
            $file = array();
            $file['photo_path'] = '/' . C('upload_dir') . '/' . $dirname . '/' . $picInfo[0]['savename'];
            if ($thumb_on) {//如果开启缩略图功能
                $picname = explode("/", $picInfo[0]['savename']);
                $fixarr = explode(",", $fix);
                $file['thumb_path'] = array();
                for ($i = 0; $i < count($fixarr); $i++) {
                    $file['thumb_path'][$fixarr[$i]] = '/' . C('upload_dir') . '/' . $dirname . '/' . $picname['0'] . '/' . $fixarr[$i] . $picname['1'];
                }//将缩略图信息放入数组中
            }
            return $file;
        }
    }

}

?>