"use strict";

(function(){

function __calcobj(q, i){

	var self = this;
	var s = q.select("span").getItemAt(0);
	var defv= i.getAttribute("default-v") || i.o.value;
	if (typeof(defv) == "undefined")
		defv = 0;
	i.reg_event("input", function(evt){
		evt.preventDefault();
		//console.debug("value added", i.o.value);
		var t = Math.round( (i.o.value - defv)*100)/ 100;
		s.rmClass("igk-danger");
		s.rmClass("igk-success");
		if (t>0){
			s.addClass("igk-success");
			
		}
		else{
			s.addClass("igk-danger");
			
		}
		s.setHtml(t);
	});
	
	igk.appendProperties(this, {
		
		toString:function(){return "igk:[#calc]"}
	});
}

igk.system.createNS("igk.calc", {
	init: function(q){
		if (!q )return;
		var i = q.select("#clValue").getItemAt(0);
		if (!i)return;
		
		return new __calcobj(q, i);
	}
});

igk.ready(function(){
	var e = $igk(".igk-calc").each(function(){	
	igk.calc.init(this);
	return true;
	});
	igk.ctrl.registerReady(function(n,p){
		if (igk.system.regex.item_match_class("igk-calc", this))
		{
			igk.calc.init($igk(this));
			//console.debug("regis "+this);
		}
	});
	
});

})();