<?php
/**
 * 后台用户  与角色的  交互模型
 */
class  AdminRelationModel extends RelationModel{
              protected  $tableName='admin';
              protected  $_link = array(
				            'role'=>array(
				            'mapping_type'            =>MANY_TO_MANY,//多对多关联
				            'foreign_key'             =>'user_id',//主表主键在中间表中的名称
				            'relation_foreign_key'    =>'role_id',//附表主键在中间表中的名称
				            'relation_table'          =>'role_user',//中间表名称
				            'mapping_fields'          =>'id,name,status',//附表需要的字段
				            ),
                       );
       }
?>