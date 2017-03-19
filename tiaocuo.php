<?php
/**
 *
 * 错误提交
 *
 * @version        $Id: erraddsave.php 1 15:38 2010年7月8日Z tianya $
 * @package        DedeCMS.Site
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/include/common.inc.php");
require_once(DEDEINC.'/memberlogin.class.php');

$htmltitle = "错误提交";
$aid = isset($aid) && is_numeric($aid) ? $aid : 0;
if(empty($dopost))
{
	$url = $_SERVER["HTTP_REFERER"];
	$str = str_replace(".html","",str_replace("http://","",$url)); //去掉http:// 
	$a_id = explode("/",$str); // 以“/”分开成数组 
	$aid = $a_id[2]; //取第一个“/”以前的字符
	$row = $dsql->GetOne(" SELECT a.`title`,b.`typename`,b.`typedir` FROM `#@__archives` a left join `#@__arctype` b on(a.typeid=b.id) WHERE a.`id` ='$aid'");
	if(!$row)
	{
		ShowMsg("谢谢您对本网站的支持，我们会尽快处理您的建议！","/");
		exit();
	}
    $title = $row['typename']."-".$row['title'];
	$backurl=$row['typedir']."/";
    require_once(DEDEROOT."/templets/plus/erraddsave.htm");
}
elseif($dopost == "saveedit")
{
    $cfg_ml = new MemberLogin();
    $title = HtmlReplace($title);
    $type = isset($type) && is_numeric($type) ? $type : 0;
    $mid = isset($cfg_ml->M_ID) ? $cfg_ml->M_ID : 0;
    $err = trimMsg(cn_substr($err,2000),1);
    $oktxt = trimMsg(cn_substr($erradd,2000),1);
    $time = time();
    $query = "INSERT INTO `#@__erradd`(aid,mid,title,type,errtxt,oktxt,sendtime)
                  VALUES ('$aid','$mid','$title','$type','$err','$oktxt','$time'); ";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg("谢谢您的支持，我们会以最快速度处理您指出的错误！","$backurl",0,5000);
    exit();
}