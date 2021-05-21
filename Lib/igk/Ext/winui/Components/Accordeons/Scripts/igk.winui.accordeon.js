"use strict";
(function(){

function get_auto_height(q){
	var o = q.getComputedStyle('height');
	q.setCss( { height:"auto"});
	var r = q.getComputedStyle('height');
	q.setCss( { height:o});
	return r;
}
function __init_content(){
	//var cl = this.clone();
	//var b = this.getComputedStyle("height");	
	this.setCss( { height:"auto"});
	this.trans = {
		maxHeight: this.getComputedStyle("height")
		//innerHtml: cl.getHtml()
	};	
	var q = this;

	if (!q.open){
		q.open = function(){
			var bck = q.getComputedStyle('transition');
			if (bck){//disable transition
				q.setCss({"transition":'none'});
			}
			var h = get_auto_height(q);
			if (bck){//restore transition
				q.setCss( { "transition":bck});
			}
			q.setCss({height:h});
		};
	}
	if (!q.close){
		q.close = function(){
			q.setCss({height:"0px"});
		};
	}	
	
	//console.debug(this.trans);
	//reset
	this.setCss({ height:null});	
}
function __click_h(evt){
	
	var q = this;
	var d = "igk-collapse";
	var t = "igk-toggle";
	var cf= $igk(q.o.parentNode).select(".igk-c").getItemAt(0);
	if (igk.system.regex.item_match_class(t, q.o))
	{
		q.rmClass(t);
		cf.close();		
	}
	else {
		cf.open();		
		q.addClass(t);
	}
	evt.stopPropagation();
	//alert("ok");
}
function is_scrolling(q){
	var g = q.fn["sys://winui/accordeon"];
	//console.debug(g);
	if (g.owner.fn["sys://scrolling"]){
		// console.debug("be scrolling");
		delete(g.owner.fn["sys://scrolling"]);
		// console.debug("after");
		// console.debug(g);
		return 1;
	}
	return 0;
};
function __click(q){
	return function(evt){
		if (is_scrolling(q)){
			return;
		}
		// var c = evt.target == q.o;
		// alert("oy xclick ? "+c+ " : "+evt.target.className+ " "+q.o.className);
		// console.debug(evt);
		// console.debug(evt.target);
		// if (c){
			// alert("stoping ");
		// evt.preventDefault(); 
		// evt.stopPropagation();
		// }
		__click_h.apply(q, [evt]);
	}
}

igk.system.createNS("igk.winui.accordeon", {
	init:function(){
	//	alert("init accordeon "+igk.getParentScript());
		
		// console.error(igk.getCurrentScript().innerText);
		
		var q = $igk(igk.getParentScript());
		igk.ready(function(){
		q.select(".igk-c").each(function(){
			__init_content.apply(this);
			return true;
		});
		q.select(".igk-panel-heading").each(function(){
			var self= this;
			self.fn["sys://winui/accordeon"]= {
				owner:q
			};
			this.reg_event("touchOrClick",  __click(self));
			// if (this.istouchable()){
				// this.reg_event("touchend", __click(self));
			// }
			// else 
				// this.reg_event("click", __click(self));
			
			igk.ctrl.selectionmanagement.disable_selection(this.o);
			
			return true;
		});
		
		});
		
		var starttouch=0;
		function __binds(){
			if (!q.fn["sys://scrolling"] && starttouch){
				q.fn["sys://scrolling"]=1;
				starttouch = 0;				
			}
		}
		q.reg_event("scroll", function(){
			console.debug("item scroll");
		});
		igk.winui.reg_event(window, "scroll", function(){	
__binds();		
		
		}, true);
		igk.winui.reg_event(document, "scroll", function(){			
			__binds();	
		}, true);
		$igk(".igk-parentscroll").reg_event("scroll", function(evt){			
			__binds();	
		}, true);	
		
		q.reg_event("igkTouchStart igkTouchMove", function(evt){
			if (evt.type =="touchstart")
				starttouch=0;
//console.debug("scrolling "+	q.fn["sys://scrolling"]);			
			if (!q.fn["sys://scrolling"] && !starttouch){
				//console.debug(evt.type);
				q.fn["sys://scrolling"]=0;
				starttouch = 1;
			}
		}, igk.features.supportPassive?{passive:true}:false);
	}
});
})();