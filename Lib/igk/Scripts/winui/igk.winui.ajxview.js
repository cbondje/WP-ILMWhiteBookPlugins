"use strict";
(function(){
igk.system.createNS("igk.winui.ajxview",
{
	init: function(uri, func)
	{
		(function(q){$ns_igk.ready(function(){$ns_igk.ajx.apost(uri, null, function(xhr){ if (this.isReady()){
				var s = igk.createNode("dummy");
				var t = igk.utils.getBodyContent(xhr.responseText);
				
				
				if (func) 
					func.apply(this, [{response: s}]); 
				else  {
					$igk(q).setHtml(t);
					igk.ajx.fn.initnode(q);
					//this.setResponseTo(q, true, s.getHtml());
				}
				} 
			}) 
		
		} );})($ns_igk.getParentScript());
	}
});

})();