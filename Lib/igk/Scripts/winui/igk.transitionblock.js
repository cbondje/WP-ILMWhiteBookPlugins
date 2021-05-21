"use strict";

(function(){


function animateHandle(q){
	var d = function(e){
		// console.debug("scrolling...");
		if (this.getisVisible()){
			// console.debug("starrt animate");
			q.addClass("animate");
		}
	};
	var _po = q.getscrollParent().o;
	if (_po == document.body){
		_po = $igk(document);
	}else{
		_po = $igk(_po);
	}
	_po.reg_event("scroll", function(){
		if (q.getisVisible()){
			q.addClass("animate");
		}
	}); 
	q.on("transitionend", function(e){
		if (e.target == q.o){
			// console.debug("transitionend: "+ e.propertyName);
			if (e.propertyName=="transform")
				_po.unreg_event("scroll", d);
		}
	});
};

// \$igk(".trans-block").each_all(function(){
	// // console.debug("init trans-block");
	// if(this.getisVisible()){
		// this.addClass("animate");
	// }else{
		// // console.debug(t);
		// new animateHandle(this);
	// }
	// // console.debug("is visible : "+ this.getisVisible());
// });
// console.debug("done");
igk.winui.initClassControl("igk-transitionblock", function(){
	if (this.getisVisible()){
		this.addClass("animate");
	}else{
		new animateHandle(this);
	}
	
	
});


})();

