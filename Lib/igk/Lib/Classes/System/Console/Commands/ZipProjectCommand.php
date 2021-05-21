<?php
namespace IGK\System\Console\Commands;

use IGK\System\Console\AppExecCommand;
use IGK\System\Console\Logger;
use ZipArchive;

class ZipProjectCommand extends AppExecCommand{

    var $command = "--zipproject";

    var $desc = "zip balafon project core";


    public function exec($command, $name=null, $path=null){
       
        if (!extension_loaded("zip") && !function_exists('zip_open')){
            Logger::danger("zip utility function not found");
            return -1;
        }

        $ctrl = igk_getctrl(str_replace("/", "\\", $name), false);
        if (!$ctrl){
            Logger::danger("controller $name not found");
            return false;
        }

        $prjname = igk_str_snake(basename(igk_io_dir(get_class($ctrl))));
        $fname = "/project_.".$prjname.".".date("Ymd").".zip";
        if ($path == null){
            $path = getcwd().$fname;
        } else if (is_dir($path)){
            $path = $path.$fname;
        }
        $author = $command->app->getConfigs()->get("author", IGK_AUTHOR);
        igk_sys_zip_project($ctrl, $path, $author);

        // $g = $ctrl->getDeclaredDir();
        // $zip = new ZipArchive();
        // if ($zip->open($path, ZIPARCHIVE::CREATE))
        // { 
        //     igk_zip_dir($g, $zip,  $prjname, "/(\/temp)|\.(vscode|git(ignore)?|gkds|DS_Store)$/");
        //     $manifest = igk_createxmlnode("manifest");
        //     $manifest["xmlns"] = "https://schema.igkdev.com/project";
        //     $manifest["appName"] = IGK_PLATEFORM_NAME."/".$prjname;
        //     $manifest->add("version")->Content = $ctrl->Configs->get("version", "1.0");// IGK_VERSION;
        //     $manifest->add("author")->Content = $author;
        //     $manifest->add("date")->Content = date("Ymd His"); 

        //     $zip->addFromString("manifest.xml", $manifest->render());
        //     $zip->addFromString("__project.def", "");
        //     $zip->close(); 
        // }

        Logger::success("zip project: ".$path);
    }
      
}