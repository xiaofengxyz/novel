<?php
require_once(dirname(__FILE__)."/config.php");
CheckPurview('co_Export');
if(empty($dopost))
{
	$dopost = '';
}
if($dopost!='done')
{
	
	require_once(DEDEADMIN."/inc/inc_catalog_options.php");
 $channelid = $usemore = 0;
	if(!empty($nid))
	{
		$mrow = $dsql->GetOne("Select count(*) as dd From `#@__co_htmls` where nid='$nid' And isdown='1' ");

		$rrow = $dsql->GetOne("Select channelid,usemore From `#@__co_note` where nid='$nid' ");
		$channelid = 1;
		$usemore = $rrow['usemore'];
	}
	include DedeInclude("templets/co_bang.htm");
	exit();
}
else
{
	$typeid = (isset($typeid)) ? $typeid : "";

$nid = (isset($nid)) ? $nid : "";

	if ($typeid=="" && !is_numeric($typeid))
{
		$addflag1='err';
	showmsg('参数错误(err:typeid  is error)',"co_main.php");
}
else
	{ 
$row =$dsql->getone("SELECT id FROM `#@__arctype` where `id`=$typeid");
if($row)
		{
	$addflag1='ok';
		}
		else
		{
			$addflag1='ok';
	$typeid=0;	
		}

}
	if ($nid=="" && !is_numeric($nid))
{
		$addflag2='err';
	showmsg('参数错误(err:nid  is error)',"co_main.php");
}
else
	{ 
$row =$dsql->getone("SELECT typeid FROM `#@__co_note` where `nid`=$nid");
if(!$row)
		{
		$addflag2='err';
	showmsg('采集规则不存在',"javascript:");
		}
		else
		{
				$addflag2='ok';
		}

}
if ($addflag1=='ok' && $addflag2=='ok')
	{
$dsql->ExecuteNoneQuery("Update `#@__co_note` set typeid='$typeid' where nid='$nid'; ");
if($typeid==0)
{
showmsg('当前采集规则取消采集成功','co_main.php');
}
else
{
showmsg('绑定栏目成功','co_main.php');
}
}					
}		

?>