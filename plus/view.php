<?php
/**
 *
 * 关于文章权限设置的说明
 * 文章权限设置限制形式如下：
 * 如果指定了会员等级，那么必须到达这个等级才能浏览
 * 如果指定了金币，浏览时会扣指点的点数，并保存记录到用户业务记录中
 * 如果两者同时指定，那么必须同时满足两个条件
 *
 * @version        $Id: view.php 1 15:38 2010年7月8日Z tianya $
 * @package        DedeCMS.Site
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/../include/common.inc.php");
require_once(DEDEINC.'/arc.archives.class.php');

//$t1 = ExecTime();

if(empty($okview)) $okview = '';
if(isset($arcID)) $aid = $arcID;
if(!isset($dopost)) $dopost = '';

$arcID = $aid = (isset($aid) && is_numeric($aid)) ? $aid : 0;
if($aid==0)
{
	@header("http/1.1 404 not found");
	@header("status: 404 not found");
	include("../404.html");
	exit(); 
}


$row2 = $dsql->GetOne("SELECT id FROM `#@__arctype` WHERE typedir='/$tdir'");
if(!$row2)
{
	@header("http/1.1 404 not found");
	@header("status: 404 not found");
	include("../404.html");
	exit(); 
}
$atypeid=$row2['id'];
//更新小说总点击次数
$row1 = $dsql->GetOne("SELECT typeid FROM `#@__archives` WHERE id='$aid' and typeid='$atypeid';");
if($row1)
{
	/* $typeid = $row1['typeid'];
	if(intval($cfg_weekstart)!='2') $beginweek=mktime(0,0,0,date('m'),date('d')-date('w'),date('Y'));//本周开始时间戳,星期日~星期六为一周。
	else $beginweek=mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y'));//本周开始时间戳,星期一~星期日为一周。
	$beginmonth=mktime(0,0,0,date('m'),1,date('Y'));//本月开始时间戳

	$clickrow=$dsql->GetOne("SELECT MAX(lastclick) as lastclick FROM `#@__arctype` WHERE reid NOT IN (0,45)");
	$lastclickall=$clickrow['lastclick'];
	$clicksql="bookclick=bookclick+1";
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
	$dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET $clicksql where id='$typeid'"); */
}
else
{
	@header("http/1.1 404 not found");
	@header("status: 404 not found");
	include("../404.html");
	exit(); 
}
//更新章节点击次数
//$dsql->ExecuteNoneQuery(" UPDATE `#@__archives` SET click=click+1 WHERE id='$aid' ");

//手机来访的直接转到手机版
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
	$wapurl=$cfg_wapurl."/wap.php?action=article&id=$aid";
	header("Location:$wapurl");
	exit();
}
$arc = new Archives($aid);
if($arc->IsError) ParamError();


//检查阅读权限
$needMoney = $arc->Fields['money'];
$needRank = $arc->Fields['arcrank'];

require_once(DEDEINC.'/memberlogin.class.php');
$cfg_ml = new MemberLogin();

if($needRank < 0 && $arc->Fields['mid'] != $cfg_ml->M_ID)
{
    ShowMsg('文章尚未审核,非作者本人无权查看!', 'javascript:;');
    exit();
}

//设置了权限限制的文章
//arctitle msgtitle moremsg
if($needMoney>0 || $needRank>1)
{
    $arctitle = $arc->Fields['title'];
    /*
    $arclink = GetFileUrl($arc->ArcID,$arc->Fields["typeid"],$arc->Fields["senddate"],
                             $arc->Fields["title"],$arc->Fields["ismake"],$arc->Fields["arcrank"]);
    */                        
    $arclink = $cfg_phpurl.'/view.php?aid='.$arc->ArcID;                         
    $arcLinktitle = "<a href=\"{$arclink}\"><u>".$arctitle."</u></a>";
    
    $description =  $arc->Fields["description"];
    $pubdate = GetDateTimeMk($arc->Fields["pubdate"]);
    
    //会员级别不足
    if(($needRank>1 && $cfg_ml->M_Rank < $needRank && $arc->Fields['mid']!=$cfg_ml->M_ID))
    {
        $dsql->Execute('me' , "SELECT * FROM `#@__arcrank` ");
        while($row = $dsql->GetObject('me'))
        {
            $memberTypes[$row->rank] = $row->membername;
        }
        $memberTypes[0] = "游客或没权限会员";
        $msgtitle = "你没有权限浏览文档：{$arctitle} ！";
        $moremsg = "这篇文档需要 <font color='red'>".$memberTypes[$needRank]."</font> 才能访问，你目前是：<font color='red'>".$memberTypes[$cfg_ml->M_Rank]."</font> ！";
        include_once(DEDETEMPLATE.'/plus/view_msg.htm');
        exit();
    }

    //需要金币的情况
    if($needMoney > 0  && $arc->Fields['mid'] != $cfg_ml->M_ID)
    {
        $sql = "SELECT aid,money FROM `#@__member_operation` WHERE buyid='ARCHIVE".$aid."' AND mid='".$cfg_ml->M_ID."'";
        $row = $dsql->GetOne($sql);
        //未购买过此文章
        if(!is_array($row))
        {
            if($cfg_ml->M_Money=='' || $needMoney > $cfg_ml->M_Money)
            {
                    $msgtitle = "你没有权限浏览文档：{$arctitle} ！";
                    $moremsg = "这篇文档需要 <font color='red'>".$needMoney." 金币</font> 才能访问，你目前拥有金币：<font color='red'>".$cfg_ml->M_Money." 个</font> ！";
                    include_once(DEDETEMPLATE.'/plus/view_msg.htm');
                    $arc->Close();
                    exit();
            }
            else
            {
                if($dopost=='buy')
                {
                    $inquery = "INSERT INTO `#@__member_operation`(mid,oldinfo,money,mtime,buyid,product,pname)
                              VALUES ('".$cfg_ml->M_ID."','$arctitle','$needMoney','".time()."', 'ARCHIVE".$aid."', 'archive',''); ";
                    if($dsql->ExecuteNoneQuery($inquery))
                    {
                        $inquery = "UPDATE `#@__member` SET money=money-$needMoney WHERE mid='".$cfg_ml->M_ID."'";
                        if(!$dsql->ExecuteNoneQuery($inquery))
                        {
                            showmsg('购买失败, 请返回', -1);
                            exit;
                        }
                        #api{{
                        if(defined('UC_APPID'))
                        {
                            include_once DEDEROOT.'/api/uc.func.php';
                            $row = $dsql->GetOne("SELECT `scores`,`userid` FROM `#@__member` WHERE `mid`='".$cfg_ml->M_ID."'");
                            uc_credit_note($row['userid'],-$needMoney,'money');
                        }
                        #/aip}}
    
                        showmsg('购买成功，购买扣点不会重扣金币，谢谢！', '/plus/view.php?aid='.$aid);
                        exit;
                    } else {
                        showmsg('购买失败, 请返回', -1);
                        exit;
                    }
                }
                
                $msgtitle = "扣金币购买阅读！";
                $moremsg = "阅读该文档内容需要付费！<br>这篇文档需要 <font color='red'>".$needMoney." 金币</font> 才能访问，你目前拥有金币 <font color='red'>".$cfg_ml->M_Money." </font>个！<br>确认阅读请点 [<a href='/plus/view.php?aid=".$aid."&dopost=buy' target='_blank'>确认付点阅读</a>]" ;
                include_once($cfg_basedir.$cfg_templets_dir."/plus/view_msg.htm");
                $arc->Close();
                exit();
            }
        }
    }//金币处理付处理
}

$arc->Display();