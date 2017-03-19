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
	if(!empty($nid))
	{
		$mrow = $dsql->GetOne("Select renid From `#@__co_note` where nid='$nid'");
		$renid=$mrow['renid'];

		$dsql->SetQuery("Select nid,notename From `#@__co_note` where renid='$renid' and booksum<5 ");
		$dsql->Execute();
		while($arr = $dsql->GetArray())
		{
			$option.=isset($option)?"\r\n<option value='".$arr['nid']."'>".$arr['notename']."</option>":"<option value='".$arr['nid']."'>".$arr['notename']."</option>";
		}
	}
	include DedeInclude("templets/co_hebing.htm");
	exit();
}
else
{
	$nid = (isset($nid)) ? $nid : "";
	if ($nid=="" && !is_numeric($nid))
	{
		showmsg('参数错误(err:nid  is error)',"co_main.php");
	}
	else
	{ 
		$row =$dsql->getone("SELECT * FROM `#@__co_note` where `nid`=$nid");
		if(!$row)
		{
			showmsg('采集规则不存在',"javascript:");
		}
		else
		{
			$booksum=$row['booksum'];
			$notename=$row['notename'];
			if(strstr($notename,'+')) $nidtype="+";
			else $nidtype="-";
			$rowlist=explode($nidtype, $notename);
			$addnotename=str_replace($rowlist[0],"",$notename);//要添加的规则名
			$listconfig=$row['listconfig'];
			$addco_note="\r\n".substr($listconfig,stripos($listconfig,'[(#)=>'),stripos($listconfig,'{/dede:batchrule}')-stripos($listconfig,'[(#)=>')+17);//要添加的列表源地址
			
			$row2 =$dsql->getone("SELECT * FROM `#@__co_note` where `nid`=$tonid");
			$addnotename=$row2['notename'].$addnotename;
			$booksum=$row2['booksum']+$booksum;
			$updatesql="update `#@__co_note` set `listconfig`=replace(replace(listconfig,'{/dede:batchrule}','".$addco_note."'),'".$row2['notename']."','".$addnotename."'),booksum='$booksum',notename='$addnotename',typeid='1' where nid='$tonid'";
			if($dsql->ExecuteNoneQuery($updatesql))
			{
				$dsql->ExecuteNoneQuery("Update `#@__co_htmls` set nid='$tonid' where nid='$nid'; ");
				$dsql->ExecuteNoneQuery("Update `#@__co_urls` set nid='$tonid' where nid='$nid'; ");
				$dsql->ExecuteNoneQuery("Update `#@__co_mediaurls` set nid='$tonid' where nid='$nid'; ");
				$dsql->ExecuteNoneQuery("Update `#@__arctype` set copynid='$tonid' where copynid='$nid'; ");
				$dsql->ExecuteNoneQuery("delete from `#@__co_note` where nid='$nid'; ");
				showmsg('采集规则合并成功','co_main.php');
			}
			else showmsg('采集规则合并失败！',"javascript:"); 
		}
	}
}		

?>