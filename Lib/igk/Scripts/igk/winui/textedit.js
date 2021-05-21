"uses strict;";

(function(){
	var m_ei;
	
	igk.system.createNS("igk.winui", {//init text object class
		textEdit:function(t,o){
			var q = $igk(t);
			if (q && !q.isTextEdit)
				q.isTextEdit=1;
			else{
				return;
			}
			//text edit object			
			function _restore(g){
				g.o.parentNode.replaceChild(q.o, g.o);
				m_ei=null;
				delete(g.o);
			}
			function _edit(){
				if (m_ei){
					m_ei.restore();
					m_ei=null;
				}				
				var g = igk.createNode("input");
				g.addClass("igk-textedit");
				g.o['id']=o.id;
				g.o["value"]=q.getHtml();				
				g.reg_event("keypress", function(e){				
					switch(e.keyCode){
						//
						case 27://escape
							_restore(g);
						break;
						case 13://enter
							igk.ajx.post(o.uri, o.id+"="+g.o.value, function(xhr){
								if (this.isReady()){
									q.setHtml(g.o["value"]);
									_restore(g);
									igk.ajx.fn.replace_or_append_to_body.apply(this, [xhr]);
								}
							});
							
						break;
						
					}
				});				
				q.o.parentNode.replaceChild(g.o, q.o);
				m_ei = {q:q, g:g, restore:function(){ _restore(this.g); }};
			};
			q.reg_event("click", function(e){
				e.preventDefault();
				e.stopPropagation();
				_edit();
			});
		
			this.edit=_edit;
		}
	});
	
	igk.system.createNS("igk.winui.textEdit", {//extent global class properties
		getCurrent:function(){return m_ei;}
	});
	
	function __itextEdit(){
		var q = this;
		var o = igk.JSON.parse(q.getAttribute("igk:data"));
		var ce = new igk.winui.textEdit(q, o);
		
		// function _restore(g){
			// g.o.parentNode.replaceChild(q.o, g.o);
			// m_ei=null;
			// delete(g.o);
		// }
		// q.reg_event("click", function(){
			// if (m_ei){
				// m_ei.restore();
				// m_ei=null;
			// }
			
			// var g = igk.createNode("input");
			// g.setHtml(q.getHtml());
			// g.o["value"]=q.getHtml();
			
			// g.reg_event("keypress", function(e){				
				// switch(e.keyCode){
					// //
					// case 27://escape
						// _restore(g);
					// break;
					// case 13://enter
						// igk.ajx.post(o.uri, o.id+"="+g.o.value, function(){
							// if (this.isReady()){
								// q.setHtml(g.o["value"]);
								// _restore(g);	
							// }
						// });
						
					// break;
					
				// }
			// });
			
			// q.o.parentNode.replaceChild(g.o, q.o);
			// m_ei = {q:q, g:g, restore:function(){ _restore(this.g); }};
		// });
	};	
	igk.winui.initClassControl("igk-textedit", __itextEdit);
})();