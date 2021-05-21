<?php

namespace IGK\System\Html\Dom;


class PickerZoneHandler{
    var $info;
    var $data;
    private function __construct(){}
    ///<summary>get picker zone object from request</summary>
    /**
     * get picker zone object from request
     */
    public static function Request(){
        if (!igk_is_ajx_demand()){
            return null;
        }
        $finfo = (object)[
			"name"=>igk_server()->HTTP_IGK_FILE_NAME,
			"size"=> igk_server()->HTTP_IGK_UP_FILE_SIZE,				
			"filetype"=>igk_server()->HTTP_IGK_UP_FILE_TYPE,
		];
        $o = new PickerZoneHandler();
        $o->info = $finfo;
        $o->data =  igk_io_get_uploaded_data();
        return $o;
    }
}