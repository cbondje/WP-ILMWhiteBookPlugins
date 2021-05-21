"use strict";

(function()
{
    var options={};
    igk.system.createNS("igk.ctrl.filemanager", {
        newFile:function(){            
            $igk("igk-dialog.new-file").first().show(); 
        },
        init:function(u){
            options = u;
        }
    });
function init_dialog(){
    this.show = function(){
        if (this.showDialog)
            return;
        this.showDialog = 1;
        this.rmClass("dispn");
        var q = this;
        var next = q.o.nextCibling;
        var parent = q.o.parentNode;

        igk.winui.notify.showMsBox({
            title:q.getAttribute("igk:title") || "Dialog",
            type: q.getAttribute("igk:dialogtype") || "default",
            content: q.o,
            closeButton:0,
            settings:
                 {close:function(){
                    q.hide();  
                    if (next){
                        next.parentNode.insertBefore(next, q.o);
                    }else{
                        parent.appendChild(q.o);
                    }
                q.showDialog = null;  
            }}});
        };
    this.hide = function(){
        this.addClass("dispn");
    };
};
//on ready 
igk.winui.initClassControl("igk-dialog", init_dialog);

// igk.ready(function(){
//     $igk("igk-dialog").each_all(function(){
        
      
//     });

// });//end ready


})();