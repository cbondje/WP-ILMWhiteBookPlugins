<?php
namespace IGK\System\IO\File;


class PHPScriptBuilder{
    public function __get($name){
        return null;
    }
    public function __call($name, $arguments)
    {
        $this->$name = $arguments[0];
        return $this;        
    }
    public function render(){
        $o ="";
        $h = "";
        $h = implode("\n", [
            "// @author: ". $this->author,
            "// @file: ".$this->file,
            "// @desc: " .implode("\n//", explode("\n", $this->desc)),
            "// @date: ".date("Ymd H:i:s")
        ])."\n";
        if ($ns = $this->namespace){
            $h.= "namespace ".$ns.";\n\n";
        }
        $defs = "";
        if ($e = $this->defs){
            $defs.= implode("\n", array_map(function($s){ return "\t".$s;} ,explode("\n", $e)))."\n";
        }

        switch($this->type){
            case "function":
                $o.= preg_replace("/^\\t/m", "", $defs);
                break;
            case "class":
               if($d = $this->doc){
                   // documents
                   $o .= "///<summary>".$d ."</summary>\n";
                   $o .= "/**\n * ".$d."\n */\n";
               }

            $o .= $this->type ." ".$this->name;
            if ($e = $this->extends){
                $h.= "use ".$e.";\n";
                $o.= " extends ".basename(igk_html_uri($e));
            } 
            if ($e = $this->implements){
                if (!is_array($e)){
                    $e = [$e];
                }
                $o.= " implements ".implode(",", $e);
            } 
            $o .= "{\n";
                
                $o .= $defs;
                
                $o .= "}";
                default:
                break;
            }
                return "<?php\n".$h."\n".$o;
    }
}