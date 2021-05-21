<?php
 

///<summary>Represente class: IGKXMLDataAdapter</summary>
/**
* Represente IGKXMLDataAdapter class
*/
final class IGKXMLDataAdapter extends IGKDataAdapter {
	public function escape_string($v){return $v;}
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){}
    ///<summary> close db</summary>
    /**
    *  close db
    */
    public function close(){}
    ///<summary> open db</summary>
    /**
    *  open db
    */
    public function connect($ctrl=null){}
    ///<summary></summary>
    ///<param name="result" default="null"></param>
    /**
    * 
    * @param mixed $result the default value is null
    */
    public function CreateEmptyResult($result=null){
        return null;
    }
    ///<summary></summary>
    ///<param name="tablename"></param>
    ///<param name="callback"></param>
    /**
    * 
    * @param mixed $tablename
    * @param mixed $callback
    */
    public function initSystablePushInitItem($tablename, $callback){}
    ///<summary></summary>
    ///<param name="tablename"></param>
    /**
    * 
    * @param mixed $tablename
    */
    public function initSystableRequired($tablename){
        return false;
    }
    ///<summary></summary>
    ///<param name="node"></param>
    /**
    * 
    * @param mixed $node
    */
    public static function LoadConfig($node){
        if(($node == null) || (!$node->HasChilds))
            return null;
        $t=array();
        foreach($node->Childs as $k){
            $t[$k->TagName]=$k->innerHTML;
        }
        return $t;
    }
    ///load data file name of text
    /**
    */
    public static function LoadData($filename){
        $fc=null;
        $fc=function($node) use (& $fc){
            if($node->HasChilds){
                if($node->ChildCount == 1){
                    if($node->Childs[0]->NodeType == IGKXmlNodeType::TEXT){
                        return $node->innerHTML;
                    }
                    else{
                        $t=array();
                        $t[$node->TagName]=$fc($node);
                        return (object)$t;
                    }
                }
                else{
                    $t=array();
                    foreach($node->Childs as $k){
                        if(isset($t[$k->TagName])){
                            $v=$t[$k->TagName];
                            $s=array();
                            $s[]=$v;
                            $s[]=$fc($k);
                            $t[$k->TagName]=$s;
                        }
                        else
                            $t[$k->TagName]=$fc($k);
                    }
                    return (object)$t;
                }
            }
            return null;
        };
        $f=$filename;
        if(is_string($f) == false)
            return null;
        $div=igk_createnode("div");
        $s=IGK_STR_EMPTY;
        if(file_exists($f)){
            $s=igk_io_read_allfile($f);
        }
        else{
            $s=$f;
        }
        $div->Load($s);
        if($div->HasChilds){
            $d=$div->getChildAtIndex(0);
            if($d){
                $t=array();
                foreach($d->Childs as $k){
                    // igk_wln_e(gettype($k), get_class($k));
                    if (isset($t[$k->TagName])){
                        if (!is_array($t[$k->TagName])){
                            $t[$k->TagName] = [$t[$k->TagName]];
                        }
                        $t[$k->TagName][] = $k;
                    }else {
                        $t[$k->TagName]= $k; //self::__loadData($k);
                    }
                }
                return (object)$t;
            }
        }
        return null;
    }

    ///<summary></summary>
    ///<param name="filename"></param>
    ///<param name="data"></param>
    /**
    * 
    * @param mixed $filename
    * @param mixed $data
    */
    public static function storeData($filename, $data){
        $d=igk_createnode("config");
        foreach($data as $k=>$v){
            $d->add($k)->Content=$v;
        }
        return igk_io_save_file_as_utf8($filename, $d->Render());
    }
}