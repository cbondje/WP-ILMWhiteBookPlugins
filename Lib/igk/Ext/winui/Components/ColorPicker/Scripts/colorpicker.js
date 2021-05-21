//color picker js
"use strict";
(function(){

	function __colorPicker(p){
		var q = $igk(p);
		
		
		igk.appendProperties(this, {
			init: function(){
				var _r = q.select("#clr").getItemAt(0);
				var _g = q.select("#clg").getItemAt(0);
				var _b = q.select("#clb").getItemAt(0);
				var _o = igk.createNode("div");
				var e = "igk-trackchange";
				var r=0, g=0, b = 0;
				q.o.appendChild(_o.o);
				function _update_cl()
				{
					var txt = "rgb("+r+", "+g+", "+b+")";
					_o.setHtml(txt +  " #"+
					igk.system.convert.ToBase(r,16,2) +
					igk.system.convert.ToBase(g,16,2) +
					igk.system.convert.ToBase(b,16,2) 
					);
					_o.setCss({backgroundColor: txt});
					if(q.getAttribute("demo")){				
						var gp = $igk(".igk-row").setCss({backgroundColor: txt});
					}
					//console.debug(document.getElementsByClassName("igk-row"));//).setCss({backgroundColor: txt});
					
				}
				_r.postRegisterEvent(e, function(evt){
					r = parseInt(255 * (evt.value/100.0));
					_update_cl();
				});
				_g.postRegisterEvent(e, function(evt){
					g = parseInt(255 * (evt.value/100.0));
					_update_cl();
				});
				_b.postRegisterEvent(e, function(evt){
					b = parseInt(255 * (evt.value/100.0));
					_update_cl();
				});
			}
		});
	}
	
	function __circlecolorPicker(p)
	{
		var RADIUS = 110;
		var rRadius = 255;
		var q = $igk(p);
		var m_cl ={r:0,g:0, b:0};
		var m_angle=0;
		var m_d = 0;
		
		var r = q.select(".igk-circ-pan").getItemAt(0);
		var m_st = false;
		var cur = igk.createNode("div");
		
		var trackv = q.select(".igk-circ-v").getItemAt(0);
		var trackb = q.select(".igk-trb").getItemAt(0);
		var m_data = igk.JSON.parse(q.getAttribute("igk-data"));
		
		var tvalue = q.select("#clvalue").getItemAt(0);
		if (tvalue){
			tvalue.addClass("alignc");
		}
		trackb.o['trb']= {
			trackv: trackv,
			update:function(v){
				this.trackv.setHtml(v);
				rRadius = parseInt(v * 255 / 100);				
				m_cl = __getColor(m_angle, m_d);
				__updateview();
			}
		};
			
		trackb.o.setAttribute("igk-trb-data", "{update: function(d){ if (!d.bar.rep) d.bar.rep = d.bar.target.o['trb']; d.bar.rep.update(d.progress); } }");
	
		
		cur.setCss({
		border:"1px solid black",
		backgroundColor:"white",
		width:"4px",
		height:"4px",
		position:'absolute'
		});
		
		var ti= igk.createNode("div");
		ti.setCss({
		border:"1px solid #444",
		backgroundColor:"white",
		width:"100%",
		top:'50%',
		height:"1px",
		position:'absolute'
		});
		var tr= igk.createNode("div");
		tr.setCss({
		border:"1px solid #444",
		backgroundColor:"white",
		width:"1px",
		height:"100%",
		top:"0px",
		marginLeft:"50%",
		position:'absolute'
		});
		//r.addClass("posr");
		
		r.o.appendChild(cur.o);
		r.o.appendChild(tr.o);
		r.o.appendChild(ti.o);
		
		q.addEvent("igk-valuechange", {value:null, hexValue:null});
		
		igk.appendProperties(this,{
			init: function(){			
				var W = r.getWidth();
				var H = r.getHeight();	
				cur.setCss({
						"left": ((W/2))+"px",
						"top": ((H/2))+"px"
						});
			}
		});
		function __getAngle(x1, y1, x2, y2)
		{
			var dx, dy;
            dx = x2 - x1;
            dy = y2 - y1;
            if ((dx == 0.0) && (dy == 0.0))
            {
                return 0.0;
            }
            if (dx == 0.0)
            {
                if (dy > 0)
                {
                    return (Math.PI / 2.0);
                }
                else
                    return (-Math.PI / 2.0);
            }
            var angle = Math.atan2(dy, dx);
            if ((dx < 0) && (dy<0))
                 angle += (2*Math.PI);
            return angle;
		}
		function __getColor(a,r){
			var h = (a * 255 / 360.0);
            var s = (r / 110) * 255.0;
            var v = rRadius;
            if (h < 0)
                h = 255 + h;
            //h = 360;
			var t = {h:h, s:s, v: v};
            var m =  igk.system.color.HSVtoColor(t.h,
                t.s,
                t.v);
			/// TODO:  FIX COLOR PICKER
			// console.debug(t, m);
			return m;
		}
		
		function __updateview(){
				var cl = m_cl;
				var txt = "rgb("+cl.r+", "+cl.g+", "+cl.b+")";
				if(q.getAttribute("demo")){									
						var txt = "rgb("+cl.r+", "+cl.g+", "+cl.b+")";
						var gp = $igk(".igk-row").setCss({backgroundColor: txt});
				}
				var e = q.o["igk-valuechange"];
				e.value = txt;
				e.hexValue ="#"+
					igk.system.convert.ToBase(cl.r,16,2) +
					igk.system.convert.ToBase(cl.g,16,2) +
					igk.system.convert.ToBase(cl.b,16,2);
				if (tvalue !=null){
					tvalue.o.value = e.hexValue;
				}
				if (m_data && m_data.update)
				{
					m_data.update.apply(q, [e]);
				}				
				//DAISE EVENT for data changed	
				q.raiseEvent("igk-valuechange");
			
		};
		function __update(evt)
		{
			if (!m_st)
					return;
				var l = r.getScreenLocation();
				var W = r.getWidth();
				var H = r.getHeight();
				// var m_s =  parseInt((Math.max(0, Math.min( evt.clientX - l.x, W)) / W) * 10000) / 100;				
				// var m_y =  parseInt((Math.max(0, Math.min( evt.clientY - l.y, H)) / H) * 10000) / 100;				
				
				//position in x
				var Cx = W/2;
				var Cy = H/2;
				//calculate radius
				var m_s =  parseInt( Math.max(0, Math.min( evt.clientX - l.x, W)) - Cx);
				var m_y =  parseInt( Math.max(0, Math.min( evt.clientY - l.y, H)) - Cy);
				var d = Math.sqrt((m_s * m_s) + (m_y * m_y));
				var PI = 3.1415926;
				m_angle = __getAngle( 0, 0, m_s, m_y) * 180/ PI;
				d = Math.min(RADIUS, d);
				m_d = d;
				//console.debug( parseInt(Math.atan2(0.5,0.5) * 18000/PI )/100.0);
				var loc = {
				x: Cx-2 + parseInt((d * Math.cos(m_angle * PI / 180))),
				y: Cy-2 + parseInt((d * Math.sin(m_angle * PI / 180)))
				};
				cur.setCss({
				"left": loc.x+"px",
				"top": loc.y+"px"
				});
				m_cl = __getColor(m_angle, d);
				__updateview();
		}
		
		r.reg_event("mousedown", function(evt){	
			if (m_st)
				return;
				m_st = true;
				igk.winui.mouseCapture.setCapture(q.o);
				__update(evt);
			});	
			r.reg_event("mousemove", function(evt){				
				if (!m_st)
					return;
				__update(evt);
			});	
			r.reg_event("mouseup", function(evt){
				
				igk.winui.mouseCapture.releaseCapture();
			if (!m_st)
				return;
				__update(evt);
				m_st =false;
				
				//document.releaseCapture();
			});	
	}
	igk.system.createNS("igk.winui.components.colorpicker", {
		init:function(){
			var q = igk.getParentScript();
			if (q)
			{
				var p = new __colorPicker(q);
				igk.ready(function(){ p.init();})
			}	
		}
	});

	igk.system.createNS("igk.winui.components.circleColorPicker", {
		init: function(){
			var q = igk.getParentScript();
			if (q)
			{
				var p = new __circlecolorPicker(q);
				igk.ready(function(){ p.init();})
			}	
		}
	});
	
})();
