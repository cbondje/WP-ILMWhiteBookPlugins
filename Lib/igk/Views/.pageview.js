"use strict";
(function(){
var pageview = []; 
var pageindex = 0;
var maxpage = 0;
var btn_prev, btn_next;
function updateSetting(index){
	pageindex = index;
	btn_prev.rmClass("dispn");
	btn_next.rmClass("dispn");
	if (index==0){
		btn_prev.addClass("dispn");
	}
	if (pageindex >=maxpage){
		btn_next.addClass("dispn");
	} 
};

igk.ready(function(){
	$igk("#pageview").each_all(function(){
		var i = 0;
		var max = this.select(">div").getCount()-1;	 
		btn_prev = $igk("button.prev").first();
		btn_next = $igk("button.next").first();
		maxpage = max;
		pageindex = i;
		igk.appendProperties(this, {
			movenext: function(){
				i = Math.min(i+1, max);
				this.setCss({
					"transform": "translate("+(-100 * i)+"%, 0)"
				});
				updateSetting(i);
			},
			moveback: function(){
				i = Math.max(i-1, 0);
				this.setCss({
					"transform": "translate("+(-100 * i)+"%, 0)"
				});
				updateSetting(i);
			}
		});
		updateSetting(i);
	});

	
});
})();