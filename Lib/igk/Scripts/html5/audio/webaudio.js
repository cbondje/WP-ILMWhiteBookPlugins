"use strict";

(function(){


igk.winui.initClassControl("igk-webaudio", function(){
	var q = this;
	var _ol = q.getAttribute("igk-webaudio-attr-listener");
			
	//console.debug(_ol);
	
	if (_ol && (typeof(_ol) == 'string')){
		var ns = igk.system.getNS(_ol);
		if(typeof(ns)=='function'){
			_ol = new ns();
		}
	}else{
		console.error("/!\\ igk-webaudio-attr-listener not found or not valid: !"+_ol);
	}
},{
	"desc":"init a webaudio control"
});


})();