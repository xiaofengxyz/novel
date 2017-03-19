<?php    if(!defined('DEDEINC')) exit("Request Error!");
/**
 * ��վ��ͼ(sitemap��)
 *
 * @version        $Id: sitemap.class.php 1 15:21 2010��7��5��Z tianya $
 * @package        DedeCMS.Libraries
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
require_once(DEDEINC."/channelunit.func.php");

/**
 * ��վ��ͼ(sitemap��)
 *
 * @package          TypeLink
 * @subpackage       DedeCMS.Libraries
 * @link             http://www.dedecms.com
 */
class SiteMap
{
    var $dsql;
    var $artDir;
    var $baseDir;

    //php5���캯��
    function __construct()
    {
        $this->idCounter = 0;
        $this->artDir = $GLOBALS['cfg_arcdir'];
        $this->baseDir = $GLOBALS['cfg_cmspath'].$GLOBALS['cfg_basedir'];
        $this->idArrary = "";
        $this->dsql = $GLOBALS['dsql'];
    }

    function SiteMap()
    {
        $this->__construct();
    }

    //������
    function Close()
    {
    }

    /**
     *  ��ȡ��վ��ͼ
     *
     * @access    public
     * @param     string  $maptype  ��ͼ����  site:վ��  rss:rss
     * @return    string
     */
    function GetSiteMap($maptype="site")
    {
        $mapString = "";
        if($maptype=="rss")
        {
            $this->dsql->SetQuery("SELECT id,typedir,isdefault,defaultname,typename,ispart,namerule2,moresite,siteurl,sitepath FROM #@__arctype WHERE reid=0 AND ispart<>2 ORDER BY sortrank");
        }
        else
        {
            $this->dsql->SetQuery("SELECT id,typedir,isdefault,defaultname,typename,ispart,namerule2,siteurl,sitepath,moresite,siteurl,sitepath FROM #@__arctype WHERE reid=0 ORDER BY sortrank");
        }
        $this->dsql->Execute(0);
        while($row=$this->dsql->GetObject(0))
        {
            if($maptype=="site")
            {
                $typelink = GetTypeUrl($row->id,MfTypedir($row->typedir),$row->isdefault,$row->defaultname,$row->ispart,$row->namerule2,$row->moresite,$row->siteurl,$row->sitepath);
            }
            else
            {
                $typelink = $GLOBALS['cfg_cmsurl']."/rss/".$row->id.".xml";
            }
            $mapString .= "<div class=\"linkbox\">\r\n<h3><a href='$typelink'>".$row->typename."</a></h3>";
            $mapString .= "\t<ul class=\"f6\">\t\t\r".$this->LogicListAllSunType($row->id,$maptype)."\t\n</ul></div>\r\n";
            /*
            $mapString .= "<tr><td width='17%' align='center' bgcolor='#FAFEF1'>";
            $mapString .= "<a href='$typelink'><b>".$row->typename."</b></a>";
            $mapString .= "</td><td width='83%' bgcolor='#FFFFFF'>";
            $mapString .= $this->LogicListAllSunType($row->id,$maptype);
            $mapString .= "</td></tr>";
            */
        }
		$mapString .= "<div class=\"linkbox\">\r\n<h3>�����½�</h3>\t<ul class=\"f6\">\t\t\r";
		// $dsql->SetQuery("SELECT a.id,a.title,b.typedir,b.typename FROM #@__archives a left join #@__arctype b on(a.typeid=b.id) WHERE channel=1 ORDER BY a.id desc limit 0,500");
        // $dsql->Execute();
		// while($row = $dsql->GetObject())
        // {            
            // $mapString .= "<li>[<a href='".$row->typedir."/'>".$row->typename."</a>]<a href='".$row->typedir."/".$row->id;.".html'>".$row->title;."</a></li>\n\t\t";
        // }
		$mapString .= "\t\n</ul></div>\r\n";
        return $mapString;
    }

    /**
     *  �������Ŀ�ĵݹ����
     *
     * @access    public
     * @param     int  $id  ��ĿID
     * @param     string  $maptype  ��ͼ����
     * @return    string
     */
    function LogicListAllSunType($id, $maptype)
    {
        $fid = $id;
        $mapString = "";
        if($maptype=="rss")
        {
            $this->dsql->SetQuery("SELECT id,typedir,isdefault,defaultname,typename,ispart,namerule2,moresite,siteurl,sitepath FROM #@__arctype WHERE reid='".$id."' AND ispart<>2 ORDER BY sortrank");
        }
        else
        {
            $this->dsql->SetQuery("SELECT id,typedir,isdefault,defaultname,typename,ispart,namerule2,moresite,siteurl,sitepath FROM #@__arctype WHERE reid='".$id."' ORDER BY sortrank");
        }
        $this->dsql->Execute($fid);
        while($row=$this->dsql->GetObject($fid))
        {
            if($maptype=="site")
            {
                $typelink = GetTypeUrl($row->id,MfTypedir($row->typedir),$row->isdefault,$row->defaultname,$row->ispart,$row->namerule2,$row->moresite,$row->siteurl,$row->sitepath);
            }
            else
            {
                $typelink = $GLOBALS['cfg_cmsurl']."/rss/".$row->id.".xml";
            }
            $mapString .= "<li><a href='$typelink'>".$row->typename."</a></li>\n\t\t";
            $mapString .= $this->LogicListAllSunType($row->id,$maptype);
        }
        return $mapString;
    }
}