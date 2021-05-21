"use strict";


(function(){
    var _assets = {
        "bold":"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGhlaWdodD0iMjRweCIgdmlld0JveD0iMCAwIDI0IDI0IiB3aWR0aD0iMjRweCIgZmlsbD0iIzAwMDAwMCI+PHBhdGggZD0iTTAgMGgyNHYyNEgwVjB6IiBmaWxsPSJub25lIi8+PHBhdGggZD0iTTE1LjYgMTAuNzljLjk3LS42NyAxLjY1LTEuNzcgMS42NS0yLjc5IDAtMi4yNi0xLjc1LTQtNC00SDd2MTRoNy4wNGMyLjA5IDAgMy43MS0xLjcgMy43MS0zLjc5IDAtMS41Mi0uODYtMi44Mi0yLjE1LTMuNDJ6TTEwIDYuNWgzYy44MyAwIDEuNS42NyAxLjUgMS41cy0uNjcgMS41LTEuNSAxLjVoLTN2LTN6bTMuNSA5SDEwdi0zaDMuNWMuODMgMCAxLjUuNjcgMS41IDEuNXMtLjY3IDEuNS0xLjUgMS41eiIvPjwvc3ZnPg==",
        "italic":"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGhlaWdodD0iMjRweCIgdmlld0JveD0iMCAwIDI0IDI0IiB3aWR0aD0iMjRweCIgZmlsbD0iIzAwMDAwMCI+PHBhdGggZD0iTTAgMGgyNHYyNEgwVjB6IiBmaWxsPSJub25lIi8+PHBhdGggZD0iTTEwIDR2M2gyLjIxbC0zLjQyIDhINnYzaDh2LTNoLTIuMjFsMy40Mi04SDE4VjRoLTh6Ii8+PC9zdmc+",
        "underline":"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGhlaWdodD0iMjRweCIgdmlld0JveD0iMCAwIDI0IDI0IiB3aWR0aD0iMjRweCIgZmlsbD0iIzAwMDAwMCI+PHBhdGggZD0iTTAgMGgyNHYyNEgwVjB6IiBmaWxsPSJub25lIi8+PHBhdGggZD0iTTEyIDE3YzMuMzEgMCA2LTIuNjkgNi02VjNoLTIuNXY4YzAgMS45My0xLjU3IDMuNS0zLjUgMy41UzguNSAxMi45MyA4LjUgMTFWM0g2djhjMCAzLjMxIDIuNjkgNiA2IDZ6bS03IDJ2MmgxNHYtMkg1eiIvPjwvc3ZnPg==",
        "undo":"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGhlaWdodD0iMjRweCIgdmlld0JveD0iMCAwIDI0IDI0IiB3aWR0aD0iMjRweCIgZmlsbD0iIzAwMDAwMCI+PHBhdGggZD0iTTAgMGgyNHYyNEgwVjB6IiBmaWxsPSJub25lIi8+PHBhdGggZD0iTTEyLjUgOGMtMi42NSAwLTUuMDUuOTktNi45IDIuNkwyIDd2OWg5bC0zLjYyLTMuNjJjMS4zOS0xLjE2IDMuMTYtMS44OCA1LjEyLTEuODggMy41NCAwIDYuNTUgMi4zMSA3LjYgNS41bDIuMzctLjc4QzIxLjA4IDExLjAzIDE3LjE1IDggMTIuNSA4eiIvPjwvc3ZnPg==",
        "redo":"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGhlaWdodD0iMjRweCIgdmlld0JveD0iMCAwIDI0IDI0IiB3aWR0aD0iMjRweCIgZmlsbD0iIzAwMDAwMCI+PHBhdGggZD0iTTAgMGgyNHYyNEgwVjB6IiBmaWxsPSJub25lIi8+PHBhdGggZD0iTTE4LjQgMTAuNkMxNi41NSA4Ljk5IDE0LjE1IDggMTEuNSA4Yy00LjY1IDAtOC41OCAzLjAzLTkuOTYgNy4yMkwzLjkgMTZjMS4wNS0zLjE5IDQuMDUtNS41IDcuNi01LjUgMS45NSAwIDMuNzMuNzIgNS4xMiAxLjg4TDEzIDE2aDlWN2wtMy42IDMuNnoiLz48L3N2Zz4=",
        "alignLeft":"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGhlaWdodD0iMjRweCIgdmlld0JveD0iMCAwIDI0IDI0IiB3aWR0aD0iMjRweCIgZmlsbD0iIzAwMDAwMCI+PHBhdGggZD0iTTAgMGgyNHYyNEgwVjB6IiBmaWxsPSJub25lIi8+PHBhdGggZD0iTTE1IDE1SDN2MmgxMnYtMnptMC04SDN2MmgxMlY3ek0zIDEzaDE4di0ySDN2MnptMCA4aDE4di0ySDN2MnpNMyAzdjJoMThWM0gzeiIvPjwvc3ZnPg==",
        "alignCenter":"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGhlaWdodD0iMjRweCIgdmlld0JveD0iMCAwIDI0IDI0IiB3aWR0aD0iMjRweCIgZmlsbD0iIzAwMDAwMCI+PHBhdGggZD0iTTAgMGgyNHYyNEgwVjB6IiBmaWxsPSJub25lIi8+PHBhdGggZD0iTTcgMTV2MmgxMHYtMkg3em0tNCA2aDE4di0ySDN2MnptMC04aDE4di0ySDN2MnptNC02djJoMTBWN0g3ek0zIDN2MmgxOFYzSDN6Ii8+PC9zdmc+",
        "alignRight":"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGhlaWdodD0iMjRweCIgdmlld0JveD0iMCAwIDI0IDI0IiB3aWR0aD0iMjRweCIgZmlsbD0iIzAwMDAwMCI+PHBhdGggZD0iTTAgMGgyNHYyNEgwVjB6IiBmaWxsPSJub25lIi8+PHBhdGggZD0iTTMgMjFoMTh2LTJIM3Yyem02LTRoMTJ2LTJIOXYyem0tNi00aDE4di0ySDN2MnptNi00aDEyVjdIOXYyek0zIDN2MmgxOFYzSDN6Ii8+PC9zdmc+",
        "justify":"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGhlaWdodD0iMjRweCIgdmlld0JveD0iMCAwIDI0IDI0IiB3aWR0aD0iMjRweCIgZmlsbD0iIzAwMDAwMCI+PHBhdGggZD0iTTAgMGgyNHYyNEgwVjB6IiBmaWxsPSJub25lIi8+PHBhdGggZD0iTTMgMjFoMTh2LTJIM3Yyem0wLTRoMTh2LTJIM3Yyem0wLTRoMTh2LTJIM3Yyem0wLTRoMThWN0gzdjJ6bTAtNnYyaDE4VjNIM3oiLz48L3N2Zz4=",   
    };
    var _EDITOR = igk.system.createNS("igk.editor",{});
    /**
     * Editor class
     * @param {name} name attached to the editor
     */
    function _textEditorClass(name){
        var _input; 
        var _content;
        var _editor;   
        var _selectionRange;     
        var _settings = {
            "readonly":false
        };
        var q = this;

        _editor = igk.createNode("div").addClass("igk-text-editor");
        _content = _editor.add('div').addClass("content").setAttribute("contenteditable", "true")
        .setCss({
            "backgroundColor":"white",
            "position":"absolute",
            "left":0,
            "top": "32px",
            "right":0,
            "bottom":0,
            "padding":"10px",
            "overflowY":"auto"
        })
        .on("input", function(e){ 
            q.update_value(e.target.innerHTML);
        })
        .on("click", function(){ 
        })
        .on("keyup", function(){
            q.updateSelection();
        })
        .on("selectstart", function(e){
            _selectionRange = null;
            // console.debug("select start");
            if (_settings.readonly){
                e.preventDefault();
                e.stopPropagation();
            }
        });
        _input = igk.createNode("input").setAttribute("type", "hidden")
            .setAttribute("name", name)
            .setAttribute("id", name)
            .setAttribute("value", "");

        igk.appendProperties(this, {
            update_value : function(t){
                this.node.o.value = t;
                this.node.setHtml(t);
                _input.setAttribute("value", t);
            },
            selectionChanged: function(e){
                var range = document.getSelection();
                var n = range.anchorNode.parentNode;
                while(n && (n != _content.o)){
                    n = n.parentNode;
                }
                if (n){
                    q.updateSelection();
                } 
            },
            getSelection: function(){
                return _selectionRange;
            },
            updateSelection: function(){
                var range = document.getSelection();
                _selectionRange = {
                    range_count: range.rangeCount,
                    content: range.getRangeAt(0).cloneContents(),
                    anchorNode: range.anchorNode,
                    anchorOffset: range.anchorOffset,
                    focusNode : range.focusNode,
                    focusOffset : range.focusOffset,
                    isCollapsed : range.isCollapsed
                };
            },
            execCommand: function(cmdName){ 
                var p = _EDITOR.fullname+".commands."+cmdName;
                var v = igk.system.getNS(p);
                if (typeof(v)== 'function'){
                    v.apply(this, []);
                }
                else {
                    console.debug("command not found:"+p);
                }
                return !1;
            },
            init:function($options){
                // TODO : init options
            }
        });
        igk.defineProperty(this, "input", {get: function(){
            return _input;
        }});
        igk.defineProperty(this, "isOnDocument", {get: function(){
            return q.editor.o.parentNode != null;
        }});
        igk.defineProperty(this, "settings", {get: function(){
            return _settings;
        }});
        igk.defineProperty(this, "editor", {get: function(){
            return _editor;
        }});
        igk.defineProperty(this, "content", {get: function(){
            return _content;
        }});
    };
    /**
     * represent a plugins class
     */
    function _textEditorPlugin(){
        
    };

    igk.appendProperties(_EDITOR,{
        text: _textEditorClass,
        plugin: _textEditorPlugin
    });

    function __init_text_editor(){

        var _e = new _EDITOR.text(this.getAttribute("name"));
        var _data = JSON.parse(this.getAttribute("igk:editor-data"));
        if (_data){
            _e.init(_data);
        }
        _e.node = this;
        _e.parent = this.o.parentNode;  
        _e.form = this.select("^form").first();
        if (_e.form){
            _e.form.on("submit", function(e){
                _e.update_value(_e.content.o.innerHTML);
                if (_e.node.getAttribute("required")){
                    var v = _e.node.o.value+"";
                    if ((v.length==0) || (v=="<br>")){
                        _e.content.addClass("igk-danger");
                        e.preventDefault();
                    }else{
                        _e.content.rmClass("igk-danger");
                    }
                } 
            });
        }
        // set menu bar
        var _header = _e.editor.add('div').addClass("header").setCss(
            {
                "height":"32px", 
            }
        );
        var _ul = _header.add("ul");
        ["bold", "italic", "underline"].forEach((i)=>{
            _ul.add("li").addClass("editor_btn").setCss({
                "background":"url('"+_assets[i]+"')",
                "backgroundSize":"24px 24px",
                "backgroundPosition":"50%",
                "backgroundRepeat":"no-repeat",
                "display":"inline-block",
                "width":"32px",
                "height":"32px"
            }).on("click", function(e){
                _e.execCommand("style."+i);
                e.preventDefault();
                e.stopPropagation();
            });
        });


        ["alignLeft", "alignCenter", "alignRight", "justify"].forEach((i)=>{
            _ul.add("li").addClass("editor_btn").setCss({
                "background":"url('"+_assets[i]+"')",
                "backgroundSize":"24px 24px",
                "backgroundPosition":"50%",
                "backgroundRepeat":"no-repeat",
                "display":"inline-block",
                "width":"32px",
                "height":"32px"
            }).on("click", function(e){
                _e.execCommand("style."+i);
                e.preventDefault();
                e.stopPropagation();
            });
        });



        ["undo", "redo"].forEach((i)=>{
            _ul.add("li").addClass("editor_btn").setCss({
                "background":"url('"+_assets[i]+"')",
                "backgroundSize":"24px 24px",
                "backgroundPosition":"50%",
                "backgroundRepeat":"no-repeat",
                "display":"inline-block",
                "width":"32px",
                "height":"32px"
            }).on("click", function(e){
                _e.execCommand("action."+i);
                e.preventDefault();
                e.stopPropagation();
            });
        });

       
        // define content view
        var _content = _e.content;        
        _content.setHtml( _e.node.o.value); 
        _e.input.o.value = _e.node.o.value; 

        _e.editor.add('div').addClass("footer");
        _e.editor.addClass("igk-form-control editor");

        function select_changed(p){
            this.p = p;
            var q = this;
            igk.appendProperties(this, {
                selectionChanged: function(e){ 
                    if (!p.isOnDocument){
                        igk.winui.unreg_event(document, "selectionchange", q.select_changed);
                    }
                    if (e.target.activeElement == _content.o ){
                        p.selectionChanged.apply(p, [e]);
                    }
                }
            });
        };
        
        igk.winui.reg_event(document, "selectionchange", (new select_changed(_e)).selectionChanged);        
        _e.parent.replaceChild(_e.editor.o, _e.node.o); 
        _e.editor.insertAfter(_e.input.o); 
    }
    igk.winui.initClassControl("igk-text-editor", __init_text_editor);


})();


// + | styling command
(function(){
    function _command(cmd){
        return function(){
            var s = this.getSelection();  
            //restore selection
            if (s && !s.isCollapsed){
                document.getSelection().setBaseAndExtent(s.anchorNode, s.anchorOffset, s.focusNode, s.focusOffset);
                document.execCommand(cmd);
                this.updateSelection();
            }else {
                console.debug("failed : "+cmd, s);
            }
        };
    };
    igk.system.createNS("igk.editor.commands.style",{
        bold: _command("bold"), 
        italic: _command("italic"),
        underline: _command("underline"),
        alignCenter: _command("justifyCenter"),
        alignLeft: _command("justifyLeft"),
        alignRight: _command("justifyRight"),
        justify: _command("justifyFull"),
    });

    igk.system.createNS("igk.editor.commands.action",{
        undo: function(){
            this.content.o.focus();
            document.execCommand("undo");
        },
        redo: function(){
            this.content.o.focus();
            document.execCommand("redo");
        },
    });
})();