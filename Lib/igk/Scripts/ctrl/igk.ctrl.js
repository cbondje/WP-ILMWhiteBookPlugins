/*
igk.ctrl NAMESPACE
*/



igk.ctrl.bindPreloadDocument("igk:canvas",
//load all controller
function(){
//var c = document.getElementsByTagName("igk:canvas");
$igk(document).select("igk:canvas").each(function(){
var i = this;
var canva = document.createElement("canvas");
i.$.dom.copyAttributes(i.o, canva, {src:"1"});

i.$.ajx.get(i.getAttribute("src"),null,function(xhr){
	if (this.isReady())
	{		
		eval(xhr.responseText);
		i.$.dom.replaceChild(i.o, canva);	
	}
	}, 
	false);
	return true;
});});


//bind igk-input-focus
(function(){
	function __forceFocus(){
		var s = this.getAttribute("igk-input-focus");
		if (s==1){
			this.focus();
		}
	}
	igk.ctrl.bindAttribManager("igk-input-focus", __forceFocus);
})();
