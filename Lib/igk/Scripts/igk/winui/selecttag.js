"use strict";

(function () {
    // | author: C.A.D. BONDJE DOUE
    // | date: 2021/05/14
    // | control: igk-winui-selecttag
    // | version: 1.0

    function GItemClass(id, text, value) {
        var _tag = igk.createNode("div");
        _tag.addClass("glass-btn dispib cursor-pointer")
            .setCss({
                "padding": "8px",
                "border": "1px solid #ddd",
                "borderRadius": "4px",
                "marginRight": "4px"
            });

        _tag.setHtml(text);
        _tag.add("input")
            .setAttribute("name", id + "[]")
            .setAttribute("type", "hidden")
            .setAttribute("value", value);
        igk.defineProperty(this, "tag", { get: function () { return _tag; } });
    }

    function __initSelecTag() {
        var q = this;
        var data = JSON.parse(q.getAttribute("igk:data"));
        var options = igk.initObj( JSON.parse(q.getAttribute("igk:options")) , {
            "show_tag": true,
            "debug":false,
            "click":null,
            "require":0,
            "selected":[],
            "select_all_text":"select all"
        } );
        if (!Array.isArray(options.selected)){
            options.selected = [options.selected+""];
        } 

        var id = q.getAttribute("id");
        if (!id) {
            q.remove();
        }
        var _input = igk.createNode("input");
        var _datalist = igk.createNode("datalist");
        var _select_allbtn = igk.createNode("a");
        var _box = null; //gk.createNode("div");
        var _listkey = id + "_datalist";
        var _rassoc = {};
        var _select_list = {};
        var _press = !1;
        var _litem = null;
        var _init_ = !1;
        var _form = q.select("^form").first();
        if (_form){
            _form.on("submit igkFormBeforeSubmit", function(e){              
                if (options.require){
                    var p = false;
                    for(var t in _select_list){
                        p = true;
                        break;
                    }
                    if (!p){
                        e.preventDefault();
                        e.stopPropagation(); 
                        q.addClass("igk-danger");
                    }else{
                        q.rmClass("igk-danger");
                    }
                }
            }); 
        }
        function _update(d){
            var c = d || (_input.o.value+"").toLowerCase(); 
            if (c in _rassoc) {
                if (! (c in _select_list)){
                    var gitem = new GItemClass(id, _rassoc[c].t, _rassoc[c].i);
                    if ( _litem ){
                        _litem.insertAfter(gitem.tag.o);    
                    }else{
                        if (!_box){
                            _box = igk.createNode("div").addClass("glass-box");
                            q.add(_box);
                        }
                        _box.add(gitem.tag.o);
                        //q.insertAfter(gitem.tag.o);
                    }
                    _litem = gitem.tag;
                    _select_list[c] = gitem;   
                    if (options.click){
                        gitem.tag.on("click", options.click);
                    }else {
                        gitem.tag.on("click", function(){
                            _select_list[c].tag.remove();
                            delete(_select_list[c]);
                            _litem = null;
                            for( var s in _select_list){
                                _litem = _select_list[s].tag;
                            }
                            _initData();
                        });
                    }
                    _initData();            
                }
                _input.o.value = "";
                _input.o.focus();
            }
        };
        function _handleTab(){
            var body = q.select("^body").first();
            if (body == null){ 
                $igk(document).unreg_event("keyup", _handleTab);
                return;
            } 

        };
        $igk(document).reg_event("keyup", _handleTab);
        function _haveProperty(n){
            for(var c in n){
                return true;
            }
            return false;
        };

        function _initData(init){
            if (_init_){
                return;
            }
            _init_ = !0; 
            _datalist.o.innerHTML = "";
            data.forEach(function (i) {
                var k = i.t.toLowerCase();
                if (init){
                    _rassoc[k] = i; 
                    if (options.selected.indexOf(i.i) != -1){
                        _update(k);
                        return;
                    };
                    i.option = _datalist.add("option").setAttribute("value", i.t).setHtml(i.t);        
                    // console.debug("select: "+i.i + "typeof: "+typeof(i.i)+ " ::: "+options.selected.indexOf(parseInt(i.i)));
                } else {
                    if (k in _select_list)
                        return;                
                    i.option = _datalist.add("option").setAttribute("value", i.t).setHtml(i.t);
                }
            }); 
            if (_datalist.o.options.length ==0){
                _input.addClass("dispn_i");
                _input.o.removeAttribute("required");
                _select_allbtn.addClass("dispn_i");
            } else {
                _input.rmClass("dispn_i");
                _select_allbtn.rmClass("dispn_i");
                if (_haveProperty(_select_list)){
                    _input.o.removeAttribute("required"); 
                }else{
                    _input.setAttribute("required", options.require);
                }
            }
            _init_ = !1;
        };

        function select_all(){
            data.forEach(function(i){
                var k = i.t.toLowerCase();
                _update(k);
            });
        };
     

  
        _datalist.o.id = _listkey;
         
        // | init input
        _input.addClass("igk-control cltext glass-text");
        _input.setAttribute("list", _listkey);
        if (options.require){
            _input.setAttribute("required", 1);
        }
                //_input.setAttribute("name", id);
        _input.on("change", function () {
            var c = (_input.o.value+"").toLowerCase();
            if (c in _rassoc){
                _update();
            } else {
                _input.o.value = "";
            }
        }).on("click", function () { 
        }).on("input", function () { 
            //_update();
            // console.debug("input: "+_input.o.value );
            if (!_press){
                _update();
                _press = !1;
            }
        }).on("keyup", function(e){
            if (e.keyCode == 9){
                // recieve focus from tab
            } 
        }).on("keypress", function(e){  
            var stop = 0;
            var _press = true;
            //             
            if (e.keyCode == 13){
                _update(); 
                stop = 1;
            }
            if (stop){
                e.preventDefault();
                e.stopPropagation();
            }
        });
        if (options.debug){
            q.setHtml("select tag: " + id);
        }
        q.insertAfter(_select_allbtn.o);
        q.insertAfter(_input.o);
        q.insertAfter(_datalist.o);

        _select_allbtn.addClass("glass-select-all cursor-pointer").setHtml( options.select_all_text );
        _select_allbtn.on("click", function(e){
            e.preventDefault();
            e.stopPropagation();
            select_all();
        });

        _initData(true);
    };
    igk.winui.initClassControl("igk-winui-selecttag", __initSelecTag);
})();