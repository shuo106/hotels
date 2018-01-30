<?php
$config2=array(
		//模版
		'URL_CASE_INSENSITIVE' =>true,
		'TMPL_ENGINE_TYPE'=>'Think',
		'SESSION_AUTO_START' =>true,
		'SHOW_PAGE_TRACE'   =>false,
		
);
$config1=include '../config.inc.php';
$config_lang=include '../language/lang.php';
return array_merge($config1,$config2,$config_lang);

?>