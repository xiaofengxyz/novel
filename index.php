<?php
/**
 * @version        $Id: index.php 1 9:23 2010-11-11 tianya $
 * @package        DedeCMS.Site
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
if(!file_exists(dirname(__FILE__).'/data/common.inc.php'))
{
    header('Location:install/index.php');
    exit();
}
require_once (dirname(__FILE__) . "/include/common.inc.php");
$mobilehost=str_replace('http://','',$cfg_wapurl);
/**
 * ����Ƿ������ֻ����������(IN_MOBILE)
 */
function isMobile()
{
	//note ��ȡ�ֻ������
	$str="";
	preg_match_all('/iPhone|iPod|Android|ios|iPad|Mobile|WAP|NetFront|JAVA|OperasMini|UCWEB|WindowssCE|Symbian|Series|webOS|UNTRUSTED/i',$_SERVER["HTTP_USER_AGENT"],$matches);
	$str = join('', $matches[0]);
	if($str) 
	{
		return true;
	}
	else return false;
}
if($_SERVER["HTTP_HOST"]==$mobilehost)
{
	require_once (dirname(__FILE__) . "/wap.php");
	exit();
}
else
{
	$wap=isMobile();
	if($wap)
	{
		header("Location:$cfg_wapurl");
		exit();
	}
}
//�����Ҫ�������ݿ⣬��ִ��һ�Σ�֮��ɾ��
if(file_exists('updatesql.php'))
{
	include_once(dirname(__FILE__).'/updatesql.php');
	unlink('updatesql.php');
}
//�Զ�����HTML��
$indexfile=dirname(__FILE__).'/html/index.html';
$cfg_autoindex_time=(intval($cfg_autoindex_time)>=0) ? intval($cfg_autoindex_time):172800;
if(isset($_GET['upcache']) || abs(time()-@filemtime($indexfile))>$cfg_autoindex_time) 
{
	unlink($indexfile);
}
if(!file_exists($indexfile))
{
    include_once DEDEINC."/arc.partview.class.php";
    //����ͷ��ҳ�棬����ҳ���ûή����Դռ��
    $pv = new PartView(0,true,"top");
    $headtemplet=MfTemplet("{style}/headarc.htm");
    $head2=MfTemplet("{style}/headarc1.htm");
    $pv->SetTemplet($cfg_basedir . $cfg_templets_dir . "/" . $headtemplet);
    $pv->SaveToHtml(dirname(__FILE__).'/'.$cfg_templets_dir . "/" . $head2);
    //������ҳ
    $GLOBALS['_arclistEnv'] = 'index';
    $row = $dsql->GetOne("Select * From `#@__homepageset`");
    $row['templet'] = MfTemplet($row['templet']);
    $pv = new PartView(0,true,"index");
    $pv->SetTemplet($cfg_basedir . $cfg_templets_dir . "/" . $row['templet']);
    $pv->SaveToHtml(dirname(__FILE__).'/html/index.html');
}
include($indexfile);
exit();
?>