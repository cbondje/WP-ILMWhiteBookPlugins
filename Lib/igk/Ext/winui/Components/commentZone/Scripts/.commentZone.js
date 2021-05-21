"uses strict";


(function(){
	
	igk.winui.initClassControl("igk-cm-btn", function(){
		//comment button
		this.addClass("igk-btn igk-btn-default");
		
		var s = this.getAttribute("href");
		var opt = igk.JSON.parse(this.getAttribute("options"));
		var show = 0;
		var p = null;
		var q = this;
		var i = 0;
		this.o.removeAttribute("href");
		//post comment
		this.reg_event("click", function(){
			if (show){
				p.remove();
				var b = i.o.value;
				if (b.length>0){
					var m = {
						'id':'data',
						'value': {
							'clComment':i.o.value,
							'clId':opt.id
						}
					};
					igk.ajx.fn.postData(s,m,null);
				};
				show = 0;
				i.setHtml("");
				i.o.value = "";
			}else{
				show = 1;
				if (!p){
					p = igk.createNode("div");		
					i = p.add("textarea");
					i.o["id"] = "clComment";
					i.addClass("cltextarea");
					
				}
				q.select("^div").first().add(p.o);
			}
		});
	});
	
	function __init_i(){
		var p = this;
		var opt = igk.JSON.parse(p.getAttribute("igk:data"));
		this.qselect('.cm-btn.more').each_all(function(){
			//init button	
			var a = this.select("^.a").first().o.parentNode;			
			var q = this;
			var sh =0;
			var cajx = 0;
			var t = 0;
			this.reg_event("click", function(evt){
				if (!sh){
					q.addClass("igk-show");
					sh = 1;
					if (cajx){
						cajx.abort();
					}
					cajx = igk.ajx.get('comment_viewmore_ajx/'+opt.id, null,function(xhr){
						if (this.isReady()){
							var h = $igk(a).qselect("div .sub-cm-"+opt.id).first();
							if (h){
								h.setHtml(xhr.responseText).init();
							}
							else{
								if (!t){
									t = $igk(a).add("div");
									t.addClass("sub-cm-"+opt.id);
								}else
									$igk(a).add(t);
								t.setHtml(xhr.responseText).init();
							}
							cajx = 0;
						}
					});
				}
				else{
					q.rmClass("igk-show");
					if (t){
						var h = $igk(a).qselect("div .sub-cm-"+opt.id).first();
						if (h){
							h.remove();
						}else{
						t.remove();
						t.setHtml('');
						}
					}
					sh = 0;
				}
			});
			
		});
	
		this.qselect(".i .cm-btn.drop").each_all(function(){
			this.reg_event("click", function(){
				igk.ajx.get('comment_drop_ajx/'+opt.id, null, function(){
					if(this.isReady()){
						console.debug("ok");
						p.remove();
					}
				});
			});
		});
	
	};
	
	
	igk.winui.initClassControl("igk-comment-z", function(){		
		this.qselect('.i').each_all(__init_i);
		// //cm-btn.more
		// this.qselect(".i .cm-btn.more").each_all(function(){
			// //init button	
			// var p = this.select("^.i").first();
			// var a = this.select("^.a").first().o.parentNode;
			// if (!p)return;
			
			// var opt = igk.JSON.parse(p.getAttribute("igk:data"));
			// var q = this;
			// var sh =0;
			// var cajx = 0;
			// var t = 0;
			// this.reg_event("click", function(evt){
				// if (!sh){
					// q.addClass("igk-show");
					// sh = 1;
					// if (cajx){
						// cajx.abort();
					// }
					// cajx = igk.ajx.get('commentviewmore/'+opt.id, null,function(xhr){
						// if (this.isReady()){
							// var h = $igk(a).qselect("div .sub-cm-"+opt.id).first();
							// if (h){
								// h.setHtml(xhr.responseText).init();
							// }
							// else{
								// if (!t){
									// t = $igk(a).add("div");
									// t.addClass("sub-cm-"+opt.id);
								// }else
									// $igk(a).add(t);
								// t.setHtml(xhr.responseText).init();
							// }
							// cajx = 0;
						// }
					// });
				// }
				// else{
					// q.rmClass("igk-show");
					// if (t){
						// var h = $igk(a).qselect("div .sub-cm-"+opt.id).first();
						// if (h){
							// h.remove();
						// }else{
						// t.remove();
						// t.setHtml('');
						// }
					// }
					// sh = 0;
				// }
			// });
			
		// });
		
		
	});	
})();