//
(function(){

window.igk.system.createNS("igk.web",{
"a_posturi": function(item, uri, callback)
{
	var self = null;
	var func = (callback==null)?null: function(xhr){
		callback.apply(this, [
			xhr, item
		]);
	};
	var __self = { 
		'target': item, 
		 post: function()
		 { 			
			 if (igk.ajx)
			 { 				
				igk.ajx.post(uri,null, func);				
			 } 
		}
		};
		__self.post();
	return false;
}
}); 
})();