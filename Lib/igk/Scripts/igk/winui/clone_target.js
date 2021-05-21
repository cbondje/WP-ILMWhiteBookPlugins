"use strict;";
(function(){
    //clone target and copy it to parent 
    igk.winui.initClassControl("igk-winui-clone-target", function(){
        var s = this.getAttribute("igk-data");
        var d = $igk(s).first();
        if (d){
            var b=d.clone();
            b.o["id"] +="-cn"; // set cloned zone
            this.o.parentNode.replaceChild(b.o,this.o);            
        }
        this.remove();

    });
})();