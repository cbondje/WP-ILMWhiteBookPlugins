/*
*file: igk.winui.querytextbox.js
*author: c.a.d. BONDJE DOUE
*description: querytextbox control. that send querty to system and populate options div.
*usage : igk.winui.querytextbox.init(target, requestedUri);
*/
(function(){
	//---------------------------------------------------------------------------------
	//private variable in context
	//---------------------------------------------------------------------------------
	
	igk.system.createNS("igk.winui.querytextbox", {
		init:function(target, uri)
		{
			if (target==null)
				return;
				
			var m_target = target;
			var m_uri = uri;
				
			
			function querytextboxobj(){
				var m_target = target;
				var m_uri = uri;
				var q = this;
				//init values
				this.ajx = null;
				this.update = update;
				this.view = document.createElement("div");
				this.ul = null;
				
				m_target.parentNode.appendChild(this.view);
				
				$igk(m_target).reg_event("keyup", function(evt){ 	
					q.update(this.value);
				});
				function _hideDiv()
				{
					$igk(q.view).setCss({display:"none"});
				}
				function _changeValue(select)
				{
					m_target.value = select[select.selectedIndex].innerHTML;
					_hideDiv();
				}
				function _updateValue(value)
				{
					m_target.value =value;
					_hideDiv();
				}
			   function update(){
				if (this.ajx!=null)
					this.ajx.xhr.abort();
				this.ajx = igk.ajx.apost(m_uri+"&t="+m_target.value, null, 
					function(xhr){
					if (this.isReady()){
						//update ul element	
						var loc = $igk(m_target).getScreenLocation();
						$igk(q.view).setCss({display:"block", position: "fixed",
							left: (loc.x)+"px", 
							top: (loc.y+ m_target.clientHeight+2)+"px", 
							minWidth: m_target.clientWidth+"px",
							maxHeight: "200px",
							overflow:"auto",
							overflowX:"hidden",
							zIndex : 1010,
							backgroundColor : "white"
						});						
						q.ajx = null;
						if (q.ul == null)
						{
							//q.ul = document.createElement("select");//load selection
							q.ul = document.createElement("ul");
							$igk(q.ul).addClass("clselect dispb fitw fith");
							//$igk(q.ul).reg_event("change", function(){ _changeValue(this,true, true); });							
						}
						
						var md = document.createElement("div");
						md.innerHTML = xhr.responseText;
						md = md.getElementsByTagName("response")[0];
						$igk(md).select("item").replaceTagWith(//"option"
						"li"
						).each(function(){							
							
							var a = document.createElement("a");
							var name = this.getAttribute("name");
							var desc = this.getAttribute("description");
							var value = this.getHtmlContent();
							var idata = "";
							if (name){
								idata += "<span>"+name+"</span>";
							}
							if (desc){
								idata += "<div>"+desc+"</div>";
							}
							a.innerHTML = idata;
							a.href = "#";
							a.setAttribute("value", value);
							this.setHtmlContent("");
							this.o.appendChild(a);
							return this;
						});
							
						q.view.innerHTML = "";
						q.view.appendChild(q.ul);
						q.ul.innerHTML = md.innerHTML;
						
						$igk(q.ul).select("a").each(function(){ 
							//for every a
							this.addClass("queryboxitem dispb fitw").reg_event("click", function(evt){ _updateValue($igk(this).getAttribute("value")); evt.preventDefault(); }); 
						return this;});
						
					}
					});
			
			};
		
				
			};			
			
			return new querytextboxobj(target, uri);
		}
	});
})();