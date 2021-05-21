<?php

namespace ILYEUM\database;

use stdClass;
use function ilm_getv as getv;
use function ilm_die as fdie;
use function ilm_getev as getev;
use function ilm_db_create_options  as db_create_options;
use function ilm_db_get_table_info  as get_db_table_info;
use function ilm_get_robjs as get_robjs;
use ILYEUM\database\OptionConstants as queryConstant;
use function ilm_wln as _wln;

/**
 * represent MySQL Grammar helper
 * @package ILYEUM\database
 */
class Grammar{

    private $m_driver;
    const FD_ID = "clId";

    public const CALLBACK_OPTS = "@callback";
    const AVAIL_FUNC=[
        'IGK_PASSWD_ENCRYPT',
        'AES_ENCRYPT', 
        'BIN', 'CHAR', 'COMPRESS', 'CURRENT_USER', 'AES_DECRYPTDATABASE', 
        'DAYNAME', 'DES_DECRYPT', 'DES_ENCRYPT', 'ENCRYPT', 'HEX', 'INET6_NTOA', 
        'INET_NTOA', 'LOAD_FILE', 'LOWER', 'LTRIM', 'MD5', 'MONTHNAME', 'OLD_PASSWORD', 
        'PASSWORD', 'QUOTE', 'REVERSE', 'RTRIM', 'SHA1', 'SOUNDEX', 'SPACE', 'TRIM', 
        'UNCOMPRESS', 'UNHEX', 'UPPER', 'USER', 'UUID', 'VERSION', 'ABS', 'ACOS', 'ASCII', 
        'ASIN', 'ATAN', 
        'BIT_COUNT', 'BIT_LENGTH', 'CEILING', 'CHAR_LENGTH', 'CONNECTION_ID', 'COS', 'COT', 
        'CRC32', 'CURRENT_DATE', 'CURRENT_TIME', 'DATE', 'DAYOFMONTH', 'DAYOFWEEK', 'DAYOFYEAR', 'DEGREES', 'EXP', 
        'FLOOR', 'FROM_DAYS', 'FROM_UNIXTIME', 'HOUR', 'INET6_ATON', 'INET_ATON', 'LAST_DAY', 
        'LENGTH', 'LN', 'LOG', 'LOG10', 'LOG2', 'MICROSECOND', 'MINUTE', 'MONTH', 'NOW', 'OCT', 'ORD', 'PI', 
        'QUARTER', 'RADIANS', 'RAND', 'ROUND', 'SECOND', 'SEC_TO_TIME', 'SIGN', 'SIN', 'SQRT', 'SYSDATE', 'TAN', 'TIME', 'TIMESTAMP', 'TIME_TO_SEC', 'TO_DAYS', 'TO_SECONDS', 
        'UNCOMPRESSED_LENGTH', 'UNIX_TIMESTAMP', 'UTC_DATE', 'UTC_TIME', 'UTC_TIMESTAMP', 'UUID_SHORT', 'WEEK', 'WEEKDAY', 'WEEKOFYEAR', 'YEAR', 'YEARWEEK'];

    
    protected static $LENGTHDATA=array("int"=>"Int", "varchar"=>"VarChar");

    public function __construct($driver){
        $this->m_driver = $driver;
    }
    protected static function AllowedDefValue(){
        static $defvalue = null;
		if ($defvalue === null){
			$defvalue = [
				"TIMESTAMP"=>[
                    "CURRENT_TIMESTAMP"=>1,
                    "NOW()"=>"CURRENT_TIMESTAMP",
                    "NULL"=>1
                ],
				"DATETIME"=>[
					"CURRENT_TIMESTAMP"=>1,
					"NOW()"=>1,
					"CURDATE"=>1,
					"CURTIME()"=>1,
                    "NULL"=>1
				],
                "JSON"=>[
                    "{}"=>"(JSON_OBJECT())",
                    "[]"=>"((JSON_ARRAY())"
                ]  
			];
		}
        return $defvalue;
    }
    ///<summary>check if this type support defaut value</summary>
    protected static function supportDefaultValue($type){
        return !in_array($type, ["text"]);
    }
    public static function ResolvType($t){
        return getv([
            "int"=>"Int",
            "uint"=>"Int",
            "udouble"=>"Double",
            "bigint"=>"BIGINT",
            "ubigint"=>"BIGINT",
            "date"=>"Date",
            "enum"=>"Enum",
            "json"=>"JSON"
        ], $t = strtolower($t), $t);    
    }
    public static function fallbackType($t, $adapter){
        switch(strtolower($t)){
            case "json":
                if ($adapter->isTypeSupported('longtext')){
                    return "longtext";
                }
                break;
            case "date":
                if ($adapter->isTypeSupported('datetime')){
                    return "datetime";
                }
        }  
        return "text";
    }
    public function createTablequery($tbname, $columninfo, $desc=null, $noengine=0, $nocomment=0){
        $adapter = $this->m_driver;
   
        $query ="CREATE TABLE IF NOT EXISTS `".$adapter->escape_string($tbname)."`(";
        $tb=false;
        $primary="";
        $unique="";
        $funique="";
        $findex="";
		$fautoindex = "";
        $uniques=array();
        $primkey="";
        $tinf=array();
        $defvalue = static::AllowedDefValue();
		
        foreach($columninfo as $v){
            if(($v == null) || !is_object($v)){
                fdie(__CLASS__." :::Error table column info is not an object error for ".$tbname);
            }
            $primkey="noprimkey://".$v->clName;
            if($tb)
                $query .= ",";
            $v_name=$this->m_driver->escape_string($v->clName);
            $query .= "`".$v_name."` ";
            $type= getev(static::ResolvType($v->clType), "Int");
           
            if ($adapter && !$adapter->isTypeSupported($type)){
                $type = static::fallbackType($type, $adapter);
            } 
            $query .= $this->m_driver->escape_string($type);
            $s=strtolower($type);
            $number=false;
            if(isset(static::$LENGTHDATA[$s])){
                if($v->clTypeLength > 0){
                    $number=true;
                    $query .= "(".$this->m_driver->escape_string($v->clTypeLength).")";                   
                }
            } else if ($type=="Enum"){
                $query .="(". implode(",", array_map(function($i){
                    return "'".$this->m_driver->escape_string($i)."'";
                }, array_filter(explode(",", $v->clEnumValues), function($c){
                    return (strlen(trim($c))>0);
                }))).")";
            }
            $query .= " ";          


            if (static::IsUnsigned($v)){
                $query .= "unsigned ";
            }

            if (!empty($v->clLinkType)){
                $this->m_driver->pushRelations($tbname, $v); 
            }
            
            if(!$number){
                if(($v->clNotNull) || ($v->clAutoIncrement))
                    $query .= "NOT NULL ";
                else
                    $query .= "NULL ";
            }
            else if($v->clNotNull){
                $query .= "NOT NULL ";
            }
            if($v->clAutoIncrement){
                $query .= $this->m_driver->GetValue("auto_increment_word", $v, $tinf)." ";
				if ($idx = getv($v,"clAutoIncrementStartIndex")){
					$fautoindex = $this->m_driver->GetValue("auto_increment_word", $v, $tinf)."={$idx} ";
				}
            }
            $tb=true;
            if( static::supportDefaultValue($type) &&  $v->clDefault || $v->clDefault === '0'){
				$_ktype = strtoupper($type);
				$_def = $r_v = isset($defvalue[$_ktype][$v->clDefault]) ?
                (is_int($defvalue[$_ktype][$v->clDefault])?
                $v->clDefault : $defvalue[$_ktype][$v->clDefault] ):
				"'".$this->m_driver->escape_string($v->clDefault)."'";
                $query .= "DEFAULT {$_def} ";

                if ($r_v && $v->clUpdateFunction){
                    $_def = !isset($defvalue[$_ktype][$v->clUpdateFunction]) ? $v->clDefault:
                    "".$this->m_driver->escape_string($v->clUpdateFunction).""; 
                    $query .= " ON UPDATE {$_def}";
                }
            }
 

            if($v->clDescription && !$nocomment){
                $query .= " COMMENT '".$this->m_driver->escape_string($v->clDescription)."' ";
            }
            if($v->clIsUnique){
                if(!empty($unique))
                    $unique .= ",";
                $unique .= "UNIQUE KEY `".$v_name."` (`".$v_name."`)";
            }
            if($v->clIsUniqueColumnMember){
                if(isset($v->clColumnMemberIndex)){
                    $tindex=explode("-", $v->clColumnMemberIndex);
                    $indexes=array();
                    foreach($tindex as $kindex){
                        if(!is_numeric($kindex) || isset($indexes[$kindex]))
                            continue;
                        $indexes[$kindex]=1;
                        $ck='unique_'. $kindex;
                        $bf="";
                        if(!isset($uniques[$ck])){
                            $bf .= "UNIQUE KEY `clUC_".$ck."_index`(`".$v_name."`";
                        }
                        else{
                            $bf=$uniques[$ck];
                            $bf .= ", `".$v_name."`";
                        }
                        $uniques[$ck]=$bf;
                    }
                }
                else{
                    if(empty($funique)){
                        $funique="UNIQUE KEY `clUnique_Column_".$v_name."_index`(`".$v_name."`";
                    }
                    else
                        $funique .= ", `".$v_name."`";
                }
            }
            if($v->clIsPrimary && !isset($tinf[$primkey])){
                if(!empty($primary))
                    $primary .= ",";
                $primary .= "`".$v_name."`";
            }
            if(($v->clIsIndex || $v->clLinkType) && !$v->clIsUnique && !$v->clIsUniqueColumnMember && $v->clIsPrimary){
                if(!empty($findex))
                    $findex .= ",";
                $findex .= "KEY `".$v_name."_index` (`".$v_name."`)";
            }
            unset($tinf[$primkey]);
        }
        if(!empty($primary)){
            $query .= ", PRIMARY KEY  (".$primary.") ";
        }
        if(!empty($unique)){
            $query .= ", ".$unique." ";
        }
        if(!empty($funique)){
            $funique .= ")";
            $query .= ", ".$funique." ";
        }
        if(count($uniques) > 0){
            foreach($uniques as $v){
                $v .= ")";
                $query .= ", ".$v." ";
            }
        }
        if(!empty($findex))
            $query .= ", ".$findex;

        $query .= ")";
        if(!$noengine)
            $query .= ' ENGINE=InnoDB ';
		if (!empty($fautoindex)){
			$query .= " ".	$fautoindex." ";
		}
        if($desc){
            $query .= "COMMENT='".$this->m_driver->escape_string($desc)."' ";
        }
        $query = rtrim($query).";";
        return $query; 
    }
    public function add_foreign_key($table, $v, $nk=null, $db=null){   
        $db = $db ?? $this->m_driver->getDbName();
        if (!empty($nk)){
            $nk = "CONSTRAINT '".$nk."'";
        }else {
            $nk= "";
        }
        $query = sprintf("ALTER TABLE %s ADD %s FOREIGN KEY (%s) REFERENCES %s  ON DELETE RESTRICT ON UPDATE RESTRICT;\n",
            "`{$db}`.`{$table}`", 
            $nk,
            $v->clName, sprintf("`%s`.`%s`(`%s`)",
            $db,
            $v->clLinkType,
            getv($v, "clLinkColumn", self::FD_ID)
        ));
        return $query;
    }
    public function add_column($table, $info, $after=null){
        $q = "ALTER TABLE ";
        $q .= "`".$table."` ADD ";
        $q .= $info->clName ." ";

        $q .= rtrim($this->getColumnInfo($info));

        if (!empty($after)){
            $q.= " AFTER `".$after."`";
        }
        return $q;
    }
    public function rm_column($table, $info, $after=null){
        $name= is_object($info)? getv($info, "clName") : $info;
        $adapter  = $this->m_driver;
        $q = "ALTER TABLE ";
        $q .= "`".$table."` DROP ";
        $q .= $adapter->escape($name); 
        return $q;
    }
    public function rename_column($table, $column, $new_name){
        $adapter  = $this->m_driver;
        $q = "ALTER TABLE ";
        $q .= "`".$table."` RENAME COLUMN ";
        $q .= $adapter->escape($column) ." TO ".$adapter->escape($new_name); 
        return $q;
    }

    public function change_column($table, $info){
        $column = $info->clName;
        $adapter  = $this->m_driver;
        $q = "ALTER TABLE ";
        $q .= "`".$table."` CHANGE ";
        $q .= $adapter->escape($column) ." ".$adapter->escape($column) ." ".rtrim($this->getColumnInfo($info));
        return $q;
    }
    /**
     * return exists if a column exists
     * @param mixed $table 
     * @param mixed $column 
     * @return bool
     */
    public function exist_column($table, $column, $db=null){
        $adapter  = $this->m_driver;
        $db= $db ?? $adapter->getDbName();
        $r = $adapter->sendQuery($q = "SELECT * FROM information_schema.COLUMNS ".      
        "where TABLE_NAME='$table' and TABLE_SCHEMA='$db' AND COLUMN_NAME='$column'");
        $row = null;
        if ($r){
            if ($r->ResultTypeIsBoolean()){
                return $r->value;  
            }
            $row = $r->getRowAtIndex(0);
        }
        return $row !=null;
    }
    public function remove_foreign($table, $info, $db=null){
        $adapter  = $this->m_driver;
        $db= $db ?? $adapter->getDbName();
        $r = $adapter->sendQuery("SELECT * FROM information_schema.TABLE_CONSTRAINTS LEFT JOIN information_schema.INNODB_FOREIGN_COLS on(".
        "CONCAT(CONSTRAINT_SCHEMA,'/',CONSTRAINT_NAME)=ID".
        ") ". 
        "where TABLE_NAME='$table' and CONSTRAINT_SCHEMA='$db' AND FOR_COL_NAME='$info'");

        $columns = [];
        foreach($r->getRows() as $c){
            $columns[$c->CONSTRAINT_SCHEMA."/".$c->CONSTRAINT_NAME] = $c->CONSTRAINT_NAME;
        }
        if ($ck = getv(array_values($columns), 0)){
            $q  = "ALTER TABLE ";
            $q .= "`".$table."` DROP FOREIGN KEY ";
            $q .= $adapter->escape($ck) ." "; 
            return $q;
        }
        return null;
    }
    protected function getColumnInfo($v, $nocomment=false){
        $adapter  = $this->m_driver;
        $defvalue=  static::AllowedDefValue();
        $query = "";
        $tinf = null;
        
        $type= getev(static::ResolvType($v->clType), "Int");
        if (!$adapter->isTypeSupported($type)){
            $type = static::fallbackType($type, $adapter);
        } 
        $query .= $adapter->escape_string($type);
        $s=strtolower($type);
        $number=false;
        if(isset(static::$LENGTHDATA[$s])){
            if($v->clTypeLength > 0){
                $number=true;
                $query .= "(".$adapter->escape_string($v->clTypeLength).")";                   
            }
        } else if ($type=="Enum"){
            $query .="(". implode(",", array_map(function($i){
                return "'".$this->m_driver->escape_string($i)."'";
            }, array_filter(explode(",", $v->clEnumValues), function($c){
                return (strlen(trim($c))>0);
            }))).")";
        }
        $query .= " ";

        if ($v->IsUnsigned()){
            $query .= "unsigned ";
        }

        if(!$number){
            if(($v->clNotNull) || ($v->clAutoIncrement))
                $query .= "NOT NULL ";
            else
                $query .= "NULL ";
        }
        else if($v->clNotNull){
            $query .= "NOT NULL ";
        }
        if($v->clAutoIncrement){
            $query .= $this->m_driver->GetValue("auto_increment_word", $v, $tinf)." ";
            if ($idx=getv($v,"clAutoIncrementStartIndex")){
                $fautoindex = $this->m_driver->GetValue("auto_increment_word", $v, $tinf)."={$idx} ";
            }
        }
        $tb=true;
        if($v->clDefault || $v->clDefault === '0'){
            $_ktype = strtoupper($type);
            $_def = $r_v = isset($defvalue[$_ktype][$v->clDefault]) ?
            (is_int($defvalue[$_ktype][$v->clDefault])?
            $v->clDefault : $defvalue[$_ktype][$v->clDefault] ):
            "'".$adapter->escape_string($v->clDefault)."'";
            $query .= "DEFAULT {$_def} ";

            if ($r_v && $v->clUpdateFunction){
                $_def = isset($defvalue[$_ktype][$v->clUpdateFunction]) ? $v->clDefault:
                "".$adapter->escape_string($v->clUpdateFunction)." ";            
                $query .= " ON UPDATE {$_def}";
            }
        }
        if($v->clDescription && !$nocomment){
            $query .= " COMMENT '".$adapter->escape_string($v->clDescription)."' ";
        }
        return $query;
    }

    public function createInsertQuery($tbname, $values, $tableInfo=null){
        $rtbname= $this->m_driver->escape_string($tbname);
        $query="INSERT INTO `".$rtbname."`(";
        $v_v="";
        $v_c=0; 
     
        $tvalues= static::GetValues($this->m_driver, $values, $tableInfo);
        foreach($tvalues as $k=>$v){
            if($v_c != 0){
                $query .= ",";
                $v_v .= ",";
            }
            else
                $v_c=1;
            $query .= "`".$this->m_driver->escape_string($k)."`";
            if($tableInfo){
                // | get value 
                $v_v .= static::GetValue($this->m_driver, $rtbname, $tableInfo, $k, $v);
            }
            else{
                if($v === null){
                    $v_v .= "NULL ";
                }
                else if(is_object($v) && method_exists($v, "getValue")){
                    $v_v .= "".$v->getValue();
                }
                else
                    $v_v .= "'".$this->m_driver->escape_string($v)."'";
            }

        }
        $query .= ") VALUES (".$v_v.");"; 

        return $query;
    }
    public function createUpdateQuery($tbname, $values, $condition=null, $tableInfo=null){
        $driver = $this->m_driver;
        $rtbname= $driver->escape_string($tbname);
        $out="";
        $out .= "UPDATE `".$rtbname."` SET ";
        $t=0;
        $v_condstr = "";
        $id=$condition == null ? getv($values, self::FD_ID): null;

        if(($id == null) && is_integer($condition)){
            $id=$condition;
        }
        $tableInfo= $tableInfo ?? get_db_table_info($tbname);
        $tvalues= static::GetValues($this->m_driver, $values, $tableInfo, 1);
        
        foreach($tvalues as $k=>$v){
            if($id && ($k == self::FD_ID) || (strpos($k, ":") !== false))
                continue;
            if($t == 1)
                $out .= ",";
            if($tableInfo){
                $out .= "`".$driver->escape_string($k)."`=".self::GetValue($this->m_driver, $rtbname, $tableInfo, $k, $v, "u");
            }
            else{
                $out .= "`".$driver->escape_string($k)."`=";
                if(!empty($v) && is_integer($v)){
                    $out .= $v;
                }
                else
                    $out .= "'".$driver->escape_string($v). "'";
            }
            $t=1;
        }


        if($condition){
            if(is_array($condition)){
                $v_condstr .= static::GetCondString($this->m_driver, $condition);
            }
            else if(is_string($condition) && !preg_match("/^[0-9]+$/i", $condition))
                $v_condstr .=$condition;
            else if(is_integer($condition) || preg_match("/^[0-9]+$/i", $condition))
                $v_condstr .= "`clId`='".$driver->escape_string($condition)."'";
            else{
                _wln("data is ".$condition. " ".strlen($condition). " ::".is_integer((int)$condition));
            }
        }
        else if($id){
            $v_condstr .= "`clId`='".$driver->escape_string($id)."'";
        }
        if (!empty($v_condstr)){
            $out.= " WHERE ".$v_condstr;
        }

        return $out;
    }
    public function createDeleteQuery($tbname, $condition=null){
        $c = "";
        if ($condition && ($c = static::GetCondString($this->m_driver, $condition))){
            $c = " WHERE ".$c;
        }
        return "DELETE FROM `".$this->m_driver->escape_string($tbname)."`".$c.";";        
    }

    public static function IsUnsigned($v){
        if (method_exists($v, "IsUnsigned")){
            return $v->IsUnsigned();
        }
        if (in_array(strtolower($v->clType) , explode("|", "ubigint"))){        
            return true;
        }
        return false;
    }

     ///<summary></summary>
    ///<param name="tbname"></param>
    ///<param name="tableInfo"></param>
    ///<param name="columnName"></param>
    ///<param name="value"></param>
    ///<param name="type" default="i"></param>
    /**
    * 
    * @param mixed $tbname
    * @param mixed $tableInfo
    * @param mixed $columnName
    * @param mixed $value
    * @param mixed $type the default value is "i"
    */
    public static function GetValue($driver, $tbname, $tableInfo, $columnName, $value, $type="i"){
        $tinf= getv($tableInfo, $columnName);        
        $def = static::AllowedDefValue();

        if($tinf === null){  
            igk_trace();          
            fdie("can't get column: [{$columnName}]'info in table: {$tbname}");
        }  
        if (!empty($tinf->clLinkType) && is_string($value) && (strpos($value,".")!==false)){            
            if ($v = $driver->GetExpressQuery($value, $tinf)){
                return $v;
            }
        }
      
        
        if((is_integer($value))){
            if (($value === 0) && !empty($tinf->clLinkType)&& !$tinf->clNotNull){                 
                return 'NULL'; 
            }
            if (($value === 0) && !empty($tinf->clLinkType) && $tinf->clNotNull){
                // select default link expression
                if ($express = $tinf->clDefaultLinkExpression){
                    if ($v = $driver->GetExpressQuery($express, $tinf)){
                        return $v;
                    }                  
                }
            }            
            if ($tinf->clType =="Enum"){
                return "'".$driver->escape_string($value)."'";
            }
            return $value;
        }
        $of = 'NULL';
        if(($type == "i") && $tinf->clInsertFunction){
            $of=$tinf->clInsertFunction;
        }
        else if(($type != "i") && $tinf->clUpdateFunction){
            $of=$tinf->clUpdateFunction;
        }
        
        if(($value === null) || ($value == $tinf->clDefault) || (($value !== '0') && empty($value))){
            if($tinf->clNotNull){
              
                if($tinf->clDefault !== null){
                    if (is_integer($tinf->clDefault)){
                        return $tinf->clDefault;
                    }else { 
                        if(static::IsAllowedDefValue($def,$tinf->clType, $tinf->clDefault)){
                            return $tinf->clDefault;
                        }
                       
                        return "'".$driver->escape_string($tinf->clDefault)."'";
                    }
                }
                
                switch(strtolower($tinf->clType)){
                    case 'int':
                    case 'integer':
                    case 'float':
                    case 'double':
                    if(!$tinf->clNotNull){
                        return 'NULL';
                    }
                    return "0";
                    case "datetime":
                    case "date":
                    case "time":
                    return "NOW()";
					default: 
                    if (is_string($value)){
                         return "''";
                    }                  
                    return sprintf($of, $value);
                }
            }
            // + allow null value
            

            if(preg_match("/(date(time)?|timespan)/i", $tinf->clType)){                
                if(strtolower($of) == 'now()'){
                    switch(strtolower($tinf->clType)){
                        case "datetime":
                        case "timespan":
                        return "'".$driver->escape_string(date("Y-m-d H:i:s"))."'";
                        case "date":
                        return "'".$driver->escape_string(date("Y-m-d"))."'";
                        case "time":
                        return "'".$driver->escape_string(date("H:i:s"))."'";
                    }
                }
                if ($value === 'NULL'){
                    $value = null;
                }
                if($tinf->clDefault && static::IsAllowedDefValue($def,$tinf->clType, $tinf->clDefault)){
                    return $tinf->clDefault;
                }
            }
            
           
            if ($of!='NULL'){
                $gt=explode("(", $of);
                $pos=strtoupper(array_shift($gt));
                if(!$tinf->clNotNull){
                    if(in_array($pos, static::AVAIL_FUNC)){                    
                        return sprintf($of, $value);
                    } 
                }
            }
            if ($value && ($value == $tinf->clDefault)){
                return "'".$driver->escape_string($value)."'";
            }
            return 'NULL';
        }
        
        if(empty($value)){
            if(!$tinf->clNotNull || ($tinf->clAutoIncrement && strtolower($tinf->clType) == 'int'))
                return 'NULL';
        }
        if(is_object($value)){
            if ($s = $driver->getObjValue($value)){
                return $s;
            }           
        }
        if($tinf){
            $of=$type == "i" ? $tinf->clInsertFunction: $tinf->clUpdateFunction;
            if(!preg_match("/date(time)?/i", $tinf->clType) && !empty($of)){
                $gt=explode("(", $of);
                $pos=strtoupper(array_shift($gt)); 
                if (!empty($s = $driver->getFuncValue($pos, $value))){
                    return $s;
                }
                return strtoupper($pos)."('".$driver->escape_string($value)."')";
            }
        }       
        return "'".$driver->escape_string($value)."'";
    }

    protected static function IsAllowedDefValue($def, $type, $value){
        if ($b = getv($def, strtoupper($type))){
            if (isset($b[strtoupper($value)])){
                return true;
            }
        }  
        return false;
    }

    protected static function GetValues($driver, $values, $tableInfo, $update=0){
        $tvalues= new stdClass(); 

		if (is_object($values) && method_exists($values,"toArray")){
			$values = $values->toArray();
		}
        if(is_array($values))
            $values=(object)$values;
        if($tableInfo){
            $filter = $driver->filter; 
            foreach($tableInfo as $k=>$v){
                if (empty($k)){
                    die("key is null or empty");
                }
                if ($v->clIsPrimary && $filter){
                    continue;
                }

                if(!property_exists($values, $k)){
                    if ($update){
                        if ($v->clLinkType||
                            !$v->clUpdateFunction||
                            !preg_match("/(date|datetime)/i", $v->clType)){
                                continue;
                        }
                    } 
                    $tvalues->$k=null;
                }
                else{
                    $tvalues->$k=$values->{$k};
                }
            }
        }
        else{
            $tvalues=$values;
        }
        return $tvalues;
    }

    public function createSelectQuery($tbname, $where=null, $options=null){
        $q="";
        $ad = $this->m_driver;
        if($options == null){
            $options= db_create_options();
        }
        else if(is_callable($options)){
            $g= db_create_options();
            $c= self::CALLBACK_OPTS;
            $g->$c=$options;
            $options=$g;
        }
        if($where != null){
			if (!is_numeric($where) && is_string($where)){
				$q .= " WHERE ".$where;
			}else{
				$operand= getv($options, "Operand", "AND");
				$q .= " WHERE ".static::GetCondString($this->m_driver, $where, $operand, $ad);
			}
        }
        $tq = static::GetExtraOptions($options, $this->m_driver);
        $column = $tq->columns;
        if (!empty($tq->join)){
            $q = " ".$tq->join . " ".$q;
        }
        if (isset($tq->extra)){
            $q .=" ".$tq->extra;
        }
        $flag = "";
        if ($ad->querydebug){
            $flag = getv($tq, "flag"); 
        }
        $q ="SELECT {$flag}{$column} FROM `".$ad->escape_string($tbname)."`".rtrim($q).";";// ".$tq->extra;
        return $q;
    }


    public static function GetCondString($driver, $tab, $operator='AND', $adapter=null, $grammar=null){
        $query="";
        $t=0;
        $fc="getValue";
        $to="obj:type";
        $adapter = $driver;
        $op= $adapter->escape_string($operator);
        $c_exp="IS NULL";
        if(is_numeric($tab)){
            return "`clId`='{$tab}'";
        }
        if(is_object($tab) && ($r = $adapter->getObjValue($tab))){
            return $r;
         
        }
        $qtab = [["tab"=>$tab, "operator"=>$op, "query"=> & $query]];
        $loop =  0;
        $tquery = [];
        while($ctab = array_shift($qtab)){
            if (!$loop){
                $loop = 1;
            }else {
                //$query .= " $op ";
                $t=0;
            }

           //igk_wln("entry one 1...".$op, array_keys($tab));
           $tab = $ctab["tab"];
           $op = $ctab["operator"];
           $query = & $ctab["query"];
           $tquery[] = & $query;
           //igk_debug_wln("ini ::: ");        
        foreach($tab as $k=>$v){ 
			$c="=";			
            if(is_object($v)){
                if ($r = $adapter->getObjValue($v)){
                    if($t == 1)
                        $query .= " $op ";
                    $query .= $r;
                    $t = 1;
                    continue;
                }
                // if($v instanceof IGKDbExpression){
                //     if($t == 1)
                //         $query .= " $op ";
                //     $query .= $v->getValue((object)[
                //         "grammar"=>$grammar,
                //         "type"=>"where",
                //         "column"=>$k
                //         ]);
                //     $t = 1;
                //     continue;
                // }
                $tb = get_robjs("operand|conditions", 0, $v); 
                if ($tb->operand && $tb->conditions && preg_match("/(or|and)/i", $tb->operand)){
                    if ($t){
                        $t = 0;
                    } 
                   // $op = strtoupper($tb->operand);
                    array_unshift($qtab,["tab"=>$tb->conditions, "operator"=>strtoupper($tb->operand)]);
                    continue; 
                }    
            }
            if($t == 1)
            $query .= " $op ";            
        
            if (is_object($v)){
                if (isset($v->$fc) && is_callable($v->$fc)){
                    $query .= "`".$v->$fc()."`";      
                }
            }
            else{
                  
                if(preg_match("/^(!|@@|@&|(<|>)=?|#|\||&)/", $k, $tab)){
                    $ch=substr($k, 0, $ln=strlen($tab[0]));
                    $k=substr($k, $ln);
                    switch($ch){
                        case '!':
                        $c="!=";
                        $c_exp="IS NOT NULL";
                        break;
                        case "@@";
                        $c=" Like ";
                        break;
                        case "@&":
                            $query .= "(".static::GetKey($k, $adapter)." & ".$adapter->escape_string($v).") = ".$adapter->escape_string($v);
                            $t = 1;
                            continue 2;
                        break;
                        default:
                        $c = $ch;
                        break;
                    }
                } 
                $query .= static::GetKey($k, $adapter); // "`".implode("`.`", array_map([$adapter, "escape_string"] , explode(".", $k)))."`";  
                if($v !== null)
                {
                    if (is_array($v)){
                        $query .= $c.implode(" ", $v);
                    }else{
                        $query .= "{$c}'".$adapter->escape_string($v)."'";
                    }
                }
                else
                    $query .= " ".$c_exp;
            }
            $t=1;
        }
    }  
    
    $tquery = array_filter($tquery);
    if (count($tquery)>1){
        $query = "(".implode(") {$operator} (", $tquery).")";
    }
        return $query;
}

    protected static function GetKey($k, $driver){
         return "`".implode("`.`", array_map([$driver, "escape_string"] , explode(".", $k)))."`";          
    }

  
///<summary></summary>
    ///<param name="options"></param>
    /**
    * 
    * @param mixed $options
    */
    protected static function GetExtraOptions($options, $ad){
        $defOrder = "ASC";
        $q="";
        $options=!is_object($options) ? (object)$options: $options;
        $optset = [];
        $columns = "*";
        $query = "";
        $flag = "";
        $join = "";
		$_express = function ($v, & $query ) use($defOrder){
            $a = 0;
            foreach($v as $s){
                $s_t = explode("|", $s);
                if ($a)
                $query.=",";
                $query.= $s_t[0] ." ".strtoupper(getv($s_t, 1, $defOrder));
                $a = 1;
            }
        };
        $_buildjoins = function ($v, & $join){
            if (!is_array($v)){
                die("join options not an array");
            }
            foreach($v as $m){ 
                $t = "INNER JOIN";
                if (!is_array($m)){
                    die("expected array list in joint: ".$m);
                }
                $tab = array_keys($m)[0];
                $vv = array_values($m)[0];

                if (isset($vv["type"])){
                    $t = $vv["type"];
                }
                $join .= $t. " ";                
                $join .= $tab;
                if (isset($vv[0]))
                    $join .= " on (".$vv[0].") ";  
            }
        };
        $t_data = get_robjs("Distinct|GroupBy|OrderBy|OrderByField|Columns|Limit|Joins",0, $options);
		foreach($t_data as $k=>$v){
            if (!$v)continue;
                switch($k){
                    case queryConstant::Distinct: 
                        $flag .= "DISTINCT ";
                        
                    break;
                    case queryConstant::Limit:
					    $optset[$k] = 1;
                        $h = 1;
                        if (is_array($v)){
                            if (isset($v["start"]) && isset($v["end"])){
                            $s = $v["start"];
                            $e = $v["end"];
                            $h = $s.", ".$e;
                            } else if (count($v)==1){
                                $h = $v[0];
                            } else if (count($v)==2){
                                $h = $v[0].",".$v[1];
                            }
                        } else {
                            if (is_numeric($v))
                                $h = $v;
                        }
                        $query.= " Limit ".$h;
                    break;
                    case queryConstant::Joins:  
                        $_buildjoins($v, $join);
                    break;
                    case queryConstant::GroupBy: 
					    $optset[$k] = 1;
                        if ($ad->supportGroupBy()){
                            $query .= " GROUP BY ";
                            $a = 0;
                            foreach($v as $s){
                                $s_t = explode("|", $s);
                                if ($a)
                                $query.=",";
                                $query.= $s_t[0];
                                $a = 1;
                            }
                        }
                    break;
                    case "OrderByField":
                        break;
                    case "OrderBy":
                        // igk_wln_e("order by", $v);
                        if (is_array($v)){                       
                            $torder = "";
                            $c = "";
                            foreach($v as $s){
                                
                                $g = explode("|", $s);
                                $type = getv($g, 1, $defOrder);


                                $c.= self::Key($g[0], $ad,  "".$type.", ");
                                // $c = "`".implode("` ".$type.", `",
                                // );         
                                
                                // igk_wln(
                                //     array_map(function($t) use($ad){
                                //         return "`".implode("`.`", array_map([$ad, "escape_string"], explode(".", $t))) ."`";
                                //     },
                                //    // self::Key($t, $ad)
                                //     array_map("trim", array_filter(explode(",", "basic.ok")))),

                                //     self::Key("info.BASIC, basic, info", $ad)
                                // );
                                //igk_wln_e("done", $c); // $c,$g);
                                // array_map( function($t)use($ad){                               
                                //     return self::Key($t, $ad);
                                // },
                                // //array_map([$ad, "escape_string"],
                                // array_map("trim", explode(",", $g[0]))));
                                if (!empty($torder))
                                    $torder .=", ";
                                $torder.= $c." ".$type;
                                
                            }                         
                            $optset[$k] = $torder;

                        } else {
                            fdie("OrderBy must be an array ['Colum,...|Type']");
                        }
                    //     $query .= " ORDER BYLL ";
                    //     $_express($v, $query);
                    break;
                    case "Columns":
                        //["func" => "CONCAT", "args"=> ["nom", "prenom"], "as" => "Charles"]
                        $a = 0;
                        $columns = "";
                        foreach($v as $s){
                            if ($a){
                                $columns.=", ";
                            }
                            if (is_string($s)) {
                                $columns.= $ad->escape_string($s);
                            } elseif (is_object($s)){
                                // object of db expression;
                                if ($rg = $ad->getObExpression($v, true)){
                                    $columns .= $rg;
                                }
                                // if ($s instanceof IGKDbExpression){
                                //     $columns.= $s->getValue();
                                // } else {
                                //     throw new IGKException(__("objet not a DB Expression"));
                                // }
                            }
                            elseif(isset($s["key"])){
                                $columns.= $ad->escape_string($s["key"]);
                            }
                            elseif(isset($s["func"]) && isset($s["args"])){
                                if(is_array($s["args"])) {
                                    $columns.= $s["func"] . "(" . implode(', ', $s["args"]) . ")";
                                } else {
                                    $columns.= $s["func"] . "(" . $s["args"] . ")";
                                }
                            } elseif (is_array($s) && (count($s)==1) && is_string($s[0])){
                                $columns .= $s[0];
                            }
                            if ($c = getv($s,"as")){
                                $columns  .= " As ".$c;
                            }
                            $a = 1;
                        }
                    break;
                }
            }

         
		if (! isset($optset["OrderBy"])){
             
            if (isset($options->Sort) && isset($options->SortColumn)){
                $v = strtoupper($options->Sort);
                if (strpos("ASC|DESC", $v)!== false){
                    $q .=" ORDER BY `".$ad->escape_string($options->SortColumn)."` ".$v;
                    $optset["OrderBy"] = 1;    
                }
            }else {
             
                if (isset($options->SortColumn) &&  @is_array($options->SortColumn)){
                    foreach($options->SortColumn as $r=>$v){
                        $v = strtoupper($v);
                        if (strpos("ASC|DESC", $v)!== false){
                        $q .=" ORDER BY `".$ad->escape_string($r)."` ".$v;
                        $optset["OrderBy"] = 1;
                        }
                    }
                }
		    }
        }
        else {
            $q.= "ORDER BY ".$optset["OrderBy"];
        }

		if (!isset($optset["Limit"])){
        if(is_numeric($limit=getv($options, "Limit"))){
            $lim=$ad->escape_string($limit);
            if(is_numeric($offset=getv($options, "LimitOffset"))){
                $lim=$ad->escape_string($offset).", ".$lim;
            }
            $q .= " Limit ".$lim ;
        }
        }
        
        return (object)["columns"=>$columns, "join"=>$join, "extra"=> $q. $query, "flag"=>$flag];
    }

    


    protected static function Key($t, $adapter, $separator=","){        
        return implode($separator, array_map(function($t) use($adapter){
                return  "`".implode("`.`", array_map([$adapter, "escape_string"], explode(".", $t)))."`";
            }, 
            array_map("trim", array_filter(explode(",", $t)))));
    }

    public function listTables(){
        return $this->m_driver->sendQuery("SHOW TABLES;");
    }
}