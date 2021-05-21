//
"uses strict";

(function(){

function __cssbuilder(s, t){
	var m_styles = {};
	var q = $igk(t);
	var m_reloading=false;
	
function reloadStyle(m_styles){
	if (m_reloading)
		return;
	m_reloading = true;
	
	for(var i in m_styles)
	{
		var v  = s.o.style[i]+'';
		if (v == "undefined"){
		
			v = null;
		}
		m_styles[i].o.value =  v;
		
	}
	m_reloading = false;
}
function __click_func(evt){
	evt.preventDefault();
	var s = this.innerHTML;		
	
	if (igk.system.string.endWith(s.toLowerCase(), 'color'))
	{

	}
	
};
function __valkey(evt){
	if (evt.keyCode ==13)
	{
		var k = $igk(this).getAttribute("cssproperty");
		var e = {};
		e[k]=this.value;
		s.o.style[k] = this.value;
		igk.winui.style_editor.setProperties(e);
		reloadStyle();
	}
}
function __loadProp(s, pattern, m_styles){

	q.select('*').each(function(){
		this.unregister();
		return true;
	});
	//unregiter all
	q.setHtml("");
	if (pattern == "")
		pattern == null;
	var b = m_keys;
	var v_tab = m_tab;
	for(var i = 0; i< b.length; i++) //for (var i in v_tab){
	{
		if ((pattern !=null) && ( b[i].indexOf(pattern) == -1))
		{
			continue;
		}
		k = v_tab[b[i]];
		m = igk.createNode("li");
		m.setCss({paddingLeft:"10px", paddingRight:"10px"});
		m.add("a")
		.addClass("dispb")
		.setAttribute("href", "#")
		.reg_event("click", __click_func)
		.setHtml(""+k);
		
		var v  = s.o.style[k];
		if (v == "undefined")
			v = null;
		var input = m.add("input");
		
		
		m_styles[k] = input;
		
		input.addClass("igk-form-control dispb")
		.setAttribute("type", "text")
		.setAttribute("value", v)
		.setAttribute("cssproperty", k)
		.reg_event("keyup", __valkey)
		.reg_event("change", (function(k){
			var m_k = k;
			return function(evt){
				if (m_reloading)
					return;
			try{
				s.o.style[k] = this.value;
				reloadStyle();
			}
			catch(ex){
				igk.show_notify_error("Exception : ", k +" <br />"+ex);
			}
			}
		})(k));
		//t+= m.o.outerHTML;
		q.appendChild(m);
	}
}
	
	igk.appendProperties(this,{
		getCssList:function(){
			var t = "";
			var v_tab = igk.css.getProperties();
			var m =null;
			var k = null;
			var b = [];
			for (var i in v_tab){
			if (i == "csstext")
				continue;
				b.push(i);
			}
			igk.system.array.sort(b);
			m_keys = b;
			m_tab = v_tab;	
			__loadProp(s, null, m_styles);
		},
		loadPattern: function(pattern){
			__loadProp(s, pattern, m_styles);
		}
	});
}


var m_tbuilder;
igk.system.createNS("igk.cssbuilder",{
	init: function(){
		var b = igk.getParentScript();
		if (b) 
		{
			m_tbuilder = new __cssbuilder(igk.createNode("dummy"), b);
			m_tbuilder.getCssList();
		}
	},
	initsearch: function(){
		var q = $igk(igk.getParentScript());
		q.reg_event('keyup', function(){
			igk.cssbuilder.searchProperty(q.o.value.toLowerCase());
		});
		q.setCss({"border":'1px solid rgb(187, 187, 187)'});
	},
	searchProperty: function(pattern){
		if (m_tbuilder)
		m_tbuilder.loadPattern(pattern);
	},
	openwindow: function(uri){
		 var editor = window.open(uri, 'igk-wnd-coloreditor', 'width=400px, height=300px ,statusbar=no, adressbar=no, size=no'); 
		 editor['igk-parentWindow'] = window;
	},
	initmediaview: function(t){
		var d = $igk(igk.getParentScript());
		if (!d)return;
		
		pwnd = window['igk-parentWindow']  || window.opener;
		var frm = d.getParentForm();
		//window.parent;
		//igk.show_notify_prop(pwnd);
		//alert( "edu t "+ window['igk-parentWindow'] );
		function __initInfo(hwnd){
			hwnd.igk.publisher.register("config/mediachanged", function(e){
				d.setHtml("Media:"+e.mediaType);
				frm.clMediaType.value = e.mediaType;
				igk.winui.style_editor.reset();
			});
			var k =hwnd.igk.css.getMediaType();
			d.setHtml("Media:"+k);
			frm.clMediaType.value = k;
			
		}
		
		if (pwnd){
			__initInfo(pwnd);
			pwnd.unload = function(){
				alert("unloading");
			};
			pwnd.load = function(){
				alert("loading");
			};
			pwnd.complete = function(){
				alert("loading dd");
			};
		}
		else {	
			alert("no parent");

		
		}
	}
});


})();


(function(){
var frm  = null;  
var wnd  = null;
var item = null;
var m_sl = null;


function __init(){

frm = $igk(igk.getParentScript()).getParentForm();  
wnd = window['igk-parentWindow'] || window.opener;
if (wnd==null)
{
	//igk.show_notify_error("attention", "parentwindow not found");
	wnd = window;
	//return;
}
item = igk.createNode('dummy');
	$igk(frm.clSelector).reg_event('change', function(evt){
		__checkvalue();
	})
	.reg_event("keyup", function(evt){ __checkvalue(); });
}

function __checkvalue(){
	
	var t=frm.clSelector.value;
	m_sl=$igk(wnd.document).select(t); 
	$igk(frm).select('.cls-v').getItemAt(0).setHtml("Items: "+m_sl.getCount());
}
	
function _resetStyle(){
	var t = frm.clSelector.value;
	if (t){
		item.o.style.cssText = "";
		frm.clValue.value ="";
		var s = $igk(wnd.document).select(t); 
		if (s){	
			s.each_all(function(){this.o.style.cssText=''; });
		}
		//_save();
	}
}
	function _save(){
		igk.ajx.postform(frm, frm.action);	
	}
	igk.system.createNS("igk.winui.style_editor",
	{
	init: __init,
	reset: _resetStyle,
	save: _save,
	setProperties:function(p){		
		item.setCss(p);
		frm.clValue.value = item.o.style.cssText;
		
		var s = $igk(wnd.document).select(frm.clSelector.value); 
		if (s) { 
		s.each(function(){ 
			this.setCss(p);
			return true;
		}); 
		}
	},
	maketransparent: function(evt){ //from circle color
		var p=null;
		if (frm.clMode.value==1)
		{
			p = {backgroundColor: 'transparent'};
		}
		else{
			p = {color: 'transparent'};	
		}
		igk.winui.style_editor.setProperties(p);
	},
	initcolor: function(evt){ //from circle color
		var p=null;
		if (frm.clMode.value==1)
		{
			p = {backgroundColor: evt.value};
		}
		else{
			p = {color: evt.value};	
		}
		igk.winui.style_editor.setProperties(p);
	}
});

})();


(function(){
})();
