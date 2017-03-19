<?php
require_once (dirname(__FILE__) . "/../include/common.inc.php");
if(!isset($bakurl))
{
	$bakurl=$_SERVER['HTTP_REFERER'];
}
if(strpos($bookid,","))
{
	$typeida = explode(",",$bookid);
	$typeids = str_replace($typeida[0].",","",$bookid);
	$bookid = $typeida[0];
	$gotourl="/download/maketxt.php?bookid=".$typeids."&bakurl=".str_replace("&","--",$bakurl)."";
	$ntime=500;
}
else
{
	$gotourl=str_replace("--","&",$bakurl);
	$ntime=2000;
}
$dsql->SetQuery("SELECT * FROM `#@__arctype` WHERE id=$bookid");
$dsql->Execute();
if($row = $dsql->GetArray())
{
	$treid=$row['reid'];
	if($row['channeltype']=='1' && $treid!=0)
	{
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
		if($row['overdate']!='0')
		{
			$txt_comtens1=str_replace("http://","",str_replace(array("[网站名称]","[站点根网址]"),array($cfg_webname,$cfg_basehost),$co_addtxttext));
			$file = fopen($bookname,"ab");
			fwrite($file,$txt_comtens1);
			fclose($file);
		}
	}
	@unlink("../html".$row['typedir'].".html");
	if($treid!=0)
	{
		$dsql->SetQuery("SELECT * FROM `#@__arctype` WHERE id=$treid");
		$dsql->Execute();
		if($row1 = $dsql->GetArray()) @unlink("../html".$row1['typedir'].".html");
	}
}
ShowMsg("[".$row['typename']."]生成txt成功！",$gotourl,"",$ntime);
?>