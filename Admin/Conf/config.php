<?php
$config2=array(
        'RBAC_SUPERADMIN' =>'admin',       //指定超级管理员识别名称
        'ADMIN_AUTH_KEY'  =>'superadmin',  //超级管理员识别键
        'USER_AUTH_ON'    =>true,          //是否开启权限验证功能 
        'USER_AUTH_TYPE'  =>1,             //验证类型(1,代表登录验证 2,代表时时验证)
        'USER_AUTH_KEY'   =>'admin_id',         // 用户认证识别号
        'USER_AUTH_NAME'   =>'admin_name',          //用户登录后网站保存的用户的名称
        'NOT_AUTH_MODULE' =>'Index',           //无需认证的控制器
        'NOT_AUTH_ACTION' =>'',            //无需认证的方法
        'RBAC_ROLE_TABLE' =>'pchotel_role',            //角色表名称
        'RBAC_USER_TABLE' =>'pchotel_role_user',       //角色与用户的中间表名称 
        'RBAC_ACCESS_TABLE' =>'pchotel_access',        //权限表   
        'RBAC_NODE_TABLE' =>'pchotel_node',            //节点表名称
        'USER_AUTH_MODEL'=>'pchotel_admin',
        'FORM_VALIDATE' =>array(
                'account'=>'/^([a-zA-Z0-9_]{3,15}|[\u4e00-\u9fa5]{1,5})$/',
                'truename'=>'/^[\u4e00-\u9fa5]{2,10}$/',
                'telephone'=>'/^[0-9]{11}$/',
                ),        
);
$config1=include './config.inc.php';
$config_lang=include './language/lang.php';
$config_sys =include './language/system.php';
return array_merge($config1,$config2,$config_lang,$config_sys);

?>