<?php


///<summary>Represente class: IGKDbColumnInfo</summary>
/**
* Represente IGKDbColumnInfo class
*/
final class IGKDbColumnInfo extends IGKObject {
    /**
     * set if the column is auto increment
     * @var mixed
     */
    var $clAutoIncrement;
    /**
     * default increment start index
     * @var mixed
     */
	var $clAutoIncrementStartIndex;
    /**
     * column member index
     * @var mixed
     */
    var $clColumnMemberIndex;
    /**
     * set the default value
     * @var int
     */
    var $clDefault;
    /**
     * set the column description
     * @var mixed
     */
    var $clDescription;
    var $clFormRefKey;
    /**
     * form input type
     * @var string
     */
    var $clInputType;
    /**
     * string comma separated values of available enum values if type is Enum
     * @var mixed
     */
    var $clEnumValues;
    var $clInsertFunction;
    /**
     * set if the column is index
     * @var mixed
     */
    var $clIsIndex;
    var $clIsNotInQueryInsert;
    /**
     * set if this column is primary key
     * @var mixed
     */
    var $clIsPrimary;
    /**
     * set if column is unique
     * @var mixed
     */
    var $clIsUnique;
    /**
     * unique column member list
     * @var mixed
     */
    var $clIsUniqueColumnMember;
    /**
     * link column name
     * @var mixed
     */
    var $clLinkColumn; 
    /**
     * the link table default display
     * @var mixed
     */
    var $clLinkTableDisplay;
    /**
     * link to table for foreing key
     * @var mixed
     */
    var $clLinkType;
    /**
     * the name of the column
     * @var mixed
     */
    var $clName;
    /**
     * define if the column require a value. default is false
     * @var false
     */
    var $clNotNull;
    /**
     * pattern to validate the column value
     * @var mixed
     */
    var $clPattern;
    /**
     * type of the column
     * @var string
     */
    var $clType;
    /**
     * column type length
     * @var null
     */
    var $clTypeLength;
    /**
     * function to call on update
     * @var mixed
     */
    var $clUpdateFunction;
    /**
     * check constraint expression
     * @var mixed
     */
    var $clCheckConstraint; 

    /**
     * the link expression for default value
     */
    var $clDefaultLinkExpression;

    /**
     * this column is require
     * @var clRequire
     */
    var $clRequire;
    ///<summary></summary>
    ///<param name="array" default="null"></param>
    /**
    * 
    * @param mixed $array the default value is null
    */
    public function __construct($array=null){
        $this->clType="Int";
        $this->clTypeLength=11;
        $this->clNotNull=false; 
        
        if(is_array($array)){
            $t=get_class_vars(get_class($this));
            foreach($array as $k=>$v){
                if(!array_key_exists($k, $t)){
                    continue;
                }
                if(preg_match("/^(false|true)$/i", $v)){
                    $v=igk_getbool($v);
                }
                $this->$k=$v;
            }
        }
        if(!igk_db_is_typelength($this->clType))
            $this->clTypeLength=null;
        if(!$this->clNotNull && empty($this->clDefault) && preg_match("/(int|float)/i", $this->clType)){
            $this->clDefault=0;
        }
        if ($this->clDefault && $this->clLinkType){
            // detect link expression
            if (preg_match("/(.)+\.(.)+/", $this->clDefault)){
                $this->clDefaultLinkExpression = $this->clDefault;
            }
            $this->clDefault = null;
        }         
    }
    public static function CreateWithRelation($attribs, $tb, $ctrl, & $tbrelation=null){
        $cl = new IGKDbColumnInfo(igk_to_array($attribs));
        if (!empty($cl->clLinkType)){
            $cl->clLinkType = IGKSysUtil::GetTableName( $cl->clLinkType, $ctrl );
        }
        if(($tbrelation !== null) && !empty($cl->clLinkType)){
            if(!isset($tbrelation[$tb]))
                $tbrelation[$tb]=array();
            $tbrelation[$tb][$cl->clName]=array("Column"=>$cl->clName, "Table"=>$cl->clLinkType);
        }
        return $cl;
    }
    ///<summary></summary>
    ///<param name="key"></param>
    /**
    * 
    * @param mixed $key
    */
    public function __get($key){
        $d=get_class_vars(get_class($this));
        if(array_key_exists($key, $d)){
            return $this->$key;
        }
        igk_die("__get Not implements : ".$key. " ".get_class($this));
    }
    ///<summary></summary>
    ///<param name="key"></param>
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $key
    * @param mixed $value
    */
    public function __set($key, $value){
        igk_die("variable : [". $key. "] Not Implements");
    }
    ///<summary>display value</summary>
    /**
    * display value
    */
    public function __toString(){
        return "IGKDbColumnInfo[".$this->clName."]";
    }
    ///get association info array
    /**
    */
    public static function AssocInfo($array, $tablename=null){
        if(!is_array($array))
            igk_die("array is not an array");
        $t=array();
        foreach($array as $k=>$v){
            if(is_object($v)){
                if($k !== $v->clName){
                    $t[$v->clName]=$v;
                }
                else{
                    $t[$k]=$v;
                }
            }
            else{
                igk_debug_wln("v is not an object : ".igk_count($array));
            }
        }
        return $t;
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function GetColumnInfo(){
        return get_class_vars("IGKDbColumnInfo");
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function NewEntryInfo(){
        return new IGKDbColumnInfo(array(
            IGK_FD_NAME=>IGK_FD_ID,
            IGK_FD_TYPE=>"Int",
            "clAutoIncrement"=>true
        ));
    }
    ///<summary>get if this is unsigned type</summary>
    /**
     * get if this is unsigned type
     * @return int|false 
     */
    public function IsUnsigned(){
        return preg_match("/u((big)?int)/i", $this->clType);
    }
}