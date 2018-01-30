<?php
class PhotoAction extends BaseAction{
	public function index(){
		$this->_menu_acvion='hotel-info';
		$map['hotelid'] = intval(session('hotel_id'));
		import('ORG.Util.Page');
		$photo = M('photo');
		$count = $photo->where($map)->count();
		$page = new Page($count,10);
		$show = $page->show();
		$content = $photo->where($map)->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('photo',$content);
		$this->assign('page',$show);
		$this->display();
	}
	public function imgupload(){
		$row = M('member_hotel')->where(array('username'=>$_SESSION['hotel_name']))->field('hotelname,hotelid')->find();		
		$this->assign('hotelname',$row['hotelname']);
		$this->assign('hotelid',$row['hotelid']);
		$t=time();
		$this->assign('timestamp',$t);
		$this->assign('mdtimestamp',md5('unique_salt'.$t));
		$this->display();
	}
	//设置缩略图
	public function setThumb(){
		M('photo')->where(array('hotelid'=>session('hotel_id')))->setField('isdefault',0);
		$whe['photoid']= $_GET['pid'];
		$res=M('photo')->where($whe)->setField('isdefault',1);
		if($res){
			die(json_encode(array('status'=>1,'msg'=>'设置成功')));
		}else{
			die(json_encode(array('status'=>0,'msg'=>'操作失败')));
		}
		
	}	
	public function doUpload(){
		 if(isset($_REQUEST['PHPSESSID'])) {
			   session_id($_REQUEST['PHPSESSID']);    // 调用 session_id function 放入 session id
			   session_start();
		   }
		$hid=session('hotel_id');
		$verifyToken = md5('unique_salt' . $_POST['timestamp']);		
/* 		if(!empty($_FILES) && $_POST['token'] == $verifyToken ) {
			$fileinfo=$this->uploadimgs('thumb1');//执行上传函数 不开启缩略图
         } */
		if($_FILES['thumb1']['name'] != '') {
			$fileinfo=$this->uploadimgs('thumb1');//执行上传函数 不开启缩略图
         }
		if($fileinfo['photo_path']){
			$data['src']   	= $fileinfo['photo_path'];	
			$data['hotelid']  = intval($hid);
			$data['hotelname'] = session('hotel_hotelname');
			$row =  M('photo')->add($data);
			if ($row) {
				die(json_encode(array('status'=>1,'msg' => '操作成功')));
			} else {
				die(json_encode(array('status'=>0,'msg'=>'操作失败')));
			}
		}else {
			die(json_encode(array('status'=>0,'msg'=>'操作失败')));
		}
	}	
	public function edit(){
		$map['photoid']=$_GET['id'];
		$content=M('photo')->where($map)->find();
		$this->assign('photo',$content);
		$this->display();
	}
	public function imgupdate(){
		$photoid=$up['photoid']=$this->_post('photoid');
		$isdefault= $this->_post('isdefault');
		if($_FILES['src']['name'] != ''){
			$editdata['src'] = $this->uploadimg($photoid);
		}
		$editdata['title']= $this->_post('title');
		$editdata['isdefault']= $isdefault;
		if($isdefault ==1){
			//如果选择默认 先去除默认
			$map=array();
			$map['hotelid']=session('hotel_id');
			$map['isdefault']=1;
			M('photo')->where($map)->setField('isdefault',0);
		}
		$rs=M('photo')->where($up)->save($editdata);
		$this->redirect('index');
	}
	public function delete(){
		$id=intval($_GET['pid']);
		$photo=M('photo');
		$rs=$photo->where("photoid=$id")->delete();
		if($rs){$this->redirect('index');}
	
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
    public function uploadimg($dirname,$thumb_on=0,$width='200,300,400',$height='200,300,400',$fix='Max_,Medium_,Mini_'){
                import("ORG.Net.UploadFile");
                $upload = new UploadFile();// 实例化上传类
                $upload->saveRule = C('upload_saveRule');//上传文件名称保存的方式
                $upload->maxSize  = C('upload_filesize');// 设置附件上传大小 
                $upload->allowExts= C('upload_type');// 设置附件上传类型
                //判断指定上传的文件夹是否存在
                
                if(!is_dir(C('upload_dir'))){
                     mkdir(C('upload_dir'),0777);
                }
                $upload->savePath = '/Hotel/uploads/'.$dirname.'/';// 设置附件上传目录
                $upload->uploadReplace=0;//上传同名文件不进行覆盖
                
                $upload->thumb=$thumb_on;//是否开启缩略图处理功能
                $upload->thumbMaxWidth=$width;//指定缩略图宽度
                $upload->thumbMaxHeight=$height;//指定缩略图的高度
                $upload->thumbPrefix=$fix;//默认的缩略图的前缀名称
                $upload->thumbPath=$upload->savePath.date("Y-m-d")."/";//缩略图的保存路径
                $upload->autoSub=true;//使用子目录保存上传文件 
                $upload->subType="date";//指定子目录的上传文件的类型为日期
                $upload->dateFormat="Y-m-d";//日期的格式是年-月-日
                $upload->thumbRemoveOrigin=0;//默认不删除原图

                if(!$upload->upload()) {// 上传错误提示错误信息
                    die(json_encode(array('status'=>0,'msg'=>$upload->getErrorMsg())));
                }else{// 上传成功 获取上传文件信息
                    $picInfo=$upload->getUploadFileInfo();
                    $file=array();
                    $file['photo_path']='/Hotel/uploads/'.$dirname.'/'.$picInfo[0]['savename'];
                        if($thumb_on){//如果开启缩略图功能
                            $picname=explode("/",$picInfo[0]['savename']);
                            $fixarr=explode(",", $fix);
                            $file['thumb_path']=array();
                            for($i=0;$i<count($fixarr);$i++){
                                  $file['thumb_path'][$fixarr[$i]]='/Hotel/uploads/'.$dirname.'/'.$picname['0'].'/'.$fixarr[$i].$picname['1']; 
                            }//将缩略图信息放入数组中
                        }
                    return $file;
                }
    }
	
	public function uploadimgs($fujian)
	{
		$extsion = array('jpg','gif','jpeg','png','bmp');
		$fileSize = 1000*1024;
		$fileName = $fujian;
		if(isset($_FILES[$fileName])){
			if($_FILES[$fileName]['error'] != 0){
				switch($_FILES[$fileName]['error']){
					case 1:
						return array('status'=>0,'url'=>'','msg'=>'超过了php.ini上传的大小');
					break;
					case 2:
						return array('status'=>0,'url'=>'','msg'=>'上传文件大小');
					break;
					case 3:
						return array('status'=>0,'url'=>'','msg'=>'文件只有部分被上传');
					break;
					case 4:
						return array('status'=>0,'url'=>'','msg'=>'图片不能为空');
					break;
					case 6:
						return array('status'=>0,'url'=>'','msg'=>'找不到临时文件夹');
					break;
					case 7:
						return array('status'=>0,'url'=>'','msg'=>'文件写入失败');
					break;
				}
			}
			$ext = explode('.',$_FILES[$fileName]['name']);
			$extInfo = $ext[count($ext)-1];
			if(in_array($extInfo,$extsion)==0){
				return array('status'=>0,'url'=>'','msg'=>'上传错误，本站允许上传'.implode('|',$extsion));
			}
			if($_FILES[$fileName]['size'] > $fileSize){
				return array('status'=>0,'url'=>'','msg'=>'上传失败，本站允许上传大小'.($fileSize/1024).'M');
			}
			$newName = $this->filenewName($extInfo);
			$res = move_uploaded_file($_FILES[$fileName]['tmp_name'],dirname(USER_ROOT).$newName);
			if($res){
				return array('status'=>1,'photo_path'=>$newName,'msg'=>'上传成功');
			}else{
				return array('status'=>0,'photo_path'=>'','msg'=>'移动临时文件出错');
			}
		}
	}

	public function filenewName($extInfo){
		$str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$text = date('His').'-';
		for($i=0;$i<4;$i++){
			$text .= substr($str,mt_rand(0,strlen($str)-1),1);	
		}
		$dir = '/Hotel/upload/H-'.session('hotel_id');
		if (!is_dir(dirname(USER_ROOT).$dir)) {
			mkdir(dirname(USER_ROOT).$dir, 0777);
		}
		$new = $dir."/$text".'.'.$extInfo; //9-27号修改
		//$new = "./uploads/Hotel/"."$text".'.'."$extInfo";
		if(file_exists($new)){
			$this->filenewName($new);	
		}else{
			return $new;
		}
	}

	
}