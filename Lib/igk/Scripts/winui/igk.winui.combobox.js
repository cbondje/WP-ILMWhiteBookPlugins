"use strict";


(function(){
	function combobox(q){
		var _sindex = -1;		
	};
    igk.winui.initClassControl("igk-winui-combobox", function(){
			var cbox = new combobox(this);
			var lo = null;
			var c = null;
			var items = [];
			var _slitems = [];
			var _slindex = -1;
			var _cancelchange = !1;
			var _strict = !0;
			var i = null;
			var _search_on = "value";
			var _options = igk.JSON.parse(this.getAttribute("igk-options"));
			if (_options){
				_strict = _options.strict || !0;
				_search_on = _options.search_on || _search_on;
			}
			
			function _showOption(ul){
				ul.setCss({
					display: ''});
			};
			function _hideOption(ul){
				ul.setCss({
					display:"none"
				}); 
			};
			function _select(li){
					var index = items.indexOf(li); 
					if(lo) lo.rmClass("igk-active"); 
					i.o.value = igk.toStr(li.getAttribute("value"));
					i.setAttribute("title", i.o.value);
					lo = $igk(li);
					lo.addClass("igk-active");
					i.rmClass("igk-danger");					
			};
			function _FillOption(i, items){
				var rg = new RegExp(i.o.value,"ig");
				var _rg = 0;
				var _test = '';
				for(var ii = 0; ii < items.length; ii++){
					_rg = $igk(items[ii]);
					_test = igk.toStr(_rg.getAttribute(_search_on));
					_rg.o.style.display = "none"; 
					if (rg.test(_test)){
						_rg.o.style.display = "";	
						_slitems.push(_rg.o);
					}  
					rg.lastIndex = 0; 
				}
			}
			
			if (this.o.tagName.toLowerCase() == 'select'){
				var d = igk.createNode("div"); 
				i = d.add("input");
				var ul = d.add("ul");
				i.setAttributes({
					'igk-role':'text'
				});
				ul.setCss({
					display: '', 
					zIndex:10,
					width: '100%',
					overflowX:'hidden',
					position:'absolute'});
					
				igk.dom.copyAttributes(this.o, i.o); 
				i.setAttribute("autocomplete", "off");
				// init input 
				i.on("input", function(){
					_showOption(ul);
					_slitems.length = 0;
					_FillOption(i, items);				
					
					
				}).on("change", function(e){
					if (_cancelchange)
						return; 
					if (_slitems.length>0){
						var rg = new RegExp(i.o.value,"ig");
						if (_slitems.length == 1){
							if (rg.test( igk.toStr(_slitems[0].getAttribute(_search_on)))){
								_select(_slitems[0]);
							}
						}else { 
							for(var ii = 0; ii < _slitems.length; ii++){
								if (rg.test( igk.toStr(_slitems[ii].getAttribute(_search_on)))){ // select the first
									_select(_slitems[ii]);
									break;
								}
							}
						}
						_slitems.length = 0;
					}
					_hideOption(ul);
				}).on("dblclick", function(e){
					_FillOption(i, items);
					ul.addClass("igk-show");
					_showOption(ul);
				}).on("blur", function(e){ 
				}).on("keydown", function(e){
					var code = event.charCode || event.keyCode;
					var p = 0;
					if (_slitems.length > 0){
						if (code == igk.winui.inputKeys.Down){						
							_slindex++;
							if (_slindex<_slitems.length){
								_select(_slitems[_slindex]);
							}
							p=1;
							
						} else if (code == igk.winui.inputKeys.Up){
							_slindex = Math.max(_slindex-1, 0);
							if (_slindex>=0){
								_select(_slitems[_slindex]);
							}
							p=1;
						}		
						if (p){
						e.preventDefault();
						e.stopPropagation();
						}
					} 
				});
				i.o.validate = function(){
					var v = !1;
					var list = items;
					if (_slitems.length > 0){
						list = _slitems;
					}
					
					var rg = new RegExp(i.o.value, "ig");
					for(var ii = 0; ii< list.length; ii++){
						if (rg.test( igk.toStr(list[ii].getAttribute(_search_on)))){
							v = !0;
							break;
						}						
					}   
					if (!v){
						i.addClass("igk-danger");
					}else {
						i.rmClass("igk-danger");
					}
					return v;
				};
				d.options = {};
				var q = this.o.options;
				var _tt;
				for(var ii = 0; ii< q.length; ii++){
					c = ul.add('li');
					_tt = q[ii].getAttribute('igk-display') || q[ii].value || q[ii].innerHTML;
					c.setHtml(_tt); 
					c.setAttribute("value", q[ii].value);
					if (_search_on !="value"){
						c.setAttribute(_search_on, igk.toStr(_tt));
					}
					c.select("a").each_all(function(){
						this.o.parentNode.replaceChild(this.o, document.createText(this.o.innerText));
					});
					items.push(c.o);
					if (this.o.selectedIndex == ii){
						_select(c.o); 
					}
				}
				
				
				ul.select(">li").on("click", function(e){ 
					_hideOption(ul);
					var li = e.target;
					while (li && (li.parentNode != ul.o)){
						li = li.parentNode;
					} 
					if (!li)return; 
					_select(li); 
				}).on("mousedown", function(e){
					_cancelchange = !0;					
				}).on("mouseup", function(e){
					_cancelchange = !1;
				});
				
				this.o.parentNode.replaceChild(d.o, this.o);
				this.setCss({display:'none'});
				
				ul.setCss({display: 'none', 
				position:'absolute'}); 
			}
			
		}, {}); 
})();
