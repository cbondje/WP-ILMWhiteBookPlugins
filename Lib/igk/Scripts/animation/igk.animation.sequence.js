//animation sequence
"use strict";
(function(){
	//order is important

	igk.system.createNS("igk.animation", {
		sequence: function(){
			//
			//sequence objet
			//
			
			var m_sq = new igk.system.collections.list(); 
			var m_a = false;
			var m_i = 0;
			var self = this;
			function __sequencetrans(evt){
				if (m_a){
					//console.debug("end ... "+m_i);
					if ((m_i >=  (m_sq.getCount()-1)) && (self.loop)){
						m_i = -1;
						//console.debug("reset loop");
					}
				
					if (m_i < (m_sq.getCount()-1))
					{
						m_i++;
						var s = m_sq.getItemAt(m_i);
						self.target.setCss({transition: s.properties[0]+' '+s.duration+' '+(s.effect||'ease-in-out')});
						__loadv(self);
					}else{
						//teminate
						m_a  = false;
					}
				
				}
			};
			function __loadv(q){
				var s = m_sq.getItemAt(m_i);
				var n = s.properties[0];
				q.target.o.style[n] = s.value;						
			}
			igk.appendProperties(this, {
				target:null,
				loop:false,
				timeout:100,
				start:function(){
					if (!this.target || m_a || (m_sq.getCount() ==0))return;
					m_i = 0;
					var s = m_sq.getItemAt(m_i);					
					//console.debug();
					this.target.unreg_event('transitionend', __sequencetrans);
					var m = s.properties[0]+' '+s.duration+' '+(s.effect||'ease-in-out');					
					this.target.setCss({transition: m});
					
					var q = this;
					setTimeout(function(){
						m_a = true;
						__loadv(q);
						var n = s.properties[0];
						q.target.o.style[n] = s.value;						
						q.target.reg_event('transitionend', __sequencetrans);
					}, q.timeout);
					
				},
				stop:function(){
					
				},
				pause:function(){},
				add:function(s){
					m_sq.add(s);
				},
				remove:function(s){
					m_sq.remove(s);
				},
				clear:function(){
					m_sq.clear();
				},
				toString:function(){return "igk.animation.sequence[object]";}
			});
		}
	});
	igk.system.createNS("igk.animation.sequence",{
			//sequence namespace
			init: function(){
				
			}
	});
	
	
	
	//alert(igk.animation.sequence.init);
	//demonstration of sequence builder
	// igk.ready(function(){
		
		// $igk(document.body).setHtml("");
		// var d = igk.createNode('div');
		
		
		// d.setHtml("info").setCss({
			// border: '1px solid black',
			// width: '200px',
			// height: '200px',
			// position:'absolute',
			// top:'0px',
			// transition: 'all 1.2s'
		// });//.addClass('igk-trans-all');
		
		
		// var s = new igk.animation.sequence();
		// s.target = d;
		// s.add({properties:['background-color'], value:"black", duration:'0.8s'});
		// s.add({properties:['background-color'], value:"red", duration:'1.8s'});
		//// s.add({properties:['top'], value:"0px", duration:'1.8s'});
		// s.loop = true;
		// $igk(document.body).add(d);
		// setTimeout(function(){
			// d.o.style.top = '40px';
		// }, 200);
		// s.start();
	// });
})();


// (function(){
// igk.system.createNS("igkdev://",{
	// info:function(){
	// }
// });


// alert(window["igkdev://"]);
// })();