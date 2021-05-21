/*
*
*/

"uses strict";
(function(){
	igk.system.createNS("igk.winui.controls",
		{
			init : function(){
				var t = document.getElementsByTagName("textarea");
				for(var i = 0; i < t.length; i++)
				{
					var s = t[i].getAttribute("defaulttext");
					if (s){
						new igk.winui.controls.textareamanager(t[i], s);
					}
				}
			},
			textareamanager: function(item , text){
				this.item  = item;
				this.tip = text;				
				this.text = "";
				var q = this;
				function update()
				{									
					if (q.text.length == 0)
					{
						q.item.value = q.tip;
						q.item.selectionStart = 0;
						q.item.selectionEnd = 0;
						$igk(q.item).addClass("cltextareatip");
						$igk(q.item).setCss({color:"gray"});
					}
					else{
						$igk(q.item).rmClass("cltextareatip");
						$igk(q.item).setCss({color:"black"});
					}
				};	
				function updateKey(evt){															
					q.text = this.value;
					//igk.show_prop(q.item); 
					update();
					
				};		
				function keypress(evt){
					//to cancel
					evt.preventDefault();
					evt.stopPropagation();
					igk.show_prop(evt); 
					
					q.text = this.value;
					console.debug(q.text);
					
				};						
				function updatedown(evt)
				{
					if (q.text.length == 0){
						//igk.show_prop(evt);
						if (evt.key.length == 1)
						{
							q.text =  evt.key; //String.fromCharCode(evt.keyCode);						
							q.item.value = q.text;
							q.item.selectionStart = 1;
							q.item.selectionEnd = 1;
							
						}
						evt.preventDefault();
					}
				}
				$igk(this.item).reg_event("change", update);
				$igk(this.item).reg_event("keyup", updateKey);
				$igk(this.item).reg_event("keydown", updatedown);
				update();
			}
		}
	);
	igk.ready(igk.winui.controls.init);
})();