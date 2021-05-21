"use strict";
(function(){
	var djx = 0;

igk.system.createNS("igk.ctrl",{
		ca_update: function(xhr, target){ 		
		if (this.isReady())
		{ 		
		 //alert(":"+xhr.responseText);
			var f = igk.getParentByTagName(target, 'form');
			var q = document.createElement('tr'); 
			q.innerHTML = xhr.responseText;
			var t = igk.getElementsByTagName(f, 'table')[0];
			if (t)
				t.appendChild(q);//.innerHTML = q.innerHTML; 
		}
		},
		ca_updatetable: function(xhr, target){
			var f = igk.getParentByTagName(target, 'form');
			var q = document.createElement('table'); 
			q.innerHTML = xhr.responseText;
			var t = igk.getElementsByTagName(f, 'table')[0];
			if (t)
				t.innerHTML = q.innerHTML;
		},
		ca_update_checkchange: function(a, name)
		{//edit controller and article datatable info
			var q = a.form[name];
			if (q)
			{
			if ((q.length) && (q.length>0))
			{
				for(var s = 0; s < a.form[a.name].length; s++)
				{
					if (a.form[a.name][s] == a)
					{
						q[s].value = a.checked? "1": "0";
						break;
					}
				}				
			}
			else{
				q.value = a.checked? "1": "0";//test changed";
			}
			}
			else 
				alert("element not found : "+ name);			  
		},
		ca_ctrl_change: function(uri, q){
			if (!q)
			return; 
			if (djx){
				djx.abort();
			}
			djx = window.igk.ajx.post(uri+q.value,null,function(xhr){
			if (this.isReady()){ 	
				var s = $igk(q.form); 				
				s = s.getChildById('view_frame'); 				
				this.setResponseTo(s); 
			}
			});
		
		}
		
});
})();