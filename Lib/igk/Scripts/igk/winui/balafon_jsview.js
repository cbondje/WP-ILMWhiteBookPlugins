"use strict";

(function(){
    igk.winui.initClassControl("igk-balafon-js-view", function(){
        var t = this.getAttribute("igk:default-tag") || 'div';
        var r = new Function(this.getText());
        var o = $igk(document.createElement(t));
        r.call(o, [{source:this}]);
        this.replaceBy(o.o);
        o.remove();
    });
})();
