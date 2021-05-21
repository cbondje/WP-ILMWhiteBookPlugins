(function(){
    var _NS  = igk.system.getNS("igk.dom.extension.FORM") || igk.system.createExtensionProperty("FORM", "igk.dom.extension") ;
    _NS.submit = function(func){
        if (this.getAttribute("enctype") == "application/json"){
            data = this.serializeData();
            igk.ajx.send({
                uri: this.o.action,
                method: 'POST',
                contentType: 'application/json',
                func: function (xhr) {
                    if (this.isReady()) {
                        if (func) {
                            func.apply(this, [xhr]);
                        }
                    }
                },
                param: data   
            });

        }else {
            this.o.submit();
        }
    };
    igk.appendProperties(_NS, {
        reset: function(){
            this.o.reset();
        },
        serializeData(){
            obj = {};
            frmData = new FormData(this.o);
            frmData.forEach(function(v, k){
                if (k in obj){
                    if (!Array.isArray(obj[k])){
                        obj[k] = [obj[k]];
                    }
                    obj[k].push(v);
                    return;
                }								
                obj[k]=v;
            });
            return JSON.stringify(obj);
        }
    });
})();