/*
Name:balafon.js
*/
// igk.js
// Author : C.A.D. BONDJE DOUE
// copyright: igkdev @ 2008-2016
// igkdev - balafon.js framework scripts utility
// 27-06-2013
// 18-02-2014 expose utility
// 23-05-14
// Release : 13-04-2016
// Release : 28/12/2017
// read fix for more info 

"use strict";

(function (window) {
	if (typeof (window.igk) != 'undefined') {
		return;
	}
	// ----------------------------------------------------------------------------------------
	// defining namespace
	// ----------------------------------------------------------------------------------------
	var m_scriptTag = null;
	var yes = !0;
	var no = false;
	var readyFunc = []; // store function to call when document complete. after this will be flush
	var m_readyGlobalFunc = [];
	var m_tag_obj = []; 		// component by tag name // tag will be converted to balafon.js usage.
	// and tag name will be attached to replaced tag managed by you

	var m_ready_calling = false; // flag for ready call
	// var __parentScript=null;
	var m_scriptNode = null; // store the execution script node. on eval context or running
	var __nsigk = null;
	var sm_regEventContext = new Array();
	var m_attribs = {}; // attributes for declaring namespace type
	// class constant
	var IGK_UNDEF = "undefined";
	var IGK_FUNC = "function";
	var IGK_CLASS = "class";
	var IGK_OBJECT = "object";
	var RZ_TIMEOUT = 200;
	var __eventObjectManager = null;
	var __libName = "BalafonJS";
	var __version = "4.0.18.0705";
	var __author = "C.A.D. BONDJE DOUE";
	var __devscript = "igk.js";
	var __lang = [];
	var __initDocSetting = 0;

	var __igk_settings = {
		nosymbol: 0
	}; // load setting
	var m_LibScript = "";
	var __debug_z = 0;
	var m_provider = ['webkit', 'ms', 'moz', 'o'];
	var _rgx = {
		attribs: /([^\s]+)\s*=\s*(((")([^"]*)("))|((')([^']*)(')))/g,
		ff: /Firefox\/([0-9]+\.[0-9]+)/,
		ios: /IOS/
	};
	var __isdev;

	__lang[0xEA001] = "failed to transform xml with xsl . {0}";


	function __init_console() {
		__debug_z = $igk("#debug-z").first();
		if (__debug_z) {

			var d = __igk_settings["console"];
			if (d) {
				for (var i = 0; i < d.length; i++) {
					var dv = __debug_z.add('div');

					switch (d[i].t) {
						case -1:
							dv.addClass('igk-danger');
							break;
						case 1:
							dv.addClass('igk-info');
						default:
							break;
					}
					dv.setHtml(d[i].m);
				}
			}
		}
		delete (__igk_settings["console"]);

	};




	if (typeof (window.console) == IGK_UNDEF) {
		// alert("create console");
		window.console = {
			error: function (m) {

				__debug_z = __debug_z ? __debug_z : $igk("#debug-z").first();
				if (__debug_z) {
					__debug_z.add('div').addClass("igk-danger").setHtml(m);
				} else {
					// alert("Error : "+m);
					var c = __igk_settings["console"] || [];
					c.push({ t: -1, m: m });
					__igk_settings["console"] = c;
				}
			}
		};

		m_readyGlobalFunc.push(__init_console);
	}

	if (typeof (console.debug) == IGK_UNDEF) {
		// alert("create debug");
		console.debug = function (m) {
			__debug_z = __debug_z ? __debug_z : $igk("#debug-z").first();
			if (__debug_z) {
				__debug_z.add('div').addClass("igk-info").setHtml(m);
			} else {
				// alert("/!\\ debug "+m);
				var c = __igk_settings["console"] || [];
				c.push({ t: 1, m: m });
				__igk_settings["console"] = c;
			}

			// var  g=$igk('#log.debug').first();
			// if(g){
			// g.add('div').setHtml(msg);
			// }
		};
	}

	function __dom_innerHTML(i) {
		if ("innerHTML" in i)
			return i.innerHTML;

		if ("nodeType" in i) {
			var k = 0;
			var s = "";
			// while(k<i.childNodes.length){
			// s +="d"+ __dom_innerHTML(i.childNodes[k]);
			// k++;
			// }
			return s;
		}
		return "/!\\ NotSupported";
	}

	function igk_object() {
		// define an igk balafonjs object
	};
	function igk_class() {
		// define an class type
	};

	igk_class.prototype = new igk_object();

	function igk_namespace() {
		// define a namespace object
	};
	igk_namespace.prototype = new igk_object();

	function igk_is_string(t) {
		return typeof (t) == 'string';
	};
	function igk_is_notdef(t) {
		return typeof (t) == IGK_UNDEF;
	};
	function igk_is_object(t) {
		return typeof (t) == 'object';
	};
	// register a component objet create by using tagname
	function igk_reg_tag_obj(n, p) {
		if (m_tag_obj[n]) {
			m_tag_obj[n] = p;
		}
		else {
			m_tag_obj[n] = p;
			m_tag_obj.push({ n: n, data: p });
		}
	};

	function igk_stop_event(ev) {
		ev.preventDefault();
		ev.stopPropagation();
	};


	function igk_url(u) {
		return new (function () {
			this.uri = u;
		})();
	};
	function igk_is_coers(uri) {
		var cu = window.URL || igk_url;
		var c = 0;
		if (typeof (cu) == 'function') {
			c = new cu(uri);
		} else {
			if (typeof (URL.createObjectURL) != 'function')
				return -1;
			return 0;

		}




		if (igk.validator.isUri(uri)) {
			if ((window.location.protocol != c.protocol) ||
				(window.location.host != c.host)
			) {
				return 1;
			}
			return 0;
		}
		return 0;
	};

	function igk_io_getData(uri, callback, mimetype) {


		if (!uri)
			return null;
		var _promise = new igk.system.Promise();
		if (igk_is_coers(uri)) {

			igk.ajx.get(uri, null, function (xhr) {
				if (this.isReady()) {
					var _o = {
						uri: uri,
						source: "xhr",
						"from": 'igk_io_getData',
						data: null
					};
					_o.data = xhr.responseText;
					callback(_o);
				}
			});

			return _promise;
		};



		if (igk.system.string.startWith(uri, "file://")) {
			// alert("loading from data file : "+uri);
			setTimeout(function () {
				var s = igk.invoke("IOGetFileContent", uri);
				// alert("s file :::: "+s);
				if (s && (s.length > 0)) {
					if (callback) {
						try {
							// igk.html.canva.animate(
							// function(){
							// callback({ source: ob, data:s});
							// });
							callback({ data: s, source: 'IOGetFileContent', uri: uri });

						}
						catch (e) {
							console.error("exception raise: " + e);
						}
					}
				}
			}, 1);
			return;
		}
		var ob = igk.createNode('object');
		if (igk.navigator.isIE()) {
			// ie force download
			var _o = {
				uri: uri,
				"from": 'igk_io_getData',
			};
			igk.ajx.get(uri, null, function (xhr) {
				if (this.isReady()) {
					_o.data = xhr.responseText;



					// _o.data = _data;
					callback(_o);

				} else {

					if ((xhr.readyState == 4) && (xhr.status != 200)) {
						_o.data = null;
						_o.errorCode = xhr.status;
						callback(_o);

					}
				}
			});
			return;
		};


		var __loaded = !1;// false;		
		ob.o.style.visibility = 'hidden';
		ob.o.style.position = 'absolute';
		ob.o.width = 4;// 1;
		ob.o.height = 4;// 1;		
		var _data_first = 0; // for object data need to setup data before added it to dom

		_data_first = igk.navigator.isChrome() || igk.navigator.isFirefox() || /Google Inc\./.test(navigator.vendor);


		if (igk.navigator.isIEEdge()) {
			ob.o.type = "text/xml";
		}
		else
			ob.o.type = mimetype || "text/xml";

		// ob.o.type = "text/plain";
		// ob.o.type = "application/json";
		ob.addClass("bdr-1");
		ob.reg_event("load", function (evt) {

			var _o = {
				uri: uri,
				source: ob.o,
				"from": 'igk_io_getData',
				data: null
			};

			if (callback) {
				var o = ob.o;
				if (ob.o.data == "") {



					var e = callback.error;
					if (e)
						e();
					else
						callback(_o);
				} else {
					var _data = '';
					try {
						if (ob.o.contentDocument) {
							var c = (ob.o.contentDocument.firstElementChild || ob.o.contentDocument.firstChild);
							_data = c.innerText || c.textContent;
						} else {
							throw new Error("no content document : " + uri + " for " + ob.o.type);
							//console.debug(ob.o);
						}
						_o.data = _data;
						callback(_o);

					}
					catch (e) {
						igk.log("Failed: loading ===> " + uri);
						_o.error = 1;
						_o.errormsg = igk.R.error_failedtoload;
					}

				}
			}
			ob.remove();
			_promise.resolve();

		}).reg_event("error", function (evt) {



			ob.remove();
			var e = callback.error;
			if (__loaded || (igk.navigator.isFirefox() && igk.navigator.getFirefoxVersion() >= 50)
				// igk.navigator.isIEEdge()
			) {
				if (e)
					e();
				else if (callback) {
					callback({ source: ob, data: null, error: 1 });
				}
				_promise.reject();
				return;
			}



			if (e)
				e();
			else if (callback) {
				callback({ source: ob, data: null, error: 1 });
			}
			_promise.reject();
		});

		var _body = igk.dom.body();
		//always initialize first before append to body for loading
		ob.o.data = uri;
		_body.prepend(ob);

		// }
		// if (_data_first)
		// ob.o.data=uri;
		// if (_body){
		// try{

		// catch(ex){

		// if (!_data_first){
		// ob.o.data = uri;
		// _body.prepend(ob);
		// }
		// }
		// }
		// // for ie data must be set after include append to document to avoir twice loading
		// if (!_data_first)
		// ob.o.data = uri;


		return _promise;
	};

	// return a selection expreession object
	function igk_select_exp(p) {
		var m_items = [];
		function loader(t) {
			m_items.push(t);
		}
		var m = new RegExp("[ ]?[\.# ]{0,1}[^\.# ]+", "ig");
		// tip: use replace function to get all element that match the pattern
		p.replace(m, loader);

		var q = {
			// selector: null,
			pattern: p,
			getCount: function () {
				return m_items.length;
			},
			check: function (item, offset) {
				for (var i = offset; i < m_items.length; i++) {
					var p = m_items[i];
					switch (p[0]) {
						case ".":
							if (!igk_item_match_class(p.substring(1), item)) {
								return !1;
							}
							break;
						case "#":
							if (item.id != p.substring(1)) {
								return !1;
							}
							break;
						case " ":
							if (item.tagName.toLowerCase() != p.substring(1)) {
								return !1;
							}
					}
				}
				return !0;
			},
			select: function (selector, item) {// select in expression
				if (!item)
					return null;

				var pk = null;
				var v_it = null;
				var b = false;
				var depth = false; // to match the current item or good to childs			
				function __select(pk) {
					if (!b) {

						var sq = $igk(item).select(pk);

						if (sq.getCount() > 0) {
							selector.load(sq);
						}
						// igk.debug.assert(debug,"OK QQ "+sq + " "+selector);		
					}
					else {
						// filter selection					
						var isel = null;
						if (depth) {
							isel = selector.select(pk);
						}
						else {
							isel = selector.select('?' + pk);
						}
						selector.clear();
						selector.load(isel);
					}
					b = true;
				}
				for (var j = 0; j < m_items.length; j++) {
					v_it = null;
					pk = m_items[j];

					switch (pk[0]) {
						case ".":// select by class	
						case "#": // select by id							
							__select(pk);
							continue;
						case ' ':// select 
							while ((pk = pk.substring(1)) && (pk[0] == ' ')) {
							}
							if (pk) {
								depth = !0;
								__select(pk);
								depth = false;
							}
							break;
						case "$":
							console.debug("is dollar error. not implement");
							break;
						default:
							break;
					}

				}
				if (!b) {
					var v_it = item.getElementsByTagName(p);
					if (v_it) {
						for (var i = 0; i < v_it.length; i++) {
							var s = v_it[i];
							if (this.check(s, 1)) {
								selector.push(s);
							}
						}
					}
				}
			}
		};
		if (q.getCount() == 0)
			return null;
		return q;

	}


	function igk_getScriptLocation() {
		var idx = 0;
		var e = 0;


		function _readUri(h, starti) {
			var tg = h.substring(starti).split(':');
			var uri = '';
			if (tg.length > 2) {
				uri = tg.slice(0, tg.length - 2).join(':');
			}
			idx = starti + uri.length;
			return uri;

		};
		function _readChromeUri(h) {
			var idx = h.indexOf('(');
			var ch = 0;
			if (idx != -1) {
				idx = 1;
				var depth = 1;
				while ((depth > 0) && idx < h.length) {
					ch = h[idx];
					if (ch == ')')
						depth--;
					else if (ch == '(') {
						depth++;
						uri = '';
					}
					else
						uri += ch;
					idx++;
				}
				var tb = uri.trim().split(':');
				if (tb.length > 2) {
					uri = _readUri(uri, 0);
					l = tb[tb.length - 2];
					c = tb[tb.length - 1];
				}

			} else {

				var tb = h.split(" ");
				h = tb[tb.length - 1];
				uri = _readUri(h, 0);
				l = h.substring(idx + 1, idx = h.indexOf(':', idx + 1));
				c = h.substring(idx + 1).trim();
			}
			var e = {
				location: uri,
				line: l,
				column: c
			};
			return e;
		};

		var _nav = igk.navigator;
		var uri = "";
		var c = 0; var l = 0;
		try {
			throw new Error('[BJS]:getscriptlocation');
		}
		catch (ex) {
			if (ex.stack) {
				// stack is different from browser. 
				// FF: 
				//	 - start at indice 1 - mark this entrie data
				//Chrome and IEEdge set to error message
				//safari act strange:
				//our goal is to convert stact to interpreted exploitable data in oder to get code and line


				var tdb = (ex.stack + '').split('\n');
				var idx = 1;
				// the first stack line must be ignored because is where the exception is throw.
				var stackinfo = [];
				if (/^Error:/.test(tdb[0]))
					idx++;
				if (_nav.isChrome())
					idx++;

				//push only available data that not concern eval data expression as <uri>:<line>:<number>
				while ((stackinfo.length == 0) && (idx < tdb.length)) {
					// ie - eval stack line
					// firefox stack line
					if (/(at eval code|> eval:)/.test(tdb[idx])) {
						idx++;
						continue;
					}
					// /(([^: ]+)@){0,1}([^ ]+):([0-9]+):([0-9]+)/
					tdb[idx].replace(/([@ ]{0,1})([^ \(\)]+):([0-9]+):([0-9]+)/, function (x, b, u, l, c) {
						var p = 0;
						var _eval = 0;
						var _cidx = -1;
						if (u == 'eval') {
							//firefox invoke eval on line 
							//firefox current stack script @uri line l > eval
							_eval = 1;
						}
						if ((_cidx = u.indexOf('@')) != -1) {
							//for firefox

							u = u.substr(_cidx + 1);
						}



						stackinfo.push({
							src: tdb[idx],
							text: x,
							'func': p,
							location: u,
							line: parseInt(l || '0'),
							column: parseInt(c || '0'),
							index: idx,
							'eval': _eval,
							stack: tdb
						});
						return tdb[idx];
					});
					idx++;
				}



				if (stackinfo.length == 1) {
					e = stackinfo[0];
				}
				//return;





				// var tdb = (ex.stack+'').split('\n');
				// var cidx = 1;
				// var h = tdb[idx];
				// var is_chrome = _nav.isChrome(); //safari edge return chrome

				// if (/^Error/.test(h)){
				// //ie and chrome promt Error tag on stack

				// cidx ++;
				// h = tdb[cidx].trim();


				// while( (cidx <= (tdb.length-1)) && /eval code/.test(h)) {//edge evaluation colode
				// cidx++;

				// h = tdb[cidx].trim();
				// }

				// if(h.startsWith("at")){
				// e = _readChromeUri(h);
				// return e;
				// }
				// return null;
				// }
				// cidx++;
				// h= tdb[cidx].trim();

				// // return null;


				// if (!_nav.isIEEdge() && ( _nav.isFirefox() || _nav.isSafari())) {
				// e = {
				// func: h.substring(0, idx = h.indexOf('@', 0)),
				// location: _readUri(h, idx + 1),
				// line: h.substring(idx + 1, idx = h.indexOf(':', idx + 1)),
				// column: h.substring(idx + 1)
				// };
				// }
				// else {
				// // edge and contain '('
				// e = {
				// func: h.substring(idx = h.indexOf(' at ') + 4, idx = h.indexOf('(', idx)).trim(),
				// location: _readUri(h, idx + 1),
				// line: h.substring(idx + 1, idx = h.indexOf(':', idx + 1)),
				// column: h.substring(idx + 1, h.indexOf(')', idx + 1))
				// }

				// }

				// } else {
				// //old safari does not support stack property



				// return null;
				// }
			}
		}
		return e;
	}


	// p:propertie
	// n:dom node
	function igk_item_match_class(p, n) {
		if (!n)
			return !1;
		var q = new RegExp("(\\s|^)(" + p + ")(\\s{1}|$)", "i");
		if (q.exec("" + n.className)) {
			return !0;
		}
		return !1;
	};
	function igk_item_inherit_class(p, n) {
		var q = n;
		while (q) {


			if (igk_item_match_class(p, q)) {
				return !0;
			}
			q = q.parentNode;
		}
		return !1;
	};

	function igk_freeEventContext() {
		igk_unreadyAll();
		if (sm_regEventContext.length > 0) {
			igk_clearEventContent(sm_regEventContext);
			if (sm_regEventContext.length != 0) {
				console.debug("[igk.js] igk_freeEventContext [for some reson not cleared]");
			}
		}
	}
	function igk_show_notify_error(t, m) {
		igk.winui.notify.showErrorInfo(t, m);
	};
	function igk_show_notify_msg(t, m, c) {
		igk.winui.notify.showMsBox(t, m, c);
	};
	function igk_get_script_src() {
		if (typeof (script_src_lnk) != IGK_UNDEF) {
			return script_src_lnk;
		}
		var p = igk_getLastScript();
		if (p) {
			return p.getAttribute("src");
		}
		return null;
	}
	function igk_unRegEventContext(chain) {
		var r = sm_regEventContext.length;
		var s = [];
		for (var i = 0; i < r; i++) {
			if (sm_regEventContext[i] == chain) {
				// register chain
				s.push(sm_regEventContext[i]);
			}
		}
		sm_regEventContext.pop(chain);
		return (r > sm_regEventContext.length);


	}
	function igk_get_html_item_definition_value(item) {
		if (item == null)
			return "<div class=\"igk-notify igk-notify-danger\">item is null</div>";

		var msg = "";
		var func = "";
		var e = igk.createNode("div");
		msg += "<div><div class=\"igk-notify-title\" style=\"color:white;\">" + item.toString() + "</div><div class=\"igk-title-4\">Properties</div><ul>";
		func += "<div class=\"igk-title-4\">Functions</div><ul>";
		var tab = [];
		for (var i in item) {
			tab.push(i);
		}
		igk.system.array.sort(tab);

		// for(var i in item)
		for (var i = 0; i < tab.length; i++) {
			try {
				var n = tab[i];
				var txt = "<div class=\"dispb igk-bigger\">" + n + "=</div>";

				if (typeof (item[n]) == IGK_FUNC) {
					func += "<li class=\"igk-col-lg-12-2\" >" + txt + "</li>";
				}
				else
					msg += "<li class=\"igk-col-lg-12-2\" >" + txt + "<pre class=\"dispb\" style='background-color:white;'>" + item[tab[i]] + "</pre>" + "</li>";

			}
			catch (ex) {
				igk.winui.debug.addMsg("error i :" + i + " : " + ex);
			}
		}
		func += "</ul><div class=\"disptable clearb\"> </div></div>";
		msg += "</ul><div class=\"disptable clearb\"> </div></div>";
		e.setHtml(msg + func);
		return e.o.outerHTML;
	}
	function igk_get_html_item_definition(item) {
		if (item == null)
			return "<div class=\"igk-notify igk-notify-danger\">item is null</div>";

		var msg = "";
		var func = "";
		var e = igk.createNode("div");
		msg += "<div><div class=\"igk-notify-title\" style=\"color:white;\">" + item + "</div><div class=\"igk-title-4\">Properties</div><ul>";
		func += "<div class=\"igk-title-4\">Functions</div><ul>";
		var tab = [];
		// push item in table
		for (var i in item) {
			tab.push(i);
		}
		igk.system.array.sort(tab);
		for (var i = 0; i < tab.length; i++) {
			try {
				var n = tab[i];
				if (typeof (item[n]) == IGK_FUNC) {
					func += "<li class=\"igk-col-lg-5-1\" >" + n + "</li>";
				}
				else
					msg += "<li class=\"igk-col-lg-5-1\" >" + n + "</li>";

			}
			catch (ex) {
				igk.winui.debug.addMsg("error i :" + i + " : " + ex);
			}
		}
		func += "</ul><div class=\"disptable clearb\"> </div></div>";
		msg += "</ul><div class=\"disptable clearb\"> </div></div>";
		e.setHtml(msg + func);
		return e.o.outerHTML;
	}
	function igk_getRegEventContextByOwner(o) {
		if (o == null)
			return null;
		var k = sm_regEventContext;
		for (var i = 0; i < k.length; i++) {
			if (k[i].o == o)
				return k[i];
		}
		return null;
	}
	function igk_getRegEventContext(prop, reg, callback) {// igk-properties,reg,callback
		var k = sm_regEventContext;
		for (var i = 0; i < k.length; i++) {
			if (k[i].properties == prop)
				return k[i];
		}
		if (reg) {
			var c = callback();
			sm_regEventContext.push(c);
			return c;
		}
		return null;
	}
	function igk_clearEventContent(context) {

		var ctab = new Array();
		var k = context;
		if ((k == null) || (k.length == 0))
			return;

		// copy 
		for (var i = 0; i < k.length; i++) {
			ctab.push(k[i]);
		}
		// clear
		for (var j = 0; j < ctab.length; j++) {
			var r = ctab[j];// get chain
			if (r.unregEventContext) {
				r.unregEventContext();
			}
		}
	}

	function igk_unreadyAll() {
		var tab = readyFunc;
		for (var i = 0; i < tab.length; i++) {
			igk.unready(tab[i]);
		}
		readyFunc = [];
	}

	// ----------------------------------------------------------------------------------------
	// igk html utility function
	// ----------------------------------------------------------------------------------------
	function a(a) {
		return IGK_UNDEF === typeof a;
	}
	function b(b) {// check if b exist
		// return (s in b) && (a(b[s]) ||(null===b[s]));
		return a(b) || (null === b);
	}
	function igkJSError(msg) {
		console.debug("igkJSError: " + typeof (this));
		this.name = "igkerror";
		this.message = msg;
		this.level = 1;
		this.toString = function () { return this.message; };
	}

	function __igksetAttribute(ns, n, v) {
		var d = ns.__ATTRIBS__;
		if (d)
			d[n] = v;
	};

	function igk_getCurrentScript() {
		if (m_scriptNode)
			return m_scriptNode;
		if (document.currentScript)
			return document.currentScript;
		// fallback
		var s = document.scripts;
		return s.length > 0 ? s[s.length - 1] : null;
	}
	function igk_getAjxInitiator() {
		return __ajxInitiator;
	}


	function igk_namespaceBuilder(t, a, c, d, callback) {
		var h = 0;
		var i = "";
		var ns = "";
		var ps = "";
		var win = t;
		var _iswin = (t == window); // if not window adding property to object

		if (typeof (win) == 'undefined') {
			throw 'win is not defined';
		}

		var _fileSrc = (d && d.fileSrc) || igk_getCurrentScript().src;
		if ((_fileSrc == "") && (_iswin)) {
			_fileSrc = "@" + m_LibScript;
		}
		this.build = function () {

			function __igksysNameSpace(n, ps) {
				var m_type = new function () {
					igk_appendProp(this, {
						getFullName: function () {
							return n;
						},
						toString: function () { return "class:igk:systemType"; }
					});
				};
				var tn = new igk_namespace();
				var mps = ps;
				var _hierarchie = {
					getParent: function () {
						if (mps)
							return igk_get_namespace(mps);
						return null;
					}
				};
				igk_defineProperty(tn, "namespace", { get: function () { return n; }, set: function () { throw "not allowed"; } });
				igk_defineProperty(tn, "type", { get: function () { return IGK_CLASS; } });
				igk_defineProperty(tn, "fullname", { get: function () { return n; } });
				igk_defineProperty(tn, "hierarchie", { get: function () { return _hierarchie; } });
				igk_appendProp(tn, {
					// namespace: n,
					// type: IGK_CLASS,
					// fullname: n,
					// hierarchie:,
					getParent: function () {



						return this.hierarchie.getParent();
					},
					getType: function () {
						return m_type;
					},
					toString: function () {
						return "namespace[" + this.namespace + "]";
					}
				});
				igk_defineProperty(tn, "__FILE_SRC__", {
					get: function () {
						return _fileSrc;
					}
				});
				igk_defineProperty(tn, "__ATTRIBS__", {
					get: function () {
						return d;
					}
				});
				return tn;

			};

			for (var f = 0 <= a.indexOf(".") ? a.split(".") : [a], h = 0; h < f.length; h++) {
				i = f[h];
				ps = ns;
				if (h > 0)
					ns += ".";
				ns += i;

				(!(i in win) || (typeof (win[i]) == 'undefine') || b(win[i])) && (win[i] = __igksysNameSpace(ns, ps));

				win = win[i];
			}

			if (d) {
				// add addiional definition properties		
				m_attribs[a] = d;
			}
			if (!b(c)) {
				igk_appendProp(win, c);
				if (callback)
					callback(win);
			}
			return win;
		};
		// return;
		// var _fileSrc=(d && d.fileSrc) || igk_getScript().src;


	}
	// >@ create private namespace
	function createPNS(t, a, c, d, callback) {
		if (!a || (typeof (a) != 'string'))
			throw new igkJSError("[igk] - wrong argument: namespace " + a);

		var g = new igk_namespaceBuilder(t, a, c, d, callback);
		return g.build();
	}

	// convert string namespace to balafonJS object
	function igk_get_namespace(n) {
		if (typeof (n) != 'string')
			return null;
		var t = n.split(".");
		var win = window[t[0]];
		if (win) {
			for (var i = 1; win && i < t.length; i++) {
				win = win[t[i]];
			}
			return win;
		}
		return null;
	}

	function igk_console_debug(msg) {
		if (igk.DEBUG && console && console.debug) {
			console.debug(msg);
		}
	}

	// register console if not defined
	if (typeof (console) == IGK_UNDEF) {
		createNS("window", {
			console: new (function () {
				igk_appendProp(this, {
					write: function (msg) {
						alert(msg);
					},
					debug: function (m) {
						// alert(m);
					}
				});
			})()
		});
	}
	// register getComputedStyle if not defined
	if (typeof (getComputedStyle) == IGK_UNDEF) {

		createNS("window", {
			getComputedStyle: function (item, selector) {

				if (!item) return null;

				function __getstylev(i, p) {
					if (i.style) {
						var h = i.style[p];
						if (h)
							return h;
						else if (i.parentNode) {
							return __getstylev(i.parentNode, p);
						}
						else if (i != igk.dom.body().o) {
							return __getstylev(igk.dom.body().o, p);
						}
					}
					return '';
				};
				function __initStyle(item) {
					var d = igk.createNode('div');
					var m = {};
					var t = "";
					m.cssText = '';
					for (var i in d.o.style) {
						if (i != "cssText") {
							m[i] = __getstylev(item, i);// d.o.style[i];
							t += " " + i + ":" + m[i] + ";";
						}
					}
					m.cssText = t;
					return m;
				};
				return __initStyle(item, selector);
			}
		});
	}

	// >@@ name: fullname of function to call
	// >@@ params : array or parameter to pas to function
	function igk_callfunction(name, params) {
		igk_console_debug("call function");
		var func = null;
		if (!name) throw Error("wrong argument: namespace ");
		var h = 0;
		var i = "";
		var ns = "";
		var a = name;
		var t = window;
		// init
		for (var win = t, f = (0 <= a.indexOf(".")) ? a.split(".") : [a], h = 0; h < f.length; h++) {
			if (!f[h]) {
				func = null;
				break;
			}
			i = f[h];
			if (h > 0)
				ns += ".";
			ns += i;
			win = win[i];
			if (!win) { func = null; break; }
			else
				func = win;
		}
		if (func) {
			func.apply(window, params);
		}
		else {
			console.debug("function " + name + " doen't exists");
		}
	}

	function igk_getEmValue(q, property) {
		if (!q)
			return 1.0;
		// property="fontSize";
		// var f=q.getComputedStyle(property);
		var f = q.getComputedStyle("fontSize");
		var e = 0.0;
		var p = null;
		var r = 1.0;
		if (property != "fontSize") {
			e = q.getComputedStyle(property);
			r = Math.round(igk.getNumber(e) / igk.getNumber(f));
		}
		else {
			p = q.getParentNode();
			if (p != null) {
				e = p.getComputedStyle(property);
				r = Math.round(igk.getNumber(f) / igk.getNumber(e));
			}
		}
		return r;
	}

	// >@ get new value number
	function igk_getNumber(value, cibling, property) {
		var ex = /(((-{0,1})[0-9]+(\.[0-9]+){0,1})(px|em|%){0,1})$/;
		var t = ex.exec(value);
		if (t) {
			if (t[5]) {
				switch (t[5]) {
					case "px":
						break;
					case "em":

						if (cibling && property) {
							var r = parseFloat(t[2]);
							var e = igk_getEmValue(cibling, property);
							var h = igk_getNumber($igk(cibling).getComputedStyle(property));
							var o = (r / e) * h;
							return o;
						}
						break;
					case "%":
						break;
				}
			}
			return parseFloat(t[2]);
		}
		return 0;
	}
	function igk_getInt(value) {
		var t = /((-{0,1})[0-9]+(\.[0-9]+){0,1})(px|em|%)*$/.exec(value);
		if (t)
			return parseInt(t[1]);
		return 0;
	}
	function igk_getUnit(value) {
		var t = /([0-9]+(\.[0-9]+){0,1})(px|em|pt|cm|mm|rem|%)*$/.exec(value);
		if (t) {
			if (typeof (t[3]) == igk.constants.undef) {
				return "px";
			}
			return t[3];
		}
		return "";
	}

	function igk_getPixel(value, cibling, property) {
		switch (value) {
			case "auto":
				if (cibling && cibling.parentNode) {
					var v = igk_getPixel($igk(cibling.parentNode).getComputedStyle(property));

					// d=v *(d /100.0);
					return v;
				}
				break;
			default:
				var ex = /(((-{0,1})[0-9]+(\.[0-9]+){0,1})(px|em|%){0,1})$/;
				var t = ex.exec(value);
				if (t) {
					var d = parseFloat(t[2]);
					if (t[5]) {
						switch (t[5]) {

							case "em":
								var e = igk_getEmValue(cibling, property);
								var h = igk_getNumber($igk(cibling).getComputedStyle(property));
								var o = (d / e) * h;
								return o;
								break;
							case "%":

								if (cibling && cibling.parentNode) {
									var v = igk_getPixel($igk(cibling.parentNode).getComputedStyle(property));

									d = v * (d / 100.0);
									return d;
								}
								else {
									console.debug("no cibling : and cibling no parent " + property);
								}
								break;
							case "px":
							default:
								// default value
								break;
						}
					}
					return parseFloat(t[2]);
				}
				break;
		}
		return 0;
	}


	function igk_getv(obj, keys, defaultvalue) {
		if (obj) {
			if (obj[keys])
				return obj[keys];
		}
		return defaultvalue;
	}

	function igk_newGUID() {
		var result = '';
		var hexcodes = "0123456789abcdef".split("");

		for (var index = 0; index < 32; index++) {
			var value = Math.floor(Math.random() * 16);

			switch (index) {
				case 8:
					result += '-';
					break;
				case 12:
					value = 4;
					result += '-';
					break;
				case 16:
					value = value & 3 | 8;
					result += '-';
					break;
				case 20:
					result += '-';
					break;
			}
			result += hexcodes[value];
		}
		return result;
	};
	// @ create window namespace
	// a:string
	// c:properties 
	// d:description
	// callback
	function createNS(a, c, d, callback) {
		return createPNS(window, a, c, d, callback);
	};
	// >@ note that files are not poste
	function igk_get_form_posturi(form) {
		var msg = "";
		var e = null;
		var p = [];
		for (var i = 0; i < form.length; i++) {
			e = form.elements[i];
			switch (e.type) {
				case "radio":
				case "checkbox":
					if (e.checked) {
						if (p[p.id]) {
							var m = p[p.id];
							if (!m.push) {
								t = [];
								t.push(m);
							}
							else
								m.push(e.value);
						}
						else
							p[e.id] = e.value;
					}
					break;
				case "file":// continue
					break;
				default:
					p[e.id] = e.value;
					break;
			}
		}
		e = 0;
		for (var i in p) {
			if (e != 0)
				msg += "&";
			msg += i + "=" + p[i];
			e = 1;
		}
		return msg;
	};
	// define property proprties 
	// <summary>
	// target : object to define
	// name : name of the property
	// name : property=property
	// </summary>
	function igk_defineProperty(target, name, property) {
		if (!target)
			return;
		try {
			var s = typeof (Object.defineProperty);
			if (s == 'function') {
				// define property
				var prop = {};
				if (property.get)
					prop.get = property.get;
				if (property.set)
					prop.set = property.set;
				prop.configurable = igk_getv(property, "configurable", true);
				prop.enumerable = igk_getv(property, "enumerable", true);
				if (!property.set && !property.get)
					prop.writable = igk_getv(property, "writable", false);
				var k = Object.defineProperty(target, name, prop);
				return k;// Object.defineProperty(target,name,prop);
			}
			else {
				if (property.nopropfunc) {
					property.nopropfunc.apply(target);
				}
				else {

					var t = {
						toString: function () { return "data"; }
					};
					target[name] = t; // function(){} property.get;
				}
			}
		}
		catch (ex) {
			if (property.nopropfunc) {
				property.nopropfunc.apply(target);
			}
			else {
				// work arround
				var t = {
					toString: function () {
						return property.get();
					}
				};



				if (igk && igk.navigator && !igk.navigator.isSafari())
					target[name] = t;
			}
		}
	};

	// define numeration on object property with only get value
	function igk_defineGetValue(s) {
		return function () {
			return s
		};
	}
	// define enumeration 
	function igk_defineEnum(o, t) {
		if (!o)
			o = {};
		var _is = (o.isEnum && o.isEnum());

		if (_is) {
			for (var i in t) {
				igk_defineProperty(o, i, { get: igk_defineGetValue(t[i]) });
				o.addEnum(i, t[i]);
			}
		} else {
			for (var i in t) {
				igk_defineProperty(o, i, { get: igk_defineGetValue(t[i]) });
			}
		}


		o.addEnum = function (i, v) {
			t[i] = v;
		};
		o.isEnum = function () {
			return 1;
		};
		o.isValuePresent = function (i) {
			return i in t;
		};
		o.getValues = function () {
			return t;
		};
		o.getComboboxEnumValue = function () {
			var t = this.getValues();
			var g = [];

			for (var i in t) {
				g.push({ name: i, val: i });
			}
			g.sort(function (a, b) {
				return a.name.localeCompare(b.name);
			});
			return g;
		};
		return o;

	}


	// append property
	function igk_appendProp(t, e, override) {
		override = typeof (override) == IGK_UNDEF ? !0 : override;
		if (t && e) {
			for (var j in e) {
				try {
					// var s = Object.getOwnPropertyDescriptor(t, j);
					// if (s && !s.configurable)
					// 	continue;
					t[j] = e[j];// copy function to igk context				
				}
				catch (exception) {
					igk.console_debug(" exception : " + j + " " + exception);
				}
			}
		}
	};
	// create a igk object element
	function __igk(name) {
		if (window.igk && window.igk.DEBUG) {
			// igk.debug.write("[CREATE A IGKOBJECT] " + name +  " : "+igk.ajx);
		}
		if (name == window) {
			throw ("/!\\ Call to __igk function on [window] is not allowed. It will break the igk js framework namespace hierarchi." +
				"if you want to register event please use 'igk.winui.reg_event instead'");
		}
		var item = null;
		if (typeof (name) == "string") {
			// name or expression
			var e = $igk(document).select(name);
			return e;
		}
		else if (typeof (name) == "igk.selector") {
			item = name;
		}
		else {
			item = name;
		}
		if (item == null)
			return null;
		// verify that element containt a o and that the o is igk
		if (item.o && item.o.igk)
			return item;

		if (window.igk) {
			window.igk.initprop(item);
		}
		return item.igk;
	};


	// show property.
	// @i : element to check
	// @element : element to show if element==0 alert prompt
	function igk_show_prop(i, element) {
		var msg = "";
		var space = "\n";
		var s = "";
		if (element != null)
			space = "<br />";
		try {

			var ig = { "outerHTML": 1, "innerHTML": 1 };

			for (s in i) {
				if (s in ig)
					continue;
				try {
					msg += s + "=" + i[s] + space;
				}
				catch (Exception) {
					msg += s + "; ex:[" + Exception + "]" + space;
				}
			}
			if (element == null)
				alert(msg);
			else {
				$igk(element).o.innerHTML = msg;
			}
		}
		catch (ex) {
			msg = "can't evaluate object";
			igk.winui.notify.showError(msg);
		}
	}
	function igk_show_prop_keys(i, element) {
		var msg = "";
		var space = "\n";
		var s = "";

		if (element != null)
			space = "<br />";
		try {


			for (s in i) {

				try {
					msg += s + "" + space;
				}
				catch (Exception) {
					msg += s + "; ex:[" + Exception + "]" + space;
				}
			}
			if (element == null)
				console.error(msg);
			else {
				$igk(element).o.innerHTML = msg;
			}
		}
		catch (ex) {
			igk.show_notify_error("Exception", "can't evaluate object");
		}
	}



	function igk_show_event(i, element) {
		var msg = "";
		var space = "\n";
		if (element != null)
			space = "<br />";
		for (var s in i) {
			if (igk.system.string.startWith(s, "on")) {
				try {
					msg += s + "=" + i[s] + space;
				}
				catch (Exception) {

				}
			}
		}
		if (element == null) {
			igk.winui.notify.showError(msg);
		}
		else {
			$igk(element).o.innerHTML = msg;
		}
	}
	// get all property in html
	function igk_get_prop(i) {
		var msg = "";
		for (var s in i) {
			try {
				msg += s + "=" + i[s] + "<br />\n";
			}
			catch (Exception) {

			}
		}
		return msg;
	}



	// 
	// utility function preload igk-img tag
	// 

	function igk_preload_image(node, async) {
		if ((node == null) || (typeof (node.getElementsByTagName) == igk.constants.undef))
			return;

		// TODO: PRELOAD IMAGe Failed on configuration pages


		function __preload() {
			var v_timg = node.getElementsByTagName("igk-img");
			if (!v_timg || (v_timg.length <= 0)) {
				return;
			}
			var v_tab = new Array();
			// copy items
			for (var s = 0; s < v_timg.length; s++) {
				v_tab[s] = v_timg[s];
			}
			var v_img = null;
			var v_div = null;
			var v_cimg = null;
			// create a preload obj
			var v_preload =
			{
				// load image on document init
				init: function (img, source) {
					// source.parentNode.replaceChild(img,source);
					$igk(img).reg_event("load", function (evt) {
					 
						if (source.parentNode)
							source.parentNode.replaceChild(img, source);
					});
				},
				replace: function (img, source) {
					if (source.parentNode)
						source.parentNode.replaceChild(img, source);
				},
				copyAttribute: function (source, dest) {
					for (var x = 0; x < source.attributes.length; x++) {
						var j = source.attributes[x];
						if (j.name == "src") continue;
						try {

							dest.setAttribute(j.name, j.value);
						}
						catch (Exception) {
							console.debug("error");
						}
					}
				}
			};


			var i = 0, v_host = null;
			for (; i < v_tab.length; i++) {
				// new Image();// 
				var src = v_tab[i].getAttribute("src");

				if (src) {
					v_host = document.createElement("span");
					$igk(v_host).addClass("igk-img-host");
					v_cimg = document.createElement("img");
					v_cimg.source = v_tab[i];
					v_cimg.src = src;
					v_host.appendChild(v_cimg);
					if (/^data:/.test(src)) {
						console.debug("data source");
						v_preload.copyAttribute(v_tab[i], v_cimg);
						v_preload.replace(v_host, v_tab[i]);
					} else {
						if (v_cimg.complete && ((v_cimg.width + v_cimg.height) > 0)) { 
							// console.debug("loding access");
							// directly loaded
							v_preload.copyAttribute(v_tab[i], v_cimg); 
							v_preload.replace(v_cimg, v_tab[i]);
						}
						else {
							// console.debug("loding access 2" + v_cimg.complete);
							// loading from 
							v_img = v_cimg;
							v_preload.init(v_img, v_tab[i]);
							v_preload.copyAttribute(v_tab[i], v_img); 
						}
					}

					
				}
			}
		}


		__preload();

		// setTimeout(__preload,500);

	}

	function igk_preload_anim_image(node) {// preload igk framework image element

		if ((node == null) || (typeof (node.getElementsByTagName) == igk.constants.undef))
			return;
		var v_timg = node.getElementsByTagName("igk-anim-img");
		if (v_timg.length <= 0)
			return;
		var v_tab = new Array();
		// copy items
		for (var s = 0; s < v_timg.length; s++) {
			v_tab[s] = v_timg[s];
		}

		var v_img = null;
		var v_div = null;
		var v_cimg = null;
		var v_preload = {
			replace: function (img, source) {
				try {
					if (source.parentNode) {
						source.parentNode.replaceChild(img.o, source);
					}
				} catch (ex) {
					igk.winui.notify.showError("Error : " + ex + " " + img);
				}
			},
			copyAttribute: function (source, dest) {
				for (var x = 0; x < source.attributes.length; x++) {
					var j = source.attributes[x];
					if (j.name == "src") continue;
					try {
						// dest.setAttribute(j.name,j.value);
					}
					catch (Exception) {
						console.debug("error");
					}
				}
			}
		};
		var i = 0;

		for (; i < v_tab.length; i++) {
			var src = v_tab[i].getAttribute("src");
			var v_width = v_tab[i].getAttribute("width");
			var v_height = v_tab[i].getAttribute("height");
			if (src) {
				v_cimg = igk.createNode("span");
				v_cimg.source = v_tab[i];
				v_cimg.src = src;
				$igk(v_cimg).setCss({
					width: v_width,
					height: v_height,
					cursor: "pointer",
					backgroundImage: "url('" + src + "')",
					overflow: "hidden",
					backgroundImageRepeat: "no-repeat",
					display: "inline-block",
					margin: "0px",
					padding: "0px"

				});

				v_preload.copyAttribute(v_tab[i], v_cimg);
				v_preload.replace(v_cimg, v_tab[i]);
				$igk(v_cimg).reg_event("mouseover", function () { $igk(this).addClass("btnover").setCss({ backgroundPosition: -this.clientWidth + "px" }); });
				$igk(v_cimg).reg_event("mouseleave", function () { $igk(this).rmClass("btnover").setCss({ backgroundPosition: "0px 0px" }); });
				$igk(v_cimg).reg_event("mousedown", function () { $igk(this).addClass("btndown").setCss({ backgroundPosition: -(2 * this.clientWidth) + "px 0px" }); });

			}
		}
	}



	// encode script in base64 
	function igk_base64encode(str) {
		return igk.utils.Base64.encode(str);
	}
	// remove all script
	// x:item
	function igk_remove_all_script(x) {
		var t = x.getElementsByTagName("script");
		var c = t.length;
		for (var i = 0; i < c; i++) {
			t[0].parentNode.removeChild(t[0]);
		}
	}
	function igk_array_copy(tab) {
		var c = tab.length;
		var btab = new Array();
		for (var i = 0; i < c; i++) {
			btab[i] = tab[i];
		}
		return btab;
	}
	function igk_is_array(tab) {
		return tab instanceof Array;
	}
	// evaluate inner script
	function igk_eval_all_script(item) {
		if (item == null) {
			return;
		}
		var s = $igk(item);
		var v_script = "";
		var c = null;
		var tab = null;
		var i = 0;
		if (s == null) {
			return;
		}
		try {
			if (s.o.getElementsByTagName) {
				tab = igk_array_copy(s.o.getElementsByTagName("script"));
				c = tab.length;
				if (c > 0) {
					for (i = 0; i < c; i++) {
						if (!tab[i]) {
							alert('not define ' + tab[i] + " i=" + i + " c=" + c + " tab.length=" + tab.length + " \n " + tab[i - 1].innerHTML);
							continue;
						}
						var j = tab[i];
						v_script = j.innerHTML;
						igk_eval(v_script, j.parentNode, j);
					}
				}
			}
		}
		catch (ex) {
			igk.show_notify_error("Exception",
				"igk_eval_all_script:\nERROR: " + ex + "<br />\n"
				+ "The script have error : " + v_script + "\ni=" + i + " \ntab.length=" + tab.length +
				"<div class='tracebox'> " + ex.stack + "</div>"
			);

		}
	}
	function igk_isdefine(i, d) {// use with declared properties. if try to check that a variable is define use typeof(var) instead

		if (typeof (i) == IGK_UNDEF) {
			if (typeof (d) == IGK_UNDEF) {
				return !1;
			}
			return d;
		}
		return i;
	}
	function igk_eval(s, sn, n) {
		// s:source code	
		// sn: script node the source script tag
		// n: the target node
		m_scriptNode = sn;
		igk.evaluating = !0;
		try {
			eval(s);
		} catch (ex) {
			// for chrome disable code extension in some case.
			var sg = '';
			if (ex.stack) {
				sg = ex.stack.replace('\n', '<br />');
			}
			igk_show_notify_error("Exception",
				"<div class='igk-error'><p>Error: igk_eval</p>Message:<b> "
				+ ex + "</b><pre>"
				// + s + "</pre><div class='tracebox'>" + sg + "</div>"
				+ "</div>"
			);
		}
		m_scriptNode = null;
		igk.evaluating = false;
	}

	// append script to head . 
	function igk_append_script_to_head(item) {
		var tab = item.getElementsByTagName("script");
		var c = tab.length;
		var head = null;

		if (!document.getElementsByTagName("head")[0]) {
			head = igk.createNode("head");
		}
		else {
			head = document.getElementsByTagName("head")[0];
		}
		for (var i = 0; i < c; i++) {
			var scriptText = tab[i].text;
			var scriptFile = tab[i].src;
			var scriptTag = igk.createNode("script");
			if ((scriptFile != null) && (scriptFile != "")) {
				scriptTag.src = scriptFile;
			}
			scriptTag.text = scriptText;
			head.appendChild(scriptTag);
		}
	}
	function igk_confirm_lnk(lnk) {
		var frm = igk_getParentByTagName(lnk, 'form');
		if (frm) {
			frm.confirm.value = 1;
			frm.action = lnk.href;
			frm.submit();
		}
	}
	// get last inserted script
	function igk_getLastScript() {
		var t = document.getElementsByTagName('script');
		if (t.length > 0)
			return t[t.length - 1];
		return null;
	}

	// get parent by tag name
	function igk_getParentByTagName(e, tagname) {
		if ((e == null) || (typeof (tagname) != "string"))
			return null;
		if (e.parentNode == null) return null;
		if ((e.parentNode.tagName != null) && (e.parentNode.tagName.toLowerCase() == tagname.toLowerCase()))
			return e.parentNode;
		return igk_getParentByTagName(e.parentNode, tagname);
	}

	// get parent script Dom Node 
	function igk_getParentScriptByTagName(tagname) {
		var p = m_scriptNode;
		var q = null;
		if (p != null) {
			q = p.parentNode;
			if (tagname) {
				if (q.tagName.toLowerCase() != tagname.toLowerCase())
					return igk_getParentByTagName(q, tagname);
			}
			return q;
			// __parentScript;
		}

		if (tagname != null)
			return igk_getParentByTagName(igk_getLastScript(), tagname);
		else {
			return igk_getLastScript().parentNode;
		}
	}
	// get parent by id
	function igk_getParentById(e, id) {
		if ((e == null) || (id == null))
			return null;
		var p = e.parentNode;
		if (!id.toLowerCase) {
			igk.show_notify_error("Error", "ERROR FOR: " + e + " Request " + id);
			return null;
		}
		var i = id.toLowerCase();
		if (p == null)
			return null;

		if ((p.id != null) && (p.id.toLowerCase() == i))
			return p;
		return igk_getParentById(p, id);

	}

	// retrieve first child by tagname
	function igk_getfirstchild(e, tag) {
		var c = [];
		var t = e.childNodes;
		var p = null;
		for (var i = 0; i < t.length; i++) {
			p = t[i];
			if (p.tagName && (p.tagName.toLowerCase() == tag.toLowerCase())) {
				c[c.length] = p;
			}
		}
		return c;
	}
	// select all child that match attribute
	function igk_getChildsByAttr(item, attribute) {

		if ((item == null) || (attribute == null) || (!item.childNodes))
			return null;
		var t = item.childNodes;
		var p = null;
		var b = null;
		var i = 0;
		var isok = !0;
		var ret = new Array();
		var iret = null;
		for (var i = 0; i < t.length; i++) {
			p = t[i];
			isok = !0;
			for (var j in attribute) {
				if (!p.getAttribute || (p.getAttribute(j) != attribute[j])) {
					isok = false;
					break;
				}
			}
			if (isok) {
				ret[ret.length] = p;
			}
			iret = igk_getChildsByAttr(p, attribute);
			if (iret != null) {
				// copy array to this
				for (j = 0; j < iret.length; j++) {
					ret[ret.length] = iret[j];
				}
			}
		}
		return ret;
	}
	function igk_checkOnePropertyExists(item, proplist) {
		if (item == null) return !1;

		var t = proplist.split(' ');
		for (var s in t) {
			if (typeof (item[t[s]]) !== IGK_UNDEF)
				return !0;
		}
		return !1;
	}
	function igk_checkAllPropertyExists(item, proplist) {
		if (item == null) return !1;

		var t = proplist.split(' ');
		for (var s in t) {
			if (typeof (item[t[s]]) === IGK_UNDEF)
				return !1;
		}
		return !0;
	}
	// retrieve a child by id
	function igk_getChildById(item, id) {
		if ((item == null) || (id == null))
			return null;
		var t = item.childNodes;
		var p = null;
		var b = null;
		var i = 0;
		for (var i = 0; i < t.length; i++) {
			p = t[i];
			if (p.id && (p.id.toLowerCase() == id.toLowerCase())) {
				return p;
			}
			else {

				b = igk_getChildById(p, id);
				if (b != null)
					return b;
			}
		}
		return null;

	}
	// get value integer
	function igk_getvi(item, prop) {
		if (item[prop]) {
			return item[prop];
		}
		else if (item.igk && item.igk["get" + prop]) {
			return item.igk["get" + prop];
		}
		return 0;
	}
	function igk_init_powered(n) {
		// get parent node
		var node = n || window.igk.getParentScriptByTagName('div');
		var node = $igk(n || window.igk.getParentScriptByTagName('div'));

		igk.ready(function () {
			var q = $igk(".igk-powered-viewer").last();

			function _update_pos() {
				if ((q.o.scrollTop > 0) || (q.o.scrollLeft > 0)) {
					//+ update horizontal bar size + global view size
					node.setCss({
						"position": "fixed",
						"right": (q.o.offsetWidth - q.o.clientWidth) + "px",
						"bottom": (q.o.offsetHeight - q.o.clientHeight) + "px"
					});

				} else {
					node.setCss({
						"position": "absolute",
						"right": "0px",
						"bottom": "0px"
					});
				}
			};
			if (q) {
				q.reg_event("scroll", function () {
					_update_pos();
				});
				q.add(node);
				_update_pos();
			}
		});

		// $igk(node).remove();
		return;
		// // 
		// var q = igk.qselect(".igk-powered-viewer").last();

		// if (!q) {
		// // attach a powered to the first most div		
		// q = $igk("div").first();
		// }

		// if (q) {
		// igk.ready(function () {
		// if (window.igk.ctrl) {
		// if (q) {
		// igk_powered_manager(node, q.o);
		// }
		// else
		// igk_debug_t('igk_init_powered', 'no cibling node found');
		// }
		// else

		// });
		// }


	};
	function igk_powered_manager(node, ciblingnode) {
		if (!node) return;

		function init(node, poweredhost) {
			var node = node;
			var s = poweredhost;
			var animinfo = {
				duration: 200, interval: 10,
				animtype: "timeout",
				context: "init_powered_manager",
				effect: "circ",
				effectmode: "easeinout"
			};
			function update_size() {
				var animprop = {
					right: "0px",
					bottom: "0px"
				};
				if ($igk(s).fn.hasVScrollBar()) {
					// get scroll size
					var _q = $igk(s).add("div").addClass("posab fitw fith").setHtml("width:info");
					var h = s.offsetWidth - _q.o.offsetWidth;
					_q.remove();
					animprop.right = (h + 2) + "px";
				}
				if ($igk(s).fn.hasHScrollBar()) {

					var _q = $igk(s).add("div").addClass("posab fitw fith").setHtml("height:info");
					var h = s.offsetHeight - _q.o.offsetHeight;
					_q.remove();
					animprop.bottom = (h + 2) + "px";

					// animprop.bottom="16px";

				}
				$igk(node).animate(animprop, animinfo);
			}

			var v_self = this;
			v_self.toString = function () { return "igk_powered_manager"; };
			var m_eventContext = igk.winui.RegEventContext(this, $igk(this));
			if (m_eventContext) {
				m_eventContext.reg_window("resize", function () { update_size(); });
			};
			update_size();
			function __doc_changed() {
				update_size();
			};
			igk.publisher.register("sys://doc/changed", __doc_changed);
		};


		var s = ciblingnode ? ciblingnode : document.getElementById(ciblingnode);
		if (s) {
			var q = new init(node, s);
		}
		else {
			console.debug("/!\\ parent cibling not found");
		}
	}

	function igk_getdir(uri) {
		if (uri == null)
			return null;
		var d = uri.split('/');
		var o = "";
		for (var s = 0; s < d.length - 1; s++) {
			if (s != 0)
				o += "/";
			o += d[s];
		}
		return o;
	};

	// initialize object
	// @n: source object
	// @d: default object
	function igk_initobj(n, d) {
		if (!n)
			return d;
		for (var i in d) {
			if (i in n) {
				continue;
			}
			n[i] = d[i];
		}
		return n;
	};

	// init
	m_scriptTag = igk_getLastScript();

	// --------------------------------------------------
	// expose global function 
	// --------------------------------------------------
	// define a igk global namespace
	createNS("igk", {}, { desc: "global igk js namespace" });
	createNS("igk.ENVIRONMENT", {}, { desc: "Environment information" });
	createNS("igk.ctrl", {}, { desc: "manage controller" });
	createNS("igk.system", {}, { desc: "global system igk js namespace" });
	createNS("igk.type", {}, { desc: "global igk js namespace" });
	createNS("igk.reflection", {}, { desc: "global igk js namespace" });
	createNS("igk.exception", {}, { desc: "global igk exception class" });
	createNS("igk.os", {}, { desc: "balafon os utility function" });
	createNS("igk.android", {}, { desc: "balafon android utility namespace" });
	createNS("igk.winui", {}, { desc: "winui global igk js namespace. manage interface" });
	createNS("igk.winui.fn", {}, { desc: "utility functions" });
	createNS("igk.winui.notify", {}, { desc: "winui.notify global igk js namespace" });
	createNS("igk.html", {}, { desc: "igk.html namespace. to manage dom element" });
	createNS("igk.html5", {}, { desc: "igk.html5 namespace" });


	igk.log = function (msg, tag, t) {
		var fc = null;
		switch (t) {
			case "i":
				fc = console.log;
				break;
			case "e":
				fc = console.error;
				break;
			case "w":
				fc = console.warning;
				break;
			default:
				fc = console.debug;
				break;

		};
		tag = tag || 'BJS';
		if (fc) {
			if (typeof (msg) == "string")
				fc("[" + tag + "] - " + msg);
			else {
				fc("[" + tag + "]");
				fc(msg);
			}
		}
	};
	createNS("igk.log", {
		debug: function (m) {
			if (igk.DEBUG) {
				igk.log(m);
			}
		},
		error: function (m) {
			if (igk.DEBUG) {
				console.error(m);
			}
		}
	}
		, { desc: 'manage log' });

	createNS("igk.attribute", {
		setAttribute: __igksetAttribute
	});

	// export navite io functions

	createNS("igk.system.io", {
		getdir: igk_getdir,
		getlocationdir: function (inf) {
			if (inf && ('location' in inf))
				return igk_getdir(inf.location);
			return null;
		},
		getData: igk_io_getData,
		getExtension: function (n) {
			return n.split('.').pop();
		},
		getLocation: function () {
			return document.location.href;
		},
		baseUri: function () {
			var uri = "" + window.location;
			var t = "";
			uri.replace(/^([^#;\?]+)/g, function (m, c, s) {
				t = m;
			});
			while ((t.length > 0) && (t[t.length - 1] == "/")) {
				t = t.substr(0, t.length - 1);
			}
			return t;
		},
		baseUriDomain: function () {
			var port = "";
			var loc = window.location;
			var is_secure = (/^https/.test(loc.protocol));
			if (((!is_secure) && (loc.port != '') && (loc.port != 80)) || (is_secure && (loc.port != '') && (loc.port != 443))) {
				port += ':' + loc.port;
			}
			return loc.protocol + "//" + loc.hostname + port;
		},
		rootUri: function () { // get uri location setup base base tag
			var t = document.head.getElementsByTagName("base");
			if (t.length == 0) {
				var s = igk.getDocumentSetting();
				var h = 0;
				if (s)
					h = s.baseuri;

				return (h || (igk.system.io.baseUriDomain())) + "/";
			} else {
				return document.baseURI || t[0].href;
			}
		},
		URL: function (uri) {
			//construct a url object
			var _NS = igk.system.io;
			var src = uri;
			var relative = 0;
			var _opts = {
				relative: 0,
				isLocal: 0,
				uri: uri,
				request: ''
			};
			if (/^(\.(\.)?\/|\/[^\/]+)/.test(uri)) {
				_opts.relative = 1;
				_opts.isLocal = 1;
				_opts.protocol = window.location.protocol;

				// while(uri.indexOf('../')){
				// uri = uri.substring(3);
				// }
				var tab = uri.split('/');
				var i = 0;
				var luri = '';
				var ruri = _NS.rootUri();
				// while ((ruri.length>0) && ruri[ruri.length-1]=='/' ){
				// ruri = ruri.substring(0, ruri.length-1);
				// }

				for (var j = 0; j < tab.length; j++) {
					switch (tab[j]) {
						case '..':
							if (i) {
								//remove last entry
								luri = luri.substring(0, luri.lastIndexOf('/'));
							} else {
								ruri = ruri.substring(0, ruri.lastIndexOf('/'));
							}
							break;
						case '.':
							break;
						default:
							if (luri.length > 0)
								luri += "/";
							luri += tab[j];
							i = 1;
							break;
					}
				}



				_opts.uri = ruri + '/' + luri;
				_opts.request = '/' + luri;

			} else {
				if (uri.startsWith(_NS.rootUri())) {
					_opts.isLocal = 1;
					_opts.uri = uri;
					_opts.protocol = window.location.protocol;
					_opts.request = "/" + uri.substring(_NS.baseUriDomain().length + 1);

				} else if (/^(((http(s)?|ftp|sw):)?\/\/)/.test(uri)) {
					_opts.uri = uri;
					_opts.request = "";
				}
			}

			igk.appendProperties(this, {
				toString: function () {
					return _opts.uri;
				}
			});
			igk.defineProperty(this, "relative", { get: function () { return _opts.relative; } });
			igk.defineProperty(this, "isLocal", { get: function () { return _opts.isLocal; } });
			igk.defineProperty(this, "uri", { get: function () { return _opts.uri; } });
			igk.defineProperty(this, "request", { get: function () { return _opts.request; } });

		},
		loadLangRes: function (uri, lang, callback, error) {

			var _getRes = 0;
			var _chtml = igk.dom.html();
			var _lang = lang || (_chtml ? _chtml.getAttribute("lang") : null) || igk.navigator.getLang();
			var _loc = igk.system.io.getdir(uri);
			var _uri = igk.resources.getlang(_loc, _lang);


			// error.apply(this);
			// return null;
			var _promise = {
				then: function (callback) {
					this.__then = callback;
				}
			};


			try {
				if (igk.navigator.isSafari())
					throw ('safari not handle async - await');

				// TODO: Update to function 
				eval(["_getRes = async function (){",
					"var o=0; ",
					"try{",
					"o  = await igk.ajx.asyncget(_uri,null, 'text/json');",
					"o  = typeof(o)=='string' && o.length>0 ? JSON.parse(o) : null;",
					"return  o;",
					"}catch(ex){",
					" console.error(ex);",
					"}",
					"return null;",
					"};"].join(" "));

			}
			catch (ex) {

				_getRes = function () {
					// var _ext = igk.navigator.isIE() ? ".ejson" : ".json";//because of mime type        
					var _ext = ".json"; // get data will get from ajx of mime type        
					var _uri = igk.resources.getlang(_loc, _lang);

					if (!document.body) {
						igk.ajx.get(_uri, null, function (xhr) {
							if (this.isReady()) {
								if (e.__then__) {
									e.__then__(JSON.parse(xhr.responseText));
								}
							}
						});
					} else {

						igk.system.io.getData(_uri, function (o) {

							if (e.__then__ && o.data && o.data.length > 0)
								e.__then__(JSON.parse(o.data));
							else if (e.__catch__)
								e.__catch__("/!\\ loadLangRes : " + _uri + "  " + o.data);
						}, "application/json");

					}
					var e = {
						"then": function (t) {
							this.__then__ = t;
							return this;
						},
						"catch": function (t) {
							this.__catch__ = t;
							return this;
						}
					};
					return e;
				}
			}

			if (typeof (_getRes) != 'undefined') {
				_getRes().then(function (o) {
					callback(o);
					// var _res = o;
					if (_promise.__then) {

						_promise.__then();
					}
					// igk.winui.events.raise(_sysnode, "resLoaded");
				}).catch(function (e) {
					var error = 1;
					console.error("[BJS] - there is an error : " + e +
						"\nUri:" + _uri);
				});

			}
		}
	});

	createNS("igk.resources", {
		getlang: function (_loc, t) {
			if (!_loc) {
				return null;
			}
			if (!t)
				t = igk.dom.html().getAttribute("lang") || igk.navigator.getLang();

			return _loc + '/Lang/res.' + t + '.json';
		}
	});

	createNS("igk.system.Db", {
		getIndexedDb: function () {
			return window.indexDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB;
		}
	});

	(function () {

		igk.system.Promise = function () {
			var _t = [];
			var _e = [];
			this._t = _t;
			this._e = _e;
		};
		igk_appendProp(igk.system.Promise.prototype, {
			resolve: function (args) {
				for (var j = 0; j < this._t.length; j++) {
					this._t[j].apply(this, args);
				}
			},
			reject: function (args) {
				for (var j = 0; j < this._e.length; j++) {
					this._e[j].apply(this, args);
				}
			},
			then: function (callback) {
				this._t.push(callback);
				return this;
			},
			error: function (callback) {
				this._e.push(callback);
				return this;
			}
		});

	})();

	// new function added for promise call usage
	igk.system.promiseCall = function () {
		var _p = {};

		return {
			then: function (func) {
				igk.appendChain(_p, 'then', func);
				return this;
			},
			error: function (func) {
				igk.appendChain(_p, 'error', func);
				return this;
			},
			resolve: function () {
				if ('then' in _p) {
					_p.then();
				}
			},
			failed: function () {
				if ('error' in _p) {
					_p.error();
				}
			}

		};

	};



	//igk namespace utility function 
	createNS("igk", {
		getQueryOptions: function (s) { //retrive query options from string
			var e = {};
			s.replace(/(;)?([^;]+)=([^;]+)/ig, function (m, t, n, v) {
				e[n] = v.trim();
			});
			return e;
		},
		getDocumentSetting: function () {
			return __initDocSetting;
		},
		isInteger: function (n) {
			var f = Number.isInteger || _is_integer;
			return f(n);
		},
		invokeAsync: function (callback, args) {
			// custom async data call
			setTimeout(function () {
				callback.call(null, args);
			}, 1);

		},
		isFunc: function (n) {
			return n == 'function';
		},
		configure: function (o) {// load configuration setting
			for (var i in o) {
				if (typeof (o[i]) == 'function') continue;
				__igk_settings[i] = o[i];
			}
		},
		regex: function () { // get global regex list
			return _rgx;
		},
		getSettings: function () { return __igk_settings; },
		object: igk_object,
		is_notdef: igk_is_notdef,
		is_string: igk_is_string,
		is_object: igk_is_object,
		reg_tag_component: igk_reg_tag_obj,
		die: function (msg, code) {
			throw code + " : " + msg;
		},
		geti: function (t) {
			if (t) return 1;
			return 0;
		},
		stop_event: igk_stop_event,
		bool_parse: function (t) {
			if (igk_is_string(t)) {
				var s = t.toLowerCase();
				if ((s.length == 0) || (s == "false") || (s == "0"))
					return !1;
				return !0;
			}
			if (t)
				return !0;
			return !1;
		}
	}, { desc: "global igk.object base class namespace" });

	// define global namespace variable
	var $_NS = createNS("igk.namespaces", {
		xhtml: "http://www.w3.org/1999/xhtml",
		xml: "http://www.w3.org/1999/xhtml",
		xsl: "http://wwww.w3.org/1999/XSL/Transform",
		svg: "http://www.w3.org/2000/svg"
	});
	igk_defineProperty($_NS, "igk", {
		get: function () {
			return "https://schemas.igkdev.com/balafonjs"
		}
	});





	createNS("igk.convert", {
		toHtmlColor: function (i) {
			var c = igk.system.colorFromString(i);
			if (c)
				return c.toHtml();
			return 0;
		}
	});

	var __jsload = {};
	var __jstoload = {};
	createNS("igk.js", {
		load: function (s) {
			if (s in __jsload)
				return;
			var p = document.createElement("script");
			var e = 0;
			p.type = "text/javascript";
			p.language = "javascript";
			p.async = true;
			p.onerror = function () {
				console.error("failed to load " + s);
				$igk(p).remove();
				delete (__jsload[s]);
				e = 1;
			};
			p.onload = function (e) {
				console.debug("laod complete " + p.readyState);
			};

			igk.ajx.get(s, null, function (xhr) {
				if (this.isReady()) {
					p.innerHTML = xhr.responseText;
				}
			}, true);
			document.head.appendChild(p);
			// 		igk.dom.body().prepend(p);
			__jsload[s] = 1;

		}
		,
		require: function (scs, sync, host) {
			// @scs: scripts source array
			// @async: synchronize call :  true|false default is false. 
			var syncdata = 0;
			host = host || document.head;
			function __loadscript(i) {
				// syncdata
				if ((i < 0) || (i >= scs.length)) {
					return;
				}

				var u = scs[i];
				if (__jsload[u] || !__jstoload[u]) {
					i++;
					syncdata.skipped++;
					if (def.c >= 0) {
						__loadscript(i);

					}
					return;
				}
				// if (!__jstoload[u]){
				// return;
				// }


				__jsload[u] = 1;
				__jstoload[u] = 0;
				var sc = document.createElement("script");
				sc.src = u;
				def.e[u] = i;

				sc.onload = (function (i, u) {
					return function () {
						syncdata.loaded++;
						syncdata.files = igk.isDefine(syncdata.files, []);
						syncdata.files.push(u);
						delete (def.e[u]);
						i++;
						if (i > scs.length - 1) {
							__invoke();
						}
						else {
							__loadscript(i);
						}
						$igk(sc).remove();
					};
				})(i, u);
				sc.onerror = function () {
					console.error("/!\\ failed:" + u);
				};
				document.head.appendChild(sc);
			};

			var prop = {};
			var def = { c: 0, e: {} };
			var u = 0;
			sync = sync || 0;
			function _fail(message) {
				if (prop.failed)
					prop.failed.apply(o, [message]);
			};
			var _scripts = [];
			var o = {
				promise: function (callback, h) {
					// @ success - callback
					// @h the host parameter
					prop.callback = callback;
					prop.host = h;
					if (def.c <= 0) {
						// direct invoke promise
						__invoke();
					}
					return this;
				},
				fail: function (callback) {
					prop.failed = callback;
					return this;
				},
				load: function (callback) {
					//called when a script is loaded 
					prop.load = callback;
					return this;
				}
			};

			function _loadinScript(u, type, index) {
				return function (xhr) {
					if (this.isReady()) {
						__jsload[u] = xhr.responseText;
						try {
							var s = document.createElement("script");
							s.type = type || "text/javascript";
							s.innerHTML = __jsload[u];
							_scripts.push(s);
							if (prop.load) {
								prop.load(s);
							}
							delete (def.e[u]); def.c--;
						} catch (ex) {
							console.error("error loading : " + ex);
						}
						if (def.c == 0) {
							__invoke();
						}
					}
				};
			};

			function __invoke() {
				for (var g = 0; g < _scripts.length; g++) {
					host.appendChild(_scripts[g]);
				}
				_scripts = [];
				var m = 0;
				if (prop.host)
					m = $igk(prop.host).o;
				if (prop.callback)
					prop.callback.apply(o, [m, syncdata]);
			};

			function _loadFormalScript(u, i) {
				var sc = document.createElement("script");
				sc.src = u;
				def.e[u] = i;
				def.c++;
				sc.onload = (function (i, u) {
					return function () {
						if (prop.load) {
							prop.load(sc);
						}
						delete (def.e[u]); def.c--; if (def.c == 0) {
							__invoke();
						}
					};

				})(i, u);
				sc.onerror = function () {
					console.error("/!\\ failed: " + u);
					_fail();
				};
				host.appendChild(sc);
			};


			if (sync) {
				def.c = scs.length;
				syncdata = {
					loaded: 0,
					skipped: 0,
					status: typeof (null)
				};
				for (var i = 0; i < scs.length; i++) {
					if (!__jstoload[scs[i]])
						__jstoload[scs[i]] = 1;
				}
				__loadscript(0);

			} else {
				//async call
				var _type = null;
				for (var i = 0; i < scs.length; i++) {

					u = scs[i];
					_type = null;

					if (typeof (u) == 'object') {
						_type = u.type;
						u = u.uri;

					}


					if (__jsload[u]) {
						def.c--;
						continue;
					}
					if (u in scs) {
						igk.ajx.get(u, null, _loadingScript(u, _type, i));
						continue;
					};
					__jsload[u] = 1;
					_loadFormalScript(u, i);


				}
			}
			// promise object
			return o;
		}
		, getLoadedScript: function () {
			return __jsload;
		},
		loadInlineScript: function () {
			var d = igk.getParentScript();
			var sc = $igk(igk.getCurrentScript());
			if (!d)
				return;
			var l = [];
			$igk(d).select('script').each_all(function () {
				var s = this.o.src;
				var r = this.getAttribute("reloaded");

				if (s && (!(s in __jsload) || r)) {
					if (r) {
						l.push(s);
						l[s] = null;
						delete (__jsload[s]);
						delete (__jstoload[s]);
					} else {
						l.push(s);
					}

				}
				this.remove();
			});

			if (l.length > 0)
				igk.js.require(l, true, d).promise(function () {
					sc.remove();
				});
		}
	});

	//store inline script to eval
	igk.scripts = {};
	igk.scripts["@injectlocation"] = "((typeof(getLocalScriptUri) !='undefined')?" + "getLocalScriptUri():" + "igk.system.io.getlocationdir(igk.getScriptLocation()))";




	(function () {
		// store global event
		createNS("igk.evts", {
			stop: igk.stop_event
		});
		igk.evts.dom = [
			"sys://dom/pageloaded",
			"sys://dom/bodyreplaced",
			"sys://ddom/nodetextchanged"
		];
	})();



	createNS("igk.reflection", {
		getFunctions: function (item) {// get an array of functions
			if (!item)
				return null;
			var r = [];
			for (var i in item) {
				if (typeof (item[i]) == IGK_FUNC) {
					r.push(i);
				}
			}
			return r;
		},
		// getFunctions:function(e){
		// var t=[];
		// if(typeof(e) !=IGK_UNDEF){
		// for(var i in e){
		// if(typeof(e[i])=="function"){
		// t.push(i);
		// }
		// }
		// }
		// return t;
		// },
		getProperties: function (e) {
			var t = [];
			if (typeof (e) != IGK_UNDEF) {
				if (typeof (e) == 'string') {
					for (var i in e) {
						if (!igk.isInteger(i) && (typeof (e[i]) != "function")) {
							t.push(i);
						}
					}
				} else {
					for (var i in e) {
						if (typeof (e[i]) != "function") {
							t.push(i);
						}
					}
				}
			}
			return t;
		}
	});


	createNS("igk.html5", {
		animate: igk_animate,
		cancelAnimate: igk_animate_cancel
	});

	__nsigk = igk;// igk namespace
	// window.igk= igk;
	window.$igk = function (n) { return __igk(n) };// register function igk function
	window.$ns_igk = __nsigk;
	window.ns_igk = __nsigk;
	window.igk.wln = console.debug;

	// tiny mce require matchMedia. provide a match media
	if (typeof (matchMedia) == 'undefined') {
		window.matchMedia = function () {
			return 0;
		};
	}

	(function () {
		//igk.io
		createNS("igk.io", {
			copyToClipboard: function (s) {
				var g = document.createElement("textarea");
				var selected =
					document.getSelection().rangeCount > 0
						? document.getSelection().getRangeAt(0)
						: !1;
				g.innerText = s;
				g.style.position = "absolute";
				g.style.left = "-9999";
				document.body.appendChild(g);
				g.select();
				document.exec("copy");
				$igk(g).remove();

				if (selected) {
					document.getSelection().removeAllRanges();
					document.getSelection().addRange(selected);
				}
			}
		});
	})();




	// loader
	(function () {
		// load files igk.io.file
		createNS("igk.io.file", {
			/**
			 * download the current data
			 * */
			downloadData: function (name, data) {
				var a = igk.createNode("a");
				igk.dom.body().appendChild(a.o); // not require in IE 
				a.o.download = name;
				a.o.href = data;
				a.o.click();
				a.remove();
			},
			// load a html or text document,
			// >u: uri
			// >f: callback function on document recieve
			load: function (u, f) { //load file argument
				if (!f)
					throw ("argument f require");

				f = typeof (f) == "function" ? f : f.complete;
				var eCb = f.error || null;// error callback

				function getNodeData(n) {
					var dummy = igk.createNode("dummy");
					var c = n.childNodes.length;
					var i = 0;
					var m = null;


					// copy node

					while (c > 0) {
						m = n.childNodes[i];

						switch (m.nodeType) {
							case 8:// comment
							case 3:// empty text node
							case 11:
							case 10:
								i++;
								break;
							default:
								dummy.o.appendChild(n.childNodes[i]);
								break;
						}
						c--;
					}
					var v_d = dummy.getHtml();  // ob.o.contentDocument.documentElement.getElementsByTagName("body")[0].innerHTML;		
					if (v_d == null) {
						return null;
					}
					return v_d;
				};

				var __loaded = 0;
				var ob = igk.createNode('object');
				ob.addClass('no-visible posab').setCss({ width: '1px', height: '1px' });
				// !important  for ie prepend data		
				ob.o.type = igk.navigator.isXBoxOne() ? "text/html" : "text/plain";	// on xbox one error		
				// ob.o.type="text/html";
				// alert(ob.o.type);		

				igk.dom.body().prepend(ob);
				ob.reg_event("error", function (evt) {
					if (__loaded || (igk.navigator.isFirefox() && igk.navigator.getFirefoxVersion() >= 50)) {
						return;
					}

					if (eCb) {
						eCb.apply(ob);
					}
					// alert("error for : "+u);

					f.apply(document, [{
						error: 1,
						data: evt,
						msg: "/!\\error: " + u
					}]);
					ob.remove();
				});
				ob.reg_event("load", function (evt) {

					// alert(ob.o.contentDocument.readyState);
					try {
						if (!ob.o.contentDocument || ob.o.contentDocument.readyState != "complete")
							return;
						var s = ob.o.contentDocument;
						var d = getNodeData(s);
						f.apply(document, [{
							data: d,
							document: s,
							uri: u
						}]);
						__loaded = 1;
					}
					catch (e) {
						console.error("Exception arrived : " + e);
					}
					finally {
						ob.remove();
					}

				});
				// load data
				// cause error for visual studio webBrowser
				// igk.show_prop(igk.getSettings());
				// ob.o.type  = "";// text/html";
				// alert("setting request ? " +u+" "+igk.getSettings().nosymbol);


				ob.o.data = u;

			}
		});
	})();

	igk_appendProp(igk.object.prototype, {
		toString: function () {
			return "igk.object";
		},
		getType: function () {
			return igk.object;
		}
	}
	);

	function igk_regex_constant() {

		return {
			tagName: /^[\w_]+[\w0-9_\-]*$/,
			className: /^\.[\w_]+[\w0-9_\-]*$/,
			idSearch: /^#[\w\-_]+(\[[\w\-_]*\])?$/
		}

	}
	function isUndef(d) {
		return (d == "unknown") || (d == IGK_UNDEF) || (typeof (d) == IGK_UNDEF);
	}

	var _FM_ = "BalafonJS";


	function __igk_nodeProperty(element) {

		var m_o = element;
		var m_self = this;
		var m_anim; // animation property
		var m_timeOutList = [];
		var m_unregfuncs = [];
		var m_config = {};
		var m_data = {};
		// define readonly property
		igk_defineProperty(this, "o", {
			get: function () { return m_o; },
			nopropfunc: function () { this.o = m_o; }
		});
		igk_defineProperty(this, "t", {
			get: function () { return $igk(m_o.parentNode); },
			nopropfunc: function () { this.t = $igk(m_o.parentNode); }
		});
		igk_defineProperty(this, "timeOutList", {
			get: function () { return m_timeOutList; }
		});

		// used to store extra data



		// this.o=element;
		// define function property
		createPNS(this, "fn", {// function utility
			o: element,
			igk: m_self,
			vscrollWidth: function () { return this.o.offsetWidth - this.o.clientWidth; },
			hscrollHeight: function () { return this.o.offsetHeight - this.o.clientHeight; },
			hasVScrollBar: function () { // has vertical scrollbar
				var h = this.igk.getHeight();
				// return (this.o.clientHeight > 0) && (this.o.scrollHeight > h);
				return (this.o.clientHeight > 0) && (this.o.offsetHeight > h);
			},
			hasHScrollBar: function () { // has horizontal scrollbar
				var w = this.igk.getWidth();
				// return (this.o.clientWidth > 0) && (this.o.scrollWidth > w);
				return (this.o.clientWidth > 0) && (this.o.offsetWidth > w);
			}
		});

		createPNS(this, "data", {
			contains: function (k) {
				if (typeof (m_data[k]) != IGK_UNDEF)
					return !0;
				return !1;
			},
			add: function (k, v) {
				m_data[k] = v;
			},
			remove: function (k) {
				delete (m_data[k]);
			},
			getData: function () {
				return m_data;
			}
		});

		this.getUnregfunc = function () {
			return m_unregfuncs;
		};
		this.getConf = function () {
			return m_config;
		};
		// 
		// configuration 
		// 
		// used to store properties configuration on igk node
		// 

		if (!this.isSr()) {
			// -------------------------------
			// not selector properties
			// -------------------------------
			var m_bE = "igk-eventcreated";
			igk_appendProp(this, {
				// -------------------------------
				// EVENTS
				// -------------------------------
				raiseEventCreated: function (n) {
					var e = this.o[m_bE];
					if (e) {
						e.name = n;
						this.raiseEvent(m_bE);
					} else {
						console.error("no event to raise : " + n);
					}
				},
				postRegisterEvent: function (n, func) {
					if (!n || !func)
						return;
					if (n == "igk-eventcreated") {
						this.reg_event(n, func);
					}
					else {
						if (typeof (this.o[n]) == IGK_UNDEF) {
							var _b = this;
							this.reg_event(m_bE, function _postevent(evt) {
								if (evt.name == n) {
									_b.unreg_event(m_bE, _postevent);
									_b.reg_event(n, func);
								}
							});
						}
						else {
							this.reg_event(n, func);
						}
					}
				}
			});
			this.addEvent("igk-eventcreated", { name: null });


			// good way to register function extension according to tag name
			var tag = element.tagName;
			if (tag) {
				var b = igk.system.createExtensionProperty(tag, "igk.dom.extension", this);
			}
		}
	};
	function __extensionPrototype(tag) {
		this.ctag = tag;
		igk_defineProperty(this, "tagName", { get: function () { return this.ctag } });
		igk_defineProperty(this, "IsExtension", { get: function () { return true; } });
		this.__proto__ = __igk_nodeProperty.prototype;
	}

	var __prop = __igk_nodeProperty.prototype;
	igk_appendProp(__prop, {
		setConfig: function (k, v) {
			this.getConf()[k] = v;
		},
		getConfig: function (k) {
			var m_config = this.getConf();
			return k in m_config ? m_config[k] : null;
		},
		getConfigKeys: function () {
			var t = [];
			var m_config = this.getConf();
			for (var i in m_config) {
				t.push(i);
			}
			return t;
		}


		// -------------------------------
		// -------------- SET PROPERTIES			
		// -------------------------------

		, toString: function () { return "igk_node_properties[" + this.o + "]"; },
		get: function (s, d) {
			if (typeof (d) == 'undefined') {
				d = 0;
			}
			var _t = s.split('/');
			var _q = this;
			for (var i = 0; i < _t.length - 1; i++) {
				if (_q[_t[i]])
					_q = _q[_t[i]];
				else {
					_q[_t[i]] = {};
					_q = _q[_t[i]];
				}
			}
			return _q[_t[i]] ? _q[_t[i]] : d;
		},
		unregister: function () {
			// unregister functions. delete mecanism

			// unregevents 
			var m_unregfuncs = this.getUnregfunc();

			igk.winui.unreg_system_event_object(this);
			for (var i = 0; i < m_unregfuncs.length; i++) {
				m_unregfuncs[i].apply(this);
			}
		},
		getParentCtrl: function (func) {
			return $igk(igk.ctrl.getParentController(this.o));
		},
		registerUnregCallback: function (func) {// register function that will be call on unreg mecanism
			if (func) {
				m_unregfuncs.push(func);
			}
		},
		// -------------- DOM FUNCTIONS
		add: function (t, properties) {
			if (typeof (t) == "string") {
				var c = $igk(this.appendNChild(t));
				c.setProperties(properties);
				return c;
			}
			return this.appendChild(t);
		},
		addText: function (s) {
			var i = document.createTextNode(s);
			return this.add(i);
		},
		addComment: function (txt) {
			var i = document.createComment(txt);
			return this.add(i);
		},
		addXml: function (tag) {
			var _export = $igk(document.createElementNS(igk.namespace.XML, tag));
			this.add(_export);
			return _export;
		},
		addNode: function (tag) {
			var _export = document.createElementNS(this.o.namespaceURI, tag);
			this.o.appendChild(_export);
			return _export;
		},
		isOnDocument: function () {// get if this node is present to document
			var q = 0;
			if (this.isSr())
				return 0;
			q = this;
			while ((q = q.o.parentNode) && (q != igk.dom.body().o)) {
				// 
				q = $igk(q);
			}
			return q == igk.dom.body().o;
		},
		prepend: function (n) {// prepend child
			var i = null;
			if (typeof (n) == "string")
				i = igk.createNode(n);
			else i = $igk(n);
			if (i == null)
				return null;

			if (this.o.firstChild == null) {
				this.o.appendChild(i.o);
			}
			else // insert before 
			{
				this.o.insertBefore(i.o, this.o.firstChild);
			}
			return i;
		},
		replace: function (i, by) {
			if (!this.isSr()) {
				this.o.replaceChild($igk(by).o, $igk(i).o);
			}
			else {
				this.o.each(this.replace, arguments);
			}
			return this;
		},
		fullscreen: function () {
			if (!this.isSr()) {
				var fc = igk.fn.getItemFunc(this.o, "requestFullScreen");
				if (fc) {
					igk.publisher.publish("sys://dom/beforefullsizeRequest");
					fc.apply(this.o);
				}
			}
			return this;
		},
		addSpace: function () {
			if (this.isSr()) {
				this.each(this.addSpace, arguments);
			}
			else
				this.o.appendChild(igk.createHtml("&nbsp;").o);
			return this;
		},
		// style functions
		// -------------- STYLES FUNCTIONS
		setOpacity: function (v) {
			if (this.isSr()) {
				this.o.each(this.setOpacity, arguments);
				return this;
			}
			else {
				this.o.style.opacity = v;
				if (this.o.style.filter) {// for internet explorer
					this.o.style["filter"] = "alpha(opacity=" + (v * 100) + ")";
				}
				this.o.style["-moz-opacity"] = v;
				this.o.style["-khtml-opacity"] = v;
			}
			return this;
		},
		setBoxShadow: function (offsetX, offsetY, blur, color) {
			// mod,ie10,chrome
			this.o.style["boxShadow"] = offsetX + " " + offsetY + " " + blur + " " + color;
		},
		setTextShadow: function (offsetX, offsetY, blur, color) {
			// mod,ie10,chrome
			this.o.style["textShadow"] = offsetX + " " + offsetY + " " + blur + " " + color;

		},
		focus: function () { if (this.o.focus) this.o.focus(); },
		click: function () {
			if (this.o.click) {
				this.o.click();
			}
			else {
				var e = document.createEvent('HTMLEvents');
				e.initEvent("click", true, true);
				this.o.dispatchEvent(e);
			}
			return this;
		},
		clone: function () {// return a clone object of this node
			var cl = igk.createNode(this.o.tagName.toLowerCase(), this.o.namespaceURI);
			cl.copyAttributes(this);
			if (this.o.childNodes.length > 0) {
				for (var i = 0; i < this.o.childNodes.length; i++) {
					if (this.o.childNodes[i].tagName)
						cl.add($igk(this.o.childNodes[i]).clone());
					else {
						cl.o.appendChild(document.createTextNode($igk(this.o.childNodes[i]).text()));
					}
				}
			}
			return cl;

		},
		copyAttributes: function (i) {
			i = $igk(i);
			if ((i == null) || !i.o.hasAttributes || !i.o.attributes)
				return;
			var j = "";
			for (var k = 0; k < i.o.attributes.length; k++) {
				j = i.o.attributes[k];
				try {
					this.o.setAttribute(j.name, j.value);
				}
				catch (Exception) {
					console.debug("error when try to copy " + j.name);
				}
			}
			return this;
		},
		each: function (func, args) {
			if (this.o.each)
				this.o.each(func, args);
			return this;
		},
		each_all: function (func, args) {
			if (this.o.each_all)
				this.o.each_all(func, args);
			return this;
		},
		getItemAt: function (index) {
			if (this.o.getItemAt) {
				return this.o.getItemAt(index);
			}
			return null;
		},
		index: function () { // get index of the element
			var k = 0;
			e = this.o;
			while (('previousCibling' in e) && ((e = e.previousCibling) != null)) {
				k++;
			}
			return k;
		},
		first: function (index) { // get the first
			if (this.o.getItemAt) {
				return this.o.getItemAt(0);
			}
			return null;
		},
		last: function (index) { // get the last
			if (this.o.getItemAt) {
				return this.o.getItemAt(this.o.getCount() - 1);
			}
			return null;
		},
		found: function () { // get if selection get on on more items 
			if (this.o.getItemAt) {
				return this.o.getCount() > 0;
			}
			return !1;
		},
		getNodeAt: function (index) {
			if (this.o.getNodeAt) {
				return this.o.getNodeAt(index);
			}
			return this.o.ChildNodes[index];
		},
		getCount: function () {
			if (this.o.getCount)
				return this.o.getCount();
			return 0;
		},
		isSr: function () { // check if selector. the each method is define
			if (this.o.each)
				return !0;
			return !1;
		},
		getChildCount: function () {
			if (this.o.childNodes) {
				return this.o.childNodes.length;
			}
			return 0;
		},
		timeOut: function (time, func) {// time out function class/ uses with animation callback
			var q = this;
			var s = this.timeOutList.length;
			var i = setTimeout(function () {
				func.apply(q);
				q.timeOutList.pop(s);
			}, time);
			q.timeOutList.push(i);
			return this;
		},
		clearTimeOut: function () {
			while (this.timeOutList.length > 0) {
				clearTimeout(this.timeOutList[0]);
				this.timeOutList.pop(0);
			}
			return this;
		},
		on: function (method, func, useCapture) {
			// sample: target.on('click', func) ; shortcut to reg_event
			if (this.o.each) {
				this.o.each(this.on, arguments);
				return this;
			}
			else {
				igk.winui.reg_event(this.o, method, func, useCapture);
				return this;
			}
		},
		waitInterval: function (duration, func) {
			if (this.isSr()) {
				this.o.waitInterval(duration, func);
			} else {
				var q = this;
				setTimeout(function () {
					func.apply(q)
				}, duration);

			}
			return this;
		},
		css: function (value) {
			if (this.o.each) {
				this.o.each(this.css, arguments);
				return this;
			}
			else {
				this.o.style = value;
				return !0;
			}
		},
		getCssSelector: function () {// get style node selection
			var o = 0;
			var cl = 0;
			o = this.o.tagName.toLowerCase();
			if (this.o.id)
				o += "#" + this.o.id;
			cl = this.o.className;
			if (cl) {
				var t = cl.split(" ");
				for (var k = 0; k < t.length; k++) {
					o += "." + t[k];
				}
			}
			return o;
		},
		getCssValue: function () {
			var o = "";
			var v, d;

			var prop = igk.css.getProperties();
			var q = this;
			var check = typeof (window.getDefaultComputedStyle) == 'function' ? function (n, d) {
				return window.getDefaultComputedStyle(q.o).getPropertyValue(n) != d;

			} : function (n, d) {
				var domStyle = igk.css.getDomStyle();
				return (d != domStyle[n]);
			};
			for (var i in prop) {
				v = i;
				if ((d = this.getComputedStyle(i)) && check(i, d)) //o.style[i];
				{
					o += v + ":" + d + "; ";
				}
			}
			return o;
		},
		disablecontextmenu: function () {
			if (this.isSr()) {
				this.o.each(this.editable, arguments);
				return this;
			}
			else {
				this.on("contextmenu", igk.winui.event.cancelBehaviour);
			}
			return this;
		},
		selectable: function (v) {
			if (this.isSr()) {
				this.o.each(this.selectable, arguments);
				return this;
			}
			else {
				if (!v) {
					igk.ctrl.selectionmanagement.disable_selection(this);
				} else {
					igk.ctrl.selectionmanagement.enableSelection(this);
				}
			}
			return this;
		},
		editable: function (v) {
			if (this.isSr()) {
				this.o.each(this.editable, arguments);
				return this;
			}
			else {
				this.o.contentEditable = v;
			}
			return this;
		},
		autocorrect: function (v) {
			if (this.isSr()) {
				this.o.each(this.autocorrect, arguments);
				return this;
			}
			else {
				this.o.setAttribute("spellcheck", v);

			}
			return this;
		},
		setCss: function (properties) {
			if (this.isSr()) {
				this.o.each(this.setCss, arguments);
				return this;
			}
			else {
				igk.css.setProperties(this.o, properties);

			}
			return this;
		},
		height: function () {
			var h = this.getComputedStyle("height");
			return igk.getNumber(h, this.o, "height");
		},
		width: function () {
			var h = this.getComputedStyle("width");
			return igk.getNumber(h, this.o, "width");
		},
		setCssAssert: function (c, p)// conditional css property
		{
			if (c) {
				this.setCss(p);
			}
			return this;
		},
		setProperties: function (properties) {
			if (!properties) return this;
			if (this.isSr()) {

				this.o.each(this.setProperties, arguments);
			}
			else {
				for (var i in properties) {
					try {
						this.o[i] = properties[i];
					}
					catch (Ex) {
						console.debug("can't  set property " + i);
					}
				}
			}
			return this;
		},
		hide: function () {
			this.setCss({ "display": "none" });
		},
		show: function () {
			this.setCss({ "display": "inline-block" });
		},
		fadein: function (interval, duration, opacityOrProperties, callback) {
			window.igk.animation.fadein(this.o, interval, duration, opacityOrProperties, callback);
			return this;
		},
		fadeout: function (interval, duration, opacityOrProperties, callback) {
			window.igk.animation.fadeout(this.o, interval, duration, opacityOrProperties, callback);
			return this;
		},
		getoffsetParent: function () {
			if (this.isSr()) {
				return this;
			}
			return $igk(igk.winui.GetRealOffsetParent(this.o));
		},
		getscrollParent: function () {
			if (this.isSr()) {
				return this;
			}
			return $igk(igk.winui.GetRealScrollParent(this.o));
		},
		appendProperties: function (properties, override) {
			if (!properties)
				return this;
			if (this.isSr()) {
				this.each(this.appendProperties, arguments);
				return this;
			}
			else {
				igk.appendProperties(this, properties, override);
			}
			return this;
		},
		// -------------------------------------------------------------
		// events
		// -------------------------------------------------------------
		dispatchEvent: function (evt) {
			if (this.o.dispatchEvent) {
				this.o.dispatchEvent(evt);
			}
			else {
				if (document.dispatchEvent) {
					document.dispatchEvent(evt);
				}
			}
		},
		raiseEvent: function (n, p, callback) {
			// raise custom event
			// n: event name
			// p: properties
			if (!this.isSr()) {
				var e = this.o[n];
				if (e != null) {

					// ei make some error
					if (arguments.length > 1) {
						igk.appendProperties(e, arguments[1]);
					}
					try {
						if (this.o.dispatchEvent) {
							this.o.dispatchEvent(e);
						}
						else {
							if (document.dispatchEvent)
								document.dispatchEvent(e);
							else {
								document.documentElement[n]++;
							}
						}
					}
					catch (ex) {
						console.debug(n + " " + ex.message + " ");
					}
				}
				if (callback){
					callback(e);
				}
			}
			return this;
		},
		addEvent: function (n, p){
			// add custom event to property
			// n: name
			// p: propertie for the event
			var q = this;
			if (!this.isSr()) {
				var e = null;
				if (typeof (Event) == IGK_FUNC) { // check if window object event exists
					var _init = {};
					if (p.cancelable){
						_init.cancelable = true;
					}
					if (p.composed){
						_init.composed = true;
					}
					if (p.bubbles){
						_init.bubbles = true;
					}
					e = new Event(n, _init); 
				}
				else {
					if (document.createEvent) {
						e = document.createEvent('CustomEvent');
						// forget this will raise UnpsecifiedEventTypeError in ie7
						e.initCustomEvent(n, true, false, {});
						igk_appendProp(e, p);
						igk_appendProp(e, { fordoc: true });
					}
					else {
						// this will raise UnpsecifiedEventTypeError in ie8 not supporting createEvent Method

						// alert(" disp "+document.dispatchEvent);
						// e= document.createEventObject(window.event); // document.createEventObject(q.o);
						// igk_appendProp(e,p);
						// igk_appendProp(e,{fordoc:true});

						e = new __igk_event(q, p, n);

					}
				}
				if (e != null) {
					try {
						this.o[n] = e;
						this.raiseEventCreated(n);
					}
					catch (e) { 
						// failed to add property 
					}
				}
			}
			return this;
		},
		getParentBody: function () {
			if (this.isSr()) { return null; }

			var q = this.o;
			while (q && (q.tagName.toLowerCase() != "body")) {
				q = q.parentNode;
			}
			return q;
		},
		// class
		addClass: function (classname) {

			function __appendClass(t, tab) {
				var ch = 0;
				var s = "";
				var k = "";
				var st = {};
				if (t.o.className && !t.o.className.indexOf) {
					// class name is not a string
					// svg case 
					console.error("object.className is not a string (probably svg case) : " + t.o.className);
					return ch;
				}
				for (var i = 0; i < tab.length; i++) {
					k = igk.system.string.trim(tab[i]);
					if (st[k]) {
						continue;
					}
					st[k] = 1;

					if ((k.lenth == 0) || (t.o.className && (t.o.className.indexOf(k) !== -1)))
						continue;
					if (s.length > 0)
						s += " ";
					s += k;
					ch = !0;
				}
				if (s.length > 0) {
					if (t.o.className == " ")
						t.o.className = igk.system.string.trim(ts);
					else {
						t.o.className = igk.system.string.trim(t.o.className + " " + s);
					}
				}
				return ch;

			};
			function __rmClass(t, tab) {
				var g = t.o.className.split(' ');
				var ch = 0;
				var s = "";
				for (var i = 0; i < g.length; i++) {
					if (g[i] in tab) {
						ch = 1;
					} else {
						if (s.length > 0)
							s += " ";
						s += g[i];
					}
				}
				t.o.className = s;
				return ch;
			};

			if (this.isSr()) {
				this.o.each(this.addClass, arguments);
				return this;
			}
			else {
				var tab = [];
				if ((typeof (classname) === "string") && (classname.length > 0)) {
					var ch = false;
					if (typeof (this.o.className) == IGK_UNDEF) {
						this.o.className = classname;
						ch = true;
					}
					else {
						tab = classname.split(" ");
						ch = __appendClass(this, tab);
					}


				} else {
					var rm = [];
					var _bc = this.o.className;

					for (var i in classname) {
						var s = igk.system.string.trim(i);
						if (((typeof (classname[i]) == "function") && classname[i]()) ||
							classname[i]) {
							tab.push(s);
						} else {
							var s = igk.system.string.trim(i);
							if (s.indexOf(s) !== -1) {
								rm.push(s);
								rm[s] = s;
							}
						}
					}
					ch = __appendClass(this, tab) || __rmClass(this, rm);

				}

				if (ch) {
					igk.publisher.publish("sys://dom/classchanged", { target: this, className: classname });
				}
			}
			return this;
		},

		// remove class 
		rmClass: function (classname) {
			if (typeof classname === "string") {
				classname = igk.system.string.trim(classname);
				if (classname.length == 0) {
					return this;
				}
				if (this.isSr()) {
					this.o.each(this.rmClass, arguments);
					return this;
				}
				else {
					var tab = classname.split(" ");
					var cur = this.o.className.split(" ");
					var rms = "";
					var index = 0;
					var s = "";
					var removing = false;
					if (cur) {
						for (var i = 0; i < tab.length; i++) {
							s = tab[i];
							//if(s == classname){

							index = cur.indexOf(s);
							if (index != -1) {
								delete (cur[index]);
							}
							// while ((index >= 0) && ((index + s.length) <= cur.length)) {
							// cur = cur.replace(tab[i], "");
							// index = cur.indexOf(tab[i]);
							// removing = !0;
							// }								
							//}
						}
						var h = igk.system.string.trim(cur.join(" "));
						this.o.className = h;
					}
				}
			}
			return this;
		},
		rmAllClass: function (rg) { // remove all class that match the pattern. space is used as separator
			if (typeof rg === "string") {
				if (this.isSr()) {
					this.o.each(this.rmClass, arguments);
					return this;
				}
				else {
					var cur = this.o.className;
					var tab;
					var _rg;
					if (cur) {
						tab = cur.split(" ");
						_rg = new RegExp(rg);
						var o = "";
						for (var i = 0; i < tab.length; i++) {
							if (_rg.test(tab[i])) {
								continue;
							}
							o += tab[i] + " ";
							// cur = cur.replace(_rg, '');		

						}
						cur = o;
						var h = igk.system.string.trim(cur);
						this.o.className = h;
						igk.success = 1;
					} else
						igk.success = 0;
				}
			}
			return this;
		},

		// replace class
		rpClass: function (oldcl, newcl) {

			if (typeof oldcl === "string") {
				if (this.isSr()) {
					this.o.each(this.rpClass, arguments);
					return this;
				}
				else {
					var tab = oldcl.split(" ");
					var cur = this.o.className;
					var rms = "";
					var index = 0;
					var s = "";
					var removing = false;
					if (cur) {
						for (var i = 0; i < tab.length; i++) {
							s = tab[i];
							index = cur.indexOf(s);
							while ((index >= 0) && ((index + s.length) <= cur.length)) {
								cur = cur.replace(tab[i], "");
								index = cur.indexOf(tab[i]);
								removing = !0;
							}
						}
						var h = igk.system.string.trim(cur);
						if (newcl)
							h += " " + newcl;
						this.o.className = h;
					}
				}
			}
			return this;
		},
		// determine 
		supportClass: function (classname) {
			if (this.isSr()) {
				return !1;
			}
			var cl = this.o.className;
			var exp = "($|[\\s]?)(" + classname + "){1}($|[\\s]+)";
			var rg = new RegExp(exp, "g");
			if (rg.test(cl)) {
				return !0;
			}
			return !1;
		},
		toggleClass: function (cl) {
			if (this.isSr()) {
				return !1;
			}
			else {
				if (this.supportClass(cl)) {
					this.rmClass(cl);
				} else {
					this.addClass(cl);
				}
			}
			return this;
		},
		qselect: function (pattern) {
			if (this.isSr()) {
				return this.o.qselect(pattern);
			}
			// select with global 
			return igk.qselect(this.o, pattern);
		},


		select: function (pattern) { // igk node utility selector
			// select with pattern					
			if (this.isSr()) {
				return this.o.select(pattern);
			}
			// select with global 
			return igk.select(this.o, pattern);
		},
		wait: function (t, func) {
			// wait on the function
			// if (return 1: finish ) if return 0: continue to wait
			var q = this;
			function _wait_callback() {
				if (!func.apply(q)) {
					setTimeout(_wait_callback, t);
				}
			}
			setTimeout(_wait_callback, t);
		},
		getForm: function () {

			if (this.o.form)
				return this.o.form;
			return igk.getParentByTagName(this.o, "form");

		},
		getAttributeCount: function () {
			if (this.o.attributes)
				return this.o.attributes.length;
			return 0;
		},
		replaceOwner: function (d) {

			if (d) {
				m_o = d;
			}
		},
		replaceTagWith: function (nTagName) {// replace tag name
			if (this.isSr()) {
				this.o.each(this.replaceTagWith, arguments)
			}
			else {
				var d = igk.createNode(nTagName);
				d.setHtml(this.o.innerHTML);
				igk.dom.copyAttributes(this.o, d.o);
				igk.dom.replaceChild(this.o, d.o);
				this.replaceOwner(d.o);
			}
			return this;
		},
		view: function () {
			// make this element visible by scrolling to it
			if (this.isSr()) {
				return this;
			}
			var p = this.getscrollParent();
			if (p) {
				if (p.o.scroll)
					p.o.scroll(this.o.offsetLeft, this.o.offsetTop);
			}
			return this;
		},
		scroll: function (x, y) {
			// change scroll button
			this.o.scrollLeft = x;
			this.o.scrollTop = y;
			if (this.o.scrollLeft != x) {
				console.error("/!\\ scroll x not changed: " + this.o.scrollLeft + "=>" + x);
			}
			if (this.o.scrollTop != y) {
				console.error("/!\\ scroll y not changed: " + this.o.scrollTop + "=>" + y);
			}
		},
		animate: function (propertyToAnimate, animationProperty) {// used to animate this element
			if (this.isSr()) {
				this.each(this.animate, arguments);
			}
			else {
				igk.animation.animate(this.o, propertyToAnimate, animationProperty);
			}
			return this;
		},
		animateUpdate: function (animationProperty) {
			igk.animation.animateUpdate(this.o, animationProperty);
			return this;
		},
		scrollTo: function (t, property, callback) {
			// igk[object].scrollTo:
			// >t : la cible contenue par cet élément
			// >property is an object { interval: @time ,duration: @time,speed : @speding,orientation : @orientation}
			if ((t == null) || typeof (t) != 'object')
				return;
			if ((property == null) || (typeof (property) == IGK_UNDEF)) {
				property = {
					duration: 500,
					interval: 20,
					effect: "linear",
					effectmode: "easeinout"
				};
			}

			var ts = igk.fn.isItemStyleSupport(this.o, "transition");
			var trf = igk.fn.isItemStyleSupport(this.o, "transform");
			var it = $igk(t);
			var self = this;
			var counter = 0;
			// item list
			var m_il = new igk.system.collections.list();
			var scrollprop = null;
			var m_duration_ms = property ? igk.datetime.timeToMs(property.duration) : null;
			var mout = null;// count time out

			function __init_scroll() {// on 		
				var offsetParent = it.getscrollParent();
				if (offsetParent == null) {

					return null;
				}
				return new (function () {
					var q = this;
					var orientation = "vertical";
					var distance = 0;
					var d = 0;
					var o = self.o;
					var v_loc = self.getLocation(); // parent location
					var startpos = 0, endpos = 0, endscroll = 0;
					var pos = 0;
					v_loc.y += self.o.scrollTop;
					v_loc.x += self.o.scrollLeft;

					var v_ttloc = it.getLocation();
					v_ttloc.y += it.o.scrollTop;
					v_ttloc.x += it.o.scrollLeft;
					var v_tloc = it.getscrollLocation(offsetParent.o);

					// fixed data
					// if((v_loc.y==v_tloc.y) &&(v_loc.x==v_tloc.x))
					// {
					// don't translate							
					// return;
					// }
					if (!property.orientation) {
						// auto detect orientation 
						var pp = it.getOffsetScreenLocation(self);

						orientation = pp.orientation;
					}
					else {
						orientation = property.orientation ? property.orientation : "vertical";
					}
					if (orientation == "vertical") {
						startpos = v_loc.y;// q.scrollTop;							
						endscroll = v_tloc.y; // target.offsetTop; // (target.offsetTop + target.offsetHeight) - q.clientHeight; // (this.getOwner().o.clientHeight);
						d = endscroll - startpos;// -0;// - v_loc.y;						
						q.dir = (d > 0) ? "godown" : "goup";
					}
					else {

						startpos = v_loc.x;// q.scrollLeft;
						endscroll = v_tloc.x; // target.offsetLeft;// + target.offsetWidth) - q.clientWidth;// + q.scrollLeft;							
						d = endscroll - startpos;// - 0;// v_loc.x;
						q.dir = (d > 0) ? "goright" : "goleft";


					}
					// calculate the normal step	
					distance = Math.abs(d);
					var sign = d >= 0 ? 1 : -1;
					q.distance = distance;
					q.sign = sign;
					q.target = t;
					igk.appendProperties(this, {
						getTransform: function () {
							if (orientation == "vertical")
								return "TranslateY(" + (-sign * distance) + "px)";
							return "TranslateX(" + (-sign * distance) + "px)";
						},
						getStartTransform: function () {
							if (orientation == "vertical")
								return "TranslateY(" + startpos + "px)";
							return "TranslateX(" + startpos + "px)";
						},
						resetTransform: function () {
							if (orientation == "vertical")
								return "TranslateY(0px)";
							return "TranslateX(0px)";
						},
						getStartScroll: function () {
							return startpos;
						},
						X: function () {
							if (orientation == "horizontal")
								return startpos + (sign * distance);
							return 0;
						},
						Y: function () {
							if (orientation == "vertical")
								return startpos + (sign * distance);
							return 0;
						}
					});

				})();
			};
			function __clearTransition() {
				self.select('>>').each_all(function () {
					if (!this.o.nodeType || (this.o.nodeType != 1) || (this.o.tagName.toLowerCase() == 'script'))
						return;
					if (!this.o.style ||
						(property.filter && property.filter(this))) {
						return;
					}
					this.rmClass("igk-trans-all-200ms")
						.setCss({
							transform: scrollprop.resetTransform(),
							transitionDuration: null
						});
				});
				var pp = scrollprop;

				self.scroll(pp.X(), pp.Y());
				// self.setCss({"transform":"translate("+pp.X()+"px, "+pp.Y()+"px)"});

				if (callback) {
					callback({ "type": "transition", target: t, x: pp.X(), y: pp.Y() });
				}
			}

			function __transition_end(evt) {
				var g = $igk(igk.winui.eventTarget(evt));
				m_il.remove(g);
				counter--;
				if (counter <= 0) {
					__clearTransition();
				} else {
					if (mout)
						clearTimeout(mout);
					mout = setTimeout(function () {
						if (counter > 0) {
							__clearTransition();
							counter = 0;
						}
					}, 500);
				}
				igk.winui.unreg_event(this, 'transitionend', __transition_end);
			};
			if (ts && trf) {// item support animation and transitions

				scrollprop = __init_scroll();
				counter = 0;
				if (scrollprop && scrollprop.distance != 0) {
					if (!scrollprop.getTransform) {

						throw ("[IGK] no scrollprop.getTransform function found");
						return;
					}
					counter = 0;
					self.select('>>')
						.each_all(function () {
							// increment counter if this content style
							// check if this contain style property and is filtered
							if (!this.o.nodeType || (this.o.nodeType != 1) || (this.o.tagName.toLowerCase() == 'script'))
								return;
							if (!this.o.style || (property.filter && property.filter(this))) {
								return;
							}
							// if(this.getComputedStyle("display")=='none'){
							// no display does't recieve thransition end. non visible item . 
							// return;
							// }


							counter++;
							m_il.add(this);
							this.reg_event("transitionend", __transition_end);
							this.addClass("igk-trans-all-200ms")
								.setCss({
									"transform": scrollprop.getTransform()
								});
						});

				}
				else {
					if (callback)
						callback();
				}
				// end transition end properties
				return;
			}

			if (m_anim && m_anim.scrolling) {
				// stop scrolling of this item
				m_anim.scrolling.stop();
			}

			if (property == null) {
				target.scrollIntoView();
				if (callback != null)
					callback({ "type": "scrollIntoView" });
				return !0;
			}

			var anim1 = igk.animation.init(
				this,
				property.interval,
				property.duration,
				function () { // init
					// store animation context
					var self = this;
					self.offsetParent = $igk(t).getscrollParent();
					if (self.offsetParent == null)
						return;
					var d = 0;
					var q = self.getOwner().o;
					var v_loc = self.getOwner().getLocation(); // parent location
					v_loc.y += q.scrollTop;
					v_loc.x += q.scrollLeft;

					var v_tloc = $igk(t).getscrollLocation(self.offsetParent.o);

					self.speed = property.speed ? property.speed : 0.05;
					if (!property.orientation) {
						// auto detect orientation 
						if (v_loc.y == v_tloc.y)
							self.orientation = "horizontal";
						else
							self.orientation = "vertical";
					}
					else {
						self.orientation = property.orientation ? property.orientation : "vertical";
					}
					self.pos = 0;

					if (self.orientation == "vertical") {

						self.startpos = v_loc.y;// q.scrollTop;							
						self.endscroll = v_tloc.y; // target.offsetTop; // (target.offsetTop + target.offsetHeight) - q.clientHeight; // (this.getOwner().o.clientHeight);
						d = self.endscroll - self.startpos;// -0;// - v_loc.y;						
						self.dir = (d > 0) ? "godown" : "goup";
					}
					else {

						self.startpos = q.scrollLeft;
						self.endscroll = v_tloc.x; // target.offsetLeft;// + target.offsetWidth) - q.clientWidth;// + q.scrollLeft;							
						d = self.endscroll - self.startpos;// - 0;// v_loc.x;
						self.dir = (d > 0) ? "goright" : "goleft";
					}
					// calculate the normal step						
					self.distance = Math.abs(d);
				},
				function () { // update 
					var d = this.distance;
					if (d == 0) {
						return !0;
					}

					var v_o = this.offsetParent != null ? this.offsetParent : this.getOwner();
					var y = 0;
					var end = false;
					var f = this.getStepfactor();
					var sign = (this.dir == "goleft") || (this.dir == "goup") ? -1 : 1;
					y = this.startpos + (sign * f * d);// ( this.step * 2 * Math.sin(Math.PI * this.getEllapsed()/this.getDuration())));	


					this.pos = y;// Math.min(this.startpos +d ,y);		

					end = (this.getEllapsed() / this.getDuration()) >= 1.0;
					// (y- this.startpos)==d);// d( this.pos - d);(this.pos >=this.startpos +d);							

					switch (this.dir) {
						case "godown":
							v_o.scroll(0, y);// this.startpos + this.pos); 							
							break;
						case "goup":
							v_o.scroll(0, y);// this.startpos - this.pos); 							
							break;
						case "goright":

							v_o.scroll(y, 0);// (this.startpos + this.pos,0); 
							break;
						case "goleft":
							v_o.scroll(y, 0);// this.startpos - this.pos,0); 
							break;
						default:
							return !1;
					}
					return !end;
				},
				function () { // end
					var v_o = this.getOwner();
					if (m_anim && m_anim.__anim && m_anim.__anim.scrolling)
						delete m_anim.__anim.scrolling;
					if (callback)
						callback({ "type": "igk.animation" });
					// delete m_anim;						
				}
			);
			m_anim = createPNS(this, "__anim", { "scrolling": anim1 });
			m_anim.type = "scrolling";
			anim1.properties = property;
			anim1.start();
			return !0;
		},
		// return the first matching parent with tag name
		getParentByTagName: function (tag) { return igk_getParentByTagName(this.o, tag); },
		// return the first parent by id
		getParentById: function (id) { return igk_getParentById(this.o, id); },
		// T: function,remove element
		remove: function () {
			if (this.isSr()) {
				this.o.each(this.remove, arguments);
				// return this;
			}
			else {
				if (this.o.parentNode)
					this.o.parentNode.removeChild(this.o);
				// return !0;
			}
			return this;
		},
		transEnd: function (n, t) {
			var q = this;

			function __function_end(evt) {


				if (evt.target == q.o) {

					if (typeof (n) == "function")
						n.apply(q);
					else {
						if (n in q) {
							q[n].apply(q);
						}
					}
					q.unreg_event("transitionend", __function_end);
				}
			};

			if (this.isSr()) {
				this.o.each(q.transEnd, arguments);
			}
			else {

				this.reg_event("transitionend", __function_end);
			}
			return q;
		}
		, loadStringAsHtml: function (s, evalscript) {
			if (this.o.each) {
				this.o.each(this.loadStringAsHtml, arguments);
			}
			else {
				if (typeof (this.o.appendChild) != "undefined") {
					var m = igk.createNode("dummyNode");
					m.setHtml(s);
					if (m.o.childNodes.length == 1) {
						var p = m.o.childNodes[0];
						var c = p.childNodes.length;
						this.o.innerHTML = "";
						while (c > 0) {
							this.o.appendChild(p.childNodes[0]);
							c--;
						}
					}
				}
				if (evalscript)
					igk.system.evalScript(this.o);
			}
			return this;
		}
		, istouchable: function () {// utility

			if (
				(typeof (this.o.ontouchstart) != IGK_UNDEF) &&
				(typeof (this.o.ontouchend) != IGK_UNDEF) &&
				(typeof (this.o.ontouchmove) != IGK_UNDEF) &&
				(typeof (this.o.ontouchcancel) != IGK_UNDEF)
			)
				return !0;
			return !1;
		}
		// checking css properties 

		, isCssSupportTransition: function () {
			return igk.fn.isItemSupport(this.o.style, ['transition', 'webkitTransition', 'msTransition', 'oTransition']);
		},
		isCssSupportAnimation: function () {
			return igk.fn.isItemSupport(this.o.style, ['animation', 'webkitAnimation', 'msAnimation', 'oTransition']); // igk_checkOnePropertyExists(this.o.style,"animation");
		},
		setTransitionDelay: function (time) {
			if (this.o.each) {
				this.o.each(this.setTransitionDelay, arguments);
			} else
				igk.css.setTransitionDelay(this, time);
			return this;
		},
		setTransition: function (k) {
			if (this.o.each) {
				this.o.each(this.setTransition, arguments);
			}
			else
				this.setCss({ transition: k });
			return this;
		},
		setTransitionDuration: function (time) {
			if (this.o.each) {
				this.o.each(this.setTransitionDuration, arguments);
			} else
				igk.css.setTransitionDuration(this, time);
			return this;
		}
		, replaceWith: function (t) {
			// t:target node or text
			// desc: replace content of the current nodes list by t. t 
			if (this.o.each) {
				this.o.each(this.replaceWith, arguments);
			}
			else {
				this.clearAttributes();
				igk.dom.copyAttributes(this.o, $igk(t).o);
				this.setHtml($igk(t).getHtml());
			}
			return this;
		}
		, replaceBy: function (t) {
			if (this.o.each) {
				this.o.each(this.replaceBy, arguments);
			}
			else {
				var p = this.o.parentNode;
				if (p) {
					this.insertBefore($igk(t).o);
					this.remove();
				}
			}
			return this;
		}
		// ,
		// replaceBy: function (i) {
		// if (igk_is_string(i)) {
		// var d = igk.createNode("dummy");
		// i = d.setHtml(i).o.firstChild;
		// }
		// var v_si = $igk(i);
		// if (!this.isSr()) {
		// var p = this.o.parentNode;
		// p.replaceChild(v_si.o, this.o);
		// } else {
		// if (this.getCount() > 1) {
		// var c = 0;
		// this.o.each(function () {
		// if (c == 0) {
		// this.replaceBy(i);
		// }
		// else {
		// this.replaceBy($igk(i).clone());
		// }
		// c = 1;
		// return !0;
		// });
		// }
		// else
		// this.o.each(this.replaceBy, arguments);
		// }
		// return this;
		// }
		, clearAttributes: function () {
			while (this.o.attributes.length > 0) {
				this.o.removeAttribute(this.o.attributes[0].name);
			}
			return this;
		}
		, text: function () {
			if (this.o.each) {
				this.o.each(this.text, arguments);
			} else {
				if ("textContent" in this.o)
					return this.o.textContent;
				return ("data" in this.o) ? this.o.data : null
			}
		}
		, setHtml: function (v, evalScript) {// set innerHTML  @v:text context ,@eval:bool true to evaluate . default is false
			if (this.o.each) {
				this.o.each(this.setHtml, arguments);
			}
			else {
				try {

					this.o.innerHTML = v;
					if (evalScript) {
						igk.system.evalScript(this.o);
					}
				}
				catch (ex) {
					console.debug("set:InnerHtml failed : " + v + "\n" + ex);
				}
			}
			return this;
		},
		getText: function () {
			var s = "";
			if (this.o.innerHTML) {
				s = igk.html.string(this.getHtml());
			}
			return s;
		},
		val: function () {
			if (this.o.each) {
				this.o.each(this.setHtml, arguments);
			}
			else {
				if (arguments.length > 0) {
					if ('value' in this.o) {
						this.o.value = arguments[0];
					}
				}
				return this.o.value;
			}
			return this;
		},
		// get htmlcontent retreive all content
		getHtml: function () {
			var s = "";

			if (this.o.each) {
				var fc = this;
				var args = arguments;
				this.o.each_all(function () {
					s += fc.getHtml.apply(this, args);
				});
			}
			else {
				return ('innerHTML' in this.o) ? this.o.innerHTML : __dom_innerHTML(this.o);
			}
			return s;
		},
		getOuterHtml: function () {
			if (this.o.outerHTML)
				return this.o.outerHTML;

			var s = "";
			if (this.o.each) {
				s += this.o.each(this.getOuterHtml, arguments);
			}
			else {
				if (typeof (this.o.tagName) != IGK_UNDEF) {
					s = "<" + this.o.tagName;
					if (this.o.hasAttributes) {
						s += " ";
						for (var i = 0; i < this.o.attributes.length; i++) {
							var g = this.o.attributes[i];
							if (i != 0)
								s += " ";
							s += g.name + "=\"" + g.value + "\"";
						}
					}
					s += ">";
					for (var i = 0; i < this.o.childNodes.length; i++) {
						s += $igk(this.o.childNodes[i]).getOuterHtml();
					}
					// s+=this.o.innerHTML;
					s += "</" + this.o.tagName + ">";
				}
				else {
					s += this.o.wholeText;
				}
			}
			return s;
		},
		evalScript: function () {
			if (this.o.each) {
				this.o.each(this.evalScript, arguments);
			}
			else {
				igk.system.evalScript(this.o);
			}
			return this;
		},
		init: function () {// init the current node
			if (this.o.each) {
				this.o.each(this.init, arguments);
			} else
				igk.ajx.fn.initnode(this.o);
			return this;
		},
		render: function () {
			var s = "";
			if (this.o.each) {
				s += this.o.each(this.renber, arguments);
			}
			else {
				return this.o.outerHTML;
			}
			return s;
		}
		, getAllAttribs: function () {// return a string of all attribute of this node				
			var msg = "";
			var attrs = null;
			if (this.o.attributes) {
				attrs = this.o.attributes;
				var ln = attrs.length;
				for (var h = 0; h < ln; h++) {
					try {
						var j = attrs[h];
						if (j)
							msg += j.name + " ";
					}
					catch (ex) {
						console.debug("error on " + h);
					}
				}
			}
			return msg;
		}
		, getParentNode: function () { return $igk(this.o.parentNode); },
		getStyle: function (name) {
			if (!this.isSr()) {
				return igk.css.getValue(this.o, name);
			}
			return "no-value";
		}
		, getComputedStyle: function (n, select) {
			//@n property to get
			//@select* : speudo class selector
			if (!this.isSr()) {
				if (window.getComputedStyle) {
					// get styles
					if (this.o.nodeType && (this.o.nodeType == 3)) {
						return null;
					}
					var q = window.getComputedStyle(this.o, select);
					return igk.css.getStyleValue(q, n);
				}

				return null;//"no-value-computed"; 
			}
			return "no-value";
		}, getComputedStylePropertyValue: function (n, select) {
			//@n property to get
			//@select* : speudo class selector
			if (!this.isSr()) {
				var s = null;
				if (window.getComputedStyle && (s = window.getComputedStyle(this.o, select))) {
					// get styles
					var q = window.getComputedStyle(this.o, select).getPropertyValue(n);
					return q;
				}

				return null;//"no-value-computed"; 
			}
			return "no-value";
		}
		, setAttributes: function (properties) { if (!properties) return; for (var i in properties) { this.o.setAttribute(i, properties[i]); } return this; }
		// set attributre set attribute as string value that will be interpreted
		, setAttribute: function (name, value) { if (this.o.each) { this.o.each(this.setAttribute, arguments); } else this.o.setAttribute(name, value); return this; }
		, setAttributeAssert: function (condition, name, value) {
			if (condition) {
				this.setAttribute(name, value);
			}
			return this;
		},
		setSize: function (w, h) { if (h == null) h = w; this.setCss({ "width": w, "height": h }); return this; }
		// -------------- GET PROPERTIES
		, getAttribute: function (value) {
			if (typeof (this.o.getAttribute) == 'function')
				return this.o.getAttribute(value);
			return null;
		}
		, getChildById: function (id) { return igk_getChildById(this.o, id); }
		, getChildsByAttr: function (properties) { return igk_getChildsByAttr(this.o, properties); }
		, getParent: function (tagname) { if (tagname) { return this.getParentByTagName(tagname); } return $igk(this.o.parentNode); }
		, getParentForm: function () { return this.getParentByTagName("form"); }
		, getPixel: function (propName, o) {// return a pixel value
			return igk.getPixel(this.getComputedStyle(propName), o || this.o, propName);
		},
		appendChild: function (name) { var item = null; if (typeof (name) == "string") { item = document.getElementById(name); } else { item = name; } if (item != null) { var s = $igk(item); this.o.appendChild(s.o); return s; } }
		, appendNChild: function (tagname) { var item = this.createElement(tagname); this.appendChild(item); return item; }

		, insertBefore: function (item) {
			// this.o.insertBefore(i,j); 
			// @ item to add
			var p = 0;
			if (item && (p = this.o.parentNode)) {
				if (p.firstChild == this.o) {
					if (p.prepend)
						p.prepend(item);
					else {
						p.insertBefore(item, p.firstChild);
					}
				}
				else
					p.insertBefore(item, this.o);
			}
			return this;

		}, insertAfter: function (item) {// insert item after the node
			if (item && this.o.parentNode) {
				if (this.o.parentNode.lastChild == this.o)
					this.o.parentNode.appendChild(item);
				else
					this.o.parentNode.insertBefore(item, this.o.nextSibling);
			}
			return this;
		}


		, firstChild: function () { return (this.o.firstChild) ? $igk(this.o.firstChild) : null; }
		, firstNode: function (type) {
			// get the first node element type math the requirement
			var r = null;
			this.select('>>').each(function () {
				if (this.o.nodeType == type) {
					r = this.o;
					// cancel
					return !1;
				}
				return !0;
			});
			return r;
		}
		, prependChild: function (node) { if (this.o.firstChild) this.o.insertBefore(node, this.o.firstChild); else { this.o.appendChild(node); } }
		, reg_event: function (method, func, opts) {

			if (this.o.each) {
				this.o.each(this.reg_event, arguments);
			}
			else {
				igk.winui.reg_event(this.o, method, func, opts);
			}
			return this;
		}
		, unreg_event: function (method, func) {
			if (this.o.each) {
				this.o.each(this.unreg_event, arguments);
			}
			else {
				igk.winui.unreg_event(this.o, method, func);
			}
			return this;
		},
		getOffsetScreenLocation: function (t) {// return the point 
			var v1 = t.getScreenLocation();
			var v2 = { x: this.o.offsetLeft, y: this.o.offsetTop };

			var q = this.o.offsetParent;
			var v3 = { x: 0, y: 0 };
			var v4 = this.getScreenLocation();
			var p = this.o;
			var o = "horizontal";
			while (q && (q != igk.dom.body().o)) {
				if (q == t.o) {



					if (t.o.offsetWidth < v2.x) {

					}
					if (((v4.x == 0) && (v4.y != 0)) || (t.o.offsetHeight < v2.y)) {
						o = "vertical";
					}
					break;
				}
				p = q;
				q = q.offsetParent;
				v2.x += p.offsetLeft;
				v2.y += p.offsetTop;

				v4.x -= p.offsetLeft;
				v4.y -= p.offsetTop;

			}
			return { orientation: o, x: v4.x, y: v4.y };
		}
	});


	__prop.getElementsByTagName = function (tag) { if (this.o.getElementsByTagName) return this.o.getElementsByTagName(tag); };
	// return the client width
	__prop.getWidth = function () { return this.o.clientWidth; };
	__prop.getglobalWidth = function () { return this.o.clientWidth + this.o.scrollWidth; };
	__prop.getglobalHeight = function () { return this.o.cliclientHeight + this.o.scrollHeight; };
	// return the client height
	__prop.getHeight = function () { return this.o.clientHeight; };
	__prop.getTop = function () { return this.getLocation().y; };
	__prop.getLeft = function () { return this.getLocation().x; };
	__prop.getSize = function () { return { w: this.getWidth(), h: this.getHeight(), toString: function () { return "w:" + this.w + " h:" + this.h; } }; };
	// return the location of the host in global display
	__prop.getLocation = function () { return igk.winui.GetScreenPosition(this.o); };
	// return the location of the host in client screen display
	__prop.getScreenLocation = function () { return igk.winui.GetRealScreenPosition(this.o); };
	__prop.getOffsetBounds = function () {
		var p = this;
		var real_size = { x: 0, y: 0, h: 0, w: 0 };
		for (var n = 0; n < p.o.childNodes.length; n++) {
			var c = $igk(p.o.childNodes[n]);
			var loc = c.getScreenLocation();
			var boc = c.getScreenBounds();
			real_size.x = Math.min(loc.x, real_size.x);
			real_size.y = Math.min(loc.y, real_size.y);
			real_size.w = Math.max(loc.x + boc.w, real_size.w);
			real_size.h = Math.max(loc.y + boc.h, real_size.h);
			// real_size.x = Math.min(loc.x, real_size.x);

		}
		loc = p.getScreenLocation();
		real_size.w -= loc.x;
		real_size.h -= loc.y;
		return real_size;
	};
	// get the bounding visibility in client screen display
	__prop.getBoundingClientRect = function () {
		// shortcut function
		var g = this.o.getBoundingClientRect ? this.o.getBoundingClientRect() : {
			x: 0, y: 0, width: 0, height: 0, left: 0, top: 0, right: 0, bottom: 0, toString: function () {
				"igk.boundingClientRect[]"
			}
		};
		// some browser don't implement x and y properties
		if (!g.x) g.x = g.left;
		if (!g.y) g.y = g.top;


		return g;
	};
	// get the screen bounding item
	__prop.getScreenBounds = function () {
		var l = igk.winui.GetRealScreenPosition(this.o);
		var s = this.getSize();
		return {
			x: l.x, y: l.y, w: s.w, h: s.h,
			contains: function (x, y) {
				return (l.x <= x) && ((l.x + s.w) >= x) &&
					(l.y <= y) && ((l.x + s.h) >= y);
			},
			toString: function () { return "getScreenBounds[" + igk.system.stringProperties(this) + "]"; }
		};
	};
	// return true is item is visible in screen display
	__prop.getisVisible = function () {
		var j = this;
		var loc = j.getBoundingClientRect();
		// j.o.getBoundingClientRect ? j.o.getBoundingClientRect(): 
		// {x:0,y:0};
		// {x:j.getscrollLeft(), y:j.getscrollTop()};
		// getLocation();
		var size = igk.winui.screenSize();
		// get screen visibility
		var vsb = ((loc.x >= 0) && (loc.x <= size.width) && (loc.y >= 0) && (loc.y <= size.height));
		return vsb;

	};

	__prop.getpresentOnDocument = function (doc) {
		// > get if this is present on document
		var j = this.o;
		var _doc = doc || document;
		while (j && (j != _doc)) {
			j = j.parentNode;
		}
		return (j != null);
	};
	__prop.getscrollLeft = function () { if (this.o.pageXOffset) { return this.o.pageXOffset; } else if (this.o.scrollLeft) return this.o.scrollLeft; return 0; };
	__prop.getscrollTop = function () { if (this.o.pageYOffset) { return this.o.pageYOffset; } else if (this.o.scrollTop) return this.o.scrollTop; return 0; };
	__prop.getscrollLocation = function (targetParent) { return igk.winui.GetScrollPosition(this.o, targetParent); };
	__prop.getscrollMaxTop = function () { if (this.o.scrollTopMax) return this.o.scrollTopMax; else return this.o.offsetHeight; };
	__prop.getscrollMaxLeft = function () { if (this.o.scrollLeftMax) return this.o.scrollLeftMax; else return this.o.offsetWidth; };



	// -------------- WINDOW function
	__prop.createElement = function (tagname) {

		if (this.o.namespaceURI == null) {
			var c = document.createElementNS(null, tagname);
			return c;
		}
		return igk.createNode(tagname, this.o.namespaceURI || igk.namespaces.xml);
	};
	__prop.isChildOf = function (target) {
		var q = this.o.parentNode;
		while (q) {
			if (q == target)
				return !0;
			q = q.parentNode;
		}
		return !1;
	};
	// -------------- ADDITIONAL FUNCTION	
	__prop.collapse = function (property, callback) {
		if (m_anim && m_anim.collapsing) {
			// stop scrolling of this item
			m_anim.collapsing.stop();
		}
		// create animation context
		var anim1 = igk.animation.init(this, property.interval, property.duration,
			function () { },
			function () { },
			function () {
				if (m_anim && m_anim.__anim && m_anim.__anim.collapsing)
					delete m_anim.__anim.collapsing;// =null;
				if (callback)
					callback();
				// delete m_anim;						
			});
		m_anim = createPNS(this, "__anim", { "collapsing": anim1 });
		m_anim.type = "scrolling";
		anim1.start();
		return !0;
	};
	__prop.expand = function (property, callback) {
		if (m_anim && m_anim.expanding) {
			// stop scrolling of this item
			m_anim.expanding.stop();
		}
		var anim1 = igk.animation.init(this, property.interval, property.duration,
			function () { },
			function () { },
			function () {
				if (m_anim && m_anim.__anim && m_anim.__anim.expanding)
					delete m_anim.__anim.expanding;// =null;
				if (callback)
					callback();
				// delete m_anim;						
			});
		m_anim = createPNS(this, "__anim", { "expanding": anim1 });
		m_anim.type = "scrolling";
		anim1.start();
		return !0;
	};


	function __igk_event(q, p, n) {
		var self = this;
		function __ecall(evt) {
			if (evt.propertyName == n) {
				igk.winui.getEventObjectManager && igk.winui.getEventObjectManager().raise(self.target.o, n);
			}

		}
		document.documentElement.attachEvent('onpropertychange', __ecall);
		if (p)
			igk_appendProp(this, p);
		igk_appendProp(this, {
			fordoc: true,
			target: q,
			unregister: function () {
				document.documentElement.detachEvent('onpropertychange', __ecall);
			}
		});
	};


	// extend properties
	// _base['fill'] = Object.getOwnPropertyDescriptor(this,'fill');
	// igk.defineProperty(this, 'fill',  {get:function(){ return _base['fill'].get.apply(_q); }, set: function(v){
	// if (!_ri)
	// _base['fill'].set(v);
	// }});
	function igk_extendProperty(o, n, p) {
		if (!o)
			return !1;
		var h = Object.getOwnPropertyDescriptor(o, n) || Object.getOwnPropertyDescriptor(o, n);
		var e = {};
		if (('get' in p) && ('get' in h)) {
			e.get = function () {
				return p.get.apply(this, [h.get]);
			};
		}
		if (('set' in p) && ('set' in h)) {
			e.set = function (v) {
				return p.set.apply(this, [v, h.set]);
			};
		}
		igk.defineProperty(o, n, e);
	};


	// >namespace: igk
	createNS("igk", {
		DEBUG: false,// for debuging purpose		
		release: "17/01/15", // date of birth
		evaluating: false,

		elog: function (msg, tag) {	// login function 
			tag = tag || _FM_;
			console.error("[" + (_FM_) + "]" + ":" + msg);
		},

		createObj: function (s, d) {
			// createobject from string or array
			if (isUndef(d))
				d = 0;
			var o = {};
			var t = s.split('|');
			for (var s = 0; s < t.length; s++) {
				o[t[s]] = d;
			}
			return o;
		},
		isUndef: isUndef,
		initObj: igk_initobj,
		// function (o, def) {
		// if (o == null || typeof (o) !== "object")
		// return def;
		// for (var i in def) {
		// if ((i in o) == false) {
		// o[i] = def[i];
		// }
		// }
		// return o;
		// },		
		createNode: function (tag, ns) {
			if (!tag)
				return 0;

			if (ns && document.createElementNS)
				return __igk(document.createElementNS(ns, tag));
			tag = tag.toLowerCase();
			return __igk(document.createElement(tag));
		},
		createNSComponent: function (ns, classname) {// create component in namespace
			var lst = igk.winui.getClassList();
			var g = lst["ns://"] && lst["ns://"][ns] && lst["ns://"][ns][classname];
			if (g) {
				var s = null;
				if (g.data.create) {
					s = g.data.create.apply(window, igk.system.array.slice(arguments, 2));
				} else {
					s = igk.createNode("div");
					s.addClass(classname);
				}
				igk.ajx.fn.initnode(s.o);
				return s;
			}
			return null;
		},
		createComponent: function (classname) {// create a control class component
			// usage sample : igk.createComponent("igk-ajx-uri-loader")
			// component must be first registrated with igk.winui.initClassControl
			var lst = igk.winui.getClassList();
			var g = lst[classname];
			if (g) {
				var s = null;
				if (g.data.create) {
					s = g.data.create.apply(window, igk.system.array.slice(arguments, 1));
				} else {
					s = igk.createNode("div");
					s.addClass(classname);
				}
				igk.ajx.fn.initnode(s.o);
				return s;
			}
			return null;

		},
		createText: function (s) {
			return __igk(document.createTextNode(s));
		},
		createHtml: function (s) {
			var o = igk.createNode("div").setHtml("&nbsp;").o;
			return igk.createText(o.textContent);
		},
		clearTimeout: function (timeout) {

			window.clearTimeout(timeout);
		},
		eval: igk_eval,
		typeofs: function (n) {
			return typeof (n) == 'string';
		},
		typeoff: function (n) {
			return typeof (n) == 'function';
		},
		loadScript: function (filename) {
			if (!filename) return;
			var uri = null;
			var p = m_scriptTag;// get the current script tag
			if (p) {


				uri = igk.constants.http_scheme + document.domain + igk_getdir(window.location.pathname + "") + "/" + igk_getdir(p.getAttribute("src")) + "/" + filename;
			}

			if (!uri) {
				return;
			}
			// load the plugins js scripts
			igk.ajx.get(uri, null, function (xhr) {
				if (this.isReady()) {
					var s = igk.createNode("script");
					s.setAttribute("type", "text/javascript");
					s.setAttribute("language", "javascript");
					s.o.innerHTML = xhr.responseText;
					document.head.appendChild(s);
				}
			});

		},
		getScriptLocation: igk_getScriptLocation,
		loadPlugins: function (plugins) {
			// every plugin must be a comma separated string of name
			// exemple : animation,regex,info
			// and every folder that contain a plugins must have a plugin.js script at root
			if (!plugins)
				return;
			// alert("load plugins ... ");
			var t = plugins.split(',');
			var uri = null;
			var p = m_scriptTag;
			if (p) {
				uri = igk.constants.http_scheme + document.domain + "/" + igk_getdir(window.location.pathname + "") + "/" + igk_getdir(p.getAttribute("src")) + "/plugins";
			}

			if (!uri) {
				return;
			}
			for (var i = 0; i < t.length; i++) {
				// load the plugins js scripts
				igk.ajx.get(uri + "/" + t[i] + "/plugin.js", null, function (xhr) {
					if (this.isReady()) {
						var s = igk.createNode("script");
						s.setAttribute("type", "text/javascript");
						s.setAttribute("language", "javascript");
						s.setHtml(xhr.responseText);
						document.head.appendChild(s.o);
					}
				});
			}
		},
		preload: function (u) {
			// load the plugins js scripts
			igk.ajx.get(u, null, function (xhr) {
				if (this.isReady()) {
					var s = igk.createNode("script");
					s.setAttribute("type", "text/javascript");
					s.setAttribute("language", "javascript");
					s.setHtml(xhr.responseText);
					var b = document.head || igk.dom.body().o;
					if (b)
						b.appendChild(s.o);
				}
			});
		},
		canInvoke: function () {
			return (window.external) && (('notify' in window.external) || (typeof (window.external.callFunc) != igk.constants.undef));
			// return ('notify' in window.external) || window.external && (typeof (window.external.callFunc) != igk.constants.undef);
		},
		invoke: function (method, params) {// used to invoke external script function	

			var n = 0;
			var fc = null;
			var _out = 0;
			var _json = { method: method };
			if (params) {
				if (typeof (params) == 'object') {
					params = igk.JSON.convertToString(params);
				}
				_json.param = params;
			}


			if ((igk.navigator.IEVersion() <= 7.0) || ('notify' in window.external)) {
				try {
					_out = window.external.notify(igk.JSON.convertToString(_json));
				} catch (ex) {
					igk.winui.notify.showMsg("<div class=\"igk-notify-danger\">Execption : External JS Notify [" + method
						+ "] not invoked <br /> " + ex + "</div");
				}
				return _out;
			}

			fc = ('callFunc' in window.external) ? window.external.callFunc : null;



			if (n) {
				try {
					if (n) {
						_out = window.external.notify(igk.JSON.convertToString(_json));
					} else {
						_out = fc(_json);//"{method:"+method+", param:\""+ params+"\"}");
					}
				}
				catch (ex) {
					igk.winui.notify.showMsg("<div class=\"igk-notify-danger\">Execption : External JS Notify [" + method
						+ "] not invoked <br /> " + ex + "</div");
				}

			}
			else {
				igk.winui.notify.showMsBox(__libName, "<div class=\"igk-notify-danger\">No external function defined [" + method + " : " + n + "]</div>", "igk-info");
			}
			return _out;
		},
		constants: {
			// declaring usage constants
			http_scheme: "http://",
			https_scheme: "https://",
			namespace: "http://www.igkdev.com",
			"undef": IGK_UNDEF,
			"true": true,
			"false": false,
			regex: igk_regex_constant()
		},
		"selector_showlist": function (s) {
			var ss = "";
			s.each(function () { ss += " " + this.o.tagName + ":" + this.o + "\n"; return !0; });
			// alert("Show selector list " + ss);

		},
		eval_all_script: igk_eval_all_script,
		init_document: function (s) { //initialize document 		 
			igk.ctrl.init_controller();
			igk_preload_image(document);
			igk_preload_anim_image(document);
			// apply preload document
			__applyPreloadDocument(document);
			__initDocSetting = s;
		},
		alert: function (m, t) {
			igk_show_notify_msg(t || 'alert', m);
		},
		console_debug: igk_console_debug,
		show_prop: igk_show_prop,
		show_notify_prop: function (e) {
			igk.winui.notify.showMsg(igk.html.getDefinition(e));
		},
		show_notify_prop_v: function (e) {
			igk.winui.notify.showMsg(igk.html.getDefinitionValue(e));
		},
		show_notify_msg: igk_show_notify_msg,
		show_notify_error: igk_show_notify_error,
		get_v: function (o, k, d) {
			if (typeof (o[k] != IGK_UNDEF))
				return o[k];
			if (typeof (d) == IGK_UNDEF)
				return null;
			return d;
		},
		show_prop_keys: igk_show_prop_keys,
		preload_image: igk_preload_image,
		show_event: igk_show_event,
		getParentScriptByTagName: igk_getParentScriptByTagName,
		getParentScriptForm: function () { return igk_getParentScriptByTagName("form"); },
		getElementsByTagName: function (e, tag) { return e.getElementsByTagName(tag); },
		getParentScript: function () { return igk_getParentScriptByTagName(null); },
		getLastScript: igk_getLastScript,
		getCurrentScript: igk_getCurrentScript,
		rmCurrentScript: function () {
			var s = igk.getCurrentScript();
			if (s) {
				$igk(s).remove();
			}
		},
		initpowered: igk_init_powered,
		getParentByTagName: igk_getParentByTagName,
		getParentById: igk_getParentById,
		appendProperties: igk_appendProp,
		defineProperty: igk_defineProperty,
		extendProperty: igk_extendProperty,
		defineEnum: igk_defineEnum,
		checkOnePropertyExists: igk_checkOnePropertyExists,
		checkAllPropertyExists: igk_checkAllPropertyExists,
		callfunction: igk_callfunction,
		appendChain: function (q, n, func) {

			switch (typeof (q[n])) {
				case 'function':
					var fc = q[n];

					q[n] = function () {
						fc.apply(q, arguments);
						func.apply(q, arguments);
					};
					return 1;

				case 'undefined':
					q[n] = func;
					return 1;
			}
			return 0;
		},
		prependChain: function (q, n, func) {

			switch (typeof (q[n])) {
				case 'function':
					var fc = q[n];

					q[n] = function () {
						func.apply(q, arguments);
						fc.apply(q, arguments);
					};
					return 1;

				case 'undefined':
					q[n] = func;
					return 1;
			}
			return 0;
		},
		pushFunction: function (f, fc) {
			//utility function used push 
			var _s = f;
			if (_s == null)
				return fc;
			_s = function () {
				f.apply(this, arguments);
				fc.apply(this, arguments);
			};
			return _s;
		},
		check: function (item, pattern) {
			var m = igk_select_exp(pattern.substring(1));
			if (m.check(item, 0)) {
				return !0;
			}
			return !1;
		},
		qselect: function (item, pattern) {// with query selector
			var v_sl = new igk.selector();
			if (item.querySelectorAll) {
				var p = item.querySelectorAll(pattern);
				for (var i = 0; i < p.length; i++) {

					v_sl.push($igk(p[i]));
				}
			}
			return $igk(v_sl);
		},
		select: function (item, pattern) {// select in igk			
			var b = null;
			var v_sl = new igk.selector();
			if (!item || (pattern == null) || (pattern.length == 0) || /['`\[\]]/.exec(pattern)) {
				return $igk(v_sl);
			}

			// query selector detection
			var v_list = pattern.split(',');
			if (v_list.length > 1) {

				for (var sm = 0; sm < v_list.length; sm++) {
					var v_cq = igk.select(item, v_list[sm]);

					if (v_cq.getCount() > 0) {
						v_sl.load(v_cq);
					}
				}
				return v_sl;
			}
			if (igk.system.string.startWith(pattern, "^?")) {
				// load block pattern data
				pattern = pattern.substring(2);
				return $igk(pattern);
			}

			if (pattern == "::") {
				// select parent
				v_sl.push($igk(item).o.parentNode);
				return $igk(v_sl);
			}
			if (pattern == "??") {// select body content
				// var k=igk.dom.body();
				v_sl.push(igk.dom.body().o);
				return $igk(v_sl);
			}
			if (igk.system.string.startWith(pattern, '?')) {
				// if element contain criteria
				// exemple ?.igk-body

				var m = igk_select_exp(pattern.substring(1));
				if (m.check(item, 0)) {
					v_sl.push(item);
				}
				return $igk(v_sl);
			}
			if (igk.system.string.startWith(pattern, '^')) {
				//+ parent search 
				// sample : ^div

				var spattern = pattern.split(" ");

				pattern = spattern[0];

				if ((/^\^[\w\-_]+$/.exec(pattern))) {
					// search parent by tagname
					// exemple: ^div
					b = $igk($igk(item).getParentByTagName(pattern.substring(1)));
					if (b)
						v_sl.push(b);
				}
				else if ((/^\^#[\w\-_]+$/.exec(pattern))) {// search parent by id
					// exemple: ^#info
					b = $igk(item).getParentById(pattern.substring(2));
					if (b)
						v_sl.push(b);
				}
				else if ((/^\^\./.exec(pattern))) {// search parent by class by class
					pattern = pattern.substring(2);
					var s = $igk(item).o.parentNode;
					var rx = new RegExp("(^| )(" + pattern + ")(\\s|$)", "i");

					while (s != null) {

						if (rx.exec("" + s.className)) {
							v_sl.push(s);
						}
						s = s.parentNode;
					}

				}
				if ((spattern.length > 1) && (v_sl.getCount() == 1)) {
					return $igk(v_sl).first().select(spattern.slice(1).join(" "));
				}
				return $igk(v_sl);
			}

			if (igk.system.string.startWith(pattern, '+')) {
				// search on next sibling
				var h = $igk(item).o.nextSibling;
				var spattern = pattern.substr(1).split(" ");

				pattern = spattern[0];
				while (h) {
					if (igk.css.isMatch(h, pattern)) {
						v_sl.push(h);
					}
					h = h.nextSibling;
				}
				return $igk(v_sl);

			}

			// used to select item on the current node
			// pattern: 
			// [*] for all item. exemple igk.select(node,'*');
			// [:attribute_name] that have the attribute name. exemple igk.select(node,":id")
			// [:^expression] the requested attribute start with the expression
			// [.callName] that match the class name
			// [tagname] that match the tag name . exemple igk.select(node,"div")
			// [>>] child only selection
			// [>:expression] child only expression
			// [>tagname] child only tag name

			var v_it = null;
			var s = null;
			var exp = null;
			var fid = false;

			if (typeof (pattern) == "string") {
				// special meaning
				switch (pattern) {
					case ">>":
						// child only
						// sample:  select('>>')
						for (var i = 0; i < item.childNodes.length; i++) {
							s = item.childNodes[i];
							v_sl.push(s);
						}
						return $igk(v_sl);
						break;
					default:
						if (/^>:([\w_#\.]+[\w\-_0-9 ]*)$/.exec(pattern)) {
							// child that match tag name
							// sample:  select(':>xcv')
							pattern = pattern.substring(2);
							for (var i = 0; i < item.childNodes.length; i++) {
								s = item.childNodes[i];
								if (v_sl.isMatch(pattern, s)) {
									v_sl.push(s);
								}
							}
							return $igk(v_sl);
						}
						break;
				}


				if (!item.getElementsByTagName) {
					// return empty selection. becoause of item not supported getElementsByTagName
					if (igk.DEBUG) console.debug("/!\ selection will failed because element not support getElementByTagName");
					return $igk(v_sl);
				}
				v_it = item.getElementsByTagName("*");
				if (pattern == "*") {
					// push all
					for (var i = 0; i < v_it.length; i++) {
						s = v_it[i];
						v_sl.push(s);
					}
				}
				else {
					if (/^:([\w_]+[\w\-_0-9]*)$/.exec(pattern)) {// search by attribute
						pattern = pattern.substring(1);
						for (var i = 0; i < v_it.length; i++) {
							s = v_it[i];
							if (s.getAttribute(pattern)) {
								v_sl.push(s);
							}
						}
					}
					else if (/^:\^([\w_]+[\w\-_0-9]*)$/.exec(pattern)) {// search by starting with attribute 
						pattern = pattern.substring(2);
						for (var i = 0; i < v_it.length; i++) {
							s = v_it[i];
							var msg = $igk(s).getAllAttribs();
							if (RegExp("" + pattern + "", "i").test(msg)) {
								v_sl.push(s);
							}
						}
					}
					else if ((/^\.[\w\-_]+$/.exec(pattern))) {// search in class name
						pattern = pattern.substring(1);
						for (var i = 0; i < v_it.length; i++) {
							s = v_it[i];
							if (igk_item_match_class(pattern, s)) {
								v_sl.push(s);
							}
						}
					}
					else if ((/^\>[\w\-_]+$/.exec(pattern))) {// search by child node tagname
						// if(igk.DEBUG) 

						pattern = pattern.substring(1);
						exp = new RegExp("(" + pattern + ")", "i");
						for (var i = 0; i < v_it.length; i++) {
							s = v_it[i];
							if ((s.parentNode == item) && exp.exec("" + s.tagName)) {
								v_sl.push(s);
							}
						}
					}
					else {

						if ((igk.constants.regex.idSearch.exec(pattern))) {// search in id
							fid = !0;
							pattern = pattern.substring(1);
							for (var i = 0; i < v_it.length; i++) {
								s = v_it[i];

								if (new RegExp("^(" + pattern + ")$", "i").exec("" + s.id)) {
									v_sl.push(s);
								}
							}
						}
						else {


							var m = igk_select_exp(pattern);
							// if(pattern==".igk-body#query-s-r"){


							// m.debug=1;
							// }

							if (m != null) {
								m.select(v_sl, item);
							}
							else {
								v_it = item.getElementsByTagName(pattern);
								for (var i = 0; i < v_it.length; i++) {
									s = v_it[i];
									v_sl.push(s);
								}
							}
						}

					}
				}
			}
			// if(fid){
			// if(v_sl.getCount()==0)
			// return null;
			// }	

			return $igk(v_sl);
		},
		load: function (func) { // load function
			if (func == null)
				return;
			if (document.readyState == "loading") {
				igk.winui.reg_event(window, "load", func);
			}
			else {
				func.apply(window);
			}
		},
		is_readyRegister: function (func) {
			for (var i = 0; i < readyFunc.length; i++) {
				if (func == readyFunc[i])
					return !0;
			}
			return !1;
		},
		readyCountFunc: function () {
			return readyFunc.length;
		},
		readyGlobal: function (func) {
			if (document.readyState == "complete") {
				func.apply(document);
			}
			else { // store to call on ready complete
				m_readyGlobalFunc[m_readyGlobalFunc.length] = func;
			}
		},
		isdevelop: function () {
			if (typeof (__isdev) == 'undefined') {
				__isdev = igk.getScriptLocation().location.split('/').pop() == __devscript;
			}
			return __isdev;
		},
		readyinvoke: function (n) {// call this function in script that have source content 

			//executing script name





			var s = igk_getCurrentScript();
			var t = igk.system.array.slice(arguments, 1);
			igk.ready(function () {
				// script :  readyinvoke

				var ns = igk.system.getNS(n);
				if (typeof (ns) == 'function') {
					var bck = m_scriptNode;
					m_scriptNode = s;
					ns.apply(s, t);
					m_scriptNode = bck;
				} else if (igk.isdevelop()) {
					console.debug(n + " : not found");
				}
			});
		},
		isDefine: igk_isdefine,
		onContentLoad: function (func, priority) {
			// register document load content
			// alert('on content load');
			// if (typeof(document.DOMContentLoad) == 'undefined'){
			// return;
			// }

			priority = igk.isDefine(priority, 10);
			var k = "igk.event.contentLoad";
			var g = igk.system.getNS(k) || igk.system.createNS(k, { isRegister: 0 });
			func = { fn: func, priority: priority };
			if (g.callback)
				g.callback.push(func);
			else
				g.callback = [func];
			if (!g.isRegister) {
				document.addEventListener('DOMContentLoaded', function (evt) {
					// sort list
					g.callback.sort(function (e, i) {
						var c = e.priority - i.priority;
						if (c != 0)
							c /= Math.abs(c);
						return c;
					});
					for (var i = 0; i < g.callback.length; i++) {
						var fc = g.callback[i];
						fc.fn.apply(document, [evt]);
					}
					delete (window[k]);
				});
				g.isRegister = 1;
			}
		},
		ready: function (func, sys) {
			// ready function
			// sys call by system
			function _async_call(e, document, i) {
				//@i : name of the measurement function for 'warning duration violation' raise in chrome
				// if (i=="f5")

				setTimeout(function () {

					e.apply(document);
				}, 10);
			}

			if ((func == null) || (func == 0)) {

				if ((document.readyState !== "complete") || m_ready_calling)
					return;
				m_ready_calling = !0;
				// call ready global function
				var e = null;
				if (m_readyGlobalFunc.length > 0) {
					for (var i = 0; i < m_readyGlobalFunc.length; i++) {
						e = m_readyGlobalFunc[i];
						try {
							_async_call(e, document, i);
							// e.apply(document);
						}
						catch (ex) {
							igk.winui.notify.showError("<div class=\"igk-title-5\">igk.js ReadyGlobal function call failed </div> <br />" + ex + "<br /><quote>" + ex.stack + "</quote><pre style=\"max-height:200px; overflow-y:auto;\">" + e + "</pre>");
						}
					}
					m_readyGlobalFunc = [];
				}
				// call all ready function		
				if (readyFunc.length > 0) {
					for (var i = 0; i < readyFunc.length; i++) {
						e = readyFunc[i];
						try {
							_async_call(e, document, "f" + i);
							// e.apply(document);
						}
						catch (ex) {
							if (igk.DEBUG) {
								var ox = "";
								if (ex.stack)
									ox = (ex.stack + "").replace("\n", "<br />");

								igk.winui.notify.showError("<div class=\"igk-title-5\">igk.js Ready function call failed </div> <br />" + ex + "<br /><quote>" +
									ox
									+ "</quote><pre class='error-code' style=\"max-height:200px; overflow-y:auto;\"><code>" + e + "</code></pre>");
							} else {
								console.error(ex);
							}
						}
					}

				}
				// clear ready func				
				readyFunc = [];
				m_ready_calling = false;
			}
			else {
				if (m_ready_calling || (document.readyState == "complete")) {
					func.apply(document);
				}
				else {
					// store to call on ready complete
					readyFunc[readyFunc.length] = func;
				}
			}
		},
		unready: function (func) {
			var s = [];
			for (var i = 0; i < readyFunc.length; i++) {
				if (readyFunc[i] == func) {
					continue;
				}
				s.push(readyFunc[i]);
			}
			readyFunc = s;
		}
		, getElementsByAttribute: function (properties) {
			if (!properties)
				return null;
			var d = document.getElementsByTagName("*");
			var out = [];
			var b = !0;

			for (var i = 0; i < d.length; i++) {
				for (var k in properties) {
					if (d[i].getAttribute(k) == properties[k]) {
						out.push(d[i]);
					}
				}
			}
			return out;
		},
		selector: function () {
			// ------------------------------------------------------
			// selector element
			// ------------------------------------------------------
			var m_items = [];
			igk_appendProp(this,
				{
					getCount: function () { return m_items.length; },
					push: function (item) {
						m_items.push(item);
						this.length = this.getCount();
					},
					isSr: function () { return !0; }, // selector property
					toString: function () { return "igk.selector[" + this.getCount() + "]"; },
					each: function (func, args) {
						args = args ? args : [];
						for (var i = 0; i < this.getCount(); i++) {
							if ((func) && func.apply && !func.apply($igk(m_items[i]), args)) {
								break;
							}
						}
						return this;
					},
					// call func in interval chain
					waitInterval: function (duration, func) {
						function __func_waiter(i, func) {
							var self = this;
							igk_appendProp(this, {
								next: null,
								wait: function () {
									func.apply(i, arguments);
									// var h=new __func_waiter(self.next,func);
									// h.timeout=setTimeout(h.wait,duration);
									if (self.next)
										self.next.start();
								},
								start: function () {
									this.timeout = setTimeout(this.wait, duration);
								}
							});
						}
						var iduration = duration;
						var h = null;
						var t = null;
						for (var i = 0; i < this.getCount(); i++) {
							if (i == 0) {
								h = new __func_waiter($igk(m_items[i]), func);
								t = h;
							}
							else {
								h.next = new __func_waiter($igk(m_items[i]), func);
								h = h.next;
							}
						}
						if (t != null) {
							t.start();
						}
						return this;
					},
					each_all: function (func, args) {
						// >@ call function in all element
						if (func) {
							// correct object expected in ie 8
							if (typeof (args) == IGK_UNDEF)
								args = [];
							for (var i = 0; i < this.getCount(); i++) {
								func.apply($igk(m_items[i]), args);
							}
						}
						return this;
					}
					, getItemAt: function (index) {
						if ((index >= 0) && (index < this.getCount()))
							return $igk(m_items[index]);
						return null;
					},
					getLastItem: function () {
						var c = this.getCount();
						return c > 0 ? this.getItemAt(c - 1) : null;
					},
					getFirstItem: function () {
						var c = this.getCount();
						return c > 0 ? this.first() : null;
					},
					getNodeAt: function (index) {
						if ((index >= 0) && (index < this.getCount()))
							return m_items[index];
						return null;
					},
					isMatch: function (p, n) {
						// check if this property match
						// @p:pattern
						// @n:dom node			
						if (igk.constants.regex.tagName.exec(p)) {
							return n.tagName && (n.tagName.toLowerCase() == p.toLowerCase());
						}
						else if (igk.constants.regex.className.exec(p)) {
							return igk_item_match_class(p, n);
						} else if (igk.constants.regex.idSearch.exec(p)) {
							return (new RegExp("^(" + p + ")$", "ig")).exec("" + n.id);
						}
						return !1;
					},

					select: function (pattern) {
						// selector selection pattern		
						var v_s = new igk.selector();
						if (this.getCount() == 0) {
							return v_s;// empty selector
						}

						for (var i = 0; i < this.getCount(); i++) {
							var g = igk.select(m_items[i], pattern);
							if (g && (g.getCount() > 0)) {
								for (var j = 0; j < g.getCount(); j++) {
									v_s.push(g.getNodeAt(j));
								}
							}
						}
						return $igk(v_s);
					},
					load: function (g) { // load selector
						if (g && g.isSr()) {
							for (var j = 0; j < g.getCount(); j++) {
								this.push(g.getNodeAt(j));
							}
						}
					},
					clear: function () {
						m_items = new Array();
					}
				});

			igk.initprop(this);
		},
		initprop: function (element) {


			// end __igk_nodeProperty

			if (typeof (element.igk) == igk.constants.undef) {
				// build element igk property
				(function () {

					this.igk = new __igk_nodeProperty(element);

					this.igk.$ = window.igk;

				}).apply(element);
			}
			if (typeof (element.$) == igk.constants.undef) {
				// build point to igkFramework
				(function () {
					// this.$=function(){
					// window.igk;
					// return null;
					// }

					this.global = window.igk;
				}).apply(element);

			}// endif
			// element.igk.$=window.igk;
		},


		getNumber: igk_getNumber,// expose get number function
		getUnit: igk_getUnit,// expose get unit
		getPixel: igk_getPixel // igk.getPixel
		// toString: function(){
		// return "namespace:igk";
		// }// end tostring
	});

	// igk exception
	igk_appendProp(igk, {
		exception: function (msg) {
			this.name = "IGKException";
			this.level = "";

			if (typeof (msg) == "string") {
				this.message = "Error:" + msg;
			}
			else if (typeof (msg) == "object") {
				igk_appendProp(this, msg);
			}
			this.toString = function () {
				return this.name + " : " + this.message;
			};
		}
	});




	createNS("igk.uri", {
		getQueryArg: function (q) { // retrieve query argument from the query uri q
			return new (function () {
				var nb = [];
				var i = q.indexOf("?");
				if (i != -1) {
					var e = q.substr(i + 1);
					var ln = e.length, pos = 0;
					var c = 0;
					var t = 0; // read mode type
					var n = ""; // name
					var v = ""; // value
					while (pos < ln) {
						c = e[pos];
						switch (c) {
							case "=":
								t = 1;
								break;
							case "&":
								nb[n] = v;
								nb.push(v);
								t = 0;
								n = "";
								v = "";
								break;
							default:
								if (t == 0) {
									n += c;
								} else
									v += c;

								break;
						}
						pos++;
					}
					if (t == 1) {
						nb[n] = v;
						nb.push(v);
					}
					console.debug(nb);
				}
				igk.appendProperties(this, {
					get: function (n) {
						return nb[n];
					}
				})
			})();
		}
	});



	// ---------------------------------------------------------
	// 
	// 

	// TODO: initialize environment style
	igk.ready(function () {
		var b = igk.dom.body();
		var n = igk.navigator;
		if (n.isIE() && n.IEVersion() <= 11) {
			b.addClass("ie-11-service");// no support of css 3 setting
		}

		//
		if (n.isSafari()) {
			b.addClass("safari"); //.igk.dom.body();
		}
	});



	// ---------------------------------------------------------
	// publish lib entity
	// ---------------------------------------------------------
	(function () {
		var m_publisher = {};
		var m_staticReg = {};
		var m_publisherobj = new function () {
			igk.appendProperties(this, {
				toString: function () { return "publisher obj"; }
			});
		};


		var m_cinf = {};
		// register function to publish. also used to register static function . call
		createNS("igk.publisher", {
			createEventData: function (t, ctx) {
				return {
					target: t,
					context: ctx
				};
			},
			register: function (n, func) {
				if (!n || !func)
					return !1;
				var e = null;
				if (m_publisher[n]) {
					e = m_publisher[n];
				}
				else {
					e = { k: n, s: new igk.system.collections.list(), toString: function () { return "publisher-entity"; } };
					m_publisher[n] = e;
				}
				e.s.add(func);
			},
			unregister: function (n, func) {
				var e = m_publisher[n];
				if (typeof (e) == IGK_UNDEF)
					return;
				e.s.remove(func);
				if (e.s.getCount() == 0) {
					delete (m_publisher[n]);
				}
			},
			publish: function (n, prop) {

				var e = m_publisher[n];
				if (typeof (e) == IGK_UNDEF)
					return;
				// m_names.push(n);

				// array copy
				var tab = [];
				for (var i = 0; i < e.s.getCount(); i++) {
					tab.push(e.s.getItemAt(0));
				}
				for (var i = 0; i < tab.length; i++) {
					m_cinf = {
						name: n,
						caller: tab[i],
						funcs: e.s
					};
					m_cinf.caller.apply(m_cinf, [prop]);
				}
				// this.name = m_names.pop();
			},
			getName: function (obj, ns, name) {
				if (obj instanceof igk.object) {

					if (obj.type == "class") {
						return name;
					}
				}
				else {

					for (var i in ns) {
						if (obj.constructor == ns[i])
							return i;
					}
				}
			},
			inheritFrom: function (src, parent) {
				if ((typeof (src) == 'function') && (typeof (parent) == 'function')) {
					var p = new parent();
					src.prototype = p;
					src.prototype.$parent = p;
					src.prototype.$super = function (m) {
						var r = arguments.length > 1 ? igk.system.array.slice(arguments, 1) : null;
						this.$parent[m].apply(this, r);
					};
					return !0;
				}
				return !1;
			},
			registerStatic: function (ns, name, func) {
				// if((!obj)||(typeof(obj) !="object"))
				// return;
				var fname = ns.namespace + "." + name;
				if (m_staticReg[fname]) {
					return;
				}
				m_staticReg[fname] = 1;
				// if(obj instanceof igk.object){

				// if(obj.type=="class"){
				// func.call(obj);

				// }
				// }
				// else{			
				func.call(ns);
				// m_staticReg[fname]=1;
				// }
			},
			createPublisherEvent: function (prop) {
				var c = new igk.publisher.event();
				igk.appendProperties(c, prop);
				return c;
			},
			event: function () {
				igk.appendProperties(this, {
					toString: function () { return "publisher event"; }
				});
			}
			, getCount: function (n) {
				// number of function list. -1 : no name
				// -2 : no function
				n = n || this.name;
				if (!n) return -1;

				var e = m_publisher[n];
				if (typeof (e) == IGK_UNDEF)
					return -2;
				return e.s.getCount();
			}


		});



	})();

	var _js_version = -1;
	createNS("igk.os", {
		destroysession: function (uri, callback) {
			igk.ajx.send({
				uri: uri,
				method: 'DELETE',
				contentType: 'application/json',
				func: function (xhr) {
					if (this.isReady()) {
						if (callback) {
							callback.apply(this, [xhr]);
						}
					}
				},
				param: '{destroysession:1}'
			});
		},
		javascriptVersion: function () {
			if (_js_version != -1)
				return _js_version;
			var _body = igk.dom.body();


			var t = ["1.1", "1.2", "1.3", "1.4",
				"1.5", "1.6", "1.7", "1.8", "1.8.1", "1.8.5", "1.9"
			];
			for (var i = 0; i < t.length; i++) {

				var s = igk.createNode("script");
				if (i == 0) {
					s.o["type"] = "text/javascript";
					s.setHtml("var _jver='1.1';");

				} else {
					// s.o["type"] = "text/javascript1."+i;
					s.o.setAttribute("language", "javascript" + t[i]);
					s.setHtml("_jver='" + t[i] + "';");
				}
				_body.add(s);
				s.remove();
			}


			_js_version = _jver;

			return _js_version;
		},
		checkupdate: function (uri, v) {

			var q = $igk(igk.getParentScript());


			(function () {
				igk.io.file.load(uri, function (d) {


					if (!d.error) {
						var c = igk.createNode("dummy");
						var d = igk.utils.getBodyContent(d.data);
						c.setHtml(d);
						var m = c.getElementsByTagName("Message")[0];
						var s = c.getElementsByTagName("status")[0] || null;
						var v = s ? s.innerHTML : -1;
						if (m)
							q.setHtml(m.innerHTML);
						else {
							q.remove();
						}
						return;
					}
					var w = $igk("#dialog.error").first();
					if (w) {
						var m = w.select(".msg").first().clone();
						igk.show_notify_error(
							w.select(".title").first().getHtml(),
							m.getHtmll());

						//"Loading ...","check update failed : "+uri);	
					}
					q.remove();
				});
				// var ob=igk.createNode('object');
				// ob.addClass('no-visible posab').setCss({width:'1px',height:'1px'});
				// igk.dom.body().prepend(ob);
				// ob.reg_event("error",function(evt){			
				// igk.show_notify_error("Loading ...","check update failed");	

				// });

				// ob.reg_event("load",function(evt){
				// if(!ob.o.contentDocument || ob.o.contentDocument.readyState!="complete")
				// return;	
				// var data=getNodeData(ob.o.contentDocument);

				// ob.remove();
				// q.remove();

				// });	
				// load data
				// ob.o.data=uri;
			})();

		},
		update: function (u) {
			var w = $igk("#dialog.wait").first();
			if (w) {
				var m = w.select(".msg").first().clone();
				igk.winui.notify.showMsBox(
					w.select(".title").first().getHtml(),
					m,
					'default',
					1
				);
				igk.ajx.fn.initnode(m.o);
			}
			igk.io.file.load(u, function (d) {
				igk.winui.notify.close(function () {
					var w = $igk("#dialog.os-complete").first();
					if (w) {
						// view notifyied dialog
						igk.winui.notify.showMsBox(
							w.select(".title").first().getHtml(),
							w.select(".msg").first().getHtml());


					} else {
						// genearl dialog notification
						igk.winui.notify.showMsBox("OS", "Update complete");
					}
					var k = igk.utils.getBodyContent(d.data);
					var m = igk.createNode('div').setHtml(k);

					var uk = m.select('ruri').first().o.innerHTML;
					// var not=igk.winui.notify.getView();

					if (uk) {
						igk.ajx.post(uk + "/?c=c_sc&f=forceview", null, igk.ajx.fn.replace_body);
					}
				});
			});
		},
		// install os zip lib
		install: function (f, msg) {
			var q = igk.createNode("div");
			ns_igk.ajx.postform(f, f.action, function (xhr) {
				if (this.isReady()) {
					f.reset();
					q.remove();
					igk.ajx.replaceBody(xhr.responseText, true);
					igk.winui.notify.showMsBox("OS", msg.complete);
				}
			});
			$igk(f).o.appendChild(q.o);
			q.setHtml(msg.wait);
		}
	});

	createNS("igk.features", {

	});

	(function () {

		var _passive = false;
		try {
			var opts = {};
			igk_defineProperty(opts, 'passive', {
				get: function () {
					_passive = true;
				}
			});
			if (window.addEventListener) {
				window.addEventListener('test', null, opts);
			}
		}
		catch (e) {

		}
		igk_defineProperty(igk.features, 'supportPassive', {
			get: function () {
				return _passive;
			}
		});

		igk_defineProperty(igk.features, 'supportBackgroundWorker', {
			get: function () {
				return typeof (window.Worker) !== 'undefined';
			}
		});

	})();


	createNS("igk.fn", {
		isset: function (i) {
			if ((i == null) || (typeof (i) == IGK_UNDEF))
				return !1;
			return !0;
		}
	},
		{ desc: "igk utility fonctions" }
	);


	// represent debug functions
	createNS("igk.debug", {
		write: function (msg) {
			if (igk.DEBUG) {
				console.debug(msg);
			}
		},
		assert: function (c, m) {// condition ,message
			if (c) {
				console.debug(m);
			}
		},
		enable: function (f) {
			igk.DEBUG = f;
		}

	});


	// represent utility fonction
	var prop_toextend = 0;
	createNS("igk.fn", {
		getItemFunc: function (it, n, fallback) {

			if (n in it)
				return it[n];
			var c = "";
			var r = n[0].toUpperCase() + n.substring(1);
			for (var i = 0; i < m_provider.length; i++) {
				c = m_provider[i] + r;// n.s;			
				if (c in it)
					return it[c];
			}
			return fallback;
		},
		getWindowFunc: function (n, fallback) {
			return igk.fn.getItemFunc(window, n, fallback);
		},
		isItemStyleSupport: function (item, name) {
			if (!item || !item.style)
				return !1;
			var t = [name.toLowerCase()];
			var h = m_provider;// ['webkit','ms','o'];
			var k = name.charAt(0).toUpperCase() + name.substring(1).toLowerCase();
			for (var i = 0; i < h.length; i++) {
				t.push(h[i] + k);
			}
			return igk.fn.isItemSupport(item.style, t);
		},
		isItemSupport: function (e, tab) {
			if (!e || !tab)
				return !1;
			for (var i = 0; i < tab.length; i++) {
				if (typeof (e[tab[i]]) != IGK_UNDEF)
					return !0;
			}
			return !1;
		}
		, isItemSupportAll: function (e, tab) {
			if (!e || !tab)
				return !1;
			for (var i = 0; i < tab.length; i++) {
				if (typeof (e[tab[i]]) == IGK_UNDEF)
					return !1;
			}
			return !0;
		},
		getItemProperty: function (i, n) { // properties to extends
			var g = prop_toextend || (function () {
				return prop_toextend = {
					isFullScreen: ["webkitIsFullScreen", "mozFullScreen"],
					fullscreenElement: ["webkitFullScreenElement", "mozFullScreenElement"],
					fullscreenEnabled: ["webkitFullScreenEnabled", "mozFullScreenEnabled"]
				};
			})();
			if (n in i)
				return n;
			var tab = g[n];

			// for(var s in g){
			// if (s in i)
			// return s;

			for (var m = 0; m < tab.length; m++) {
				if (tab[m] in i) {
					return tab[m];
				}
			}
			// }
			return 0;
		}

	});


	// createNS("igk.document",{
	// },
	// {
	// desc: "document utility function"
	// });

	// function __init_doc_prope(o){
	// var _wdoc = window.document;
	// igk_defineProperty(o,"isFullScreen",{
	// get:function(){ return _wdoc[igk.fn.getItemProperty(_wdoc, "isFullScreen")]; }
	// });


	// etant la propriété navigateur de document 
	// utilisation $igk(document).isFullScreen

	function def_property(o, n) {
		igk_defineProperty($igk(o), n, {
			get: function () { return o[igk.fn.getItemProperty(o, n)]; }
		});
	}
	def_property(window.document, "isFullScreen");
	def_property(window.document, "fullscreenElement");
	def_property(window.document, "fullscreenEnabled");


	// __init_doc_prope(igk.document);
	// for external management function
	createNS("igk.ext", {
		call: function (name, p) {
			return igk.invoke(name, p);
			// alert("call .igk.invoke");


			// var r = arguments.length > 1 ? igk.system.array.slice(arguments, 1) : IGK_UNDEF;
			// // check if external is define ie
			// if (typeof (external) != "undefined") {
			// r = (typeof (p) == "object") ? igk.JSON.convertToString(p) : r;
			// try {
			// if (r == IGK_UNDEF)
			// return external[name]();
			// return external[name](r);
			// } catch (ex) {
			// igk.show_notify_error("Exception on Call : " + name,
			// ex + "<br /><pre>" + ex.stack + "</pre>"
			// );
			// }
			// }
			// return null;
		},
		buildJSONData: function (t) {
			// build json data from target
			if ((t == null) || (typeof (t) == IGK_UNDEF))
				return "";
			var ob = {};
			$igk(t).select(".dial-item").each_all(function () {
				var id = this.getAttribute("id");
				switch (this.o.tagName.toLowerCase()) {
					case "select":
						ob[id] = this.o.value;
						break;
					default:
						var o = this.getAttribute("type") || "text";
						switch (o) {
							case "checkbox":
								if (this.o.checked)
									ob[id] = this.o.value;
								break;
							default:
								ob[id] = this.o.value;
								break;
						}
						break;
				}
			});
			var s = JSON.stringify(ob);
			return s;
		}
	});


	// igk.color namespace
	var _colors = { transparent: "Transparent", black: "#000", navy: "#00007F", darkblue: "#00008C", mediumblue: "#00C", blue: "#00F", darkgreen: "#006300", green: "#007F00", teal: "#007F7F", darkcyan: "#008C8C", deepskyblue: "#00BFFF", darkturquoise: "#00CED1", mediumspringgreen: "#00F999", lime: "#0F0", springgreen: "#00FF7F", aqua: "#0FF", cyan: "#0FF", midnightblue: "#191970", dodgerblue: "#1E8EFF", lightseagreen: "#21B2AA", forestgreen: "#218C21", seagreen: "#2D8C56", darkslategrey: "#2D4F4F", darkslategray: "#2D4F4F", limegreen: "#3C3", mediumseagreen: "#3DB270", turquoise: "#3FE0D1", royalblue: "#3F68E0", steelblue: "#4482B5", darkslateblue: "#473D8C", mediumturquoise: "#47D1CC", indigo: "#490082", darkolivegreen: "#546B2D", cadetblue: "#5E9EA0", cornflowerblue: "#6393ED", mediumaquamarine: "#6CA", dimgray: "#686868", dimgrey: "#686868", slateblue: "#6B59CC", olivedrab: "#6B8E23", slategrey: "#707F8E", slategray: "#707F8E", lightslategray: "#778799", lightslategrey: "#778799", mediumslateblue: "#7A68ED", lawngreen: "#7CFC00", chartreuse: "#7FFF00", aquamarine: "#7FFFD3", maroon: "#7F0000", purple: "#7F007F", olive: "#7F7F00", grey: "#7F7F7F", gray: "#7F7F7F", skyblue: "#87CEEA", lightskyblue: "#87CEF9", blueviolet: "#892BE2", darkred: "#8C0000", darkmagenta: "#8C008C", saddlebrown: "#8C4411", darkseagreen: "#8EBC8E", lightgreen: "#8EED8E", mediumpurple: "#9370D8", darkviolet: "#9300D3", palegreen: "#99F999", darkorchid: "#93C", yellowgreen: "#9C3", sienna: "#A0512D", brown: "#A52828", darkgrey: "#A8A8A8", darkgray: "#A8A8A8", lightblue: "#ADD8E5", greenyellow: "#ADFF2D", paleturquoise: "#AFEDED", lightsteelblue: "#AFC4DD", powderblue: "#AFE0E5", firebrick: "#B22121", darkgoldenrod: "#B7870A", mediumorchid: "#BA54D3", rosybrown: "#BC8E8E", darkkhaki: "#BCB76B", silver: "#BFBFBF", mediumvioletred: "#C61484", indianred: "#CC5B5B", peru: "#CC843F", chocolate: "#D1681E", tan: "#D1B58C", lightgrey: "#D3D3D3", lightgray: "#D3D3D3", palevioletred: "#D87093", thistle: "#D8BFD8", orchid: "#D870D6", goldenrod: "#D8A521", crimson: "#DB143D", gainsboro: "#DBDBDB", plum: "#DDA0DD", burlywood: "#DDB787", lightcyan: "#E0FFFF", lavender: "#E5E5F9", darksalmon: "#E8967A", violet: "#ED82ED", palegoldenrod: "#EDE8AA", lightcoral: "#EF7F7F", khaki: "#EFE58C", aliceblue: "#EFF7FF", honeydew: "#EFFFEF", azure: "#EFFFFF", sandybrown: "#F4A360", wheat: "#F4DDB2", beige: "#F4F4DB", whitesmoke: "#F4F4F4", mintcream: "#F4FFF9", ghostwhite: "#F7F7FF", salmon: "#F97F72", antiquewhite: "#F9EAD6", linen: "#F9EFE5", lightgoldenrodyellow: "#F9F9D1", oldlace: "#FCF4E5", red: "#F00", magenta: "#F0F", fuchsia: "#F0F", deeppink: "#FF1493", orangered: "#F40", tomato: "#FF6347", hotpink: "#FF68B5", coral: "#FF7F4F", darkorange: "#FF8C00", lightsalmon: "#FFA07A", orange: "#FFA500", lightpink: "#FFB5C1", pink: "#FFBFCC", gold: "#FFD600", peachpuff: "#FFD8BA", navajowhite: "#FFDDAD", moccasin: "#FFE2B5", bisque: "#FFE2C4", mistyrose: "#FFE2E0", blanchedalmond: "#FFEACC", papayawhip: "#FFEFD6", lavenderblush: "#FFEFF4", seashell: "#FFF4ED", cornsilk: "#FFF7DB", lemonchiffon: "#FFF9CC", floralwhite: "#FFF9EF", snow: "#FFF9F9", yellow: "#FF0", lightyellow: "#FFFFE0", ivory: "#FFFFEF", white: "#FFF" };

	createNS("igk.system.colors", {}); // transparent: "Transparent", black: "#000", navy: "#00007F", darkblue: "#00008C", mediumblue: "#00C", blue: "#00F", darkgreen: "#006300", green: "#007F00", teal: "#007F7F", darkcyan: "#008C8C", deepskyblue: "#00BFFF", darkturquoise: "#00CED1", mediumspringgreen: "#00F999", lime: "#0F0", springgreen: "#00FF7F", aqua: "#0FF", cyan: "#0FF", midnightblue: "#191970", dodgerblue: "#1E8EFF", lightseagreen: "#21B2AA", forestgreen: "#218C21", seagreen: "#2D8C56", darkslategrey: "#2D4F4F", darkslategray: "#2D4F4F", limegreen: "#3C3", mediumseagreen: "#3DB270", turquoise: "#3FE0D1", royalblue: "#3F68E0", steelblue: "#4482B5", darkslateblue: "#473D8C", mediumturquoise: "#47D1CC", indigo: "#490082", darkolivegreen: "#546B2D", cadetblue: "#5E9EA0", cornflowerblue: "#6393ED", mediumaquamarine: "#6CA", dimgray: "#686868", dimgrey: "#686868", slateblue: "#6B59CC", olivedrab: "#6B8E23", slategrey: "#707F8E", slategray: "#707F8E", lightslategray: "#778799", lightslategrey: "#778799", mediumslateblue: "#7A68ED", lawngreen: "#7CFC00", chartreuse: "#7FFF00", aquamarine: "#7FFFD3", maroon: "#7F0000", purple: "#7F007F", olive: "#7F7F00", grey: "#7F7F7F", gray: "#7F7F7F", skyblue: "#87CEEA", lightskyblue: "#87CEF9", blueviolet: "#892BE2", darkred: "#8C0000", darkmagenta: "#8C008C", saddlebrown: "#8C4411", darkseagreen: "#8EBC8E", lightgreen: "#8EED8E", mediumpurple: "#9370D8", darkviolet: "#9300D3", palegreen: "#99F999", darkorchid: "#93C", yellowgreen: "#9C3", sienna: "#A0512D", brown: "#A52828", darkgrey: "#A8A8A8", darkgray: "#A8A8A8", lightblue: "#ADD8E5", greenyellow: "#ADFF2D", paleturquoise: "#AFEDED", lightsteelblue: "#AFC4DD", powderblue: "#AFE0E5", firebrick: "#B22121", darkgoldenrod: "#B7870A", mediumorchid: "#BA54D3", rosybrown: "#BC8E8E", darkkhaki: "#BCB76B", silver: "#BFBFBF", mediumvioletred: "#C61484", indianred: "#CC5B5B", peru: "#CC843F", chocolate: "#D1681E", tan: "#D1B58C", lightgrey: "#D3D3D3", lightgray: "#D3D3D3", palevioletred: "#D87093", thistle: "#D8BFD8", orchid: "#D870D6", goldenrod: "#D8A521", crimson: "#DB143D", gainsboro: "#DBDBDB", plum: "#DDA0DD", burlywood: "#DDB787", lightcyan: "#E0FFFF", lavender: "#E5E5F9", darksalmon: "#E8967A", violet: "#ED82ED", palegoldenrod: "#EDE8AA", lightcoral: "#EF7F7F", khaki: "#EFE58C", aliceblue: "#EFF7FF", honeydew: "#EFFFEF", azure: "#EFFFFF", sandybrown: "#F4A360", wheat: "#F4DDB2", beige: "#F4F4DB", whitesmoke: "#F4F4F4", mintcream: "#F4FFF9", ghostwhite: "#F7F7FF", salmon: "#F97F72", antiquewhite: "#F9EAD6", linen: "#F9EFE5", lightgoldenrodyellow: "#F9F9D1", oldlace: "#FCF4E5", red: "#F00", magenta: "#F0F", fuchsia: "#F0F", deeppink: "#FF1493", orangered: "#F40", tomato: "#FF6347", hotpink: "#FF68B5", coral: "#FF7F4F", darkorange: "#FF8C00", lightsalmon: "#FFA07A", orange: "#FFA500", lightpink: "#FFB5C1", pink: "#FFBFCC", gold: "#FFD600", peachpuff: "#FFD8BA", navajowhite: "#FFDDAD", moccasin: "#FFE2B5", bisque: "#FFE2C4", mistyrose: "#FFE2E0", blanchedalmond: "#FFEACC", papayawhip: "#FFEFD6", lavenderblush: "#FFEFF4", seashell: "#FFF4ED", cornsilk: "#FFF7DB", lemonchiffon: "#FFF9CC", floralwhite: "#FFF9EF", snow: "#FFF9F9", yellow: "#FF0", lightyellow: "#FFFFE0", ivory: "#FFFFEF", white: "#FFF" });
	for (var c in _colors) {
		igk_defineProperty(igk.system.colors, c, {
			get: (function (c) {
				return function () {
					return _colors[c];
				}
			})(c),
			configurable: false,
			enumerable: true
		});
	}
	createNS("igk.Number",{
		parseByte: function(i){
			if (i>255)
				return 255;
			if (i<0)
				return 0;
			return i;
		}
	});
	createNS("igk.system.color", {
		HSVtoColor: function (h, s, v) {
			// angle 
			var r, g, b;
			var c = v * s;
			var x = c * (1 - Math.abs(((h / 60.0) % 2) - 1));
			var m = v - c;
			if (h < 60) {
				r = c; g = x; b = 0;
			} else if (h < 120) {
				r = x; g = c; b = 0;
			}
			else if (h < 180) {
				r = 0; g = c; b = x;
			}
			else if (h < 240) {
				r = 0; g = x; b = c;
			}
			else if (h < 300) {
				r = x; g = 0; b = c;
			} else {
				r = c; g = 0; b = x;
			}
			return {
				r: igk.Number.parseByte(r * 255),
				g: igk.Number.parseByte(g * 255),
				b: igk.Number.parseByte(b * 255)
			};
		}
	});

	function _setall(t, s) {
		for (var i = 0; i < t.length; i++) {
			t[i] = s;
		}
	}
	createNS("igk.system.colors", {
		toFloatArray: function (n) { // convert expression color to float array of argb

			var c = 0;// igk.system.colors[n.toLowerCase()];
			if (igk.isInteger(n)) {

				// for number
				c = "#" + n.toString(16);
			} else {
				c = igk.system.colors[n.toLowerCase()];
			}
			var t = new Float32Array(4);
			if (c) {
				if (c[0] == "#") {
					var s = c.substring(1);
					switch (s.length) {
						case 1:
							_setall(t, parseInt((s + s), 16) / 255.0);
							break;
						case 3:
							t[0] = 1.0;
							t[1] = parseInt((s[0] + s[0]), 16) / 255.0;
							t[2] = parseInt((s[1] + s[1]), 16) / 255.0;
							t[3] = parseInt((s[2] + s[2]), 16) / 255.0;
							break;
						case 4:
							t[0] = parseInt((s[0] + s[0]), 16) / 255.0;
							t[1] = parseInt((s[1] + s[1]), 16) / 255.0;
							t[2] = parseInt((s[2] + s[2]), 16) / 255.0;
							t[3] = parseInt((s[3] + s[3]), 16) / 255.0;
							break;
						case 6:
							t[0] = 1.0;
							t[1] = parseInt((s[0] + s[1]), 16) / 255.0;
							t[2] = parseInt((s[2] + s[3]), 16) / 255.0;
							t[3] = parseInt((s[4] + s[5]), 16) / 255.0;
							break;
						case 8:
							t[0] = parseInt((s[0] + s[1]), 16) / 255.0;
							t[1] = parseInt((s[2] + s[3]), 16) / 255.0;
							t[2] = parseInt((s[4] + s[5]), 16) / 255.0;
							t[3] = parseInt((s[6] + s[7]), 16) / 255.0;
							break;

					}
				}
			}

			return {
				a: t[0], r: t[1], g: t[2], b: t[3]
			};

		}
	});

	createNS("igk.uri", {
		addquery: function (s, t) {
			if (s.length > 0)
				s += "&" + t;
			else
				s += "?" + t;
			return s;
		},
		getquery: function (ctrlname, funcName, params) {
			var s = "";
			if (ctrlname)
				s = igk.uri.addquery(s, "c=" + ctrlname);
			if (funcName)
				s = igk.uri.addquery(s, "f=" + funcName);
			if (params)
				s = igk.uri.addquery(s, params);
			return s;
		}
	});

	createNS("igk.char", {
		isKeyCodeNumber: function (keyCode) {
			return (keyCode >= 46) && (keyCode < 69);
		},
		isChar: function (key) {
			var t = /^[\w\d]$/.exec(key, true);
			return (t != null);
		},
		isControl: function (keyCode) {
			return (keyCode <= 40);
		}
	});

	createNS("igk.datetime", {// date time utility function
		timeToMs: function (d) {
			if (typeof (d) == 'string') {
				d = igk.system.string.trim(d);
				if (igk.system.string.endWith(d, 's')) {
					return parseInt(d.substr(0, d.length - 1) * 1000);
				}
				return parseInt(d) + "ms";
			}
			if (typeof (d) == 'number') {
				return parseInt(d) + "ms";
			}
			return 0;
		}
	});
	function __igk_get_activeXDocument() {
		var progIDs = [
			"Msxml2.DOMDocument.6.0",
			"Msxml2.DOMDocument.5.0",
			"Msxml2.DOMDocument.4.0",
			"Msxml2.DOMDocument.3.0",
			"MSXML2.DOMDocument",
			"MSXML.DOMDocument",
			"htmlfile"
		];
		for (var i = 0; i < progIDs.length; i++) {
			try {
				return new ActiveXObject(progIDs[i]);
			} catch (e) { };
		}
		return null;
	};
	// static return a new active igk x document
	function __new_activeDocumentObject() {
		var d = __igk_get_activeXDocument();
		if (!d) return null;

		return new (function (d) {
			var m_d = d;
			// encapsulate propertye of created active document to be exposed
			igk_defineProperty(this, "async", {
				get: function () { return m_d.async; },
				set: function (v) { m_d.async = v; }
			});
			igk_defineProperty(this, "readyState", {
				get: function () { return m_d.readyState; }
			});
			igk_defineProperty(this, "firstChild", {
				get: function () { return m_d; }
			});


			igk_appendProp(this, {
				load: function (f) {
					return m_d.load(f);
				},
				loadXML: function (s) {
					return m_d.loadXML(s);
				},
				onreadystatechange: null,
				addEventListener: function (method, func, capture) {
					m_d["on" + method] = func;
				},
				removeEventListener: function (method, func) {
					m_d["on" + method] = null;
				},
				toString: function () { return "igk:// activeXDocument"; }
			});
		}
		)(d);


	}

	function __dom_get_root(e) {
		var r = null; 
		$igk(e).select(">>").each(function () {
			if (this.o.nodeType == 1) {
				r = this.o;
				return false;
			}
			return true;
		});
		return r;
	};

	function __replace_xml_doc(d, txt) {
		var r = igk.dom.loadXML(txt);
		if (r) {
			d.replaceChild(r, d.documentElement);
		}
	};
	var __html = 0;
	var __body = 0;
	createNS("igk.dom", { // dom utilities
		body: function () {
			if (__body && __body.parentNode) {
				return __body;
			}
			__body = $igk(document).select('body').first();
			if (!__body)
				throw '[BJS] - body not yet loaded';
			return __body;
		},
		html: function () {
			if (!__html) {
				var c = document.getElementsByTagName('html');
				if (c.length > 0)
					__html = $igk(c[0]);
			}
			return __html;
		},
		replaceTagWith: function (q, tag) {
			// @q : node
			// @tag: the new tag
			var d = igk.createNode(tag);
			d.setHtml(q.o.innerHTML);
			igk.dom.copyAttributes(q.o, d.o);
			igk.dom.replaceChild(q.o, d.o);
			return d;
		},
		rectContains: function (domRect, x, y) {
			// determine if domRect contains cursor location
			return ((x >= domRect.x) && (x <= domRect.right)) && ((y >= domRect.y) && (y <= domRect.bottom));
		},
		createXMLDocument: function (tagName) {
			if (igk.navigator.$ActiveXObject()) {
				return __igk_get_activeXDocument();
			}
			return igk.dom.createDocument(tagName || "xml");
		},
		activeXDocument: function () {
			return __new_activeDocumentObject();
		},
		createDocument: function (t, ns) {
			t = t || "xml";
			var d = null;
			var m_cb = null;
			var cs = 1;
			ns = ns || "http://www.w3.org/1999/xhtml";


			if (document.implementation) {
				// if(typeof(document.implementation.createHTMLDocument) !=igk.constants.undef){
				// d=document.implementation.createHTMLDocument('');
				// }else 

				if (typeof (document.implementation.createDocument) != igk.constants.undef) {
					// in ie createDocument create an XMLDocument object
					d = document.implementation.createDocument(null, t, null);

				}
			} else {

				if (igk.navigator.$ActiveXObject()) {

					d = __new_activeDocumentObject(); // new ActiveXObject("htmlfile");
					cs = 0; // disable the register event location

				}
			}

			if (d) {
				if (cs && !d.load) {

					d.load = function (l, callback) {
						m_cb = callback;
						var fc = d.async ? function (xhr) {
							// dispatch ready state
							var e = document.createEvent("HTMLEvents");// custom ready state
							e.initEvent("readystatechange", false, false);
							e.readyState = xhr.readyState;
							e.xhr = xhr;

							if ((xhr.readyState == 4) && (xhr.status == 200)) {
								__replace_xml_doc(d, xhr.responseText);
								if (m_cb)
									m_cb();
							}
							d.dispatchEvent(e);
						} : null;
						var g = igk.ajx.get(l, null, fc, d.async, false, {
							//"ajx.xhr" :{responseType:"msxml-document"}
						});

						if (!d.async) {
							__replace_xml_doc(d, g.xhr.responseText);
						}
						return d;
					};
					d.async = 1;
				}
				else if (d.load && d.async) {

					var bfc = d.load;
					// replace load function					
					d.load = function (l, callback) {
						m_cb = callback;
						bfc.apply(this, [l]);
						return d;
					};
					// __igk(d).reg_event("load",function(){

					// });
					__igk(d).reg_event("readystatechange", function () {
						if (m_cb && ((d.readyState == "complete") || (d.readyState == 4))) {
							m_cb();
						}
					});
				}
			}
			return d;
		},
		getPropertiesTab: function (t) {
			var o = [];
			for (var i in t) {
				if (typeof (t[i]) == "function")
					continue;
				o[i] = t[i];
			}
			return o;
		},
		compare: function (tab, t) {
			var o = [];
			var c = {};
			for (var i in t) {
				if (typeof (t[i]) == "function")
					continue;
				o[i] = t[i];

				if (tab[i] != o[i])
					c[i] = o[i];
			}
			return c;
		},
		replaceChild: function (target, node) {// replace the "target" node with the requested "node"
			if (target)
				target.parentNode.replaceChild(node, target);
		},
		copyAttributes: function (f, n, a) {// copy attribute
			// f from node
			// n to node
			// a ignore attribute
			if ((f == null) || (n == null) || (!n.hasAttributes))
				return;

			var j = "";
			for (var i = 0; i < f.attributes.length; i++) {
				j = f.attributes[i];
				try {
					if ((a != null) && (a[j.name] == 1))
						continue;
					n.setAttribute(j.name, j.value);
				}
				catch (Exception) {

				}
			}

		}
		, transformXSL: function (xml, xsl) {
			if (!xml || !xsl)
				return null;
			var ex = null;
			if (document.implementation && document.implementation.createDocument && (typeof (XSLTProcessor) != igk.constants.undef)) {
				// navigator: sf,ch,fi
				var xsltProcessor = new XSLTProcessor();
				xsltProcessor.importStylesheet(xsl);
				ex = xsltProcessor.transformToFragment(xml, document);



				if (ex) {
					// get root node
					ex = __dom_get_root(ex);
				}

			} else if (igk.navigator.$ActiveXObject() || (xml.responseType == "msxml-document")) {

				if (xml.responseType == "msxml-document") {
					ex = xml.transformNode(xsl);
				} else {

					var srcTree = new ActiveXObject("Msxml2.DOMDocument.6.0");
					srcTree.async = false;
					// You can substitute other XML file names here.



					// srcTree.documentElement=xml.documentElement;


					srcTree.loadXML($igk(xml.documentElement).getOuterHtml());


					if (srcTree.parseError.errorCode != 0)
						console.debug("srcTree : error code " + srcTree.parseError.errorCode + " : " + srcTree.parseError.reason);



					var xsltTree = new ActiveXObject("Msxml2.DOMDocument.6.0");
					xsltTree.async = false;
					xsltTree.validateOnParse = false;
					xsltTree.loadXML($igk(xsl.documentElement).getOuterHtml());
					if (xsltTree.parseError.errorCode != 0)
						console.debug("xsl : error code " + xsltTree.parseError.errorCode + " : " + xsltTree.parseError.reason);

					ex = igk.dom.loadXML(srcTree.transformNode(xsltTree));

				}
			}

			return ex;
		}
		, transformXSLString: function (sxml, sxsl) {
			// return the document 
			var ex = null;
			if (igk.navigator.$ActiveXObject()) {

				var srcTree = new ActiveXObject("Msxml2.DOMDocument.6.0");
				srcTree.async = false;
				srcTree.loadXML(sxml);
				if (srcTree.parseError.errorCode != 0) {
					console.debug("srcTree : error code " + srcTree.parseError.errorCode + " : " + srcTree.parseError.reason);
					return 0;
				}

				var xsltTree = new ActiveXObject("Msxml2.DOMDocument.6.0");
				xsltTree.async = false;
				xsltTree.validateOnParse = false;
				xsltTree.loadXML(sxsl);

				// xsltTree.replaceChild(xsl.documentElement,xsltTree.xml);
				if (xsltTree.parseError.errorCode != 0) {
					console.debug("xsl : error code " + xsltTree.parseError.errorCode + " : " + xsltTree.parseError.reason);
					return 0;
				}



				ex = igk.dom.loadXML(srcTree.transformNode(xsltTree));
			}
			else {

				if (document.implementation && document.implementation.createDocument) {
					var xsltProcessor = new XSLTProcessor();
					// load xml 
					var xsl = igk.dom.loadXML(sxsl);
					var xml = igk.dom.loadXML(sxml);
					if (xsl && xml) {

						var dsl = document.implementation.createDocument(null, 'xml', null);// msxml-document');
						// 	  alert(dsl.documentElement);
						dsl.replaceChild(xsl, dsl.documentElement);
						var dxl = document.implementation.createDocument(null, 'xml', null);// msxml-document');
						dxl.replaceChild(xml, dxl.documentElement);

						// xsltProcessor.importStylesheet(dsl);
						// ex=xsltProcessor.transformToFragment(dxl,document);

						xsltProcessor.importStylesheet(dsl);
						ex = xsltProcessor.transformToFragment(dxl, document);



						// if(ex){
						// get root node


						// ex=__dom_get_root(ex);
						// }
					}
				}
			}
			return ex;
		}
		, transformXSLUri: function (u_xml, u_xsl, callback) {
			var doc = igk.dom.createDocument();
			var ex = null;
			if (callback) {
				// use async strategie
				doc.async = true;
				doc.load && doc.load(u_xml,
					function (evt) {

						var doc2 = igk.dom.createDocument();
						doc2.async = false;
						doc2.validateOnParse = false;
						var xsl = doc2.load(u_xsl);
						var _ch = igk.navigator.isChrome() || igk.navigator.isSafari();






						if (_ch) {
							var dh = null;
							// correct html tag tag because ignore by those navigator implementation
							dh = doc2.getElementsByTagName("html");
							if (dh.length == 1) {
								$igk(dh[0]).replaceTagWith("igk-html");
							}
						}

						ex = igk.dom.transformXSL(doc, xsl);

						if (_ch && (ex.tagName.toLowerCase() == "igk-html")) {
							ex = $igk(ex).replaceTagWith("html").o;

							// igk.DEBUG=1;
							// order is important
							$igk(ex).select(" igk-th,igk-td,igk-tr,igk-table").each(function () {
								this.replaceTagWith(this.o.tagName.substring(4).toLowerCase());
								return true;
							});
							// igk.DEBUG=0;
						}

						ex && callback(ex);
					}
				);
			}
			else {

				doc.async = false;
				doc.load && doc.load(u_xml,
					function (evt) {
						var doc2 = igk.dom.createDocument();
						doc2.async = false;
						var xsl = doc2.load(u_xsl);

						ex = igk.dom.transformXSL(doc, xsl);

					}
				);
				return ex;
			}
		}
		, loadXML: function (s) {
			var r = null;
			var _ch = igk.navigator.isChrome() || igk.navigator.isSafari();
			if (_ch) {
				s = s.replace(/(\<(\/)?)(html|table|th|tr|td)( [^>]+)?>/gi, '$1igk-$3$4>', s);
			}

			if ("DOMParser" in window) {
				var g = (new window.DOMParser()).parseFromString(s, "text/xml");
				r = __dom_get_root(g);
				if (r && r.tagName.toLowerCase() == "parsererror") {
					return null;
				}
			}
			else {
				// TODO
				r = igk.dom.activeXDocument();
				r.load(s);
			}
			return r;
		}
	});

	(function () {

		var m_xsltransform = [];// array of good transformation


		createNS("igk.dom.xslt", {
			initTransform: function () {
				var p = $igk(igk.getParentScript());
				if (!p) {
					return;
				}
				var _s_ns = igk.system.string;
				var _x = p.select('.xml').first();
				var _y = p.select('.xslt').first();
				var _g = 0;
				if (_x && _y) {
					var _sxml = _s_ns.rmComment(_x.getHtml());
					// .substr(4) ; // remove comment
					// _sxml = _sxml.substring(0, _sxml.length-3);
					var _sxsl = _s_ns.rmComment(_y.getHtml());
					// .substr(4) ; // remove comment
					// _sxsl = _sxsl.substring(0, _sxsl.length-3);
					var _dxsl = _sxsl; // copy data
					var opts = igk.JSON.parse(_y.getAttribute("xslt:data"));
					var rgx = 0;
					if (opts) {

						for (var s in opts) {
							rgx = new RegExp("%" + s + "%", "g");
							_dxsl = _dxsl.replace(rgx, opts[s]);
						}

					}



					try {
						_g = igk.dom.transformXSLString(_sxml, _dxsl);
						_y.remove();
						if (_g)
							p.o.replaceChild(_g, _x.o);
						var _callback = 0;
						var o = {
							xml: _sxml,
							xsl: _sxsl,
							type: "xmltransform",
							idx: m_xsltransform.length,
							options: opts,
							initResult: function (callback) {
								callback(p);
								_callback = callback;
							},
							reload: function (opts) {
								console.debug("reload");
								console.debug(opts);
								var c = this.idx;
								var _dxsl = _sxsl;
								if (opts) {
									for (var s in opts) {
										rgx = new RegExp("%" + s + "%", "g");
										_dxsl = _dxsl.replace(rgx, opts[s]);
									}
								}
								var _tg = igk.dom.transformXSLString(_sxml, _dxsl);
								// p.o.replaceChild(_tg, _g);
								p.setHtml('').add(_tg);// .o.replaceChild(_tg, _g);
								_g = _tg;
								_callback(p);
							}
						};
						m_xsltransform.push(o);
						return o;
					}
					catch (ex) {
						console.error(ex);
					}

				}
			}
		});

		igk.appendProperties(igk.dom.xslt.initTransform, {
			lastTransform: function () {
				if (m_xsltransform.length > 0)
					return m_xsltransform[m_xsltransform.length - 1];
				return null;
			}
		});

	})();

	(function () {
		var source;

		createNS("igk.JSON", {
			init_data: function (src, str, fallback) {
				var _v = igk.JSON.parse(str);
				if ((_v != null) && (typeof (_v) == "object")) {
					for (var i in src) {
						if (typeof (_v[i]) != igk.constants.undef) {
							src[i] = _v[i];
						}
					}
				} else {
					if (fallback) {
						fallback(src);
					}
				}
				return src;

			},
			// used to parse attribute property
			parse: function (js, target) {
				var q = null;
				source = target;

				try {
					if ((js != null) && (typeof (js) == "string"))
						q = eval('(' + js + ')');
				}
				catch (ex) {
					q = js;
				}
				this.target = null;
				source = null;
				return q;
			}
			, convertToString: function (jsonobj) {// convert json object to string
				if (typeof (JSON) != 'undefined' && JSON.stringify) {
					return JSON.stringify(jsonobj);
				}

				var r = "{";
				var k = 0;
				var prop = [];

				for (var i in jsonobj) {
					if ((typeof (jsonobj[i]) == IGK_FUNC) ||
						(typeof (jsonobj[i]) == IGK_UNDEF)
					) {
						continue;
					}
					if (k > 0)
						r += ",";

					if (typeof (jsonobj[i]) == "object") {
						// treat data
						prop.push(jsonobj[i]);
						r += "\"" + i + "\": ";
					}
					else
						r += "\"" + i + "\":" + jsonobj[i];

					k++;
				}
				r += "}";
				return r;
			}
			, setSource: function (src) {
				source = src;
			}


		});

	})();

	function igk_form_geturi(frm) {// get the ajx form uri. or action
		var u = $igk(frm).getAttribute("igk-ajx-form-uri");
		if (u)
			return u;
		return frm["action"];
	};
	function igk_form_ajx_getfunc(frm) {
		var s = $igk(frm).getAttribute("igk-ajx-form-target");
		var q = null;
		if (s) {
			q = $igk(s).first();
		}

		if (q && !q.isSr()) {
			return igk.ajx.fn.replace_content(q.o);
		}
		// else{
		// igk.winui.notify.showErrorInfo("Error ","Item not found : "+s + " | "+q);

		// }
		return null;
	};
	function igk_form_submit(frm) {
		if (frm) {
			var ajxform = $igk(frm).getAttribute("igk-ajx-form");
			if (ajxform) {
				 ns_igk.ajx.postform(frm, igk_form_geturi(frm), igk_form_ajx_getfunc(frm));
			}
			else {
				frm.submit();
			}
		}
	}
	createNS("igk.form", {
		keypress_validate: function (i, event) {
			var frm = null;
			if (event.keyCode == 13) {
				event.preventDefault();

				frm = window.igk.getParentByTagName(i, 'form');

				igk_form_submit(frm);

				return !1;
			}
			return !0;
		},
		submitonclick: function (l) {
			var q = window.igk.getParentByTagName(l, 'form');
			if (q) {
				igk_form_submit(q);
				return !1;
			}
			return !0;
		},
		confirmLink: function (e, msg) {
			var f = igk.getParentByTagName(e, 'form');
			if (f) {
				if (confirm(msg)) {
					f.action = '' + e.href; f.confirm.value = 1;
					f.submit();
				}
			}
		},
		updateTarget: function (form, cibling) {
			// update the cibling object
			// >form : source form 
			// >cibling : object to update properties
			if (!cibling || !form)
				return;
			var q = form;
			for (var i in cibling) {
				if (typeof (q[i]) != 'undefined' && (q[i].type != 'undefined')) {
					switch (q[i].type) {
						case "checkbox":
						case "radio":
							if (q[i].checked)
								cibling[i] = q[i].value;
							else
								cibling[i] = '';
							break;
						default:
							cibling[i] = q[i].value;
							break;
					}
				}
			}
		},
		posturi: function (uri, method) {
			var frm = igk.createNode("form");
			frm.setCss({ "display": "none" });
			igk.dom.body().appendChild(frm.o);
			frm.o.method = method ? method : "POST";
			frm.o.action = uri;
			frm.o.submit();
		},
		form_tojson: function (form) {
			if (!form)
				return null;
			var p = [];
			var e = null;
			var k = "";
			for (var i = 0; i < form.length; i++) {

				e = form.elements[i];
				k = e.id;
				if (!k)
					continue;
				if (igk.system.string.endWith(k, "[]")) {
					k = igk.system.string.remove(k, k.length - 2, 2);
					if (typeof (p[k]) == "undefined") {
						p[k] = [];
					}
				}
				switch (e.type) {
					case "radio":
					case "checkbox":
						var vv = e.value;
						if (!e.checked) {
							vv = "";// empty value
						}
						if (p[k]) {
							var m = p[k];
							if (typeof (m) != "string") {
								m.push(vv);
								p[k] = m;
							}
							else {
								var t = [];
								t.push(m);
								t.push(vv);
								p[k] = t;
							}
						}
						else
							p[k] = vv;

						break;
					case "file":// continue
						break;
					default:
						if (p[k]) {
							var m = p[k];
							if (typeof (m) != "string") {
								m.push(e.value);
								p[k] = m;
							}
							else {
								var t = [];
								t.push(m);
								t.push(e.value);
								p[k] = t;
							}
						}
						else {
							p[k] = e.value;
						}
						break;
				}
			}
			e = 0;
			var msg = "";
			for (var i in p) {
				if (e != 0)
					msg += ",";
				if (p[i].push)
					msg += i + ":[" + p[i] + "]";
				else
					msg += i + ":" + p[i];
				e = 1;
			}
			return "{" + msg + "}";
		}

		, parse: function (frm) {
			var m = "";
			m = igk_get_form_posturi(frm);
			return m;
		}

	}

	);

	// method used for ajx form control
	createNS("igk.form.ajxform", {
		submit: function (frm) {
			// var q = $igk(frm).getAttribute("igk:target");
			ns_igk.ajx.postform(frm, igk_form_geturi(frm), function (xhr) {
				if (this.isReady()) {
					var v = $igk(frm).select(".response").first();
					if (v) {
						igk.ajx.fn.replace_content(v.o);
					}
					else {
						igk.ajx.fn.replace_or_append_to_body.apply(this, [xhr]);
						// igk.winui.notify.showMsBox("ajx form response not found ", this.xhr.responseText);
					}
				}
			});
		}
	});


	// web utility functions
	createNS("igk.web", {
		setcookies: function (name, value, exdays) {

			var exdate = new Date();
			if (exdays)
				exdate.setDate(exdate.getDate() + exdays);
			var c_value = escape(value) + ((exdays == null) ? "" : "; expires=" + exdate.toUTCString());
			document.cookie = name + "=" + c_value;
		},
		getcookies: function (name) {
			var c_value = document.cookie;
			var c_start = c_value.indexOf(" " + name + "=");
			if (c_start == -1) {
				c_start = c_value.indexOf(name + "=");
			}
			if (c_start == -1) {
				c_value = null;
			}
			else {
				c_start = c_value.indexOf("=", c_start) + 1;
				var c_end = c_value.indexOf(";", c_start);
				if (c_end == -1) {
					c_end = c_value.length;
				}
				c_value = unescape(c_value.substring(c_start, c_end));
			}
			return c_value;
		},
		clearcookies: function () {
			document.cookie = null;
		},
		rmcookies: function (name) {
			var s = document.cookie + "";
			var i = s.indexOf(name);
			if (i >= 0) {
				document.cookie = name + "=;expires=-10";
			}
		}

	});
	// web utility functions
	createNS("igk.web.storage", {
		get: function (k, v) {
			if (window.sessionStorage) {
				return window.sessionStorage[k] || v;
			}
		},
		set: function (k, v) {
			if (window.sessionStorage) {
				window.sessionStorage[k] = v;
			}
		},
		getLocal: function (k, v) {
			if (window.localStorage) {
				return window.localStorage[k] || v;
			}
		},
		storeLocal: function (k, v) {
			if (window.localStorage) {
				window.localStorage[k] = v;
			}
		}
	});




	(function () {// init system
		if (typeof (Event) == IGK_UNDEF) {
			Event = {
				prototype: {
				},
				toString: function () {
					return "[object igk.event]";
				}
			};
		}
		if ((typeof (Event.prototype.preventDefault) == IGK_UNDEF)) {
			Event.prototype.preventDefault = function () {
			};
		}
		return !0;
	})();


	var m_currentModule = null; // current module name
	var m_module_info = null; // current module info
	var m_module_loaded = {}; // module loaded execution
	var m_moduleList = {}; // module info flist
	function igk_get_trace_info(_m, e, c) {
		if (e.stack) {
			// FIREFOX 
			var s = e.stack + "";
			var t = s.trim().split("\n");





			// var tt=t[c];
			// get chain list
			var li = [];
			// FIREFOX | chrome | and ie nor render the stack at the same
			// var regex=new RegExp("[^(\@]*((ftp|http|https):// [^:]+)");
			var regex = new RegExp("((ftp|http|https)://([^:]+:[0-9]+(/([^:]+))?|[^:]+))");

			for (var i = 0; i < t.length; i++) {
				if (regex.test(t[i])) {
					li.push(t[i]);

				}
			}
			c = c || li.length - 1;
			var tt = li[c];
			if (tt) {
				var uri = regex.exec(tt)[0];


				_m.module_src = uri;// module start source
				_m.dir = uri.substring(0, uri.lastIndexOf("/"));
			}
			_m.stack = e.stack;
			_m.stack_list = li;


		} else {
			// can't get stack in strick mode or SAFARI < 6
			var src = igk_getScriptSrc();
			if (src != null) {
				_m.modedule_src = src;// tt.substring(0,tt.lastIndexOf("/"));
				_m.dir = src.substring(0, src.lastIndexOf("/"));
			} else {
				console.error("/!\\ cant get module path" + igk_getScriptSrc());
				return 0;
			}

		}
		return 1;
	}
	// define the a module script. set the root module
	function igk_defineModule(n) {
		m_currentModule = n;

		//try {
		var e = new Error();

		//catch (e) {
		// get stack info
		var _m = {
			name: n,
			module_src: null,
			dir: null
		};

		igk.error.lastError = e;
		if (igk_get_trace_info(_m, e, 1)) {
			m_module_info = _m;
			m_moduleList[n] = _m;
		} else {
			m_module_info = null;
		}

		//}
		return _m;
	};
	function igk_getModule(n) {
		n = n || m_currentModule;
		return m_moduleList[n];
	};
	function igk_getScriptSrc() {
		// get current script declaration file
		var g = document.scripts;
		if (g) {
			return g[g.length - 1].src;
		}
		return null;

	};

	m_LibScript = igk_getScriptSrc();

	function createObject(T, t, args) {
		for (var j in T.prototype) {
			t[j] = T.prototype[j];
		}
		T.apply(t, args);

	};

	createNS("igk.system", {// system global management namespace
		createExtensionProperty: function (p, ns, obj) {

			var prop = ns;
			var _n = igk_get_namespace(prop);
			if (typeof (_n) == "undefined") {
				_n = createNS(prop, {});
			}
			if (typeof (_n[p]) == "undefined") {
				var ob = new __extensionPrototype(p);
				igk_defineProperty(_n, p, {
					get: function () {
						return ob;
					}
				});
			}
			if (obj)
				Object.setPrototypeOf(obj, _n[p]);
			return _n[p];
		},
		stringProperties: function (o) {
			// stringify properties
			var m = "";
			var k = 0;
			for (var i in o) {
				if (typeof (o[i]) == "function") continue;
				if (k == 1)
					m += ",";
				m += i + ":" + o[i];
				k = 1;
			}
			return m;
		},
		createNS: createNS,
		createPNS: createPNS,
		getScriptSrc: igk_getScriptSrc,
		module: igk_defineModule,
		isClass: function (o, t, opts) {
			// o: object
			// t: true to raise error
			// opts: options object with [msg] properties
			var r = o instanceof igk_namespace;
			if (t)
				throw opts.msg;
			return r;
		},
		isInherit: function (o, A) {
			if (!o || (typeof (o) != 'object'))
				return false;
			var c = Object.getPrototypeOf(o);
			while (c) {
				if (c == A) {
					return true;
				}
				c = c.prototype;

				if (c == Object)
					break;
			}
			return false;
		},
		createClass: function (ns, tn, constructor, opts) {
			if (!igk.system.class) {
				igk.system.class = function () {
				};
			}
			if (!constructor) {
				console.error("createClass constructor required");
				return null;

			}


			// var tn = '';
			var p = null;
			if (typeof (tn) == 'object') {
				var g = tn;
				tn = g.name;
				p = g.parent;

				if (('parent' in g) && !igk.isDefine(p)) {
					throw ("[igk] - parent not found for " + g.name + " " + p);
				}
			}




			var fc = 0;
			fc = function () {
				if (this == ns) {
					throw (opts ? opts.msg : null) || igk.R.gets("<< new >> operator is require");
				}
				if (p) { //invoke parent constructor
					createObject(p, this, arguments);
				}
				constructor.apply(this, arguments);

				//bind properties that apply to 

				igk.appendProperties(this, {
					getTypeFullName: function () {
						return ns.fullname + "." + tn;
					},
					getType: function () {
						return tn;
					},
					isInstanceOf: function (t) {
						return igk.system.isInherit(this, t);
					}
				});

				// this.getTypeFullName = function () {
				// return ns.fullname + "." + tn;
				// };
				// this.isInstanceOf = function (t) {
				// return this instanceof t;
				// };
			};
			fc.Name = tn;
			//igk extra info
			fc.__source__ = 'class';
			fc.__name__ = tn;
			igk.defineProperty(fc, 'fullname', {
				get: function () {
					return ns.fullname + '.' + tn;
				}
			});
			fc.__fullname__ = ns.fullname + '.' + tn;
			// [x].data = "base";







			// igk.appendProperties(fc.prototype, {
			// getTypeFullName: function () {
			// return ns.fullname + "." + tn;
			// },
			// getType:function(){
			// return tn;
			// },
			// isInstanceOf: function (t) {
			// return this instanceof t;
			// }
			// });

			if (typeof (p) == 'string') {
				var r = igk.system.getNS(p);
				if (r == null) {


					var bfc = fc;

					fc = function () {
						var k = igk.system.getNS(p);
						if (k) {
							bfc.prototype = k;
							ns[tn] = bfc;
						} else {
							throw ("not found : " + p);
						}
						p = k;
						return new bfc();
					};
				} else {
					p = r;
					fc.prototype = p;
				}
			} else {
				if (p) {
					//fc.__igk__prototype = p;
					// fc.prototype = p.prototype;
					fc.prototype = p;
				}
			}
			igk.system.createNS(ns.fullname, {
				tn: fc
			});
			igk.defineProperty(fc, 'name', { get: function () { return tn; } });
			ns[tn] = fc;
			return fc;
		},
		createClassList: function (ns, e) {
			if (!ns)
				return 0;
			var g = 0;
			for (var i in e) {
				if (typeof (g = e[i]) == "function") {
					ns[i] = igk.system.createClass(ns, i, g);
					// ns[i].__source__ = "class";
				}
			}
		},
		getClassList: function (ns) {
			if (typeof (ns) == 'string')
				ns = igk.system.getNS(ns);
			var tab = [];
			for (var s in ns) {
				if (typeof (ns[s]) == 'function' && (ts = ns[s].__source__) && (ts == 'class')) {
					tab.push({ name: s, ns: ns.fullname, func: ns[s] });
				}
			}
			return tab;
		},
		getBindFunctions: function (n) {
			var r = {};
			if (typeof (n) == 'object') {
				for (var i in n) {
					if (typeof (n[i]) == 'function')
						r[i] = n[i].bind(n);
				}
			}
			return r;
		},
		evalScript: function (item) {
			if (item == null) return; var t = item.getElementsByTagName("script");
			for (var i = 0; i < t.length; i++) {
				igk.eval(t[i].innerHTML, t[i].parentNode, t[i]);
			}
		},
		toString: function () { return "igk.system"; },
		getNS: igk_get_namespace,
		require: function (n) {
			var t = igk_get_namespace(n);
			if (!t)
				throw new Error('namespace ' + n + ' not found');
			return t;
		},
		include: function (u) {
			var src = null;
			// include script in expression
			igk.ajx.post(u, null, function (xhr) {
				if (this.isReady()) {
					src = xhr.responseText;
					if (src)
						eval(src);
				}
			});
		}
	});

	createNS("igk.system.diagnostic", {
		traceinfo: igk_get_trace_info, // module, error, level
		debug: function (m) {
			console.debug(m);
		},
		assert: function (c, m) {
			if (c)
				console.assert(c, m);
		}
	});



	function _s(t) {
		if ((t + "").length <= 1)
			return "0" + t;
		return t;
	};

	// igk.system.color management
	(function () {
		var _cnode = 0; //convertter node with css
		var S = igk.system;
		var _CNS = igk.system.color || {};
		function __ColorClass(r, g, b, a) {// .ctrl color 
				this.r = r;
				this.g = g;
				this.b = b;
				this.a = a ? a : 1.0;
				igk.appendProperties(this, {
					toString: function () {
						var r = this.r;
						var g = this.g;
						var b = this.b;
						var a = this.a;
						if (a == 1.0)
							return "rgb(" + r + "," + g + "," + b + ")";
						return "rgba(" + r + "," + g + "," + b + "," + a + ")";
					},
					toHtml: function () {   // var decColor=b +(256 * g) +(65536 * r);
						var r = this.r;
						var g = this.g;
						var b = this.b;
						var a = this.a;
						var decColor = (r << 16) + (g << 8) + b;
						if (decColor == 0)
							if (this.a == 0)
								return "#00000000";
							else
								return "#000";
						return "#" + decColor.toString(16).padStart(6, '0');
					},
					toColorf: function (p) {
						var _r = Math.round;
						p = Math.pow(100, (p || 5));
						return {
							a: 1.0,
							r: _r((this.r / 255.0) * p) / p,
							g: _r((this.g / 255.0) * p) / p,
							b: _r((this.b / 255.0) * p) / p
						};
					},
					toInt: function () { return (this.r << 16) + (this.g << 8) + this.b; },
					getLuminance: function () {
						var r, g, b, cl = this;
						r = cl.r;
						g = cl.g;
						b = cl.b;
						return ((r * 299) + (g * 587) + (b * 114)) / (255 * 1000.0);
					}
				});

		}; 
		igk.appendProperties(__ColorClass, _CNS); 

		igk.appendProperties(igk.system, {
			color: __ColorClass,
			colorGetA: function (v) {
				if (!v)
					return 0;
				if (typeof (v) == 'string') {
					if (v.toLowerCase() == "transparent") {
						return 0;
					}
					if (/^#/.exec(v)) {
						v = v.substring(1);
						switch (v.length) {
							case 8:
								var result = /^([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(v);

								return Math.round(parseInt(result[1], 16) * 100 / 255) / 100;
								break;
							case 4:
								var result = /^([a-f\d]{1})([a-f\d]{1})([a-f\d]{1})([a-f\d]{1})$/i.exec(v);
								return Math.round(parseInt(result[1] + result[1], 16) * 100 / 255) / 100;
								break;
						}
					}
				}
				return 1;

			},
			colorFromString: function (value) {
				// @@@ static function get color from string
				var r = 0;
				var g = 0;
				var b = 0;
				var a = 1.0;
				if (value) {
					var t = /rgb\(([^\)])+\)/.exec(value, "i");
					if (t) {
						var rgb = value.substring(4, value.length - 1)
							.replace(/ /g, '')
							.split(',');
						r = parseInt(rgb[0]);
						g = parseInt(rgb[1]);
						b = parseInt(rgb[2]);
					} else if ((t = /rgba\(([^\)])+\)/.exec(value, "i"))) {
						var rgb = value.substring(5, value.length - 1)
							.replace(/ /g, '')
							.split(',');
						r = parseInt(rgb[0]);
						g = parseInt(rgb[1]);
						b = parseInt(rgb[2]);
						a = parseFloat(rgb[3]);
					}
					else if (/^#/.exec(value)) {
						value = value.substring(1);
						switch (value.length) {
							case 6:
								var result = /^([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(value);
								r = parseInt(result[1], 16);
								g = parseInt(result[2], 16);
								b = parseInt(result[3], 16);
								break;
							case 8:
								var result = /^([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(value);
								//#FF00FF00 = aaRRGGBB
								a = parseInt(result[1], 16) / 255.0;
								r = parseInt(result[2], 16);
								g = parseInt(result[3], 16);
								b = parseInt(result[4], 16);
								break;
							case 4:
								var result = /^([a-f\d]{1})([a-f\d]{1})([a-f\d]{1})([a-f\d]{1})$/i.exec(value);
								//#F000 = aRGB
								a = parseInt(result[1] + result[1], 16) / 255.0;
								r = parseInt(result[2] + result[2], 16);
								g = parseInt(result[3] + result[3], 16);
								b = parseInt(result[4] + result[4], 16);
								break;
							case 3:
								var result = /^([a-f\d]{1})([a-f\d]{1})([a-f\d]{1})$/i.exec(value);
								r = parseInt(result[1] + result[1], 16);
								g = parseInt(result[2] + result[2], 16);
								b = parseInt(result[3] + result[3], 16);
								break;
						}
					}
					else {
						if (igk.system.color[value.toLowerCase()]) {
							return igk.system.colorFromString(igk.color[value.toLowerCase()]);
						}
					}
				}
				return new igk.system.color(r, g, b, a);
			},
			colorfFromString: function (value) {
				return igk.system.colorFromString(value).toColorf();
			},
			hsl2rgb: function (h, l, v) {
				if (!_cnode) {
					_cnode = igk.createNode("div");
					//chrome require node to be added
					if (igk.navigator.getProperty('cssDomRequire'))
						igk.dom.body().add(_cnode);
				}
				_cnode.setCss({ backgroundColor: 'hsl(' + h + ',' + l + '%, ' + v + '%)' });
				return S.colorFromString(_cnode.getComputedStyle('backgroundColor'));
			},
			hsl2rgbf: function (h, l, v) {
				if (!_cnode) {
					_cnode = igk.createNode("div");
				}
				_cnode.setCss({ backgroundColor: 'hsl(' + h + ',' + l + '%, ' + v + '%)' });
				return S.colorfFromString(_cnode.getComputedStyle('backgroundColor'));
			},
			rgb2hsl: function (r, g, b) {
				var min;
				var max;
				var delta;
				var r = r / 255.0;
				var g = g / 255.0;
				var b = b / 255.0;
				var h;
				var s;
				var v;
				min = Math.min(Math.min(r, g), b);
				max = Math.max(Math.max(r, g), b);
				v = max;
				delta = max - min;
				if (max == 0 || delta == 0) {
					// R, G, and B must be 0, or all the same.
					// In this case, S is 0, and H is undefined.
					// Using H = 0 is as good as any...
					s = 0;
					h = 0;
				}
				else {
					s = delta / max;
					if (r == max) {
						// Between Yellow and Magenta
						h = (g - b) / delta;
					}
					else if (g == max) {
						// Between Cyan and Yellow
						h = 2 + (b - r) / delta;
					}
					else {
						// Between Magenta and Cyan
						h = 4 + (r - g) / delta;
					}
				}
				// Scale h to be between 0 and 360. 
				// This may require adding 360, if the value
				// is negative.
				h *= 60;
				if (h < 0) {
					h += 360;
				}
				// Scale to the requirements of this 
				// application. All values are between 0 and 255.
				return { h: h, s: (s * 100), l: (v * 100) };
			}


		});

	})();


	//date management
	createNS("igk.system.Date", {
		format: function (date, format) {
			var _date = new Date(date);

			var s = format;

			s = s.replace(/(Y|m|d|H|i|s)/g, function (m) {

				switch (m) {
					case "Y":
						return _date.getFullYear();
					case "m":
						return _s(_date.getMonth() + 1);
					case "d":
						return _s(_date.getDate());
					case "H":
						return _s(_date.getHours());
					case "i":
						return _s(_date.getMinutes());
					case "s":
						return _s(_date.getSeconds());

				}
			});

			return s;

			// var _dates =
			// _date.getFullYear() + "-" + _s(_date.getMonth() + 1) + "-" + _s(_date.getDate()) +
			// " " + _s(_date.getHours()) + ":" + _s(_date.getMinutes()) + ":" + _s(_date.getSeconds());
		}
	});




	// export module loading
	createNS("igk.system.module", {
		load: function (n, callback) {// load script in module script async

			var _m = m_module_info; // get current module info
			var _s = null;
			if (!_m || !_m.dir)
				return;
			// var _loaded 
			if (_m && !(n in m_module_loaded)) {

				m_module_loaded[n] = 1;
				var q = igk.createNode("script");
				q.setAttribute("defer", 1);
				if (document.head)
					$igk(document.head).add(q);
				q.reg_event("load", function (evt) {
					if (callback) {
						callback(q);
					}
				});



				q.o.src = _m.dir + n;
			}
		},
		getFileUri: function (f) {
			if (igk.validator.isUri(f))
				return f;
			var _m = m_module_info;
			return _m.dir + f;
		},
		getModuleLocation: function () {
			//console.debug(m_module_info);
			return m_module_info ? m_module_info.dir : null;
		},
		getModule: igk_getModule

	});

	igk.defineProperty(igk.system.module, "loadedModules", { get: function () { return m_module_loaded; } });
	igk.defineProperty(igk.system.module, "currentModuleInfo", { get: function () { return m_module_info; } });


	var _app_link = null;
	createNS("igk.system.apps", {// system application manager namespace
	});
	igk.defineProperty(igk.system.apps, "link", { get: function () { return _app_link; } });

	createNS("igk.system.styles", {
		textShadow: function (x, y, offset, color) {
			this.x = x;
			this.y = y;
			this.offset = offset,
				this.color = igk.system.createFromString(color);
			this.toString = function () { return "igk.system.styles.textshadow"; };
		},
		textShadowCreate: function (s) {// create a text shadow properties
			if (s == "none") {
				return new igk.system.styles.textShadow(0, 0, 0, "transparent");
			}
			else {
				var tab = s.split(" ");
				if (tab.length == 4) {
					return new
						igk.system.styles.textShadow(
							igk.getNumber(tab[0]),
							igk.getNumber(tab[1]),
							igk.getNumber(tab[2]),
							tab[3]);
				}
				return null;
			}
		}

	});
	var m_lastError;

	createNS("igk.error", {
	});

	igk_defineProperty(igk.error, "lastError", { get: function () { return m_lastError; }, set: function (v) { m_lastError = v; } });



	createNS("igk.system.collections", {
		list: function () {// list object
			var m_list = [];
			igk.appendProperties(this, {
				getCount: function () {
					return m_list.length;
				},
				toString: function () { return "igk.system.collections.list#" + this.getCount() },
				add: function (item) {
					// if(item && !this.contains(item))
					m_list.push(item);
				},
				clear: function () {
					if (this.getCount() > 0) {
						m_list = [];
					}
				},
				remove: function (item) {
					var cp = [];
					var c = false;
					for (var s = 0; s < m_list.length; s++) {
						if (m_list[s] == item)
							continue;
						cp.push(m_list[s]);
						c = !0;
					}
					m_list = cp;
					return c;
				},
				removeAt: function (index) {
					// create a clone copy
					var cp = [];
					var c = false;
					for (var s = 0; s < m_list.length; s++) {
						if (s == index) continue;
						cp.push(m_list[s]);
						c = !0;
					}
					m_list = cp;
					return c;
				},
				indexOf: function (item) {
					if (!item)
						return -1;
					for (var i = 0; i < this.getCount(); i++) {
						if (m_list[i] == item) {
							return i;
						}
					}
					return -1;
				},
				getItemAt: function (index) {
					if ((index >= 0) && (index < this.getCount()))
						return m_list[index];
					return null;
				},
				toArray: function () {
					var t = [];
					for (var i = 0; i < this.getCount(); i++) {
						t.push(m_list[i]);
					}
					return t;
				},
				contains: function (item) {
					if (!item)
						return !1;
					for (var i = 0; i < this.getCount(); i++) {
						if (m_list[i] == item) {
							return !0;
						}
					}
					return !1;
				},
				forEach: function (callback) {
					if (callback) {
						var c = this.getCount();
						for (var i = 0; i < c; i++) {
							callback(m_list[i], i);
						}
					}
					return this;
				}
			});
		},
		dictionary: function () {	// represent igk system dictionary
			var m_keys = new igk.system.collections.list();// keys list
			var m_values = new igk.system.collections.list();// obj list
			var m_index = -1;
			var m_ck = null;
			var m_cv = null;
			igk.appendProperties(this, {
				it: function () {
					m_index = 0;
					m_ck = null;
					m_cv = null;
				},
				clear: function () {
					m_keys.clear();
					m_values.clear();
					m_ck = null;
					m_cv = null;
					m_index = 0;
				},
				moveNext: function () {
					if (m_index < this.getCount()) {
						m_ck = m_keys.getItemAt(m_index);
						m_cv = m_values.getItemAt(m_index);
						m_index++;
						return !0;
					}
					else {
						m_ck = null;
						m_cv = null;
						return !1;
					}
				},
				getcurrentKey: function () { return m_ck; },
				getcurrentValue: function () { return m_cv; },
				toString: function () {
					return "igk.system.collections.dictionary#[count:" + this.getCount() + "]";
				},
				getKeys: function () {// return keys of the collection as an array
					return m_keys.toArray();
				},
				getValues: function () {// return value of the collection as an array
					return m_values.toArray();
				},
				containKey: function (key) {
					return (m_keys.indexOf(key) != -1);
				},
				getItem: function (key) {
					var i = m_keys.indexOf(key);
					if (i == -1)
						return null;
					return m_values.getItemAt(i);
				},
				getCount: function () {
					return m_keys.getCount();
				},
				add: function (key, value) {
					if (!key) return;

					var i = m_keys.indexOf(key);
					if (i == -1) {
						m_keys.add(key);
						m_values.add(value);
					}
				},
				remove: function (key) {
					var i = m_keys.indexOf(key);
					if (i != -1) {
						m_keys.removeAt(i);
						m_values.removeAt(i);
						return 1;
					}
					return 0;
				}

			});
		}
	});

	createNS("igk.system.array", {// array utility fonctions
		isContain: function (t, u) {
			if (t) {
				if (t.contains) {// firefox
					return t.contains(u);
				}
				else {
					for (var i = 0; i < t.length; i++) {
						if (t[i] == u)
							return !0;
					}
				}
			}
			return !1;
		},
		slice: function (tab, startindex) {
			var v_otab = [];
			for (var i = startindex; i < tab.length; i++) {
				v_otab.push(tab[i]);
			}
			return v_otab;
		},
		sort: function (tab) {
			if (tab) {

				if (tab && Array && Array.sort) {
					return Array.sort(tab);
				}
				else if (tab.sort) {
					// implement a sort method function
					tab.sort(function (a, b) {
						var ta = typeof (a);
						var tb = typeof (b);
						if (ta == tb) {
							switch (ta) {
								case 'number':
									if (a < b)
										return -1;
									if (a == b)
										return 0;
									return 1;
									break;
								default:

									break;
							}
						}
						return (a + '').localeCompare(b + '');
					});
				}
				return tab;
			}
		}
	});

	createNS("igk.html", {
		string: function (text) {
			// string text
			text = text.replace(/\</g, "&lt;");
			text = text.replace(/\>/g, "&gt;");
			return text;
		},
		isTextNode: function (item) {
			return (item && (item.nodeType == 3));
		},
		closeEmptyTag: function (s, replacecallback) {
			function replacing(m) {
				var tag = m.trim().split(' ')[0].substring(1);
				m = m.replace("/>", "></" + tag + ">");
				return m;
			}

			var rg = new RegExp("((<)([^\/>])+(\/>))", "ig");
			return s.replace(rg, replacecallback || replacing);
		},
		appendQuery: function (uri, exp) {
			if (uri.indexOf('?') != -1) {
				uri = uri + "&" + exp;
			} else {
				uri = uri + "?" + exp;
			}
			return uri;
		},
		getDefinition: igk_get_html_item_definition,
		getDefinitionValue: igk_get_html_item_definition_value,
		addToHead: function (n) {
			if (document.head) {
				document.head.appendChild(n);
			}
			else {// internet explorer 8 no get head property defined for document

				igk.ready(function () {
					// set the head
					document.head = document.getElementsByTagName("head")[0];
					document.head.appendChild(n);


				});
			}
		}
	});


	// animate function
	createNS("igk.html.canva", {
		animate: igk_animate,
		cancel: igk_animate_cancel
	});


	function _CustomEvent(event, params) {
		params = params || { bubbles: false, cancelable: false, detail: undefined };
		var evt = document.createEvent('CustomEvent');
		evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
		return evt;
	}


	createNS("igk.winui.events", { // utility events function
		raise: function (t, n) { // raiseEvent
			// @t : target 
			// @n : event name 
			// extra: properties to append
			if (igk.Event) {
				var evt = igk.Event.createEvent(n);
				if (arguments.length > 2) {
					igk.appendProperties(evt, arguments[2]);
				}
				t.dispatchEvent(evt);
				return;
			}

			if (window.Event) {
				try {
					var evt = new Event(n);//"resize");

					if (arguments.length > 2) {
						igk.appendProperties(evt, arguments[2]);
					}
					t.dispatchEvent(evt);

					createNS("igk.Event", {
						createEvent: function (n) {
							return new Event(n);
						}
					});
				}
				catch (ex) {
					createNS("igk.Event", {
						createEvent: function (n) {
							return new _CustomEvent(n, null);
						}
					});

					var evt = igk.Event.createEvent(n);
					if (arguments.length > 2) {
						igk.appendProperties(evt, arguments[2]);
					}
					t.dispatchEvent(evt);
				}
			}
		},
		createEvent: _CustomEvent,
		regKeyPress: function (fc) {
			igk.winui.reg_event(document, "keypress keyup", fc);
		},
		unregKeyPress: function (fc) {
			igk.winui.unreg_event(document, "keypress keyup", fc);
		}
	});



	(function () {

		var maillink = null;
		createNS("igk.mail", {
			sendmail: function (mail) {
				if (maillink == null)
					maillink = igk.createNode('a');
				var a = maillink;
				a.o.href = 'mailto:' + mail;
				igk.dom.body().appendChild(a.o);
				a.o.click();
			}
		});
	})();
	(function () {
		var m_iframes = [];

		function _get_loaded_text(item) {
			var c = "";
			if (item) {
				try {
					if (item.contentDocument)
						item = item.contentDocument;
					else if (item.object) {
						item = item.object;
					}
				}
				catch (ex) {
					// igk.show_notify_error('Exception','ie contentDocument error');
				}
				if (item.childNodes == null)
					return;
				var x = item.childNodes.length;
				for (var i = 0; i < x; i++) {
					if (item.childNodes[i]) {
						c += $igk(item.childNodes[i]).getOuterHtml();
					}
					else {
						console.debug("i " + i);
					}
				}
			}
			return c;
		};

		createNS("igk.file", { // file utiility name space
			getcontents: function (uri, callback, async) {
				if (uri == null)
					return null;
				// obtentions du contenu ne peut 
				var c = "";
				if (typeof (async) == IGK_UNDEF)
					async = !0;

				if (!async) {
					// used ajx // in that case log is writed to console debug . 		
					igk.ajx.get(uri, null, function (xhr) {
						if (this.isReady()) {
							callback.apply(window, [xhr.responseText]);
						}
					}, false);
					return;
				}

				var wait = !0;
				var q = igk.createNode("iframe");
				q.addClass("no-vibility")
					.setAttribute("src", uri)
					.setAttribute("async", false)
					.reg_event("load", function (e) {
						wait = false;
						e.preventDefault();
						c = _get_loaded_text(q.o);
						q.o.parentNode.removeChild(q.o);
						if (callback)
							callback.apply(window, [c]);
					});
				if (document.head) {
					document.head.appendChild(q.o);
				}
				else {
					console.debug('c = ');
					var h = document.getElementsByTagName('head')[0];
					if (h) h.appendChild(q.o);
				}
			},
			get_loaded_text: _get_loaded_text


		});
	})();

	function __ctrl_ctr() {
		this.name = "ctrl";

	};
	createNS("igk.html.ctrl", {
		ctrl: __ctrl_ctr,
		checkbox: new (function () {
			__ctrl_ctr.apply(this);
			igk_appendProp(this, {
				toggle: igk_check_all,
				init: function (t, c) {
					igk.html.ctrl.checkbox.toggle(t,
						$igk(t).getParentByTagName('table'),
						t.checked,
						true,
						c);
				},
				toString: function () { return "[igk.Obj igk.html.ctrl.checkbox]"; }

			});
		})(),
		btn: new (function () {
			igk_appendProp(this, {
				toString: function () { return "[igk.Obj igk.html.ctrl.btn]"; }
			});
		})()
	});

	//STRING MANIPULATION
	createNS("igk.system.string", {
		split: function (str, ch) {
			var i = 0;
			var r = [];
			var w = "";
			while (i < str.length) {

				if (str[i] == ch) {
					r.push(w);
					w = "";
				}
				else
					w += str[i];
				i++;
			}
			if (w.trim().length > 0) {
				r.push(w);
			}
			return r;
		}
		, rmComment: function (s) {// remove comment
			var vi = s.substr(4); // remove comment
			return vi.substring(0, vi.length - 3);
		}
	});


	//----------------------------------------------------------------
	//update string proptotype
	//----------------------------------------------------------------
	var _str_ = window.String.prototype;
	if (!_str_.padStart) {
		_str_.padStart = function (n, s) {
			var g = "";
			while (this.length < n) {
				n--;
				g += s;
			}
			return g + this;
		};
	}





	createNS("igk.system.regex", {
		item_match_class: igk_item_match_class,
		item_inherit_class: igk_item_inherit_class,
		split: function (pattern, v) {
			var q = new RegExp(pattern);
			var s = new RegExp("^(" + pattern + ")+$");
			if (igk.isUndef(v)) {

				throw "is undefr(is undef " + pattern;
				return v;

			}


			if (!v.match(s)) {
				return [v];
			}
			var r = [];
			while ((v.length > 0) && v.match(q)) {
				r.push(RegExp.$1);
				v = v.replace(q, "");
			}
			return r;
		}
	});
	createNS("igk.system.fonts", {
		// special function to install font in balafon system
		installFont: function (a, uri) {
			var s = a.getAttribute('igk-font-name');
			var uri = uri + '&n=' + s;
			var frm = $igk(a).getParentForm();
			s = a.getAttribute("id");
			var top = a.offsetParent.scrollTop;
			igk.ajx.get(uri, null, function (xhr) {
				if (this.isReady()) {
					var i = igk.createNode("dummy");
					this.setResponseTo(i.o);
					// get the first form
					var n = i.select("form").first();
					if (n) {
						if (n.o.parentNode) {
							n.o.parentNode.replaceChild(n, frm);
						}
						else {
							// no parent
							console.debug(";-) No parent found for " + frm);
						}

						n.select("#" + s).each(function () {
							this.o.offsetParent.scrollTop = top;
							return !1;
						});
						if (igk.system.apps.link)
							igk.system.apps.link.reload();
					}
				}
			});
		}
	});


	// igk.system.convert namespace
	createNS("igk.system.convert", {
		parseToInt: function (i) {
			var v = parseInt(i);
			if (Number.isNaN(v))
				v = 0;
			return v;
		},
		parseToBool: function (i) {
			if (typeof (i) == "string") {
				switch (i.toLowerCase()) {
					case "true":
					case "1":
						return !0;
				}
				return !1;
			}
			if (i)
				return !0;
			return !1;
		},
		HexP: function (r) {

			var g = (r >= 10) ? String.fromCharCode(parseInt("A".charCodeAt(0) + (r - 10))) : "" + r;
			return g;
		},
		ToBase: function (d, base, length) {
			if (typeof (length) == IGK_UNDEF)
				length = -1;
			if (typeof (d) == IGK_UNDEF)
				return "UX";
			if (Number.isNaN(d) || Number.isNaN(base))
				return "UX";

			d = parseInt(d);
			var o = "";
			var hpex = igk.system.convert.HexP;
			var ToBase = igk.system.convert.ToBase;
			if (base > 0) {
				var p = parseInt(d / base);
				var r = d % base;
				if (p < base) {
					if (p != 0)
						o = hpex(p) + "" + hpex(r);
					else
						o = hpex(r);
				}
				else {
					o = hpex(r) + o;
					o = ToBase(p, base) + o;
				}

			}
			if (length != -1) {
				for (var i = o.length; i < length; i++) {
					o = "0" + o;
				}
			}
			return o;
		}
	});

	// igk.system.string for string utility used fonction
	createNS("igk.system.string", {
		padleft: function (m, s, l) {
			while (m && (m.length < l)) {
				m = s + m;
			}
			return m;
		},
		padright: function (m, s, l) {
			while (m && (m.length < l)) {
				m = m + s;
			}
			return m;
		},
		trim: function (m) {
			if (m == null) {
				throw new igk.exception('bad');
			}

			if (m.trim)
				return m.trim();
			while (m && (m.length > 0) && (m[0] == ' ')) {
				m = m.substring(1);
			}
			while (m && (m.length > 0) && (m[m.length - 1] == ' ')) {
				m = m.substring(0, m.length - 1);
			}
			return m;
		},
		startWith: function (m, str) {
			if (m && m.slice)
				return m.slice(0, str.length) == str;
			return !1;
		},
		endWith: function (m, str) {
			return m.slice(-str.length) == str;
		},
		remove: function (m, index, length) {
			return m.substring(0, index) + m.substring(index + length, m.length);
		},
		insert: function (m, index, pattern) {
			return m.substring(0, index) + pattern + m.substring(index, m.length);
		},
		capitalize: function (s) {
			if (s && s.length > 1)
				return s[0].toUpperCase() + s.substring(1).toLowerCase();
			return s;
		}
	});

	// ------------------------------------------------------------------------------------
	// igk.math NAME SPACE
	// ------------------------------------------------------------------------------------
	createNS("igk.math", {
		_2PI: Math.PI * 2,
		vector2d: function (x, y) {
			return {
				x: x,
				y: y,
				sub: function (d) {
					this.x -= d.x;
					this.y -= d.y;
					return this;
				},
				add: function (d) {
					this.x += d.x;
					this.y += d.y;
					return this;
				},
				distance: function (d) {
					var dx = (this.x - d.x);
					var dy = (this.y - d.y);
					var f = Math.sqrt((dx * dx) + (dy * dy));
					return f;
				},
				clone: function () {
					return igk.math.vector2d(this.x, this.y);
				},
				toString: function () { return "vector2d{x:" + this.x + ";y:" + this.y + "}" }
			};
		},
		matrix3x3: function () {
			var m_element = [];
			var MATRIX_LENGTH = 9;
			function mult_matrix(tb1, tb2) {
				var rtb = new Array(MATRIX_LENGTH);
				var k = 0;
				var offsetx = 0;
				var offsety = 0;
				var v_som = 0;
				for (var k = 0; k < MATRIX_LENGTH;) {
					for (var i = 0; i < 4; i++) {// columns
						v_som = 0.0;
						for (var j = 0; j < 4; j++) {
							offsety = (4 * j) + i;// calculate column index
							v_som += tb1[offsetx + j] * tb2[offsety];
						}
						rtb[k] = v_som;
						k++;
					}
					offsetx += 4;
				}
				return rtb;
			};

			igk.appendProperties(this, {
				getElements: function () {
					return m_element;
				},
				reset: function () {
					m_element[0] = m_element[4] = m_element[8] = 1;
					m_element[1] = m_element[2] = m_element[3] = 0;
					m_element[5] = m_element[6] = m_element[7] = 0;
				},
				translate: function (dx, dy, dz) {
					m_element[6] += dx;
					m_element[7] += dy;
					if (dz) {
						m_element[8] *= dz;
					}
				},
				scale: function (ex, ey, ez) {
					m_element[0] *= ex;
					m_element[4] *= ey;
					if (ez) {
						m_element[8] *= ez;
					}
				},
				rotate: function (angle) {
				},
				rotateAt: function (angle, point) {

				}
			});
			this.reset();
		}
	});


	createNS("igk.math.vector2d", {
		empty: function () {
			return new igk.math.vector2d(0, 0);
		},
		parse: function (s) {
			if (s == null)
				return new igk.math.vector2d(0, 0);
			var t = s.split(';');
			var g = null;
			if (t.length == 2)
				g = new igk.math.vector2d(parseFloat(t[0]), parseFloat(t[1]));
			else
				g = new igk.math.vector2d(parseInt(t), parseInt(t));
			return g;

		}
	});
	// ------------------------------------------------------------------------------------
	// igk.winui NAME SPACE
	// ------------------------------------------------------------------------------------

	// used to animate . callback must return 1 in oder to continue animation
	function igk_animate(callback) {

		var animFrame = igk.animation.getAnimationFrame();
		var animObj = {
			cancel: function () {
				igk.html.canva.cancel(this.id);
			}
		};
		function __doCall() {
			if (callback(1000 / 60)) {
				setTimeout(__doCall, 1000 / 60);
			}
		};
		function __runCall() {
			if (callback()) {
				// continue animate
				animObj.id = animFrame(__runCall);
			}
		};

		// used to animated rendering scene


		if (typeof (animFrame) == "undefined") {
			__doCall();
		}
		else {
			animObj.id = animFrame(__runCall);
			return animObj;
		}
	};

	// used to cancel 
	function igk_animate_cancel(id) {
		var _canimFrame = igk.animation.getAnimationCancelFrame();
		if (_canimFrame) {
			return _canimFrame(id);
		}
	};

	function igk_create_event_ojectManager() {
		var m_obj = new igk.system.collections.list();// keys list
		var m_value = new igk.system.collections.list();// obj list
		var m_key = null;
		var m_ukey = null;
		var m_unreg = 0;

		igk.appendProperties(this, {
			raise: function (item, method) { // raiseEvent created
				// dispatch event used for ie8 compatibility
				if (!item)
					return;

				m_key = item;
				var i = m_obj.indexOf(item);
				// alert("raise "+item);
				if (i != -1) {
					var dic = m_value.getItemAt(i);
					if (dic.containKey(method)) {
						var funcs = dic.getItem(method).funcs;
						// alert("ddd " + item + ": "+ " "+method+" " );
						var args = [item[method]];
						for (var s = 0; s < funcs.getCount(); s++) {
							funcs.getItemAt(s).apply(item, args);
						}
					}
				}
			},
			register: function (item, method, func) {
				if (!item || !func || (typeof (func) != IGK_FUNC) || (m_key == item)) // last to avoid recursion
					return;

				m_key = item;
				var i = m_obj.indexOf(item);
				var o = null;
				if (i == -1) {
					o = { target: item, funcs: new igk.system.collections.list(), method: method };
					// maintain index
					o.funcs.add(func);
					var dic = new igk.system.collections.dictionary();

					dic.add(method, o);
					m_obj.add(item);
					m_value.add(dic);
				}
				else {
					var dic = m_value.getItemAt(i);
					if (dic) {
						if (dic.containKey(method)) {

							dic.getItem(method).funcs.add(func);

						}
						else {
							var o = { target: item, funcs: new igk.system.collections.list(), method: method };
							o.funcs.add(func);
							dic.add(method, o);
						}
					}
				}
				m_key = null;
			},
			unregister: function (item, method, func) {

				if (m_unreg)
					return
				m_unreg = 1;
				var o = null;
				if (item == window)
					o = item;
				else
					o = $igk(item).o;
				if ((!item) || (m_ukey == item))
					return;
				m_ukey = item;
				var i = m_obj.indexOf(o);
				var dic = null;

				if (i != -1) {
					dic = m_value.getItemAt(i);
					var tab = dic.getValues();
					if (method) {


						// remove all method registrated
						if (typeof (func) == 'undefined') {
							for (var t = 0; t < tab.length; t++) {
								if (tab[t].method == method) {
									var c = tab[t].funcs.toArray();
									for (var x = 0; x < c.length; x++) {
										igk.winui.unreg_event(o, tab[t].method, c[x]);
									}
									dic.remove(method);
									continue;
								}
							}
						} else {

							for (var t = 0; t < tab.length; t++) {
								if (tab[t].method == method) {
									var c = tab[t].funcs.toArray();
									for (var x = 0; x < c.length; x++) {
										if (c[x] == func) {
											igk.winui.unreg_event(o, tab[t].method, c[x]);
										}
									}
									continue;
								}
							}
						}
						m_ukey = null;
						m_unreg = 0;
						return;
					}



					for (var t = 0; t < tab.length; t++) {
						// 							
						var c = tab[t].funcs.toArray();

						for (var x = 0; x < c.length; x++) {
							igk.winui.unreg_event(o, tab[t].method, c[x]);
						}
					}
					m_obj.removeAt(i);
					m_value.removeAt(i);
				}
				// else{

				// }
				m_unreg = 0;
				m_ukey = null;
			},
			toString: function () {
				return "igk.winui.eventObjectManager";
			},
			unregister_child: function (q) {
				if (q)
					$igk(q).select("*").each(function () {
						this.unregister();
						return !0;
					});
			}
		});
		return this;
	};


	createNS("igk.winui", {// represent window screen utility namespace
		toString: function () { return "igk.winui"; },
		open: function (uri) {
			var frm = window.open(uri);
			frm.onload = function () {
				frm.close();
			};
		},
		screenSize: function () {
			return {
				height: window.innerHeight, width: window.innerWidth, toString: function () {
					return "height:" + this.height + " ; width: " + this.width;
				}
			};
		},
		focus: function (id) { var q = document.getElementById(id); if (q) q.focus(); },
		fitfix2: function (node, parent, onw, onh) {
			var t = $igk(node);
			var l = $igk(parent);
			var h = onh == null ? !0 : onh;
			var w = onw == null ? !0 : onw;

			if (t) {

				t.setCss({
					"position": "fixed"
				});
				if (w) {
					t.setCss({ "right": (l.fn.hasVScrollBar() ? l.fn.vscrollWidth() : 0) + "px" });
				}
				if (h) {
					t.setCss({ "bottom": ((l.fn.hasHScrollBar() ? l.fn.hscrollHeight() + 1 : 0)) + "px" });
				}
			}
		},
		getEventObjectManager: function () {
			if (__eventObjectManager == null) {
				__eventObjectManager = new igk_create_event_ojectManager();
			}
			return __eventObjectManager;
		},
		// define static method
		GetScreenPosition: function (item) { // get position according to screen . without scrolling calculation
			var left = 0;
			var top = 0;
			if (item) {
				left += item.offsetLeft;
				top += item.offsetTop;
				while (!igk.isUndef(typeof (item.offsetParent)) && (item.offsetParent != null)) {
					left += (item.clientLeft) ? item.clientLeft : 0;
					top += (item.clientTop) ? item.clientTop : 0;
					item = item.offsetParent;
				}
			}
			return new igk.math.vector2d(left, top);
		},
		GetScreenSize: function () {
			var x = window.innerWidth || 0;
			var y = window.innerHeight || 0;
			return new igk.math.vector2d(x, y);
		},
		getWidth: function () {
			return window.innerWidth || 0;
		},
		getHeight: function () {
			return window.innerHeight || 0;
		},
		// get the document location
		GetDocumentLocation: function () {
			var left = 0;
			var top = 0;
			left = window.pageXOffset ? -window.pageXOffset : 0;
			top = window.pageYOffset ? -window.pageYOffset : 0;
			return new igk.math.vector2d(left, top);
		},
		// get the real screen location of the item with scroll calculation
		GetRealScreenPosition: function (item) { // 
			// >item: DomNode

			var left = 0;
			var top = 0;
			left = window.pageXOffset ? -window.pageXOffset : 0;
			top = window.pageYOffset ? -window.pageYOffset : 0;
			if (item) {
				// left+=item.offsetLeft?item.offsetLeft:0;
				// top +=item.offsetTop?item.offsetTop:0;
				left += -igk.winui.GetScrollLeft(item) + ((item.offsetLeft) ? item.offsetLeft : 0);
				top += -igk.winui.GetScrollTop(item) + ((item.offsetTop) ? item.offsetTop : 0);

				while ((item.offsetParent != null)) {

					item = item.offsetParent;
					left += -igk.winui.GetScrollLeft(item) + ((item.offsetLeft) ? item.offsetLeft : 0);
					top += -igk.winui.GetScrollTop(item) + ((item.offsetTop) ? item.offsetTop : 0);
				}
			}
			return new igk.math.vector2d(left, top);
		},

		GetScrollPosition: function (item, parent) {
			if (!parent)
				return new igk.math.vector2d(0, 0);
			var left = 0;
			var top = 0;
			left = window.pageXOffset ? -window.pageXOffset : 0;
			top = window.pageYOffset ? -window.pageYOffset : 0;
			if (item) {
				// left+=item.offsetLeft?item.offsetLeft:0;
				// top +=item.offsetTop?item.offsetTop:0;
				left += -igk.winui.GetScrollLeft(item) + ((item.offsetLeft) ? item.offsetLeft : 0);
				top += -igk.winui.GetScrollTop(item) + ((item.offsetTop) ? item.offsetTop : 0);

				while ((item.offsetParent != null) && (item.offsetParent != parent)) {

					item = item.offsetParent;
					left += -igk.winui.GetScrollLeft(item) + ((item.offsetLeft) ? item.offsetLeft : 0);
					top += -igk.winui.GetScrollTop(item) + ((item.offsetTop) ? item.offsetTop : 0);
				}
				if (item.offsetParent != parent) {
					return new igk.math.vector2d(-1, -1);
				}
			}
			return new igk.math.vector2d(left, top);
		},
		GetRealOffsetParent: function (item) {
			if (item) {
				var q = item.offsetParent;
				while (q != null) {
					if (q.offsetParent == null)
						break;
					q = q.offsetParent;
				}
				return q;
			}
			return null;
		},
		GetRealScrollParent: function (item) {// get the current scroll parent
			if (item) {
				// note: offsetParent is only available for item with  display not equal to 'none'
				var q = item.offsetParent;//  || item.parentNode;
				var cq = 0;

				while ((q != null) && (q.tagName.toLowerCase() != 'body')) {
					cq = $igk(q);
					if (q.offsetParent == null) {
						console.debug("no offset parent? " + (cq.fn.hasVScrollBar() || cq.fn.hasHScrollBar()));
						break;
					}


					if (cq.fn.hasVScrollBar() || cq.fn.hasHScrollBar()) {



						break;
					}
					q = q.offsetParent;// || q.parentNode;
				}

				return q;
			}
			return null;
		},
		GetMousePoint: function (evt) {
			return new igk.math.vector2d(evt.clientX, evt.clientY);
		},
		// >@@ get the child mouse location
		GetChildMouseLocation: function (item, evt)// return mouse location in child
		{
			if (evt == null) {
				return new igk.math.vector2d(0, 0);
			}
			var loc = igk.winui.GetRealScreenPosition($igk(item).o);


			loc.x = evt.clientX - (isNaN(loc.x) ? 0 : loc.x);
			loc.y = evt.clientY - (isNaN(loc.y) ? 0 : loc.y);
			return loc;
		},
		GetChildTouchLocation: function (item, evt, index) {
			var touchv = {
				"touchstart": 1,
				"touchmove": 1,
				"toucancel": 1,
				"touchend": 1
			};
			if (!evt || !touchv[evt.type])
				return igk.math.vector2d(0, 0).clone();
			var i = index || 0;


			var s = ((evt.touches.length > 0) && (i < evt.touches.length)) ? evt.touches.item(i) : null;


			if (s == null)
				return igk.math.vector2d(0, 0).clone();

			var loc = igk.winui.GetRealScreenPosition($igk(item).o);
			loc.x = s.pageX - (isNaN(loc.x) ? 0 : loc.x);
			loc.y = s.pageY - (isNaN(loc.y) ? 0 : loc.y);


			return loc;
		},
		// >@@get if the child has a mouse input
		HasMouseInputInChild: function (item, evt) {
			var loc = igk.winui.GetChildMouseLocation(item, evt);
			return igk.winui.controlUtils.HasChildContainPoint(item, loc);
		},
		GetScrollLeft: function (item) {
			if (item == null) return 0;
			if (item.pageXOffset) {
				return item.pageXOffset;
			}
			else if (item.scrollLeft)
				return item.scrollLeft;
			return 0;
		},
		GetScrollTop: function (item) {
			if (item == null) return 0;
			if (item.pageYOffset) {
				return item.pageYOffset; // pageXOffset
			}
			else if (item.scrollTop)
				return item.scrollTop;
			return 0;
		},
		registerEventHandler: function (name, objListener) {
			// > register event handler list
			var th = name.split(" ");
			var n, d;;
			for (var i = 0; i < th.length; i++) {
				d = igk.winui.getEventHandler(th[i]);
				if (d) {
					continue;
				}
				if (objListener) {
					igk.winui.events.register.registerEvent(th[i], objListener);
				}
			}

		},
		getEventHandler: function (method) {
			if (typeof (this.eventRegister) == IGK_UNDEF) {
				this.eventRegister = new function () {
					this.m_manager = {};
					igk.appendProperties(this, {
						getMethod: function (name) {
							if (typeof (this.m_manager[name]) != IGK_UNDEF) {
								return this.m_manager[name];
							}
							return null;
						},
						registerEvent: function (name, obj) {
							if (typeof (this.m_manager[name]) == IGK_UNDEF) {
								this.m_manager[name] = obj;
							}
						},
						toString: function () {
							return "method register ";
						}

					});
				};
				igk.system.createNS("igk.winui.events", {
					register: this.eventRegister,
					exceptionEvent: new (function () {// for manage exception method event
						var m_t = {};
						m_t["mouseleave"] = { replace: "mouseout" };

						igk.appendProperties(this, {
							contain: function (m) {
								return typeof (m_t[m]) != IGK_UNDEF;
							},
							getMethod: function (m) {
								if (typeof (m_t[m]) != IGK_UNDEF) {
									return m_t[m].replace;
								}
								return null;
							},
							toString: function () {
								return "[object igk.winui.events.exceptionEvent]";
							}
						});
					})()
				});
			}
			return this.eventRegister.getMethod(method);
		},
		reg_window_event: function (method, func, useCapture) {
			return igk.winui.reg_system_event(window, method, func, useCapture);
		},
		unreg_window_event: function (method, func) {
			return igk.winui.unreg_system_event(window, method, func, useCapture);
		},
		reg_system_event: function (item, method, func, useCapture) {

			if (item == null)
				return !1;

			// igk.debug.assert( method=="webkittransitionend","register please handle transitionend");
			if (typeof (item[method]) == igk.constants.undef) {

				if (item["on" + method] + "" == igk.constants.undef) {
					var e = igk.winui.events.exceptionEvent;
					if (e.contain(method)) {
						return igk.winui.reg_system_event(item, e.getMethod(method), func, useCapture);
					}
					console.debug("/!\\ can't register event on" + method);
					return !1;
				}
			}
			if (item.addEventListener) {
				var t = item.addEventListener(method, func, typeof (useCapture) == 'object' ? useCapture : false);
				igk.winui.getEventObjectManager().register(item, method, func);
				return !0;
			}
			else if (item.attachEvent) {

				if (igk.DEBUG) {
					console.debug("attachevent " + method);
				}

				if (igk.navigator.IEVersion() == 7) {
					// 

					var ftp = function (evt) {
						evt.preventDefault = function () {
						};
						func.apply(item, [evt]);
					};
					item.attachEvent("on" + method, ftp);
					igk.winui.getEventObjectManager().register(item, method, ftp);
				}
				else {
					var p = item.attachEvent("on" + method, func);
					igk.winui.getEventObjectManager().register(item, method, func);
					if (method == "igk-trackchange") {
						// var h=document.createEventObject(window.event);
						// alert("register on medthod "+item.fireEvent + " p is "+p + "  "+h  + " "+item.attachEvent("onclick",func));
						// alert(item["onclick"]);
						// item.fireEvent("ontest",h);

						// var clickEvent=document.createEventObject(window.event);
						// clickEvent.button=1;  // left click
						// item.fireEvent(method,clickEvent);

						// alert("done");
						$igk(item).raiseEvent("igk-trackchange");
					}
				}
				return !0;
			}
			else {

				var m = item["on" + method];
				if (typeof (m) == "function") {
					item["on" + method] = function () {
						m.apply(this, arguments);
						func.apply(this, arguments);
					}
				}
				else {
					item["on" + method] = func;
				}
				igk.winui.getEventObjectManager().register(item, method, func);
			}
			return !0;
		},
		reg_event: function (item, method, func, useCapture) {// global	
 
			var g = method.split(' ');
			var s = 0;
			var o = 1;
			while (o && (s = g.pop())) {
				var eventHandler = igk.winui.getEventHandler(s);
				if (eventHandler != null) {
					o = o && eventHandler.reg_event(item, func, useCapture);
				}
				else o = o && igk.winui.reg_system_event(item, s, func, useCapture);

			}
		},

		unreg_system_event_object: function (item) { // unregister all event register for this item
			if (!item)
				return;
			igk.winui.getEventObjectManager().unregister(item);
		},
		unreg_system_event: function (item, method, func) {

			if ((item == null) ||
				(((method in item) && (item["on" + method] + "" == igk.constants.undef))
					&& (!item.removeEventListener)
					&& (item.detachEvent)
				))
				return !1;

			igk.winui.getEventObjectManager().unregister(item, method, func);
			if (item.removeEventListener) {
				item.removeEventListener(method, func, false);
			}
			else if (item.detachEvent) {
				item.detachEvent("on" + method, func);
			}
			else {
				var m = item["on" + method];
				if (typeof (m) == "function") {
					item["on" + method] = null;
				}
				else {
					item["on" + method] = null;
				}
			}
			return !0;
		},
		unreg_event: function (item, method, func) {
			var eventHandler = igk.winui.getEventHandler(method);
			if (eventHandler != null) {
				return eventHandler.unreg_event(item, func);
			}
			return igk.winui.unreg_system_event(item, method, func);
		},
		regwindow_event: function (method, func) {
			if (!igk.winui.reg_event(window, method, func))
				return igk.winui.reg_event(document, method, func);
			return !0;
		},
		unregwindow_event: function (method, func) {

			if (!igk.winui.unreg_event(window, method, func))
				return igk.winui.unreg_event(document, method, func);
			return !0;
		},

		RegEventContext: function (eventContextOwner, properties) {// o that will host and igk properties to binds
			// >@@ eventContextOwner: the context o
			// >@@ property used to register 
			if ((properties == null) || (eventContextOwner == null) || (igk_getRegEventContextByOwner(eventContextOwner) != null)) {

				return null;
			}
			function __eventContextObject(eventContextOwner, properties) {
				var q = this;
				var m_eventContextOwner = eventContextOwner;
				var m_properties = properties;
				var m_col = new Array();

				// save unregEventContext function to target o
				function __regEventContextFunction() {
					var v_ts = properties;

					var v_ch = igk_getRegEventContext(v_ts, true, function () { return new chainUnreg(eventContextOwner, v_ts); });

					if (!v_ts.unregEventContext) {
						igk.appendProperties(v_ts,
							{
								unregEventContext: function () {
									// unreg single context								
									q.clear();
									v_ch.chain--;
									if (v_ch.chain == 0) {
										if (!igk_unRegEventContext(v_ch)) {
											throw ("item not unregister ");
										}
									}
								}
							});
					}
					else {
						var meth = v_ts.unregEventContext;
						v_ts.unregEventContext = function () {
							meth();
							q.clear();
							v_ch.chain--;

							if (v_ch.chain == 0) {
								if (!igk_unRegEventContext(v_ch)) {
									throw ("unreg -- items");
								}
							}

						};
						v_ch.chain++;
					}

				};
				__regEventContextFunction();

				function chainUnreg(o, properties) {
					this.o = o;
					this.properties = properties;
					this.chain = 1;
					this.unregEventContext = function () { this.properties.unregEventContext(); };
					this.toString = function () { return "chainUnreg[" + o + ":" + this.chain + "]"; };

				};
				function eventCibling(target, name, func) {
					this.name = name;
					this.target = target;
					var q = this;
					this.func = function () { func.apply(q.target, arguments); };
					this.toString = function () { return "eventcibling:" + name; };
				};
				function __unregister(s){
					if (igk_is_array(s)) {
						for (var i = 0; i < s.length; i++) {
							__unregister(s[i]);
						}
					}
					else if (s instanceof eventCibling) {
						// unregister event cibling
						igk.winui.unreg_event(s.target, s.name, s.func);
					}
				};

				// resizing function
				var m_resizefuncs = [];
				var m_resizetimeout = 0;
				var m_resizeevent = false;
				function __is_resized_event() {
					return m_resizeevent;
				};
				function __resizing_push(func) {
					if (func) {
						m_resizefuncs.push(func);
					}
				};
				function __resize_call_invoke(evt) {
					for (var i = 0; i < m_resizefuncs.length; i++) {
						m_resizefuncs[i].apply(window, arguments);
					}
				}
				function __resize_call(evt) {
					if (m_resizetimeout) {

						igk.clearTimeout(m_resizetimeout);
						m_resizetimeout = null;
					}
					// start a new time out
					m_resizetimeout = setTimeout(function () { __resize_call_invoke(evt); }, RZ_TIMEOUT);
				};

				window.igk.appendProperties(this, {
					clear: function () {// unegister all element
						if (m_col) {
							for (var i in m_col) {
								var s = m_col[i];
								__unregister(s);
							}
						}
					},
					reg_event: function (target, name, func) {// register cibling function

						if ((target == null) || !name || (func == null)) {
							return !1;
						}
						var v_cibling = new eventCibling(target, name, func);

						igk.winui.reg_event(v_cibling.target, v_cibling.name, v_cibling.func);

						var key = name + ":" + target;

						if (typeof (m_col[key]) == igk.constants.undef)
							m_col[key] = v_cibling;
						else {
							var tab = m_col[key];
							var v_isarray = igk_is_array(tab);
							if (v_isarray) {
								tab.push(v_cibling);
								m_col = tab;
							}
							else {
								var v_t = new Array();
								v_t.push(tab);
								v_t.push(v_cibling);
								// replace
								m_col[key] = v_t;
							}
						}
						return !0;
					},
					reg_window: function (name, func) {// register window function
						if (name == "resize") {// handle resize
							__resizing_push(func);
							if (!__is_resized_event()) {
								this.reg_event(window, name, __resize_call);
								m_resizeevent = !0;
							}
							return;
						}
						this.reg_event(window, name, func);
					},
					toString: function () {
						return "RegEventContext";
					}
				});
			}
			return new __eventContextObject(eventContextOwner, properties);
		},
		saveHistory: function (uri) {
			if (typeof history.pushState == "function") {
				history.pushState({}, document.title, uri + "&history=1");
			}
		},
		reg_keypress: function (func) {
			igk.winui.reg_event(document, "keypress", func);
		},
		unreg_keypress: function (func) {
			igk.winui.unreg_event(document, "keypress", func);
		},

		centerDialog: function (dialog, minw, minh) {	// center the dialog
			if (dialog == null)
				return;
			dialog.style.top = "50%";
			dialog.style.left = "50%";

			// setup the size
			dialog.style.minWidth = minw ? minw + "px" : null;
			dialog.style.minHeight = minh ? minh + "px" : null;
			$igk(dialog).setCss({
				"position": "absolute",
				"marginLeft": -(dialog.clientWidth / 2) + "px",
				"marginTop": -(dialog.clientHeight / 2) + "px"
			});

			// lock the size 
			if (!dialog.style.minWidth)
				dialog.style.minWidth = dialog.clientWidth + "px";
			if (!dialog.style.minHeight)
				dialog.style.minHeight = dialog.clientHeight + "px";

			// update size for draggin

			var pt = igk.winui.GetRealScreenPosition(dialog);
			var adjusted = false;
			if (pt.x < 0) { pt.x = 0; adjusted = !0; }
			if (pt.y < 0) { pt.y = 0; adjusted = !0; }

			if (adjusted) {
				dialog.style.margin = "0";	// remove marging			
				dialog.style.left = pt.x + "px";
				dialog.style.top = pt.y + "px";
			}

		},
		dragFrameManager: {	// used to drag frame box
			target: null,
			box: null,// box to move
			startpos: null,
			dragstart: false,
			oldposition: null,
			locToScreen: true,
			dragSize: new igk.math.vector2d(4, 4),
			toString: function () {
				return "igk.winui.dragmanager";
			},
			init: function (target, box) {
				if (target == null)
					return null;

				function __construct(target, box) {

					var self = this;
					var m_c = 0;
					this.target = target;
					this.box = (box == null) ? target : box;
					this.changelocation = igk.winui.dragFrameManager.changelocation;


					var m_eventContext = igk.winui.RegEventContext({
						box: this.box,
						toString: function () { return "dragFrameManager.BoxOwner" },
						properties: $igk(box)
					},
						$igk(this.box));

					if (m_eventContext) {

						m_eventContext.reg_event(document, "mousemove", function () { self.changelocation.apply(self, arguments); });
						m_eventContext.reg_event(document, "mouseup", function () { if (self.dragstart) { self.dragstart = false; }; });

						m_eventContext.reg_event(this.target,
							"mousedown", function (evt) {
								if (!self.dragstart) {
									self.startpos = new igk.math.vector2d(evt.clientX, evt.clientY);
									self.oldposition = igk.winui.GetScreenPosition(self.box);
									self.dragstart = !0;
								}
							});
						m_eventContext.reg_event(this.target, "mouseup", function (evt) { self.dragstart = false; });
					}
					else
						alert("igk.winui.dragFrameManager ... event context failed");
					return self;
				};
				return new __construct(target, box);
			},
			changelocation: function (evt) {
				if (this.dragstart) {
					var left = 0;
					var right = 0;
					var pt = igk.math.vector2d(
						this.oldposition.x + (evt.clientX - this.startpos.x),
						this.oldposition.y + (evt.clientY - this.startpos.y)
					);
					var adjustedx = false;
					var adjustedy = false;
					if (this.locToScreen) {
						if (pt.x < 0) { pt.x = 0; adjustedx = true; }
						if (pt.y < 0) { pt.y = 0; adjustedy = true; }

						if (this.box.offsetParent) {
							if (!adjustedx)
								if ((pt.x + this.box.clientWidth) >= this.box.offsetParent.clientWidth) {
									pt.x = this.box.offsetParent.clientWidth - this.box.clientWidth;
									adjustedx = !0;
								}

							if (!adjustedy)
								if ((pt.y + this.box.clientHeight) >= this.box.offsetParent.clientHeight) {
									pt.y = this.box.offsetParent.clientHeight - this.box.clientHeight;
									adjustedy = !0;
								}
						}
					}

					this.box.style.margin = "0";
					this.box.style.left = pt.x + "px";
					this.box.style.top = pt.y + "px";
					// this.box.style.right='auto';

				};
			}
		}
	});

	(function () {

		var _uiListener = 0;

		createNS("igk.winui", {
			createInput: function (id, type, opts) {
				if (_uiListener) {
					return _uiListener.apply(this, arguments);
				}
				var i = null;
				switch (type) {
					case "select":
						i = igk.createNode("select");
						i.setAttribute("id", id);
						i.o['name'] = id;
						if (opts && opts.data) {
							var sl = opts.select || '';
							for (var j = 0; j < opts.data.length; j++) {
								var o = i.add("option");
								o.setAttribute("value", opts.data[j]);
								o.setHtml(opts.data[j]);

								if (opts.data[i] == sl) {
									o.setAttribute("selected", 'true');
								}
							}
						}
						if (opts && opts.change) {
							i.on("change", opts.change);
						}
						break;
					default:
						i = igk.createNode("input");
						i.setAttribute("id", id);
						i.o['name'] = id;
						var _pl = (opts ? opts.placeholder : '') || '';
						if (type) {
							i.o['type'] = type;
						}
						i.setAttribute("placeholder", _pl);
						if ((type == 'file') && opts && opts.accept) {
							i.setAttribute("accept", opts.accept);
						}

						break;
				}
				return i;
			},
			setListener: function (b) {
				_uiListener = b;
			},
			getListener: function () {
				return _uiListener;
			}
		});

	})();


	// winui class control management
	(function () {
		var m_class_control = [];
		var m_class_list = null;
		var m_init = 0;
		var m_rg = 0;
		var m_rcg;


		function __initClassControl(n) {
			var q = $igk(this);
			if (q.fn.isClassControl) {
				console.debug("is already a class control " + q.fn.controlName);
				return;
			}

			if (!m_rcg)
				m_rcg = new RegExp("((^| )+)(" + m_rg + ")($| )");
			var t = this.className;
			if (m_rcg.test(t)) {



				var m = m_rcg.exec(t);



				var n = m[3];
				var g = m_class_control[n];
				if (!g) {
					console.debug('test ' + t);
					console.debug(m);
					console.debug(m[2]);
					return;
				}

				var c = g.func;



				c.apply(q, [n, g.data]);
				q.fn.isClassControl = 1; // mark as controller
				q.fn.ControlName = n; // contorol name
			}

		};

		createNS("igk.winui", {
			reloadClassList: function () {// reload class list
				m_class_list = null;
				return igk.winui.getClassList();
			},
			getClassList: function () {
				if (m_class_list != null)
					return m_class_list;
				var t = [];
				var nslist = {};
				for (var i in m_class_control) {
					t.push(i);
					t[i] = m_class_control[i];
					if (m_class_control[i].ns) {
						if (!nslist[m_class_control[i].ns])
							nslist[m_class_control[i].ns] = [];
						nslist[m_class_control[i].ns].push(t[i]);
					}
					else {
						if (!nslist[igk.constants.namespace])
							nslist[igk.constants.namespace] = [];
						nslist[igk.constants.namespace][i] = t[i];
						nslist[igk.constants.namespace].push(t[i]);
					}
				}
				t["ns://"] = nslist;
				m_class_list = t;
				return t;
			},
			initClassObj: function (n) {
				// n: dom node
				igk_init_node_class_obj.apply(n);
			},
			initClassControl: function (n, c, inf) {
				if (!m_init && igk.ctrl.registerReady) {
					// register node ready class control
					m_init = 1;
					igk.ctrl.registerReady(__initClassControl);
				}
				// n: name
				// c: callback function
				// inf : info
				if (m_class_control[n])
					return;
				m_class_control[n] = {
					data: inf || 1,
					func: c,
					n: n
				};
				if (m_rg)
					m_rg += '|' + n;
				else if (m_rg == 0) {
					m_rg = n;
				} else
					m_rg += n;
				m_rcg = null;
				return {
					init: function () {

					}
				};
			}
		});




		function igk_init_node_class_obj() {
			// init node objet attached by registered class
			var c = this;
			var b = m_class_control;
			var fc = null;

			for (var i in b) {
				fc = b[i].data.func;
				if (fc) {
					if (igk_item_match_class(b[i].n, c)) {
						fc.apply($igk(c));
					}
					else
						$igk(c).select("." + b[i].n).each(function () {
							fc.apply(this);
							return !0;
						});

				}
			}
		}


		// igk.ready(igk_init_class_obj);
		// igk.ajx.fn.registerNodeReady(function(){igk_init_node_class_obj.apply(this); });


	})();


	igk_defineProperty(igk, 'version', {
		get: function () { return __version; }
	});
	igk_defineProperty(igk, 'author', {
		get: function () { return __author; }
	});


	// >namespace: igk.android
	createNS("igk.android", {
		init: function (ds) {
			if (igk.navigator.isAndroid()) {
				igk.dom.body().addClass("igk-android");
				var m = igk.createNode("meta");
				m.setAttribute("name", "viewport");
				m.setAttribute("content", ds);
				$igk(document.head).add(m);
			}
		}
	});

	// .msdialog object	
	(function () {
		var m_dlgx = []; // list of opened dialog
		var m_d = 0; // demand for showing a dialog
		function __setupview(div) {

			div.setCss({
				"height": "auto" // auto by default
			});// .forceview();	

			var _h = div.getHeight();
			var p = -(_h / 2.0);

			var pn = div.getParentNode();
			var t = 0;// to get half position
			var h = div.getHeight() + "px";
			var oflow = false;

			if (pn) {
				t = pn.getHeight() / 2.0;

			}
			else {
				t = div.getTop();
			}
			if ((t + p) < 0) {

				// p=-t;
				if (pn.getHeight() < div.getHeight()) {
					h = pn.getHeight() + "px";
					oflow = true;
				}

			}

			div.setCss({
				"marginTop": p + "px"
			});

			// div.setCss({
			// "height": "auto" // auto by default
			// });		
			// var p=-(div.getHeight() /2.0);
			// var pn=div.getParentNode();
			// var t=0;// to get half position
			// var h=div.getHeight()+"px";
			// var oflow=false;

			// if(pn){
			// t=pn.getHeight()/2.0;
			// }
			// else{
			// t=div.getTop();			
			// }
			// if((t +p)<0)
			// {
			// p=-t;
			// if(pn.getHeight() < div.getHeight())
			// {
			// h=pn.getHeight()+"px";
			// oflow= true;						
			// }

			// }
			// div.setCss({
			// "marginTop": p+"px"
			// });		

			// if(h!=div.getComputedStyle('height'))
			// {
			// ========				
			// div.animate({height: h},{
			// duration:200,
			// interval:10,
			// animtype:"timeout",
			// context:"notify-height-context",
			// effect: "circ",
			// effectmode:"easeinout",
			// complete: function(){
			// if(!oflow){
			// $igk(div).setCss({height: "auto",overflowY:"hidden"});
			// }
			// else {
			// $igk(div).setCss({overflowY: "auto"});
			// }
			// }
			// });
			// }



		}

		function __hide_dialog(q) {
			q.rmClass("igk-show");
			if (q.data["ms-dialog-dispose"]) {
				q.remove();
				m_dlgx.pop();
			}
			if (m_dlgx.length == 0) {
				igk.winui.events.unregKeyPress(__key_press);
			}
			q.data["ms-dialog-init"] = null;
		}
		function __key_press(evt) {
			switch (evt.keyCode) {
				case 27: // escape				
					var q = m_dlgx.pop();
					if (q)
						__hide_dialog(q);
					evt.preventDefault();
					evt.stopPropagation();

					return !1;
			}
		}
		// show dialog private function
		function __show_dialog(s) {
			s.addClass("igk-show");
			function __init() {
				// init each dialog
				if (!this.data["ms-dialog-init"]) {
					var q = this;
					__setupview(q);
					q.select(".igk-btn-close").each(function () {
						var a = this;
						a.addClass("igk-btn igk-btn-close");
						a.reg_event("click", function (evt) {
							evt.stopPropagation();
							evt.preventDefault();
							__hide_dialog(q);

						});

					});
					// init ms-dialog
					this.data["ms-dialog-init"] = 1;
					if (m_dlgx.length == 0) {
						igk.winui.events.regKeyPress(__key_press);
					}
					m_dlgx.push(q);
				}
				return !0;
			};

			if (s.isSr()) {
				s.each(__init);
			}
			else {
				__init.apply(s);
			}
		};



		// extend winui for winui dialog support
		createNS("igk.winui", {
			showDialog: function (id) {
				var s = $igk(id);

				if (s != null) {
					__show_dialog(s);
				}
			},
			hideDialog: function (id) {
				var s = $igk(id);
				if (s != null) {
					s.each(function () {
						if (this.hide)
							this.hide();
					});
				}
			},
			showDialogUri: function (uri) {
				if (m_d == 1) {
					return;
				}
				m_d = 1;
				igk.ajx.post(uri, null, function (xhr) {
					if (this.isReady()) {
						var n = igk.winui.createDialog();
						n.add("div").setHtml(xhr.responseText);
						igk.dom.body().appendChild(n);
						__show_dialog(n);
						m_d = 0;
					}
				});
			},
			createDialog: function (id, data) {
				var n = igk.createNode("div");
				n.setAttribute("id", id);
				n.addClass("igk-ms-dialog");
				var a = n.add("a").setAttribute("href", "#")
					.addClass("igk-btn-close");
				a.setHtml(igk.R.btn_close);
				a.on("click", function () {
					n.remove();
				});
				n.data["ms-dialog-dispose"] = 1;

				if (data) {
					n.add(data);
				}
				n.init();
				return n;
			}

		});
	})();

	// manage keyPress	- keyUp - define usage key code
	(function () {

		var _keyns = createNS("igk.winui.inputKeys", {
		});
		var _keys = {
			Enter: 13,
			Escape: 27,
			Left: 37, Up: 38, Right: 39, Down: 40
		};
		function _defkey(k) {
			return function () {
				return _keys[k];
			};
		};
		function _getCode(event) {
			return event.charCode || event.keyCode;
		};

		for (var k in _keys) {
			igk_defineProperty(_keyns, k, { get: _defkey(k) });
		}
		//register key event
		createNS("igk.winui", {
			keyPress: function (event, code, callback) {
				var c = _getCode(event);
				if (c == code) {
					callback(event);
					event.preventDefault();
					event.stopPropagation();
				}
			},
			keyUp: function (event, code, callback) {
				var c = _getCode(event);
				if (c == code) {
					callback(event);
					event.preventDefault();
					event.stopPropagation();
				}
			}
		});
	})();

	// Manage printing service
	(function () {
		var m_ptrframe = null;
		createNS("igk.winui", {
			openUrl: function (u, n, p) {
				var hwn = window.open(u, n, p);
				return hwn;
			},
			print: function (u) {
				var _prf = document.domain + "::/prf";
				var _n = (window[_prf] == null);// check for new window
				var _wnd = window[_prf] || window.open(u, _prf, "fullscreen=0, toolbar=0,resizable=0,menubar=0,title=0, width=420, height=500, left=-9999, top=0");
				var _el = igk.winui.reg_event;
				var _echain = null;
				var _tchain = null;
				var _P = {
					resolve: function () {

					},
					then: function (callback) {
						if (_tchain == null)
							_tchain = callback;
						else
							_tchain = function () {
								_tchain.apply(_P);
								callback.apply(_P);
							};
						return this;
					},
					error: function (callback) {
						if (_echain == null)
							_echain = callback;
						else
							_echain = function () {
								_echain.apply(_P);
								_echain.apply(_P);
							};
						return this;
					}
				};
				if (_wnd) {
					try {
						if (_n) {
							_el(_wnd, 'load', function () {
								if (_wnd.hide)
									_wnd.hide();
								_wnd.print();
								// chrome need the control to still be hopen
								if (!igk.navigator.isChrome())
									_wnd.close();
							});

						} else {
							_wnd.location = u; // refresh location
						}
					} catch (ex) {
						console.debug("[igk.winui.print] - can't register load event  - " + ex);
					}
				}
				return _P;
			},
			printUrl: function (u) {
				// print url 
				if (m_ptrframe == null) {
					m_ptrframe = igk.createNode("iframe");
					m_ptrframe.reg_event("load", function () {
						m_ptrframe.o.contentWindow.focus();
						m_ptrframe.o.contentWindow.print();
					});
					m_ptrframe.addClass("dispn");
					igk.dom.body().appendChild(m_ptrframe.o);
				}
				m_ptrframe.o["href"] = u;
				m_ptrframe.o["src"] = u;

			},
			confirm: function (t, m, callback) {// confirm dialog title message
				var div = igk.createNode("div");
				div.setCss({ maxWidth: "480px", margin: "auto" });
				div.add("div").setHtml(m);
				var b = div.add("div");
				b.addClass("igk-action-bar igk-pull-right").setCss({ backgroundColor: "transparent" });
				var btn = b.add("input").setAttributes({ type: 'button', value: 'confirm' }).addClass("igk-btn");
				btn.reg_event("click", function (evt) {
					evt.preventDefault();
					if (callback)
						callback.apply(this, evt);
					igk.winui.notify.close();
				});

				igk.winui.notify.showMsBox(t, div);
			}

		});
	})();

	// utily winui functions
	(function () {
		createNS("igk.winui", {
			'click': function (u) {
				return function (e) {
					e.stopPropagation();
					e.preventDefault();
					var a = document.createElement("a");
					a.href = u;
					igk.dom.body().appendChild(a);
					$igk(a).click();
					// .remove();

				};
			}
		});
	})();

	(function () { // mouse button utility
		var Left = 1;
		var Right = 2;
		var Middle = 3;
		var None = 0;
		function getButton(t) {
			switch (t) {
				case 0:
					return Left;
				case 1:
					return Middle;
				case 2:
					return Right;
			}
			return None;
		};

		var tev = {};
		tev["mousemove"] = function (e) {
			if ((e.buttons == e.button) && (e.button == 0)) {
				return None;
			}

			switch (e.buttons) {
				case 1:
					return Left;
				case 4:
					return Middle;
				case 2:
					return Right;
			}
			return getButton(e.button);
		};
		tev["mousedown"] = function (e) {
			return getButton(e.button);
		};
		tev["mouseup"] = function (e) {
			if ((e.buttons == e.button) && (e.button == 0)) {
				return Left;
			}
			return getButton(e.button);
		};

		igk.winui.mouseButton = function (evt) {
			//if (evt.buttons == 0)
			//return None;
			// for most browser



			if (igk.navigator.isIE() && igk.navigator.IEVersion() < 10) {

				switch (evt.button) {
					case 1:
						return Left;
					case 4:
						return Middle;
					case 2:
						return Right;
				}
			}
			else {

				if (evt.type in tev)
					return tev[evt.type].apply(null, [evt]);

				switch (evt.button) {
					case 0:
						if (evt.buttons == 0 && (evt.type == "mousemove")) {
							return None;
						}
						return Left;
					case 1:
						return Middle;
					case 2:
						return Right;
				}
			}
			return None;
		};
		igk_defineProperty(igk.winui.mouseButton, "Left", { get: function () { return Left; } });
		igk_defineProperty(igk.winui.mouseButton, "Right", { get: function () { return Right; } });
		igk_defineProperty(igk.winui.mouseButton, "Middle", { get: function () { return Middle; } });
		igk_defineProperty(igk.winui.mouseButton, "None", { get: function () { return None; } });

		// empty name space
		createNS("igk.winui.mouseButton", {});


		// event utilitiy
		igk.winui.eventTarget = function (evt) {

			if (evt.target)
				return evt.target;
			if (evt.srcElement)
				return evt.srcElement;
			return null;
		};

		igk.winui.headDocument = function () {
			if (document.head)
				return document.head;
			document.head = document.getElementsByTagName("head")[0];
			return document.head;
		};

	})();


	// dialog manager
	(function () {
		var dialogs = [];
		var init = 0;

		function HDialog(d) {

			var self = this;
			this.closeDialog = function () {
				var q = 0;
				while (q = dialogs.pop()) {
					d.unreg_event(q.closeDialog);
					if (q == d) {
						return 1;
					}
				}
				init = 0;
				igk.winui.events.unregKeyPress(__close_dialog);
				return 0;
			};
		};

		function __close_dialog(e) {

			if ((dialogs.length > 0) && (e.type == 'keyup') && (e.keyCode == igk.winui.inputKeys.Escape)) {
				dialogs[dialogs.length - 1].close();
				e.preventDefault();
				e.stopPropagation();
			}

		};

		igk.system.createNS("igk.winui.dialogs", {
			register: function (d) {
				var _d = new HDialog(d);
				d.on("close", _d.closeDialog);
				dialogs.push(d);
				if (!init) {
					igk.winui.events.regKeyPress(__close_dialog);
					init = 1;
				}
			}

			, getlastDialog: function () {
				if (dialogs.length > 0) {
					return dialogs[dialogs.length];
				}
				return null;
			},
			isOpen: function () {
				return dialogs.length > 0;
			}


		});


	})();


	// balafon form builder engine
	(function () {
		var engines = {};
		var default_e = null;
		igk.system.createNS("igk.winui.engine", {
			register: function (n, o) {
				engines[n] = o;
			},
			setDefaultEngine: function (n) {
				default_e = n;
			},
			getEngine: function (n, t, R) {
				//@R: asset manager for lang support
				n = n || default_e;
				var s = null;
				if (n) {
					if (n in engines) {
						s = engines[n];
						if (t) {
							s.host = t;
						}
						if (R)
							s.setAsset(R);
						return s;
					}
				}
				if (default_e && (n != default_e) && (default_e in engines)) {
					s = engines[default_e];
					if (t)
						s.host = t;
					if (R)
						s.setAsset(R);
					return s;
				}
				return null;
			},
			getEngineNameList: function () {
				var t = [];
				for (var i in engines)
					t.push(i);
				t.sort();
				return t;

			},
			formBuilderEngine: function () {
				//form builder engine constructor
				var host;
				var R = null;
				igk.defineProperty(this, "host", {
					get: function () {
						return host;
					},
					set: function (v) {
						host = $igk(v);
						if (host.o && !host.engine) {
							host.engine = new hostEngine(this);
						}
					}
				});

				this.setAsset = function (r) {
					R = r;
				};

				function _getRes(id) {
					return (R ? R[id] : null) || id;
				};
				this.getRes = _getRes;
				this.add = function (t) {
					this.host.add(t);
					return this;
				};
			}

		});


		function hostEngine() {
			var m_items = {};
			this.getItem = function (n) {
				if (n in m_items)
					return m_items[n];
				return null;
			};
			this.register = function (n, h) {
				m_items[n] = h;
			};
			this.clear = function (n, h) {
				m_items = {};
			};
			this.getFields = function () {
				return m_items;
			};
		};


	})();

	(function () {
		var m_capture;
		var m_r = false;
		var m_event_capture = false;
		var m_bck = {};

		function __capturemouse(evt) {
			if (m_r) {
				evt.stopPropagation();
				return;
			}

			if (m_capture && m_event_capture) {
				var t = null;
				if (typeof (evt.constructor) == 'function') {
					t = new evt.constructor(evt.type, evt);
				} else {
					// ie mouse event
					if (/mouse/.test(evt.type)) {
						t = document.createEvent("MouseEvents");
						t.initMouseEvent(evt.type, evt.bubbles, evt.cancelable, evt.view, evt.detail, evt.screenX, evt.screenY, evt.clientX, evt.clientY, evt.ctrlKey, evt.altKey, evt.shiftKey, evt.metaKey, evt.button, null);
					}
					if (!t)
						return;


				}
				t.EventTarget = m_capture;
				t["igk-event-source"] = "mouse-capture";
				m_r = !0;
				m_capture.dispatchEvent(t);

			}
			evt.stopPropagation();
			m_r = false;
		};


		// mouse capture definition : chrome do not implement capture specification
		igk.system.createNS("igk.winui.mouseCapture", {
			getCapture: function () { //return the current capture object reference
				return m_capture;
			},
			setCapture: function (n) {// set the current capture reference
				// n: target node that handle and capture the mouse. exemple in colorpicker.js script
				if ((n != null) && (m_capture != n)) {
					m_capture = n;
					if (n.setCapture)
						n.setCapture();
					else {
						//chrome not supporting set capture
						m_event_capture = true;
						igk.winui.reg_event(window, "mousemove", __capturemouse);
						igk.winui.reg_event(window, "mouseup", __capturemouse);
						// to correct chrome behaviour
						m_bck["ondragstart"] = m_capture["ondragstart"];
						m_bck["ondrop"] = m_capture["ondrop"];
						m_capture["ondragstart"] = igk.winui.fn.cancelEventArgs;
						m_capture["ondrop"] = igk.winui.fn.cancelEventArgs;

						igk.dom.body().setCss({ pointerEvents: 'none' });
						$igk(n).setCss({ pointerEvents: 'auto' });
					}
				}
			},
			releaseCapture: function () {// free capture
				if (m_capture && m_capture.setCapture) {
					m_capture.setCapture(null);
				}
				if (document.releaseCapture)
					document.releaseCapture();
				if (m_event_capture) {
					igk.winui.unreg_event(window, "mousemove", __capturemouse);
					igk.winui.unreg_event(window, "mouseup", __capturemouse);
					m_capture["ondragstart"] = m_bck["ondragstart"];
					m_capture["ondrop"] = m_bck["ondrop"];
					igk.dom.body().setCss({ pointerEvents: 'auto' });
				}
				m_capture = null;
				m_event_capture = false;
			}
		})
	})();
	(function () {
		createNS("igk.winui.history", {
			push: function (uri, data, t) {


				if (typeof (window.history.pushState) == "function") {
					window.history.pushState(data, t, uri);
				}
				else {
					console.debug("no history state available");
				}
			},
			replace: function (uri, data) {
				if (typeof (window.history.replaceState) == "function") {
					window.history.replaceState(data, null, uri);
				}
				else {
					console.debug("no history state available");
				}
			}
		});
		function __popstate(e) {
			e.preventDefault();
			var u = 0;
			if (e.state) { // safari pop that at first state

				// if ( e.state.src=='balafonjs'){

				// }else{
				// u=e.state.uri;
				// if(u){
				// igk.ajx.get(u,null,igk.ajx.fn.append_to_body);

				// }else{

				// }
			}

			// else{
			// u=(document.location.href+"").split('?')[0];
			// if(u){
			// u+="/default/goback";
			// igk.ajx.get(u,null,igk.ajx.fn.append_to_body);

			// }

			// }
		};
		if ('onpopstate' in window)
			igk.winui.reg_event(window, "popstate", __popstate);

	})();

	(function () {
		// controller setting up
		var m_contextMenu;

		function __ctr() {
			var m_target = igk.createNode("ul");
			m_target.addClass("igk-context-menu");
			var q = this;
			m_target.addClass("posfix");

			function __click(evt) {
				q.close();
				// evt.stopPropagation();
				// evt.preventDefault();
			}

			function __scroll(evt) {
				var b = $igk(q.contextCibling).getScreenLocation();
				var p = q.ciblingpos;
				if ((p.x != b.x) || (p.y != b.y)) {
					q.close();
				}
			}
			function __loadItemTo(n, t) {
				t = $igk(t);
				for (var i = 0; i < t.getChildCount(); i++) {
					__loadItem(n.add("li"), t.o.childNodes[i]);
				}
			};
			function __loadItem(n, t) {
				if (!t.tagName)
					return;
				switch (t.tagName.toLowerCase()) {
					case "li":
						if (t.childNodes.length == 1) {
							if (igk.html.isTextNode(t.childNodes[0])) {
								var ajx = t.getAttribute("ajx") == 1;
								var complete = "ns_igk.ajx.fn.replace_content(this.igk.contextMenu.contextCibling)";
								var c = t.getAttribute("complete");
								if (c) {
									complete = c;
								}
								var g = t.getAttribute("class");
								if (g != null) n.setAttribute("class", g);
								n.add("a")
									.setAttribute("href", t.getAttribute("uri"))
									.appendProperties({ "contextMenu": q })
									.setAttributeAssert(ajx, "onclick", "javascript: ns_igk.ajx.post(this.href,null," + complete + "); return !1;")
									.setHtml(t.innerHTML);
							}
							else {
								__loadItemTo(n.add("ul").addClass("igk-context-sub"), t.childNodes[0]);
							}
						}
						else if (t.childNodes.length > 1) {
							var ul = n.add("ul").addClass("igk-context-sub");
							__loadItemTo(ul, t);
						}
						break;
					case "sep":
						n.addClass("igk-context-menu-sep");
						break;
				}
			}


			igk.appendProperties(this, {
				contextTarget: null,
				contextCibling: null,
				getTarget: function () { return m_target; },
				load: function (d) {
					m_target.setHtml(null);
					var dummy = igk.createNode("div");
					dummy.setHtml(d);
					__loadItemTo(m_target, dummy);

				},
				close: function () {
					// unreg event
					igk.winui.unreg_event(document, "click", __click);
					igk.qselect(".overflow-y-a").unreg_event("scroll", __scroll);
					var q = m_target;
					m_target.addClass("igk-trans-all-200ms").setCss({ "opacity": 0.0 }).timeOut(400,
						function () {
							m_target.rmClass("igk-trans-all-200ms igk-show");
							q.o.parentNode.removeChild(q.o);
							q.clearTimeOut();
						}
					);
				},
				show: function (t, c, l) {
					// t:context target
					// c:cibling		
					// l:location			
					this.contextTarget = t;
					this.contextCibling = c;
					this.pos = l;
					this.ciblingpos = $igk(c).getScreenLocation();
					igk.dom.body().appendChild(m_target.o);

					m_target.addClass("posfix igk-show").setCss({
						left: l.x + "px",
						top: l.y + "px"
					}).addClass("igk-trans-all-200ms").setCss({ opacity: 1 }).timeOut(400, function () {
						m_target.rmClass("igk-trans-all-200ms");
					});
					// register click 



					igk.winui.reg_event(document, "click", __click);
					// register scroll
					igk.qselect(".overflow-y-a").reg_event("scroll", __scroll);
				},
				toString: function () {
					return "igk.winui.contextmenu";
				}
			});

		};

		// init global ctx menu
		m_contextMenu = new __ctr();
		// define global context menu property
		igk_defineProperty(igk.winui, 'contextMenu', {
			get: function () { return m_contextMenu; },
			nopropfunc: function () { this.contextMenu = m_contextMenu; }
		});


		igk.winui.initClassControl("igk-context-menu", function () {
			// init all system class menu
			var id = $igk(this.getAttribute("igk:for"));
			if (!id)
				return;
			var q = this;
			var v = 0;
			q.close = function () {
				igk.winui.unreg_event(document, "click", __q_click);
				igk.qselect(".overflow-y-a").unreg_event("scroll", __q_scroll);
				q.addClass("igk-trans-all-200ms").setCss({ "opacity": 0.0 }).timeOut(400,
					function () {
						q.rmClass("igk-trans-all-200ms igk-show");
						// q.o.parentNode.removeChild(q.o);
						q.clearTimeOut();
					}
				);
				v = 0;
			};
			function __q_click(evt) {

				q.close();
			};
			function __q_scroll(evt) {
				q.close();
			};

			q.show = function () {
				q.addClass("posfix igk-show").addClass("igk-trans-all-200ms").setCss({ opacity: 1 }).timeOut(400, function () {
					q.rmClass("igk-trans-all-200ms");
				});
				// reg event
				igk.winui.reg_event(document, "click", __q_click);
				igk.qselect(".overflow-y-a").reg_event("scroll", __q_scroll);
			};

			$igk(id).reg_event("click", function (evt) {

				if (v == 0) {
					q.show();
					evt.preventDefault();
					evt.stopPropagation();
					v = 1;
				}
			}).setCss({ "cursor": "pointer" });


		}, { desc: "igk context menu" });

	})();


	// notify item controller
	igk.system.createNS("igk.winui.notifyctrl", {
		init: function (t) {
			igk.ready(function () { new igk.winui.notifyctrl.run(t).start(); });
		},
		run: function (target) {
			this.target = $igk(target);
			this.noautohide = this.target.getAttribute("igk-no-auto-hide");
			var q = this;
			this.start = function () {
				var self = this;

				if (q.noautohide == 1)
					return;

				setTimeout(function () {
					igk.animation.fadeout(self.target.o, 20, 500, 1.0, function () {
						self.target.setCss({ "display": "none" }).remove();
					});
				}, 2000);
			};
		}
	});

	createNS("igk.winui.event", {
		stopPropagation: function (e) {
			if ((e != null) && (e.stopPropagation)) {
				e.stopPropagation();
			}
		},
		preventDefault: function (e) {
			if (e) {
				if (e.preventDefault)
					e.preventDefault();
				// no preventDefault function found
			}
		},
		cancelBehaviour: function (e) {
			e.preventDefault();
			e.stopPropagation();
		}

	});

	(function () {
		var m = [];

		createNS("igk.winui.event.fn", {// utility event namespace 
			handleKeypressExit: function (p) {
				m.push(p);
				var fc = function (evt) {
					switch (evt.keyCode) {
						case 27: // escape
							if (igk.winui.framebox.currentFrame == null) {

								var t = m.pop();
								if (p.complete) {
									p.complete.apply(this, [evt]);
									igk.winui.unreg_event(document, "keypress", fc);
								}
							}
							break;
					}
				};
				return fc;
			}
		});
	})();
	igk.winui.controlUtils = {
		HasChildContainPoint: function (item, point) {
			// >item: DomNode
			// >item: offsets point
			var loc = igk.winui.GetRealScreenPosition(item);
			var o = ((point.x >= 0.0) && (point.x <= item.clientWidth) && (point.y >= 0.0) && (point.y <= item.clientHeight));
			return o;
		}
	};

	// define a navigator object
	igk.navigator = new (function () {
		var m_version = "1.2";
		igk_defineProperty(this, 'version', {
			get: function () { return m_version; },
			enumerable: true,
			configurable: true,
			nopropfunc: function () {
				this.m_version = m_version;
			}
		});
	})();
	// init control device
	var XBox360 = false;
	var XBoxOne = false;
	var m_navprop = {};
	var _nav = igk.navigator;

	if (/Xbox/.test(navigator.userAgent)) {
		if (/Xbox One/.test(navigator.userAgent)) {
			XBoxOne = true;
		}
		else {
			XBox = true;
		}
		igk.ready(function () {
			igk.dom.body().addClass("xbox" + (XBoxOne ? 'one' : ''));
		});

	}

	igk_appendProp(_nav,
		{	// static function
			getLang: function () {
				if (window.navigator.language)
					return window.navigator.language + "";
				return window.navigator.languages + "";
			},
			getProperty: function (n) {
				return m_navprop[n] || false;
			},
			isXBoxOne: function () { return XBoxOne; },
			isXBox360: function () { return XBox360; },
			isFirefox: function () {
				return window.navigator.userAgent.indexOf("Firefox/") != -1;
			},
			getFirefoxVersion: function () {
				var i = window.navigator.userAgent.indexOf("Firefox/");
				if (i != -1)
					return /Firefox\/([0-9]+\.[0-9]+)/.exec(window.navigator.userAgent + "")[1];
				return -1;
			},
			getUserMedia: (function () {
				return function _t() {
					var e = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia || navigator.oGetUserMedia;
					if (e)
						return e.apply(navigator, arguments);
					else {
						// alert("No getUserMedia supported");

					}
				}
			})(),
			getScreenWidth: function () { return igk.winui.screenSize().width; },
			getScreenHeight: function () { return igk.winui.screenSize().height; },
			getOrientation: function () { return window.orientation || 0 },
			getDevicePixelRatio: function () { return window.devicePixelRatio || (window.screen.availWidth / document.documentElement.clientWidth) },
			navTo: function (uri) {// navigate to that uri				
				var a = igk.createNode("a");
				a.setCss({ display: "none" });
				a.o.href = uri;
				igk.dom.body().appendChild(a.o);
				a.o.click();
			},
			logAgent: function () {
				var _nav = igk.navigator;
				console.debug("IsChrome: " + _nav.isChrome());
				console.debug("IsFireFox: " + _nav.isFirefox());
				console.debug("IsSafari: " + _nav.isSafari());
				console.debug("IsEdge: " + _nav.isIEEdge());
			},
			isSecure: function () {
				// get if this navigator is secure
				return document.location.protocol == "https:";

			},
			isChrome: function () {
				var ua = window.navigator.userAgent + '';
				if ((ua.indexOf("Chrome/") != -1) && /Google Inc\./.test(window.navigator.vendor)) // for chrome
				{
					return !0;
				}
				return !1;
			},
			chromeVersion: function () {

				if (igk.navigator.isChrome()) {
					var i = window.navigator.userAgent.indexOf("Chrome/");
					return (window.navigator.userAgent + "").substring(i + 7).split(' ')[0];
				}
				return 0;
			},
			isIE: function () {
				var ua = window.navigator.userAgent + '';
				if ((ua.indexOf("MSIE") != -1) || ua.indexOf("Trident/") != -1) // for ie 11
				{
					return !0;
				}
				return !1;
			},
			isIEEdge: function () {
				var ua = window.navigator.userAgent + '';
				if (ua.indexOf("Edge/") != -1)
					return !0;
				return !1;// igk.navigator.isIE() &&(igk.navigator.IEVersion()>=11);
			},
			getEEdgeVersion: function () {
				var i = window.navigator.userAgent.indexOf("Edge/");
				if (i != -1)
					return /Edge\/([0-9]+\.[0-9]+)/.exec(window.navigator.userAgent + "")[1];
				return -1;
			},
			IEVersion: function () {
				if (!igk.navigator.isIE())
					return -1;
				var ua = window.navigator.userAgent + '';
				var i = ua.indexOf("MSIE");
				if (i != -1) {
					return ua.substring(i).split(';')[0].split(' ')[1];
				}
				i = ua.indexOf("Trident/");
				if (i != -1) {
					var v = ua.substring(i + 8).split(';')[0];
					if (v == 7)
						return 11;
				}
				return -1;
			},
			isAndroid: function () {
				var v = (window.navigator.userAgent + "").toLowerCase().indexOf("android");
				if (v != -1) {
					return !0;
				}
				return !1;
			},
			isIOS: function () {
				return 0;
			},
			isSafari: function () { // return real safari web browser
				var i = window.navigator.userAgent.indexOf("Safari/");
				var _nav = igk.navigator;
				if ((i != -1) && /Apple Computer, Inc\./.test(window.navigator.vendor)) {
					return !0;
				}
				return !1;
			},
			SafariVersion: function () {
				var i = window.navigator.userAgent.indexOf("Safari/");
				if (i != -1) {
					return (window.navigator.userAgent + "").substring(i + 7).split(' ')[0];
				}
				return 0;
			},
			FFVersion: function () {
				var i = window.navigator.userAgent.indexOf("Firefox/");
				if (i != -1) {
					return (window.navigator.userAgent + "").substring(i + 8).split(' ')[0];
				}
				return 0;
			},
			$ActiveXObject: function () {
				if ('ActiveXObject' in window)
					return 1;
				return 0;
			},
			toString: function () { return "igk.navigator"; }
		});


	m_navprop.cssDomRequire = _nav.isChrome() || _nav.isFirefox() || _nav.isSafari(); //element must be added to dom before getting css computed style


	createNS("igk.animation",
		{
			init: function (o, interval, duration, initcallbackfunc, updatecallbackfunc, endcallbackfunc, properties) {

				var m_timeout = null; // time out validate
				var m_updatecallback = null;
				var m_o = o;
				var m_interval = interval;
				var m_duration = duration;
				var m_updatecallback = updatecallbackfunc;
				var m_initcallback = initcallbackfunc;
				var m_endcallback = endcallbackfunc;
				var m_ellapsed = 0.0;
				var m_properties = properties; // additional properties
				var m_opupdate = null;
				// anim.init instance constructor
				function __animInstance() {
					this.getOwner = function () { return m_o; };
					this.getInterval = function () { return m_interval; };
					this.getDuration = function () { return m_duration; };
					this.getEllapsed = function () { return m_ellapsed; };
					this.getStepfactor = function () {
						if (m_opupdate) {
							var v_delta = Math.round(m_opupdate(m_ellapsed / m_duration) * 100) / 100;
							return v_delta;
						}
						return this.getEllapsed() / this.getDuration();
					};
					this.start = function () {
						m_initcallback.apply(this);
						m_opupdate = igk.animation.getEffect(this.properties.effect, this.properties.effectmode);
						this.update();
					};
					this.update = function () {
						var self = this;
						m_ellapsed += m_interval;
						if ((m_ellapsed <= m_duration) && (m_updatecallback.apply(this))) {
							// continue
							m_timeout = setTimeout(function () {
								self.update();
							}, this.getInterval());
						}
						else {
							// call end callback
							m_endcallback.apply(this);
						}
					};
					this.stop = function () {
						if (m_timeout) {
							igk.clearTimeout(m_timeout);
							m_timeout = null;
						}
						m_ellapsed = 0;
					};
					this.reset = function () {
						this.stop();
						m_initcallback.apply(this);
					};
					this.toString = function () { return "igk.animation.instance"; };
				};
				return new __animInstance();
			},
			InitBgAdorator: function (element, frequency, interval, fadeinterval, israndom, getimguri, defindex, updateuri) {

				// alert(interval);
				// interval=3000;
				var q = new igk.animation.adorator(element, frequency, interval, fadeinterval, israndom);
				igk.ajx.get(getimguri, null,
					function (xhr) {
						if (this.isReady()) {

							var d = igk.createNode('datas');
							d.setHtml(xhr.responseText);
							var tt = d.getElementsByTagName('igk-img');
							d.select('igk-img').each_all(function () {
								q.add(this.getAttribute("src"));
							});
							// q.loadImages(tt);
							setTimeout(function () {
								q.start(function () { igk.ajx.get(updateuri + q.index, null, null); });
							},
								400);
						}
					}
				);
			},
			// background image adorator
			adorator: function (element, frequency, interval, fadeinterval, israndom) {
				if (element == null)
					throw "element is null";

				this.element = element;

				igk.initprop(this.element);
				this.frequency = frequency;
				this.interval = interval;
				this.fadeinterval = fadeinterval;
				this.israndom = israndom;
				this.images = new Array();
				this.index = 0;
				this.lasttimeout = null;
				this.defaultset = false;
				this.muststart = false;
				this.setDefaultIndex = function (index) {
					if (this.images) {
						if ((index >= 0) && (index < this.images.length)) {
							this.index = index;
						}
						this.defaultset = !0;
					}
				};
				this.toString = function () { return "igk.animation.adorator"; };
				this._setStyle = function (e) {
					e.style.width = "100%";
					e.style.height = "100%";
					// e.style.minHeight="768px";
					// e.style.minWidth="1024px";
					e.style.position = "absolute";
					e.style.top = "0px";
					e.style.left = "0px";
					e.style.display = "block";
					e.className = "igk-bgadorator-block";
				};
				this.loadImages = function (imgs) {
					function __regimage(item, e) {
						item._setStyle(e.o);
						// igk.initprop(e.o);
						item.images[item.images.length] = e;
						e.parentNode.removeChild(e);
					};

					var truncated = false;
					if (imgs.length > 0) {
						for (var i = 0; i < imgs.length; i++) {
							// check for complete
							if (!imgs[i].complete) {
								var self = this;
								var timgs = imgs;
								if (self.loaded) {
									igk.clearTimeout(self.loaded);
								}
								// recall function and check that all image are loaded completly
								self.loaded = setTimeout(function () { self.loadImages(timgs); }, 400);
								truncated = !0;
								return;
							}

						}
					}
					if (!truncated) {
						var e = null;
						this.images = new Array();

						var self = this;
						var c = imgs.length;
						// copy images
						for (var i = 0; i < c; i++) {
							e = imgs[0];
							if (e) {
								__regimage(self, e);
							}
						}
						if (this.muststart) {
							this.start();
						}
					}
					else {
						console.error("Some Image(s) truncated");
					}
				};
				this.add = function (uri) {
					var e = igk.createNode("img");
					e.o.src = uri;

					this._setStyle(e.o);
					this.images[this.images.length] = e;
				};

				this._init = function () {
					if ((!this.images) && (this.images.length <= 0))
						return;
					var t = this.element.getElementsByTagName("img");
					var e = null;
					if (t.length > 0) {
						e = t[0];
					}
					else {
						this.element.insertBefore(this.images[0], this.element.childNodes[0]);
						e = this.images[0];
						igk.animation.fadein(e, this.frequency, this.fadeinterval);
					}
				};
				this.start = function (notifyfunction) {


					if ((!this.images) || (this.images.length <= 0)) {
						this.notifyfunction = notifyfunction;
						this.muststart = !0;
						return;
					}

					var t = this.element.getElementsByTagName("img");
					var e = null;

					if (t.length > 0) {
						e = t[0];
					}
					else {
						var v_re = this.images[0].o;
						var v_ce = this.element.childNodes[0];
						if ((v_re != null) && (v_ce != null)) {
							this.element.insertBefore(v_re, v_ce);
						}

						e = this.images[0].o;
						igk.animation.fadein(e, this.frequency, this.fadeinterval);
					}
					var d = !this.defaultset;
					this.notifyfunction = notifyfunction;

					if (!this.defaultset) {
						this.setDefaultIndex(Math.floor((Math.random() * this.images.length)));
						if (this.notifyfunction) {
							this.notifyfunction();
						}
					}
					var img = this.images[this.index].o;
					if (img && e && (img !== e)) {
						this.element.replaceChild(img, e);
						// if(d)
						igk.animation.fadein(img, this.frequency, this.fadeinterval);
					}

					var self = this;
					if (this.lasttimeout) {
						igk.clearTimeout(this.lasttimeout);
						this.lasttimeout = null;
					}
					this.lasttimeout = setTimeout(function () { self.update(); }, this.interval);

					this.muststart = false;
				};
				this.update = function () {
					var self = this;
					var i = 0;
					if (this.israndom) {
						i = Math.floor((Math.random() * this.images.length));
					}
					else {
						i = ((this.index + 1) % this.images.length);
					}
					if (i == this.index)
						return;

					this.index = i;
					// get image to replace
					var e = this.element.getElementsByTagName("img")[0];
					var img = null;
					if (typeof (e) == IGK_UNDEF) {
						// backerror;
					}
					else {
						img = this.images[this.index];
						if (img == null) {
							throw "element is null at " + this.index;
						}
						if (e != img.o) {
							try {
								if (img)
									$igk(e).insertAfter(img);
								if (e)
									igk.animation.fadeout(e, this.frequency, this.fadeinterval, 1, function () { if (e.parentNode) { e.parentNode.removeChild(e); } });
								if (img)
									igk.animation.fadein(img, this.frequency, this.fadeinterval);
							}
							catch (ex) {
								igk.show_notify_error("Exception",
									"Exception__adorator__ : index=" + this.index + " <br />" + ex +
									"<p class=\"igk-trace\">" + ex.stack + "</p>");
							}

						}
						this.lasttimeout = setTimeout(function () { self.update(); }, this.interval);
						if (this.notifyfunction) {
							this.notifyfunction();
						}
					}
				};
				this.stop = function () {
					if (this.lasttimeout) {
						igk.clearTimeout(this.lasttimeout);
						this.lasttimeout = null;
					}
				};
			},

			// init animation context 
			// >@@ style: fadein fadeout
			// every animation context must have a unique style name in order to be retreive or replace on the animation system
			context: function (element, style, duration, updatetype) {
				if (element == null)
					throw "Context failed : Operation Not Allowed element is null";
				// reg primary context. 
				function __regAnimationContext(context, item, style) {
					var i = null;
					if (typeof (item.igk.animationContext) == igk.constants.undef) {
						// create a new object of the animation context
						i = new __primarycontext(element, style);
						// register new context
						item.igk.animationContext = new function () {
							var m_contexts = [];
							igk.appendProperties(this, {
								pushContext: function (style, context) {
									m_contexts[style] = context;
								},
								getContext: function (style) {
									if (typeof (m_contexts[style]) == IGK_UNDEF) {
										return m_contexts[style];
									}
									return null;
								}
							});
						};
						item.igk.animationContext.pushContext(style, i);
					}
					else {
						// get the init animation context
						i = item.igk.animationContext.getContext(style);
						if (i == null) {// register and push animation context
							i = new __primarycontext(element, style);
							item.igk.animationContext.pushContext(style, i);
						}

					}
					return i;
				}
				// 	primarycontext object
				function __primarycontext(element, style) {
					this.element = element;
					this.style = style;
					this.interval = 20;
					this.duration = duration ? duration : 1000;
					this.updatetype = updatetype || "timeout"; // timeout or interval
					this.step = 0.1;
					this.ellapsed = 0.0;
					this.opacity = 1.0;
					this.endopacity = 1.0;
					this.callback = null;
					this.toString = function () { return "igk.animation.context"; };
					this.start = function () {// start animation function

						switch (this.style) {
							case "fadein":
							case "fadeout":
								this.element.igk.setOpacity(this.opacity);
								break;
						}
						this.step = this.interval / this.duration;
						var q = this;

						if (this.updatetype == "timeout") {
							this.lasttimeout = setTimeout(function () { q.update(); }, this.interval);
						}
						else {
							// register interval function
							this.lasttimeout = setInterval(function () {
								q.update();
								if (q.end) {
									clearInterval(this.lasttimeout);
								}
							}, this.interval);
						}
					};
					this.update = function () {
						var end = false;
						switch (this.style) {
							case 'fadein':
								this.opacity += this.step;
								if (this.opacity > this.endopacity) {
									this.opacity = this.endopacity;
									end = !0;
								}
								break;
							case 'fadeout':
								this.opacity -= this.step;
								if (this.opacity < this.endopacity) {
									this.opacity = this.endopacity;
									end = !0;
								}
								break;
						}
						this.element.igk.setOpacity(this.opacity);
						var q = this;
						if (!end) {
							if (this.lasttimeout) {
								igk.clearTimeout(this.lasttimeout);
							}
							this.lasttimeout = setTimeout(function () { q.update(); }, this.interval);
						}
						else {
							this.stop();
							if (this.callback) {
								this.callback();
							}
						}
					};
					this.stop = function () {
						if (this.updatetype == "timeout") {
							if (this.lasttimeout) {
								igk.clearTimeout(this.lasttimeout);
								this.lasttimeout = null;
							}
						}
						else {
							if (this.lasttimeout) {
								clearInterval(this.lasttimeout);
								this.lasttimeout = null;
							}
						}
					};
				};;

				return __regAnimationContext(this, element, style);
			},

			fadecontext: function (element, style, duration) {
				if (element == null)
					throw "Context failed : Operation Not Allowed element is null";
				igk.animation.context.apply(this, new Array(element, style, duration));
			},
			// >@@ static function
			// >@@ element,refresh interval ,duration of the effect,default opacity,fonction to call on complete
			fadein: function (element, interval, duration, opacity, callback) {
				if (element == null) return;

				var c = igk.animation.context($igk(element).o, 'fadein');
				c.stop();
				c.interval = interval;
				c.duration = duration ? duration : 1000;
				if (typeof (opacity) == "object") {
					c.opacity = opacity.from ? opacity.from : 0;
					c.endopacity = opacity.to ? opacity.to : 1;
				}
				else {
					c.opacity = opacity ? opacity : 0;
					c.endopacity = 1;
				}
				c.callback = callback;
				c.start();
				return c;
			},
			// >@@ static function
			// >@@ element ,refresh interval,duration of the effect,default opacity,call back function ,endopacity
			fadeout: function (element, interval, duration, opacity, callback) {
				if (element == null)
					return;
				var op = 1;
				var ep = 0;
				if (typeof (opacity) == "object") {
					op = opacity.from ? opacity.from : 1;
					ep = opacity.to ? opacity.to : 0;
				}
				else {
					op = opacity ? opacity : 1;
					ep = 0;
				}
				if ((callback == null) && $igk(element).isCssSupportTransition()) {
					$igk(element)
						.setCss({ opacity: op })
						.addClass("igk-transition-easeinout")
						.setTransitionDuration(((duration ? duration : 1000) / 1000.0) + 's')
						.setCss({ opacity: ep });
					return;
				}
				var d = duration ? duration : 1000;
				var c = igk.animation.context($igk(element).o, 'fadeout', d);
				c.stop();
				c.interval = interval;
				c.duration = d;
				c.opacity = op;
				c.endopacity = ep;
				c.callback = callback;
				c.start();
				return c;
			},
			toString: function () {
				return "igk.animation";
			},
			autohide: function (item, startat) {
				setTimeout(function () {
					igk.animation.fadeout(item, 20, 500, 1.0, function () {
						$igk(item).setCss({ "display": "none" });
						$igk(item).remove();

					});
				},
					startat);
			},
			// >@@ animate properties
			animate: function (element, properties, animinfo) {
				if (element == null) {
					return;
				}

				var contextname = igk_getv(animinfo, "context", 'animate');
				var v_animtype = igk_getv(animinfo, "animtype", 'timeout'); // timeout or inteval
				var v_duration = animinfo ? (animinfo.duration ? animinfo.duration : 1000) : 1000;
				var v_animeffect = igk_getv(animinfo, "effect", 'linear');// // easein,easeout,easeinout
				var v_animeffectmode = igk_getv(animinfo, "effectmode", 'easein');
				var v_update = igk_getv(animinfo, "update", null);
				var v_complete = igk_getv(animinfo, "complete", null);

				// if((v_complete==null) && $igk(element).isCssSupportTransition())
				// {
				// $igk(element).addClass("igk-transition-"+v_animeffectmode).setCss(properties);
				// return;
				// }


				var c = igk.animation.context($igk(element).o, contextname, v_duration, v_animtype);

				// stop the previous animation context
				c.stop();
				// setup animation properties
				c.interval = animinfo ? (animinfo.interval ? animinfo.interval : 20) : 20;
				c.callback = animinfo ? (animinfo.complete || null) : null;
				c.updatecallback = animinfo ? (animinfo.update || null) : null;
				var k = new function () {
					this.time = 0; // maintain time
					this.steps = new Array();		// step info of the moved item	
				};
				var s = null;
				var m = null;
				var step = null;
				var out_func = igk.animation.getEffect(v_animeffect, v_animeffectmode);

				for (var i in properties) {
					switch ((i + "").toLowerCase()) {
						case "textshadow":
							s = window.igk.system.styles.textShadowCreate($igk(element).getComputedStyle(i));
							m = window.igk.system.styles.textShadowCreate(properties[i]);
							break;
						case "color":
						case "backgroundcolor":
						case "bordercolor":
							{
								s = window.igk.system.colorFromString($igk(element).getComputedStyle(i));
								m = window.igk.system.colorFromString(properties[i]);
								k.steps[i] = {
									step: {
										// calculate the distance of the 2 animation
										r: (m.r - s.r),// *(c.interval/ c.duration),
										g: (m.g - s.g),// *(c.interval/ c.duration),
										b: (m.b - s.b),// *(c.interval/ c.duration)
										toString: function () { return "(" + this.r + "," + this.g + "," + this.b + ")"; }
									},
									start: s,
									end: m,
									pos: { r: 0, g: 0, b: 0 },
									name: i,
									unit: igk_getUnit(m)
								};
							}
							break;
						default:
							s = igk_getNumber($igk(element).getComputedStyle(i));
							m = igk_getNumber(properties[i], $igk(element), i);

							k.steps[i] = {
								// get distance
								step: (m - s),// (c.interval/ c.duration),
								start: s,
								end: m,
								pos: 0,
								name: i,
								unit: igk_getUnit(m)
							};
					}
				}
				// replace update
				c.update = function () {

					var q = this;
					var end = false;
					var v_delta = 0.0;
					k.time += q.interval; // update the time		
					// calculate the delta
					v_delta = Math.round(out_func(k.time / q.duration) * 100) / 100;

					for (var i in k.steps) {
						if ((i == "length"))
							continue;

						var h = k.steps[i];
						if ((typeof (h.step) == 'undefined') || (h.step == 0))
							continue;


						var item = {};
						var v = 0;
						var key = i.toLowerCase();
						switch (key) {
							case "opacity":// float value
								h.pos = v_delta * h.step;
								v = (h.start + h.pos);
								break;
							case "color":
							case "backgroundcolor":
							case "bordercolor":
								h.pos.r = (v_delta * h.step.r);
								h.pos.g = (v_delta * h.step.g);
								h.pos.b = (v_delta * h.step.b);
								// v=h.start;
								var r = parseInt(h.start.r + h.pos.r);
								var g = parseInt(h.start.g + h.pos.g);
								var b = parseInt(h.start.b + h.pos.b);
								v = new igk.system.color(r, g, b);

								v = v.toHtml();
								break;
							default:
								// h.pos +=h.step;
								h.pos = v_delta * (h.end - h.start);
								v = parseInt((h.start + h.pos)) + "" + h.unit;
								break;
						}
						item[i] = v;
						$igk(this.element).setCss(item);

					}
					if (this.updatecallback) {
						this.ellapsed = k.time;
						this.updatecallback.apply(this);
					}
					end = k.time >= q.duration;
					if (!end) {
						if (q.updatetype == "timeout") {
							if (this.lasttimeout) {
								igk.clearTimeout(this.lasttimeout);
							}
							this.lasttimeout = setTimeout(function () { q.update(); }, this.interval);
						}
					}
					else {
						// end ;								
						$igk(this.element).setCss(properties);
						this.stop();
						if (this.callback) {
							this.callback();
						}
					}
					this.end = end;
				};
				c.start();
				return c;
			},
			animateUpdate: function (element, animinfo) {// used to animate with a custom update function
				if ((element == null) || (animinfo == null) || (typeof (animinfo.update) === IGK_UNDEF))
					return;
				var contextname = igk_getv(animinfo, "context", 'animateUpdate');
				var v_animtype = igk_getv(animinfo, "animtype", 'timeout');
				var v_duration = animinfo ? (animinfo.duration ? animinfo.duration : 1000) : 1000;
				var c = igk.animation.context($igk(element).o, contextname, v_duration, v_animtype);
				c.stop();
				c.interval = animinfo ? (animinfo.interval ? animinfo.interval : 20) : 20;
				c.duration = v_duration;
				c.callback = animinfo ? animinfo.complete : null;
				var k = new function () {
					this.time = 0; // maintain time
				};

				// replace update
				c.update = function () {
					var q = this;
					var end = false;
					k.time += q.interval;

					animinfo.update.apply(this, [k]);
					end = k.time >= q.duration;
					if (!end) {
						if (this.lasttimeout)
							igk.clearTimeout(this.lasttimeout);
						this.lasttimeout = setTimeout(function () { q.update(); }, this.interval);
					}
					else {
						$igk(this.element).setCss(properties);
						this.stop();
						if (this.callback) {
							this.callback();
						}
					}
				};
				c.start();
				return c;
			}
			, getAnimationFrame: function () {

				return igk.fn.getWindowFunc("requestAnimationFrame", __getAnimationFrame);
			},
			getAnimationCancelFrame: function () {
				return igk.fn.getWindowFunc("cancelAnimationFrame", __cancelAnimationFrame);
			}
		});

	var m_animFrames = [];
	var lastTime = 0;
	function __getAnimationFrame(callback) {
		var currTime = new Date().getTime();
		var timeToCall = Math.max(0, 16 - (currTime - lastTime));
		var id = window.setTimeout(function () {
			callback(currTime + timeToCall);
		},
			timeToCall);
		lastTime = currTime + timeToCall;
		return id;
	}
	function __cancelAnimationFrame(id) {
		window.clearTimeout(id);
	}


	igk_appendProp(igk.animation, {
		effects: new function () {
			igk_appendProp(this, {
				"linear": function (progress) {
					return progress;
				},
				"quad": function (progress) {
					return Math.pow(progress, 2);
				},
				"quint": function (progress) {
					return Math.pow(progress, 2);
				},
				"pow": function (progress, pow) {
					return Math.pow(progress, pow || 2);
				},
				"circ": function (progress) {
					return 1 - Math.sin(Math.acos(progress));
				},
				"back": function (progress) {
					return Math.pow(progress, 2) * ((1.5 + 1) * progress - 1.5);
				},
				"bounce": function (progress) {
					for (var a = 0, b = 1, result; 1; a += b, b /= 2) {
						if (progress >= (7 - 4 * a) / 11) {
							return -Math.pow((11 - 6 * a - 11 * progress) / 4, 2) + Math.pow(b, 2);
						}
					}
				},
				"makeEaseInOut": function (delta) {
					return function (progress) {
						if (progress < .5)
							return delta(2 * progress) / 2;
						else
							return (2 - delta(2 * (1 - progress))) / 2;
					};
				},
				"makeEaseOut": function (delta) {
					return function (progress) {
						return 1 - delta(1 - progress);
					};
				}
				, toString: function () { return "igk.anmation.effects"; }
			});
		},
		getEffect: function (animeffect, animeffectmode) {
			if (!animeffect)
				animeffect = "linear";
			if (!animeffectmode)
				animeffect = "easein";
			var out_func = igk.animation.effects[animeffect];
			switch (animeffectmode) {
				case "easein":// no reverse effect
					break;
				case "easeout":
					out_func = igk.animation.effects.makeEaseOut(out_func);
					break;
				case "easeinout":
					out_func = igk.animation.effects.makeEaseInOut(out_func);
					break;
			}
			return out_func;
		}
	});

	createNS("igk.validator", {
		isUri: function (s) {
			return /(http(s){0,1}|ftp|file):\/\/(.)+$/.test(s);
		},
		toString: function () { return igk.validator.fullname; }
	});





	function __ajx_initfunc(func) {// get ajx func request
		if (typeof (func) == 'string') {
			var g = $igk(func);
			if (g.getCount() > 0) {
				func = function (xhr) {
					if (this.isReady()) {
						g.setHtml(xhr.responseText).init();
					}
				};
			}
		}
		return func || igk.ajx.fn.replace_or_append_to_body;
	};


	(function () {
		var m_ajx_monitorListener; // for monitoring
		var m_hxhr; // store xhr instance for the ready state change
		var sm_join = {};
		var m_ajxhe = 0; // object of {xhr header}
		var m_exajxdata = 0; // {responseType:'text/plain|blob|arraybuffer'}


		function __ajx_setExtraData(ajx, extradata) {
			extradata = extradata || m_exajxdata;
			if (extradata) {


				igk_appendProp(ajx, extradata);
				if ('responseType' in extradata) {
					ajx.xhr.responseType = extradata.responseType;
				}
			}
			m_exajxdata = 0;
		};
		createNS("igk.ajx",
			{
				bindHeader: function (p) { //bind properties to next ajx header
					m_ajxhe = p;
				},
				bindExtraData: function (exdata) { // bind extra data that will be used for ajx request
					//object property or null 
					m_exajxdata = exdata;

				},
				setHeader: function (xhr) {
					// ini ovh must be the minus sign
					xhr.setRequestHeader("IGK-X-REQUESTED-WITH", "XMLHttpRequest");
					if (m_ajxhe) {
						for (var m in m_ajxhe) {
							xhr.setRequestHeader(m, m_ajxhe[m]);
						}
						m_ajxhe = 0;
					}
				},
				setMonitorListener: function (monitor) {// static func
					m_ajx_monitorListener = monitor;
				},
				isMonitoring: function () {// static func
					return m_ajx_monitorListener != null;
				},
				getCurrentXhr: function () {
					return m_hxhr;
				},
				GetParentHost: function () {
					return m_hxhr ? m_hxhr.source : null;
				},
				eval: function (n) {// evaluate only script content. not forcing file to download again and initialize

					if (typeof (n) != "string") {

						if (n.tagName && n.tagName.toLowerCase() == "script") {
							var pn = n.parentNode ? n.parentNode : igk.dom.body().o;
							try {
								var v_script = $igk(n).getHtml();
								if (igk.system.string.trim(v_script).length > 0) {
									igk.eval(v_script, pn, n);
								}
								// remove useless
								if (n.getAttribute("autoremove"))
									$igk(n).remove();
							} catch (ex) {
								// for chrome disable code extension in some case.
								console.debug(ex);
							}
						}
						else {



							igk_preload_image(n); // preload n
							// igk_eval_all_script(n);					
							var ct = $igk(n).select("script");

							ct.each(function () {

								var _t = this.getAttribute("type");
								// var _s=this.getAttribute("src"); 
								if (_t == 'text/balafonjs') {
									__bindbalafonjs.apply(this);
								}
								else {
									// eval all script

									var v_script = this.getHtml();
									try {
										igk_eval(v_script, this.o);
									} catch (ex) {
										// for chrome disable code extension in some case.					
										console.debug(ex);
									}
								}
								return this;
							});
						}
					}
				},
				// replace entire body with the content of text	
				replaceBody: function (text, unregister) {
					var c = window.igk.utils.getBodyContent(text);

					try {
						// igk_freeEventContext();		
						// igk.ctrl.clearAttribManager();
						if (unregister) {
							igk.winui.getEventObjectManager().unregister(document);
							igk.winui.getEventObjectManager().unregister(window);
							igk.winui.getEventObjectManager().unregister_child(igk.dom.body().o);
						}
						// clear body content
						igk.dom.body().setHtml("");
						// set the new content
						igk.dom.body().setHtml(c);
						// force call of ready on function
						// int body node
						igk.ajx.fn.initnode(igk.dom.body().o);
						// raise body replaced event
						igk.publisher.publish(igk.evts.dom[1], { evt: { text: text, target: igk.dom.body().o } });
						// igk.ready();
					}
					catch (ex) {
						igk.winui.notify.showErrorInfo("Javascript Exception", "replaceBody Evaluation failed <br />" + ex);
						console.error(ex);
					}

				},
				a_postResponse: function (a, parentTag) {
					window.igk.ajx.post(a.href, null, new window.igk.ajx.targetResponse(a, parentTag).update);
					return !1;
				},
				a_getResponse: function (a, parentTag) {
					window.igk.ajx.get(a.href, null, new window.igk.ajx.targetResponse(a, parentTag).update);
					return !1;
				},
				aposturi: function (uri, targetNodeId) {// post uri and set response to targetNodeId
					window.igk.ajx.post(uri, null, function (xhr) { if (this.isReady()) { this.setResponseTo(document.getElementById(targetNodeId), true); } });
				},
				ageturi: function (uri, targetNodeId) {// get uri and set response to targetnodeid
					var q = $igk(targetNodeId);
					if (q && uri) {
						igk.ajx.get(uri, null, function (xhr) {
							if (this.isReady()) {
								this.replaceResponseNode(q.o);
							}
						});
					}
				},
				// create a response node;
				responseNode: function (nodeId) {
					var m_target = document.getElementById(nodeId);
					if (m_target) {
						window.igk.appendProperties(this, {
							"response": function (xhr) {
								if (this.isReady()) {
									this.setResponseTo(m_target, true);
								}
							}
						});
					}
				},
				getResponseNodeFunction: function (cibling, parentNodeTag) {
					var b = $igk(cibling).getParentByTagName(parentNodeTag);
					if (b) {

						return function (xhr) {
							if (this.isReady()) {
								this.setResponseTo(b);
							}
						};
					}
					return null;
				},
				// create a new response bodyset the response body
				responseBody: function () {
					window.igk.appendProperties(this, {
						"response": function (xhr) {
							if (this.isReady()) {
								window.igk.ajx.replaceBody(xhr.responseText, true);
							}
						}
					});
				},
				targetResponse: function (item, parentTag) {// object used to set response of ajx query
					var m_target = null;
					if (parentTag) {
						m_target = $igk(item).getParentByTagName(parentTag);
					}
					else
						m_target = item;
					if (m_target == null)
						return null;

					window.igk.appendProperties(this,
						{
							update: function (xhr) {
								// update the response string
								if (this.isReady()) {
									this.setResponseTo(m_target, true);
								}
							},
							toString: function () {
								return "igk.ajx.targetResponse"
							}
						});
				},
				ajx: function (monitorlistener) {// ajx object
					var xhr = null;
					if (igk.isFunc(typeof (window.XMLHttpRequest)))    //  Objet standard
					{
						xhr = new window.XMLHttpRequest();     //  Firefox,Safari,...
					}
					else {
						if (igk.navigator.$ActiveXObject())              //  Internet Explorer
						{
							try {
								xhr = new ActiveXObject("Microsoft.XMLHTTP");
							}
							catch (ex) {
								throw ("No Ajax Support");
							}
						}
					}


					if ((xhr == null) || (xhr.readyState + "" == igk.constants.undef)) {
						throw ("No Ajax Support");
					}

					this.xhr = xhr;
					this.saveState = false;
					this.uri = null;
					this.postargs = null;
					this.method = "GET";
					this.synchronize = false;

					this.setResponseMethod = function (method) { // instructions de traitement de la réponse 
						var q = this;
						if (method) {
							switch (typeof (method)) {
								case "function":
									q.responseMethod = method;
									q.xhr.onreadystatechange = function () {
										// try{	
										if (q.responseMethod) {
											m_hxhr = q;
											igk.context = 'xhr';
											q.responseMethod(this);
											m_hxhr = null;
											igk.context = null;
										}
										// }
										// catch(ex)
										// {				
										// igk.winui.notify.showErrorInfo("Exception",
										// "<h3>AJX:__setResponseMethod__</h3><div>"+ ex +"<div><code>Trace : " +ex.trace+"</code> <pre style='text-overflow:ellipsis; overflow:hidden; max-height:4em;'>"+ 
										// igk.html.string(this.responseText)
										// +"</pre><p class=\"igk-trace\" style='padding-top:1.3em' ><pre style='max-height:3em; overflow-y:auto;'>"+ex.stack
										// +"</pre></p>"
										// // + " <div style='color:#222'>"+q.responseMethod+"</div>"
										// );
										// }
									};
									break;
								case 'object':


									var fc = method.complete ? function () { method.complete.apply(method, [q.xhr, method]); } : null;
									q.xhr.onreadystatechange = null;// ,fc;
									q.xhr.onload = fc;// method.complete? function(){ method.complete.apply(method, [q.xhr, method]);  }; // method.complete;
									q.xhr.onerror = method.error;
									break;
							}
						}
					};
					function __initheader() {
						// migration of apache request that name must not contains underscore or will be ignored
						igk.ajx.setHeader(this.xhr);
						if (igk.isFunc(typeof (document.domain)))
							this.xhr.setRequestHeader("IGK-FROM", "gaga"); //document.domain
						else
							this.xhr.setRequestHeader("IGK-FROM", "igkdev");

						//this.xhr.setRequestHeader("IGK-FROM-DATA","ONE");
					};


					this.send = function (method, url, postargs, sync) {

						this.uri = url;
						// igk.constants constant definition
						this.asynchronize = typeof (sync) == igk.constants.undef ? true : sync;
						this.postargs = postargs;
						this.method = method;
						this.xhr.open(method, url, this.asynchronize);


						// this.xhr.setRequestHeader("Content-Type","multipart/form-data");   		
						// this.xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded;charset=iso-8859-1");

						// TODO: help microsoft to create a xml in result

						if (this["ajx.xhr"]) {
							igk_appendProp(this.xhr, this["ajx.xhr"]);
						}
						// this.xhr.responseType="msxml-document";

						// default request header
						__initheader.apply(this);
						if (postargs) {
							if (typeof (postargs) == "string") {
								// for url encoding
								this.xhr.setRequestHeader("Content-Type", this.contentType || "application/x-www-form-urlencoded;charset=iso-8859-1");
								// chrom refuse to set those header. type is marked as unsafe
								// this.xhr.setRequestHeader("Content-length",postargs.length);
								// this.xhr.setRequestHeader("Connection","close");
							}
						}
						if (monitorlistener) {
							this.xhr.onloadstart = monitorlistener.loadstart;
							this.xhr.onloadend = monitorlistener.loadend;
							this.xhr.onprogress = monitorlistener.loadprogress;
						}
						this.xhr.send(postargs);
					};


				},
				postWebRequest: function (uri, action, param, func, headers, async, savestate, extradata) {// for service 
					if (typeof (async) == igk.constants.undef)
						async = !0;
					var ajx = new igk.ajx.ajx();
					ajx.saveState = (savestate) ? true : false;
					__ajx_setExtraData(ajx, extradata);

					igk.appendProperties(ajx, {
						serviceResponse: function () {
							var d = document.createElementNS(igk.namespaces.xhtml, "dummy");
							$igk(d).setHtml(ajx.xhr.responseText);
							var r = $igk(d).select(action + "_result").first();
							if (r) {
								return r.o.innerHTML;
							}
							return 0;
						}
					});

					ajx.setResponseMethod(__ajx_initfunc(func));// || igk.ajx.fn.replace_or_append_to_body);
					ajx.xhr.open("POST", uri, async);
					// ajx.xhr.setRequestHeader("Content-Type", "text/html");
					ajx.xhr.setRequestHeader("Content-Type", "text/plain");
					igk.ajx.setHeader(ajx.xhr);
					ajx.xhr.setRequestHeader("SOAPACTION", action);

					// if (headers) {
					// for (var i in headers) {
					// ajx.xhr.setRequestHeader(i, headers[i]);
					// }
					// }

					var v_params = ""; // params to send 		
					for (var i in param) {
						v_params += "<" + i + ">" + param[i] + "</" + i + ">";
					}
					var v_body = "<" + action + ">" + v_params + "</" + action + ">";

					// build packet to send
					var packet = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body>' + v_body + '</soap:Body></soap:Envelope>';

					ajx.xhr.send(packet);
					return ajx;
				},
				post: function (uri, param, func, async, savestate, extradata) {
					return igk.ajx.send(uri, 'POST', param, func, async, savestate, extradata);
				},
				apost: function (uri, param, func, savestate, extradata) {
					return this.post(uri, param, func, true);
				},
				get: function (uri, param, func, async, savestate, extradata) {

					// extradata used to init ajx object
					var ajx = new igk.ajx.ajx(m_ajx_monitorListener);
					if (typeof (async) == igk.constants.undef)
						async = !0;
					ajx.saveState = (savestate == igk.constants.undef) ? true : false;
					__ajx_setExtraData(ajx, extradata);
					ajx.setResponseMethod(__ajx_initfunc(func));
					ajx.send("GET", uri, param, async);
					return ajx;
				},
				aget: function (uri, param, func, extradata) {
					this.get(uri, param, func, true, extradata);
				},
				join: function (n) {
					if (n in sm_join) {
						sm_join[n].abort();
					}
					sm_join[n] = this;
					return igk.ajx;
				},
				send: function (uri, method, param, func, async, savestate, extradata) {
					//document.write("sending ===== : "+uri);
					//return null;
					if (typeof (async) == igk.constants.undef)
						async = !0;
					var contentType = null;
					if (typeof (uri) == 'object') {
						method = uri.method || method;
						param = uri.param || param;
						func = uri.func || func;
						contentType = uri.contentType || contentType;
						uri = uri.uri || uri;

					}    
					var ajx = null;
					try {


						ajx = new igk.ajx.ajx(m_ajx_monitorListener);
						ajx.saveState = (savestate) ? true : false;
						__ajx_setExtraData(ajx, extradata);
						ajx.setResponseMethod(__ajx_initfunc(func));
						ajx.contentType = contentType;

						ajx.send(method, uri, param, typeof (async) == igk.constants.undef ? !0 : async);

					}
					catch (e) {
						igk.log.write("ajx log error : Error ::::: ");
						igk.show_notify_prop_v(e);
					}
					return ajx;
				},
				postform: function (form, uri, func, sync) {
					if (!form)
						return;
					uri = uri || form.getAttribute("action");
					var method = form.getAttribute("method") || "POST";;
					var msg = "";
					var e = null;
					var p = [];
					if (window.tinyMCE) { // to update the tinyMce before update
						window.tinyMCE.triggerSave();
					}
					var fc = method=="POST"?igk.ajx.post :igk.ajx.get;
						

					function __appendForm(id, value) {
						if (p[id]) {
							var tab = p[id];
							if (igk_is_array(tab)) // array
							{
								tab.push(value);
							}
							else {
								var tt = [];
								tt.push(tab);
								tt.push(value);
								p[id] = tt;
							}
						}
						else
							p[id] = value;
					}

					// prepend waiter
					if (igk.winui.lineWaiter) {
						var lw = igk.winui.lineWaiter.prependTo(form);
						var tf = func;
						$igk(form).select(".actions").addClass("dispn");
						func = function (xhr) {
							if (tf) {
								tf.apply(this, [xhr]);
							}
							else {
								console.debug("there is no function callback");
							}
							if (this.isReady()) {
								lw.remove();
								$igk(form).select(".actions").rmClass("dispn");
							} else if (xhr.readyState == 4) {
								lw.remove();
							}
						};
					}
					if (window.FormData) {
					
						// if supporting FormData
						var frmData = null;
						if (form.getAttribute("enctype") == "application/json") {
							var obj = 0;
							var data = 0;
							if (obj = form.getAttribute("form-data")) {
								data = obj;
								if (typeof (data) == 'object')
									data = JSON.stringify(data);
							} else {
								obj = {};
								igk.winui.form.serialize(form, obj); 
								data = JSON.stringify(obj);
							}
							igk.ajx.send({
								uri: uri,
								method: method,
								contentType: 'application/json',
								func: function (xhr) {
									if (this.isReady()) {
										if (func) {
											func.apply(this, [xhr]);
										}
									}
								},
								param: data  // '{requestInformation:[]}'
							});
						} else {
							frmData = new FormData(form);
							if (window.event && window.event.submitter){
								var i = window.event.submitter;
								var n = i.getAttribute("name");
								if (n){
									frmData.append(n, i.getAttribute("value"));
								}
							}else {							
								form.querySelectorAll("input[type='submit']").forEach(function(i){									
									var n = i.getAttribute("name");
									if (n){
										frmData.append(n, i.getAttribute("value"));
									}
								});
							}
							// $igk(form).select("^.igk-winui-panel-dialog")
							// console.debug("send....form data", form.getAttribute("enctype") ,frmData, form );
							// return;
							// uri = "http://local.com:8801/data.php";
							fc(uri, frmData, func, (sync == igk.constants.undef) ? sync : true, true, {
								source: $igk(form) // setting the source of the current definition
							}); 
						}
					}
					else {
					 
						for (var i = 0; i < form.length; i++) {
							e = form.elements[i];

							switch (e.type) {
								case "radio":
								case "checkbox":
									if (e.checked) {
										__appendForm(e.id, e.value);
									}
									break;
								case "file":
									if (e.files.length > 0) {
										frmData.append(e.id, e.files[0]);
									}
									break;
								default:
									__appendForm(e.id, e.value);
									break;
							}
						}
						e = 0;
						for (var i in p) {
							if (e != 0)
								msg += "&";


							if (igk_is_array(p[i])) {
								for (var t in p[i]) {
									if (e != 0)
										msg += "&";
									msg += i + "=" + p[i][t];
									e = 1;
								}
							}
							else
								msg += i + "=" + p[i];
							e = 1;
						}
						fc(uri, msg, func, (sync == igk.constants.undef) ? sync : true, true, {
							source: form
						});
					}

				},
				asyncget: function (uri, param, mimetype) {
					var Prom = window.Promise;
					if (!Prom)
						return false;

					try {
						var b = igk.dom.body(); // body document not found
					}
					catch (ex) {
						//used ajx to get data
						var p = new Promise(function (r, j) {

							var fc = function (o) {
								r(o);
							};
							fc.error = function () {
								j('[BJS] failed to get : ' + uri);
							};


							igk.ajx.get(uri, null, function (xhr) {
								console.debug("for : " + xhr.status);
								if (this.isReady()) {
									fc(xhr.responseText);
								}
							});
						});
						return p;
					}
					//use ob to get data
					mimetype = mimetype || 'text/plain';
					var p = new Promise(function (r, j) {
						var fc = function (o) {
							r(o.data);
						};
						fc.error = function () {
							j(new Error('[BJS] - objdata failed: ' + uri + ', ' + mimetype));
						};

						igk.system.io.getData(uri, fc, mimetype);
					});
					return p;

				},
				uploadInputFile: function (inputfile, uri, async, responseCallback, startcallBack, progressCallback, doneCallback) {
					if (inputfile.files.length > 0)
						igk.ajx.uploadFile(null, inputfile.files[0], uri, async, responseCallback, startcallBack, progressCallback, doneCallback);
				},
				uploadFile: function (osrc, file, uri, async, responseCallback, startcallBack, progressCallback, doneCallback, method) {
					if (!file) {
						console.error("/!\\ file not define");
						return;
					}

					// method: blob or null
					var bob = null;
					var reader = null;
					var v_ajx = new igk.ajx.ajx();
					v_ajx.source = osrc;
					var self = this;
					var xhr = v_ajx.xhr;
					var m = method ? method : 'blob';
					responseCallback = responseCallback || igk.ajx.fn.replace_or_append_to_body;

					if (igk.navigator.isIE() && igk.navigator.IEVersion()) {
						v_ajx.setResponseMethod(function (xhr) {
							if (this.isReady()) {
								// get file....
								igk.ajx.post(uri + '&ie=1', null, responseCallback);
							}
						});
					}
					else {
						v_ajx.setResponseMethod(responseCallback);
					}

					// 
					// var async=!0;// important to view progression .on chrome
					xhr.open("POST", uri, async);// async);// devnull.php");


					// return;
					v_ajx.xhr.upload.onerror = function (evt) {
						igk.winui.notify.showErrorInfo(
							"Error",
							"/!\\ uploaded failed: " + uri + "<br />" +
							" " + evt);
						console.debug("Error : " + v_ajx.xhr.statusText);
						console.debug(v_ajx.xhr);


					};

					// async registration function
					// v_ajx.xhr.onprogress=function(evt){
					// };
					// async registration function

					if (async) {
						v_ajx.xhr.upload.onprogress = function (evt) {
							if (progressCallback) {
								progressCallback.apply(this, arguments);
							}
						};
						v_ajx.xhr.upload.onload = function (evt) {
							if (doneCallback) {
								doneCallback.apply(this, arguments);
							}
						};
						v_ajx.xhr.upload.onloadstart = function (evt) {
							if (startcallBack) {
								startcallBack.apply(this, arguments);
							}
						}
					}
					xhr.overrideMimeType('text/plain; charset=x-user-defined-binary');
					xhr.setRequestHeader("Cache-Control", "no-cache");

					igk.ajx.setHeader(xhr);

					xhr.setRequestHeader("IGK-UPLOADFILE", true);
					xhr.setRequestHeader("IGK-FILE-NAME", file.name);
					xhr.setRequestHeader("IGK-UP-FILE-SIZE", file.size); // file.file[0] size);
					xhr.setRequestHeader("IGK-UP-FILE-TYPE", file.type);
					// xhr.setRequestHeader("Content-Type","multipart/form-data");
					// xhr.setRequestHeader("Content-Type","multipart/form-data;charset=utf-8; boundary=" + Math.random().toString().substr(2));
					xhr.setRequestHeader("Content-Type", "charset=utf-8; boundary=" + Math.random().toString().substr(2));
					//"Content-type","multipart/form-data; charset=utf-8; boundary=" + Math.random().toString().substr(2));





					var r = file;
					var filer = false;
					if (m != 'blob') {

						if (typeof (FileReader) != IGK_UNDEF)
							filer = new FileReader();
						if (!filer) {
							return;
						}
						// 1.
						reader = filer;
						reader.onprogress = function (evt) {
							console.debug("progress read");

						};
						reader.onload = function (evt) {
							// igk.winui.notify.showMsg("<div class=\"igk-notify igk-notify-default\">"+ igk.html.getDefinition(xhr)+"</div>");
							var t = igk.winui.eventTarget(evt);
							if (xhr.sendAsBinary)// mod 
								xhr.sendAsBinary(t.result);
							else {// ie and other				
								xhr.send(t.result);
							}
						};
						if (reader.readAsBinaryString)
							reader.readAsBinaryString(r);
						else
							reader.readAsText(r);
						
					}
					else {

						if (r.slice) {
							bob = r.slice(0, file.size);
							// important to avoid
							try {


								xhr.send(bob);
							}
							catch (ex) {
								igk.winui.notify.showErrorInfo("JS Exception ", "" + ex);
							}
						}
					}
				},
				load: function (f, callback) {
					var async = 0;
					var param = null;
					var ajx = new igk.ajx.ajx();
					ajx.saveState = 1;
					ajx.setResponseMethod(callback);// func || igk.ajx.fn.replace_or_append_to_body);		
					ajx.send("GET", f, param, async);
					return ajx;
				},
				toString: function () { return "igk.ajx"; },
				globalMonitorListener: function () {
					// object		
					igk.appendProperties(this, {
						loadstart: function (evt) { igk.publisher.publish('sys://ajx/loadstart', { evt: evt }); },
						loadend: function (evt) { igk.publisher.publish('sys://ajx/loadend', { evt: evt }); },
						loadprogress: function (evt) {
							igk.publisher.publish('sys://ajx/loadprogress', { evt: evt });
						}
					});
				}
			});

		createNS("igk.ajx", {
			fetch: function (uri, data) {
				if (!window.Promise) {
					throw ("Promise not found - old browser");
				}
				var p = new Promise(function (resolv, reject) {
					igk.ajx.aget(uri, data, function (xhr) {
						if (this.isReady()) {
							resolv.apply(xhr);
						} else if (xhr.readyState == 4) {
							reject.apply(xhr);
						}
					});
				});
				return p;
			},
			postFormData: function (uri, data) {
				var f = igk.dom.body().add("form");
				f.setAttribute("action", uri);
				f.setAttribute("method", "POST");
				f.setAttribute("enctype", "multipart/form-data");
				for (var i in data) {
					//console.debug(typeof(data[i]));
					f.add("input").setAttribute("name", i)
						.setAttribute("value", (typeof (data[i]) == 'object') ? JSON.stringify(data[i]) : data[i]);
				}
				f.o.submit();
			}
		});

		m_ajx_monitorListener = new igk.ajx.globalMonitorListener();

		igk_appendProp(igk.ajx.ajx.prototype,
			{

				isReady: function () {
					return (this.xhr.readyState == 4) && (this.xhr.status == 200);
					// (
					// (( this.xhr.readyState == 4) && (this.xhr.status == 200)) 
					// || // for chrome
					// ((this.xhr.readyState == 4) && (this.xhr.status == 200) && (/^(OK|No Error)/.test(this.xhr.statusText) ))
					// );
				},
				isFailed: function () {
					if ((this.xhr.readyState == 4))
						return (this.xhr.status != 200);
					return true;
				},
				toString: function () { return "igk.ajx"; },
				setResponseTo: function (q, unregister) { // q is node
					if (q && (typeof (q.innerHTML) != IGK_UNDEF)) { // set response to node
						if (q == igk.dom.body().o) {

							this.replaceBody();
						}
						else {
							// unregister childs
							if (unregister) {
								igk.winui.getEventObjectManager().unregister_child(q);
							}
							q.innerHTML = this.xhr.responseText;
							igk.ajx.fn.initnode(q);
						}
					}
				},
				replaceBody: function () {
					igk.ajx.replaceBody(this.xhr.responseText, true);
				},
				replaceResponseNode: function (node, preload) {// replaceNode ,preload document. default is true
					var i = igk.createNode("dummy");
					var p = typeof (preload) == igk.constants.undef ? !0 : preload;
					// this.setResponseTo(i.o);
					i.setHtml(this.xhr.responseText);
					if (node && node.parentNode) {
						// load childs
						var b = i.firstChild();
						var g = false;
						var pt = $igk(node.parentNode);
						var ol = null;
						var f = 0;
						//var marker = node.insertBefore('div');
						var _ini = [];
						if (node.previousSibling == null) {
							while (i.o.lastChild) {
								_ini.push(i.o.lastChild);
								pt.o.insertBefore(i.o.lastChild, pt.o.firstChild);
							}
						}
						else {
							var c = node.previousSibling;
							while (i.o.lastChild) {
								_ini.push(i.o.lastChild);
								pt.o.insertBefore(i.o.lastChild, c);
							}
						}

						$igk(node).remove();
						for (var i = 0; i < _ini.length; i++) {
							if (_ini[i].nodeType == 1)
								igk.ajx.fn.initnode(_ini[i]); // .push(i.o.lastChild)
						}

						//TODO : replace node


						// while (b != null) {
						// if (!g) {
						// pt.o.replaceChild(b.o, node);
						// f = b;
						// }
						// else {
						// // pt.o.appendChild(b.o);// b.insertAfter(ol.o);
						// ol.insertAfter(b.o);
						// }
						// igk.ajx.fn.initnode(b.o);
						// g = !0;
						// ol = b;
						// // i.o.removeChild(b.o);
						// b = $igk(i.o.firstChild); // firstChild();
						// }

						// if (p && g) {
						// __applyPreloadDocument(document);
						// }
						return f;
					}
				}

				, appendResponseTo: function (q) {

					var s = igk.createNode("dummy");
					s.setHtml(this.xhr.responseText);
					var f = s.o.firstChild;
					var h = s.o.firstChild;
					while (f) {
						q.appendChild(f);
						igk.ajx.eval(f);
						f = f.nextSibling;
					}
				},
				prependResponseTo: function (q) {
					var s = igk.createNode("dummy");
					s.o.innerHTML = this.xhr.responseText;
					var m = q.o.firstChild;
					var f = s.o.firstChild;
					var c = f;
					while (f) {
						c = c.nextSibling;
						q.insertBefore(f, m);
						igk.ajx.eval(f);
						f = c;
					}

				},
				abort: function () {
					// abort xhr response
					this.xhr.abort();
				}
			});


	})();

	createNS("igk.soap", {
		query: function (data) {
			var ns = "http://schemas.xmlsoap.org/soap/envelope/";
			var r = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">';

			var g = igk.createNode("soap:Body", ns);

			// return;
			var m = data.method;
			if (m) {
				var fm = g.add(igk.createNode(m, ns));
				if (data.params) {
					for (var i in data.params) {
						fm.add(i).setHtml(data.params[i]);
					}
				}

				// fm.add("name").setHtml("dof");
			}
			// <soap:Body><getCountry><name>abc</name></getCountry></soap:Body></soap:Envelope>
			r += g.o.outerHTML;
			r += "</soap:Envelope>";

			igk.ajx.post(data.uri, r, function (xhr) {
				if (this.isReady()) {


					var q = igk.createNode("div");
					q.setHtml(xhr.responseText);
					if (xhr.status == 200) {
						if (m && data.ready) {
							var g = q.getElementsByTagName(m + "_result")[0];

							data.ready({ data: g.innerHTML });
						}
					} else {
						var g = q.getElementsByTagName("faultstring")[0];
						if (data.error && g) data.error({ data: g.innerHTML });
					}
				}
			});

		}
	}, { desc: "used to handle soap request" });


	(function () {

		var m_nodeReady = [];
		var m_initBeforeReady = [];
		// var _rootDepth=0;// count init node
		// 
		// uitilies ajx function
		// 
		var _postdata = [];


		function _bready(c) {
			// before ready call node
			var l = m_initBeforeReady.length;
			if (l <= 0)
				return;
			var m = $igk(c);
			for (var i = 0; i < l; i++) {
				m_initBeforeReady[i].apply(m);
			}
		};
		function _initnode(c) {

			_bready(c);
			// _rootDepth++;		
			// evaluate all script in this 
			// igk.ready(null,'initnode');
			// evaluate script contained in this node
			igk.ajx.eval(c);
			// evaluate binding Attrib Data
			igk.ctrl.callBindAttribData(c);
			// call ready on node
			igk.ajx.fn.nodeReady(c);
		 
			var p = igk.publisher;
			p.publish("sys://node/init", {
				target: c, rootDepth: 0
			}); 
			p.publish("sys://doc/changed", { target: c, context: 'initnode' });
		};


		createNS("igk.ajx.fn", {
			none: function () { },
			complete_ready: function (select) {// create a ready for a select node	
				return function (xhr, q) {
					var s = q.select(select).first();
					if (s)
						s.setHtml(xhr.responseText).init();
				};
			},
			postData: function (uri, n, t, m) {


				if (uri in _postdata) {
					_postdata[uri].abort();
				}
				var fc = null;
				if (typeof (t) == 'string') {
					var g = $igk(t).first();
					if (g)
						fc = igk.ajx.fn.replace_content(g.o);
				} else if (typeof (t) == 'function') {
					fc = t;
				} else {
					fc = igk.ajx.fn.replace_or_append_to_body;
				}
				var v = n.value;
				if (typeof (v) == 'object')
					v = igk.JSON.convertToString(v);

				// method type
				var _send = igk.ajx.post;
				var _data = n.id + "=" + v;
				if (!m) {
					_send = igk.ajx.get;
					uri += ((uri.indexOf("?") == -1) ? "?" : "&") + _data;
				}
				g = _send(uri, _data, function (xhr) {
					if (this.isReady()) {
						if (fc)
							fc.apply(this, [xhr]);
						delete (_postdata[uri]);
					}
				});
				_postdata[uri] = g;
			},
			appendTo: function (t) {
				return function (xhr) {
					if (this.isReady()) {
						igk.ajx.fn.append_to(xhr, t);
					}
				}
			},
			scriptReplaceContent: function (m, u, t) {
				var fc = igk.ajx.get;
				if (m == "post")
					fc = igk.ajx.post;
				function rp(xhr) {
					if (this.isReady()) {
						if (igk.winui.lineWaiter)
							igk.winui.lineWaiter.remove(t);
						igk.ajx.fn.replace_content(t).apply(this, [xhr]);
					} else if ((xhr.status != 200) && (xhr.readyState == 4)) {
						igk.winui.lineWaiter.remove(t);
						if (igk.ENVIRONMENT.debug)
							$igk(t).setHtml(igk.R.gets("AJX - Error"));

					}
				}
				if (igk.winui.lineWaiter) {
					igk.winui.lineWaiter.prependTo(t);
				}
				fc.apply(window, [u, null, rp]);
			},
			getfortarget: function (u, t) {
				// u:uri
				// t:target

				if (!t)
					return;
				igk.ajx.get(u, null, function (xhr) { if (this.isReady()) { this.setResponseTo(t); igk.ajx.fn.initnode(t); } });
			},
			replace_body: function (xhr) {
				if (this.isReady()) {
					this.replaceBody();
				}
			},
			replace_node: function (host) {// get the replace node function
				return function (xhr) {
					if (this.isReady()) {
						this.replaceResponseNode(host, false);
					}
				}
			},
			replace_content: function (host) {
				return function (xhr) {
					if (this.isReady()) {
						// get body content
						var g = igk.utils.getBodyContent(xhr.responseText, 1);
						if (g != null) {
							igk.dom.body().setHtml('');
							igk.ajx.fn.replace_or_append_to(igk.dom.body().o).apply(this, [xhr]);
						}
						else {



							this.setResponseTo(host, true);
						}
					}
				};
			},
			replace_or_append_to: function (t) {
				return function (xhr) {
					if (this.isReady()) {
						// igk.dom.body().setHtml("");
						var q = igk.createNode("dummy");
						// $igk(igk.dom.loadDocument(xhr.responseText));// igk.createNode("dummy");
						q.setHtml(xhr.responseText);
						var i = q.o.childNodes.length;
						if (i > 0) {
							var vo = new igk.selector();
							while (i > 0) {
								var c = q.o.childNodes[0];
								var p = 0;

								if (igk.ajx.fn.ctrl_replacement(c) == false) {
									if (c.id) {
										var ki = document.getElementById(c.id);
										if (ki) {
											p = 1;
											ki.parentNode.replaceChild(c, ki);
										}
									}
								} else {
									p = 1;
								}
								if (p == 0)
									t.appendChild(c);
								igk.ajx.fn.initnode(c);
								i--;
								vo.push(c);
							}
							// return vo;
						}
						igk.publisher.publish(igk.evts.dom[2], { target: t, src: xhr.responseText });
					}
				};
			},
			replace_or_append_to_body: function (xhr) {
				// replace_or_append_to_body
				//console.debug("start data");
				if (this.isReady()) {
					// get body only content
					//console.debug("start data is ready : "+xhr.responseText);
					// igk.log.write("data : "+xhr.responseStatus);
					// igk.log.write("state : "+xhr.readyState);
					var g = igk.utils.getBodyContent(xhr.responseText, 1);
					if (g != null)
						igk.dom.body().setHtml('');
					igk.ajx.fn.replace_or_append_to(igk.dom.body().o).apply(this, [xhr]);
				}
			},
			append_to_body: function (xhr) {
				if (this.isReady()) {
					igk.ajx.fn.append_to(xhr, igk.dom.body().o);
				}
			},
			prepend_to_body: function (xhr) {
				if (this.isReady()) {
					igk.ajx.fn.prepend_to(xhr, igk.dom.body().o);
				}
			},
			ctrl_replacement: function (c) {
				if (c && c.tagName && (c.tagName.toLowerCase() == 'igk:replace-ctrl')) {

					var q = $igk(c);
					var st = q.getAttribute("target");
					var h = q.getAttribute("hash");

					// alert("dkdk : "+st);
					if (st) {
						var t = $igk(st).first();
						if (t) {

							var s = q.select(">>");
							if (s.getCount() == 1) {
								s.each(function () {
									t.replaceBy(this);
									igk.ajx.fn.initnode(this.o);
									return !1;
								})
							}
							else {
								var m = "";
								s.each(function () {
									m += this.o.outerHTML;
									return !0;
								});
								t.setHtml(m);
								// t.unregister();
								igk.ajx.fn.initnode(t.o);

							}
							q.remove();
							if (h) {
								document.location.hash = h;
							}
							return !0;
						}
					}
					// no target

					q.select(">>").each(function () {
						// get first child node			
						var g = $igk("#" + this.o.id).first();
						if (g && (g.getAttribute('igk-type') == "controller")) {
							g.replaceBy(this);
							// this.o.id="default";
							igk.ajx.fn.initnode(this.o);

						}
						return !0;
					});
					$igk(c).remove();
					return !0;
				}
				return !1;
			},
			append_to: function (xhr, target) {
				if (xhr.responseText.length > 0) {
					var q = igk.createNode("div");
					var txt = igk.utils.treatBodyContent(xhr.responseText);
					q.setHtml(txt);
					var i = q.o.childNodes.length;
					if (i > 0) {
						var vo = new igk.selector();
						var ct = 0;
						while (i > 0) {
							var c = q.o.childNodes[ct];
							if (igk.ajx.fn.ctrl_replacement(c) == false) {


								if (c.tagName && (c.tagName.toLowerCase() == 'igk-body') && ($igk(target).o == igk.dom.body().o)) {

									igk.dom.body().replaceWith(c);
									igk.ajx.fn.initbody();
									ct++;
								} else {
									target.appendChild(c);
									igk.ajx.fn.initnode(c);
								}
							}
							i--;
							vo.push(c);
						}
						return vo;
					}
				}
				return null;
			},
			prepend_to: function (xhr, t) {
				if (xhr.responseText.length > 0) {
					var q = igk.createNode("div");
					q.setHtml(xhr.responseText);
					var i = q.o.childNodes.length;
					while (i > 0) {
						var c = q.o.childNodes[0];
						if (igk.ajx.fn.ctrl_replacement(c) == false) {
							$igk(t).prependChild(c);
							igk.ajx.fn.initnode(c);
						}
						i--;
					}
				}
			},
			nodeReady: function (c) {
				if (c.tagName && /(no)?script/.test(c.tagName.toLowerCase()))
					return;
				if (m_nodeReady && (m_nodeReady.length > 0)) {
					for (var i = 0; i < m_nodeReady.length; i++) {
						m_nodeReady[i].apply(c);
					}
				}
			},
			registerNodeReady: function (f) {
				// 
				// register callback function that will call at the end on igk.ajx.fn.initnode chain.
				// remark: if you want to call on every node used igk.ctrl.registerReady
				// 
				if (typeof (f) == "function")
					m_nodeReady.push(f);
			},
			unregisterNodeReady: function (f) {
				var s = [];
				for (var i = 0; i < m_nodeReady.length; i++) {
					if (m_nodeReady[i] == func) {
						continue;
					}
					s.push(readyFunc[i]);
				}
				m_nodeReady = s;

			},
			initBeforeReady: function (f) {
				// register global function function that will be called when ever a node require to be ready
				if (typeof (f) == "function")
					m_initBeforeReady.push(f);
			},
			uninitBeforeReady: function (f) {
				var c = [];
				for (var i = 0; i < m_initBeforeReady.length; i++) {
					if (m_initBeforeReady[i] == f) continue;
					c.push(m_initBeforeReady[i]);
				}
				m_initBeforeReady = c;
			},
			initbody: function () {// init body and publish event system/bodychange
				igk.ajx.fn.initnode(igk.dom.body().o);
				igk.publisher.publish("system/bodychange", { target: igk.dom.body().o });
			},
			bindto: function (n, h) {
				// >n:target node 
				// >h:history link
				return function (xhr) {
					if (this.isReady()) {
						this.setResponseTo(n);
						igk.ajx.fn.initnode(n.o);
						if (h)
							window.igk.winui.saveHistory(h);
					}
				}
			},
			initnode: function (c) {	// initnode utility fonction. in ajx context. accept only dom tag node 
				if (!c)
					return;
				if (typeof (c.nodeType) == "undefined") {
					throw new Error("BAD dom node initialization ... element is not a node : " + c);
				}



				function bindNode(n) {
					this.c = n;
					var q = this;
					igk.appendProperties(this, {
						init: function () {
							// -- -
							// because of the readyState will initialize all "component" on 'complete' no need to call init node
							// -- -

							// igk.ajx.fn.initnode(q.c);					

							// igk.ajx.eval(q.c);	
							// evaluate binding Attrib Data
							// igk.ctrl.callBindAttribData(q.c);	
							_initnode(q.c);
						}
					});
				};

				if (document.readyState != 'complete') {
					igk.ready(new bindNode(c).init);
					return;
				}
				_initnode(c);


			}

		});


	})();

	igk.animation.constants = {
		gL: "gotoleft",
		gR: "gotoright",
		gT: "gototop",
		gB: "gotobottom",
		DIR_VERTICAL: "vertical",
		DIR_HORIZONTAL: "horizontal"
	};


	function igk_setcss_bound(i, rect) {
		$igk(i).setCss({ "left": rect.x + "px", "top": rect.y + "px", "width": rect.width + "px", "height": rect.height + "px" });
	}
	function igk_debug_show_heararchi(node) {

		var q = frm;
		while (q) {

			igk.console_debug(q.tagName + ":" + q.id);
			q = q.parentNode;
		}
	}
	function igk_debug(msg) {
		if (igk.DEBUG) {
			igk.console_debug(msg);
		}
	}
	function igk_debug_t(tag, msg) {
		if (igk.DEBUG) {
			igk.console_debug("[" + tag + "]-" + msg);
		}
	}

	createNS("igk.math", {
		rectangle: function (x, y, w, h) {
			this.x = x ? x : 0;
			this.y = y ? y : 0;
			this.width = w ? w : 0;
			this.height = h ? w : 0;
			this.toString = function () { return "igk.math.rectangle[" + this.x + "," + this.y + "," + this.width + "," + this.height + "]"; };
			this.isEmpty = function () { return (this.width == 0) || (this.height == 0); };
			this.inflate = function (x, y) { this.x -= x; this.y -= y; this.width += 2 * x; this.height += 2 * y; };

		}

	});

	createNS("igk.utils",
		{
			getv: igk_getv,
			get_form_posturi: igk_get_form_posturi,
			submit_fromenter: function (k, evt) {
				if (evt.keyCode && (evt.keyCode == 13)) {
					k.form.submit();
					return !1;
				}
				return !0;
			},
			treatBodyContent: function (txt) {
				if (!txt)
					throw ("/!\\ txt not define");
				return txt.replace(/(<\/?)body( .+?)?>/gi, '$1IGK-BODY$2>', txt);
			},
			getBodyContent: function (text, bonly) {
				var s = "";
				var t = igk.createNode("div");
				if (text) {
					s = igk.utils.treatBodyContent(text);
					t.setHtml(s);
				}
				var tt = t.getElementsByTagName("IGK-BODY");
				if (tt.length == 1) {
					// body tag found return inner content
					var out = tt[0].innerHTML;
					return out;
				}
				if (bonly)
					return null;
				return s;
			}
		});

	igk.utils.Base64 = {

		// private property
		_keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

		// public method for encoding
		encode: function (input) {
			var output = "";
			var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
			var i = 0;

			input = igk.utils.Base64._utf8_encode(input);

			while (i < input.length) {

				chr1 = input.charCodeAt(i++);
				chr2 = input.charCodeAt(i++);
				chr3 = input.charCodeAt(i++);

				enc1 = chr1 >> 2;
				enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
				enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
				enc4 = chr3 & 63;

				if (isNaN(chr2)) {
					enc3 = enc4 = 64;
				} else if (isNaN(chr3)) {
					enc4 = 64;
				}

				output = output +
					this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
					this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

			}

			return output;
		},

		// public method for decoding
		decode: function (input) {
			var output = "";
			var chr1, chr2, chr3;
			var enc1, enc2, enc3, enc4;
			var i = 0;

			input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

			while (i < input.length) {

				enc1 = this._keyStr.indexOf(input.charAt(i++));
				enc2 = this._keyStr.indexOf(input.charAt(i++));
				enc3 = this._keyStr.indexOf(input.charAt(i++));
				enc4 = this._keyStr.indexOf(input.charAt(i++));

				chr1 = (enc1 << 2) | (enc2 >> 4);
				chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
				chr3 = ((enc3 & 3) << 6) | enc4;

				output = output + String.fromCharCode(chr1);

				if (enc3 != 64) {
					output = output + String.fromCharCode(chr2);
				}
				if (enc4 != 64) {
					output = output + String.fromCharCode(chr3);
				}

			}

			output = igk.utils.Base64._utf8_decode(output);

			return output;

		},

		// private method for UTF-8 encoding
		_utf8_encode: function (string) {
			if (!string)
				return null;
			string = string.replace(/\r\n/g, "\n");
			var utftext = "";

			for (var n = 0; n < string.length; n++) {

				var c = string.charCodeAt(n);

				if (c < 128) {
					utftext += String.fromCharCode(c);
				}
				else if ((c > 127) && (c < 2048)) {
					utftext += String.fromCharCode((c >> 6) | 192);
					utftext += String.fromCharCode((c & 63) | 128);
				}
				else {
					utftext += String.fromCharCode((c >> 12) | 224);
					utftext += String.fromCharCode(((c >> 6) & 63) | 128);
					utftext += String.fromCharCode((c & 63) | 128);
				}

			}

			return utftext;
		},

		// private method for UTF-8 decoding
		_utf8_decode: function (utftext) {
			var string = "";
			var i = 0;
			var c = 0;
			var c1 = 0;
			var c2 = 0;

			while (i < utftext.length) {

				c = utftext.charCodeAt(i);

				if (c < 128) {
					string += String.fromCharCode(c);
					i++;
				}
				else if ((c > 191) && (c < 224)) {
					c2 = utftext.charCodeAt(i + 1);
					string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
					i += 2;
				}
				else {
					c2 = utftext.charCodeAt(i + 1);
					c3 = utftext.charCodeAt(i + 2);
					string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
					i += 3;
				}

			}

			return string;
		}

	};

	function igk_check_change(uri, every) {
		var obj = {
			uri: uri,
			interval: every,
			doaction: function () {
				var self = this;
				igk.ajx.post(uri, null, function (xhr) {
					if (this.isReady()) {
						if (xhr.responseText.length > 0) {
							var q = igk.createNode("data");
							// q.innerHTML=xhr.responseText;
							igk_remove_all_script(q);
							var tdata = q.getElementsByTagName("data");


							if (tdata.length != 1) {
								var t = document.getElementById("changeresponse");
								if (t == null) {
									t = igk.createNode("div");
									t.id = "changeresponse";
									igk.dom.body().appendChild(t);
								}
							}


							setTimeout(function () { self.doaction(); }, self.interval);
						}
					}
				}
				);
			}
		};
		obj.doaction();
	}



	function igk_confirm_article_del(item) {
		if (confirm("voulez vous vraiment supprimer l'article ?\nNote: la suppression es irréversible")) {

			item.href = item.href + '&confirm=1';

			return !0;
		}
		return !1;
	}
	// check all element in target
	function igk_check_all(parent, node, value, toggle, target) {
		if (node == null) {
			console.debug("igk_check_all: node is null. perhaps IGKcontext failed");
			return;
		}
		var t = null;
		if (!target)
			t = $igk(node).select("input");
		else
			t = $igk(node).select(target);

		function __update() {
			if ((this.o != parent) &&
				(this.o.type == "checkbox")) {
				if (toggle) {
					if (this.o.checked == value) {
						this.o.checked = !value;
					}
					else {
						this.o.checked = value;
					}
				}
				else {
					this.o.checked = value;
				}
			}
			return !0;
		};
		if (t) {
			if (t.isSr())
				t.each(__update);
			else {
				__update.apply(t);
			}
		}
	}
	// igk.utils.changeStyle=


	// preloaded functions
	var m_preloadFunctions = [];
	function __applyPreloadDocument(document) {
		for (var i = 0; i < m_preloadFunctions.length; i++) {
			m_preloadFunctions[i].apply(document);
		}
	};
	function __registerHtmlPreloadDocumentCallBack(name, callback) {// single callback
		if (callback && name) {
			if (!m_preloadFunctions[name]) {
				// m_preloadFunctions.push(callback);
				// m_preloadFunctions[name]=callback;
				var c = {
					funcs: [],
					funcCount: function () {
						return this.funcs.length;
					},
					apply: function (target) {
						for (var i = 0; i < this.funcs.length; i++) {
							this.funcs[i].apply(target);
						}
					},
					add: function (callback) {
						if (callback) {
							this.funcs.push(callback);
						}
					},
					remove: function (callback) {
						for (var i = 0; i < this.funcs.length; i++) {
							if (callback == this.funcs[i]) {
								this.funcs.pop(this.funcs[i]);
							}
						}
					},
					toString: function () {
						return "functionRegister";
					}

				};
				m_preloadFunctions[name] = c;
				m_preloadFunctions.push(c);
			}
			var s = m_preloadFunctions[name];
			s.add(callback);
		}
	};

	function __unregisterHtmlPreloadDocumentCallBack(name, callback) {
		if (m_preloadFunctions[name]) {
			m_preloadFunctions.remove(callback);
		}
	}
	// configuration menu management
	createNS("igk.configmenu", {
		init: function (p) {
			var t = p.getElementsByTagName("a");
			var a = null;
			for (var i = 0; i < t.length; i++) {
				a = t[i];
				a.onclick = function () { return igk.configmenu.navigate(this, this.getAttribute('page')); };

			}

		},
		navigate: function (i, p) {
			var frm = igk.getParentById(i, 'form');
			if (frm == null) return;
			frm.action = i.href; frm.menu.value = p; frm.submit();
			return !1;
		}
	});

	// tiny mce plugins 
	(function () {

		var m_loadtiny = 0;
		var b_reg = 0;
		var m_globals = [];
		var __u = igk.constants.undef;

		// var mut = new MutationObserver(function(e){

		// });
		// igk.ready(function(){
		// mut.observe(igk.dom.body().o, {childList:true});
		// });

		createNS("igk.tinyMCE",
			{
				runOn: function (el, op) { 
					igk.ready(function () {
						if (typeof (tinyMCE) != __u) {
							// because of identification. 
							// remove all editor that's not present on document
							for (var i in tinyMCE.editors) {
								if (i == 'length') continue;

								var m = tinyMCE.editors[i];
								if (!$igk(m.getElement()).isOnDocument()) {
									tinyMCE.remove(m);
								}
							}
							setTimeout(function () {
								var g = { selector: el.elements };
								var m = null;
								for (var i in op) {
									if (i == "protect") {
										m = Array();
										for (var j = 0; j < op[i].length; j++) {
											m.push(new RegExp(op[i][j], "g"));
										}
										g[i] = m;
										continue;
									}

									g[i] = op[i];
								} 
								tinyMCE.init(g); 
							}, 100);
							return;
						}
					});


				
				},
				init: function (elements, op) {
					// var prop={
					// // General options
					// mode : mode,
					// theme : "advanced",
					// // instanciate plugin list
					// plugins : "autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
					// // Theme options
					// theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
					// theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
					// theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
					// theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
					// theme_advanced_toolbar_location : "top",
					// theme_advanced_toolbar_align : "left",
					// theme_advanced_statusbar_location : "bottom",
					// theme_advanced_resizing : true,
					// entity_encoding : "raw",

					// // 'forced_root_block' : 'div',
					// // Skin options
					// skin : "o2k7",
					// skin_variant : "silver",

					// // Example content CSS(should be your site CSS)
					// content_css : "css/example.css",

					// // Drop lists for link/image/media/template dialogs
					// // template_external_list_url : "js/template_list.js",
					// // external_link_list_url : "js/link_list.js",
					// // external_image_list_url : "js/image_list.js",
					// // media_external_list_url : "js/media_list.js",

					// // Replace values for the template plugin
					// template_replace_values : {
					// username : "Some User",
					// staffid : "991234"
					// },
					// style_formats:[
					// {title:"myFormat",inline:"div",classes:"thediv"}
					// ]


					function __loadAndInit() {
						var tq = {};
						if (op) {
							for (var i in op) {
								tq[i] = op[i];
							};
						}
						var t = elements.elements;
						if (typeof (t) == "string") {
							tq.selector = t;
							tinyMCE.init(tq);
							return;
						}
						else {
							for (var i in t) {
								var q = $igk(elements[i]).first();
								tq.selector = elements;
								tinyMCE.init(tq);
							}
						}
					}

					if (typeof (tinyMCE) != igk.constants.undef) {
						__loadAndInit();
					}
					else {
						igk.ready(function () {
							__loadAndInit();
						});
					}


					function __bindGlobal(t) {
						// call on every ajx context finish loaded		
						for (var i = 0; i < m_globals.length; i++) {
							m_globals[i].apply();
						}
						m_globals = [];
					}
					if (!b_reg) {
						b_reg = 1;
					} else {
						m_globals.push(function () { __loadAndInit() });
					}

				},
				edit: function (selector, op) {
					function __loadAndInit() {
						var q = { selector: selector, inline: true };
						for (var i in op) {
							q[i] = op[i];
						}
						tinyMCE.init(q);
					}
					if (typeof (tinyMCE) != igk.constants.undef) {
						__loadAndInit();
					} else {
						igk.ready(__loadAndInit);
					}
				}
				, save: function (n, uri, callback) {
					// selector
					var g = tinyMCE.get(n);
					if (g) {
						// g.setProgressState(1);
						igk.ajx.post(uri, 'tiny=1&clContent=' + g.getContent(), function (xhr) {
							if (this.isReady()) {
								// g.setProgressState(0);
								if (callback)
									callback.apply(this);

								igk.ajx.fn.replace_or_append_to_body.apply(this, [xhr]);

							}
						});

					}
				}
			});
	})();


	// ---------------------------------------------------------
	// script controller entity
	// ---------------------------------------------------------
	(function () {

		var m_controllers = [];// list of controller
		var m_initialize = false; // initialized or noted
		// attributes data
		var m_attrib_datas = {
			"igk-type": { n: "igk", desc: "Declare type. attribute is used as Type" },
			"igk-js-anim-over": { n: "js", desc: "execute anim on mouseover and mouse release", code: "igk-js-anim-over=\"{backgroundColor: 'red'},{ anim property....}\"" },
			"igk-js-anim-focus": { n: "js", desc: "execute anim on fucus ans blur" },
			"igk-js-eval": { n: "js", desc: "used to eval script in current context" },
			"igk-js-eval-init": { n: "js", desc: "used to eval script in current context after document is ready" },
			"igk-js-init-uri": { n: "js", desc: "uri that will be invoke on document ready" },
			"igk-js-bodyheight": { n: "js", desc: "indicate that this node must target the body height" },
			"igk-node-disable-selection": { n: "node", type: "attribute", desc: "node can be selelected. default is true" },
			"igk-article-options": { n: "ctrl", desc: "indicate must be considered as an article options" },
			"igk-ctrl-options": { n: "ctrl", desc: "indicate must be considered as a controller options node" },
			"igk-input-focus": { n: "js", desc: "force the cibling node to get focus" },
			"igk-input-data": { n: "js", desc: "setup the data used to validate the input" },
			"igk-form-validate": { n: "js", desc: "used with form to indicate that the form must be validate before submit." },
			"igk-js-fix-height": { n: "js", desc: "fix elemenet height to target height. value is a id of the element" },
			"igk-js-fix-width": { n: "js", desc: "fix elemenet width to target width. value is a id of the element" },
			"igk-js-fix-size": { n: "js", desc: "fix elemenet size to target size. value is a id of the element" },
			"igk-js-fix-prop": { n: "js", desc: "fix element properties on size.expect property wait: {target,prop} " },
			"igk-js-fix-eval": { n: "js", desc: "fix element properties on window resize property wait: {update:function()} " },
			"igk-ajx-form": { n: 'js', desc: 'bool indicate that a form is used in ajx context. mixed(0|1|[{complete:func,targetid:[id of response node]}]. used igk-ajx-form-targedid to indicated the reponse node' },
			// select utility data
			"igk-js-bind-select-to": { n: "js", desc: "bind select to data from target ref #id,passing object refid or json: {id:#sample,selected:value,usecustom:0|1,allowempty:1|0,emptyvalue:value}" },
			"igk-ajx-form-target": { n: 'js', type: 'attr', desc: 'used in form to set the target of the form response,(select|=|::) where=is for the current form,:: if for the entire document ' }
		};
		var m_ctrl_types = {
			"igk-ctrl-options": { n: "igk", desc: "declare controller options" },
			"igk-ctrl": { n: "igk", desc: "declare a controller" }
		};
		var m_attribManager = []; // attrib manager
		var m_h_ctrl = {}; // hosted controller functions
		var m_readylist = null;
		var m_callflag = 0;
		function __attribToString() {
			return "igk.ctrl.attribManagerInfo";
		};
		function __registerAttribManager(key, callback) {
			if (callback && key) {
				var e = null;
				if (m_attribManager[key])
					e = m_attribManager[key];
				else {
					e = { k: key, s: new Array(), toString: __attribToString };
					m_attribManager[key] = e;
					m_attribManager.push(e);
				}
				e.s.push(callback);
			}
		};

		function __callReadyFunc(n, de) {
			if (!n || (m_readylist == null) || m_callflag) {
				return;
			}
			var e = null;

			var i = m_readylist.getCount();
			var j = 0;
			m_callflag = 1;
			for (j = 0; j < m_readylist.getCount(); j++) {

				e = m_readylist.getItemAt(j);
				e.apply(n, [de]);
			}
			m_callflag = 0;
			if (i != j)
				console.debug("after call to ready list " + m_readylist.getCount());
		}
		function __readyFuncEventArgs() {
			var m_preventContinue = false;

			igk.appendProperties(this, {
				preventContinue: function () {
					m_preventContinue = !0;
				},
				getPreventContinue: function () {
					return m_preventContinue;
				}
			});
		};
		var m_attribCtrl = [];
		function __loadAttribCtrl(n, k) {
			// >node 
			// >key
			if (!(k in m_attribCtrl)) {
				m_attribCtrl[k] = [];
			}
			m_attribCtrl[k].push(n);
		}
		function __callBindAttribData(node) {
			var q = $igk(node);
			var cnf = q.getConfig("igk:callAttribBindingData");
			if (cnf == 1) {
				return;
			}

			var re = new __readyFuncEventArgs();
			// call ready function on node
			__callReadyFunc(node, re);

			if (re.getPreventContinue()) {
				return;
			}
			var s = q.getAllAttribs();
			var r = null;
			var e = null;
			if (typeof (s) == igk.constants.undef)
				return;
			function __invoke(key, tab, node) {
				var e = null;
				var n = $igk(node);
				var attr = null;
				for (var i = 0; i < tab.length; i++) {

					e = tab[i];

					try {

						attr = n.getAttribute(key);
						e.apply(n, [key, attr]);
						__loadAttribCtrl(n, key);

					}
					catch (ex) {
						console.error(ex);
						igk.winui.notify.showErrorInfo("Exception",
							"__invoke::Binding ::: " + key + " <br />" + ex
							+ "<p class=\"stack\" style=\"max-height: 240px; font-family:'courier new'; line-height:1.5; font-size:8pt; overflow: auto\">" + ex.stack.split("\n").join("<br />") + "</p>"
							//+ "<div class=\"source\" >From : Source Code</div><pre style=\"max-height:200px; overflow-y:auto;\">" + e + "</pre>
						+"");
					}
				}
			};

			var fc = node.hasAttribute || function (n) {
				return node.getAttribute(n) != null;
			};

			for (var i = 0; i < m_attribManager.length; i++) {
				e = m_attribManager[i];
				r = new RegExp("(" + e.k + ")");
				if (r.test(s) && fc.apply(node, [e.k])) {
					__invoke(e.k, e.s, node);
				}
			}
			// if (igk.BINDING)

			// mark it as binding data
			q.setConfig("igk:callAttribBindingData", 1);
		}

		function __clearBindAttribData() {
			m_attribManager = [];
		}
		function __ctrl_utility_functions(n) {// controler utility function access with "igk/nodeobj".fc
			var m_o = n;
			igk_defineProperty(this, "o", {
				get: function () {
					return m_o;
				}
			});
			igk.appendProperties(this, {
				toString: function () {
					return "[object igk.ctrl.utility.function]";
				}
			});
		}
		createNS("igk.ctrl",
			{
				getAttribCtrl: function (k) {
					return m_attribCtrl[k] || null;
				},
				getAttribCtrlList: function (k) {
					var t = [];
					for (var i in m_attribCtrl) {
						if (i == "length")
							continue;
						t.push(i);
					}
					return t;// m_attribCtrl[k] || null;
				},
				bindAttribManager: function (key, callback, setting) {
					if (setting && !(key in m_attrib_datas)) {
						igk.ctrl.registerAttribManager(key, setting);
					}
					if (m_attrib_datas[key] && callback) {
						__registerAttribManager(key, callback);
					}
					else {
						igk.winui.notify.showMsg("<div class=\"igk-notify igk-notify-danger\">" +
							"Error No Binding Attrib registrated for \"" + key +
							"\".<br/><div>You must register it before call the bindAttribManager function. igk.ctrl.registerAttribManager");
					}
				},
				// bind function to call when document page loaded before ready
				bindPreloadDocument: function (name, callback) {
					__registerHtmlPreloadDocumentCallBack(name, callback);
				},
				getcontroller: function (item) { // get the parent controller
					if (item == null)
						return null;
					if (item.getAttribute && (item.getAttribute("igk-type") == "controller"))
						return item;
					else
						return igk.ctrl.getcontroller(item.parentNode);
				},
				confirm: function (item, msg, uri) {// used to confirm frame
					if ((window.confirm) && window.confirm(msg)) {
						item.form.confirm.value = 1;
						item.form.action = uri;
						return !0;
					}
					return !1;
				},
				init_controller: function () {
					// init controller on first page loading	
					if (m_initialize)
						return;
					// init all visible ctrl
					var d = document.getElementsByTagName("*");
					var id = null;
					var s = null;
					m_controllers.length = 0;// clear m_controller
					if (d) {// element found		

						for (var i = 0; i < d.length; i++) {

							s = d[i];
							if (s.getAttribute("igk-type") == "controller") {
								id = s.getAttribute("igk-type-id");
								m_controllers.push(s);
								if (id) {
									// register controllers
									m_controllers[id] = s;
								}
								$igk(s).fc = new __ctrl_utility_functions($igk(s));
							}
						}
						igk.ready(function () {
							// bind attribute data on document ready
							igk.ctrl.callBindAttribData(igk.dom.body().o);
						});
					}
					m_initialize = !0;
				},
				// controller function
				getCtrlById: function (id) {
					if (m_controllers[id])
						return m_controllers[id];
					return null;
				},

				findCtrlById: function (id) {
					if (m_initialize) {
						return igk.ctrl.getCtrlById(id);
					}
					var d = document.getElementsByTagName("*");
					var id = null;
					var s = null;
					m_controllers.length = 0;
					if (d) {// element found		

						for (var i = 0; i < d.length; i++) {
							s = d[i];
							if (s.getAttribute("igk-type") == "controller") {
								id = s.getAttribute("igk-type-id");

								if (id == id) {
									return s;
								}

							}
						}
					}
					return null;
				},
				// get all controller
				getCtrls: function () {
					return m_controllers;
				},
				isregCtrl: function (q) {
					if ((q.nodeType == 1) && (q.getAttribute("igk-type") == "controller")) {
						var id = q.getAttribute("igk-type-id");
						if (id && !m_controllers[id]) {
							m_controllers[id] = $igk(q);
							$igk(q).fc = new __ctrl_utility_functions($igk(q));
						}
						return q;
					}
					return null;
				},
				getParentController: function (node) {
					// go to parent until parent controller is found
					if ((node == null) || (node.parentNode == null))
						return null;
					var q = node.parentNode;
					while (q != null) {
						if (igk.ctrl.isregCtrl(q)) {
							return q;
						}
						q = q.parentNode;
					}
					return q;
				},
				callBindAttribData: function (node) {
					// call binding to system on node	
					if (node) {
						__callBindAttribData(node);
						if (node.getElementsByTagName) {
							var s = node.getElementsByTagName("*");
							if (s) {
								for (var i = 0; i < s.length; i++) {
									igk.invokeAsync(__callBindAttribData, s[i]);
									// igk.invokeAsync(__callBindAttribData(s[i]);
								}
							}
						} else {
							switch (node.nodeType) {
								case 3:
								case 8:// comment
									break;
								default:
									console.debug("/!\\ getElementsByTagName function not found : " + node.nodeType);
									break;
							}
						}
					}
				},
				getAttribData: function () {// get binding schema help
					return m_attrib_datas;
				},
				registerReady: function (func) {
					// register a callback function that will be called everytime new node is added to document in igk.ajx.fn.initnode chain.
					// initnode use it in 'igk.ready' callback to maintain chain. function will be call for every node.
					// note: if you whant your new created callback to be called on new document complete used igk.ajx.fn.registerNodeReady against
					if (m_readylist == null)
						m_readylist = new igk.system.collections.list();
					m_readylist.add(func);
				},
				registerAttribManager: function (name, options) {
					if ((name) && (typeof (m_attrib_datas[name]) == igk.constants.undef)) {
						m_attrib_datas[name] = options;
						return !0;
					}
					return !1;
				},
				isAttribManagerRegistrated: function (name) {
					if ((name) && (typeof (m_attrib_datas[name]) != igk.constants.undef)) {
						return !0;
					}
					return !1;
				},
				regCtrl: function (name, baseuri) {// register controller		
					m_h_ctrl[name] = new (function (name, baseuri) {
						this.name = name;
						this.baseuri = baseuri;
						igk.appendProperties(this, {
							getUri: function (funcname) {
								if (funcname) {
									return this.baseuri + "&f=" + funcname;
								}
								return this.baseuri;
							}
						});
					})(name, baseuri);
				},
				getRegCtrl: function (name) {
					return igk.get_v(m_h_ctrl, name, null);
				},
				getRegCtrls: function () {
					return m_h_ctrl;
				}

			});

		// selection model
		createNS("igk.ctrl", {
			initselect_model: function (t, o, model) {// target select output zone		
				var q = null;
				var to = null;
				var p = igk.getParentScript();

				if (igk_is_string(t)) {
					q = $igk(p).select(t).first();
				}
				else {
					q = t;
				}
				if (igk_is_string(o)) {
					to = $igk(p).select(o).first();
				} else
					to = o;
				if (!q || !to) {
					return;
				}

				function __changez(evt) {
					var v = q.o.value;
					var i = q.o.selectedIndex;
					var n = $igk(q.o[i]).getAttribute("model") || "default";
					var cl = null;
					if (model)
						cl = model.select("." + n).first();
					else
						cl = $igk(p).select(".igk-select-model ." + n).first();
					if (cl) {
						to.setHtml(cl.getHtml());
					}
					else
						to.setHtml("no data");
					// manual raise resize event
					igk.winui.events.raise(window, 'resize');
				};
				// -------------------------------------------------------------------------
				// 
				// -------------------------------------------------------------------------
				$igk(q).reg_event("change", __changez)
					.reg_event("keyup", function (evt) {

						var kc = evt.keyCode || 0;
						switch (kc) {
							case 37:// LEFT
							case 38:// UP
							case 39:// RIGHT
							case 40:// BOTTOM
								__changez();
								break;
						}

					}); 
				__changez();
 
				// alert("bad===="+$igk(q).getParentBody());		
				if (q.o.form) {
					// restore the default view
					$igk(q.o.form).reg_event("reset", function () {
						var s = q.select(">:option");
						// get the first option
						var i = s.first();
						if (i) {
							q.o.value = i.o.value;

							__changez();
						}
					});
				}


			}
		});

	})();




	createNS("igk.ctrl.utils", {
		check_all: igk_check_all
	});

	(function () {
		function __append_frametobody(responseText) {
			var q = document.createElement("div");
			q.innerHTML = responseText;
			var c = q.childNodes[0];
			if (c) {// have node				
				igk.dom.body().appendChild(c);
				igk.ajx.fn.initnode(c);

				if (c.getElementsByTagName) {
					var tforms = c.getElementsByTagName("form")[0];
					var tc = $igk(c).select("form");
					// init default frame management		
					if (tc.each) {
						tc.each(function () {

							if (this.o.onsubmit == null) {
								this.setProperties({
									"onsubmit": function (evt) {
										// raised on submit									
										window.igk.ajx.postform(this, this.action.split("#")[0], function (xhr) {
											if (this.isReady()) {

												if (global) {
													this.replaceBody(xhr.resonseText);
												}
												else {
													if (m)
														this.setResponseTo(m);

												}
											}
										});
										this.frame.close();
										evt.preventDefault();
										return !1;
									}
								});
							}
							return this;
						});
					}
				}
			}
		};

		createNS("igk.ctrl.frames",
			{
				appendFrameResponseToBody: __append_frametobody,
				postframe: function (e, uri, ctrl) {
					var m = null;
					var t = $igk(e).select(ctrl).first();// getParentByTagName("form");
					if (ctrl != null) {
						m = window.igk.ctrl.getCtrlById(ctrl);
					}

					// alert("m is "+m + " " + ctrl+ " "+window.igk.ctrl.getCtrlById(ctrl));
					igk.ajx.get(uri, null, function (xhr) {
						// igk.frames.post function			
						if (this.isReady()) {
							if (t) {
								t.setHtml(xhr.responseText).init();
							}
							else {
								igk.ajx.fn.replace_or_append_to_body.apply(this, [xhr]);
								// __append_frametobody(xhr.responseText);					
							}
						}
					});
				}
			});

		// end igk.ctrl.frames
	})();

	// page editor entity
	createNS("igk.winui.gui", {
		rectangleSnippet: function (o) {
			this.o = o;
			this.bound = new igk.math.rectangle(0, 0, 8, 8);
			this.view = igk.createNode("div");
			$igk(this.view).setCss({ "position": "absolute", "border": "1px solid black", "backgroundColor": "transparent" });
			$igk(this.view).setProperties({ "class": "dark_op90" });
			igk_setcss_bound(this.view, this.bound);
			this.o.view.appendChild(this.view);
			this.setBound = function (rc) { this.bound = rc; igk_setcss_bound(this.view, this.bound); }
		}
	});

	createNS("igk.winui.rectangleSelector",
		{
			init: function (target) {
				if (!target)
					return null;
				$igk(target).setCss({ "position": "absolute", "left": "0px", "top": "0px", "width": "400px", "height": "400px", "border": "1px dotted gray", "backgroundColor": "transparent", "zIndex": "500" });
				var m_bound = new igk.math.rectangle();

				$igk(m_bound).setProperties({ x: 10, y: 10, width: 400, height: 250 });



				function __updatesize() {
					var m_rc = 0;
					var m_snippets = this.snippets;
					igk_setcss_bound(this.view, this.bound);
					m_rc = new igk.math.rectangle(0 + this.bound.width / 2, 0, 0, 0);
					m_rc.inflate(4, 4);
					m_snippets[0].setBound(m_rc);
					m_rc = new igk.math.rectangle(0 + this.bound.width, this.bound.height / 2, 0, 0);
					m_rc.inflate(4, 4);
					m_snippets[1].setBound(m_rc);
					m_rc = new igk.math.rectangle(0 + this.bound.width / 2, + this.bound.height, 0, 0);
					m_rc.inflate(4, 4);
					m_snippets[2].setBound(m_rc);
					m_rc = new igk.math.rectangle(0, + this.bound.height / 2, 0, 0);
					m_rc.inflate(4, 4);
					m_snippets[3].setBound(m_rc);
				};
				function __initSippets(t) {
					var m_snippets = new Array();
					m_snippets[0] = new igk.winui.rectangleSnippet(t);
					m_snippets[1] = new igk.winui.rectangleSnippet(t);
					m_snippets[2] = new igk.winui.rectangleSnippet(t);
					m_snippets[3] = new igk.winui.rectangleSnippet(t);
					return m_snippets;
				}

				// igk.dom.body().appendChild(this.view);

				return new function () {
					this.view = target;
					this.bound = m_bound;
					this.update = __updatesize;
					this.toString = function () { return "igk.winui.rectangleSelector"; };
					this.snippets = __initSippets(this);
					this.update();
				};
			}
		});


	// for igk.css compatibility utility
	createNS("igk", {
		css: new (function () {
			var props = {};
			var domProp = null;
			var vendors = ['webkit', 'ms', 'o'];
			function getchars(x) {
				var t = [];
				for (var i = 0; i < x.length; i++)
					t.push(x[i]);
				return t;
			}
			function getstring(ch) {
				var t = "";
				for (var i = 0; i < ch.length; i++)
					t += ch[i];
				return t;
			}
			function __setProperty(item, properties) {
				var _navsupport = igk.navigator.isIE();
				if (item && item.style && properties) {
					for (var i in properties) {
						try {
							if (i.startsWith("--")) {
								//ie 11 not supporting custom data on css								
								item.style.setProperty(i, properties[i]);
								if (!_navsupport)
									continue;
							}// else
							item.style[i] = properties[i];
						}
						catch (ex) {
							// boxSizing cause error					
						}
					}
				}
				else {
					console.debug('[BJS] -/!\v properties ' + item + ' not defined');
				}
			}
			// load dummy css style properties
			var xdum = igk.createNode("div", igk.namespaces.xhtml);
			var dum = xdum.o;
			var l = false;
			if (dum.style) {
				for (var i in dum.style) {
					if (typeof (dum.style[i]) != IGK_FUNC) {
						switch (i) {
							case 'cssText':
							case 'length':
							case 'parentRule':
								continue;
						}
						// 
						// firefox implement some property with - symbol ignore them
						// 
						if (i.indexOf('-') != -1)
							continue;
						props[(i + '').toLowerCase()] = i;

						l = !0;
					}
				}
			}
			// delete(dum);
			// load css from dummy style resolving the safary error

			if (!l && window.getComputedStyle) {
				var txt = window.getComputedStyle(dum).cssText;
				if (txt) {
					var tab = txt.split(';');
					for (var i = 0; i < tab.length; i++) {
						var s = tab[i].split(':')[0]; // first word
						var d = getchars(s);
						var index = 1;
						if (s[0] == '-') {
							index = 2;
						}
						else {
							// replace all next segment width uppercase layer
						}
						while (index > 0) {
							index = s.indexOf('-', index);
							if (index == -1)
								break;
							if (index + 1 < s.length) {
								d[index + 1] = (s[index + 1] + '').toUpperCase();
							}
							index++;
						}
						s = getstring(d).replace(/( |\-)/g, "");
						if (typeof (props[s.toLowerCase()]) == IGK_UNDEF)
							props[s.toLowerCase()] = s;
					}
				}
			}
			// animation and transition
			var e = ['animation', 'transition'];
			var v = vendors;
			// checking global prop
			for (var i = 0; i < v.length; i++) {
				for (var j = 0; j < e.length; j++) {
					var s = (v[i] + e[j]).toLowerCase();
					if ((typeof (props[s]) == IGK_UNDEF) && props[s + "delay"]) {
						props[s] = v[i] + e[j][0].toUpperCase() + e[j].substring(1);
					}
				}
			}


			function __getStyleValue(stylelist, n) {

				switch (n.toLowerCase()) {
					case "transition":
						var s = stylelist[n];
						if (!igk.isUndef(s) && s.length > 0) // you specify a transition. get by chrome
							return s;

						// other navigation join property style
						var v_p = ['property', 'duration', 'timing-function', 'delay'];
						var v_v = vendors;
						var v_k = "";
						var v_prop = {
							toString: function () {
								var t = v_prop.property;
								var di = v_prop.duration;
								var tf = v_prop["timing-function"];
								var dl = v_prop.delay;

								var s = "";
								if (!igk.isUndef(t)) {
									for (var i = 0; i < t.length; i++) {
										if (i > 0) {
											s += ',';
										}
										s += t[i] + " " + di[i] + " " +
											tf[i] + " " +
											dl[i]
											;

									}
								}
								return s;
							}
						};
						var v_t = 0;
						var v_splitcsss_pattern = "([^,(]+(\\(.+?\\))?)[\\s,]*";
						// for standard
						for (var i = 0; i < v_p.length; i++) {
							v_k = n + "-" + v_p[i];
							if ((i == 0) && (typeof (stylelist[v_k]) != igk.constants.undef))
								v_t = 1;
							if (!v_t)
								break;
							s += ((i > 0) ? " | " : "") + stylelist[v_k];
							v_prop[v_p[i]] = igk.system.regex.split(v_splitcsss_pattern, stylelist[v_k]);
						}
						if (!v_t) {
							// find througth specification
							v_t = 0;
							for (var j = 0; j < v_v.length; j++) {
								v_prop[v_v[j]] = {};
								if (v_t)
									s += "|";
								for (var i = 0; i < v_p.length; i++) {
									v_k = v_v[j] + n + "-" + v_p[i];
									s += stylelist[v_k];

									if (!igk.isUndef(stylelist[v_k])) // style found ..
										v_prop[v_p[i]] = igk.system.regex.split(v_splitcsss_pattern, stylelist[v_k]);
								}
								v_t = 1;
							}
						}


						return v_prop.toString();

						break;
					default:
						return stylelist[n];
				}

			}


			// css utility properties
			igk.appendProperties(this, {
				getProperties: function () {
					return props;
				},
				getDomStyle: function () {
					if (domProp == null) {
						//generate
						domProp = {};
						var rb = igk.createNode("body"); //initialize
						rb.setCss({ "display": "none", "clear": "both", "width": "auto" });
						document.body.appendChild(rb.o);
						for (var i in props) {

							domProp[i] = rb.getComputedStyle(props[i]);
							if (i == "width") {
								console.debug(i + " x = " + domProp[i]);
							}
						}
						rb.remove();
					}

					return domProp;
				},
				getStyleValue: function (stylelist, n) {
					// get css style list value
					// @stylelist: get width getComputedStyle function
					// @n : the name of the property to get


					return __getStyleValue(stylelist, n);
				},
				getValue: function (item, name) {
					var tprops = this.getProperties();
					var d = igk.createNode("div");

					// for(var  i in tprops)
					// {
					// d.add("div").addClass("igk-col-lg-12-3").setHtml(i + " : "+tprops[i]);
					// }
					// igk.show_notify_msg("properties", d);
					var kr = tprops[name.toLowerCase()];
					if (tprops[kr]) {
						return item.style[tprops[kr]];
					}
					return item.style[name];
				},
				getVendors: function () {
					return vendors;
				},
				isItemSupport: function (names) {
					if (typeof (names) == 'string') {
						var s = names.toLowerCase();
						return s in props;
					}

					for (var i = 0; i < names.length; i++) {
						if (typeof (props[names[i].toLowerCase()]) != IGK_UNDEF)
							return !0;
					}
					var s = dum.style;
					if (s) {
						// for safari
						for (var i = 0; i < names.length; i++) {
							if (typeof (s[names[i]]) != IGK_UNDEF)
								return !0;
						}
					}
					return !1;
				},
				setProperty: function (item, name, value) {
					var k = {};
					var n = null;
					if (igk.css.isItemSupport(['webkit' + name])) {
						n = props[('webkit' + name).toLowerCase()];
						k[n] = value;
						i
					}
					else if (igk.css.isItemSupport([name])) {
						n = props[name.toLowerCase()];
						k[n] = value;
						// that notation work only for firefox
						// item.setCss({[n]: value});						
					}

					// setting real value

					__setProperty(item, k);
				},

				setProperties: function (item, properties) {
					if ((item == null) || (!item.style)) {
						return;
					}
					var k = {};
					var n = null;
					var v = null;
					for (var ni in properties) {
						if (typeof (ni) != 'string')
							continue;

						if (ni.startsWith("--")) {
							k[ni] = properties[ni];
							continue;
						}
						v = properties[ni];
						if (igk.css.isItemSupport(['webkit' + ni])) {
							n = props[('webkit' + ni).toLowerCase()];
							if (n)
								k[n] = v;

						}
						else if (igk.css.isItemSupport([ni])) {
							n = props[ni.toLowerCase()];
							if (n) {
								k[n] = v;
							}
						}
					}
					// setting real value		
					__setProperty(item, k);

				},
				setTransitionDuration: function (item, time) {
					this.setProperty(item.o, 'transitionduration', time);
					return this;
				},
				toString: function () {
					return "[object igk.css]";
				}
				, setTransitionDelay: function (item, time) {
					this.setProperty(item.o, 'transitiondelay', time);
					return this;
				},
				changeStyle: function (name, style) {// change css style definition
					function changeStyle(selectorText) {
						var theRules = new Array();
						if (document.styleSheets[0].cssRules) {
							theRules = document.styleSheets[0].cssRules;
						}
						else if (document.styleSheets[0].rules) {
							theRules = document.styleSheets[0].rules;
						}
						for (n in theRules) {
							if (theRules[n].selectorText == selectorText) {
								theRules[n].style = style;
							}
						}
					}
				}

			});
			return this;
		})()
	}
	);

	function _is_integer(n) {
		return parseInt(n) + "" == n + "";
	};
	// utility function 
	// createNS("igk", {
	// getQueryOptions: function(){

	// },	
	// isInteger: function (n) {
	// var f = Number.isInteger || _is_integer;
	// return f(n);
	// }
	// });

	// ----------------------------------------------------------------------------------
	// register node mecanism 
	// ----------------------------------------------------------------------------------
	// manage tag component
	igk.ctrl.registerReady(function () {
		var t = this.tagName ? this.tagName.toLowerCase() : "";
		if (m_tag_obj[t] && m_tag_obj[t].func) {
			m_tag_obj[t].func.apply(this);
		}

	});

	// -------------------------------------------------------
	// notification dialog
	// -------------------------------------------------------
	(function () {
		// notify visible
		var m_nv = false;
		var bdiv = null;// shared bdiv
		var notify_frames = [];

		igk.publisher.register("system/bodychange", function () {
			if (m_nv) {
				// item was visible
				if (bdiv != null) {
					igk.dom.body().appendChild(bdiv.o);
				}
			}
		});



		// .STATIC notify objcet
		igk.winui.notify = function () {// notification class .atomic
			if (this.type && (this.type == IGK_CLASS)) {

				if (typeof (this.notify.getInstance) == IGK_UNDEF) {
					this.notify.getInstance = (function () {
						var _instance = new igk.winui.notify();
						return function () {
							return _instance;
						};
					})();
				}
				return this.notify.getInstance();
			}
			if (typeof (igk.winui.notify.getInstance) == IGK_UNDEF) {
				igk.appendProperties(this, {
					name: "igk-notifybox",
					toString: function () {
						return this.name;
					}
				});
				igk.appendProperties(this, igk.winui.notify);
				igk.winui.notify.sm_instance = this;
			}
			else
				return igk.winui.notify.getInstance();
		};



		var defStyle = " google-Roboto";
		// merge with notify access.
		createNS("igk.winui.notify", {
			setFilterClass: function (f) {
				if (bdiv != null)
					bdiv.filters = f;
			},
			// >msg:message
			// >type: type of notify
			// >nc: noclose,
			closeAlls: function () {
				if (notify_frames && notify_frames.length > 0) {
					for (var i = 0; i < notify_frames.length; i++) {
						notify_frames[i].close();
					}
					notify_frames = []; // reset frames
				}
			},
			showMsg: function (msg, type, nc, settings) {
				// msg : content message
				// type : of the content notification
				// nc: close button
				if (typeof (this) == IGK_FUNC) {// static object
					igk.winui.notify().showMsg(msg, type, nc, settings);
					return;
				}
				var div = null;
				var m_hide = false;
				var dial = null;
				var _sm = igk.winui.notify;

				// create shared data
				if (!this.initialize) {

					bdiv = igk.createNode("div");
					this.target = bdiv;
					this.initialize = !0;
					bdiv.filters = "igk-bgfilter-blur";// default filter class
				}


				bdiv = this.target;
				dial = this;
				if (!bdiv)
					return;

				// setup new content
				bdiv.setHtml("");// clear
				bdiv.addClass("igk-js-notify-box");
				var div = bdiv.add("div");
				var cl = "igk-js-notify ";
				if (type) {
					cl += type;

				}
				div.addClass(cl).setOpacity(1);
				div.setAttribute("igk-js-fix-loc-scroll-width", "1");
				div.setHtml("");// clear
				if (typeof (msg) == "string") {
					div.setHtml(msg);// clear content with message
				}
				else {
					div.appendChild(msg);
				}
				var fc = igk.winui.event.fn.handleKeypressExit({
					target: this,
					complete: _g_close_notify
				});

				// append close button
				if (!nc) {
					var close = igk.createNode("div");
					close.addClass("dispb posab loc_r loc_t");
					close.add("a", {
						"onclick": _g_close_notify
					}).setHtml("close").addClass("igk-notify-btn-close");
					div.appendChild(close);

				}
				function _g_close_notify() {
					_close_notify(null);
				};
				function _setupview() {
					div.setCss({
						"height": "auto" // auto by default
					});
					var p = -(div.getHeight() / 2.0);
					var pn = div.getParentNode();
					var t = 0;// to get half position
					var h = div.getHeight() + "px";
					var oflow = false;

					if (pn) {
						t = pn.getHeight() / 2.0;
					}
					else {
						t = div.getTop();
					}
					if ((t + p) < 0) {
						p = -t;
						if (pn.getHeight() < div.getHeight()) {
							h = pn.getHeight() + "px";
							oflow = true;
						}

					}
					div.setCss({
						"marginTop": p + "px"
					});
					if (h != div.getComputedStyle('height')) {
						// ========				
						div.animate({ height: h }, {
							duration: 200,
							interval: 10,
							animtype: "timeout",
							context: "notify-height-context",
							effect: "circ",
							effectmode: "easeinout",
							complete: function () {
								if (!oflow) {
									$igk(div).setCss({ height: "auto", overflowY: "hidden" });
								}
								else {
									$igk(div).setCss({ overflowY: "auto" });
								}
							}
						});
					}
				};
				function _setupview_callback() {
					_setupview();
					igk.winui.unreg_event(window, "load", _setupview_callback);
				};

				// append static method to current instance
				_sm.Close = _close_notify;
				_sm.UpdateView = _setupview;
				_sm.settings = settings;

				var __closing = false;
				function _close_notify(callback) {
					if (__closing)
						return;
					__closing = !0;
					igk.animation.fadeout(div.o, 20, 200, 1.0, function () {

						var pn = bdiv.o.parentNode;
						if (pn) {
							pn.removeChild(bdiv.o);
							if (bdiv.filters)
								$igk(pn).rmClass(bdiv.filters);

						}
						bdiv.unregister();
						__closing = false;
						m_nv = false;
						if (callback) {
							callback.apply(document);
						}
						if (settings && settings.close) {
							settings.close.apply(_sm);
						}

					});
					// unregister key press
					igk.winui.unreg_event(document, "keypress", fc);
					m_hide = !0;
					dial.hide = !0;
				}
				igk.winui.reg_event(window, "load", __show);
				if (!nc) {

					if (igk.navigator.isFirefox()) {
						igk.winui.reg_event(document, "keypress", fc);
					} else {
						igk.winui.reg_event(document, "keydown", fc);
					}
				}

				var m_eventContext = igk.winui.RegEventContext(this, $igk(this));
				if (m_eventContext) {
					m_eventContext.reg_window("resize", _setupview);
				}

				notify_frames.push({
					close: _g_close_notify
				});
				function __show() {
					// because of the ready call you must call only when showMsg

					if (m_hide)
						return;

					igk.dom.body().addClass(bdiv.filters).appendChild(bdiv.o);
					if (!igk.is_readyRegister(__show)) {
						_setupview();
					}

					div.setCss({
						"zIndex": "800",// set to top index
						"top": "50%",
						"overflowY": "auto",
						"overflowX": "hidden"
					});

					igk.ctrl.callBindAttribData(div.o);
					bdiv.addClass("igk-show");
					m_nv = true;
					// igk.animation.fadein(bdiv.o,20,200,{form:0.0,to:0.8});
				};
				igk.ready(__show);
			},
			showMsBox: function (t, m, cln, nc) { // nc : no close button
				var settings = null;
				if (typeof (t) == 'object') {
					var b = t;
					t = b.title;
					m = b.content;
					cln = b.type;
					nc = b.closeButton;
					settings = b.settings;

				}
				

				var q = igk.createNode("div")
					.addClass("igk-notify");
				if (cln) {
					q.addClass(cln);
				}
				q = q.add("div").addClass("igk-container");
				var content = q.add("div");
				content.addClass("content-z");// content zone
				var box = content.add("div");

				box.addClass("title igk-title-4");
				if (igk_is_string(t)) {
					box.setHtml(t);
				}
				else {
					box.add(t);
				}
				if (igk_is_string(m)) {
					content.add("div").addClass("igk-panel igk-notify-panel").setHtml(m);
				}
				else {
					content.add("div").addClass("igk-panel").o.appendChild($igk(m).o);
				}
				igk.winui.notify.showMsg(q, cln, nc, settings);
			},
			showError: function (msg) {
				var q = igk.createNode("div")
					.addClass("igk-notify igk-notify-danger"+defStyle)
					.setHtml(msg);
				igk.winui.notify.showMsg(q.o.outerHTML, "igk-danger");
			},
			showErrorInfo: function (title, msg) {
				var q = igk.createNode("div")
					.addClass("igk-notify igk-notify-danger"+defStyle);
				q.add("div").addClass("igk-title-4").setHtml(title);
				q.add("div").addClass("igk-panel igk-notify-panel").setHtml(msg);
				var msg = q.o.outerHTML.split("\n").join("<br />");
				igk.winui.notify.showMsg(msg, "igk-danger");
			},
			close: function () {
				// close the top notification dialog
				if (notify_frames.length > 0) {
					var f = notify_frames.pop();
					f.close();
				}
			},
			visible: function () {
				return m_nv;
			},
			init: function () {// initialize a notification controler
				// init this current node as a message box 
				var q = $igk(igk.getParentScript());

				if (q && (q.o.parentNode != null)) {
					igk.ready(function () {
						var t = q.select('.title').first().getHtml();
						var m = q.select('.msg').first();
						var i = (q.o.parentNode != null);
						// remove data
						q.select("^.igk-notify-box").remove();
						if (i)
							igk.winui.notify.showMsBox(t, m, q.o.className);
					});
				}
			}

		});

	})();

	// ----------------------------------------------------------------------------
	// balafon js utility fonction
	// ----------------------------------------------------------------------------
	var igk_rmScript = function () {
		var c = igk_getCurrentScript();
		if (c && !c.src) {
			$igk(c).remove();
		}
	};
	createNS("igk.balafonjs.utils", {
		closeDialog: function () {
			igk_rmScript();
		},
		closeNotify: function (g) {
			igk.winui.notify.close();
			if (g)
				igk_rmScript();
		}
	}, {
		desc: 'balafon utility functions namespace'
	});

	//encapsulate event source for best handling behaviour
	createNS("igk.winui.eventArgs", {
		progress: function (evt) {
			var q = this;
			var m_evt = evt;
			igk.appendProperties(this, {
				getEventSource: function () { return m_evt; },
				getTotalSize: function () { return m_evt.TotalSize; },
				getUploadedSize: function () { return m_evt.position; }
			});
		}
	});


	// manage drag and drop operations
	// drag drop properties descriptions
	// {
	// uri: cibling uri
	// 
	// }
	createNS("igk.winui.dragdrop", {
		init: function (target, properties) {
			// usage * properties
			// uri: uri,
			// update:function(evt){}  
			// enter : function(e){}
			// drop: function(e){}
			function dragdropManager(target, properties) {
				if (!target)
					return;
				var m_target = $igk(target);
				var m_properties = properties;
				var m_q = target;
				var m_eventcontext = igk.winui.RegEventContext(m_target, $igk(m_target));
				if (m_eventcontext == null)
					return;

				var q = this;
				var m_supportlist = null;

				igk.appendProperties(this, {
					getUri: function () {
						if (m_properties && m_properties.uri)
							return m_properties.uri;
						return null;
					},
					getProperties: function () {
						return m_properties;
					},
					support: function (k) {// get if this dragdrop support
						if (m_properties && m_properties.supported) {
							if (m_supportlist == null) {
								var e = m_properties.supported.split(",");
								var p = new (function (e) {
									var m_tab = e;
									var m_obj = {};
									for (var i = 0; i < e.length; i++) {
										m_obj[e[i]] = i;
									}
									this.contains = function (s) {
										return typeof (m_obj[s]) != IGK_UNDEF;
									}
								})(e);
								m_supportlist = p;
							}
							return m_supportlist.contains(k);
						}
						return !0;
					},
					toString: function () {
						return "igk.winui.dragdrop.dragdropManager";
					}
				});

				m_eventcontext.reg_event(m_q, "dragenter", function (evt) {
					// console.debug("drag enter");
					evt.preventDefault();
					if (m_properties && m_properties.enter) {
						m_properties.enter.apply(q, arguments);
					}
				});
				m_eventcontext.reg_event(m_q, "dragleave", function () {
					
					if (m_properties && m_properties.leave) {
						m_properties.leave.apply(q, arguments);
					}
				});
				m_eventcontext.reg_event(m_q, "dragover", function (evt) {

					// allow drop on item 
					// evt.dataTransfer.effectAllowed="copy";
					evt.preventDefault();
					if (m_properties && m_properties.over) {
						m_properties.over.apply(q, arguments);
					}

				});
				// not define on firefox
				// m_eventcontext.reg_event(m_q,"dragdrop",function(evt){

				// evt.preventDefault();

				// });
				m_eventcontext.reg_event(m_q, "drop", function (evt) {
					evt.preventDefault();
					if (m_properties && m_properties.drop) {
						m_properties.drop.apply(q, arguments);
						return;
					}
					if (igk.system.array.isContain(evt.dataTransfer.types, "text/html")) {
						var n = igk.createText(evt.dataTransfer.getData("text/html"));
						m_target.appendChild(n);
					}
				});
				// m_eventcontext.reg_event(m_q,"drag",function(evt){

				// });
				// set up
				m_target.setAttribute("draggable", false);
			}

			return new dragdropManager(target, properties);
		}
	});






	function __init_drag() {
		var opts = igk.JSON.parse(this.getAttribute("igk-draggable-data"), this);
		igk.winui.dragdrop.init(this.o, opts);
	}
	// register class
	igk.winui.initClassControl("igk-draggable", __init_drag, { desc: "draggable node" });



	(function () {
		var m_f = [];
		var m_i = null;

		function _get_parentscroll(p) {
			var q = $igk(p);
			var c = q.getscrollParent();

			if (p && p.target) {
				c = q.select(p.target);
			}
			else {
				c = q.select('^.igk-parentscroll').getNodeAt(0);
			}
			return c;
		}
		function fixscrollmanager(t, p, c) {
			var q = $igk(t);
			$igk(c).reg_event("scroll", function (evt) {
				if (p && p.update) {
					p.update.apply(q, [{
						parent: c,
						scroll: { x: c.scrollLeft, y: c.scrollTop }
					}]);
				}
			});
			return this;
		};
		createNS("igk.winui.fn.fixscroll", {
			init: function (p) {// init with property {update:function}
				var q = igk.getParentScript();
				igk.ready(function () {
					if ((q == null) || igk.system.array.isContain(m_f, q)) {
						return;
					}
					var c = _get_parentscroll(q);
					if (c) {
						m_f.push(q);
						return new fixscrollmanager(q, p, c);
					}
				});
			}
		});
	})();

	createNS("igk.storage", { // used to store shared data
	});


	createNS("igk.winui.fn", {
		cancelEventArgs: function (evt) {
			evt.preventDefault();
			evt.stopPropagation();
			return !1;
		},
		close_all_frames: function () {

			igk.winui.notify.closeAlls();
			igk.ready(function () {

				var t = [];
				igk.dom.body().select(".igk-notify-box").each_all(function () {
					var q = this;
					q.remove();

				});


			});

		},
		isNodeVisible: function (n) {
			throw "not implement[isNodeVisible]";
		},
		navigateTo: function (t, pr) {
			var v_t = null;
			return function () {
				var c = null;
				if (typeof (t) == 'string') {
					c = $igk(t).first();
				}
				else {
					c = $igk(t);
				}
				if (c) {
					var p = $igk(c.o.offsetParent);
					if (p) {
						p.scrollTo(c, pr);
					}
				}
				else {
					igk.show_notify_error("Item not found", t);
				}
			};
		}
	});

	(function () {// igk resources manager

		var keys = {};
		var m_lang = {
			fr: ['fr', 'fr-FR', 'fr-Be'],
			en: ['en', 'en-En', 'en-US']
		};
		var _res = {};
		function init_res(_res, loc) {

			igk.appendProperties(_res, {
				getLang: function () {
					var s = igk.navigator.getLang().split(',')[0];
					for (var i in m_lang) {
						if (igk.system.array.isContain(m_lang[i], s))
							return i;
					}
					return s;
				},
				gets: function (k) {
					if (__lang[k]) {
						return __lang[k];
					}
					return k;
				},
				format: function (n) {
					var txt_ = n;
					if (/(string)/.test(typeof (_res[n])))
						txt_ = _res[n];
					if (arguments.length > 1) {
						var tab = igk.system.array.slice(arguments, 1);

						txt_ = txt_.replace(/\{([0-9]+)\}/g, function (m, s) {
							return tab[s];
						});
					}
					//format string 
					return txt_;
				},
				getLocation: function () {
					return loc;
				},
				"__": function (n) {
					if (n in igk.R) {
						return igk.R[n];
					}
					return n;
				}
			});

		};
		igk.defineProperty(igk, 'R', {
			get: function () {
				return _res;
			}
		});


		function _loadResource(loc, mime, opts) {


			mime = mime || "text/plain";
			var _getRes = 0;
			var _src = ["_getRes = async function (){",
				"try{",
				"var o  = await igk.ajx.asyncget(loc,null, mime);",
				"o  = o ? opts.then(o) : null;",
				"return  o;",
				"}catch(ex){",
				" console.error('get resource failed: '+loc+ '\nMessage:'+ex.getMessage());",
				"}",
				"return null;",
				"};"].join(" ");
			try {
				if (igk.navigator.isSafari())
					throw ('safari not handle aync await');
				eval(_src);

			}
			catch (ex) { // do not support async data 
				_getRes = function () {
					var _ext = igk.navigator.isIE() ? ".ejson" : ".json";// because of mime type        
					var e = {
						"then": function (t) {
							this.__then__ = t;
							return this;
						},
						"catch": function (t) {
							this.__catch__ = t;
							return this;
						}
					};
					igk.system.io.getData(loc, function (o) {
						if (e.__then__ && o.data && (o.data.length > 0)) {
							e.__then__(opts.then(o.data));
						}
						else if (e.__catch__) {
							e.__catch__("/!\\ failed: " + loc);
						}
					}, "application/xml");

					return e;
				};
			}

			if (typeof (_getRes) != 'undefined') {
				_getRes().then(function (o) {
					_res = o;
					init_res();
				})["catch"](function (e) {
					var error = 1;
					igk.log.write("[BJS] - " + e);
				});
			}

		};


		igk.ready(function () {
			var _lang = igk.dom.html().getAttribute("lang") || igk.navigator.getLang();

			//if windows application 
			// var _ext = ".json";
			var dir = igk.system.io.getlocationdir(igk.getScriptLocation());
			if (!dir)
				return;

			var loc = igk.resources.getlang(dir, _lang); // + "/Lang/res." + _lang + _ext; 			
			// TODO: Load language resources files ::::: in productions


			// return;
			// alert("loading : "+loc);

			var g = null;
			var _storage = window.localStorage;
			if (typeof (_storage) != 'undefined')
				g = _storage.getItem("lang/" + _lang);//store global language items
			if (g == null) {
				_loadResource(loc, "text/json", {
					then: function (o) {
						if (_storage)
							_storage.setItem("lang/" + _lang, o);
						return igk.JSON.parse(o);
					}
				});

			} else {
				igk.invokeAsync(function () {
					_res = igk.JSON.parse(g);
					init_res(_res, loc);
				});
			}

		});

	})();



	(function () {
		function __sendFile(evt, target, async, update, startCallback, progressCallback, doneCallBack) {
			var p = target.getProperties();

			if (p.buzy) {
				return;
			}

			p.buzy = !0;
			if (evt.dataTransfer && igk.system.array.isContain(evt.dataTransfer.types, "Files")) {
				var _t = evt.dataTransfer.files.length;
				var _m = _t;
				p.total = _t;
				p.current = 0;
				var _r = function () {
					_t--;


					if (_t <= 0) {
						p.buzy = null;
						p.current = 0;
					} else {
						p.current = _m - _t;
					}
					doneCallBack.apply(this, arguments);
				};
				for (var i = 0; i < _m; i++) {
					if (target.support(evt.dataTransfer.files[i].type)) {
						igk.ajx.uploadFile(null, evt.dataTransfer.files[i], target.getUri(), async, update, startCallback, progressCallback, _r /*doneCallBack*/);
					} else {
						//not supported to reduce the total of file upload
						_t--;
						p.total = _t;
					}
				}
			}
			else {
				console.debug(evt.dataTransfer.types);
				igk.winui.notify.showMsBox("Warning", "[ BJS ] - Send file to serveur failed", "warning");
			}

		};
		// 
		// utility function for dropting file
		// 
		createNS("igk.winui.dragdrop.fn", {
			upload_file: function (evt) { // upload file
				evt.preventDefault();
				evt.stopPropagation();
				// important must stop propagation 

				var target = this;
				var p = target.getProperties();
				__sendFile(evt, target, p.async, p.update, p.start, p.progress, p.done);
			}
		}
		);



	})(); 


	createNS("igk.winui.debug", {
		addMsg: function (msg) {
			var d = $igk("igk-debugger").first();
			if (d) {
				d.setHtml(msg);
			}
		}
	});

	// ns uri
	(function () {

		createNS("igk.io.file", {
			// / TODO::check to download data builded with javascript
			download: function (t, n, v) {
				// t: mime-type image/png
				// n: name
				// v: value
				var data = new Blob(["test-ok"]);
				var a = igk.createNode("a");

				// var csv='a,b,c\n1,2,3\n';
				var data = new Blob([v], { "type": t });
				igk.dom.body().appendChild(a.o); // not require in IE 

				a.o.download = n || "file.data";// f
				a.o.href = URL.createObjectURL(data);
				a.o.type = t;

				a.o.click();
				a.remove();
				return 1;
			}
		});
		createNS("igk.io.xml", {
			parseString: function (txt, type) {
				var doc = new DOMParser();
				var xml = doc.parseFromString(txt, type || "text/xml");
				if (xml.documentElement.nodeName == "parsererror") {

					// document.write("Error in XML<br><br>" + xml.documentElement.childNodes[0].nodeValue);
					// alert("Error in XML\n\n" + xml.documentElement.childNodes[0].nodeValue);
					return false;
				}
				return xml;
			},
			parseUri: function (uri) {
				var g = igk.ajx.get(uri, null, null, false);
				var s = g.xhr.responseText;
				return s;
			}

		});

		// -------------------------------------------------------------------
		// testing xsl transformation
		// -------------------------------------------------------------------


		// xml=new ActiveXObject("MSXML2.DOMDocument");
		// xml.async=false;
		// xml.loadXML(xmltxt);
		// igk_show_prop(xml);
		// igk.io.xml.parseString("info");


		// var doc =igk.dom.createDocument();// document.implementation.createDocument(null,"root",null);


		// doc.async=false;
		// __igk(doc).reg_event("readystatechange",function(evt){


		// if(evt.readyState==4){

		// doc =igk.dom.createDocument();
		// doc.async=false;
		// xsl =doc.load("Lib/igk/Scripts/demo.xsl").firstChild;




		// }

		// });

		// igk.ready(function(){






		// document.write(doc.firstChild.outerHTML);
		// });
		// doc.load && doc.load("Lib/igk/Scripts/data.xml",function(evt){


		// var doc2 =igk.dom.createDocument();
		// doc2.async=false;
		// var	xsl=doc2.load("Lib/igk/Scripts/demo.xsl");



		// var ex=igk.dom.transformXSL(doc,xsl);




		// var ex= doc.firstChild.transformNode(xsl);


		// }



		// );
		// document.write(doc.firstChild);
		// igk.ajx.load("Lib/igk/Scripts/data.xml");


		// doc.open("Lib/igk/Scripts/data.xml");		


	})();


	window.onbeforeunload = function (evt) {
		if (igk_freeEventContext) {
			// free all context
			igk_freeEventContext();
		}

	};

	 

	function __global_ready(evt) {
		igk.context = "global_ready";
		if (document.readyState == "complete") {

			igk.ready(null, "readystatechange");
		}
	};
	igk.winui.reg_event(document, "readystatechange", __global_ready);


	function __bindbalafonjs() { // bind balafon js data type
		var q = this.o;
		var src = '"use strict"; (function(){' + q.innerHTML + '}).apply(this);';
		// igk_eval.apply(q, [q.innerHTML, q]);
		try {
			//igk_eval.apply(q, [src, q]);
			var _bck = m_scriptNode;
			m_scriptNode = this.o;
			(new Function(src)).apply(this);
			m_scriptNode = _bck;

		} catch (e) {
			console.debug(src);
			console.error(e);
		}
		if (q.getAttribute("autoremove")) {
			this.remove();
		}
	};

	// balafon script js
	igk.ready(function () {
		var j = 0;
		igk.dom.body().qselect("script[type='text/balafonjs']").each_all(function () {

			__bindbalafonjs.apply(this);
			j++;
		});
	});

	var _udef = 'undefined';
	// special functions
	ns_igk._$exists = function (n) { return typeof (igk.system.getNS(n)) != _udef; };



})(window);

// -----------------------------------------------------------------------------------------
// >namespace: igk.winui 
// -----------------------------------------------------------------------------------------


// ----------------------------------------------------------------------------------------------------------
// -----------------------------------EXTENSION--------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------
(function () {
	if (document.head) {
		var e = $igk(document.head).select("link");
		e.each(function () {
			if (this.getAttribute("rel") == "igkcss") {
				// igk.show_prop(this.o);
				igk.ajx.get(this.getAttribute("href"), null, function (xhr) {
					if (this.isReady()) {
						var s = igk.createNode("style");
						s.setAttribute('igk-id', 'igkcss');
						s.innerHTML = xhr.responseText;
						document.head.appendChild(s);
					}
				}, false); // important to lock loading before the document continue loading
			}
			return !0;
		});
	}
})();

$igk(igk.getParentScript()).select("link").each(function () {
	var s = (this.o.getAttribute("href") + "");
	if (s && (s.indexOf("R/Styles/base.php") != -1)) {
		igk.system.apps.link = this;
		igk.appendProperties(this, {
			basehref: this.o.href,
			counter: 0,
			reload: function () {
				var bck = this.basehref;
				this.o.href = bck + "?reload=" + this.counter;
				this.counter++;
			}
		});
	}
	return !0;
});

igk.ctrl.registerAttribManager("igk-js-fix-loc-scroll-width", { desc: "register element to be fixed with scroll width" });
igk.ctrl.registerAttribManager("igk-js-fix-loc-scroll-height", { desc: "register element to be fixed with scroll height" });
igk.ctrl.registerAttribManager("igk-fix-loc-target", { desc: "defined target parent #id. that will content the parent class. if not defined parent with element parentNode or igk-parentscroll class" });


igk.ctrl.bindAttribManager("igk-js-fix-loc-scroll-width", function () {
	function _fixManager(target) {

		var animinfo = {
			duration: 200,
			interval: 10,
			animtype: "timeout",
			context: "fix-scroll-context",
			effect: "circ",
			effectmode: "easeinout"
		};

		function __update() {
			var s = this.target.getAttribute("igk-js-fix-loc-scroll-width");
			var h = this.target.getAttribute("igk-js-fix-loc-scroll-height");
			var v_target = this.target;
			var p = this.target.getComputedStyle("position");
			if ((p == "fixed")) {
				var m = this.target.getAttribute("igk-fix-loc-target");
				var g = null;
				if (m == null) {
					m = igk.qselect(".igk-parentscroll").first();
					if (m == null) {
						m = this.target.o.parentNode;
					}
				}
				g = $igk(m);
				if (g == null) {// no parent
					return;
				}
				var animprop = {};

				if (s == 1) {
					igk.appendProperties(animprop, { "right": "0px" });
					if (g.fn.hasVScrollBar()) {
						animprop.right = g.fn.vscrollWidth() + "px";

					}
				}
				if (h == 1) {
					igk.appendProperties(animprop, { "bottom": "0px" });
					if (g.fn.hasHScrollBar()) {
						animprop.bottom = g.fn.hscrollHeight() + "px";
					}
				}
				this.target.animate(animprop, animinfo);
			}
		};

		igk.appendProperties(this, {
			target: target,
			update: __update
		});
		var self = this;
		var m_eventContext = igk.winui.RegEventContext(this, $igk(this));
		if (m_eventContext) {
			m_eventContext.reg_window("resize", function () { self.update(); });
		}
		this.update();
		return this;
	}
	var s = this.getAttribute("igk-js-fix-loc-scroll-width");
	if (s == 1 && (this.getComputedStyle("position") == "fixed")) {

		var v_fm = new _fixManager(this);
	}
	return !1;
});


// 
// controller utility presentation igk-new-lang-key
// 
(function () {
	var context_options = {};
	var m_items = {};

	function __reg_key(n, t) {
		if (m_items[n]) {
			var b = m_items[n];
			if (b.length) {
				b.push(t);
			}
			else {
				b = [];
				b.push(m_items[n]);
				b.push(t);
				// replace
				m_items[n] = b;
			}
		}
		else {
			m_items[n] = t;
		}
	}

	function __add_prop() {
		var q = this;
		var k = this.getHtml();
		__reg_key(k, q);

		// q.reg_context_options("modkeyv",k);
		// q.reg_context_options("delkeyv",k);

		// q.reg_event("click",function(evt){		
		// evt.preventDefault();
		// });
		// replace context menu
		q.reg_event("contextmenu", function (evt) {
			evt.preventDefault();


			var langctrl = igk.ctrl.getRegCtrl("c_l");
			if (langctrl) {
				var p = q.getParentCtrl();
				var k = q.getHtml();
				var pid = p ? p.o.id : '';


				igk.ajx.post(langctrl.getUri("mod_key&ajx=1"), "key=" + k + "&ctrl=" + pid,
					igk.ajx.fn.replace_or_append_to_body,
					true);
			}
			else {
				if (!igk.winui.notify.visible()) {
					var h = q.select(".error").first();
					if (h) {
						h = h.getHtml();
						igk.winui.notify.showError(h);//"<div class='error-3'>/!\\ No language controller found !!! </div>");
					}
				}
			}
		});
	}

	function __register_lang_key(s) {
		if (s == null)
			return;
		s.setAttribute("igk-new-lang-key", 1)
			.appendProperties({
				"igk-new-lang-key-setup": 1,
				reg_context_options: function (k, uri) {
					context_options[k] = uri;
				},
				show_context_options: function () {
					var ul = igk.winui.contextMenu();

					ul.setHtml(null);
					var loc = this.getScreenLocation();
					var size = { w: this.getWidth(), h: this.getHeight() + this.o.scrollHeight };

					ul.addClass("posab").setCss({
						zIndex: 10,
						border: "1px solid black",
						padding: "8px",
						position: "absolute",
						left: loc.x + "px",
						minWidth: "200px",
						top: (loc.y + size.h) + "px"
					});

					for (var i in context_options) {
						ul.add("li").add("a")
							.setHtml(i)
							.setAttribute("href", "?mod=1&k=" + context_options[i]);
					}
					igk.dom.body().appendChild(ul);
				}
			}, true);

		if (s.isSr()) {
			s.each_all(__add_prop);
		}
		else {
			__add_prop.apply(s);
		}
	}

	function __initglobalkey() {
		var s = igk.qselect(".igk-new-lang-key").each_all(function () {
			console.debug("register lang key");
			__register_lang_key(this);
		});

		igk.unready(__initglobalkey);
	}
	igk.ready(__initglobalkey);

	igk.ctrl.registerAttribManager("igk-new-lang-key", { desc: "register new keys language editor" });
	igk.ctrl.bindAttribManager("igk-new-lang-key", function (m, n) {

		var t = this["igk-new-lang-key-setup"];
		this.setCss({
			color: "red",
			cursor: "help"
		});
		if (t == null) {
			// init node key

			__register_lang_key($igk(this));
		}

	});

	// update language view 
	igk.system.createNS("igk.winui.langkey.fn", {
		update:
			function (n, v, d) {
				if (n != v) {
					$igk('.igk-new-lang-key').each_all(function () {
						if (this.getHtml() == n) {

							// this.setHtml(v);
							var idx = this.getAttribute("igk:data") || 0;
							d && (v = d[idx]);
							var t = igk.createText(v);
							this.o.parentNode.replaceChild(t.o, this.o);
						}
					});
				}
				var s = igk.getCurrentScript();
				if (s) {
					$igk(s).remove();
				}
			}
	});

})();

igk.system.createNS("igk.winui.navlink", {
	init: function (target, link) {
		if (typeof (this.classinit) == igk.constants.undef) {
			this.navitems = [];
			this.classinit = !0;
		}

		var navitems = this.navitems;


		target.reg_event("click", function (evt) {
			evt.preventDefault();
			var b = $igk("#" + link).first();

			if (b && !b.isSr()) {
				// igk.dom.body()
				var q = $igk(b).getscrollParent();
				// igk.show_prop(b.o);					

				if (q) {
					q.scrollTo(b.o, {
						duration: 500,
						interval: 20,
						effect: "linear",
						effectmode: "easeinout"
					}, null);
				}
			}
			else {
				console.debug('[IGK] - Element not found ' + link);
			}
		});
		navitems.push(target);
	}
});
igk.ctrl.registerAttribManager("igk-nav-link", { desc: "navigation link" });
igk.ctrl.bindAttribManager("igk-nav-link", function (n, m) {
	if (!m)
		return;
	this.setAttribute("href", "!#" + m);
	var link = null;
	igk.winui.navlink.init(this, m);
});

function animationProperty(q, s) {
	// q:target
	// s:source
	if (s == null)
		throw "source is null";
	if (q == null)
		throw "item is null";
	var duration = '0.2s';
	var effect = "ease-in-out";
	var rmduration = '0.2s';
	var rmeffect = 'ease-in-out';
	var tq = igk.JSON.parse(s);
	var store = {};
	for (var m in tq.css) {
		store[m] = q.getComputedStyle(m);
		// for safari fix
		if (typeof (store[m]) == igk.constants.undef)
			store[m] = null;
	}
	if (tq.duration)
		duration = tq.duration;
	if (tq.effect)
		duration = tq.effect;

	igk.appendProperties(this, {
		bind: function () {
			if (q.isCssSupportAnimation()) {
				if (tq.select) {
					q.select(tq.select)
						.setCss({ transition: 'all ' + duration + ' ' + effect })
						.setCss(tq.css);
				}
				else {
					q.setCss({ transition: 'all ' + duration + ' ' + effect })
						.setCss(tq.css);
				}
			}
			else {
				q.animate(tq.css, { duration: igk.datetime.timeToMs(duration) });
			}
		},
		unbind: function () {
			if (q.isCssSupportAnimation()) {
				if (tq.select) {
					q.select(tq.select)
						.setCss({ transition: 'all ' + rmduration + ' ' + effect })
						.setCss(store);
				}
				else {
					q.setCss({ transition: 'all ' + rmduration + ' ' + effect })
						.setCss(store)
						.timeOut(300, function () {
							console.debug("end transition");
						});
				}
			}
			else {
				q.animate(store, { duration: igk.datetime.timeToMs(rmduration) });
			}
		}
	});
}

// translation animations igk-js-anim-msover
igk.ctrl.registerAttribManager("igk-js-anim-msover", { desc: "globa animation style on mouse over" });
igk.ctrl.bindAttribManager("igk-js-anim-msover", function () {
	var q = this;
	var source = this.getAttribute("igk-js-anim-msover");
	var store = {};
	var duration = '0.2s';
	var effect = "ease-in-out";
	var rmduration = '0.2s';
	var rmeffect = 'ease-in-out';

	var tq = null;

	if (!source) {
		return;
	}
	tq = igk.JSON.parse(source);
	for (var m in tq.css) {
		store[m] = q.getComputedStyle(m);
		// for safari fix
		if (typeof (store[m]) == igk.constants.undef)
			store[m] = null;
	}
	if (tq.duration)
		duration = tq.duration;
	if (tq.effect)
		duration = tq.effect;

	this.reg_event("mouseover", function (evt) {
		if (q.isCssSupportAnimation()) {
			if (tq.select) {
				q.select(tq.select)
					.setCss({ transition: 'all ' + duration + ' ' + effect })
					.setCss(tq.css);
			}
			else {
				q.setCss({ transition: 'all ' + duration + ' ' + effect })
					.setCss(tq.css);
			}
		}
		else {
			q.animate(tq.css, { duration: igk.datetime.timeToMs(duration) });
		}

	});
	// mouseenter not supported for safari window
	// this.reg_event("mouseenter",function(){ 

	// });
	// mouseout vs mouseleave . safari error
	this.reg_event("mouseout", function () {

		// igk.show_prop(store);
		if (q.isCssSupportAnimation()) {
			if (tq.select) {
				q.select(tq.select)
					.setCss({ transition: 'all ' + rmduration + ' ' + effect })
					.setCss(store);
			}
			else {
				q.setCss({ transition: 'all ' + rmduration + ' ' + effect })
					.setCss(store)
					.timeOut(300, function () {
						console.debug("end transition");
					});
			}
		}
		else {
			q.animate(store, { duration: igk.datetime.timeToMs(rmduration) });
		}
	});

}
);


igk.ctrl.bindAttribManager("igk-js-anim-focus", function () {
	var q = this;
	var source = this.getAttribute("igk-js-anim-focus");
	var store = {};
	if (!source) {
		return;
	}

	if (q.isCssSupportAnimation()) {
		var anim = new animationProperty(q, source);
		q.reg_event("focus", function () { anim.bind(); });
		q.reg_event("blur", function () { anim.unbind(); });

	}
	else {
		var t = eval("new Array(" + source + ")");
		for (var m in t[0]) {
			store[m] = q.getComputedStyle(m);
		}
		this.reg_event("focus", function () { eval("q.animate(" + source + ");"); });
		this.reg_event("blur", function () { q.animate(store, t[1]); });
	}
});


igk.ctrl.bindAttribManager("igk-js-bind-select-to", function (n, v) {
	// v : target id or json properties
	// 	: target must start with #
	// 	: if json properties allowed value is {id:target,}
	var s = null;
	var q = this;
	var qv = q.getAttribute('value');

	if (igk.system.string.startWith(v, "#")) {
		s = $igk(v);
		if (s) {
			s.select("option").each(function () {
				// copy
				q.appendChild(this.clone());
				// continue execution
				return !0;
			});
		}
	}
	else {
		var s = igk.JSON.parse(v);
		if (s && s.id) {

			if (s.allowempty) {
				var opt = igk.createNode("option");
				opt.setAttribute("value", typeof (s.emptyvalue) != igk.constants.undef ? s.emptyvalue : null);
				q.appendChild(opt);
			}
			var select = s.selected;
			var tag = s.tag ? s.tag : 'option';

			var present = false;
			var v_sl = $igk(s.id).select(tag)
				.each(function () {
					// copy				
					var r = null;
					if (tag != 'option') {
						r = igk.createNode("option");
						r.copyAttributes(this);
						r.setHtml(this.o.innerHTML);
					}
					else
						r = this.clone();
					var vv = r.getAttribute('value');
					if (vv == select) {
						r.setAttribute('selected', 'true');
						present = true;
					}
					// alert("tag is "+tag + " VS  "+r.o.tagName);
					// if (tag != 'option'){
					// r.o.tagName = "option";
					// }
					q.appendChild(r);
					// continue execution
					return !0;
				});
			if (!present && s.usecustom) {

				if ((select + "").length > 0) {
					var r = igk.createNode("option");
					r.setAttribute('value', select);
					r.setAttribute('selected', 'selected');
					r.setHtml(select);
					q.appendChild(r);
				}
			}
		}

	}
});



// igk.ctrl.bindAttribManager("igk-js-bind-select-to",function(n,v){
// var s=null;
// var q=this;
// var qv=q.getAttribute('value');

// if(igk.system.string.startWith(v,"#"))
// {
// s=$igk(v);
// if(s){
// s.select("option").each(function(){
// copy
// q.appendChild(this.clone());
// continue execution
// return !0;
// });
// }
// }
// else{
// var s = igk.JSON.parse(v);	
// if(s && s.id){

// if(s.allowempty){
// var opt=igk.createNode("option");
// opt.setAttribute("value",typeof(s.emptyvalue) !=igk.constants.undef ? s.emptyvalue : null);
// q.appendChild(opt);

// }
// var select=s.selected;
// var tag=s.tag ? s.tag : 'option';
// var present=false;
// s=$igk(s.id).select(tag)
// .each(function(){
// copy

// var r=null;
// if(tag !='option')
// {
// r=igk.createNode("option");
// r.copyAttributes(this);
// r.setHtml(this.o.innerHTML);
// }
// else 
// r=this.clone();
// var vv=r.getAttribute('value');
// if(vv==select){
// r.setAttribute('selected','true');
// }
// if(tag !='option')
// r.o.tagName="option";
// q.appendChild(r);
// present |=(vv==qv);
// continue execution
// return !0;
// });

// if((qv!=null) && !present){
// var r=igk.createNode("option");
// r.setAttribute('value',qv);
// r.setHtml(qv);
// q.appendChild(r);
// }
// }

// }
// });








// handler for touch events
(function () {
	var m = [];
	var c = igk.createNode("div");

	function __disable_func(evt) {
		evt.preventDefault();
	}

	// override the click event functions register function for click or touch screen
	igk.winui.registerEventHandler("click", {
		reg_event: function (item, func, useCapture) {	// click host handler				
			return igk.winui.reg_system_event(item, "click", func, useCapture);
		},
		unreg_event: function (item, func) {			 			
			return igk.winui.unreg_system_event(item, "click", func); 
		}
	});



	function _dblevent(item, func) {
		var last = 0;
		func.badge = function (evt) {
			if ((evt.timeStamp - last) < 500) {
				//console.debug("change type");
				var h = igk.winui.events.createEvent("dbltouch", {});
				//evt.type ="dbltouch";
				func.apply($igk(item).o, [h]);
			}
			last = evt.timeStamp;
		};
		return func.badge;
	};

	igk.winui.registerEventHandler("dbltouch", {
		reg_event: function (item, func, useCapture) {
			if ($igk(item).istouchable()) {
				return igk.winui.reg_system_event(item, "touchstart", _dblevent(item, func), useCapture);
			}
			return 0;
		},
		unreg_event: function (item, func) {
			if ($igk(item).istouchable()) {
				return igk.winui.unreg_system_event(item, "touchstart", func, useCapture);
			}
		}
	});

	var m_eventDatas = {};
	function _regEventData(n, data) {
		var t = null;
		if (typeof (m_eventDatas[n]) == igk.constants.undef) {
			t = [];
		}
		else
			t = m_eventDatas[n];
		t.push(data);
		m_eventDatas[n] = t;
	};
	function _unregEventData(n, item, func) {
		var t = null;
		var c = false;
		if (m_eventDatas[n] != igk.constants.undef) {
			t = m_eventDatas[n];
			var cp = [];

			var e = null;
			for (var s = 0; s < t.length; s++) {
				e = t[s];
				if ((e.i == item) && (e.func == func)) {
					cp.push(e);
					c = !0;
				}
			}
			if (c)
				m_eventDatas[n] = cp;
		}
		return c;
	};
	function _getEventData(n, item, func) {
		var e = null;
		var t = m_eventDatas[n];
		if (t) {
			for (var s = 0; s < t.length; s++) {
				e = t[s];
				if ((e.i == item) && (e.func == func)) {
					return e;
				}
			}
		}
		return !1;
	};
	function _unbindEventData(e, n) {
		var t = m_eventDatas[n];
		var r = 0;
		if (t) {
			var __t = [];
			for (var s = 0; s < t.length; s++) {

				if (t[s] == e) {
					r = 1;
					continue;
				}
				__t.push(t[s]);
			}
			m_eventDatas[n] = __t;
		}
		return r;
	};

	
	// register double click event
	(function(PN){
	igk.winui.registerEventHandler(PN, {
		reg_event: function (item, func, useCapture, single) {
			var _dc = 0;
			if (typeof (single) == 'undefined') {
				single = 1;
			}
			if (single && (_dc = _getEventData(PN, item, func))) {
				console.debug("[BJS] - function already binded for .", item, item === _dc.i, _dc);
				return;
			}
			var c = {
				n: PN, index: 0, i: item, h: 0, "func": func, bind: function (evt) {
					// bind is the actual method that will be register 
					if (evt.type == "touchend") {
						if (evt.cancelable) {
							evt.preventDefault();
							evt.stopPropagation();
						}
						c.h = 1;
					}
					else if (c.h == 1) {
						c.h = 0;
						return;
					}
					c.func.apply(item, [evt]);
				}
			};
			c.index = m_eventDatas[c.n] ? m_eventDatas[c.n].length : 0;
			_regEventData(c.n, c);
			if ($igk(item).istouchable()) {
				igk.winui.reg_system_event(item, "touchend", c.bind, useCapture);
			}
			return igk.winui.reg_system_event(item, "click", c.bind, useCapture);
		},
		unreg_event: function (item, func, useCapture) {
			var n = PN;
			var c = _getEventData(n, item, func);
			if (c) {
				if ($igk(item).istouchable()) {
					igk.winui.unreg_system_event(item, "touchend", c.bind);
				}
				var o = igk.winui.unreg_system_event(item, "click", c.bind);
				//console.debug("unbind event : "+ 
				_unbindEventData(c, n);

				return o;
			}
		}
	});
	})("touchOrClick");
	(function(PN){  
		igk.winui.registerEventHandler(PN, {
			reg_event: function (item, func, useCapture, single) {
				var _dc = 0;
				if (typeof (single) == 'undefined') {
					single = 1;
				}
				if (single && (_dc = _getEventData(PN, item, func))) {
					console.debug("[BJS] - function already binded for .", item, item === _dc.i, _dc);
					return;
				}
				var c = {
					n: PN, index: 0, i: item, h: 0, "func": func, bind: function (evt) {
						// bind is the actual method that will be register 
						if (evt.type == "touchend") {
							if (evt.cancelable) {
								evt.preventDefault();
								evt.stopPropagation();
							}
							c.h = 1;
						}
						else if (c.h == 1) {
							c.h = 0;
							return;
						}
						c.func.apply(item, [evt]);
					}
				};
				c.index = m_eventDatas[c.n] ? m_eventDatas[c.n].length : 0;
				_regEventData(c.n, c);
				if ($igk(item).istouchable()) {
					igk.winui.reg_system_event(item, "dbltouchend", c.bind, useCapture);
				}
				return igk.winui.reg_system_event(item, "dblclick", c.bind, useCapture);
			},
			unreg_event: function (item, func, useCapture) {
				var n = PN;
				var c = _getEventData(n, item, func);
				if (c) {
					if ($igk(item).istouchable()) {
						igk.winui.unreg_system_event(item, "doubletouchend", c.bind);
					}
					var o = igk.winui.unreg_system_event(item, "dblclick", c.bind);
					//console.debug("unbind event : "+ 
					_unbindEventData(c, n);
	
					return o;
				}
			}
		});
	})("doubleTouchOrClick");

	function __mobile_device_event(n, mobe) {
		return {
			reg_event: function (item, func, useCapture) {
				var c = {
					n: n, index: 0, i: item, h: 0, "func": func, bind: function (evt) {
						if (evt.type == mobe) {
							if (useCapture && !useCapture.passive) {
								evt.preventDefault();
								evt.stopPropagation();
							}
							c.h = 1;
						}
						else if (c.h == 1) {
							c.h = 0;
							return;
						}
						c.func.apply(item, [evt]);
					}
				};
				c.index = m_eventDatas[c.n] ? m_eventDatas[c.n].length : 0;
				_regEventData(c.n, c);
				if ($igk(item).istouchable()) {
					igk.winui.reg_system_event(item, mobe, c.bind, useCapture);
				}
				return item;// null;// igk.winui.reg_system_event(item,"click",c.bind,useCapture);
			},
			unreg_event: function (item, func, useCapture) {
				var c = _getEventData(n, item, func);
				var o = null;
				if (c) {
					if ($igk(item).istouchable()) {
						o = igk.winui.unreg_system_event(item, mobe, c.bind);
					}
					// var o=igk.winui.unreg_system_event(item,"click",c.bind);				

					return o;
				}
			}
		};
	};


	igk.winui.registerEventHandler("igkTouchStart", __mobile_device_event("igkTouchStart", "touchstart"));
	igk.winui.registerEventHandler("igkTouchMove", __mobile_device_event("igkTouchMove", "touchmove"));
	igk.winui.registerEventHandler("igkTouchEnd", __mobile_device_event("igkTouchEnd", "touchend"));
	// {
	// reg_event: function(item,func,useCapture){
	// var c={n:'igkTouchStart',index: 0,i: item,h: 0,"func":func,bind: function(evt){


	// if(evt.type=="touchend"){
	// evt.preventDefault();
	// evt.stopPropagation();
	// c.h=1;
	// }
	// else if(c.h==1){
	// c.h=0;
	// return;
	// }
	// c.func.apply(item,[evt]);
	// }};
	// c.index=m_eventDatas[c.n] ? m_eventDatas[c.n].length : 0;
	// _regEventData(c.n,c); 
	// if($igk(item).istouchable())
	// {			
	// igk.winui.reg_system_event(item,"touchstart",c.bind,useCapture);
	// }		
	// return item;// null;// igk.winui.reg_system_event(item,"click",c.bind,useCapture);
	// },
	// unreg_event: function(item,func,useCapture){
	// var c=_getEventData("igkTouchStart",item,func);
	// var o=null;
	// if(c){
	// if($igk(item).istouchable())
	// {			
	// o = igk.winui.unreg_system_event(item,"touchstart",c.bind);
	// }		
	// // var o=igk.winui.unreg_system_event(item,"click",c.bind);				

	// return o;
	// }
	// }		
	// });



	if (typeof (c.o.onmouseenter) == "undefined") {// for safari browser usage
		igk.winui.registerEventHandler("mouseenter", {
			reg_event: function (item, func, useCapture) {	// mousenter				
				return igk.winui.reg_system_event(item, "mouseover", func, useCapture);
			},
			unreg_event: function (item, func, useCapture) {
				return igk.winui.unreg_system_event(item, "mouseover", func);
			}
		});
	}

	c.unregister();
	// delete(c);

})();

// handler for mousewhell
(function () {
	var _N = "mousewheel wheel";
	igk.winui.registerEventHandler(_N, {
		reg_event: function (item, func, useCapture) {
			var _n = ("onmousewheel" in item) ? "mousewheel" : "wheel";
			return igk.winui.reg_system_event(item, _n, func, useCapture);

		},
		unreg_event: function (item, func, useCapture) {
			var _n = ("onmousewheel" in item) ? "mousewheel" : "wheel";
			return igk.winui.unreg_system_event(item, _n, func);
		}
	}
	);

})();

// handler for transitionend
(function () {
	var m = {};
	var webkit = new igk.system.collections.list();// store key function
	var webkit_e = new igk.system.collections.dictionary();
	var inchain = false;

	function __webkitcall(evt) {
		var r = webkit.toArray();
		for (var i = 0; i < r.length; i++) {
			var s = webkit_e.getItem(r[i]);
			if (s != null) {
				// dispatch event to child
				__call(r[i], s.toArray(), arguments);
			}
			r[i].apply(igk.winui.eventTarget(evt), arguments);
		}
	}
	function __call(func, r, args) {
		for (var i = 0; i < r.length; i++) {
			func.apply(r[i], args);
		}
	}
	function __reg_item_func(item, func) {
		var s = webkit_e.getItem(func);
		if (s == null) {
			s = new igk.system.collections.list();
			webkit_e.add(func, s);
		}
		s.add(item);
	}
	function __unreg_item_func(item, func) {
		var s = webkit_e.getItem(func);
		if (s != null) {
			s.remove(item);
			if (s.getCount() <= 0) {
				webkit_e.remove(func);
				return !0;
			}
			return !1;
		}
		return !0;
	}
	// register webkit event
	function _reg_webkitevent(n) {

		var kn = 'onwebkit' + n;
		return {
			reg_event: function (item, func, useCapture) {// trans
				// item.
				if (item == null)
					return;






				if ((typeof (item[kn]) != igk.constants.undef)
					||
					((item != window) && (typeof (window[kn]) != igk.constants.undef))
				) {
					// if(kn=="onwebkittransitionend"){
					// window[kn]=function(){

					// };
					// return ;
					// }
					if (webkit.contains(func)) {
						if (item != window)
							__reg_item_func(item, func);
						return;
					}
					webkit.add(func);
					if (window[kn] == null)
						window[kn] = __webkitcall;
					else if ((window[kn] != __webkitcall) && !inchain) {
						var tfunc = window[kn];
						window[kn] = function (evt) {
							tfunc.apply(this, arguments);
							__webkitcall.apply(this, arguments);
						};
						inchain = !0;
					}
					webkit_e.add(item);
					return;
				}
				// register 		
				if (item.addEventListener) {

					item.addEventListener(n, func, useCapture);
				}
			},
			unreg_event: function (item, func, useCapture) {
				// 
				if (item == null)
					return;
				if (webkit.contains(func)) {

					if (item != window) {
						if (!__unreg_item_func(item, func)) {
							return;
						}
					}
					webkit.remove(func);
					if (webkit.getCount() == 0) {
						if (!inchain) {
							window[kn] = null;
						}
					}
					return;
				}
				if (item.removeEventListener) {
					item.removeEventListener(n, func);
				}

			}
		};

	}
	// current version of firefox not  raising transition start
	igk.winui.registerEventHandler('transitionstart', _reg_webkitevent('transitionstart'));
	igk.winui.registerEventHandler('transitionend', _reg_webkitevent('transitionend'));
	igk.winui.registerEventHandler('animationend', _reg_webkitevent('animationend'));
	igk.winui.registerEventHandler('animationiteration', _reg_webkitevent('animationiteration'));



})();


(function () {
	//resizing handle
	function _resizingHandle() {
		return {
			reg_event: function (item, func, useCapture) {// trans
				igk.winui.reg_event(window, 'resize', func);
				return item;
			},
			unreg_event: function (item, func, useCapture) {
				igk.winui.unreg_event(window, 'resize', func);
				return item;
			}
		};
	};

	igk.winui.registerEventHandler('windowresize', _resizingHandle());
})();


(function () {

	// TODO Register fullscreenevent
	function __gettabname(item, tab) {
		var kn = 0;
		var s = "";
		for (var i = 0; i < tab.length; i++) {
			s = "on" + tab[i];
			if (s in item) {
				kn = tab[i];
				break;
			}
		}
		return kn;
	}
	function __initEvents(tab, fallbackitem) {
		return {
			reg_event: function (item, func, useCapture) {// trans
				// item.
				if (item == null)
					return;
				var kn = __gettabname(item, tab);
				if (!kn) {
					if (fallbackitem && (fallbackitem != item)) {
						kn = __gettabname(fallbackitem, tab);
						item = fallbackitem;
					}
					if (!kn) {
						console.debug("can't register event : " + tab[0]);
						return;
					}
				}
				igk.winui.reg_system_event(item, kn, func, useCapture);
			},
			unreg_event: function (item, func, useCapture) {
				// 
				if (item == null)
					return;
				if (item == null)
					return;
				var kn = __gettabname(item, tab);
				if (!kn) {
					if (fallbackitem && (fallbackitem != item)) {
						kn = __gettabname(fallbackitem, tab);
						item = fallbackitem;
					}
					if (!kn) {
						console.debug("can't unregister event " + tab[0]);
						return;
					}
				}
				igk.winui.reg_event(item, kn, func, useCapture);
			}
		};
	};


	igk.winui.registerEventHandler("fullscreenchange", __initEvents(["fullscreenchange", "webkitfullscreenchange", "mozfullscreenchange", "msfullscreenchange", "ofullscreenchange"], document));
	igk.winui.registerEventHandler("fullscreenerror", __initEvents(["fullscreenerror", "webkitfullscreenerror", "mozfullscreenerror", "msfullscreenerror", "ofullscreenerror"], document));


})();


// ---------------------------------------------------------
// BIND SELECTION MANAGEMENT
// ---------------------------------------------------------
(function () {

	var m_viewarticle = false;
	var m_article = new igk.system.collections.list();

	igk.ctrl.bindAttribManager("igk-article-options", function () {
		var q = this;


		var source = igk.system.convert.parseToBool(this.getAttribute("igk-article-options"));
		q.show = function () {
			// this.setCss({display:"block",position:"relative"});
		};
		q.hide = function () {
			// this.setCss({display:"none",position:"absolute"});			
		};
		if (!m_viewarticle)
			q.hide();
		else {
			q.show();
		}
		m_article.add(q);

	});

	igk.ctrl.bindAttribManager("igk-js-fix-height", function (n, m) {
		var r = null;
		if (m == "::")
			r = $igk(this.o.parentNode);
		else
			r = $igk(m).first();
		if (r == null)
			return;
		var q = this;
		// register size changed
		var m_eventContext = igk.winui.RegEventContext(this, $igk(this));
		if (m_eventContext) {
			m_eventContext.reg_window("resize", function () {

				q.setCss({ height: r.getHeight() + "px" });
			});
		}
		// r.reg_event('resize',function(){

		// });

		// for(var s in r.o){
		// if(igk.system.string.startWith(s,'on'))
		// {
		// r.o[s]=(function(s){

		// return function(){
		// switch(s){
		// case "onmouseenter":
		// case "onmouseout":
		// case "onpointerenter":
		// case "onpointermove":
		// case "onmousemove":
		// case "onmouseleave":
		// case "onpointerleave":
		// case "onpointerout":
		// case "onpointerover":
		// case "onmouseover":

		// return;
		// }

		// };

		// })(s);
		// }
		// }
		// var t=1;
		// function __change_size(){
		// if(t==1)
		// r.setCss({height:"150px"});
		// else
		// r.setCss({height:"200px"});		

		// t=(t+1) % 2;
		// setTimeout(__change_size,2000);
		// }
		// __change_size();
		// r.setCss({border: '1px solid black'});
		q.setCss({ height: r.getHeight() + "px" });
	});
	igk.ctrl.bindAttribManager("igk-js-fix-width", function (n, m) {
		var r = null;
		if (m == "::")
			r = $igk(this.o.parentNode);
		else
			r = $igk(m).first();
		if (r == null)
			return;
		var q = this;
		function __update() {
			q.setCss({ width: (r.getWidth()) + "px" });
		}
		// register size changed
		var m_eventContext = igk.winui.RegEventContext(this, $igk(this));
		if (m_eventContext) {
			m_eventContext.reg_window("resize", function () {
				__update();
			});
		}
		__update();
	});


	igk.ctrl.bindAttribManager("igk-js-fix-eval", function (n, m) {
		var o = igk.JSON.parse(m);
		if (o == null)
			return;
		var q = this;
		function __update() {
			o.update.apply(q);
		}
		// register size changed
		var m_eventContext = igk.winui.RegEventContext(this, $igk(this));
		if (m_eventContext) {
			m_eventContext.reg_window("resize", function () {
				__update();
			});
		}
		__update();

	});


	// igk-js-eval
	(function () {
		function __evalfunction() {
			var s = this.getAttribute("igk-js-eval");
			if (s) {
				igk.eval(s, this.o);
			}
		};
		igk.ctrl.bindAttribManager("igk-js-eval", __evalfunction);
	})();
	// igk-js-eval-init
	(function () {
		var m_initItems = [];
		function __init() {
			for (var i = 0; i < m_initItems.length; i++) {
				var s = m_initItems[i].getAttribute("igk-js-eval-init");
				igk.eval(s, m_initItems[i].o);
			}
		};
		igk.ready(__init);

		function __evalfunction() {

			var s = this.getAttribute("igk-js-eval-init");
			if (s) {
				if (document.readyState == "complete")
					igk.eval(s, this.o);
				else
					m_initItems.push(this);

			}
		};
		igk.ctrl.bindAttribManager("igk-js-eval-init", __evalfunction);
	})();

	(function () {

		function __evalUri() {
			var self = this;
			var uri = this.getAttribute("igk-js-init-uri");
			if (uri) {
				igk.ajx.get(uri, null, function (xhr) {
					if (this.isReady()) {
						this.replaceResponseNode(self.o, false);
					}
				}, true);
			}
		}
		igk.ctrl.bindAttribManager("igk-js-init-uri", __evalUri);
	})();

	// #igk-ajx-form
	(function () {
		igk.winui.registerEventHandler("igkFormBeforeSubmit", {
			reg_event: function(item, fc){
				if (item.tagName == "FORM"){	
					if ( !("igkFormBeforeSubmit" in item)){ 
					$igk(item).addEvent("igkFormBeforeSubmit", {
						"cancelable":true,
						"bubbles": true
					}).on("submit", function(e){
						var cancel = false;
						$igk(item).raiseEvent("igkFormBeforeSubmit", null, function(e){							
							cancel = e.defaultPrevented;
						});
						if (cancel){
							e.preventDefault();
							e.stopPropagation();
						}
					});
					
				} 
				igk.winui.reg_system_event(item, 'igkFormBeforeSubmit', fc );
			}
			return item;				
			}, 
			unreg_event: function(item, fc){
				igk.winui.reg_system_event(item, 'igkFormBeforeSubmit', fc);
				return item;
			}
		}); 

		function close(q) {
			var h = q.select("^.igk-js-notify-box").first();
			if (h) {
				igk.winui.notify.close();
			}
		}
		function __init() {
			var self = this;
			var r = this.getAttribute("igk-ajx-form");
			var r_obj = r && (r != '1') ? igk.JSON.parse(r) : null;
			var noa = this.getAttribute("igk-ajx-form-no-autoreset");
			var no_c = this.getAttribute("igk-ajx-form-no-close");
			var ajxdata = igk.JSON.parse(this.getAttribute("igk-ajx-form-data"));
			//register cusom event
			this.addEvent("igkFormBeforeSubmit", {
				"cancelable":true,
				"bubbles": true
			}); 

		  


			if (((r == 1) || r_obj) && (this.o.tagName.toLowerCase() == "form")) {

				var b = r_obj && r_obj.targetid ? r_obj.targetid : self.getAttribute("igk-ajx-form-target");
	 
		
				this.reg_event("submit", function (evt) {
					var cancel = false;
					var c = self.raiseEvent("igkFormBeforeSubmit", null, function(e){
						cancel = e.defaultPrevented;						
					});
					evt.preventDefault();
					evt.stopPropagation(); 
					if (cancel){
						return;
					}

					igk.ajx.postform(self.o, self.o.action, function (xhr) {

						if ((xhr.readyState == 4) && (xhr.status != 200)) {
							close(self);
							return;
						}
						if (this.isReady()) {
							if (b) {
								var k = null;

								switch (b) {
									case "=":
										k = self;
										break;
									case "::":// hold global selection selection 
									case "??":// body target
										k = igk.dom.body();
										k.unregister();
										igk.ajx.fn.replace_or_append_to_body.apply(this, [xhr]);
										k = null;
										break;
									default:
										k = $igk(b).first();
										break;
								}
								if (k) {
									k.unregister();
									this.setResponseTo(k.o, false);
								}
							}
							else {
								var s = self.o["igk:source"];
								var k = $igk(s);
								if (k) {
									k.unregister();
									this.setResponseTo(k.o, false);
								}
								else {
									igk.ajx.fn.append_to(xhr, igk.dom.body().o);
								}
							}
							// close the frame box by default
							var frame = self.o["igk:framebox"];
							if (!no_c && frame) {
								frame.close();
							} else {
								close(self);
							}
							// autoreset
							if (!noa)
								self.o.reset();
							if (ajxdata && ajxdata.complete) {
								ajxdata.complete.apply(self);
							}
							if (r_obj && r_obj.complete) {
								r_obj.complete.apply(self);
							}
						}
					});
				});
			}
		}
		igk.ctrl.bindAttribManager("igk-ajx-form", __init);
	})();




	(function () {
		var m_ctrl = null;

		igk.appendProperties(igk.ctrl, {
			show_article_options: function () {
				for (var i = 0; i < m_article.length; i++) {
					if (!m_viewarticle)
						m_article[i].show();
					else m_article[i].hide();
				}
				m_viewarticle = !m_viewarticle;
				if (m_viewarticle)
					igk.web.setcookies("igk-sao", "1");
				else
					igk.web.setcookies("igk-sao", null);
			},
			show_ctrl_options: function () {
				if (m_ctrl == null) {
					// ....
					return;
				}
				for (var i = 0; i < m_ctrl.length; i++) {
					if (!m_viewctrl)
						m_ctrl[i].show();
					else
						m_ctrl[i].hide();
				}
				m_viewctrl = !m_viewctrl;
				if (m_viewctrl)

					igk.web.setcookies("igk-sco", "1");
				else
					igk.web.setcookies("igk-sco", null);
			}

		});
	})();


	function __init_roll_owner(q) {
		q.reg_event("mouseout", function (evt) { __rmClass.apply(q); });
		q.reg_event("mouseover", function (evt) { __addClass.apply(q); });
		q.select(".igk-roll-in").each_all(function () {
			var g = this.select("^.igk-roll-owner").first();
			if (g == q) {
				this.rollparent = q;
				var self = this;
				this.reg_event("mouseover", function (evt) {
					self.addClass("igk-roll-in-hover");
				});
				this.reg_event("mouseenter", function (evt) {
					self.addClass("igk-roll-in-hover");
				});
			}
		});
	};

	function __addClass() {
		var t = $igk(this);
		$igk(this).qselect(".igk-roll-in").each_all(function () {
			if (this.rollparent == t)
				this.addClass("igk-roll-in-hover");

		});
	}
	function __rmClass() {
		var t = $igk(this);
		t.select(".igk-roll-in").each_all(function () {
			if (this.rollparent == t) {
				this.rmClass("igk-roll-in-hover");
			}
		});
	};
	function __toggleview(v) {
		if (v)
			__addClass.apply(this);
		else
			__rmClass.apply(this);
	};


	function __bind_roll() {
		// 
		// init system web item class registration 
		// 

		// (function(){
		// rollowner manager
		// igk-roll-in-hover
		// igk-roll-in

		// roll style 
		igk.qselect(".igk-roll-owner").each(function () {
			var q = this;
			__init_roll_owner(q);
			return !0;
		});
		igk.qselect(".igk-touch-roll-owner").each(function () {
			this.rmClass("igk-roll-owner");// for cohesion
			var m = false;
			var q = this;
			if (this.istouchable()) {
				this.reg_event("touchend",
					function () {
						__toggleview.apply(q, [!m]);
						m = !m;
					}
				);
			}
			else {
				this.reg_event("click",
					function () {
						__toggleview.apply(q, [!m]);
						m = !m;
					}
				);
			}
			return !0;
		});






		
	};

	igk.ready(function () {
		// roll owner specification

		igk.ctrl.registerReady(function (e) {
			if (igk.system.regex.item_match_class("igk-roll-owner", this)) {
				__init_roll_owner($igk(this));
			}
		});

		new __bind_roll();



	});

})();

//----------------------------------------------------------
// BIND EVENT ATTRIB Management
//----------------------------------------------------------
(function () {
	var _attribs = {};
	igk.ctrl.registerAttribManager("igk:oninit", {});
	igk.ctrl.bindAttribManager("igk:oninit", function (m, v) {
		// alert("bind attrib management: "+v);
		try {
			var fc = Function(v);
			fc.apply(this.o);
			if (!_attribs["oninit"])
				_attribs["oninit"] = [];
			_attribs["oninit"].push(this);
		} catch (e) {
			console.error(e);
		}
	});
})();

// 
// class control: igk-fixed-action-bar
// attributes : igk-target . target to the how if not found get the scroll parent
// desc: used to show a action bar . the action bar is hidden by default.
// when scrollling the target item scroll will then show the action-bar.
// note action-bar is fixed
// 
(function () {
	var m_size = 0;
	var m_item = new igk.system.collections.list();


	function __initview(n, t) {
		var h = n.data.offset || n.getHeight();
		if (t.o.scrollTop <= h) {
			n.rmClass("igk-show");
		}
		else {
			n.addClass("igk-show");
		}
		// var w = n.data.measure.getWidth() 
		// - (-igk.getPixel(n.getComputedStyle("marginLeft"), n.o))
		// -igk.getPixel(n.getComputedStyle("marginRight"), n.o);
		var w = n.data.measure.getWidth()
			- -igk.getPixel(n.getComputedStyle("marginLeft"), n.o)
			- igk.getPixel(n.getComputedStyle("marginRight"), n.o);



		n.setCss({ width: w + "px" });

	};

	function __register(n) {
		if (!m_size) {
			igk.winui.reg_event(window, "resize", function () {
				var t = m_item.getCount();
				var n = 0;
				for (var i = 0; i < t; i++) {
					n = m_item.getItemAt(i);
					__initview(n, n.data.target);
				}
			});
			n.setCss({ top: "0px" });
			m_size = 1;
		}

		var id = n.getAttribute("igk-target") || "^.igk-parentscroll";
		var offset = n.getAttribute("igk-offset");
		if (id) {
			m_item.add(n);

			var me = igk.createNode("div").addClass("posab fitw").setCss({ height: "1px", "visibility": "hidden" });

			n.data.measure = me;
			n.data.offset = offset;

			n.insertAfter(me.o);

			var c = $igk(n.select(id));
			if (c) {
				if (c.isSr()) {

					var q = c.first();
					if (!q && (id != "^.igk-parentscroll")) {
						console.error("fixed-actionbar:  target not found : " + id);
						return;
						//q = n.select("^.igk-parentscroll").first();

					}
					q.reg_event("scroll", function () {
						__initview(n, q);
					});
					__initview(n, q);
					n.data.target = q;
				}
				else {
					n.data.target = c;
					$igk(c).reg_event("scroll", function () {
						__initview(n, c);
					});
					__initview(n, c);
				}


			}
			else {
				console.debug("item not found " + c);
			}
		} else {
			console.info("warning: no igk-fixed-action-bar-target attribute found");
		}
	};

	igk.winui.initClassControl("igk-fixed-action-bar", function () {
		__register(this);

	}, {
		"desc": "fixed: action-bar"
	});

})();


// 
// igk.winui.framebox
// 
(function () {

	var m_targetResponse = null;
	var framebox_callback = [];

	function __submit_form(evt) {
		// submit the form frame
		var q = this;
		var c = (m_targetResponse != null) ? m_targetResponse : eval(q.getAttribute("igk-ajx-lnk-tg-response"));
		var clf = q.getAttribute("igk-frame-close");	// close frame after
		if (c) {
			var m = document.getElementById(c);
			if (m) {
				window.igk.ajx.postform(q,
					q.action,
					function (xhr) { if (this.isReady()) { if (xhr.responseText.length > 0) { this.setResponseTo(m); } } q.reset(); },
					false);
				if (clf) {
					q['igk:framebox'].close();
				}
				evt.preventDefault();
				return !1;
			}
		}
		// don't cancel the default prevent mecanism
		return !0;
	}



	function __call_frame_closed(frameid) {// call event for frame closed 
		// passsing an array to functions
		var v_fremoved = [];
		var v_args = [frameid];
		for (var i = 0; i < framebox_callback.length; i++) {
			var f = framebox_callback[i];

			if ((f != null) && (f.apply(window, v_args))) {
				v_fremoved.push(f);
			}
		}
		// remove callback
		for (var j = 0; j < v_fremoved.length; j++) {
			framebox_callback.pop(v_fremoved[j]);
		}
	}

	function __close_frame(m_frame) {
		igk.winui.framebox.frames.pop(m_frame);
		var tf = igk.winui.framebox.frames;
		igk.winui.framebox.currentFrame = tf.length > 0 ? tf[tf.length - 1] : null;
	}

	// 
	igk.system.createNS("igk.winui.framebox",
		{
			// currentFrame: null,// visible frame
			frames: new Array(),// array of frames
			reg_frame_close: function (callback) {// call when frame closed on client side	
				framebox_callback.push(callback);
			},
			close_currentframe: function (node) {
				var frm = ns_igk.winui.framebox.getdialog_frame(node);

				igk.ajx.get(frm.closeuri + "&ajx=1", null, null);
				var f = frm;
				$igk(f.parentNode).setCss({ opacity: 1 }).animate(
					{ opacity: 0 }, {
					interval: 20,
					duration: 200,
					complete: function () {
						ns_igk.winui.framebox.close(frm);
					}
				});
			},
			getdialog_frame: function (node) {// register dialog for drawing manager
				var t = null;
				var k = "igk-framebox-dialog";
				if (node.id == k)
					t = node;
				else
					t = igk.getParentById(node, k);
				if (t) {
					igk.appendProperties(t, {
						"frameDialogOwner": t.parentNode
					});
				}
				// return the framebox_dialog. normarly parent is body a div that is contained in a body.
				// the frameOwner is the node thatwill be removed 
				return t;
			},
			reg_dialog: function (node) {// register dialog
				if (node == null)
					return;
				var t = igk.winui.framebox.getdialog_frame(node);
				var n = null;
				if (t !== null) {
					n = $igk(t).select(".title").getNodeAt(0);

					if (n) {
						// init title
						igk.winui.dragFrameManager.init(n, t);
					}
					igk.winui.framebox.currentFrame = t;	// setup the current frame
					igk.winui.framebox.frames.push(t);
				}
			},
			init: function (p, w, h) {	// init frame box. p=parent,w=require width,h=require height
				if (p == null) {
					return;
				}
				var m_frame = igk.winui.framebox.getdialog_frame(p);
				var m_closeBtn = p.getElementsByTagName("a")[0];
				if (m_frame == null)
					return;
				if (m_closeBtn == null) {


					igk.winui.notify.showErrorInfo("Error", '/!\\ JS: frame init :: No close button found.');
					return;
				}
				igk.winui.framebox.reg_dialog(p);
				m_frame.closeuri = m_closeBtn.href + "&ajx=1";

				function __centerDialog() {
					igk.winui.centerDialog(p, w, h);
				};
				function __setSize(m) {
					m.setCss({ "width": (igk.winui.screenSize().width - 15) + "px" });
				};

				// center dialog



				if (igk.system.regex.item_inherit_class("igk-android", p)) {
					var m = $igk(p).rmClass("resizable").addClass("dispb fit overflow-y-a")
						.select(".datas");



					igk.winui.reg_event(window, 'resize', function () {
						__setSize(m);
					});
					__setSize(m);
					// $igk(p).add("div").setHtml(igk.winui.screenSize());
					// alert(igk.winui.screenSize());

				} else {
					if (!$igk(p).supportClass("no-center"))
						__centerDialog();
				}
				m_frame.parentNode.close = function () {// register frame to close 
					igk.winui.framebox.close(m_frame);
				};
				m_frame.close = function () {
					igk.winui.framebox.close(this);
				};

				var s = $igk(m_frame).select("*");
				// mark all properties width a frame property
				m_frame.parentNode.targetResponse = m_targetResponse ? m_targetResponse : igk.dom.body().o;
				s.setProperties({ "igk:framebox": m_frame });
				$igk(m_frame).select("form").each_all(function () {
					var f = this.o.onsubmit;
					if (f) {
						this.o.onsubmit = f;
					}
					else {
						this.o.onsubmit = __submit_form;
					}
				});

				function __a_close_ajx() {
					igk.ajx.get(m_frame.closeuri, null, null);
					var f = m_frame;
					$igk(f.parentNode).setCss({ opacity: 1 }).animate(
						{ opacity: 0 }, {
						interval: 20,
						duration: 200,
						complete: function () {
							igk.winui.framebox.close(f);
						}
					});
				}
				// register event
				function __a_event(evt) {
					if (igk.winui.framebox.currentFrame == m_frame) {
						if (evt.keyCode == 27) {

							if (!evt.defaultPrevented) {
								__a_close_ajx();
								evt.preventDefault();// for only one
								igk.winui.events.unregKeyPress(__a_event);
								// remove frames lists
								__close_frame(m_frame);
							}
						}
					}
				};
				igk.winui.events.regKeyPress(__a_event);

				var s = ("" + m_closeBtn.href).split("#")[1];
				var id = null;
				if (s && (s.split('?')[0] == "/closeframe")) {
					id = s.split("/closeframe?id=")[1];
					m_closeBtn.onclick = function () {
						var frame = igk.getParentById(p, id);
						if (frame) {
							// close frame by animating it
							$igk(frame).setCss({ opacity: 1 }).animate(
								{ opacity: 0 }, {
								interval: 20,
								duration: 200,
								complete: function () {
									frame.parentNode.removeChild(frame);
									__a_close_ajx();

								}
							});
						}
						return !1;
					};
				}
				else if (m_closeBtn) {
					m_closeBtn.onclick = function (evt) {
						evt.preventDefault();
						__a_close_ajx();
						return !1;
					};
				}
			},
			close: function (frame) {// close the current frame
				frame = frame;

				if (frame && frame.frameDialogOwner) {
					var p = frame.frameDialogOwner;
					var s = $igk(frame);
					if (s.unregEventContext) {

						s.unregEventContext();
					}

					if (p && p.parentNode) {
						p.parentNode.removeChild(p);
						__call_frame_closed(p.id);
					}
				}
			},
			closeCurrentFrame: function () {
				var f = igk.winui.framebox.currentFrame;
				if (f) {
					// unreg event
					__close_frame(f);

				}
			},
			init_confirm_frame: function (p, uri, ajxcontext) {
				var frm = igk.getParentByTagName(p, 'form');
				var r = frm.getAttribute("igk-confirmframe-response-target"); // get response id	
				if (r != null) {
					m_targetResponse = document.getElementById(r);
				}
				function __closeForm() {
					igk.winui.events.unregKeyPress(__keypressfunc);
					var v_frame = igk.winui.framebox.getdialog_frame(frm);
					if (v_frame != null) {
						igk.winui.framebox.close(v_frame);
					}
				}
				function __keypressfunc(evt) {
					switch (evt.keyCode) {
						case 27: // escape
							if (!ajxcontext) {
								frm.action = uri;
								frm.confirm.value = 0;
								frm.submit();
							}
							else {

								igk.ajx.post(
									frm["frame-close-uri"].value + "&id=" + frm["frame-id"].value,
									null, null, false);
								__closeForm();
							}
							igk.winui.events.unregKeyPress(__keypressfunc);
							evt.preventDefault();
							evt.stopPropagation();
							return !1;
						case 13:
							igk.winui.events.unregKeyPress(__keypressfunc);
							frm.submit();
							evt.preventDefault();
							return !1;
					}
				}
				if (frm) {
					igk.winui.events.regKeyPress(__keypressfunc);
				}

			},
			btn: {
				yes: function (q) {// for yes button message response

					// return !1;			
					var v_frame = q['igk:framebox'];
					window.igk.ajx.postform(q.form, q.form.action, function (xhr) {
						if (this.isReady()) {
							if (v_frame) {
								v_frame.close();
								if (v_frame.targetResponse) {
									this.setResponseTo(v_frame.targetResponse);
								} else {
									igk.ajx.fn.replace_or_append_to_body.apply(this, [xhr]);
								}

							}
							else {
								// this.replaceBody(xhr.responseText);	 
								igk.ajx.fn.replace_or_append_to_body.apply(this, [xhr]);
							}
						}
					},
						false);

					return !1;
				}
			}
		});// end namespace

	var _c_frame = null;
	igk.defineProperty(igk.winui.framebox, "currentFrame", {
		get: function () { return _c_frame; },
		set: function (v) { _c_frame = v; }
	});
})();



// -------------------------------------------------------------
// igk.ctrl.menu
// -------------------------------------------------------------

(function () {

	igk.system.createNS("igk.ctrl.menu",
		{
			init: function (target) {
				var q = $igk(target);
				var e = $igk(q.getAttribute("igk-data-menu-binding")).first();
				if (this.namespace) {
					if (e)
						return new this.init(target);
					return null;
				}
				var b = igk.createNode("div");

				b.setCss({
					overflowY: 'auto'
				}).addClass("fitw fith igk-data-src");
				b.setHtml(e.getHtml());
				q.o.appendChild(b.o);
				igk.ajx.fn.initnode(b.o);
			}
		});

	var v_menu_name = "igk-data-menu";
	if (!igk.ctrl.isAttribManagerRegistrated(v_menu_name))
		igk.ctrl.registerAttribManager(v_menu_name, { n: "js", desc: "menu register " });

	igk.ctrl.bindAttribManager(v_menu_name, function () {
		var q = this;
		var source = igk.system.convert.parseToBool(this.getAttribute(v_menu_name));
		if (source) {
			igk.ctrl.menu.init(q);
		}
	});
})();

// -------------------------------------------------------------
// 
// -------------------------------------------------------------
(function () {

	// ajx stored
	var m_ajx = null;
	function __init_tab_control() {
		var q = this.select("ul").select("li.igk-active").first();
		var self = this;
		if (q != null) {
			this.o.activetab = q;
		}
		this.o.activate = function (i) {
			var s = i.select("^li");
			if (self.o.activetab)
				self.o.activetab.rmClass("igk-active");
			s.addClass("igk-active");
			self.o.activetab = s;
		};
		this.init = !0;
	};


	igk.winui.initClassControl("igk-tabcontrol", __init_tab_control, {
		desc: "igk-control : tabcontrol"
	});

	igk.system.createNS("igk.winui.controls.tabcontrol", {
		init: function (uri, q) {
			ns_igk.ajx.fn.scriptReplaceContent('GET', uri, q);
		}
	});


	igk.ctrl.registerAttribManager("igk-ajx-tab-lnk", { ns: "ajx", desc: "tabcontrol ajx link" });
	igk.ctrl.bindAttribManager("igk-ajx-tab-lnk", function (m, s) {
		if (typeof (this.attribs) == 'undefined')
			this.attribs = {};

		if (this.attribs.tabattribs) {
			return;
		}

		this.attribs.tabattribs = 1;;


		var tab = this.select('^.igk-tabcontrol').first();
		if (tab == null) { console.error("/!\\ no tabcontrol found"); return; }

		if (typeof (tab.init) == "undefined") {
			init_tab_control.apply(tab);
		}


		
		var self = this;
		if (s){
			this.reg_event("click", function (evt) {
				var q = tab.select('.igk-tabcontent').first();
				evt.preventDefault();
				if (m_ajx) {
					m_ajx.abort();
				}
				if (!q){
					return;
				}
				q.addClass("fade-out");// .setHtml('');	

				m_ajx = igk.ajx.get(self.o.href, null, function (xhr) {
					if (this.isReady()) {
						igk.ajx.fn.replace_content(q.o).apply(this, [xhr]);
					} else {
						if ((xhr.readyState == 4) && (xhr.readyState != 200)) {
							m_ajx.abort();
							q.setHtml('Error');
						}
					}
				}, true);
				tab.o.activate(self);

			}).reg_event("focus", function (evt) {
				evt.preventDefault();
				this.blur();
			});;
		}

	});



})();

(function () {
	function _init_track() {
		var c = this.select(".igk-trb-cur").first();
		if (c) {
			var r = new (function (q, c) {
				q.addEvent("igk-trackchange", { value: null });

				// demo reg event track changed		
				this.target = q;
				this.c = c;
				this.data = null;
				var v_d = q.getAttribute("igk-trb-data");
				if (v_d)
					this.data = igk.JSON.parse(v_d);
				var self = this;
				var m_s, m_e;
				var m_st = false;
				function __update(evt) {
					if (!m_st)
						return;
					var l = q.getScreenLocation();
					var W = q.getWidth();
					var H = q.getHeight();
					r = igk.getNumber(q.getComputedStyle("paddingTop")) +
						igk.getNumber(q.getComputedStyle("paddingBottom"));
					H -= r;

					var dirup = igk.system.regex.item_match_class("igk-dir-up", q.o);
					var hv = false;
					if (dirup) {
						hv = !0;
						m_s = Math.min(100, parseInt((Math.max(0, H - Math.min(evt.clientY - l.y, H)) / H) * 10000) / 100);
						// self.c.setCss({marginTop: parseInt((H *( 1 - m_s/100)))+"px"});			
						self.c.setCss({ top: parseInt((H * (1 - m_s / 100))) + "px", bottom: '0px' });
					}
					else if (igk.system.regex.item_match_class("igk-dir-down", q.o)) {
						hv = !0;
						m_s = Math.min(100, parseInt((Math.max(0, Math.min(evt.clientY - l.y, H)) / H) * 10000) / 100);
					}


					q.o["igk-track"] = m_s;


					if (hv) {
						self.c.setCss({ height: m_s + "%" });
						if (self.data && self.data.update) {
							self.data.update.apply(self, [{ progress: m_s, bar: self }]);
						}
					}
					else {
						m_s = parseInt((Math.max(0, Math.min(evt.clientX - l.x, W)) / W) * 10000) / 100;
						self.c.setCss({ width: m_s + "%" });
						if (self.data && self.data.update) {
							self.data.update.apply(self, [{ progress: m_s, bar: self }]);
						}
					}

					// DAISE EVENT for data changed
					q.o["igk-trackchange"].value = m_s;
					q.raiseEvent("igk-trackchange");
				}
				q.reg_event("mousedown", function (evt) {
					if (m_st)
						return;
					m_st = !0;
					// cancel mouse selection
					igk.winui.mouseCapture.setCapture(q.o);
					igk.winui.selection.stopselection();
					__update(evt);
				});
				q.reg_event("mousemove", function (evt) {
					__update(evt);
				});
				q.reg_event("mouseup", function (evt) {
					if (m_st) {
						__update(evt);
						m_st = false;
						igk.winui.mouseCapture.releaseCapture();
						igk.winui.selection.enableselection();
					}
				});
				igk.appendProperties(this, {
					toString: function () { return "igk-trb-info" }
				});

			})(this, c);
		}
	}

	igk.ctrl.registerReady(function (e) {
		if (igk.system.regex.item_match_class("igk-trb", this)) {
			_init_track.apply($igk(this));
		}
	});


})();


// ---------------------------------------------------------------
// indication
// ---------------------------------------------------------------
// igk-ajx-lnk
// possible value : 1 | {method:function([ajx.get|ajx.post]),execute: [execute directly] ,complete: after receive}
(function () {
	var m_xhr = null;
	igk.system.createNS("igk.winui.ajx.lnk", {
		getLink: function () { return m_xhr.source; },// expose link for evaluation
		getXhr: function () { return m_xhr; }
	});
	igk.ctrl.registerAttribManager("igk-ajx-lnk", { ns: "ajx", desc: "Ajax link. used in combination with 'igk-ajx-lnk-tg' properties. " });
	igk.ctrl.bindAttribManager("igk-ajx-lnk", function (n, m) {
		if (!m)
			return;
		var q = this;
		var v = this.getAttribute("href");
		var meth = this.getAttribute("igk-ajx-lnk-method") ?? "GET";

		if (m && v) {
			q.addClass("igk-ajx-lnk");
			var v_meth = m.method || igk.ajx.get;
			if (meth== "POST"){
				v_meth = igk.ajx.post;
			}
			v = igk.html.appendQuery(v, "ajx-lnk=1");
			var fc = m.update;
			var obj = igk.JSON.parse(m, q);
			var opxhr = null;
			q.reg_event("click", function (evt) { 
				if (evt.handle || evt.defaultPrevented) {
					return;
				}
				evt.preventDefault();
				evt.stopPropagation();
				evt.handle = 1;
				if (obj.execute) {
					obj.execute.apply(this, evt);
					return;
				}
				var tn = q.getAttribute("igk:target");
				var rpm = q.getAttribute("igk:replacemode") || 'content'; // content| node
				var r = null;
				if (obj.target)
					r = $igk(obj.target).first();
				else if (tn) {
					r = q.select(tn).first() || $igk(tn).first(); // || q.select(tn).first();
				}




				if (r != null) {
					if (rpm == "content") {
						fc = igk.ajx.fn.replace_content(r.o);
					} else
						fc = igk.ajx.fn.replace_node(r.o);
					// var t = r.first();
					// if (t)
					// fc = igk.ajx.fn.replace_content(t.o);
					// else {

					// q.select(tn).first();
					// }
				}
				if (opxhr != null) {
					opxhr.abort();
					opxhr = null;
				}
				if (fc == null) {
					fc = igk.ajx.fn.append_to_body;
				}
				opxhr = v_meth(v, null, function (xhr) {
					if (this.isReady()) {
						m_xhr = xhr;
						this.source = q;
						if (fc) {
							fc.apply(this, [xhr]);
						}
						if (obj.complete) {
							obj.complete.apply(this, [xhr]);
						}
						m_xhr = null;
					}
				});
			});
		}
	});


	igk.ctrl.registerAttribManager("igk-callback", {
		"desc": "register extra function to be called from server script"
	});
	var fcs = {
		'hide': function (n) {
			return function () {
				this.select(n).hide();// ("igk-hide").remove();
			};
		}
	};
	igk.ctrl.bindAttribManager("igk-callback", function (t, v) {
	
		var c = igk.JSON.parse(v);
		var g = {};
		if (c) {
			for (var i in c) {

				var f = c[i];
				if (igk.typeofs(f) && (i in fcs)) {
					this[i] = fcs[i](f);
				} else if (igk.typeoff(f)) {
					this[i] = f;
				} else {
					console.debug("failed " + typeof (f));
				}
			}
		}
		this.callback = g;
	});



	igk.winui.initClassControl("igk-winui-ajx-lnk-replace", function () {

		var q = this;
		var _i = q.getAttribute("igk-lnk-target");
		var _index = q.getAttribute("igk-lnk-index") || "0";
		var _host = m_host || igk.ajx.GetParentHost();

		// return;
		var ck = 0;
		if (_host) {
			var sl = _host.select(_i);
			if (sl.getCount() > 0) {
				if (_index == "*")
					sl.setHtml(q.o.innerHTML).init();
				else
					sl.getItemAt(_index).setHtml(q.o.innerHTML).init();
				ck = 1;
			}
		}
		if (!ck) {
			var v = igk.dom.body().select(_i).first();
			if (v) {
				v.setHtml(q.o.innerHTML).init();
			} else
				console.debug("item not found " + _i);
		}
		q.remove();
	}, { desc: "use to replace a cibling node with the inner content" })


})();


(function () {
	igk.ctrl.registerAttribManager("igk-ajx-lnk-form", { ns: "ajx", desc: "ajax link. used in combination with igk-ajx-data properties" });
	igk.ctrl.bindAttribManager("igk-ajx-lnk-form", function (n, m) {
		var q = this;
		var v = this.getAttribute("href");
		if (m && v) {
			q.reg_event("click", function (evt) {
				evt.preventDefault();
				var t = $igk(q.getAttribute("igk-ajx-lnk-tg")).first();
				var frm = q.getParentForm();
				if (frm) {
					var fc = null;
					if (t)
						fc = igk.ajx.fn.replace_node(t.o);
					igk.ajx.postform(frm, v, fc);
				}
			});
		}
	})
})();

(function () {
	igk.ctrl.registerAttribManager("igk-js-cn", { ns: 'js', desc: "igk clone node target" });
	igk.ctrl.bindAttribManager("igk-js-cn", function (n, a) {
		var q = $igk(a);
		if (q) {
			var b = q.clone();
			b.o["id"] += "-cn";
			this.o.parentNode.replaceChild(b.o, this.o);
		}
	});
})();

// igk-toggle
(function () {
	igk.ctrl.registerAttribManager("igk-js-toggle", { ns: 'js', desc: "igk toggle property on click" });
	igk.ctrl.bindAttribManager("igk-js-toggle", function (n, a) {
		if (!a) return;

		var p = igk.JSON.parse(a);
		var self = this;
		var co_id = this.getAttribute("igk-js-toggle-cookies");

		if (!p.name)
			p.name = "class";

		var pt = this.select(p.parent).first();
		if (!pt)
			return;
		var q = pt.select(p.target).first();
		var self = this;
		if (!q) {
			igk.show_notify_error(n + " [not found]", "can't notify error");
			return;
		}

		function __click(evt) {
			evt.preventDefault();
			switch (p.name) {
				case 'class':
				default:
					if (igk.system.regex.item_match_class(p.data, q.o)) {
						q.rmClass(p.data);
						pt.addClass("igk-toggle");
						if (co_id) {
							igk.web.setcookies(co_id, 1);
						}
					}
					else {
						q.addClass(p.data);
						pt.rmClass("igk-toggle");
						if (co_id) {
							igk.web.rmcookies(co_id);
						}
					}
					if (p.complete)
						p.complete.apply(self);
					break;
			}
		}
		if (this.istouchable()) {
			this.reg_event("touchend", __click);
		}
		else
			this.reg_event("click", __click);

		if (co_id) {

			var s = igk.web.getcookies(co_id);
			if (s) {
				this.o.click();
			}
		}
	});
})();

// disable-selection
(function () {

	igk.system.createNS("igk.ctrl.selectionmanagement", {
		disable_selection: function (t) {
			t = $igk(t).o;
			if (typeof (t.onselectstart) != "undefined")
				t.onselectstart = function () { return !1; };
			if (typeof (t.style.MozUserSelect) != "undefined")
				t.style.MozUserSelect = "none";

			t.onblur = function () { return !1; };
			t.ondragstart = function () { return !1; };
			$igk(t).fn.noselection = 1;
		},
		enableSelection: function (t) {
			t = $igk(t);

			if (t.fn.noselection) {

				t.o.onblur = null; //function () { return !1; };
				t.o.ondragstart = null; //function () { return false };

				if (typeof (t.o.onselectstart) != "undefined")
					t.o.onselectstart = null; // function () { return !1; };
				if (typeof (t.o.style.MozUserSelect) != "undefined")
					t.o.style.MozUserSelect = "";

				delete (t.fn.noselection);
			}
		},

		clearSelection: function () {//clear selction 
			var sl = window.getSelection();
			if (sl.rangeCount > 0) {
				for (var i = 0; i < sl.rangeCount; i++) {
					sl.removeRange(sl.getRangeAt(i));
				}
			}
		}
		, initnode: function () {// init node for selection management
			var q = this;
			var source = this.getAttribute("igk-js-anim-over");
			var store = {};
			if (source) {
				var t = eval("new Array(" + source + ")");
				for (var m in t[0]) {
					store[m] = q.getComputedStyle(m);
				}
				this.reg_event("mouseover", function (evt) {

					if (q.supportAnimation()) {
						var d = igk.JSON.parse(source);
						q.setCss({ transition: 'all 0.5s ease-in-out' })
							.setCss(d);
					}
					else {
						eval("q.animate(" + source + ");");
					}

				});
				this.reg_event("mouseleave", function () {
					if (q.supportAnimation()) {
						q.setCss({ transition: 'all 0.2s ease-in-out' }).setCss(store).timeOut(300, function () {
							q.setCss({ transition: null });
						});
					}
					else {
						q.animate(store, t[1]);
					}
				});
			}
		}
	});



	igk.ctrl.bindAttribManager("igk-node-disable-selection", function () {
		var s = igk.system.convert.parseToBool(this.getAttribute("igk-node-disable-selection"));
		if (s == true) {
			var q = this.o;
			igk.ctrl.selectionmanagement.disable_selection(q);
		}
	});
})();


// disable context menu attribute

(function () {
	igk.ctrl.bindAttribManager("igk-no-contextmenu", function () {
		this.on("contextmenu", function (e) {
			e.preventDefault();
			e.stopPropagation();
		});
	}, {
		desc: 'disable context menu on node'
	});

})();

// parent scroll marker
(function () {
	igk.winui.initClassControl("igk-parentscroll", function () {
		var q = this;
		q.reg_event("scroll", function (evt) {
			igk.publisher.publish("sys://html/doc/scroll", { target: this, args: evt });
		});
	});
})();


//----------------------------------------------------
// igk-js-autofix attribute data bidning
//----------------------------------------------------
(function () {
	igk.ctrl.registerAttribManager("igk-js-autofix", { ns: 'js', desc: "auto fix position of element" });
	igk.ctrl.bindAttribManager("igk-js-autofix", function (m, n) {
		if (!n)
			return;
		var o = igk.JSON.parse(n); // 1|{left: length, top: length, offset: }
		var c = null;
		var autofixcallback = this.getAttribute("igk:autofixcallback") || function () { };

		if (o == 1) {
			o = { target: null, style: null, offset: 0 };
			var fixs = this.getAttribute("igk-autofix-style");
			o.style = igk.JSON.parse(fixs) || null;

		} else if (typeof (o) != 'object') {
			return;
		}

		if (!o.target) c = this.select("^.igk-parentscroll").first();
		else c = this.select(o.target).first();
		if (!c)
			return;
		if (!o.style)
			o.style = { left: "0px", top: "0px" };

		var t = this;
		var tc = igk.createNode("div");
		tc.setHtml("&nbsp;").addClass("no-visibility");
		var oldStyle = {};
		for (var s in o.style) {
			oldStyle[s] = this.getComputedStyle(s);
		}

		function __initview() {
			var g = t.getScreenLocation();


			var npos = 0;

			if (npos == 'fixed') {
				if (c.o.scrollTop <= o.offset) {
					t.rmClass("posfix");
					t.setStyle(oldStyle);
					autofixcallback(1);
				}
			} else {
				if (c.o.scrollTop > o.offset) {
					t.addClass("posfix");
					if (o.style) {
						t.setCss(o.style);
					}

					autofixcallback(2);
				}
			}
			// alert("top : "+o.style.top);

			// if (c.o.scrollTop > o.offset) {
			// // $igk(t.o.parentNode).prependChild(
			// if (!igk.system.regex.item_match_class("posfix", t.o)) {
			// t.o.parentNode.insertBefore(tc.o, t.o);
			// tc.setCss({ height: t.getHeight() + "px" });
			// t.addClass("posfix_i")
			// .setCss(o.style)
			// .setCss({'width': tc.getWidth() + "px" });
			// }

			// }
			// else {
			// t.rmClass("posfix").setCss({
			// left: 'auto',
			// top: 'auto',
			// width: 'auto'
			// });
			// tc.remove();
			// }
		};
		$igk(c).reg_event("scroll", function (evt) {
			__initview();
		});

		igk.ready(function () {
			__initview();
		});


	});

})();


//
//+ attrib : igk-js-autofix-item
//
(function () {
	igk.ctrl.registerAttribManager("igk-js-autofix-item", { 'ns': 'js', 'desc': 'start auto fix item to host. item must be added before being initialized' });

	igk.ctrl.bindAttribManager("igk-js-autofix-item", function (m, n) {
		var o = igk.JSON.parse(n);
		var c = null;
		if (!o.target) c = this.select("^.igk-parentscroll").first();
		else c = this.select(o.target).first();

		c = c || $igk(document);

		c.reg_event("scroll", __autofix_check_scroll(this, c, o));
	});

	function __autofix_check_scroll(q, p, o) {
		var man = new _autofix_manager();
		man.q = q;
		man.p = p;
		man.o = o;

		function _hostbind(e) {
			man.check(e);
		};
		return _hostbind;
	};
	//create a class
	function _autofix_manager() { };
	//add method to class using prototype
	_autofix_manager.prototype.check = function (e) {

		var g = this.q.getScreenLocation();
		var c = this.p;
		var offset = this.o.offset || 0;

		if (c.o.scrollTop > offset) {
			console.debug("bind ...");
			c.addClass("igk-js-autofix-host");
			this.q.addClass("fix");
		} else {
			console.debug("unbind");
			c.rmClass("igk-js-autofix-host");
			this.q.rmClass("fix");
		}
	};

})();


// igk.ctrl.togglebutton management
(function () {

	igk.system.createNS("igk.ctrl.togglebutton",
		{
			init: function (target) {
				if (!target)
					return;
				var q = $igk(target);
				var s = q.getAttribute("igk-toggle-target");
				if (!s)
					return null;
				if (this.namespace) {
					return new this.init(target);
				}


				var e = $igk(s).first();
				var t = q.getAttribute("igk-toggle-class");
				var toggle = "visibiliy";
				// if(t){
				toggle = "class";
				// }

				this.updateState = function () {
					var v_state = q.getAttribute("igk-toggle-state");
					switch (v_state) {
						case "expand":
							{
								if (e) e.setCss({ width: 200 / 3.0 + "%" });
								q.setAttribute("igk-toggle-state", "collapse");
							}
							break;
						case "collapse":
							if (e) e.setCss({ width: 0 + "%" });
							q.setAttribute("igk-toggle-state", "expand");
							break;
					}
				};
				var self = this;
				q.reg_event("click", function (evt) {
					self.updateState();
				});
				this.updateState();
			}
		});


	if (!igk.ctrl.isAttribManagerRegistrated("igk-toggle-button")) {
		var k = igk.ctrl.registerAttribManager("igk-toggle-button", { n: "js", desc: "register toggle button" });
	}
	igk.ctrl.bindAttribManager("igk-toggle-button", function () {

		var v = this.getAttribute("igk-toggle-button");
		var q = this;
		var source = igk.system.convert.parseToBool(this.getAttribute("igk-toggle-button"));
		if (source) {
			igk.ctrl.togglebutton.init(q);
		}
	});
})();





// img.src="?vimg=warzone";

// Q: javascript get current domain?
// R: document.domain,window.location.hostname

// Q: define constant in javascript
// R: no const name for most navigator. use property expression

// Q: async a post
// R: ok true=async ,false=sync

// Q: property in igk
// R: by default properties is a set of [g/s]et[method_name]

(function () {
	igk.system.createNS("igk.html", {
		addInput: function (t, p, n, v) {
			i = igk.createNode("input");
			i.o["type"] = n;
			i.o["class"] = "cl" + n;
			i.o["value"] = v;
			t.o.appendChild(i.o);
			return i;
		}
	});
})();

// --------------------------------------------
// focus on the scrollable control
// --------------------------------------------
igk.ready(function () {
	var q = igk.qselect(".igk-parentscroll").first();
	if (q) {
		q.o.focus();
	}
});

// --------------------------------------------
// slider button management
// --------------------------------------------
(function () {
	var contain_slider = false;
	var m_s_btn = [];
	var ct_sliderbtn = "winui/sliderbutton";
	var m_datas = {};
	function _slide_to() {
		var p = igk.JSON.parse($igk(this).getAttribute("igk-slider-data"));
		if (p) {
			p.scrollparent = $igk($igk(p.target).first().getscrollParent());
			p.cibling = $igk(this);
			ns_igk.winui.fn.navigateTo(p.target, p.property).apply(this, null);
			m_datas[p.scrollparent.getCssSelector()] = p;
			igk.web.storage.set("igk/slider", p.scrollparent.getCssSelector());
		}
	}
	function _slider_click(evt) {


		evt.preventDefault();
		_slide_to.apply(this);
	}
	function _view_size() {
		for (var i in m_datas) {
			var p = m_datas[i];
			ns_igk.winui.fn.navigateTo(p.target, p.property).apply(p.cibling, null);
		}
	}
	function _init_slider_button(q) {
		if (q.data.contains(ct_sliderbtn))
			return;

		if (q.istouchable()) {
			q.reg_event("touchend", _slider_click);
		}
		else
			q.reg_event("click", _slider_click);

		m_s_btn.push(q);
		q.data.add(ct_sliderbtn, 1);
		if (!contain_slider) {
			// register document size change
			igk.winui.reg_event(window, "resize", _view_size);
			// load slider from cookies
			var sl = igk.web.storage.get("igk/slider");

			contain_slider = !0;
		}
	}
	igk.ctrl.registerReady(function () {
		if (igk.system.regex.item_match_class("igk-slider-btn", this)) {
			_init_slider_button($igk(this));
		}
	});
	igk.ready(function () {
		igk.qselect(".igk-slider-btn").each_all(function () {
			_init_slider_button(this);
		});
	});

	igk.system.createNS("igk.winui.sliderbtn", {
		fn: {
			ajxslideToReady: function (q, p) {
				// if(!q)return null;
				return function (xhr) {
					if (this.isReady()) {
						$igk(q).setAttribute("igk-slider-data", p);
						_init_slider_button($igk(q));
						_slide_to.apply($igk(q));
					}
				}
			}
		}
	});

})();







// ---------------------------------------------------
// form validation data
// ---------------------------------------------------
(function () {
	igk.ctrl.bindAttribManager("igk-form-validate", function (n, v) {
		if (!v) {
			return;
		}
		var q = this;
		// ------------------------------------------------------------
		// TODO: CHECK VALIDITY
		// ------------------------------------------------------------
		// alert(this.o.noValidate);

		// var q= this;
		// this.reg_event("invalid",function(evt){

		// });
		this.reg_event("submit", function (evt) { 
			if ((typeof (q.o.checkValidity) != igk.constants.undef) && !q.o.checkValidity()) {
				console.debug("data not valid");
				evt.preventDefault();
				return;
			}
			var _o = false;
			$igk(this).select("input").each(function(){ 
				if (typeof (this.igkCheckIsInvalid) != igk.constants.undef) {
					_o |= this.igkCheckIsInvalid();
				}
				return !0;
			});
			if (_o)
				evt.preventDefault();
		});
	});
})();


// ---------------------------------------------------
// android management system
// ---------------------------------------------------
(function () {

	// init android Namespace
	igk.system.createNS("igk.android", {
	});


	function __initAndroid() {
		if (!igk.navigator.isAndroid() && !igk.dom.body().supportClass("igk-android"))
			return;
		var m_actx = igk.createNode("android-ctx");
		var m_ectx = igk.winui.RegEventContext(m_actx, m_actx);

		if (m_ectx) {
			m_ectx.reg_window("resize", function () {
				__setup_screen();
			});

			if (typeof (window.onorientationchange) != "undefined") {
				m_ectx.reg_window("orientationchange", function (evt) {
					if (window.orientation == 90) {
						$igk('.igk-android').addClass("lnd-scape");
					}
					else
						$igk('.igk-android').rmClass("lnd-scape");
				});
			}
		}
		var mt = igk.css.getMediaType();
		__setup_screen();
		igk.dom.body().addClass(mt);
		var mt = igk.css.getMediaType();
		igk.publisher.register(igk.publisher.events.mediachanged, function (e) {


			igk.dom.body().rmClass(mt).addClass(e.mediaType);
			mt = e.mediaType;
		});
		return m_actx;

	}

	function __setup_screen() {
		if (typeof (window.onorientationchange) == "undefined") {
			// 
			if (window.innerWidth > window.innerHeight) {

				$igk('.igk-android').addClass("lnd-scape");
			}
			else {
				$igk('.igk-android').rmClass("lnd-scape");
			}
		}
	}



	igk.ready(function () {
		__initAndroid();
	});

})();



// ----------------------------------------------------------
// code view surface
// ----------------------------------------------------------
(function () {
	var m_types = ['css', 'php', 'csharp', 'html', 'xml'];
	var m_codes = []; // store code object that currently been transformed
	var m_reg = (function (t) {
		var r = '';
		for (var i = 0; i < t.length; i++) {
			if (i > 0)
				r += '|';
			r += t[i];
		}
		return new RegExp('((' + r + ')code)', 'i');
	})(m_types);
	function __init_code_area() {

		var q = this;
		if (!q) {
			return;
		}
		if (q.hightlight)
			return;
		q.hightlight = 1;
		m_codes.push(q);



		// return;
		// q.addClass("dispib");
		var c = '';
		var b = null;
		if ((b = m_reg.exec(q.o.className))) {
			c = b[2];
		} else {
			c = q.getAttribute("lang");
			if (!(c in m_types)) {
				c = 'php';
			}
		}
		q.addClass("code-php");
		var s = q.o.textContent.trim();// .getHtml().trim();
		var t = s.split('\n');

		// return;
		// clear node
		q.setHtml("");

		q.getSource = function () {
			return s;
		};

		var m = null;
		if (c) {
			m = "igk.highlightjs." + c;
			m = eval("new " + m + "();");
		} else {
			m = new igk_e();
		}
		var l = "";
		for (var i = 0; i < t.length; i++) {
			l = t[i];

			var d = q.add("div");
			d.setHtml(m.evals(l));
			// break;
		}
		var o = q.add('span').addClass("dispib").setHtml('_');
		var w = igk.getNumber(o.getComputedStyle('width'));
		o.remove();
		// delete(o);



		w = (((m.getLines() + '').length * w) + 10) & 0xFFFA;


		igk.css.appendRule(q.getCssSelector() + " > div > span.ln {text-align:right; width:" + w + "px;}");

	};
	function igk_e() {// evaluator
		var l = 0;
		igk.appendProperties(this, {
			evals: function (m) {
				l++;
				return m;
			},
			getLines: function () { return l; }
		});
	};
	var inf = {
		ln: 0,
		mode: 0,
		pos: 0
	};
	function _readWord(s) {
		var w = "";
		var c = 0;
		var ch = "";

		while (inf.pos < inf.ln) {
			ch = inf.s[inf.pos];
			// TODO TRADITIONAL WAY

			c = ch.toLowerCase().charCodeAt(0);
			if (((c >= 48) && (c <= 57)) || ((c >= 97) && (c <= 122)) || (ch == '_')) {
				w += ch;
			} else {
				break;
			}

			inf.pos++;
		}

		// delete(c);
		if (w.length == 0)
			inf.read = 0;
		return w;
	}
	function _readStringLitteral(ch) {
		var w = ch;
		var p = ch;
		while (inf.pos < inf.ln) {
			ch = inf.s[inf.pos];

			if (ch != p) {
				w += ch;
			} else {
				if (inf.s[inf.pos - 1] == "\\") {
					// escaped
					w += ch;
				} else
					break;
			}
			inf.pos++;
		}
		return w + p;
	}
	function igk_php_eval() {// php evaluation code

		igk_e.apply(this);
		var reserved = /((true|false)|(a(bstract|nd|rray|s))|(c(a(llable|se|tch)|l(ass|one)|on(st|tinue)))|(d(e(clare|fault)|ie|o))|(e(cho|lse(if)?|mpty|nd(declare|for(each)?|if|switch|while)|val|x(it|tends)))|(f(inal|or(each)?|unction))|(g(lobal|oto))|(i(f|mplements|n(clude(_once)?|st(anceof|eadof)|terface)|sset))|(n(amespace|ew))|(p(r(i(nt|vate)|otected)|ublic))|(re(quire(_once)?|turn))|(s(tatic|witch))|(t(hrow|r(ait|y)))|(u(nset|se))|(__halt_compiler|break|list|(x)?or|var|while))$/;
		var w = 0;
		var l = 1;// line count
		var mode = 0;
		this.evals = function (s) {
			// read line
			s = s.trim().replace('<!--?php', '<span class="proc">&lt;?php</span>').replace('?-->', '<span class="proc">?&gt;</span>');
			var o = '';
			var m = 0;
			var tr = '';// tempory read		
			o += '<span class=\'ln\'>' + l + '</span>';// line number
			if (/(&lt;\?php|\?&gt;)/.test(s.trim())) {
				o += "<span class='proc'>" + s + "</span>";
			}
			else {
				if (s.length == 0)
					o += "<br />";
				else {

					inf.ln = s.length;
					inf.pos = 0;
					inf.read = 1;
					inf.s = s;
					var sp = igk.createNode("span");
					var ch = "";
					while (inf.read && (inf.ln > inf.pos)) {
						ch = s[inf.pos];
						switch (ch) {
							case ' ':
								sp.add("span").setHtml("&nbsp;");
								break;
							case '\t':
								sp.add("span").addClass("t").setHtml("&nbsp;");
								break;

							case "\"":
							case "'":
								if (inf.mode == 0) {
									inf.mode = 1;// string
									inf.pos++;
									w = _readStringLitteral(ch);
									sp.add("span").addClass("s").setHtml(w);
									inf.mode = 0;
								}
								break;
							case '/':// for comment
								if ((inf.pos + 1 < inf.ln) && (inf.s[inf.pos + 1] == "/")) {
									sp.add("span").addClass("cm").setHtml(ch + inf.s.substr(inf.pos + 1));
									inf.read = 0;
								} else
									sp.add("span").addClass("pc").setHtml("/");
								break;
							case "@":
								if ((inf.pos + 1 < inf.ln) && ("\"'".indexOf(inf.s[inf.pos + 1]) != -1)) {
									inf.pos++;
									w = _readStringLitteral(inf.s[inf.pos++]);
									sp.add("span").addClass("s").setHtml("@" + w);
									inf.mode = 0;
								}
								break;
							case '$':// read var
								inf.pos++;
								w = _readWord();
								sp.add("span").addClass("v").setHtml("$" + w);
								inf.pos--;
								// inf.read = 0;

								break;
							case "&":
								if (inf.mode == 0) {
									inf.start = inf.pos;
									inf.pos++;
									while ((inf.pos < inf.ln) && (s[inf.pos] != ';')) {
										inf.pos++;
									}
									if (s[inf.pos] == ';') {
										m = sp.add("span").addClass("pc").setHtml(s.substr(inf.start, inf.pos - inf.start));

									}
									else
										inf.read = 0;
									delete (inf.start);
								}
								break;
								break;
							default:
								if (inf.mode == 0) {
									if (",.?|()#[]-+{}\\/%*><;:=".indexOf(ch) != -1) { // igk.char.isPonctuation(ch)){

										m = sp.add("span").addClass("pc").setHtml(ch);

									} else {
										w = _readWord();
										m = sp.add("span").addClass("w").setHtml(w);
										if (reserved.test(w)) {
											m.addClass("r");
										}
										inf.pos--;
									}
									// inf.read = 0;
								}
								// sp.add("span").addClass("w").setHtml(w);
								break;
						}
						inf.pos++;
					}

					o += sp.getHtml();
				}
			}
			l++;
			return o;
		};
		this.getLines = function(){
			return l;
		};
	}

	function igk_xml_eval() {
		throw new Error("Not implement");
	};

	igk.system.createNS("igk.highlightjs", {
		'php': igk_php_eval,
		'xml': igk_xml_eval
	});

	function __initCode() {
		$igk("code.igk-code").each_all(__init_code_area);
	};
	igk.ready(__initCode);


	// igk.ctrl.registerReady(function () {
	// 	if (this.tagName && this.tagName.toLowerCase() == "code" && this.getAttribute('igk-code')) {
	// 		__init_code_area.apply($igk(this));
	// 	}		
	// });

	igk.system.createNS("igk.winui.codes", {
		getCodes: function () {
			return m_codes || [];
		}
	});

})();

// ------------------------------
// register media type 
// ------------------------------

(function () {
	var corecss = "balafon.css";
	var r = igk.createNode('div');
	var dev = igk.createNode('div');
	var dum = null;
	var rule = null;
	var m_chtheme = null;// will store the changed theme for dynamic theme changing purpose
	dev.addClass("igk-device");
	r.addClass('igk-media-type');

	// for chrome navigator require to register

	// if(igk.navigator.isChrome()){

	// igk.ready(function(){
	// // r.setCss({position:'absolute',zIndex:40});

	// // register media to bottom
	// igk.dom.body().add("div").setCss({position:'absolute',visibility:'hidden',overflow:'hidden','height':'0px', 'bottom':'0px'})
	// .addClass("igk-m-i")// media info
	// .add(r).
	// t.add(dev);		
	// igk.css.appendRule(".igk-media-type:before{position:absolute;}");
	// igk.css.appendRule(".igk-device:before{position:absolute;}");
	// igk.publisher.publish("sys://css/info",{});	


	// });
	// }

	function __getRule(f) {
		var m = null;
		var q = new RegExp("/" + f + "(.+)*");
		for (var i = 0; i < document.styleSheets.length; i++) {
			m = document.styleSheets[i];
			if (m.href && q.test(m.href + ""))
				return m;
		}
		return null;
	}


	function _initTransitionProperties(t, list) {


		t = $igk(t);
		if (typeof (list) == 'string') {
			if (!t.autoTransition)
				t.autoTransition = {};
			if (t.autoTransition[list]) {
				return true;
			}
			if (list == 'all') {
				list = ['width', 'height', 'marginLeft', 'marginRight', 'margin', 'padding', 'paddingLeft', 'paddingRight'];
			}
		}

		var g = t.getComputedStyle("transition");
		if (!g)
			return false;

		var mark = 0;
		function _initPropertyStyle(t, n) {
			if (!t.autoTransition)
				t.autoTransition = {};
			if (t.autoTransition[n])
				return;

			t.autoTransition[n] = 1;
			return new (function (n) {
				var _os = t.o.style[n]; // old style

				igk.defineProperty(t.o.style, n, {
					get: function () {

						if (mark)
							return _os;
						return t.getComputedStyle(n);
					},
					set: function (v) {

						if (v == 'auto') {
							t.o.style.setProperty(n, 'auto');
							var r = t.getComputedStyle(n);

							t.o.style.setProperty(n, _os);
							setTimeout(function () {
								t.o.style.setProperty(n, r);
							}, 1);
							return;
						}
						_os = v;
						t.o.style.setProperty(n, v);
					}
				});
			})(n);
		};

		var n = '';
		var d = '';
		for (var i = 0; i < list.length; i++) {
			n = list[i];
			mark = 1;
			var v = t.getComputedStyle(n);
			var s = t.o.style[n];

			mark = 0;
			if (s == '') {
				// init width default property
				t.o.style.setProperty(n, v);
			}




			_initPropertyStyle(t, n);


		}

		// restore transition properties
		t.setCss({
			transition: g
		});




	};



	igk.system.createNS("igk.css", {
		toggleClass: function(i,p){
			var q = $igk(i).first();
			if (q){
				q.toggleClass(p);
			}
		},
		initAutoTransitionProperties: _initTransitionProperties,
		loadLinks: function (t) {			
			for (var i = 0; i < t.length; i++) {
				var c = document.createElement("link");
				c.setAttribute("href", t[i]);
				c.setAttribute("rel", "stylesheet");				
				igk.dom.body().add(c);
			}
		},
		getEmSize: function (c, t) {
			// return the em font size of this target element
			var s = igk.getNumber($igk(c).getComputedStyle("font-size"));
			var T = s;
			t = t || igk.dom.body().o;
			if (t == c)
				return 1;
			while (c) {
				c = c.o.parentNode;
				if (c && (c != t)) {
					c = $igk(c);
					T = igk.getNumber($igk(c).getComputedStyle("font-size"));
					if (T != s)
						break;
				}
				else {
					c = null;
				}
			}
			s = Math.round(s / T, 3);

			return s;
		},
		appendRule: function (c) { // append rule to balafon.css.php or css definition 
			rule = rule || __getRule(corecss);

			try {
				if (rule)
					rule.insertRule(c, rule.cssRules.length);
			}
			catch (e) {

			}
		},
		appendStyle: function (uri) {
			// plugin style to document
			if (!uri) {
				return;
			}
			var e = document.createElement("link");
			$igk(e).setAttribute("href", uri)
				.setAttribute("type", "text/css")
				.setAttribute("rel", "stylesheet");
			document.head.appendChild(e);
			return document.styleSheets[document.styleSheets.length - 1];
		},
		getMediaType: function () {
			return (r.getComputedStyle('content', ':before') + "").replace(/\"/g, "");
		},
		getMediaIndex: function () {
			return (r.getComputedStyle('z-index', ':before') + "").replace(/\"/g, "");
		},
		getDevice: function () {
			return (dev.getComputedStyle('content', ':before') + "").replace(/\"/g, "");
		},
		changeTheme: function (uri, value) {
			igk.ajx.get(uri + "/" + value, null, function (xhr) {
				if (this.isReady()) {
					rule = rule || __getRule(corecss);

					if (rule) {
						// rule.remove();
						while (rule.cssRules.length > 0) {
							rule.deleteRule(0);
						}
						if (m_chtheme != null) {
							$igk(m_chtheme).remove();
						}
						var s = document.createElement("style");
						s["type"] = "text/css";
						s.innerText = xhr.responseText;
						document.head.appendChild(s);
						m_chtheme = s;


						// igk.css.appendRule(xhr.responseText, 0);
					}
				}
			});
		},
		selectStyle: function (rx, callback) {
			var d = document.styleSheets;
			if (d.length <= 0)
				return 0;
			var match = [];
			var k = [];
			for (var i in rx) {
				for (var j = 0; j < d.length; j++) {
					if (!match[j] && rx[i].test(d[j][i])) {
						callback(d[j]);
						match[j] = 1;
						k.push(d[j]);
					}
				}
			}
			return k;
		},
		getComputedClassStyle: function (c, p, j) {
			// @c:class name
			// @p:property
			// @j:speudo type
			if (dum == null) {
				dum = igk.createNode('div');
				if (igk.navigator.isChrome()) {
					igk.ready(function () {
						igk.dom.body().add(dum);// .setHtml("infof").add(r).t.add(dev);		
					});
				} else
					$igk(h).add(dum);
				dum.addClass(c);
			}

			var g = dum.getComputedStyle(p, j);
			dum.remove();
			dum = null;
			return g;
		}
		, getComputedSrcStyle: function (h, c, p, j) {
			// h:host
			// 
			h = h || igk.dom.body().o;
			if (dum == null) {
				dum = igk.createNode('div');
				if (igk.navigator.isChrome()) {
					igk.ready(function () {
						$igk(h).add(dum);// .setHtml("infof").add(r).t.add(dev);		
					});
				} else
					$igk(h).add(dum);

				dum.addClass("dispn").addClass(c);
			}
			var g = dum.getComputedStyle(p, j);
			dum.remove();
			dum = null;
			return g;
		}

		, appendTempStyle: function (c) {
			var q = $igk("select#tempsp").first();
			if (!q) {
				q = igk.dom.body().add("style");
				q.o["type"] = "text/css";
			}
			q.setHtml(c);
		}
		, clearTempStyle: function () {
			var q = $igk("select#tempsp").first();
			if (q) {
				q.setHtml('');
			}
		}

		, isMatch: function (t, p) {
			// check if  t match pattern
			// @t:dom node
			// p:pattern
			if ((/^#[\w\-_]+$/.exec(p))) {// search parent by id
				// exemple: ^#info
				b = $igk(t).getParentById(p.substring(1));
				if (b)
					v_sl.push(b);
			}
			else if ((/^\./.exec(p))) {// search parent by class by class
				p = p.substring(1);
				var s = $igk(t).o;
				var rx = new RegExp("(" + p + ")(\\s|$)", "i");
				if (rx.exec("" + s.className)) {
					return 1;
				}
			} else {
				if (item.tagName.toLowerCase() == pattern)
					return 1;
			}

			return 0;
		}

	});


	igk.defineProperty(igk.css, "rule", { get: function () { return rule; } });


	// iniitilial ie events
	igk.ready(function () {
		var B = igk.dom.body();
		if (igk.navigator.isFirefox() || igk.navigator.isChrome() || igk.navigator.isSafari()) {
			// to get content of css style item must be added to document
			B.add("div").setCss({ position: 'absolute', visibility: 'hidden', overflow: 'hidden', 'height': '0px', 'bottom': '0px' })
				.addClass("igk-m-i")// media info
				.add(r).
				t.add(dev);
			igk.css.appendRule(".igk-media-type:before{position:absolute;}");
			igk.css.appendRule(".igk-device:before{position:absolute;}");
		}
		var dev = igk.css.getDevice();
		var m_c = igk.css.getMediaType();// current
		function __checkMedia() {

			var i = igk.css.getMediaType();

			var d = null;
			if (i != m_c) {
				B.rmClass(m_c).addClass(i);
				__raiseMedia(i);
			}
		};

		function __raiseMedia(i) {
			m_c = i;
			igk.publisher.publish(igk.publisher.events.mediachanged
				, {
					mediaType: i,
					mediaIndex: igk.css.getMediaIndex(),
					device: igk.css.getDevice()
				});
		};
		B.addClass(m_c);
		__raiseMedia(m_c);
		igk.winui.reg_event(window, 'resize', __checkMedia);

	});

	var e = {};
	e.mediachanged = 1;
	for (var s in e) {
		e[s] = 'sys://events/' + s;
	}
	igk.system.createNS("igk.publisher.events", e);

})();



(function () {
	function igk_str_padEnd(l, v) {
		var hl = this.length;
		var s = this.toString();
		while (hl < l) {
			s += '' + v;
			hl++;
		}
		return s;
	}
	function igk_str_startWith(c) {
		if (typeof (c) == 'undefined')
			return !1;
		var l = this.length;
		var x = c.length;
		var i = 0;

		if (l == 0)
			return !1;

		if (typeof (this[0]) == 'undefined') {
			// ie7 do not suport bracket operator
			while ((i < x) && (i < l) && (this.charAt(i) == c.charAt(i))) {
				i++;
			}
		}
		else {
			while ((i < x) && (i < l) && (this[i] == c[i])) {
				i++;
			}
		}
		return i == x;
	};
	if (!String.prototype.startsWith)
		String.prototype.startsWith = igk_str_startWith;

})();

//
// canvas utility 
//
(function () {

	//filter list
	var prop = ['sepia', 'blur', 'contrast', 'brightness', 'drop-shadow', 'grayscale', 'hue-rotate', 'invert', 'saturate', 'opacity'];

	function igk_get_filter_exp(f) {
		if (f == null) return "none";
		var s = "";
		var m = "";
		for (var i in f) {
			m = i;
			switch (m) {
				case "huerotate":
					m = "hue-rotate";
					break;
				case "dropshadow":
					m = "drop-shadow";
					break;
			}
			if (s.length > 0)
				s += " ";
			s += m + "(" + f[i] + ")";
		}
		return s;
	};



	function igk_getFilterProp(o) {
		var go = igk.JSON.parse(o.getAttribute("igk:filter"));
		if (go) {
			var r = "";
			for (var i = 0; i < prop.length; i++) {

				if (igk_isdefine(go[prop[i]])) {
					if (r.length > 0)
						r += " ";
					r += prop[i] + "(" + go[prop[i]] + ")";
				}
			}
			// o.setCss({filter:'progid:DXImageTransform.Microsoft.Blur(PixelRadius="5")'});
			if (igk.navigator.isIEEdge()) {
				o.setCss({ filter: r });

				//return null;
			}
			o.setCss({ filter: r });
			return r;
		}
		return null;
	};

	var _g_canva = igk.createNode("canvas");
	var _g_ctx = _g_canva.o.getContext ? _g_canva.o.getContext("2d") : null;

	if (_g_ctx == null)
		return;


	igk.system.createNS("igk.canvas", {
		supportFilter: function () {
			return "filter" in _g_ctx;
		},
		getFilterExpression: function (c) {
			var f = null;

			// transform code string to canvas filter expression
			if (c != null) {
				if (!String.prototype.padEnd)
					String.prototype.padEnd = igk_str_padEnd;
				var s = (c + "").padEnd(20, 0);
				var u = [];
				for (var i = 0; i < 10; i++) {
					u[i] = "0x" + s.substring(i * 2, (i * 2) + 2);
				}
				var sfilter = {};

				sfilter.grayscale = Math.round((eval(u[0]) / 100 / 255.0) * 10000) + "%";//"0
				sfilter.huerotate = Math.round((eval(u[1]) / 100 / 255.0) * 36000) + "deg";// ("
				sfilter.blur = eval(u[2]) + "px";// ("0x
				sfilter.sepia = Math.round((eval(u[3]) / 100 / 255.0) * 10000) + "%";
				sfilter.saturate = (100 - Math.round((eval(u[4]) / 100 / 255.0) * 10000)) + "%";
				sfilter.invert = Math.round((eval(u[5]) / 100 / 255.0) * 10000) + "%";
				sfilter.opacity = (100 - Math.round((eval(u[6]) / 100 / 255.0) * 10000)) + "%";
				sfilter.brightness = (100 - Math.round((eval(u[7]) / 100 / 255.0) * 20000)) + "%";// scale from 0 - 200  / default is 100
				sfilter.contrast = (100 - Math.round((eval(u[8]) / 100 / 255.0) * 10000)) + "%";

				f = sfilter;
			}
			return igk_get_filter_exp(f);
		},
		getFilterString: igk_get_filter_exp,
		toStringExpression: function (v) {

			var r = "";
			for (var i = 0; i < prop.length; i++) {
				var n = prop[i];
				if (n in v) {
					r += igk.system.convert.ToBase(Math.round((v[prop[i]] / 100) * 255), 16);
				}
			}
			return r;
		}
	});

	var hprop = {};
	for (var i = 0; i < prop.length; i++) {
		hprop[prop[i]] = i;
	}

	var v_list = igk.defineEnum(null, hprop);
	igk.defineProperty(igk.canvas, "filters", {
		get: function () {
			return v_list;
		}
	});

})();




// horizontal menu manager
(function () {
	// select all ul that have a igk-hmenu class 
	// select all 
	var items = [];
	function __hideSubmenu(q) {
		q.rmClass("igk-show");
	}
	function __showSubmenu(q) {
		q.addClass("igk-show");
	}
	function __initMenu(q) {
		if (!q || q.data["system/menu"])
			return;

		$igk(q.o.parentNode).reg_event("mouseover", function () {
			__showSubmenu(q);
		}).reg_event("mouseleave", function () { __hideSubmenu(q); });
		q.data["system/menu"] = 1;
	}

	function __init() {
		$igk("ul.igk-hmenu").select('li ul').each(function () {
			items.push(this);
			__initMenu(this);
			return !0;
		});
	}
	// ready menu function
	igk.ready(function () {
		__init();
		igk.ajx.fn.registerNodeReady(function () {
			__init();
		});
	});






})();


(function () {
	igk.system.createNS("igk.ctrl", {
		initMemoryUsage: function (uri) {

			var p = $igk(igk.getParentScript());
			var tout = null;
			function __getMemory() {
				if (p.o.parentNode == null) {
					return;
				}
				if (tout) {
					clearTimeout(tout);
					tout = null;
				}
				igk.ajx.get(uri, null, function (xhr) {
					if (this.isReady()) {
						var pt = igk.createNode("dummy");
						pt.setHtml(xhr.responseText);
						var q = $igk(pt.o.children[0]);
						if (q) {
							p.setHtml(q.getHtml());
						}
						setTimeout(__getMemory, 2000);

					}
				});
			}
			igk.ready(__getMemory);
		}
	});
})();

(function () {


	// if (igk.navigator.isIE() && igk.navigator.IEVersion()<10){
	// 	// not allow the creationg of this application
	// 	// ie 10 auto load and lock the file because unload
	// 	return;
	// }
	// file picker
	var s = 0;
	var _pic = 0;
	var re = 0; // recreate field

	function __getfile() {
		var s = ns_igk.createNode("input");
		s.setAttribute("type", "file");
		s.o.id = "clFile";
		s.o.name = "clFile";
		// for firefox
		// s.addClass("dispn");
		// for safari
		s.addClass("posab");
		s.setCss({ visibility: 'hidden' });
		return s;
	};

	igk.system.createNS("igk.system.io", {
		pickfile: function (uri, p, osrc) {
			s = s || __getfile();
			p = p || {};
			// reset the value
			s.o.value = null;
			if (p.accept) {
				s.o.setAttribute("accept", p.accept);
			}
			// pick file. 
			// >uri: uri to send the picked file
			// >p: property to manage picking file
			// >osrc: source of the requesting

			igk.dom.body().prepend(s);
			function __change() {
				if (uri == null) {
					var complete = (p ? p.complete : null);
					if (complete) {
						complete(s.o.files[0]);
					}

				} else {
					igk.ajx.uploadFile(osrc, s.o.files[0], uri, true,
						p ? p.complete : null,
						p ? p.start : null,
						p ? p.progress : null,
						p ? p.done : null
					);

				}
				s.unreg_event('change', __change);
				s.remove();
				s.o.value = null;
				if (re)
					s = null;
			}
			// remove the reg change event
			s.unreg_event('change', __change);
			s.reg_event('change', __change);
			s.o.accept = null;
			if (p.accept) {
				s.o.accept = p.accept;
			}
			try {
				//try to set the files
				s.o.files = null;
			}
			catch (e) {
				//
				re = 1; // need to recreate.
			}
			s.o.click();
		}
	}
	);

	igk.system.io.pickfile.getSrc = function () { return _src; };


	igk.winui.initClassControl("igk-js-pickfile", function () {
		var q = this;
		var s = igk.JSON.parse(q.getAttribute("igk:data"));
		if (s && igk.is_object(s)) {

			this.reg_event("click", function () {
				igk.system.io.pickfile(
					s.uri,
					s.options,
					q);
			});
		}
	});
})();

// debugger manager
(function () {
	igk.winui.initClassControl("igk-debuggernode",
		function () {
			var q = this;
			this.reg_event("click", function () {
				q.setHtml("");
			});
		}, { desc: "debugger node" });
})();







// (function(){
// igk.ready(function(){
// $igk("*").each(
// function(){

// return !0;
// }
// );

// })

// })();


// igk-tooltip
(function () {
	igk.ctrl.registerAttribManager("igk-tooltip", { desc: "for tooltip component" });
	igk.ctrl.bindAttribManager("igk-tooltip", function (n, m) {
		var p = igk.JSON.parse(m);
		var q = this;
		var tip = null;
		this.reg_event("mouseover", function (evt) {

			if (tip == null)
				tip = igk.winui.tooltip.show(q, p.data);
			else
				tip.show();
		});
		// .reg_event("mouseout",function(evt){

		// if(tip)
		// tip.hide();
		// }).reg_event("mouseleave", function(){

		// if(tip)
		// tip.hide();
		// });
	});
	var _tip_offset = 200;
	var _tip_c = 1000;

	function _tip() {
		var t = igk.createNode("div");
		t.setOpacity(0.2)
			.addClass("igk-trans-all dispb posab pad-a-4")
			.setCss({
				zIndex: _tip_offset + _tip_c,
				padding: "4px",
				backgroundColor: "white",
				border: "1px solid black",
				cursor: "pointer"
			})
			.setHtml(" ");
		// igk.ctrl.selectionmanagement.disable_selection(t.o);

		var _m_closing = false;
		var _m_t = "";
		igk.appendProperties(this, {
			show: function () {
				if (_m_closing)
					return;
				_m_closing = 1;
				t.setOpacity(1.0)
					.setHtml(this.data);
				var b = this.owner.getScreenBounds();
				var loc = this.owner.getScreenLocation();



				t.setCss({ left: loc.x + "px", top: (loc.y + (b.h / 2)) + "px" });
				igk.dom.body().appendChild(t.o);
			},
			hide: function () {
				if (_m_closing == 1) {
					t.setOpacity(0.1);
				}
			}
		});
		var self = this;
		t.reg_event("mouseover", function () {
			self.show();
		}).reg_event("mouseout", function (evt) {
			self.hide();
		}).reg_event("transitionend", function (evt) {
			if (evt.propertyName == "opacity")
				if (_m_closing) {
					t.remove();
					_m_closing = 0;
				}
		});
	};

	igk.winui.tooltip = function () {
		// tooltip constructor
	};

	igk.system.createNS("igk.winui.tooltip", {
		show: function (q, data) {
			var d = new _tip();
			d.data = data;
			d.owner = q;
			d.show();
			return d;
		}
	});

})();


// (function(){

// if(!igk.math.rectangle.intersect){
// igk.system.createNS("igk.math.rectangle",{
// intersect: function(rc1,rc2){

// var H=rc1.h + rc2.h;
// var W=rc1.w + rc2.w;
// var minx=Math.min(rc1.x,rc2.x);
// var maxx=Math.max(rc1.x + rc1.w,rc2.x + rc2.w);
// var miny=Math.min(rc1.y,rc2.y);
// var maxy=Math.max(rc1.y + rc1.h,rc2.y + rc2.h);
// var w1=W -(maxx - minx);
// var h1=H -(maxy - miny);

// if(
// (w1 >=0)
// &&
// (h1 >=0)
// )
// {
// double k=Math.Min(rc1.Right,rc2.Right) - w1;
// double r=Math.Min(rc1.Bottom,rc2.Bottom) - h1;
// return !0; // new Rectangled(k,r,w1,h1);
// }
// return !1; // Rectangled.Empty;
// }
// });

// }
// alert("ok 3");

igk.ready(
	function () {

		function __load(j) {

			// var loc=j.getBoundingClientRect(); // j.o.getBoundingClientRect ? 
			// j.o.getBoundingClientRect(): {x:0,y:0};// \{x:j.getscrollLeft(),
			// y:j.getscrollTop()
			// \};// getLocation();
			// 

			// var size=igk.winui.screenSize();

			// get screen visibility
			// var vsb=((loc.x>=0) &&(loc.x<=size.width) &&(loc.y>=0) &&(loc.y<=size.height));









			// firefox : scrollHeight ok ... other not ok
			// iternet chrome

			// igk.show_prop(j.o);



			// var s=j.getSize();








			if (j.data["img-js.loaded"]) {

				return !1;
			}
			if (j.getisVisible()) {
				j.data["img-js.loaded"] = 1;

				// load image
				var i = document.createElement("img");
				$igk(i).reg_event("load", function (evt) {
					console.debug("complete ....");
					igk.dom.copyAttributes(j.o, i, { 'data': 1 });
					j.o.parentNode.replaceChild(i, j.o);

				});
				// set properties
				i["src"] = j.getAttribute("data");
				return !0;
			}
			return !1;
		};


		// var items=[];

		function __fcScroll(evt) {


			var tab = $igk(evt.target).data["img-js"].items;// items;// copy tab
			var _ctab = [];
			// view all item


			// __load.apply(this,[$igk(evt.target)]);
			// t
			for (var i = 0; i < tab.length; i++) {
				var j = tab[i];
				// if(j && !j.loaded){
				if (!__load(j)) {
					_ctab.push(j);
				}
				// delete(items[i]);
				// }
			}
			$igk(evt.target).data["img-js"].items = _ctab;
			if (_ctab.length == 0) {
				$igk(evt.target).unreg_event("scroll", __fcScroll);
			}
			console.debug("" + tab.length);
		}
		// igk.qselect(".igk-parentscroll").reg_event("scroll",__fcScroll);

		igk.dom.body().select("igk-img-js").each(function () {
			var p = this.o.offsetParent;
			if (p != null) {
				var q = $igk(p);
				// get the offset parent to register to visibility component
				if (!q.data["img-js.parentScroll"]) {
					q.reg_event("scroll", __fcScroll);
					q.data["img-js.parentScroll"] = 1;
					q.data["img-js"] = {
						items: []
					};

				}
				// store item affected
				// 	 items.push(this);
				q.data["img-js"].items.push(this);
			}
			return !0;
		});
	});

// })();


(function () {
	// igk-scroll-loader tag component
	// :Represent an element that will be load every time scroll change or visibility
	// :::testapi func:test_contentscroll
	function __fcScroll(q) {

		if (q.loaded || q.loading || !q.getisVisible())
			return !1;
		q.loading = !0;
		igk.ajx.get(q.getAttribute('data'), null, function (xhr) {
			if (this.isReady()) {
				q.loaded = !0;
				q.loading = false;
				q.unreg_event("scroll", __load);
				qh = this.replaceResponseNode(q.o);
				if (qh)
					qh.view();
			}
		});
		return !0;

	};
	function __load() {
		var d = $igk(this);
		var k = "igk-scroll-loader.items";
		var h = d.data[k];
		if (h.length == 0) {
			d.unreg_event("scroll", __load);
			d.data[k] = null;
			return;
		}
		console.debug("scrolling ..." + h.length);
		for (var i = 0; i < h.length; i++) {
			if (__fcScroll(h[i])) {
				h.splice(i, 1);
				i--;
			}
		}
	}
	function __init_doc_scrollloader() {

		igk.dom.body().select("igk-scroll-loader").each(function () {
			__init_tag(this);
			return !0;
		});
	}
	function __init_tag(t) {

		var p = null;
		if (t.data["igk-scroll-loader"])
			return;
		// this.o.offsetParent;

		p = t.getscrollParent().o;
		var sk = "igk-scroll-loader.parentScroll";


		if (p != null) {
			var q = $igk(p);
			if (!q.data[sk]) {

				q.reg_event("scroll", __load);
				q.data[sk] = 1;
				q.data["igk-scroll-loader.items"] = [];
			}
			var h = q.data["igk-scroll-loader.items"];
			h.push(t);
			t.data["igk-scroll-loader"] = 1;
			if (t.getisVisible()) {
				__fcScroll(t);
			}
		} else {
			console.debug("no scrolling ");
		}

	}

	// register a scroll loader component
	igk.reg_tag_component("igk-scroll-loader",
		{
			desc: "scroll loader",
			func: function () {
				__init_tag($igk(this));
			}
		});

	igk.ready(__init_doc_scrollloader);

	// igk.ajx.fn.registerNodeReady(function(){
	// var q=$igk(this);

	// });

})();



(function () {
	// binding select data
	// tagname : select
	// attribute expected : igk-bind-data-ajx

	igk.ctrl.registerAttribManager("igk-bind-data-ajx", { desc: "used to bind data for selection" });
	igk.ctrl.bindAttribManager("igk-bind-data-ajx", function (n, m) {
		var q = this;
		switch (this.o.tagName.toLowerCase()) {
			case 'select':
				igk.ajx.post(m, null, function (xhr) {
					if (this.isReady()) {
						q.setHtml(xhr.responseText);
						console.debug(xhr.responseText);
					}
				});
				break;
		}
	});

})();



// obj: circle waiter
(function () {

	function __init_waiter() {
		var q = this;
		var _running = true;
		var _dat = null;
		var _offset = 0;

		igk.appendProperties(this.data, {
			canva: null,// canva zone
			dir: 1,// direction
			penWidth: 2,// pen width
			render: function (v, cl, of_set) {

				var w = igk.getNumber(this.canva.getComputedStyle("width"));
				var h = igk.getNumber(this.canva.getComputedStyle("height"));
				var v1 = 0;
				var v2 = 0;

				var cx = w / 2;
				var cy = h / 2;
				var penw = this.penWidth || 4;
				var r = Math.min(w / 2, h / 2) - (penw / 2);

				this.canva.setAttribute("width", w);
				this.canva.setAttribute("height", h);

				var ctx = this.canva.o.getContext('2d');
				ctx.lineWidth = penw;
				ctx.clearRect(0, 0, w, h);
				// background
				ctx.strokeStyle = '' + cl;
				ctx.beginPath();
				var offset = (_offset * (Math.PI/180.0)) -(Math.PI / 2);
				var _s = _getData();
			 
				switch (_s.mode) {
					case 2:
						if (this.dir == 1) {
							v1 = offset;
							v2 = offset + (2 * Math.PI) * v;
							// ctx.arc(cx,cy,r,offset, offset+ (2*Math.PI)*v ,false);
						}
						else {
							v1 = offset + (2 * Math.PI) * (1 - v);
							v2 = offset + (2 * Math.PI);
							// ctx.arc(cx,cy,r,offset +(2*Math.PI)*(1-v) ,offset+ (2*Math.PI) ,false);
						}
						break;
					case 1:
						if (this.dir == 1){
							v1 =   offset + (of_set * (Math.PI * 2));
							v2 =   offset + (2 * Math.PI) * v;
						}
						else {
							v1 =   offset + (2 * Math.PI) * (1 - v);
							v2 =   offset + ((2 * Math.PI) * (1 - of_set));
						}
						break;
				}
				if (v1 != v2)
					ctx.arc(cx, cy, r, v1, v2, false);

				ctx.stroke();
			}
		});

		function _getData(){
			if (_dat == null) {
				var _s = q.data.storyboard.getComputedStyle('content', ':before');
				var _t = /^"((.)+)"$/i.exec(_s);
				_dat = igk.JSON.init_data({ stop: 'width', mode: 1 }, (_t ? _t[1].replace(/\\\"/g, "\"") : null), function (s) {
					s.stop = (_t ? _t[1] : null) || 'width';
				});
				q.data.penWidth = q.data.storyboard.getComputedStyle('border-size', ':before');
			}
			return _dat;
		};
		this.data.canva = this.add("canvas").addClass("posab fitw fith loc_t loc_l");

		this.data.storyboard = this.add("div").addClass("igk-anim-time-board")
			.reg_event("transitionend", function (evt) {
				var _m = _getData();
		 

				if (evt.propertyName == _m.stop) {
					// base of definition
					if (q.data.dir == 1) {
						q.data.storyboard.setCss({ 'width': '0px', height: '0px' }).rpClass("igk-cl-1", "igk-cl-2");
						q.data.dir = -1;
					}
					else {
						q.data.storyboard.setCss({ 'width': '100px', height: '100px' }).rpClass("igk-cl-2", "igk-cl-1");
						// .rmClass("igk-cl-2").addClass("igk-cl-1");
						q.data.dir = 1;
					}
					//for infinit loop
					//_running = false;
					setTimeout(function () {
						_running = !0;
					}, 2000);
				}

			})
			// .addClass("dispn")	
			.setCss(
				{
					"width": "0px",
					"height": "0px"
				})
			.addClass("igk-cl-2")
			.setHtml(" ");
		// for animation
		setTimeout(function () {
			var n = q.data.storyboard;
			n.setCss({ width: '100px', height: '100px' })
				.rpClass("igk-cl-2", "igk-cl-1");

			q.data.render(0, 'transparent', 0);

			igk.html.canva.animate(function (e) { 
				if (q.o.parentNode==null){
					// stop animation
					return !1;
				}
				if (!q.data.end) {
					var x = igk.getNumber(n.getComputedStyle("width"));
					var y = igk.getNumber(n.getComputedStyle("height"));
					var cl = n.getComputedStyle('color');
					_offset += 5;//= (new Date()).getMilliseconds() *180/(1*1000);
					if (_offset>360){
						_offset = 1;
					}
					// console.debug(_offset);
					q.data.render(
						Math.round((x / 100.0) * 100) / 100,
						cl,
						Math.round((y / 100.0) * 100) / 100
					);
					return _running;
				}
				q.data.render(1.0, "", 1.0);
				return _running;
			});
		}, 500);

		// this.reg_event("click",function(){
		// igk.ajx.post("http://localhost/igkdev/Configs/?express=1",'data=5');
		// });
		return !0;
	}

	// igk.winui.initClassControl
	igk.winui.initClassControl("igk-circle-waiter", __init_waiter);
})();
// -----------------------------------------------------------------
// vertical - horizontal scrollbar
// -----------------------------------------------------------------
// obj: vscroll bar
(function () {

	function _a(p, t) {
		// p: cibling
		// t: target active item in cibling 
		// var m = p.select('a.igk-active').first();

		var m = p.select(t).first();
		var s = p.getscrollParent();
		var y = 0;
		if (m && s) {
			var cH = s.o.clientHeight;// .o.parentNode.parentNode.clientHeight;// p.o.clientHeight;
			var sT = m.o.parentNode.offsetTop;




			if (sT > 0) {
				if (!(sT < cH)) {
					y = sT - cH + Math.ceil(igk.getNumber(m.getComputedStyle('height')));

					p.setCss({ 'transform': 'translateY(-' + (y) + 'px)' });
				}
			}

			// if (cH < sT){
			// var y =m.o.parentNode.offsetTop ;
			// // p.o.clientHeight - (m.o.parentNode.offsetTop - 
			// // igk.getNumber(m.getComputedStyle('font-size')));
			// if (y>0){
			// // m.setCss({fontSize:"2em"});


			// p.setCss({'transform':'translateY(-'+(y)+'px)'});
			// igk.publisher.publish("sys://doc/changed", {target:p});
			// }
			// }
		}
		return y;
	};

	function __init_scroll(orn) {
		// >param: orn : orientation
		// TODO: scroll in div on touching not yet implement
		var q = this;
		if (q.data["igk-scroll-b"]) {
			throw ("already igk-scroll-b init");
			return;
		}
		// cursor div
		var cur = q.add("div").addClass("igk-scroll-cur");
		var p = $igk(q.o.parentNode);
		var cibling = p.select(this.getAttribute("igk:cibling")).first();
		var m_spos = 0;// start position
		var m_psize = 0;
		if (cibling) {
			m_spos = _a(cibling, q.getAttribute("igk:target"));
		}
		var m_enable = false;
		var m_init = 0;
		q.data["igk-scroll-b"] = 1;
		p.addClass("igk-scroll-host");



		function init_view() {


			var real_size = p.getOffsetBounds();
			// // {x:0, y:0, w:0, h:0};
			// // for(var n = 0; n < p.o.childNodes.length; n++){
			// // var c = $igk( p.o.childNodes[n]);
			// // var loc = c.getScreenLocation();
			// // var boc = c.getScreenBounds();
			// // real_size.x = Math.min(loc.x, real_size.x);
			// // real_size.y = Math.min(loc.y, real_size.y);
			// // real_size.w = Math.max(loc.x + boc.w, real_size.w);
			// // real_size.h = Math.max(loc.y + boc.h, real_size.h);
			// real_size.x = Math.min(loc.x, real_size.x);

			// // }
			// // loc = p.getScreenLocation();
			// // real_size.w -= loc.x;
			// // real_size.h -= loc.y;
			m_psize = real_size;




			// igk.winui.transform.getData(p);

			// 1=> p.o.scrollHeight
			// x=> p.o.offsetHeight;
			var x = 0;
			var s = 0;
			var t = 0;
			// setup size - scroll size
			if (orn == 'h') {
				x = p.o.offsetWidth / p.o.scrollWidth;
				s = Math.max(32, (x * p.o.clientWidth));
				cur.setCss({ width: s + "px" });
			}
			else {
				// x = p.o.offsetHeight / p.o.scrollHeight;
				//x = p.o.offsetHeight / p.o.clientHeight;
				x = p.o.clientHeight / real_size.h;
				if (x < 1) {// offset proportion
					// s = Math.floor(Math.max(32, (x * p.o.clientHeight)));
					// s = Math.floor(Math.max(32, (x * p.o.offsetHeight)));
					s = Math.floor(Math.max(32, (x * p.o.clientHeight))); // p.o.offsetHeight)));
					// var bh = cur.getHeight();
					cur.setCss({ "Height": s + "px" });

					// " Height: "+p.getHeight());
					// p.o.scrollTo(0,200);
				}
			}
			if (x < 1) {
				if (!m_init) {
					// setup marking position
					// must show the 




					if (orn == 'v') {
						// t = Math.floor(p.o.offsetHeight * (m_spos / p.o.scrollHeight));
						t = Math.floor(p.o.offsetHeight * (m_spos / p.o.clientHeight));
						cur.setCss({ top: t + 'px' });
					} else {
						t = Math.floor(p.o.offsetWidth * (m_spos / p.o.scrollWidth));
						cur.setCss({ left: t + 'px' });
					}

					// __update();
					m_init = 1;
				}
				q.addClass("igk-show");
				m_enable = 1;
			} else {
				q.rmClass("igk-show");
				m_enable = 0;
				var e = __init_data();
				e.start = 0;
				e.end = 0;
				e.value = 0;
				// var dir=evt.deltaY > 0? -1: 1;
				// step :: heigh scroll by 10

				_u_cur(cur, e, 0);
			}

		};
		function __stop_capture() {
			igk.winui.mouseCapture.releaseCapture();
			cur.data["s:/msdown"] = null;
			cur.data["s:/msprop_s"] = null;
		};

		function __init_data(evt) {
			var x = evt ? (orn == "h" ? evt.clientX : evt.clientY) : 0;
			var f = igk.winui.transform.getData;
			var c = cibling;
			var v = c ? (orn == "h" ? f(c).getX() : f(c).getY()) : 0;
			return {
				top: cur.getTop(),
				start: x,
				end: x,
				value: v,
				childs: p.select('>>')
			};
		};
		function _u_cur(cur, e, y) {
			e.end = y;
			e.diff = e.end - e.start;
			var t = e.top + e.diff;
			var maxt = (q.getHeight() - cur.getHeight());

			if (t <= 0)
				t = 0;
			else
				t = Math.min(t, maxt);
			var x = (t / maxt);

			// if (e.value == x){

			// return;
			// }
			var d = 0;
			e.value = x;
			if (orn == 'h') {
				cur.setCss({ "left": t + "px" });
				d = (-p.o.clientWidth + m_psize.w) * e.value;
			} else {
				cur.setCss({ "top": t + "px" });
				d = (-p.o.clientHeight + m_psize.h) * e.value;
			}

			// maxt==> 1
			// t==> x

			// (t / maxt);

			// p.o.clientHeight==>100%
			// p.o.offsetHeight==>x
			// 
			// p.o.offsetHeight * e.value==>x
			// var d = (p.o.scrollHeight -  p.o.offsetHeight) * e.value;
			// var d = (-p.o.clientHeight + m_psize.h) * e.value;
			// var d=Math.min(100,(p.o.offsetHeight * 100) /p.o.clientHeight );




			e.childs.each(function () {
				if ((this != q) && !this.data["igk-scroll-b"]) {
					// method 1 if transform support for performance
					var data = igk.winui.transform.getData(this);
					data.setTranslateY(-d);
					this.setCss({ "transform": data.toString() });
					// method 2 in global
					// this.setCss({"top":(-d)+"px"});

				}
				return !0;
			});
		};
		function __update() {
			var e = __init_data();
			var v = e.value * -1;

			if (!m_init) {
				e.start = 0;
				e.end = 0;
				e.value = 0;
			}
			_u_cur(cur, e, v);
		}

		// igk.android.log.add('is touchable ' + p.istouchable());
		if (p.istouchable()) {
			// support touch
			var tlog = null;
			var vlog = null;
			p.reg_event("touchstart", function (evt) {
				// igk.android.log.add('touchstart');
				if (evt.touches.length == 1) {
					tlog = igk.createNode("div");
					igk.android.log.add(tlog);
					vlog = igk.createNode("div");
					igk.android.log.add(vlog);
					init_view();
				}


			}).reg_event("touchend", function (evt) {
				q.rmClass("igk-show");
				// igk.android.log.add('touchend');
			}).reg_event("touchmove", function (evt) {
				var _th = evt.touches[0];
				// tlog.setHtml("{"+_th.clientX+" x "+_th.clientY+"}");
				// vlog.setHtml("{"+_th.screenX+" x "+_th.screenY+"}");
				// igk.android.log.add('touchmove '+evt.touches[0].clientX);
			}).reg_event("touchcancel", function (evt) {
				// igk.android.log.add('touchcancel');
			});;


		}
		var passive = igk.features.supportPassive ? { passive: true } : false;
		p.reg_event("mouseover", function () {
			init_view();
		}).reg_event("mouseleave", function () {

			if (cur.data["s:/msdown"])
				return;
			q.rmClass("igk-show");
		}).reg_event("mouseup", function (evt) {



			if (!p.getScreenBounds().contains(evt.clientX, evt.clientY)) {
				q.rmClass("igk-show");
			}
		}).reg_event("mousewheel", function (evt) {
			// if (!igk.features.supportPassive){
			//console.debug("wheel");
			evt.stopPropagation();
			evt.preventDefault();
			// }
			if (!m_enable)
				return;
			var e = __init_data();
			e.start = 0;
			e.end = 0;
			e.value = 0;
			var dir = evt.deltaY > 0 ? -1 : 1;
			// step :: heigh scroll by 10


			_u_cur(cur, e, dir * 10);


		}, false).reg_event("scroll", function (evt) {
			evt.stopPropagation();
			evt.preventDefault();
		}, false);

		igk.publisher.register("sys://doc/changed", function (o) {
			if ((o.target == p.o) || igk.dom.isChild(o.target, p.o)) {// __is_parent(o.target, p.o)){
				// reload scroll
				__update();
			}
		});

		cur.reg_event("mousedown", function (evt) {
			if (!cur.data["s:/msdown"]) {
				igk.winui.mouseCapture.setCapture(cur.o);
				cur.data["s:/msdown"] = 1;
				igk.winui.selection.stopselection();


				var e = __init_data(evt);
			};
			if (orn == 'h') {
				e.start = evt.clientX;
				e.end = evt.clientX;
				e.left = cur.getLeft();
			}
			cur.data["s:/msprop_s"] = e;
		})
			.reg_event("mousemove", function (evt) {
				if (cur.data["s:/msdown"]) {

					if (igk.winui.mouseButton(evt) != igk.winui.mouseButton.Left) {
						// cause of drag and drop in chrome

						return;
					}
					var e = cur.data["s:/msprop_s"];
					if (orn == 'v')// check orientation:vertical
					{
						_u_cur(cur, e, evt.clientY);

					} else { // orientation horizontal
						e.end = evt.clientX;
						e.diff = e.end - e.start;
						var t = e.left + e.diff;
						var maxt = (q.getWidth() - cur.getWidth());
						if (t < 0)
							t = 0;
						else
							t = Math.min(t, maxt);
						cur.setCss({ "left": t + "px" });
						e.value = (t / maxt);
						var d = (p.o.scrollWidth - p.o.offsetWidth) * e.value;
						// var d=Math.min(100,(p.o.offsetHeight * 100) /p.o.clientHeight );
						e.childs.each(function () {
							if ((this != q) && !this.data["igk-scroll-b"]) {
								// method 1 if transform support for performance
								var data = igk.winui.transform.getData(this);

								data.setTranslateX(-d);
								this.setCss({ "transform": data.toString() });
								// "translateX("+(-d)+"px)"
								// method 2 in global
								// this.setCss({"top":(-d)+"px"});						

							}
							return !0;
						});


					}
				}
			})
			.reg_event("mouseup", function (evt) {
				__stop_capture();
				igk.winui.selection.enableselection();
			});
		// __update();
	}
	igk.winui.initClassControl("igk-vscroll", function () { __init_scroll.apply(this, ['v']); }, { "desc": "vertical scroll bar" });
	igk.winui.initClassControl("igk-hscroll", function () { __init_scroll.apply(this, ['h']); }, { "desc": "horizontal scroll bar" });
})();


igk.system.createNS("igk.system", {
	dateTime: function (date) {
		if (!date)
			return null;
		function _dateTimeObj(date) {
			this.date = date;
			igk.appendProperties(this, {
				format: function (str) {
					var o = "";
					for (var i = 0; i < str.length; i++) {
						var m = str[i];
						switch (m) {
							case "m": o += this.date.getMinutes(); break;
							case "M": o += (this.date.getMonth() + 1); break;
							case "d": o += this.date.getDate(); break;
							case "Y": o += this.date.getFullYear(); break;
							case "h": o += this.date.getHours(); break;
							case "s": o += this.date.getSeconds(); break;
							default:
								o += m;
						}
					}
					return o;

				}
			});
		};
		return new _dateTimeObj(date);
	}
});


// used to manage android log
(function () {
	var slog = null;

	igk.system.createNS("igk.android.log", {
		add: function (m) {
			if (slog == null)
				return;
			if (typeof (m) == 'string') {
				var d = new Date();
				slog.add('div').setHtml(igk.system.dateTime(d).format('d:M:Y') + "=> " + m);
			} else {
				// igk.android.log.add('reg obj '+typeof(m));
				slog.add('div').add(m);
			}
		},
		clear: function (m) {
			slog.setHtml('');
		}
	});
	function __initAndroidLog() {
		if (slog) return;

		slog = this;
		igk.android.log.add('start');
	};
	igk.winui.initClassControl("igk-android-log", __initAndroidLog);
})();

(function () {
	function __transform_data(s) {
		var m0 = 1, m1 = 0, m2 = 0, m3 = 1, tx = 0, ty = 0, tz = 0;
		var mz = 0;
		var t = '2d';
		if (/^matrix\(/i.exec(s)) {
			// matrix pattern;
			var tb = s.match(/^matrix\((.+)\)$/)[1].split(',');


			m0 = parseFloat(tb[0]); m1 = parseFloat(tb[1]); m2 = parseFloat(tb[2]);
			m3 = parseFloat(tb[3]); tx = parseFloat(tb[4]); ty = parseFloat(tb[5]);
		}
		else if (s == 'none') {// identity
		}
		else {
			throw "Matrix not found :::: " + s;
		}
		function __get_s() {
			return "matrix(" + m0 + "," + m1 + "," + m2 + "," + m3 + "," + tx + "," + ty + ")";
		};
		igk.appendProperties(this, {
			getX: function () {
				return tx;
			},
			getY: function () {
				return ty;
			},
			getz: function () {
				return tz;
			},
			setTranslateX: function (d) {
				tx = d;
			},
			setTranslateY: function (d) {
				ty = d;
			},
			setTranslateZ: function (d) {
				tz = d;
			},
			setScaleX: function (d) {
				m0 = d;
			},
			setScaleY: function (d) {
				m3 = d;
			},
			setScaleZ: function (d) {

			},
			toString: __get_s
		});
	};
	igk.system.createNS("igk.winui.transform", {
		getData: function (n) {
			var s = $igk(n).getComputedStyle("transform");
			// string type

			return new __transform_data(s || 'none');
		}
	});

})();


// winui selection util
(function () {
	var s = 0;
	var slfunc = 0;
	function __no_select(evt) {
		// onselectstart for document work for ie
		evt.preventDefault();
		evt.stopPropagation();

	};
	var NS = igk.system.createNS("igk.winui.selection", {
		stopselection: function () {
			igk.winui.selection.clear();
			// for global ie
			if (typeof (document.onselectstart) != 'undefined')
				$igk(document).reg_event('selectstart', __no_select);
			else if (typeof (igk.dom.body().style.MozUserSelect) != 'undefined') // for firefox only
				igk.dom.body().style.MozUserSelect = 'none';
			s = 1;
		},
		enableselection: function () {
			// for global ie
			if (typeof (document.onselectstart) != 'undefined')
				$igk(document).unreg_event('selectstart', __no_select);
			else if (typeof (igk.dom.body().style.MozUserSelect) != 'undefined') // for firefox only
				igk.dom.body().style.MozUserSelect = '';
			// if(typeof(igk.dom.body().style.MozUserSelect) !='undefined')
			// igk.dom.body().style.MozUserSelect='';
			s = 0;
		},
		clear: function () {
			var sel = NS.Selection;
			if (sel) {
				var fc = sel.removeAllRanges || sel.empty;
				fc.apply(sel);
			}
		}
	});


	igk.defineProperty(NS, "Selection", {
		get: function () {
			if (!slfunc) {
				slfunc = window.getSelection ? window.getSelection() : document.selection;
			}
			return slfunc;
		}
	});

})();

// 
(function () {
	// igk-ctrl-options
	igk.winui.initClassControl("igk-ctrl-options", function () {
		// this.setCss({zIndex:800,backgroundColor:'#eee',"position":"absolute","width":"100%"});

	});
})();


(function () {
	function __init_card_id() {
		var q = this;
		var _2PI = igk.math._2PI;
		var _src = q.getAttribute('igk:link');

		q.o.removeAttribute('igk:link');


		var _data = {
			img: _src == null ? null : q.add("img").addClass("posab dispn").setAttribute("src", _src).reg_event("load", function (evt) {
				_data.render();
				q.o.removeAttribute('igk:link');

			}).reg_event("error", function () {
				console.error("/!\\ Error on loading image " + _src);

			}),
			storyline: (function () {
				var s = q.add("div").addClass("posab dispn story-line");
				var bg = s.add("div").addClass("posab dispn bg");
				var bdr = s.add("div").addClass("posab dispn bdr");
				return {
					"bg": bg,
					"bdr": bdr
				};
			})(),
			canva: q.add("canvas"),
			ctx: null,
			render: function (w, h) {
				if (this.ctx == null) {
					this.ctx = this.canva.o.getContext('2d');
				}
				var w = igk.getNumber(this.canva.getComputedStyle("width"));
				var h = igk.getNumber(this.canva.getComputedStyle("height"));
				var v_bdrcl = _data.storyline.bdr.getComputedStyle("color");
				var v_bg = _data.storyline.bg.getComputedStyle("background-color");
				var v_bdrs = igk.getNumber(_data.storyline.bdr.getComputedStyle("height")) || 4;


				var _ctx = this.ctx;
				var cx = w / 2;
				var cy = h / 2;
				var penw = v_bdrs;
				var minR = Math.min(w / 2, h / 2);
				var R = minR - (penw / 2);
				var r = minR - (penw / 2) - 10;



				this.canva.setAttribute("width", w);
				this.canva.setAttribute("height", h);
				if ((R < 0) || (r < 0))
					return;




				_ctx.clearRect(0, 0, w, h);

				_ctx.fillStyle = v_bg;
				_ctx.arc(cx, cy, R, 0, _2PI, false);
				_ctx.closePath();
				_ctx.fill();

				_ctx.save();
				// clip to region
				_ctx.clip();

				// draw image
				if (this.img && this.img.o.complete) {
					var _W = w - 8;
					var _H = h - 8;
					var zx = _W / this.img.o.width;
					var zy = _H / this.img.o.height;
					var zx = Math.min(zx, zy);

					_ctx.drawImage(this.img.o, 4 + ((_W - (this.img.o.width * zx)) / 2), 4, this.img.o.width * zx, this.img.o.height * zx);
					// igk.drawing.effect.stackBlur(_ctx,0,0,w,h,5);
				}
				_ctx.restore();
				_ctx.strokeStyle = v_bdrcl;
				_ctx.lineWidth = v_bdrs;
				_ctx.arc(cx, cy, R, 0, _2PI, false);
				_ctx.closePath();

				// _ctx.shadowColor='#999';
				// _ctx.shadowBlur=4;
				// _ctx.shadowOffsetX=4;
				// _ctx.shadowOffsetY=4;

				_ctx.stroke();



			}
		};

		_data.render();
	};
	igk.winui.initClassControl("igk-card-id", __init_card_id, {
		desc: "igk card id"
	});
})();


// ------------------------------------------------------------------
// balafon js component management
// ------------------------------------------------------------------
(function () {

	igk.system.createNS("igk.balafonjs", {
		initComponent: function (t) {
			t = t || igk.getParentScript();
			if (!t)
				return;
			var st = igk.winui.getClassList();
			$igk(t).add("div").setHtml(st);
		}
	});

	igk.ctrl.registerAttribManager("igk-balafonjs", {});
	// node compopent width balajs js javascript
	igk.ctrl.bindAttribManager("igk-balafonjs", function (m, v) {
		if (m)
			eval(v);
	});

})();


// ----------------------------------------------------------
// igk-hpageview  
// ----------------------------------------------------------
(function () {
	function __init() {
		var q = this;
		var def = [];
		var v_idx = 0;// store the next value
		var cur = 0;// current visible node
		var m_roles = {};
		var m_autosweep = 0;


		igk.appendProperties(q, {
			movenext: function () {
				if (v_idx <= def.length - 1) {
					goTo(def[v_idx]);
					v_idx++;
					update_role();
				}
			},
			moveback: function () {
				if ((v_idx > 1) && (def.length > 0)) {
					goTo(def[v_idx - 2]);
					v_idx--;
					update_role();
				}
			}
		});

		function reg_role(t, n) {
			if (!(t in m_roles)) {
				m_roles[t] = [];

			}
			m_roles[t].push(n);
		};
		function update_role() {
			var t = m_roles["prev"];
			for (var s = 0; s < t.length; s++) {
				if (v_idx <= 1) {
					t[s].addClass("dispn");
				}
				else {
					t[s].rmClass("dispn");
				}
			}
			t = m_roles["next"];
			var s = (def.length > 0) && (v_idx < def.length);

			for (var k = 0; k < t.length; k++) {
				if (s) {

					t[k].rmClass("dispn");
				}
				else {
					t[k].addClass("dispn");
				}
			}
		};

		// function goBack(){
		// 	if(v_idx>0){
		// 		v_idx--;
		// 		q.scrollTo(def[v_idx]);
		// 	}
		// };
		function goTo(o) {
			q.scrollTo(o.o);
			cur = o;
		};
		// object of each view
		// {p: previous node,n: next node,nextCond: next condition to evaluate}
		q.select(">>").each(function () {
			if (this.o.tagName && this.o.tagName.toLowerCase() == "div") {
				var obj = igk.JSON.parse(this.getAttribute("igk-hpageview-data"));
				var id = this.getAttribute("id") || ((def.length + 1) + "");
				def[id] = this;
				def.push(this);
				if (!obj) {
					obj = { p: null, n: null };
				}
				if (m_autosweep) {
					this.reg_event("click", function () {
						q.movenext();
						// if(obj.n)
						// goTo(def[obj.n]);

						// else if(v_idx<def.length){

						// goTo(def[v_idx]);
						// v_idx++;
						// }
					});
				}


			}
			return !0;
		});

		q.getParentNode().select("input").each_all(function () {

			var s = this.getAttribute("igk-hpageview-role");
			if (s) {
				reg_role(s, this);
			}
		});

		if (def.length > 0)
			v_idx = 1;
		// scroll to the begining
		q.scrollTo(def[0]);


		// igk.winui.reg_event(window,"transitionend", function(evt){



		// // goTo(cur);
		// // }
		// });

		// because of file
		igk.winui.reg_event(window, "resize", function () {
			var b = q.getComputedStyle("transition");
			if (b) {
				q.setTransition("none");
			}

			setTimeout(function () {
				if (cur) {

					goTo(cur);
				}
			}, 2000);
		});
		update_role();
	};
	igk.winui.initClassControl("igk-hpageview", __init, {
		desc: "igk-pageview control"
	});

})();

// -----------------------------------------------------------------------------
// igk-svg-symbol
// -----------------------------------------------------------------------------

(function () {
	var START_ELEMENT = 1;
	var END_ELEMENT = 2;
	var ATTRIBUTE = 3;
	var TEXT = 4;
	var PROCESSOR = 5;
	var IGK_TAGNAME_CHAR_REGEX = /[0-9a-z\-\:_\.]/;
	function _html_domProperty() {
		return {
			createElement: function (t) {
				return document.createElement(t);
			},
			createTextElement: function (v) {
				return document.createTextNode(v);
			},
			getNS: function () {
				// system namespace
				return null; // igk.dom.body().namespaceURI;
			}
		};

	};
	igk.system.createNS("igk.dom",
		{
			isChild: function (o, p) { // echeck if an item [o] is a child of [p]
				while (o && (o != p)) {
					o = o.parentNode;
				}
				return o != null;
			},
			isParent: function (s, p) {
				while (s && p && (s.nodeType == 1)) {
					if ((s = s.parentNode) == p)
						return 1;
				}
				return 0;
			},
			loadDocument: function (txt, p) {
				p = p || _html_domProperty();

				// replace all script
				// text = txt.replace(/<script/

				var c = new igk.dom.reader(txt);

				var root = null;
				var cnode = null;
				// var o=0;
				var ns = p.getNS();
				var stop = 1;
				while (stop && c.read()) {

					switch (c.type) {
						case PROCESSOR:
							throw "not used";
						// break;
						case ATTRIBUTE:
							for (i in c.attribs) {
								cnode.setAttribute(i, c.attribs[i]);
							}
							break;
						case TEXT:
							if (cnode) {
								if (cnode.childNodes.length == 0) {
									cnode.innerHTML = c.value;
								} else {
									cnode.appendChild(p.createTextElement(c.value));
								}
							}
							break;
						case START_ELEMENT:
							var m = p.createElement(c.name, c);
							if (ns) {
								m.setAttribute("xmlns", ns);
							}
							if (m.tagName == "SCRIPT") {
								c.readScript(m);

							}
							if (root == null) {
								root = m;
								cnode = root;
							} else {
								if (cnode == null) {

									cnode = p.createElement("dummy");
									cnode.appendChild(root);
									root = cnode;
									cnode.appendChild(m);
									cnode = m;
								} else {
									cnode.appendChild(m);
									cnode = m;
								}
							}
							break;
						case END_ELEMENT:
							if (cnode)
								cnode = cnode.parentNode;
							break;
						default:
							console.debug("element ignored " + c.type);
							break;
					}
					o++;
				}

				return root;
			}
			, reader: function (txt) {
				this.txt = txt;
				this.pos = 0;
				this.isEmpty = 1;
				this.hasAttrib = 0;
				this.attribs = null;

				var self = this;

				// private function 

				function __canRead() {
					return ((self.pos >= 0) && (self.txt && txt.length >= 0) && (self.pos < self.txt.length));
				};
				function __readName() {
					var v = "";
					// check reaed cararacter
					while (__canRead() && IGK_TAGNAME_CHAR_REGEX.exec(self.txt[self.pos])) {
						v += self.txt[self.pos];
						self.pos++;
					}

					return v;
				};
				function __readLine() {
					var v = "";
					var v_ch = 0;
					while (self.pos < self.txt.length) {
						v_ch = self.txt[self.pos];
						if (v_ch == "\n") {
							break;
						}
						v += v_ch;
						self.pos++;
					}
					return v;

				};
				function __readAttributes() {
					var v = "";
					while (__canRead()) {
						v += self.txt[self.pos];
						if (v.length > 2 && (v.substring(v.length - 2) == "/>")) {
							v = v.substring(0, v.length - 2);
							self.isEmpty = 1;
							break;
						} else if ((v.length > 0) && (v.substring(v.length - 1) == ">")) {
							v = v.substring(0, v.length - 1);
							self.isEmpty = 0;
							break;
						}
						self.pos++;
					}
					return igk.system.string.trim(v);
				};
				function __readTo(end) {
					var v = "";
					var ln = end.length;
					while (__canRead()) {
						v += self.txt[self.pos];
						if (v.length >= ln && (v.substring(v.length - ln) == end)) {
							v = v.substring(0, v.length - ln);
							break;
						}
						self.pos++;
					}
					return igk.system.string.trim(v);
				}
				function __loadAttribs(h) {
					var s = {};
					h.replace(igk.regex().attribs, function (m) {
						var t = m.split('=');
						var kl = /^("|')/.exec(t[1]) ?
							t[1].substring(1, t[1].length - 1) : t[1];
						s[t[0]] = kl;
					});
					return s;
				}
				igk.appendProperties(this, {
					"name": null,
					"value": null,
					"type": null,
					read: function () {
						if (this.type == START_ELEMENT) {
							var h = __readAttributes();
							if (h.length > 0) {
								this.hasAttrib = 1;


								this.type = ATTRIBUTE;
								this.attribs = __loadAttribs(h);

								return 1;
							} else if (this.isEmpty) {
								this.type = END_ELEMENT;
								return 1;
							}
						} else if ((this.type == ATTRIBUTE) && (this.isEmpty)) {
							this.type = END_ELEMENT;
							return 1;
						}
						// var v=0;
						var v_r = 1;
						var v_enter = 0;
						// var v_tmp="";

						while (__canRead()) {
							v_r = 0;
							v_ch = this.txt[this.pos];


							switch (v_ch) {
								case "<":// start tag
									v_enter = 1;
									break;
								case ">":
									if ((this.type == ATTRIBUTE) ||
										(this.type == START_ELEMENT)) {
										// if current type is attribute 
										var c_pos = this.pos++;
										v_ch = "";// read text
										v_temp = __readTo("<");

										if (v_temp.length > 0) {
											this.type = TEXT;
											this.value = v_temp;
											this.isEmpty = false;
											this.hasAttrib = false;
											return 1;
										}
										else
											this.pos = c_pos;
									}


									break;
								case "?":// for processor
									if (v_enter) {
										v_tmp = __readTo("?>");
									}
									break;
								case "!":
									if (v_enter) {
										v_tmp = __readTo("-->");
									}
									break;
								case "/":// for end tag
									if (v_enter) {
										this.pos++;
										this.type = END_ELEMENT;
										this.name = __readName();
										this.value = null;
										this.isEmpty = false;
										this.hasAttrib = false;

										return true;
									}
									// $v .=$c;
									break;
								default:

									if (v_enter) {
										if (v_ch == " ")
											throw new Error("empty char not valid");

										this.name = __readName();
										this.value = null;
										this.type = START_ELEMENT;
										this.isEmpty = false;
										this.hasAttrib = false;

										v_enter = 0;

										return true;
									}

									break;
							}



							this.pos++;
						}


						if (v_r) {
							return false;
						}



						return true;
					},
					skip: function () {
						// igk_wln("ddd ".$this->NodeType);
						if (this.type == START_ELEMENT) {
							if (!_isEmpty) {

								$n = this.name.lowerCase();
								// igk_wln("is not empty".$n);

								var depth = 0;
								var end = 0;
								while (!end && this.Read()) {
									switch (this.type) {
										case START_ELEMENT:
											depth++;
											break;
										case END_ELEMENT:
											if ((!depth) && (this.name.lowerCase() == n)) {
												end = true;
											}
											else if (depth > 0)
												depth--;
											break;
									}
								}
								return end;
							}
						}
						return 0;

					}// end skip function
					, readScript: function (m) {
						var q = this;
						var v = "";
						var i = {};
						var dx = 0;
						var v_ch = 0;
						var attribs = __readAttributes();
						if (attribs.length > 0) {
							q.type = ATTRIBUTE;
							q.attribs = __loadAttribs(attribs);
							for (i in q.attribs) {
								m.setAttribute(i, q.attribs[i]);
							}
						}
						// if (q.read()){


						// }
						var e = 0;
						while (!e && __canRead()) {
							q.pos++;
							v_ch = q.txt[q.pos];
							switch (v_ch) {
								case ">":
									// start reading
									break;
								case "<":// detected the closing tag
									if (q.txt[q.pos + 1] == "/") {
										q.pos += 2;
										var n = __readName();
										if (n.toLowerCase() == m.tagName.toLowerCase()) {
											q.type = END_ELEMENT;
											q.value = n;
											e = 1;
											break;
										}
									}
									v += v_ch;
									break;
								case "\'":
								case '"':
									// read string
									var spos = q.pos;

									while ((dx = q.txt.indexOf(v_ch, q.pos + 1)) != -1) {
										if (dx > 0) {
											if (q.txt[dx - 1] == "\\")// escaped{
											{
												q.pos = dx;
												continue;
											}
										}
										break;
									}
									if (dx > spos) {
										v += q.txt.substr(spos, dx - spos + 1);
										q.pos = dx;
									}
									break;
								case "/":// detect comment
									if (q.txt.length - 1 > q.pos) {
										switch (q.txt[q.pos + 1]) {
											case "/":
												// read line
												v += __readLine();
												break;
											case "*":
												//TODO :::: RM Comment
												dx = q.txt.indexOf("*\/", q.pos + 2);
												v += "\/*" + q.txt.substr(q.pos, dx - q.pos);
												q.pos = dx + 1;
												break;
										}
									}
									break;
								default:
									v += v_ch;
									break;
							}
						}

						m.innerText = v;
						console.debug("done :" + v);
					}


				});// end append prop

			}// end reader
			, childToArray: function (n) {
				var t = $igk(n).o.childNodes;
				var o = [];
				if (t) {
					for (var s = 0; s < t.length; s++) {
						o.push(t[s]);
					}
				}
				return o;
			}


			// end igk.dom namespace declaration	
		});

	// end create document
})();


(function () {
	var m_symbols = [];
	var m_noloads = [];
	var m_keyloads = {};
	var m_ns = igk.namespaces.svg; // "http://www.w3.org/2000/svg";
	function __loadSvg(text) {
		return igk.dom.loadDocument(text, {
			createElement: function (tag) {
				return document.createElementNS(m_ns, tag);
			},
			getNS: function () { return m_ns; }
		});
	};
	function __init() {
		var n = this.getAttribute("igk:svg-name");
		var g = m_symbols[n];

		if (g) {
			var c = g.clone();
			// append attribute to node class
			c.setAttribute("class", this.getAttribute("class"));
			// c.setAttribute("fill","red");
			this.o.parentNode.replaceChild(c.o, this.o);
		} else {
			m_noloads.push(this);
			var kobj = { name: n, t: this };
			if (!m_keyloads[n])
				m_keyloads[n] = [];
			m_keyloads[n].push(kobj);

		}

	};
	function __initsymbol() {
		var n = this.getAttribute("id");

		if (!m_symbols[n]) {


			// if egdge for xbox one load outer document

			var ot = this.o.outerHTML;
			var doc = ot ? $igk(__loadSvg(ot)) : null;
			if (!doc) {
				// if other 
				m_symbols.push(this);
				m_symbols[n] = this;
			} else {
				m_symbols.push(doc);
				m_symbols[n] = doc;
			}

			// doc.setAttribute("class",this.getAttribute("class"));

			if (m_keyloads[n]) {




				for (var ii = 0; ii < m_keyloads[n].length; ii++) {

					var t = m_keyloads[n][ii].t;
					// debug=1;

					__init.apply(t);
					// debug=0;

				}

				m_keyloads[n] = null;
				delete (m_keyloads[n]);


			}

		}
	};
	function __loadSymbols(f) {

		if (igk.getSettings().nosymbol) {
			// because of no symbol required IE specification 
			return;
		}

		igk.io.file.load(f, function (d) {


			if (typeof (d.data) != "string") {
				if (d.error) {
					return;
				}
			}
			var d = igk.utils.getBodyContent(d.data);
			var q = igk.createNode("div").setHtml(d);//d.data);
			var svg_c = 0;
			var t = q.select(">>").each(function () {
				if (this.o.tagName) {
					switch (this.o.tagName.toLowerCase()) {
						case "svgs":
							// load multiple svg document
							var m = this;
							// select child svg

							m.select(">> svg").each_all(function () {
								this.fn.svg = f;
								__initsymbol.apply(this);
							});
							break;
						case "svg":
							// load single document
							__initsymbol.apply(this);
							svg_c++;
							break;
					}
				}
				return !0;
			});


			igk.publisher.publish("sys://svg/lds", { target: this, file: f, count: svg_c, selector: t }); // loaded symbol
		});
	};


	igk.system.createNS("igk.svg", {
		loadSymbols: __loadSymbols,
		append: function (n, t) {
			var sv = igk.svg.newSvgContainer(n);
			t.add(sv);
			sv.init();
		},
		getLoadedKey: function () {
			var t = [];
			for (var i = 0; i < m_symbols.length; i++) {

				t.push(m_symbols[i].getAttribute("id"));
			}
			return t;
		},
		newSvgContainer: function (n) { // create new igk;svgSymbol block container
			var q = igk.createNode("div");
			q.setAttribute('igk:svg-name', n).addClass("igk-svg-symbol " + n);
			return q;
		},
		newSvgDocument: function () {
			//return a new svg document
			return $igk(document.createElementNS(igk.namespaces.svg, 'svg')).setAttributes({
				version: '1.1'
			});
		}
	});
	igk.winui.initClassControl("igk-svg-symbol", __init, {
		desc: "igk-svg-symbol item. get a symbol from a svg document files and render it to content"
	});

	// firefox load css before raise document ready
	// firefox allow creation of node reference without adding it to document
	var n = igk.createNode("div");
	n.addClass("igk-svg-symbol-lists");
	// var lst=n.getComputedStyle("content",":before").replace(/"/g,'').split(',');
	igk.ready(function () {
		if (igk.getSettings().nosymbol) {
			// because of no symbol required IE specification 
			return;
		}
		// add to body required to get content
		igk.dom.body().appendChild(n.o);
		var h = n.getComputedStyle("content", ":before");

		if (h && (h != 'none')) {
			var lst = h.replace(/"/g, '').split(',');

			for (var i = 0; i < lst.length; i++) {

				igk.svg.loadSymbols(lst[i]);
			}
		}
		n.remove();
	});


})();

// ---------------------------------------------------------------------------------------------------	
// ---------------------------------------------------------------------------------------------------	
(function () {
	function __init() {
		igk.initpowered(this.o);

	}
	igk.winui.initClassControl("igk-powered", __init, {
		desc: "manage powered message"
	});
})();


// ---------------------------------------------------------------------------------------------------	
// igk-comm-lnk
// ---------------------------------------------------------------------------------------------------	
(function () {
	function __init() {
		var hr = this.getAttribute("href");
		var n = this.getAttribute("igk:title");
		this.reg_event("click", function () {
			window.open(hr);
		});
		// 
		this.add(igk.svg.newSvgContainer(n));

	}
	igk.winui.initClassControl("igk-comm-lnk", __init, {
		desc: "represent a communication link node. image will be by default a igk-svg-symbol"
	});
})();
// ---------------------------------------------------------------------------------------------------	
// igk-page : constrol that will bind page verticaliy according to screen size. but resize ::: 
// ---------------------------------------------------------------------------------------------------	
(function () {
	var m_pages = [];
	function __init() {
		m_pages.push(this);
		var s = igk.winui.screenSize();
		var w = s.width;
		var h = s.height;
		_update_i(this, w, h);

	};
	function _update_i(k, w, h) {
		// var m=k.getComputedStyle("height");
		k.setCss({ height: "auto" });
		if (k.o.offsetHeight < h) {
			k.setCss({
				height: h + "px"
			});
		} else {
			k.setCss({
				height: k.o.offsetHeight + "px"
			});
		}
		if (k.o.offsetWidth < w) {

		}
	}
	function __update() {
		var s = igk.winui.screenSize();
		var w = s.width;
		var h = s.height;

		for (var i = 0; i < m_pages.length; i++) {
			var k = m_pages[i];
			_update_i(k, w, h);
		}
	}
	var tmout = 0;
	igk.winui.initClassControl("igk-page", __init, {
		desc: "page management"
	});
	igk.winui.reg_event(window, "resize", function () {
		clearTimeout(tmout);
		if (m_pages.length > 0) {

			tmout = setTimeout(function () {
				__update();
			}, 500);

		}
	});



})();

(function () {
	// 
	// button used to show dialog
	// 
	igk.winui.initClassControl("igk-js-btn-show-dialog", function () {
		var _id = this.getAttribute("igk:dialog-id");
		if (_id) {
			this.reg_event("click", function () {
				ns_igk.winui.showDialog(_id); return !1;
			});
		}
	}, {
		desc: "JS Component to show inline ms dialog button"
	});
})();
(function () {
	// 
	// input regex entrey
	// 

	function __init_input_regex(ctx) {
		if (!this.o.tagName || (this.o.tagName.toLowerCase() != 'input') || ! /(text|password|email)/i.exec(this.o.type))
			return;

		var q = this;
		var def = "sys://control/inputregex";



		if (q.data[def]) {

			return !1;
		}
		q.data[def] = 1;
		q.data[def + "/class"] = ctx;
		var _rg = this.getAttribute("igk:char-regex"); // to validate input entry
		var _rg_opt = this.getAttribute("igk:char-regex-option") || "i"; // options for char input entry
		var _ig = this.getAttribute("igk:input-regex"); // to validate the data
		var _ig_opt = this.getAttribute("igk:input-regex-options") || "i"; // to validate the data
		var _e_msg = this.getAttribute("igk:error-msg");
		var _e_id_msg = this.getAttribute("igk:error-target-id");
		var _ms_n = null;// message node

		if (_ig) {

			_ig = new RegExp("^" + _ig + "$", _ig_opt);
			this.reg_event("change", function () {
				q.igkCheckIsInvalid();

			}
			);
			q.igkIsInvalid = function () {
				return _ig.exec(q.o.value);
			};
			q.igkCheckIsInvalid = function () {
				if (_ms_n) {
					$igk(_ms_n).remove();
					_ms_n = null;
				}
				if (q.igkIsInvalid()) {
					q.rmClass("igk-invalid");
					return !1;
				} else {
					q.addClass("igk-invalid");
					_ms_n = igk.createNode('div');
					_ms_n.addClass("igk-e-msg");
					if (_e_id_msg) {
						var _st = $igk(_e_id_msg);
						if (_st) { 
							_ms_n.setHtml(_st.getHtml());
						}
					} else {
						if (_e_msg) {
							_ms_n.setHtml(_e_msg);
						}
					}
					q.insertAfter(_ms_n.o);
					return !0;
				}
			};
		}

		if (!_rg)
			return;
		var _emsg = this.getAttribute("igk:error-msg");
		// if(_emsg)
		// this.o.setCustomValidity(_emsg);
		_rg = new RegExp(_rg, _rg_opt);



		this.reg_event("invalid", function () {

			q.o.setCustomValidity(_emsg);
		});

		this.reg_event("keypress", function (evt) {

			evt.stopPropagation();
			var c = evt.charCode || evt.keyCode;
			var ctrlkey = evt.ctrlKey;
			var altKey = evt.altKey;
			var ch = evt.char || evt.key; // get char expression ie vs modzilla
			// igk.show_prop(evt);	


			// if(!ctrlkey && !altKey &&(c > 31)&&(evt.key.length==1) && !_rg.exec(ch))
			// evt.preventDefault();

			if (!ctrlkey && !altKey && (c > 31) && !_rg.exec(ch))
				evt.preventDefault();

			return !1;
		});
		// this.reg_event("keyup",function(evt){

		// evt.stopPropagation();
		// evt.preventDefault();
		// return !1;
		// });

		this.reg_event("input", function () {
			q.o.setCustomValidity("");
		});
		// this.reg_event("change",function(evt){

		// evt.preventDefault();
		// });
		// protect for paste data
		this.reg_event("paste", function (evt) {

			if (evt.clipboardData.types <= 0)
				return;
			// var t=evt.clipboardData.types[0];
			// igk.show_prop(evt.clipboardData.types);
			var d = evt.clipboardData.getData("text/plain");

			for (var i = 0; i < d.length; i++) {
				if (!_rg.exec(d[i])) {
					evt.stopPropagation();
					evt.preventDefault();
					break;
				}
			}


			return !1;
		});
		if (q.o.value)
			q.igkCheckIsInvalid();
		return !0;

	}
	igk.ctrl.registerAttribManager("igk-input-regex", {
		desc: "used to validate inform balafon js to validate data",
		ns: 'validation'
	});
	igk.ctrl.bindAttribManager("igk-input-regex", function (a, v) {
		// init binding input regex for item
		// a: attribute
		// v: value
		if (!a || !v) {
			return;
		}
		__init_input_regex.apply(this, ['attrib']);

	});
	igk.winui.initClassControl("igk-input-regex", function () {
		if (!this.o.tagName || (this.o.tagName.toLowerCase() != 'input'))
			return;
		__init_input_regex.apply(this, ['class']);

	}, {
		desc: "JS input-regex component"
	});
})();


// ------------------------------------------------------------------------------------------------------------
// bind igk-input-data
// ------------------------------------------------------------------------------------------------------------
(function () {
	// input data must have {regex=string,maxlength=-1|int,casesensitive=0,validate=func,update=func}
	function inputdata(target, b) {
		this.target = target;
		this.options = b;
		var a = $igk(target);
		var self = this;

		function __updatecal() {
			if (self.options.update)
				self.options.update(a.o.value);
		}
		function __removeSelectedChars(target) {
			var h = (target.value + "");
			var i = target.selectionStart;
			target.value = igk.system.string.remove(h, target.selectionStart, target.selectionEnd - target.selectionStart);
			target.selectionStart = i;
			target.selectionEnd = i;
		}

		a.reg_event("keypress", function (evt) {
			if (evt.key) {

				// for number
				if (evt.charCode > 0) {
					var old = "" + evt.target.value;
					var p = null;
					var v_update = false;
					if (evt.target.selectionStart == evt.target.value.length) {
						old = old + "" + evt.key;
					}
					else {
						p = evt.target.selectionStart;
						if (p != evt.target.selectionEnd) {
							var m = igk.system.string.remove(old, p, evt.target.selectionEnd - p);
							old = igk.system.string.insert(m, p, evt.key)// igk.system.string.remove(old,p,evt.target.selectionEnd-p)  igk.system.string.remove(old,p,evt.target.selectionEnd-p));
						}
						else
							old = igk.system.string.insert(old, p, evt.key);
						p++;
					}

					if (new RegExp(self.options.regex).exec(old))// new RegExp(""+self.options.regex+"","i").exec(old))
					{


						if (old == "-")
							evt.target.value = "-";
						else {
							evt.target.value = old;
						}
						v_update = !0;
					}
					if (v_update && (p != null)) {
						evt.target.selectionStart = p;
						evt.target.selectionEnd = p;
					}
					__updatecal();
					evt.preventDefault();
				}
				else {
					if (!(evt.keyCode > 0)) {
						evt.preventDefault();
					}
					else {
						var s = evt.getPreventDefault();
						if (!s) {
							var i = evt.target.selectionStart;
							var h = (evt.target.value + "");
							switch (evt.keyCode) {
								case 8:// back
									if (i >= 0) {
										if (h.length == i) {
											evt.target.value = h.substring(0, i - 1);
										}
										else {
											if (i == evt.target.selectionEnd) {
												evt.target.value = igk.system.string.remove(h, i - 1, 1);
												evt.target.selectionStart = i - 1;
												evt.target.selectionEnd = i - 1;
											}
											else {
												__removeSelectedChars(evt.target);
											}
											// igk.show_prop(evt.target); 
										}
										__updatecal();
									}
									evt.preventDefault();
									break;
								case 46: // delete
									if ((evt.target.selectionStart == evt.target.selectionEnd) && (i < h.length)) {
										evt.target.value = igk.system.string.remove(h, i, 1);
										evt.target.selectionStart = i;
										evt.target.selectionEnd = i;
									}
									else if (i != evt.target.selectionEnd) {
										// remove value
										__removeSelectedChars(evt.target);

									}
									__updatecal();
									evt.preventDefault();
									break;
							}
						}
					}
				}
			}
			return !1;
		});
	}

	function __init() {
		// define some constant
		// var NUMBER_REGEX=/^(-)?([0-9]+)(\.[0-9]*)?$/;
		// var INT_REGEX=/^(-)?([0-9]+)$/;		
		var s = this.getAttribute("igk-input-data");
		var b = null;
		b = eval("(" + s + ")");
		if (b && b.regex) {
			new inputdata(this, b);
		}
	}
	igk.ctrl.bindAttribManager("igk-input-data", __init);
})();


// form a button
(function () {
	igk.winui.initClassControl("igk-form-sbtn", function () {
		var q = this;
		if (q.o.tagName.toLowerCase() == "a") {
			var f = q.getAttribute("href");
			var frm = q.getParentByTagName("form");

			if (frm) {
				q.reg_event("click", function (evt) {
					evt.stopPropagation();
					evt.preventDefault();
					if ((f == '#') || (f == '')) {
						switch (q.getAttribute("igk:rel")) {
							case "rel":
							default:
								frm.reset();
								break;
						}
					} else {
						frm["action"] = f;
						frm.submit();
					}
					return !1;
				});
			}
		}
	});
	igk.winui.initClassControl("igk-form-sbtn-ajx", function () {
		var q = this;
		if (q.o.tagName.toLowerCase() == "a") {
			var f = q.getAttribute("href");
			var c = 0;
			q.reg_event("click", function (evt) {
				evt.stopPropagation();
				evt.preventDefault();
				if (c == 0) {
					c = 1;
					igk.ajx.post(f, null, function () {
						if (this.isReady()) {
							igk.ajx.fn.replace_or_append_to_body.apply(this, arguments);
							c = 0;
						}
					});

				}
				return !1;
			});
		}
	});

})();


// ajx button pickfile
(function () {
	igk.winui.initClassControl("igk-ajx-pickfile", function (
		// 	n,m,o
	){
		var q = this;
		var u = this.getAttribute("igk:uri");
		if (u == null) {
			console.error("/!\\ igk-ajx-pickfile: no uri found found ");
			return;
		}
		var s = this.getAttribute("igk:param");
		var p = igk.JSON.parse(s, this);
		if (typeof (p) != 'object') {
			p = {};
		} 

		if (q.o.tagName.toLowerCase() == "input") {
			var n = igk.dom.replaceTagWith(q, "div");
			var v = q.getAttribute("value");
			n.setHtml(v ? v : q.getHtml());
			n.o.removeAttribute("value");
			n.o.removeAttribute("type");
			n.o.removeAttribute("igk:uri");
			n.o.removeAttribute("igk:data");
			n.o.removeAttribute("igk:uri");
			q = n;
		}
		q.reg_event('click', function (evt) {
			evt.preventDefault();
			evt.stopPropagation();
			var cp = {};
			cp.type = p && p.type && p.type.exec("(p|l)") ? p.type : 'p';
			cp.complete = (p ? p.complete : null) || function (s) {
				if (this.isReady()) {
					if (p && p.complete) {
						p.complete.apply(this, [s, q]);
					} else {
						this.source = q;
						igk.ajx.fn.replace_or_append_to_body.apply(this, arguments);
					}
					if (_pl) {
						_pl.each_all(function () {
							this.setHtml('');
						});
					}
					_pl = null;
				}
			};
			var _pl = null;
			cp.progress = (p ? p.progress : null) || function (e) {
				if (!_pl)
					_pl = $igk("#igk-pickfile-progress");
				_pl.each_all(function () {
					switch (cp.type) {
						case "p":
							this.setHtml(Math.round((e.loaded / e.total) * 100) + "%");
							break;
						default:
							this.setHtml(e.loaded + " / " + e.total);
							break;
					}
					// this.setHtml(e);
				});
			};
			cp.start = p ? p.start : null;
			cp.accept = p ? p.accept : null;
			//console.debug(cp);
			igk.system.io.pickfile(u, cp);
		});

	}, {
		desc: "Used to pick file in ajx context"
	});
})();


// XML stransform node
(function () {

	igk.winui.initClassControl("igk-xsl-node", function (
		// 	o,m
	) {




		// var attr = igk.winui.ClassRequireAttribute.apply(this, "igk:xslt-data");


		var u = igk.JSON.parse(this.getAttribute("igk:xslt-data"));
		if (u) {
			var q = this;
			igk.ready(function () {
				igk.dom.transformXSLUri(u.xml, u.xsl, function (d) {
					if (u.target) {
						var s = $igk(d).select(u.target);

						var t = s && (s.o.length == 1) && s.first();
						if (t) {
							q.setHtml(t.o.innerHTML);
							// init childs node
							q.select('>>').each_all(function () { igk.ajx.fn.initnode(this.o); });
							igk.publisher.publish("sys://doc/changed", igk.publisher.createEventData());
							return;
						}
					}
					q.o.appendChild(d);
					igk.publisher.publish("sys://doc/changed", igk.publisher.createEventData());
				});
			});
		}
	}, {
		desc: 'used no build xsl node',
		create: function (xml, xsl) {
			if ((!xml) || (!xsl)) {
				throw ('Arguments not valid');
			}
			var n = igk.createNode('div');
			n.addClass("igk-xsl-node");
			n.setAttribute("igk:xslt-data", igk.JSON.convertToString({ xml: xml, xsl: xsl }));
			return n;
		}
	});

})();

(function () {

	function __update(v) {
		var q = this;
		var w = igk.getNumber(q.getComputedStyle("width"));
		var h = igk.getNumber(q.getComputedStyle("height"));
		// w , h , can be used in the expression because of the eval
		if (w && h) {
			var exp = eval('obj=' + v + ';');
			this.setCss(exp);
		}
	}
	// fix position
	function __init(n, v) {
		if (!n) {

		}
		var q = this;
		var g = null;
		function __runfix() {
			__update.apply(q, [v]);
		};
		igk.appendProperties(q, {
			"fix": {
				"update": __runfix
			}
		});
		// igk.ready(function(){__update.apply(q,[v])},0);
		igk.winui.reg_event(window, "resize", g = function () {

			// if(!q.getpresentOnDocument()){
			// igk.winui.unreg_event(window,"resize",g);
			// }


			__update.apply(q, [v]);
		});
		q.fix.update();
		igk.publisher.register("sys://html/doc/scroll", __runfix);
	}

	igk.ctrl.registerAttribManager("igk-attr-fix-position", { desc: 'used to correct element position according to expression' });
	igk.ctrl.bindAttribManager("igk-attr-fix-position", __init);
})();

// AJX URI LOADER
(function () {
	igk.system.createNS("igk.thread", {
		wait: function (t, tg, fc) {// used to as sync an action
			if (t <= 0) {
				fc.apply(tg);
				return;
			}
			var s = 1;
			// var m=100;
			function _th_wait_fc() {
				if (s)
					s = 0;
				fc.apply(tg);
			};
			setTimeout(_th_wait_fc, t);
		}
	});
	igk.winui.initClassControl("igk-ajx-uri-loader", function () {
		var u = this.getAttribute("igk:href");
		var a = this.getAttribute("igk:append");
		var self = this;
		if (u) {
			// var lw = this.add("div").addClass("igk-google-line-waiter");
			var q = $igk(this.o.parentNode);
			var ta = q.select(a).first();
			igk.ajx.get(u, null, function (xhr) {
				if (this.isReady()) {
					var x = this;
					this.source = q;
					igk.thread.wait(0, xhr, function () {
						if (a) {
							if (ta) {

								igk.ajx.fn.replace_or_append_to(ta).apply(x, [xhr]);
							} else
								igk.ajx.fn.replace_or_append_to(q).apply(x, [xhr]);
						}
						else {
							q.setHtml(xhr.responseText).init();

						}
						// lw.remove();
						self.remove();
					});
				} else if (xhr.readyState == 4) {
					// lw.remove();
					self.remove();
				}
			}, true);

		}
	},
		{
			desc: 'used load uri content in ajx context'
		});
})();

// (function(){
// var sm_shader=null;
// var mg_currentProgram=0;
// igk.system.createNS("igk.html5.drawing.webgl",{
// shader: function(){// singleton shader object
// if(sm_shader){

// return sm_shader;
// }
// if(this instanceof igk.object){	
// return  new igk.html5.drawing.webgl.shader();
// }


// igk.appendProperties(this,{
// loadAndCompile: function(gl,arraylistVShader,arraylistFShader){
// var p=null; // program data
// return null if failed
// var vshader=[];
// var fshader=[];

// var vertexShader=null;// gl.createShader(gl.VERTEX_SHADER);
// var fragmentShader=null;// gl.createShader(gl.FRAGMENT_SHADER);

// gl.shaderSource(vertexShader,vertextShaderText);
// gl.shaderSource(fragmentShader,fragShaderText);

// gl.compileShader(vertexShader);

// if(!gl.getShaderParameter(vertexShader,gl.COMPILE_STATUS)){

// return;
// }

// gl.compileShader(fragmentShader);
// if(!gl.getShaderParameter(fragmentShader,gl.COMPILE_STATUS)){

// return;
// }


// var program=gl.createProgram();
// var v_str="";
// for(var i=0; i < arraylistVShader.length; i++){
// v_str=arraylistVShader[i];
// vertexShader=gl.createShader(gl.VERTEX_SHADER);
// gl.shaderSource(vertexShader,v_str);
// gl.compileShader(vertexShader);

// if(!gl.getShaderParameter(vertexShader,gl.COMPILE_STATUS)){



// return;
// }
// vshader[i]=vertexShader;

// gl.attachShader(program,vertexShader);
// }

// for(var i=0; i < arraylistFShader.length; i++){

// v_str=arraylistFShader[i];
// fragmentShader=gl.createShader(gl.FRAGMENT_SHADER);
// gl.shaderSource(fragmentShader,v_str);
// gl.compileShader(fragmentShader);
// if(!gl.getShaderParameter(fragmentShader,gl.COMPILE_STATUS)){

// return;
// }
// fshader[i]=fragmentShader;

// gl.attachShader(program,fragmentShader);

// }



// link program
// gl.linkProgram(program);
// if(!gl.getProgramParameter(program,gl.LINK_STATUS)){

// return;
// }

// gl.validateProgram(program);
// if(!gl.getProgramParameter(program,gl.VALIDATE_STATUS)){

// return;
// }


// p=new function(){
// var m_gl=0;
// igk.appendProperties(this,{
// program: program,
// vertextShaders:vshader,
// fragmentShaders:fshader,
// useIt:function(gl){			
// gl.useProgram(this.program);
// if(mg_currentProgram!=this){
// m_gl=0;
// }else
// m_gl=gl;
// },
// setUniform:function(name,v){
// if(!m_gl)
// return;
// }
// });
// };

// return p;
// },
// toString:function(){return "igk.html5.drawing.webgl.shader"; }
// });
// sm_shader=this;
// return sm_shader;
// }
// });


// })();


// --------------------------------------------------------------------------------------------------------
// HTML5
// --------------------------------------------------------------------------------------------------------

// --------------------------------------------------------------------------------------------------------
// AUDIO CONTEXT
// --------------------------------------------------------------------------------------------------------
(function () {
	igk.system.createNS("igk.html5", {
		audioContext: window.AudioContext || window.webkitAudioContext,
	});
	var _no_context = "no-audio-context";
	igk.system.createNS("igk.html5.audioBuilder", {
		getComponents: function () {
			if (!igk.html5.audioContext)
				return _no_context;
			var s = new igk.html5.audioContext();
			var i = 0;
			var o = [];
			var m = "";
			if (s) {
				for (var a in s) {
					if (typeof (s[a]) == "function" && /^create/.test(a)) {
						if (i)
							m += ",";
						m += a.substring(6);
						i = 1;
					}
				}
			}
			return m;
		},
		getComponentsDescriptionFile: function () {

			function _getargs(nn) {
				var args = {
					"Buffer": [2, 20500, 41100],
					"PeriodicWave": [new Float32Array(3), new Float32Array(3)],
					"MediaStreamSource": [null],
					"MediaElementSource": [igk.createNode("audio")]
				};
				if (nn in args)
					return args[nn];
				return [];
			};
			var r = igk.html5.audioBuilder.getComponents();
			if (r == _no_context) return;
			var s = new igk.html5.audioContext();
			var t = r.split(',');
			var m = igk.createNode("webaudiodef");
			var k = null;
			for (var i = 0; i < t.length; i++) {

				k = m.add("node");
				k.setAttribute("Name", t[i]);
				k.setAttribute("desc", "");

				// load properties
				try {
					var d = s["create" + t[i]].apply(s, _getargs(t[i]));

					for (var jj in d) {
						k.add("property").setAttribute("name", jj);
					}

				} catch (e) {
					console.debug("error " + e);
				}
			}

			igk.io.file.download("application/xml", "data.xml", m.render());
			return 1;
		}
	});
})();

//----------------------------------------------------------------------------------------------------------
// WEBGLContext
//----------------------------------------------------------------------------------------------------------
(function () {
	igk.system.createNS("igk.html5", {
		freeWebGLContext: function (gl) { // free webgl context
			gl = gl || m_gl;
			if (gl) {
				c = gl.getExtension('WEBGL_lose_context');
				if (c) { c.loseContext(); }
			}
			if (m_gl == gl)
				m_gl = null;
		},
		createWebGLContext: function (c) {
			return c.getContext("webgl") || c.getContext('experimental-webgl');
		}
	});
})();


// --------------------------------------------------------------------------------------------------------
// HTML5 GAME SURFACE
// --------------------------------------------------------------------------------------------------------
(function () {
	var m_contexts = [];
	var _params = null;
	var m_gl = 0;
	var _def = 0;
	var _pause = 0;
	// var _log =0;

	function _Run(ol) {
		// external game listener;
		var gl = ol.gl;
		var fc = igk.animation.getAnimationFrame();
		var e = 0;
		var loop = function () {
			if (!_pause) {
				ol.listener.tick(gl);
				e = gl.getError();
				if (e) {
					throw "render failed cause of an error " + e;
				}
			}
			fc(loop);
		};
		fc(loop);
	};

	igk.system.createNS("igk.html5.drawing", {
		getContext: function () {
			return m_contexts;
		},
		gameContextListener: function () {
			// base class of game rendering context
			var _p_data = _params;
			var _canvas = _p_data.canvas;
			var _self = this;
			var _p_settings = null;

			$igk(_canvas).addClass("igk-game-surface");


			igk.winui.reg_event(window, 'resize', function () {
				_self.updateSize(_p_data.gl);
				_self.raise("sizeChanged");
			});


			_params = null;
			igk.appendProperties(this, {
				toString: function () {
					return "[class: igk.html5.drawing.gameContextListener]";
				},
				raise: function (n) { // raiseEvent:  game Component event
					var e = { target: this };
					if (arguments.length > 1) {
						e.param = arguments[1];
					}
					igk.publisher.publish("igk.bge://" + n, e);
					return this;
				},
				on: function (n, callback) {
					igk.publisher.register("igk.bge://" + n, callback);
					return this;
				},
				setBgColor: function (bg) {
					_bgCl = bg;
				},

				// gl function				
				initContext: function (gl) {
				},
				initGame: function (gl) {
					// initGame
				},
				render: function (gl) {
					gl.clearColor(_bgCl.r, _bgCl.g, _bgCl.b, _bgCl.a);// 											
					gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);
				},
				updateWorld: function (gl) {

				},
				loadContent: function (gl) {
				},
				unloadContent: function (gl) {
				},
				updateSize: function (gl) {
				},
				tick: function (gl) {
					this.updateWorld(gl);
					this.render(gl);
				},
				capture: function (u, w, h) {
					_pause = 1;


					var c = $igk(this.canvas).getParent().select(".scene").first();
					var v_bck = $igk(this.canvas).o.style;

					$igk(this.canvas).setCss({ position: "fixed" });
					this.updateSize(_def.gl, w, h);
					this.tick(_def.gl);
					var i = this.canvas.toDataURL();


					this.updateSize(_def.gl, null, null);
					$igk(this.canvas).o.style = v_bck;// restore the previous style setting					
					_pause = 0;// remove pause				
					if (u) {
						igk.ajx.post(u, "data=" + i, igk.ajx.fn.none);
					}
					return i;
				},
				fullscreen: function () {

					var c = $igk(this.canvas);
					var fc = igk.fn.getItemFunc(c.o, "requestFullScreen");
					if (fc) {
						this.raise("fullsizeRequest");
						fc.apply(c.o);
					}
				}


			});

			igk.defineProperty(this, "canvas", { get: function () { return _canvas; } }); // get canvas sources
			igk.defineProperty(this, "gl", { get: function () { return _p_data.gl; } }); // get gl context
			igk.defineProperty(this, "settings", { get: function () { return _p_settings; } }); // get setting of this

			_currentGame = this;
		},
		FreeContext: igk.html5.freeWebGLContext,
		CreateContext: function (q, listener) {
			// init node
			var canvas = q.o;// document.getElementById("game-surface");
			var _ol = listener || q.getAttribute("igk-webgl-game-attr-listener");
			var gl = igk.html5.createWebGLContext(canvas);
			// init scene properties
			var _m = $igk(q.o.parentNode).add("div").addClass("scene dispn posfix"); // store scene properties
			var _mcl = _m.getComputedStyle("backgroundColor");





			var _clbg = igk.system.colorFromString(_m.getComputedStyle("backgroundColor")).toInt();// String(); 

			// _m.remove();
			// 	return 0;

			if (!gl) {
				if (igk.navigator.isSafari())
					return 0;

				ctx = canvas.getContext("2d");
				ctx.clearRect(0, 0, canvas.width, canvas.height);
				ctx.fillStyle = igk.system.colors.cornflowerblue;
				ctx.fillRect(0, 0, canvas.width, canvas.height);
				ctx.fillStyle = igk.system.colors.darkblue;
				ctx.font = "12pt courrier new";
				ctx.fillText(
					"Sorry but your browser does not support WEBGL. try with another one",
					0,
					(canvas.height / 2),
					100
				);
				return 0;
			}
			// delete gl;
			// igk.show_prop(gl);
			igk.winui.reg_event(window, "pagehide", function () {
				gl = null;
				q.remove();
				});

			if (!canvas.getAttribute("width"))
				canvas.width = 200;
			if (!canvas.getAttribute("height"))
				canvas.height = 200;
			_params = {
				canvas: q.o,
				gl: gl,
				enable_error_handler: 1
			};

			function _createOl(fc) {
				return new fc();
			};



			if (_ol) {
				switch (typeof (_ol)) {
					case 'string': {
						var ns = igk.system.getNS(_ol);
						if (typeof (ns) == 'function') {
							// parameter to pass to init method
							_ol = _createOl(ns);
							// _params={
							// canvas: q.o,
							// gl:gl,
							// enable_error_handler:1
							// };
							// _ol=new ns();


						} else {
							// no definition class found
							console.debug("/!\\ No game definition class found. [ " + _ol + " ]");
							_ol = null;
						}
					}
						break;
					case "function":
						_ol = _createOl(_ol);
						break;


				}
			} else {
				console.error("/!\\ igk-webgl-game-attr-listener not provided. ");
				_ol = 0;
				// return 0;
			}



			var cl = igk.system.colors.toFloatArray(_clbg);
			if (!_ol) {

				// gl.clearColor(1.0,0.55,0.55,1.0);
				gl.clearColor(cl.r, cl.g, cl.b, cl.a);

				gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);

				return 0;
			} else {
				// init _ol
				gl.TRUE = 1;
				gl.FALSE = 0;

				_ol.setBgColor(cl);
				_ol.initGame(gl);// init game environment
				_ol.updateSize(gl);// 
				_ol.initContext(gl);
				_ol.loadContent(gl);
				// data attached
				var ol = {
					listener: _ol,
					gl: gl
				};
				m_gl = gl;

				igk.winui.reg_event(window, "pagehide", function () {
					igk.html5.drawing.FreeContext(gl);
				});
				_def = ol;
				_Run(ol);
			}
			return ol;
		}
	});


	igk.winui.initClassControl("igk-webgl-game-surface", function () {
		var q = this;

		setTimeout(function () {

			var n = q.add(igk.createNode('canvas'));
			var g = igk.html5.drawing.CreateContext(n, q.o.getAttribute("igk-webgl-game-attr-listener"));
			if (!g) {
				console.log('/!\\ can not attach webgl to surface . something bad happen');
				n.remove();
				return;
			}
			q.add(n);
			g.listener.canvas.setAttribute("id", q.o.getAttribute("id"));
			q.o.removeAttribute("id");
		}, 500);


	}, {
		desc: 'webgl graphic surface',
		create: function (listener) {
			var n = igk.createNode('canvas');
			n.addClass('igk-webgl-game-surface');
			if (listener)
				n.setAttribute("igk-webgl-game-attr-listener", listener);
			return n;
		}
	}
	);
})();

//--------------------------------------------------------------------------------------------------------
//media management
//--------------------------------------------------------------------------------------------------------
(function () {

	function __media_setting(q, b) {
		// q.setCss({display:"none !important"});
		q.setAttribute("style", "display:none !important");
		var m_inf = {};
		function __media_change() {
			console.debug("media change");

			var c = igk.css.getMediaType();



			if ((c != 'global') && !(c in m_inf)) {
				var d = document.createElement("div");
				w = igk.getNumber(q.getComputedStyle("width"));
				h = igk.getNumber(q.getComputedStyle("height"));
				d.setAttribute("width", w);
				d.setAttribute("height", h);
				$igk(d).addClass(q.o.className + " " + c).setCss({ width: w + "px", height: h + "px", "backgroundImage": "url('" + b + "&w=" + w + "&h=" + h + "')" });

				$igk(d).setConfig("igk:callAttribBindingData", 1);
				$igk(d).setConfig("igk:initnode", 1);

				q.o.parentNode.appendChild(d);
				m_inf[c] = d;

			}
		};

		igk.appendProperties(this, {
			change: __media_change
		});
		igk.publisher.register(igk.publisher.events.mediachanged, this.change);
	}

	igk.winui.initClassControl("igk-winui-media-img", function () {
		var q = this;
		var b = q.getAttribute("igk:base");
		var c = new __media_setting(q, b);
		c.change();
	});
})();



// -----------------------------------------------------
// init all parallax
// -----------------------------------------------------

(function () {
	var parallax = [];

	function __i_parallax() {
		var q = this;
		var p = q.getAttribute("igk:param");

		q.setCss({
			backgroundImage: "url('" + p + "')",
			// backgroundRepeat: "no-repeat",
			backgroundPosition: "center"
			// backgroundSize:"100% auto"
		});
		var m = new function () {
			igk.appendProperties(this, {
				callback: function (evt) {
					var of; // off in percent
					var m_t = $igk(evt.target).getscrollMaxTop();
					var v_c = q.o;
					var c_h = q.o.scrollHeight;
					if (m_t > 0)
						of = parseInt((evt.target.scrollTop / m_t) * 100.0);
					else {
						of = 0;
					}

					// firefox raise warning for scrolling effect position
					var p = Math.round((of / 200.0) * 10) * 2;
					var cl = "igk-pos-" + p;

					if (!(new RegExp(cl + "$")).test(q.o.className)) {
						q.rmAllClass("^igk-pos-(.)+$")
							.addClass(cl);

					}
					// else{

					// }
					// q.setCss({backgroundPosition: "center "+(-of)+"px"});
					// }

				}
			});
		};


		igk.publisher.register('sys://html/doc/scroll', m.callback);
	};


	igk.winui.initClassControl("igk-winui-parallax", __i_parallax);

})();




// (function(){
// var p= igk.getParentScript();
// igk.ready(function(){
// \$igk(p).select('.igk-winui-parallax').each_all(function(){
// var q = this;

// // --------------------------------------------------------------
// // style fixed followed box 
// // --------------------------------------------------------------



// var m = new function(){
// igk.appendProperties(this, {
// callback:function(evt){
// // return;




// var of; // off in percent
// var m_t = \$igk(evt.target).getscrollMaxTop();
// var v_c = q.o;
// var c_h = q.o.scrollHeight;






// if (m_t>0)
// of = parseInt((evt.target.scrollTop/m_t)*100.0);
// else{
// of = 0;
// }

// // if (of==0){

// // // q.setCss({backgroundPosition: "center"});	
// // }else{

// // firefox raise warning for scrolling effect position
// var p = Math.round((of/200.0)*10)*2;
// var cl = "igk-pos-"+p;

// if ( !(new RegExp(cl+"$")).test(q.o.className)){
// q.rmAllClass("^igk-pos-(.)+$")
// .addClass(cl);

// }
// // else{

// // }
// // q.setCss({backgroundPosition: "center "+(-of)+"px"});
// // }

// }
// });
// };

// igk.publisher.register('sys://html/doc/scroll', m.callback);


// // igk.winui.reg_event(window, 'scroll', function(){

// // });
// });

// // igk.show_prop(p);
// });// end ready

// })();





// close managment
// (function(){
// igk.ready(function(){
// igk.dom.body().onunload=function(){

// };
// window.onunload = function(){

// };
 
// igk.ajx.post("http://192.168.0.50/igkdev/testapi/clien_exit");
// });


// });
// })();


// (function(){
// var s=igk.system.regex.split("\\s*([^,]+)[\\s,]*","cubic-bezier(0,0,1,1),cubic-bezier(0,0,1,1)");

// var s=igk.system.regex.split("([^,(]+(\\(.+?\\))?)[\\s,]*","cubic-bezier(1,3,5),width");

// })(); 
// var count = 0;
// igk.publisher.register("sys://doc/changed",function(p){


// count++;

// }) 



(function () {

	// igk.system.createNS();

	igk.winui.initClassControl("igk-winuin-jsa-ex", function () {

		if (igk.canInvoke()) {
			this.rmClass("dispn");
			var q = this;
			var c = this.getAttribute("igk:data");

			var p = igk.JSON.parse(c);
			this.reg_event("click", function () {
				igk.invoke(p.m, p.args);
			});
		}
		else
			this.remove();
	});

})();


(function () {
	var dialgs = [];
	var sk = 0;// for key
	function __close(evt) {
		this.remove();
	}
	function n_dialog(g) {
		var q = this;
		q.options = null;
		q.closeByEscape = 0;
		q.closeBySubmit = 1;
		igk.appendProperties(this, {
			mediachanged: function (e) {

				q.initLoc();
			},
			close: function (evt) {
				__close.apply(g, evt);
				var m = 0;
				while ((m = dialgs.pop())) {
					if (m == q)
						break;
				}
			},
			showOpts: function (evt) {
				if (q.options) {
					q.options.toggleClass("dispn");
				}
			},
			initLoc: function () {
				var W = igk.getNumber(g.getComputedStyle("width"));
				var w = -(W / 2);
				var h = (-igk.getNumber(g.getComputedStyle("height")) / 2);


				if (W != igk.winui.screenSize().width) {
					g.setCss({
						position: "fixed",
						top: '50%',
						left: '50%',
						marginLeft: w + "px",
						marginTop: h + "px"
					});
				} else {
					g.setCss({
						position: "absolute",
						top: 'auto',
						left: 'auto',
						marginLeft: "auto",
						marginTop: "auto"
					});
				}
			},
			subfunc: function (frm) {
				return function (evt) {
					evt.preventDefault();

					igk.ajx.postform(frm.o, frm.getAttribute("action"), function (xhr) {
						if (this.isReady()) {
							if (q.closeBySubmit)
								__close.apply(g, evt);
							igk.ajx.fn.replace_or_append_to_body.apply(this, [xhr]);

						}
					});

				};
			}
		});
	}
	function __press(evt) {
		if (dialgs.length > 0) {
			switch (evt.keyCode) {
				case 27: // escape
					var m = dialgs[dialgs.length - 1];
					if (m.closeByEscape) {
						m.close();
						evt.preventDefault();
						evt.stopPropagation();
					}
					break;
			}
		}
	};

	igk.winui.initClassControl("igk-winui-dialogbox", function () {
		var g = new n_dialog(this);

		// attach properties
		var v_d = this.getAttribute("igk:data");
		if (v_d) {
			var e = igk.JSON.parse(v_d);
			for (var i in g) {
				if (typeof (g[i]) == "function") continue;
				g[i] = e[i];
			}
		}


		this.select(".cls").first().reg_event("click", g.close);
		var o = this.select(".opts").first().reg_event("click", g.showOpts);
		g.options = this.select(".d-opts").first();// dialog options
		var frm = this.select("form").first();
		if (frm) {
			frm.reg_event("submit", g.subfunc(frm));

			// diseable pression on input to enter data
			frm.select("input").reg_event("keyup keydown keypress", function (evt) {
				if (evt.keyCode == 13) {
					evt.preventDefault();
					return false;
				}
			});
			// igk.getKeys(evt);

			// }
			// ));
		}
		if (g.options) {
			g.options.addClass("dispn");
			g.options.select("a").each_all(function () {
				var r = this.getAttribute("href");
				switch (r) {
					case "::close":
						this.reg_event("click", function (evt) { evt.preventDefault(); g.close(evt); });
						break;
				}
			});
		} else {
			o.addClass("dispn");
		}
		this.addClass("posfix ");
		// .setCss({
		// top:'50%',
		// left:'50%',
		// marginLeft: (-igk.getNumber(this.getComputedStyle("width"))/2)+"px",
		// marginTop: (-igk.getNumber(this.getComputedStyle("height"))/2)+"px",
		// });
		g.initLoc();
		igk.publisher.register(igk.publisher.events.mediachanged, g.mediachanged);
		if (!sk) {
			igk.winui.events.regKeyPress(__press);
		}
		dialgs.push(g);
	});

})();




(function () {
	// remove all item if item visible
	function _rm_js_hide() {

		igk.qselect(".igk-js-hide").each_all(function () {
			this.rmClass("igk-js-hide");
		});
	}
	igk.ready(_rm_js_hide);
	igk.publisher.register(igk.evts.dom[1], function (d) {
		if (d.evt.target == igk.dom.body().o) {
			_rm_js_hide();
		}
	});


})();



// winui-toast
(function () {
	var c_toast = null;
	function __transition_end(evt) {
		if ((c_toast == null) || (evt.target != c_toast.o) || (evt.propertyName != "opacity"))
			return;


		// c_toast.remove();		
		igk.winui.unreg_event(c_toast, "transitionend", __transition_end);
		c_toast.remove();
		c_toast = null;
	};
	function __init_toast() {
		if (c_toast) {
			igk.winui.unreg_event(c_toast, "transitionend", __transition_end);
			c_toast.remove();

		}
		var i = this.getHtml();

		if (this.getAttribute("noHide"))
			return;

		this.setCss({
			opacity: 1.0
		});
		var q = this;
		var _fo = 0; // fadin out
		c_toast = q;
		setTimeout(function () {
			if (_fo) return;
			igk.winui.reg_event(q, "transitionend", __transition_end);
			q.setCss({ opacity: 0.0 });
			_fo = 1;
		}, 2000);
	};
	igk.system.createNS("igk.winui.controls", {
		toast: function () {
			//toast constructor
		}
	});

	igk.winui.controls.toast.show = function (m, type) {
		var d = igk.createNode("div").addClass("igk-winui-toast");
		if (type) {
			d.addClass(type);
		};
		d.setHtml(m);
		igk.dom.body().add(d);
		d.init();
	};

	igk.winui.controls.toast.initDemo = function () {
		igk.winui.controls.toast.show("Toast Demonstration");
	};
	igk.winui.initClassControl("igk-winui-toast", __init_toast);
})();



(function () {
	igk.winui.initClassControl("igk-framebox-dialog", function (n, s) {
		var d = igk.JSON.parse(this.getAttribute("data"));
		if (!d) {
			d = { w: null, h: null };
		}
		this.select(".framebox-title").each(function () {
			igk.ctrl.selectionmanagement.disable_selection(this.o);
		});
		igk.winui.framebox.init(this.o, d.w, d.h);
	});
})();


(function () {
	igk.system.createNS("igk.winui.stateBtn", {
		init: function (q) {
			var o = $igk(q.o.parentNode).select('input').first().o;
			q.reg_event("click", function (e) {
				e.stopPropagation();
				e.preventDefault();
				o.click();
			});
		}
	});
})();




// ajx update component
(function () {
	function __init_function() {
		var self = this;
		var s = this.getAttribute("igk:target");
		var tv = this.select(">>").first();
		var _initl = [];

		var fc = function (t) {
			var c = this;
			if (t.target == self.o) {
				for (var i = 0; i < _initl.length; i++) {
					_initl[i].init();
				}
				igk.publisher.unregister("sys://node/init", c.caller);
			}
		};
		igk.publisher.register("sys://node/init", fc);


		if (s && tv) {
			this.remove();
			var m = { d: s };
			var q = eval(s + ";");

			if (q) {
				var g = $igk(q);

				if (g.isSr()) {
					var i = 0;
					g.each_all(function () {
						// 

						var _ie = tv; // insert element
						if (i) {

							_ie = tv.clone();

						}
						this.o.parentNode.replaceChild(
							_ie.o,
							this.o
						);


						_initl.push(_ie);
						// _ie.init();

						i = 1;
					});
				} else {
					g.o.parentNode.replaceChild(
						tv.o,
						g.o
					);
				}
			}
		}
	}

	igk.winui.initClassControl("igk-ajx-update-view", __init_function);

})();


(function () {
	// clone the repsonse and append to cibling
	function __init_function() {
		var self = this;
		var s = this.getAttribute("igk:target");
		var tv = this.select(">>");

		if (!s || (tv.getCount() == 0))
			return;
		var q = eval(s + ";");
		if (q) {
			if (q.isSr()) {
				q.each_all(function () {
					var m = this;
					tv.each_all(function () {
						m.add(this.clone());
					});

				});
			} else {
				tv.each_all(function () {
					q.add(this.clone());
				});
			}
		}
	}

	igk.winui.initClassControl("igk-ajx-append-view", __init_function);
})();

//---------------------------------------------------------------------------
// igk-svg-lst: svg list image
// igk-svg-lst-i: svg list item
//---------------------------------------------------------------------------
(function () {
	var m_item = {};

	// svg list view item 
	function __init_svg_i() {
		var n = this.getAttribute("igk:svg-name");
		if (m_item[n]) {
			// replace 
			var g = $igk(m_item[n]).clone();
			var p = this.o.parentNode;

			$igk(p).addClass("igk-svg-host");
			p.replaceChild(g.o, this.o);
			if (p.getAttribute("title"))
				$igk(p).qselect("svg > title").remove();

		} else {
			console.debug("item not found :" + n);
		}
	};
	function __initlist() {

		for (var i = 0; i < this.o.childNodes.length; i++) {
			var j = this.o.childNodes[i];
			m_item[j.tagName.toLowerCase()] = $igk(j).select("svg").first();
		}
		this.remove();
	};
	// svg list init svg list
	function __init_svg_l() {

		igk.dom.body().select(".igk-svg-lst").each_all(__initlist);
		// var s = igk.dom.body().add("style");
		// s.o["type"]="text/css";
		// s.o["class"]="igk-svg-lst-manager";
		// s.setHtml("\/*igk.js : script init svg *\/ .igk-svg-lst svg, .igk-svg-lst svg.no-visibible{width:1px; height:1px; }");
		// s.remove();
	}

	igk.ready(__init_svg_l);

	igk.winui.initClassControl("igk-svg-lst", function () {

		__initlist.apply(this);

	});

	igk.ajx.fn.initBeforeReady(function () {
		$igk(this).select(".igk-svg-lst").each_all(__initlist);
	});
	igk.winui.initClassControl("igk-svg-lst-i", __init_svg_i);

	igk.winui.createSVGLi = function (n) {
		if (m_item[n]) {
			var g = $igk(m_item[n]).clone();
			return g;
		} else {
			console.error("[igk] - svg-lst-item <<" + n + ">> not found");
		}
	};
	// igk.ajx.fn.registerNodeReady(function(){	

	// $igk(this).select(".igk-svg-lst").each_all(__initlist);
	// });
})();


// when server sending ajx response  . css can be added we need to reload the css target
(function () {
	igk.ajx.fn.registerNodeReady(function () {
		if (!this.tagName || this.tagName.toLowerCase() != "style")
			return;

		// $igk(this).select("style").each_all(function(){
		var f = $igk("#" + $igk(this).getAttribute("igk:from")).first();
		if (f) {
			f.setHtml(this.innerHTML);
			$igk(this).remove();
		}

		// });
	});

})();


// igk-iframe
(function () {
	var iframes = [];
	function __init_iframe() {
		var f = igk.createNode("iframe");
		igk.dom.copyAttributes(this.o, f.o);// .copyAttributess(this);
		f.reg_event("error", function () {
			// alert("error");
		}).reg_event("load", function () {
		});
		igk.dom.replaceChild(this.o, f.o);// .replaceNode(f);
	};
	igk.ready(function () {
		igk.dom.body().select("igk-iframe").each_all(__init_iframe);
	});
})();

// 
// igk-ptr-btn
// 
(function () {

	function __init_ptr_btn() {
		var q = this;
		var g = igk.JSON.parse(q.getAttribute("igk:data"));
		var u = 0;
		if (g) {
			if (typeof (g) == 'string')
				u = g;
			else
				u = g.uri;
		}
		this.reg_event('click', function (e) {
			if (u) {
				ns_igk.winui.print(u);
			} else {
				igk.winui.reg_event(window, 'beforeprint', function (e) {

					console.debug("before print");
					console.debug(e);
				});
				window.print();
			}
			igk.stop_event(e);

		});
	};

	igk.winui.initClassControl('igk-ptr-btn', __init_ptr_btn);

})();



(function () {
	// igk-ajx-replace-source : used to replace ajx cibling context
	// igk:data = data used to select the replacing zone
	igk.winui.initClassControl('igk-ajx-replace-source', function () {
		console.debug("replaceiont :::: ");
		var g = igk.ajx.getCurrentXhr();
		var s = g ? g.source : null;
		if (!s) return;
		var d = this.getAttribute("igk:data");
		var sl = s.select(d);
		var o = this.getHtml();
		sl.setHtml(o).init();
		this.remove();
	});
})();

// (function () {
// igk-ajx-replace-ciblibk : used to replace ajx cibling context
// igk:data = data used to select the replacing zone
// igk.winui.initClassControl('igk-ajx-replace-source', function () {
// var o = this.getHtml();
// var f = this.o.firstChild;
// var d = this.getAttribute("igk:data");
// $igk(d).each_all(function(){
// this.insetBefore(
// });
// this.remove();
// });
// })();








// popup menu guide
(function () {
	var doc_e = 0;// get if reg doc event for closing
	var m_d = 0;
	igk.winui.initClassControl("igk-winui-popup-menu", function () {
		var attr = this.getAttribute("igk:target");

		var c = 0;
		if (attr)
			c = $igk(attr).first();
		if (!c) {
			this.setCss({ "display": "none" });
			this.remove();
			return;
		}


		var d = igk.createNode("div");// the menu guide
		d.addClass("igk-guide-menu posfix igk-sm-only igk-xsm-only menu");
		var dc = igk.dom.body();
		var initial = 0;

		this.reg_event("click", function (evt) {
			if (!initial) {
				dc.prepend(d);
				if (c) {
					d.setHtml("<ul>" + c.getHtml() + "</ul>");

				}
				initial = 1;
			}

			evt.stopPropagation();
			evt.preventDefault();
			m_d = d;
			d.toggleClass("igk-show");
		});
		if (!doc_e) {
			igk.winui.reg_event(document, "click", function () {
				// remove class anyway if click on document
				if (m_d && m_d.supportClass("igk-show")) m_d.rmClass("igk-show");
			});
			doc_e = 1;
		}

	});
})();


(function () {

	function __render_xml_view_tag(q, n) {
		var tab = [];
		var s = 0;
		var m = 0;
		var tb = 0;
		var attrs = 0;

		tab.push({ n: n, p: q, l: 0 });


		while ((s = tab.pop())) {

			if (s.e) {
				console.debug("end");
				m = igk.createNode("div").addClass("l");
				m.add("span").setHtml("&lt;");
				m.add("span").addClass("tag").setHtml(s.n.tagName.toLowerCase());
				m.add("span").setHtml("/&gt;");
				s.p.add(m);
				continue;
				// return;
			}
			switch (s.n.nodeType) {
				case 1:
					if (s.n.tagName) {
						m = igk.createNode("div").addClass("l");
						m.add("span").setHtml("&lt;");
						m.add("span").addClass("tag").setHtml(s.n.tagName.toLowerCase());
						if (s.n.hasAttributes) {
							attrs = s.n.attributes;
							for (var ai = 0; ai < attrs.length; ai++) {
								var la = m.add("span").addClass("attr");
								la.add("span").addClass("n").setHtml(attrs[ai].name);
								la.add("span").setHtml("=");
								la.add("span").addClass("v").setHtml("\"" + attrs[ai].value + "\"");
							}
						}
						// s.n.getAttributes

						s.p.add(m);
					} else {
						m = igk.createNode("span");
						m.setHtml("NoTAG ");
						s.p.add(m);
					}
					var tb = s.n.childNodes;
					if ((tb.length == 0) || (s.n.innerHTML.trim() == "")) {
						m.add("span").setHtml("/&gt;");
					} else {
						m.add("span").setHtml("&gt;");
						var quote = m.add("quote");
						tab.push({ n: s.n, e: 1, p: m });
						for (var mm = tb.length - 1; mm >= 0; mm--) {
							tab.push({ n: tb[mm], p: quote, l: s.l + 1 });
						}
					}
					break;
				case 3:// text element

					// return;
					// m = igk.createNode("div");
					// m.add("span").setHtml("&lt;");
					// m.add("span").addClass("tag").setHtml(s.n.tagName.toLowerCase());

					break;
				case 8:
					var d = s.p.add("div");
					d.add("span").setHtml("&lt;").addClass("pr");
					d.add("span").setHtml(s.n.nodeValue);
					d.add("span").setHtml("&gt;").addClass("pr");
					break;

			}
		}
	}



	igk.winui.initClassControl("igk-xml-viewer", function () {
		if (this.getAttribute("igk:loaded"))
			return;
		var t = igk.dom.childToArray(this);

		this.setHtml("");
		for (var i = 0; i < t.length; i++) {
			// to xml view
			var b = t[i];
			__render_xml_view_tag(this, b);
		}
		console.debug(t.length);
		// $igk(this.o.childNodes[0]).remove();
	});
})();



// TODO: test extra attribute event - demonstration - hold click with BALAFON javasript 
(function () {
	igk.system.createNS("igk.event", {
		stop: function (e) {
			e.preventDefault();
			e.stopPropagation();
		}
	});
	var list = { 
		"click": "touchOrClick",
		"doubleclick":"doubleTouchOrClick" 
	};

	igk.ctrl.registerAttribManager("[click]", { desc: "click property event" });
	igk.ctrl.registerAttribManager("[doubleclick]", { desc: "doubleclick property event" });
	igk.ctrl.bindAttribManager("[click]", function (m, n) {
		if (n == null) {
			return;
		}
		var fc = new Function(n);
		var q = this;
		this.reg_event("touchOrClick", function (event) {
			igk.event.stop(event);
			if (event.handle)
				return;
			fc.apply(q, [event]);
		});
	});

	igk.ctrl.bindAttribManager("[doubleclick]", function (m, n) {
		if (n == null) {
			return;
		}
		var fc = new Function(n);
		var q = this;
		this.reg_event("doubleTouchOrClick", function (event) {
			igk.event.stop(event);
			if (event.handle)
				return;
			fc.apply(q, [event]);
		});
	});

	function _initmethod(ms) {
		return function (m, n) {
			if (n == null) {
				return;
			}
			this.reg_event(ms, function (e) {
				if (e.handle)
					return;
				eval(n);
			});
		};
	};

	var mouseevents = ["mouseover", "mousedown", "mouseup"];
	for (var i = 0; i < mouseevents.length; i++) {
		var n = mouseevents[i];
		igk.ctrl.registerAttribManager("[" + n + "]", { desc: "click property event" });
		igk.ctrl.bindAttribManager("[" + n + "]", _initmethod(n));
	}



})();

(function () {
	var bind = function (n) {
		eval(n);
	};
	igk.ctrl.registerAttribManager("[ready]", { desc: "ready run load event" });
	igk.ctrl.bindAttribManager("[ready]", function (m, n) {
		if (n == null) {
			return;
		}
		var q = this;

		igk.ready(function () {
			bind.apply(q, [n]);
		});


	});
})();


// igk-js-button
(function () {
	igk.winui.initClassControl("igk-js-button", function () {
		var s = this.getAttribute("igk:js-action");
		if (s) {
			this.reg_event("click", function (e) {
				eval(s);
				e.preventDefault();
				e.stopPropagation();
			});
		}

	});
})();

// 
// igk-winui-more-view
// 
(function () {
	igk.winui.initClassControl("igk-winui-more-view", function () {
		var hide = 0;
		var rem = this.getAttribute("igk:hide");
		var q = this.o.nextSibling;
		var b = [];
		var t = this;
		t.on("click", function () {
			t.toggleClass("igk-hide");
			if (rem) {
				t.remove();
			}
		});
		return;


	});

})();



// igk-winui-js-logger
(function () {
	// desc used to create a div that will recieve log message .

	var _glogger = 0;
	function JSLogger(t) {
		var _editable = t.getAttribute("editable");
		// event info
		var _ei = {
			target: t.o,
			host: this
		};


		if (_editable) {

		}
		// events
		t.addEvent("logchanged", _ei);
		function _raiseEvent(n, p) {
			igk.winui.events.raise(t, n, p);
		};

		function _add(te, msg) {
			var g = t.add("div").addClass(te).setHtml(msg);
			_raiseEvent("logchanged");
			return g;
		};

		igk.appendProperties(this, {
			add: function () {
				var g = t.add.apply(t, arguments);
				return g;
			},
			clear: function () {
				t.setHtml("");
			},
			addi: function (msg) { // add info		
				return _add('igk-info', msg);
			},
			adde: function (msg) { // add error 				
				return _add('igk-danger', msg);
			},
			addw: function (msg) { // add error 
				return _add('igk-warning', msg);
			}
		});
	};

	igk.system.createNS("igk.log", {
		write: function (msg) {
			if (_glogger)
				_glogger.add("div").setHtml(msg);
			else
				console.debug(msg);
		},
		clear: function () {
			if (_glogger)
				_glogger.clear();

		}
	});

	igk.winui.initClassControl("igk-winui-js-logger", function () {
		if (_glogger) {
			return;
		}
		_glogger = new JSLogger(this);

	});
})();




(function () {
	//form utility
	igk.system.createNS("igk.winui.form", {
		postData:function(link, target){
			var q =  link.select(target).first();
			if (q){
				var lnk = link.o.getAttribute("href");
				var data = igk.winui.form.serialize(q.o, {});
				// console.debug(data);
				// console.debug(JSON.stringify(data));
				igk.ajx.post({
					"uri": lnk,
					"param":JSON.stringify(data),
					"contentType":"application/json"
				},
					null,null);

				//igk.ajx.post(lnk, JSON.stringify(data), null);// JSON.stringify(data));
			}
		},
		serialize: function(form, obj){
			var obj = obj || {};
			var frmData = new FormData(form);
			frmData.forEach(function (v, k) {
				if (k in obj) {
					if (!Array.isArray(obj[k])) {
						obj[k] = [obj[k]];
					}
					obj[k].push(v);
					return;
				}
				obj[k] = v;
			});
			return obj;
		},
		validate: function (t) {
			var q = $igk(igk.getParentScript()).select("^form").first();
			q.reg_event("submit", function (e) {
				for (var i = 0; i < t.length; i++) {
					if (!q.o[t[i]].value) {
						e.preventDefault();
						e.stopPropagation();
						return;
					}
				}

			});
		}
	});
})();

(function () {

	//no scroll item selections
	igk.ctrl.registerAttribManager("igk-winui-no-scroll", {
		"desc": "disable scrolling on item"
	});
	igk.ctrl.bindAttribManager("igk-winui-no-scroll", function (m, v) {
		if (!v || !igk.css.isItemSupport(["scrollBarWidth"]))
			return;
		this.setCss({ "scrollbarWidth": "none" });
	});

})();

(function () {
	//utility extension html to str
	var _d = 0;
	igk.system.createNS("igk", {
		toStr: function (v) {
			if (!_d) {
				_d = igk.createNode("div");
			}
			return _d.setHtml(v).o.innerText;
		}
	});

})();


(function (){
	// auto hide core component
	igk.winui.initClassControl("anim-autohide", function (c) {
		var q = this;
		q.reg_event("animationend", function (e) {
			if (e.animationName == "anim-autohide") {
				this.remove();
			}
		});
	});

})();

(function (){
	// col hover table
	var cellindex = -1;

	function rmStyle(q, index){
		q.qselect("tr td, tr th").each_all(function(){
			if (this.o.cellIndex == index){
				this.rmClass("hover");
			}
		});
	};
	function addStyle(q, index){
		q.qselect("tr td, tr th").each_all(function(){
			if (this.o.cellIndex == index){
				this.addClass("hover");
			}
		});
	};
	igk.ctrl.registerAttribManager("igk-table-col-hover",{		
	});
	var time_= 0;
	igk.ctrl.bindAttribManager("igk-table-col-hover", function(n,m){
		var q = this;
		this.on("mouseover", function(e){
			if (("cellIndex" in e.target)&&(/TD|TH/.test(e.target.tagName))){
				clearTimeout(time_);
				time_ = 0;
				if (cellindex !=e.target.cellIndex){
					rmStyle(q, cellindex);
					cellindex = e.target.cellIndex;
					addStyle(q, cellindex);
				}
			}
		}).on("mouseout", function(e){
			// console.debug("mouse out : "+e.target.tagName);
			if (/TD|TH/.test(e.target.tagName)){
				clearTimeout(time_);
				time_ = setTimeout(function(){
					rmStyle(q, cellindex);
					cellindex = -1;
				}, 500);
				
			}
		});
	});
 

})();


// -------------------------------------
// | igk:validation-data validation data
// -------------------------------------
(function(){
	igk.ctrl.registerAttribManager("igk:validation-data", {});
	igk.ctrl.bindAttribManager("igk:validation-data", function (t, v){
		var d = igk.createNode("div");
		d.addClass("data");
		var state = 0;
		var q = $igk(this);
		var data= JSON.parse(v);
		var form = q.select("^form").first();
		function _updateState(){
			state = 0;
			if (q.o.value.length > 0){
				if (q.o.value.length < data.length){
					state = 1;
				} else {
					for(var s in data.pattern){
						if (! (new RegExp(data.pattern[s])).test(q.o.value)){
							if (s.indexOf("*")!=-1){
								state = 2;
							} else {
								state = 1;
							}
							// console.debug("failed : "+s+ " = "+data.pattern[s]);
							break;
						}
					}
				}
			}
			// console.debug("state : "+state);
			switch(state){
				case 0:
					d.rmClass("igk-danger igk-warning igk-info");
					break;
				case 1:
					d.addClass("igk-danger");
					break;
				case 2:
					d.addClass("igk-warning");
					break;
				case 3:
					d.addClass("igk-info");
					break;
			}
		};
		if (form){
			form.on("submit igkFormBeforeSubmit", function(e){
				if (state==1){
					e.preventDefault();
					e.stopPropagation();
				}
			});

			if (data.matchTarget){
				var target = form.select(data.matchTarget).first();
				if (target){
					target.on("input", function(){						
						_updateState();
						if (q.o.value != target.o.value){
							state = 1;
						}
					});
				}
			}
		}
		q.on("input", function(){		
			_updateState();			
		}).insertAfter(d.o);
		_updateState(true);
	});
})();