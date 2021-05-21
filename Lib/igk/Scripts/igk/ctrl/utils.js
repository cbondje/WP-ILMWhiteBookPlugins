//
"use strict";

(function(){
	
	//controller and article functions
	var CA = igk.system.createNS("igk.ctrl.ca", {	
	});
	
	
	igk.appendProperties(CA, {
		editChange: function(tq,target, uri){
			// console.debug(target);
			// console.debug(uri);
			if (target && uri)
			(function(i){
					var p = $igk('.cnf-edit-view-result').getItemAt(0); 
					var q = window.igk.getParentById(i, target); 
					window.igk.ajx.post(uri+i.value, null, function(xhr){  
					if (this.isReady()){ 
						this.setResponseTo(p.o);
						var r = $igk($igk(i).o.form).select('.c-opts').getItemAt(0);
						var tp = p.select('.c-opts').first();
						if (r && tp){
							r.setHtml(tp.getHtml()).init();
						}
					}
				});
			})(tq);
			return !1;
		}
	});
	
})();