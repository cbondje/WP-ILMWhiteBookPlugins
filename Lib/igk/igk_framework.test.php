<?php
// author: C.A.D. BONDJE DOUE
// licence: IGKDEV - Balafon @ 2019
// desc: testing file

igk_utest_test(["title"=>"igk_view_handle_actions array var",
        "expected"=>"ok",
        "callback"=>function(){
            if(igk_view_handle_actions("testing", array("test"=>function(){
                    return "ok";
                }), ["test"], 0))
        return "ok";
    return "failed";
}
    ]);