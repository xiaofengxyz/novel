<?php
/**
 * �����ɼ�����
 *
 * @version        $Id: co_edit_text.php 1 14:31 2010��7��12��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('co_Export');
if(empty($dopost)) $dopost = '';

 function create_zip($files = array(),$destination = '',$overwrite = false) {
 //if the zip file already exists and overwrite is false, return false
 if(file_exists($destination) && !$overwrite) { return false; }
 //vars
 $valid_files = array();
 //if files were passed in...
 if(is_array($files)) {
 //cycle through each file
 foreach($files as $file) {
 //make sure the file exists
 if(file_exists($file)) {
 $valid_files[] = $file;
 }
 }
 }
 //if we have good files...
 if(count($valid_files)) {
 //create the archive
 $zip = new ZipArchive();
 if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
 return false;
 }
 //add the files
 foreach($valid_files as $file) {
 $zip->addFile($file,$file);
 }
 //debug
 //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
 //close the zip -- done!
 $zip->close();
 //check to make sure the file exists
 return file_exists($destination);
 }
 else
 {
 return false;
 }
 }
 
if($dopost!='done')
{
    require_once(DEDEADMIN."/inc/inc_catalog_options.php");
    $totalcc = $channelid = $usemore = 0;
    if(!empty($nid))
    {
        $mrow = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__co_htmls` WHERE nid='$nid' AND isdown='1' ");
        $totalcc = $mrow['dd'];
        $rrow = $dsql->GetOne("SELECT channelid,usemore FROM `#@__co_note` WHERE nid='$nid' ");
        $channelid = $rrow['channelid'];
        $usemore = $rrow['usemore'];
    }
    else
    {
        $mrow = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__co_htmls` WHERE isdown='1' ");
        $totalcc = $mrow['dd'];
    }
    include DedeInclude("templets/co_export.htm");
    exit();
}
else
{
    require_once(DEDEINC.'/dedecollection.class.php');
    $channelid = isset($channelid) && is_numeric($channelid) ? $channelid : 0;
    $typeid = isset($typeid) && is_numeric($typeid) ? $typeid : 0;
    $pageno = isset($pageno) && is_numeric($pageno) ? $pageno : 1;
    $startid = isset($startid) && is_numeric($startid) ? $startid : 0;
    $endid = isset($endid) && is_numeric($endid) ? $endid : 0;
    
    if(!isset($makehtml)) $makehtml = 0;
    if(!isset($onlytitle)) $onlytitle = 0;
    if(!isset($usetitle)) $usetitle = 0;
    if(!isset($autotype)) $autotype = 0;

    $co = new DedeCollection();
    $co->LoadNote($nid);
    $orderway = (($co->noteInfos['cosort']=='desc' || $co->noteInfos['cosort']=='asc') ? $co->noteInfos['cosort'] : 'desc');
    if($channelid==0 && $typeid==0)
    {
        ShowMsg('��ָ��Ĭ�ϵ�����Ŀ��Ƶ��ID��','javascript:;');
        exit();
    }
    if($channelid==0)
    {
        $row = $dsql->GetOne("SELECT ch.* FROM `#@__arctype` tp LEFT JOIN `#@__channeltype` ch ON ch.id=tp.channeltype WHERE tp.id='$typeid'; ");
    }
    else
    {
        $row = $dsql->GetOne("SELECT * FROM `#@__channeltype` WHERE id='$channelid'; ");
    }
    if(!is_array($row))
    {
        echo "�Ҳ���Ƶ������ģ����Ϣ���޷���ɲ�����";
        exit();
    }

    //�������򣬲�������ʱ��SQL���
    $channelid = $row['id'];
    $maintable = $row['maintable'];
    $addtable = $row['addtable'];
    if(empty($maintable)) $maintable = '#@__archives';
    if(empty($addtable))
    {
        echo "�Ҳ�����������Ϣ���޷���ɲ�����";
        exit();
    }
    $adminid = $cuserLogin->getUserID();

    //΢������
    $indexSqlTemplate = "INSERT INTO `#@__arctiny`(`arcrank`,`typeid`,`channel`,`senddate`,`sortrank`) VALUES ('$arcrank','@typeid@' ,'$channelid','@senddate@', '@sortrank@'); ";

    //������Ϣ����
    $mainSqlTemplate  = "INSERT INTO `$maintable`(id,typeid,sortrank,flag,ismake,channel,arcrank,click,money,title,shorttitle,color,writer,source,litpic,pubdate,senddate,mid,description,keywords)
               VALUES ('@aid@','@typeid@','@sortrank@','@flag@','-1','$channelid','$arcrank','@clickstart@','0','@title@','','','@writer@','@source@','@litpic@','@pubdate@','@senddate@','$adminid','',''); ";

    //���ɸ��ӱ�����SQL���
    $inadd_f = $inadd_v = '';
    $dtp = new DedeTagParse();
    $dtp->SetNameSpace('field','<','>');
    $dtp->LoadString($row['fieldset']);
    foreach($dtp->CTags as $ctag)
    {
        $tname = $ctag->GetTagName();
        $inadd_f .= ",`$tname`";
        $notsend = $ctag->GetAtt('notsend');
        $fieldtype = $ctag->GetAtt('type');
        if($notsend==1)
        {
            //�Բ�ͬ��������Ĭ��ֵ
            if($ctag->GetAtt('default')!='')
            {
                $dfvalue = $ctag->GetAtt('default');
            }
            else if($fieldtype=='int' || $fieldtype=='float' || $fieldtype=='number')
            {
                $dfvalue = '0';
            }
            else if($fieldtype=='dtime')
            {
                $dfvalue = time();
            }
            else
            {
                $dfvalue = '';
            }
            $inadd_v .= ",'$dfvalue'";
        }
        else
        {
            $inadd_v .= ",'@$tname@'";
        }
    }
    $addSqlTemplate = "INSERT INTO `{$addtable}`(`aid`,`typeid`{$inadd_f}) Values('@aid@','@typeid@'{$inadd_v})";

    //�������ݵ�SQL����
    $dtp = new DedeTagParse();
    $totalpage = $totalcc / $pagesize;
    $startdd = ($pageno-1) * $pagesize;
	$oldtypeid="";
    if(!empty($nid))
    {
        $straid="";
		$dsql->SetQuery("SELECT aid FROM `#@__co_htmls` WHERE nid=$nid AND isdown='1' ORDER BY aid $orderway LIMIT $startdd,$pagesize");
		$dsql->Execute();
		while($row = $dsql->GetObject())
		{
			$straid.=$row->aid;
			$straid=$straid.","; 
		}
		$straid=substr($straid,0,strlen($straid)-1);
		$dsql->SetQuery("SELECT * FROM `#@__co_htmls` WHERE aid in ($straid) ORDER BY aid $orderway ");
    }
    else
    {
        $dsql->SetQuery("SELECT * FROM `#@__co_htmls` WHERE isdown='1' ORDER BY aid $orderway LIMIT $startdd,$pagesize");
    }
	if($co_count=="")
		$co_count=0;
	if($oldtypeid=="")
		$oldtypeid=0;
    $dsql->Execute();
    while($row = $dsql->GetObject())
    {
        if(trim($row->result=='')) continue;
		$exid = $row->aid;
		
		//����ɼ�����С˵������Ŀ
		if($channelid=="99")
		{
			$title = $row->title;
			$dtp->LoadString($row->result);
			if(!is_array($dtp->CTags)) continue;

			//��ȡʱ��ͱ���
			$title = $row->title;
			$overdate="0";
			$isover="no";
			foreach ($dtp->CTags as $ctag)
			{
				$itemName = $ctag->GetAtt('name');
				if($itemName == 'title' && $usetitle==0)
				{
					$title = trim($ctag->GetInnerText());
					if($title=='')
					{
						$title = $row->title;
					}
				}
				else if($itemName == 'source')
				{
					$source = trim($ctag->GetInnerText());
				}
				else if($itemName == 'writer')
				{
					$writer = trim($ctag->GetInnerText());
				}
				else if($itemName == 'body')
				{
					$body = trim($ctag->GetInnerText());
				}
				else if($itemName == 'bookimg')
				{
					$bookimg = trim($ctag->GetInnerText());
					$bookimg = str_replace("{/dede:img}","",$bookimg);
					$bookimg = str_replace("{dede:pagestyle maxwidth='800' ddmaxwidth='240' row='3' col='3' value='2'/}","",$bookimg);
					$bookimg = str_replace("{dede:comments}","",$bookimg);
					$bookimg = str_replace("ͼ�����ͻ�ɼ�ʱ���ɴ������������ģ������������û�и���img������ʾ������Ч","",$bookimg);
					$bookimg = str_replace("{/dede:comments}","",$bookimg);
					$bookimg = str_replace("{dede:img ddimg='' text='ͼ 1'}","",$bookimg);
					$bookimg = trim($bookimg);
				}
				else if($itemName == 'pubdate')
				{
					$pubdate = trim($ctag->GetInnerText());
				}
				else if($itemName == 'bookurl')
				{
					$bookurl = trim($ctag->GetInnerText());
				}
				else if($itemName == 'copynid')
				{
					$copynid = trim($ctag->GetInnerText());
				}
				else if($itemName == 'overtag')
				{
					$overtag = trim($ctag->GetInnerText());
					$days=(time()-strtotime($pubdate))/86400;
					if($overtag=="�����" || $days>20)
					{
						$templist="{style}/list_article.htm";
						$overdate=$pubdate;
						$isover="yes";
					}
					else
					{
						$templist="{style}/list_article2.htm";
					}
				}
			}
			if($title=="" || $writer=="")
				continue;

			//����ظ�С˵
			if($onlytitle)
			{
				$testrow = $dsql->GetOne("SELECT COUNT(ID) AS dd FROM `#@__arctype` WHERE typename='$title' and zuozhe='$writer'");
				if($testrow['dd']>0)
				{
					$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isexport=1 where aid='$exid' ");
					echo "���ݿ��Ѵ��ڱ���Ϊ: {$title} ��С˵��������ֹ�˴˱������ݵ���<br />\r\n";
					continue;
				}
			}
			if(in_array($source,array("����ħ��","����С˵","ħ��С˵","���С˵","��������","����ħ��","�����ս","Զ����","�������","ħ��У԰","��������","��������","��������","ħ��С˵","���С˵","����С˵"))
				$retypeid=1;//����
			elseif(in_array($source,array("��������","����С˵","��������","����С˵","�ŵ�����","�������","��ķ���","�ִ�����"))
				$retypeid=2;//����
			elseif(in_array($source,array("��������","Ů��Ƶ��","����С˵","����С˵","ְҵС˵","��ս����","�ٳ�С˵","����С˵","��������","��������","�ഺУ԰","У԰С˵"))
				$retypeid=3;//����
			elseif(in_array($source,array("��ԽС˵","�Ŵ���Խ"))
				$retypeid=4;//��Խ
			elseif(in_array($source,array("�ƻ�����","�ƻ�С˵","�Ǽ�ս��","δ������"))
				$retypeid=5;//�ƻ�
			elseif(in_array($source,array("����С˵","�ŵ�����","����С˵","�ڰ�С˵","��ͳ����","�����似","��������"))
				$retypeid=6;//����
			elseif(in_array($source,array("��ʷ����","��ʷС˵","�Ŵ���ʷ","��ʷ�ܿ�","��ʷ","��ʷ����","�����ʷ","�ܿ���ʷ"))
				$retypeid=876;//��ʷ
			elseif(in_array($source,array("����С˵","����","����ս��","�ִ�ս��","����ս��"))
				$retypeid=877;//����
			elseif(in_array($source,array("�ֲ�С˵","����С˵","�ֲ�����","�ֲ�","����","�ֲ����","��̽����","�����"))
				$retypeid=878;//�ֲ�
			elseif(in_array($source,array("����С˵","��ϷС˵","����С˵","��������","��������"))
				$retypeid=879;//����
			elseif(in_array($source,array("����ͬ��","����С˵","ͬ��С˵","����С˵","Ӱ��С˵"))
				$retypeid=880;//ͬ��
			else
				$retypeid=7;//����
			$retyperow = $dsql->GetOne("SELECT * FROM `#@__arctype` WHERE id='$retypeid'; ");
			$retypedir = $retyperow['typedir'];//С˵�����dir
			
			$typedir = GetPinyin(stripslashes($title));
			$typedir = "/".$typedir;
			$typedir = preg_replace("#\/{1,}#", "/", $typedir);
			//����Ƿ���������С˵Ŀ¼���������Ŀ¼ƴ�����Զ������������
			$typedirrow = $dsql->GetOne("SELECT `typedir` FROM `#@__arctype` WHERE `typedir`='$typedir'");
			if($typedirrow)
			{
				for($ti=1;$ti<100;$ti++)
				{
					$typedir="SELECT `typedir` FROM `#@__arctype` WHERE `typedir`='".$typedir.$ti."'";
					$typedirrow = $dsql->GetOne($typedir);
					if(!$typedirrow)
					{
						$typedir = $typedir.$ti;
						break;
					}
				}
			}			
			$addSqlType = "insert into `#@__arctype` (`reid`, `topid`, `sortrank`, `typename`, `typedir`, `isdefault`, `defaultname`, `issend`, `channeltype`, `maxpage`, `ispart`, `corank`, `tempindex`, `templist`, `temparticle`, `namerule`, `namerule2`, `modname`, `keywords`, `seotitle`, `moresite`, `sitepath`, `siteurl`, `ishidden`, `cross`, `crossid`, `content`, `smalltypes`, `bookclick`, `zuozhe`, `startdate`, `overdate`, `booksize`, `downloadurl`,`description`,`typeimg`) values('$retypeid','$retypeid','50','$title','$typedir','-1','index.html','1','1','-1','0','0','{style}/index_article.htm','$templist','{style}/article_article.htm','{typedir}/{Y}/{M}{D}/{aid}.html','','default','','','0','$retypedir','','0','0','','','','0','$writer','','$overdate','0','','$body','$bookimg')";
			$dsql->ExecuteNoneQuery($addSqlType);
			
			//����С˵�½ڲɼ�����
			$bookid = $dsql->GetLastID();
			$row = $dsql->GetOne("Select * From `#@__co_note` where nid='$copynid'");
			if($row)
			{
				foreach($row as $k=>$v)
				{
					if(!isset($$k))
					{
						$$k = addslashes($v);
					}
				}
				$usemore = (empty($usemore) ? '0' : $usemore);
				//�Ѿ��걾�ķ���һ��ɼ���ÿ3��һ������̫��������
				if($isover=="yes")
				{
					/* $row1 = $dsql->GetOne("Select * From `#@__co_note` where renid='".$copynid."-1' and booksum<3 ");
					if($row1)
					{
						$co_nid=$row1['nid'];
						$booksum=$row1['booksum']+1;
						$notename=$row1['notename']."-".$title;
						$co_note="[(#)=>".$bookurl."; (*)=>1-1; typeid=>".$bookid."]{/dede:batchrule}";
						$cosql="update `#@__co_note` set `listconfig`=replace(replace(listconfig,'{/dede:batchrule}','".$co_note."'),'".$row1['notename']."','".$notename."'),booksum='$booksum',notename='$notename',typeid='1' where nid='$co_nid'";
					}
					else
					{
						$renid=$copynid."-1";
						$notename=$row['notename']."-".$title;
						$listconfig=$row['listconfig'];
						$co_note="{dede:batchrule}[(#)=>".$bookurl."; (*)=>1-1; typeid=>".$bookid."]{/dede:batchrule}";
						$listconfig = str_replace($row['notename'],$notename,str_replace(substr($listconfig,stripos($listconfig,'{dede:batchrule}'),stripos($listconfig,'{/dede:batchrule}')-stripos($listconfig,'{dede:batchrule}')+17),$co_note,$listconfig));
						$cosql = " INSERT INTO `#@__co_note`(`channelid`,`notename`,`sourcelang`,`uptime`,`cotime`,`pnum`,`isok`,`listconfig`,`itemconfig`,`usemore`,booksum,renid,typeid)
						   VALUES ('1','$notename','$sourcelang','".time()."','0','0','1','$listconfig','$itemconfig','1','1','$renid','1'); ";
					} */
					$renid=$copynid."-1";
					$notename=$row['notename']."-".$title;
					$listconfig=$row['listconfig'];
					$co_note="{dede:batchrule}[(#)=>".$bookurl."; (*)=>1-1; typeid=>".$bookid."]{/dede:batchrule}";
					$listconfig = str_replace($row['notename'],$notename,str_replace(substr($listconfig,stripos($listconfig,'{dede:batchrule}'),stripos($listconfig,'{/dede:batchrule}')-stripos($listconfig,'{dede:batchrule}')+17),$co_note,$listconfig));
					$cosql = " INSERT INTO `#@__co_note`(`channelid`,`notename`,`sourcelang`,`uptime`,`cotime`,`pnum`,`isok`,`listconfig`,`itemconfig`,`usemore`,booksum,renid,typeid)
					   VALUES ('1','$notename','$sourcelang','".time()."','0','0','1','$listconfig','$itemconfig','1','1','$renid','1'); ";
				}
				else
				{
					/* $row1 = $dsql->GetOne("Select * From `#@__co_note` where renid='".$copynid."-2' and booksum<3 ");
					if($row1)
					{
						$co_nid=$row1['nid'];
						$booksum=$row1['booksum']+1;
						$notename=$row1['notename']."+".$title;
						$co_note="[(#)=>".$bookurl."; (*)=>1-1; typeid=>".$bookid."]{/dede:batchrule}";
						$cosql="update `#@__co_note` set `listconfig`=replace(replace(listconfig,'{/dede:batchrule}','".$co_note."'),'".$row1['notename']."','".$notename."'),booksum='$booksum',notename='$notename' where nid='$co_nid'";
					}
					else
					{
						$renid=$copynid."-2";
						$notename=$row['notename']."+".$title;
						$listconfig=$row['listconfig'];
						$co_note="{dede:batchrule}[(#)=>".$bookurl."; (*)=>1-1; typeid=>".$bookid."]{/dede:batchrule}";
						$listconfig = str_replace($row['notename'],$notename,str_replace(substr($listconfig,stripos($listconfig,'{dede:batchrule}'),stripos($listconfig,'{/dede:batchrule}')-stripos($listconfig,'{dede:batchrule}')+17),$co_note,$listconfig));
						$cosql = " INSERT INTO `#@__co_note`(`channelid`,`notename`,`sourcelang`,`uptime`,`cotime`,`pnum`,`isok`,`listconfig`,`itemconfig`,`usemore`,booksum,renid,typeid)
						   VALUES ('1','$notename','$sourcelang','".time()."','0','0','1','$listconfig','$itemconfig','1','1','$renid','1'); ";
					} */
					$renid=$copynid."-2";
					$notename=$row['notename']."+".$title;
					$listconfig=$row['listconfig'];
					$co_note="{dede:batchrule}[(#)=>".$bookurl."; (*)=>1-1; typeid=>".$bookid."]{/dede:batchrule}";
					$listconfig = str_replace($row['notename'],$notename,str_replace(substr($listconfig,stripos($listconfig,'{dede:batchrule}'),stripos($listconfig,'{/dede:batchrule}')-stripos($listconfig,'{dede:batchrule}')+17),$co_note,$listconfig));
					$cosql = " INSERT INTO `#@__co_note`(`channelid`,`notename`,`sourcelang`,`uptime`,`cotime`,`pnum`,`isok`,`listconfig`,`itemconfig`,`usemore`,booksum,renid,typeid)
					   VALUES ('1','$notename','$sourcelang','".time()."','0','0','1','$listconfig','$itemconfig','1','1','$renid','1'); ";
				}
				$dsql->ExecuteNoneQuery($cosql);
			}
			else
			{
				$dsql->ExecuteNoneQuery("Delete From `#@__arctype` where id='".$bookid."'");
				ShowMsg("�½ڲɼ����򲻴��ڣ�","javascript:;");
				exit();
			}
			
			//������С˵Ŀ¼ʱ���û�ж�Ӧ��������Ʒ�������Զ�����һ��������reid��topidҪ����ʵ������趨��Ŀǰ����45
			$row = $dsql->GetOne("SELECT `typedir` FROM `#@__arctype` WHERE `typename`='$writer'");
			if(!$row)
			{
				$zuozhedir = GetPinyin(stripslashes($writer));
				$zuozhedir = "/".$zuozhedir;
				$zuozhedir = preg_replace("#\/{1,}#", "/", $zuozhedir);
				$row = $dsql->GetOne("SELECT `typedir` FROM `#@__arctype` WHERE `typedir`='$zuozhedir'");
				if($row)
				{
					for($ti=1;$ti<100;$ti++)
					{
						$tsql="SELECT `typedir` FROM `#@__arctype` WHERE `typedir`='".$zuozhedir.$ti."'";
						$row = $dsql->GetOne($tsql);
						if(!$row)
						{
							$zuozhedir = $zuozhedir.$ti;
							break;
						}
					}
				}
				$zuozhe_in_query = "insert into `#@__arctype` (`reid`, `topid`, `sortrank`, `typename`, `typedir`, `isdefault`, `defaultname`, `issend`, `channeltype`, `maxpage`, `ispart`, `corank`, `tempindex`, `templist`, `temparticle`, `namerule`, `namerule2`, `modname`, `description`, `keywords`, `seotitle`, `moresite`, `sitepath`, `siteurl`, `ishidden`, `cross`, `crossid`, `content`, `smalltypes`, `bookclick`, `typeimg`, `zuozhe`, `startdate`, `overdate`, `booksize`, `downloadurl`) values('45','45','50','$writer','$zuozhedir','-1','index.html','0','1','-1','0','0','{style}/index_article.htm','{style}/list_article3.htm','{style}/article_article.htm','{typedir}/{Y}/{M}{D}/{aid}.html','','default','�������������".$writer."��С˵��Ʒ����������Ķ��Ĺ����з������ǵ�ת�������⣬�뼰ʱ��������ϵ���ر����ѵ��ǣ�С˵��Ʒһ�㶼�Ǹ�������д����ʱ��˼����ʽ��������ģ�������鹹�ĳɷݱȽ϶࣬����ģ�£�','','','0','/zuopinji','','1','0','','�������������".$writer."��С˵��Ʒ����������Ķ��Ĺ����з������ǵ�ת�������⣬�뼰ʱ��������ϵ���ر����ѵ��ǣ�С˵��Ʒһ�㶼�Ǹ�������д����ʱ��˼����ʽ��������ģ�������鹹�ĳɷݱȽ϶࣬����ģ�£�','','0','/images/jipin-default.jpg','','','0','0','')";
				$dsql->ExecuteNoneQuery($zuozhe_in_query);
				//�����ĵ��ؼ�����
				$row = $dsql->GetOne("SELECT `keyword` FROM `#@__keywords` WHERE `keyword`='$writer'");
				if(!$row)
				{
					$keyword_in_query = "insert into `#@__keywords` (`keyword`, `rank`, `sta`, `rpurl`) values('$writer','30','1','$zuozhedir/')";
					$dsql->ExecuteNoneQuery($keyword_in_query);
				}
			}
		}
		else
		{
			//$addSqlTemplate,$mainSqlTemplate,$indexSqlTemplate
			$clickstart=mt_rand(21,51);
			$ntypeid = ($autotype==1 && $row->typeid != 0) ? $row->typeid : $typeid;
			$indexSql = str_replace('@typeid@', $ntypeid, $indexSqlTemplate);
			$mainSql = str_replace('@typeid@', $ntypeid, $mainSqlTemplate);
			$mainSql = str_replace('@clickstart@', $clickstart, $mainSql);
			$addSql = str_replace('@typeid@', $ntypeid, $addSqlTemplate);
			$dtp->LoadString($row->result);
			$exid = $row->aid;
			if(!is_array($dtp->CTags)) continue;

			//��ȡʱ��ͱ���
			$pubdate = $sortrank = time();
			$title = $row->title;
			$litpic = '';
			foreach ($dtp->CTags as $ctag)
			{
				$itemName = $ctag->GetAtt('name');
				if($itemName == 'title' && $usetitle==0)
				{
					$title = trim($ctag->GetInnerText());
					if($title=='')
					{
						$title = $row->title;
					}
				}
				else if($itemName == 'pubdate')
				{
					$pubdate = trim($ctag->GetInnerText());
					if(preg_match("#[^0-9]#", $pubdate))
					{
						$pubdate = $sortrank = GetMkTime($pubdate);
					}
					else
					{
						$pubdate = $sortrank = time();
					}
				}
				else if($itemName == 'litpic')
				{
					$litpic = trim($ctag->GetInnerText());
				}
			}

			//����ظ�����
			$title = addslashes($title);
			$title1 = cn_substr($title, 60);
			if($onlytitle)
			{
				$testrow = $dsql->GetOne("SELECT COUNT(ID) AS dd FROM `$maintable` WHERE title LIKE '$title1%' and typeid='$ntypeid'");
				if($testrow['dd']>0)
				{
					$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isexport=1 where aid='$exid' ");
					echo "���ݿ��Ѵ��ڱ���Ϊ: {$title} ���ĵ���������ֹ�˴˱������ݵ���<br />\r\n";
					continue;
				}
			}

			//�滻�̶�����Ŀ
			$senddate = time();
			$flag = '';
			if($litpic!='') $flag = 'p';

			//����Ƽ�
			if($randcc>0)
			{
				$rflag = mt_rand(1, $randcc);
				if($rflag==$randcc)
				{
					$flag = ($flag=='' ? 'c' : $flag.',c');
				}
			}
			$indexSql = str_replace('@senddate@', $senddate, $indexSql);
			$indexSql = str_replace('@sortrank@', $sortrank, $indexSql);
			$mainSql = str_replace('@flag@', $flag, $mainSql);
			$mainSql = str_replace('@sortrank@', $sortrank, $mainSql);
			$mainSql = str_replace('@pubdate@', $pubdate, $mainSql);
			$mainSql = str_replace('@senddate@', $senddate, $mainSql);
			$mainSql = str_replace('@title@', cn_substr($title, 100), $mainSql);
			$addSql = str_replace('@sortrank@', $sortrank, $addSql);
			$addSql = str_replace('@senddate@', $senddate, $addSql);

			//�滻ģ����������ֶ�
			foreach($dtp->CTags as $ctag)
			{
				if($ctag->GetName()!='field')
				{
					continue;
				}
				$itemname = $ctag->GetAtt('name');
				$itemvalue = addslashes(trim($ctag->GetInnerText()));
				$mainSql = str_replace("@$itemname@", $itemvalue, $mainSql);
				$addSql = str_replace("@$itemname@", $itemvalue, $addSql);
			}
			
			//�������ݿ�
			$rs = $dsql->ExecuteNoneQuery($indexSql);
			if($rs)
			{
				$aid = $dsql->GetLastID();
				$mainSql = str_replace('@aid@', $aid, $mainSql);
				$addSql = str_replace('@aid@', $aid, $addSql);
				$mainSql = preg_replace("#@([a-z0-9]{1,})@#", '', $mainSql);
				$addSql = preg_replace("#@([a-z0-9]{1,})@#", '', $addSql);
				$rs = $dsql->ExecuteNoneQuery($mainSql);
				if(!$rs)
				{
					echo "���� '$title' ʱ����".$dsql->GetError()."<br />";
					$dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$aid' ");
				}
				else
				{
					$rs = $dsql->ExecuteNoneQuery($addSql);
					if(!$rs)
					{
						echo "���� '$title' ʱ����".$dsql->GetError()."<br />";
						$dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE id='$aid' ");
						$dsql->ExecuteNoneQuery("DELETE FROM `$maintable` WHERE id='$aid' ");
					}
				}
				if($channelid=="1")
				{
					$row = $dsql->GetOne("SELECT a.id,a.title,a.click,a.typeid,b.body,c.typename,c.zuozhe,c.bookclick,c.booksize,c.templist FROM `$maintable` a left join `{$addtable}` b on(a.id=b.aid) left join `#@__arctype` c on(a.typeid=c.id) WHERE a.id='$aid'; ");
					if($row)
					{
						$startaid=$row['id'];
						$txt_typeid=$row['typeid'];
						$templist=$row['templist'];
						//����С˵���±�ʶ
						if($oldtypeid!=$txt_typeid)
						{
							$dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET startaid='$startaid',bookclick='0',booksize='0' WHERE id='$txt_typeid' ");
						}
						//һ���Բɼ��౾���ʱ�򣬵�������ID����ͬʱ,�Զ�ѹ����һ����Ϊzip��
						if($oldtypeid!=0 && $oldtypeid!=$txt_typeid)
						{
							$rowt = $dsql->GetOne("SELECT * FROM `#@__arctype` WHERE id='$oldtypeid'; ");
							if($row)
							{
								$old_templist=$rowt['templist'];
								$txt_filename=$rowt['zuozhe']."-".$rowt['typename'];
								$txt_comtens="\r\n"."���������ɡ�".$cfg_indexname."����".str_replace("http://","",$cfg_basehost)."�������ṩ���أ���վֻ�ṩ�ÿ���ȫ��С˵ȫ��txt���أ�";
								$file = fopen("$txt_filename.txt","ab");
								fwrite($file,$txt_comtens);
								fclose($file);
								$filename=$txt_filename.".txt";
								$files=array($filename);
								$zipname="../download/".$txt_filename.".zip";
								if(file_exists($zipname))
								{
									unlink($zipname);
								}
								create_zip($files,$zipname, true);
								if($old_templist=="{style}/list_article.htm")
									unlink($filename);
								else
									rename($filename,"../plus/txt/$filename");
							}
							$dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET downloadurl='/download/$txt_filename.zip' WHERE id='$oldtypeid' ");
						}
						$txt_filename=$row['zuozhe']."-".$row['typename'];
						$book_name=$row['typename'];
						$txt_title=$row['title'];
						//echo "<font size='2px'>��".$row['typename']."-".$txt_title."��  </font>";
						$txt_body=$txt_title."\r\n".$row['body'];
						$txt_click=$row['click']+$row['bookclick'];
						$txt_body=str_replace("&nbsp;"," ",str_replace("<br />","\r\n",str_replace("<br>","\r\n",trim($txt_body))));
						$txt_body=str_replace("\r\n\r\n","\r\n",str_replace("\r\n\r\n\r\n","\r\n",str_replace("\r\n\r\n\r\n\r\n","\r\n",$txt_body)));
						$acrlen=round(strlen($txt_body)/1.8);
						$booksize=$acrlen+$row['booksize'];
						$dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET bookclick='$txt_click',booksize='$booksize' WHERE id='$txt_typeid' ");
						$txt_comtens=$txt_body."\r\n"."\r\n";
						$file = fopen("$txt_filename.txt","ab");
						fwrite($file,$txt_comtens);
						fclose($file);					
						$oldtypeid=$txt_typeid;
					}
				}
			}
		}
        $dsql->ExecuteNoneQuery("UPDATE `#@__co_htmls` SET isexport=1 WHERE aid='$exid' ");
    }

    //����Ƿ���ɻ��������
    if($totalpage <= $pageno)
    {
        if($channelid>0 && $makehtml==1)
        {
            if( $autotype==0 && !empty($nid) )
            {
                $mhtml = "makehtml_archives_action.php?typeid=$typeid&startid=$startid&endid=$endid&pagesize=20";
                ShowMsg("������ݵ��룬׼�������ĵ�HTML...",$mhtml);
                exit();
            }
            else
            {
                ShowMsg("����������ݵ��룬���ֹ�����HTML��","javascript:;");
                exit();
            }
        }
        else
        {
            if($channelid=="1")
			{
				$txt_comtens="\r\n"."���������ɡ�".$cfg_indexname."����".str_replace("http://","",$cfg_basehost)."�������ṩ���أ���վֻ�ṩ�ÿ���ȫ��С˵ȫ��txt���أ�";
				$file = fopen("$txt_filename.txt","ab");
				fwrite($file,$txt_comtens);
				fclose($file);
				$filename=$txt_filename.".txt";
				$files=array($filename);
				$zipname="../download/".$txt_filename.".zip";
				if(file_exists($zipname))
				{
					unlink($zipname);
				}
				create_zip($files,$zipname, true);
				if($templist=="{style}/list_article.htm")
				{
					unlink($filename);
					$dsql->ExecuteNoneQuery("UPDATE `#@__co_note` SET typeid='0' WHERE nid='$nid' ");
				}
				else
					rename($filename,"../plus/txt/$filename");
				$dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET downloadurl='/download/$txt_filename.zip' WHERE id='$txt_typeid' ");
			}
			ShowMsg("����������ݵ��룡","javascript:;");
            exit();
        }
    }
    else
    {
        if($totalpage>0)
        {
            $rs = substr(($pageno / $totalpage * 100), 0, 2);
        }
        else
        {
            $rs = 100;
        }
        $pageno++;
        $gourl = "co_export.php?dopost=done&nid=$nid&totalcc=$totalcc&channelid=$channelid&pageno=$pageno";
        $gourl .= "&nid=$nid&typeid=$typeid&autotype=$autotype&arcrank=$arcrank&pagesize=$pagesize&randcc=$randcc";
        $gourl .= "&startid=$startid&endid=$endid&onlytitle=$onlytitle&usetitle=$usetitle&makehtml=$makehtml&co_count=1&oldtypeid=$txt_typeid";
        ShowMsg("Ŀǰ����$book_name ������� {$rs}% ���룬����������...",$gourl,'',500);
        exit();
    }
}