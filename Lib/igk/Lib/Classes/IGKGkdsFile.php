<?php
// @file: IGKGkdsFile.php
// @author: C.A.D. BONDJE DOUE
// @description:
// @copyright: igkdev © 2020
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

define("IGK_GKDS_LAYERDOCUMENT", "LayerDocument");
///<summary>Represente class: IGKGkdsFile</summary>
/**
* Represente IGKGkdsFile class
*/
final class IGKGkdsFile extends IGKObject {
    private $m_document;
    private $m_gd;
    private $m_source;
    ///<summary></summary>
    /**
    * 
    */
    private function __construct(){}
    ///<summary></summary>
    /**
    * 
    */
    private function _restore(){}
    ///<summary></summary>
    /**
    * 
    */
    private function _save(){}
    ///<summary></summary>
    /**
    * 
    */
    private function _visit(){
        foreach($this->m_document->Childs as  $v){
            $m="Visit".$v->TagName;
            if(method_exists(__CLASS__, $m))
                $this->$m($v);
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    public function Dispose(){
        $this->GD->Dispose();
        unset($this->m_gd);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDocument(){
        return $this->m_document;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getGD(){
        return $this->m_gd;
    }
    ///<summary></summary>
    ///<param name="filename"></param>
    ///<param name="index"></param>
    /**
    * 
    * @param mixed $filename
    * @param mixed $index the default value is 0
    */
    public static function ParseToGD($filename, $index=0){
        if(!defined("IGK_GD_SUPPORT") || !file_exists($filename))
            return null;
        $doc=IGKHtmlReader::LoadFile($filename);
        if($doc == null)
            return null;
        $t=igk_getv($doc->getElementsByTagName(IGK_GKDS_LAYERDOCUMENT), $index);
        if($t == null)
            return null;
        $f=new IGKGkdsFile();
        $f->m_document=$t;
        $f->m_gd=IGKGD::Create($t["Width"], $t["Height"]);
        $f->m_gd->Clearf("white");
        $f->_visit();
        return $f;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function RenderPicture(){
        header("Content-Type: image/png");
        $this->GD->Render();
    }
    ///<summary></summary>
    ///<param name="i"></param>
    /**
    * 
    * @param mixed $i
    */
    public function VisitCircle($i){
        $c=IGKVector2f::FromString($i["Center"]);
        $t=explode(" ", $i["Radius"]);
        $r=0;
        if(count($t) == 1){
            $r=IGKVector2f::FromString($i["Radius"]);
        }
        else{
            $r= IGKVector2f::FromString($t[0]);
        }
        $this->GD->FillEllipse(IGKColorf::FromString("red")->toByte(), $c, $r);
        $this->GD->DrawEllipse(IGKColorf::FromString("black"), $c, $r);
    }
    ///<summary></summary>
    ///<param name="layer"></param>
    /**
    * 
    * @param mixed $layer
    */
    public function VisitLayer($layer){
        foreach($layer->Childs as  $v){
            $m="Visit".$v->TagName;
            if(method_exists(__CLASS__, $m))
                $this->$m($v);
        }
    }
}
