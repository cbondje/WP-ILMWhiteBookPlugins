<?php
namespace IGK\System\Database;

use IGKDbColumnInfo;

/**
 * schema table builder
 * @package IGK\System\Database
 */
class SchemaTableBuilder extends SchemaBuilderHelper{


    /**
     * represent schema table builder
     * @param mixed $node 
     * @param mixed $schema 
     * @return SchemaTableBuilder 
     */
    public static function Create($node, $schema){
        $c = new SchemaTableBuilder();
        $c->_output = $node;
        $c->_schema = $schema;
        return $c;
    }
   
    public function columnAttributes(array $attributes){
        return $this->_addcolumnAttributes($attributes);
    }
    public function column_varchar($name, $length, ?array $options=null){
        if ($length<=0){
            die("length not valid");
        }
        $default = $options ? igk_getv($options, "default") : null;
        $primarykey = $options ? igk_getv($options, "primarykey") : null;
        $desc = $options ? igk_getv($options, "desc") : null;
        $is_unique = $options ? igk_getv($options, "is_unique") : null;
        $is_unique_column = $options ? igk_getv($options, "is_unique_column") : null;
        $column_member_index = $options ? igk_getv($options, "column_member_index") : null;
        return $this->column($name, "VarChar",$length, $default, $primarykey, false, $desc, $is_unique, $is_unique_column, 
        $column_member_index);
    }
    /**
     * 
     * @param mixed $name 
     * @param mixed $type 
     * @param mixed $length 
     * @param mixed|null $default 
     * @param bool $primarykey 
     * @param bool $auto_increment 
     * @param string $desc 
     * @param bool $is_unique 
     * @param bool $is_unique_column 
     * @param null|int $column_member_index 
     * @return $this 
     */
    public function column($name, $type, $length, $default=null, 
        $primarykey=false, 
        $auto_increment=false,
        $desc="null",
        $is_unique=false,  
        $is_unique_column=false,
        ?int $column_member_index=null){
        $this->_addcolumnAttributes([
            "clName"=>$name,
            "clType"=>$type ?? "Int",  
            "clTypeLength"=>$length,
            "clDescription"=>$desc,
            "clAutoIncrement"=>$auto_increment, 
            "clDefault"=>$default,
            "clIsPrimaryKey"=>$primarykey,
            "clIsUnique"=>$is_unique,
            "clIsUniqueColumnMember"=>$is_unique_column,
            "clColumnMemberIndex"=>$column_member_index
        ]);
        return $this;
    }
}