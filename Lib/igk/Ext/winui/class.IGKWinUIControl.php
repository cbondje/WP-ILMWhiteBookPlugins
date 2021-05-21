<?php
//UTILITY FUNCTION

//----------------------------------------------------------------
//represent a base winui control
//----------------------------------------------------------------
abstract class IGKWinUIControl extends IGKHtmlItem
{
	private $m_id;

	public function __construct($tagname)
	{
		$this->m_id = igk_new_id();
		parent::__construct($tagname);
	}
}
?>