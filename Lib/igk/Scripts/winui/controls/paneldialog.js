"use strict";

(function(){
	

var _dialog = [];
var _init = 0;
var _NS = "igk.winui.controls";
function _closeDialog(b){
    var p = b || _dialog.pop();
    if (p){
        p.addClass("igk-hide").reg_event("transitionend", function(){
            p.remove();
        });
    }
};

function _panDialogInit(){
    _dialog.push(this);
    if (!_init){
        igk.winui.events.regKeyPress(function(e){
            if(e.keyCode == igk.winui.inputKeys.Escape){
                if (_dialog.length> 0){
                    _closeDialog();
                    e.stopPropagation();
                    e.preventDefault();
                }               
            }
        });
        _init=1;
    }
    var q = this;
    var _idx = _dialog.length-1;
	//var _rm = q.remove;
	igk.appendChain(q, 'remove', function(){
		q.unreg_event("windowresize", _updating);
	});
	
	
	
    var _closeBtn = this.select(".box .igk-title .close").first();
    if (_closeBtn){
        _closeBtn.reg_event("click", function(e){
            _closeDialog(q);
            e.stopPropagation();
            e.preventDefault();
            delete(_dialog[_idx]);
        });
    };
	function _update_size(){
		
		
		q.rmClass("inner");
		// console.debug("size changed ");
		var f = q.select(".igk-content").first();
		f.setCss({
			"height":"auto"
		});
		
		
		var H = q.o.scrollHeight;
		var h = window.innerHeight;
		var box = q.select("> .box").first();
		var c = box.getScreenLocation();
		var m = f.getScreenLocation();
		var diff = {x:c.x - m.x, y:c.y - m.y};
		// console.debug(diff);
		// console.debug(H+" vs "+h);
		if (H>h){
			// console.debug("add class");
			q.addClass("inner inner-h");
			f.setCss({"height": (box.height() + diff.y -10 )+"px"});
		}else{
			// console.debug("rm class");
			q.rmClass("inner inner-h");
		}
	};
	function _updating(){
		if (tm_out)
			clearTimeout(tm_out);
		
		// console.debug("update size");
		tm_out = setTimeout(_update_size, 500);
	};
	this.select("form").each_all(function(){
		if (this.getAttribute("igk-ajx-form")){
			var input = document.createElement("input");
			input["type"] = "hidden";
			input["name"] = "igk-ajx-form";
			input["value"] = 1;
			this.appendChild(input);
		}
	});
	var tm_out = 0;
	q.on("windowresize", _updating);
	_update_size();
};

var _LNS = igk.system.createNS(_NS, {
    panelDialog:function(){}
});

igk.appendProperties(_LNS.panelDialog.prototype, {

});


igk.appendProperties(_LNS.panelDialog, {
close: _closeDialog
});


igk.winui.createPanelDialog=function(t, d){
	var dd = igk.createNode("div");
	dd.addClass("igk-winui-panel-dialog");
	var box = dd.add("div").addClass("box");
	var tl = box.add("div").addClass("igk-title");
	tl.add("span").setHtml(t);
	var ctn = box.add("div").addClass("igk-content");
	var s = 0;
	if (d){
		ctn.add(d);
	}
	//if (s){
		//if ((svgBtn == igk_getv(s, "closeBtn")){
			var btn = igk.createNode("div").addClass("igk-svg-lst-i").setAttribute("igk:svg-name", "drop");
			// console.debug(btn);
			//}
			
	var g = igk.winui.createSVGLi("drop");
	if (g){
		tl.add("a").addClass("igk-btn close igk-svg-host").setAttribute("href", "#").add(g); //etHtml("close");
	}	
	_panDialogInit.apply(dd);
	dd.show = function(){
		//console.debug("showing");
	};
	return dd;
};



igk.winui.initClassControl("igk-winui-panel-dialog", _panDialogInit);

igk.winui.closePanelDialog = _closeDialog;
})();