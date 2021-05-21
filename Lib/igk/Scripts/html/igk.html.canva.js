/*
file : igk.html.canva
description : represent a canvas document manager to used in canva
create: 22/07/2014

*/
"use strict";

(function(){
var static_canva;
var _sfigure = 0;//indicate a starting figure
		function __append_bezier( ctx,
			 x1,  y1,
             x2,  y2,
             x3,  y3)
        {
            ctx.bezierCurveTo(x1, y1,x2, y2, x3, y3);
        }
		function gdip_near_zero(v)
        {
            return ((v >= -0.0001) && (v <= 0.0001));
        }
		function append_ellipse(ctx,  x,  y,  width,  height)
        {
            var rx = width / 2.0;
            var ry = height / 2.0;
            var cx = x + rx;
            var cy = y + ry;
            var C1 = 0.552285;
            //const float C2 = 0.552285f;
            /* origin */
			cx.moveTo(cx + rx, cy);
			
            // __append(cx + rx, cy,
                // 0//enuGdiGraphicPathType.StartFigure
				// , false);
            /* quadrant I */
            __append_bezier(ctx, 
                           cx + rx,
                           cy - C1 * ry,
                           cx + C1 * rx,
                           cy - ry,
                           cx,
                           cy - ry);
            /* quadrant II */
            __append_bezier(ctx, 
                           cx - C1 * rx, cy - ry,
                           cx - rx, cy - C1 * ry,
                           cx - rx, cy);
            /* quadrant III */
            __append_bezier(ctx, 
                           cx - rx, cy + C1 * ry,
                           cx - C1 * rx, cy + ry,
                           cx, cy + ry);
            /* quadrant IV */
            __append_bezier(ctx, 
                           cx + C1 * rx, cy + ry,
                           cx + rx, cy + C1 * ry,
                           cx + rx, cy);
            // close the path 
            close_figure();
        };
		
		function __append_arcs(ctx,  x,  y,  width,  height,  startAngle,  sweepAngle)
        {
            var i;
            var drawn = 0;
            var increment;
            var endAngle;
            var enough = false;
            if (Math.abs(sweepAngle) >= 360)
            {
                __append_ellipse(ctx, x, y, width, height);
                //GdipAddPathEllipse (path, x, y, width, height);
                return;
            }
            endAngle = startAngle + sweepAngle;
            increment = (endAngle < startAngle) ? -90 : 90;
            /* i is the number of sub-arcs drawn, each sub-arc can be at most 90 degrees.*/
            /* there can be no more then 4 subarcs, ie. 90 + 90 + 90 + (something less than 90) */
            for (i = 0; i < 4; i++)
            {
                var current = startAngle + drawn;
                var additional;
                if (enough)
                    return;
                additional = endAngle - current; /* otherwise, add the remainder */
                if (Math.abs(additional) > 90)
                {
                    additional = increment;
                }
                else
                {
                    /* a near zero value will introduce bad artefact in the drawing (#78999) */
                    if (gdip_near_zero(additional))
                        return;
                    enough = true;
                }
                __append_arc(ctx, _sfigure && (i == 0), /* only move to the starting pt in the 1st iteration */
                         x, y, width, height, /* bounding rectangle */
                         current, current + additional);
                drawn += additional;
            }
        }

		function __append_arc (ctx, start, x,  y,  width,  height,  startAngle,  endAngle)
{
        var _2PI = (Math.PI * 2);
        var M_PI = 3.14159265358979323846;
        var delta, bcp;
        var sin_alpha, sin_beta, cos_alpha, cos_beta;
        var rx = width / 2;
        var ry = height / 2;
        /* center */
        var cx = x + rx;
        var cy = y + ry;
        /* angles in radians */
        var alpha = (startAngle * Math.PI / 180.0);
        var beta = (endAngle * Math.PI / 180.0);
        /* adjust angles for ellipses */
        alpha = Math.atan2(rx * Math.sin(alpha), ry * Math.cos(alpha));
        beta = Math.atan2 (rx * Math.sin (beta), ry * Math.cos (beta));
        if (Math.abs(beta - alpha) > M_PI){
            if (beta > alpha)
                beta -= _2PI;
            else
                alpha -= _2PI;
        }
        delta = beta - alpha;
        //// http://www.stillhq.com/ctpfaq/2001/comp.text.pdf-faq-2001-04.txt (section 2.13)
        bcp =( 4.0 / 3 * (1 - Math.cos (delta / 2)) / Math.sin (delta / 2));
        sin_alpha = Math.sin (alpha);
        sin_beta = Math.sin (beta);
        cos_alpha = Math.cos (alpha);
        cos_beta = Math.cos (beta);
        /* move to the starting point if we're not continuing a curve */
        if (start) {
                /* starting point */
                var sx = (cx + rx * cos_alpha);
                var sy = (cy + ry * sin_alpha);
				//console.debug(ctx);
				ctx.moveTo(sx,sy);
                //append ( sx, sy, enuGdiGraphicPathType.LinePoint , false );
        }
        __append_bezier (ctx,
               ( cx + rx * (cos_alpha - bcp * sin_alpha)),
               ( cy + ry * (sin_alpha + bcp * cos_alpha)),
               (cx + rx * (cos_beta + bcp * sin_beta)),
               (cy + ry * (sin_beta - bcp * cos_beta)),
               (cx + rx * cos_beta),
               (cy + ry * sin_beta));
}
//canva utility function

function __addArc(ctx, rc, starta, sweepa){

	__append_arcs(ctx, rc.x, rc.y, rc.w, rc.h, starta, sweepa);
};
function __build_round_rec(ctx, rc, rounddef){

			var vtl_dx =!rounddef?0.1:Math.max(rounddef.topLeft.x * 2, 0.1);
            var vtl_dy =!rounddef?0.1:Math.max(rounddef.topLeft.y * 2, 0.1);
            var vtr_dx =!rounddef?0.1:Math.max(rounddef.topRight.x * 2, 0.1);
            var vtr_dy =!rounddef?0.1:Math.max(rounddef.topRight.y * 2, 0.1);
            var vbr_dx =!rounddef?0.1:Math.max(rounddef.bottomRight.x * 2, 0.1);
            var vbr_dy =!rounddef?0.1:Math.max(rounddef.bottomRight.y * 2, 0.1);
            var vbl_dx =!rounddef?0.1:Math.max(rounddef.bottomLeft.x * 2, 0.1);
            var vbl_dy =!rounddef?0.1:Math.max(rounddef.bottomLeft.y * 2, 0.1);
            // Rectanglef v_rect = this.Bounds;
            // PathSegment pSegment = new PathSegment();
			_sfigure = 1;
			ctx.beginPath();
            __addArc(ctx, {x:rc.x, y:rc.y,w: vtl_dx,h: vtl_dy}, 180.0, 90.0);
			_sfigure  = 0;
			ctx.lineTo(rc.x + rc.w -rounddef.topRight.x, rc.y);
			
            __addArc(ctx,{x:
                rc.x + rc.w - vtr_dx, y:rc.y,
                w:vtr_dx, h:vtr_dy}, -90.0, 90.0);
				
			ctx.lineTo(rc.x + rc.w, rc.y + rc.h - (vbr_dy/2.0));
            __addArc(ctx, {x:
                rc.x + rc.w - vbr_dx, y:rc.y + rc.h - vbr_dy,
                w:vbr_dx, h:vbr_dy}, 0.0, 90.0);
				
			ctx.lineTo(rc.x + (vbl_dx/2.0), rc.y + rc.h);
            __addArc(ctx,{x:
                    rc.x, y:rc.y + rc.h - vbl_dy,
                    w:vbl_dx, h:vbl_dy}, 90.0, 90.0);
            ctx.lineTo(rc.x , rc.y + (vtl_dy/2.0));
		ctx.closePath();
};

function _build_rec(ctx, x, y, w, h){
	ctx.beginPath();
	ctx.moveTo(x, y);
	ctx.lineTo(x+w, y);
	ctx.lineTo(x+w, y+h);
	ctx.lineTo(x, y+h);	
	ctx.closePath();
};
function _build_circle(ctx, c, r){
	//@c:center
	//@r:radius
	ctx.beginPath();
	ctx.arc(c.x, c.y, r, 0, Math.PI * 2, false);	
	ctx.closePath();
};


igk.system.createNS("igk.html.canva", {
	canvaDocument: function(){//create a class. call this function with new operator
		igk.appendProperties(this,{
			index: 0,			
			toString: function() {
				return "canvaDocument";
			}
		});		
		return this; //referered object
	},
	canvaObj : function(id, code){//id and code expression
		this.id = id;
		this.code = code;
		this.matrix = new function(){
			this.m_matrix = new igk.math.matrix3x3();
			var self = this;
			this.transform=function(v_context){	
				
				var e = self.m_matrix.getElements();
				v_context.setTransform(e[0], e[1],e[3], e[4], e[6],e[7]);
			};
			this.reset=function(){
				self.m_matrix.reset();
			};
			this.rotate=function(angle){
				self.m_matrix.rotate(angle);
			};
			this.translate=function(dx, dy){
				self.m_matrix.translate(dx, dy, 0);
			};
			this.scale = function(ex, ey){
				self.m_matrix.scale(ex, ey, 1);
			};
			this.toString=function(){return "canvaObjInternalMatrix";};
		};
		igk.appendProperties(this, {
			render: function(v_context){	
				if (!v_context)
				{
					return;
				}
				v_context.save();
				this.matrix.transform(v_context);
				eval(this.code);	
				v_context.restore();				
			},
			translate: function(x, y){
				this.matrix.translate(x,y);
			},
			resetTransform: function(){
				this.matrix.reset();
			}
		});
	},
	canvaObjDocument:function(context, width, height){//contains a collection of canvaObj
		if (context == null)
			throw new Exception("canvaObjDocument require a context");
		var m_count =0;
		var m_childs = [];
		var m_names = {};
		var m_context = context;
		var m_w = width;
		var m_h = height;
		var self = this;
		igk.appendProperties(this, {
			getCount:function(){return m_childs.length; },
			getElementById: function(id){
				var e = null;
				for(var i = 0; i < m_childs.length; i++)
				{
					e =  m_childs[i];
					if (e.id==id)
						return e;
				}
				return null;
			},
			render: function(v_context){
				for(var i in m_childs)
				{
					m_childs[i].render(v_context);
				}
			},
			add: function(id, v_code){
				if (!m_names[id]){
					var t = new igk.html.canva.canvaObj(id, v_code);
					m_names[id] = t;
					m_childs.push(t);
				}
			},
			update: function(){
				m_context.clearRect(0,0,m_w, m_h);
				this.render(m_context);
			},
			toString: function() {
				return "canvaObjDocument"
			}
		});
	},
	//canvas utility fonction
	drawText: function(cx,t,x,y, ft, st, maxwith){
		cx.font = ft;
		cx.fillStyle = st;
		cx.fillText(t,x,y, maxwith);
	},
	buildCircle: _build_circle,	
	buildRect: _build_rec,
	drawRoundRec: function(cx, rc, rounddef, close){
	
		//cx.strokeRect(rc.x, rc.y,rc.w,rc.h);
		__build_round_rec(cx, rc, rounddef);
		cx.stroke();
	},
	drawRect2p: function(cx, sp, ep){
		_build_rec(cx, 
			Math.min(sp.x, ep.x), Math.min(sp.y, ep.y), 
			Math.abs(sp.x - ep.x), 
			Math.abs(sp.y - ep.y)); 
		cx.stroke();
	},
	fillRect2p: function(cx, sp, ep){
		_build_rec(cx, 
			Math.min(sp.x, ep.x), Math.min(sp.y, ep.y), 
			Math.abs(sp.x - ep.x), 
			Math.abs(sp.y - ep.y)); 
		cx.fill();
	},
	drawLine: function(cx, st, en){
		cx.beginPath();
		cx.moveTo(st.x, st.y);
		cx.lineTo(en.x, en.y);
		cx.stroke();
	
	},
	fillRoundRec: function(cx, rc, rounddef, close){
		
		cx.beginPath();
		cx.moveTo(rc.x, rc.y);
		__addArc(cx, rc, 180,270);
		if (close)
		cx.closePath();
		cx.fill('evenodd');
		
	},
	drawArc: function(cx, rc, starta, sweepa){
		__addArc(cx, rc, starta,sweepa);
		cx.stroke();
	},
	fillArc: function(cx, rc, starta, sweepa){
		__addArc(cx, rc, starta,sweepa);
		cx.fill('eventodd');
	},
	
	loadObj: function(uri, callback){
		igk.ajx.get(uri,null,  function(xhr){
			if (this.isReady()){
				var obj = eval(xhr.responseText);
				//console.debug(f);
				//var obj = igk.JSON.parse(xhr.responseText);
				//console.debug("loead ....");
				//load 
				if (obj && obj.length == 1)
				{
					callback(obj[0]);
				}
					
			}
		});
	}
	
});


var coph =null; //canva_obj_prop_host
function __init_gkds_canva_obj(){

		var self = this;
		var a=this.getAttribute("igk-canva-gkds-obj-data");
		//this.setTransition("all 1s ease-in-out");
		//self.addClass("bdr-1");
		var data = igk.JSON.parse(a);
		if (data){			
			this.setTransition(data.transition || "all 1s ease-in-out");
			
			igk.html.canva.loadObj(data.uri, function(o){
						// console.debug("load data");
						if (typeof(o.length) == 'undefined'){
							self.setHtml("");
							var c = self.add("canvas");
							// c.addClass("posab");
							// c.o.width = igk.getNumber(self.getComputedStyle("width")); //o.w;
							// c.o.height= igk.getNumber(self.getComputedStyle("height"));//o.h;
							// self.add("div").setHtml("info");
							
							
							
							var p = 
								{
									canva: c,
									obj: o,
									getElementById: function(id){
										var q = this.obj;
										var l = null;
										var j = 0;
										var m = 0;
										var i = 0;
										var p = null;
										while(q){
											if (q.id && (q.id == id))
												return q;
											if ((m == 0) && q.layers ){
												m = 1;
												l = q.layers[j];
												p = q; //backup p
												j++;
												i=0;
												q = l[i];
												i++;
												continue;
											}
											else{
												if (m==1){//found on a layer
													if (i< l.length){
														q = l[i];
														i++;
														continue;
													}
													else if (j< p.layers.length){ 
														l = p.layers[j];
														j++;
														i=0;
														q = l[i];
														i++;
														continue;
													}
												}
											}
											break;
										}
									},
									render: function(ini){
										//console.debug(this);
										if (this.rendering)
											return;
										var ctx = this.canva.o.getContext("2d");
										var q = this;
										var w = this.canva.o.width;
										var h = this.canva.o.height;
										function __render(){
										ctx.clearRect(0,0,w, h);
										if (data.init)
												data.init.apply(self, [ctx]);
											igk.html.canva.render_document_obj(ctx, q.obj);
										};
										function __transition(evt){
											//console.debug("transitioneend");
											complete= true;
											q.rendering = false;
											self.unreg_event('transitionend', __transition);	
										};										
										
										// console.debug(self.getComputedStyle('color'));
										// console.debug(self.getComputedStyle('color', ':hover'));
										// console.debug(self.o.style);
										// console.debug("transition : "+getComputedStyle(self.o, 'transition'));
										if (ini || (getComputedStyle(self.o)['transitionDuration'] == "0s")){
											//no transition
											__render();
										}
										else {
											var complete=false;
										
										    self.reg_event('transitionend', __transition);										
											igk.html.canva.animate(function(t){
											//console.debug("render "+complete);
											__render();											
											return !complete;
											});
											q.rendering = true;
										}
										// console.debug(getComputedStyle(self.o)['transitionDuration']);
									}
								};
							
							igk.appendProperties(self.data, {
								"igk-canva-gkds-obj":p
							});
							p.render(true);
							self.reg_event("mouseenter", function(){ p.render(); });
							self.reg_event("mouseout", function(){
								if (igk.navigator.isFirefox())
								{//solution : for firefox post rendering...								
									setTimeout(function(){ p.render(); }, 20);
								}
								else
									p.render(); 
							
							});
						}
						
					
					// console.debug("check get element by id");
					// console.debug(p.getElementById("Text_29145996"));
					
					
					});
	
		}
}
igk.system.createNS("igk.html.canva",{
	render_document_obj: function(ctx, obj){
		//used to render a document on a canva context
		var v_ctx = ctx;
		
		for(var i = 0; i < obj.layers.length; i++){
			var l = obj.layers[i];
			for(var j=0; j<l.length; j++){
				
				eval(l[j].p);
				ctx.fill("evenodd");
				ctx.stroke();
			}
		}
	}
});

//register  class object
igk.winui.initClassControl("igk-canva-gkds-obj",function(){
	__init_gkds_canva_obj.apply(this);		
}, 
{desc: "item to render canva object"}
);

// register on ready data 
// igk.ready(function(){
	
	// $igk(".igk-canva-gkds-obj").each(function(){
		// __init_gkds_canva_obj.apply(this);		
		// return true;
	// });	
	// console.debug(igk.get_class_obj()["igk-canva-gkds-obj"]);
// });
// igk.ctrl.registerReady(function(e){			
// if (igk.system.regex.item_match_class("igk-canva-gkds-obj", this))
// {			
// console.debug("match class .....");
// __init_gkds_canva_obj.apply($igk(this));			
// }	
// });
// igk.ajx.fn.registerNodeReady(function(){
// console.debug(this);
// });

})();
