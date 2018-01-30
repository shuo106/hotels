<?php
/*
*微信 回复模块
*
*/
class ReplyAction extends Action
{
   	private $weObj;			//微信类
   	private $FromUserName; //发送者id
   	private $ToUserName;	//接收者id
 	public function _initialize(){
		import("ORG.Util.Wechat");// 导入微信类
		$wxconfig = M('wxconfig')->find();
		  $options = array(
			'token'=>$wxconfig['Token'], 			//填写你设定的key
			'appid'=>$wxconfig['Appid'], 				//填写高级调用功能的app id
			'appsecret'=>$wxconfig['AppSecret'], 		//填写高级调用功能的密钥
		);
		$this->weObj = new Wechat($options); 
		$this->FromUserName='';
		$this->ToUserName='';
	} 
	//微信回复接口
	public function index(){
		$this->weObj->valid();
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $this->fromUsername = $postObj->FromUserName;
            $this->toUsername = $postObj->ToUserName;
			//关键字回复 或事件推送
			$key = iconv('ASCII', 'UTF-8', $postObj->EventKey);
			$key = $key ? $key : trim($postObj->Content); 
			$MsgType = $postObj->MsgType;
			if($MsgType=='event'){	//消息类型 事件
 				$EventType = $postObj->Event;//事件类型
				if($EventType=='subscribe'){	//未关注时 关注事件 
					echo $this->subscribeReply();
				}elseif($EventType=='unsubscribe'){	//取消关注事件 
					echo $this->subscribeReply();
				}elseif($EventType=='SCAN'){	//已关注时 关注事件 
					echo $this->SCANReply($key);
				}elseif($EventType=='CLICK'){	//自定义菜单事件
					echo $this->textReply($key);
				}
			}else{
				echo $this->textReply($key);
			}
			/*   $MsgType = $postObj->MsgType;
			switch($MsgType)
			{
				case 'text':
					$this->keyword = trim($postObj->Content);
					$this->textReply();
					break;
				case 'news':
					$this->keyword = $postObj->EventKey;
					$this->newsReply();
					break;
				case 'event':
					$this->eventReply();
					break;
				case 'image':
					$this->imageReply();
					break;
				case 'voice':
					$this->voiceReply();
					break;
				case '语音':
					$this->voiceReply();
					break;
				case 'video':
					$this->videoReply();
					break;
				case 'music':
					$this->musicReply();
					break; 
			}*/
		}else{
			echo "";
            exit;
		}
	}
	//文字回复
	public function textReply($key){
         $textTpl = "<xml>
						<ToUserName>%s</ToUserName>
						<FromUserName>%s</FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType>text</MsgType>
						<Content>%s</Content>
						<FuncFlag>0</FuncFlag>
					</xml>";
		$data['key'] = $key;
		$reply=M('wxrule')->where($data)
				->join('pchotel_wxtreply ON pchotel_wxrule.id = pchotel_wxtreply.rid')
				->field('content')
				->find();
		if($reply['content']){
			return sprintf($textTpl, $this->fromUsername, $this->toUsername, time(), $reply['content']);
		}else{
			return $this->newsReply($key);
		}
	}
	//图文回复
	public function newsReply($key){
		$textTpl = "<xml>
					<ToUserName>%s</ToUserName>
					<FromUserName>%s</FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType>news</MsgType>
					<ArticleCount>"; 
			$data['key']=$key;
			$reply=M('wxrule')->where($data)
					->join('pchotel_wxitreply ON pchotel_wxrule.id = pchotel_wxitreply.rid')
					->field('content,title,description,thumb,url')
					->order('pchotel_wxitreply.id asc')
					->select();
			if($reply){
				$nums=count($reply);
				$textTpl.=$nums.'</ArticleCount>
						<Articles>';
				foreach($reply as $v){
						$textTpl.='<item>';
						$textTpl.='<Title>'.$v['title'].'</Title>';
						$textTpl.='<Description>'.$v['description'].'</Description>';
						$textTpl.='<PicUrl>http://'.$_SERVER ['HTTP_HOST'].$v['thumb'].'</PicUrl>';
						$textTpl.='<Url>'.$v['url'].'/wechatid/'.$this->fromUsername.'</Url>';
						$textTpl.='</item>';
				}
				$textTpl.='</Articles></xml>'; 
				$resultStr = sprintf($textTpl, $this->fromUsername, $this->toUsername, time());
				return $resultStr;
		   }else{
				return '';
		   }
	}
	//欢迎关注事件  未关注
	public function subscribeReply(){
		$data['welcome']=1;
		$reply=M('wxrule')->where($data)->find();
		if($reply['key']){
			return $this->textReply($reply['key']);
		}
		return '';
	}	
	//欢迎关注事件  已关注
	public function SCANReply(){
		$data['welcome']=1;
		$reply=M('wxrule')->where($data)->find();
		if($reply['key']){
			return $this->textReply($reply['key']);
		}
		return '';
	}	
	//默认事件
	public function defaultReply(){
		$data['default']=1;
		$reply=M('wxrule')->where($data)->find();
		if($reply['key']){
			return $this->textReply($reply['key']);
		}
		return '';
	}
	
	/*//事件回复
	public function eventReply(){
		
	}	
	 //图片回复
	public function imageReply(){
		
	}
	//语音回复
	public function voiceReply(){
		
	}
	//视频回复
	public function videoReply(){
		
	}
	//音乐回复
	public function musicReply(){
		
	} */
	public function test(){
		
	}
}
?>