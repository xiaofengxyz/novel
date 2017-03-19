function $id(id){return document.getElementById(id);}
if(document.body.scrollWidth<1140){$('.floatBox')?$('.floatBox').css('display','none'):'';}
function GetXmlHttpObject(){
	var xmlHttp=null;
	if(window.ActiveXObject){try{xmlHttp=new ActiveXObject('Microsoft.XMLHttp');}catch(e){xmlHttp=new ActiveXObject('Msxml2.XMLHTTP');}}else if(window.XMLHttpRequest){xmlHttp=new XMLHttpRequest();}
	return xmlHttp;
}
function x_get(URL,OBJ,XID,ELSE,OTHER){
	var xmlHttp=null;
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null){alert ('Browser does not support HTTP Request!');return false;}
	xmlHttp.onreadystatechange=function()
	{
	  if(xmlHttp.readyState==4 && xmlHttp.status==200)  
	  {
		  var cDATA=xmlHttp.responseText;
		  if(cDATA){
			  $id(OBJ)?$id(OBJ).innerHTML=cDATA:'';//公共
			  //以下为区别对待
			  if(XID==1){
				  TIP('成功收藏本书！');$id('cms_favorites')?($id('cms_favorites').innerHTML=parseInt($id('cms_favorites').innerHTML)+1):'';
			  }else if(XID==2){
				  TIP('成功添加书签！');
			  }else if(XID=='praise'){
				  TIP('上帝啊！又来了一个粉丝，我受不了了……！');$id('cms_praises').innerHTML=parseInt($id('cms_praises').innerHTML)+1;
			  }else if(XID=='score' || XID=='opt'){
				  TIP('成功评价！');$id(ELSE).innerHTML=parseInt($id(ELSE).innerHTML)+1;
			  }else if(XID=='comment' && ELSE=='yes'){
				  $('html,body').animate({scrollTop:645});
			  }else if(XID=='JP'){
				  $id('CJP_IMG')?$id('CJP_IMG').className='':'';$id('reFRESH')?$id('reFRESH').className='':'';
			  }else if(XID=='logout'){
				  TIP('安全退出！');relog();
			  }else if(XID=='relog'){
				  $('.left_con').html(cDATA);
			  }else if(XID=='hot'){
				  if(ELSE=='hot'){$('.HOT_BOX').html(cDATA);callHOT(OTHER,'info')}else if(ELSE=='info'){$id('ul_infobox').innerHTML=cDATA;};
			  }else if(XID=='state'){
				  $('.MAK').html(cDATA);
			  }else if(XID=='shot'){
				  $('.'+ELSE).html(cDATA);
			  }
		  }else{
			  if(XID=='comment'){
				  $id('ul_b_list').innerHTML='<br /><br />&nbsp;::&nbsp;暂无评论，立即占据评论首位！<br /><br /><br />';$id('cIMG').className='';
			  }
		  }
	  }
	}
	xmlHttp.open('GET',URL,true);
	xmlHttp.send(null);
}
//以上是公共
function ajax_favorite(aid,num){var url=cmsUrl+'favorite.php?inajax=1&aid='+aid;x_get(url,'',num);}
function ajax_praise(id){var url=cmsUrl+'praise.php?inajax=1&id='+id;x_get(url,'','praise');}
function ajaxUscore(aid,score,id){var url=cmsUrl+'score.php?inajax=1&aid='+aid+'&score='+score;x_get(url,'','score',id);}
function ajaxComment(aid,cid,opt,id){var url=cmsUrl+'comment.php?action=vote&inajax=1&aid='+aid+'&cid='+cid+'&option='+opt;x_get(url,'','opt',id);}
function rand(){$id('btn_img')?$id('btn_img').className='btn_img':'';var url=cmsUrl+'callRAND.php';x_get(url,'randBOX');}
function callComment(obj,aid,page,to){$id('cIMG').className='cIMG';var url=cmsUrl+'callComment.php?aid='+aid+'&page='+page;x_get(url,obj,'comment',to);}
function callJP(obj,caid,aid){$id('CJP_IMG')?$id('CJP_IMG').className='on':'';$id('reFRESH')?$id('reFRESH').className='on':'';var url=cmsUrl+'callJP.php?caid='+caid+'&aid='+aid;x_get(url,obj,'JP');}
function doStatic(mode,caid,aid,addno){if(mode=='cnindex'){if(caid!='' && caid!=0){caid='&caid='+caid}else{caid=''};var url=cmsUrl+'static.php?mode='+mode+caid;}else if(mode=='cnlist'){if(caid!='' && caid!=0){caid='&caid='+caid}else{caid=''};var url=cmsUrl+'static.php?mode='+mode+caid;}else if(mode=='arc'){if(addno!='' && addno!=0){addno='&addno='+addno}else{addno=''};var url=cmsUrl+'static.php?mode='+mode+'&aid='+aid+addno;};x_get(url);}
function logout(){var url=cmsUrl+'login.php?action=logout';x_get(url,'','logout');}
function relog(){var url=cmsUrl+'login.php?mode=js&relog=relog';x_get(url,'','relog');}
function callHOT(caid,mode){var url=cmsUrl+'callHOT.php?caid='+caid+'&mode='+mode;x_get(url,'','hot',mode,caid);}
function callSTATE(aid){var url=cmsUrl+'callSTATE.php?aid='+aid;x_get(url,'','state');}
function callSHOT(here,num,site){site=='so'?first=SEARCH_URL:first=cmsUrl;var url=first+'callSHOT.php?num='+num;x_get(url,'','shot',here);}
function callSHARE(entry,num,mode,caid){
	var url=cmsUrl+'sharePHP.php?entry='+entry+'&num='+num+'&mode='+mode+'&caid='+caid;
	x_get(url);
}
//提示信息
function TIP(msg){$('.TIP').html('<div class="h">温馨提示：</div><span class="MSG">'+msg+'</span>');$('.TIP,.MAK').show();setTimeout(function(){$('.TIP,.MAK').fadeOut();$('.TIP,.MAK').removeClass('on');},2000);}