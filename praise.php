<?php
require_once (dirname(__FILE__) . "/include/common.inc.php");
$id = preg_replace("[^0-9]", '', $id);
$row = $dsql->GetOne("SELECT id,tuijian,tuijianm,tuijianw,lasttuijian FROM `#@__arctype` WHERE id='$id'");
if($row)
{
	$pubdate=time();
	$lasttuijian=$row['lasttuijian'];
	$beginweek=mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y'));//���ܿ�ʼʱ���
	$beginmonth=mktime(0,0,0,date('m'),1,date('Y'));//���¿�ʼʱ���
	$booktuijian=1;
	$tuijian=$booktuijian+$row['tuijian'];
	if($beginmonth<=$lasttuijian)//�ж��Ƿ�ͬһ����
		$tuijianm=$booktuijian+$row['tuijianm'];//���Ƽ�
	else
		$tuijianm=$booktuijian;
	if($beginweek<=$lasttuijian)//�ж��Ƿ�ͬһ��
		$tuijianw=$booktuijian+$row['tuijianw'];//���Ƽ�
	else
		$tuijianw=$booktuijian;
	$dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET tuijian='$tuijian',tuijianm='$tuijianm',tuijianw='$tuijianw',lasttuijian='$pubdate' WHERE id='$id' ");
	echo "OK";
}
?>