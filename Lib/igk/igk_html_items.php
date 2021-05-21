<?php
// author: C.A.D. BONDJE DOUE
// licence: IGKDEV - Balafon @ 2019
use IGK\Resources\R;
use function igk_resources_gets as __;






///<summary>used to bind atricle from controller in ajx context</summary>
/**
* used to bind atricle from controller in ajx context
*/
final class IGKHtmlAJXBindDataNodeItem extends IGKHtmlComponentNodeItem {
    private $m_invoked;
    var $Article;
    var $Ctrl;
    var $Row;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("div");
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
    public function invokeUri(){
        $d=igk_createNode("div");
        igk_html_binddata($this->Ctrl, $d, $this->Article, $this->Row);
        $d->RenderAJX();
        $this->m_invoked=true;
        $this->add($d);
        igk_exit();
    }
    ///<summary></summary>
    ///<param name="options" default="null"></param>
    /**
    * 
    * @param mixed $options the default value is null
    */
    public function render($options=null){
        if(!$this->m_invoked){
            $this["igk-js-init-uri"]=$this->getController()->getUri("invokeUri", $this);
        }
        return parent::Render($options);
    }
}
///<summary>represent a ajx button link</summary>
/**
* represent a ajx button link
*/
final class IGKHtmlAJXButtonItem extends IGKHtmlDiv {
    private $m_method;
    private $m_rep;
    private $m_uri;
    ///<summary></summary>
    ///<param name="uri" default="null"></param>
    ///<param name="reponse" default="'null'"></param>
    ///<param name="method" default="'POST'"></param>
    /**
    * 
    * @param mixed $uri the default value is null
    * @param mixed $reponse the default value is 'null'
    * @param mixed $method the default value is 'POST'
    */
    public function __construct($uri=null, $reponse='null', $method='POST'){
        parent::__construct();
        $this->m_uri=$uri;
        $this->m_method=$method;
        $this->m_rep=$reponse;
        $this["class"]="igk-btn igk-ajx-btn";
        $this["onclick"]=new IGKValueListener($this, 'Script');
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getScript(){
        $s='javascript: ';
        if($this->m_uri){
            $rep=$this->m_rep;
            if($this->m_method == 'POST'){
                $s .= "ns_igk.ajx.post('{$this->m_uri}', null, $rep);";
            }
            else{
                $s .= "ns_igk.ajx.get('{$this->m_uri}', null, $rep);";
            }
        }
        $s .= 'return false;';
        return $s;
    }
}
///</summary>used in ajx context to pass the controller node that will be replaced on client side</summary>
/**
*/
final class IGKHtmlAJXCtrlReplacementNode extends IGKHtmlItem {
    private $m_ctrls;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("igk:replace-ctrl");
        $this->m_ctrls=array();
    }
    ///<summary></summary>
    ///<param name="option" default="null"></param>
    /**
    * 
    * @param mixed $option the default value is null
    */
    protected function __getRenderingChildren($option=null){
        $tab=array();
        foreach($this->m_ctrls as  $v){
            $t=$v->target ?? $v->ctrl->TargetNode;
            if($t->IsVisible){
                $tab[]=$t;
            }
        }
        return $tab;
    }
    ///<summary></summary>
    ///<param name="b"></param>
    ///<param name="target" default="null"></param>
    /**
    * 
    * @param mixed $b
    * @param mixed $target the default value is null
    */
    public function addCtrl($b, $target=null){
        $this->m_ctrls[$b->Name]=(object)["ctrl"=>$b, "target"=>$target];
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getCanAddChild(){
        return false;
    }
    ///<summary></summary>
    ///<param name="o" default="null" ref="true"></param>
    /**
    * 
    * @param  * $o the default value is null
    */
    protected function innerHTML(& $o=null){
        $so="";
        foreach($this->m_ctrls as  $v){
            $t=$v->target ?? $v->ctrl->TargetNode;
            if($t->IsVisible){
                $so .= $t->Render($o);
            }
        }
        return $so;
    }
}
///<summary>Represente class: IGKHtmlAJXReplacementNode</summary>
/**
* Represente IGKHtmlAJXReplacementNode class
*/
final class IGKHtmlAJXReplacementNode extends IGKHtmlItem{
    private $m_nodes;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("igk:replace-ctrl");
        $this->m_nodes=array();
    }
    ///<summary></summary>
    ///<param name="n"></param>
    ///<param name="tag" default="null"></param>
    /**
    * 
    * @param mixed $n
    * @param mixed $tag the default value is null
    */
    public function addNode($n, $tag=null){
        $this->m_nodes[]=$n;
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
    public function getChildCount(){
        return igk_count($this->m_nodes);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getChilds(){
        return $this->m_nodes;
    }
    ///<summary></summary>
    ///<param name="o" default="null" ref="true"></param>
    /**
    * 
    * @param  * $o the default value is null
    */
    protected function innerHTML(& $o=null){
        $so="";
        foreach($this->m_nodes as  $v){
            if($v->IsVisible)
                $so .= $v->Render($o);
        }
        return $so;
    }
}
///<summary>represent a tab control node where tab contains came from ajx query</summary>
/**
* represent a tab control node where tab contains came from ajx query
*/
final class IGKHtmlAJXTabControlItem extends IGKHtmlCtrlComponentNodeItemBase {
    private $m_selected;
    private $m_tabViewListener;
    private $m_tabcontent;
    private $m_tablist;
    private static $demoComponent;
    public const CONTROL = "AJXTabControl";

    public function getSelectedIndex(){
        return $this->m_selected;
    }

	public function getSettings($key){
		if ($this->m_tabViewListener){
			return $this->m_tabViewListener->getParam($key);
		}
		return "isnull";
	}
    ///<summary></summary>
    ///<param name="opt" default="null"></param>
    /**
    * 
    * @param mixed $opt the default value is null
    */
    public function __AcceptRender($opt=null){
        if($this->m_tabViewListener !== null){
            $this->m_tabViewListener->TabViewPage($this, $this->m_tablist, $this->m_tabcontent);
        }
        return parent::__AcceptRender($opt);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("div");
        $this->setClass("igk-tabcontrol");
        $h=$this->addDiv()->setClass("igk-tab-h");
        $ul=$h->add("ul");
        $this->m_tablist=$ul;
        $c=$this->addDiv();
        $this->m_tabcontent=$c;
        $this->m_tabcontent->setClass("igk-tabcontent");
    }
    ///<summary></summary>
    ///<param name="content" default="null"></param>
    ///<param name="uri" default="null"></param>
    ///<param name="active" default="false"></param>
    ///<param name="method" default="GET"></param>
    /**
    * 
    * @param mixed $content the default value is null
    * @param mixed $uri the default value is null
    * @param mixed $active the default value is false
    * @param mixed $method the default value is "GET"
    */
    public function addTabPage($content=null, $uri=null, $active=false, $method="GET"){
        $li=$this->m_tablist->add("li");
        $li->setParam("uri", $uri);
        $li->setParam("method", $method);
        $li->addA($uri)->setAttribute("igk-ajx-tab-lnk", 1)->setContent($content);
        if($active){
            if($this->m_selected){
                $this->m_selected->setClass("-igk-active");
            }
            $li->setClass("igk-active");
            $i=0;
            if($uri){
                $this->replaceContent($this->m_tabcontent, $uri, $method);
            }
            $this->m_selected=$li;
        }
        return $li;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function ClearChilds(){
        $this->m_tablist->ClearChilds();
        $this->m_tabcontent->ClearChilds();
    }
    ///<summary></summary>
    ///<param name="t"></param>
    /**
    * 
    * @param mixed $t
    */
    public function initDemo($t){
        $s=igk_get_component(__METHOD__);
        if($s){
            $s->Dispose();
        }
        $buri=igk_register_temp_uri(__CLASS__);
        $this->ClearChilds();
        $this->addTabPage("page1", $buri."/showpage/1", true);
        $this->addTabPage("page2", $buri."/showpage/2", false);
        $this->addTabPage("page3", $buri."/showpage/4", false);
        $i=$this->m_selected ? $this->m_selected: 1;
        $this->m_tabcontent->Content=igk_ob_get_func(array($this, "showpage"), array($i));
        $t->addDiv()->Content="Code Sample";
        $t->addDiv()->addCode()->setAttribute("igk-code", "php")->Content=<<<EOF
\$this->ClearChilds();
\$this->addTabPage("page1", \$this->getComponentUri("showpage/1"), true);
\$this->addTabPage("page2", \$this->getComponentUri("showpage/2"), false);
\$this->addTabPage("page3", \$this->getComponentUri("showpage/4"), false);
EOF;
        igk_reg_component(__METHOD__, $this);
    }
    ///<summary></summary>
    ///<param name="t"></param>
    ///<param name="uri"></param>
    ///<param name="method" default="'GET'"></param>
    /**
    * 
    * @param mixed $t
    * @param mixed $uri
    * @param mixed $method the default value is 'GET'
    */
    private function replaceContent($t, $uri, $method='GET'){
        $t->addBalafonJS(1)->Content=<<<EOF
(function(q){ igk.winui.controls.tabcontrol.init('$uri', q);})(igk.getParentScript());
EOF;
    }
    ///<summary></summary>
    ///<param name="i"></param>
    /**
    * 
    * @param mixed $i
    */
    public function select($i){ 
        if($this->m_selected){
            $this->m_selected->setClass("-igk-active");
        }
        $this->m_tabcontent->clearChilds();
        $li=$this->m_tablist->Childs[$i];
        if($li){
            $uri=$li->getParam("uri");
            $method=$li->getParam("method");
            $li->setClass("igk-active");
            if($uri){
                $this->replaceContent($this->m_tabcontent, $uri, $method);
            }
        }
    }
    ///<summary></summary>
    ///<param name="listener"></param>
    ///<param name="param" default="null"></param>
    /**
    * 
    * @param mixed $listener
    * @param mixed $param the default value is null
    */
    public function setComponentListener($listener, $param=null){}
    ///<summary></summary>
    ///<param name="o"></param>
    /**
    * 
    * @param mixed $o
    */
    public function setTabViewListener($o){
        $this->m_tabViewListener=$o;
    }
    ///<summary> , "for demonstration"</summary>
    /**
    *  , "for demonstration"
    */
    public function showpage($index=0){
        if($this->Ctrl){
            $this->Ctrl->showTabPage($index);
        }
        else{
            $d=igk_createNode("div");
            $d->Content="Demo page ".$index;
            $this->m_selected=$index;
            $d->RenderAJX();
        }
    }
}
///<summary>Represente class: IGKHtmlAJXViewItem</summary>
/**
* Represente IGKHtmlAJXViewItem class
*/
final class IGKHtmlAJXViewItem extends IGKHtmlDiv {
    private $m_lineWaiter;
    private $m_script;
    private $m_scriptfunction;
    private $m_uri;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("div");
        $this->m_lineWaiter=$this->addLineWaiter();
        $this->m_script=$this->addScript();
    }
    ///<summary></summary>
    ///<param name="options" default="null"></param>
    /**
    * 
    * @param mixed $options the default value is null
    */
    public function AcceptRender($options=null){
        if(!empty($this->m_uri)){
            $expr="";
            if($this->m_scriptfunction){
                $expr=", function(xhr) {{{$this->m_scriptfunction}}}";
            }
            $this->m_script->Content="\$ns_igk.winui.ajxview.init('{$this->m_uri}' $expr);";
        }
        else
            return IGK_STR_EMPTY;
        return parent::AcceptRender($options);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getScript(){
        return $this->m_scriptfunction;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getUri(){
        return $this->m_uri;
    }
    ///<summary></summary>
    ///<param name="script"></param>
    /**
    * 
    * @param mixed $script
    */
    public function setScript($script){
        $this->m_scriptfunction=$script;
        return $this;
    }
    ///<summary></summary>
    ///<param name="uri"></param>
    /**
    * 
    * @param mixed $uri
    */
    public function setUri($uri){
        $this->m_uri=$uri;
        return $this;
    }
}
///<summary>represent a basic node that will be rendered on ajx context only</summary>
/**
* represent a basic node that will be rendered on ajx context only
*/
class IGKHtmlAJXViewNodeItem extends IGKHtmlItem{
    ///<summary></summary>
    ///<param name="option" default="null"></param>
    /**
    * 
    * @param mixed $option the default value is null
    */
    protected function __AcceptRender($option=null){
        if(!igk_is_ajx_demand())
            return false;
        return parent::__AcceptRender($option);
    }
    ///<summary></summary>
    ///<param name="tag" default="null"></param>
    /**
    * 
    * @param mixed $tag the default value is null
    */
    public function __construct($tag=null){
        if($tag == null)
            $tag="igk:ajx-view-node";
        parent::__construct($tag);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getIsRenderTagName(){
        return "igk:ajx-view-node" != $this->TagName;
    }
}
///<summary>used to loading current inline script item</summary>
/**
* used to loading current inline script item
*/
final class IGKHtmlScriptInlineLoaderItem extends IGKHtmlAJXViewNodeItem{
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("script");
        $this["type"]="text/javascript";
        $this["language"]="javascript";
        $this->Content="igk.js.loadInlineScript();";
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getCanAddChild(){
        return false;
    }
}
///<summary>Represente class: IGKHtmlArticleConfigNode</summary>
/**
* Represente IGKHtmlArticleConfigNode class
*/
final class IGKHtmlArticleConfigNode extends IGKHtmlItem{
    private $m_ctrl;
    private $m_dropfileUri;
    private $m_filename;
    private $m_forceview;
    private $m_target;
    ///<summary></summary>
    ///<param name="ctrl" default="null"></param>
    ///<param name="target" default="null"></param>
    ///<param name="filename" default="null"></param>
    ///<param name="forceview"></param>
    /**
    * 
    * @param mixed $ctrl the default value is null
    * @param mixed $target the default value is null
    * @param mixed $filename the default value is null
    * @param mixed $forceview the default value is 0
    */
    public function __construct($ctrl=null, $target=null, $filename=null, $forceview=0){
        parent::__construct("div");
        $this->m_filename=$filename;
        $this->m_target=$target;
        $this->m_ctrl=$ctrl;
        $f=$filename;
        $this->m_forceview=$forceview;
        $this["class"]="igk-article-options";
        $this["igk-article-options"]="true";
        $this->Index=-9999;
        $config=igk_getctrl(IGK_CA_CTRL);
        $n=($ctrl) ? $ctrl->Name: "";
        if($config){
            IGKHtmlUtils::AddImgLnk($this, igk_js_post_frame($config->getUri("ca_edit_article_ajx&navigate=1&ctrlid=".$n."&m=1&fc=1&fn=".base64_encode($f)), $ctrl), "edit_16x16");
            IGKHtmlUtils::AddImgLnk($this, igk_js_post_frame($config->getUri("ca_add_article_frame_ajx&ctrlid=".$n."&m=1&fc=1&fn=".base64_encode($f)), $ctrl), "add_16x16");
            if(file_exists($f)){
                $this->m_dropfileUri=$config->getUri("ca_drop_article_ajx&navigate=1&ctrlid=".$n."&n=".base64_encode($f));
                IGKHtmlUtils::AddImgLnk($this, igk_js_post_frame(new IGKValueListener($this, "dropFileUri"), $ctrl), "drop_16x16")->setAlt("droparticle");
            }
        }
        else{
            $this->Content="no config article found";
        }
        $target->add($this);
        $this->setIndex(-1000);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getdropFileUri(){
        return $this->m_dropfileUri;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getIsVisible(){
        return $this->m_forceview || (parent::getIsVisible() && igk_app()->IsSupportViewMode(IGKViewMode::WEBMASTER));
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setdropFileUri($v){
        $this->m_dropfileUri=$v;
        return $v;
    }
}
///<summary>Represente class: IGKHtmlArticleViewItem</summary>
/**
* Represente IGKHtmlArticleViewItem class
*/
final class IGKHtmlArticleViewItem extends IGKHtmlCtrlNodeItemBase {
    private $m_InnerOnly;
    private $m_iview;
    private $m_row;
    private $m_view;
    ///<summary></summary>
    ///<param name="file" default="null"></param>
    ///<param name="ctrl" default="null"></param>
    ///<param name="row" default="null"></param>
    /**
    * 
    * @param mixed $file the default value is null
    * @param mixed $ctrl the default value is null
    * @param mixed $row the default value is null
    */
    public function __construct($file=null, $ctrl=null, $row=null){
        parent::__construct("div");
        $this->m_view=$file;
        $this->m_row=$row;
        $this->Ctrl=$ctrl;
    }
    ///<summary></summary>
    ///<param name="option" default="null"></param>
    /**
    * 
    * @param mixed $option the default value is null
    */
    protected function __getRenderingChildren($option=null){
        if($this->m_InnerOnly){
            return $this->Childs->ToArray();
        }
        return parent::__getRenderingChildren($option=null);
    }
    ///<summary></summary>
    ///<param name="o" default="null"></param>
    /**
    * 
    * @param mixed $o the default value is null
    */
    public function AcceptRender($options=null){
        if($this->m_iview){
            $this->initView();
        }
        return $this->IsVisible;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getFile(){
        return $this->m_view;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getInnerOnly(){
        return $this->m_InnerOnly;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getRow(){
        return $this->m_row;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function initView(){
        if(!$this->m_iview){
            return;
		}
        $this->m_iview=false;
        $this->ClearChilds();
        $c=igk_getctrl($this->getCtrl());
        $f=$this->m_view;
        $r=$this->m_row;
        if($c && $f){
            igk_html_binddata($c, $this, $f, $r, false, false);
        }
        else{
            if(IGKViewMode::IsWebMaster())
                $this->Content="no data to bind#[ctrl:{$this->getCtrl()},file:{$this->getFile()}, c:{$c}]";
        }
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setFile($v){
        $this->m_view=$v;
        $this->m_iview=true;
        return $this;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setInnerOnly($v){
        $this->m_InnerOnly=$v;
        $this->m_iview=true;
        return $this;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setRow($v){
        $this->m_row=$v;
        $this->m_iview=true;
        return $this;
    }
}
///<summary>Represente class: IGKHtmlAuthorizationNodeItem</summary>
/**
* Represente IGKHtmlAuthorizationNodeItem class
*/
class IGKHtmlAuthorizationNodeItem extends IGKHtmlItem {
    private $m_AccessKey;
    private $m_authCtrl;
    ///<summary></summary>
    ///<param name="tag" default="null"></param>
    ///<param name="accesskey" default="null"></param>
    ///<param name="authCtrl" default="null"></param>
    /**
    * 
    * @param mixed $tag the default value is null
    * @param mixed $accesskey the default value is null
    * @param mixed $authCtrl the default value is null
    */
    public function __construct($tag=null, $accesskey=null, $authCtrl=null){
        parent::__construct($tag);
        $this->m_authCtrl=$authCtrl;
        $this->m_AccessKey=$accesskey;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getAccessKey(){
        return $this->m_AccessKey;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getAuthCtrl(){
        return $this->m_authCtrl;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getIsVisible(){
        $c=$this->m_AccessKey;
        if(empty($c))
            return false;
        return igk_sys_authorize($c, $this->m_authCtrl);
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setAccessKey($v){
        $this->m_AccessKey=$v;
        return $this;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setAuthCtrl($v){
        $this->m_authCtrl=$v;
        return $this;
    }
}
///<summary>Represente class: IGKHtmlBindDataNodeItem</summary>
/**
* Represente IGKHtmlBindDataNodeItem class
*/
class IGKHtmlBindDataNodeItem extends IGKHtmlCtrlNodeItemBase{
    private $m_File;
    private $m_Row;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("div");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getFile(){
        return $this->m_File;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getRow(){
        return $this->m_Row;
    }
    ///<summary></summary>
    ///<param name="t"></param>
    /**
    * 
    * @param mixed $t
    */
    public function initProperties($t){
        foreach($t as $k=>$v){
            $this->$k=$v;
        }
    }
    ///<summary></summary>
    ///<param name="options" default="null"></param>
    /**
    * 
    * @param mixed $options the default value is null
    */
    public function render($options=null){
        $this->ClearChilds();
        igk_html_binddata($this->Ctrl, $this, $this->File, $this->Row, false);
        return parent::Render($options);
    }
    ///<summary></summary>
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $value
    */
    public function setFile($value){
        $this->m_File=$value;
    }
    ///<summary></summary>
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $value
    */
    public function setRow($value){
        $this->m_Row=$value;
    }
}
///<summary>Represente class: IGKHtmlBreadCrumbsItem</summary>
/**
* Represente IGKHtmlBreadCrumbsItem class
*/
final class IGKHtmlBreadCrumbsItem extends IGKHtmlItem{
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("ol");
        $this["class"]="igk-breadcrumbs";
    }
    ///<summary></summary>
    ///<param name="uri"></param>
    ///<param name="content"></param>
    /**
    * 
    * @param mixed $uri
    * @param mixed $content
    */
    public function addItem($uri, $content){
        $li=$this->add("li");
        $li->addA($uri)->Content=$content;
        return $li;
    }
}
///<summary>Represente class: IGKHtmlCanvaNodeItem</summary>
/**
* Represente IGKHtmlCanvaNodeItem class
*/
final class IGKHtmlCanvaNodeItem extends IGKHtmlItem {
    private $m_uri;
    ///<summary></summary>
    ///<param name="ctrl" default="null"></param>
    /**
    * 
    * @param mixed $ctrl the default value is null
    */
    public function __construct($ctrl=null){
        parent::__construct("canvas");
        $this->m_ctrl=$ctrl;
        $this["width"]="320px";
        $this ["height"]="500px;";
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getUri(){
        return $this->m_uri;
    }
    ///<summary></summary>
    ///<param name="xmlOption" default="null" ref="true"></param>
    /**
    * 
    * @param  * $xmlOption the default value is null
    */
    protected function innerHTML(& $xmlOption=null){
        if($this->m_uri){
            $o=parent::innerHTML($xmlOption);
            $script=igk_createNode("script");
            $script->Content="window.igk.winui.canva.initctrl('".$this->m_uri."');";
            $o .= $script->Render();
            unset($script);
            return $o;
        }
        return null;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setUri($v){
        $this->m_uri=$v;
    }
}
///<summary>Represente class: IGKHtmlChildNodeViewItem</summary>
/**
* Represente IGKHtmlChildNodeViewItem class
*/
final class IGKHtmlChildNodeViewItem extends IGKHtmlItem {
    private $m_basic;
    ///<summary></summary>
    ///<param name="basicTag" default="null"></param>
    /**
    * 
    * @param mixed $basicTag the default value is null
    */
    public function __construct($basicTag=null){
        parent::__construct("igk:childnodeview");
        $this->m_basic=$basicTag;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function AddChild(){
        if($this->m_basic){
            return $this->add($this->m_basic);
        }
        return null;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getBasicTag(){
        return $this->m_basic;
    }
    ///<summary></summary>
    ///<param name="o" default="null"></param>
    /**
    * 
    * @param mixed $o the default value is null
    */
    public function render($o=null){
        $k=0;
        return $this->__renderVisibleChild($this->__getRenderingChildren($option=null), $o, $k);
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setBasicTag($v){
        $this->m_basic=$v;
        return $this;
    }
}
///<summary>Represente class: IGKHtmlCodeViewerItem</summary>
/**
* Represente IGKHtmlCodeViewerItem class
*/
final class IGKHtmlCodeViewerItem extends IGKHtmlItem {
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("code");
        $this->language="php";
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getLanguage(){
        return $this["igk-type"];
    }
    ///<summary></summary>
    ///<param name="t"></param>
    /**
    * 
    * @param mixed $t
    */
    public function initDemo($t){
        $this->Language="php";
        $this["class"]="phpcode";
        $this->Content=<<<EOF
<?php
echo 'hello the sample';
?>
EOF;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setLanguage($v){
        $this["igk-type"]=$v;
        return $this;
    }
}
///<summary>Represente class: IGKHtmlCommentItem</summary>
/**
* Represente IGKHtmlCommentItem class
*/
final class IGKHtmlCommentItem extends IGKHtmlItem {
    private $forRendering;
    ///<summary></summary>
    ///<param name="value" default="null"></param>
    /**
    * 
    * @param mixed $value the default value is null
    */
    public function __construct($value=null){
        parent::__construct("igk-comment");
        $this->Content=$value;
    }
    ///<summary>display value</summary>
    /**
    * display value
    */
    public function __toString(){
        return "IGKHtmlComment[".$this->Content."]";
    }
    ///<summary></summary>
    ///<param name="item"></param>
    ///<param name="index" default="null"></param>
    /**
    * 
    * @param mixed $item
    * @param mixed $index the default value is null
    */
    protected function _AddChild($item, $index=null){
        return false;
    }
    ///<summary></summary>
    ///<param name="o" default="null"></param>
    /**
    * 
    * @param mixed $o the default value is null
    */
    public function AcceptRender($options=null){
        parent::__AcceptRender();
        $this->forRendering=parent::AcceptRender($options);
        return $this->forRendering;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getContent(){
        if($this->forRendering)
            return '<!--  '.parent::getContent().' -->';
        return parent::getContent();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getHasAttributes(){
        return false;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getIsRenderTagName(){
        return false;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getNodeType(){
        return IGKXMLNodeType::COMMENT;
    }
    ///<summary></summary>
    ///<param name="o" default="null"></param>
    /**
    * 
    * @param mixed $o the default value is null
    */
    public function RenderComplete($o=null){
        $this->forRendering=false;
    }
}
///<summary>Represente class: IGKHtmlConnectFormItem</summary>
/**
* Represente IGKHtmlConnectFormItem class
*/
final class IGKHtmlConnectFormItem extends IGKHtmlCtrlComponentNodeItemBase {
    private $m_badUri;

	/** @var IGKHtmlItem */
    private $m_frm;
    private $m_goodUri;
    /** @var BaseController */
    private $Ctrl;
    ///<summary></summary>
    ///<param name="type" default="email"></param>
    /**
    * 
    * @param mixed $type the default value is "email"
    */
    public function __construct($type="email"){
        parent::__construct("div");
        $this->Ctrl= igk_getctrl(IGK_USER_CTRL);
        $this->m_loginType=$type;
        $this->setClass("igk-connect-form");
        $this->m_frm=parent::addForm();
        $this->m_frm["action"]=$this->getComponentUri("connect");
        $this->m_frm["igk-form-validate"]=1;
        $i=$this->m_frm->addDiv()->addSLabelInput("clLogin", $type);
        $i->input["igk-input-focus"]=1;
        $this->m_frm->addDiv()->addSLabelInput("clPwd", "password");
        $d=$this->m_frm->addDiv();
        $m=$d->addDiv();
        $m->addInput("clRememberMe", "checkbox", "rm-me")->setAttribute("checked", 1);
        $m->addLabel("lb.clRememberMe", "clRememberMe");
        $m->addA("signup")->setStyle("padding-left:8px")->Content=R::ngets("lb.register");
        $d=$this->m_frm->addDiv();
        $d->addDiv()->addInput("bnt_connect", "submit", R::ngets("btn.connect"))->setClass("igk-btn igk-btn-default igk-sm-fitw");
    }
    ///<summary></summary>
    ///<param name="nameortag"></param>
    ///<param name="attribute" default="null"></param>
    ///<param name="index" default="null"></param>
    /**
    * 
    * @param mixed $nameortag
    * @param mixed $attribute the default value is null
    * @param mixed $index the default value is null
    */
    public function add($nameortag, $attribute=null, $index=null){
        if($this->m_frm != null)
            return $this->m_frm->add($nameortag, $index);
        return parent::add($nameortag, $attribute, $index);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function ClearChilds(){
        $this->m_frm->ClearChilds();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function connect(){
        $c=$this->Ctrl;
        $e='#!\e=';
        if($c){
            $b=$c->connect();
            if($b){
                if($this->m_goodUri)
                    igk_navto($this->m_goodUri);
            }
            else{
                $e .= 1;
                igk_notifyctrl("login")->addErrorr("e.login.failed_1", "#failed");
                if($this->m_badUri){
                    igk_navto($this->m_badUri.$e);
                }
            }
        }
        else{
            igk_wln("no  controller setup");
        }
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getBadUri(){
        return $this->m_badUri;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getForm(){
        return $this->m_frm;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getGoodUri(){
        return $this->m_goodUri;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getloginType(){
        return $this->m_loginType;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setBadUri($v){
        $this->m_badUri=$v;
        return $this;
    }
    ///<summary></summary>
    ///<param name="listener"></param>
    ///<param name="param" default="null"></param>
    /**
    * 
    * @param mixed $listener
    * @param mixed $param the default value is null
    */
    public function setComponentListener($listener, $param=null){}
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setGoodUri($v){
        $this->m_goodUri=$v;
        return $this;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setloginType($v){
        $this->m_loginType=$v;
        return $this;
    }
}
///<summary> used in article to setup controller item
/**
*  used in article to setup controller item
*/
final class IGKHtmlContactFormItem extends IGKHtmlComponentNodeItem {
    private $m_msbox;
    private $m_uri;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("form");
        $this["method"]="POST";
        // $this["enctype"]="multipart/form-data";
        $this->m_msbox=$this->addDiv();
        $i=$this->addSLabelInput("clYourName", "text");
        $i->input->setClass("igk-form-control igk-form-required");
        $i=$this->addSLabelInput("clEmail", "email");
        $i->input->setClass("igk-form-control igk-form-required");
        $i=$this->addSLabelInput("clSubject", "text");
        $i->input->setClass("igk-form-control igk-form-required");
        $i=$this->addSLabelTextarea("clMessage", "text");
        $i->textarea->setClass("igk-form-control");
        $this->addInput("btn_s", "submit", R::ngets("btn.sendMessage"))->setClass("igk-btn igk-btn-default floatr");
        $this["action"]=$this->getComponentUri("sendmsg");
        $this["igk-ajx-form"]="1";
        $this["igk-ajx-form-target"]="=";
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function getUri($v){
        return $this->m_uri;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function sendmsg(){
        $o=igk_get_robj();
        $this->m_msbox->ClearChilds();
        if(!igk_html_validate($o, array("clEmail"=>"mail"))){
            $this->m_msbox->addNotifyBox("danger")->Content= __("send message failed");
        }
        else{
            $this->m_msbox->Content=null;
            $conf=$this->getController()->App->Configs;
            $m=$conf->mail_contact;
            $d=igk_createNode("div");
            $d->addDiv()->Content="From : ".$o->clYourName;
            $d->addDiv()->Content=$o->clMessage;
            $smg=$d->Render(null);
            $b=igk_mail_sendmail($m, "no-reply@".$conf->website_domain, $o->clSubject, $smg);
            if($b){
                $this->m_msbox->addNotifyBox("success")->setAutohide(true)->Content=R::ngets("msg.message.send");
            }
            igk_resetr();
        }
        $this->RenderAJX();
        igk_exit();
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setUri($v){
        $this->m_uri=$v;
        return $this;
    }
}
///<summary>used to select properties from data table</summary>
/**
* used to select properties from data table
*/
final class IGKHtmlCtrlSelectItem extends IGKHtmlCtrlNodeItemBase {
    private $m_condition;
    private $m_selected;
    private $m_table;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("select");
        $this["class"]="igk-form-control";
    }
    ///<summary></summary>
    ///<param name="expression"></param>
    ///<param name="entries"></param>
    /**
    * 
    * @param mixed $expression
    * @param mixed $entries
    */
    protected function bindExpression($expression, $entries){
        $c=new IGKHtmlItem("dummy");
        $c->LoadExpression($expression);
        igk_html_bind_node($this->Ctrl, $c, $this, $entries);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getSelected(){
        return $this->m_selected;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getTable(){
        return $this->m_table;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function initView(){
        $this->ClearChilds();
        $t=$this->m_table;
        $c=$this->Ctrl;
        $r=igk_db_table_select_where($t, $this->m_condition, $c);
        if($r != null && ($r->RowCount > 0)){
            $display="{\$row->clName}";
            $sel=$this->m_selected;
            $h="";
            if($sel){
                $h="[func:\$row->clId== '$sel'?'selected=\"true\"' :null]";
            }
            $this->bindExpression("<option value=\"{\$row->clId}\" $h >{$display}</option>", $r->Rows);
        }
    }
    ///<summary></summary>
    ///<param name="cond"></param>
    /**
    * 
    * @param mixed $cond
    */
    public function setCondition($cond){
        if(is_array($cond) || ($cond == null)){
            $this->m_condition=$cond;
        }
        return $this;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setSelected($v){
        $this->m_selected=$v;
        return $this;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setTable($v){
        $this->m_table=$v;
        return $this;
    }
}
///<summary>represent the base contrtoller node. item</summary>
/**
* represent the base contrtoller node. item
*/
final class IGKHtmlCtrlViewNodeItem extends IGKHtmlCtrlNodeItemBase {
    private $m_content;
    private $m_optionsTag;
    ///<summary></summary>
    ///<param name="tagName" default="null"></param>
    /**
    * 
    * @param mixed $tagName the default value is null
    */
    public function __construct($tagName=null){
        if($tagName == null)
            $tagName=igk_sys_getconfig("app_default_controller_tag_name", "div");
        parent::__construct($tagName);
        $this->m_content=igk_html_node_NoTagNode();
        $this->m_optionsTag=igk_html_node_webMasterNode();
        $this->m_optionsTag->setClass("igk-ctrl-view-node-options");
        parent::add($this->m_optionsTag);
        parent::add($this->m_content);
    }
    ///<summary></summary>
    ///<param name="o" default="null"></param>
    /**
    * 
    * @param mixed $o the default value is null
    */
    public function AcceptRender($options=null){
        igk_wln("accept render .".$this->IsVisible);
        return $this->IsVisible;
    }
    ///<summary></summary>
    ///<param name="nameorchilds"></param>
    ///<param name="attributes" default="null"></param>
    ///<param name="index" default="null"></param>
    /**
    * 
    * @param mixed $nameorchilds
    * @param mixed $attributes the default value is null
    * @param mixed $index the default value is null
    */
    public function add($nameorchilds, $attributes=null, $index=null){
        return $this->m_content->add($nameorchilds, $attributes, $index);
    }
    ///<summary></summary>
    ///<param name="child"></param>
    /**
    * 
    * @param mixed $child
    */
    public function attachChild($child){
        $this->m_content->attachChild($child);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function ClearChilds(){
        $this->m_content->ClearChilds();
    }
    ///<summary></summary>
    ///<param name="child"></param>
    /**
    * 
    * @param mixed $child
    */
    public function detachChild($child){
        $this->m_content->detachChild($child);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getOptionNode(){
        return $this->m_optionsTag;
    }
    ///<summary></summary>
    ///<param name="options" default="null" ref="true"></param>
    /**
    * 
    * @param  * $options the default value is null
    */
    protected function innerHTML(& $options=null){
        igk_wln("inner ...");
        $o="";
        $o .= $this->m_content->getinnerHTML($options);
        if($this->m_optionsTag->getIsVisible()){
            $o .= $this->m_optionsTag->getinnerHTML($options);
        }
        return $o;
    }
    ///<summary></summary>
    ///<param name="childs"></param>
    ///<param name="setParent" default="1"></param>
    /**
    * 
    * @param mixed $childs
    * @param mixed $setParent the default value is 1
    */
    public function remove($childs=null, $setParent=1){
        $this->m_content->remove($childs, $setParent);
    }
    ///<summary></summary>
    ///<param name="o" default="null"></param>
    /**
    * 
    * @param mixed $o the default value is null
    */
    public function RenderComplete($o=null){}
}
///<summary>Represente class: IGKHtmlCurrentUserInfoItem</summary>
/**
* Represente IGKHtmlCurrentUserInfoItem class
*/
final class IGKHtmlCurrentUserInfoItem extends IGKHtmlItem {
    private $m_display;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("span");
        $this["class"]="igk-u-inf";
        $this->m_display="clLogin";
    }
    ///<summary></summary>
    ///<param name="o" default="null"></param>
    /**
    * 
    * @param mixed $o the default value is null
    */
    public function AcceptRender($options=null){
        $u=IGKApp::getInstance()->Session->User;
        if($u == null)
            return false;
        $d=$this->Display;
        $this->Content=$u->$d;
        return parent::getIsVisible();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDisplay(){
        return $this->m_display;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setDisplay($v){
        $this->m_display=$v;
        return $this;
    }
}
///<summary>Represente class: IGKHtmlDataEntryItem</summary>
/**
* Represente IGKHtmlDataEntryItem class
*/
final class IGKHtmlDataEntryItem extends IGKHtmlDiv {
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct();
    }
    ///<summary></summary>
    ///<param name="r"></param>
    ///<param name="visibleName" default="null"></param>
    /**
    * 
    * @param mixed $r
    * @param mixed $visibleName the default value is null
    */
    public function LoadData($r, $visibleName=null){
        if($r == null)
            return;
        if(method_exists(get_class($r), "getRows")){
            foreach($r->Rows as  $v){
                $i=$this->add("item");
                $i->setAttributes($v);
                if($visibleName)
                    $i->Content=$v->$visibleName;
            }
        }
        else{
            $i=$this->add("item");
            $i->setAttributes($r);
            if($visibleName)
                $i->Content=$r->$visibleName;
        }
    }
    ///<summary></summary>
    ///<param name="options" default="null"></param>
    /**
    * 
    * @param mixed $options the default value is null
    */
    public function render($options=null){
        return parent::innerHTML($options);
    }
}
///<summary>Represente class: IGKHtmlDatePickerItem</summary>
/**
* Represente IGKHtmlDatePickerItem class
*/
final class IGKHtmlDatePickerItem extends IGKHtmlItem{
    ///<summary></summary>
    ///<param name="id" default="null"></param>
    /**
    * 
    * @param mixed $id the default value is null
    */
    public function __construct($id=null){
        parent::__construct("div");
        if($id){
            $this->_initview($id);
        }
    }
    ///<summary></summary>
    ///<param name="id"></param>
    /**
    * 
    * @param mixed $id
    */
    private function _initview($id){
        $this->addLabel($id);
        $this->addInput($id, "text", date('Y/m/d'));
    }
    ///<summary></summary>
    ///<param name="t"></param>
    /**
    * 
    * @param mixed $t
    */
    public function initDemo($t){
        $this->_initview("sample");
    }
}
///<summary>Empty tag node item. for img private sample</summary>
/**
* Empty tag node item. for img private sample
*/
final class IGKHtmlEmptyTagNodeItem extends IGKXmlNode{
    ///<summary></summary>
    ///<param name="n"></param>
    /**
    * 
    * @param mixed $n
    */
    function __construct($n){
        parent::__construct($n);
    }
}
///<summary>Represente class: IGKHtmlExpressionNodeItem</summary>
/**
* Represente IGKHtmlExpressionNodeItem class
*/
class IGKHtmlExpressionNodeItem extends IGKHtmlItem{
    var $ctrl;
    var $raw;
    var $setting;
    ///<summary></summary>
    ///<param name="args" default="null"></param>
    ///<param name="ctrl" default="null"></param>
    /**
    * 
    * @param mixed $args the default value is null
    * @param mixed $ctrl the default value is null
    */
    public function __construct($args=null, $ctrl=null){
        parent::__construct("igk:expression-node");
        $this->raw=$args;
        $this->ctrl=$ctrl;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getIsRenderTagName(){
        return false;
    }
    ///<summary></summary>
    ///<param name="options" default="null"></param>
    /**
    * 
    * @param mixed $options the default value is null
    */
    public function render($options=null){
        $script_obj=igk_html_databinding_getobjforscripting($this->ctrl);
        $_e=html_entity_decode($this["expression"]);
        $shift=0;
        if($_e[0] != "@"){
            if($script_obj->Count() > 1){
                $script_obj->shiftParent();
                $shift=1;
            }
        }
        while($_e[0] == "@"){
            $_e=substr($_e, 1);
        }
        if(empty($_e=trim($_e))){
            return "";
        }
        $sout=igk_html_databinding_treatresponse($_e, $this->ctrl, $this->raw, null);
        if($shift){
            $script_obj->resetShift();
        }
        return $sout;
    }
}
///<summary>Represente class: IGKHtmlFooterFixedBoxItem</summary>
/**
* Represente IGKHtmlFooterFixedBoxItem class
*/
final class IGKHtmlFooterFixedBoxItem extends IGKHtmlItem {
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("div");
        $this->setClass("posfix loc_b loc_l loc_r");
        $this->setAttribute("igk-js-fix-loc-scroll-width", "1");
    }
}
///<summary>Represente class: IGKHtmlGKDSNodeItem</summary>
/**
* Represente IGKHtmlGKDSNodeItem class
*/
final class IGKHtmlGKDSNodeItem extends IGKHtmlItem {
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("igk:gkds");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getUri(){
        return $this["src"];
    }
    ///<summary></summary>
    ///<param name="t"></param>
    /**
    * 
    * @param mixed $t
    */
    public function initDemo($t){
        $t->addA("#")->Content="PickImage";
        $this["src"]=igk_html_uri(igk_io_baseUri()."/".igk_io_basePath(dirname(__FILE__)."/Data/R/demo.gkds"));
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setUri($v){
        $this["src"]=$v;
    }
}
///<summary>Represente class: IGKHtmlGroupControlItem</summary>
/**
* Represente IGKHtmlGroupControlItem class
*/
final class IGKHtmlGroupControlItem extends IGKHtmlItem{
    ///<summary></summary>
    ///<param name="name" default="null"></param>
    /**
    * 
    * @param mixed $name the default value is null
    */
    public function __construct($name=null){
        parent::__construct("div");
        $this["class"]="igk-form-group";
    }
}
///<summry> represent a select element </summary>
///<summary>represent horizontal menu item</summary>
/**
* represent horizontal menu item
*/
final class IGKHtmlHMenuItem extends IGKHtmlItem {
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("ul");
        $this["class"]="igk-horizontal-menu";
    }
    ///<summary></summary>
    ///<param name="name"></param>
    ///<param name="link" default="null"></param>
    ///<param name="content" default="null"></param>
    /**
    * 
    * @param mixed $name
    * @param mixed $link the default value is null
    * @param mixed $content the default value is null
    */
    public function addItem($name, $link=null, $content=null){
        $li=$this->add("li");
        $li->setId($name);
        if($link){
            $li->addA($link)->setContent($content);
        }
        else if($content){
            $li->setContent($content);
        }
        return $li;
    }
}
///<summary>Represente class: IGKHtmlHomeButtonItem</summary>
/**
* Represente IGKHtmlHomeButtonItem class
*/
final class IGKHtmlHomeButtonItem extends IGKHtmlItem {
    private $m_targetid;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("div");
        $this->m_targetid="home";
        $this->setClass("igk-gohome-btn igk-trans-all-200ms")->setAttribute("igk-js-eval", "igk.winui.fn.fixscroll.init({update: function(v){ if(v.scroll.y > 0) { this.addClass('igk-show'); }  else this.rmClass('igk-show') ;}});");
        $this->addA("#")->setClass("igk-glyph-home dispb fitw fith")->setAttribute("igk-nav-link", $this->m_targetid)->addCenterBox()
        ->getBox()->setClass("fith")->add("span")->setClass("glyphicons")->Content=" &#xe021;";
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getTargetId(){
        return $this->m_targetid;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getTargetLink(){
        return "#!/".$this->m_targetid;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setTargetId($v){
        $this->m_targetid=$v;
        return $this;
    }
}
///<summary>Represent IGK App HeaderBar Item</summmary>
/**
* Represent IGK App HeaderBar Item
*/
final class IGKHtmlIGKAppHeaderBarItem extends IGKHtmlItem {
    private $m_Box;
    private $m_apps;
    ///<summary></summary>
    ///<param name="app"></param>
    /**
    * 
    * @param mixed $app
    */
    public function __construct($app){
        if($app == null)
            igk_die("argument exception \$app is null");
        parent::__construct("div");
        $this->m_apps=$app;
        $this["class"]="igk-app-header-bar";
        $this->initView();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getBox(){
        return $this->m_Box;
    }
    ///<summary></summary>
    ///<param name="t"></param>
    /**
    * 
    * @param mixed $t
    */
    public function initDemo($t){}
    ///<summary></summary>
    /**
    * 
    */
    public function initView(){
        $this->ClearChilds();
        $r=$this->addRow()->setClass("no-margin");
        $h1=$r->addDiv()->setClass(" igk-col-lg-12-2 fith floatl presentation");
        $title=$h1->addDiv()->addA(igk_io_baseUri())->setClass("dispb no-decoration");
        $title->add("span")->setClass("dispib posr")->setStyle("left:10px; top:12px;")->Content="igkdev";
        $title->addDiv()->setClass("igk-title-4")->Content=$this->m_apps->AppTitle;
        $this->m_Box=$r->addDiv();
        $this->m_Box->setClass("igk-col-lg-12-10 .ibox");
    }
}
///<summary>Represente class: IGKHtmlIGKHeaderBarItem</summary>
/**
* Represente IGKHtmlIGKHeaderBarItem class
*/
class IGKHtmlIGKHeaderBarItem extends IGKHtmlItem {
    private $m_Box;
    private $m_title;
    private $m_uri;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("div");
        $this->setClass("igk-add-margin");
        $this->m_uri=igk_io_baseDomainUri();
        $this->initView();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getBox(){
        return $this->m_Box;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getCompanyTitle(){
        return igk_getv(IGKApp::getInstance()->Configs, "company_name", IGK_COMPANY);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getTitle(){
        return $this->m_title;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getUri(){
        return $this->m_uri;
    }
    ///<summary></summary>
    ///<param name="t"></param>
    /**
    * 
    * @param mixed $t
    */
    public function initDemo($t){}
    ///<summary></summary>
    /**
    * 
    */
    public function initView(){
        $this->ClearChilds();
        $r=$this->addRow();
        $h1=$r->addDiv()->setClass(" igk-col-lg-12-2 fith floatl presentation");
        $title=$h1->addDiv()->addA(new IGKValueListener($this, "Uri"))->setClass("dispb no-decoration");
        $title->add("span")->setClass("dispib posr")->setStyle("left:10px; top:12px;")->Content=new IGKValueListener($this, "CompanyTitle");
        $title->addDiv()->setClass("igk-title-4")->Content=new IGKValueListener($this, "Title");
        $this->m_Box=$r->addDiv();
        $this->m_Box->setClass("igk-col-lg-12-10 ibox");
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setTitle($v){
        $this->m_title=$v;
        return $this;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setUri($v){
        $this->m_uri=$v;
        return $this;
    }
}
///<summary>Represente class: IGKHtmlIGKSysHeaderBarItem</summary>
/**
* Represente IGKHtmlIGKSysHeaderBarItem class
*/
final class IGKHtmlIGKSysHeaderBarItem extends IGKHtmlIGKHeaderBarItem {
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getCompanyTitle(){
        return IGK_COMPANY;
    }
}
///<summary>Represente class: IGKHtmlImgItem</summary>
/**
* Represente IGKHtmlImgItem class
*/
class IGKHtmlImgItem extends IGKHtmlItem {
    private $m_imageSrcEval;
    private $m_img;
    private $m_lnk;
    ///<summary></summary>
    ///<param name="uri" default="null"></param>
    /**
    * 
    * @param mixed $uri the default value is null
    */
    public function __construct($uri=null){
        $this->m_img=igk_html_node_innerImg();
        parent::__construct("igk-html-image");
        $this->m_imageSrcEval=new IGKHtmlImgEvalSrc($this, $this->m_img);
        $this->m_lnk=null;
        $this->m_img["src"]=$this->m_imageSrcEval;
        $this->m_img["alt"]=null;
        $this->m_lnk=$uri;
    }
    ///<summary></summary>
    ///<param name="o" default="null"></param>
    /**
    * 
    * @param mixed $o the default value is null
    */
    protected function __getRenderingChildren($o=null){
        return array($this->m_img);
    }
    ///<summary>display value</summary>
    /**
    * display value
    */
    public function __toString(){
        return get_class($this)."#[".$this->TagName."] Attributes : [".$this->Attributes->Count."] ; Childs : [ ".$this->ChildCount." ]";
    }
    ///<summary></summary>
    ///<param name="tag"></param>
    /**
    * 
    * @param mixed $tag
    */
    protected function _p_isClosedTag($tag){
        switch(strtolower($tag)){
            case "igk-html-image":
            case "igk-img":
            case "img":
            case "image":
            return true;
        }
        return false;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getIsRenderTagName(){
        return false;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getSrc(){
        return $this->m_lnk;
    }
    ///<summary></summary>
    ///<param name="loadedTag"></param>
    /**
    * 
    * @param mixed $loadedTag
    */
    public function IsLoadedClosedTag($loadedTag){
        switch(strtolower($loadedTag)){
            case "igk-html-image":
            case "igk-img":
            case "img":
            case "image":
            return true;
        }
        return false;
    }
    ///<summary></summary>
    ///<param name="key"></param>
    /**
    * 
    * @param mixed $key
    */
    public function offsetGet($key){
        if(strtolower($key) == "src")
            return $this->m_lnk;
        return $this->m_img->offsetGet($key);
    }
    ///offsetSet src
    /**
    * @return mixed
    */
    public function offsetSet($key, $value){
        if($key == "igk:src"){
            $c=realpath($value);
            if(!empty($c)){
                $this->setSrc($c);
                return $this;
            }
        }
        if(IGKString::StartWith($key, 'igk:')){
            return parent::offsetSet($key, $value);
        }
        if(strtolower($key) == "src"){
            $s=IGKValidator::IsUri($value) ? $value: igk_html_rm_base_uri($value);
            $this->setSrc($s);
            return $this;
        }
        if($value === $this->getStyle()){
            parent::offsetSet($key, $value);
        }
        else{
            $this->m_img->offsetSet($key, $value);
        }
        return $this;
    }
    ///<summary></summary>
    ///<param name="option" default="null"></param>
    /**
    * 
    * @param mixed $option the default value is null
    */
    public function RenderComplete($option=null){}
    ///<summary></summary>
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $value
    */
    public function setSrc($value){
        if(empty($value))
            return $this;
        $this->m_lnk=$value;
        return $this;
    }
}
///<summary>Represente class: IGKHtmlImgLnkItem</summary>
/**
* Represente IGKHtmlImgLnkItem class
*/
final class IGKHtmlImgLnkItem extends IGKHtmlA{
    private $m_img;
    ///<summary></summary>
    ///<param name="uri" default="null"></param>
    ///<param name="img" default="null"></param>
    ///<param name="width" default="16px"></param>
    ///<param name="height" default="16px"></param>
    ///<param name="desc" default="null"></param>
    /**
    * 
    * @param mixed $uri the default value is null
    * @param mixed $img the default value is null
    * @param mixed $width the default value is "16px"
    * @param mixed $height the default value is "16px"
    * @param mixed $desc the default value is null
    */
    public function __construct($uri=null, $img=null, $width="16px", $height="16px", $desc=null){
        parent::__construct();
        $this["href"]=$uri;
        $this->m_img=$this->add("img", array(
            "width"=>$width,
            "height"=>$height,
            "src"=>R::GetImgUri(trim($img)),
            "alt"=>R::ngets($desc)
        ));
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getAlt(){
        return $this->m_img["alt"];
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setAlt($v){
        $this->m_img["alt"]=$v;
        return $this;
    }
}
///<summary> represent a component to callback actions</summary>
/**
*  represent a component to callback actions
*/
class IGKHtmlItemComponentCallbackItem extends IGKHtmlComponentNodeItem{
    var $args;
    var $callback;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("div");
    }
    ///<summary></summary>
    ///<param name="o" default="null"></param>
    /**
    * 
    * @param mixed $o the default value is null
    */
    public function AcceptRender($options=null){
        return false;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function doaction(){
        $c=$this->callback;
        if($c){
            call_user_func_array($c, $this->args ? $this->args: array());
        }
        if(igk_is_ajx_demand()){
            igk_exit();
        }
    }
}
///<summary>Action bar that is fixed on the document</summary>
/**
* Action bar that is fixed on the document
*/
final class IGKHtmlJSMsBoxItem extends IGKHtmlScript {
    var $m_content;
    var $m_title;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct();
        $this->setClass("igk-js-msbox");
        $this->m_content=igk_createNode("div");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getContent(){
        return $this->m_content;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getTitle(){
        return $this->m_title;
    }
    ///<summary></summary>
    ///<param name="o" default="null"></param>
    /**
    * 
    * @param mixed $o the default value is null
    */
    public function render($o=null){
        $title=$this->Title;
        $content=$this->m_content->Render(null);
        return <<<OF
<script type="text/javascript" language="javascript" >
igk.winui.notify.showMsBox('{$title}', '{$content}');
</script>
OF;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setTitle($v){
        $this->m_title=$v;
    }
}
///<summary>represent language selection options</items>
/**
* represent language selection options
*/
final class IGKHtmlMemoryUsageInfoNodeItem extends IGKHtmlComponentNodeItem {
    public function & getSetting(){
        $m = [];
        return $m;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("div");
        $this->add("div")->Content=new IGKValueListener($this, "MemoryInUsed");
        $this->add("div")->Content=new IGKValueListener($this, "MemoryPeekInUsed");
        $this->add("div")->Content=new IGKValueListener($this, "Components");
        // $b=$this->addActionBar();
        // $b->addABtn($this->getComponentUri("clear_component"))->setClass("igk-btn igk-btn-default")->Content=R::ngets("btn.clearcomponent");
        // $b->addAJXA($this->getComponentUri("component_info_ajx"))->setClass("igk-btn igk-btn-default")->Content=R::ngets("btn.getcomponentinfo");
        // $uri=$this->getComponentUri("memoryinfo");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function clear_component(){
        igk_getctrl(IGK_COMPONENT_MANAGER_CTRL)->DisposeAll();
        session_destroy();
        igk_navtobaseuri();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function component_info_ajx(){
        $d=igk_createNode();
        $c=igk_getctrl(IGK_COMPONENT_MANAGER_CTRL)->getComponents();
        $tab=$d->add("table");
        foreach($c as $k=>$v){
            $r=$tab->add("tr");
            $r->add("td")->Content=$k;
            $r->add("td")->Content=get_class($v);
            $id=$v->getParam(IGK_DOC_ID_PARAM) ?? igk_getv($v, 'id');
            $r->add("td")->Content="id: ".$id;
            $r->add("td")->Content=method_exists($v, "getId") ? $v->getId(): "-";
            $r->add("td")->Content=method_exists($v, "getOwner") ? $v->getOwner()->toString(): "-";
        }
        igk_ajx_notify_dialog(R::gets("title.componentinfo"), $d);
        igk_exit();
    }
    ///<summary>Get componsent strings </summary>
    /**
    * 
    */
    public function getComponents(){
        return __("Component : {0}",  igk_count(igk_getctrl(IGK_COMPONENT_MANAGER_CTRL)->getComponents()));
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getMemoryInUsed(){
        return IGKNumber::GetMemorySize(memory_get_usage());
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getMemoryPeekInUsed(){
        return IGKNumber::GetMemorySize(memory_get_peak_usage());
    }
    ///<summary></summary>
    /**
    * 
    */
    public function memoryinfo(){
        $this->RenderAJX();
        igk_exit();
    }
}
///<summary> used in article to setup controller item
/**
*  used in article to setup controller item
*/
final class IGKHtmlMenuBarItem extends IGKHtmlCtrlNodeItemBase {
    private $m_view;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("div");
        $this->setClass("igk-menu-bar");
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setView($v){
        $this->m_view=$v;
    }
}
///<summary>Represente class: IGKHtmlNoTagNodeItem</summary>
/**
* Represente IGKHtmlNoTagNodeItem class
*/
class IGKHtmlNoTagNodeItem extends IGKHtmlItem{
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("igk:notagnode");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getIsRenderTagName(){
        return false;
    }
}
///<summary>Represente class: IGKHtmlNonEmptyNodeItem</summary>
/**
* Represente IGKHtmlNonEmptyNodeItem class
*/
final class IGKHtmlNonEmptyNodeItem extends IGKHtmlItem{
    ///<summary></summary>
    ///<param name="tag" default="div"></param>
    /**
    * 
    * @param mixed $tag the default value is "div"
    */
    public function __construct($tag="div"){
        parent::__construct($tag);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getIsVisible(){
        return ($this->ChildCount > 0);
    }
}
///<summary>Represente class: IGKHtmlNothingItem</summary>
/**
* Represente IGKHtmlNothingItem class
*/
final class IGKHtmlNothingItem extends IGKHtmlItem {
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("nothingitem");
    }
    ///<summary></summary>
    ///<param name="item"></param>
    ///<param name="index" default="null"></param>
    /**
    * 
    * @param mixed $item
    * @param mixed $index the default value is null
    */
    protected function _AddChild($item, $index=null){
        return false;
    }
    ///<summary></summary>
    ///<param name="options" default="null"></param>
    /**
    * 
    * @param mixed $options the default value is null
    */
    public function render($options=null){
        return null;
    }
}
///<summary>used to notify message </summary>
/**
* used to notify message
*/
final class IGKHtmlNotifyBoxItem extends IGKHtmlDiv {
    private $m_autohide;
    private $m_type;
    ///<summary></summary>
    ///<param name="type" default="null"></param>
    /**
    * 
    * @param mixed $type the default value is null
    */
    public function __construct($type=null){
        parent::__construct();
        $this["class"]="igk-notify-box";
        if($type)
            $this->setType($type);
        $this["igk-no-auto-hide"]=new IGKValueListener($this, "Autohide");
        $this->m_autohide=$this->addScript();
        $this->m_autohide->Content=<<<EOF
\$ns_igk.winui.notifyctrl.init(\$ns_igk.getParentScript());
EOF;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getAutohide(){
        return $this->m_autohide->IsVisible;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setAutohide($v){
        $this->m_autohide->IsVisible=$v;
        return $this;
    }
    ///<summary></summary>
    ///<param name="type"></param>
    /**
    * 
    * @param mixed $type
    */
    public function setType($type){
        if($this->m_type)
            $this->setClass("+igk-notify-".$this->m_type);
        $this->setClass("+igk-notify-".$type);
        $this->m_type=$type;
        return $this;
    }
}
///<summary>Represente class: IGKHtmlNotifyDialogBoxItem</summary>
/**
* Represente IGKHtmlNotifyDialogBoxItem class
*/
final class IGKHtmlNotifyDialogBoxItem extends IGKHtmlItem {
    private $m_Message;
    private $m_title;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("div");
        $this["class"]="igk-notify-box";
        $nv=$this->addDiv();
        $nv["class"]="content";
        $nv->addDiv()->setClass("title")->Content=new IGKValueListener($this, 'Title');
        $nv->addDiv()->setClass("msg")->Content=new IGKValueListener($this, 'Message');
        $nv->addScript()->Content=<<<EOF
if(ns_igk)ns_igk.winui.notify.init();
EOF;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getMessage(){
        return $this->m_Message;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getTitle(){
        return $this->m_title;
    }
    ///<summary></summary>
    ///<param name="options" default="null" ref="true"></param>
    /**
    * 
    * @param  * $options the default value is null
    */
    public function renderAJX(& $options=null){
        parent::renderAJX($options);
        $this->setIsVisible(false);
    }
    ///<summary></summary>
    ///<param name="title"></param>
    ///<param name="msg"></param>
    /**
    * 
    * @param mixed $title
    * @param mixed $msg
    */
    public function show($title, $msg){$this->m_title=$title;
        $this->m_Message=$msg;
        $this->setIsVisible(null);
        return $this;
    }
}
///<summary>Represente class: IGKHtmlPagePreviewItem</summary>
/**
* Represente IGKHtmlPagePreviewItem class
*/
final class IGKHtmlPagePreviewItem extends IGKHtmlItem {
    private $m_uri;
    ///<summary></summary>
    ///<param name="uri" default="null"></param>
    /**
    * 
    * @param mixed $uri the default value is null
    */
    public function __construct($uri=null){
        parent::__construct("div");
        $this->m_uri=$uri;
    }
    ///<summary></summary>
    ///<param name="t"></param>
    /**
    * 
    * @param mixed $t
    */
    public function initDemo($t){}
    ///<summary></summary>
    /**
    * 
    */
    public function View(){
        if($this->m_uri){
            $d=igk_io_basePath(dirname(__FILE__)."/cgi-bin/previewpage.cgi");
            if(empty($d) == false){
                $uri=igk_io_baseUri()."/".igk_html_uri($d);
                $s=igk_curl_post_uri($uri, array("query"=>$this->m_uri));
                igk_wln("check for ". $uri. "<br />".$this->m_uri."<br />". " in : <br />out : ".$s);
            }
        }
    }
}
///<summary>Represente class: IGKHtmlPaginationItem</summary>
/**
* Represente IGKHtmlPaginationItem class
*/
final class IGKHtmlPaginationItem extends IGKHtmlItem {
    private $m_first;
    private $m_last;
    var $CurrentPage;
    var $MaxPage;
    var $NextUri;
    var $Offset;
    var $PrevUri;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("div");
        $this["class"]="igk-pagination";
        $this->Offset=10;
        $b=$this->add("li");
        $b->addA("")->Content="&laquo;";
        $b->setIndex(-0x0FFF);
        $this->m_first=$b;
        $b=$this->add("li");
        $b->setIndex(0xFFFF);
        $b->addA("")->Content="&raquo;";
        $this->m_last=$b;
    }
    ///<summary></summary>
    ///<param name="index"></param>
    ///<param name="uri"></param>
    /**
    * 
    * @param mixed $index
    * @param mixed $uri
    */
    public function addPage($index, $uri){
        $li=$this->add("li");
        $li->addA($uri)->setContent($index);
        return $li;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function flush(){
        if($this->CurrentPage == 0)
            $this->m_first->setClass("igk-inactive");
        else{
            $h=$this->m_first->getChildAtIndex(0);
            $h["href"]=$this->PrevUri;
        }
        if($this->CurrentPage>=($this->MaxPage-1)){
            $this->m_last->setClass("igk-inactive");
        }
        else{
            $h=$this->m_last->getChildAtIndex(0);
            $h["href"]=$this->NextUri;
        }
    }
    ///<summary></summary>
    ///<param name="t"></param>
    /**
    * 
    * @param mixed $t
    */
    public function initDemo($t){
        $t->addDiv()->Content="Panigation - Demonstration";
        $this->SetUp(10, 4, true, "?page=");
        $this->flush();
    }
    ///<summary></summary>
    ///<param name="max"></param>
    ///<param name="current"></param>
    ///<param name="visible"></param>
    ///<param name="uri"></param>
    ///<param name="shift"></param>
    ///<param name="tag" default="'form'"></param>
    /**
    * 
    * @param mixed $max
    * @param mixed $current
    * @param mixed $visible
    * @param mixed $uri
    * @param mixed $shift the default value is 0
    * @param mixed $tag the default value is 'form'
    */
    public function SetUp($max, $current, $visible, $uri, $shift=0, $tag='form'){
        $this->CurrentPage=$current;
        $this->MaxPage=$max;
        if($current>=$visible){
            $r=(int)round($current % $visible);
            $shift=$visible * (int)floor($current/$visible);
            $visible=min($max, $shift + $visible);
        }
        for($i=$shift; $i < $visible; $i++){
            $p=$this->addPage($i + 1, "javascript: ns_igk.ajx.post('".$uri."&page=".$i."', null, ns_igk.ajx.getResponseNodeFunction(this, '{$tag}') ); return false;");
            if($i == $current){
                $p["class"]="igk-active";
            }
        }
    }
}
///<summary>Represente class: IGKHtmlPanelItem</summary>
/**
* Represente IGKHtmlPanelItem class
*/
final class IGKHtmlPanelItem extends IGKHtmlDiv {
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct();
        $this["class"]="igk-panel";
    }
}
///<summary>used to present defintion
/**
* used to present defintion
*/
final class IGKHtmlPhpCodeItem extends IGKHtmlCtrlNodeItemBase {
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("code");
        $this["class"]="igk-phpcodenode";
    }
    ///<summary></summary>
    ///<param name="d"></param>
    ///<param name="index" default="null"></param>
    /**
    * 
    * @param mixed $d
    * @param mixed $index the default value is null
    */
    public function _AddChild($d, $index=null){
        if($this->IsLoading){
            if(is_object($d) && igk_reflection_class_extends($d, "IGKHtmlText")){
                $this->m_content=$d->Content;
                return true;
            }
        }
        return parent::_AddChild($d, $index);
    }
}
///<summary>used to evaluate php code every time you demand for rendering</summary>
/**
* used to evaluate php code every time you demand for rendering
*/
final class IGKHtmlPhpEvalCodeItem extends IGKHtmlCtrlNodeItemBase{
    var $params;
    ///<summary></summary>
    /**
    * 
    */
    function __construct(){
        parent::__construct("div");
        $this["class"]="igk-phpevalnode";
    }
    ///<summary></summary>
    ///<param name="context"></param>
    ///<param name="c"></param>
    /**
    * 
    * @param mixed $context
    * @param mixed $c
    */
    private function __evalin($context, $c){
        extract($context);
        return eval($c);
    }
    ///<summary></summary>
    ///<param name="options" default="null"></param>
    /**
    * 
    * @param mixed $options the default value is null
    */
    public function __getRenderingChildren($options=null){
        return array();
    }
    ///<summary></summary>
    ///<param name="o" default="null"></param>
    /**
    * 
    * @param mixed $o the default value is null
    */
    public function render($o=null){
        $bck=$this->Content;
        $this->Content="";
        $c="";
        $b=array_merge(array("ctrl"=>igk_getctrl($this->getCtrl()), "row"=>$this), $this->params ?? array());
        $this->Content=$this->__evalin($b, $bck);
        $this->NoOverride=1;
        $c=igk_html_render_node($this, $o);
        unset($this->NoOverride);
        $this->Content=$bck;
        return $c;
    }
}
///<summary>Represente class: IGKHtmlPopWindowAItem</summary>
/**
* Represente IGKHtmlPopWindowAItem class
*/
final class IGKHtmlPopWindowAItem extends IGKHtmlA{
    ///<summary></summary>
    ///<param name="lnk" default="null"></param>
    /**
    * 
    * @param mixed $lnk the default value is null
    */
    public function __construct($lnk=null){
        parent::__construct();
        $this["onclick"]="javascript: ns_igk.winui.openUrl(this.href, this.wndName); return false;";
        $this["href"]=$lnk;
    }
}
///<summary>Represente class: IGKHtmlRegistrationFormItem</summary>
/**
* Represente IGKHtmlRegistrationFormItem class
*/
final class IGKHtmlRegistrationFormItem extends IGKHtmlItem {
    private $m_action;
    private $m_baduri;
    private $m_gooduri;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("div");
        $this["class"]="igk-signup-form";
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getAction(){
        return $this->m_action;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getBadUri(){
        return $this->m_baduri;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getGoodUri(){
        return $this->m_gooduri;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function initView(){
        $this->ClearChilds();
        $frm=$this->addForm();
        $frm["action"]=new IGKValueListener($this, "action");
        igk_notifyctrl()->setNotifyHost($frm->addDiv());
        $ul=$frm->add("ul");
        $ul->add("li")->addSLabelInput("clLogin", "email");
        $ul->add("li")->addSLabelInput("clPwd", "password");
        $ul->add("li")->addSLabelInput("clRePwd", "password");
        $frm->addInput("confirm", "hidden", 1);
        $frm->addInput("noNavigation", "hidden", "1");
        $dd=$frm->addDiv()->setClass("disptable fitw");
        $dd->addDiv()->setClass("disptabc")->addInput("clAcceptCondition", "checkbox");
        $dd->addDiv()->setClass("disptabc fitw")->addDiv()->add("label")->setAttribute("for", "clAcceptCondition")->setStyle("padding-left:10px")->Content=new IGKHtmlUsageCondition();
        $frm->addHSep();
        $frm->addInput("btn_reg", "submit", R::ngets("btn.register"))->setClass("dispb igk-sm-fitw");
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setAction($v){
        $this->m_action=$v;
        return $this;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setBadUri($v){
        $this->m_baduri=$v;
        return $this;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setGoodUri($v){
        $this->m_gooduri=$v;
        return $this;
    }
}
///<summary> Item parent for rollin child</summary>
/**
*  Item parent for rollin child
*/
final class IGKHtmlRollOwnerItem extends IGKHtmlItem {
    private $m_content;
    private $m_rollin;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("div");
        $this->setClass("igk-roll-owner");
        $this->m_rollin=parent::add("div")->setClass("igk-roll-in");
        $this->m_content=parent::add("div");
    }
    ///<summary></summary>
    ///<param name="s"></param>
    ///<param name="t" default="null"></param>
    ///<param name="b" default="null"></param>
    /**
    * 
    * @param mixed $s
    * @param mixed $t the default value is null
    * @param mixed $b the default value is null
    */
    public function add($s, $t=null, $b=null){
        return $this->m_content->add($s, $t, $b);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function ClearChilds(){
        $this->m_content->ClearChilds();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getContent(){
        return $this->m_content->Content;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getRoll(){
        return $this->m_rollin;
    }
    ///<summary></summary>
    ///<param name="t"></param>
    /**
    * 
    * @param mixed $t
    */
    public function initDemo($t){
        $this->Content="Please Pass your mouse here";
        $this->Roll->Content="Info";
    }
    ///<summary></summary>
    ///<param name="options" default="null" ref="true"></param>
    /**
    * 
    * @param  * $options the default value is null
    */
    protected function innerHTML(& $options=null){
        $o="";
        $o .= $this->m_rollin->Render($options);
        $o .= $this->m_content->Render($options);
        return $o;
    }
    ///<summary></summary>
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $value
    */
    public function setContent($value){
        $this->m_content->setContent($value);
        return $this;
    }
}
///<summary>Represente class: IGKHtmlRowColSpanItem</summary>
/**
* Represente IGKHtmlRowColSpanItem class
*/
final class IGKHtmlRowColSpanItem extends IGKHtmlItem{
    private $m_classstyle;
    private $m_colindex;
    private $m_cols;
    private $m_colsn;
    ///<summary></summary>
    ///<param name="cols" default="4"></param>
    ///<param name="classStyle" default="igk-col-lg-12-3"></param>
    /**
    * 
    * @param mixed $cols the default value is 4
    * @param mixed $classStyle the default value is "igk-col-lg-12-3"
    */
    public function __construct($cols=4, $classStyle="igk-col-lg-12-3"){
        parent::__construct("div");
        $this["class"]="igk-row";
        $this->m_colsn=$cols;
        $this->m_classstyle=$classStyle;
        $this->_initRow($cols, $classStyle);
    }
    ///<summary></summary>
    ///<param name="item"></param>
    ///<param name="index" default="null"></param>
    /**
    * 
    * @param mixed $item
    * @param mixed $index the default value is null
    */
    public function _AddChild($item, $index=null){
        if($this->m_colindex == -1){
            return parent::_AddChild($item, $index);
        }
        $t=igk_getv($this->m_cols, $this->m_colindex);
        $s=$t->add($item, null, null);
        $this->m_colindex=($this->m_colindex + 1) % igk_count($this->m_cols);
        return $s;
    }
    ///<summary></summary>
    ///<param name="cols"></param>
    ///<param name="classStyle"></param>
    /**
    * 
    * @param mixed $cols
    * @param mixed $classStyle
    */
    private function _initRow($cols, $classStyle){
        $this->m_cols=array();
        $this->m_colindex=-1;
        for($i=0; $i < $cols; $i++){
            $dv=(new IGKHtmlRowColSpanNode($i))->setClass($classStyle);
            $this->add($dv);
            $this->m_cols[]=$dv;
        }
        $this->m_colindex=$cols > 0 ? 0: -1;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function ClearChilds(){
        parent::ClearChilds();
        $this->_initRow($this->m_colsn, $this->m_classstyle);
    }
}
///for debugging
/**
*/
final class IGKHtmlRowColSpanNode extends IGKHtmlItem{
    private $m_rowid;
    ///<summary></summary>
    ///<param name="id"></param>
    /**
    * 
    * @param mixed $id
    */
    public function __construct($id){
        parent::__construct("div");
        $this->m_rowid=$id;
    }
    ///<summary></summary>
    ///<param name="i"></param>
    ///<param name="index" default="null"></param>
    /**
    * 
    * @param mixed $i
    * @param mixed $index the default value is null
    */
    protected function _AddChild($i, $index=null){
        $s=parent::_AddChild($i, $index);
        return $s;
    }
    ///<summary></summary>
    ///<param name="child"></param>
    ///<param name="index" default="null"></param>
    /**
    * 
    * @param mixed $child
    * @param mixed $index the default value is null
    */
    protected function _setUpChildIndex($child, $index=null){
        if($index === null){
            $i=$child->Index;
            if(!is_numeric($i) || $child->AutoIndex){
                $child->setIndex($this->ChildCount-1, true);
            }
        }
        else if(is_numeric($index)){
            $child->setIndex($index, false);
        }
    }
    ///<summary></summary>
    ///<param name="n"></param>
    ///<param name="a" default="null"></param>
    ///<param name="i" default="null"></param>
    /**
    * 
    * @param mixed $n
    * @param mixed $a the default value is null
    * @param mixed $i the default value is null
    */
    public function add($n, $a=null, $i=null){
        return parent::add($n, $a, $i);
    }
}
///<summary>row item</summary>
/**
* row item
*/
final class IGKHtmlRowItem extends IGKHtmlDiv {
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct();
        $this["class"]="igk-row";
    }
    ///<summary></summary>
    /**
    * 
    */
    public function addCell(){
        $d=$this->addDiv();
        $d->setClass("igk-row-cell");
        return $d;
    }
    ///<summary></summary>
    ///<param name="classlist" default="null"></param>
    /**
    * 
    * @param mixed $classlist the default value is null
    */
    public function addCol($classlist=null){
        return $this->addDiv()->setClass("igk-col ".$classlist);
    }
}
///<summary>component in charge of searching on the current page</summary>
/**
* component in charge of searching on the current page
*/
final class IGKHtmlSearchBarItem extends IGKHtmlCtrlComponentNodeItemBase {
    private $m_ajx;
    private $m_search;
    private $m_uri;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("div");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getAJX(){
        return $this->m_ajx;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getTargetId(){
        return $this->m_targetid;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getUri(){
        return $this->m_uri;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setAJX($v){
        $this->m_ajx=$v;
        return $this;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setTargetId($v){
        $this->m_targetid=$v;
        return $this;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setUri($v){
        $this->m_uri=$v;
        return $this;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function View(){
        $this->ClearChilds();
        $s=$this->addSearch();
        $s->Uri=$this->Uri;
        $s->TargetId=$this->TargetId;
        $s->AJX=$this->AJX;
        $s->loadingComplete();
        $this->m_search=$s;
    }
}
///<summary>Represente class: IGKHtmlSearchItem</summary>
/**
* Represente IGKHtmlSearchItem class
*/
final class IGKHtmlSearchItem extends IGKHtmlItem {
    private $m_AJX;
    private $m_TargetId;
    private $m_ajxfunc;
    private $m_ctrl;
    private $m_frm;
    private $m_input;
    private $m_link;
    private $m_method;
    private $m_prop;
    private $m_search;
    private $m_uri;
    ///<summary></summary>
    ///<param name="uri" default="null"></param>
    ///<param name="search" default="null"></param>
    ///<param name="prop" default="q"></param>
    ///<param name="ajx"></param>
    ///<param name="target" default="null"></param>
    /**
    * 
    * @param mixed $uri the default value is null
    * @param mixed $search the default value is null
    * @param mixed $prop the default value is "q"
    * @param mixed $ajx the default value is 0
    * @param mixed $target the default value is null
    */
    public function __construct($uri=null, $search=null, $prop="q", $ajx=0, $target=null){
        parent::__construct("div");
        $this["class"]="clsearch search_fcl";
        $this->m_AJX=$ajx;
        $this->m_method="POST";
        $this->m_uri=$uri;
        $this->m_frm=$this->addForm();
        $this->m_prop=$prop;
        $this->m_search=$search;
        $this->m_TargetId=$target;
        $this->initView();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getAJX(){
        return $this->m_AJX;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getMethod(){
        return $this->m_method;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getTargetId(){
        return $this->m_TargetId;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getUri(){
        return $this->m_uri;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function initView(){
        $uri=$this->m_uri;
        $tab=igk_getquery_args($uri);
        if(isset($tab["c"])){
            $this->m_ctrl=igk_getctrl($tab["c"]);
            $f=igk_getv($tab, "f");
            $this->m_ajxfunc=null;
            if($f){
                $f=str_replace("-", "_", $f);
                if(!IGKString::EndWith($f, "_ajx")){
                    $f=$f."_ajx";
                    if(method_exists($this->m_ctrl, $f)){
                        $this->m_ajxfunc=$this->m_ctrl->getUri($f);
                    }
                }
                else{
                    $this->m_ajxfunc=$this->m_ctrl->getUri($f);
                }
            }
        }
        $frm=$this->m_frm;
        $prop=$this->m_prop;
        $search=$this->m_search;
        if(!$frm || empty($uri)){
            return;}
        $frm->ClearChilds();
        $frm["action"]=$uri;
        $frm["id"]="search_item";
        $frm["method"]=new IGKValueListener($this, "Method");
        $frm->addDiv()->setClass("igk-underline-div");
        $frm->NoTitle=true;
        $frm->NoFoot=true;
        $d=$frm->addDiv();
        $d["class"]="disptable fitw";
        $d=$d->addDiv()->setClass("disptabr");
        $this->m_link=IGKHtmlUtils::AddImgLnk($d, $uri, "btn_search_16x16", "24px", "24px");
        $this["class"]="alignm";
        $this->m_input=$d->addInput($prop, "text", igk_getr($prop, $search));
        $this->m_input["class"]="igk-form-control fitw";
        $this->m_input["onkeypress"]="javascript:return ns_igk.form.keypress_validate(this,event);";
        if($this->AJX || $this->m_ajxfunc){
            $frm["igk-ajx-form"]=1;
            $frm["igk-ajx-form-uri"]=$this->m_ajxfunc;
            $frm["igk-ajx-form-target"]=$this->m_TargetId;
        }
        else{
            $frm["igk-ajx-form"]=null;
            $frm["igk-ajx-form-uri"]=null;
            $frm["igk-ajx-form-target"]=null;
        }
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setAJX($v){
        $this->m_AJX=$v;
        return $this;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setMethod($v){
        $this->m_method=$v;
        return $this;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setTargetId($v){
        $this->m_TargetId=$v;
        return $this;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setUri($v){
        $this->m_uri=$v;
        return $this;
    }
    ///<summary></summary>
    ///<param name="v"></param>
    /**
    * 
    * @param mixed $v
    */
    public function setValue($v){
        $this->m_search=$v;
        return $this;
    }
}
///<summary>represent language selection options</items>
/**
* represent language selection options
*/
final class IGKHtmlSelectLangNodeItem extends IGKHtmlItem {
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("select");
        $this["onchange"]="javascript: ns_igk.ajx.get('?l='+this.value, null, ns_igk.ajx.fn.replace_body); return false;";
    }
    ///<summary></summary>
    /**
    * 
    */
    public function initView(){
        igk_wln_e(__FILE__.':'.__LINE__, "Init View ");

        $this->ClearChilds();
        $ctrl=igk_getctrl(IGK_LANGUAGE_CTRL);
        $tab=array_merge($ctrl->Languages);
        igk_html_build_select_option($this, $tab, array("allowEmpty"=>false, "keysupport"=>false), R::GetCurrentLang());
    }
}
///<summary>toggle class button</summary>
/**
* Represente IGKHtmlToggleButtonItem class
*/
final class IGKHtmlToggleButtonItem extends IGKHtmlItem {
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("button");
        $this["class"]="igk-toggle-button";
        $this["igk-toggle-button"]=true;
        $this["igk-toggle-state"]="collapse";
    }
    ///<summary></summary>
    ///<param name="c" default="1"></param>
    /**
    * 
    * @param mixed $c the default value is 1
    */
    public function addBar($c=1){
        $this->clearChilds();
        for($i=0; $i < $c; $i++)
            $this->add("span")->setClass("igk-iconbar dispb");
        return $this;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getClassProperty(){
        return $this["igk-toggle-class"];
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getTarget(){
        return $this["igk-toggle-target"];
    }
    ///<summary></summary>
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $value
    */
    public function setClassProperty($value){
        $this["igk-toggle-class"]=$value;
        return $this;
    }
    ///<summary></summary>
    ///<param name="target"></param>
    /**
    * 
    * @param mixed $target
    */
    public function setTarget($target){
        if($target == null){
            $this["igk-toggle-target"]=null;
            return;
        }
        if(is_string($target)){
            $this["igk-toggle-target"]=$target;
        }
        else{
            $h=$this["igk-toggle-target"];
            if(is_object($h) && get_class($h) == "IGKHtmlTargetValue"){
                $h->setTarget($target);
            }
            else
                $this["igk-toggle-target"]=new IGKHtmlTargetValue($target);
        }
        return $this;
    }
}
///<summary> Item parent for rollin on touch screen</summary>
/**
*  Item parent for rollin on touch screen
*/
final class IGKHtmlTouchRollOwnerItem extends IGKHtmlItem {
    private $m_content;
    private $m_rollin;
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("div");
        $this->setClass("igk-touch-roll-owner");
        $this->m_rollin=parent::add("div")->setClass("igk-roll-in");
        $this->m_content=parent::add("div");
    }
    ///<summary></summary>
    ///<param name="s"></param>
    ///<param name="t" default="null"></param>
    ///<param name="b" default="null"></param>
    /**
    * 
    * @param mixed $s
    * @param mixed $t the default value is null
    * @param mixed $b the default value is null
    */
    public function add($s, $t=null, $b=null){
        return $this->m_content->add($s, $t, $b);
    }
    ///<summary></summary>
    /**
    * 
    */
    public function ClearChilds(){
        $this->m_content->ClearChilds();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getContent(){
        return $this->m_content->Content;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getRoll(){
        return $this->m_rollin;
    }
    ///<summary></summary>
    ///<param name="t"></param>
    /**
    * 
    * @param mixed $t
    */
    public function initDemo($t){
        $this->Content="Please Click here to show roll in";
        $this->Roll->Content="Info";
    }
    ///<summary></summary>
    ///<param name="options" default="null" ref="true"></param>
    /**
    * 
    * @param  * $options the default value is null
    */
    protected function innerHTML(& $options=null){
        $o="";
        $o .= $this->m_rollin->Render($options);
        $o .= $this->m_content->Render($options);
        return $o;
    }
    ///<summary></summary>
    ///<param name="value"></param>
    /**
    * 
    * @param mixed $value
    */
    public function setContent($value){
        $this->m_content->setContent($value);
        return $this;
    }
}
///<summary> represent a trackbar winui item </summary>
/**
*  represent a trackbar winui item
*/
final class IGKHtmlTrackbarItem extends IGKHtmlItem {
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct("div");
        $this->setClass("igk-trb");
        $this->addDiv()->setClass("igk-trb-cur");
    }
    ///<summary></summary>
    ///<param name="t"></param>
    /**
    * 
    * @param mixed $t
    */
    public function initDemo($t){
        $this["igk-trb-data"]="{update: function(d){ if (typeof(d.bar.rep) == 'undefined'){ d.bar.rep = d.bar.target.add('div'); } d.bar.rep.setHtml(d.progress); }}";
    }
}
///<summary>Represente class: IGKHtmlXmlViewerItem</summary>
/**
* Represente IGKHtmlXmlViewerItem class
*/
final class IGKHtmlXmlViewerItem extends IGKHtmlDiv {
    ///<summary></summary>
    /**
    * 
    */
    public function __construct(){
        parent::__construct();
        $this["class"]="igk-xml-viewer";
    }
    ///<summary></summary>
    ///<param name="target"></param>
    ///<param name="depth"></param>
    /**
    * 
    * @param mixed $target
    * @param mixed $depth
    */
    private function __renderDepth($target, $depth){
        if($depth > 0){
            for($i=0; $i < $depth; $i++){
                $target->add("span")->setClass("t")->addSpace();
            }
        }
    }
    ///<summary></summary>
    ///<param name="t"></param>
    /**
    * 
    * @param mixed $t
    */
    public function initDemo($t){
        $t->addDiv()->addSectionTitle(5)->Content="Samples ";
        $t->addDiv()->addPhpCode()->Content="\$t->addXmlViewer()->Load('[xml_content]');";
        $this->ClearChilds();
        $this->Load(<<<EOF
<demo attr_1="attrib_definition" >The viewer<i >sample</i></demo>
EOF
        , IGKHtmlContext::XML);
    }
    ///<summary></summary>
    ///<param name="content"></param>
    ///<param name="context" default="XML"></param>
    /**
    * 
    * @param mixed $content
    * @param mixed $context the default value is XML
    */
    public function Load($content, $context=IGKHtmlContext::XML){
        if(empty($content))
            return;
        
        $c=IGKHtmlReader::Load($content, $context);
        $root=null;
        foreach($c->Childs as  $v){
            $c=$this->loadItem($v, $this);
            if(!$root && ($v->NodeType == IGKXMLNodeType::ELEMENT)){
                $root=$v;
            }
        }
        $this["igk:loaded"]=1;
    }
    ///<summary></summary>
    ///<param name="r"></param>
    ///<param name="target"></param>
    ///<param name="depth"></param>
    /**
    * 
    * @param mixed $r
    * @param mixed $target
    * @param mixed $depth the default value is 0
    */
    public function loadItem($r, $target, $depth=0){
        $this->__renderDepth($target, $depth);
        $target->add("span")->setClass("s")->Content="&lt;".$r->TagName;
        if($r->HasAttributes){
            foreach($r->Attributes->ToArray() as $k=>$v){
                $target->addSpace();
                $target->add("span")->setClass("attr")->Content=$k;
                $target->add("span")->setClass("o")->Content="=";
                $target->add("span")->setClass("attrv")->Content="\"".IGKHtmlUtils::GetValue($v)."\"";
            }
        }
        $s=IGKHtmlUtils::GetValue($r->Content);
        if($r->HasChilds || !empty($s)){
            $target->add("span")->setClass("s")->Content="&gt;";
            if(!empty($s)){
                $target->add("span")->setClass("tx")->Content=$s;
            }
            foreach($r->Childs as $k=>$v){
                $target->addBr();
                switch($v->NodeType){
                    case IGKXMLNodeType::COMMENT:
                    $target->add("span")->setClass("c")->Content="&lt;!--".IGKHtmlUtils::GetValue($v->Content)."--&gt;";
                    break;
                    case IGKXMLNodeType::TEXT:
                    $target->add("span")->setClass("tx")->Content=IGKHtmlUtils::GetValue($v->Content);
                    break;default:
                    $c=$this->loadItem($v, $this, $depth + 1);
                    break;
                }
            }
            if($r->HasChilds){
                $target->addBr();
                $this->__renderDepth($target, $depth);
            }
            $target->add("span")->setClass("e")->Content="&lt;/".$r->TagName."&gt;";
        }
        else{
            $target->add("span")->setClass("s")->Content="/&gt;";
        }
    }
}

/**
 * summary html array looper
 */
class IGKHtmlLooper extends IGKHtmlItemBase{
    private $args;
    private $node;  
    
    public function __construct($args, $node){
        parent::__construct();
        $this->args = $args;
        $this->node = $node;
    }

    public function getIsRenderTagName() { return false; }

    public function render($options = null) { return null; }
    public function host(callable $callback){
        foreach($this->args as $c){
            $callback($this->node, $c);
        }
    }   
}


igk_reg_html_component("expression-node", function(){
    return igk_html_node_expression_node(null);
});