<?php
/**
 * �ɼ���ַ
 *
 * @version        $Id: co_url.php 1 14:31 2010��7��12��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/datalistcp.class.php");
setcookie("ENV_GOBACK_URL",$dedeNowurl,time()+3600,"/");
$where = "";
$cotype = "";

if(!isset($nid)) $nid="";
else
{
	$row = $dsql->GetOne("SELECT typeid From `#@__co_note` where nid=$nid");
	$cotype=($row['typeid']==0) ? "off":"on";
}
if(!empty($nid)) $where = " where cu.nid='$nid' ";
if(empty($small)) $small = 0;

if($nid!='')
{
    $exportbt = "
    <input type='button' name='b0' value='��������' class='coolbg np'
    style='width:80px' onClick=\"location.href='co_export.php?nid=$nid';\" />
    <input type='button' name='b0' value='�ɼ����ڵ�' class='coolbg np'
    style='width:80px' onClick=\"location.href='co_gather_start.php?nid=$nid';\" />
    ";
}
else
{
    $exportbt = "";
}

$sql = "SELECT aid,nid,typeid,isdown,isexport FROM `#@__co_htmls` cu
$where ORDER BY cu.aid DESC";
$dlist = new DataListCP();
$dlist->SetParameter("nid", $nid);
$dlist->SetParameter("small", $small);
if($small==0)
{
    $dlist->SetTemplate(DEDEADMIN."/templets/co_url.htm");
}
else
{
    $dlist->SetTemplate(DEDEADMIN."/templets/co_url_2.htm");
}
$dlist->SetSource($sql);
$dlist->display();

function IsDownLoad($isd)
{
    if($isd=="0") return "δ����";
    else return "������";
}

function IsExData($isex)
{
    if($isex==0) return "δ����";
    else return "�ѵ���";
}
function getarcinfo($aid,$info)
{
    global $dsql;
	$row = $dsql->GetOne("SELECT * From `#@__co_htmls` where aid=$aid");
	foreach($row as $k=>$v)
	{
		if(!isset($$k))
		{
			$$k = addslashes($v);
		}
	}
    return $$info;
}
function getnoteinfo($nid,$info)
{
    global $dsql;
	$row = $dsql->GetOne("SELECT * From `#@__co_note` where nid=$nid");
	foreach($row as $k=>$v)
	{
		if(!isset($$k))
		{
			$$k = addslashes($v);
		}
	}
    return $$info;
}
function gettypeinfo($typeid,$info)
{
    global $dsql;
	$row = $dsql->GetOne("SELECT * From `#@__arctype` where id=$typeid");
	foreach($row as $k=>$v)
	{
		if(!isset($$k))
		{
			$$k = addslashes($v);
		}
	}
    return $$info;
}