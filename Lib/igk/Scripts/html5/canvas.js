"use strict";

(function(){
	// console.debug("bas");
	var m_devices = [];
	
	function is_percent(x){
		return (typeof(x)=='string' ) && /[0-9]+(\.[0-9]+)?%$/.test(x.trim());
	}
	function _getLocX(host, x){
		var h = igk.getNumber(x);
		if (is_percent(x)){
			x = host.getX() * h / 100.0;
			
		}else 		
			x = igk.getNumber(x);
		return x;		
	};
	function _getLocY(host, x){
		var h = igk.getNumber(x);
		if (is_percent(x)){
			x = host.getY() * h / 100.0;
			
		}else 		
			x = igk.getNumber(x);
		return x;		
	};
	
	function _getW(host, x){
		var h = igk.getNumber(x);
		if (is_percent(x)){
			x = host.getWidth() * h / 100.0;			
		}else 		
			x = igk.getNumber(x);
		return x;		
	};
	//static get height of the component according to host
	function _getH(host, x){
		var h = igk.getNumber(x);
		if (is_percent(x)){
			x = host.getHeight() * h / 100.0;
			
		}else 		
			x = igk.getNumber(x);
		return x;		
	};
	
	function _init2DObj(canva, context){
		this._ctx = context;
		this._canva=canva;
		m_devices.push(this);
		var _index = m_devices.length -1;
		
		igk.appendProperties(this, {
			dispose:function(){
				
			},
			getX:function(){
				return 0;
			},
			getY:function(){
				return 0;
			},
			getWidth:function(){
				return igk.getNumber($igk(this._canva).getComputedStyle("width"));
			},
			getHeight:function(){
				return igk.getNumber($igk(this._canva).getComputedStyle("height"));
			},				
			setColor:function(cl){
				this._ctx.strokeStyle = cl;
			},
			setFill:function(cl){
				this._ctx.fillStyle = cl;
			},
			fillRect: function(color, x, y, w,h){
				//console.debug("fill ...");
				x = _getLocX(this, x);
				y = _getLocY(this, y);
				w = _getW(this, w);
				h = _getH(this, h);
				
				// console.debug("x "+x);
				// console.debug("y "+y);
				// console.debug("width "+w);
				// console.debug("height "+h);
				
				
				this._ctx.save();
				this.setFill(color);
				
				this._ctx.shadowColor='black';
				this._ctx.shadowBlur = 10;
				
				this._ctx.beginPath();
				this._ctx.rect(x,y,w,h);					
				this._ctx.closePath();	
				this._ctx.fill();
				this._ctx.save();
			}
		});
	};
	
	igk.system.createNS("igk.html5.Canvas", {
		create2DDevice:function(n){
			var ctx = n.getContext("2d"); 
			if (!ctx)
				throw ("failed to created canvas device");
			
			return new _init2DObj(n, ctx);
			
		}
	});
})();