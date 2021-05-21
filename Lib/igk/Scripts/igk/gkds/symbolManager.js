"use strict";
//author: CAD BONDJE DOUE
//gkds symbol management

(function(){

var m_items = []; //stored symbols

function __loadSymbols(data, symLoader){
	var gkds = new igk.wo.gkds();
	gkds.canva = igk.createNode("canvas");
	
	var xml = $igk(igk.createNode("dummy").setHtml(data).o.getElementsByTagName("gkds")[0]);
	var m_node = null;
	if (xml){
		var s = xml.select("documents").select(">>");		
		s.each(function(){
			// console.debug(this.o.tagName);
			if (this.o.tagName && (this.o.tagName.toLowerCase() == "layerdocument"))
			{
				var doc = new igk.wo.documents(gkds);							
				
				doc.load(this);				
				doc.id = this.getAttribute("id");
				doc.gkds = gkds;
				m_items.push(doc);
				
			}
			return true;
		});
		m_node = xml;
	}
	symLoader.loadItems(m_items);
	__initglobalsymbol();
}

function __symbolLoader(uri, p){	
	var ob = igk.createNode("object");
	// ob.o.width= 48;
	// ob.o.height = 48;
	// ob.o.type = "text/xml";
	var m_init = false;
	ob.addClass("no-visible bdr-1 dispb posab");
	ob.setCss({"width":"48px", "height":"48px"});
	var m_data = null;
	var self = this;
	var m_items = [];
	var m_rdata  = false;
	//console.debug("loading : "+uri);
	
	ob.reg_event("error", function(evt){			
		console.error("/!\\ Error : [ igk.gkds.symbolManager] - Loading symbols failed.... "+uri);	
	});
	
	function _r_fc(evt){			
		//get data	
		//console.debug("load data .. _r_fc"+ob.o.readyState);
		if (!ob.o.contentDocument || ob.o.contentDocument.readyState!="complete")
			return;
		// console.debug(evt);
		 if (!ob.o.contentDocument.childNodes)
			return;
		m_rdata = true;
		var c  = ob.o.contentDocument.childNodes.length;
		var dummy = igk.createNode("dummy");
		//copy node
		while(c>0){
			dummy.o.appendChild(ob.o.contentDocument.childNodes[0]);
			c--;
		}		
		m_data = dummy.getHtml();  //ob.o.contentDocument.documentElement.getElementsByTagName("body")[0].innerHTML;				
		if (m_data==null){
			return;
		}		
		// console.debug(m_data);
		__loadSymbols(m_data, self);
		//remove object after data loaded
		ob.remove();
		ob = null;
	};
	//alert(ob.o.onload);
	if (typeof(ob.o.onload) != 'undefined'){
		//console.debug("register load event");
		ob.reg_event("load", _r_fc);
	}
	else
		ob.reg_event("readystatechange", _r_fc);	

	if (!m_init || (ob.o.parentNode != document.body)){	
		// !important  for ie prepend data
		ob.o.type = "text/xml";	
		//ob.o.data = uri;
		ob.o.width = 1;
		ob.o.height = 1;
		//ob.add("object").setHtml("Object does't support gkds file");
		//$igk(document.body).setHtml("");
		igk.ready(function(){	
			if (igk.getSettings().nosymbol){
				$igk(".igk-symbol").each_all(function(){
					this.addClass("dispn");
					this.remove();
				});
				return;		
			}	
			
			//edge data must be set after add to document
			if (igk.navigator.isIEEdge()){
				$igk(document.body).prepend(ob);
				ob.o.data = uri;	
			}else{
				// /!\ for firefox data must be set before added to document				
				ob.o.data = uri;	
				$igk(document.body).prepend(ob);				
			}				
		});		
		m_init = true;
	}
	
	
	igk.appendProperties(this,{
		getSymbolBitmapData: function(id, w, h, p){
			var c = m_items[id];
			if (!c)return null;
			if (igk.is_notdef(w)){
				w = 100;
			}
			if (igk.is_notdef(h)){
				h = 100;
			}
			
			c.gkds.canva.o.width = w; //100;
			c.gkds.canva.o.height = h;//100;
			//igk.show_prop(c.gkds.canva.o);
			var ctx  = c.gkds.canva.o.getContext('2d');						
			ctx.clearRect(0,0,100,100);
			ctx.scale(w/100.0, h/100.0);
			// ctx.fillStyle = '#1A1A1A';
			// ctx.fillRect(0,0,100,100);
			
			var s = {
					fillStyle: p && p.fillStyle ? p.fillStyle : "#f0f",
					fillOpacity: p && p.fillOpacity? p.fillOpacity : 1,
					strokeStyle:p && p.strokeStyle? p.strokeStyle : "#000",
					strokeOpacity:p && p.strokeOpacity? p.strokeOpacity : "0",					
					fillMode: p && p.fillMode? p.fillMode : "evenodd",
					base:1
			};			
			c.render(c.gkds.canva, ctx, s);			
			var h = c.gkds.canva.o.toDataURL();		
			return "url('"+h+"')";
		},
		loadItems: function(tab){
			for(var i = 0; i < tab.length; i++){				
				if (!m_items[tab[i].id]) 
					m_items.push(tab[i]);
				m_items[tab[i].id] = tab[i];				
			}
			m_isloaded = true;
			igk.publisher.publish("sys://gkds/itemloaded");
			if (p && p.complete)
				p.complete.apply(this);
		}
	});
}

var m_loader=null;
var m_isloaded = false;
igk.system.createNS("igk.gkds.symbolManager", {
	load: function(uri, p){//load symbol file	
		//console.debug("load symbol file ::: from : "+uri);	
		var s = new __symbolLoader(uri,p);
		m_loader = s;
		return s;
	},
	getIsLoaded: function(){ return m_isloaded;},
	getSymbolBitmapData:function(id,w,h,p){
		if (m_loader){
			return m_loader.getSymbolBitmapData(id,w,h,p);
		}
		return null;
	}
});

function __init_symbol(){
		var d = this.getHtml();		
		//console.debug("html : "+ d);
		var t = d.charCodeAt(0).toString(16)+"";
		t = igk.system.string.padleft(t, "0", 3);
		var self = this;
		var cl =self.getComputedStyle("color"); 
		var op =self.getComputedStyle("opacity"); 
		var s = igk.JSON.parse(self.getAttribute("igk-symbol-data"));
		
		this.setHtml(" ");
		//console.debug("get code for "+t + " "+d);
		

		function __view(){		
			m_re = true;
			__store();
			__render();
			m_re = false;
		};
		
		function __render(){
			var w = s? s.w : igk.getNumber(self.getComputedStyle('width'));
			var h = s? s.h : igk.getNumber(self.getComputedStyle('height'));			
			var cl =self.getComputedStyle('color');			
			self.setCss(
					{
						backgroundImage: igk.gkds.symbolManager.getSymbolBitmapData('_'+t, 
						w,
						h,
						{
							fillStyle: cl,
							fillOpacity:1.0, //self.getComputedStyle('opacity')
							fillMode: 'nonzero',//'evenodd',non-zero
							strokeStyle: s && s.strokeStyle? s.strokeStyle: "#000",
							strokeOpacity:s && s.strokeOpaticy? s.strokeOpacity:0.0
					})
			});		
		}
		
		var data = {			
			color:0,
			content:''
		};
		function __store(){
			for(var i in data){
				data[i] = self.getComputedStyle(i);
			}
		}
		__store();
		
		//alert(this.o.onDOMAttrModified);
		//igk.show_notify_prop(this.o);
		
		//TODO:: Observer data changed
		var m_re=false;
		if (igk.navigator.isSafari() )
		{
			//console.debug("safari required a special dom mecanism");
		}
		else if (this.o.addEventListener)
		{	
			
			if (MutationObserver){
				
				var mut = new MutationObserver(function(mutation){
					// console.debug("attribute changed");
					// console.debug(mutation);
					
				});
				
				mut.observe(this.o, {attributes:1});
			}else{			
				
				this.o.addEventListener("DOMAttrModified", function(evt){
					// igk.show_notify_prop(evt);
					if (m_re){			
						return;
					}
					var n = evt.attrName;
					if (( n == "style") || (n=="class"))
					{
						var r =false;
						for(var i in data){
							if (data[i] != self.getComputedStyle(i)){
								r = true;
								break;
							}
						}
						if (r){
							__view();
						}
					}
				});
				
			}
			
				
		
		}
		//-----------------------------------------------
		//console.debug("d");
		//igk.show_prop(document.styleSheets[0].cssRules);
		//-----------------------------------------------
		//demonstration purpose : 

		// this.reg_event("mouseover", function(){
			//self.setCss({color: 'red'});		
			//self.setOpacity(1);
			// __view();
		// });
		//mouseleave
		// this.reg_event("mouseout", function(){
			//self.setCss({color: 'black'});			
			//self.setOpacity(1);
			// console.debug("mosu e");
			// __view();
		// });
	
	
		this.data["igk-symbol-init"] = 1;
		if(igk.gkds.symbolManager.getIsLoaded()){			
			__render();
		}
		else{			
			var _f =  function(){
				__render();
			};
			igk.publisher.register("sys://gkds/itemloaded",_f);
		}

}

function __initglobalsymbol(){	
	
	//select all item marked with igk-symbol class and init them if symbol data loaded
	// console.debug("init symbols " + m_items.length);
	// console.debug("search ...."+ document.readyState + " "+ $igk(".igk-symbol"));
	if (m_items.length <= 0)
		return;
	var i = 0;
	$igk(".igk-symbol").each(function(){	
		if (!this.data["igk-symbol-init"] ){	
			__init_symbol.apply(this);
		}
		i++;
		return true;
	});	
};
igk.ajx.fn.registerNodeReady(__initglobalsymbol);

})();

