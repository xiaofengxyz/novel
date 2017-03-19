(function(exports){
	var core = {};
	
	core.Class = function(member, statics){
		var klass = function(){
			this['_constructor'].apply(this, arguments);
		};
		klass.prototype._constructor = function(){};
		for(var i in member){
			klass.prototype[ (i == 'constructor' ? '_' : '' ) + i] = member[i];
		}
		for(var i in statics){
			klass[i] = statics[i];
		}
		klass['init'] && klass['init'].call(klass);
		return klass;
	}
	
	core.Service = function(member){
		if( typeof member !== 'function' ) return null;
		
		return core.Class(member(), {
			instance: null,
			create: function(){
				return this.instance !== null ? this.instance : ( this.instance = new this() )
			}
		});
	}
	
	core.error = [];
	core.register = function(e, c) {
		var g = e.split(".");
		var f = core;
		var b = core.error;
		var d = null;
		while (d = g.shift()) {
			if (g.length) {
				if (f[d] === undefined) {
					f[d] = {}
				}
				f = f[d]
			} else {
				if (f[d] === undefined) {
					if( typeof c === 'string' && c === 'check' ){
						core.console.log('Property undefined:'+e);
						return $.noop;
					}
					try {
						f[d] = c(core);
					} catch(h) {
						b.push(h)
					}
				}else{
					if( typeof c == 'string' && c == 'check' ){
						return f[d]
					}
					core.console.log('redefined:'+e)
				}
			}
		}
	}
	
	//延迟函数
	core.delay = function(fn, delay){
		return setTimeout(fn, delay || 0)
	}
	
	core.watch = function(fn, delay){
		var timer = core.delay(fn, delay)
		return function(){
			clearTimeout(timer);
			timer = null;
		}
	}
	
	core.Tabs = function(handles, items){
		handles.bind("touchend click", function(){
			var index = $(this).index();
			items.hide().eq(index).show();
			handles.removeClass("active").eq(index).addClass("active")
		});
	}
	
	
	//使用平台
	core.client = {
		init: function () {
			this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
			this.version = this.searchVersion(navigator.userAgent)
				|| this.searchVersion(navigator.appVersion)
				|| "an unknown version";
			this.OS = this.searchString(this.dataOS) || "an unknown OS";
		},
		searchString: function (data) {
			for (var i=0;i<data.length;i++)	{
				var dataString = data[i].string;
				var dataProp = data[i].prop;
				this.versionSearchString = data[i].versionSearch || data[i].identity;
				if (dataString) {
					if (dataString.indexOf(data[i].subString) != -1)
						return data[i].identity;
				}
				else if (dataProp)
					return data[i].identity;
			}
		},
		searchVersion: function (dataString) {
			var index = dataString.indexOf(this.versionSearchString);
			if (index == -1) return;
			return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
		},
		dataBrowser: [
			{
				string: navigator.userAgent,
				subString: "Chrome",
				identity: "Chrome"
			},
			{ 	string: navigator.userAgent,
				subString: "OmniWeb",
				versionSearch: "OmniWeb/",
				identity: "OmniWeb"
			},
			{
				string: navigator.vendor,
				subString: "Apple",
				identity: "Safari",
				versionSearch: "Version"
			},
			{
				prop: window.opera,
				identity: "Opera"
			},
			{
				string: navigator.vendor,
				subString: "iCab",
				identity: "iCab"
			},
			{
				string: navigator.vendor,
				subString: "KDE",
				identity: "Konqueror"
			},
			{
				string: navigator.userAgent,
				subString: "Firefox",
				identity: "Firefox"
			},
			{
				string: navigator.vendor,
				subString: "Camino",
				identity: "Camino"
			},
			{		// for newer Netscapes (6+)
				string: navigator.userAgent,
				subString: "Netscape",
				identity: "Netscape"
			},
			{
				string: navigator.userAgent,
				subString: "MSIE",
				identity: "Explorer",
				versionSearch: "MSIE"
			},
			{
				string: navigator.userAgent,
				subString: "Gecko",
				identity: "Mozilla",
				versionSearch: "rv"
			},
			{ 		// for older Netscapes (4-)
				string: navigator.userAgent,
				subString: "Mozilla",
				identity: "Netscape",
				versionSearch: "Mozilla"
			}
		],
		dataOS : [
			{
				string: navigator.userAgent,
				subString: "iPad",
				identity: "iPad"
			},
			{
				string: navigator.platform,
				subString: "Win",
				identity: "Windows"
			},
			{
				string: navigator.platform,
				subString: "Mac",
				identity: "Mac"
			},
			
			{
				string: navigator.userAgent,
				subString: "Android",
				identity: "Android"
			},
			{
				string: navigator.userAgent,
				subString: "iPhone",
				identity: "iPhone/iPod"
		    },
			{
				string: navigator.platform,
				subString: "Linux",
				identity: "Linux"
			}
		]
	}
	
	core.client.init();
	
	core.cookie = function(){
		var pluses = /\+/g;

		function raw(s) {
			return s;
		}

		function decoded(s) {
			return decodeURIComponent(s.replace(pluses, ' '));
		}

		function converted(s) {
			if (s.indexOf('"') === 0) {
				// This is a quoted cookie as according to RFC2068, unescape
				s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
			}
			try {
				return config.json ? JSON.parse(s) : s;
			} catch(er) {}
		}

		var config = cookie = function (key, value, options) {

			// write
			if (value !== undefined) {
				options = $.extend({}, config.defaults, options);

				if (typeof options.expires === 'number') {
					var days = options.expires, t = options.expires = new Date();
					t.setDate(t.getDate() + days);
				}

				value = config.json ? JSON.stringify(value) : String(value);

				return (document.cookie = [
					encodeURIComponent(key), '=', config.raw ? value : encodeURIComponent(value),
					options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
					options.path    ? '; path=' + options.path : '',
					options.domain  ? '; domain=' + options.domain : '',
					options.secure  ? '; secure' : ''
				].join(''));
			}

			// read
			var decode = config.raw ? raw : decoded;
			var cookies = document.cookie.split('; ');
			var result = key ? undefined : {};
			for (var i = 0, l = cookies.length; i < l; i++) {
				var parts = cookies[i].split('=');
				var name = decode(parts.shift());
				var cookie = decode(parts.join('='));

				if (key && key === name) {
					result = converted(cookie);
					break;
				}

				if (!key) {
					result[name] = converted(cookie);
				}
			}

			return result;
		};
		
		config.defaults = {};
		
		var removeCookie = function (key, options) {
			if (cookie(key) !== undefined) {
				cookie(key, '', $.extend(options, { expires: -1 }));
				return true;
			}
			return false;
		}
		
		cookie.removeCookie = removeCookie;
		
		return cookie
	}();
	
	exports.core = core;
	
})(this);

if(_inlineCodes && _inlineCodes.length){
    $.map(_inlineCodes, function(fn){
        typeof fn === 'function' && fn()
    })
}