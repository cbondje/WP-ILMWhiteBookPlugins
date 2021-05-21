
//-------------------------------------------------------------------------------------------
//igk-top-nav-bar
//desc: used to managed top navigation bar
//mark with attribute igk-top-nav-bar a div that will be used for top nanvigation bar
//-------------------------------------------------------------------------------------------
"use strict";
(function(){
var cibling = "igk-top-nav-bar";
igk.system.createNS("igk.ctrl.topnavbar",
{
	init:function(target){	
		var q = $igk(target);
		var s =null;
		var _t = q.getAttribute("igk-nav-bar-target") ||  "^.igk-parentscroll";
		var _opts = igk.JSON.parse(q.getAttribute("igk-nav-bar-options")) || {
			offset:0
		};
		s = q.select(_t);
		var offp = s.getItemAt(0);
		
		// console.debug(offp.o);
		// console.debug("done");
		var tg_id = ( tg_id = ($igk(_opts["offset-target"]) || null)) ?  tg_id.getItemAt(0) : null;
		
		if (tg_id){
			//console.debug(tg_id.o);
			//var g = tg_id.getScreenLocation();
			//console.debug(g);
			var g = igk.winui.GetScreenPosition(tg_id.o);
			//console.debug(igk.winui.GetScreenPosition(tg_id.o));
			_opts.offset = g.y + tg_id.o.scrollHeight;
		}
		
		// q
		//.addClass("dispn")
		// .setCss({zIndex:1000})
		// .setOpacity(0.0);
		
		
		function __bind(p){
			var dx = p.scrollTop - _opts.offset;
			// console.debug(p.scrollTop);
			// console.debug(_opts);
			// console.debug("dx:"+dx);
				if ((dx)>0){
					
					//q.rmClass("dispn");
					
					igk.winui.fitfix2(q, p, true, false);
					// q.setCss({
						// "position":"fixed",
						// "left" : "0px",
						// "width":"auto",
						// "display":"block"
					// });
					q.addClass("igk-show");
					// console.debug(q.o);
					// console.debug(q.o.className);
				}
				else{
					//q.setOpacity(0.0);
					q.rmClass("igk-show");	
					//q.addClass("igk-show");					
				}	
		}
		function parentOffset(i)
		{
			var st = i.parentNode;
			//var g = null;
			while(st && (st.parentNode!=null) && (st.parentNode != document.body)){				
				st = st.parentNode;
			}
			return st;
		}
		
		igk.load(function(){	
				if (offp)
				var p = null;
				if (offp)
					p  = offp.o;
				else 
					p = parentOffset(target.o);
				if (p){
					ns_igk.winui.reg_event(p,"scroll", function(evt){
						//igk.show_prop(p);
						// console.debug("document scroll");
						// console.debug(p.clientWidth+"px");
__bind(p);						
					
					});
					//console.debug('bind....');
					__bind(p);
				}else{
					console.debug("there is no parent offset");
				}
			});
	}
});


if(!igk.ctrl.isAttribManagerRegistrated(cibling))
	igk.ctrl.registerAttribManager(cibling, {n:"js", desc:"register top nav bar"});
	
igk.ctrl.bindAttribManager(cibling, function(){
	    var q = this;
		var source = igk.system.convert.parseToBool(this.getAttribute(cibling));
		if (source){
			igk.ctrl.topnavbar.init(q);
			// console.debug("init top navbar");
		}
});
})();

