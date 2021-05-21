<?php

class IGKHtmlFormSelectGenderItem extends IGKHtmlItem{
	public function __construct(){
		parent::__construct("select");
		$this->add("option")->setAttribute("value","m")->Content = R::gets("enum.Male");
		$this->add("option")->setAttribute("value","f")->Content = R::gets("enum.Female");
	}
}
?>