<?php
require_once(dirname(__FILE__).'/config.php');
require_once(DEDEINC."/datalistcp.class.php");
CheckPurview('member_Type');
$mdir = DEDEROOT.'/data/module';
if(empty($dopost))
{
	$dopost = "";
}

//�������
if($dopost=="save")
{
	$startID = 1;
	$endID = $idend;
	for(;$startID<=$endID;$startID++)
	{
		$query = '';
		$id = ${'ID_'.$startID};
		$find =   ${'find_'.$startID};
		$replace =    ${'replace_'.$startID};
		$interconvert =   ${'interconvert_'.$startID};
		if(!isset($interconvert)) 
		  $interconvert = 0;
		else
		  $interconvert = 1;
		if(isset(${'check_'.$startID}))
		{
			if($find!='' and $replace!='')
			{
				$query = "update `#@__str_replace` set `find`='$find',`replace`='$replace',`interconvert`=$interconvert where `id`='$id'";
				$dsql->ExecuteNoneQuery($query);
			}
		}
		else
		{
			$query = "Delete From #@__str_replace where id='$id' ";
			$dsql->ExecuteNoneQuery($query);
		}
	}

	//�����¼�¼
	if(isset($check_new) && $find_new!='' && $replace_new!='')
	{
		
		if(empty($interconvert_new)) 
		  $interconvert_new = 0;
		else
		  $interconvert_new = 1;
		$query = "Insert Into `#@__str_replace`(`find`,`replace`,`interconvert`) Values('{$find_new}','{$replace_new}','{$interconvert_new}');";
		$dsql->ExecuteNoneQuery($query);
	}
	header("Content-Type: text/html; charset={$cfg_soft_lang}");
	echo "<script> alert('�ɹ������滻�ʱ�'); </script>";
}
else if($dopost=="batch_save")
{
	
if( !is_uploaded_file($batch) )
	{
		ShowMsg("��ʲô��û���ϴ�Ŷ��","javascript:;");
		exit();
	}
   $fp = fopen($batch, "r");
   if (!$fp)
   {
      ShowMsg("�ļ������ڣ�","-1");
      exit;
   }
   			//$query = "Delete From #@__str_replace ";
			//$dsql->ExecuteNoneQuery($query);
   $count = 0;
   while (!feof($fp))
   {
      $bruce=fgets($fp);
      $temp = explode($separator, $bruce);
	  $find_new = $temp[0];
	  $replace_new = $temp[1];
	  $interconvert_new = 1;
	  if($find_new!='' and $replace_new!='')
	  {
	    $query = "Insert Into `#@__str_replace`(`find`,`replace`,`interconvert`) Values('{$find_new}','{$replace_new}','{$interconvert_new}');";
	    $dsql->ExecuteNoneQuery($query);
	    $count++;
	  }
   }
   fclose($fp);
   echo "<script> alert('�ɹ�����".$count."��ͬ��ʣ�'); </script>";
}
$sql = "Select * From #@__str_replace order by id desc";
$dlist = new DataListCP();
$dlist->SetTemplate(DEDEADMIN."/templets/pr_str_replace.htm");
$dlist->SetSource($sql);
$dlist->display();

function huihuan($inter)
{
	if($inter==1)
	{
		return "checked='1'";
	}
	else
	{
		return "";
	}
}

?>