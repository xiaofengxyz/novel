<?php
require_once (dirname(__FILE__) . "/include/common.inc.php");
$id = preg_replace("[^0-9]", '', $id);
$row = $dsql->GetOne("SELECT id,tuijian,tuijianm,tuijianw,lasttuijian FROM `#@__arctype` WHERE id='$id'");
if($row)
{
	$pubdate=time();
	$lasttuijian=$row['lasttuijian'];
	$beginweek=mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y'));//本周开始时间戳
	$beginmonth=mktime(0,0,0,date('m'),1,date('Y'));//本月开始时间戳
	$booktuijian=1;
	$tuijian=$booktuijian+$row['tuijian'];
	if($beginmonth<=$lasttuijian)//判断是否同一个月
		$tuijianm=$booktuijian+$row['tuijianm'];//月推荐
	else
		$tuijianm=$booktuijian;
	if($beginweek<=$lasttuijian)//判断是否同一周
		$tuijianw=$booktuijian+$row['tuijianw'];//周推荐
	else
		$tuijianw=$booktuijian;
	$dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET tuijian='$tuijian',tuijianm='$tuijianm',tuijianw='$tuijianw',lasttuijian='$pubdate' WHERE id='$id' ");
	echo "OK";
}
?>