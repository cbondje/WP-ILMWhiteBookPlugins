"use strict";
(function(){

function _sliderObj(q, p){
	var btnl = igk.createNode("div").addClass("igk-btn-slider igk-pull-left");
	var btnr = igk.createNode("div").addClass("igk-btn-slider igk-pull-right");
	var sliderz = igk.createNode("div").addClass("igk-slider-z");
	var pages = [];
	// var info = igk.createNode("div");
	// info.setHtml("info");
	var m_vpage = 0;
	var self = this;
	var property = {orientation:'horizontal', filter: _filter};
	
	if (p.orientation && (p.orientation == 'vertical'))
	{
		q.addClass("vertical");
		property.orientation = "vertical";
	}
	
	function _update(){
		if (m_vpage>0){
			btnl.addClass("igk-active");
		}
		else 
			btnl.rmClass("igk-active");
			
		if ((m_vpage>=0) &&  (m_vpage < (pages.length-1))){
			btnr.addClass("igk-active");
		}
		else 
			btnr.rmClass("igk-active");
	}
	function _filter(i){
		if ( (i.o.tagName.toLowerCase() == 'script') || (i.o == btnl.o) || (i.o==btnr.o))
			return true;
		
		return false;
	}
	function _init(){
		pages = [];
		q.select(".igk-slider-page").each(function(){
			if(this.o.parentNode == q.o){
				pages.push(this);
				sliderz.add(this);
			}
			return true;
		});
		_update();
	}
	function _moveleft(evt){
		evt.preventDefault();
		// console.debug("vpage : "+m_vpage);
		if (m_vpage>0)
		{
			m_vpage--;		
			ns_igk.winui.fn.navigateTo(pages[m_vpage].o,property).apply(this, evt);
		}
		_update();
	};
	function _moveright(evt){
		evt.preventDefault();
		if (m_vpage < self.getCount()-1)
		{
			m_vpage++;			
			ns_igk.winui.fn.navigateTo(pages[m_vpage].o,property).apply(this, evt);
		}
		_update();
	};
	
	btnl.reg_event("click", _moveleft);
	btnr.reg_event("click", _moveright);
	
	q.add(sliderz);
	q.add(btnl);
	q.add(btnr);	
	//q.add(info);	
	
	igk.appendProperties(this, {
		getVisiblePageIndex: function(){return m_vpage; },
		getCount:function(){return pages.length; },
		init:_init
	});
	
	_init();
}


igk.system.createNS("igk.winui.slider", {
	init: function(p){
		var q = $igk(igk.getParentScript());		
		var slider = new _sliderObj(q, p);		
	}
});
})();