<?php
if(!isset($GLOBALS['_vars'])) $GLOBALS['_vars'] = array(); 
$fields = array();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
<title>小说管理</title>
<script language="javascript">
function getCheckboxItem()
{
	var allSel="";
	if(document.form1.ids.value) return document.form1.ids.value;
	for(i=0;i<document.form1.ids.length;i++)
	{
		if(document.form1.ids[i].checked)
		{
			if(allSel=="")
				allSel=document.form1.ids[i].value;
			else
				allSel=allSel+","+document.form1.ids[i].value;
		}
	}
	return allSel;	
}
function ReSel()
{
	for(i=0;i<document.form1.ids.length;i++)
	{
		if(document.form1.ids[i].checked) document.form1.ids[i].checked = false;
		else document.form1.ids[i].checked = true;
	}
}
//生成txt
function maketxt_select(id)
{
	if(id=="") id = getCheckboxItem();
	if(id=="")	{alert("请至少选择一本小说！");return;}
	if(window.confirm('你确定要重新生成这些小说的txt文件吗?'))
	{ location.href = "co_do.php?dopost=maketxt&id="+id; }
}
function maketxt_all()
{
	if(window.confirm('你确定要重新生成所有小说的txt文件吗?'))
	{ location.href = "/download/totxt.php"; }
}
//删除小说
function deletebook_select(id)
{
	if(id=="") id = getCheckboxItem();
	if(id=="")	{alert("请至少选择一本小说！");return;}
	if(window.confirm('你确定要删除这些小说吗?'))
	{ location.href = "catalog_del.php?dopost=delselect&typeid="+id; }
}
//清空小说
function cleanselect(id)
{
	if(id=="") id = getCheckboxItem();
	if(id=="")	{alert("请至少选择一本小说！");return;}
	if(window.confirm('你确定要删除这些小说吗?'))
	{ location.href = "catalog_del.php?dopost=cleanselect&typeid="+id; }
}
function setovertag(id)
{
	if(id=="") id = getCheckboxItem();
	if(id=="")	{alert("请至少选择一本小说！");return;}
	if(window.confirm('你确定设置为完本吗?'))
	{ location.href = "catalog_do.php?dopost=setovertag&typeid="+id; }
}
function checkArchives(id)
{
	if(id=="") id = getCheckboxItem();
	if(id=="")	{alert("请至少选择一本小说！");return;}
	if(window.confirm('你确定检测并去除这些小说的重复章节吗?'))
	{ location.href = "catalog_do.php?dopost=checkArchives&typeid="+id; }
}
function checktitles(id)
{
	if(id=="") id = getCheckboxItem();
	if(id=="")	{alert("请至少选择一本小说！");return;}
	if(window.confirm('你确定去除这些小说中多余的字符吗（例如：第*卷、正文等）?'))
	{ location.href = "catalog_do.php?dopost=checktitles&typeid="+id; }
}
//刷新缓存
function flushtemp_select(id)
{
	if(id=="") id = getCheckboxItem();
	if(id=="")	{alert("请至少选择一本小说！");return;}
	if(window.confirm('你确定要重新生成这些小说的封面页吗?'))
	{ location.href = "catalog_do.php?dopost=flushtemp&typeid="+id; }
}
function flushtemp_all()
{
	alert("要更新所有的缓存？请移步菜单：【核心】→【批量维护】→【更新系统缓存】！");
}
//移动小说
function movebook(id)
{
	if(id=="") id = getCheckboxItem();
	if(id==""){ alert("请至少选择一本小说！"); return;}
	location.href = "catalog_do.php?dopost=moveCatalog&typeid="+id;
}
//获得选中其中一个的id
function getOneItem()
{
	var allSel="";
	if(document.form1.ids.value) return document.form1.ids.value;
	for(i=0;i<document.form1.ids.length;i++)
	{
		if(document.form1.ids[i].checked)
		{
				allSel = document.form1.ids[i].value;
				break;
		}
	}
	return allSel;	
}
//点击该行可以改变该行的颜色
var currentActiveRow;
function changeActiveRow(obj) {
	currentActiveRow = obj;
	var rowcolor;
	rowcolor=currentActiveRow.style.backgroundColor;
	if (rowcolor=="#F2F2F2"){
		currentActiveRow.style.backgroundColor = "";
	}
	else
	{
		currentActiveRow.style.backgroundColor = "#F2F2F2";
	}
}
//所有勾选的行都改变颜色
function tr_bgcolor(c){
var tr = c.parentNode.parentNode;
tr.rowIndex % 1 == 1 ? tr.style.backgroundColor = c.checked ? '#ffff00' : '#FFFFF' : tr.style.backgroundColor = c.checked ? '#ffff00' : '';
} 
</script>
<link href="css/base.css" rel="stylesheet" type="text/css">
</head>
<body background='images/allbg.gif' leftmargin='8' topmargin='8'>
<table border="0" width="99%" cellspacing="1" bgcolor="#D1DDAA" align="left">
  <tr> 
    <td colspan="7"> <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr> 
          <td><div class="hd">
			<div class="filter even">
				<span>类型：</span>
				<?php echo $typelink; ?>
				<a href="catalog_add.php?listtype=all"><font color="blue">【添加分类】</font></a>
			</div>
			<div class="filter">
				<span>排序：</span>
				<?php echo $orderlink; ?>
			</div>
			<div class="filter even">
				<span>状态：</span>
				<?php echo $overink; ?>
			</div>
		</div></td>
        </tr>
      </table></td>
  </tr>
  <tr bgcolor="#FAFAF1"> 
    <td colspan="7">
	<form name="From" method="post" action="book_admin.php?action=search" class="search-form">
		<table>
			<tr>
				<td><input type="text" name="wd" class="text-border vm" value="" /></td>
				<td width="8"></td>
				<td width="70"><input type="submit" class="btn btn-auto btn-blue vm" value="搜索" /></td>
			</tr>
		</table>
	</form>
	</td>
  </tr>
  <tr align="center" bgcolor="#FAFAF1"> 
    <td width="4%">选择</td>
    <td width="4%">ID</td>
    <td width="19%">名称[作者](总点击/月点击/周点击)</td>
	<td width="6%">分类</td>
    <td width="4%">状态</td>
	<td width="49%">最新章节</td>
    <td width="14%">操作</td>
  </tr>
  <form name="form1">
    <?php
$atts = array();
$atts['tagname'] = 'datalist';
$blockValue = $this->refObj->GetArcList($atts,$this->refObj,$fields); 
if(is_array($blockValue)){
foreach( $blockValue as $key=>$fields )
{
?> 
    <tr bgcolor="#FFFFFF" align="center" onMouseMove="javascript:this.bgColor='#ddffff';" onMouseOut="javascript:this.bgColor='#FFFFFF';" height="22" > 
      <td><input type='checkbox' name='ids' value='<?php echo $fields['id']; ?>' class='np' onclick="tr_bgcolor(this);"></td>
      <td><?php echo $fields['id']; ?></td>
	  <?php if($fields['reid']==0){ ?>
	  <td align="left"><a href='book_admin.php?action=book&tid=<?php echo $fields['id']; ?>' title='查看本类小说'><?php echo $fields['typename']; ?></a></td>
	  <?php }elseif($fields['reid']==45){ ?>
	  <td align="left"><a href='book_admin.php?action=search&wd=<?php echo $fields['typename']; ?>' title='查看该作者的所有小说'><?php echo $fields['typename']; ?></a></td>
	  <?php }else{ ?>
      <td align="left"><a href='catalog_do.php?cid=<?php echo $fields['id']; ?>&dopost=listArchives' title='查看章节列表' target='_blank'><?php echo $fields['typename']; ?></a>[<a href='book_admin.php?action=search&wd=<?php echo $fields['zuozhe']; ?>' style='color:#066' title='查看该作者的所有小说'><?php echo $fields['zuozhe']; ?></a>](<?php echo $fields['bookclick']; ?>/<?php echo $fields['bookclickm']; ?>/<?php echo $fields['bookclickw']; ?>)</td>
	  <?php } ?>
	  <td><?php echo str_replace("·","",$fields['retypename']); ?></td>
	  <?php if($fields['reid']==0||$fields['reid']==45){ ?>
      <td></td>
      <td></td>
	  <?php }else{ ?>
      <td><?php echo zhuangtai($fields['overdate']); ?></td>
      <td align="left"><?php
	  $lastupdate=$fields['lastupdate'];
	  $overdate=$fields['overdate'];
	  $cotype=$fields['cotype'];
	  $copynid=$fields['copynid'];
	  $notename=$fields['notename'];
	  $typeName=$fields['typename'];
	  $id=$fields['id'];
	  if(time()-$lastupdate>172800)
		{
			if($notename!="")
			{
				$cotype=($cotype=='0') ? "采集已停止":"正在采集中";
				$lastupdate1="【<a href=\"co_do.php?dopost=setcgn&nid=".$copynid."&typename=".$typeName."\" target=\"_blank\" title=\"从其他站采集该小说\">换站采集</a>】<a href=\"co_url.php?nid=".$copynid."\" target=\"_blank\" title=\"".$cotype.",点击打开采集内容列表\"><font color='red'>".date("y-m-d H:i",$lastupdate)."</font></a>";
			}
			else
			{
				$lastupdate1="<font color='red'>【<a href=\"co_do.php?dopost=copy&bookid=".$id."&typename=".$typeName."\" target=\"_blank\" title=\"该小说的采集规则不存在或者被删除，点击添加\">添加规则</a>】".date("y-m-d H:i",$lastupdate)."</font>";
			}
		}
		else $lastupdate1="【<a href=\"co_do.php?dopost=setcgn&nid=".$copynid."&typename=".$typeName."\" target=\"_blank\" title=\"从其他站采集该小说\">换站采集</a>】<a href=\"co_url.php?nid=".$copynid."\" target=\"_blank\" title=\"正在采集中,点击打开采集内容列表\">".date("y-m-d H:i",$lastupdate)."</a>";
		if($overdate!="0")
		{
			if($copynid=="8388607" || $copynid=="" || $notename=="")
				$sumact = "【<font color='blue'>已采集完</font>】".date("y-m-d H:i",$lastupdate);
			else
				$sumact = $lastupdate1;
		}
		else
		{
			$sumact = $lastupdate1;
		}
		echo $sumact."：".zhangjie($id);
	  ?>
	  </td>
	  <?php }  if($fields['reid']==0){ ?>
      <td><a href='<?php echo $fields['typedir']; ?>/' target='_blank' title='查看前台效果'>预览</a>|<a href='catalog_add.php?id=<?php echo $fields['id']; ?>' title='添加新的小说到该分类下'>添加</a>|<a href='catalog_edit.php?id=<?php echo $fields['id']; ?>' title='修改分类信息'>修改</a>|<a href='catalog_del.php?id=<?php echo $fields['id']; ?>&typeoldname=<?php echo urlencode($fields['typename']); ?>' title='删除改分类'>删除</a>|<a href='catalog_edit.php?action=3&id=<?php echo $fields['id']; ?>' title='指定自动采集到该分类下的目标站小说分类'>采集内容</a></td>
	  <?php }elseif($fields['reid']==45){ ?>
	  <td><a href='<?php echo $fields['typedir']; ?>.html' target='_blank' title='查看前台效果'>预览</a>|<a href='catalog_edit.php?id=<?php echo $fields['id']; ?>' title='修改信息'>修改</a>|<a href='catalog_del.php?id=<?php echo $fields['id']; ?>&typeoldname=<?php echo urlencode($fields['typename']); ?>'>删除</a></td>
	  <?php }else{ ?>
	  <td><a href='<?php echo $fields['typedir']; ?>/' target='_blank' title='查看前台效果'>预览</a>|<a href='catalog_edit.php?id=<?php echo $fields['id']; ?>' title='修改小说信息'>修改</a>|<a href='catalog_do.php?dopost=moveCatalog&typeid=<?php echo $fields['id']; ?>' title='移动到其他分类'>移动</a>|<a href='catalog_del.php?id=<?php echo $fields['id']; ?>&typeoldname=<?php echo urlencode($fields['typename']); ?>' title='删除该小说'>删除</a></td>
	  <?php }?>
    </tr>
    <?php
}
}?> 
  </form>
  <tr bgcolor="#FAFAF1"> 
    <td height="24" colspan="7"> 
      <table width="98%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td>
			<input type="button" name="b11" value="反选" class="coolbg np" style="width:40px" onClick="ReSel();" /> 
          	&nbsp;
          	<a href="javascript:maketxt_select('');" class='coolbg np' title='重新生成选定小说的txt电子书'>生成TXT</a>
            <a href="javascript:maketxt_all();" class='coolbg np' title='重新生成全部小说的txt电子书'>生成全部txt</a> 
             | 
            <a href="javascript:deletebook_select('');" class='coolbg np' title='删除选定的小说'>删除小说</a>
             | 
            <a href="javascript:cleanselect('');" class='coolbg np' title='清空选定小说的所有章节'>清空章节</a>
            <a href="javascript:checkArchives('');" class='coolbg np' title='去除选定小说的重复章节'>去重章节</a>
            <a href="javascript:checktitles('');" class='coolbg np' title='去除章节标题中多余的字符（例如：第*卷、正文等）'>整理章节标题</a>
             | 
            <a href="javascript:flushtemp_select('');" class='coolbg np' title='更新选定小说的封面和章节列表页'>更新缓存</a>
            <a href="javascript:setovertag('');" class='coolbg np' title='将选定的小说设置为完本'>设为完本</a>
            <a href="javascript:movebook('');" class='coolbg np' title='移动选定的小说到其他分类'>移动分类</a>
           </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr bgcolor="#FAFAF1"> 
    <td height="24" colspan="7" align="center">
    	<?php
$atts = array();
$atts['tagname'] = 'pagelist';
$atts['listsize'] = '5';
$atts['listitem'] = 'info,index,end,pre,next,pageno,select';
 echo $this->refObj->GetPageList($atts,$this->refObj,$fields); 
?>

    </td>
  </tr>
  <tr bgcolor="#FAFAF1"> 
    <td colspan="7">
	<font color="red">说明：<br />1、要删除所有小说或者清空所有章节，请重装一次！<br />2、要更新所有小说的封面和章节列表页，请移步左边菜单：【核心】→【批量维护】→【更新系统缓存】<br />3、小说章节是循环采集的，默认一次采集10章，如果与目标站的章节不符，可能是还没有采集完，请耐心等待。<br />4、所有的按钮和链接都有提示信息，请先看清楚再操作。</font>
	</td>
  </tr>
</table>
</body>
</html>