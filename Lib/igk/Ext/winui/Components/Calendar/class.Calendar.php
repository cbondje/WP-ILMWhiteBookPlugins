<?php

//
//Name: calendar controller
//version: 1.0
//

// final class IGKHtmlCalendarItem extends IGKHtmlItem
// {
	// public function __construct(){
		// parent::__construct("div");
	// }
// }

final class IGKCalendarHtmlItemCtrl extends IGKNonVisibleControllerBase
{
	public function InitComplete(){
		parent::InitComplete();
        $f =dirname(__FILE__)."/Styles/default.pcss";
        if (file_exists($f))
		    include_once($f);

	}
}
?>