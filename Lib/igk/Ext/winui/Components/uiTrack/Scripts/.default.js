"uses strict";

(function(){
	
	//attribute : igk:uitrack-options = {}
	//@min:range minimum
	//@max:range maximum
	//@default: default value must be between the [min...max] range
	//@update: callback function to call for upate text presentation
	
	var _ui = igk.winui;
	var CW = 0;
	_ui.uitrack = function(){
		
	};
	
	
	function __init(){
		function _update_v(_x){			
			var g = q.o["curChanged"];
			
			g.value=_x;
			g.target=opt.i;
			q.raiseEvent("curChanged");
			opt.i.o.value = _x;
			var y = parseInt(_x*100)+"%";
			if (topt){
				if (topt.update)
					opt.d.setHtml(topt.update.apply(topt, [_x]));//parseInt(_x*100)+"%");
				else{
					opt.d.setHtml(parseInt( ((topt.max - topt.min) * _x) - topt.min));	
				}
			}else
				opt.d.setHtml(y);
			var _r =  "calc("+_x+" * (100% - "+(CW)+"px) + 2px )";			//2px to inter the cursor
			opt.bl.setCss({width: _r});// "calc(100% - "+ y+");"});
		}
		function _update(clientx){
			if (!c.handle)
				return;
			var l=q.getScreenLocation();
			var W=q.getWidth();
			var H=q.getHeight();	
			CW = f.getWidth();			
			var _x = 1.0;
			_x = Math.max(0,Math.min( clientx - l.x,W)) / W ;//* 10000) / 10000.0;	
		
			
			f.setCss({left: "calc("+_x+" * (100% - "+CW+"px))"});
			_update_v(_x);
			
		};
		function _stop_h(){
			//console.debug("stop capture");
			
			
			//console.debug("release  capture");
			if (!c.touch)
				_ui.mouseCapture.releaseCapture();
			_ui.selection.enableselection();
			c.handle=0;
			c.touch=0;
		};
	
		var q = this;
		var opt={};
		var topt = igk.JSON.parse(this.getAttribute("igk:uitrack-options"));
		var _id =q.getAttribute("id");
		
		q.o.removeAttribute("id");
		
		// console.debug(topt);
		
		q.addEvent("curChanged", {
			value:0,
			target:null
		});
		var e = igk.createNode("input");
		e.setAttribute("type", "text");
		e.setAttribute("value", 0);
		e.setAttribute("id", _id);
		e.setAttribute("name", _id);
		var bl = igk.createNode("div").addClass("bl");	//blood line
		var f = igk.createNode("div").addClass("cur");
		var d = igk.createNode("div").addClass("disp");		
		
		var c = {handle:0, touch:0};
		
		if (!f.istouchable()){
		f.reg_event("mouseup mousedown mousemove" , function(evt){
			//console.debug("mouse handler "+evt.type);
			if (_ui.mouseButton(evt)==_ui.mouseButton.Left){
				var clientx =evt.clientX;
			switch(evt.type){
				case "mousedown":					
					_ui.mouseCapture.setCapture(f.o);				
					_ui.selection.stopselection();
					c.handle=1;
					_update(clientx);
				break;
				case "mouseup":
					if (!c.touch){
						_update(clientx);						
					}
					_stop_h();
					break;
				case "mousemove":
					_update(clientx);
					break;
			}
			evt.stopPropagation();
			}else if (c.handle){
				_stop_h();
			}
		})}
		// else {
			// f.reg_event("igkTouchStart igkTouchMove igkTouchEnd", function(evt){
			 // console.debug("touch handler "+evt.type);
			// // console.debug(evt.touches);
			// // if (evt.touches.length==1){
			// // var clientx =evt.touches[0].clientX;
			// // switch(evt.type){
				// // case "touchstart":
					// // _ui.mouseCapture.setCapture(f.o);				
					// // _ui.selection.stopselection();
					// // c.handle=1;
					// // c.touch =1;					
					// // _update(clientx);
				// // break;
				// // case "touchmove":				
					// // _update(clientx);
				// // break;
				// // case "touchend":
					// // _update(clientx);
					// // _stop_h();
				// // break;
			// // }
			// //}
		// }, igk.features.supportPassive?{passive:true}:false);
		
		// }
		
		
		q.add(e);		
		q.add(d);
		q.add(bl);
		q.add(f);	
		
		
		opt.i = e;
		opt.f = f;
		opt.d = d;
		opt.bl = bl;
		
		if (!q.istouchable()){
		q.reg_event("mousedown mouseup", function(evt){
			
			if (!c.touch && (_ui.mouseButton(evt)==_ui.mouseButton.Left)){
				//console.debug("mousedown start global ");
				_ui.mouseCapture.setCapture(f.o);				
				_ui.selection.stopselection();
				c.handle=1;
				_update(evt.clientX);
			}
		});
		}
		else {
			q.reg_event("igkTouchStart igkTouchMove igkTouchEnd",function(tevt){			
			// console.debug("touch start global ------------------"+c.touch + " "+tevt.type);
			// var x = 0;
			if (tevt.touches.length>0)
			{
					x = tevt.touches[0].clientX;
					switch(tevt.type){
						case "touchstart":
						if (!c.touch){
							
							// _ui.mouseCapture.setCapture(q.o);				
							 _ui.selection.stopselection();
							c.handle = 1;
							c.touch =1;
							_update(x);			
						}else{
							
						}
						break;
						case "touchmove":
							_update(x);			
							break;
						case "touchend":
							_update(x);			
							_stop_h();
							break;
					}
			}else{				
				switch(tevt.type){
					case "touchend":
						_stop_h();
						break;
				}
			}
		},igk.features.supportPassive?{passive:true}:false);
		}
		_update_v(0);
		// e.o.value = "70";
		// console.debug("done");
		
		
		// q.reg_event("curChanged", function(){
			// console.debug("sursor changed .....");
		// });
	}
	igk.system.createNS("igk.winui.uitrack",{
		version:"1.0",
		init:__init
	});	
	igk.winui.initClassControl("igk-winui-uitrack", __init);
})();