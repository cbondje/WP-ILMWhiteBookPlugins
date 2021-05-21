"use strict";
//--------------------------------------------------------------------------------------
//represent an horizontal scroll pane. used in combination of class.IGKJS_horizontalPane
//--------------------------------------------------------------------------------------
//HPane anim type supported : translation|fade|rotation

//definition of hpane
//--igk-hpane-container
//|_______>igk-pane
//|____________>igk-pane-page
//|____________>igk-pane-page
//|____________>...
//|____________>igk-pane-page
//|_______>igk-hpane-bz : bullet zone


//define option of igk-hpane-container: 
//{
	//@style:'[animType]', 
	//@animDuration::'auto animation duration
	//@showBullet:0|1
	//@showNav:0|1

//styling: 1 igk-pane overflow is hidden ! important
//for rotation



(function(){
	var g_panes=[];
	var ckeys=['.igk-pane-page','.igk-pane', '.hpane-bz'];
	var ifc = igk.fn.isItemStyleSupport;
	var support_transition = 0;
	
	
	igk.winui.horizontalScrollPane=function(t){
		//.ctr horizontal pane contructor
		this.host = t;
		var m_init=0;//init for left property avoid firefox flicker
		var _idx=g_panes.length;
		var pane = t.select(ckeys[1]).first();
		var bz = t.select(ckeys[2]).first();
		var _pos=0;
		var _bullets=[];
		var opts = igk.initObj( igk.JSON.parse(t.getAttribute("igk:data")), {
			style:'rotation',
			showBullets:1,
			showNav:1,
			animDuration:5000,
			autoAnim:1
		});		
		var q = this;
		
		var tout = 0; //timeout
		function __startAnim(){
			if(tout)
				clearTimeout(tout);
			
			if ((_pos+1) < __items().getCount()){			
				q.goNext();
			}else{
				_pos = 0;
				q.scrollTo(0);
			}
		
		};
		function __restartAnim(){
			if(tout)
				clearTimeout(tout);
			if (opts.autoAnim){
			tout = setTimeout(__startAnim, opts.animDuration );			
			}
		}
		
		function __items(){
			return pane.select(ckeys[0]);
		};
		function __updateBullet(){
			if (!opts.showBullets)
				return;
			
			if (_bullets.active){
				_bullets.active.rmClass("igk-active");
			}
			_bullets.active = _bullets[_pos];
			_bullets.active.addClass("igk-active");			
		};
		igk.appendProperties(this, {//object properties
			remove:function(){
				t.remove();
			},
			goNext:function(){
				var s = pane.select(ckeys[0]);				
				if ((_pos>=0) && (_pos< s.getCount()-1)){
					_pos++;
					this.scrollTo(_pos);
				}
			},
			goPrev:function(){
				var s = pane.select(ckeys[0]);
				if (_pos>0){
					_pos--;
					this.scrollTo(_pos);
				}
			},
			scrollTo:function(c){
				var s = pane.select(ckeys[0]);
				if (igk.isInteger(c)){
					c = s.getItemAt(c).o;
				}
				var posx, posy;
			
				
				//this.reset();
				
				//var f = "translate(-"+posx+"px, "+posy+"px)";//pixel positionning failed on resize
				
				
				
				if (igk.navigator.isFirefox()){
					if (!m_init){
						s.each_all(function(){					
							this.setCss({"left": "0%"});
						//console.debug("done :"+f);
						});	
						m_init=1;						
					}
					
					if ( c.offsetLeft !=0){
						var l = $igk(c).getComputedStyle('left');
						posx = ((c.offsetLeft-igk.getNumber(l))/pane.o.offsetWidth)*100;						
						// return;
						s.each_all(function(){					
							this.setCss({"left": -posx+"%"});
							// console.debug("done :"+posx);
						});		
					}
				}
				else{
					posx = 100 * c.offsetLeft / pane.o.offsetWidth;
					posy = 100 * c.offsetTop / pane.o.offsetHeight;
					var f = "translate(-"+posx+"%, -"+posy+"%)";//pixel positionning failed on resize.use %
					s.each_all(function(){					
						this.setCss({"transform":f});
					});				
				}
				__updateBullet();
				__restartAnim();
			},
			reset:function(){
				if (igk.navigator.isFirefox()){
					__items().each_all(function(){					
						this.setCss({"left":"0px"});
					});		
				}else{
					__items().each_all(function(){					
						this.o.style.transform=null;//.setCss({"left":"0px"});
					});		
				}
				var fl = pane.o.scrollTo || pane.o.scroll;
				if (fl)
					fl.apply(pane.o, [0,0]);
				_pos=0;
				__updateBullet();
			}
			
		});
	
		g_panes[_idx]=this;
		
		
		var l = __items().getCount();
		//init bullets		
		bz.setHtml("");//clear bullet zone
		if (opts.showBullets){
			for(var i = 0; (l>1) && (i< l); i++)
			{
				var e = igk.createNode("div")
					.addClass("hpane-b")
					.reg_event('click', (function(i){
						return function(e){
							_pos = i;
							q.scrollTo(i);
						};
					})(i)
					);
					bz.add(e);							
					_bullets.push(e);
			}
			__updateBullet();
		}
		
		if (opts.showNav){
			//init navigation button
			t.add("div").addClass("hpane-btn hpane-btn-n")
			.setCss({"right":"2px", "top":"50%", "marginTop":"-24px"}).reg_event("click",function(){ q.goNext();});
			
			t.add("div").addClass("hpane-btn hpane-btn-p")
			.setCss({"left":"2px", "top":"50%", "marginTop":"-24px"}).reg_event("click", function(){q.goPrev();});
		}
		
		if (opts.autoAnim){
			
			setTimeout(__startAnim, opts.animDuration );
		}
	};
	var _class_ = igk.winui.horizontalScrollPane;
	
	igk.system.createNS("igk.winui.horizontalScrollPane", {
		//global static properties
		init:function(t){
			//init hpane
			var q= $igk(t);
			var pane = new _class_(q);
			
			
			
			
			pane.reset();
			
			window.pan = pane;
			
			
			return pane;
		}		
		,item:function(i){
			return g_panes[i];
		}
	});
	
	igk.ready(function(){
		var _b = igk.dom.body();
		support_transition = ifc(_b.o,'transition') && ifc(_b.o, 'transform');
	});
	
})();





// (function()
// {
// var m_inits = [];

// function __contains(target){
	// for(var i = 0; i< m_inits.length; i++)
	// {
		// if (m_inits[i].target.parentNode == target){
			// return m_inits[i].hPane;
		// }
	// }	
	// return null;
// }
// function _append_log(n){
	// var sn = igk.createNode('div');
	// sn.setHtml(n);
	// $igk("#result").first().add(sn);
// }
// igk.system.createNS("igk.winui.horizontalScrollPane",
// {
	// update:function(r,a){
		// var i =0;
		// $igk('.igk-pane-page').addClass("no-transition").rmClass(r).rmClass("no-transition").each_all(function(){
			// var q = this;
			// this.timeOut(10,function(){
			// _append_log('start:'+i+':'+q.getComputedStyle('transition'));
			// q.addClass(a);
			// i++;
			// });
		// });
		
		// // $igk('.igk-pane-page').addClass('no-transition').rmClass(r).rmClass('no-transition').each_all(function(){
			// // _append_log('start:'+i+':'+this.getComputedStyle('transition'));
			// // this.addClass(a);
			// // i++;
		// // });
	// },
// append_to_body_from: function(from){
	
	// return function(xhr){
		// if (this.isReady()){
			// var q = igk.ajx.fn.append_to(xhr, document.body);			
			// if (q!=null){
				// q.each(function(){ 
				// var b = this.select("form").getItemAt(0);
				// if (b)
				// {
					// b.o["igk:source"] = from.igk.contextMenu.contextCibling; 							
				// }
				// return true});
			// }
		// }
	// };
// },
// init : function(target, definition, property){
	// // console.debug("init horizontal pane "+target);
	
	// $igk(target).select(".igk-pane-img")
	// .addClass("no-drag no-flick")
	// .reg_event("dragstart", _not_handle);
	
	// function _not_handle(e){
		// e.preventDefault();
		// e.stopPropagation();
	// };
	// //disable context menu on pane-view
	// $igk(target).select(".igk-pane-view").reg_event('contextmenu', _not_handle);
	// //return;
	// var s = __contains(target);
	// if (s !=null)
	// {
		// console.debug("hsp target already initialized");
		// return s.hPane;
	// }
	// //private global variable
	// var m_target = target;
	// var m_property = property;
	// var m_definition = definition;
	// var m_bullets = [];
	// var m_navigate =false;
	// var m_autoanimate = definition.autoanimate? true: false;
	// var m_animtype =  definition.animtype?definition.animtype:"translation";
	// var m_period = definition.period?definition.period:10000;
	// var m_duration = property.duration? property.duration: 1000;
	// var m_interval = property.interval?property.interval: 20;
	// var m_autonavTimeout = null;
	// var m_out = null;
	// var m_effect = property.effect? property.effect: "linear";
	// var m_effectmode = property.effectmode? property.effectmode: "easein";
	
	// //require that the target is in position relative
	// $igk(target).addClass("posr dispb");
	
	// //private static function
	// function __clearAutoTimeout()
	// {
		// if (m_autonavTimeout!=null)
			// clearTimeout(m_autonavTimeout);		
		// m_autonavTimeout = null;		
	// }
	// function __startAutoTimeout(q){
		// //console.debug("start auto anim");
		// var pc = q.getPageCount();
		// if (pc<=1)
			// return;
		// // if (pc<=1)
		// // {
			// // q.btn_next.addClass("dispn");
			// // q.btn_prev.addClass("dispn");
			// // q.bz.addClass("dispn");
		// // }
		// // else{
			// q.btn_next.rmClass("dispn");
			// q.btn_prev.rmClass("dispn");
			// q.bz.rmClass("dispn");
			// if (m_autoanimate){
				// //start auto animation 
				// //stop timeout
				// if (m_autonavTimeout)				
				// {
					// clearTimeout(m_autonavTimeout);
				// }
				// m_autonavTimeout =  setTimeout(function(){__autoAnimate(q);}, m_period);
			// }
		// // }
	// }
	// function __autoAnimate(q)
	// {  //auto animate
		// if ( q && (q.getPageCount() > 0) && m_autoanimate)
		// {
			// __navigate(q, (q.selectedIndex + 1) % q.getPageCount());
			// if (m_autonavTimeout)
			// {
				// clearTimeout(m_autonavTimeout);
			// }
			// m_autonavTimeout = setTimeout(function(){ __autoAnimate(q);}, m_period);
		// }
	// }
	// function __select(index){
		// //select bullet
		// $igk(m_bullets[index].bullet).setCss({									
									// "backgroundPosition":"-16px 0px"
								// });
		
	// };

	// function __unselect(index){
	// //unselect bullet
		// $igk(m_bullets[index].bullet).setCss({									
									// "backgroundPosition":"0px 0px"
								// });
		
	// };
	// function __animateBtn(btn, condition){
		// if (!btn)
			// return;
		// if (condition)
		// {
			// if (!btn.fadeout){
				// btn.fadeout = true;				
				// $igk(btn).rmClass ("igk-active");
				
				// igk.animation.fadeout(btn, m_interval, m_duration, {"from":1, "to": 0.25},function(){ btn.fadeout=true;
						// $igk(btn).addClass("igk-disable");
				// });
			// }
		// }
		// else{		
			// if (btn.fadeout)
			// {
				// btn.fadeout = false;
				// $igk(btn).rmClass ("igk-disable");
				// $igk(btn).addClass("igk-active");
				// igk.animation.fadein(btn, m_interval, m_duration, {"from":0.25, "to": 1.0}, function(){ btn.fadeout=false; });				
			// }
		// }
	// }
	// function __updateNavbtn(q)
	// {
		// switch(m_animtype)
		// {
		// case "rotation":
		// case "rotate":
			// //dont fade
			// __animateBtn(q.btn_prev, false);
			// __animateBtn(q.btn_next, false);
			// break;
		// default:
			// var c = q.getPageCount();
			// var i = q.selectedIndex;
			// //console.debug("page count "+q.getPageCount() + " "+q.selectedIndex);
			// //for prev
			// if (c <= 1)
			// {
				// q.btn_prev.addClass("dispn");
				// q.btn_next.addClass("dispn");
				// __animateBtn(q.btn_prev, false);
				// __animateBtn(q.btn_next, false);
			// }
			// else{
				// __animateBtn(q.btn_prev, i<=0);
				// //for next
				// __animateBtn(q.btn_next, i >= (c-1));		
			// }
		// break;
		// }
	// }
	// function __linknavigate(evt)
	// {		
		// __clearAutoTimeout(); 
		// this.parentNode.navigate();
		// __startAutoTimeout(m_out); 
		// return false;
	// }
	// function __nofocus(evt)
	// {
		 // this.blur(); 
		 // return false;
	// }
	// function __navBtn(){
		// //setup a navigation button
		// var b = igk.createNode("div");
		// var a = igk.createNode("a");
		// b.addClass("hpane-btn igk-disable posab dispb loc_t").setCss({"width":"48px","height":"48px"});
		
		// a.addClass("fitw fith dispb");
		// a.href="#";
		// a.o.onclick = __linknavigate;
		// a.o.onfocus = __nofocus;
		// b.appendChild(a);
		// b.fadeout=true;
		// return b;
	// }
	// function __newBulletlink(){
		// var a = igk.createNode("a");
		// a.o.href="#";
		// a.o.onclick= __linknavigate;
		// a.o.onfocus = __nofocus;
		// a.addClass("fitw fith dispb")
		// .setCss({"textDecoration":"none"});
		// return a;
	// }	
	// //navigate func.
	// //desc: use to manualy navigate to child on HPaneContainer
	// //
	// function __navigate(q,m,noanimate){	
		// var pcount = q.getPageCount();
		// if (m_navigate || (pcount<=1))
			// return;
		// m_navigate = true;
		// var cindex = q.selectedIndex;		
		// switch(m_animtype)
		// {		
		// case "fade":
			// var v_fin= (cindex != m) || !q.fadeinit;
			// if (cindex != m){
				// var i = q.pages[cindex].page;
				
				// i.setOpacity(1.0);
				// i.animate({opacity: 0},{
				// duration: m_duration,
				// interval : m_interval,
				// complete: function(){
				// }
				// });
			// }
			// if (v_fin){
			// q.fadeinit = true;
			// var c = q.pages[m].page;
			// c.setOpacity(0);
			// c.animate({opacity: 1}, {
				// duration: m_duration,
				// interval : m_interval,
				// complete: function(){
					// __unselect(q.selectedIndex );
					// q.selectedIndex = m;
					// __select(q.selectedIndex);
					// __updateNavbtn(q);
					// m_navigate = false;
				// }
			// });	
			// }	
		// break;
		// case "rotate":
		// case "rotation":	
			// console.debug("scroll to");
			// $igk(q.pagezone).scrollTo($igk(q.pages[m].page), 
				// m_property,
				// function(){
					// __unselect(q.selectedIndex);
					// q.selectedIndex=m;
					// __updateNavbtn(q);
					// __select(m);
					// m_navigate = false;
					// console.debug("done");
				// }
			// );
			
			// // $igk(q.pagezone).scrollTo($igk(q.pages[m].page),  !noanimate?m_property:null, function(){ 
				// // __unselect(q.selectedIndex );
				// // q.selectedIndex = m;
				// // __updateNavbtn(q);
				// // __select(q.selectedIndex);				
				// // m_navigate = false;
				// // });		
			// break;
		// default:
		// if (q.pages[m])
		// {
			// $igk(q.pagezone).scrollTo($igk(q.pages[m].page),  !noanimate?m_property:null, function(){ 
				// __unselect(q.selectedIndex );
				// q.selectedIndex = m;
				// __updateNavbtn(q);
				// __select(q.selectedIndex);				
				// m_navigate = false;
				// });
		// }
		// break;
		// }
			
	// };
	// function __getAnimType(){
		// switch(m_animtype)
		// {
			// case "rotation":
			// case "rotate":
				// return "rot";
			// default:
				// return m_animtype;
		// }
	// }
	// function __initnavBtn(q)
	// {
		// //add previous and next button
		// //for prev
				// q.btn_prev = __navBtn();
				// igk.appendProperties(q.btn_prev.o,{
					// navigate: function(){ //go prev
					// if (q.selectedIndex>0)
					// {	__navigate(q, q.selectedIndex-1);
					// }
					// else {
						// if (__getAnimType()=="rot")
						// {
							// __navigate(q, q.getPageCount()-1);
						// }
					// }
				// },
				// q : q
				// });
				
				// $igk(q.btn_prev)
				// .addClass("hpane-btn-p")
				// .setCss({"left":"2px", "top":"50%", "marginTop":"-24px"});				
				// q.tn.appendChild(q.btn_prev.o);
				
		// //for next		
				// q.btn_next = __navBtn();
				// igk.appendProperties(q.btn_next.o, {
					// navigate : function(){ //go next
					// if ((q.selectedIndex + 1) < q.getPageCount())
					// {
						// __navigate(q, q.selectedIndex+1);
					// }
					// else if (__getAnimType()=="rot")
						// __navigate(q, 0);
				// },
				// q : q
				// });
				// $igk(q.btn_next)
				// .addClass("hpane-btn-n")
				// .setCss({"right":"2px", "top":"50%", "marginTop":"-24px"});
				// q.tn.appendChild(q.btn_next.o);
				
	// };
	
	// function __construct(targetnode)
	// {
		// igk.appendProperties(
			// this, 
			// {
			// tn: targetnode,
			// pagezone: null,
			// pages : null,
			// selectedIndex :0, 			
			// btn_prev:null,
			// btn_next: null,
			// bz:null, //bullet zone
			// free: function(){
				// delete (this);
			// },
			// getPageCount  : function(){ if (this.pages) return this.pages.length; return 0; },		
			// toString: function(){return "igk.winui.horizontalScrollPane"},			
			// init :function()
			// {	
				// var t = this.tn.getElementsByTagName("div");
				// var e = null;
				// this.pages =[];
				// var q = this;
				// var nav  = null;
				// for(var i = 0; i< t.length; i++)
				// {
					// switch(t[i].getAttribute("igk-control-type"))
					// {
						// case "igk-pane-page":
						// {//get page
							// e = 
							// {
								// index : i,
								// page: $igk(t[i]),
								// click :  function(evt){
									// var m =  (this.index + 1) % q.pages.length;									
									// __navigate(q, m);
								// }
							// };								
							// if (m_animtype == "fade")
							// {						
								// $igk(e.page)
								// .addClass("posab fitw dispb loc_l loc_t")
								// .setOpacity(0.0)
								// .setCss({zIndex: 10});
							// }							
							// q.pages[q.pages.length] = e;
						// }
						// break;
						// case "igk-pane":
							// if (this.pagezone==null)
							// {//register only one panel
								// this.pagezone = $igk(t[i]);
								// //this.pagezone.addClass("igk-pane");
								// this.pagezone.addClass(m_animtype);								
							// }
							// break;
					    // case "hpane-bz":
							// if (nav ==null){//register only one bullet zone
								// nav = $igk(t[i]);	
								// //init navigation
								// nav.addClass("hpane-bz");	
								// q.bz = nav;								
							// }
							// break;
						// default:
							// break;
						
					// }
				// }		
					// var l = this.pages.length;
				
				// if (l > 0)
				// {
					// //init bullet zone
					// var d = (nav!=null)? nav: (function(){						
						// var k = igk.createNode("div"); 
						// q.tn.appendChild(k); 
						// return k;})(); 
				
				// if (l>1)
				// __initnavBtn(q);				
					// //d.className = "posab fitw loc_b alignc";						
					// //build hpane bullet button
					// e = null;
					// for(var i = 0; (l>1) && (i< l); i++)
					// {
						// e = igk.createNode("div")
							// .addClass("hpane-b");
						// igk.appendProperties(e.o, {
							// index: i,
							// navigate : function(){ 
								// __navigate(q, this.index);}
						// });
						// e.appendChild(__newBulletlink());
						// d.appendChild(e);
						
						// m_bullets[m_bullets.length] = {
							// bullet : e.o,		
							// offsetLeft : function(){ 	
								// return q.pages[this.bullet.index].page.o.offsetLeft; 
							// }
						// };
						
					// }
					// __navigate(q, (function(){
					// //maintain the current selected index
					// //because scroll value doen't change on refresh
						// for(var s = 0; s < m_bullets.length; s++)
						// {
							// if (m_bullets[s].offsetLeft() == q.pagezone.scrollLeft)
							// {
								// q.selectedIndex = s;
								// return s;
							// }
						// }
						// q.selectedIndex = l>0? 0 : -1;
						// return 0;
					// })());
				// }
				// var m_eventContext = igk.winui.RegEventContext(q,$igk(q));
				
				// if (m_eventContext){
					// var timer=0;
				 // m_eventContext.reg_window("resize",function (){ 
					// //force navigation on resize
					
					// if(timer)
						// clearTimeout(timer);
					// timer = setTimeout(function(){
					
					// __navigate(q, q.selectedIndex); 
					// }, 500);
					
				 // });				
				// }
				// __startAutoTimeout(q);
			// }
		// });		
		
		// this.init();
	// };	
	// m_out =  new __construct(target);	
	// m_inits.push({"target":target, "hPane":m_out});
	// return m_out;
// },
// initdrag: function (uri, properties){
		// (function(q){
		// var uf = igk.winui.dragdrop.fn.upload_file;
		// var p =  q.parentNode.parentNode;
		// var d = $igk(q).select('.igk-hbox').getItemAt(0);
		// if (d){
			// var b = d.getHtml().toLowerCase();
			// if (b == 'add')
			// {
				// //add button
				// $igk(q).reg_event("click", function(evt){
					// evt.preventDefault();
					// var i = igk.createNode("input");
					// i.o["type"] ="file";
					// i.reg_event("change", function(evt){
						// if (i.o.value)
						// {		
							// var prop = __getDragoption();						
							// igk.ajx.uploadFile(i.o.files[0] ,uri, true, prop.update, prop.start, prop.progress, prop.done);
						// }
					// });
					// i.o.click();
				// });
			// }
		// }
		
		// var prg = null;
		// function __getDragoption(){
			// return {
				// uri: uri, 
				// //dragin options
				// targetajx:p,  
				// supported: 'text/html,image/jpeg,image/png', 
				// async:true,
				// //progress function
				// start: function(){
					// prg = igk.createNode("div");
					// prg.addClass("igk-progressbar posab loc_b loc_l loc_r");
					// prg.add("div").addClass("igk-progressbar-cur igk-trans-200ms");
					// p.appendChild(prg.o);					
				// },
				// progress: properties && properties.progress? properties.progress: function(evt){ 
					// var u_p = parseInt((evt.loaded/ evt.total) * 100);
					// if (prg){
						// prg.select(".igk-progressbar-cur").setCss({width: u_p+"%"});						
					// }
				// },
				// done:function(evt){ 
					// if (prg)
						// prg.remove();
				// },		
				// //update result when done
				// update: function(xhr){
					// if (this.isReady()){
						// var 
		// p = $igk(q).select("^.igk-hpane-container").getItemAt(0);		
						// // console.debug(p);
						// this.replaceResponseNode(p.o);
					// }
				// },
				
				// //igk.ajx.fn.replace_content(p),
				// //drop function
				// drop: uf, //call when element is dragged
				// enter: function(evt){ 		
				// }, 
				// over: function(evt){		
				// },
				// leave:function(evt){ } 
				// };
		// };
		
		// //reg on drag on page
		// $igk(p).select(".igk-pane-page").each(function(){
			// igk.winui.dragdrop.init(this.o,	__getDragoption());
			// return true;
		// });		
		// //reg on drag on target
		// igk.winui.dragdrop.init(q,__getDragoption());
	// })(ns_igk.getParentScript());
// },
// initclearpage:function(uri){
// (function(q){$igk(q).reg_event("click", function(evt){ evt.preventDefault(); 

// var p = $igk(q).select("^.igk-hpane-container").getItemAt(0);
// if (p == null)
	// console.debug("can't get hpane container");
// igk.ajx.post(uri, null, 



// //igk.ajx.fn.replace_content(q.parentNode.parentNode)); 
// function(xhr){
	
	// if (this.isReady()){
		// this.replaceResponseNode(p.o, true);
	// //	__replace(xhr.responseText);
		
	// }
// }
// //igk.ajx.fn.replace_content(q.parentNode.parentNode)



// ); }); })(ns_igk.getParentScript());
// },
// initoptions:function(uri){
// (function(q){
// //init 
// //get opstion 
// var m_options = null;
// $igk(q).reg_event("click", function(evt){ 
// evt.preventDefault(); 
// var l = $igk(q).getScreenLocation();
// l.x += $igk(q).getWidth();

// if (m_options!=null){
		// //stop propagation to parent !important 
		// evt.stopPropagation();	
		// igk.winui.contextMenu.load(m_options);
		// igk.winui.contextMenu.show(q, q.parentNode.parentNode, l);
		// return;
// }
// igk.ajx.post(uri, null, function(xhr){		
	// if (this.isReady()){
		// //show contextmenu
		// igk.winui.contextMenu.load(xhr.responseText);
		// igk.winui.contextMenu.show(q,q.parentNode.parentNode, l);
		// m_options = xhr.responseText;
	// }
// }); }); })(ns_igk.getParentScript());
// },
// dropfile_ajx: function(t, uri){
	// var p = $igk( $igk(t).select('^.igk-pane').getItemAt(0).o.parentNode);		
	// igk.ajx.get(uri, null, 
			// function(xhr){
				// if (this.isReady()){
					// this.replaceResponseNode(p.o, false);	
								
				// }
			// }		
	// );	
// }
// });
// }
// )();