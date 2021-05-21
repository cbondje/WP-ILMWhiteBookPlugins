(function(){
	var m_support={};
function videoSupport(){
	// console.debug("check ...");
	if (igk.isUndef(m_support.video)){
		var vid = document.createElement('video');
		m_support.video = vid instanceof HTMLVideoElement;
		// console.debug(vid);
		return m_support.video ;
	}	
	return m_support.video;
};
function audioSupport(){
	// console.debug("check ...");
	if (igk.isUndef(m_support.audio)){
		var vid = document.createElement('audio');
		m_support.audio = vid instanceof HTMLVideoElement;		
	}	
	return m_support.audio;
};
	
igk.system.createNS("igk.html5", {
	support:function(k){
		k = k+"Support";
		if (eval("typeof("+k+")")){
		var s = eval (k+"()");
		
		return s;
		}
		return 0;
	}
});

})();