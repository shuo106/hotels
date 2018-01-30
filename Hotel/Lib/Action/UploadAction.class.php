<?php
class UploadAction extends Action{
	public function hotel(){
		$hid=$_REQUEST['hotelid'];	
		if(!empty($_FILES)) {
			$fileinfo=$this->uploadimg($hid);//执行上传函数 不开启缩略图
         }
		if($fileinfo['photo_path']){
			$data['src']   	=$fileinfo['photo_path'];	
			$data['hotelid']  =$hid;	
			echo M('photo')->add($data);
		}else {
			echo 'Invalid file type.';
		}	
	}
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
                $upload->savePath = '/Hotel/'.C('upload_dir').'/'.$dirname.'/';// 设置附件上传目录
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
                    $file['photo_path']='/Hotel/'.C('upload_dir').'/'.$dirname.'/'.$picInfo[0]['savename'];
                        if($thumb_on){//如果开启缩略图功能
                            $picname=explode("/",$picInfo[0]['savename']);
                            $fixarr=explode(",", $fix);
                            $file['thumb_path']=array();
                            for($i=0;$i<count($fixarr);$i++){
                                  $file['thumb_path'][$fixarr[$i]]='/Hotel/'.C('upload_dir').'/'.$dirname.'/'.$picname['0'].'/'.$fixarr[$i].$picname['1']; 
                            }//将缩略图信息放入数组中
                        }
                    return $file;
                }
    }
}