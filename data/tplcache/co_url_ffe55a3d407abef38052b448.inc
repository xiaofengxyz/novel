<?php
if(!isset($GLOBALS['_vars'])) $GLOBALS['_vars'] = array(); 
$fields = array();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg_soft_lang; ?>">
<title>采集内容管理</title>
<link href="css/base.css" rel="stylesheet" type="text/css">
<script language="javascript">
//获得选中文件的文件名
function getCheckboxItem()
{
	var allSel="";
	if(document.form1.aids.value) return document.form1.aids.value;
	for(i=0;i<document.form1.aids.length;i++)
	{
		if(document.form1.aids[i].checked)
		{
			if(allSel=="")
				allSel=document.form1.aids[i].value;
			else
				allSel=allSel+","+document.form1.aids[i].value;
		}
	}
	return allSel;	
}
function ReSel()
{
	for(i=0;i<document.form1.aids.length;i++)
	{
		if(document.form1.aids[i].checked) document.form1.aids[i].checked = false;
		else document.form1.aids[i].checked = true;
	}
}
function DelSel()
{
	var nid = getCheckboxItem();
	location.href = "co_do.php?dopost=clear&ids="+nid;
}
function DelSel2()
{
	var nid = getCheckboxItem();
	location.href = "co_do.php?dopost=clear&clshash=true&ids="+nid;
}
function ClearCt()
{
	var nid = getCheckboxItem();
	location.href = "co_do.php?dopost=clearct&ids="+nid;
}
function upexport()
{
	var nid = getCheckboxItem();
	location.href = "co_do.php?dopost=upexport&ids="+nid;
}
function upexport2()
{
	var nid = getCheckboxItem();
	location.href = "co_do.php?dopost=upexport2&ids="+nid+"&nid=<?php echo $nid; ?>";
}
function Clearresult()
{
	var nid = getCheckboxItem();
	location.href = "co_do.php?dopost=clearresult&ids="+nid;
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
//设置为半自动
function caiji()
{
	window.open("../download/caiji.php?nid=<?php echo $nid; ?>");
}
//设置为半自动
function setauto()
{
	location.href = "co_do.php?dopost=auto&nid=<?php echo $nid; ?>";
}
//设置为全自动
function setnoeor()
{
	location.href = "co_do.php?dopost=noeor&nid=<?php echo $nid; ?>";
}
//所有勾选的行都改变颜色
function tr_bgcolor(c){
var tr = c.parentNode.parentNode;
tr.rowIndex % 1 == 1 ? tr.style.backgroundColor = c.checked ? '#ffff00' : '#FFFFF' : tr.style.backgroundColor = c.checked ? '#ffff00' : '';
} 
</script>
</head>
<body background='images/allbg.gif' leftmargin='8' topmargin='8'>
<table width="98%" border="0" cellpadding="2" cellspacing="1" bgcolor="#D6D6D6" align="center">
  <tr> 
    <td height="28" colspan="8" background='images/tbg.gif'>
    	<table width="96%" border="0" cellpadding="0" cellspacing="0">
        <tr> 
          <td width="20%" height="18" style="padding-left:10px;"><strong>临时内容管理：</strong></td>
          <td width="80%" align="right">
          	<input type="button" name="ba2" value="节点管理" class="coolbg np" style="width:80px" onClick="location.href='co_main.php';" />
          	<input type="button" name="ba1" value="清空所有节点的采集记录" class="coolbg np" style="width:150px" onClick="location.href='co_do.php?dopost=clearall';" />
          	</td>
        </tr>
      </table>
     </td>
  </tr>
  <tr align="center" height="26" bgcolor="#FBFCE2"> 
    <td width="6%">选择</td>
    <td width="20%">内容标题</td>
    <td width="25%">所属节点</td>
    <td width="10%">获取日期</td>
    <td width="8%">下载</td>
    <td width="8%">导出</td>
    <td width="10%">预设栏目</td>
    <td width="8%">来源网页</td>
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
  <tr align="center" bgcolor="#FFFFFF" height="26" align="center" onMouseMove="javascript:this.bgColor='#ddffff';" onMouseOut="javascript:this.bgColor='#FFFFFF';"> 
    <td><input type='checkbox' name='aids' value='<?php echo $fields['aid']; ?>' class='np' onclick="tr_bgcolor(this);"><?php echo $fields['aid']; ?></td>
    <td><a href='co_view.php?aid=<?php echo $fields['aid']; ?>'><?php echo getarcinfo($fields['aid'],'title'); ?></a></td>
    <td><a href='co_edit.php?nid=<?php echo $fields['nid']; ?>'><?php echo getnoteinfo($fields['nid'],'notename'); ?></a></td>
    <td><?php echo date('Y-m-d H:i:s',getarcinfo($fields['aid'],'dtime')); ?></td>
    <td><?php echo IsDownLoad($fields['isdown']); ?></td>
    <td><?php echo IsExData($fields['isexport']); ?></td>
    <td><?php echo gettypeinfo($fields['typeid'],'typename'); ?></td>
    <td><a href='<?php echo getarcinfo($fields['aid'],'url'); ?>' target='_blank'>[源网址]</a></td>
  </tr>
 <?php
}
}?>
</form>
  <tr> 
    <td height="30" colspan="8" bgcolor="#FCFDF7">&nbsp;
	  <input type="button" name="b11" value="反选" class="coolbg np" style="width:40px" onClick="ReSel();" /> 　
	  <!---<input type="button" name="b12" value="仅删除记录" class="coolbg np" style="width:80px" onClick="DelSel();" />--->
	  <input type="button" name="b13" value="设置为已下载已导出" class="coolbg np" style="width:150px" onClick="upexport();" />
	  <input type="button" name="b13" value="仅清空采集到的内容" class="coolbg np" style="width:150px" onClick="Clearresult();" />
	  <input type="button" name="b13" value="清空采集内容和文章" class="coolbg np" style="width:150px" onClick="ClearCt();" />
	  <input type="button" name="b14" value="删除网址及内容" class="coolbg np" style="width:120px" onClick="DelSel2();" />
	  <input type="button" name="b14" value="取消前面章节的采集" class="coolbg np" style="width:120px" onClick="upexport2();" />
	  <?php
		if($cotype=="off")
		{
			echo '<input type="button" name="b14" value="设置本规则为半自动" class="coolbg np" style="width:120px" onClick="setauto();" />
			<input type="button" name="b14" value="设置本规则为全自动" class="coolbg np" style="width:120px" onClick="setnoeor();" />
	  ';
		}
	  ?>
	  <input type="button" name="b14" value="采集本规则" class="coolbg np" style="width:120px" onClick="caiji();" />
    </td>
  </tr>
  <tr> 
    <td height="36" colspan="8" bgcolor="#F9FCEF" align="center">
    <?php
$atts = array();
$atts['tagname'] = 'pagelist';
$atts['listsize'] = '5';
 echo $this->refObj->GetPageList($atts,$this->refObj,$fields); 
?>

    </td>
  </tr>
</table>
</body>
</html>