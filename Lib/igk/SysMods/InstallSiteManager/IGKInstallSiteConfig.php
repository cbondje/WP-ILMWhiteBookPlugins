<?php

// file: IGKInstallSiteConfig
// desc: install site
//
use function igk_resources_gets as __;


class IGKInstallSiteConfig extends IGKConfigCtrlBase{
	public function install($folder=null, $packagefolder=null){
		if ($packagefolder===null){
			$packagefolder = igk_get_packages_dir();
		}
		if ($folder == null)
		{
			// install request
			if (igk_server()->method("POST") && igk_valid_cref(1)){
				$folder = igk_html_uri(igk_getr("rootdir", $folder));
				$packagefolder = igk_getr("packagedir", $packagefolder);
			}
		} 
		if ($uri_demand = igk_is_uri_demand($this)){
			$this->setEnvParam("replaceuri", 1);
		}
		if(empty($folder)){
			return false;
		}

		$core = IGK_LIB_FILE;
		$src = rtrim($folder, "/")."/src";

		if (file_exists($src)){
			return false;
		}

		if (!igk_io_createdir($src)){
			return false;
		}
		igk_io_createdir($src."/application");
		igk_io_createdir($src."/public");
		igk_io_createdir($src."/temp");
		igk_io_createdir($src."/logs");
		igk_io_createdir($src."/crons");
		igk_io_createdir($src."/test");



		if (!is_link($lnk = $src."/application/Lib/igk"))
		{
			igk_io_createdir(dirname($lnk));
			symlink(dirname($core) , $lnk);
		}

		if (!empty($packagefolder) && !is_link($lnk = $src."/application/".IGK_PACKAGES_FOLDER))
		{
			symlink($packagefolder , $lnk);
		}

$index = $src."/public/index.php"; 
igk_io_w2file($index, <<<EOF
<?php 
// 
// @description: Balafon entry point
//
\$apppath = realpath(__DIR__."/../");
define("IGK_APP_DIR", \$apppath."/application");
define("IGK_SESS_DIR", \$apppath."/sesstemp");
define("IGK_PROJECT_DIR", IGK_APP_DIR."/Projects"); 
require_once(IGK_APP_DIR."/Lib/igk/igk_framework.php"); 
try{ 
    igk_sys_render_index(__FILE__);
}
catch(Exception \$ex){
    igk_ilog("Error: ".\$ex->getMessage()); 
}
EOF
);
$listen = igk_getr("listen");
$environment = igk_getr("environment", "development");
if (empty($environment)){
	$environment = "development";
}
$tport = "80";
if (is_numeric($listen) && (strlen($listen)>=4)){
	$tport = $listen;
	$listen = "Listen ".$tport."\n";
} else 
	$listen = "";
$root = $src."/public";

igk_io_w2file($src."/vhost.conf", <<<EOF
{$listen}<VirtualHost *:$tport>
SetEnv ENVIRONMENT {$environment}
SetEnv IGK_LIB_DIR {$src}/application/Lib/igk
DocumentRoot {$root}
<Directory {$root}>
Options +FollowSymLinks -MultiViews -Indexes
Order deny,allow
AllowOverride none
Allow from all
Require all granted

<IfModule rewrite_module>
RewriteEngine on
RewriteCond "%{REQUEST_FILENAME}" !-d
RewriteCond "%{REQUEST_FILENAME}" !-f
RewriteRule ^(.)+$ "/index.php?rwc=1" [QSA,L]
</IfModule>

</Directory>
<Directory {$src}/public/assets/_chs_/dist/js>
 AddType text/javascript js
 AddEncoding deflate js
</Directory>
</VirtualHost>
EOF
);
// create vhost link on apache
$vhost_dir = "/private/etc/apache2/other";
if (is_dir($vhost_dir)){
	$conf_file = $vhost_dir."/vhost.".basename($folder).".conf";
	igk_io_symlink($conf_file, $src."/vhost.conf");


}


igk_notifyctrl("installsite")->addSuccessr("Install site success");

	}
	public function __construct(){
		parent::__construct();
	}
	public function setConfig($c){
	}
	public function getConfigPage(){
		return "installsite";
	}
	public function getConfigGroup(){
		return "administration";
	}
	public function getIsConfigPageAvailable(){
		return !igk_io_is_subdir(igk_io_applicationdir(), IGK_LIB_DIR);
	}
	public function View()
	{
		$t = $this->getTargetNode();
		$t->clearChilds();
		if (!$this->getIsConfigPageAvailable())
			return;
		if ($this->getEnvParam("replaceuri"))
			$t->addReplaceUri($this->getUri("ShowConfig"));
		$c = $t->addPanelBox();
		$c->addSectionTitle(4)->Content = __("Install Site");
		$c->addNotifyHost("installsite");
		$form = $c->addForm();
		$form["method"] = "POST";
		$form["action"] = $this->getUri("install");

		$form->addFields(
			[
				"rootdir"=>["attribs"=>["class"=>"igk-form-control required" , "placeholder"=>__("Install site folder. use full path")]],
				"packagedir"=>["attribs"=>["class"=>"igk-form-control", "placeholder"=>__("Custom package folder")]],
				"listen"=>["attribs"=>["class"=>"igk-form-control", "placeholder"=>__("port")]],
				"environment"=>["attribs"=>["class"=>"igk-form-control", "placeholder"=>__("environment")]]
			]
		);
		igk_html_form_initfield($form);
		//+ tips information
		$div = $form->addDiv();
		$div->addP()->Content = __("TIPS");
		$div->addArticle($this, "help.installer.tips");
		$_ac_bar = $form->addActionBar();
		$_ac_bar->addInput("btn.send", "submit", __("Install"));
	}

}