<?php

class IGKIncludeForVSTool extends IGKToolCtrlBase
{

	public function getImageUri(){
		$uri = igk_html_resolv_img_uri(igk_io_baseDir("Lib/igk/Default/R/Img/pics_48x48/tool_incforvs.png"));
		return $uri;
	}
	private function LoadFile($node , $pattern,  $dir)
	{
		$hdir = opendir($dir);

		if (is_resource($hdir))
		{
			while($s = readdir($hdir))
			{
				if (($s==".") || ($s== ".."))
					continue;
				$dname = $dir.DIRECTORY_SEPARATOR.$s;
				if (is_dir($dname))
				{
					$this->LoadFile($node, $pattern."\\".$s, $dname);
				}
				else if (file_exists($dname))
				{
					$node->add("None", array("Include"=>realpath($dname)))->add("Link")->Content = $pattern."\\".$s;
				}
			}
			closedir($hdir);
		}
	}
	public function doAction()
	{
		$out = "";
		$dir = igk_io_currentRelativePath("Lib");
		$f =  IGKHtmlItem::CreateWebNode("ItemGroup");
		//get all files
		$this->LoadFile($f, "Lib", $dir);
		$out = $f->Render();
		igk_download_content("includeforvs.xml", strlen($out) , $out);

	}
}
?>