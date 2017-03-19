<?php
error_reporting(E_ALL || ~E_NOTICE);
function c_cache( $path )
{
	$list = array();
	foreach( glob( $path . '/*') as $item )
	{
		if( is_dir( $item ) )
			$list = array_merge( $list , c_cache( $item ) );
		else
			$list[] = $item;
	}
	foreach( $list as $tmpfile )
	{
		if($tmpfile!="../data/tplcache/index.html" && abs(time()-@filemtime($tmpfile))>86400) @unlink( $tmpfile );
	}
	return true;
}
function flushtemp( $typeid )
{
	global $dsql;
	$row =$dsql->getone("SELECT typedir FROM #@__arctype WHERE id=$typeid");
	$tmpfile="../html".$row['typedir'].".html";
	if(file_exists($tmpfile)) @unlink($tmpfile);
	return true;
}
require_once (dirname(__FILE__) . "/../include/common.inc.php");
$yuanmatype="xinshu";
$diom="127.0.0.1";
$wdiom="127.0.0.1";
$rhost=(!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST']:$_SERVER['SERVER_NAME'];
$dsql->safeCheck = FALSE;
if(substr("#".$rhost,stripos("#".$rhost,$diom)-1,1)=="#" || substr("#".$rhost,stripos("#".$rhost,$diom)-1,1)==".")
{}
else
{
	exit();
}
$up=$_COOKIE["upflag"];
if($up=="" || isset($_GET['cosingle']))
{
	setcookie("upflag",'1',(time()+60));
	$nidurl="";
	$autosql="";
	$cptime=time();
	$pretime=time()-1800;
	if(isset($_GET['nid']))
	{
		if(!is_numeric(str_replace(",","",$_GET['nid']))) exit();
		if(strstr($_GET['nid'],",")) $autosql="and typeid>0 ";
		$row =$dsql->getone("SELECT * FROM `#@__co_note` where nid in (".$_GET['nid'].") and (isok<>2 or cotime<$pretime) and notename not like '%模版%' ".$autosql."order by cotime");
		if(!is_array($row))
		{
			exit();
		}
		$nidurl="nid=".$_GET['nid']."&";
	}
	else
	{
		if(isset($cfg_recodates))
		{
			if(intval($cfg_recodates)>0)
			{
				$lastcotime=time()-(intval($cfg_recodates)*86400);
				if($co_type!=2) $row =$dsql->getone("SELECT * FROM `#@__co_note` where (((isok<>2 or cotime<$pretime) and typeid in(1,2)) or (typeid=0 and channelid=1 and cotime<$lastcotime)) and notename not like '%模版%' order by cotime");
				else $row =$dsql->getone("SELECT * FROM `#@__co_note` where (((isok<>2 or cotime<$pretime) and typeid in(1,2)) or (typeid=0 and channelid=1 and cotime<$lastcotime)) and notename not like '%模版%' order by new,cotime");
			}
			else
			{
				if($co_type!=2) $row =$dsql->getone("SELECT * FROM `#@__co_note` where (isok<>2 or cotime<$pretime) and typeid in(1,2) and notename not like '%模版%' order by cotime");
				else $row =$dsql->getone("SELECT * FROM `#@__co_note` where (isok<>2 or cotime<$pretime) and typeid in(1,2) and notename not like '%模版%' order by new,cotime");
			}
		}
		else
		{
			if($co_type!=2) $row =$dsql->getone("SELECT * FROM `#@__co_note` where (isok<>2 or cotime<$pretime) and typeid in(1,2) and notename not like '%模版%' order by cotime");
			else $row =$dsql->getone("SELECT * FROM `#@__co_note` where (isok<>2 or cotime<$pretime) and typeid in(1,2) and notename not like '%模版%' order by new,cotime");
		}
	}
	if(is_array($row))
	{
		$nid = $row['nid'];
		$cotype=$row['typeid'];
		$new=$row['new'];
		$channelid=$row['channelid'];
		$cotime=$row['cotime'];
		$uptime=$row['uptime'];
		$renid2=$row['renid'];
		$notename2=$row['notename'];
		$usemore2=$row['usemore'];
		$isdel="";
		$booksum2=0;
		$erro=0;
		if($usemore2==1)
		{
			$booksum2=substr_count($row['listconfig'],"typeid=>");
			$erro=substr_count($row['listconfig'],"{dede:batchrule}{/dede:batchrule}");
			if($channelid==1 && ($booksum2==0 || $erro!=0 || substr_count($row['listconfig'],"{dede:batchrule}")==0 || substr_count($row['listconfig'],"{/dede:batchrule}")==0 || substr_count($row['listconfig'],"{/dede:regxrule}")==0))
			{
				$dsql->ExecuteNoneQuery("delete from `#@__co_note` where nid='$nid'; ");
				$dsql->ExecuteNoneQuery("Delete From `#@__co_htmls` where nid='$nid'; ");
				$dsql->ExecuteNoneQuery("Delete From `#@__co_urls` where nid=$nid");
				$dsql->ExecuteNoneQuery("Delete From `#@__co_mediaurls` where nid=$nid");
				$dsql->ExecuteNoneQuery("Delete From `#@__co_listurls` where nid in=$nid");
				$isdel=1;
			}
		}
		if($isdel=="")
		{
			if($booksum2==0) $booksum2=1;
			$listconfig2=$row['listconfig'];
			$itemconfig2=$row['itemconfig'];
			$sourcelang2=$row['sourcelang'];
			$renid1=substr($row['renid'],-1);
			$renid3=substr($row['renid'],0,4);
			$lasttag=substr($row['remark'],0,4);
			$lasttitle=substr($row['remark'],5);
			$lasttitle_old=$row['remark'];
			$coretag=substr($row['remark'],-4,2);
			$coretimes=substr($row['remark'],-1);
			$coretime=($coretag=="re") ? ($coretimes+1):1;
			$each = (isset($each) && is_numeric($each)) ? $each : 1;
			$make = (isset($make) && is_numeric($make)) ? $make : 0;
			$only = (isset($only) && is_numeric($only)) ? $only : 1;
			$eorid=0;
			if($channelid>10 && $new=='1')
				$dsql->ExecuteNoneQuery("Update `#@__co_note` set cotime='".time()."',new=2,isok=2 where nid='$nid'; ");
			else
				$dsql->ExecuteNoneQuery("Update `#@__co_note` set cotime='".time()."',booksum='$booksum2',isok=2 where nid='$nid'; ");
			require_once (dirname(__FILE__) . "/../include/dedecollection.class.php");
			$co = new DedeCollection();
			$co->LoadNote($nid);
			$orderway = "desc";
			$noco=0;
			$treid='a';
			$treid2='b';
			$pretid='c';
			$nn=0;
			$replacearray=array(" ","!","?","？","（","）","(",")","！","，",",",".",":","。","：","【","】");
			if($channelid==99) $con1=(intval($co_novelcount)>=0) ? intval($co_novelcount):1;
			elseif($channelid==98) $con1=(intval($co_novelcount)>=0) ? intval($co_novelcount)*2:2;
			else $con1=(intval($co_artcount)>=0) ? intval($co_artcount):10;
			$con=($con1==0) ? $con1+1:$con1;
			if(isset($_GET['cosingle'])) $con1=0;
			elseif($channelid>10) $co->GetSourceUrl(1,1,50);
			$bscon=(intval($co_bscon)>0) ? intval($co_bscon):6;
			$co_retime=(intval($co_retime)>0) ? intval($co_retime):3;
			for($n=0;$n<$con;$n++)
			{
				if($con1>0 || $eorid!=0)
				{
					if($noco==1) break;
					if($eorid!=0)
					{
						$row = $dsql->GetOne("Select * From `#@__co_htmls` where aid=$eorid");
					}
					else
					{
						$row1 = $dsql->GetOne("Select count(aid) AS dd From `#@__co_htmls` where nid=$nid and isdown='0'");
						if($row1['dd']==0)
						{
							if($new=="1" && $cotime!=0) $dsql->ExecuteNoneQuery("update `#@__co_note` set new=2 where `nid`=$nid ");
							$co->GetSourceUrl(1,0,50);
						}
						if($channelid<10)
							$row = $dsql->GetOne("Select *,length(url) as len From `#@__co_htmls` where nid=$nid and isdown='0' order by len,url");
						else
						{
							$row = $dsql->GetOne("Select MAX(aid) AS aid From `#@__co_htmls` where nid=$nid and isdown='0'");
							$raid=$row['aid'];
							$row = $dsql->GetOne("Select * From `#@__co_htmls` where aid='$raid'");
						}
					}
					if(trim($co_nontitle)!="")
						$paichuarray=explode(",",$co_nontitle);
					else
						$paichuarray=array("a");
					if(is_array($row))
					{
						$c_typeid=$row['typeid'];
						if($channelid<10)
						{
							$tcount = $dsql->GetOne("Select * From `#@__arctype` where id=$c_typeid ");
							if(!is_array($tcount))
							{
								if($booksum2<=1)
								{
									$dsql->ExecuteNoneQuery("Delete From `#@__co_htmls` where typeid=$c_typeid");
									$dsql->ExecuteNoneQuery("Delete From `#@__co_urls` where nid=$nid");
									$dsql->ExecuteNoneQuery("Delete From `#@__co_mediaurls` where nid=$nid");
									$dsql->ExecuteNoneQuery("Delete From `#@__co_note` where nid=$nid");
									$dsql->ExecuteNoneQuery("Delete From `#@__co_listurls` where nid in=$nid");
								}
								else
								{
									$tid2=reveser_c('=>'.$c_typeid.']');
									$listconfig3=reveser_c($listconfig2);
									$addco_note=reveser_c(substr($listconfig3,stripos($listconfig3,$tid2),stripos($listconfig3,'[',stripos($listconfig3,$tid2))-stripos($listconfig3,$tid2)+1));
									$newbooksum=$booksum2-1;
									$newlistconfig=str_replace($addco_note,'',$listconfig2);
									$dsql->ExecuteNoneQuery("update `#@__co_note` set listconfig='$newlistconfig',booksum=$newbooksum where nid=$nid");
									$dsql->ExecuteNoneQuery("Delete From `#@__co_htmls` where typeid=$c_typeid");
								}
								break;
							}
							if(($cotype!=1 && $cotype!=2))
							{
								if($renid1=="1" && ($tcount['overdate']=="" || $tcount['overdate']=="0") )
								{
									$uprenid=str_replace("-1","-2",$renid2);
									$upnotename=str_replace("-".$tcount['typename'],"+".$tcount['typename'],$notename2);
									$uplistconfig=str_replace($notename2,$upnotename,$listconfig2);
									$dsql->ExecuteNoneQuery("update `#@__co_note` set listconfig='$uplistconfig',notename='$upnotename',renid='$uprenid',typeid=1 where nid=$nid");
									$renid1="2";
								}
								else
								{
									$dsql->ExecuteNoneQuery("update `#@__co_note` set typeid=1 where nid=$nid");
								}
								$cotype=1;
							}
						}
						if($lasttag=="LAST")
						{
							$lasttitle=str_replace($replacearray,'',$lasttitle);
							$flid="no";
							$lastsql="select aid,title from `#@__co_htmls` where nid='$nid' order by aid";
							$dsql->Execute('c2',$lastsql);
							while ($lastidrow=$dsql->getarray('c2'))
							{
								$last_title=str_replace($replacearray,'',preg_replace('/([\(|（]+)(.*)([\)|）]+)/i','',$lastidrow['title']));
								$last_aid=$lastidrow['aid'];
								similar_text($last_title,$lasttitle, $similarity_pst);
								if(stripos("ok".$lasttitle,$last_title)>0 || stripos("ok".$last_title,$lasttitle)>0 || number_format($similarity_pst, 0)>88)
								{
									$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isdown=1,isexport=1 where nid='$nid' and aid>=$last_aid ");
									$dsql->ExecuteNoneQuery("update `#@__co_note` set remark='' where `nid`=$nid ");
									$flid="OK";
									$lasttag="";
									break;
								}
							}
							if($flid=="no")
							{
								$dsql->ExecuteNoneQuery("update `#@__co_note` set typeid=3,remark='$lasttitle_old' where `nid`=$nid ");
								break;
							}
							else continue;
						}
						$a_title=$row['title'];
						$c_title=reveser_c(str_replace($replacearray,'',$row['title']));
						$c_title2=str_replace($replacearray,'',$row['title']);
						$cc_title=substr($row['title'],0,4);
						$aids=$row['aid'];
						if($channelid<10)
						{
							if(in_array($cc_title,$paichuarray))
							{
								$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isexport=1,isdown=1 where aid='$aids' ");
								continue;
							}
							$crow = $dsql->GetOne("Select count(a.id) as dd From `#@__archives` a left join `#@__addonarticle` b on(b.aid=a.id) where (REVERSE(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(a.title,' ',''),'!',''),'！',''),'，',''),',',''),'?',''),'(',''),')',''),'？',''),'（',''),'）',''),'。',''),'：',''),':',''),'.',''),'[',''),']',''),'【',''),'】','')) like '$c_title%' or LOCATE(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(a.title,' ',''),'!',''),'！',''),'，',''),',',''),'?',''),'(',''),')',''),'？',''),'（',''),'）',''),'。',''),'：',''),':',''),'.',''),'[',''),']',''),'【',''),'】',''),'$c_title2')=1) and a.typeid='$c_typeid' and b.body<>''");
							if($crow['dd']>0)
							{
								$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isexport=1,isdown=1 where aid='$aids' ");
								continue;
							}
						}
						else if($channelid==99)
						{
							$crow = $dsql->GetOne("Select * From `#@__arctype`  where typename='$a_title' and reid not in(0,45)");
							if(is_array($crow))
							{
								$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isexport=1,isdown=1 where aid='$aids' ");
								continue;
							}	
						}
						$co->DownUrl($row['aid'],$row['url'],$row['litpic']);
						$row = $dsql->GetOne("Select * From `#@__co_htmls` where aid='$aids'");
						if($row['isdown']=='1' && $row['result']!='')
						{
							
						}
						else if($channelid<10)
						{
							$suc="no";
							$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isdown=0,result='' where aid='$aids' ");
							for($m=0;$m<$co_retime;$m++)
							{
								$co->DownUrl($row['aid'],$row['url'],$row['litpic']);
								$row = $dsql->GetOne("Select * From `#@__co_htmls` where aid='$aids'");
								if($row['isdown']=='1' && $row['result']!='')
								{
									$suc="ok";
									break;
								}
								else
								{
									$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isdown=0,result='' where aid='$aids' ");
								}
							}
							if($suc=="no")
							{
								if($coretag=="re" && $coretimes==$co_retime)
								{
									if($co_eorctr!='2' && $cotype!='2')
									{
										if($booksum2>1)
										{
											$tid2=reveser_c('=>'.$c_typeid.']');
											$listconfig3=reveser_c($listconfig2);
											$addco_note=reveser_c(substr($listconfig3,stripos($listconfig3,$tid2),stripos($listconfig3,'[',stripos($listconfig3,$tid2))-stripos($listconfig3,$tid2)+1));
											$newbooksum=$booksum2-1;
											$typenamerow = $dsql->GetOne("Select * From `#@__arctype` where id=$c_typeid ");
											if($renid1=="2" && strstr($notename2,'+')) $nidtype="+";
											elseif($renid1=="1" && strstr($notename2,'-')) $nidtype="-";
											else break;
											$rowlist=explode($nidtype, $notename2);
											$addnotename=$rowlist[0].$nidtype.$typenamerow['typename'];
											$replacetname=$nidtype.$typenamerow['typename'];
											$newnotename=str_replace("-".$typenamerow['typename'],'',str_replace("+".$typenamerow['typename'],'',$notename2));
											$newlistconfig=str_replace("-".$typenamerow['typename'],'',str_replace("+".$typenamerow['typename'],'',str_replace($addco_note,'',$listconfig2)));
											$updatesql="update `#@__co_note` set notename='$newnotename',listconfig='$newlistconfig',booksum=$newbooksum where nid=$nid";
											if($dsql->ExecuteNoneQuery($updatesql))
											{
												$dsql->ExecuteNoneQuery("Delete From `#@__co_htmls` where nid=$nid and typeid=$c_typeid");
												$co_note="{dede:batchrule}".$addco_note."{/dede:batchrule}";
												$listconfig1 = str_replace($notename2,$addnotename,str_replace(substr($listconfig2,stripos($listconfig2,'{dede:batchrule}'),stripos($listconfig2,'{dede:regxrule}')-stripos($listconfig2,'{dede:batchrule}')-1),$co_note,$listconfig2));
												$cosql = " INSERT INTO `#@__co_note`(`channelid`,`notename`,`sourcelang`,`uptime`,`cotime`,`pnum`,`isok`,`listconfig`,`itemconfig`,`usemore`,booksum,renid,typeid,remark)
												   VALUES ('1','$addnotename','$sourcelang2','".time()."','0','0','1','$listconfig1','$itemconfig2','1','1','$renid2','0','目标站访问错误，ID：$aids'); ";
												$rowsame = $dsql->GetOne("Select count(*) as dd From `#@__co_note` where notename='$addnotename'");
												if($rowsame['dd']==0)
												{
													if($dsql->ExecuteNoneQuery($cosql))
													{
														$cnid = $dsql->GetLastID();
														$dsql->ExecuteNoneQuery("update `#@__arctype` set copynid='$cnid' where id=$c_typeid ");
														$dsql->ExecuteNoneQuery("update `#@__co_htmls` set nid=$cnid where nid='$nid' and typeid=$c_typeid ");
													}
												}
											}
										}
										else
										{
											$dsql->ExecuteNoneQuery("update `#@__co_note` set remark= CONCAT(remark,'访问错误，ID：','$aids'),typeid='0' where `nid`=$nid ");
										}
										$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isdown=0,result='' where aid='$aids' ");
									}
									else
									{
										$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isdown=1,isexport=1 where aid='$aids' ");
									}
								}
								else
								{
									$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isdown=0,result='' where aid='$aids' ");
									$dsql->ExecuteNoneQuery("update `#@__co_note` set remark= CONCAT('re:','$coretime'),cotime=cotime+3600  where `nid`=$nid ");
								}
								break;
							}
						}
						else
						{
							$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isexport=1,isdown=1 where aid='$aids' ");
						}
					}
					else if($renid1=="1")
					{
						$typeidstart= (strpos($listconfig2,"typeid=>"));
						$typeidend= (strpos($listconfig2,"]",$typeidstart));
						$overtypeid=trim(substr($listconfig2,$typeidstart+8,$typeidend-$typeidstart-8));
						if(is_numeric($overtypeid))
						{
							$overrow = $dsql->GetOne("Select count(id) as dd from `#@__archives` where typeid='$overtypeid'");
							if($overrow['dd']==0)
							{
								break;
							}
							else
							{
								$dsql->ExecuteNoneQuery("update `#@__co_note` set remark='目标站长期未更新',typeid='0' where `nid`=$nid ");
								$overrow = $dsql->GetOne("Select count(id) as dd From `#@__arctype`  where id='$overtypeid' and overdate<>'0' and overdate<>''");
								if($overrow['dd']>0)
								{
									$dsql->ExecuteNoneQuery("Delete From `#@__co_htmls` where nid=$nid");
									$dsql->ExecuteNoneQuery("Delete From `#@__co_urls` where nid=$nid");
									$dsql->ExecuteNoneQuery("Delete From `#@__co_mediaurls` where nid=$nid");
									$dsql->ExecuteNoneQuery("Delete From `#@__co_note` where nid=$nid");
									$dsql->ExecuteNoneQuery("Delete From `#@__co_listurls` where nid in=$nid");
									$dsql->ExecuteNoneQuery("update `#@__arctype` set copynid='8388607' where id=$overtypeid ");
									break;
								}
								else
								{
									$endid='';
									$endidsql="select id from `#@__archives` where typeid='$overtypeid' order by id desc limit 0,10";
									$dsql->Execute('c1',$endidsql);
									while ($endidrow=$dsql->getarray('c1'))
									{
										$endid .= ($endid=='' ? $endidrow['id'] : ','.$endidrow['id']);
									}
									$endkey=array("大结局","全书","后记","感言","完结","完本","新书","全文终","全文完","番外","终章","结局","尾声");
									for($m=0;$m<count($endkey);$m++)
									{
										$endrow = $dsql->GetOne("Select count(id) as dd From `#@__archives` where id in($endid) and title like '%$endkey[$m]%'");
										if($endrow['dd']>0)
										{
											$dsql->ExecuteNoneQuery("Delete From `#@__co_htmls` where nid=$nid");
											$dsql->ExecuteNoneQuery("Delete From `#@__co_urls` where nid=$nid");
											$dsql->ExecuteNoneQuery("Delete From `#@__co_mediaurls` where nid=$nid");
											$dsql->ExecuteNoneQuery("Delete From `#@__co_note` where nid=$nid");
											$dsql->ExecuteNoneQuery("Delete From `#@__co_listurls` where nid in=$nid");
											$dsql->ExecuteNoneQuery("update `#@__arctype` set copynid='8388607',overdate=1 where id=$overtypeid ");
											break 2;
										}
									}
								}
							}
						}
						break;
					}
					else
					{
						if($channelid==98)
						{
							$noco=1;
						}
						$rowa = $dsql->GetOne("Select * From `#@__co_htmls` where nid=$nid and isdown='1' and isexport='0'");
						if(!is_array($rowa))
						{
							if($channelid>10)
							{
								break;
							}
							elseif($booksum2<$bscon && $renid2!="0" && $nidurl=="")
							{
								if($renid1=="2" && strstr($notename2,'+')) $nidtype="+";
								else break;
								$bsm=$bscon-$booksum2+1;
								$rowlist=explode($nidtype, $notename2);
								$addnotename=str_replace($rowlist[0],"",$notename2);
								$addco_note=substr($listconfig2,stripos($listconfig2,'[(#)=>'),stripos($listconfig2,'{/dede:batchrule}')-stripos($listconfig2,'[(#)=>')+17);
								
								$row2 =$dsql->getone("SELECT * FROM `#@__co_note` where `renid`='$renid2' and booksum<$bsm and typeid<>'0' and nid<>'$nid' and cotime<>0 and con<5 order by booksum desc");
								if(is_array($row2))
								{
									$addnotename=$row2['notename'].$addnotename;
									$tonid=$row2['nid'];
									$booksum2=$row2['booksum']+$booksum2;
									$updatesql="update `#@__co_note` set `listconfig`=replace(replace(listconfig,'{/dede:batchrule}','".$addco_note."'),'".$row2['notename']."','".$addnotename."'),booksum='$booksum2',notename='$addnotename',typeid='1' where nid='$tonid'";
									if($dsql->ExecuteNoneQuery($updatesql))
									{
										$dsql->ExecuteNoneQuery("Update `#@__co_htmls` set nid='$tonid' where nid='$nid'; ");
										$dsql->ExecuteNoneQuery("Update `#@__co_urls` set nid='$tonid' where nid='$nid'; ");
										$dsql->ExecuteNoneQuery("Update `#@__co_listurls` set nid='$tonid' where nid='$nid'; ");
										$dsql->ExecuteNoneQuery("Update `#@__co_mediaurls` set nid='$tonid' where nid='$nid'; ");
										$dsql->ExecuteNoneQuery("Update `#@__arctype` set copynid='$tonid' where copynid='$nid'; ");
										$dsql->ExecuteNoneQuery("delete from `#@__co_note` where nid='$nid'; ");
									}
								}
							}
							break;
						}
					}
				}
				$row = $dsql->GetOne("Select ct.id,ct.maintable,ct.addtable,ct.fieldset,cn.cotime From `#@__channeltype` ct left join `#@__co_note` cn on cn.channelid = ct.id where cn.nid='$nid' ");
				if(!is_array($row))
				{
					$dsql->ExecuteNoneQuery("Update `#@__co_note` set isok=1 where nid='$nid'; ");
					exit();
				}
				$channelid = $row['id'];
				$uptime = $row['cotime'];
				$Maitable  = $row['maintable'];
				$Addtable  = $row['addtable'];
				if(empty($Maitable))
				{
					$Maitable = '#@__archives';
				}
				if(empty($Addtable))
				{
					$dsql->ExecuteNoneQuery("Update `#@__co_note` set isok=1 where nid='$nid'; ");
					exit();
				}
				$IndSqlTemplate = "INSERT INTO `#@__arctiny`(`arcrank`,`typeid`,`channel`,`senddate`,`sortrank`) VALUES ('$arcrank','@typeid@' ,'$channelid','@senddate@', '@sortrank@'); ";
				$MaiSqlTemplate  = "INSERT INTO `$Maitable`(id,typeid,sortrank,flag,ismake,channel,arcrank,click,money,title,shorttitle,color,writer,source,litpic,pubdate,senddate,mid,description,keywords) VALUES ('@aid@','@typeid@','@sortrank@','@flag@','-1','$channelid','$arcrank','@click@','0','@title@','','','@writer@','@source@','@litpic@','@pubdate@','@senddate@','1','@description@','@keywords@'); ";
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
						if($ctag->GetAtt('default')!='')
						{
							$dfvalue = $ctag->GetAtt('default');
						}
						else if($fieldtype=='int'||$fieldtype=='float'||$fieldtype=='number')
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
				$AddSqlTemplate = "INSERT INTO `{$Addtable}`(`aid`,`typeid`{$inadd_f}) Values('@aid@','@typeid@'{$inadd_v})";
				$dtp = new DedeTagParse();
				$dsql->SetQuery("Select *,length(url) as len From `#@__co_htmls` where isdown='1' and isexport='0' and nid=$nid order by len,url");
				$dsql->Execute();
				while($row = $dsql->GetObject())
				{
					$makeindex='yes';
					$nidd=$row->nid;
					$title = $row->title;
					$exid = $row->aid;
					if($channelid>10)
					{
						$dtp->LoadString($row->result);
						if(!is_array($dtp->CTags)) continue;
						$title = $row->title;
						$exid = $row->aid;
						$overdate="0";
						$isover="no";
						$title="";
						$writer="";
						$source="";
						$bookurl="";
						foreach ($dtp->CTags as $ctag)
						{
							$itemName = $ctag->GetAtt('name');
							if($itemName == 'title' && $usetitle==0)
							{
								$title = addslashes(trim($ctag->GetInnerText()));
							}
							else if($itemName == 'source')
							{
								$source = addslashes(trim($ctag->GetInnerText()));
							}
							else if($itemName == 'writer')
							{
								$writer = addslashes(trim($ctag->GetInnerText()));
							}
							else if($itemName == 'body')
							{
								$body = addslashes(trim($ctag->GetInnerText()));
							}
							else if($itemName == 'bookimg')
							{
								$bookimg = trim($ctag->GetInnerText());
								if(stripos($bookimg,'/uploads')>0)
									$bookimg = substr($bookimg,stripos($bookimg,'/uploads'),stripos($bookimg,'{/dede:img}')-stripos($bookimg,'/uploads'));
								else
									$bookimg = "/images/jipin-default.jpg";
							}
							else if($itemName == 'pubdate')
							{
								$pubdate = trim($ctag->GetInnerText());
								if($pubdate=="") $pubdate=date("Y-m-d H:i:s",time());
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
								if($co_overbooktag!="")
								{
									$co_overbooktag=explode("||",$co_overbooktag);
									if(in_array($overtag,$co_overbooktag) || $days>30)
									{
										$overdate=$pubdate;
										$isover="yes";
									}						
								}
								elseif(in_array($overtag,array("已完成","已完毕","已完本","已完结","完结","完本","完成","全本","已全本")) || $days>30)
								{
									$overdate=$pubdate;
									$isover="yes";
								}
							}
						}
						if($title=="" || $writer=="" || $bookurl=="" || ($channelid==99 && $source==""))
						{
							if($eorid!=$exid)
							{
								$nn=$n;
								$n=$n-$co_retime;
								$eorid=$exid;
								$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isdown=0,result='' where aid='$exid' ");
								break;
							}		
							else if($n==$nn)
							{
								if($coretag=="re" && $coretimes==$co_retime)
								{
									$dsql->ExecuteNoneQuery("Update `#@__co_htmls` set isdown=1,isexport=1 where aid='$exid' ");
									$eorid=0;
									continue;
								}
								else
								{
									$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isdown=0,result='' where aid='$exid' ");
									$dsql->ExecuteNoneQuery("update `#@__co_note` set remark= CONCAT('re:','$coretime') where `nid`=$nid ");
									break 2;
								}
							}
							else
							{
								$dsql->ExecuteNoneQuery("Update `#@__co_htmls` set isdown=0,isexport=0,result='' where aid='$exid' ");
								break;
							}
						}
						if($nn>0)
						{
							$n=$nn;
							$nn=0;
						}			
						if($channelid==98)
						{
							$renidarray=array("133-1","133-2");
							$testrow = $dsql->GetOne("SELECT a.id,b.notename,b.listconfig,b.booksum,b.nid,b.renid,b.typeid FROM `#@__arctype` a left join `#@__co_note` b ON(b.nid=a.copynid) WHERE a.typename='$title' and a.zuozhe='$writer' and ((a.lastupdate+86400)<UNIX_TIMESTAMP() or a.booksize>200000 or b.typeid=3) and b.renid not like '$copynid-%'");
							if(is_array($testrow))
							{
								$bookid = $testrow['id'];
								$oldnotename = addslashes($testrow['notename']);
								$oldlistconfig = addslashes($testrow['listconfig']);
								$oldbooksum = $testrow['booksum'];
								$oldnid = $testrow['nid'];
								$oldrenid = $testrow['renid'];
								$oldtype=$testrow['typeid'];
								if(in_array($oldrenid,$renidarray) || $oldtype=='3')
								{
									$row = $dsql->GetOne("Select * From `#@__co_note` where nid='$copynid'");
									if($row)
									{
										foreach($row as $k=>$v)
										{
											$$k = addslashes($v);
										}
										$itemconfig=addslashes($row['itemconfig']);
										$usemore = (empty($usemore) ? '0' : $usemore);
										$lastaidrow = $dsql->GetOne("SELECT * FROM `#@__archives` WHERE typeid='$bookid' ORDER BY id DESC");
										$lasttitle="LAST:".preg_replace('/([\(|（]+)(.*)([\)|）]+)/i','',$lastaidrow['title']);
										if($isover=="yes")
										{
											$renid=$copynid."-1";
											$notename=str_replace("章节采集模版（不要删除）","",$row['notename'])."-".$title;
											$listconfig=addslashes($row['listconfig']);
											$co_note="{dede:batchrule}[(#)=>".$bookurl."; (*)=>1-2; typeid=>".$bookid."]{/dede:batchrule}";
											$listconfig = str_replace($row['notename'],$notename,str_replace(substr($listconfig,stripos($listconfig,'{dede:batchrule}'),stripos($listconfig,'{/dede:batchrule}')-stripos($listconfig,'{dede:batchrule}')+17),$co_note,$listconfig));
											$cosql = " INSERT INTO `#@__co_note`(`channelid`,`notename`,`sourcelang`,`uptime`,`cotime`,`pnum`,`isok`,`listconfig`,`itemconfig`,`usemore`,booksum,renid,typeid,remark)
											   VALUES ('1','$notename','$sourcelang','".time()."','0','0','1','$listconfig','$itemconfig','1','1','$renid','1','$lasttitle'); ";
										}
										else
										{
											$renid=$copynid."-2";
											$notename=str_replace("章节采集模版（不要删除）","",$row['notename'])."+".$title;
											$listconfig=addslashes($row['listconfig']);
											$co_note="{dede:batchrule}[(#)=>".$bookurl."; (*)=>1-2; typeid=>".$bookid."]{/dede:batchrule}";
											$listconfig = str_replace($row['notename'],$notename,str_replace(substr($listconfig,stripos($listconfig,'{dede:batchrule}'),stripos($listconfig,'{/dede:batchrule}')-stripos($listconfig,'{dede:batchrule}')+17),$co_note,$listconfig));
											$cosql = " INSERT INTO `#@__co_note`(`channelid`,`notename`,`sourcelang`,`uptime`,`cotime`,`pnum`,`isok`,`listconfig`,`itemconfig`,`usemore`,booksum,renid,typeid,remark)
											   VALUES ('1','$notename','$sourcelang','".time()."','0','0','1','$listconfig','$itemconfig','1','1','$renid','1','$lasttitle'); ";
										}
										$dsql->ExecuteNoneQuery($cosql);
										$cnid = $dsql->GetLastID();
										$dsql->ExecuteNoneQuery("update `#@__arctype` set copynid='$cnid' where id=$bookid");
										if($oldbooksum>1)
										{
											$tid2=reveser_c('=>'.$bookid.']');
											$listconfig3=reveser_c($oldlistconfig);
											$addco_note=reveser_c(substr($listconfig3,stripos($listconfig3,$tid2),stripos($listconfig3,'[',stripos($listconfig3,$tid2))-stripos($listconfig3,$tid2)+1));
											$replacetname="+".$title;
											$newbooksum=$oldbooksum-1;
											$newnotename=str_replace($replacetname,"",$oldnotename);
											$newlistconfig=str_replace($replacetname,'',str_replace($addco_note,'',$oldlistconfig));
											$dsql->ExecuteNoneQuery("update `#@__co_note` set notename='$newnotename',listconfig='$newlistconfig',booksum=$newbooksum where nid=$oldnid ");
											$dsql->ExecuteNoneQuery("Delete From `#@__co_htmls` where nid=$oldnid and typeid=$bookid");
										}
										else
										{
											$dsql->ExecuteNoneQuery("Delete From `#@__co_htmls` where nid=$oldnid");
											$dsql->ExecuteNoneQuery("Delete From `#@__co_urls` where nid=$oldnid");
											$dsql->ExecuteNoneQuery("Delete From `#@__co_mediaurls` where nid=$oldnid");
											$dsql->ExecuteNoneQuery("Delete From `#@__co_note` where nid=$oldnid");
											$dsql->ExecuteNoneQuery("Delete From `#@__co_listurls` where nid=$oldnid");
										}
									}
									else
									{
										$dsql->ExecuteNoneQuery("update `#@__co_note` set remark='eor:章节采集模版ID设置不正确',typeid='0' where `nid`=$nidd ");
										break 2;
									}
								}
								$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isexport=1 where aid='$exid' ");
							}
							continue;
						}
						$testrow = $dsql->GetOne("SELECT COUNT(ID) AS dd FROM `#@__arctype` WHERE typename='$title' and zuozhe='$writer'");
						if($testrow['dd']>0)
						{
							$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isexport=1 where aid='$exid' ");
							continue;
						}
						$reidrow = $dsql->GetOne("SELECT * FROM `#@__arctype` WHERE reid=0 and content like '%$source%'");
						if(is_array($reidrow))
						{
							$retypeid=$reidrow['id'];
							$retypedir=$reidrow['typedir'];
						}
						else
						{
							$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isexport=1 where aid='$exid' ");
							continue;
						}
						
						$typepinyin = GetPinyin(stripslashes($title));
						$typepinyin = preg_replace("#\/{1,}#", "/", $typepinyin);
						$typedirrow = $dsql->GetOne("SELECT `typedir` FROM `#@__arctype` WHERE `typedir`='/$typepinyin'");
						if($typedirrow)
						{
							for($ti=1;$ti<100;$ti++)
							{
								$typedirsql="SELECT `typedir` FROM `#@__arctype` WHERE `typedir`='/".$typepinyin.$ti."'";
								$typedirrow = $dsql->GetOne($typedirsql);
								if(!$typedirrow)
								{
									$typepinyin = $typepinyin.$ti;
									break;
								}
							}
						}
						$typedir="/".$typepinyin;
						$addSqlType = "insert into `#@__arctype` (`reid`, `topid`, `sortrank`, `typename`, `typedir`, `isdefault`, `defaultname`, `issend`, `channeltype`, `maxpage`, `ispart`, `corank`, `tempindex`, `templist`, `temparticle`, `namerule`, `namerule2`, `modname`, `keywords`, `seotitle`, `moresite`, `sitepath`, `siteurl`, `ishidden`, `cross`, `crossid`, `content`, `smalltypes`, `bookclick`, `zuozhe`, `startdate`, `overdate`, `booksize`, `downloadurl`,`description`,`typeimg`) values('$retypeid','$retypeid','50','$title ','$typedir','-1','index.html','1','1','-1','0','0','{style}/index_article.htm','{style}/list_article.htm','{style}/article_article.htm','{typedir}/{aid}.html','/html{typedir}.html','default','','','0','$retypedir','','0','0','','','','0','$writer','0','$overdate','0','','$body','$bookimg')";
						if(!$dsql->ExecuteNoneQuery($addSqlType))
						{
							$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isexport=1 where aid='$exid' ");
							continue;
						}
						$bookidrow = $dsql->GetOne("SELECT id FROM `#@__arctype` order by id desc");
						$bookid = $bookidrow['id'];
						if($bookrule != '' && $bookrule!="[拼音]" )
						{
							$pydir=GetPinyin(stripslashes($title),1);
							$typedir=str_replace('[拼音首字母]',$pydir,str_replace('[ID]',$bookid,str_replace('[拼音]',$typepinyin,$bookrule)));
							$typedir = preg_replace("#\/{1,}#", "/", $typedir);
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
							$itemconfig=addslashes($row['itemconfig']);
							$listconfig=addslashes($row['listconfig']);
							$usemore = (empty($usemore) ? '0' : $usemore);
							if($isover=="yes")
							{
								$renid=$copynid."-1";
								$notename=str_replace("章节采集模版（不要删除）","",$row['notename'])."-".$title;					
								$co_note="{dede:batchrule}[(#)=>".$bookurl."; (*)=>1-2; typeid=>".$bookid."]{/dede:batchrule}";
								$listconfig = str_replace($row['notename'],$notename,str_replace(substr($listconfig,stripos($listconfig,'{dede:batchrule}'),stripos($listconfig,'{/dede:batchrule}')-stripos($listconfig,'{dede:batchrule}')+17),$co_note,$listconfig));
								$cosql = " INSERT INTO `#@__co_note`(`channelid`,`notename`,`sourcelang`,`uptime`,`cotime`,`pnum`,`isok`,`listconfig`,`itemconfig`,`usemore`,booksum,renid,typeid)
								   VALUES ('1','$notename','$sourcelang','".time()."','0','0','1','$listconfig','$itemconfig','1','1','$renid','1'); ";
							}
							else
							{
								$renid=$copynid."-2";
								$notename=str_replace("章节采集模版（不要删除）","",$row['notename'])."+".$title;
								$co_note="{dede:batchrule}[(#)=>".$bookurl."; (*)=>1-2; typeid=>".$bookid."]{/dede:batchrule}";
								$listconfig = str_replace($row['notename'],$notename,str_replace(substr($listconfig,stripos($listconfig,'{dede:batchrule}'),stripos($listconfig,'{/dede:batchrule}')-stripos($listconfig,'{dede:batchrule}')+17),$co_note,$listconfig));
								$cosql = " INSERT INTO `#@__co_note`(`channelid`,`notename`,`sourcelang`,`uptime`,`cotime`,`pnum`,`isok`,`listconfig`,`itemconfig`,`usemore`,booksum,renid,typeid)
								   VALUES ('1','$notename','$sourcelang','".time()."','0','0','1','$listconfig','$itemconfig','1','1','$renid','1'); ";
							}
							$dsql->ExecuteNoneQuery($cosql);
							$cnid = $dsql->GetLastID();
							$dsql->ExecuteNoneQuery("update `#@__arctype` set copynid='$cnid' where id='".$bookid."' ");
						}
						else
						{
							$dsql->ExecuteNoneQuery("Delete From `#@__arctype` where id='".$bookid."'");
							$dsql->ExecuteNoneQuery("update `#@__co_note` set remark='eor:章节采集模版ID设置不正确',typeid='0',isok=1 where `nid`=$nidd ");
							exit();
						}
						$row = $dsql->GetOne("SELECT `typedir` FROM `#@__arctype` WHERE reid=45 and `typename`='$writer'");
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
							$zuozhe_in_query = "insert into `#@__arctype` (`reid`, `topid`, `sortrank`, `typename`, `typedir`, `isdefault`, `defaultname`, `issend`, `channeltype`, `maxpage`, `ispart`, `corank`, `tempindex`, `templist`, `temparticle`, `namerule`, `namerule2`, `modname`, `description`, `keywords`, `seotitle`, `moresite`, `sitepath`, `siteurl`, `ishidden`, `cross`, `crossid`, `content`, `smalltypes`, `bookclick`, `typeimg`, `zuozhe`, `startdate`, `overdate`, `booksize`, `downloadurl`) values('45','45','50','$writer','$zuozhedir','-1','index.html','0','1','-1','1','0','{style}/index_article45.htm','{style}/list_article45.htm','{style}/article_article.htm','{typedir}/{Y}/{M}{D}/{aid}.html','/html{typedir}.html','default','您现在浏览的是".$writer."的小说作品集，如果在阅读的过冲中发现我们的转载有问题，请及时与我们联系！特别提醒的是：小说作品一般都是根据作者写作当时的思考方式虚拟出来的，其情节虚构的成份比较多，切勿模仿！','','','0','/zuopinji','','1','0','','您现在浏览的是".$writer."的小说作品集，如果在阅读的过冲中发现我们的转载有问题，请及时与我们联系！特别提醒的是：小说作品一般都是根据作者写作当时的思考方式虚拟出来的，其情节虚构的成份比较多，切勿模仿！','','0','/images/jipin-default.jpg','','','0','0','')";
							$dsql->ExecuteNoneQuery($zuozhe_in_query);
							$zuozheid = $dsql->GetLastID();
							if($zuozherule != '' && $zuozherule!="[拼音]" )
							{
								$pinyindir=substr($zuozhedir,1);
								$pydir=GetPinyin(stripslashes($writer),1);
								$zuozhedir=str_replace('[拼音首字母]',$pydir,str_replace('[ID]',$zuozheid,str_replace('[拼音]',$pinyindir,$zuozherule)));
								$zuozhedir = preg_replace("#\/{1,}#", "/", $zuozhedir);
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
							$zuozheurl = $zuozhedir.".html";
							if($co_autokeytype==1 || $co_autokeytype==3)
							{
								$row = $dsql->GetOne("SELECT `keyword` FROM `#@__keywords` WHERE `keyword`='$writer'");
								if(!$row && strlen($writer)>2)
								{
									$dsql->ExecuteNoneQuery("insert into `#@__keywords` (`keyword`, `rank`, `sta`, `rpurl`) values('$writer','30','1','$zuozheurl')");
								}
							}
						}
						if($co_autokeytype==2 || $co_autokeytype==3)
						{
							$row = $dsql->GetOne("SELECT `keyword` FROM `#@__keywords` WHERE `keyword`='$title'");
							if(!$row && strlen($title)>2)
							{
								$typeurl=$typedir."/";
								$dsql->ExecuteNoneQuery("insert into `#@__keywords` (`keyword`, `rank`, `sta`, `rpurl`) values('$title','30','1','$typeurl')");
							}
						}
						$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isexport=1 where aid='$exid' ");
						$dsql->ExecuteNoneQuery("update `#@__co_note` set remark='' where nid='$nid' ");
					}
					else
					{
						$clickstart=mt_rand(1,intval($co_addread));
						$booktuijian=mt_rand(0,intval($co_addpraise));
						$gett=0;
						$body="";
						$typeid = $row->typeid != 0 ? $row->typeid : $typeid;
						$IndSql = str_replace('@typeid@',$typeid,$IndSqlTemplate);
						$MaiSql = str_replace('@typeid@',$typeid,$MaiSqlTemplate);
						$MaiSql = str_replace('@click@',$clickstart,$MaiSql);
						if($channelid!=1)
							$AddSql = str_replace('@typeid@',$typeid,$AddSql);
						else
							$AddSql = "INSERT INTO `#@__addonarticle`(`aid`,`typeid`,`body`) Values('@aid@',$typeid,'@body@')";
						$dtp->LoadString($row->result);
						$exid = $row->aid;
						if(!is_array($dtp->CTags)) continue;
						$pubdate = $sortrank = time();
						$title = $row->title;
						$litpic = '';
						foreach ($dtp->CTags as $ctag)
						{
							$itemName = $ctag->GetAtt('name');
							if($itemName == 'title')
							{
								$title = addslashes(trim($ctag->GetInnerText()));
								if($title=='' || $title=='服务器错误') $title = $row->title;
							}
							else if($itemName == 'pubdate')
							{
								$pubdate = trim($ctag->GetInnerText());
								if($pubdate!="")
								{
									if(!is_numeric($pubdate))
										$pubdate = $sortrank = GetMkTime($pubdate);
									else
										$pubdate = $sortrank = $pubdate;
									$gett=1;
								}
								else
								{
									$pubdate = $sortrank = time();
								}
							}
							else if($itemName == 'body')
							{
								$body = addslashes(trim($ctag->GetInnerText()));
								$body = mb_decode_numericentity($body,array(0,0xffffff,0,0xffffff),'GBK');
								$body = str_replace('&amp;nbsp;','',$body);	
							}
						}
						
						if($body=="" || strlen($body)-mb_strlen($body,'UTF8')<3)
						{
							if($eorid!=$exid)
							{
								$n=0;
								$eorid=$exid;
								$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isdown=0,result='' where aid='$exid' ");
								break;
							}		
							else if($n==$co_retime)
							{
								if($coretag=="re" && $coretimes==$co_retime)
								{
									if($co_nullctr!='2' && $cotype!='2')
									{
										if($booksum2>1)
										{
											$tid2=reveser_c('=>'.$typeid.']');
											$listconfig3=reveser_c($listconfig2);
											$addco_note=reveser_c(substr($listconfig3,stripos($listconfig3,$tid2),stripos($listconfig3,'[',stripos($listconfig3,$tid2))-stripos($listconfig3,$tid2)+1));
											$newbooksum=$booksum2-1;
											$typenamerow = $dsql->GetOne("Select * From `#@__arctype` where id=$typeid ");
											if($renid1=="2" && strstr($notename2,'+')) $nidtype="+";
											elseif($renid1=="1" && strstr($notename2,'-')) $nidtype="-";
											else break;
											$rowlist=explode($nidtype, $notename2);
											$addnotename=$rowlist[0].$nidtype.$typenamerow['typename'];
											$replacetname=$nidtype.$typenamerow['typename'];
											$newnotename=str_replace("-".$typenamerow['typename'],'',str_replace("+".$typenamerow['typename'],'',$notename2));
											$newlistconfig=str_replace("-".$typenamerow['typename'],'',str_replace("+".$typenamerow['typename'],'',str_replace($addco_note,'',$listconfig2)));
											$updatesql="update `#@__co_note` set notename='$newnotename',listconfig='$newlistconfig',booksum=$newbooksum where nid=$nid";
											if($dsql->ExecuteNoneQuery($updatesql))
											{
												$dsql->ExecuteNoneQuery("Delete From `#@__co_htmls` where nid=$nid and typeid=$typeid");
												$co_note="{dede:batchrule}".$addco_note."{/dede:batchrule}";
												$listconfig1 = str_replace($notename2,$addnotename,str_replace(substr($listconfig2,stripos($listconfig2,'{dede:batchrule}'),stripos($listconfig2,'{dede:regxrule}')-stripos($listconfig2,'{dede:batchrule}')-1),$co_note,$listconfig2));
												$cosql = " INSERT INTO `#@__co_note`(`channelid`,`notename`,`sourcelang`,`uptime`,`cotime`,`pnum`,`isok`,`listconfig`,`itemconfig`,`usemore`,booksum,renid,typeid,remark)
												   VALUES ('1','$addnotename','$sourcelang2','".time()."','0','0','1','$listconfig1','$itemconfig2','1','1','$renid2','0','目标站内容为空，ID：$exid'); ";
												$rowsame = $dsql->GetOne("Select count(*) as dd From `#@__co_note` where notename='$addnotename'");
												if($rowsame['dd']==0)
												{
													if($dsql->ExecuteNoneQuery($cosql))
													{
														$cnid = $dsql->GetLastID();
														$dsql->ExecuteNoneQuery("update `#@__arctype` set copynid='$cnid' where id=$typeid ");
														$dsql->ExecuteNoneQuery("update `#@__co_htmls` set nid=$cnid where nid='$nid' and typeid=$typeid ");
													}
												}
											}
										}
										else
										{
											$dsql->ExecuteNoneQuery("update `#@__co_note` set remark= CONCAT(remark,'目标站内容为空，ID：','$exid'),typeid='0' where `nid`=$nid ");
										}
										$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isdown=0,result='' where aid='$exid' ");
									}
									else
									{
										$eorid=0;
									}
								}
								else
								{
									$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isdown=0,result='' where aid='$exid' ");
									$dsql->ExecuteNoneQuery("update `#@__co_note` set remark= CONCAT('re:','$coretime'),cotime=cotime+3600 where `nid`=$nid ");
								}
								$treid2='b';
								break 2;
							}
							else
							{
								$dsql->ExecuteNoneQuery("Update `#@__co_htmls` set isdown=0,isexport=0,result='' where aid='$exid' ");
								break;
							}
						}
						$title = addslashes($title);
						$title1=str_replace($replacearray,'',reveser_c($title));
						if($only)
						{
							$testrow = $dsql->GetOne("Select a.id,b.body From `#@__archives` a left join `#@__addonarticle` b on(b.aid=a.id) where REVERSE(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(replace(a.title,' ',''),'!',''),'！',''),'，',''),',',''),'?',''),'(',''),')',''),'？',''),'（',''),'）',''),'。',''),'：',''),':',''),'.',''),'[',''),']',''),'【',''),'】','')) like '$title1%' and a.typeid='$typeid'");
							if(is_array($testrow))
							{
								$taid=$testrow['id'];
								$tbody=$testrow['body'];
								if($tbody!="")
								{
									$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isexport=1 where aid='$exid' ");
									continue;
								}
								else
								{
									$checkrow = $dsql->GetOne("select * from  `#@__addonarticle` where aid='$taid' ");
									if(is_array($checkrow))
									{
										$dsql->ExecuteNoneQuery("update `#@__addonarticle` set body='$body' where aid='$taid' ");
									}
									else
									{
										$dsql->ExecuteNoneQuery("insert into `#@__addonarticle` (`aid`,`typeid`,`body`,`redirecturl`,`templet`,`userip`)
								   VALUES ('$taid','$typeid','$body','','',''); ");
									}
									$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isexport=1 where aid='$exid' ");
									continue;
								}
							}
						}
						$senddate = time();
						$flag = '';
						$IndSql = str_replace('@senddate@',$senddate,$IndSql);
						$IndSql = str_replace('@sortrank@',$sortrank,$IndSql);
						$MaiSql = str_replace('@flag@',$flag,$MaiSql);
						$MaiSql = str_replace('@sortrank@',$sortrank,$MaiSql);
						$MaiSql = str_replace('@pubdate@',$pubdate,$MaiSql);
						$MaiSql = str_replace('@senddate@',$senddate,$MaiSql);
						$MaiSql = str_replace('@title@',cn_substr($title, 100),$MaiSql);
						$AddSql = str_replace('@body@',$body,$AddSql);
						foreach($dtp->CTags as $ctag)
						{
							
							if($ctag->GetName()!='field') continue;
							$itemname = $ctag->GetAtt('name');
							$itemvalue = addslashes(trim($ctag->GetInnerText()));
							$MaiSql = str_replace("@$itemname@",$itemvalue,$MaiSql);
							if($co_nontext!="")
							{
								$co_nontext=explode("\r\n",$co_nontext);
								$AddSql = str_replace($co_nontext,"",str_replace("@$itemname@",$itemvalue,$AddSql));
							}
							else
								$AddSql = str_replace("@$itemname@",$itemvalue,$AddSql);
						}
						if($dsql->ExecuteNoneQuery($IndSql))
						{
							$aid = $dsql->GetLastID();
							$MaiSql= str_replace('@aid@',$aid,$MaiSql);
							$AddSql = str_replace('@aid@',$aid,$AddSql);
							$MaiSql= ereg_replace('@([a-z0-9]{1,})@','',$MaiSql);
							$AddSql = ereg_replace('@([a-z0-9]{1,})@','',$AddSql);
							if(!$dsql->ExecuteNoneQuery($MaiSql))
							{
								$dsql->ExecuteNoneQuery("Delete From `#@__arctiny` where id='$aid' ");
							}
							else
							{
								if(!$dsql->ExecuteNoneQuery($AddSql))
								{
									$dsql->ExecuteNoneQuery("Delete From `#@__arctiny` where id='$aid' ");
									$dsql->ExecuteNoneQuery("Delete From `$Maitable` where id='$aid' ");
								}
								else
								{
									if($make)
									{
										require_once(DEDEINC."/arc.archives.class.php");
										$ac=new Archives($aid);
										$rurl=$ac->MakeHtml();
										$ac->Close();
										$ac=new Archives($aid-1);
										$rurl=$ac->MakeHtml();
										$ac->Close();
										$dsql->ExecuteNoneQuery("Update `#@__co_note` set cotime='".time()."' where nid='$nid'; ");
									}
									$row = $dsql->GetOne("SELECT a.id,a.title,a.click,a.typeid,b.body,c.typename,c.typedir,c.zuozhe,c.bookclick,c.bookclickm,c.bookclickw,c.tuijian,c.tuijianm,c.tuijianw,c.booksize,c.lastclick,c.lasttuijian,c.startdate,c.reid,d.typename as retypename FROM `$Maitable` a left join `{$Addtable}` b on(a.id=b.aid) left join `#@__arctype` c on(a.typeid=c.id) left join `#@__arctype` d on(c.reid=d.id) WHERE a.id='$aid'");
									if($row)
									{
										$txt_filename=$row['zuozhe']."-".$row['typename'];
										$typename=$row['typename'];
										$typedir=$row['typedir'];
										$txt_comtens="";
										$startdatesql="";
										$txt_title=$row['title'];
										$treid=$row['reid'];
										$tretypename=$row['retypename'];
										$updatetime=time();
										$lastclick=$row['lastclick'];
										$lasttuijian=$row['lasttuijian'];
										if(intval($cfg_weekstart)!='2') $beginweek=mktime(0,0,0,date('m'),date('d')-date('w'),date('Y'));
										else $beginweek=mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y'));
										$beginmonth=mktime(0,0,0,date('m'),1,date('Y'));
										$txt_body=$txt_title."\r\n".$row['body'];
										$txt_click=$row['click']+$row['bookclick'];
										$tuijian=$booktuijian+$row['tuijian'];
										$clickrow=$dsql->GetOne("SELECT MAX(lastclick) as lastclick FROM `#@__arctype` WHERE reid NOT IN (0,45)");
										$lastclickall=$clickrow['lastclick'];
										if($beginmonth<=$lastclick)
											$txt_clickm=$row['click']+$row['bookclickm'];
										else
										{
											$txt_clickm=$row['click'];
											if($row['bookclickm']!=0 && $beginmonth>$lastclickall)
												$dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET bookclickm='0',tuijianm='0',lastclick=$beginmonth ");
										}
										if($beginweek<=$lastclick)
											$txt_clickw=$row['click']+$row['bookclickw'];
										else
										{
											$txt_clickw=$row['click'];
											if($row['bookclickw']!=0 && $beginmonth>$lastclickall)
												$dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET bookclickw='0',tuijianw='0',lastclick=$beginweek ");
										}
										if($beginmonth<=$lasttuijian)
											$tuijianm=$booktuijian+$row['tuijianm'];
										else
											$tuijianm=$booktuijian;
										if($beginweek<=$lasttuijian)
											$tuijianw=$booktuijian+$row['tuijianw'];
										else
											$tuijianw=$booktuijian;
										if($gett==1&&$row['booksize']=='0'&&$row['startdate']=='0')
										{
											$startdate=date("Y-m-d",$pubdate);
											$startdatesql=",startdate='".$startdate."'";
										}
										$a=array('‘','’','“','”','–','—',' ','￠','￡','¤','￥','|','§','◎','《','》','°','±','2','3','1','�','×','÷','"','&','<','>','…','‰','<','>','←','↑','→','↓','-','√','∞','≠','≤','≥');
										$b=array('&lsquo;','&rsquo;','&ldquo;','&rdquo;','&ndash;','&mdash;','&nbsp;','&cent;','&pound;','&curren;','&yen;','&brvbar;','&sect;','&copy;','&raquo;','&laquo;','&deg;','&plusmn;','&sup2;','&sup3;','&sup1;','&euro;','&times;','&divide;','&quot;','&amp;','&lt;','&gt;','&hellip;','&permil;','&lsaquo;','&rsaquo;','&larr;','&uarr;','&rarr;','&darr;','&minus;','&radic;','&infin;','&ne;','&le;','&ge;');
										$txt_body=str_replace($b,$a,str_replace("<br />","\r\n",str_replace("<br>","\r\n",trim($txt_body))));
										$txt_body=str_replace("<p>","\r\n",str_replace("</p>","\r\n",trim($txt_body)));
										$txt_body=str_replace("\r\n\r\n","\r\n",str_replace("\r\n\r\n\r\n","\r\n",str_replace("\r\n\r\n\r\n\r\n","\r\n",$txt_body)));
										$acrlen=round(strlen($txt_body)/2.05);
										$booksize=$acrlen+$row['booksize'];							
										if(floor($row['booksize']/$co_addtxtpsize)<floor($booksize/$co_addtxtpsize) || $row['booksize']=='0')
										{
											$txt_comtens=str_replace("http://","",str_replace(array("[网站名称]","[站点根网址]"),array($cfg_webname,$cfg_basehost),$co_addtxttext))."\r\n\r\n";
										}
										$dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET bookclick='$txt_click',bookclickm='$txt_clickm',bookclickw='$txt_clickw',tuijian='$tuijian',tuijianm='$tuijianm',tuijianw='$tuijianw',booksize='$booksize',lastupdate='$updatetime',lastclick='$updatetime',lasttuijian='$updatetime'$startdatesql WHERE id='$typeid' ");
										$txt_comtens.=$txt_body."\r\n"."\r\n";
										$startrow = $dsql->GetOne("SELECT COUNT(id) as dd FROM `#@__archives` WHERE typeid='$typeid' ");
										if($startrow['dd']=='1')
										{
											$dsql->ExecuteNoneQuery("UPDATE `#@__arctype` SET startaid='$aid',bookclick='".$row['click']."',bookclickm='".$row['click']."',bookclickw='".$row['click']."',tuijian='$booktuijian',tuijianm='$booktuijian',tuijianw='$booktuijian',booksize='$acrlen',lastupdate='$updatetime',lastclick='$updatetime',lasttuijian='$updatetime',downloadurl='/download/".$txt_filename.".txt' WHERE id='$typeid' ");
											if($co_autotxt!='2' && $channelid==1)
											{
												$file = fopen("$txt_filename.txt","ab");
												fwrite($file,$txt_comtens);
												fclose($file);
											}
										}
										elseif($co_autotxt!='2' && $channelid==1)
										{
											if(!file_exists("$txt_filename.txt"))
											{
												maketxt($typeid);
											}
											else
											{
												$file = fopen("$txt_filename.txt","ab");
												fwrite($file,$txt_comtens);
												fclose($file);
											}
										}
										if($pretid!=$typeid && $pretid!='c')
										{
											$tid=$pretid;
											$dsql->ExecuteNoneQuery("update `#@__arctype` set copynid='$nid' where id='$tid'");
											flushtemp( $tid );
											if($treid2!=$treid && $treid2!='b')
											{
												$tid=$treid2;
												flushtemp( $tid );
											}
										}
										$treid2=$row['reid'];
										$tretypename2=$row['retypename'];
										$pretid=$typeid;
										$ptypename=$row['typename'];
									}
								}
							}
							$dsql->ExecuteNoneQuery("update `#@__co_htmls` set isexport=1 where aid='$exid' ");
							$dsql->ExecuteNoneQuery("update `#@__co_note` set remark='' where nid='$nid' ");
						}
					}
					$eorid=0;
				}
			}
			$conrow = $dsql->GetOne("Select count(aid) as dd From `#@__co_htmls` where nid='$nid' and isdown=0");
			$nidcon=$conrow['dd'];
			$dsql->ExecuteNoneQuery("update `#@__co_note` set con=$nidcon,isok=1,listconfig=REPLACE(REPLACE(REPLACE(listconfig,'\r\n\r\n[(#)=>','\r\n[(#)=>'),'][(#)=>',']\r\n[(#)=>'),'\r\n{/dede:batchrule}','{/dede:batchrule}') where nid='$nid' ");
			if($makeindex=='yes' && $treid2!='b')
			{
				$tid=$typeid;
				$dsql->ExecuteNoneQuery("update `#@__arctype` set copynid='$nid' where id='$tid'");
				include_once(DEDEINC."/arc.listview.class.php");
				flushtemp( $tid );
				$tid=$treid;
				flushtemp( $tid );
				c_cache( "../data/tplcache" );
				$homeFile =dirname(__FILE__)."/../html/index.html";
				$cfg_autoindex_time=(intval($cfg_autoindex_time)>=0) ? intval($cfg_autoindex_time):600;
				if(abs(time()-@filemtime($homeFile))>$cfg_autoindex_time)
				{
					$tid='';
					$dsql->ExecuteNoneQuery("Delete From `#@__arccache`");
					include_once(DEDEINC."/arc.partview.class.php");
					$row = $dsql->GetOne("Select * From `#@__homepageset`");
					$row['templet'] = MfTemplet($row['templet']);
					$pv = new PartView(0,true,"index");
					$pv->SetTemplet($cfg_basedir . $cfg_templets_dir . "/" . $row['templet']);
					$pv->SaveToHtml('../html/index.html');
					$tid='375';
					$paihangbangtemplet=$cfg_df_style."/paihang.htm";
					$paihangbangtemplet = $cfg_basedir.$cfg_templets_dir."/".$paihangbangtemplet;
					if(file_exists($paihangbangtemplet))
					{
						$lv = new ListView($tid,"","");
						$reurl = $lv->MakeHtml();
						$lv->Close();
					}
					$sitemaptemplet=$cfg_df_style."/sitemap.htm";
					$pv2 = new PartView(0,true,"sitemap");
					$pv2->SetTemplet($cfg_basedir.$cfg_templets_dir."/".$sitemaptemplet);
					$sitemapFile ="../sitemap.html";
					$pv2->SaveToHtml($sitemapFile);
				}
				$sitemapFile =dirname(__FILE__)."/../baidu_sitemap.xml";
				$cfg_updateperi=(intval($cfg_updateperi)>=0) ? intval($cfg_updateperi)*60:21600;
				if(abs(time()-@filemtime($sitemapFile))>$cfg_updateperi)
				{
					$sitemaptemplet=$cfg_df_style."/google_sitemap.htm";
					$pv2 = new PartView(0,true,"sitemap");
					$pv2->SetTemplet($cfg_basedir.$cfg_templets_dir."/".$sitemaptemplet);
					$sitemapFile ="../sitemap.xml";
					$pv2->SaveToHtml($sitemapFile);
					$sitemaptemplet=$cfg_df_style."/baidu_sitemap.htm";
					$pv2 = new PartView(0,true,"baidu_sitemap");
					$pv2->SetTemplet($cfg_basedir.$cfg_templets_dir."/".$sitemaptemplet);
					$sitemapFile ="../baidu_sitemap.xml";
					$pv2->SaveToHtml($sitemapFile);
				}
			}
			if($nidcon=='0' and $channelid==1 and $booksum2>1 and $cotype>0)
			{
				preg_match_all('/typeid=>([0-9]+)]/', $listconfig2, $tidarr);
				$co_stopnonupdate=(intval($co_stopnonupdate)>=0) ? intval($co_stopnonupdate):20;
				$curtime=time()-($co_stopnonupdate*86400);
				for($a=0;$a<count($tidarr[1]);$a++)
				{
					$endrow = $dsql->GetOne("SELECT a.id,a.typename,FROM_UNIXTIME(a.lastupdate) AS lastt FROM #@__arctype a WHERE a.id=".$tidarr[1][$a]." and a.lastupdate<$curtime");
					if(is_array($endrow))
					{
						$tid2=reveser_c('=>'.$tidarr[1][$a].']');
						$tidlen=strlen($tid2);
						$listconfig3=reveser_c($listconfig2);
						$addco_note=reveser_c(substr($listconfig3,stripos($listconfig3,$tid2),stripos($listconfig3,'[',stripos($listconfig3,$tid2))-stripos($listconfig3,$tid2)+1));
						$row = $dsql->GetOne("Select * From `#@__co_note` where nid='$nid' and booksum>1");
						if($row)
						{
							foreach($row as $k=>$v)
							{
								$$k = addslashes($v);
							}
							$newbooksum=$booksum-1;
							$replacetname="+".$endrow['typename'];
							$newnotename=str_replace($replacetname,'',$notename);
							$newlistconfig=str_replace($replacetname,'',str_replace($addco_note,'',$listconfig));
							$updatesql="update `#@__co_note` set notename='$newnotename',listconfig='$newlistconfig',booksum=$newbooksum where nid=$nid";
							if($dsql->ExecuteNoneQuery($updatesql))
							{
								$renid=str_replace('-2','-1',$renid);
								if($renid1=="2" && strstr($notename,'+')) $nidtype="+";
								elseif($renid1=="1" && strstr($notename,'-')) $nidtype="-";
								else break;
								$rowlist=explode($nidtype, $notename);
								$addnotename=$rowlist[0]."-".$endrow['typename'];
								$co_note="{dede:batchrule}".$addco_note."{/dede:batchrule}";
								$listconfig1 = str_replace($notename,$addnotename,str_replace(substr($listconfig,stripos($listconfig,'{dede:batchrule}'),stripos($listconfig,'{dede:regxrule}')-stripos($listconfig,'{dede:batchrule}')-1),$co_note,$listconfig));
								$cosql = " INSERT INTO `#@__co_note`(`channelid`,`notename`,`sourcelang`,`uptime`,`cotime`,`pnum`,`isok`,`listconfig`,`itemconfig`,`usemore`,booksum,renid,typeid)
								   VALUES ('1','$addnotename','$sourcelang','".time()."','0','0','1','$listconfig1','$itemconfig','1','1','$renid','1'); ";
								$rowsame = $dsql->GetOne("Select count(*) as dd From `#@__co_note` where notename='$addnotename'");
								if($rowsame['dd']==0)
								{
									if($dsql->ExecuteNoneQuery($cosql))
									{
										$cnid = $dsql->GetLastID();
										$dsql->ExecuteNoneQuery("update `#@__arctype` set copynid='$cnid' where id='".$tidarr[1][$a]."' ");
									}
								}
							}
						}
					}
				}
			}
		}
	}
}
$dsql->Close();
?>