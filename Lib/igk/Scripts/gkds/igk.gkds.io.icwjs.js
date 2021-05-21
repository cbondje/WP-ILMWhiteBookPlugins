//namespace : igk.gkds.io.icwjs
//Description: used to load created canvas script from DrSStudio
//author: C.A.D BONDJE DOUE
//date: 13/08/15
//usage : igk.gkds.io.icwjs.read(canva, uri);


(function(){


igk.system.createNS("igk.gkds.io.icwjs", {
read: function(canva, uri){
	var v_ctx = canva.getContext('2d');
	igk.ajx.post(uri,null, function(xhr){ 
		if (this.isReady()){ 	
			eval("(function(){"+xhr.responseText+"})();");
		}
		});
}
});

})();