<?php
class runtime
{ 
    var $StartTime = 0; 
    var $StopTime = 0; 
 
    function get_microtime() 
    { 
        list($usec, $sec) = explode(' ', microtime()); 
        return ((float)$usec + (float)$sec); 
    } 
 
    function start() 
    { 
        $this->StartTime = $this->get_microtime(); 
    } 
 
    function stop() 
    { 
        $this->StopTime = $this->get_microtime(); 
    } 
 
    function spent() 
    { 
        return round(($this->StopTime - $this->StartTime) * 1000, 1); 
    } 
 
}
//例子 
//echo (memory_get_usage()/1024/1024).'M-1<br />';
$runtime= new runtime;
$runtime->start();
require_once (dirname(__FILE__) . "/../include/common.inc.php");
$dsql->ExecuteNoneQuery("update `#@__arctype` set isok='0' where reid not in(0,45) and isok='1'; ");
for($n=1;$n<4;$n++)
{
	$row = $dsql->GetOne("SELECT * FROM `#@__arctype` WHERE reid not in(0,45) and isok='0' order by id;");
	if($row)
	{
		$bookid=$row['id'];
		$a=array('‘','’','“','”','–','—',' ','￠','￡','¤','￥','|','§','◎','《','》','°','±','2','3','1','�','×','÷','"','&','<','>','…','‰','<','>','←','↑','→','↓','-','√','∞','≠','≤','≥');
		$b=array('&lsquo;','&rsquo;','&ldquo;','&rdquo;','&ndash;','&mdash;','&nbsp;','&cent;','&pound;','&curren;','&yen;','&brvbar;','&sect;','&copy;','&raquo;','&laquo;','&deg;','&plusmn;','&sup2;','&sup3;','&sup1;','&euro;','&times;','&divide;','&quot;','&amp;','&lt;','&gt;','&hellip;','&permil;','&lsaquo;','&rsaquo;','&larr;','&uarr;','&rarr;','&darr;','&minus;','&radic;','&infin;','&ne;','&le;','&ge;');
		$bookname=$row['zuozhe']."-".$row['typename'].".txt";
		if(file_exists($bookname))
		{
			unlink($bookname);
		}
		$booksize=0;
		$dsql->SetQuery("SELECT a.id,a.title,a.click,b.body FROM #@__archives a left join #@__addonarticle b on(a.id=b.aid) WHERE a.typeid=$bookid order by id asc");
		$dsql->Execute();
		while($row1 = $dsql->GetArray())
		{
			$txt_comtens="";
			$txt_title=$row1['title'];
			$txt_title=$txt_title."\r\n";		
			$txt_body=$txt_title.$row1['body'];
			$txt_body=str_replace($b,$a,str_replace("<br />","\r\n",str_replace("<br>","\r\n",trim($txt_body))));
			$txt_body=str_replace("<p>","\r\n",str_replace("</p>","\r\n",trim($txt_body)));
			$txt_body=str_replace("\r\n\r\n","\r\n",str_replace("\r\n\r\n\r\n","\r\n",str_replace("\r\n\r\n\r\n\r\n","\r\n",$txt_body)));
			$acrlen=round(strlen($txt_body)/2.05);
			if(floor($booksize/$co_addtxtpsize)<floor(($acrlen+$booksize)/$co_addtxtpsize) || $booksize==0)
			{
				$txt_comtens.=str_replace("http://","",str_replace(array("[网站名称]","[站点根网址]"),array($cfg_webname,$cfg_basehost),$co_addtxttext))."\r\n\r\n";
			}
			$booksize=$acrlen+$booksize;
			$txt_comtens.=$txt_body."\r\n\r\n";
			$file = fopen($bookname,"ab");
			fwrite($file,$txt_comtens);
			fclose($file);
		}
		if($row->overdate!='0')
		{
			$txt_comtens1=str_replace("http://","",str_replace(array("[网站名称]","[站点根网址]"),array($cfg_webname,$cfg_basehost),$co_addtxttext));
			$file = fopen($bookname,"ab");
			fwrite($file,$txt_comtens1);
			fclose($file);
		}
		$dsql->ExecuteNoneQuery("update `#@__arctype` set isok='2' where id=$bookid; ");
		echo $_GET['m']+$n." - 已完成：".$bookname."-字数：".$booksize."<br/>";
	}
	else
	{
		$dsql->ExecuteNoneQuery("update `#@__arctype` set isok='1' where isok='2'; ");
		echo "全部完成";
		exit();
	}
}
$row = $dsql->GetOne("SELECT count(id) as dd FROM `#@__arctype` WHERE reid not in(0,45) and isok='0';");
$m=$_GET['m']+$n-1;
$runtime->stop();
echo "页面执行时间: ".$runtime->spent()." 毫秒（".date("Y-m-d H:i:s",time()+1)."）<br />";
ShowMsg("完成 ".$m." 篇，剩余".$row['dd']."篇，继续中！","totxt.php?m=$m","",500);
?>