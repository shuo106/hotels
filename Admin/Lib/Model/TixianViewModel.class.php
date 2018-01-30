<?php
      class TixianViewModel EXTENDS ViewModel{
      	        public $viewFields =array(
                  'tixian'=>array('id','txhang','txname','txhangnum','txshenfen','txmobile','txdate','txjine','status','remainAmount','handleDate','blbeizhu','banliren','banliDate','beizhu'),
                  'member'=>array('id'=>'uid','username','truename','email','_on'=>'tixian.uid=member.id','_type'=>'LEFT'),
      	       	);
      }










?>