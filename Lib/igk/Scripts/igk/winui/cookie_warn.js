"use strict;";
(function(){
    igk.winui.initClassControl("igk-cookie-warn", function(){
        var q = this;
        var a = this.select(".close a").first();
        if (a){
            a.reg_event("click", function(e){
                //console.debug("click "+q.getComputedStyle("height"));
                var trans = q.getComputedStyle("transition");
              //  console.debug(trans);
                q.setCss({height: q.getComputedStyle("height"), transition:'none'});
                e.preventDefault();
                e.stopPropagation();
                q. setCss({transition:trans, height:"0px", padding:"0px"});
                igk.web.setcookies(q.getAttribute("igk-domain-ewarn"), 1);
            });
        }

        q.reg_event("transitionend", function(e){
            // console.debug("transition end");
            // console.debug("height "+q.getComputedStyle("height"));
            
            if ((e.target == q.o) && (e.propertyName == 'height') ){
                if (q.getcomputedStyle("height")=="0px"){
                    
                    q.remove();
                }
            }
        });

    });
})();