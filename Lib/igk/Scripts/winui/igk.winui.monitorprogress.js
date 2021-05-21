"use strict";
(function(){

    var monitor = 0;
    igk.winui.initClassControl("igk-winui-monitor-progress", 
    function(){
        if (monitor)
            return;
        //init only one ajx monitor progress per page
        var q = this;
        var ajx = 0;
        igk.publisher.register("sys://ajx/loadstart", function(e){
            q.setCss({"display":""}); 
            ajx = e.evt.target;
        });
        igk.publisher.register("sys://ajx/loadend", function(e){ 
            q.setCss({"width": "0px"});
            ajx = 0;
        });
        igk.publisher.register("sys://ajx/loadprogress", function(e){
            // console.debug("monittor .... progress",e);
            var t = 0;
            if (e.evt.total > 0){ // we know the total amount of getting data
                t =  e.evt.loaded / e.evt.total;  
                q.setCss({"width": (t * 100) +"%"});
            }else {
                
            }
        });
        q.setCss({"display":"none"});
    },
    {"desc":"monitor progress component"}
    ); 
})(); 