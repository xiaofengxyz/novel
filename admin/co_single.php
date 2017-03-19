<?php
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/dedecollection.class.php");
CheckPurview('co_Export');
if(empty($dopost))
{
	$dopost = '';
}
if($dopost!='done')
{
	$courl="";
	$cnotename_pre="";
	require_once(DEDEADMIN."/inc/inc_catalog_options.php");
	$dsql->SetQuery("Select nid,notename,itemconfig From `#@__co_note` where channelid='99' order by notename");
	$dsql->Execute();
	while($arr = $dsql->GetArray())
	{
		$cnotename= preg_replace('/封面(.*)([\)|）]+?)/i','',$arr['notename']);
		if($cnotename_pre!=$cnotename)
		{
			$exaurl= substr($arr['itemconfig'],stripos($arr['itemconfig'],'dede:previewurl}')+16,stripos($arr['itemconfig'],'{/dede:previewurl}')-stripos($arr['itemconfig'],'dede:previewurl}')-16);
			$option.=isset($option)?"\r\n<option value='".$arr['nid']."'>".$cnotename."</option>":"<option value='".$arr['nid']."'>".$cnotename."</option>";
			$showurl.=isset($showurl) ? "\r\n if(addnid==".$arr['nid']."){document.getElementById('exaurl').innerHTML='例如：".$exaurl." （请确保网址格式相同，否则将不能正常采集！）';}":"if(addnid==".$arr['nid']."){document.getElementById('exaurl').innerHTML='例如：".$exaurl." （请确保网址格式相同，否则将不能正常采集！）';}";
		}
		$cnotename_pre=$cnotename;
	}
	$showurl.="\r\n if(addnid==''){document.getElementById('exaurl').innerHTML='';}";
	if(isset($_GET['nid']))
	{
		$courl="<script type=\"text/javascript\">(function() { function async_load(){ var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = '/download/spider.php?cosingle=1&nid=".$_GET['nid']."'; var x = document.getElementsByTagName('script')[0]; x.parentNode.insertBefore(s, x); } if (window.attachEvent) window.attachEvent('onload', async_load); else window.addEventListener('load', async_load, false); })(); </script>";
	}
	include DedeInclude("templets/co_single.htm");
	exit();
}
else
{
	if ($tonid=="" && !is_numeric($tonid))
	{
		showmsg('请选择目标站！',"co_single.php",'',5000);
		exit();
	}
	$co_s_url=trim($co_s_url);
	if ($co_s_url=="")
	{
		showmsg('请添加要采集的小说简介页面的网址！',"co_single.php",'',5000);
		exit();
	}
	$downurl=addslashes($co_s_url);
	$lrow = $dsql->GetOne("SELECT * FROM `#@__co_urls` WHERE nid=$tonid and hash='".md5($co_s_url)."'");
	if(is_array($lrow))
	{
		$dsql->ExecuteNoneQuery("Delete From `#@__co_htmls` where nid=$tonid and url='".$downurl."'");
		$dsql->ExecuteNoneQuery("Delete From `#@__co_urls` where nid=$tonid and hash='".md5($co_s_url)."'");
	}
	$inquery = "INSERT INTO `#@__co_htmls` (`nid` ,`typeid`, `title` , `litpic` , `url` , `dtime` , `isdown` , `isexport` , `result`)
	VALUES ('{$tonid}' ,'0', '".$downurl."' , '' , '".$downurl."' , '".time()."' , '0' , '0' , ''); ";
	$dsql->ExecuteNoneQuery($inquery);

	$inquery = "INSERT INTO `#@__co_urls`(hash,nid) VALUES ('".md5($co_s_url)."','{$tonid}');";
	$dsql->ExecuteNoneQuery($inquery);
	
	$dsql->SetSql("SELECT * FROM `#@__co_htmls` WHERE nid='$tonid' order by aid desc limit 1");
	$dsql->Execute();
	$row = $dsql->GetObject();
	$aid = $row->aid;
	$nid = $row->nid;
	$url = $row->url;
	$litpic = $row->litpic;
	$co = new DedeCollection();
	$co->LoadNote($nid);
	$co->DownUrl($aid, $url, $litpic);
	$row = $dsql->GetOne("Select * From `#@__co_htmls` where aid='$aid'");
	if($row['result']=='')
	{
		$suc="no";
		$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isdown=0,result='' where aid='$aid' ");
		for($m=0;$m<$co_retime;$m++)
		{
			$co->DownUrl($aid, $url, $litpic);
			$row = $dsql->GetOne("Select * From `#@__co_htmls` where aid='$aid'");
			if($row['isdown']=='1' && $row['result']!='')
			{
				$suc="ok";
				break;
			}
			else
			{
				$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isdown=0,result='' where aid='$aid' ");
			}
		}
		if($suc=="no")
		{
			showmsg("连续".$co_retime."次采集失败！请检查！","co_view.php?aid=$aid",'',3000);
			exit();
		}
	}
	showmsg('添加成功！',"co_single.php?nid=$nid");
}		

?>