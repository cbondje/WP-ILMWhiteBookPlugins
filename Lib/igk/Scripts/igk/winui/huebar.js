"use strict";
(function(){

igk.system.createNS("igk.winui.huebar", {
toWebColor:function(h,s,v){
	var r, g, b;
	var c = v *s;
	var x = c * (1 - Math.abs( ((h /60.0) % 2) - 1));
	var m = v - c;
	if (h<60){
		r = c; g=x; b=0;
	}else if (h<120){
		r = x; g=c; b=0;
	}
	else if (h<180){
		r = 0; g=c; b=x;
	}
	else if (h<240){
		r = 0; g=x; b=c;
	}
	else if (h<300){
		r = x; g=0; b=c;
	}else{
		r = c; g=0; b=x;
	}
	return {r:parseInt(r * 255),
			g:parseInt(g * 255),
			b:parseInt(b * 255)};
},
init:function(){	
var q = $igk(igk.getParentScript());
var c = q.select(".cur").first();
var v = q.o.nextCibling;//.parentNode.qselect('+ .huev').first();

var g = q.select('+.huev').first();
var m_st = 0;
var W=q.getWidth()-c.getWidth();
if (g){
	q.addEvent("hue-changed", {value:null});
	q.reg_event("hue-changed", function(e){
		// console.debug('hue changed');
		// console.debug(e);
		g.setHtml(Math.round(e.value)+"°");
	});
}
function __update(e){
	if (!m_st)return;
	var l=q.getScreenLocation();
	var _t =Math.max(0, Math.min(W, (e.clientX-l.x))); 
	var _x = _t+ "px"; 
	var _v = (_t/W)*360;
	//console.debug(_x);
	c.setCss({left: _x});
	q.value = _v;
	if (g){
	q.o['hue-changed'].value = _v;
	q.raiseEvent("hue-changed", {value:_v});
	}
};
q.reg_event("mousemove", function(e){
	// console.debug("move");
	__update(e);
}).reg_event("mousedown", function(e){
		if(m_st)
			return;
		m_st=!0;
		W=q.getWidth()-c.getWidth();
		//cancel mouse selection
		igk.winui.mouseCapture.setCapture(q.o);				
		igk.winui.selection.stopselection();
		__update(e);
}).reg_event("mouseup", function(e){
	if(m_st)
	{
	__update(e);
	m_st=0;
	igk.winui.mouseCapture.releaseCapture();
	igk.winui.selection.enableselection();
	}
});
}
});
})();