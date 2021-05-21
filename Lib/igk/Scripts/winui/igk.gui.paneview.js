"uses strict";

igk.system.createNS("igk.winui.paneview",  {
loadfromUri : function(target, uri){
	(new function (target,uri){
		this.target = target;
		this.uri = uri;
		this.load = function(){
			var t = this.target;
			igk.ajx.get(this.uri, null, function(xhr){
				if (this.isReady()){					
					t.innerHTML = xhr.responseText;
					igk.winui.paneview.init(t.target);
				}
			});
		}
	}(target,uri)).load();	
},
init:function(parent)
{
var p = parent? parent: igk.getParentScript();
var s = $igk(p).select(".pane-view-groupitem");
var prop  = { interval: 20 , duration: 200, speed : 1,orientation:"vertical"};

function __viewType(evt, index)
{
	
	var t = this.getAttribute("pane-view-type");
	var tab = this.getElementsByTagName("div");
	switch(t)
	{		
		case "fade":
			if(index==1)
			{
				igk.animation.fadeout(tab[0],20, 500,1.0);
				igk.animation.fadein(tab[1],20, 500,0.0);							
			}
			else {
				igk.animation.fadeout(tab[1],20, 500,1.0);
				igk.animation.fadein(tab[0],20, 500,0.0);
			}
		break;
		case "scrollh":
			prop.orientation = "horizontal";
			$igk(this).scrollTo(tab[index],prop);
			break;
		case "scroll":
		default:
			prop.orientation = "vertical";
			$igk(this).scrollTo(tab[index],prop); 
		break;
	}
	evt.stopPropagation();
}

s.each(function(){
		
	var t = this.getAttribute("pane-view-type");
	switch(t)
	{
		case "fade":
			$igk(this).addClass("posr");
			var s = $igk(this).select(".pane-view-block");
			s.each(function(){ 		
				$igk(this).addClass("posab loc_t loc_l");
				return true;
			});			
			s.getNodeAt(0).style.zIndex = 1;
			s.getNodeAt(1).style.zIndex = 0;
		break;
		case "scrollh":			
			this.addClass("nowrap");
			
			var s = $igk(this).select(".pane-view-block");
			s.each(function(){ 		
				$igk(this).addClass("dispib");
				return true;
			});
		break;
	}
	
	this.reg_event("mouseover", function(evt){  __viewType.call(this, evt, 1);});
	this.reg_event("mouseout" , function(evt){ 
		var e = evt.toElement || evt.relatedTarget;		
        if ( (e) && ( (e.parentNode == this) || (e == this))) {
			
           return;
        }		
		__viewType.call(this, evt, 0);});
	return true;
});

}
});