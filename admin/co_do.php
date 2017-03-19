<?php
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/oxwindow.class.php");
if(!isset($nid)) $nid=0;
$ENV_GOBACK_URL = empty($_COOKIE["ENV_GOBACK_URL"]) ? "co_url.php" : $_COOKIE["ENV_GOBACK_URL"];

//删除节点
//删除节点将删除所有旧的网址索引
/*
function co_delete()
*/
if($dopost=="delete")
{
	CheckPurview('co_Del');
	//$nid = intval($nid);
	$dsql->ExecuteNoneQuery("Delete From `#@__co_htmls` where nid in ($nid)");
	$dsql->ExecuteNoneQuery("Delete From `#@__co_note` where nid in ($nid)");
	$dsql->ExecuteNoneQuery("Delete From `#@__co_urls` where nid in ($nid)");
	$dsql->ExecuteNoneQuery("Delete From `#@__co_listurls` where nid in ($nid)");
	ShowMsg("删除成功!",$_SERVER['HTTP_REFERER']);
	exit();
}

//清空采集内容
//清空采集内容时仍会保留旧的网址索引，在监控模式下始终采集新的内容
/*
function url_clear()
*/
else if($dopost=="clear")
{
	CheckPurview('co_Del');
	if(!isset($ids)) $ids='';
	if(empty($ids))
	{
		if(!empty($nid))
		{
			
			$dsql->ExecuteNoneQuery("Delete From `#@__co_htmls` where nid in($nid)");
			$dsql->ExecuteNoneQuery("Delete From `#@__co_urls` where nid in($nid)");
			$dsql->ExecuteNoneQuery("Delete From `#@__co_listurls` where nid in ($nid)");
			$dsql->ExecuteNoneQuery("Update `#@__co_note` set remark='',cotime=0,con=0,new=1 where nid in($nid)");
		}
		ShowMsg("成功清空所选节点采集的内容!",$_SERVER['HTTP_REFERER']);
		exit();
	}
	else
	{
		if(!empty($clshash))
		{
			$dsql->SetQuery("Select nid,url From `#@__co_htmls` where aid in($ids) ");
			$dsql->Execute();
			while($arr = $dsql->GetArray())
			{
				$nhash = md5($arr['url']);
				$nid = $arr['nid'];
				$dsql->ExecuteNoneQuery("Delete From `#@__co_urls ` where nid='$nid' And hash='$nhash' ");
			}
		}
		$dsql->ExecuteNoneQuery("Delete From `#@__co_htmls` where aid in($ids) ");
		ShowMsg("成功删除指定的网址内容!",$ENV_GOBACK_URL);
		exit();
	}
}
else if($dopost=="clearct")
{
	CheckPurview('co_Del');
	if(!empty($ids))
	{
		$dsql->SetQuery("Select typeid,REVERSE(title) as title From `#@__co_htmls` where aid in($ids) ");
		$dsql->Execute();
		while($arr = $dsql->GetArray())
		{
			$typeid =$arr['typeid'];
			$title = $arr['title'];
			$row = $dsql->GetOne("SELECT id From `#@__archives` where REVERSE(title) like '$title%' and typeid='$typeid'");
			if($row)
			{
				$aid=$row['id'];
				$dsql->ExecuteNoneQuery("Delete From `#@__arctiny` where id='$aid' ");
				$dsql->ExecuteNoneQuery("Delete From `#@__addonarticle` where id='$aid' ");
				$dsql->ExecuteNoneQuery("Delete From `#@__archives` where id='$aid'");
			}
		}
		$dsql->ExecuteNoneQuery("Update `#@__co_htmls` set isdown=0,isexport=0,result='' where aid in($ids) ");
	}
	ShowMsg("成功清除所选内容和对应文章!",$ENV_GOBACK_URL);
	exit();
}
else if($dopost=="clearresult")
{
	CheckPurview('co_Del');
	if(!empty($ids))
	{
		$dsql->ExecuteNoneQuery("Update `#@__co_htmls` set isdown=0,isexport=0,result='' where aid in($ids) ");
	}
	ShowMsg("成功清除所选内容!",$ENV_GOBACK_URL);
	exit();
}
else if($dopost=="upexport")
{
	if(!empty($ids))
	{
		$dsql->ExecuteNoneQuery("Update `#@__co_htmls` set isdown=1,isexport=1 where aid in($ids) ");
	}
	ShowMsg("成功更新所选内容!",$ENV_GOBACK_URL);
	exit();
}
else if($dopost=="upexport2")
{
	if(!empty($ids))
	{
		$dsql->ExecuteNoneQuery("Update `#@__co_htmls` set isdown=1,isexport=1 where aid>$ids and nid=$nid");
	}
	ShowMsg("成功更新所选内容!",$ENV_GOBACK_URL);
	exit();
}
else if($dopost=="truncate")
{
	$dsql->ExecuteNoneQuery(" TRUNCATE TABLE `#@__co_urls`");	
	$dsql->ExecuteNoneQuery(" TRUNCATE TABLE `#@__co_mediaurls`");	
	ShowMsg("成功清除所有采集内容!",$ENV_GOBACK_URL);
	exit();
}
//取消自动采集
else if($dopost=="noco")
{
	$dsql->ExecuteNoneQuery("Update `#@__co_note` set typeid='0' where nid in($nid)");
	ShowMsg("操作成功!",$_SERVER['HTTP_REFERER']);
	exit();
}
//半自动采集
else if($dopost=="auto")
{
	$dsql->ExecuteNoneQuery("Update `#@__co_note` set typeid='1',remark='' where nid in($nid)");
	ShowMsg("操作成功!",$_SERVER['HTTP_REFERER']);
	exit();
}
//全自动采集
else if($dopost=="noeor")
{
	$dsql->ExecuteNoneQuery("Update `#@__co_note` set typeid='2',remark='' where nid in($nid)");
	ShowMsg("操作成功!",$_SERVER['HTTP_REFERER']);
	exit();
}
//自动换站采集
else if($dopost=="setautocgn")
{
	$dsql->ExecuteNoneQuery("Update `#@__co_note` set typeid='3',remark='' where nid in($nid)");
	ShowMsg("操作成功!",$_SERVER['HTTP_REFERER']);
	exit();
}
//换站采集
else if($dopost=="setcgn")
{
	CheckPurview('co_AddNote');
	if(empty($bookid))
	{
		$row =$dsql->getone("SELECT * FROM `#@__co_note` where `nid`=$nid");
		if($row)
		{
			$typeidselect="<option value=''>请选择</option>";
			$booksum=$row['booksum'];
			$notename=$row['notename'];
			if(strstr($notename,'+')) $nidtype="+";
			else $nidtype="-";
			$typelist=explode($nidtype, $notename);
			for($n=1;$n<count($typelist);$n++)
			{
				$bookidrow =$dsql->getone("SELECT * FROM `#@__arctype` where typename='".$typelist[$n]."' and reid not in(0,45)");
				$typeidselect .= "<option value='".$bookidrow['id']."'>".$bookidrow['typename']."</option>";
			}
		}
		$courl="";
		$cnotename_pre="";
		$option="<option value=''>请选择</option>";
		include_once(DEDEADMIN."/inc/inc_catalog_options.php");
		$dsql->SetQuery("Select nid,notename,listconfig From `#@__co_note` where channelid<10 and notename like '%模版%' order by notename,nid");
		$dsql->Execute();
		while($arr = $dsql->GetArray())
		{
			$cnotename= preg_replace('/模版(.*)([\)|）]+?)/i','',$arr['notename']);
			$cnotename= preg_replace('/章节(.*)/i','',$cnotename);
			if($cnotename_pre!=$cnotename)
			{
				$exaurl= substr($arr['listconfig'],stripos($arr['listconfig'],'[(#)=>')+6,stripos($arr['listconfig'],'(*)=>')-stripos($arr['listconfig'],'(#)=>')-6);
				$exaurl= str_replace(';','',$exaurl);
				$option.="\r\n<option value='".$arr['nid']."'>".$cnotename."</option>";
				$showurl.=isset($showurl) ? "\r\n if(addnid==".$arr['nid']."){document.getElementById('exaurl').innerHTML='例如：".$exaurl." （请确保网址格式相同，否则将不能正常采集！）';}":"if(addnid==".$arr['nid']."){document.getElementById('exaurl').innerHTML='例如：".$exaurl." （请确保网址格式相同，否则将不能正常采集！）';}";
			}
			$cnotename_pre=$cnotename;
		}
		$showurl.="\r\n if(addnid==''){document.getElementById('exaurl').innerHTML='';}";
		$wintitle = "采集管理-更换采集目标站";
		$wecome_info = "<a href='co_main.php'>采集管理</a>::更换采集目标站";
		$win = new OxWindow();
		$win->Init("co_do.php","js/blank.js","POST");
		$win->AddHidden("dopost",$dopost);
		$win->AddHidden("nid",$nid);
		$win->AddHidden("backurl",$_SERVER['HTTP_REFERER']);
		$win->AddTitle("请输入以下信息：");
		$win->AddItem("要换站采集的小说：","<select name='bookid' >".$typeidselect."</select>");
		$win->AddItem("小说连载状态：","<input type=\"radio\" value=\"1\" name=\"overtag\"/>已完本 <input type=\"radio\" checked=\"checked\" value=\"2\" name=\"overtag\"/>连载中");
		$win->AddItem("新的目标站：","\r\n<script type=\"text/javascript\">function showurl(addnid){".$showurl."}</script>\r\n<select name='tonid'  onchange='showurl(this.value)'>\r\n".$option."\r\n</select>(注：只能从规则名称中含有“模版”字样的网站中采集)");
		$win->AddItem("要采集的章节列表网址：","<div id='exaurl' style='color:#FF0000'></div>\r\n<input type='text' name='bookurl' value='' style=\"width:90%\" />");
		$winform = $win->GetWindow("ok");
		$win->Display();
		exit();
	}
	$row = $dsql->GetOne("Select * From `#@__co_note` where nid='$tonid'");
	foreach($row as $k=>$v)
	{
		if(!isset($$k))
		{
			$$k = addslashes($v);
		}
	}
	$usemore = (empty($usemore) ? '0' : $usemore);
	if(strstr($notename,'+')) $nidtype="+";
	else $nidtype="-";
	$renid=$tonid;
	if(strstr($renid,'-'))
	{
		$renidarray=explode("-", $renid);
		$renid=$renidarray[0]."-".$overtag;
	}
	else
	{
		$renid=$renid."-".$overtag;
	}
	$overtype=($overtag=="1") ? "-":"+";
	$rowlist=explode($nidtype, $notename);
	$typenamerow =$dsql->getone("SELECT * FROM `#@__arctype` where id='".$bookid."'");
	$mynotename=str_replace("章节采集模版（不要删除）","",$rowlist[0]).$overtype.$typenamerow['typename'];
	$replacetname=$typenamerow['typename'];
	$listconfig=str_replace($notename,$mynotename,$listconfig);
	$bookurl=trim($bookurl);
	$bookid=trim($bookid);
	if($bookurl!="" && $bookid!="")
	{
		$lastaidrow = $dsql->GetOne("SELECT * FROM `#@__archives` WHERE typeid='$bookid' ORDER BY id DESC");
		if(is_array($lastaidrow))
			$remark="LAST:".preg_replace('/([\(|（]+)(.*)([\)|）]+)/i','',$lastaidrow['title']);
		$usemore = (empty($usemore) ? '0' : $usemore);
		$co_note="{dede:batchrule}[(#)=>".$bookurl."; (*)=>1-1; typeid=>".$bookid."]\r\n{/dede:batchrule}";
		$listconfig = str_replace(substr($listconfig,stripos($listconfig,'{dede:batchrule}'),stripos($listconfig,'{/dede:batchrule}')-stripos($listconfig,'{dede:batchrule}')+17),$co_note,$listconfig);
	}
	$inQuery = " INSERT INTO `#@__co_note`(`channelid`,`notename`,`sourcelang`,`uptime`,`cotime`,`pnum`,`isok`,`listconfig`,`itemconfig`,`usemore`,booksum,renid,typeid,remark)
               VALUES ('$channelid','$mynotename','$sourcelang','".time()."','0','0','0','$listconfig','$itemconfig','$usemore','1','$renid','1','$remark'); ";
	$dsql->ExecuteNoneQuery($inQuery);
	$copynid = $dsql->GetLastID();
	if($bookurl!="" && $bookid!="")
	{
		$dsql->ExecuteNoneQuery("update `#@__arctype` set copynid='$copynid' where id='".$bookid."' ");
	}
	$row =$dsql->getone("SELECT * FROM `#@__co_note` where `nid`=$nid");
	$listconfig2=$row['listconfig'];
	$booksum2=$row['booksum'];
	$notename2=$row['notename'];
	if(strstr($notename2,'+')) $nidtype2="+";
	else $nidtype2="-";
	if($booksum2>1)
	{
		$tid2=reveser_c('=>'.$bookid.']');
		$listconfig3=reveser_c($listconfig2);
		$addco_note=reveser_c(substr($listconfig3,stripos($listconfig3,$tid2),stripos($listconfig3,'[',stripos($listconfig3,$tid2))-stripos($listconfig3,$tid2)+1));
		$newbooksum=$booksum2-1;
		$newnotename=str_replace($nidtype2.$replacetname,'',$notename2);
		$newlistconfig=str_replace($nidtype2.$replacetname,'',str_replace($addco_note,'',$listconfig2));
		$dsql->ExecuteNoneQuery("update `#@__co_note` set notename='$newnotename',listconfig='$newlistconfig',booksum=$newbooksum where nid=$nid");
		$dsql->ExecuteNoneQuery("Delete From `#@__co_htmls` where nid=$nid and typeid=$bookid");
	}
	else
	{
		$dsql->ExecuteNoneQuery("Delete From `#@__co_htmls` where nid=$nid");
		$dsql->ExecuteNoneQuery("Delete From `#@__co_urls` where nid=$nid");
		$dsql->ExecuteNoneQuery("Delete From `#@__co_mediaurls` where nid=$nid");
		$dsql->ExecuteNoneQuery("Delete From `#@__co_note` where nid=$nid");
		$dsql->ExecuteNoneQuery("Delete From `#@__co_listurls` where nid=$nid");
	}
	ShowMsg("换站成功!",$backurl);
	exit();
}
//变更完本状态
else if($dopost=="changerenid")
{
	$row = $dsql->GetOne("SELECT notename,renid From `#@__co_note` where nid='$nid'");
	if($row)
	{
		$notename=$row['notename'];
		if(substr($row['renid'],-1)=='1')
		{
			$notename2=str_replace("-","+",$notename);
			$renid=str_replace("-1","-2",$row['renid']);
		}
		else
		{
			$notename2=str_replace("+","-",$notename);
			$renid=str_replace("-2","-1",$row['renid']);
		}
	}
	$dsql->ExecuteNoneQuery("Update `#@__co_note` set notename='$notename2',listconfig=replace(listconfig,'$notename','$notename2'),renid='$renid' where nid='$nid'");
	ShowMsg("操作成功!",$_SERVER['HTTP_REFERER']);
	exit();
}
/*
function url_clearall()
*/
else if($dopost=="clearall")
{
	CheckPurview('co_Del');
	$dsql->ExecuteNoneQuery("Delete From `#@__co_htmls` ");
	$dsql->ExecuteNoneQuery("Delete From `#@__co_urls ` ");
	ShowMsg("成功清空所有采集的临时内容!",$_SERVER['HTTP_REFERER']);
	exit();
}
//内容替换
/*
function co_replace() { }
*/
else if($dopost=="replace")
{
	//if()
	//$nid $aid $regtype $fdstring $rpstring
	$rpstring = trim($rpstring);
	if($regtype=='string')
	{
		$dsql->ExecuteNoneQuery("Update `#@__co_htmls` set `result`=REPLACE(`result`,'$fdstring','$rpstring') where nid='$nid' ");
	}
	else
	{
		//返回一条测试结果，并要求用户确认操作
		if(empty($rpok))
		{
			$fdstring = stripslashes($fdstring);
			$rpstring = stripslashes($rpstring);
			$hiddenrpvalue = "<textarea name='fdstring' style='display:none'>{$fdstring}</textarea>\r\n<textarea name='rpstring' style='display:none'>{$rpstring}</textarea>\r\n";
			$fdstring = str_replace("\\/","#ASZZ#",$fdstring);
			$fdstring = str_replace('/',"\\/",$fdstring);
			$fdstring = str_replace('#ASZZ#',"\\/",$fdstring);
			$result = $rs = stripslashes($rs);
			if($fdstring!='')
			{
				$result = trim(preg_replace("/$fdstring/isU",$rpstring,$rs));
			}
			$wintitle = "采集管理-内容替换";
			$wecome_info = "<a href='co_main.php'>采集管理</a>::内容替换";
			$win = new OxWindow();
			$win->Init("co_do.php","js/blank.js","POST");
			$win->AddHidden('dopost',$dopost);
			$win->AddHidden('nid',$nid);
			$win->AddHidden('regtype','regex');
			$win->AddHidden('aid',$aid);
			$win->AddHidden('rpok','ok');
			$win->AddTitle("内容替换操作确认：如果下面结果正确，点击确认，系统将替换当前节点所有内容！{$hiddenrpvalue}");
			$win->AddItem("原来的内容：","<textarea name='rs' style='width:90%;height:250px'>{$rs}</textarea>\r\n");
			$win->AddItem("按规则替换后的内容：","<textarea name='okrs' style='width:90%;height:250px'>{$result}</textarea>\r\n");
			$winform = $win->GetWindow("ok");
			$win->Display();
			exit();
		}
		else
		{
			if($fdstring!='')
			{
				$dsql->SetQuery("Select `aid`,`result` From `#@__co_htmls` where nid='$nid' ");
				$dsql->Execute();
				while($row = $dsql->GetArray())
				{
					$fdstring = stripslashes($fdstring);
					$rpstring = stripslashes($rpstring);
					$fdstring = str_replace("\\/","#ASZZ#",$fdstring);
					$fdstring = str_replace('/',"\\/",$fdstring);
					$fdstring = str_replace('#ASZZ#',"\\/",$fdstring);
					$result = trim(preg_replace("/$fdstring/isU",$rpstring,$row['result']));
					$result = addslashes($result);
					$dsql->ExecuteNoneQuery("Update `#@__co_htmls` set `result`='$result' where aid='{$row['aid']}' ");
				}
			}
		}
	}
	ShowMsg("成功替换当前节点所有数据！","co_view.php?aid=$aid");
	exit();
}
//复制节点
/*
function co_copy()
*/
else if($dopost=="copy")
{
	CheckPurview('co_AddNote');
	if(empty($mynotename))
	{
		$wintitle = "采集管理-复制采集规则";
		$wecome_info = "<a href='co_main.php'>采集管理</a>::复制采集规则";
		$win = new OxWindow();
		$win->Init("co_do.php","js/blank.js","POST");
		$win->AddHidden("dopost",$dopost);
		$win->AddTitle("请输入以下信息：");
		if(!empty($bookid))
		{
			$typeidselect="<option value=\"".$bookid."\" selected =\"selected\">".$typename."</option>";
			$courl="";
			$cnotename_pre="";
			$showurl.="\r\n if(addnid==''){document.getElementById('exaurl').innerHTML='';}";
			$option="<option value=''>请选择</option>";
			include_once(DEDEADMIN."/inc/inc_catalog_options.php");
			$dsql->SetQuery("Select nid,notename,listconfig From `#@__co_note` where channelid<10 and notename like '%模版%' order by notename,nid");
			$dsql->Execute();
			while($arr = $dsql->GetArray())
			{
				$cnotename= preg_replace('/模版(.*)([\)|）]+?)/i','',$arr['notename']);
				$cnotename= preg_replace('/章节(.*)/i','',$cnotename);
				if($cnotename_pre!=$cnotename)
				{
					$exaurl= substr($arr['listconfig'],stripos($arr['listconfig'],'[(#)=>')+6,stripos($arr['listconfig'],'(*)=>')-stripos($arr['listconfig'],'(#)=>')-6);
					$exaurl= str_replace(';','',$exaurl);
					$option.="\r\n<option value='".$arr['nid']."|".$cnotename."+".$typename."'>".$cnotename."</option>";
					$showurl.="\r\n if(addnid=='".$arr['nid']."|".$cnotename."+".$typename."'){document.getElementById('exaurl').innerHTML='例如：".$exaurl." （请确保网址格式相同，否则将不能正常采集！）';}";
				}
				$cnotename_pre=$cnotename;
			}
			
			$win->AddItem("要采集的小说：","<select name='bookid' >".$typeidselect."</select>");
			$win->AddItem("小说连载状态","<input type=\"radio\" value=\"1\" name=\"overtag\"/>已完本 <input type=\"radio\" checked=\"checked\" value=\"2\" name=\"overtag\"/>连载中");
			$win->AddItem("采集的目标站：","\r\n<script type=\"text/javascript\">function showurl(addnid){".$showurl."}</script>\r\n<select name='mynotename' onchange='showurl(this.value)'>\r\n".$option."\r\n</select>(注：只能从规则名称中含有“模版”字样的网站中采集)");
			$win->AddItem("目标站章节列表网址：","<div id='exaurl' style='color:#FF0000'></div>\r\n<input type='text' name='bookurl' value='' style=\"width:90%\" />");
			$win->AddHidden("backurl","co_main.php");
		}
		else
		{
			$win->AddHidden("nid",$nid);
			$win->AddHidden("backurl",$_SERVER['HTTP_REFERER']);
			$win->AddItem("新规则名称：","<input type='text' name='mynotename' value='' size='30' />不能含有“|”字符");
			$win->AddItem("要采集的章节列表网址：","<input type='text' name='bookurl' size='100' /><font color=red>（1）</font>留空则保留现有采集网址不变");
			$win->AddItem("对应站内小说ID：","<input type='text' name='bookid' value='' size='15' /><font color=red>（2）</font>如果填写了上面的网址，则ID必须填写");
			$win->AddItem("小说连载状态","<input type=\"radio\" value=\"1\" name=\"overtag\"/>已完本 <input type=\"radio\" value=\"2\" checked=\"checked\" name=\"overtag\"/>连载中");
			$win->AddItem("说明：","（1）和（2）必须同时填写或者同时不填写，<font color=red>仅适合站内已有该小说，但是没有对应采集规则，或者希望从别的站采集该小说的情况！</font>");
		}
		$winform = $win->GetWindow("ok");
		$win->Display();
		exit();
	}
	if(strstr($mynotename,'|'))
	{
		$myarray=explode("|", $mynotename);
		$nid=$myarray[0];
		$mynotename=$myarray[1];
		$message="添加规则成功！";
	}
	else
	{
		$message="复制规则成功！";
	}
	$row = $dsql->GetOne("Select * From `#@__co_note` where nid='$nid'");
	foreach($row as $k=>$v)
	{
		if(!isset($$k))
		{
			$$k = addslashes($v);
		}
	}
	if($overtag==1) $mynotename=str_replace("+","-",$mynotename);
	$usemore = (empty($usemore) ? '0' : $usemore);
	$listconfig=str_replace($notename,$mynotename,$listconfig);
	if($renid=="" || $renid=="0")
	{
		$renid=$nid."-".$overtag;
	}
	else
	{
		if(strstr($renid,'-'))
		{
			$renidarray=explode("-", $renid);
			$renid=$renidarray[0]."-".$overtag;
		}
		else
		{
			$renid=$renid."-".$overtag;
		}
	}
	$bookurl=trim($bookurl);
	$bookid=trim($bookid);
	$cotype=0;
	if($bookurl!="" && $bookid!="")
	{
		$cotype=1;
		$lastaidrow = $dsql->GetOne("SELECT * FROM `#@__archives` WHERE typeid='$bookid' ORDER BY id DESC");
		if($lastaidrow)
		{
			$remark="LAST:".preg_replace('/([\(|（]+)(.*)([\)|）]+)/i','',$lastaidrow['title']);
		}
		$typeidrow = $dsql->GetOne("SELECT * FROM `#@__arctype` WHERE id='$bookid' and reid not in(0,45)");
		if($typeidrow)
		{
			$usemore = (empty($usemore) ? '0' : $usemore);
			$co_note="{dede:batchrule}[(#)=>".$bookurl."; (*)=>1-1; typeid=>".$bookid."]\r\n{/dede:batchrule}";
			$listconfig = str_replace(substr($listconfig,stripos($listconfig,'{dede:batchrule}'),stripos($listconfig,'{/dede:batchrule}')-stripos($listconfig,'{dede:batchrule}')+17),$co_note,$listconfig);
		}
		else
		{
			ShowMsg("您要采集的小说不存在!请检查ID是否正确！","co_do.php?dopost=copy&nid=$nid'");
			exit();
		}
	}
	$inQuery = " INSERT INTO `#@__co_note`(`channelid`,`notename`,`sourcelang`,`uptime`,`cotime`,`pnum`,`isok`,`listconfig`,`itemconfig`,`usemore`,booksum,renid,typeid,remark)
               VALUES ('$channelid','$mynotename','$sourcelang','".time()."','0','0','0','$listconfig','$itemconfig','$usemore','1','$renid','$cotype','$remark'); ";
	$dsql->ExecuteNoneQuery($inQuery);
	$copynid = $dsql->GetLastID();
	if($bookurl!="" && $bookid!="")
	{
		$dsql->ExecuteNoneQuery("update `#@__arctype` set copynid='$copynid' where id='".$bookid."' ");
	}
	ShowMsg($message,$backurl);
	exit();
}
//测试Rss源是否正确
/*-----------------------
function co_testrss()
-------------------------*/
else if($dopost=="testrss")
{
	CheckPurview('co_AddNote');
	$msg = '';
	if($rssurl=='')
	{
		$msg = '你没有指定RSS地址！';
	}
	else
	{
		include(DEDEINC."/dedecollection.func.php");
		$arr = GetRssLinks($rssurl);
		$msg = "从 {$rssurl} 发现的网址：<br />";
		$i=1;
		if(is_array($arr))
		{
			foreach($arr as $ar)
			{
				$msg .= "<hr size='1' />\r\n";
				$msg .= "link: {$ar['link']}<br />title: {$ar['title']}<br />image: {$ar['image']}\r\n";
				$i++;
			}
		}
	}
	$wintitle = "采集管理-测试";
	$wecome_info = "<a href='co_main.php'>采集管理</a>::RSS地址测试";
	$win = new OxWindow();
	$win->AddMsgItem($msg);
	$winform = $win->GetWindow("hand");
	$win->Display();
	exit();
}
//测试批量网址是否正确
/*-----------------------
function co_testregx()
-------------------------*/
else if($dopost=="testregx")
{
	CheckPurview('co_AddNote');
	$msg = '';
	if($regxurl=='')
	{
		$msg = '你没有指定匹配的网址！';
	}
	else
	{
		include(DEDEINC."/dedecollection.func.php");
		$msg = "匹配的网址：<br />";
		$lists = GetUrlFromListRule($regxurl,'',$startid,$endid,$addv);
		foreach($lists as $surl)
		{
			$msg .= $surl[0]."<br />\r\n";
		}
	}
	$wintitle = "采集管理-测试匹配规则";
	$wecome_info = "<a href='co_main.php'>采集管理</a>::测试匹配列表网址规则";
	$win = new OxWindow();
	$win->AddMsgItem($msg);
	$winform = $win->GetWindow("hand");
	$win->Display();
	exit();
}

//采集未下载内容
/*--------------------
function co_all()
---------------------*/
else if($dopost=="coall")
{
	CheckPurview('co_PlayNote');
	$mrow = $dsql->GetOne("Select count(*) as dd From `#@__co_htmls` ");
	$totalnum = $mrow['dd'];
	if($totalnum==0)
	{
		ShowMsg("没发现可下载的内容！","-1");
		exit();
	}
	$wintitle = "采集管理-采集未下载内容";
	$wecome_info = "<a href='co_main.php'>采集管理</a>::采集未下载内容";
	$win = new OxWindow();
	$win->Init("co_gather_start_action.php","js/blank.js","GET");
	$win->AddHidden('startdd','0');
	$win->AddHidden('pagesize','5');
	$win->AddHidden('sptime','0');
	$win->AddHidden('nid','0');
	$win->AddHidden('totalnum',$totalnum);
	$win->AddMsgItem("本操作会检测并下载‘<a href='co_url.php'><u>临时内容</u></a>’中所有未下载的内容，是否继续？");
	$winform = $win->GetWindow("ok");
	$win->Display();
	exit();
}
?>