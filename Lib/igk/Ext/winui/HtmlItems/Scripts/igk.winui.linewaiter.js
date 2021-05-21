"uses strict";

(function(){

	function __create(){		
		var d = igk.createNode("div");
		d.addClass("igk-line-waiter");
		d.setAttribute("model", "js");
		//add 3 line  cursor
		d.add("div").addClass("igk-line-waiter-cur");
		d.add("div").addClass("igk-line-waiter-cur");
		d.add("div").addClass("igk-line-waiter-cur");
		d.control = { initialize: false, type:'linewaiter'};
		return d;
	}
	igk.system.createNS("igk.winui.lineWaiter",{
	init: function(q){		
		q = q || $igk(igk.getParentScript());				
		
		//item for js
		if (q.control && (q.control.type =='linewaiter'))
		{
			if (q.control.initialize)
				return;
			q.control.initialize = true;
		}	
		
		
		igk.ready(function(){						
			var s = q.select(".igk-line-waiter-cur");			
			if (s.getCount()>0)
				s.waitInterval(500, function(){
				//wait an run item after 500
					var self= this;
					var levent=null;	
					function _t_e(evt){
						var g = $igk(igk.winui.eventTarget(evt));				
						//choose the last modified property	
						if ((self.o == g.o) && 	(evt.propertyName == "right")){	
							
							if (igk.system.regex.item_match_class("igk-animate", g.o)){	
								g.rmClass("igk-animate");
							}
							else {
								g.addClass("igk-animate");
							}							
						}
					}
					this.reg_event("transitionend", _t_e)
					.addClass("igk-animate");
			});
		});
	},
	addTo: function(t){
		var d = __create();
		$igk(t).add(d);
		igk.winui.lineWaiter.init(d);		
		return d;
	},
	prependTo: function(t){
		var d = __create();
		$igk(t).prepend(d);
		igk.winui.lineWaiter.init(d); 
		return d;
	},
	remove: function(t){
		//remove line waiter in target node
		$igk(t).select(".igk-line-waiter").each(function(){
			this.remove();
			return true;
		});
	}
	}
	);
	
})();

// igk.readyGlobal(function(){
	// var d = $igk(document.body).prepend("div");
	// var waiter = igk.winui.lineWaiter.addTo(d);		
	// setTimeout(function(){ waiter.remove(); }, 8000);
// });