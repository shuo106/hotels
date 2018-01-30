<?php
/*
Uploadify
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
Released under the MIT License <http://www.opensource.org/licenses/mit-license.php> 
*/
// Define a destination
	session_start();
	$config= include '../../config.inc.php';
	$con = mysql_connect($config['DB_HOST'],$config['DB_USER'],$config['DB_PWD']);
	if (!$con){
	  die('Could not connect: ' . mysql_error());
	}else{
		
		mysql_select_db($config['DB_NAME'],$con);
		$verifyToken = md5('unique_salt' . $_POST['timestamp']);
		if($_POST['type']!='Hotel'){
			$targetFolder = $_POST['app'].'/uploads/'.$_POST['type'].'/UID-'.$_POST['admin_id'].'/'.date('Y-m-d').'/'; 
			$targetFolder2=$_SERVER['DOCUMENT_ROOT'] .$targetFolder;
			$isFile=explode('/',$targetFolder2);
			$f='';
			foreach($isFile as $v){
				$f.=$v.'/';
				if(!is_dir($f)){
					 mkdir($f,0777);
				}
			}
			$targetFolder = $targetFolder;
		}else{
			$targetFolder = $_POST['app'].'/Hotel/upload/H-'.$_POST['hotelid'].'/'; 
		}
//
		if (!empty($_FILES) && $_POST['token'] == $verifyToken ) {
			
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
			if(!is_dir($targetPath)){
				 mkdir($targetPath,0777);
			}
			$fileParts = pathinfo($_FILES['Filedata']['name']);
			$newname= substr(md5(time().getRandom(10)),15);
			$targetFile = rtrim($targetPath,'/') . '/' .$newname.'.'.$fileParts['extension'];//. $_FILES['Filedata']['name'];
			
			$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
		
			$targetFolder.=$newname.'.'.$fileParts['extension'];
			
			if (in_array($fileParts['extension'],$fileTypes)) {
				move_uploaded_file($tempFile,$targetFile);
				mysql_query("SET NAMES 'UTF8'"); 
				$sql="INSERT INTO lvyou_photo (src) VALUES ('$targetFolder')";
				mysql_query($sql);
				echo mysql_insert_id();
			} else {
				echo 'Invalid file type.';
			}
			mysql_close($con);
		}
	}
	function getRandom($param){
    $str="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $key = "";
    for($i=0;$i<$param;$i++)
     {
         $key .= $str{mt_rand(0,32)};    
     }
     return $key;
 }
?>