<?php
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/datalistcp.class.php");
$sql  = "Select co.typeid,co.nid,co.channelid,co.notename,co.sourcelang,co.uptime,co.cotime,co.pnum,co.remark,ch.typename";
$sql .= " From `#@__co_note` co left join `#@__channeltype` ch on ch.id=co.channelid order by channelid desc,co.cotime desc,co.typeid desc";
$dlist = new DataListCP();
$dlist->pageSize=20; 
$dlist->SetTemplet(DEDEADMIN."/templets/co_main.htm");
$dlist->SetSource($sql); 
$dlist->display();

function GetDatePage($mktime)
{
	return $mktime=='0' ? '从未采集过' : MyDate('y-m-d H:i',$mktime);
}
function typeye($type)
{
	if($type=='0') return "<font color='red'>已取消采集</font>";
	elseif($type=='3') return "<font color='#888888'>待换站</font>";
	elseif($type=='2') return "<font color='blue'>全自动采集</font>";
	elseif($type=='1') return "半自动采集";
}
function type($type)
{
	return $type!='普通文章' ? "<font color='blue'>".$type."</font>" : $type;
}
function TjUrlNum($nid)
{
	global $dsql;
	$row =$dsql->GetOne("Select count(*) as dd From `#@__co_htmls` where nid='$nid' ");
	$urlcount=$row['dd'];
	$row =$dsql->GetOne("Select count(*) as dd From `#@__co_htmls` where nid='$nid' and isdown=1");
	$dsum=$row['dd'];
	$isdowncount="<a href='co_url.php?nid=$nid&small=0&totalresult=".$urlcount."&pageno=".ceil($dsum/25)."' target='_blank'>".$dsum."</a>";
	$row =$dsql->GetOne("Select count(*) as dd From `#@__co_htmls` where nid='$nid' and isexport=1");
	$esum=$row['dd'];
	$isexportcount="<a href='co_url.php?nid=$nid&small=0&totalresult=".$urlcount."&pageno=".ceil($esum/25)."' target='_blank'>".$esum."</a>";
	if($dsum==$esum)
		return $urlcount."/".$isdowncount."/".$isexportcount;
	else
		return "<font color='red'>".$urlcount."/".$isdowncount."/".$isexportcount."</font>";
}

?>