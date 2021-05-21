"use strict";
(function(){
    function show_tab(i){ 
        $igk(_tabs[_index]).addClass("dispn");
        $igk(_tabs[i]).rmClass("dispn");
        _index = i;
    };
    var _index = 0;    
    var _tab = 0;
    var _a = document.querySelectorAll("ul.menu a");
    var _tabs = document.querySelectorAll(".tabcontrol .tab");
 
    _a.forEach(function(i){        
        var _info = {tab: _tab};
        i.addEventListener("click", function(e){
            e.preventDefault();
            show_tab(_info.tab);
        });
        _tab++;
    });
    if (_a.length> 0)
        show_tab(_index);
})();