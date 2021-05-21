"use strict";

(function(){
	
	igk.ctrl.registerAttribManager("igk-winui-checkbox-toggle-table", { desc: "register element to be fixed with scroll width" });
	
	
	igk.ctrl.bindAttribManager("igk-winui-checkbox-toggle-table", function(m,n){
		var q = this.o.tagName;
		if (n){
			var c = 0;
			if ((q == "input") && (this.o.type == "checkbox")){
				c = this;
			}else{
				//search for check box
				c = this.qselect("input[type='checkbox']").first();
			}
			
			if (c){
				c.on("change", function(){
					igk.html.ctrl.checkbox.toggle(this, igk.getParentByTagName(this, 'table'), this.checked, true );
				});
			}		
		}
	});
	
	
})();