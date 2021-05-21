<?php

use IGK\Resources\R;

use function igk_resources_gets as __;

///<summary>represent html utility </summary>
/**
* represent html utility
*/
final class IGKHtmlUtils {
    private static $gRendering;

    public static function SubmitActionCallback($title=null, $name="btn_submit"){
        return function($a)use ($title, $name){
            $a->addInput($name, "submit", $title)->setClass("igk-btn-default");
        };
    }
    public static function ConfirmAction(){
        return function($a){
            $a->addInput("btn.ok", "submit", __("restore"));
            $a->addInput("btn.cancel", "submit", __("cancel"))->on("click", "igk.winui.controls.panelDialog.close()");
        };

    }
    public static function GetInputType($type){
        static $requireInput;
        if ($requireInput===null){
            $requireInput  = explode("|", "text|email|password");
        } 
        if (in_array($type, $requireInput)){ 
            return $type;
        }
        return "text";
    }
    ///AddImgLnk add image link
    /**
    */
    public static function AddAnimImgLnk($target, $uri, $imgname, $width="16px", $height="16px", $desc=null, $attribs=null){
        if(is_object($target)){
            $a=$target->add("a", array("href"=>$uri, "class"=>"img_lnk"));
            $t=array();
            $t["a"]=$a;
            $t["img"]=$a->add("igk-anim-img", array(
                "width"=>$width,
                "height"=>$height,
                "src"=>R::GetImgUri($imgname),
                "alt"=>__($desc)
            ));
            $a->AppendAttributes($attribs);
            return (object)$t;
        }
        return null;
    }
    ///add button link
    /**
    */
    public static function AddBtnLnk($target, $langkey, $uri, $attributes=null){
        if($target == null)
            return;
        $a=$target->add("a", array("class"=>"igk-btn igk-btn-lnk", "href"=>$uri));
        $a->Content=is_string($langkey) ? __($langkey): $langkey;
        if(is_array($attributes)){
            $a->AppendAttributes($attributes);
        }
        return $a;
    }
    ///AddImgLnk add image link
    /**
    */
    public static function AddImgLnk($target, $uri, $imgname, $width="16px", $height="16px", $desc=null, $attribs=null){
        if(is_object($target)){ 
            
            $a=$target->addImgLnk($uri, $imgname, $width, $height, $desc);
            if ($attribs)
            $a->AppendAttributes($attribs);
            return $a;
        }
        return null;
    }
    ///<summary></summary>
    ///<param name="item"></param>
    ///<param name="target"></param>
    ///<param name="index" default="null"></param>
    /**
    * 
    * @param mixed $item
    * @param mixed $target
    * @param mixed $index the default value is null
    */
    public static function AddItem($item, $target, $index=null){
        if(($item == null) || ($target == null))
            return false;
        if($item->ParentNode === $target)
            return true;
        self::RemoveItem($item);
        return $target->add($item, null, $index);
    }
    ///<summary></summary>
    ///<param name="tr"></param>
    ///<param name="targetid" default="null"></param>
    /**
    * 
    * @param mixed $tr
    * @param mixed $targetid the default value is null
    */
    public static function AddToggleAllCheckboxTh($tr, $targetid=null){
        if($targetid != null)
            $targetid=",'#$targetid'";
        return $tr->add("th", array("class"=>"box_16x16"))->addLi()->add("input", array(
            "type"=>"checkbox",
            "onchange"=>"ns_igk.html.ctrl.checkbox.toggle(this, ns_igk.getParentByTagName(this, 'table'), this.checked, true $targetid);"
        ));
    }
    ///<summary></summary>
    ///<param name="array"></param>
    /**
    * 
    * @param mixed $array
    */
    public static function BuildForm($array){
        $frm=igk_createnode("form");
        foreach($array as $k=>$v){
            switch(strtolower($k)){
                case "label":
                $lb=$frm->addLabel();
                $lb->Content=__(IGK_STR_EMPTY);
                break;
                case "radio":
                $frm->addInput($v["id"], igk_getv($v, "text", null), "radio");
                break;
                case "checkbox":
                $frm->addInput($v["id"], igk_getv($v, "text", null), "checkbox");
                break;
                case "hidden":
                $frm->addInput($v["id"], igk_getv($v, "text", null), "hidden");
                break;
                case "button":
                case "submit":
                case "reset":
                $frm->addInput($v["id"], strtolower($k), igk_getv($v, "text", "r"));
                break;
                case "textarea":
                $frm->addTextArea($v["id"]);
                break;
                case "br":
                $frm->addBr();
                break;
            }
        }
        return $frm;
    }
    ///<summary></summary>
    ///<param name="item"></param>
    ///<param name="target"></param>
    /**
    * 
    * @param mixed $item
    * @param mixed $target
    */
    public static function CopyChilds($item, $target){
        if(($item == null) || ($target == null) || !$item->HasChilds)
            return false;
        foreach($item->getChilds() as $k){
            $target->Load($k->Render());
        }
        return true;
    }
    ///used to create sub menu in category
    /**
    */
    public static function CreateConfigSubMenu($target, $items, $selected=null){
        $ul=$target->add("ul", array("class"=>"igk-cnf-content_submenu"));
        foreach($items as $k=>$v){
            $li=$ul->addLi();
            $li->add("a", array("href"=>$v))->Content=__("cl".$k);
            if($selected == $k){
                $li["class"]="+igk-cnf-content_submenu_selected";
            }
            else{
                $li["class"]="-igk-cnf-content_submenu_selected";
            }
        }
        return $ul;
    }
    ///get all element childs
    /**
    */
    public static function GetAllChilds($t){
        $d=array();
        if(method_exists(get_class($t), "getChilds")){
            $s=$t->getChilds();
            if(is_array($s)){
                $d=array_merge($d, $s);
                foreach($s as $k){
                    $d=array_merge($d, self::GetAllChilds($k));
                }
            }
        }
        return $d;
    }
    ///<summary></summary>
    ///<param name="$c"></param>
    ///<param name="context" default="null"></param>
    /**
    * 
    * @param mixed $c
    * @param mixed $context the default value is null
    */
    public static function GetAttributeValue($c, $context=null){
        $s=self::GetValue($c);
        $q=trim($s);
        $v_h="\"";
        if(preg_match("/^\'/", $q) && preg_match("/\'$/", $q)){
            $v_h="'";
        }
        if(IGKString::StartWith($q, $v_h)){
            $q=substr($q, 1);
        }
        if(IGKString::EndWith($q, $v_h)){
            $q=substr($q, 0, strlen($q) - 1);
        }
        if($v_h == "\""){
            $q=str_replace("\"", "&quot;", $q);
        }
        if($context && is_string($context) && (preg_match("/(xml|xsl)/i", $context))){
            $q=str_replace("&amp;", "&", $q);
        }
        return $q;
    }
    ///<summary></summary>
    ///<param name="n"></param>
    ///<param name="options" default="null"></param>
    /**
    * 
    * @param mixed $n
    * @param mixed $options the default value is null
    */
    public static function GetContentValue($n, $options=null){
        

        if($n->iscallback("handleRender")){
            return $n->handleRender();
        }
        $c=$n->getContent();
        $inner=""; 
        if(igk_is_callback_obj($c)){
            return igk_invoke_callback_obj(igk_getv($c, 'clType') == 'node' ? $n: null, $c);
        }
        if(is_array($c)){
            $r=igk_createnode('content');
            $i=0;
            foreach($c as $k=>$v){
                if(ord($k) == 0)
                    $r->add("item")->setAttribute("key", $k)->setContent($v);
                $i++;
            }
            return $r->getinnerHtml(null);
        }
        if(is_object($c) && igk_reflection_class_extends($c, "IGKHtmlItem")){
            if(self::$gRendering === $c)
                return "";
            self::$gRendering=$c;
            $o=$c->Render($options);
            self::$gRendering=null;
            return $o;
        } 
        return self::GetValue($c, $options);
    }
    ///<summary></summary>
    ///<param name="array"></param>
    /**
    * 
    * @param mixed $array
    */
    public static function GetTableFromSingleArray($array){
        $tab=igk_createnode("table");
        foreach($array as $k=>$v){
            $tr=$tab->addTr();
            $tr->addTd()->Content=$k;
            $tr->addTd()->Content=$v;
        }
        return $tab;
    }
    ///<summary>return value according to string</summary>
    /**
    * return value according to string
    */
    public static function GetValue($c, $options=null){
        $out=IGK_STR_EMPTY;
        if(($c == "0") || (is_numeric($c) && ($c === "0")))
            return "0";
        if(is_numeric($c) || (is_string($c) && !empty($c))){
            $out .= $c;
        }
        else if(is_object($c)){
            $cl=get_class($c);
            if(igk_reflection_class_extends($cl, 'IGKHtmlItemBase')){
                return igk_html_render_node($c, $options, null);
            }
            while(is_object($c)){
                $c=self::GetValueObj($c, $options);
            }
            if(empty($c))
                return null;
            $out .= $c;
        } else{
            $out = $c; 
        }
        return $out;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    ///<param name="options"></param>
    /**
    * 
    * @param mixed $v
    * @param mixed $options
    */
    public static function GetValueObj($v, $options){
        if(method_exists(get_class($v), IGK_FC_GETVALUE)){
            $v=$v->getValue($options);
        }
        else{
            switch(igk_getv($v, IGK_OBJ_TYPE_FD)){
                case 'callable':
                $v=call_user_func_array($v->name, $v->attrs ? $v->attrs: array());
                break;
                case '_callback':
                $v=igk_invoke_callback_obj(null, $v);
                break;default:
                if(is_callable($fc=igk_getv($v, "callback"))){
                    $v=(call_user_func_array($fc, array_merge(igk_getv($v, "params", []), $options ? [$options]: [])));
                }
                else{
                    $v="\"IGK:DATAOBJ\"";
                }
                break;
            }
        }
        return $v;
    }
    ///<summary></summary>
    ///<param name="item"></param>
    ///<param name="target"></param>
    /**
    * 
    * @param mixed $item
    * @param mixed $target
    */
    public static function MoveChilds($item, $target){
        if(($item == null) || ($target == null) || !$item->HasChilds)
            return false;
        foreach($item->getChilds() as $k){
            IGKHtmlUtils::AddItem($k, $target);
        }
        return true;
    }
    ///<summary></summary>
    ///<param name="id"></param>
    ///<param name="value" default="null"></param>
    ///<param name="type" default="text"></param>
    /**
    * 
    * @param mixed $id
    * @param mixed $value the default value is null
    * @param mixed $type the default value is "text"
    */
    public static function nInput($id, $value=null, $type="text"){
        $btn=igk_createnode("input")->AppendAttributes(array("id"=>$id, "name"=>$id, "type"=>$type, "value"=>$value));
        switch(strtolower($btn["type"])){
            case "button":
            case "submit":
            case "reset":
            $btn["class"]="clbutton";
            break;
        }
        return $btn;
    }
    ///<summary></summary>
    ///<param name="id"></param>
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $id
    * @param mixed $value
    */
    public static function nTextArea($id, $value){
        return igk_createnode("textarea")->AppendAttributes(array("id"=>$id, "name"=>$id, "value"=>$value));
    }
    ///<summary></summary>
    ///<param name="item"></param>
    /**
    * 
    * @param mixed $item
    */
    public static function RemoveItem($item){
        $p=null;
        if(($item != null) && (($p=$item->getParentNode()) != null)){
            if($p->remove($item) == false){
                igk_debug_wln("/!\\ Failed to remove an item");
                return false;
            }
            return true;
        }
        return false;
    }
    ///<summary></summary>
    ///<param name="var"></param>
    /**
    * 
    * @param mixed $var
    */
    public static function ShowHierarchi($var){
        $out=IGK_STR_EMPTY;
        if($var->HasChilds){
            $out .= "<ul>";
            foreach($var->Childs as $k){
                $out .= "<li>".$k->TagName;
                if($k->TagName == "a")
                    $out .= " : ".$k->Content;
                if($k->TagName == "input")
                    $out .= " : ".$k["value"];
                $out .= self::ShowHierarchi($k);
                $out .= "</li>";
            }
            $out .= "</ul>";
        }
        return $out;
    }
    ///<summary></summary>
    ///<param name="target"></param>
    ///<param name="type" default="tr"></param>
    ///<param name="startAt"></param>
    ///<param name="class1" default="table_darkrow"></param>
    ///<param name="class2" default="table_lightrow"></param>
    /**
    * 
    * @param mixed $target
    * @param mixed $type the default value is "tr"
    * @param mixed $startAt the default value is 0
    * @param mixed $class1 the default value is "table_darkrow"
    * @param mixed $class2 the default value is "table_lightrow"
    */
    public static function ToggleTableClassColor($target, $type="tr", $startAt=0, $class1="table_darkrow", $class2="table_lightrow"){
        if($target == null)
            return;
        $k=0;
        $tab=$target->getElementsByTagName($type);
        for($i=$startAt; $i < count($tab); $i++){
            if(isset($tab[$i])){
                $tr=$tab[$i];
                if($k == 0){
                    $tr["class"]=$class1;
                    $k=1;
                }
                else{
                    $tr["class"]=$class2;
                    $k=0;
                }
            }
        }
    }
}