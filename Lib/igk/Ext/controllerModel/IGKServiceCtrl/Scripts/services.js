//script to manager script

"uses strict";

(function(){
	
	igk.system.createNS("igk.services", {
		invoke: function(m, c){
			//console.debug("invoking "+m);
			var uri =  window.location.href.split('#')[0];
			function _sendRequest(){
			
				igk.ajx.postWebRequest(uri, m, "", function(xhr){
				if (this.isReady()){
					igk.winui.notify.showMsBox("Service : "+m+"_response ", xhr.responseText);
					
				}
				});
			}
			
			if (c > 0){				
				igk.winui.notify.showMsBox("ServiceParam", "/!\\sorry this service method : <b>"+m+"</b>: required some parameter(s). if no parameter set correctly you will get an error.");				
			}else
			_sendRequest();
		}
	});
	
})();
