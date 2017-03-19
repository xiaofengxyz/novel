<?php
/**
 * 删除栏目
 *
 * @version        $Id: catalog_del.php 1 14:31 2010年7月12日Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__).'/config.php');

//检查权限许可
CheckPurview('t_Del,t_AccDel');
require_once(DEDEINC.'/typeunit.class.admin.php');
require_once(DEDEINC.'/oxwindow.class.php');
$id = trim(preg_replace("#[^0-9]#", '', $id));

//检查栏目操作许可
CheckCatalog($id,"你无权删除本栏目！");
if(empty($dopost)) $dopost='';
if($dopost=='ok')
{
    $ut = new TypeUnit();
    $ut->DelType($id,$delfile);
    UpDateCatCache();
    ShowMsg("成功删除一个栏目！","catalog_main.php");
    exit();
}
//批量删除
else if($dopost=="delselect")
{
	if(!isset($bakurl))
	{
		$bakurl=$_SERVER['HTTP_REFERER'];
	}
	if(strpos($typeid,","))
	{
		$typeida = explode(",",$typeid);
		$typeids = str_replace($typeida[0].",","",$typeid);
		$typeid = $typeida[0];
		$gotourl="catalog_del.php?dopost=delselect&typeid=".$typeids."&bakurl=".str_replace("&","--",$bakurl)."";
		$ntime=500;
	}
	else
	{
		$gotourl=str_replace("--","&",$bakurl);
		$ntime=2000;
	}
	$trow = $dsql->GetOne("SELECT a.typename,a.reid,a.typedir,a.copynid,b.typedir as retypedir FROM #@__arctype a left join #@__arctype b on(b.id=a.reid) WHERE a.id=$typeid");
	$typename=$trow['typename'];
	$reid=$trow['reid'];
	$nid=$trow['copynid'];
	if($reid!=0)
	{
		//删除采集规则
		$nidrow = $dsql->GetOne("SELECT * FROM #@__co_note WHERE nid=$nid");
		if(is_array($nidrow))
		{
			if($nidrow['booksum']<=1)
			{
				$dsql->ExecuteNoneQuery("Delete From `#@__co_htmls` where typeid=$typeid");
				$dsql->ExecuteNoneQuery("Delete From `#@__co_urls` where nid=$nid");
				$dsql->ExecuteNoneQuery("Delete From `#@__co_mediaurls` where nid=$nid");
				$dsql->ExecuteNoneQuery("Delete From `#@__co_note` where nid=$nid");
				$dsql->ExecuteNoneQuery("Delete From `#@__co_listurls` where nid in=$nid");
			}
			else
			{
				$tid2=reveser_c('=>'.$typeid.']');
				$listconfig3=reveser_c($nidrow['listconfig']);
				$addco_note=reveser_c(substr($listconfig3,stripos($listconfig3,$tid2),stripos($listconfig3,'[',stripos($listconfig3,$tid2))-stripos($listconfig3,$tid2)+1));
				$newbooksum=$nidrow['booksum']-1;
				$newnotename=str_replace("-".$typename,'',str_replace("+".$typename,'',$nidrow['notename']));
				$newlistconfig=str_replace("-".$typename,'',str_replace("+".$typename,'',str_replace($addco_note,'',$nidrow['listconfig'])));
				$dsql->ExecuteNoneQuery("update `#@__co_note` set notename='$newnotename',listconfig='$newlistconfig',booksum=$newbooksum where nid=$nid");
				$dsql->ExecuteNoneQuery("Delete From `#@__co_htmls` where typeid=$typeid");
			}
		}
		//删除文档及小说封面信息
		$ut = new TypeUnit();
		$ut->DelType($typeid,"yes");
		UpDateCatCache();
		@unlink("../html".$trow['typedir'].".html");
		@unlink("../html".$trow['retypedir'].".html");
		ShowMsg("成功删除[".$typename."]",$gotourl,"",$ntime);
	}
	else
	{
		ShowMsg("[".$typename."]属于分类栏目，请在操作菜单中单独删除！",$gotourl,"",$ntime);
	}
    exit();
}
//清空章节
else if($dopost=="cleanselect")
{
	if(!isset($bakurl))
	{
		$bakurl=$_SERVER['HTTP_REFERER'];
	}
	if(strpos($typeid,","))
	{
		$typeida = explode(",",$typeid);
		$typeids = str_replace($typeida[0].",","",$typeid);
		$typeid = $typeida[0];
		$gotourl="catalog_del.php?dopost=delselect&typeid=".$typeids."&bakurl=".str_replace("&","--",$bakurl)."";
		$ntime=500;
	}
	else
	{
		$gotourl=str_replace("--","&",$bakurl);
		$ntime=2000;
	}
	$trow = $dsql->GetOne("SELECT a.typename,a.reid,a.typedir,b.typedir as retypedir FROM #@__arctype a left join #@__arctype b on(b.id=a.reid) WHERE a.id=$typeid");
	$typename=$trow['typename'];
	$reid=$trow['reid'];
	if($reid!=0)
	{
		$ut = new TypeUnit();
		$ut->CleanType($typeid,"yes");
		UpDateCatCache();
		@unlink("../html".$trow['typedir'].".html");
		@unlink("../html".$trow['retypedir'].".html");
		ShowMsg("成功清空[".$typename."]",$gotourl,"",$ntime);
	}
	else
	{
		ShowMsg("[".$typename."]属于分类栏目，不能清空！",$gotourl,"",$ntime);
	}
    exit();
}
$dsql->SetQuery("SELECT typename,typedir FROM #@__arctype WHERE id=".$id);
$row = $dsql->GetOne();
$wintitle = "删除栏目确认";
$wecome_info = "<a href='catalog_main.php'>栏目管理</a> &gt;&gt; 删除栏目确认";
$win = new OxWindow();
$win->Init('catalog_del.php','js/blank.js','POST');
$win->AddHidden('id',$id);
$win->AddHidden('dopost','ok');
$win->AddTitle("你要确实要删除栏目： [{$row['typename']}] 吗？");
$win->AddItem('栏目的文件保存目录：',$row['typedir']);
$win->AddItem('是否删除文件：',"<input type='radio' name='delfile' class='np' value='no' checked='1' />否 &nbsp;<input type='radio' name='delfile' class='np' value='yes' />是");
$winform = $win->GetWindow('ok');
$win->Display();