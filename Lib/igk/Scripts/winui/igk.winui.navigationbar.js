
/*
  file: igk.winui.navigationbar.js
  author: C.A.D. BONDJE DOUE
 IGK NAVIGATION SCRIPTS
 REPRESENT A IGK CONTROL FOR VERTICAL NAVIGATION BAR
*/
igk.system.createNS("igk.winui.navigationbar", {
	fitfix : function(node, parent)
	{
	
		var t =$igk(node);
		var l =$igk(parent);
		if(t){
			
			t.setCss({
				"position":"fixed",
				"right": (l.fn.hasVScrollBar()? l.fn.vscrollWidth()+1 : 0)+"px",
				"bottom": ((l.fn.hasHScrollBar()? l.fn.hscrollHeight() +1: 0))+"px",
				"top":"auto"
			});		
		}
	},
	//node: node where to start
	//parent: node that match controller reference
	init: function(node, parent,  properties)
	{
		
	if ((node == null) || (parent == null))
		return ;
		
	var m_node = node;
	var m_parent = parent;
	var m_properties = igk.utils.getv(properties, "properties", {duration:1000, interval:20, "orientation":"vertical"});
	var m_autodetect = igk.utils.getv(properties, "autodetect", true); //autodecting node
	var m_autonavigate = igk.utils.getv(properties, "autonavigate", true); //allow automatic navigation
	var m_dictionary = [];//dictionary of navigator tag
	var m_manual_scroll = true; // for user to scroll manually
	var m_scrollTimeout = null;
	var m_currentpage = null;
	
	function __initLink(info){
		igk.appendProperties(this, info);
		var self = this;
		this.detectview = function()
		{
			var y = $igk(m_parent).getscrollTop();
			var t = $igk(this.target);
			var m = t.o.offsetHeight;			
			var cy = t.getLocation().y;
			var vH = y-cy;
			if (this.selected)
			{
				if (!( (vH>=0) && (vH<m)))
				{	
					self.selected = false;
					if (this.bgview)
						igk.animation.fadeout(this.bgview.o, 20, 500,1.0, function(){ self.selected = false;});				
						
				}
			}
			else{
				if ((vH>=0) && (vH<m))
				{					
					self.selected = true;

					//change hash
					var s = (""+self.a.href).split('#')[1];									
					m_currentpage =  s.split('/')[1]; 
					var v_uri = (document.location+"").split('?')[0].split("#")[0];
					//document.location.hash = s;
					  window.history.pushState({"html":"data","pageTitle":v_uri+"#"+s},"",v_uri+"#"+s);
					if (this.bgview){					
						igk.animation.fadein(this.bgview.o, 20, 500,0.0, function(){ 
							self.selected = true; 	
						});
					}
				}
			}
			
		};
		if (this.options )
		{
				if (this.options.scroll)
				{
					var func =  function() {   self.options.scroll(self, m_parent); } ;
					igk.winui.reg_event(m_parent, "scroll", func );		
					func();
				}
		}
		if (!this.options || !this.options.nomenu)
		{
			this.bgview = $igk(this.a.parentNode).add("div", {
			"className":"posab fitw fith loc_l loc_t zback navigationbg", 
			"class":"posab fitw fith loc_l loc_t zback navigationbg"
			});
			this.bgview.setOpacity(0.0);
			
		}
		this.a.onfocus = function(){ this.blur(); return false; };
		this.a.onclick = function(){ 
			var a = self.a;
			if (document.location != a.href)
			{ 
				var s = (""+document.location).split('?')[0].split("#")[0];					
				var hash = (""+a.href).split('#')[1];
				document.location.href = s+"#"+hash;
				__navigateTo();
			}
			else{
				__navigateTo();
			}
			return false;
		}
	};
	function __gettarget(n)
	{	
		if (m_dictionary[n])
			return m_dictionary[n].target;
		return null;
	};
	function __navigateTo()
	{
		
		var l =(""+document.location);
		
		if (/#\//.exec(l))
		{
			
			var n =  l.split('#/')[1]; 
			if (m_currentpage != n)
			{			
				m_currentpage = n;
				var i = $igk(__gettarget(n));
				if(i)		
				{					
					m_manual_scroll =false;
					$igk(m_parent).scrollTo(i.o, m_properties, function(){ __detectnode(); m_manual_scroll =true;});
				}
			}
			else{
				var i = $igk(__gettarget(n));
				if(i)		
				{					
					m_manual_scroll =false;
					$igk(m_parent).scrollTo(i.o, m_properties, function(){ __detectnode(); m_manual_scroll =true;});
				}
			}			
		}
		
	};
	function __detectnode(){
		if (!m_autodetect)
			return;
		for(var i = 0; i < m_dictionary.length; i++)
		{
			m_dictionary[i].detectview();
		}		
	}
	//init bar
	(function(node){
		//get all child a element on this node
		var v_nodes = node.getElementsByTagName("a");
		//var v_marks = [];
		var v_key = null;
		var v_taget = null;
		var v_ainfo  = null;
		var p = m_node;
		
		for(var i = 0; i < v_nodes.length ; i++)
		{
			//replace name with navigation name
			v_a = v_nodes[i];
			
		if (/#/.exec(v_a.href))
			{
			
			n = v_a.href.split('#')[1];			
			//console.debug("navigation key "+n);
			v_key = ((v_key = v_a.getAttribute("igk-navigation-key"))==null)? n: v_key;	
			v_target  = v_a.getAttribute("igk-navigation-target");	
			
			
			if (m_parent.id == v_target)
			{
				hp = $igk(m_parent);
			}
			else
				hp = $igk(m_parent).getChildById(v_target);						
			
			if (hp == null)
			{					
				continue;
			}	
			v_ainfo =  {
			"name": n, 
			"offset":0,
			"target": hp,
			"targetname" : v_target,
			"options": igk.JSON.parse(v_a.getAttribute("igk-navigation-options")),
			"a": v_a,
			"allownavigation": true,
			"selected":false
			};
			//register menu
			if (!v_ainfo.options || !v_ainfo.options.nomenu)
			{
				v_ainfo.allownavigation = false;		
			}
			
			v_a.href = "#/"+v_key;		
			m_dictionary[v_key] =  new __initLink(v_ainfo);
			m_dictionary.push(m_dictionary[v_key]);
			}
			
		}
		//register event
		if (p)
		{
		
				var m_eventContext = igk.winui.RegEventContext(p);	
				if(m_eventContext)
				{
					m_eventContext.reg_window("hashchange", function(){   __navigateTo(); } );	
					m_eventContext.reg_window("load", function(){   if (m_autonavigate) __navigateTo(); });	
					m_eventContext.reg_window("resize", function(){  if (m_autonavigate) __navigateTo();  });
					m_eventContext.reg_event(m_parent, "scroll", function()
					{
							if (m_manual_scroll) 
							{	
								//wait for detecting node. after scroll end
								
								if (m_scrollTimeout)
									clearTimeout(m_scrollTimeout);
								m_scrollTimeout = setTimeout(
								__detectnode, 100);
							}
					});		
				}
				__detectnode();
				if (m_autonavigate)
				{
					__navigateTo();
				}
				//igk.ready( 
				// function(){ __detectnode(); });
				
				// }
				// else{
					// alert("igk.winui.navigationbar. can't initiate navigationbar");
				// }
				
			
			// })(m_node);
		}
	})(m_node);
}
});