<?php
// @file: class.IGKAppleIconSpecificsCTRL.php
// @author: C.A.D. BONDJE DOUE
// @description:
// @copyright: igkdev © 2020
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

///<summary>Represente class: IGKAppleIconCtrl</summary>
/**
* Represente IGKAppleIconCtrl class
*/
abstract class IGKAppleIconCtrl extends IGKCtrlTypeBase {
    ///<summary></summary>
    /**
    * 
    */
    public static function GetAdditionalConfigInfo(){
        return array(
            "clAppleIconUri"=>igk_createAdditionalConfigInfo(array("clRequire"=>1)),
            "clAppleTouchIconType"=>new IGKAdditionCtrlInfo("select",
            array(
                "apple-touch-icon"=>"apple-touch-icon",
                "apple-touch-icon-precomposed"=>"apple-touch-icon-precomposed"
            ))
        );
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getAppleIcon(){
        $tb=explode(',', $this->Configs->clAppleIconUri);
        return $tb;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getCanAddChild(){
        return false;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getisVisisble(){
        return true;
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function InitComplete(){
        parent::InitComplete();
        $tab=$this->getAppleIcon();
        $c=igk_count($tab);
        $regex="/\.(?P<name>(([0-9]+)x([0-9]+))*)/i";
        if($c == 1){
            $lnk=$this->app->Doc->addLink("apple-touch-icon");
            $lnk["rel"]=$this->Configs->clAppleTouchIconType;
            $v=$this->Configs->clAppleIconUri;
            if(preg_match($regex, $v)){
                preg_match_all($regex, $v, $t);
                $lnk["sizes"]=$t["name"][0];
            }
            $lnk["href"]="?vimg=".$v;
        }
        else{
            $i=0;
            foreach($tab as  $v){
                $lnk=$this->app->Doc->addLink("apple-touch-icon:".$i);
                $lnk["rel"]=$this->Configs->clAppleTouchIconType;
                if(preg_match($regex, $v)){
                    preg_match_all($regex, $v, $t);
                    $lnk["sizes"]=$t["name"][0];
                }
                $lnk["href"]="?vimg=".$v;
                $i++;
            }
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function initTargetNode(){}
    ///<summary></summary>
    ///<param name="t" ref="true"></param>
    /**
    * 
    * @param  * $t
    */
    public static function SetAdditionalConfigInfo(& $t){
        $t["clAppleIconUri"]=igk_getr("clAppleIconUri");
        $t["clAppleTouchIconType"]=igk_getr("clAppleTouchIconType");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function View(){}
}
?>