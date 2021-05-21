"use strict";

(function(){
var _sblock = 0;
igk.system.createNS("igk.ctrl.sessionblock",{
init:function(){
	
	// console.debug("sessiong block init "+_sblock);
	
	var target = igk.getParentScript();
	if (!target  || (target==window) || $igk(target).data['session.init']){
		console.error("/!\ can't init sessession ....");
		return;
	}
	var script = igk.getCurrentScript();
	if (script){
		target.removeChild(script);	
	}
	// $igk(target).rmClass("igk-debug-info");
	//console.debug(target);
	_sblock = script;
	
	var content = target.innerHTML;	
	var info = null;
	
	//backup
	target.innerHTML = "";
	var m_nodeChild  = document.createElement("div", igk.namespaces.xhtml);
	var m_visible =false;
	var m_oldh = 0;
	m_nodeChild.innerHTML  =  content;
	
	igk.ajx.fn.initnode(m_nodeChild);
	
	function hide_info(d)	{
		$igk(d).animate({ height:"0px", "opacity":0}, 
			{
				duration:200, 
				interval:20,
				complete: function(){
				$igk(d).addClass("dispn").setCss({
					padding:"0px"
				});
				if (d.parentNode){
				d.parentNode.removeChild(d);
				}
				info = null;
		}});
		
	}
	function show_info(evt)	{
		if (info!=null)
		{			
			hide_info(info);
			return;	
		}
		var d  = m_nodeChild;
		$igk(d).addClass(
			"igk-session-block no-print google-Roboto js"
		).setCss(
			{
				minWidth: "300px",
				height: "0px",
				display:"inline-block",
				overflow: "hidden",
				top: (igk.getNumber(target.offsetTop) + 32)+"px",
				zIndex: parseInt (q.getComputedStyle('zIndex'))+1,
				right:"32px",
				padding:"10px",
				fontSize: "8pt"
			}
		);			
		
		 
		 
		if (d.parentNode==null)
		document.body.appendChild(d);	
		var h = d.scrollHeight;	
		if (h == 0)
			h = m_oldh;//restore height
		else 
			m_oldh = h;//save h
		$igk(d).setOpacity(0).rmClass("dispn").animate({ height: (h+10)+"px", opacity:1}, {interval:1, duration:20});
		info = d;
	};

	// console.debug("init session button");
var q = $igk(target);
q.addClass("igk-session-button").rmClass("igk-js-hide").setCss(
		{
			"width":"24px",
			"height":"24px",
			"backgroundRepeat":"no-repeat",
			"backgroundPosition":"0px 0px",
			"backgroundColor":"transparent",
			//"zIndex" : 300,
			"position":"fixed",
			//"boxSizing" : "none", readonly for old
			"overflow":"hidden",
			"right":"32px"
			//"top":"16px"
		}).reg_event("mouseover", function(){ 		
		q.setCss({"backgroundPosition":"0px -24px"});
	})
	.reg_event("mouseleave", function(){ $igk(target).setCss({"backgroundPosition":"0px 0px"});})
	.reg_event("click", show_info);		
	q.data['session.init'] = 1;
	
}
});

})();