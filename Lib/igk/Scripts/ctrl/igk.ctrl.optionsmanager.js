/*
controller options manager
*/

(function(){
	igk.system.createNS("igk.ctrl.optionsmanager", 
		{
			init: function(){
				//get all node that got the attribute igk-type
				var s = igk.dom.body().select(":igk-type");
				if (s)
				{
					s.each(function(){
						if (this.getAttribute("igk-type") == "ctrl-options")
						{//init controller options on node
						
							this.setCss({
								fontSize:"1em",
								width: "100px",
								height: "32px",
								top:"0px",
								left:"0px"
							});
							this.getParentNode().setCss({
								//"paddingTop" : (this.getParentNode().o.style.paddingTop+32)+"px"
							}).reg_event("click", function(evt){
								//show option on right click
								//igk.show_prop(evt);
								
							});
						}
						return true;//to continue iterating
					});
				}
			}			
		}
	);	
	igk.ready(igk.ctrl.optionsmanager.init);
}
)();



(function(){
	igk.system.createNS("igk.ctrl.page_zone", 
		{
			init: function(){
			//igk.show_prop(document.body.style);
				//get all node that got match the class .page_zone
				var s = $igk(document.body).select(".page_zone");
				if (s)
				{
					var size = igk.window.GetScreenSize();
					s.each(function(){
						//init controller options on node
							this.setCss({
								display:"inline-block",
								textAlign:"left",
								position:"relative",
								//width: (size.x - 200)+"px",
								maxWidth:"auto",
								minWidth:"auto"
							});
							this.animate({width:(size.x - 200)}, {duration:200});
						
						return true;//to continue iterating
					});
				}
			}			
		}
	);		
}
)();