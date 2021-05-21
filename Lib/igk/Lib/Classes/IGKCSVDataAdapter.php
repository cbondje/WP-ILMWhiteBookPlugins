<?php


///<summary>Represente class: IGKCSVDataAdapter</summary>
/**
* Represente IGKCSVDataAdapter class
*/
final class IGKCSVDataAdapter extends IGKDataAdapter {
    private $m_ctrl;
    private $m_dbname;
    private $m_fhandle;
	public function escape_string($v){
		return $v;
	}
    ///<summary></summary>
    ///<param name="ctrl" default="null"></param>
    /**
    * 
    * @param mixed $ctrl the default value is null
    */
    public function __construct($ctrl=null){
        $this->m_ctrl=$ctrl;
    }
    ///<summary></summary>
    ///<param name="data"></param>
    /**
    * 
    * @param mixed $data
    */
    static function __removeguillemet($data){
        if(IGKString::StartWith($data, '"')){
            $data=substr($data, 1);
        }
        if(IGKString::EndWith($data, '"'))
            $data=substr($data, 0, strlen($data)-1);
        return $data;
    }
    ///<summary></summary>
    ///<param name="l"></param>
    /**
    * 
    * @param mixed $l
    */
    private static function _CSVReadLine($l, $sep=","){
        $v=false; 
        $m=explode($sep, $l);
        $r="";
        $tab=array();
        foreach($m as $i){
            $c=trim($i);
            if(!$v){
                if(preg_match("/^\"/", $c)){
                    $v=true;
                    $r .= substr(ltrim($i), 1);
                    if(preg_match("/\"$/", $r)){
                        $v=false;
                        $r=substr($r, 0, -1);
                    }
                }
                else
                    $r .= $i;
            }
            else{
                if(preg_match("/\"$/", $c)){
                    $v=false;
                    $r .= $sep.$i;
                    $r=substr($r, 0, -1);
                }
                else
                    $r .= $sep.$i;
            }
            if(!$v){
                $tab[]=trim($r);
                $r="";
            }
        }
        return $tab;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function close(){
        if($this->m_fhandle){
            fclose($this->m_f);
        }
    }
    ///<summary></summary>
    ///<param name="datafile" default="file"></param>
    /**
    * 
    * @param mixed $datafile the default value is "file"
    */
    public function connect($datafile="file"){
        $this->m_dbname=$datafile;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function selectCount(){
        igk_wln_e("CSV Adapter: Not Implement, ".__METHOD__, igk_show_trace());
    }
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
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $value
    */
    public static function GetValue($value){
        if((strpos($value, igk_csv_sep()) !== false) || preg_match("/ /i", $value))
            return "\"".$value."\"";
        return $value;
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
    ///<param name="filename"></param>
    /**
    * 
    * @param mixed $filename
    */
    public static function LoadData($filename, $options=null){
        $txt=IGKIO::ReadAllText($filename);
        return self::LoadString($txt, true, $options);
    }
    ///<summary></summary>
    ///<param name="txt"></param>
    ///<param name="rmBom" default="true"></param>
    /**
    * 
    * @param mixed $txt
    * @param mixed $rmBom the default value is true
    */
    public static function LoadString($txt, $rmBom=true, $options=null){
        if(empty($txt))
            return array();
        if($rmBom){
            $txt=igk_io_remove_bom($txt);
        }
        $lines=explode(IGK_LF, $txt);
        $entries=array();
        $sep = $options ? igk_getv($options, "separator", ","):",";
        foreach($lines as $l){
            if(empty($l)){
                continue;
            }
            $tab=self::_CSVReadLine($l, $sep);
            $entries[]=$tab;
        }
        return $entries;
    }
    ///<summary></summary>
    ///<param name="tbname"></param>
    /**
    * 
    * @param mixed $tbname
    */
    public function selectAll($tbname){
        $this->selectAllFile($tbname);
    }
    ///<summary></summary>
    ///<param name="tbname"></param>
    /**
    * 
    * @param mixed $tbname
    */
    public function selectAllFile($tbname){
        $f=igk_io_applicationdatadir()."/".$tbname.".csv";
        if(file_exists($f)){
            $r=IGKCSVQueryResult::CreateEmptyResult();
            $r->AppendEntries(self::LoadData($f), $this->m_ctrl->getDataTableInfo());
            return $r;
        }
        return null;
    }
    ///<summary></summary>
    ///<param name="filename"></param>
    ///<param name="entries"></param>
    /**
    * 
    * @param mixed $filename
    * @param mixed $entries
    */
    public static function StoreData($filename, $entries){
        $out=IGK_STR_EMPTY;
        $v_ln=false;
        foreach($entries as $v){
            if($v_ln){
                $out .= IGK_LF;
            }
            else
                $v_ln=true;
            $v_sep=false;
            foreach($v as $d){
                if($v_sep){
                    $out .= igk_csv_sep();
                }
                else
                    $v_sep=true;
                $out .= self::GetValue($d);
            }
        }
        igk_io_save_file_as_utf8($filename, $out, true);
    }
    ///convert tab to line entry
    /**
    */
    public static function toCSVLineEntry($tab, $key=null){
        $out=IGK_STR_EMPTY;
        $v_sep=false;
        if(is_object($tab)){
            foreach($tab as  $c){
                if($v_sep)
                    $out .= igk_csv_sep();
                else
                    $v_sep=true;
                $out .= self::GetValue($c);
            }
            return $out;
        }
        if(!is_array($tab)){
            return null;
        }
        if($key != null){
            $v_sep=false;
            foreach($tab as $c){
                if($v_sep)
                    $out .= igk_csv_sep();
                else
                    $v_sep=true;
                $out .= $c->$key;
            }
        }
        else{
            foreach($tab as $c){
                if($v_sep)
                    $out .= igk_csv_sep();
                else
                    $v_sep=true;
                $out .= self::GetValue($c);
            }
        }
        return $out;
    }
}