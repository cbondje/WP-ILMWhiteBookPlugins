"use strict";

(function(){
    //----------------------------------------------------------
    // | class control: igk-picker-zone
    // | require attribute : igk:picker-zone-data
    //----------------------------------------------------------

    function PickerZoneClass(n){
        var data = JSON.parse(n.getAttribute("igk:picker-zone-data")) || {};
        var t_uri = data && data.uri ?  data.uri : "/";
        igk.winui.dragdrop.init(n.o, {
            uri: t_uri ,
            supported: data.accept,
	        update:function(evt){
            },
            drop: function(e){
                var data = e.dataTransfer;
                // console.debug("drop file ", e);
                for(var i = 0; i < data.files.length; i++){
                    igk.ajx.uploadFile(n.o, data.files[i],t_uri, true, null);
                } 
            },
            enter: function(e){
                // console.debug("ennnnnn");
            }

        });
        // console.debug(data);
        n.on("click", function(){
            igk.system.io.pickfile(t_uri, {
                "accept": data.accept
            });
        }).on("drop", function(){

        });
    };


    igk.winui.initClassControl("igk-picker-zone", function(){
        new PickerZoneClass(this);
    });

})();