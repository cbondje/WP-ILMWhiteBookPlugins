"uses strict";
//component: canvas editor surface
//author: C.A.D BONDJE DOUE
//release: 09-12-15
//copyright : igkdev @ balafon license.txt
(function(){



function __init_canva_editor(){
	var q = this;
	var data={};	
	var c  = q.add("canvas");
	var m_items = new igk.system.collections.list();
	var ms_down = false;
	q.data["canva_editor"]= data;
	
	igk.appendProperties(q, {
		addItem: function(i){
			m_items.add(i);
			
		},
		removeItem:function(i){
			m_items.remove(i);
		},
		save:function(){
		//save as
		},
		clear:function(){
			m_items.clear();
			data.render();
		},
		print: function(){
		}
	});
	if (c.istouchable()){
		c.reg_event("touchstart", function(evt){
				evt.preventDefault();
				var e = igk.winui.GetChildTouchLocation(c, evt);
				var b = igk.winui.mouseButton.Left;
				ms_down = 1;		
				data.mecanism.start(b, e.x, e.y);
				igk.winui.mouseCapture.setCapture(c.o);
				data.render();
			
		});
		c.reg_event("touchmove", function(evt){		
		if (!ms_down){
			return;
		}
		var e = igk.winui.GetChildTouchLocation(c, evt);
		var b = igk.winui.mouseButton.Left;
		data.mecanism.update(b, e.x, e.y);
		data.render();
			
		});
		c.reg_event("touchend", function(evt){			
			ms_down= 0;
			igk.winui.mouseCapture.releaseCapture();
			data.render();
		});
		
		c.reg_event("touchcancel", function(evt){			
			console.debug("cancel");
			ms_down= 0;
			igk.winui.mouseCapture.releaseCapture();
			data.mecanism.cancel();
			data.render();
		});
	}
	// else{	
	// console.debug("ereg douwn");
	c.reg_event("mousedown", function(evt){
		if (ms_down)
			return;
			
		var e = igk.winui.GetChildMouseLocation(c, evt);
		var b = igk.winui.mouseButton(evt);	
		ms_down = 1;		
		data.mecanism.start(b, e.x, e.y);
		igk.winui.mouseCapture.setCapture(c.o);
		data.render();
	});
	c.reg_event("mousemove", function(evt){
		if (!ms_down){
			return;
		}
		var loc = igk.winui.GetRealScreenPosition(c.o);
		var e = igk.winui.GetChildMouseLocation(c, evt);
		var b = igk.winui.mouseButton(evt);				
		data.mecanism.update(b, e.x, e.y);
		data.render();
		
	});
	c.reg_event("mouseup", function(){
		ms_down= 0;
		igk.winui.mouseCapture.releaseCapture();
		data.render();
	});
	c.reg_event("mouseleave", function(){
		//ms_down = 0;
		data.render();
	});
	
	// }
	
	function __init_rendering_data(){
		var m_zoomX = 1.0;
		var m_zoomY = 1.0;
		var m_posX =0.0;
		var m_posY =0.0;
		igk.appendProperties(this,{});
		
		igk.defineProperty(this, "ZoomX", {get:function(){return m_zoomX; }});
		igk.defineProperty(this, "ZoomY", {get:function(){return m_zoomX; }});
	};
	igk.appendProperties(data, {
		renderData: new __init_rendering_data(),
		mecanism:null,
		ctx : c.o.getContext('2d'),
		render: function(){
			var ctx = this.ctx;
			var w = c.getComputedStyle("width");
			var h = c.getComputedStyle("height");
			
			
			
			c.setAttribute("width", w);
			c.setAttribute("height", h);			
			ctx.clearRect(0,0,w,h);		
			
			
			for(var i = 0; i < m_items.getCount(); i++){
				var v_q = m_items.getItemAt(i);
				if (v_q){
					v_q.render(ctx, this.renderData);
				}				
			}
			if (this.mecanism){
				//this.mecanism.render(ctx);
			}
		}
	});
	
	data.mecanism = new igk.app.canvaEditor.mecanisms.CreateMecanism("circle");
	//igk.show_notify_prop(data.mecanism);
	data.mecanism.setParent(q);
	data.render();
};


(function(){
igk.system.createNS("igk.app.canvaEditor", {
	mecanisms: {
		Bases: function(){
			//.ctr : init global mecanims base			
			var m_fill = "transparent";
			var m_stroke = "#000";
			var m_p=null;
			var m_st = new igk.math.vector2d(0,0);
			var m_se = new igk.math.vector2d(0,0);
			igk.appendProperties(this, {
				initBrush:function(ctx){
					
				},
				start:function(b, x,y){
					m_st.x = x;
					m_st.y = y;					
					m_se.x = x;
					m_se.y = y;
					
				},
				update:function(b, x,y){},
				complete:function(b, x,y){},					
				render:function(ctx){
				},
				getParent:function(){
					return m_p;
				},
				setParent:function(p){
					m_p = p;
				},
				toString:function(){
					return "BaseMecanism";
				}
			});
		}
	}
});
var __super = igk.app.canvaEditor.mecanisms.Bases;
igk.system.createNS("igk.app.canvaEditor.mecanisms",{
	CreateMecanism: function(name){
		if (typeof(igk.app.canvaEditor.mecanisms[name]) != igk.constants.undef){
			return new igk.app.canvaEditor.mecanisms[name]();
		}
		//__mecanism.push(name);
		var s = new igk.app.canvaEditor.mecanisms.line();
		s.objecttype=name;
		return s;
	},
	line: function(){
		//define line mecanism
		__super.apply(this);	
		var m_s = igk.math.vector2d(0,0);
		var m_e = igk.math.vector2d(0,0);
		var m_o = null;
		igk.appendProperties(this, {
				objecttype: "line",
				start:function(b, x,y){				
					m_o = new igk.app.canvaEditor[this.objecttype]();
					this.getParent().addItem(m_o);
					m_s.x = x;
					m_s.y = y;
					m_o.Start = m_s.clone();
					m_o.End = m_s.clone();					
				},
				update:function(b, x, y){
					if (!m_o){
						return;
					}
					m_e.x = x;
					m_e.y = y;
					m_o.End = m_e.clone();
				},
				end:function(b, x,y){
					this.update(b, x, y);
					m_o = null;					
				},
				render:function(ctx,data){					
					//overlay the line item
					//ctx.save();
					if (m_o){						
						igk.html.canva.drawLine(ctx, m_s, m_e);
						
					}					
					//ctx.restore();
				},
				toString: function(){
					return "line";
				}
		});		
	}
});

})();

(function(){
//object Object
igk.system.createNS("igk.app.canvaEditor", {
Bases:function(){
	igk.appendProperties(this, {
		render: function(ctx, data){
		}
	});
}
});

var __super = igk.app.canvaEditor.Bases;
//define object
igk.system.createNS("igk.app.canvaEditor", {
	line:function(){
		__super.apply(this);
		var m_st = igk.math.vector2d.empty();
		var m_se = igk.math.vector2d.empty();
		igk.appendProperties(this, {
			render: function(ctx, data){
				igk.html.canva.drawLine(ctx, m_st, m_se);
			}
		});
		
		igk.defineProperty(this, "Start", {get:function(){
			return m_st;
		},set:function(v){
			m_st = v;
		}});
		
		igk.defineProperty(this, "End", {get:function(){
			return m_se;
		},set:function(v){
			m_se = v;
		}});		
	},
	rectangle:function(){
		//inherit line
		igk.app.canvaEditor.line.apply(this);		
		igk.appendProperties(this, {
			render: function(ctx, data){				
				igk.html.canva.fillRect2p(ctx, this.Start, this.End);
				igk.html.canva.drawRect2p(ctx, this.Start, this.End);
			}
		});
	},
	circle:function(){
		//inherit rectangle
		igk.app.canvaEditor.rectangle.apply(this);		
		igk.appendProperties(this, {
			render: function(ctx, data){
				igk.html.canva.buildCircle(ctx, this.Start, this.End.distance(this.Start));
				ctx.fill();
				ctx.stroke();				
			}
		});
	}
	
});
})();

igk.winui.initClassControl("igk-canva-editor", __init_canva_editor);

})();


