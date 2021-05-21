"use strict";

(function(){

    var _validator = igk.system.createNS("igk.form.validator",{
        stringIsEmpty: function(v){
            if (typeof(v)=="string")
                return v.length == 0;
            return !1;
        }  
    }); 


var $js = $igk(igk.getCurrentScript());
var q = $js.select("^form").first();
q.qselect("label.clrequired + input").each_all(function(){
    // console.debug("require fields");
    this.setAttribute("require", 1);
});
q.on("submit", function(evt){
    var e = 0;
    var provider = {
        msg:[]
    };
    $igk(q.o.clLastName).rmClass("igk-danger");
    $igk(q.o.clPwd).rmClass("igk-danger");
    $igk(q.o.clRePwd).rmClass("igk-danger");

    if (_validator.stringIsEmpty(q.o.clLastName.value)){
        e =1;
        $igk(q.o.clLastName).addClass("igk-danger");
        provider.msg.push("last name is empty"); 
    }
    if (_validator.stringIsEmpty(q.o.clLogin.value)){
        e =1;
        $igk(q.o.clLogin).addClass("igk-danger");
        provider.msg.push("login is empty"); 
    }
    if (_validator.stringIsEmpty(q.o.clPwd.value)){
        $igk(q.o.clPwd).addClass("igk-danger");
        provider.msg.push("password is empty"); 
        e = 1;
    } else { 
        if (q.o.clPwd.value != q.o.clRePwd.value){
            $igk(q.o.clPwd).addClass("igk-danger");
            provider.msg.push("password mismatch"); 
            e = 1;
        }
    }
    if (e){ 
        evt.preventDefault(); 
    }
});
})();