<?php
require_once(dirname(__FILE__)."/include/common.inc.php");
//顶级导航列表
$channellist1="";
$channellist2="";
$channellist3="";
$overurl="/over.html";
$dsql->SetQuery("Select id, typename,typedir From `#@__arctype` where reid=0 And channeltype=1 And ishidden=0 order by sortrank");
$dsql->Execute();
$n=1;
while($rowa=$dsql->GetObject())
{
	$tid=$rowa->id;
	$ttypename=$rowa->typename;
	$ttypedir=$rowa->typedir;
	if($tid==376)
	{
		$channellist1 .= "<li><em class='ver'>|</em><a class='con' href='".$ttypedir.".html' title='".str_replace('·','',$ttypename)."小说'>".str_replace("·","",$ttypename)."</a></li>";
		$channellist2 .= "<a class='ph' href='".$ttypedir.".html' title='".$ttypename."'>".$ttypename."</a>";
	}
	else
	{
		$channellist1 .= "<li><em class='ver'>|</em><a href='".$ttypedir.".html' title='".str_replace('·','',$ttypename)."小说'>".str_replace("·","",$ttypename)."</a></li>";
		$channellist2 .= "<a href='".$ttypedir.".html' title='".$ttypename."'>".$ttypename."</a>";
	}
	if($n<7)
	{
		if($aid!=str_replace("/","",$ttypedir))
			$channellist3.="<li><a href='".$ttypedir."/shuku.html' title='查看".$ttypename."小说列表'>".$ttypename."</a><em class='ver'>| </em></li>";
		else
			$channellist3.="<li><a href='".$ttypedir."/shuku.html' style='color:#F00;font-weight: 800;' title='查看".$ttypename."小说列表'>".$ttypename."</a><em class='ver'>| </em></li>";
	}
	$n++;
}
$resql=" and b.reid NOT IN(0,45)";
$ordersql=($orderby!="") ? " order by ".$orderby : " order by b.id";
$oderbysql=($orderway!="") ? " ".$orderway : " desc";
$oversql=($overtag=="over") ? " and b.overdate<>0" : "";
$typename="";
if(empty($aid)) $aid ="0";
if($aid!='0')
{
	if(!is_numeric($aid))
	{
		$tinfos = $dsql->GetOne("SELECT id,typename,typedir FROM `dede_arctype` WHERE typedir='/$aid' and reid=0 ");
		if(!$tinfos)
		{
			@header("http/1.1 404 not found");
			@header("status: 404 not found");
			include("/404.html");
			exit(); 
		}
		$reid = $tinfos['id'];
		$typename = $tinfos['typename'];
		$typedir = $tinfos['typedir'];
	}
	else
	{
		$tinfos = $dsql->GetOne("Select id,typename,typedir From `dede_arctype` where id='$aid' and reid=0 ");
		if(!$tinfos)
		{
			@header("http/1.1 404 not found");
			@header("status: 404 not found");
			include("/404.html");
			exit(); 
		}
		$reid = $tinfos['id'];
		$typename = $tinfos['typename'];
		$typedir = $tinfos['typedir'];
	}
	$resql=" and b.reid=".$reid ;
	$overurl=$typedir.$overurl;
}
if($overtag=="over")
{
	$typename=$typename."完结全本";
}
$scount=0;
$tids="";
$sql2="SELECT b.id as id FROM `dede_arctype` b WHERE 1=1".$resql." and b.ishidden=0".$oversql;
$dsql->SetQuery($sql2);
$dsql->Execute();
while($rowb=$dsql->GetObject())
{
	$tids.=($tids=="") ? $rowb->id : ",".$rowb->id;
	++$scount;
}
$sql="Select b.id,b.lastupdate,b.typename,b.booksize,b.bookclick,b.tuijian,b.zuozhe,b.typedir,b.overdate,c.typedir as zuozhedir,d.typename as retypename,d.typedir as retypedir from `dede_arctype` b left join `dede_arctype` c on(c.typename=b.zuozhe) left join `dede_arctype` d on(d.id=b.reid) where b.id in($tids)".$ordersql.$oderbysql;
$topage=ceil($scount/50);
if(empty($pageno)) $pageno =1;
if($topage>1)
{
	if($pageno==1)
	{
		$pagestart="javascript:";
		$pagepre="javascript:";		
		if($overtag=="over")
		{
			$pageend=$typedir."/over_".$scount."_".$topage.".html";
			$pagenext=$typedir."/over_".$scount."_".($pageno+1).".html";
		}
		else
		{
			$pageend=$typedir."/shuku_".$scount."_".$topage.".html";
			$pagenext=$typedir."/shuku_".$scount."_".($pageno+1).".html";
		}
	}
	elseif($pageno==$topage)
	{
		$pageend="javascript:";
		$pagenext="javascript:";		
		if($overtag=="over")
		{
			$pagestart=$typedir."/over.html";
			$pagepre=($pageno-1>1) ? $typedir."/over_".$scount."_".($pageno-1).".html" : $typedir."/over.html";
		}
		else
		{
			$pagestart=$typedir."/shuku.html";
			$pagepre=($pageno-1>1) ? $typedir."/shuku_".$scount."_".($pageno-1).".html" : $typedir."/shuku.html";
		}
	}
	else
	{
		if($overtag=="over")
		{
			$pagestart=$typedir."/over.html";
			$pagepre=($pageno-1>1) ? $typedir."/over_".$scount."_".($pageno-1).".html" : $typedir."/over.html";
			$pageend=$typedir."/over_".$scount."_".$topage.".html";
			$pagenext=$typedir."/over_".$scount."_".($pageno+1).".html";
		}
		else
		{
			$pagestart=$typedir."/shuku.html";
			$pagepre=($pageno-1>1) ? $typedir."/shuku_".$scount."_".($pageno-1).".html" : $typedir."/shuku.html";
			$pageend=$typedir."/shuku_".$scount."_".$topage.".html";
			$pagenext=$typedir."/shuku_".$scount."_".($pageno+1).".html";
		}
	}
}
else
{
	$pagestart="javascript:";
	$pageend="javascript:";
	$pagepre="javascript:";
	$pagenext="javascript:";
}
require_once(DEDEINC."/datalistcp.class.php");
$dlist = new DataListCP();
$dlist->SetTemplet($cfg_basedir.$cfg_templets_dir."/".$cfg_df_style."/shuku.htm");
$dlist->pageSize = 50;
$dlist->SetSource($sql);
$dlist->Display();

function zhuangtai($date)
{
	return ($date!='0'&&$date!='') ? "<font color='blue'>完本</font>" : "连载";
}
function zhangjie($tid)
{
	global $dsql;
	$lastarc = $dsql->GetOne("SELECT id,title FROM `dede_archives` WHERE typeid='$tid' ORDER BY id DESC");
	if($lastarc)
	{
		$lastarcch=$lastarc['id'].'.html" title="'.$lastarc['title'].'" target="_blank">'.$lastarc['title'];
		return $lastarcch;
	}
	else
		return '" target="_blank">';
}
?>
