igk.system.createNS("igk.winui.utils",
{
fixfit:function(node, parent, properties){

		var t =$igk(node);
		var l =$igk(parent);
		if(t){
			
			if (properties == null)
			{			
				t.setCss({"right": (l.fn.hasVScrollBar()? l.fn.vscrollWidth()+"px" : "0px")});
				t.setCss({"bottom": ((l.fn.hasHScrollBar()? l.fn.hscrollHeight() : 0))+"px"});
				t.setCss({"top":"auto"});		
			}
			else{
				if (properties.right) t.setCss({"right": (l.fn.hasVScrollBar()? l.fn.vscrollWidth(): 0) +"px"});
				if (properties.bottom)t.setCss({"bottom":(l.fn.hasHScrollBar()? l.fn.hscrollHeight() : 0)+"px"});
			}
		}
		}
		
		
});