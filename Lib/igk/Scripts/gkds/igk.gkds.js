//file: igk.wo.gkds script file

"use strict";
(function(){
	var m_offscreen = igk.createNode('canvas');	//create a offscreen canvas
	
	function __addPolygon(tab){
		var d= "";
		d += "ctx.moveTo("+tab[0].x+", "+tab[0].y+");";
		for(var i = 1; i < tab.length; i++){
			d += "ctx.lineTo("+tab[i].x+", "+tab[i].y+");";
		}
		return d;	
	}
	function __addClosedCurve(tab, t){
		var d= "";		
		//d += "ctx.moveTo("+tab[0].x+", "+tab[0].y+");";		
		var ctangents = __GetCurveTangents(1, tab, tab.length, t, false);// !this.Closed);		
        d += __appendCurve(tab, ctangents, 0, tab.length, true);
		return d;	
	}
	
	function __GetCurveTangents(terms, points, count, tension, open)
        {
            var coefficient = tension /3.0;
            var tangents = [];
            if (count <= 2)
                return tangents;
			
            for (var i = 0; i < count; i++)
            {
                var r = i + 1;
                var s = i - 1;
                if (r >= count)
                {
                    if (!open)
                    r = 0;// count - 1;
                    else
                    r = count - 1;
                }
                if (open)
                {
                    if (s < 0)
                        s = 0;
                }
                else
                {
                    if (s < 0)
                        s += count;
                }
				tangents[i] = new igk.math.vector2d((coefficient * (points[r].x - points[s].x)),
				(coefficient * (points[r].y - points[s].y)
				));
                // tangents[i].X += ;
                // tangents[i].Y += (coefficient * (points[r].Y - points[s].Y));
            }
            return tangents;
        }
	function append_bezier(x1, y1, x2, y2, x3, y3){
		return "ctx.bezierCurveTo("+x1+
		", "+y1+
		", "+x2+
		", "+y2+
		", "+x3+
		", "+y3+
		");";
	}
	function close_figure(){
		return "";
	}
	
	function __appendCurve(points,tangents, startindex, Length, closed){
            var ptype = ((closed) ||  (points.length == 0)) ? 
			0//start figure
			:
			1 // enuGdiGraphicPathType.LinePoint
			;
            var i, x1, x2, x3, y1, y2, y3;
			
            //append(points[startindex].X, points[startindex].Y , ptype, true);
			var d = "";
			d += "ctx.moveTo("+points[0].x+", "+points[0].y+");";
            for (i = startindex; i < startindex + Length-1; i++)
            {
                var j = i + 1;
                x1 = points[i].x + tangents[i].x;
                y1 = points[i].y + tangents[i].y;
                x2 = points[j].x - tangents[j].x;
                y2 = points[j].y - tangents[j].y;
                x3 = points[j].x;
                y3 = points[j].y;
                d += append_bezier(x1, y1, x2, y2, x3, y3);
            }
            //complete (close) the curve using the first point
            if (closed)
            {
				x1 = points[i].x + tangents[i].x;
				y1 = points[i].y + tangents[i].y;
				x2 = points[0].x - tangents[0].x;
				y2 = points[0].y - tangents[0].y;
				x3 = points[0].x;
				y3 = points[0].y;
                d += append_bezier(x1, y1, x2, y2, x3, y3);
                d += close_figure();
            }
			return d;
    };
	
	igk.system.createNS("igk.wo",{
		renderitem: function(){
			var m_childs = [];
			var m_n = null;//node
			var m_p = null;//parent
			
			var m_matrix = new igk.wo.gkds.matrix();
			
			function __getFillBrush(m){
				var b = igk.wo.gkds.extractdef(m.trim());
				var d = "";
				if(b == null)
					return d;
				
				//
				if (b.Type){
					switch(b.Type.toLowerCase()){
						case "solid":
							d+="ctx.fillStyle = '"+window.igk.system.colorFromString(b.Colors)+"';";
							
							var a = ns_igk.system.colorGetA(b.Colors);							
							if (a<1){
								d+= "ctx.globalAlpha = "+a+";";
							}
							
							return d;
						case "lineargradient":
		d += "if (!grd) var grd =null; grd = ctx.createLinearGradient(0, 0, 150, 0);";
		var tb = b.Colors.split(' ');
      for(var i = 0; i < tb.length; i++)
	  {
		var cl = tb[i];
		
		d += "grd.addColorStop("+i+",  '"+window.igk.system.colorFromString(cl)+"');";   
	  }
      d += "ctx.fillStyle = grd;";
							break;
					}
				}
				return d;
			};
			
			igk.appendProperties(this, {
				$super: this,
				contains: function(x,y){
					var ctx = m_offscreen.o.getContext('2d');
					// this.render(m_offscreen.o, ctx, {});
					// var b = ctx.isPointInPath(x,y);
					//console.debug("is contains path"+b);					
					return false;
				},
				toString: function(){
					if (m_n){
						return "gkdsitem#"+m_n.o.tagName+":"+this.getType();
					}
					return "gkdsitem";
				},
				getType: function(){
					return "no-type";
				},
				getParent: function(){
					return m_p;
				},
				getGkds: function(){
					if (m_gkds!=null)
						return m_gkds;
					return m_p ? m_p.getGkds(): null;
				},
				clear: function(){
					m_childs = [];
				},
				loadChilds: function(n){				
				var ln =  n.o.childNodes.length;
				for(var i = 0; i < ln;i++)
				{
					var b = n.o.childNodes[i];
					if (b.tagName)
					{
						var k = b.tagName.toLowerCase();
						
						var ii = igk.wo.gkds.createItem(k, $igk(b));
						if (ii!=null){
							m_childs.push(ii);
							ii.m_p = this;								
						}
					}
				}
				},
				load: 	function (n){	
				if (!n)
				return;
				m_n = $igk(n);
				m_n.gkds = this;					
				if (n.o.childNodes){					
					this.loadChilds(n);
				}
			this.initialize();
		
		},
		initialize:function(){},
				loadMatrix: function(){
					var m = this.o.getAttribute("Matrix");				
					if (m){					
						this.Matrix.loadMatrixs(m);
						return this.Matrix.getCode();
					}
					return "";
				},
				getFillStyle: function(){
					var m = this.o.getAttribute("FillBrush");
					if (m){
						return __getFillBrush(m);
					}
					return "ctx.fillStyle = \"white\";";
				},
				getStrokeStyle: function(){
					var m = this.o.getAttribute("StrokeBrush");
					if (!m){
						return "ctx.strokeStyle = \"black\"; ctx.globalAlpha=0;";
					}
						var b= igk.wo.gkds.extractdef(m.trim());
						var d = "";
						if (b.Width){
							d+="ctx.lineWidth= "+b.Width+";";
						}						
						if (b.Type){
							switch(b.Type.toLowerCase()){
								case "solid":
									var cl = ns_igk.system.colorFromString(b.Colors);
									d+="ctx.strokeStyle = \""+cl+"\";";
									var a = ns_igk.system.colorGetA(b.Colors);
									
									if (a <1)
									{
										d+= " ctx.globalAlpha = "+a+"; ";
									}
									break;
							}
						}			
					return d;
				},
				getFillMode: function(){
					var m = this.o.getAttribute("FillMode");
					if (m == "Alternate"){
					}
					return "evenodd";
				},
				getStrokeM: function(){
					var d = "ctx.lineCap = \"butt\";";
					d += "ctx.lineJoin = \"miter\";";
					return d;
				},
				renderStyle:function(style){
					if (style==null)
						return "";
				var d = "";
				var fg = style.base ? "ctx.fillStyle = '"+ style.fillStyle +"';": this.getFillStyle();
				var fs = style.base ? "ctx.strokeStyle = '"+style.strokeStyle+"';": this.getStrokeStyle();
				
				
				var sm = style.base ? style.strokeMode : this.getStrokeM();
				var fm = style.base ? style.fillMode : this.getFillMode();
				if (style.base){
					d+= "ctx.globalAlpha=1;";
					d += fg;
					if (typeof(style.fillOpacity)!= "undefined"){
						d+= "ctx.globalAlpha="+style.fillOpacity+";";
					}
					d += "ctx.fill(\""+fm+"\");";
					d += fs; 
					if (typeof(style.strokeOpacity)!= "undefined"){
						d+= "ctx.globalAlpha="+style.strokeOpacity+";";
					}
				
					d += "ctx.stroke();";
				}
				else{
				
					if (style.fillStyle != fg){
						style.fillStyle = fg;				
					}
					d += "ctx.globalAlpha=1;";
					d += fg;
					if (style.fillMode != fm){
						style.fillMode += fm;					
					}
					
					d += "ctx.fill(\""+fm+"\");";
					
					if (style.strokeStyle != fs){
						style.strokeStyle += fs;										
					}
					d += fs; 
					
					if (style.strokeMode != sm){
						style.strokeMode=sm;
						d += sm;
					}
					d += "ctx.stroke();";
				}
				return d;
//"ctx.fillStyle = \"#ddd\";"+
//"ctx.fill(\"evenodd\");"+
//"ctx.strokeStyle = \"#000\";"+
//"ctx.lineCap = \"butt\";"+
//"ctx.lineJoin = \"miter\";"+
//"ctx.stroke();";

},						
				render: function(c, ctx, style){
					//c:canva zone
					//ctx: context	
					ctx.save();
					if (m_childs.length>0)
					{
						for(var i = 0; i < m_childs.length;i++)
						{	
							m_childs[i].render(c, ctx, style);
						}
					}
					ctx.restore();
				}
			});
		
			igk.defineProperty(this, "o", {
				get: function(){
					return m_n;
				}
			});
			
			igk.defineProperty(this, "Matrix", {
				get: function(){
					return m_matrix;
				}
			});
		},
		document: function(){
			igk.wo.renderitem.apply(this);
		},
		layer: function(){
			//layer container
			igk.wo.renderitem.apply(this);
			igk.appendProperties(this, {
				getType:function(){return "layer"; }
			});
		},
		nonvisible: function(){
			igk.wo.renderitem.apply(this);
			igk.appendProperties(this, {
				getType:function(){return "nonvisible"; }
			});
		},
		documents:function(gkds){
			//document container
			igk.wo.renderitem.apply(this);
			igk.appendProperties(this, {
				getGkds: function(){ return gkds;}
				}
			);
		},
		circle: function(){
			igk.wo.renderitem.apply(this);
			igk.appendProperties(this, {
			getCode: function(){
				return "";
			},
			loadMatrix: function(){
				var m = this.o.getAttribute("Matrix");				
				if (m){					
					this.Matrix.loadMatrixs(m);
					return this.Matrix.getCode();
				}
				return "";
			},
			render:function(c, ctx, style){			
				var cx = 0, cy = 0;
				var t = igk.math.vector2d.parse(this.o.getAttribute("Center"));
				var r = igk.wo.gkds.radius(this.o.getAttribute("Radius"));
				var d  = "";
				d = this.loadMatrix();
				cx = t.x;
				cy = t.y;
				
				if (typeof(r) != "object")
					d += " ctx.beginPath(); ctx.arc(cx, cy, "+r+",0, 2*Math.PI, false); ctx.closePath();";
				else {
					// console.debug("multicircle");
				
					for(var i = 0; i < r.length; i++){
						d += "ctx.beginPath();";
						if (i>0){
							var b = parseFloat(cx )+parseFloat(r[i]);
							d += "ctx.moveTo("+b+", cy);";	
						}
						// console.debug("ref "+i + "  "+r[i]);
						if ((i>0) && ((i%2)!=0)){
							d += "  ctx.arc(cx, cy, "+r[i]+",0, -2*Math.PI, false);";						
							//console.debug("ref "+i + "  "+r[i]);
						}
						else
							d += "  ctx.arc(cx, cy, "+r[i]+",0, 2*Math.PI, false);";						
						d += "ctx.closePath();";
					}
					d += "ctx.closePath();";
				}
				ctx.save();
				eval(d+ " "+ this.renderStyle(style));
				ctx.restore();
			},
			getType:function(){return "circle"; }
			});
		},
		star: function(){
			//sample : <Star FillMode="Alternate" OffsetAngle="0" Angle="16.69924" EnableTension="False" 
			//Tension="0" Center="50.29295;53.70821" Count="5" 
			//OuterRadius="41.76123" 
			//InnerRadius="20.88061" Id="Star_60814766" />
			//
			igk.wo.renderitem.apply(this);
			igk.appendProperties(this, {
			getCode: function(){
				return "";
			},		
			render:function(c, ctx, style){					
				var PI_C =   Math.PI/180.0;
				var t = this.o.getAttribute("Tension");				
				var et = igk.bool_parse(this.o.getAttribute("EnableTension"));
				var oa = (this.o.getAttribute("OffsetAngle") || 0) * PI_C;
				var fm = this.o.getAttribute("FillMode");	
				var vc = igk.math.vector2d.parse(this.o.getAttribute("Center"));
				var a = (this.o.getAttribute("Angle") ||0) * PI_C;
				var count = (this.o.getAttribute("Count") || 5) * 2;
				var ir = parseFloat( this.o.getAttribute("InnerRadius"));
				var or = parseFloat(this.o.getAttribute("OuterRadius"));
				
				var step = (360/count) * PI_C;
				var d  = "";
				d = this.loadMatrix();
				
				d += "ctx.beginPath();";
				var vtab = [];				
			for (var i = 0; i < count; i++)
			{
				if ((i % 2) == 0)
				{
					//for inner radius
					vtab.push(new igk.math.vector2d(
						(vc.x + (ir * Math.cos((i * step) + (a + oa)))),
						(vc.y + (ir * Math.sin((i * step) + a + oa)))
						));
				}
				else
				{
					vtab.push(new igk.math.vector2d (
						(vc.x + (or * Math.cos(i * step + a))),
						(vc.y + (or * Math.sin(i * step + a))))
						);
				}
			}
			// et = true;
			// t = 5.0;
			if (vtab.length > 1)
			{
				if (et)
					d += __addClosedCurve(vtab, t);
				else
					d += __addPolygon(vtab);
			}
				d += "ctx.closePath();";			
				ctx.save();
				eval(d+ " "+ this.renderStyle(style));
				ctx.restore();
			}});
		},
		polygon: function(){
			igk.wo.renderitem.apply(this);
			var c_center;
			var c_radius;
			var c_count;
			var c_angle;
			var c_etension;
			var c_tension;
			var c_def = "";
			//replace properties
			igk.appendProperties(this, {
				initialize:function(n){
					//this.$super.load(n);										
					c_count = parseInt($igk(this.o).getAttribute("count")) ||5;//.getElementsByTagName('PointTypes')[0]).getHtml().split(';');
					c_angle = parseFloat($igk(this.o).getAttribute("angle")) || 0;
					c_radius = $igk(this.o).getAttribute("radius").split(';');
					c_etension = $igk(this.o).getAttribute("enabletension");
					c_tension = parseFloat($igk(this.o).getAttribute("tension")) || 0;
					c_center = igk.math.vector2d.parse($igk(this.o).getAttribute("center"));
					
					
					c_def = "";
					if (c_count<=2)
						return;
					var vtab = new Array(c_count);
                    var step = (360 / c_count) * (Math.PI / 180.0);
                    var v_angle = (c_angle * Math.PI/ 180.0);
					var rd = 0;
                     for ( var  j = 0; j < c_radius.length; j++)
                     {
                         for (var i = 0; i < c_count; i++)
                         {
							rd = igk.math.vector2d.parse(c_radius[j]);
                            vtab[i] = new igk.math.vector2d(
                                 (c_center.x + rd.x * Math.cos(i * step + v_angle)),
                                 (c_center.y + rd.y * Math.sin(i * step + v_angle)));
                         }
                         if (c_etension)
                         {
							c_def += __addClosedCurve(vtab, c_tension);
                         }
                         else
							c_def+= __addPolygon(vtab); 
                         // console.debug(vtab);
                     }
                     
					
					
				},
				render: function(c, ctx,style){
					var d  = "";
					d = this.loadMatrix();
					d += "ctx.beginPath();";
					d += c_def;					
					d += "ctx.closePath();";			
					ctx.save();
					eval(d+ " "+ this.renderStyle(style));
					ctx.restore();
				}
			});
		},
		rectangle:function(){
			igk.wo.renderitem.apply(this);
			igk.appendProperties(this, {
			getCode: function(){//rectangle code
				return "";
			},		
			render:function(c, ctx, style){			
				var x = 0, y = 0, w=0, h=0;
				var t = igk.wo.gkds.rectangle(this.o.getAttribute("Bounds"));				
				var d  = "";
				d = this.loadMatrix();
				var x = t.x;
				var y = t.y;
				var w = t.w;
				var h = t.h;
				
				d += "ctx.beginPath();";
				d +=" ctx.rect(x,y,w,h);";					
				d += "ctx.closePath();";			
				ctx.save();
				eval(d+ " "+ this.renderStyle(style));
				ctx.restore();
			}});
		},
		arc:function(){
			igk.wo.renderitem.apply(this);
			igk.appendProperties(this, {//arch
			});
		},
		ellipse:function(){
			igk.wo.renderitem.apply(this);
			igk.appendProperties(this, {});
		},
		path: function(){
			igk.wo.renderitem.apply(this);			
			var types = [];
			var points = [];
			igk.appendProperties(this, {
				loadChilds:function(n){
				},
				initialize:function(n){
					//this.$super.load(n);						
					var p = this.o.getElementsByTagName('PointTypes')[0];
					
					var st = p.innerHTML;
				types =  igk.system.string.split(st+"", ';');
				// console.debug("types ;;;; ");
				// console.debug(p.innerHTML);
				// console.debug(types);
				
				points =  $igk(this.o.getElementsByTagName('Points')[0]).getHtml().split(' ');
					if (types.length != points.length)
					{
						console.debug("can't create path. length miss match");	
						return false;
					}				
				},
				render: function(c, ctx, style){
					var d  = "";
					d = this.loadMatrix();
				
					d += "ctx.beginPath();";
					var close = 0;
					 // StartFigure = 0,
        // LinePoint = 1, //line control moint
        // ControlPoint = 3,//bezier control point
        // Mask = 0x7,//masking. not used
        // Marker= 0x20, //marker for path iteration
        // EndPoint = 0x80 // end point
		var v_bezierPoint = [];
		function _jointBezier(pts){
			var s = "";
			
			for(var i = 0; i < pts.length; i++){
					if (i !=0)
						s +=",";
				s += pts[i].x +", "+pts[i].y;
			}			
			return s;
		};
		function _getPoint(pts){
			var pt = pts.split(';');
			pt[0] = parseFloat(pt[0]);
			pt[1] = parseFloat(pt[1]);
			return new igk.math.vector2d(pt[0] , pt[1]);
		};
		function _getBezierCurve(tab){
			var q0, q1, q2;
			// var q0 = tab[0];
			// var c1 = tab[1];
			// var c2 = tab[2];								
			// var q2 = tab[3];		
			
			// var q1 = {x: (3/4) * (c1.x + c2.x -  (q0.x/3)  - (q2.x/3)),
				// y:  (3/4) * (c1.y + c2.y -  (q0.y/3)  - (q2.y/3))};	
			if (tab.length==4){
				q0 = tab[1];
				q1 = tab[2];
				q2 = tab[3];
			}
			else {
				q0 = tab[0];
				q1 = tab[1];
				q2 = tab[2];
			}
				
			var d= "ctx.bezierCurveTo("+_jointBezier([q0,q1,q2])+");";	
			//d += "ctx.moveTo("+q2.x+", "+q2.y+");";               
			return d;
		};
		
		// console.debug("length "+types.length);
		var phc = 0; 
		var revert = 0;
		function __pushdata(data){
			d += data;
		};
		function __get_reservesePoint(types, points, dat){
			var ct = [];
			var cp = [];
			//first point
			// console.debug(dat.i);
			var index = dat.i;
			ct.push(types[index]);
			cp.push(points[index]);			
			var i = dat.i+1;
			var l =1;
			while(i< types.length){
				ct.push(types[i]);
				cp.push(points[i]);
				if((types[i]& 0x80) ==0x80)
					break;
				i++;
				l++;
			}
			// console.debug(ct);
			ct.reverse();
			cp.reverse();
			//update new point
			// console.debug(types);
			// console.debug(":::"+ct.length+ "::::"+l);
			// console.debug(ct);
			var loffset = 0;
			for (var j = 0; j<ct.length;j++){
				if (ct[j]==0)
					types[index+j] = 0x80 + loffset;//ct[j];
				else if ((ct[j]& 0x80) ==0x80)
				{
					loffset = (ct[j] -  0x80);
					types[index+j] = 0;
				}
				else
					types[index+j] = ct[j];
				points[index+j] = cp[j];
			}
			//dat.i = i;
			dat.ct = ct;
			dat.cp = cp;
			dat.l = l;
			return "/*reverse data*/";
		}
		var safreverse = false;
				for(var i = 0 ; i < types.length; i++){
					close = 0;
					var pt = _getPoint(points[i]);
					var t = parseInt(types[i]);
					// console.debug("type:"+t);
					if (t==0){
						//safari required a anti clockwise to evenodd graphics path
						phc++;
						if (safreverse && ((phc%2)==0)){					
							revert = 1;
							var dat = {i:i};
							d+= __get_reservesePoint(types, points, dat);
							// console.debug(types);
							i = dat.i;
						}
					}
					
					switch(t){
						case 0://start						
							//reset bezier
							v_bezierPoint = [];
							__pushdata("ctx.moveTo("+pt.x+", "+pt.y+");");      
						break;						
						case 1://line point
						  if (v_bezierPoint.length > 0)
							{
								
								console.debug("there are unfinshed bezier " + v_bezierPoint.length);
								//length must be 3
								if (v_bezierPoint == 3)
								{
									v_bezierPoint.push(pt);
									__pushdata(_getBezierCurve(v_bezierPoint));										
								}
								// switch (v_bezierPoint.length)                            
								// { 
									// case 4:
										// d += "ctx.bezierCurveTo("+_jointBezier(v_bezierPoint)+");";
										// v_bezierPoint = [];
										// break;
									// default :
										// break;
								// }
								
							}
							//else
							__pushdata("ctx.lineTo("+pt.x+", "+pt.y+");");
							v_bezierPoint = [];
						break;
						case 3: //bezier point
							v_bezierPoint.push(pt);
							switch(v_bezierPoint.length){
								case 3:																					
									// console.debug("addlines");
									__pushdata(_getBezierCurve(v_bezierPoint));
									v_bezierPoint = [];						
								break;
							}
						break;
						default:		
							var end = false;
							if ((t & 32) == 32){
								//marker point
								t -= 32;
							}
							if ((t & 0x80) == 0x80)
							{
								t -= 0x80;
								end = true;
							}
					if (end)					
					{
						 // console.debug("endef : "+t);
						//alert("close path ");					
						//d += "ctx.closePath();";	
						switch(t){
							case 1:
								__pushdata("ctx.lineTo("+pt.x+", "+pt.y+");");
								break;
							case 3:
								// console.debug("end with bezier :  "+v_bezierPoint.length);
								switch(v_bezierPoint.length){
									case 2:								
									v_bezierPoint.push(pt);
									__pushdata(_getBezierCurve(v_bezierPoint)); 
									break;
									// case 0:		
									// v_bezierPoint.push(pt);
									// v_bezierPoint.push(pt);
									// v_bezierPoint.push(pt);
									// __pushdata(_getBezierCurve(v_bezierPoint)); 
									// break;
									default:
										console.debug("error ...."+v_bezierPoint.length);
										break;
									
								}//error for else
								break;
						}
						v_bezierPoint = [];
						d+="ctx.closePath();";  						
						//close = 1;  
						/*
						switch(v_bezierPoint.length)
						{
							case 2:
								console.debug("333333333333333333333333333 ::: "+t);
								v_bezierPoint.push(new igk.math.vector2d(pt[0] , pt[1]));
								d+= "ctx.bezierCurveTo("+_jointBezier(v_bezierPoint)+");";
								v_bezierPoint = [];
								break;
							case 0 :
								d+= "ctx.lineTo("+pt[0]+", "+pt[1]+");";
								break;
							case 3:							
								d+= "ctx.bezierCurveTo("+_jointBezier(v_bezierPoint)+");";
								v_bezierPoint = [];								
								d+= "ctx.lineTo("+pt[0]+", "+pt[1]+");";								
								break;
							default :
								break;
						}*/
						    
						}
						break;						
					}
				}
				d +=" ";	
				
				//if (close==0)					
				d += "ctx.closePath();";	
				d += " "+ this.renderStyle(style);				
				ctx.save();
				// console.debug(d);
				eval(d);
				ctx.restore();
			}
		});
		},
		gkds: function(){		
			var m_items = [];
			var m_node = null;			
			igk.appendProperties(this, {
				canva:null,
				getElementsByTagName:function(tag){					
					return m_node.select(tag);
				},
				loadGkds: function(content){//load gkds content
					var xml = $igk(igk.createNode("dummy").setHtml(content).o.getElementsByTagName("gkds")[0]);//.ChildNodes[0]; //igk.xml.load(content);
					var self = this;
					if (xml){
						xml.select("documents").each(function(){
							var doc = new igk.wo.documents(self);							
							doc.load(this);
							doc.gkds = self;
							m_items.push(doc);
							return true;
						});
						//this.render();
						m_node = xml;
					}
					
				},
				render: function(canvaobj){					
					var c =	canvaobj? canvaobj.o : this.canva.o;
					var ctx = c.getContext ? c.getContext("2d") : null;
					if (ctx == null)
					{						
						return;
					}
					//basic style
					var s = {
					fillStyle:"#fff",
					strokeStyle:"#000",
					fillMode: "evenodd"
					};
					for(var i = 0; i < m_items.length ; i++)
					{
						m_items[i].render(c,ctx,s);
					}
				},
				getItemCount: function(){return m_items.length; },
				getItemAt: function(i){  if ((i>=0) && (i< m_items.length)) return m_items[i];  return null; }
			});
		}
	});
	igk.system.createNS("igk.wo.gkds",{
		brush: function(n){
			return "#000";
		},
		extractdef: function(m){
			var o = {};
			var tr = m.split(";");
			for(var i = 0; i < tr.length; i++){
				if (tr[i].trim() == "")continue;
				var b = tr[i].split(':');
				o[b[0]] = b[1];
			}
			return o;
		},
		rectangle: function(n){
			var t = {x:0,y:0,w:0,h:0};
			var h = n.trim().split(';');
			t.x = parseFloat(h[0]);
			t.y = parseFloat(h[1]);
			t.w = parseFloat(h[2]);
			t.h = parseFloat(h[3]);
			return t;
		},
		radius: function(n){
			if (!n)
				return 0;
			var t = n.split(' ');
			var r = null;
			if ((typeof(t)!="string") &&  (t.length>1)){
				r = [];
				for(var i = 0; i < t.length; i++){
					if (t[i].length > 0)
						r.push(parseFloat(t[i]));
				}
			}
			else 
				r = parseFloat(n);
			return r;
				
		},
		matrix: function(){
			var m_isIdentity= true;
			var m1, m2, m3, m4, m5, m6;
			m1 = m4 = 1;
			m2 = m3 = m5 = m6= 0;
			function _reset(){
				m1 = m4 = 1;
				m2 = m3 = m5 = m6= 0;
				m_isIdentity = true;
			}
			_reset();
			igk.appendProperties(this, {//matrix code
				getCode: function(){
					//note : use transform to transform to the current matrix
					//setTransform is to be avoid
					return "ctx.transform("+
					m1 + ", "+m2+","+
					m3 + ", "+m4+","+
					m5 + ", "+m6
					+");";
				},
				reset: _reset,
				loadMatrixs: function(s){
					var t = s.trim().split(',');
					switch(t.length)
					{
						case 6:
							m_isIdentity = false;
							m1 = parseFloat(t[0]);
							m2 = parseFloat(t[1]);
							m3 = parseFloat(t[2]);
							m4 = parseFloat(t[3]);
							m5 = parseFloat(t[4]);
							m6 = parseFloat(t[5]);
						break;
						case 16: //1,0,0,0,0,1,0,0,0,0,1,0,-99,33,0,1
							m_isIdentity = false;						
							m1 = parseFloat(t[0]);
							m2 = parseFloat(t[1]);
							m3 = parseFloat(t[4]);
							m4 = parseFloat(t[5]);
							m5 = parseFloat(t[12]);
							m6 = parseFloat(t[13]);
						default:
						break;
					}
				}
			});			
			igk.defineProperty(this, "isIdentity", {get: function(){ return m_isIdentity; }} );
		},
		createItem: function(tagname, n){
			if (typeof(igk.wo[tagname]) != "undefined")
			{			
				var b = new igk.wo[tagname]();
				b.load(n);
				return b;
			}
			// console.debug("not define "+tagname);
			var b = new igk.wo.nonvisible();
			b.load(n);
			return b;
			
		},
		createFrom: function(uri, canva){
			var t = new  igk.wo.gkds();
			t.canva = canva;
			function replacing(m){				
				var tag = m.trim().split(' ')[0].substring(1);				
				m = m.replace("/>", "></"+tag+">");
				return m;
			}
			function closeemptytag(s){
				var rg = new RegExp("((<)([^\/>])+(\/>))", "ig");			
				var b = s.replace(rg, replacing);								
				return s;
			}
			var xhr = new XMLHttpRequest(); 
			var p = $igk("#progress");
			//download file an progression demo
			igk.ajx.get(uri, null, function(xhr){
				if (this.isReady()){
					var s = xhr.responseText;					
					s = igk.html.closeEmptyTag(s);					
					t.loadGkds(s);
				}
				else{
					if ((xhr.readyState == 1) && p){
						xhr.onprogress = function(evt){						
							p.setHtml( Math.round(((evt.loaded/evt.total)*100))+"");						
						};
					}
				}
			});
		}
	});
	
	//load and initialize gkds tag data
	igk.ready(function(){
		var e = $igk("igk:gkds");
		e.each(function(){			
			var src = this.getAttribute("src");
			if (src)
			{	
			var div = igk.createNode("canvas");
			div.setAttribute("width", "400px");
			div.setAttribute("height", "300px");
			div.setCss({width: "400px", height: "300px", border:"none" });			
			igk.wo.gkds.createFrom(this.getAttribute("src"), div);
			this.o.parentNode.replaceChild( div.o,this.o);
			}
			return true;
		});
	});
})();



// //demo function 
// igk.ready(function(){
// Function.prototype.extend = function(parent){
	// var child = this;
	// child.prototype = parent;
	// child.prototype.$super = parent;
// //	new child(igk.system.array.slice(arguments,1));
	// child.prototype.constructor = child;
	
	// console.debug(child.prototype );
// };
// var Animal = {
	// name:"",
	// speak: function(){
		// console.debug("animal speak : "+this.name);
	// }
// };

// var Rabbit =  function(n){
	// console.debug("create new rabit");
	// this.name = n;
	// this.speak = function(){
	// console.debug("rabbit speak");
		// this.$super.speak.apply(this, arguments);
	// };
// };
// Rabbit.extend(Animal);

// var b1 = new Rabbit("b1");
// //var b2 = new Rabbit("b24");
// b1.speak();
// //b2.speak();
// });*/

// // (function(){
// // var d = igk.createNode("input");
// // igk.show_notify_prop(d.o);
// // })();
