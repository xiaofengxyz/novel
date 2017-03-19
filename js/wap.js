var checkbg = "#A7A7A7";
//内容页用户设置
function nr_setbg(intype){
	var huyandiv = document.getElementById("huyandiv");
	var light = document.getElementById("lightdiv");
	if(intype == "huyan"){
		if(huyandiv.style.backgroundColor == ""){
			set("light","huyan");
			document.cookie="light=huyan"; 
		}
		else{
			set("light","no");
			document.cookie="light=no"; 
		}
	}
	if(intype == "light"){
		if(light.innerHTML == "关灯"){
			set("light","yes");
			document.cookie="light=yes"; 
		}
		else{
			set("light","no");
			document.cookie="light=no"; 
		}
	}
	if(intype == "big"){
		set("font","big");
		document.cookie="font=big"; 
	}
	if(intype == "middle"){
		set("font","middle");
		document.cookie="font=middle"; 
	}
	if(intype == "small"){
		set("font","small");
		document.cookie="font=small"; 
	}		
}

function getset(){ 
	var strCookie=document.cookie; 
	var arrCookie=strCookie.split("; ");  
	var light;
	var font;

	for(var i=0;i<arrCookie.length;i++){ 
		var arr=arrCookie[i].split("="); 
		if("light"==arr[0]){ 
			light=arr[1]; 
			break; 
		} 
	} 
	for(var i=0;i<arrCookie.length;i++){ 
		var arr=arrCookie[i].split("="); 
		if("font"==arr[0]){ 
			font=arr[1]; 
			break; 
		} 
	}
	
	if(light == "yes"){
		set("light","yes");
	}
	else if(light == "no"){
		set("light","no");
	}
	else if(light == "huyan"){
		set("light","huyan");
	}
	//font
	if(font == "big"){
		set("font","big");
	}
	else if(font == "middle"){
		set("font","middle");
	}
	else if(font == "small"){
		set("font","small");
	}
	else{
		set("","");	
	}
}

function set(intype,p){
	var nr_body = document.getElementById("nr_body");//页面body
	var huyandiv = document.getElementById("huyandiv");//护眼div
	var lightdiv = document.getElementById("lightdiv");//灯光div
	var fontfont = document.getElementById("fontfont");//字体div
	var fontbig = document.getElementById("fontbig");//大字体div
	var fontmiddle = document.getElementById("fontmiddle");//中字体div
	var fontsmall = document.getElementById("fontsmall");//小字体div
	var nr1 =  document.getElementById("nr1");//内容div
	var nr_title =  document.getElementById("nr_title");//文章标题
	
	//初始化
	//document.getElementsByName("page_nr")[1].style.color = "#000";
	
	//灯光
	if(intype == "light"){
		if(p == "yes"){	
			//关灯
			lightdiv.innerHTML = "开灯";
			nr_body.style.backgroundColor = "#111";
			huyandiv.style.backgroundColor = "";
			nr_title.style.color = "#777";
			nr1.style.color = "#666";
		}
		else if(p == "no"){
			//开灯
			lightdiv.innerHTML = "关灯";
			nr_body.style.backgroundColor = "#fff";
			nr1.style.color = "#000";
			nr_title.style.color = "#000";
			huyandiv.style.backgroundColor = "";
		}
		else if(p == "huyan"){
			//护眼
			lightdiv.innerHTML = "关灯";
			huyandiv.style.backgroundColor = checkbg;
			nr_body.style.backgroundColor = "#005716";
			nr1.style.color = "#aaa";
		}
	}
	//字体
	if(intype == "font"){
		//alert(p);
		fontbig.style.backgroundColor = "";
		fontmiddle.style.backgroundColor = "";
		fontsmall.style.backgroundColor = "";
		if(p == "big"){
			fontbig.style.backgroundColor = checkbg;
			nr1.style.fontSize = "20px";
		}
		if(p == "middle"){
			fontmiddle.style.backgroundColor = checkbg;
			nr1.style.fontSize = "16px";
		}
		if(p == "small"){
			fontsmall.style.backgroundColor = checkbg;
			nr1.style.fontSize = "12px";
		}
	}
}