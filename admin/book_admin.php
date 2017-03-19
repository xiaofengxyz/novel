<?php
require_once(dirname(__FILE__)."/config.php");
function zhuangtai($overdate)
{
	return ($overdate!='0'&&$overdate!='') ? "<font color='blue'>完本</font>" : "连载";
}
function zhangjie($tid)
{
	global $dsql;
	$lastarc = $dsql->GetOne("SELECT id,title FROM `#@__archives` WHERE typeid='$tid' ORDER BY id DESC");
	if($lastarc)
	{
		$lastarcch='<a href="/wap.php?action=article&id='.$lastarc['id'].'">'.filter_utf8_char(substr($lastarc['title'],0,60)).'</a>';
		return $lastarcch;
	}
	else
		return '';
}
function filter_utf8_char($src)
{
	$src = strval($src);
	$dest = '';
	$len = strlen($src);
	for ($i = 0; $i < $len ; $i++) {
		$ascii = ord($src[$i]);
		if($ascii < 0x7F) {
			$dest .= $src[$i];
		}
		elseif ($ascii >= 0x81 && $ascii<=0xFE) {
			if (1 == ($len-$i)){
				break;
			}
			$b2 = ord($src[++$i]);
			if($b2 >= 0x40 && $b2 <= 0xFE){
				$dest .= $src[$i-1] . $src[$i];
			}
		}
	}
	return $dest;
}
$action=(isset($action) && $action!="") ? $action:"book";
$tidurl=$orderurl=$overurl=$andsql=$overcur1=$overcur2=$ordercur1=$ordercur2=$ordercur3=$ordercur4=$toptypename=$overname=$searchurl="";
$tidcur=$ordercur=$overcur=' class="current"';
if($action=="search")
{
	$wd=trim($wd);
	$searchurl="&wd=".$wd;
}
$ordersql=" order by b.sortrank,b.lastupdate desc";
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
		$andsql .= " and b.overdate<>'0' and b.overdate<>'' and b.reid<>0";
		$overcur1=' class="current"';
		$overname="完本";
	}
	elseif($over=='2')
	{
		$overurl = "&over=2";
		$andsql .= " and b.overdate='0' and b.reid<>0";
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
			$ordersql=" order by b.sortrank,b.bookclick desc";
			$ordercur1=' class="current"';
		}
		elseif($order==2)
		{
			$ordersql=" order by b.sortrank,b.bookclickm desc";
			$ordercur2=' class="current"';
		}
		elseif($order==3)
		{
			$ordersql=" order by b.sortrank,b.bookclickw desc";
			$ordercur3=' class="current"';
		}
		elseif($order==4)
		{
			$ordersql=" order by b.sortrank,b.id desc";
			$ordercur4=' class="current"';
		}
		elseif($order==5)
		{
			$ordersql=" order by b.sortrank,b.booksize desc";
			$ordercur5=' class="current"';
		}
		$ordercur='';
	}
}

$typelink='<a href="book_admin.php?action=book'.$searchurl.$overurl.$orderurl.'"'.$tidcur.'>全部</a>';
$overink='<a href="book_admin.php?action='.$action.$searchurl.$tidurl.$orderurl.'"'.$overcur.'>全部</a>
<a href="book_admin.php?action='.$action.'&over=1'.$searchurl.$tidurl.$orderurl.'"'.$overcur1.'>已完本</a>
<a href="book_admin.php?action='.$action.'&over=2'.$searchurl.$tidurl.$orderurl.'"'.$overcur2.'>连载中</a>';
$orderlink='<a href="book_admin.php?action='.$action.$searchurl.$overurl.$tidurl.'"'.$ordercur.'>最后更新</a>
<a href="book_admin.php?action='.$action.'&order=1'.$searchurl.$overurl.$tidurl.'"'.$ordercur1.'>总点击</a>
<a href="book_admin.php?action='.$action.'&order=2'.$searchurl.$overurl.$tidurl.'"'.$ordercur2.'>月点击</a>
<a href="book_admin.php?action='.$action.'&order=3'.$searchurl.$overurl.$tidurl.'"'.$ordercur3.'>周点击</a>
<a href="book_admin.php?action='.$action.'&order=4'.$searchurl.$overurl.$tidurl.'"'.$ordercur4.'>新书</a>
<a href="book_admin.php?action='.$action.'&order=5'.$searchurl.$overurl.$tidurl.'"'.$ordercur5.'>字数</a>';
//类型选择
$dsql->SetQuery("Select id, typename From `#@__arctype` where reid=0".$topand." And ispart<>2 order by sortrank");
$dsql->Execute();
while($row=$dsql->GetObject())
{
	$act=($row->id==45) ? "zuozhe":"book";
	if($tid==$row->id)
	{
		$typelink .= '<a href="book_admin.php?action='.$act.'&tid='.$row->id.$searchurl.$overurl.$orderurl.'" class="current">'.str_replace("·","",$row->typename).'</a>';
		$toptypename=str_replace("·","",$row->typename);
	}
	else
		$typelink .= '<a href="book_admin.php?action='.$act.'&tid='.$row->id.$searchurl.$overurl.$orderurl.'">'.str_replace("·","",$row->typename).'</a>';
}
$toptypename=$overname.$toptypename;
$scount=0;
$tids="";
if($action=="zuozhe")
	$sql2="SELECT b.id as id FROM `#@__arctype` b WHERE 1=1".$andsql." and b.reid=45";
elseif($action=="search")
	$sql2="SELECT b.id as id FROM `#@__arctype` b WHERE (b.typename like '%$wd%' or b.zuozhe like '%$wd%') and b.ishidden=0";
else
	$sql2="SELECT b.id as id FROM `#@__arctype` b WHERE 1=1".$andsql." and b.reid<>45 and b.ishidden=0";
$dsql->SetQuery($sql2);
$dsql->Execute();
while($rowb=$dsql->GetObject())
{
	$tids.=($tids=="") ? $rowb->id : ",".$rowb->id;
	++$scount;
}
$sql="Select b.id,b.reid,b.lastupdate,b.typename,b.booksize,b.bookclick,b.bookclickm,b.bookclickw,b.tuijian,b.zuozhe,b.typedir,b.overdate,c.typedir as zuozhedir,c.id as zuozheid,d.typename as retypename,d.typedir as retypedir,b.copynid,e.notename,e.typeid as cotype from `#@__arctype` b left join `#@__arctype` c on(c.typename=b.zuozhe and c.reid=45) left join `#@__arctype` d on(d.id=b.reid) left join `#@__co_note` e on(e.nid=b.copynid) where b.id in($tids)".$ordersql;
require_once(DEDEINC."/datalistcp.class.php");
$dlist = new DataListCP();
$dlist->SetTemplet(DEDEADMIN."/templets/book_admin.htm");
$dlist->pageSize = 20;
$dlist->SetParameter("action",$action);
if($action=="search")
	$dlist->SetParameter("wd",$wd);
$dlist->SetParameter("tid",$tid);
$dlist->SetParameter("over",$over);
$dlist->SetParameter("order",$order);
$dlist->SetParameter("uid",$uid);
$dlist->SetSource($sql);
$dlist->Display();
$dsql->Close();
exit();
?>