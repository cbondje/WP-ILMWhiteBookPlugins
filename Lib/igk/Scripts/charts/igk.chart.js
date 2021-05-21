//namespace : igk.chart
//Description: used to manage chart in library
//author: C.A.D BONDJE DOUE
//date: 13/08/15
//usage : igk.gkds.io.icwjs.read(canva, uri);

"uses strict";
(function(){

var m_charts = [];


function init_round_chart(){

			igk.appendProperties(this.data,{
				"chart-data": {
				}
			});
			
			var ch_data = this.getAttribute("chart-data");
			var dk = (ch_data !=null) ? igk.JSON.parse(ch_data): null;
			var data = 0;
			var m_animate = false;
			var m_duration = 500;
			var self = this;
			// console.debug(  typeof(dk) +" :::: "+ ch_data + " "+this);
			// console.debug( dk);
			if ((dk !=null) && (typeof(dk) == "object"))
			{
				data = dk.value;
				m_animate = dk.animate || false;
				m_duration= dk.duration || m_duration;
			}
			else 
				data = dk;
				
			var d = self.add("def").setCss({visibility:'hidden', position:'absolute'});
			
			var b1 = d.add("div").addClass('bar1').setHtml("bar1");
			var b2 = d.add("div").addClass('bar2').setHtml("bar2");
			var btx = d.add("div").addClass('text').setHtml("text");

			
			//get color definition
			var cl1= b1.getComputedStyle('color') || "#5148fe";
			var cl2= b2.getComputedStyle('color') || "#dedede";
			var cl3 = btx.getComputedStyle('color') || "#888";
			//get size
			var w = igk.getNumber(this.getComputedStyle("width"));
			var h = igk.getNumber(this.getComputedStyle("height"));			
			self.data.canva = igk.createNode("canvas");
			self.data.canva.addClass("posab");
			// console.debug(data);
			function render(data){
			var t = igk.chart.round_chart_data(self.data.canva, cl1, cl2, w, h,data)+"";			
			var d = null;
			if (!self.text){
				self.setHtml("");//clear 
				d = self.add("div");
				self.text  = d;				
				self.prependChild(self.data.canva.o);
			}
			else 
				d = self.text;			
			
			
			d.addClass("alignc alignm disptabc");
			d.setCss({fontSize:"4em", width: w+"px", height: h+"px" , color:cl3});
			d.setHtml(Math.round((data * 100)) + "%");		
			}
			render(m_animate? 0.0: data);
			
			
						
			if (m_animate){
				var d  = m_duration;
				var t = 0;
				var n = igk.createNode("div");
				var y = Math.round(data * 100);
				n.setCss({width:"0px", transition:'all 0.5s ease-in-out', border:"none", "position":"absolute", "visibility":"hidden" });
				//n.setTransitionDelay("4.00s");
				//n.setHtml("data-evolution");
				self.prependChild(n.o);				
				
				setTimeout(function(){
					n.setCss({width: y+'px'});
					igk.html.canva.animate(function(e){
					var x = igk.getNumber(n.getComputedStyle("width"));
					if (x<y){
						render(x/100.0);					
						return true;
					}
					render(y/100.0);
					return false;
					});
					
					
				}, 100);
			}

}

igk.system.createNS("igk.chart",{
	round_chart_data: function(c, cl1, cl2, w,h, data){	
		 if (data==null)
			return;
			
		c.setAttribute("width", w);
		c.setAttribute("height", h);
		var ctx = c.o.getContext('2d');
		var cx = w/2;
		var cy = h/2;
		//radius must not be negative or index size error will be throw
		var r = Math.max( igk.getNumber(c.getComputedStyle("fontSize")), (Math.min(w,h)-20)) /2;
		
		//console.debug("data === "+ cx  + " " + cy);
		
		ctx.clearRect(0,0,w,h);
		if (data==0)
			data = 40;
		
		ctx.lineWidth = 8.5;
		ctx.strokeStyle = cl1;
		ctx.beginPath(); ctx.arc(cx, cy, r, -(Math.PI / 2)+  (2*Math.PI)*data,  (2*Math.PI) -(Math.PI / 2), false);
		//ctx.rect(0,0,w,h);
		ctx.stroke();
		ctx.lineWidth = 9.5;
		//background
		ctx.strokeStyle  = cl2;
		ctx.beginPath(); 
		ctx.arc(cx, cy, r, -(Math.PI / 2),  -(Math.PI / 2)+  (2*Math.PI)*data , false);
		//ctx.rect(0,0,w,h);
		ctx.stroke();
		
		
		// igk.mods.canva.drawText(ctx, (data * 100) + "%", 11,101, "4em Arial", "#aaa", 0);
		// igk.mods.canva.drawText(ctx, (data * 100) + "%", 10,100, "40px Arial", "#F0a",0);			
		//fore ground
		// ctx.fillStyle = cl2;
		//ctx.rect(0,0,w,h);
		// ctx.fill("evenodd");		
		//ctx.clear();
		//igk.show_notify_prop(ctx);		
		// console.debug(c.o.toDataURL());
		
		return c.o.toDataURL();
	}	
});


//register class object
igk.winui.initClassControl("igk-round-chart",init_round_chart,  {desc: "circle percentage chart"});

//igk.ready(igk.chart.init);

})();