igk.system.createNS("igk.ctrl.changement",
{
init : function(uri, interval){

	if (!window.igk){
		return;
	}

	var target = window.igk.getParentScript();
	var m_timeout = null;
	var m_checkinterval =  interval?interval : 5000;
	var m_viewbox = null;
	
	
	function update()
	{
		m_timeout = setTimeout(checkforupdate, m_checkinterval);
	}
	function checkforupdate()
	{
		//alert(uri);
		igk.ajx.post(uri, null, function(xhr){ if (this.isReady()){ 
			if (xhr.responseText)
			{
				//alert("document changed : " +  (xhr.responseText) + " ll" );
				if (m_viewbox == null)
				{
					m_viewbox = document.createElement("div");
					document.body.appendChild(m_viewbox);
				}
				
				$igk(m_viewbox).setCss({"height": "32px", "backgroundColor":"red"}).addClass("fitw posfix loc_t loc_l");
				m_viewbox.innerHTML = xhr.responseText;
				igk.animation.fadeout(m_viewbox, 20, 1000, 1.0, function(){ update(); m_viewbox.parentNode.removeChild(m_viewbox); m_viewbox = null; });
			}
			else{					
				update();
			}
		}}, true);
		
	}	
	igk.ready(checkforupdate);
	
	//setTimeout(checkforupdate, 1000);
	
}
});