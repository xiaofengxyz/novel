<?php
/**
 *
 * 栏目列表/频道动态页
 *
 * @version        $Id: list.php 1 15:38 2010年7月8日Z tianya $
 * @package        DedeCMS.Site
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/../include/common.inc.php");
//$tid=str_replace("/","",$tid);
if($actype=="download") $tfilename=dirname(__FILE__)."/../html/download/".$tid.".html";//下载页
else $tfilename=dirname(__FILE__)."/../html/".$tid.".html";//封面页
//检查是否是以手机浏览器进入(IN_MOBILE)
function isMobile()
{
	//note 获取手机浏览器
	$str="";
	preg_match_all('/iPhone|iPod|Android|ios|iPad|Mobile|WAP|NetFront|JAVA|OperasMini|UCWEB|WindowssCE|Symbian|Series|webOS|UNTRUSTED/i',$_SERVER["HTTP_USER_AGENT"],$matches);
	$str = join('', $matches[0]);
	if($str) 
	{
		return true;
	}
	else return false;
}
$wap=isMobile();
if($wap)
{
	$tinfos = $dsql->GetOne("SELECT id,reid,typedir FROM `#@__arctype` WHERE typedir='/$tid' ");
	if($tinfos)
	{
		$tid = $tinfos['id'];
	}
	else
	{
		@header("http/1.1 404 not found");
		@header("status: 404 not found");
		include("../404.html");
		exit(); 
	}
	$wapurl=$cfg_wapurl."/wap.php?action=list&id=$tid";
	header("Location:$wapurl");
	exit();
}
$cfg_autotype_time=(intval($cfg_autotype_time)>=0) ? intval($cfg_autotype_time):172800;
if(!file_exists($tfilename) || abs(time()-@filemtime($tfilename))>$cfg_autotype_time) //HTML文件时间间隔大于2天，重新生成一次
{
	$tinfos = $dsql->GetOne("SELECT id,reid,typedir FROM `#@__arctype` WHERE typedir='/$tid' ");
	if($tinfos)
	{
		$tid = $tinfos['id'];
		$reid = $tinfos['reid'];
		$typedir = $tinfos['typedir'];
	}
	else
	{
		@header("http/1.1 404 not found");
		@header("status: 404 not found");
		include("../404.html");
		exit(); 
	}
	if($actype=="download")
	{
		if($reid==0 || $reid==45)
		{
			header('HTTP/1.1 301 Moved Permanently');
			header("Location:$typedir.html");
			exit();
		}
		else
		{
			include_once DEDEINC."/arc.partview.class.php";
			$pv = new PartView($tid,$needtypelink=TRUE,$actype);
			$pv->SetTemplet($cfg_basedir.$cfg_templets_dir."/".$cfg_df_style."/downlist.htm");
			$pv->SaveToHtml($tfilename);
			$pv->Close();
		}
	}
	else
	{
		include_once(DEDEINC."/arc.listview.class.php");
		$lv = new ListView($tid,"",$actype);
		$reurl = $lv->MakeHtml();
		$lv->Close();
	}
}
//更新点击数
$updatetime=time();
if(intval($cfg_weekstart)!='2') $beginweek=mktime(0,0,0,date('m'),date('d')-date('w'),date('Y'));//本周开始时间戳,星期日~星期六为一周。
else $beginweek=mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y'));//本周开始时间戳,星期一~星期日为一周。
$beginmonth=mktime(0,0,0,date('m'),1,date('Y'));//本月开始时间戳

$clickrow=$dsql->GetOne("SELECT MAX(lastclick) as lastclick FROM `#@__arctype` WHERE reid NOT IN (0,45)");
$lastclickall=$clickrow['lastclick'];
$clicksql="lastclick=".$updatetime.",bookclick=bookclick+1";
if($beginmonth<=$lastclickall)//判断是否同一个月
	$clicksql.=",bookclickm=bookclickm+1";//月点击
else
{
	$clicksql.=",bookclickm=1";
	$dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET bookclickm='0',tuijianm='0',lastclick=$beginmonth ");
}
if($beginweek<=$lastclickall)//判断是否同一周
	$clicksql.=",bookclickw=bookclickw+1";//周点击
else
{
	$clicksql.=",bookclickw=1";
	$dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET bookclickw='0',tuijianw='0',lastclick=$beginweek ");
}
$dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET $clicksql where id='$tid'");
include($tfilename);
exit(); 
//$t1 = ExecTime();

// if(!is_numeric($tid))
// {
	$tinfos = $dsql->GetOne("SELECT id FROM `#@__arctype` WHERE typedir='/$tid' ");
	if($tinfos)
		$tid = $tinfos['id'];
// }
$tid = (isset($tid) && is_numeric($tid) ? $tid : 0);

$channelid = (isset($channelid) && is_numeric($channelid) ? $channelid : 0);

if($tid==0 && $channelid==0)
{
	@header("http/1.1 404 not found");
	@header("status: 404 not found");
	include("../404.html");
	exit(); 
}
if(isset($TotalResult)) $TotalResult = intval(preg_replace("/[^\d]/", '', $TotalResult));


//如果指定了内容模型ID但没有指定栏目ID，那么自动获得为这个内容模型的第一个顶级栏目作为频道默认栏目
if(!empty($channelid) && empty($tid))
{
    $tinfos = $dsql->GetOne("SELECT tp.id,ch.issystem FROM `#@__arctype` tp LEFT JOIN `#@__channeltype` ch ON ch.id=tp.channeltype WHERE tp.channeltype='$channelid' And tp.reid=0 order by sortrank asc");
    if(!is_array($tinfos)) die(" No catalogs in the channel! ");
    $tid = $tinfos['id'];
}
else
{
    $tinfos = $dsql->GetOne("SELECT ch.issystem FROM `#@__arctype` tp LEFT JOIN `#@__channeltype` ch ON ch.id=tp.channeltype WHERE tp.id='$tid' ");
}

if($tinfos['issystem']==-1)
{
    $nativeplace = ( (empty($nativeplace) || !is_numeric($nativeplace)) ? 0 : $nativeplace );
    $infotype = ( (empty($infotype) || !is_numeric($infotype)) ? 0 : $infotype );
    if(!empty($keyword)) $keyword = FilterSearch($keyword);
    $cArr = array();
    if(!empty($nativeplace)) $cArr['nativeplace'] = $nativeplace;
    if(!empty($infotype)) $cArr['infotype'] = $infotype;
    if(!empty($keyword)) $cArr['keyword'] = $keyword;
    include(DEDEINC."/arc.sglistview.class.php");
    $lv = new SgListView($tid,$cArr);
} else {
	include_once(DEDEINC."/arc.listview.class.php");
    $lv = new ListView($tid,"",$actype);
    //对设置了会员级别的栏目进行处理
    if(isset($lv->Fields['corank']) && $lv->Fields['corank'] > 0)
    {
        include_once(DEDEINC.'/memberlogin.class.php');
        $cfg_ml = new MemberLogin();
        if( $cfg_ml->M_Rank < $lv->Fields['corank'] )
        {
            $dsql->Execute('me' , "SELECT * FROM `#@__arcrank` ");
            while($row = $dsql->GetObject('me'))
            {
                $memberTypes[$row->rank] = $row->membername;
            }
            $memberTypes[0] = "游客或没权限会员";
            $msgtitle = "你没有权限浏览栏目：{$lv->Fields['typename']} ！";
            $moremsg = "这个栏目需要 <font color='red'>".$memberTypes[$lv->Fields['corank']]."</font> 才能访问，你目前是：<font color='red'>".$memberTypes[$cfg_ml->M_Rank]."</font> ！";
            include_once(DEDETEMPLATE.'/plus/view_msg_catalog.htm');
            exit();
        }
    }
}

if($lv->IsError) ParamError();

$lv->Display();
