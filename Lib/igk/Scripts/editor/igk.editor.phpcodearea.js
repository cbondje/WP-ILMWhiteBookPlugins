/*
phpcodearea
*/
(function(){

	igk.system.createNS("igk.editor.phpcodearea", {
		init: function(tn, props) //target name, properties
		{ 
			var s = $igk(igk.getParentScript()).select('#'+tn);
			var t = null;			
			if (s.getCount()>0){
				t = s.o.getNodeAt(0);
			}
			if (t==null)
				return;
			
		}
	});
})();