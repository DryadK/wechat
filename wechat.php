<?php

define("TOKEN", "weixin");

$wechatObj = new wechatCallbackapiTest();
if (!isset($_GET['echostr'])) {
    $wechatObj->responseMsg();
}else{
    $wechatObj->valid();
}

class wechatCallbackapiTest
{
    //验证签名
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if($tmpStr == $signature){
            echo $echoStr;
            exit;
        }
    }

    //响应消息
    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            $this->logger("R ".$postStr);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
             
            //消息类型分离
            switch ($RX_TYPE)
            {
                case "text":
                    $result = $this->receiveText($postObj);
                    break;
                default:
                    $result = "unknown msg type: ".$RX_TYPE;
                    break;
            }
            $this->logger("T ".$result);
            echo $result;
        }else {
            echo "";
            exit;
        }
    }
   
    //接收文本消息
    private function receiveText($object)
    {
        $keyword = trim($object->Content);
        //多客服人工回复模式
        if (strstr($keyword, "111")){
            $result = $this->transmitService($object);
        }
        //自动回复模式
        else{
                $lyrics=array("怨只怨人在风中聚散都不由我",
							"我在长堤上等着你，天空正好飘着毛毛雨，我想回家拿把雨伞又怕你找不到我",
                            "多少年以后 往事随云走，那纷飞的冰雪容不下那温柔",
                            "你拥抱的并不总是也拥抱你",
                            "来来往往的人驶入天涯 情窦初开中就让她羽化",
                            "我不是一定要你回来，只是当又一个人看海，回头才发现你不在，留下我迂回的徘徊",
                            "有一天醒来脑袋一片空白，有一天睡去梦境却清楚明白",
                            "我们天空 何时才能成一片，我们天空 何时能相连",
                            "怀抱里的绿洲 有泪水灌溉的朦胧",
                            "我会唾弃自己的宽容 情愿放逐每条背叛的线索",
                            "动情是容易的 因为不会太久",
                            "无奈我们看懂彼此是彼此的过客 爱情是个轮廓不能私有",
                            "把最初的感动巨细无遗的保留心中 不容许让时间腐朽了初衷",
                            "深爱是残忍的 它不喜新厌旧",
                            "拥抱我在你幽幽梦中 我们之间除了思念还缺了些什么",
                            "千万记得天涯有人在等待，路程再多再遥远不要不回来",
                            "人生已经太匆匆 我好害怕总是泪眼朦胧",
                            "多少人曾爱慕你年轻时的容颜，可知谁愿承受岁月无情的变迁",
                            "茶没有喝光早变酸 从来未热恋已相恋，陪着你天天在兜圈 那缠绕怎么可算短",
                            "宁愿是条船 如果你是大海，至少让她降落在你怀中" );
                $content = $lyrics[(int)rand(0,count($lyrics)-1)] ;
            
            
            if(is_array($content)){
                if (isset($content[0]['PicUrl'])){
                    $result = $this->transmitNews($object, $content);
                }else if (isset($content['MusicUrl'])){
                    $result = $this->transmitMusic($object, $content);
                }
            }else{
                $result = $this->transmitText($object, $content);
            }
        }
		
        return $result;
    }
        
        
    //回复文本消息
    private function transmitText($object, $content)
    {
        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }

    

    //日志记录
    private function logger($log_content)
    {
        if(isset($_SERVER['HTTP_APPNAME'])){   //SAE
            sae_set_display_errors(false);
            sae_debug($log_content);
            sae_set_display_errors(true);
        }else if($_SERVER['REMOTE_ADDR'] != "127.0.0.1"){ //LOCAL
            $max_size = 10000;
            $log_filename = "log.xml";
            if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
            file_put_contents($log_filename, date('H:i:s')." ".$log_content."\r\n", FILE_APPEND);
        }
    }
}
?>