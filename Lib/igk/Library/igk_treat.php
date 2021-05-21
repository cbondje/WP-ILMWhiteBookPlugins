<?php
// @file: igk_treat.php
// @author: C.A.D. BONDJE DOUE
// @copyright: igkdev © 2019
// @license: Microsoft MIT License. For more informartion read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

defined("IGK_FRAMEWORK") || die("REQUIRE FRAMEWORK - No direct access allowed");

///<summary>protect request information</summary>
/**
* protect request information
*/
function igk_protect_request(& $tab){
    static $protecResolver=null;
    if($protecResolver === null){
        $protecResolver=new IGKProtectHtmlField();
    }
    $q=array();
    foreach($tab as $k=>$v){
        if(!is_numeric($v)){
            $v=$protecResolver->protect($v);
        }
        $tab[$k]=$v;
    }
}
///<summary>represent igk_treat_source function</summary>
///<param name="source" type="mixed: (string|arrayof(string))">string to treat</param>
///<param name="callback">callback to call when done</param>
///<param name="tab" default="null">tab information for algorightm</param>
///<param name="options" default="null" ref="true">optiosn for treatment</param>
/**
* represent igk_treat_source function
* @param mixed: (string|arrayof(string)) source string to treat
* @param mixed closure callback callback to call when done
* @param mixed tab tab information for algorightm
* @param mixed options options for treatment
*/
function igk_treat_source($source, $callback, $tab=null, & $options=null){
    if(is_string($source)){
        $source=explode("\n", $source);
    }
    if(!function_exists("igk_treat_append")){
        function igk_treat_append($options, $t, $indent=0){
            if(isset($options->writeListener)){
                $fc=$options->writeListener;
                call_user_func_array($fc, func_get_args());
                return;
            }
            if($options->mode != 0)
                return;
            $options->output .= $t;
        }
    }
    if(!function_exists("igk_treat_create_options")){
        function igk_treat_create_options(){
            $options=(object)array();
            return $options;
        }
    }
    if(!function_exists("igk_treat_source_expression")){
        function igk_treat_source_expression($options){
            $tab=array();
            return $tab;
        }
    }
    $options=$options ?? igk_treat_create_options();
    $tab=$tab ?? igk_treat_source_expression($options);
    $out=& $options->output;
    $offset=& $options->offset;
    $sline=& $options->lineNumber;
    $tline=igk_count($source);
    $options->totalLines=$tline;
    $options->source=$source;
    $options->{"@automatcher_flag"}=array();
    $flag=0;
    $autoreset_flag=& $options->{"@automatcher_flag"};
    while($sline < $tline){
        $t=$source[$sline];
        $sline++;
        if($options->IgnoreEmptyLine && (strlen(trim($t)) == 0)){
            continue;
        }
        if($flag){
            if($options->DataLFFlag && ($options->conditionDepth<=0)){
                $options->DataLFFlag=0;
                igk_treat_append($options, $options->LF, 0);
            }
            else{
                if(is_object($options->toread) && ($options->toread->mode == 0)){
                    $options->DataLFFlag=0;
                    igk_treat_append($options, " ", 0);
                }
            }
        }
        $flag=1;
        //$matchFlag=0;
        $tq=array(rtrim($t));
        $offset=0;
        $auto_reset_list=isset($options->autoResetList) ? $options->autoResetList: array("operatorFlag", "mustPasLineFlag");
        while($t=array_pop($tq)){
            $matches=null;
            $mlist=null;
            foreach($tab as  $v){
                if(((is_callable($gf=$v->mode) && $gf($options)) || ($v->mode === "*") || ($v->mode === $options->mode)) && preg_match($v->pattern, $t, $matches, PREG_OFFSET_CAPTURE, $offset)){
                    $start=$matches[0][1];
                    if(!$mlist || ($mlist->start > $start)){
                        if(!$mlist)
                            $mlist=(object)array();
                        $mlist->start=$start;
                        $mlist->matcher=$v;
                        $mlist->data=$matches;
                        $mlist->options=$options;
                    }
                }
            }
            if($mlist){
                foreach($auto_reset_list as $re){
                    if(isset($options->$re)){
                        if(isset($autoreset_flag[$re])){
                            $options->$re=0;
                            unset($autoreset_flag[$re]);
                        }
                        else
                            $autoreset_flag[$re]=1;
                    }
                }
                if($options->endMarkerFlag && isset($options->definitions->lastTreat)){
                    if(isset($autoreset_flag["endMarkerFlag"])){
                        $options->endMarkerFlag=0;
                        unset($autoreset_flag["endMarkerFlag"]);
                    }
                    else
                        $autoreset_flag["endMarkerFlag"]=1;
                }
                igk_debug_wln("matcher: ".$mlist->matcher->name);
                $fc=$mlist->matcher->callback;
                $t=$fc($t, $mlist->start, $offset, $mlist);
                if(!empty($t)){
                    array_push($tq, $t);
                    continue;
                }
            }
            break;
        }
        $s=trim($t);
        if((strlen($s) == 0) && $options->IgnoreEmptyLine){
            $flag=0;
        }
        else{
            igk_treat_append($options, ltrim($t), 0);
        }
    }
    unset($options->{"@automatcher_flag"});
    if($callback){
        return $callback($out, $options);
    }
    return $out;
}
///<summary>Represente class: IGKProtectHtmlField</summary>
/**
* Represente IGKProtectHtmlField class
*/
class IGKProtectHtmlField{
    private $engines;
    private $options;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        $this->_initOptions();
        $this->engines=array();
        $this->_initengines();
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    private function __output($v){
        return $v;
    }
    ///<summary></summary>
    /**
    * 
    */
    private function _initengines(){
        $tab=& $this->engines;
        array_unshift($tab, (object)array(
            "name"=>"uncollapsestring",
            "mode"=>'*',
            "pattern"=>"/(\"|')/i",
            "callback"=>function(& $t, $start, & $offset, $m){
                    $lis=$start;
                    $ch=$t[$start];
                    $s="";
                    $multilinestart=($ch == "'");
                    $ln=& $m->options->lineNumber;
                    $tln=$m->options->totalLines;
                    $before=substr($t, 0, $start);
                    $x=substr($t, $start + 1);
                    $start=0;
                    $escaped=0;
                    while((($pos=strpos($x, $ch, $start)) === false) && ($ln < $tln) || ($escaped=(($pos > 0) && $x[$pos-1] == '\\'))){
                        if($escaped){
                            if($pos > 1){
                                if($x[$pos-2] == "\\"){
                                    break;
                                }
                            }
                            $start=$pos + 1;
                            $escaped=0;
                            continue;
                        }
                        $s .= substr($x, $start).$m->options->LF;
                        $x=$m->options->source[$ln];
                        $ln++;
                        $start=0;
                        $escaped=0;
                    }
                    if($pos !== false){
                        $t=substr($x, $pos + 1);
                        $offset=0;
                        $s .= substr($x, 0, $pos);
                        $s=$before.$ch.$s.$ch;
                        $offset=strlen($s);
                        $t=$s.$t;
                    }
                    else{ 
                        // "no ed found"
                        $offset = strlen($t);
                        return $t;
                        // igk_wln_e("something wrong ... string litteral", $t);
                    }
                    return $t;
                }
        ));
        array_unshift($tab, (object)array(
            "name"=>"scriptTagRemove",
            "mode"=>"*",
            "pattern"=>"/\<(\/)?(script|embed|audio|object|style|img|frame|iframe|link)/i",
            "callback"=>function(& $t, $start, & $offset, $m){
                    $r=substr($t, 0, $start);
                    if(!empty($r)){
                        igk_treat_append($m->options, $r, 0);
                    }
                    $t=substr($t, $start + strlen($m->data[0][0]));
                    $offset=0;
                    $m->options->mode=1;
                    return $t;
                }
        ));
        array_unshift($tab, (object)array(
            "name"=>"scriptTagRemove",
            "mode"=>1,
            "pattern"=>"/\>/i",
            "callback"=>function(& $t, $start, & $offset, $m){
                    $r=substr($t, 0, $start);
                    $t=substr($t, $start + strlen($m->data[0][0]));
                    $offset=0;
                    $m->options->mode=0;
                    return $t;
                }
        ));
    }
    ///<summary></summary>
    /**
    * 
    */
    private function _initOptions(){
        $this->options=(object)array(
            "out"=>"",
            "lineNumber"=>0,
            "IgnoreEmptyLine"=>0,
            "output"=>"",
            "data"=>"",
            "mode"=>0,
            "offset"=>0,
            "endMarkerFlag"=>0,
            "DataLFFlag"=>0,
            "toread"=>null,
            "DataLF"=>"\n"
        );
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function protect($v){
        $this->_initOptions();
        $options=& $this->options;
        $v=igk_treat_source($v, function(){
            return call_user_func_array(array($this, '__output'), func_get_args());
        }
        , $this->engines, $options);
        return $v;
    }
}