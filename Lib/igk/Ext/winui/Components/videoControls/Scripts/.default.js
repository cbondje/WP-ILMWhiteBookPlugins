"use stricts";
//file: Lib\igk\Ext\WinUI\Components\videoControls\Scripts\.default.js
//desc: video control scripts
(function(){
var _ui = igk.winui;
var m_vidModels = [];
var BTN_FS = 2; //full screen
var BTN_FS_M = 5; //full screen mode


function videoModel(controls, vid){
	var reg={fs:0, useFilter:0};
	var sfilter= "";
	
	function hideCur(){
		vid.o.style.cursor ='none';
	};
	
	//exposed properties
	igk.appendProperties(this, {
		pause:function(){vid.o.pause();},
		play:function(){
			if (vid.o.ended)
				this.seek(0);
			if (vid.o.paused)
				vid.o.play();
			else vid.o.pause();
		},
		stop:function(){
			this.pause();
			this.seek(0);
		},
		seek:function(pos){
			vid.o.currentTime=pos;
		},		
		setVolume:function(vol){ //between 0.0 and 1.0
			vid.o.volume = vol
		},
		fullScreen:function(){
			vid.fullscreen();
			hideCur();
			if (!reg.fs){
				igk.winui.reg_event(window, "mousemove", function(){
					vid.o.style.cursor='initial';
					if (reg.hcur )
						clearTimeout(reg.hcur);
					reg.hcur = setTimeout(hideCur, 4000);
				});
				reg.fs =1;
			}
		},
		setPosition: function(p){
			d = vid.o.duration;
			if (d)
			vid.o.currentTime = p * d;
		},
		capture:function(w,h, filter){
			var canva =  igk.createNode("canvas");
			w = w || vid.o.videoWidth || vid.o.clientWidth;
			h = h || vid.o.videoHeight || vid.o.clientHeight;
			filter = filter || sfilter;
			canva.setAttribute("width", w)
				 .setAttribute("height", h);
			var ctx = canva.o.getContext("2d");
			if (filter && igk.canvas.supportFilter(ctx)){
				ctx.filter = igk.canvas.getFilterExpression(filter);			
			}
			ctx.drawImage(vid.o,0,0);			
			var s =  canva.o.toDataURL();
			return s;			
		},setFilter: function(s){					
			sfilter= s;
		}
		,getFilter:function(){
			return sfilter;
		},
		getFilterToString:function(f){
			return igk.canvas.getFilterExpression(sfilter);			
		},
		setPlaybackRate:function(v){
			vid.o.playbackRate = v;
		}
	});
	this.fn = {};//utility
	var self =this;

	vid.reg_event("timeupdate", function(){	
		var p = vid.o.currentTime;
		var s = self.fn["timee"];	
		var d = vid.o.duration;		
		if (s)
			s.setHtml(_textt(p));		
		s = self.fn["timel"];
		if (s && d)
			s.setHtml("-"+_textt(d-p));
		
		s = self.fn["timeprogress"];
		if(s && d)
			self.updateProgress(p / d);		
		s = self.fn["timeas"];
		if(s && d){
			s.setHtml(_textts(p)+"/"+_textts(d));
		}
	});	
};
var dur = new Date(0, 0, 0, 0, 0, 0, 0);
function _textt(t){
	t = Math.round(t * 1000);
	function _s(h){
		return h<10?"0"+h:h;
	}	
	dur = new Date(0, 0, 0, 0, 0, 0, t);
	var _t = dur;
	return _s(_t.getHours())+":"+_s(_t.getMinutes())+":"+_s(_t.getSeconds());
};
function _textts(t){
	var t = Math.round(t);//* 1000); //millisection
	//var d = new Date(0, 0, 0, 0, 0, 0, t);
	var m = (""+Math.round(t / 60)).padStart(2,"0");
	var s = ((t % 60)+"").padStart(2,"0");
	return m+":"+s;//d.getTotalMinutes(); 
}

function _init_progress(p, h, vid){
	var m_i = p.add("input");
	var m_c = p.add("div").addClass("bl");//main line
	var m_b = p.add("div").addClass("bf");//buffer
	var m_f = p.add("div").addClass("cur");
	var c = {handle:0, touch:0};
	
	h.fn["timeprogress"]=1;
	var buffers = [];
	function _update_buffer(){
		var _s=0, _e=0, _i;
		
		
		if (typeof(vid.o.buffered) =="undefined"){
			return;
		}
		
		

		for(var i = 0; i < vid.o.buffered.length; i++){
			_s = Math.ceil((vid.o.buffered.start(0) / vid.o.duration) * 10000)/100;
			_e = Math.ceil((vid.o.buffered.end(0) / vid.o.duration) * 10000)/100;
			if (!(i < buffers.length)){
				_i = m_b.add('div');	
				buffers[i] = _i;
			}
			else 
				_i = buffers[i];
			_i.data = {start:_s, end:_e};//.setHtml(vid.o.buffered.start(0) + " to "+_e+"%");			
			_i.setCss({
				left:_s+"%",
				width: (_e - _s)+"%"
			});
		}
		//"+vid.o.buffered.length +" >>> Form "+vid.o.buffered.start(0) + " to "+vid.o.buffered.end(0));
		
	}
	_update_buffer();
	
	vid.reg_event("timeupdate", _update_buffer);
	
	function _update(x){
		if (!vid.o.duration)
			return;
		var W = p.getWidth();
		x = x- m_c.getScreenLocation().x;
		x = Math.min(W, Math.max(x, 0));
		m_c.setCss({"width":x+"px"});
		x = Math.round(((x / W)) * 10000) /10000;
		h.setPosition(x);
		m_i.o.value =x;
	};
	function _stop_h(){
		if (!c.touch)
			_ui.mouseCapture.releaseCapture();
		_ui.selection.enableselection();
		c.handle=0;
		c.touch=0;
	};
	
	if (p.istouchable()){
		
	}
	else{
		p.reg_event("mouseup mousedown mousemove", function(evt){
			if (_ui.mouseButton(evt)==_ui.mouseButton.Left){
				var clientx =evt.clientX;
			switch(evt.type){
				case "mousedown":					
					_ui.mouseCapture.setCapture(p.o);				
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
		});
	}
	
	igk.appendProperties(h,{
		setTrackValue : function(b){
			m_i.o.value = v;
		},
		updateProgress : function(x){
			m_c.setCss({"width":((x * 10000)/100.0)+"%"});		
		}
	});
	
	
		
}

function __init(){ //init video controls according to model some function exists.
var vid = $igk(this.o.parentNode).select("video").first();
if (!vid)return;
//check if video support play function
// console.debug(vid.o);
if ( !(vid.o instanceof HTMLVideoElement) || igk.isUndef(vid.o.play)){
	return;
}
// // console.debug(vid.o.play);
// // console.debug(navigator.userAgent);
// // alert("is safari ? "+igk.navigator.isSafari());
// // if (igk.navigator.isSafari()){
	// $igk(this.o.parentNode).select("video").first().setHtml("No video support").setCss({paddingTop:"56.6%"})
	// .setAttribute("controls", true);
	// // return;
// // }



//safari

vid.o.removeAttribute("controls");



var btns = ["&#xf002;",'&#xf003;', "&#xf004;", "&#xf005;", "&#xf006;", "&#xf007", "&#xf008"];
var btnMUTE = 6;



this.setHtml("");//clear the content of this controls
function __init_play(n){
	var o = n.add("div").setHtml(btns[0]);
	vid.reg_event("play", function(){
		o.setHtml(btns[1]);
	});
	vid.reg_event("pause", function(){
		o.setHtml(btns[0]);
	});
}


// console.debug("init video control");
var h = new videoModel(this, vid);
m_vidModels.push(h);
var d = igk.JSON.parse(this.getAttribute("igk:data"));
if (d){
	//init buttons
	var t = d.buttons.split('|');
	var n = 0;
	var p = 0;
	var c = 0;
	var W =0;
	var opts = igk.createObj('play|vol|timee|timel|timeas|trackpos|fullscreenmode|fullscreen|capture');
	for(var i = 0; i < t.length; i++){
		n =t[i].toLowerCase();
		if ((n.length==0) || (h.fn[n]) ||(!(n in opts)))
			continue;
		var span =this.add("span");
		switch(n){
			case "play":
			h.fn[n] = span.addClass("btn-c btn-"+n)			
			.reg_event("click",function(e){
			
				e.preventDefault();
				e.stopPropagation();
				h.play();
			});
			__init_play(h.fn[n], vid);
			

			break;
			case "capture":
			h.fn[n] = span.addClass("btn-c btn-"+n).reg_event("click",function(){
				if (d.captureTarget){
					var s = h.capture();
					igk.ajx.post(d.captureTarget, "data="+s);
				}
			}).add('div').setHtml(btns[4]);
			break;
			case "vol":
			h.fn[n] = span.addClass("btn-c btn-"+n).reg_event("click",function(e){				
				vid.o.muted = !vid.o.muted;
				var n = vid.o.muted ? btns[6] : btns[3];  
				h.fn['vol'].setHtml(n);
				igk.stop_event(e);
			}).reg_event("mousemove", function(){
				
			}).add('div').setHtml(btns[3]);
			break;
			case "timee":
			h.fn[n] = span.addClass("txt-c txt-"+n).add("div").setHtml("00:00:00");
			break;
			case "timel":
			h.fn[n] = span.addClass("txt-c txt-"+n).add("div").setHtml("-00:00:00");
			break;
			case "timeas": //time all in second
			h.fn[n] = span.addClass("txt-c txt-"+n).add("div").setHtml("00:00/00:00");			
			break;
			case "trackpos":
			h.fn[n] = span.addClass("txt-c txt-"+n)
			.init();
			_init_progress(h.fn[n].add("div").addClass("progress"), h , vid);
			break;
			case "fullscreenmode":
			h.fn[n] = span.addClass("btn-c btn-"+n).reg_event("click",function(){
				$igk(document.body).toggleClass('fs-m');
			}).add('div').setHtml(btns[BTN_FS_M]);
			
			break;
			case "fullscreen":
			h.fn[n] = span.addClass("btn-c btn-"+n).reg_event("click",function(){
				h.fullScreen();
			}).add('div').setHtml(btns[BTN_FS]);
			
			//bind full screen change for firefox
			$igk(document).reg_event("fullscreenchange", function(){
				var s = $igk(document).isFullScreen;
				if (s)
					vid.o.controls = true;
				else 
					vid.o.controls = false;
			});
			break;
		}
		// p = h.fn[n];
	
		// if (p){
			// // if (n!='trackpos')
			// // W+= span.getBoundingClientRect().width;//.o.offsetWidth;// p.getBoxWidth();//32+8;
		// // console.debug(span.o.offsetWidth);
			// // p.setCss({"marginLeft" : c+"px"});//"attr(padding-left + 32px)"});
			// // c+=($igk(p).getWidth() + igk.getPixel(p.getComputedStyle("margin-right")));
			// // console.debug("width : "+$igk(p).getWidth());
			// // console.debug(igk.getPixel(p.getComputedStyle("margin-right")));
		// }
	}
	
	// p = h.fn["trackpos"];
	// if (p){
		// // var x = (1- ((W)/ this.getWidth())) * 100.0;
		// // console.debug(x);
		// // p.setCss({width: x+"%"});
	// }

	
	//init setting
	h.setPlaybackRate(d.playbackRate || 1.0);
	
}

};


igk.system.createNS("igk.videos.players", {
	getItem:function(i){
		if ((i>=0)&&(i<m_vidModels.length))
			return m_vidModels[i];
		return null;
	}
});

igk.winui.initClassControl("igk-video-controls", __init);

})();