<?php

final class IGKTestTools extends IGKToolCtrlBase
{

	public function getImageUri(){
		$uri = igk_html_resolv_img_uri(igk_io_baseDir("Lib/igk/Default/R/Img/pics_48x48/tool_sitemapgen.png"));
		return $uri;
	}
	public function DoAction(){
	}
}


igk_tool_reg("testing",array("ImageUri"=>"", "Action"=>function(){

}));
?>