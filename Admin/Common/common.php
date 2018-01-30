<?php
/**
     * [mutisort 对二维数组按照第二维的某个键值重新排序]
     * @param array array <p>
     * 待排序的二维数组
     * </p>
     * @param kname string <p>
     * 二维数组的的键名，根据此数组排序
     * </p>
     * @param mode bool <p>
     * 假：倒序；真：正序
     * </p>
     * @return [array] [the sorted array]
     */
    function mutisort($array, $kname, $mode=false) {
        $temp1 = $temp2 = array();
        foreach ($array as $key => $val) {
            $temp1[$key] = $val[$kname];
        }
        if($mode==false){
            arsort($temp1);
        }elseif($mode==true){
            asort($temp1);
        }
        foreach ($temp1 as $k => $v) {
            $temp2[$k] = $array[$k];
        }
        return $temp2;
    }
     /**
      * [p 数组打印排列方法]
      * @param  [type] $arr [description]
      * @return [type]      [description]
      */
     function p($arr){
           echo '<pre>';
           print_r($arr);
           echo '</pre>';
     } 
     /**
      * [node_merge 重组节点信息为多维数组]
      * @return [type] $node [要处理的节点数组]
      * @return [type] $pid [父级节点id]
      * @return [type] [description]
      */
    function node_merge($node,$access=null,$pid=0){
           // p($node);
            $arr=array();
            foreach ($node as $v) {
            	if(is_array($access)&&in_array($v['id'],$access)){
                       $v['power']=1;
            	}
            	if($pid==$v['pid']){
            		$v['child']=node_merge($node,$access,$v['id']);
            		$arr[]=$v;
            	}
            }
            return $arr;
     }
     /**
      * [node_getid 递归得到所有该节点下面的id]
      * @param  [type]  $node [递归的数组]
      * @param  integer $pid  [父级id]
      * @return [type]        [description]
      */
    function node_getid($node,$pid=0){ 
            static $arr=array();//定义为静态的变量         
            foreach ($node as $k=>$v) {
              if($pid==$v['pid']){
                $arr[]=$v['id'];
                unset($node[$k]);//删除没有用的循环
                node_getid($node,$v['id']);
              }
            }
           return $arr;
    }

    /**
     * [formatlev 子父级分类建立关系]
     * @return [type] [description]
     */
    function formatlev($cate,$pid=0,$html='--',$lev=0){
            $arr=array();
            foreach($cate as $v){
                if($v['pid']==$pid){
                    $v['lev']=$lev+1;
                    $v['html']=str_repeat($html,$lev); 
                    $arr[]=$v;
                    $arr=array_merge($arr,formatlev($cate,$v['id'],$html,$lev+1));
                }
            }
            return $arr;
    }


    function lev($cate,$pid=0,$html='--',$lev=0){
            $arr=array();
            foreach($cate as $v){
                if($v['pid']==$pid){
                    $v['lev']=$lev+1;
                    $v['html']=str_repeat($html,$lev); 
                    $arr[]=$v;
                    $arr=array_merge($arr,lev($cate,$v['id'],$html,$lev+1));
                }
            }
            return $arr;
    }        

    /**
 * [ch_num 将阿拉伯数字转换成中文数字]
 * @param  [type]  $num  [description]
 * @param  boolean $mode [description]
 * @return [type]        [description]
 */
function ch_to_num($num,$mode=true) {
            $char = array("","一","二","三","四","五","六","七","八","九");
            $dw = array("","十","百","仟","","萬","億","兆");
            $dec = "點";
            $retval = "";
            if($mode)
            preg_match_all("/^0*(\d*)\.?(\d*)/",$num, $ar);
            else
            preg_match_all("/(\d*)\.?(\d*)/",$num, $ar);

               if($ar[2][0] != "")
                    $retval = $dec . ch_to_num($ar[2][0],false); //如果有小数，先递归处理小数
                    if($ar[1][0] != "") {
                        $str = strrev($ar[1][0]);
                        for($i=0;$i<strlen($str);$i++) {
                            $out[$i] = $char[$str[$i]];
                            if($mode) {
                              $out[$i] .= $str[$i] != "0"? $dw[$i%4] : "";
                              if($i!=0){
                               if(($str[$i]+$str[$i-1]) == 0)$out[$i] = "";
                               if($i%4 == 0)$out[$i] .= $dw[4+floor($i/4)];  
                              }
                            }
                         }
                      $retval = join("",array_reverse($out)) . $retval;
                    }
        return $retval;
}
 
?>