"use strict";

(function(){
	igk.system.createNS("igk.google", {		
	});
	
	igk.system.createNS("igk.google.controls", {		
		addCircleWaiter: function (b){
			var n = igk.createNode("div");
			n.addClass("igk-google-circle-waiter");
			b.add(n);
			igk.ajx.fn.initnode(n.o);
		},
		addLineWaiter: function (b){
			var n = igk.createNode("div");
			n.addClass("igk-google-line-waiter");
			b.add(n);
			igk.ajx.fn.initnode(n.o);
		}
	});
	
	function __circle_waiter_init(s){

	//igk-anim-time-board defintion
	//width: rotation of the circle
	
		//console.debug("init circle waiter");
	var q = this;
	igk.appendProperties(q,{
		start:function(){
			if (_running)return;
			
			_do_anim();
			_running = 1;
			// console.debug("start");
		},
		stop:function(){
			_running =false;
			// console.debug("stop");
		}
	});
	
	//test line waiter
	// q.reg_event("click", function(){
		// //testing add cirle waiter
		// //igk.google.controls.addCircleWaiter(q.getParentNode());
		// //testing remove circle waiter
		// var p = q.getParentNode();
		// q.remove();
		// setTimeout(function(){
			// //p.add(q);
			// igk.google.controls.addCircleWaiter(p);
			// //igk.ajx.fn.initnode(q.o);
		// },
		// 3000);
	// });
	//
	var _running=true;
	var _dat=null;
	var _2PI = (2 * Math.PI );
	var _3PI2 = (Math.PI / 2)*3;
	var _10Deg= (30/360)*_2PI;
	var _ta = 0;
	igk.appendProperties(this.data,{
		canva: null,//canva zone
		dir:1,//direction
		penWidth: 2, //pen width
		oldClass:1,
		catchm:0,
		T1:0,
		T2:_3PI2,
		update: function(){
			var s = q.data.storyboard_1;
			if (q.data.catchm){
				q.data.catchm = 0;
				
				s.setCss({width:"100px"});
				q.data.T2 += _3PI2;
			}else{
				q.data.catchm = 1;
				q.data.T1 = q.data.T2 -_10Deg;
				s.setCss({width:"0px"});
			}
		},
		render: function(v, cl,of_set, bg){
			//console.debug("rendering..."+q.getisVisible());		
			//console.debug(this.canva.o);
				var w = igk.getNumber(this.canva.getComputedStyle("width"));
				var h = igk.getNumber(this.canva.getComputedStyle("height"));	
				//console.debug("Render : width  "+w + " height "+h +" v:"+v);
				var cx = w/2;
				var cy = h/2;
				var penw= this.penWidth || 4;
				var R = Math.min(w/2, h/2) - (penw/2);
				var r = Math.min(w/2, h/2) - (penw/2) - 10;
				
				//update the size
				this.canva.setAttribute("width", w);
				this.canva.setAttribute("height", h);
				
				var ctx = this.canva.o.getContext('2d');
				
				ctx.clearRect(0,0,w,h);
				//background
				ctx.strokeStyle  = ''+cl;
				
				if (bg){
				ctx.beginPath();
				ctx.fillStyle = "#ddd";
				ctx.arc(cx, cy, R, 0,  _2PI,false);
				ctx.closePath();
				ctx.fill();
				}
				
				ctx.lineWidth = penw;
				ctx.beginPath(); 
				var offset =  -(Math.PI / 2)+(v*_2PI);
				var _s = _getData();
				if (r>0){
					// ctx.arc(cx, cy, r, offset,  offset+  _3PI2 - ((_3PI2 - _10Deg)* of_set)  , false);
					if (!this.catchm){
					//ratraper
						ctx.arc(cx, cy, r, offset+ this.T1 + ((this.T2 - this.T1  - _10Deg)* of_set),  offset+  this.T2   , false);
						// _ta = (_3PI2 - _10Deg);
					}
					else{
						ctx.arc(cx, cy, r, offset+this.T1 ,  offset+ this.T2 + (_3PI2 * (1-of_set))	 , false);
						
					}
				}
			//	console.debug(cl);
				ctx.stroke();
				//delete not allowed in strict mode
				// var a = {ctx:ctx};
				//.dispose();
				// delete a.ctx;
				// console.debug(ctx);
		}
		
	});

	function _getData(){
		if (_dat ==null)
		{
		var _s = q.data.storyboard.getComputedStyle('content', ':before');
		var _t = /^"((.)+)"$/i.exec(_s);		
		_dat = igk.JSON.init_data({stop:'width', mode:1}, (_t? _t[1].replace(/\\\"/g,"\"")  : null) , function(s){				
			s.stop = (_t? _t[1] : null) || 'width';
		});
		
		q.data.penWidth = q.data.storyboard.getComputedStyle('border-size', ':before');
		}
		return _dat;
	}
	this.data.canva = this.add("canvas").addClass("igk-canva-bg");	
	
	this.data.storyboard = this.add("div").addClass("igk-anim-time-board i-1")
	.reg_event("transitionend", function(evt){	
		var _m = _getData();
		//console;debug('stransi end '+evt.propertyName);
		//get transitionned property
		if ((evt.target == q.data.storyboard.o) && (evt.propertyName==_m.stop)){
			//base of definition
			var _oc = q.data.oldClass;
			var _nc = ((_oc+1) % 5) || 1;
			if (q.data.dir==1){
			q.data.storyboard.setCss({'width':'0px'}).rpClass("igk-cl-"+_oc, "igk-cl-"+_nc);
			q.data.dir = -1;
			}
			else{
			q.data.storyboard.setCss({'width':'100px'}).rpClass("igk-cl-"+_oc, "igk-cl-"+_nc);
			//.rmClass("igk-cl-2").addClass("igk-cl-1");
			q.data.dir = 1;
			}
			q.data.oldClass = _nc;
		}
			// q.data.storyboard.remove();
			
	})
	.reg_event("transitionstart", function(evt){
		 // console.debug('transition start');
		
	})
	.setCss(
	{"width":"0px",
	"height":"0px"})
	.addClass("igk-cl-2")
	.setHtml(" ");
	//for following
	this.data.storyboard_1 = this.add("div").addClass("igk-anim-time-board i-2").reg_event("transitionend",function(evt){
		//console.debug(evt.target );
		var s = q.data.storyboard_1;
		if ((evt.propertyName=="width")&&( (evt.target == s.o))){
			q.data.update();			
		}
	});
	
	
		
	//for animation
		function _do_anim(){

			// console.debug("on document ?"+q.isOnDocument());
			if (!q.o.parentNode || !q.isOnDocument()){
				// console.debug("/!\\ can't run google circle waiter anim. no parent set");
				_running =false;
				 return false;
			}
					q.data.storyboard
					.setCss({width: '100px',height:'100px'})
					.rpClass("igk-cl-2", "igk-cl-1");
					
					q.data.storyboard_1
					.setCss({width:'100px'});
					//console.debug("do anim");
					 
					 q.data.render(0,'transparent', 0);
					 
					 igk.html.canva.animate(function(e){
					if (!q.o.parentNode){
						console.error("not animable no parent. stop animate canvas");
						return false;
					}
					if (!q.getisVisible()){
						//console.debug("not animable not visible ---1"+_running);
						return _running;
					}
						
					if (!q.data.end){
						var n = q.data.storyboard;
						var n1=q.data.storyboard_1;
						var x = igk.getNumber(n.getComputedStyle("width"));
						var y = igk.getNumber(n1.getComputedStyle("width"));
						var cl = n.getComputedStyle('color');
						//console.debug(y+" "+n.getComputedStyle("height"));
						if (q.data.dir == -1)
							x = 100-x;
						// console.debug(y);
						 q.data.render(
								Math.round( (x/100.0)*100)/100,
								cl,
								Math.round( (y/100.0)*100)/100
								);
						//console.debug("finish_updated");
						return _running;
					}
					q.data.render(1.0, "", 1.0);
					return _running;
					});
		};
		setTimeout(_do_anim, 1000);		
		//continue init
		return true;
	};
	
	
	function __line_waiter_init(s){		
		var q = this;
		var _ctx= null;
		var _data={};
		var _lstart=0;
		var _anim_id=0;
		if (q.remove){
			var f = q.remove;
			q.remove =function(){
				if (_anim_id)
					_anim_id.cancel();
				f.apply(q);
			};
		}
		igk.appendProperties(_data, {
			//canva: q.add("canvas").addClass("igk-canva-bg"),
			cur:q.add("div")
					.setCss({right:(q.getWidth())+"px", left:"0px"})
					.addClass("cur"),
			end:0,
			running:1,
			getctx:function(){
				if (!_ctx){
					_ctx = this.canva.o.getContext('2d');
				}
				return _ctx;
			},
			render:function(){
			}
		});
		
		function _init_cur(){			
			//bck transition
			var _trans = _data.cur.getComputedStyle("transition");
			_data.cur.setCss({transition:'none', right:(q.getWidth())+"px", left:"0px"});
			//console.debug("init cur "+_trans);
			//restore transition	
// setTimeout(function(){	
			_lstart=0;
			//trick to force browser to update transition
			//var _btrans = 
			_data.cur.getComputedStyle("transition");
			// console.debug(_btrans);
			_data.cur.setCss({transition:_trans, right:'0px'});
			// },300);
		};
		function _do_anim(){
			if (!q.o.parentNode)
				 return false;
			//init style
			//bck transition			
			//init
			_data.cur.setCss({right:"0px"});		
			
			
			_anim_id = igk.html.canva.animate(function(e){
				if (!q.o.parentNode)
					return false;
					
				var _X = q.getWidth();
				var _x = igk.getNumber(_data.cur.getComputedStyle("right"));	
				var _y = igk.getNumber(_data.cur.getComputedStyle("left"));	
				if (_X>0){
				//console.debug("animate....");
					if (!_lstart && ( (1 - (_x/_X)) > 0.3))
					{
						//console.debug("ok");
						_data.cur.setCss({left:_X+"px"});
						_lstart = 1;
					}
					//console.debug(_x + " x "+_X);
					if (_lstart && (_x<=0) && (_y>=_X)){
						//reach 
						//console.debug("end "+_x+ " x "+_y);
						_init_cur();
					}
				}
				return _data.running;				
			});
		};
		//wait for half before start
		setTimeout(_do_anim, 200);
		
		var _resizing = 0;
		igk.winui.reg_event(window, 'resize', function(){
			// stop previous animation
			
			if (_resizing)
				return;
			_resizing =  1;
			_init_cur();
			_resizing = 0;
			}
		);				
	};
	//register google control
	igk.winui.initClassControl("igk-google-circle-waiter", __circle_waiter_init, {
		desc:"google circle waiter"
	});	
	igk.winui.initClassControl("igk-google-line-waiter", __line_waiter_init, {
		desc:"google line waiter"
	}); 
	
})();


(function(){
	igk.system.createNS("igk.drawing.effect", {
		stackBlur: function(ctx, x,y, w, h, radius, iterations){		
			boxBlurCanvasObjRGB(ctx, x,y, w, h, radius || 2, iterations || 2);			
		}
	});

})();

//---------------------------------------------------------------------------
// google button
//---------------------------------------------------------------------------

(function(){
	function __init_google_button(){
		var q = this;
		this.reg_event("click", function(e){
			
			q.setAttribute("curx", '10px');
			q.setAttribute("cury", '10px');
			q.addClass("igk-show-cur");
			var cur = q.add("div");
			cur.addClass("cur");		
			var loc=igk.winui.GetChildMouseLocation(q,e);			
			var x = loc.x+"px";
			var y = loc.y+"px";
			
			$igk(cur).setCss({
				left:x,
				top:y,
				width:'0%',
				height:'0%'
			}).timeOut(200, function(){
				cur.addClass("trans").setCss({
				left:'0px',
				top:'0px',
				width:'100%',
				height:'100%'
			});
			});
		});
	}
	igk.winui.initClassControl("google-button", __init_google_button);
})();