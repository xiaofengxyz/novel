<?php
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/oxwindow.class.php");
if(!isset($nid)) $nid=0;
$ENV_GOBACK_URL = empty($_COOKIE["ENV_GOBACK_URL"]) ? "co_url.php" : $_COOKIE["ENV_GOBACK_URL"];

//ɾ���ڵ�
//ɾ���ڵ㽫ɾ�����оɵ���ַ����
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
	ShowMsg("ɾ���ɹ�!",$_SERVER['HTTP_REFERER']);
	exit();
}

//��ղɼ�����
//��ղɼ�����ʱ�Իᱣ���ɵ���ַ�������ڼ��ģʽ��ʼ�ղɼ��µ�����
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
		ShowMsg("�ɹ������ѡ�ڵ�ɼ�������!",$_SERVER['HTTP_REFERER']);
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
		ShowMsg("�ɹ�ɾ��ָ������ַ����!",$ENV_GOBACK_URL);
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
	ShowMsg("�ɹ������ѡ���ݺͶ�Ӧ����!",$ENV_GOBACK_URL);
	exit();
}
else if($dopost=="clearresult")
{
	CheckPurview('co_Del');
	if(!empty($ids))
	{
		$dsql->ExecuteNoneQuery("Update `#@__co_htmls` set isdown=0,isexport=0,result='' where aid in($ids) ");
	}
	ShowMsg("�ɹ������ѡ����!",$ENV_GOBACK_URL);
	exit();
}
else if($dopost=="upexport")
{
	if(!empty($ids))
	{
		$dsql->ExecuteNoneQuery("Update `#@__co_htmls` set isdown=1,isexport=1 where aid in($ids) ");
	}
	ShowMsg("�ɹ�������ѡ����!",$ENV_GOBACK_URL);
	exit();
}
else if($dopost=="upexport2")
{
	if(!empty($ids))
	{
		$dsql->ExecuteNoneQuery("Update `#@__co_htmls` set isdown=1,isexport=1 where aid>$ids and nid=$nid");
	}
	ShowMsg("�ɹ�������ѡ����!",$ENV_GOBACK_URL);
	exit();
}
else if($dopost=="truncate")
{
	$dsql->ExecuteNoneQuery(" TRUNCATE TABLE `#@__co_urls`");	
	$dsql->ExecuteNoneQuery(" TRUNCATE TABLE `#@__co_mediaurls`");	
	ShowMsg("�ɹ�������вɼ�����!",$ENV_GOBACK_URL);
	exit();
}
//ȡ���Զ��ɼ�
else if($dopost=="noco")
{
	$dsql->ExecuteNoneQuery("Update `#@__co_note` set typeid='0' where nid in($nid)");
	ShowMsg("�����ɹ�!",$_SERVER['HTTP_REFERER']);
	exit();
}
//���Զ��ɼ�
else if($dopost=="auto")
{
	$dsql->ExecuteNoneQuery("Update `#@__co_note` set typeid='1',remark='' where nid in($nid)");
	ShowMsg("�����ɹ�!",$_SERVER['HTTP_REFERER']);
	exit();
}
//ȫ�Զ��ɼ�
else if($dopost=="noeor")
{
	$dsql->ExecuteNoneQuery("Update `#@__co_note` set typeid='2',remark='' where nid in($nid)");
	ShowMsg("�����ɹ�!",$_SERVER['HTTP_REFERER']);
	exit();
}
//�Զ���վ�ɼ�
else if($dopost=="setautocgn")
{
	$dsql->ExecuteNoneQuery("Update `#@__co_note` set typeid='3',remark='' where nid in($nid)");
	ShowMsg("�����ɹ�!",$_SERVER['HTTP_REFERER']);
	exit();
}
//��վ�ɼ�
else if($dopost=="setcgn")
{
	CheckPurview('co_AddNote');
	if(empty($bookid))
	{
		$row =$dsql->getone("SELECT * FROM `#@__co_note` where `nid`=$nid");
		if($row)
		{
			$typeidselect="<option value=''>��ѡ��</option>";
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
		$option="<option value=''>��ѡ��</option>";
		include_once(DEDEADMIN."/inc/inc_catalog_options.php");
		$dsql->SetQuery("Select nid,notename,listconfig From `#@__co_note` where channelid<10 and notename like '%ģ��%' order by notename,nid");
		$dsql->Execute();
		while($arr = $dsql->GetArray())
		{
			$cnotename= preg_replace('/ģ��(.*)([\)|��]+?)/i','',$arr['notename']);
			$cnotename= preg_replace('/�½�(.*)/i','',$cnotename);
			if($cnotename_pre!=$cnotename)
			{
				$exaurl= substr($arr['listconfig'],stripos($arr['listconfig'],'[(#)=>')+6,stripos($arr['listconfig'],'(*)=>')-stripos($arr['listconfig'],'(#)=>')-6);
				$exaurl= str_replace(';','',$exaurl);
				$option.="\r\n<option value='".$arr['nid']."'>".$cnotename."</option>";
				$showurl.=isset($showurl) ? "\r\n if(addnid==".$arr['nid']."){document.getElementById('exaurl').innerHTML='���磺".$exaurl." ����ȷ����ַ��ʽ��ͬ�����򽫲��������ɼ�����';}":"if(addnid==".$arr['nid']."){document.getElementById('exaurl').innerHTML='���磺".$exaurl." ����ȷ����ַ��ʽ��ͬ�����򽫲��������ɼ�����';}";
			}
			$cnotename_pre=$cnotename;
		}
		$showurl.="\r\n if(addnid==''){document.getElementById('exaurl').innerHTML='';}";
		$wintitle = "�ɼ�����-�����ɼ�Ŀ��վ";
		$wecome_info = "<a href='co_main.php'>�ɼ�����</a>::�����ɼ�Ŀ��վ";
		$win = new OxWindow();
		$win->Init("co_do.php","js/blank.js","POST");
		$win->AddHidden("dopost",$dopost);
		$win->AddHidden("nid",$nid);
		$win->AddHidden("backurl",$_SERVER['HTTP_REFERER']);
		$win->AddTitle("������������Ϣ��");
		$win->AddItem("Ҫ��վ�ɼ���С˵��","<select name='bookid' >".$typeidselect."</select>");
		$win->AddItem("С˵����״̬��","<input type=\"radio\" value=\"1\" name=\"overtag\"/>���걾 <input type=\"radio\" checked=\"checked\" value=\"2\" name=\"overtag\"/>������");
		$win->AddItem("�µ�Ŀ��վ��","\r\n<script type=\"text/javascript\">function showurl(addnid){".$showurl."}</script>\r\n<select name='tonid'  onchange='showurl(this.value)'>\r\n".$option."\r\n</select>(ע��ֻ�ܴӹ��������к��С�ģ�桱��������վ�вɼ�)");
		$win->AddItem("Ҫ�ɼ����½��б���ַ��","<div id='exaurl' style='color:#FF0000'></div>\r\n<input type='text' name='bookurl' value='' style=\"width:90%\" />");
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
	$mynotename=str_replace("�½ڲɼ�ģ�棨��Ҫɾ����","",$rowlist[0]).$overtype.$typenamerow['typename'];
	$replacetname=$typenamerow['typename'];
	$listconfig=str_replace($notename,$mynotename,$listconfig);
	$bookurl=trim($bookurl);
	$bookid=trim($bookid);
	if($bookurl!="" && $bookid!="")
	{
		$lastaidrow = $dsql->GetOne("SELECT * FROM `#@__archives` WHERE typeid='$bookid' ORDER BY id DESC");
		if(is_array($lastaidrow))
			$remark="LAST:".preg_replace('/([\(|��]+)(.*)([\)|��]+)/i','',$lastaidrow['title']);
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
	ShowMsg("��վ�ɹ�!",$backurl);
	exit();
}
//����걾״̬
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
	ShowMsg("�����ɹ�!",$_SERVER['HTTP_REFERER']);
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
	ShowMsg("�ɹ�������вɼ�����ʱ����!",$_SERVER['HTTP_REFERER']);
	exit();
}
//�����滻
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
		//����һ�����Խ������Ҫ���û�ȷ�ϲ���
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
			$wintitle = "�ɼ�����-�����滻";
			$wecome_info = "<a href='co_main.php'>�ɼ�����</a>::�����滻";
			$win = new OxWindow();
			$win->Init("co_do.php","js/blank.js","POST");
			$win->AddHidden('dopost',$dopost);
			$win->AddHidden('nid',$nid);
			$win->AddHidden('regtype','regex');
			$win->AddHidden('aid',$aid);
			$win->AddHidden('rpok','ok');
			$win->AddTitle("�����滻����ȷ�ϣ������������ȷ�����ȷ�ϣ�ϵͳ���滻��ǰ�ڵ��������ݣ�{$hiddenrpvalue}");
			$win->AddItem("ԭ�������ݣ�","<textarea name='rs' style='width:90%;height:250px'>{$rs}</textarea>\r\n");
			$win->AddItem("�������滻������ݣ�","<textarea name='okrs' style='width:90%;height:250px'>{$result}</textarea>\r\n");
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
	ShowMsg("�ɹ��滻��ǰ�ڵ��������ݣ�","co_view.php?aid=$aid");
	exit();
}
//���ƽڵ�
/*
function co_copy()
*/
else if($dopost=="copy")
{
	CheckPurview('co_AddNote');
	if(empty($mynotename))
	{
		$wintitle = "�ɼ�����-���Ʋɼ�����";
		$wecome_info = "<a href='co_main.php'>�ɼ�����</a>::���Ʋɼ�����";
		$win = new OxWindow();
		$win->Init("co_do.php","js/blank.js","POST");
		$win->AddHidden("dopost",$dopost);
		$win->AddTitle("������������Ϣ��");
		if(!empty($bookid))
		{
			$typeidselect="<option value=\"".$bookid."\" selected =\"selected\">".$typename."</option>";
			$courl="";
			$cnotename_pre="";
			$showurl.="\r\n if(addnid==''){document.getElementById('exaurl').innerHTML='';}";
			$option="<option value=''>��ѡ��</option>";
			include_once(DEDEADMIN."/inc/inc_catalog_options.php");
			$dsql->SetQuery("Select nid,notename,listconfig From `#@__co_note` where channelid<10 and notename like '%ģ��%' order by notename,nid");
			$dsql->Execute();
			while($arr = $dsql->GetArray())
			{
				$cnotename= preg_replace('/ģ��(.*)([\)|��]+?)/i','',$arr['notename']);
				$cnotename= preg_replace('/�½�(.*)/i','',$cnotename);
				if($cnotename_pre!=$cnotename)
				{
					$exaurl= substr($arr['listconfig'],stripos($arr['listconfig'],'[(#)=>')+6,stripos($arr['listconfig'],'(*)=>')-stripos($arr['listconfig'],'(#)=>')-6);
					$exaurl= str_replace(';','',$exaurl);
					$option.="\r\n<option value='".$arr['nid']."|".$cnotename."+".$typename."'>".$cnotename."</option>";
					$showurl.="\r\n if(addnid=='".$arr['nid']."|".$cnotename."+".$typename."'){document.getElementById('exaurl').innerHTML='���磺".$exaurl." ����ȷ����ַ��ʽ��ͬ�����򽫲��������ɼ�����';}";
				}
				$cnotename_pre=$cnotename;
			}
			
			$win->AddItem("Ҫ�ɼ���С˵��","<select name='bookid' >".$typeidselect."</select>");
			$win->AddItem("С˵����״̬","<input type=\"radio\" value=\"1\" name=\"overtag\"/>���걾 <input type=\"radio\" checked=\"checked\" value=\"2\" name=\"overtag\"/>������");
			$win->AddItem("�ɼ���Ŀ��վ��","\r\n<script type=\"text/javascript\">function showurl(addnid){".$showurl."}</script>\r\n<select name='mynotename' onchange='showurl(this.value)'>\r\n".$option."\r\n</select>(ע��ֻ�ܴӹ��������к��С�ģ�桱��������վ�вɼ�)");
			$win->AddItem("Ŀ��վ�½��б���ַ��","<div id='exaurl' style='color:#FF0000'></div>\r\n<input type='text' name='bookurl' value='' style=\"width:90%\" />");
			$win->AddHidden("backurl","co_main.php");
		}
		else
		{
			$win->AddHidden("nid",$nid);
			$win->AddHidden("backurl",$_SERVER['HTTP_REFERER']);
			$win->AddItem("�¹������ƣ�","<input type='text' name='mynotename' value='' size='30' />���ܺ��С�|���ַ�");
			$win->AddItem("Ҫ�ɼ����½��б���ַ��","<input type='text' name='bookurl' size='100' /><font color=red>��1��</font>�����������вɼ���ַ����");
			$win->AddItem("��Ӧվ��С˵ID��","<input type='text' name='bookid' value='' size='15' /><font color=red>��2��</font>�����д���������ַ����ID������д");
			$win->AddItem("С˵����״̬","<input type=\"radio\" value=\"1\" name=\"overtag\"/>���걾 <input type=\"radio\" value=\"2\" checked=\"checked\" name=\"overtag\"/>������");
			$win->AddItem("˵����","��1���ͣ�2������ͬʱ��д����ͬʱ����д��<font color=red>���ʺ�վ�����и�С˵������û�ж�Ӧ�ɼ����򣬻���ϣ���ӱ��վ�ɼ���С˵�������</font>");
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
		$message="��ӹ���ɹ���";
	}
	else
	{
		$message="���ƹ���ɹ���";
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
			$remark="LAST:".preg_replace('/([\(|��]+)(.*)([\)|��]+)/i','',$lastaidrow['title']);
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
			ShowMsg("��Ҫ�ɼ���С˵������!����ID�Ƿ���ȷ��","co_do.php?dopost=copy&nid=$nid'");
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
//����RssԴ�Ƿ���ȷ
/*-----------------------
function co_testrss()
-------------------------*/
else if($dopost=="testrss")
{
	CheckPurview('co_AddNote');
	$msg = '';
	if($rssurl=='')
	{
		$msg = '��û��ָ��RSS��ַ��';
	}
	else
	{
		include(DEDEINC."/dedecollection.func.php");
		$arr = GetRssLinks($rssurl);
		$msg = "�� {$rssurl} ���ֵ���ַ��<br />";
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
	$wintitle = "�ɼ�����-����";
	$wecome_info = "<a href='co_main.php'>�ɼ�����</a>::RSS��ַ����";
	$win = new OxWindow();
	$win->AddMsgItem($msg);
	$winform = $win->GetWindow("hand");
	$win->Display();
	exit();
}
//����������ַ�Ƿ���ȷ
/*-----------------------
function co_testregx()
-------------------------*/
else if($dopost=="testregx")
{
	CheckPurview('co_AddNote');
	$msg = '';
	if($regxurl=='')
	{
		$msg = '��û��ָ��ƥ�����ַ��';
	}
	else
	{
		include(DEDEINC."/dedecollection.func.php");
		$msg = "ƥ�����ַ��<br />";
		$lists = GetUrlFromListRule($regxurl,'',$startid,$endid,$addv);
		foreach($lists as $surl)
		{
			$msg .= $surl[0]."<br />\r\n";
		}
	}
	$wintitle = "�ɼ�����-����ƥ�����";
	$wecome_info = "<a href='co_main.php'>�ɼ�����</a>::����ƥ���б���ַ����";
	$win = new OxWindow();
	$win->AddMsgItem($msg);
	$winform = $win->GetWindow("hand");
	$win->Display();
	exit();
}

//�ɼ�δ��������
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
		ShowMsg("û���ֿ����ص����ݣ�","-1");
		exit();
	}
	$wintitle = "�ɼ�����-�ɼ�δ��������";
	$wecome_info = "<a href='co_main.php'>�ɼ�����</a>::�ɼ�δ��������";
	$win = new OxWindow();
	$win->Init("co_gather_start_action.php","js/blank.js","GET");
	$win->AddHidden('startdd','0');
	$win->AddHidden('pagesize','5');
	$win->AddHidden('sptime','0');
	$win->AddHidden('nid','0');
	$win->AddHidden('totalnum',$totalnum);
	$win->AddMsgItem("���������Ⲣ���ء�<a href='co_url.php'><u>��ʱ����</u></a>��������δ���ص����ݣ��Ƿ������");
	$winform = $win->GetWindow("ok");
	$win->Display();
	exit();
}
?>