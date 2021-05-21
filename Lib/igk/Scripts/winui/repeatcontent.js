"use strict";
//repeat content and replace the node
(function (){

function _init(){
	var q = this;
	var s = q.o.innerHTML;
	var c = q.getAttribute("igk-repeat") || 1;
	var d = "";
	//console.debug("start: "+s);
	for(var i = 0; i<c;i++){
		s = s + q.o.innerHTML;
	}
	var g = igk.createNode("dummy");
	g.setHtml(s);//document.createElement('div');
	var p = g.o.firstChild;
	c = 0;
	while(p){
		q.o.parentNode.insertBefore(p, q.o); //p is removed from the previous list so the first child is always set
		$igk(p).init();
		p = g.o.firstChild;
		// console.debug("count ::: " + c);
		c++;
		
		// if (c>50)
			// break;
	}	
	q.remove();
	// alert(q);
};


igk.winui.initClassControl("igk-winui-rc", _init, {
	desc:'repeat-content'
});
})();