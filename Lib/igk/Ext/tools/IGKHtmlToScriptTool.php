<?php
class IGKHtmlToScriptTool extends IGKToolCtrlBase
{
	public function getImageUri(){
		$uri = igk_html_resolv_img_uri(igk_io_baseDir("Lib/igk/Default/R/Img/pics_48x48/tool_c2script.jpeg"));
		return $uri;
	}
	public function convert()
	{
		$c = igk_getr("clHtmlCode");
		$dv =  IGKHtmlItem::CreateWebNode("div");
		$dv->Load($c);

		$out ="<?php\n";
		$out .= self::ConvertToScript($dv);
		$out .="?>";
		igk_download_content("script.php", strlen($out) , $out);
		igk_exit();
	}
	private static function GetAttribute($k)
	{
		$t = $k->Attributes;
		if ($t)
		{
			foreach($t as $m=>$s)
			{
				$out .= "\$d[\"".$m."\"] = \"".trim(IGKHtmlUtils::GetValue($s))."\";\n";
			}
		}
	}
	private static function GetTextContent($k)
	{
			if ($k->Content)
				{
		return  "\$d->Content = \"".trim(IGKHtmlUtils::GetValue($k->Content))."\";\n";
				}
			return null;

	}
	private static function GetChild($dv,  $owner=null)
	{
		$out = "";
		$v_child = $dv->Childs;
			if ($v_child)
			{
				foreach($v_child as $k)
				{

					switch($k->NodeType)
					{
						case IGKXmlNodeType::TEXT:
							if ($owner)
							{
								$out .= self::GetTextContent($k);
							}
						break;
						default:
						if ($owner){
							$out .= "\$d = \$d->add(\"".$k->TagName."\");\n";
						}
						else{
							$out .= "\$d =  IGKHtmlItem::CreateWebNode(\"".$k->TagName."\");\n";
						}
						// if ($k->Content)
						// {
							// $out .= "\$d->Content = ".IGKHtmlUtils::GetAttributeValue($k->Content).";\n";
						// }
						break;
					}
					//render attributes
					$t = $k->Attributes;
					if ($t)
					{
						foreach($t as $m=>$s)
						{
							$out .= "\$d[\"".$m."\"] = \"".trim(IGKHtmlUtils::GetValue($s))."\";\n";
						}
					}
					$out .= self::ConvertToScript($k, true);
				}
			}
			if ($owner)
			{
				$out .= "\$d = \$d->ParentNode;\n";
			}
			else{
				$out .= "unset(\$d);\n";
			}
			return $out;
	}

	private static function ConvertToScript($dv, $owner=null)
	{
		$out = "";
		if ($owner === null)
		{
			$out .= self::GetChild($dv);
		}
		else
		{

		if (($dv !== null) && is_object($dv))
		{
			//detect if has child property

			if ($dv->NodeType == IGKXmlNodeType::TEXT)
			{
				//$out .= self::GetTextContent($dv);
			}
			else
			{
				$out .= self::GetChild($dv, $owner);
			}
		}
		else{
			igk_wln("dv = ".$dv);
		}

		}
		return $out;
	}
	public function doAction()
	{
		$frame = igk_html_frame($this, "tool.htmltoscript");

		//$frame = igk_add_new_frame($ctrl, $id, $closeuri, $target);
		$frame->Title = R::ngets("title.frameConvertHTMLToScript");
		$d = $frame->getBoxContent();
		$d->ClearChilds();

		$frame->Form = $d->addForm();
		$frame->Form["action"] = $this->getUri("convert");
		$frame->Form->Div = $frame->Form->addDiv();
		$frame->Form->Div->addTextArea("clHtmlCode", "code .... ");
		$frame->Form->Div->addInput("confirm", "hidden",1);
		$frame->Form->addHSep();
		$frame->Form->addInput("btn.submit", "submit", R::ngets("btn.convert"));

	}
}

?>