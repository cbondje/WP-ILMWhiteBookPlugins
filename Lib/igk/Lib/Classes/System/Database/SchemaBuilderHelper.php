<?php
// @author: C.A.D BONDJE DOUE
// @file: SchemaBuilderHelper.php
// @desc: schema builder helper
// @date: 20210422 06:53:24
namespace IGK\System\DataBase;

use Exception;
use IGKDbColumnInfo;

class SchemaBuilderHelper{
    protected $_output;
    protected $_schema;

    protected function _addcolumnAttributes($attributes, $node=null){
        $node = $node ?? $this->_output;
        $c = new IGKDbColumnInfo($attributes);
        $m = $node->add("Column");
        foreach($c as $k=>$v){
            $m[$k] = $v;
        }  
    }

    public static function Migrate($options){
        if ($m = igk_getv($options, "migrations")){ 
            try{
                foreach($m as $t){
                    $t->upgrade(); 
                }
            }
            catch(Exception $ex){
                igk_dev_wln_e(__FILE__.":".__LINE__."some error", $ex->getMessage());
            }
        }
        return false;
    }
    public static function Downgrade($options){
        if ($m = igk_getv($options, "migrations")){ 
            try{
                $m = array_reverse($m);
                foreach($m as $t){
                    $t->downgrade(); 
                }
                return true;
            }
            catch(Exception $ex){
                igk_dev_wln_e(__FILE__.":".__LINE__.":some error", $ex->getMessage());
            }
        }
        return false;
    }
}