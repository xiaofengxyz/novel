<?php
/**
 * ��Ŀ���
 *
 * @version        $Id: catalog_add.php 1 14:31 2010��7��12��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/typelink.class.php");

if(empty($listtype)) $listtype='';
if(empty($dopost)) $dopost = '';
if(empty($upinyin)) $upinyin = 0;
if(empty($channelid)) $channelid = 1;
if(isset($channeltype)) $channelid = $channeltype;

$id = empty($id) ? 0 :intval($id);
$reid = empty($reid) ? 0 :intval($reid);
$nid = 'article';
if($typeimg=="" || empty($typeimg)) $typeimg = "/images/jipin-default.jpg";
if($id==0 && $reid==0)
{
    CheckPurview('t_New');
}
else
{
    $checkID = empty($id) ? $reid : $id;
    CheckPurview('t_AccNew');
    CheckCatalog($checkID, '����Ȩ�ڱ���Ŀ�´������࣡');
}

if(empty($myrow)) $myrow = array();

$dsql->SetQuery("SELECT id,typename,nid FROM `#@__channeltype` WHERE id<>-1 AND isshow=1 ORDER BY id");
$dsql->Execute();
while($row=$dsql->GetObject())
{
    $channelArray[$row->id]['typename'] = $row->typename;
    $channelArray[$row->id]['nid'] = $row->nid;
    if($row->id==$channelid)
    {
        $nid = $row->nid;
    }
}
if($dopost=='quick')
{
    $tl = new TypeLink(0);
    $typeOptions = $tl->GetOptionArray(0,0,$channelid);
    include DedeInclude('templets/catalog_add_quick.htm');
    exit();
}
/*---------------------
function action_savequick(){ }
---------------------*/
else if($dopost=='savequick')
{
    if(!isset($savetype)) $savetype = '';
    $isdefault = isset($isdefault)? $isdefault : 0;
    $tempindex = "{style}/index_{$nid}.htm";
    $templist = "{style}/list_{$nid}.htm";
    $temparticle = "{style}/article_{$nid}.htm";
    $queryTemplate = "INSERT INTO `#@__arctype`(reid,topid,sortrank,typename,typeimg,zuozhe,startdate,overdate,booksize,bookclick,downloadurl,typedir,isdefault,defaultname,issend,channeltype,    tempindex,templist,temparticle,modname,namerule,namerule2,ispart,corank,description,keywords,seotitle,moresite,siteurl,sitepath,ishidden,`cross`,`crossid`,`content`,`smalltypes`)    VALUES('~reid~','~topid~','~rank~','~typename~','~typeimg~','~zuozhe~','~startdate~','~overdate~','~booksize~','~bookclick~','~downloadurl~','~typedir~','$isdefault','$defaultname','$issend','$channeltype',
    '$tempindex','$templist','$temparticle','default','$namerule','$namerule2','0','0','','','~typename~','0','','','0','0','0','','')";
    
    if (empty($savetype))
    {
        foreach($_POST as $k=>$v)
        {
            if(preg_match("#^posttype#", $k))
            {
                $k = str_replace('posttype', '', $k);
            }
            else
            {
                continue;
            }
            $rank = ${'rank'.$k};
            $toptypename = trim(${'toptype'.$k});
            $sontype = trim(${'sontype'.$k});
            $toptypedir = GetPinyin(stripslashes($toptypename));
            $toptypedir = $referpath=='parent' ? $nextdir.'/'.$toptypedir : '/'.$toptypedir;
			$row = $dsql->GetOne("SELECT `typedir` FROM `#@__arctype` WHERE `typedir`='$toptypedir'");
			if($row)
			{
				for($ti=1;$ti<100;$ti++)
				{
					$tsql="SELECT `typedir` FROM `#@__arctype` WHERE `typedir`='".$toptypedir.$ti."'";
					$row = $dsql->GetOne($tsql);
					if(!$row)
					{
						$toptypedir = $toptypedir.$ti;
						break;
					}
				}
			}
            if(empty($toptypename))
            {
                continue;
            }
            $sql = str_replace('~reid~','0',$queryTemplate);
            $sql = str_replace('~topid~','0',$sql);
            $sql = str_replace('~rank~',$rank,$sql);
            $sql = str_replace('~typename~',$toptypename,$sql);
            $sql = str_replace('~typedir~',$toptypedir,$sql);
            $dsql->ExecuteNoneQuery($sql);
            $tid = $dsql->GetLastID();
            if($tid>0 && $sontype!='')
            {
                $sontypes = explode(',',$sontype);
                foreach($sontypes as $k=>$v)
                {
                    $v = trim($v);
                    if($v=='')
                    {
                        continue;
                    }
                    $typedir = $toptypedir.'/'.GetPinyin(stripslashes($v));
					$row = $dsql->GetOne("SELECT `typedir` FROM `#@__arctype` WHERE `typedir`='$typedir'");
					if($row)
					{
						for($ti=1;$ti<100;$ti++)
						{
							$tsql="SELECT `typedir` FROM `#@__arctype` WHERE `typedir`='".$typedir.$ti."'";
							$row = $dsql->GetOne($tsql);
							if(!$row)
							{
								$typedir = $typedir.$ti;
								break;
							}
						}
					}
                    $sql = str_replace('~reid~',$tid,$queryTemplate);
                    $sql = str_replace('~topid~',$tid,$sql);
                    $sql = str_replace('~rank~',$k,$sql);
                    $sql = str_replace('~typename~',$v,$sql);
                    $sql = str_replace('~typedir~',$typedir,$sql);
                    $dsql->ExecuteNoneQuery($sql);
                }
            }
        }
    } else {
    

        $row = $dsql->GetOne("SELECT `typedir` FROM `#@__arctype` WHERE `id`={$reid}");
        foreach($_POST as $k=>$v)
        {
            if(preg_match("#^posttype#", $k))
            {
                $k = str_replace('posttype', '', $k);
            }
            else
            {
                continue;
            }
            $rank = ${'rank'.$k};
            $toptypename = trim(${'reltype'.$k});
            $toptypedir = GetPinyin(stripslashes($toptypename));
            switch ($referpath) {
                case 'parent':
                    $toptypedir = $nextdir.'/'.$toptypedir;
                    break;
                case 'typepath':
                    $toptypedir = isset($row['typedir'])? $row['typedir'].'/'.$toptypedir : '/'.$toptypedir;
                    break; 
                default:
                    $toptypedir = '/'.$toptypedir;
                    break;
            }
            $row = $dsql->GetOne("SELECT `typedir` FROM `#@__arctype` WHERE `typedir`='$toptypedir'");
			if($row)
			{
				for($ti=1;$ti<100;$ti++)
				{
					$tsql="SELECT `typedir` FROM `#@__arctype` WHERE `typedir`='".$toptypedir.$ti."'";
					$row = $dsql->GetOne($tsql);
					if(!$row)
					{
						$toptypedir = $toptypedir.$ti;
						break;
					}
				}
			}
            if(empty($toptypename))
            {
                continue;
            }
            $sql = str_replace('~reid~', $reid, $queryTemplate);
            $sql = str_replace('~topid~', $reid, $sql);
            $sql = str_replace('~rank~', $rank, $sql);
            $sql = str_replace('~typename~', $toptypename, $sql);
            $sql = str_replace('~typedir~', $toptypedir, $sql);
            $dsql->ExecuteNoneQuery($sql);
        }
    }
    UpDateCatCache();
    ShowMsg('�ɹ�����ָ����Ŀ��','catalog_main.php');
    exit();
}
/*---------------------
function action_save(){ }
---------------------*/
else if($dopost=='save')
{
    $smalltypes = '';
    if(empty($smalltype)) $smalltype = '';
    if(is_array($smalltype)) $smalltypes = join(',',$smalltype);
    
    if(!isset($sitepath)) $sitepath = '';
    if($topid==0 && $reid>0) $topid = $reid;
    if($ispart!=0) $cross = 0;
    
    $description=str_replace("&nbsp;","",str_replace("<br />","",str_replace("<br>","",str_replace(" ","",trim($description)))));
    $keywords = str_replace("��","",trim($keywords));
	$zuozhe=str_replace(" ","",trim($zuozhe));
	$typename=trim($typename);
	if($reid!=0 && $reid!=45)
	{
		$row = $dsql->GetOne("SELECT `typedir` FROM `#@__arctype` WHERE `typename`='$typename' and zuozhe ='$zuozhe'");
		if($row)
		{
			ShowMsg("��С˵�Ѵ��ڣ�","-1");
            exit();
		}
	}
    if($ispart != 2 )
    {
        //��Ŀ�Ĳ���Ŀ¼
        if($referpath=='cmspath') $nextdir = '{cmspath}';
        if($referpath=='basepath') $nextdir = '';
        //��ƴ������
        if($upinyin==1 || $typedir=='')
        {
            $typedir = GetPinyin(stripslashes($typename));
        }
        $typedir = $nextdir.'/'.$typedir;
        $typedir = preg_replace("#\/{1,}#", "/", $typedir);
		$row = $dsql->GetOne("SELECT `typedir` FROM `#@__arctype` WHERE `typedir`='$typedir'");
		if($row)
		{
			for($ti=1;$ti<100;$ti++)
			{
				$tsql="SELECT `typedir` FROM `#@__arctype` WHERE `typedir`='".$typedir.$ti."'";
				$row = $dsql->GetOne($tsql);
				if(!$row)
				{
					$typedir = $typedir.$ti;
					break;
				}
			}
		}
    }

    //������վ��ʱ������(����Զ�����Ŀ)
    if($reid==0 && $moresite==1)
    {
        $sitepath = $typedir;

        //��������ַ
        if($siteurl!='')
        {
            $siteurl = preg_replace("#\/$#", "", $siteurl);
            if(!preg_match("#http:\/\/#i", $siteurl))
            {
                ShowMsg("��󶨵Ķ���������Ч������(http://host)����ʽ��","-1");
                exit();
            }
            if(preg_match("#".$cfg_basehost."#i", $siteurl))
            {
                ShowMsg("��󶨵Ķ��������뵱ǰվ����ͬһ���򣬲���Ҫ�󶨣�","-1");
                exit();
            }
        }
    }
/* 
    //����Ŀ¼
    if($ispart != 2 && $isdefault != -1)
    {
        $true_typedir = str_replace("{cmspath}", $cfg_cmspath, $typedir);
        $true_typedir = preg_replace("#\/{1,}#", "/", $true_typedir);
        if(!CreateDir($true_typedir))
        {
            ShowMsg("����Ŀ¼ {$true_typedir} ʧ�ܣ��������·���Ƿ�������⣡","-1");
            exit();
        }
    } */
    
    $in_query = "INSERT INTO `#@__arctype`(reid,topid,sortrank,typename,typeimg,zuozhe,startdate,overdate,booksize,bookclick,typenewbook,topnews,typedir,isdefault,defaultname,issend,channeltype,tempindex,templist,temparticle,modname,namerule,namerule2,ispart,corank,description,keywords,seotitle,moresite,siteurl,sitepath,ishidden,`cross`,`crossid`,`content`,`smalltypes`)
    VALUES('$reid','$topid','$sortrank','$typename','$typeimg','$zuozhe','$startdate','$overdate','$booksize','$bookclick','$typenewbook','$topnews','$typedir','$isdefault','$defaultname','$issend','$channeltype','$tempindex','$templist','$temparticle','default','$namerule','$namerule2','$ispart','$corank','$description','$keywords','$seotitle','$moresite','$siteurl','$sitepath','$ishidden','$cross','$crossid','$content','$smalltypes')";

    if(!$dsql->ExecuteNoneQuery($in_query))
    {
        ShowMsg("����Ŀ¼����ʱʧ�ܣ�����������������Ƿ�������⣡","-1");
        exit();
    }
	$bookid = $dsql->GetLastID();
	
	//������С˵����ʱ���û�ж�Ӧ��������Ʒ�������Զ�����һ��������reid��topidҪ����ʵ������趨��Ŀǰ����45
	if($reid!=0 && $reid!=45)
	{
		//���С˵URL��ʽ
		if($bookrule != '' && $bookrule!="[ƴ��]" )
		{
			$pydir=GetPinyin(stripslashes($title),1);
			$typepinyin=substr($typedir,1);
			$typedir=str_replace('[ƴ������ĸ]',$pydir,str_replace('[ID]',$bookid,str_replace('[ƴ��]',$typepinyin,$bookrule)));
			$typedir = preg_replace("#\/{1,}#", "/", $typedir);
			//����Ƿ���������С˵Ŀ¼���������Ŀ¼ƴ�����Զ������������
			$typedirrow = $dsql->GetOne("SELECT `typedir` FROM `#@__arctype` WHERE `typedir`='/$typedir'");
			if($typedirrow)
			{
				for($ti=1;$ti<100;$ti++)
				{
					$typedirsql="SELECT `typedir` FROM `#@__arctype` WHERE `typedir`='/".$typedir.$ti."'";
					$typedirrow = $dsql->GetOne($typedirsql);
					if(!$typedirrow)
					{
						$typedir = $typedir.$ti;
						break;
					}
				}
			}
			$typedir="/".$typedir;
			$dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET `typedir`='$typedir' WHERE id='$bookid' ");
		}
		//�������
		$row = $dsql->GetOne("SELECT `typedir` FROM `#@__arctype` WHERE `typename`='$zuozhe' and reid=45");
		if(!$row)
		{
			$zuozhedir = GetPinyin(stripslashes($zuozhe));
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
			$zuozhe_in_query = "insert into `#@__arctype` (`reid`, `topid`, `sortrank`, `typename`, `typedir`, `isdefault`, `defaultname`, `issend`, `channeltype`, `maxpage`, `ispart`, `corank`, `tempindex`, `templist`, `temparticle`, `namerule`, `namerule2`, `modname`, `description`, `keywords`, `seotitle`, `moresite`, `sitepath`, `siteurl`, `ishidden`, `cross`, `crossid`, `content`, `smalltypes`, `bookclick`, `typeimg`, `zuozhe`, `startdate`, `overdate`, `booksize`, `downloadurl`) values('45','45','50','$zuozhe','$zuozhedir','-1','index.html','0','1','-1','1','0','{style}/index_article45.htm','{style}/list_article.htm','{style}/article_article.htm','{typedir}/{Y}/{M}{D}/{aid}.html','/html{typedir}.html','default','�������������".$zuozhe."��С˵��Ʒ����������Ķ��Ĺ����з������ǵ�ת�������⣬�뼰ʱ��������ϵ���ر����ѵ��ǣ�С˵��Ʒһ�㶼�Ǹ�������д����ʱ��˼����ʽ��������ģ�������鹹�ĳɷݱȽ϶࣬����ģ�£�','','','0','/zuopinji','','1','0','','�������������".$zuozhe."��С˵��Ʒ����������Ķ��Ĺ����з������ǵ�ת�������⣬�뼰ʱ��������ϵ���ر����ѵ��ǣ�С˵��Ʒһ�㶼�Ǹ�������д����ʱ��˼����ʽ��������ģ�������鹹�ĳɷݱȽ϶࣬����ģ�£�','','0','/images/jipin-default.jpg','','','0','0','')";
			$dsql->ExecuteNoneQuery($zuozhe_in_query);
			$zuozheid = $dsql->GetLastID();
			//�������URL��ʽ
			if($zuozherule != '' && $zuozherule!="[ƴ��]" )
			{
				$pinyindir=substr($zuozhedir,1);
				$pydir=GetPinyin(stripslashes($zuozhe),1);
				$zuozhedir=str_replace('[ƴ������ĸ]',$pydir,str_replace('[ID]',$zuozheid,str_replace('[ƴ��]',$pinyindir,$zuozherule)));
				$zuozhedir = preg_replace("#\/{1,}#", "/", $zuozhedir);
				//����Ƿ���������С˵Ŀ¼���������Ŀ¼ƴ�����Զ������������
				$typedirrow = $dsql->GetOne("SELECT `typedir` FROM `#@__arctype` WHERE `typedir`='/$zuozhedir'");
				if($typedirrow)
				{
					for($ti=1;$ti<100;$ti++)
					{
						$typedirsql="SELECT `typedir` FROM `#@__arctype` WHERE `typedir`='/".$zuozhedir.$ti."'";
						$typedirrow = $dsql->GetOne($typedirsql);
						if(!$typedirrow)
						{
							$zuozhedir = $zuozhedir.$ti;
							break;
						}
					}
				}
				$zuozhedir="/".$zuozhedir;
				$dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET `typedir`='$zuozhedir' WHERE id='$zuozheid' ");
			}
			//�����߲����ĵ��ؼ�����
			$zuozheurl=$zuozhedir.".html";
			if($co_autokeytype==1 || $co_autokeytype=3)
			{
				$row = $dsql->GetOne("SELECT `keyword` FROM `#@__keywords` WHERE `keyword`='$zuozhe'");
				if(!$row && strlen($zuozhe)>2)
				{
					$keyword_in_query = "insert into `#@__keywords` (`keyword`, `rank`, `sta`, `rpurl`) values('$zuozhe','30','1','$zuozheurl')";
					$dsql->ExecuteNoneQuery($keyword_in_query);
				}
			}
		}
	}
	
	//�����������Ŀ��������Ʒ���������ĵ��ؼ�����
	if($reid=='45')
	{
		//�������URL��ʽ
		if($zuozherule != '' && $zuozherule!="[ƴ��]" )
		{
			$pydir=GetPinyin(stripslashes($title),1);
			$typepinyin=substr($typedir,1);
			$typedir=str_replace('[ƴ������ĸ]',$pydir,str_replace('[ID]',$bookid,str_replace('[ƴ��]',$typepinyin,$zuozherule)));
			$typedir = preg_replace("#\/{1,}#", "/", $typedir);
			//����Ƿ���������С˵Ŀ¼���������Ŀ¼ƴ�����Զ������������
			$typedirrow = $dsql->GetOne("SELECT `typedir` FROM `#@__arctype` WHERE `typedir`='/$typedir'");
			if($typedirrow)
			{
				for($ti=1;$ti<100;$ti++)
				{
					$typedirsql="SELECT `typedir` FROM `#@__arctype` WHERE `typedir`='/".$typedir.$ti."'";
					$typedirrow = $dsql->GetOne($typedirsql);
					if(!$typedirrow)
					{
						$typedir = $typedir.$ti;
						break;
					}
				}
			}
			$typedir="/".$typedir;
			$dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET `typedir`='$typedir' WHERE id='$bookid' ");
		}
		$row = $dsql->GetOne("SELECT `keyword` FROM `#@__keywords` WHERE `keyword`='$typename'");
		if(!$row && strlen($typename)>2)
		{
			$zuozheurl=$typedir.".html";
			$keyword_in_query = "insert into `#@__keywords` (`keyword`, `rank`, `sta`, `rpurl`) values('$typename','30','1','$zuozheurl')";
			$dsql->ExecuteNoneQuery($keyword_in_query);
		}
	}
	//��С˵�����ĵ��ؼ�����
	if($reid!='45' && ($co_autokeytype==2 || $co_autokeytype=3))
	{
		$row = $dsql->GetOne("SELECT `keyword` FROM `#@__keywords` WHERE `keyword`='$typename'");
		if(!$row && strlen($typename)>2)
		{
			$typeurl=$typedir."/";
			$dsql->ExecuteNoneQuery("insert into `#@__keywords` (`keyword`, `rank`, `sta`, `rpurl`) values('$typename','30','1','$typeurl')");
		}
	}
	
    UpDateCatCache();
    if($reid>0)
    {
        PutCookie('lastCid',GetTopid($reid),3600*24,'/');
    }
    ShowMsg("�ɹ�����һ�����࣡","catalog_main.php");
    exit();

}//End dopost==save

//��ȡ�Ӹ�Ŀ¼�̳е�Ĭ�ϲ���
if($dopost=='')
{
    $channelid = 1;
    $issend = 1;
    $corank = 0;
    $reid = 0;
    $topid = 0;
    $typedir = '';
    $moresite = 0;
    if($id>0)
    {
        $myrow = $dsql->GetOne(" SELECT tp.*,ch.typename AS ctypename FROM `#@__arctype` tp LEFT JOIN `#@__channeltype` ch ON ch.id=tp.channeltype WHERE tp.id=$id ");
        $channelid = $myrow['channeltype'];
        $issennd = $myrow['issend'];
        $corank = $myrow['corank'];
        $topid = $myrow['topid'];
        $typedir = $myrow['typedir'];
    }

    //����Ŀ�Ƿ�Ϊ����վ��
    $moresite = empty($myrow['moresite']) ? 0 : $myrow['moresite'];
}

include DedeInclude('templets/catalog_add.htm');