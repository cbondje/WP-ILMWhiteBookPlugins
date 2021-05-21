/*

page editor tool .

*/

"use strict";

(function(){
var m_initialize = false;
var m_cibling = null;
igk.system.createNS("igk.ctrl.pageeditor", {
	hide:function(btn){
		var q = $igk($igk(btn).getParentCtrl());
		if (q) 
		{
			if (q.fc && q.fc.hide)
				q.fc.hide();
			else 				
				console.debug("bad: parent controller item does't contain hide function. not initialized "+q);
		}
	},
	init: function(p){	
		//init page editor for translation			
		if (p == null){
			//get the form
			p = igk.getParentScript();
			
		}
		var pctrl = $igk(igk.ctrl.isregCtrl(p) || $igk(p).getParentCtrl());
		if (m_cibling == p){
			//console.debug("already registered "+p);
			return;
		}
		
		var m_target = $igk(pctrl);		
		m_cibling = p;
		
		m_target.setCss({
		boxShadow : "0px 0px 4px #4E4E4E",
		padding:"4px",
		whiteSpace: "nowrap",
		left:"50%"
		});
		// var w = m_target.o.clientWidth;//get the real width
// alert(w);
		
		$igk(m_target).setOpacity(0.0);
		//append custom properties or function
		
		igk.ready(function(){
		
				var pp = {
					hide:function(){
					
						pctrl.setCss({display:'none'});
					},
					show: function(){
						pctrl.setCss({display:'block'});
					}
				};			
				if (pctrl.fc)
				{
					igk.appendProperties(pctrl.fc, pp);
					
				}
				else 
					pctrl.fc = pp;
				
					
				var w = m_target.o.clientWidth;//get the real width
					// alert(w);
					$igk(m_target).animate(
					{		
					opacity : 1.0,
					marginLeft : -(w/2)+"px"
					}, 
					{
						duration:200, 
						interval:20, context:"pageeditortranslation", 
						update: function(){
					},
					complete:function(){
					}}
					);
		});
		//init target width node property
		m_initialize = true;
	}
});
})();