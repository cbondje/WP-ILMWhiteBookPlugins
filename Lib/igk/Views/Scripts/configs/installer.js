(function(){
if (typeof(igk) == "undefined")
	return;
igk.system.createNS("igk.core", {
'install':function(uri, t){
	var q = $igk(t).first();
	// console.debug("install start");
	return function(e){
		if ((e.readyState != 4) || (e.status!=200))
			return;   
		if (window.EventSource){
			// console.debug("start event source "+uri);
			var source = new EventSource(uri);			
			source.addEventListener("message", function(e){
				console.debug("message : "+e.data);
				if (e.data=="failed"){
					source.close();
				}
				q.setHtml(e.data);
			});
			
			source.addEventListener("error", function(e){
				console.debug("event finish :"+error);
				if(e.readyState == EventSource.CLOSED){
					console.log("connection closed");
				}
			});
			
			source.addEventListener("finish", function(e){				
				console.debug("event finish :");
				source.close();
				q.setHtml('');
				if (e.data == 'ok'){
					// reload on finish
					document.location.reload(true);
				} 
			});
			 

		} else {			
			q.setHtml(igk.R.msg_installing);
			igk.ajx.post(uri, null, function(xhr){
				if (this.isReady()){ 
					q.setHtml('');
					document.location.reload(true);
				}
			});
		}
	};
},
'progress':function(t, _s){
	if (!_s)
		_s = "Progress:";
	var q = $igk(t).first();
	return function(e){ 
		q.setHtml(_s+( Math.round((e.loaded / e.total) * 100)) + "%");
	};
}}); 
})();