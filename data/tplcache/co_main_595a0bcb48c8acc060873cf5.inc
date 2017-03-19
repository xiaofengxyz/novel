<?php
if(!isset($GLOBALS['_vars'])) $GLOBALS['_vars'] = array(); 
$fields = array();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
<title>采集规则管理</title>
<script language="javascript" src="js/context_menu.js"></script>
<script language="javascript" src="js/ieemu.js"></script>
<script language="javascript">
function getCheckboxItem()
{
	var allSel="";
	if(document.form1.nids.value) return document.form1.nids.value;
	for(i=0;i<document.form1.nids.length;i++)
	{
		if(document.form1.nids[i].checked)
		{
			if(allSel=="")
				allSel=document.form1.nids[i].value;
			else
				allSel=allSel+","+document.form1.nids[i].value;
		}
	}
	return allSel;	
}
function ReSel()
{
	for(i=0;i<document.form1.nids.length;i++)
	{
		if(document.form1.nids[i].checked) document.form1.nids[i].checked = false;
		else document.form1.nids[i].checked = true;
	}
}
if(moz) {
	extendEventObject();
	extendElementModel();
	emulateAttachEvent();
}
//编辑规则
function EditNote(nid)
{
	if(nid=="") nid = getOneItem();
  if(nid==""){ alert("请选择一个规则！"); return;}
	location.href = "co_edit.php?nid="+nid;
}
//编辑规则配置
function EditNoteText(nid)
{
	if(nid=="") nid = getOneItem();
  if(nid==""){ alert("请选择一个规则！"); return;}
	location.href = "co_edit_text.php?nid="+nid;
}
//清空规则
function ClearNote(nid)
{
	if(nid=="") nid = getCheckboxItem();
	if(nid==""){ alert("请选择一个规则！"); return;}
	location.href = "co_do.php?dopost=clear&nid="+nid;
}
//变更状态
function changerenid(nid)
{
	if(nid=="") nid = getCheckboxItem();
	if(nid==""){ alert("请选择一个规则！"); return;}
	location.href = "co_do.php?dopost=changerenid&nid="+nid;
}
//删除规则
function DelNote(nid)
{
	if(nid=="") nid = getCheckboxItem();
	if(nid==""){ alert("请选择一个规则！"); return;}
	if(window.confirm('你确定要删除这些规则吗?'))
	{ location.href = "co_do.php?dopost=delete&nid="+nid; }
}
//设置为半自动
function setauto(nid)
{
	if(nid=="") nid = getCheckboxItem();
	if(nid==""){ alert("请选择一个规则！"); return;}
	if(window.confirm('你确定要把这些规则设置为半自动吗?'))
	{ location.href = "co_do.php?dopost=auto&nid="+nid; }
}
//设置为全自动
function setnoeor(nid)
{
	if(nid=="") nid = getCheckboxItem();
	if(nid==""){ alert("请选择一个规则！"); return;}
	if(window.confirm('你确定要把这些规则设置为全自动吗?'))
	{ location.href = "co_do.php?dopost=noeor&nid="+nid; }
}
//设置为手动
function setnoco(nid)
{
	if(nid=="") nid = getCheckboxItem();
	if(nid==""){ alert("请选择一个规则！"); return;}
	if(window.confirm('你确定要把这些规则设置为手动吗?'))
	{ location.href = "co_do.php?dopost=noco&nid="+nid; }
}
//替换规则
function setcgn(nid)
{
	if(nid=="") nid = getCheckboxItem();
	if(nid==""){ alert("请选择一个规则！"); return;}
	if(window.confirm('你确定要替换这些规则吗?'))
	{ location.href = "co_do.php?dopost=setcgn&nid="+nid; }
}
//查看已下载的内容
function ViewDown(nid)
{
	if(nid=="") nid = getOneItem();
	location.href = "co_url.php?nid="+nid;
}
//测试规则
function TestRule(nid)
{
	if(nid=="") nid = getOneItem();
	if(nid==""){ alert("请选择一个规则！"); return;}
	location.href = "co_test_rule.php?nid="+nid;
}
//导出采集
function ExportDown(nid)
{
	if(nid=="") nid = getOneItem();
	if(nid==""){ alert("请选择一个规则！"); return;}
	location.href = "co_export.php?nid="+nid;;
}
function bang(nid)
{
	if(nid=="") nid = getOneItem();
	if(nid==""){ alert("请选择一个规则！"); return;}
	location.href = "co_bang.php?nid="+nid;;
}
//导入规则
function GetRule()
{
	location.href = "co_get_corule.php";
}
//导出规则
function ExportRule(nid)
{
	if(nid=="") nid = getOneItem();
	if(nid==""){ alert("请选择一个规则！"); return;}
	location.href = "co_export_corule.php?nid="+nid;
}

//采集所选规则
function GatherSel(nid)
{
	if(nid=="") nid = getCheckboxItem();
	if(nid==""){ window.open("../download/caiji.htm");return;}
	window.open("../download/caiji.php?nid="+nid);
}
//复制所选规则
function CopyNote(nid)
{
	if(nid=="") nid = getOneItem();
	if(nid==""){ alert("请选择一个规则！"); return;}
	location.href = "co_do.php?dopost=copy&nid="+nid;
}
//上下文菜单
function ShowMenu(evt,obj,nid)
{
  var eobj,popupoptions
  popupoptions = [
    new ContextItem("测试当前规则",function(){ TestRule(nid); }),
    new ContextItem("采集当前规则",function(){ GatherSel(nid); }),
    new ContextSeperator(),
    new ContextItem("更改当前规则",function(){ EditNote(nid); }),
    new ContextItem("更改规则配置",function(){ EditNoteText(nid); }),
    new ContextSeperator(),
    new ContextItem("查看采集内容",function(){ ViewDown(nid); }),
    new ContextItem("导出采集",function(){ ExportDown(nid); }),
    new ContextSeperator(),
    new ContextItem("清空已下载信息",function(){ ClearNote(nid); }),
    new ContextItem("删除当前规则",function(){ DelNote(nid); }),
    new ContextSeperator(),
    new ContextItem("复制当前规则",function(){ CopyNote(nid); }),
    new ContextSeperator(),
    new ContextItem("导出配置规则",function(){ ExportRule(nid); }),
    new ContextItem("关闭菜单(<u>C</u>)",function(){})
  ]
  ContextMenu.display(evt,popupoptions)
}
//获得选中其中一个的id
function getOneItem()
{
	var allSel="";
	if(document.form1.nids.value) return document.form1.nids.value;
	for(i=0;i<document.form1.nids.length;i++)
	{
		if(document.form1.nids[i].checked)
		{
				allSel = document.form1.nids[i].value;
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
<body background='images/allbg.gif' leftmargin='8' topmargin='8' onLoad="ContextMenu.intializeContextMenu()">
<table border="0" cellpadding="3" cellspacing="1" bgcolor="#D1DDAA" align="left">
  <tr> 
    <td height="20" colspan="10" background='images/tbg.gif'> <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr> 
          <td width="20%" height="18"><strong>采集规则管理：</strong></td>
          <td width="80%" align="right">采集间隔在系统基本参数-模块管理下设置，点击手动可取消采集该规则<input type="button" name="ba" value="替换词设置" onClick="location='pr_str_replace.php';" class="coolbg np" style="width:100px" />
          	<input type="button" name="ba" value="添加新规则" onClick="location='co_add.php?action=select';" class="coolbg np" style="width:100px" /> 
          </td>
        </tr>
      </table></td>
  </tr>
  <tr align="center" bgcolor="#FAFAF1"> 
    <td width="30px">选择</td>
    <td width="300px">规则名称</td>
	<td width="420px">规则操作</td>
	<td width="60px">采集类型</td>
    <td width="60px">规则类型</td>
    <td width="120px">最后采集时间</td>
    <td width="120px">加入日期</td>
    <td width="40px">编码</td>
    <td width="100px">网址数</td>
    <td width="100px">备注</td>
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
    <tr bgcolor="#FFFFFF" align="center" onMouseMove="javascript:this.bgColor='#ddffff';"
    	 onMouseOut="javascript:this.bgColor='#FFFFFF';" height="22" oncontextmenu="ShowMenu(event,this,<?php echo $fields['nid']; ?>);"> 
      <td><input type='checkbox' name='nids' value='<?php echo $fields['nid']; ?>' class='np' onclick="tr_bgcolor(this);"></td>
      <td align="left"><a href='co_url.php?nid=<?php echo $fields['nid']; ?>' title='查看已经采集的内容'>[ID:<?php echo $fields['nid']; ?>]<?php echo $fields['notename']; ?></a></td>
      <td>[<a href='co_edit.php?nid=<?php echo $fields['nid']; ?>'>编辑</a>] [<a href='../download/caiji.php?nid=<?php echo $fields['nid']; ?>' target="_blank" title="注意：非自动的也会采集！">采集</a>] [<a href='co_do.php?dopost=clear&nid=<?php echo $fields['nid']; ?>'>清空</a>] [<a href='co_do.php?dopost=auto&nid=<?php echo $fields['nid']; ?>' title="注意：设置后碰到错误时将停止本规则的采集，需要手动处理后才能继续采集！">半自动</a>] [<a href='co_do.php?dopost=noeor&nid=<?php echo $fields['nid']; ?>' title="注意：设置后碰到错误都将自动跳过并继续采集，可能会出现漏章！">全自动</a>] [<a href='co_do.php?dopost=noco&nid=<?php echo $fields['nid']; ?>' title="注意：取消自动采集！">手动</a>] [<a href='co_hebing.php?nid=<?php echo $fields['nid']; ?>'>合并</a>] [<a href='co_do.php?dopost=setcgn&nid=<?php echo $fields['nid']; ?>' title="从其他网站采集本小说，仅对只有一本小说的规则生效">换站</a>] [<a href='co_do.php?dopost=setautocgn&nid=<?php echo $fields['nid']; ?>' title="自动换站采集">自动换站</a>] [<a href='co_do.php?dopost=changerenid&nid=<?php echo $fields['nid']; ?>' title="改变完本或连载状态！">变更状态</a>]</td>
	  <td><?php echo typeye($fields['typeid']); ?></td>
      <td><?php echo type($fields['typename']); ?></td>
      <td><?php echo GetDatePage($fields['cotime']); ?></td>
      <td><?php echo date("y-m-d H:i",$fields['uptime']); ?></td>
      <td><?php echo $fields['sourcelang']; ?></td>
      <td><?php echo TjUrlNum($fields['nid']); ?></td>
	  <td><?php echo $fields['remark']; ?></td>
    </tr>
    <?php
}
}?> 
  </form>
  <tr bgcolor="#FAFAF1"> 
    <td height="24" colspan="10"> 
      <table width="90%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td>
			<input type="button" name="b11" value="反选" class="coolbg np" style="width:40px" onClick="ReSel();" /> 
          	&nbsp;
          	<a href="#" onClick="location='co_add.php?action=select';" class='coolbg np'>增加新规则</a>
            <a href="javascript:GetRule();" class='coolbg np'>导入规则</a>
            <a href="javascript:ExportRule('');" class='coolbg np'>导出规则</a> 
             | 
            <a href="../download/caiji.htm" target="_blank" class='coolbg np' style='color:red' title="打开全自动采集页面">开始采集</a>
            <a href="javascript:setauto('');" class='coolbg np' title="将所选规则设置为半自动采集模式">设置为半自动</a>
            <a href="javascript:setnoeor('');" class='coolbg np' style='color:blue' title="将所选规则设置为全自动采集模式">设置为全自动</a>
            <a href="javascript:setnoco('');" class='coolbg np' style='color:blue' title="取消所选规则的自动采集">设置为手动</a>
            <a href="javascript:setcgn('');" class='coolbg np' style='color:blue' title="从其他网站采集所选规则内的小说">换站</a>
             |
            <a href="javascript:CopyNote('');" class='coolbg np'>复制</a>
            <a href="javascript:ClearNote('');" class='coolbg np' title="清空选定规则中已采集的内容，但不会清空已入库的文章">清空</a> 
             | 
            <a href="javascript:DelNote('');" class='coolbg np'>删除</a>
           </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr bgcolor="#FAFAF1"> 
    <td height="24" colspan="10" align="center">
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
    <td colspan="10">
	<font color="red">说明：<br />1、全自动：如果目标站章节内容为空或者不能正常访问时，则自动跳过，继续采集后面的章节。<br />2、半自动：如果目标站章节内容为空或者不能正常访问时，则停止采集该规则，等待手动处理。<br />3、手动：停止采集该规则，可手动点“采集”进行单条采集。<br />4、所有“章节采集模板”都不能设置为全自动或者半自动，更不能删除。<br />5、网址数：三个数字分别代表：需要采集的网址/已采集的/已入库的。点击后面两个数字可以查看目前采集到的内容。<br />6、换站采集：从目标站A转换到目标站B采集，必须先有目标站B的采集规则。<br />7、采集前必须先看安装使用说明，每个按钮都有提示信息，要先看清楚再操作。</font>
	</td>
  </tr>
</table>
</body>
</html>