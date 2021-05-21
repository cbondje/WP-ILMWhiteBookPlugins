<?php 

namespace IGK\Models;

use Exception;
use IGK\Models\ModelBase; 
use IGKNumber;

/** 
 */
class ReferenceModels extends ModelBase {
	/** 
	 */
	protected $table = "%prefix%reference_models"; 

	/**
	 * update object reference
	 */
	public static function updateRef($refobj){
		$refobj->clNextValue++;
		$u = $refobj->update; 
		$u();
		$u = $refobj->get_output;
		return $u();
	}
	/**
	 * get reference data
	 * @param mixed $ctrl 
	 * @param mixed|null $model 
	 * @param int $base 
	 * @param int $ref 
	 * @return object 
	 * @throws Exception 
	 */
	public static function get_ref($ctrl, $model=null, $base=36, $ref=6){
		$obj=(object)array(); 
        $obj->update=function() use ($obj){		         
            if($obj->newValue){  
				static::create((array)$obj);		 
            }
            else{ 
				$s = (array)$obj;
				unset($s["clId"]);
				static::update($s, ["clId"=>$obj->clId]); 
            }
        };
		$raw = null;
		$v_tmodel = $model == null ? (method_exists($ctrl, "getRefModel") ? $ctrl->getRefModel(): "MDL"): $model;

		$obj->get_output =  function()use( $obj, $v_tmodel, $base, $ref){
			$c= $obj->clNextValue;			
			return $v_tmodel."".IGKNumber::ToBase($c, $base, $ref);
		};
        $r= static::select_all(["clModel"=>$v_tmodel]);
        $raw= igk_getv($r, 0);
        $c = $raw? $raw->clNextValue: null;
        $c++;
        $out= $v_tmodel."".IGKNumber::ToBase($c, $base, $ref);
        igk_obj_append($obj, array(
            "out"=>$out,
            "newValue"=>count($r) == 0,
            "ctrl"=>$ctrl,
            "clModel"=>$v_tmodel,
            "clNextValue"=>$c,
            "clId"=>$raw ? $raw->clId: null
        ));
        return $obj;
	}
}
