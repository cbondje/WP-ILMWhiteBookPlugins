<?php

namespace IGK\System\Installers;

class InstallSite
{
    public static function Install($folder, $packagefolder = null)
    {
        $installer = new InstallSite;
        return $installer->installSite($folder, $packagefolder);
    }
    public function installSite($folder, $packagefolder = null)
    {

        $core = IGK_LIB_FILE;
        $src = rtrim($folder, "/") . "/src";

        if (file_exists($src)) {
            return false;
        }

        if (!igk_io_createdir($src)) {
            return false;
        }
        igk_io_createdir($src . "/application");
        igk_io_createdir($src . "/public");
        igk_io_createdir($src . "/temp");
        igk_io_createdir($src . "/logs");
        igk_io_createdir($src . "/crons");
        igk_io_createdir($src . "/test");
        // generate git ingore
        igk_io_w2file($folder . "/.gitignore", implode("\n", [
            "*/.vscode/**",
            ".gitignore",
            "phpunit.xml.dist",
            "phpunit-watcher.yml",
            "src/application/Packages/vendor/*"
        ]));
        // generate phpunit-watcher file
        igk_io_w2file($folder . "/phpunit-watcher.yml", implode("\n", []));

        // generate phpunit.xml.dist distribution
        $php_xml = igk_createxmlnode("phpunit");
        $php_xml["xmlns:xsi"] = "http://www.w3.org/2001/XMLSchema-instance";
        $php_xml["xsi:noNamespaceSchemaLocation"] = "./src/application/Packages/vendor/phpunit/phpunit/phpunit.xsd";
        $php_xml["bootstrap"] = "./src/application/Packages/vendor/autoload.php";
        $php_xml["colors"] = "true";
        ob_start();

        $php_xml->renderXML((object)["xmldefinition" => 1, "noheader" => 1]);
        $ob = ob_get_clean();
        igk_io_w2file($folder . "/phpunit.xml.dist", $ob);


        if (!is_link($lnk = $src . "/application/Lib/igk")) {
            igk_io_createdir(dirname($lnk));
            symlink(dirname($core), $lnk);
        }

        if (!empty($packagefolder) && !is_link($lnk = $src . "/application/" . IGK_PACKAGES_FOLDER)) {
            symlink($packagefolder, $lnk);
        }

        $index = $src . "/public/index.php";
        igk_io_w2file(
            $index,
            <<<EOF
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
        if (empty($environment)) {
            $environment = "development";
        }
        $tport = "80";
        if (is_numeric($listen) && (strlen($listen) >= 4)) {
            $tport = $listen;
            $listen = "Listen " . $tport . "\n";
        } else
            $listen = "";
        $root = $src . "/public";

        igk_io_w2file(
            $src . "/vhost.conf",
            <<<EOF
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
AddType text/javascripit js
AddEncoding deflate js
<IfModule mod_headers.c>
Header set Cache-Control "max-age=31536000"
</IfModule>
</Directory>
</VirtualHost>
EOF
        );
        // create vhost link on apache
        $vhost_dir = "/private/etc/apache2/other";
        if (is_dir($vhost_dir)) {
            $conf_file = $vhost_dir . "/vhost." . basename($folder) . ".conf";
            igk_io_symlink($conf_file, $src . "/vhost.conf");
        }
    }
}
