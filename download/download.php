<?
function create_zip($files = array(),$destination = '',$overwrite = false)
{
	if(file_exists($destination) && !$overwrite) { return false; }
	$valid_files = array();
	if(is_array($files))
	{
		foreach($files as $file)
		{
			if(file_exists($file))
			{
				$valid_files[] = $file;
			}
		}
	}
	if(count($valid_files))
	{
		$zip = new ZipArchive();
		if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true)
		{
			return false;
		}
		foreach($valid_files as $file)
		{
			$zip->addFile($file,$file);
		}
		$zip->close();
		return file_exists($destination);
	}
	else
	{
		return false;
	}
}
function isMobile()
{
	//note 获取手机浏览器
	$str="";
	preg_match_all('/iPhone|iPod|Android|ios|iPad|Mobile|WAP|NetFront|JAVA|OperasMini|UCWEB|WindowssCE|Symbian|Series|webOS|UNTRUSTED/i',$_SERVER["HTTP_USER_AGENT"],$matches);
	$str = join('', $matches[0]);
	if($str) 
	{
		return true;
	}
	else return false;
}
require_once("../include/common.inc.php");
$tid=$_GET['filename'];
if(!is_numeric($tid))
{
	ShowMsg("很抱歉，这本电子书暂时还无法下载3…","/",'',5000);
	exit();
}
$filetype=$_GET['filetype'];
$row=$dsql->GetOne("Select * From `#@__arctype` where id='$tid' and reid not in(0,45)");
if(is_array($row))
{
	if($filetype=="txt")
		$file_name=$row['zuozhe']."-".$row['typename'].".txt";
	elseif($filetype=="zip")
	{
		$file_name_txt=$row['zuozhe']."-".$row['typename'].".txt";
		$files=array($file_name_txt);
		$file_name=$row['zuozhe']."-".$row['typename'].".zip";
		if(file_exists($file_name))
		{
			unlink($file_name);
		}
		create_zip($files,$file_name, true);
	}
	else
	{
		ShowMsg("很抱歉，这本电子书暂时还无法下载1…","/",'',5000);
		exit();
	}
	$fileurl=$file_name;
	if(file_exists($fileurl))
	{
		//打开文件
		$file=fopen($fileurl,"r");
		//输入文件标签
		Header("Content-type:application/octet-stream");
		Header("Accept-Ranges:bytes");
		Header("Content-Length:".filesize($fileurl));
		$wap=isMobile();
		if($wap) $file_name = gb2utf8($file_name);
		Header("Content-Disposition:attachment;filename=".$file_name);
		//输出文件内容
		//读取文件内容并直接输出到浏览器
		$s = fread($file,filesize($fileurl));
		fclose($file);
		if(print($s))
		{
			if($filetype=="zip")
			{
				unlink($fileurl);
			}
		}
		exit();
	}
	else
	{
		ShowMsg("很抱歉，这本电子书暂时还无法下载2…","/",'',5000);
		exit();
	}
}
else
{
	ShowMsg("很抱歉，这本电子书暂时还无法下载3…","/",'',5000);
	exit();
}
?>