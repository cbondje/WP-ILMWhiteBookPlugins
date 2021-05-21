<?php
// @file: IGKInstallSiteTool.php
// @author: C.A.D. BONDJE DOUE
// @description:
// @copyright: igkdev © 2020
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

///<summary>Represente class: IGKInstallSiteTool</summary>
/**
* Represente IGKInstallSiteTool class
*/
class IGKInstallSiteTool extends IGKToolCtrlBase{
    ///<summary></summary>
    /**
    * 
    */
    public function doAction(){
        $frame=igk_html_frame($this, "tool.installsite");
        $d=$frame->getBoxContent();
        $d->ClearChilds();
        $d["class"]="google-Roboto";
        $frm=$d->addForm();
        $frm["action"]=$this->getUri("install");
        $frm->addSectionTitle(4)->Content="Install Site ";
        $frm->addInput("dir", "text")->setAttribute("placeholder", "target directory");
        $frm->addInput("btn.submit", "submit");
        $frm->addInput("frame", "hidden", 1);
        igk_html_form_initfield($frm);
        $frame->renderAJX();
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getIsAvailable(){
        return true;
    }
    ///<summary>install site </summary>
    /**
    * install site
    */
    public function install(){
        $g=igk_getr("dir");
        $result = igk_createxmlnode("response");

        if(empty($g) || !IGKIO::CreateDir($g)){

            $result->msg="failed to create {$g} directory";
            igk_json(json_encode($result));
            igk_exit();
        }
        $root=$g."/src";
        $sep=DIRECTORY_SEPARATOR;
        $appdir=$root."/application";
        IGKIO::CreateDir($appdir);
        igk_io_symlink(dirname(IGK_LIB_DIR), $appdir."/Lib");
        $dir=$root."/public";
        IGKIO::CreateDir($dir);
        $appdir=igk_html_uri($appdir);
        igk_io_w2file($dir."/index.php", <<<EOF
<?php
\$apppath = realpath(dirname(__FILE__)."/../application");
define('IGK_PROJECT_DIR', \$apppath.'{$sep}Projects');
define('IGK_APP_DIR', \$apppath);
define('IGK_SESS_DIR', dirname(\$apppath).'{$sep}temp');
@require_once(\$apppath."/Lib/igk/igk_framework.php");
unset(\$appath);
igk_sys_render_index(__FILE__);
EOF
        );
        if($frame=igk_getr("frame")){
            igk_ajx_replace_uri(igk_server()->HTTP_REFERER);
        }
    }
}
