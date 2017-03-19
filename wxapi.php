<?php
require_once (dirname(__FILE__) . "/include/common.inc.php");

//定义常量TOKEN，这个值可以随便设置，但是必须保证微信公众平台后台的值和这里设置的一样
define("TOKEN", "jipinxiaoshuoapi");
$wechatObj = new wechatCallbackapiTest();
$wechatObj->responseMsg();
//$wechatObj->valid();

class wechatCallbackapiTest
{
    //微信公众平台开发者认证，已经认证过了就不用了
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }
	//自动回复功能
	//global $cfg_wapurl,$cfg_webname;
    public function responseMsg()
    {
        //获取用户发过来的xml内容
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

          //分析发送过来的内容类型，包括文本、点击事件等
        if (!empty($postStr)){
                
                  $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $RX_TYPE = trim($postObj->MsgType);

                switch($RX_TYPE)
                {
                    case "text":
                        $resultStr = $this->handleText($postObj);
                        break;
                    case "event":
                        $resultStr = $this->handleEvent($postObj);
                        break;
                    default:
                        $resultStr = "Unknow msg type: ".$RX_TYPE;
                        break;
                }
                echo $resultStr;
        }else {
            echo "请问有什么可以帮助您的吗？";
            exit;
        }
    }
	//自动回复
    public function handleText($postObj)
    {
        global $cfg_wapurl,$cfg_webname;
		$fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $keyword = trim($postObj->Content);
        $time = time();
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>0</FuncFlag>
                    </xml>";             
        if(!empty( $keyword ))
        {
            $msgType = "text";
			$replacearray=array(" ","!","?","select","update","delete","(",")",",",".",":");
			$keyword = str_replace($replacearray,'',html2text($keyword));
			
			if($keyword!="")
			{
				$contentStr=getreadbook($fromUsername,$keyword);
				//$contentStr="测试中……";
			}
			else
				$contentStr = "/::)感谢您关注【".mb_convert_encoding($cfg_webname, "UTF-8", "GBK")."】"."\n"."/::P"."\n"."在线阅读方式如下："."\n\n"."【在线阅读】\n请输入：阅读+书名（作者也可以，少字也没关系/::)）\n例如：阅读我欲封天\n或者：阅读耳根\n\n【继续上次阅读】\n请输入：继续"."\n\n请输入：";
			$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            echo $resultStr;
        }else{
            echo "请输入您要咨询的信息...";
        }
    }
	//关注事件，在关注或者打开的时候自动回复内容
    public function handleEvent($object)
    {
        global $cfg_wapurl,$cfg_webname;
		$contentStr = "";
        switch ($object->Event)
        {
            case "subscribe":
                $contentStr = "/::)感谢您关注【".mb_convert_encoding($cfg_webname, "UTF-8", "GBK")."】"."\n"."/::P"."\n"."在线阅读方式如下："."\n\n"."【在线阅读】\n请输入：阅读+书名（作者也可以，少字也没关系/::)）\n例如：阅读我欲封天\n或者：阅读耳根\n\n【继续上次阅读】\n请输入：继续"."\n\n请输入：";
                break;
			case "CLICK":
				switch ($object->EventKey)
				{
					case "sgcx":
						$contentStr = "感谢您关注【".mb_convert_encoding($cfg_webname, "UTF-8", "GBK")."】"."\n"."该功能正在努力开发中..."."\n"."更多功能，敬请期待...";
						break 2;
					case "hycx":
						$contentStr = "感谢您关注【".mb_convert_encoding($cfg_webname, "UTF-8", "GBK")."】"."\n"."该功能正在努力开发中..."."\n"."更多功能，敬请期待...";
						break 2;
					default :
						$contentStr = "感谢您关注【".mb_convert_encoding($cfg_webname, "UTF-8", "GBK")."】"."\n"."该功能正在努力开发中..."."\n"."更多功能，敬请期待...";
						break 2;
				}
            default :
                $contentStr = "Unknow Event: ".$object->Event;
                break;
        }
        $resultStr = $this->responseText($object, $contentStr);
        return $resultStr;
    }
    
    public function responseText($object, $content, $flag=0)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>%d</FuncFlag>
                    </xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $flag);
        return $resultStr;
    }

	//核对是不是从腾讯的微信服务器发过来的请求
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];    
                
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}
function getreadbook($fromUsername,$keyword)
{
	$dsql = new DedeSql(false);
	global $cfg_wapurl,$cfg_webname;
	
	$n=0;
	$keytype=substr($keyword,0,6);
	$keyword1=substr($keyword,6);
	if($keytype=="继续")
	{
		$content="为找到了下面的这些记录/::)：\n";
		$rowwxsql="Select * From `weixin_reader` where uid='$fromUsername'";
		$dsql->SetQuery($rowwxsql);
		$dsql->Execute();
		$rowwx = $dsql->GetArray();
		if($rowwx['lasttime']>0)
		{
			$readarray=explode("|",substr($rowwx['data'],1));
			for($n=0;$n<count($readarray);$n++)
			{
				$dataarray=explode(",",$readarray[$n]);
				$aid=$dataarray[1];
				$query = "Select tp.typename,arc.title From `#@__archives` arc left join `#@__arctype` tp on tp.id=arc.typeid where arc.id='$aid'";
				$dsql->SetQuery($query);
				$dsql->Execute();
				if($row = $dsql->GetArray())
				{
					$content .= '<a href="'.$cfg_wapurl.'/wap.php?action=article&id='.$aid.'&uid='.$fromUsername.'">《'.mb_convert_encoding($row['typename'], "UTF-8", "GBK").'》 - '.mb_convert_encoding($row['title'], "UTF-8", "GBK").'【继续阅读】</a>'."\n\n";
				}
			}
		}
		if($n==0)
		{
			$content="呃.../::-|/::-|我们\n没找到您的阅读记录/::(\n（只有在微信上阅读过小说的章节才会有记录）\n\n您先随便选一本吧/::(      \n我们再找找/:8*\n"."【在线阅读】\n请输入：阅读+书名（作者也可以，少字也没关系/::)）\n例如：阅读我欲封天\n或者：阅读耳根\n\n请输入：";
		}
	}
	elseif($keyword1!="" && $keytype=="阅读")
	{
		$content="为您找到了下面的这些小说：";
		$keyword = mb_convert_encoding($keyword1, "GBK", "UTF-8");
		$qsql="Select id,typename,zuozhe From `#@__arctype` where ishidden=0 and reid not in(0,45) and (typename like '%$keyword%' or zuozhe like '%$keyword%') order by bookclick desc";
		$dsql->SetQuery($qsql);
		$dsql->Execute();
		while($row = $dsql->GetArray())
		{
			$content.="\n【".mb_convert_encoding($row['typename'], "UTF-8", "GBK")."】作者：".mb_convert_encoding($row['zuozhe'], "UTF-8", "GBK").'   <<a href="'.$cfg_wapurl.'/wap.php?action=list&id='.$row['id'].'&uid='.$fromUsername.'">就看这本</a>>'."\n";
			$n++;
		}
		$content.="\n           【<a href='".$cfg_wapurl."/wap.php?action=search&wd=".$keyword1."&uid=".$fromUsername."'>查看更多…</a>】";
		if($n==0)
		{
			$dsql->Close();
			//$reidsql="SELECT * FROM `#@__arctype` WHERE reid=0 and content like '%$keyword%'";
			$reidrow = $dsql->GetOne("SELECT * FROM `#@__arctype` WHERE reid=0 and content like '%$keyword%'");
			if(is_array($reidrow))
			{
				$retypeid=$reidrow['id'];
				$retypename=str_replace('小说','',mb_convert_encoding($reidrow['typename'], "UTF-8", "GBK"));
				$content="为您找到了下面的这些很不错的".$retypename."小说/::)：";
				$resql="Select id,typename,zuozhe From `#@__arctype` where ishidden=0 and reid='$retypeid' order by bookclickm desc limit 8";
				$dsql->SetQuery($resql);
				$dsql->Execute();
				while($row = $dsql->GetArray())
				{
					$content.="\n【".mb_convert_encoding($row['typename'], "UTF-8", "GBK")."】作者：".mb_convert_encoding($row['zuozhe'], "UTF-8", "GBK").'   <<a href="'.$cfg_wapurl.'/wap.php?action=list&id='.$row['id'].'&uid='.$fromUsername.'">就看这本</a>>'."\n";
					$n++;
				}
				$content.="\n           【<a href='".$cfg_wapurl."/wap.php?action=list&type=top&id=".$retypeid."&uid=".$fromUsername."'>查看更多…</a>】";
			}
			if($n==0)
			{
				$content="呃.../::-|/::-|很抱歉！/::-|我们\n没找到您要看的小说/::(\n\n您再换一本吧/:8*\n"."【在线阅读】\n请输入：阅读+书名（作者也可以，少字也没关系/::)）\n例如：阅读我欲封天\n或者：阅读耳根\n\n【继续上次阅读】\n请输入：继续"."\n\n请输入：";
			}
		}
	}
	else
	{
		$kws="ok".$keyword;
		$keyarray=array("架空小说","女尊天下","历史小说","穿越小说","历史小说","军事小说","重生小说","豪门小说","乡土小说","爱情小说","都市小说","言情小说","古典小说","古代小说","校园小说","青春小说","h小说","总裁小说","浪荡小说","女生小说","纯爱小说","修真小说","武侠小说","仙侠小说","贵族小说","亡灵小说","魔法小说","魔幻小说","玄幻小说","奇幻小说","推理小说","悬疑小说","侦探小说","科幻小说","恐怖小说","灵异小说","异灵小说","竞技小说","游戏小说","动漫小说","网游小说","同人小说","耽美小说","架空","女尊天下","历史","穿越","历史","军事","重生","豪门","乡土","爱情","都市","言情","古典","古代","校园","青春","h","总裁","浪荡","女生","纯爱","修真","武侠","仙侠","贵族","亡灵","魔法","魔幻","玄幻","奇幻","推理","悬疑","侦探","科幻","恐怖","灵异","异灵","竞技","游戏","动漫","网游","同人","耽美");
		foreach($keyarray as $keya)
		{
			if(mb_stripos($kws,$keya)>0)
			{
				$keya = mb_convert_encoding($keya, "GBK", "UTF-8");
				$reidrow = $dsql->GetOne("SELECT * FROM `#@__arctype` WHERE reid=0 and content like '%$keya%'");
				if(is_array($reidrow))
				{
					$retypeid=$reidrow['id'];
					$retypename=str_replace('小说','',mb_convert_encoding($reidrow['typename'], "UTF-8", "GBK"));
					$content="稍等下哈/::-|……\n为您找到了下面的这些很不错的【".$retypename."】小说/::)：";
					$resql="Select * From `#@__arctype` where ishidden=0 and reid='$retypeid' order by bookclickm desc limit 8";
					$dsql->SetQuery($resql);
					$dsql->Execute();
					while($row = $dsql->GetArray())
					{
						$content.="\n【".mb_convert_encoding($row['typename'], "UTF-8", "GBK")."】作者：".mb_convert_encoding($row['zuozhe'], "UTF-8", "GBK").'   <<a href="'.$cfg_wapurl.'/wap.php?action=list&id='.$row['id'].'&uid='.$fromUsername.'">就看这本</a>>'."\n";
						$n++;
					}
					$content.="\n           【<a href='".$cfg_wapurl."/wap.php?action=list&type=top&id=".$retypeid."&uid=".$fromUsername."'>查看更多…</a>】";
				}
				if($n>0) break;
			}
		}
		if($n==0)
		{
			$otherkeys=array("推荐","介绍","好看的","推介","好的","好吧","有什么","可以");
			foreach($otherkeys as $otherkey)
			{
				if(mb_stripos($kws,$otherkey)>0)
				{
					$okr="yes";
					break;
				}
				else $okr="no";
			}
			if($okr=="yes")
			{
				$content="稍等下哈/::-|……为您找到了这些小说，都还不错哦/::)：";
				$bookid="";
				$qsql="Select id,typename,zuozhe From `#@__arctype` where ishidden=0 and reid not in(0,45) order by bookclick desc limit 5";
				$dsql->SetQuery($qsql);
				$dsql->Execute();
				while($row = $dsql->GetArray())
				{
					$content.="\n【".mb_convert_encoding($row['typename'], "UTF-8", "GBK")."】作者：".mb_convert_encoding($row['zuozhe'], "UTF-8", "GBK").'   <<a href="'.$cfg_wapurl.'/wap.php?action=list&id='.$row['id'].'&uid='.$fromUsername.'">就看这本</a>>'."\n";
					$bookid.=($bookid=="") ? $row['id']:",".$row['id'];
					$n++;
				}
				$content .= "\n\n下面这些是这个月比较受欢迎的，您也看一下/::)：";
				$qsql="Select id,typename,zuozhe From `#@__arctype` where ishidden=0 and reid not in(0,45) and id not in(".$bookid.") order by bookclickm desc limit 5";
				$dsql->SetQuery($qsql);
				$dsql->Execute();
				while($row = $dsql->GetArray())
				{
					$content.="\n【".mb_convert_encoding($row['typename'], "UTF-8", "GBK")."】作者：".mb_convert_encoding($row['zuozhe'], "UTF-8", "GBK").'   <<a href="'.$cfg_wapurl.'/wap.php?action=list&id='.$row['id'].'&uid='.$fromUsername.'">就看这本</a>>'."\n";
					$n++;
				}
				$content.="\n           【<a href='".$cfg_wapurl."/wap.php?action=top&uid=".$fromUsername."'>查看更多…</a>】";
			}
			else
			{
				$content="找到了哦/::D，您看看对不对/::)：";
				//$keyword = iconv("UTF-8","GBK",$keyword); 
				$keyword1 = mb_convert_encoding($keyword, "GBK", "UTF-8"); 
				$qsql="Select id,typename,zuozhe From `#@__arctype` where ishidden=0 and reid not in(0,45) and (typename like '%$keyword1%' or zuozhe like '%$keyword1%') order by typename limit 10";
				$dsql->SetQuery($qsql);
				$dsql->Execute();
				while($row = $dsql->GetArray())
				{
					$content.="\n【".mb_convert_encoding(str_replace("·","",$row['typename']), "UTF-8", "GBK")."】作者：".mb_convert_encoding($row['zuozhe'], "UTF-8", "GBK").'   <<a href="'.$cfg_wapurl.'/wap.php?action=list&id='.$row['id'].'&uid='.$fromUsername.'">就看这本</a>>'."\n";
					$n++;
				}
				$content.="\n           【<a href='".$cfg_wapurl."/wap.php?action=search&wd=".$keyword."&uid=".$fromUsername."'>查看更多…</a>】";
			}
			if($n==0)
			{
				$content = "亲爱的/::)，实在对不起/::-|\n我没找到您要看的小说/::'("."\n"."您选其它的先看看吧/::-|："."\n"."【在线阅读】\n请输入：阅读+书名（作者也可以，少字也没关系/::)）\n例如：阅读我欲封天\n或者：阅读耳根\n\n【继续上次阅读】\n请输入：继续"."\n\n请输入：";
			}
		}
	}
	return $content;
}
?>