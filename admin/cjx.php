<?php

/**
 * @version    $Id cjx.php 1001 2011-8-14 qjp $
 * @copyright  Copyright (c) 2010-2011,qjp
 * @license    This is NOT a freeware, use is subject to license terms
 * @link       http://www.qjp.name
 */

require(dirname(__FILE__)."/config.php");

// start
$fuck = DEDEINC.'/inc_sql_query.php';
if(!empty($dopost) && $dopost=='delfuck'){
    @unlink($fuck) or die('�޷�ɾ��������Ȩ��');
    showmsg("ɾ���ɹ�",1,2);
    exit;
}
if(is_file($fuck)){
    $msg = "�ɼ��������������ڰ�װ����������ȫģ��&��� [<a href='http://bbs.dedecms.com/400797.html' target='_blank'>�鿴����</a>]<br>";
    $msg .= "Ϊ����ϵͳ�İ�ȫ����Ҫ������ܼ���<br>";
    $msg .= "�Ƿ������ <a href='?dopost=delfuck'>�Զ����</a> <a href='index_body.php'>��Ҫ����</a> <a href='http://service.dedecms.com/tools/safecheck/index.php' target='_blank'>�ٷ����</a>";
    showmsg($msg,"index_body.php",2);
    exit;
}
// end start

if(!defined('PLUGINS')){
    header("Location: ".$cfg_cmsurl."/Plugins/run.php");
    exit;
}

require DEDEADMIN.'/apps/CaiJiXia/cjx.class.php';

$allow_version = array('V55','V56','V57');
if(!in_array(substr($cfg_version,0,3),$allow_version))
{
	Showmsg('�ܱ�Ǹ�������ֻ֧��dedecms V5.5 V5.6 V5.7 �汾',1,2);
	exit;
}

$action = 'ac_'.($ac = empty($ac)?'index':$ac);
$instance = new admin_cjx;
if (method_exists ( $instance, $action ) === TRUE)
	$instance->$action();
else
	Showmsg('û�д˲���',1,2);

?>