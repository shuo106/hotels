<?php
class UploadAction extends CommonAction{
	public function index() {
		if ($_FILES['file']['name'] != '') {
			$dirname = $this->_get('dir')?$this->_get('dir'):'data';
			$fileinfo = $this -> uploadimg($dirname, true, '600', '450', 'thumb');
			//执行上传函数 不开启缩略图
			if ($fileinfo['photo_path'] && $fileinfo['thumb_path']['thumb']) {
				$this->json(200, '上传成功', array('thumb' => $fileinfo['thumb_path'], 'yuantu' => $fileinfo['photo_path']));
			}else{
				$this->json(300, '上传失败');
			}
		}
	}
	public function room(){
		if ($_FILES['file']['name'] != '') {
			$dirname = $this->_get('dir')?$this->_get('dir'):'data';
			$fileinfo = $this -> uploadimg($dirname, true, '100', '66', 'thumb');
			//执行上传函数 不开启缩略图
			if ($fileinfo['photo_path'] && $fileinfo['thumb_path']['thumb']) {
				$data['rid']=$this->_get('rid');
				$data['src']=$fileinfo['photo_path'];
				$rs=M('photo')->add($data);
				if($rs){
					$this->json(200, '上传成功', array('thumb' => $fileinfo['thumb_path'], 'yuantu' => $fileinfo['photo_path'],'pid'=>$rs,'rid'=>$this->_get('rid')));					
				}else{
					$this->json(300, '上传失败');
				}
			}else{
				$this->json(300, '上传失败');
			}
		}
	}
	public function uploadimg($dirname,$thumb_on=0,$width='200,300,400',$height='200,300,400',$fix='img_'){
                import("ORG.Net.UploadFile");
				$upload = new UploadFile();// 实例化上传类
				$upload->saveRule = C('upload_saveRule');//上传文件名称保存的方式
				$upload->maxSize  = C('upload_filesize');// 设置附件上传大小 
                $upload->allowExts= C('upload_type');// 设置附件上传类型
				//判断指定上传的文件夹是否存在
				
				if(!is_dir(C('upload_dir'))){
                     mkdir(C('upload_dir'));
				}
				$upload->savePath =  C('upload_dir').'/'.$dirname.'/';// 设置附件上传目录
				$upload->uploadReplace=0;//上传同名文件不进行覆盖
				
				$upload->thumb=$thumb_on;//是否开启缩略图处理功能
			    $upload->thumbMaxWidth=$width;//指定缩略图宽度
			    $upload->thumbMaxHeight=$height;//指定缩略图的高度
			    $upload->thumbPrefix=$fix;//默认的缩略图的前缀名称
			    $upload->thumbPath=$upload->savePath.date("Y-m-d")."/";//缩略图的保存路径
			    $upload->autoSub=true;//使用子目录保存上传文件 
			    $upload->subType="date";//指定子目录的上传文件的类型为日期
			    $upload->dateFormat="Y-m-d";//日期的格式是年-月-日
			    //$upload->thumbRemoveOrigin=true;//生成缩略图后不删除原图
				$upload->thumbRemoveOrigin=0;//默认不删除原图

				if(!$upload->upload()) {// 上传错误提示错误信息
					die(json_encode(array('status'=>0,'msg'=>$upload->getErrorMsg())));
				}else{// 上传成功 获取上传文件信息
					$picInfo=$upload->getUploadFileInfo();
			            if($thumb_on){
                          $picname=explode("/",$picInfo[0]['savename']);
			           	  $file=array(
					             'photo_path'=>'/'.$upload->savePath.$picInfo[0]['savename'],
					             'status'=>'1',
					             'thumb_path'=>'/'.$upload->savePath.$picname['0'].'/'.$fix.$picname['1']
			               );
			           }else{
			           	  $file=array(
					             'photo_path'=>'/'.$upload->savePath.$picInfo[0]['savename']
					             );
			           }
			           return $file;
                }
		}	
}
