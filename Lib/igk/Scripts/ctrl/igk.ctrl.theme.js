(function(){

function __init_font_tool(){ 
	this.addClass("posr");
	var d = igk.createNode("div");
	var p = new igk.winui.tooltip(this, d);
	d.setHtml(this.getAttribute("igk-font-tool-tip"));	
	d.addClass("igk-tooltip");//.setCss({backgroundColor:"indigo", color:"white"});
	
	if (this.getAttribute("igk-font-drop-font"))
	{
		__init_drop_font.apply(this);
	}
	return true; 
};

function __init_drop_font(){

	this.setCss({width: "64px"});	
	var d = igk.createNode("div");
	var img = igk.createNode("img");
	var v = this.getAttribute("igk-font-drop-font-uri");
	var cv = this.getAttribute("igk-font-drop-font-uri");
	var self = this;
	img.o.src= v;
	img.reg_event("click", function(){ igk.ajx.post(self.getAttribute("igk-font-drop-font"),null, 
	 
	function(xhr){ if (this.isReady()){ 
			window.igk.ajx.replaceBody(xhr.responseText);
			$igk(document).scrollTo($igk(document).select(".font_list").getNodeAt(0));		
		}	
	}); });
	img.o.alt = "d";
	d.setSize("18px").setCss({"border":"none",
	"top":"4px",
	"fontSize":"100%",
	"right":"4px"}).addClass("posab");	
	d.appendChild(img);	
	this.appendChild(d);		
};


window.igk.system.createNS("igk.ctrl.theme", 
{
init: function()
{
//init font manager
var t = $igk(document).select(':igk-font-tool-tip').each(__init_font_tool);
}
});



igk.ctrl.bindPreloadDocument("igk.ctrl.theme", igk.ctrl.theme.init);

igk.ctrl.registerAttribManager("igk-font-tool-tip",{desc:'font-tool-tip'});
igk.ctrl.bindAttribManager("igk-font-tool-tip",function(){
__init_font_tool.apply(this);
});


})();