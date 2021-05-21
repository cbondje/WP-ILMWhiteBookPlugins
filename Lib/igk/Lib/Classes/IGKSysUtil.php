<?php
///<summary> System utility class </summary>

use IGK\Controllers\BaseController;

/**
 * 
 * @package 
 */
final class IGKSysUtil
{
    private function __construct(){
    }

    public static function Encrypt($pwd, $prefix=null){
        return igk_encrypt($pwd, $prefix);
    }
    /**
     * clear lib controller
     * @return void 
     */
    public static function CleanLibFolder(){
        if ($hdir = opendir($rdir = realpath(IGK_LIB_DIR."/../"))){
            while($cdir = readdir($hdir)){
                if (($cdir=="." )|| ($cdir=="..") || ($cdir=="igk")){
                    continue;
                }
                if (is_dir($b = $rdir."/".$cdir)){ 
                    IGKIO::RmDir($b);
                } 
            }
        }
    }
    /**
     * Retreive all managed data information
     * @param string $dataadapter 
     * @return array 
     * @throws Exception 
     */
    public static function GetConfigDataInfo($dataadapter = IGK_MYSQL_DATAADAPTER)
    {
        $ctrl = igk_app()->getControllerManager()->getControllers();
        $tables = [];
        foreach ($ctrl as $v) {
            $tables = array_merge($tables, self::GetControllerConfigDataInfo($v, $dataadapter));
        }
        return $tables;
    }
    /**
     * 
     * @param mixed $file 
     * @return void 
     */
    public static function GetDataDefinitionFromFile($file, $v=null, & $tables=null){
        if ($tables ===null)
            $tables = [];
        $data = igk_db_load_data_schemas($v->getDataSchemaFile());
        $tschema = $data->tables;
        if ($tschema) {
            $entries = [];
            foreach ($tschema as $ck => $cv) {
                if (isset($tables[$ck])) {
                    igk_ilog("Table $ck already found. [" . $v->Name . "] get from " . $tables[$ck]->ctrl->Name . " with schema");
                    return null;
                }

                $cinfo = igk_getv($cv, "ColumnInfo");
                $desc = igk_getv($cv, "Description");
                $entries = igk_getv($cv, "Entries");
                $tables[$ck] = (object)array("info" => $cinfo, "ctrl" => $v, "desc" => $desc, "entries" => $entries);
            }
        }
        return $tables;
    }
    /**
     * retriceve all data info form controller
     * @param mixed $controller 
     * @param string $dataadapter 
     * @return null|array 
     * @throws Exception 
     */
    public static function GetControllerConfigDataInfo($controller, $dataadapter = IGK_MYSQL_DATAADAPTER)
    {
        $tables = [];
        $v = $controller;
        if ($v->getDataAdapterName() == $dataadapter) {
            $b = BaseController::Invoke($v, "getUseDataSchema");
            // 
            if (!$b) {
                $tname = $v->getDataTableName();
                $tinfo = $v->getDataTableInfo();
                if (!empty($tname) && ($tinfo !== null)) {
                    if (isset($tables[$tname])) {
                        igk_ilog("Table $tname already found. [" . $v->Name . "] get from " . $tables[$tname]->ctrl->Name . " no schema");
                        return null;
                    }
                    $tables[$tname] = (object)array("info" => $tinfo, "ctrl" => $v, "desc" => null, "entries" => null);
                }
            } else {
                self::GetDataDefinitionFromFile($v->getDataSchemaFile(), $v, $tables);
            }
        }
        return $tables;
    }

    /**
     * 
     * @param BaseController $controller 
     * @return string 
     */
    public static function GetControllerSqlQueryData(BaseController $controller): string
    {
        $s = "";
        if ($tables = self::GetControllerConfigDataInfo($controller)) {
            $controller->Db->connect();
            $relations = [];
            $tentries = [];
            foreach ($tables as $table => $info) {
                //     Utils::GenerateAndWriteMigration($table, $info, $out) || die("failed to write ".$table);


                $s .= IGKSQLQueryUtils::CreateTableQuery($table, $info->info, $info->desc) . PHP_EOL;
                $refered = 0;
                $refered_counter = 0;
                $links = "";
                $queryfilter = igk_environment()->mysql_query_filter;
                foreach ($info->info as $ti) {


                    if ($ti->clLinkType) {
                        $refColumn = igk_getv($ti, "clLinkColumn", IGK_FD_ID);
                        $nk = $queryfilter ? '' : '`'.$table . "_" . $ti->clName.'`';

                        $links .= trim(IGKString::Format(
                            "ALTER TABLE {0} ADD CONSTRAINT {1} FOREIGN KEY (`{2}`) REFERENCES {3}  ON DELETE RESTRICT ON UPDATE RESTRICT;",
                            "`{$table}`",
                            $nk,
                            $ti->clName,
                            IGKString::Format(
                                "`{0}`(`{1}`)",
                                $ti->clLinkType,
                                $refColumn
                            )
                        ));

                        if ($refered = ($refered || ($ti->clLinkType != $table))) {
                            $refered_counter++;
                        }
                    }
                }
                if ($entry = $info->entries) {
                    if (!isset($tentries[$refered_counter])) {
                        $tentries[$refered_counter] = [];
                    }
                    array_push($tentries[$refered_counter], ["table" => $table, "entries" => $entry]);
                }
                if (!empty($links)) {
                    if (!isset($relations[$refered_counter])) {
                        $relations[$refered_counter] = "";
                    } else {
                        $relations[$refered_counter] .=  PHP_EOL;
                    }
                    $relations[$refered_counter] .= $links;
                }
            }
            if (!empty($relations)) {
                krsort($relations);
                foreach ($relations as $v) {
                    $s .= trim($v) . PHP_EOL;
                }
                //$s .= implode(PHP_EOL, $relations); 
            }
            if ($tentries) {
                krsort($tentries);
                foreach ($tentries as $te) {
                    foreach ($te as $m) {
                        $tb = $m["table"];
                        foreach ($m["entries"] as $c) {
                            $s .= IGKSQLQueryUtils::GetInsertQuery($tb, $c) . PHP_EOL;
                        }
                    }
                }
            } 
            $controller->Db->close();
        }
        return $s;
    }

    /**
     * resolv the table name
     */
    public static function GetTableName($table, $ctrl=null){   
        return igk_db_get_table_name($table, $ctrl); 
    }
}
