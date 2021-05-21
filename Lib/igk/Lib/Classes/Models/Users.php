<?php 
namespace IGK\Models;

use IGK\Helper\Utility;
use IGKUsersController;

/** 
 */
class Users extends ModelBase {
	/** 
	 */
	protected $table = "%prefix%users";

	protected $controller = IGKUsersController::class;

	protected $display = "clLogin";

	protected $fillable = ["clLogin", "clPwd", "clFirstName", "clLastName"];

	protected $form_fields = ["clLogin", "clPwd", "clFirstName", "clLastName"];


	
	public function fullname(){
		return Utility::GetFullName($this);
	}
	public function mail_display(){
		return implode(" ",array_filter([
			strtoupper($this->clLastName), ucfirst($this->clFirstName),
			"&lt;".$this->clLogin."&gt;"]));
	}
}
