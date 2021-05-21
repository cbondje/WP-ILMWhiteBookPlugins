/*
file: igk.ctrl.selectionmanagement.js
author:cad bondje doue
release: 25/07/14
project : IGKWEB Plateform (BALAFON)
represent selection utility
*/
(function(){


igk.ctrl.bindPreloadDocument("igk-reset-body-management", function(){
	igk.ready(function(){
	igk.dom.body().select(".igk-reset-body").each(function(){
		function getRuleName(i)
		{
			var reg = new RegExp("([A-Z])","g");//global analyse
			return i.replace(reg, "-$1").toLowerCase();
		}
		function getRule(p)
		{
			var msg ="";
			for(var i in p)
			{
				msg += getRuleName(i) +":"+p[i]+";";
			}
			return msg;
		}
		var b = $igk(document.body);
		var p ={};
		var index = igk.system.apps.link.o.sheet.cssRules.length;
		var cl = this.o.className;
		this.rmClass(cl);
		//reset properties
		var rTab = ["fontSize", "lineHeight","textAlign","margin","padding","border", "backgroundColor","color"];
		for(var i in rTab)
		{
			i = rTab[i];
			if (/(length|parentRule|cssText)/.exec(i) || (  typeof(this.o.style[i]) == "function"))
			{
				continue;
			}
			if (b.getComputedStyle(i)!= this.o.style[i])
			{
				p[i] =  b.getComputedStyle(i)+"";
			}
		}
		igk.system.apps.link.o.sheet.insertRule(".igk-reset-body{"+getRule(p)+"}", 0);
		this.addClass(cl);
		return true;
	});

	});
});
igk.ctrl.bindAttribManager("igk-js-bodyheight", function(){
	var source = this.getAttribute("igk-js-bodyheight");
	
	if (source){
		var q = this;
		this.setCss({"minHeight":document.body.clientHeight+"px"});
		var m_eventContext = igk.winui.RegEventContext(this, $igk(this));
		if (m_eventContext){
			m_eventContext.reg_window( "resize", function(){q.setCss({"minHeight":document.body.clientHeight+"px"}); });	
		}
	}
});
if (igk.ctrl.selectionmanagement)
igk.ctrl.bindAttribManager("igk-js-anim-over",  igk.ctrl.selectionmanagement.initnode);

var m_article = new Array();
var m_ctrl = new Array();

var m_viewctrl = false;
if (igk.web.getcookies("igk-sao")==1)
	m_viewarticle = true;
if (igk.web.getcookies("igk-sco")==1)
{
	m_viewctrl = true;
}

//manage controller options
igk.ctrl.bindAttribManager("igk-ctrl-options",function(){	
		var q = this;
		var source = igk.system.convert.parseToBool(this.getAttribute("igk-ctrl-options"));		 
		 q.show = function(){
			this.setCss({display:"block", position:"relative"});
		 };
		 q.hide = function(){
			this.setCss({display:"none", position:"absolute"});			
		 };
		 if (!m_viewctrl)
		 {
			q.hide();
		}
		else{			
			q.show();
		}
		m_ctrl.push(q);
});




})();	