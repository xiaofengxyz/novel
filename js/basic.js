function $id(id){return document.getElementById(id);}
function getcookie(name){var cookie_start=document.cookie.indexOf(name);var cookie_end=document.cookie.indexOf(";",cookie_start);return cookie_start == -1?'':unescape(document.cookie.substring(cookie_start+name.length+1,(cookie_end > cookie_start?cookie_end:document.cookie.length)));}
function setcookie(cookieName,cookieValue,seconds,path,domain,secure){var expires=new Date();expires.setTime(expires.getTime()+seconds);document.cookie=escape(cookieName)+'='+escape(cookieValue)+(expires?';expires='+expires.toGMTString():'')+(path?';path='+path:'/')+(domain?';domain='+domain:'')+(secure?';secure':'');}
function X_Object(){
	var xmlHttp=null;
	if(window.ActiveXObject){try{xmlHttp=new ActiveXObject('Microsoft.XMLHttp');}catch(e){xmlHttp=new ActiveXObject('Msxml2.XMLHTTP');}}else if(window.XMLHttpRequest){xmlHttp=new XMLHttpRequest();}
	return xmlHttp;
}
function X_GET(URL,OBJ,XID,ELSE,OTHER){
	var xmlHttp=null;
	xmlHttp=X_Object();
	if (xmlHttp==null){alert ('Browser does not support HTTP Request!');return false;}
	xmlHttp.onreadystatechange=function(){
	  if(xmlHttp.readyState==4 && xmlHttp.status==200)  
	  {
		  var cDATA=xmlHttp.responseText;cDATA?($('.box_box')?$('.box_box').html(cDATA):''):'';
	  }
	}
	xmlHttp.open('GET',URL,true);
	xmlHttp.send(null);
}
function callTXT(url,chid,aid,from){var url=TXTURL+'callTXT.php?url='+url+'&chid='+chid+'&aid='+aid+'&from='+from;X_GET(url,'','txt');}