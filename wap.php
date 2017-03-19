<?php
require_once (dirname(__FILE__) . "/include/common.inc.php");
header("Content-Type: text/html; charset=utf-8");
//header("Content-type:text/vnd.wap.wml");
require_once(dirname(__FILE__)."/include/wap.inc.php");
if(empty($action)) $action = 'index';
$cfg_templets_dir = $cfg_basedir.$cfg_templets_dir;
$channellist = "";
$newartlist = '';
$channellistnext = '';
$topand = '';
$wxuid = '';
$uid = '';
$coll="";
function zhuangtai($date)
{
	return ($date!='0'&&$date!='') ? "<font color='blue'>已完本</font>" : "连载中";
}
function zhangjie($tid)
{
	global $dsql,$wxuid;
	$lastarc = $dsql->GetOne("SELECT id,title FROM `#@__archives` WHERE typeid='$tid' ORDER BY id DESC");
	if($lastarc)
	{
		$lastarcch='<a href="/wap.php?action=article&id='.$lastarc['id'].$wxuid.'">'.$lastarc['title'].'</a>';
		return $lastarcch;
	}
	else
		return '';
}
if(isset($_GET['uid']))
{
	$uid=$_GET['uid'];
	$wxuid="&uid=".$uid;
	$wxuid2="wap.php?uid=".$uid;
}
//判断是否是伪静态地址进来的
if($action=='list')
{
	if($type!="top" || !is_numeric($id))
	{
		$tinfos = $dsql->GetOne("SELECT id,reid,typename FROM `#@__arctype` WHERE typedir like '%/$id' or id='$id'");
		if(!$tinfos)
		{
			@header("http/1.1 404 not found");
			@header("status: 404 not found");
			include("/404.html");
			exit(); 
		}
		$id = $tinfos['id'];
		$tzuozhe = $tinfos['typename'];
		if($tinfos['reid']==0)
		{
			$type="top";
			$topand=" and id<>".$id;
		}
		if($tinfos['reid']==45) $type="zuozhe";
	}
}
if($action=='list' || $action=='index' || $action=='top')
{
	//顶级导航列表
	$dsql->SetQuery("Select id, typename From `#@__arctype` where reid=0".$topand." And channeltype=1 And ishidden=0 And id not in(375,376,6981) And ispart<>2 order by sortrank");
	$dsql->Execute();
	$n=1;
	$toptid=array();
	$toptypename=array();
	while($row=$dsql->GetObject())
	{
		$toptid[] = $row->id;
		$toptypename[] = $row->typename;
		if(isset($id) && $type=="top")
		{
			$ccss1="";
			$ccss2="";
			if($id==$row->id)
			{
				$ccss1=" active";
				$ccss2=" class=\"active\"";
			}
		}
		else
		{
			$ccss1=" active";
			$ccss2=" class=\"active\"";
		}
		if($n%3==2)
			$channellist .= "<a href=\"/wap.php?action=list&type=top&id={$row->id}".$wxuid."\" class=\"xuanyi".$ccss1."\">".ConvertStr($row->typename)."</a>";
		else
			$channellist .= "<a href=\"/wap.php?action=list&type=top&id={$row->id}".$wxuid."\"".$ccss2.">".ConvertStr($row->typename)."</a>";
		$n++;
	}
	if($type=="top" || $action=='index' || $action=='top')
	{
		$topsql="";
		if($type=="top")
		{
			$topsql="a.reid=".$id." and ";
		}
		//点击榜
		$dianjibang="";
		$dsql->SetQuery("Select a.id,a.typename,a.tuijian,a.typeimg,a.description,a.zuozhe,b.id as reid,b.typename as retypename,c.id as zuozheid From `#@__arctype` a left join `#@__arctype` b on(b.id=a.reid) left join `#@__arctype` c on(c.typename=a.zuozhe) WHERE ".$topsql."a.reid not in(0,45) order by a.bookclick desc limit 0,10");
		$dsql->Execute();
		$n=1;
		while($row=$dsql->GetObject())
		{
			if($n<4)
				$dianjibang.="<li class=\"t\"><span class=\"count\" style=\"float: left;\">".$n."</span><a href='/wap.php?action=list&id={$row->id}".$wxuid."' style=\"float: left;\">{$row->typename}</a><dt style=\"float: right;\">[".$row->zuozhe."]</dt></li>";
			else
				$dianjibang.="<li><span class=\"count\" style=\"float: left;\">".$n."</span><a href='/wap.php?action=list&id={$row->id}".$wxuid."' style=\"float: left;\">{$row->typename}</a><dt style=\"float: right;\">[".$row->zuozhe."]</dt></li>";
			$n++;
		}
		//推荐榜
		$tuijianbang="";
		$dsql->SetQuery("Select a.id,a.typename,a.tuijian,a.typeimg,a.description,a.zuozhe,b.id as reid,b.typename as retypename,c.id as zuozheid From `#@__arctype` a left join `#@__arctype` b on(b.id=a.reid) left join `#@__arctype` c on(c.typename=a.zuozhe) WHERE ".$topsql."a.reid not in(0,45) order by a.tuijian desc limit 0,10");
		$dsql->Execute();
		$n=1;
		while($row=$dsql->GetObject())
		{
			if($n<4)
				$tuijianbang.="<li class=\"t\"><span class=\"count\" style=\"float: left;\">".$n."</span><a href='/wap.php?action=list&id={$row->id}".$wxuid."' style=\"float: left;\">{$row->typename}</a><dt style=\"float: right;\">[".$row->zuozhe."]</dt></li>";
			else
				$tuijianbang.="<li><span class=\"count\" style=\"float: left;\">".$n."</span><a href='/wap.php?action=list&id={$row->id}".$wxuid."' style=\"float: left;\">{$row->typename}</a><dt style=\"float: right;\">[".$row->zuozhe."]</dt></li>";
			$n++;
		}
		//字数榜
		$zishubang="";
		$dsql->SetQuery("Select a.id,a.typename,a.tuijian,a.typeimg,a.description,a.zuozhe,b.id as reid,b.typename as retypename,c.id as zuozheid From `#@__arctype` a left join `#@__arctype` b on(b.id=a.reid) left join `#@__arctype` c on(c.typename=a.zuozhe) WHERE ".$topsql."a.reid not in(0,45) order by a.booksize desc limit 0,10");
		$dsql->Execute();
		$n=1;
		while($row=$dsql->GetObject())
		{
			if($n<4)
				$zishubang.="<li class=\"t\"><span class=\"count\" style=\"float: left;\">".$n."</span><a href='/wap.php?action=list&id={$row->id}".$wxuid."' style=\"float: left;\">{$row->typename}</a><dt style=\"float: right;\">[".$row->zuozhe."]</dt></li>";
			else
				$zishubang.="<li><span class=\"count\" style=\"float: left;\">".$n."</span><a href='/wap.php?action=list&id={$row->id}".$wxuid."' style=\"float: left;\">{$row->typename}</a><dt style=\"float: right;\">[".$row->zuozhe."]</dt></li>";
			$n++;
		}
	}
}
//当前时间
$curtime = strftime("%Y-%m-%d %H:%M:%S",time());
$cfg_webname = ConvertStr($cfg_webname);

//主页
/*------------
function __index();
------------*/
if($action=='index')
{
	//强烈推荐
	$cfg_waptui=trim(str_replace('，',',',$cfg_waptui));
	$tuijianconten='<ul class="list">';
	$andsql=($cfg_waptui=='0' || $cfg_waptui=='') ? "order by tuijianm desc limit 0,4":"and id in($cfg_waptui)";
	$dsql->SetQuery("Select id,typename,tuijian,typeimg From `#@__arctype` WHERE reid<>45 and reid<>0 ".$andsql);
	$dsql->Execute();
	$n=1;
	while($row=$dsql->GetObject())
	{
		if($n%4==1 && $n!=1)
			$tuijianconten.='</ul><ul class="list">';
		$tuijianconten.="<li><a href='/wap.php?action=list&id={$row->id}".$wxuid."'><img src='{$row->typeimg}' alt=\"{$row->typename}\"  onerror=\"this.src='/images/jipin-default.jpg'\"/></a><div class=\"name\"><a href='/wap.php?action=list&id={$row->id}".$wxuid."'>{$row->typename}</a></div></li>";
		$n++;
	}
	$tuijianconten.='</ul>';
	//热门推荐
	$rementuijian="";
	$dsql->SetQuery("Select a.id,a.typename,a.tuijian,a.typeimg,a.description,a.zuozhe,b.id as reid,b.typename as retypename,c.id as zuozheid From `#@__arctype` a left join `#@__arctype` b on(b.id=a.reid) left join `#@__arctype` c on(c.typename=a.zuozhe) WHERE a.reid not in(0,45) order by a.bookclickm desc limit 0,10");
	$dsql->Execute();
	$n=1;
	while($row=$dsql->GetObject())
	{
		if($n==1)
			$rementuijian.="<li class=\"column-2\"><div class=\"left\"><a href=\"/wap.php?action=list&id={$row->id}".$wxuid."\"><img src=\"{$row->typeimg}\" alt=\"{$row->typename}\" width=\"85\" height=\"110\" onerror=\"this.src='/images/jipin-default.jpg'\"/></a></div><div class=\"right\"><h4><a href=\"/wap.php?action=list&id={$row->id}".$wxuid."\">{$row->typename}</a><span style=\"float: right;\">[".$row->zuozhe."]</span></h4><div class=\"summary\">{$row->description}</div></div></li>";
		else
			$rementuijian.="<li><a href='/wap.php?action=list&id={$row->id}".$wxuid."'><span>[".str_replace("·","",$row->retypename)."]</span>{$row->typename}</a><span style=\"float: right;\">[".$row->zuozhe."]</span></li>";
		$n++;
	}
	$rementuijian.="<li><span style=\"float: right;\">[<a href='/wap.php?action=shuku&order=2".$wxuid."'>更多热门小说···</a>]</span></li>";
	//新书推荐
	$xinshutuijian1="";
	$xinshutuijian2="";
	$dsql->SetQuery("Select a.id,a.typename,a.tuijian,a.typeimg,a.description,a.zuozhe,b.id as reid,b.typename as retypename,c.id as zuozheid From `#@__arctype` a left join `#@__arctype` b on(b.id=a.reid) left join `#@__arctype` c on(c.typename=a.zuozhe) WHERE a.reid not in(0,45) and a.booksize<500000 order by a.bookclickm desc limit 0,7");
	$dsql->Execute();
	$n=1;
	while($row=$dsql->GetObject())
	{
		if($n<5)
			$xinshutuijian1.="<li><a href='/wap.php?action=list&id={$row->id}".$wxuid."'><img src='{$row->typeimg}' alt=\"{$row->typename}\" onerror=\"this.src='/images/jipin-default.jpg'\"/></a><div class=\"name\"><a href='/wap.php?action=list&id={$row->id}".$wxuid."'>{$row->typename}</a></div></li>";
		else
			$xinshutuijian2.="<li><a href='/wap.php?action=list&id={$row->id}".$wxuid."'><span>[".str_replace("·","",$row->retypename)."]</span>{$row->typename}</a><span style=\"float: right;\">[".$row->zuozhe."]</span></li>";
		$n++;
	}
	//完本推荐
	$wanbentuijian1="";
	$wanbentuijian2="";
	$dsql->SetQuery("Select a.id,a.typename,a.tuijian,a.typeimg,a.description,a.zuozhe,b.id as reid,b.typename as retypename,c.id as zuozheid From `#@__arctype` a left join `#@__arctype` b on(b.id=a.reid) left join `#@__arctype` c on(c.typename=a.zuozhe) WHERE a.reid not in(0,45) and a.overdate<>'0' order by a.bookclickm desc limit 0,7");
	$dsql->Execute();
	$n=1;
	while($row=$dsql->GetObject())
	{
		if($n<5)
			$wanbentuijian1.="<li><a href='/wap.php?action=list&id={$row->id}".$wxuid."'><img src='{$row->typeimg}' alt=\"{$row->typename}\" onerror=\"this.src='/images/jipin-default.jpg'\"/></a><div class=\"name\"><a href='/wap.php?action=list&id={$row->id}".$wxuid."'>{$row->typename}</a></div></li>";
		else
			$wanbentuijian2.="<li><a href='/wap.php?action=list&id={$row->id}".$wxuid."'><span>[".str_replace("·","",$row->retypename)."]</span>{$row->typename}</a><span style=\"float: right;\">[".$row->zuozhe."]</span></li>";
		$n++;
	}
	//最近更新
	$newarc="";
	$query = "SELECT tp.id,tp.typedir,tp.typename,tp.zuozhe,tp.booksize,tp.overdate,tp.lastupdate,zz.id as zzid,zz.typedir as zuozhedir FROM `dede_arctype` tp LEFT JOIN `dede_arctype` zz ON zz.typename=tp.zuozhe WHERE tp.reid not in(0,45) order by lastupdate desc limit 0,20";
	$dsql->SetQuery($query);
	$dsql->Execute();
	while($row=$dsql->GetArray())
	{
		$overtag=($row['overdate']!=0 && $row['overdate']!="") ? "已完本":"连载中";
		$newarc.='<li class="column-2 ">
				 <a class="name" href="/wap.php?action=list&id='.$row['id'].$wxuid.'">'.$row['typename'].'</a>
				 <span style="float:right;font-size:0.8125em;color: #999;">'.$overtag.'</span>
				<p class="update">最新章节：'.zhangjie($row['id']).'</p>
				<p class="info">作者：<a href="/wap.php?action=list&type=zuozhe&id='.$row['zzid'].$wxuid.'" class="author">'.$row['zuozhe'].'</a><span class="words">字数：'.$row['booksize'].'</span></p>
			</li>';
	}
	$newarc.="<li class=\"column-2 \"><span style=\"float: right;font-size:12px;\">[<a href='/wap.php?action=shuku".$wxuid."'>更多小说更新列表···</a>]</span></li>";
	//显示WML
	include($cfg_templets_dir."/wap/index.htm");
	$dsql->Close();
	exit();
}
elseif($action=='top')
{
	$row = $dsql->GetOne("Select description,keywords,seotitle From `#@__arctype` where id='375' ");
	$description = $row['description'];
	$keywords = $row['keywords'];
	$seotitle = $row['seotitle'];
	$topcontents = "";
	$topjscontents = "";
	for($a=0;$a<count($toptid);$a++)
	{
		$topcontents .= '<div class="mod block rank-switch top'.$a.'">
		<div class="hd tab-switch">
			<span index="0" class="item active" style="width:50%">'.str_replace("小说","",str_replace("·","",$toptypename[$a])).'小说排行榜</span>
			<span index="1" class="item " style="width:50%">'.str_replace("小说","",str_replace("·","",$toptypename[$a])).'小说月榜</span>
		</div>
		<div class="bd">';
		$topjscontents .= '_inlineRun(function(){core.Tabs( $(".top'.$a.' .item"),$(".top'.$a.' ul"))});'."\r\n";
		//分类总排行榜
		$tdianjibang="";
		$dsql->SetQuery("Select a.id,a.typename,a.zuozhe,c.id as zuozheid From `#@__arctype` a left join `#@__arctype` c on(c.typename=a.zuozhe) WHERE a.reid=".$toptid[$a]." order by a.tuijian+a.bookclick desc limit 0,10");
		$dsql->Execute();
		$n=1;
		while($row=$dsql->GetObject())
		{
			if($n<4)
				$tdianjibang.="<li class=\"t\"><span class=\"count\" style=\"float: left;\">".$n."</span><a href='/wap.php?action=list&id={$row->id}".$wxuid."' style=\"float: left;\">{$row->typename}</a><dt style=\"float: right;\">[".$row->zuozhe."]</dt></li>";
			else
				$tdianjibang.="<li><span class=\"count\" style=\"float: left;\">".$n."</span><a href='/wap.php?action=list&id={$row->id}".$wxuid."' style=\"float: left;\">{$row->typename}</a><dt style=\"float: right;\">[".$row->zuozhe."]</dt></li>";
			$n++;
		}
		$topcontents .= '<ul class="list" >'.$tdianjibang.'</ul>'."\r\n";
		//分类月排行榜
		$ttuijianbang="";
		$dsql->SetQuery("Select a.id,a.typename,a.zuozhe,c.id as zuozheid From `#@__arctype` a left join `#@__arctype` c on(c.typename=a.zuozhe) WHERE a.reid=".$toptid[$a]." order by a.tuijianm+a.bookclickm desc limit 0,10");
		$dsql->Execute();
		$n=1;
		while($row=$dsql->GetObject())
		{
			if($n<4)
				$ttuijianbang.="<li class=\"t\"><span class=\"count\" style=\"float: left;\">".$n."</span><a href='/wap.php?action=list&id={$row->id}".$wxuid."' style=\"float: left;\">{$row->typename}</a><dt style=\"float: right;\">[".$row->zuozhe."]</dt></li>";
			else
				$ttuijianbang.="<li><span class=\"count\" style=\"float: left;\">".$n."</span><a href='/wap.php?action=list&id={$row->id}".$wxuid."' style=\"float: left;\">{$row->typename}</a><dt style=\"float: right;\">[".$row->zuozhe."]</dt></li>";
			$n++;
		}
		$topcontents .= '<ul class="list" style="display:none">'.$ttuijianbang.'</ul></div></div>'."\r\n";
	}
	//显示WML
	include($cfg_templets_dir."/wap/top.htm");
	$dsql->Close();
	exit();
}
else if($action=='shuku')
{
	$needCode = 'utf-8';
	$tidurl=$orderurl=$overurl=$andsql=$overcur1=$overcur2=$ordercur1=$ordercur2=$ordercur3=$ordercur4=$toptypename=$overname="";
	$tidcur=$ordercur=$overcur=' class="current"';
	$ordersql=" order by b.lastupdate desc";
	if(isset($tid))
	{
		$tid = preg_replace("[^0-9]", '', $tid);
		if($tid!="")
		{
			$tidurl = "&tid=".$tid;
			$andsql .= " and b.reid=".$tid;
			$tidcur='';
		}
	}
	if(isset($over))
	{
		$over = preg_replace("[^0-9]", '', $over);
		if($over=='1')
		{
			$overurl = "&over=1";
			$andsql .= " and b.overdate<>'0' and b.overdate<>''";
			$overcur1=' class="current"';
			$overname="完本";
		}
		elseif($over=='2')
		{
			$overurl = "&over=2";
			$andsql .= " and b.overdate='0'";
			$overcur2=' class="current"';
			$overname="连载";
		}
		$overcur='';
	}
	if(isset($order))
	{
		$order = preg_replace("[^0-9]", '', $order);
		if($order!="")
		{
			$orderurl = "&order=".$order;
			if($order==1)
			{
				$ordersql=" order by b.bookclick+b.tuijian desc";
				$ordercur1=' class="current"';
			}
			elseif($order==2)
			{
				$ordersql=" order by b.bookclickm+b.tuijianm desc";
				$ordercur2=' class="current"';
			}
			elseif($order==3)
			{
				$ordersql=" order by b.id desc";
				$ordercur3=' class="current"';
			}
			elseif($order==4)
			{
				$ordersql=" order by b.booksize desc";
				$ordercur4=' class="current"';
			}
			$ordercur='';
		}
	}
	$row = $dsql->GetOne("Select description,keywords,seotitle From `#@__arctype` where id='376' ");
	$description = $row['description'];
	$keywords = $row['keywords'];
	$seotitle = $row['seotitle'];
	$typelink='<a href="/wap.php?action=shuku'.$overurl.$orderurl.$wxuid.'"'.$tidcur.'>全部</a>';
	$overink='<a href="/wap.php?action=shuku'.$tidurl.$orderurl.$wxuid.'"'.$overcur.'>全部</a>
	<a href="/wap.php?action=shuku&over=1'.$tidurl.$orderurl.$wxuid.'"'.$overcur1.'>已完本</a>
	<a href="/wap.php?action=shuku&over=2'.$tidurl.$orderurl.$wxuid.'"'.$overcur2.'>连载中</a>';
	$orderlink='<a href="/wap.php?action=shuku'.$overurl.$tidurl.$wxuid.'"'.$ordercur.'>最后更新</a>
	<a href="/wap.php?action=shuku&order=1'.$overurl.$tidurl.$wxuid.'"'.$ordercur1.'>总人气</a>
	<a href="/wap.php?action=shuku&order=2'.$overurl.$tidurl.$wxuid.'"'.$ordercur2.'>月人气</a>
	<a href="/wap.php?action=shuku&order=3'.$overurl.$tidurl.$wxuid.'"'.$ordercur3.'>新书</a>
	<a href="/wap.php?action=shuku&order=4'.$overurl.$tidurl.$wxuid.'"'.$ordercur4.'>字数</a>';
	//类型选择
	$dsql->SetQuery("Select id, typename From `#@__arctype` where reid=0".$topand." And channeltype=1 And ishidden=0 And id not in(375,376) And ispart<>2 order by sortrank");
	$dsql->Execute();
	while($row=$dsql->GetObject())
	{
		if($tid==$row->id)
		{
			$typelink .= '<a href="/wap.php?action=shuku&tid='.$row->id.$overurl.$orderurl.$wxuid.'" class="current">'.str_replace("·","",$row->typename).'</a>';
			$toptypename=str_replace("小说","",str_replace("·","",$row->typename));
		}
		else
			$typelink .= '<a href="/wap.php?action=shuku&tid='.$row->id.$overurl.$orderurl.$wxuid.'">'.str_replace("·","",$row->typename).'</a>';
	}
	$toptypename=$overname.$toptypename;
	$varlist = "cfg_webname,seotitle,keywords,description,channellist,cfg_templeturl,typeconten,novel_powerby,typelink,overink,orderlink,topcontents,overname,toptypename";
	ConvertCharset($varlist);
	$scount=0;
	$tids="";
	$sql2="SELECT b.id as id FROM `#@__arctype` b WHERE 1=1".$andsql." and b.reid NOT IN(0,45) and b.ishidden=0";
	$dsql->SetQuery($sql2);
	$dsql->Execute();
	while($rowb=$dsql->GetObject())
	{
		$tids.=($tids=="") ? $rowb->id : ",".$rowb->id;
		++$scount;
	}
	$sql="Select b.id,b.lastupdate,b.typename,b.booksize,b.bookclick,b.tuijian,b.zuozhe,b.typedir,b.overdate,c.typedir as zuozhedir,c.id as zuozheid,d.typename as retypename,d.typedir as retypedir from `#@__arctype` b left join `#@__arctype` c on(c.typename=b.zuozhe) left join `#@__arctype` d on(d.id=b.reid) where b.id in($tids)".$ordersql;
	include_once(dirname(__FILE__)."/include/datalistcp.class.php");
	$dlist = new DataListCP();
	$dlist->SetTemplet($cfg_templets_dir."/wap/shuku.htm");
	$dlist->pageSize = 30;
	$dlist->SetParameter("action","shuku");
	$dlist->SetParameter("tid",$tid);
	$dlist->SetParameter("over",$over);
	$dlist->SetParameter("order",$order);
	$dlist->SetParameter("uid",$uid);
	$dlist->SetSource($sql);
	$dlist->Display();
	$dsql->Close();
	exit();
}
/*------------
function __list();
------------*/
//列表
else if($action=='list')
{
	$needCode = 'utf-8';
	$id = preg_replace("[^0-9]", '', $id);
	if(empty($id)) exit;
	$row = $dsql->GetOne("Select a.reid,a.typename,a.ishidden,a.zuozhe,a.booksize,a.bookclick,a.overdate,a.description,a.typeimg,a.copynid,co.notename,co.typeid as cotype From `#@__arctype` a  left join `#@__co_note` co on co.nid=a.copynid where id='$id' ");
	$reid = ConvertStr($row['reid']);
	$typename = ConvertStr($row['typename']);
	$zuozhe = ConvertStr($row['zuozhe']);
	$booksize = ConvertStr($row['booksize']);
	$bookclick = ConvertStr($row['bookclick']);
	$description = html2text($row['description']);
	$overdate = $row['overdate'];
	$typeimg = $row['typeimg'];
	$notename = $row['notename'];
	$cotype = $row['cotype'];
	$copynid = $row['copynid'];
	if($type=="top")
	{
		//分类热门推荐
		$rementuijian="";
		$dsql->SetQuery("Select a.id,a.typename,a.tuijian,a.typeimg,a.description,a.zuozhe,b.id as reid,b.typename as retypename,c.id as zuozheid From `#@__arctype` a left join `#@__arctype` b on(b.id=a.reid) left join `#@__arctype` c on(c.typename=a.zuozhe) WHERE a.reid=".$id." order by a.bookclickm desc limit 0,7");
		$dsql->Execute();
		$n=1;
		while($row=$dsql->GetObject())
		{
			if($n<3)
				$rementuijian.="<li class=\"column-2\"><div class=\"left\"><a href=\"/wap.php?action=list&id={$row->id}".$wxuid."\"><img src=\"{$row->typeimg}\" alt=\"{$row->typename}\" width=\"80\" height=\"110\" onerror=\"this.src='/images/jipin-default.jpg'\"/></a></div><div class=\"right\"><h4><a href=\"/wap.php?action=list&id={$row->id}".$wxuid."\">{$row->typename}</a><span style=\"float: right;\">[".$row->zuozhe."]</span></h4><div class=\"summary\">{$row->description}</div></div></li>";
			else
				$rementuijian.="<li><a href='/wap.php?action=list&id={$row->id}".$wxuid."'><span>[".str_replace("·","",$row->retypename)."]</span>{$row->typename}</a><span style=\"float: right;\">[".$row->zuozhe."]</span></li>";
			$n++;
		}
		$rementuijian.="<li><span style=\"float: right;font-size:12px;\">[<a href='/wap.php?action=shuku&order=2&tid=$id".$wxuid."'>更多热门".str_replace("小说","",str_replace("·","",$typename))."小说···</a>]</span></li>";
		//分类新书推荐
		$xinshutuijian="";
		$dsql->SetQuery("Select id,typename,tuijian,typeimg From `#@__arctype` WHERE reid=".$id." and booksize<500000 order by bookclickm desc limit 0,4");
		$dsql->Execute();
		while($row=$dsql->GetObject())
		{
			$xinshutuijian.="<li><a href='/wap.php?action=list&id={$row->id}".$wxuid."'><img src='{$row->typeimg}' alt=\"{$row->typename}\" onerror=\"this.src='/images/jipin-default.jpg'\"/></a><div class=\"name\"><a href='/wap.php?action=list&id={$row->id}".$wxuid."'>{$row->typename}</a></div></li>";
		}
		//分类完本推荐
		$wanbentuijian="";
		$dsql->SetQuery("Select id,typename,tuijian,typeimg From `#@__arctype` WHERE reid=".$id." and overdate<>'0' order by bookclick desc limit 0,4");
		$dsql->Execute();
		while($row=$dsql->GetObject())
		{
			$wanbentuijian.="<li><a href='/wap.php?action=list&id={$row->id}".$wxuid."'><img src='{$row->typeimg}' alt=\"{$row->typename}\" onerror=\"this.src='/images/jipin-default.jpg'\"/></a><div class=\"name\"><a href='/wap.php?action=list&id={$row->id}".$wxuid."'>{$row->typename}</a></div></li>";
		}
		//分类最近更新
		$typenewarc="";
		$query = "SELECT tp.id,tp.typedir,tp.typename,tp.zuozhe,tp.booksize,tp.overdate,tp.lastupdate,zz.id as zzid,zz.typedir as zuozhedir FROM `dede_arctype` tp LEFT JOIN `dede_arctype` zz ON zz.typename=tp.zuozhe WHERE tp.reid=".$id." order by lastupdate desc limit 0,20";
		$dsql->SetQuery($query);
		$dsql->Execute();
		while($row=$dsql->GetArray())
		{
			$overtag=($row['overdate']!=0 && $row['overdate']!="") ? "已完本":"连载中";
			$typenewarc.='<li class="column-2 ">
					 <a class="name" href="/wap.php?action=list&id='.$row['id'].$wxuid.'">'.$row['typename'].'</a>
					 <span style="float:right;font-size:0.8125em;color: #999;">'.$overtag.'</span>
					<p class="update">最新章节：'.zhangjie($row['id']).'</p>
					<p class="info">作者：<a href="/wap.php?action=list&type=zuozhe&id='.$row['zzid'].$wxuid.'" class="author">'.$row['zuozhe'].'</a><span class="words">字数：'.$row['booksize'].'</span></p>
				</li>';
		}
		$typenewarc.="<li class=\"column-2 \"><span style=\"float: right;font-size:12px;\">[<a href='/wap.php?action=shuku&tid=$id".$wxuid."'>更多".str_replace("小说","",str_replace("·","",$typename))."小说更新列表···</a>]</span></li>";
		//显示WML
		include($cfg_templets_dir."/wap/toplist.htm");
		$dsql->Close();
	}
	else
	{
		include_once(dirname(__FILE__)."/include/datalistcp.class.php");
		
		if($type=="zuozhe")
		{
			$sids = GetSonIds($id,1,true);
			$varlist = "cfg_webname,typename,channellist,channellistnext,cfg_templeturl,novel_powerby";
			ConvertCharset($varlist);
			$dlist = new DataListCP();
			$dlist->SetTemplet($cfg_templets_dir."/wap/zuozhelist.htm");
			$dlist->pageSize = 30;
			$dlist->SetParameter("action","list");
			$dlist->SetParameter("type","top");
			$dlist->SetParameter("id",$id);
			$dlist->SetParameter("uid",$uid);
			$dlist->SetSource("Select id,typename,zuozhe,booksize,overdate From `#@__arctype` where zuozhe='$tzuozhe' And channeltype=1 And ishidden=0 And ispart<>2 order by id desc");
		}
		else
		{
			if($reid==0) exit();
			$sids = $id;
			$updateclick=$_COOKIE["updateclick"];
			if($updateclick!=$sids)
			{
				//更新点击数
				if(!isset($_GET['pageno']))
				{
					$updatetime=time();
					if(intval($cfg_weekstart)!='2') $beginweek=mktime(0,0,0,date('m'),date('d')-date('w'),date('Y'));//本周开始时间戳,星期日~星期六为一周。
					else $beginweek=mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y'));//本周开始时间戳,星期一~星期日为一周。
					$beginmonth=mktime(0,0,0,date('m'),1,date('Y'));//本月开始时间戳

					$clickrow=$dsql->GetOne("SELECT MAX(lastclick) as lastclick FROM `#@__arctype` WHERE reid NOT IN (0,45)");
					$lastclickall=$clickrow['lastclick'];
					$clicksql="lastclick=".$updatetime.",bookclick=bookclick+1";
					if($beginmonth<=$lastclickall)//判断是否同一个月
						$clicksql.=",bookclickm=bookclickm+1";//月点击
					else
					{
						$clicksql.=",bookclickm=1";
						$dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET bookclickm='0',tuijianm='0',lastclick=$beginmonth ");
					}
					if($beginweek<=$lastclickall)//判断是否同一周
						$clicksql.=",bookclickw=bookclickw+1";//周点击
					else
					{
						$clicksql.=",bookclickw=1";
						$dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET bookclickw='0',tuijianw='0',lastclick=$beginweek ");
					}
					$dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET $clicksql where id='$sids'");
					setcookie("updateclick",$sids,(time()+3600));
				}
			}
			$row = $dsql->GetOne("Select typename From `#@__arctype` where id='$reid' ");		
			$retypename = ConvertStr($row['typename']);
			//类似的小说
			$leishi='<ul class="list">';
			$dsql->SetQuery("Select id,typeimg,typename From `#@__arctype` where reid='$reid' And id<>$sids And ishidden=0 order by bookclickm desc limit 8");
			$dsql->Execute();
			$n=1;
			while($row=$dsql->GetObject())
			{
				if($n%4==1 && $n!=1)
					$leishi.='</ul><ul class="list">';
				$leishi .= "<li><a href=\"/wap.php?action=list&id={$row->id}".$wxuid."\" target=\"_blank\"><img src=\"{$row->typeimg}\" alt=\"".$row->typename."\" onerror=\"this.src='/images/jipin-default.jpg'\"/></a><p class=\"name\"><a href=\"/wap.php?action=list&id={$row->id}".$wxuid."\">".$row->typename."</a></p></li>";
				$n++;
			}
			$leishi.='</ul>';
			//最新章节
			$dsql->SetQuery("Select id,title From `#@__archives` where typeid=$sids And arcrank=0 order by id desc limit 3");
			$dsql->Execute();
			while($row=$dsql->GetObject())
			{
				$channellistnext .="<li><a href=\"/wap.php?action=article&id={$row->id}".$wxuid."\">".ConvertStr($row->title)."</a></li>";
			}
			$varlist = "cfg_webname,channellist,newartlist,tuijianconten,typeconten,novel_powerby,typename,retypename,zuozhe,description,channellistnext,leishi";
			ConvertCharset($varlist);
			$dlist = new DataListCP();
			$dlist->SetTemplet($cfg_templets_dir."/wap/list.htm");
			$dlist->pageSize = 30;
			$dlist->SetParameter("action","list");
			$dlist->SetParameter("id",$id);
			$dlist->SetParameter("uid",$uid);
			$dlist->SetSource("Select id,title From `#@__archives` where typeid=$sids And arcrank=0 order by id");
			if($notename!="" && $cotype>0 && $cotype!=3)
			{
				$coll="?nid=".$copynid;
			}
		}
		$dlist->Display();
	}
	exit();
}
else if($action=='jilu')
{
	$jilucontents="";
	$aids="";
	if(isset($_COOKIE['content_id']))
	{
		$getcontent = unserialize(str_replace("\\", "", $_COOKIE['content_id']));
		foreach($getcontent as $row=>$r)
		{
			$nowarray=explode("-",str_replace("|","",$r));
			$aid = preg_replace("[^0-9]", '', $nowarray[1]);
			if(is_numeric($aid))
			{
				$aids.=($aids!="") ? ",".$aid:$aid;
			}
		}
		$query = "select a.id,a.title,b.typename,a.typeid,b.booksize,b.overdate,b.zuozhe from `#@__archives` a left join `#@__arctype` b on(b.id=a.typeid) where a.id in($aids) order by find_in_set(a.id,'$aids')";
		$dsql->SetQuery($query);
		$dsql->Execute();
		while($row=$dsql->GetArray())
		{
			$overtag=($row['overdate']!=0 && $row['overdate']!="") ? "已完本":"连载中";
			$jilucontents.='<li class="column-2 ">
					<div class="right">
						 <a class="name" href="/wap.php?action=list&id='.$row['typeid'].$wxuid.'">'.$row['typename'].'</a>
						 <span style="float:right;font-size:0.8125em;color: #999;">'.$overtag.'</span>
						<p class="update">上次看到：<a href="/wap.php?action=article&id='.$row['id'].$wxuid.'">'.$row['title'].'</a></p>
						<p class="info">作者：'.$row['zuozhe'].'  <span class="words">字数：'.$row['booksize'].'</span></p>
					</div>
				</li>';
		}
	}
	else $jilucontents="很抱歉！没有找到您的阅读记录哦:)";
	$varlist = "cfg_webname,typename,channellist,channellistnext,cfg_templeturl,novel_powerby,jilucontents";
	ConvertCharset($varlist);
	include($cfg_templets_dir."/wap/jilu.htm");
	$dsql->Close();
	exit();
}
//文档
/*------------
function __article();
------------*/
else if($action=='article')
{
	
	if($tid!="")
	{
		$tid = preg_replace("[^0-9]", '', $tid);
		$row = $dsql->GetOne("Select min(id) as id From `#@__archives` where typeid='$tid' ");
		$id=$row['id'];
	}
	$id = preg_replace("[^0-9]", '', $id);
	if(empty($id)) exit;
	//文档信息
	$query = "
	  Select tp.typename,retp.typename as retypename,tp.reid,tp.ishidden,tp.typedir,tp.copynid,arc.typeid,arc.title,arc.arcrank,arc.pubdate,arc.writer,arc.click,addon.body,co.notename,co.typeid as cotype From `#@__archives` arc 
	  left join `#@__arctype` tp on tp.id=arc.typeid
	  left join `#@__arctype` retp on retp.id=tp.reid
	  left join `#@__addonarticle` addon on addon.aid=arc.id
	  left join `#@__co_note` co on co.nid=tp.copynid
	  where arc.id='$id'
	";
	$row = $dsql->GetOne($query,MYSQLI_ASSOC);
	foreach($row as $k=>$v) $$k = $v;
	unset($row);
	$pubdate = strftime("%y-%m-%d %H:%M:%S",$pubdate);
	if($arcrank!=0) exit();
	$title = ConvertStr($title);
	if($ishidden==1) exit();
	if($notename!="" && $cotype>0 && $cotype!=3)
	{
		$coll="?nid=".$copynid;
	}
	
	//
	$content_id = array();
	if(isset($_COOKIE['content_id']))
	{
		$content_id = "";
		$h="no";
		$now_content = str_replace("\\", "", $_COOKIE['content_id']);
		$now = unserialize($now_content);//把字符串解析为php可以阅读的值
		foreach($now as $n=>$w)
		{
			$nowarray=explode("-",str_replace("|","",$w));
			if($nowarray[0]!=$typeid && $n<10)
			{
				$content_id .=($content_id=="") ? $w:",".$w;
			}
			elseif($nowarray[0]==$typeid && $nowarray[1]>=$id)
			{
				$content_id1=$w;
				$h="yes";
			}
		}
		if($h=="no") $content_id = "|".$typeid."-".$id.",".$content_id;
		else $content_id = $content_id1.",".$content_id;
		$content_id=explode(",",$content_id);
		$content= serialize($content_id); //把数组实例化成字符串
		setcookie("content_id",$content, time()+86400*365);
	}
	else
	{
		$content_id[] = "|".$typeid."-".$id;
		$content= serialize($content_id);//把数组实例化成字符串
		setcookie("content_id",$content, time()+86400*365);
	}
	
	//更新微信阅读记录
	if($uid!="")
	{
		$rowwx = $dsql->GetOne("Select * From `weixin_reader` where uid='$uid'");
		if($rowwx['lasttime']>0)
		{
			$uptag=1;
			if(stripos("a".$rowwx['data'],"|".$typeid.",")>0)
			{
				preg_match('/\|'.$typeid.',([0-9]+)/i',$rowwx['data'],$oldid);
				if($oldid[1]<$id)
					$readdata=preg_replace('/\|'.$typeid.',[0-9]+/i','',$rowwx['data']);
				else $uptag=0;
			}
			else $readdata=$rowwx['data'];
			if($uptag==1)
			{
				$readarray=explode("|",substr($readdata,1));
				if(count($readarray)<5)
					$readdata=$readdata."|".$typeid.",".$id;
				else
				{
					$rpdata="|".$readarray[0];
					$readdata=str_replace($rpdata,"",$readdata)."|".$typeid.",".$id;
				}
				$query_wx="UPDATE weixin_reader set data='".$readdata."',lasttime=UNIX_TIMESTAMP() where uid='".$uid."'";
				$dsql->ExecuteNoneQuery($query_wx);
			}
		}
		else 
		{
			$readdata="|".$typeid.",".$id;
			$query_wx="INSERT INTO weixin_reader (uid,data,lasttime) VALUES ('".$uid."','".$readdata."',UNIX_TIMESTAMP())";
			$dsql->ExecuteNoneQuery($query_wx);
		}
	}
	
	//章节中推荐阅读
	$query = "select a.typename,a.id,a.bookclickm from `#@__arctype` a where a.reid='$reid' and a.id<>'$typeid' order by bookclickm desc limit 0,10";
	$dsql->SetQuery($query);
	$dsql->Execute();
	$tuijian="";
	while($row=$dsql->GetArray()){
		$s="<a href='/wap.php?action=list&id=".$row['id'].$wxuid."' title='".$row['typename']."' target='_blank'>".$row['typename']."</a>";
		$tuijian.=($tuijian=="") ? $s:"、".$s;
	}
	
	
	$page="";
	$prefetch="";
	$rowpre = $dsql->GetOne("Select MAX(id) as preid From `#@__archives` where id<'$id' and typeid='$typeid'");
	if($rowpre['preid']>0)
		$page.="<a href='/wap.php?action=article&id=".$rowpre['preid'].$wxuid."' class=\"prev\"><span><</span>上一章</a>";
	else
		$page.="<a href='#' class=\"prev\"></a>";
	$rownext = $dsql->GetOne("Select MIN(id) as nextid From `#@__archives` where id>'$id' and typeid='$typeid'");
	if($rownext['nextid']>0)
	{
		$page.="<a href='/wap.php?action=article&id=".$rownext['nextid'].$wxuid."' class=\"next\">下一章<span>></span></a>";
	}
	else
		$page.="<a href='#' class=\"next\">暂时没有了</a>";
	//栏目内容(分页输出)
	include($cfg_templets_dir."/wap/article.htm");
	$dsql->Close();
	echo $pageBody;
	exit();
}
//查询
/*------------
function __article();
------------*/
else if($action=='search')
{
	$needCode = 'utf-8';
	$keyword = html2text(addslashes(cn_substr($wd,30)));
	$keyword = mb_convert_encoding($keyword, "GBK", "UTF-8");
	if($cfg_notallowstr !='' && preg_match("#".$cfg_notallowstr."#i", $keyword))
	{
		echo mb_convert_encoding("你的搜索关键字中存在非法内容，被系统禁止！", "UTF-8", "GBK");
		exit();
	}

	if(($keyword=='' || strlen($keyword)<2) && empty($typeid))
	{
		echo mb_convert_encoding("关键字不能小于2个字节！", "UTF-8", "GBK");
		exit();
	}

	//检查搜索间隔时间
	$lockfile = DEDEDATA.'/time.lock.inc';
	$lasttime = file_get_contents($lockfile);
	if(!empty($lasttime) && ($lasttime + $cfg_search_time) > time())
	{
		echo "系统忙，请稍后再试！";
		exit();
	}
	$varlist = "cfg_webname,channellist,novel_powerby";
	ConvertCharset($varlist);
	require_once(dirname(__FILE__)."/include/datalistcp.class.php");
	$dlist = new DataListCP();
	$dlist->SetTemplet($cfg_templets_dir."/wap/searchlist.htm");
	$dlist->pageSize = 10;
	$dlist->SetParameter("action","search");
	$dlist->SetParameter("type","top");
	$dlist->SetParameter("wd",$keyword);
	$dlist->SetParameter("uid",$uid);
	$dlist->SetSource("Select id,typename,zuozhe,booksize From `#@__arctype` where ishidden=0 and reid<>0 and (typename like '%$keyword%' or zuozhe like '%$keyword%') order by bookclick desc");
	$dlist->Display();
	exit();
}
//错误
/*------------
function __error();
------------*/
else
{
	ConvertCharset($varlist);
	include($cfg_templets_dir."/wap/error.wml");
	$dsql->Close();
	ConvertCharset($varlist);
	echo $pageBody;
	exit();
}
?>
