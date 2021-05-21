<?php
// file : igk_html_func_items.php
// author: C.A.D. BONDJE DOUE
// licence: IGKDEV - Balafon @ 2019
// description: Balafon's html functional components
// TODO: parallax not implement

use IGK\Models\Users;
use IGK\System\Html\Dom\Factory;
use function igk_resources_gets as __;
use IGK\Resources\R;

///<summary>function igk_css_link_callback</summary>
///<param name="p"></param>
///<param name="key"></param>
///<param name="href"></param>
/**
 * function igk_css_link_callback
 * @param mixed $p
 * @param mixed $key
 * @param mixed $href
 */
function igk_css_link_callback($p, $key, $href)
{
    $g = $p->getParam($key);
    if ($g && $href) {
        if (is_object($href) && isset($g[$href->refName])) {
            unset($g[$href->refName]);
        } else if (is_string($href) && isset($g[$href])) {
            unset($g[$href]);
        }
    }
    $p->setParam($key, $g);
}
///<summary>encapsulate file_get_contents</summary>
///<param name="file"></param>
/**
 * encapsulate file_get_contents
 * @param mixed $file
 */
function igk_file_content($file)
{
    return file_get_contents($file);
}

/**
 * 
 * @param mixed $hook 
 * @param mixed $args 
 * @return IGKHtmlNoTagNodeItem 
 */
function igk_html_node_yield($hook, ...$args)
{
    $n = igk_html_node_notagnode();
    $n->addObData(
        function () use ($hook, $args) {
            igk_hook($hook, ...$args);
        },
        null
    );
    return $n;
}
///<summary>create text node</summary>
/**
 * create text node
 */
function igk_html_node_text($txt = null)
{
    return igk_createtextnode($txt);
}

///<summary></summary>
///<param name="menuList"></param>
///<param name="selected"></param>
/**
 * 
 * @param mixed $menuList
 * @param mixed $selected
 */
function igk_html_node_configsubmenu($menuList, $selected)
{
    $ul = igk_createnode("ul")->setClass("igk-cnf-content_submenu");
    foreach ($menuList as $k => $v) {
        $li = $ul->addLi();
        $li->add("a", array("href" => $v))->Content = __("cl" . $k);
        if ($selected == $k) {
            $li["class"] = "+igk-cnf-content_submenu_selected";
        } else {
            $li["class"] = "-igk-cnf-content_submenu_selected";
        }
    }
    return $ul;
}
/**
 * create xml node
 */
function igk_html_node_xmlnode($tag)
{
    return igk_createxmlnode($tag);
}
/**
 * call hook to render content on node 
 * @param mixed $hook 
 * @param mixed $args 
 * @return IGKHtmlNoTagNodeItem 
 */
function igk_html_node_hook($hook, ...$args)
{
    $n = igk_html_node_notagnode();
    $n->addObData(function () use ($hook, $args) {
        igk_hook($hook, ...$args);
    }, null);
    return $n;
}
function igk_html_node_extends($parentview)
{
    $p = igk_html_parent_node() ?? die("parent required");
    throw new Exception("Not implemnts");
}

///<summary>helper: loop thru array</summary>
/**
 * helper: loop thru array<
 */
function igk_html_node_loop(array $array, ?callable $callback = null)
{
    $p = igk_html_parent_node() ?? die("parent required");
    $c = new IGKHtmlLooper($array, $p);
    if ($callback) {
        $c->host($callback);
    }
    return $c;
}

///<summary>function igk_html__tabbutton_add</summary>
///<param name="q"></param>
/**
 * function igk_html__tabbutton_add
 * @param mixed $q
 */
function igk_html__tabbutton_add($q)
{
    $n = igk_createnode("div");
    $q->add($n);
    return $n;
}
///<summary>function igk_html_add_context_menu_item</summary>
///<param name="n"></param>
///<param name="uri"></param>
///<param name="display"></param>
/**
 * function igk_html_add_context_menu_item
 * @param mixed $n
 * @param mixed $uri
 * @param mixed $display
 */
function igk_html_add_context_menu_item($n, $uri, $display)
{
    $n->addLi()->addA($uri)->Content = $display;
}
///<summary>function igk_html_callback_ajx_lnksettarget</summary>
///<param name="n"></param>
///<param name="p"></param>
/**
 * function igk_html_callback_ajx_lnksettarget
 * @param mixed $n
 * @param mixed $p
 */
function igk_html_callback_ajx_lnksettarget($n, $p)
{
    $n["igk-lnk-target"] = $p;
}
///<summary>function igk_html_callback_alinktn</summary>
///<param name="n"></param>
/**
 * function igk_html_callback_alinktn
 * @param mixed $n
 */
function igk_html_callback_alinktn($n)
{
    $n->setStyle("width: " . $n->getParam('data')->w . "px; height: " . $n->getParam('data')->h . "px;");
    $i = $n->getParam('data')->img;
    $i["src"] = $n->getParam('data')->src;
    $i["srcdata"] = $n->getParam('data')->src;
    return $n->IsVisible;
}
///<summary>function igk_html_callback_ctrlview_acceptrender</summary>
///<param name="n"></param>
///<param name="s"></param>
///<param name="clear"></param>
/**
 * function igk_html_callback_ctrlview_acceptrender
 * @param mixed $n
 * @param mixed $s
 * @param mixed $clear
 */
function igk_html_callback_ctrlview_acceptrender($n, $s, $clear = 1)
{
    if ($clear) {
        $n->clearChilds();
    }
    $d = $n->getParam("data");
    if ($d) {
        $c = $d->ctrl;
        $v = $d->view;
        if ($c) {
            try {
                ob_start();
                $c->getViewContent($v, $n, 0, $d->params);
                $ss = ob_get_contents();
                if (!empty($ss)) {
                    $n->addText($ss);
                }
                ob_end_clean();
                return true;
            } catch (Exception $ex) {
                igk_ilog("some error:" . $ex->getMessage());
            }
        }
    } else {
        igk_ilog('no data provide for node', __FILE__ . "::" . __FUNCTION__ . "::" . __LINE__);
    }
    return false;
}
///<summary>function igk_html_callback_replacecontent_acceptrender</summary>
///<param name="n"></param>
/**
 * function igk_html_callback_replacecontent_acceptrender
 * @param mixed $n
 */
function igk_html_callback_replacecontent_acceptrender($n)
{
    if (!$n->isVisible)
        return 0;
    $n->clearChilds();
    $n->addBalafonJS()->Content = <<<EOF
(function(q){igk.ready( function(){ ns_igk.ajx.fn.scriptReplaceContent('{$n->method}', '{$n->uri}', q);}); })(igk.getParentScript());
EOF;
    return 1;
}
///<summary>function igk_html_code_copyright_callback</summary>
///<param name="ctrl"></param>
/**
 * function igk_html_code_copyright_callback
 * @param mixed $ctrl
 */
function igk_html_code_copyright_callback($ctrl = null)
{
    $s = ($ctrl ? igk_getv($ctrl->Configs, 'copyright') : null) ?? igk_getv(igk_app()->Configs, 'copyright', IGK_COPYRIGHT);
    if (!empty($s)) {
        $s = igk_html_databinding_treatresponse($s, null, null);
    }
    return $s;
}
///<summary>utility to create a container section</summary>
///<return >primary row</summary>
/**
 * utility to create a container section
 */
function igk_html_create_container_section($t)
{
    $dv = $t->addDiv();
    $ct = $dv->addContainer();
    $r = $ct->addRow();
    return $r;
}

function igk_html_node_walk($tagname, $items, $callback)
{
    $p = igk_html_parent_node();
    if (is_array($items)) {

        array_walk($items, function ($v) use ($p, $callback, $tagname) {
            $callback($p->add($tagname), $v);
        });
    }
    return $p;
}

function igk_html_node_list($items, $callback = null, $ordered = 0)
{
    if ($callback == null) {
        $callback = function ($i, $v) {
            $i->Content = $v;
        };
    }
    $n = igk_createnode($ordered ? "ol" : "ul");
    if (is_array($items)) {
        foreach ($items as $v) {
            $callback($n->li(), $v);
        }
    }
    return $n;
}



function igk_html_node_usesvg($name)
{
    $s = igk_createnode("span");
    $s->Content = igk_svg_use($name);
    return $s;
}

function igk_html_node_menukey($menus, $ctrl = null, $root = "ul", $item = "li", $callback = null)
{
    $n = igk_createnode("ul");
    igk_html_load_menu_array($n, $menus, $item, $root, $ctrl, $callback);
    return $n;
}
/**
 * target the menuu display
 * @param mixed $target 
 * @return object 
 * @throws ReflectionException 
 * @throws Exception 
 */
function igk_html_node_bindMenu($target)
{
    $m = igk_createnode('div');
    $m["igk-data-menu"] = 1;
    $m["igk-data-menu-binding"] = $target;
    return $m;
}


///<summary>build menu </summary>
function igk_html_node_menu($tab, $selected = null, $uriListener = null, $callback = null, ?Users $user = null)
{
    if (!is_array($tab)) {
        igk_die("must set an array of menu items");
    }
    $ul = igk_createnode("ul");
    $ul["class"] = "igk-menu";
    if ($uriListener) {
        if (!is_callable($uriListener)) {
            if (is_object($uriListener) && method_exists($uriListener, "getAppUri")) {
                $uriListener = [$uriListener, "getAppUri"];
            } else
                $uriListener = null;
        }
    }
    $tarray = array(["menu" => $tab, "c" => null, "ul" => $ul]);
    $user = $user ?? Users::currentUser();


    $c = 0;
    while ($q = array_pop($tarray)) {
        $c = $q["c"];
        $tab = $q["menu"];
        $ul = $q["ul"];

        foreach ($tab as $i => $v) {

            // $li = $ul->addLi()->setClass("m-l");
            // if (is_array($v)){
            //     $li->setClass("m-group");
            //     $li->Content = __($i);
            //     $ul = $li->add("ul");
            //     array_push( $tarray, ["menu"=>$v, "c"=>$c+1, "ul"=>$ul]);
            //     continue;
            // }
            $auth = igk_getv($v, "auth");
            if ((is_bool($auth) && !$auth) ||
                ((is_string($auth) && $user && !$user->auth($auth)))
            ) {
                continue;
            }

            $li = $ul->addLi()->setClass("m-l");
            if ($callback)
                $callback(1, $li);
            $uri = $v["uri"];
            if ($uriListener) {
                $uri = $uriListener($uri);
            }
            $a = $li->addA($uri);
            if ($selected && (($selected == $i) || ($selected == igk_getv($v, "selected")))) {
                $li["class"] = "+selected";
            }
            $li["class"] = "+" . igk_css_str2class_name($i);
            if ($icon = igk_getv($v, "icon")) {
                if (is_callable($icon)) {
                    $icon($a);
                } else {
                    $a->google_icons($icon);
                }
            }
            $a->span()->Content = igk_getv($v, "text", __($i));
        }
    }

    return $ul;
}
///<summary>handle used to render css style</symmary>
/**
 * handle used to render css style
 */
function igk_html_handle_cssstyle($n)
{
    $s = $n->Content;
    $tab = array();
    $s = preg_replace_callback(
        "/@(?P<name>" . IGK_IDENTIFIER_RX . "):(?P<value>[^;]+);/i",
        function ($m) use (&$tab) {
            $tab[trim($m["name"])] = $m["value"];
            return "";
        },
        $s
    );
    if (igk_count($tab) > 0) {
        foreach ($tab as $k => $v) {
            $s = str_replace("@" . $k, $v, $s);
        }
    }
    return $n->minfile ? igk_min_script($s) : $s;
}
///<summary>function igk_html_node_a</summary>
///<param name="href"></param>
///<param name="attributes"></param>
///<param name="index"></param>
/**
 * function igk_html_node_a
 * @param mixed $href
 * @param mixed $attributes
 * @param mixed $index
 */
function igk_html_node_a($href = "#", $attributes = null, $index = null)
{
    $a = new IGKHtmlA();
    $a["href"] = $href;
    $a->setIndex($index);
    if ($attributes) {
        $a->AppendAttributes($attributes);
    }
    if ($href != "#") {
        $a->EmptyContent = $href;
    }
    return $a;
}
function igk_html_node_a_post($uri, $complete = '')
{
    $a = igk_html_node_a();
    if (empty($complete)) {
        $complete = 'null';
    }
    $a->on("click", "igk.ajx.post('" .
        $uri
        . "',null, " . $complete . ");");
    return $a;
}
function igk_html_node_a_get($uri, $complete = '')
{
    $a = igk_html_node_a();
    $a->on("click", "igk.ajx.get('" .
        $uri
        . "',null,'" . $complete . "');");
    return $a;
}
///<summary>function igk_html_node_abbr</summary>
///<param name="title"></param>
/**
 * function igk_html_node_abbr
 * @param mixed $title
 */
function igk_html_node_abbr($title = null)
{
    $n = new IGKHtmlItem("abbr");
    $n['class'] = 'igk-abbr';
    $n['title'] = $title;
    return $n;
}
///<summary>function igk_html_node_abtn</summary>
///<param name="uri"></param>
/**
 * function igk_html_node_abtn
 * @param mixed $uri
 */
function igk_html_node_abtn($uri = "#")
{
    $n = igk_createnode("a");
    $n["class"] = "igk-btn";
    $n["href"] = $uri;
    return $n;
}
///<summary>function igk_html_node_aclearsandreload</summary>
/**
 * function igk_html_node_aclearsandreload
 */
function igk_html_node_aclearsandreload()
{
    $ctrl = igk_getctrl(IGK_SESSION_CTRL);
    $n = igk_createXmlNode('a');
    $n["class"] = "igk-btn";
    $n["href"] = $ctrl ? $ctrl->getUri("ClearS") . "&r=" . base64_encode(igk_io_currentUri()) : null;
    $n->Content = __("Clear session and reload");
    return $n;
}
///<summary>build action bar</summary>
///<param name="actions"></param>
/**
 * function igk_html_node_actionbar
 * @param array|callable $actions array or 
 */
function igk_html_node_actionbar($actions = null)
{
    $n = igk_createnode("div");
    $n->setClass("igk-action-bar");
    if ($actions) {
        if (is_callable(($actions))) {
            $actions($n);
        } else if (is_array($actions)) {
            foreach ($actions as $l => $v) {
                $n->addABtn(igk_getv($v, "uri"))->setClass("igk-btn-default")->Content = __(igk_getv($v, "k"));
            }
        }
    }
    return $n;
}

function igk_html_node_checkbox($id, $value = null)
{
    $n = igk_createnode('input');
    $n["type"] = "checkbox";
    $n->setId($id);
    $n["value"] = $value;
    return $n;
}
function igk_html_node_picker_zone($uri, $accepts = "", $complete = null)
{
    $dv = igk_createnode("div");
    $dv->setClass("igk-picker-zone")
        ->setAttribute("igk:picker-zone-data", json_encode([
            "uri" => $uri,
            "accept" => $accepts,
            "complete" => $complete
        ]));
    return $dv;
}
///<summary>function igk_html_node_ajsbutton</summary>
///<param name="code"></param>
///<param name="type"></param>
/**
 * function igk_html_node_ajsbutton
 * @param mixed $code
 * @param mixed $type
 */
function igk_html_node_ajsbutton($code, $type = 'default')
{
    $n = igk_createnode("a");
    $n["class"] = "igk-btn igk-btn-" . $type;
    $n["onclick"] = "javascript: var igk=ns_igk; {$code}; return false;";
    $n["href"] = "#";
    return $n;
}
///<param name="options">JSON Options</param>
/**
 * @param mixed $optionsJSON Options
 */
function igk_html_node_ajspickfile($u, $options = null)
{
    $n = igk_createnode("a");
    $n["class"] = "igk-js-pickfile";
    $js = "{uri:'" . $u . "'";
    if ($options) {
        $js .= ", options:" . $options . "";
    }
    $js .= "}";
    $n["href"] = "#";
    $n["igk:data"] = $js;
    return $n;
}
///<summary> represent an ajx link</summary>
///<param name="replacemode">the content mode . value (content|node)</param>
/**
 *  represent an ajx link
 * @param mixed $replacemodethe content mode . value (content|node)
 */
function igk_html_node_ajxa($lnk = null, $target = "", $replacemode = 'content', $method = "GET")
{
    $dn = new IGKHtmlItem("a");
    $dn->setAttribute("igk-ajx-lnk", 1);
    $dn["href"] = $lnk;
    $dn["igk:replacemode"] = !preg_match("/^(content|node)$/", $replacemode) ? null : (($replacemode == 'content') ? null : $replacemode);
    $dn["igk:method"] = $method != "GET" ? "POST" : null;
    $dn["igk:target"] = $target;
    return $dn;
}
///<summary>function igk_html_node_ajxabutton</summary>
///<param name="link"></param>
/**
 * function igk_html_node_ajxabutton
 * @param mixed $link
 */
function igk_html_node_ajxabutton($link)
{
    $n = igk_html_node_ajxa($link);
    $n["class"] = "igk-btn";
    return $n;
}
///<summary>function igk_html_node_ajxappendto</summary>
///<param name="cibling"></param>
/**
 * function igk_html_node_ajxappendto
 * @param mixed $cibling
 */
function igk_html_node_ajxappendto($cibling)
{
    $n = igk_createnode("div");
    $n["class"] = "igk-ajx-append-view";
    $n["igk:target"] = $cibling;
    return $n;
}
///<summary>change the document code in ajx context</summary>
/**
 * change the document code in ajx context
 */
function igk_html_node_ajxdoctitle($title)
{
    $n = igk_createnode("balafonJS");
    $n->Content = "document.title = \"{$title}\";";
    return $n;
}
///<summary>represent ajx form</summary>
/**
 * represent ajx form
 */
function igk_html_node_ajxform($uri = null, $target = null)
{
    $f = igk_createnode("form");
    $f["action"] = $uri;
    $f["igk-ajx-form"] = 1;
    $f["igk-ajx-form-target"] = $target;
    return $f;
}
///<summary>function igk_html_node_ajxlnkreplace</summary>
///<param name="target"></param>
/**
 * function igk_html_node_ajxlnkreplace
 * @param mixed $target
 */
function igk_html_node_ajxlnkreplace($target = "::")
{
    $n = igk_createnode("div");
    $n["class"] = "igk-winui-ajx-lnk-replace";
    $n["igk-lnk-target"] = $target;
    $n->setCallback("setTarget", "igk_html_callback_ajx_lnksettarget");
    return $n;
}
///<summary>function igk_html_node_ajxpaginationview</summary>
///<param name="baseuri"></param>
///<param name="total"></param>
///<param name="perpage"></param>
///<param name="selected"></param>
///<param name="target"></param>
/**
 * function igk_html_node_ajxpaginationview
 * @param mixed $baseuri
 * @param mixed $total
 * @param mixed $perpage
 * @param mixed $selected
 * @param mixed $target
 */
function igk_html_node_ajxpaginationview($baseuri, $total, $perpage, $selected = 1, $target = null)
{
    return igk_html_node_paginationView($baseuri, $total, $perpage, $selected, 1, null, $target);
}
///AJX
///<summary>ajx div component used to load a file</summary>
///<param name="param">json data. {accept:'image/*, text/xml', start:callback, progress:callback}</param>
/**
 * ajx div component used to load a file
 * @param mixed $paramjson data. {accept:'image/*, text/xml', start:callback, progress:callback}
 */
function igk_html_node_ajxpickfile($uri, $param = null)
{
    $u = igk_createnode('input');
    $u["type"] = "file";
    $u->setClass("igk-ajx-pickfile");
    $u->Attributes->Set("igk:uri", $uri);
    $param && $u->Attributes->Set("igk:param", $param);
    return $u;
}
///<summary>function igk_html_node_ajxreplacecontent</summary>
///<param name="uri"></param>
///<param name="method"></param>
/**
 * function igk_html_node_ajxreplacecontent
 * @param mixed $uri
 * @param mixed $method
 */
function igk_html_node_ajxreplacecontent($uri, $method = "GET")
{
    $n = igk_createnotagnode();
    $n->method = $method;
    $n->uri = $uri;
    $n->setCallback("AcceptRender", "igk_html_callback_replacecontent_acceptrender");
    return $n;
}
///<summary>function igk_html_node_ajxreplacesource</summary>
///<param name="selection"></param>
/**
 * function igk_html_node_ajxreplacesource
 * @param mixed $selection
 */
function igk_html_node_ajxreplacesource($selection)
{
    $n = igk_createnode("div");
    $n["class"] = "igk-ajx-replace-source";
    $n["igk:data"] = $selection;
    return $n;
}
///<summary>function igk_html_node_ajxupdateview</summary>
///<param name="cibling"></param>
/**
 * function igk_html_node_ajxupdateview
 * @param mixed $cibling
 */
function igk_html_node_ajxupdateview($cibling)
{
    $n = igk_createnode("div");
    $n["class"] = "igk-ajx-update-view";
    $n["igk:target"] = $cibling;
    return $n;
}

///<summary>append item that will be used for uri loader</summary>
///<param name="uri">uri to load async</param>
///<param name="append">append result to parent node</param>
function igk_html_node_ajxuriloader($uri, $append = 0)
{
    $n = igk_createnode("div");
    $n->setAttribute("igk:href", $uri);
    $n["class"] = "igk-ajx-uri-loader";
    if ($append) {
        $n["igk:append"] = $append;
    }
    return $n;
}
///<summary>used to render data</summary>
/**
 * used to render data
 */
function igk_html_node_arraydata($tab)
{
    if (!is_array($tab)) {
        igk_die("\$data must must be an array");
    }
    $n = igk_createnode("div");
    $n["class"] = "+igk-array-data";
    foreach ($tab as $k => $v) {
        $cv = $n->addDiv()->setClass("r")->setStyle("display:table-row");
        $cv->addDiv()->setClass("k")->setStyle("display:table-cell")->Content = $k;
        $cv->addDiv()->setClass("v")->setStyle("display:table-cell")->addObData($v);
    }
    return $n;
}
///<summary>function igk_html_node_arraylist</summary>
///<param name="list"></param>
///<param name="tag"></param>
///<param name="callback"></param>
/**
 * function igk_html_node_arraylist
 * @param mixed $list
 * @param mixed $tag
 * @param mixed $closurecallback
 */
function igk_html_node_arraylist($list, $tag = "li", $callback = null)
{
    $n = igk_createnotagnode();
    if (!is_array($list))
        igk_die(__("list is not an array"), __FUNCTION__);
    foreach ($list as $k => $v) {
        $i = $n->add($tag);
        if (!$callback || !$callback($i, $k, $v))
            $i->Content = $v;
    }
    return $n;
}
///<summary>bind article</summary>
/**
 * bind article
 */
function &igk_html_node_article($ctrl, $name, $raw = [], $showAdminOption = 1)
{
    $n = igk_html_node_NoTagNode();
    if ($ctrl === null) {
        $ctrl = igk_getctrl(IGKSysDbController::class);
    }
    igk_html_article($ctrl, trim($name), $n, $raw, null, true, true, $showAdminOption);
    return $n;
}
///<summary>function igk_html_node_backgroundlayer</summary>
/**
 * function igk_html_node_backgroundlayer
 */
function igk_html_node_backgroundlayer()
{
    $n = igk_createnode();
    $n["class"] = "igk-background-layer";
    $n->setCallback("addPic", " \$this->addImg(); return \$this;");
    return $n;
}
///<summary></summary>
///<param name="v"></param>
/**
 * 
 * @param mixed $v
 */
function igk_html_node_badge($v)
{
    $n = igk_createNode("div");
    $n["class"] = "igk-winui-badge";
    $n->setContent($v);
    return $n;
}
///<summary>function igk_html_node_balafonjs</summary>
///<param name="autoremove"></param>
/**
 * function igk_html_node_balafonjs
 * @param mixed $autoremove
 */
function igk_html_node_balafonjs($autoremove = 0)
{
    $c = igk_createXmlNode("script");
    $c["type"] = "text/balafonjs";
    $c["autoremove"] = $autoremove;
    $c->setCallback("handleRender", "igk_html_callback_production_minifycontent");
    return $c;
}
///<summary>function igk_html_node_bindarticle</summary>
///<param name="ctrl"></param>
///<param name="name"></param>
///<param name="data"></param>
///<param name="showAdminOption"></param>
/**
 * function igk_html_node_bindarticle
 * @param mixed $ctrl
 * @param mixed $name
 * @param mixed $data
 * @param mixed $showAdminOption
 */
function &igk_html_node_bindarticle($ctrl, $name, $data = null, $showAdminOption = 1)
{
    $n = igk_createnode();
    igk_html_binddata($ctrl, $n, $name, $data, true, true, $showAdminOption);
    return $n;
}
///<summary>function igk_html_node_bindcontent</summary>
///<param name="content"></param>
///<param name="entries"></param>
///<param name="ctrl"></param>
/**
 * function igk_html_node_bindcontent
 * @param mixed $content
 * @param mixed $entries
 * @param mixed $ctrl
 */
function igk_html_node_bindcontent($content, $entries, $ctrl = null)
{
    $n = igk_html_node_NoTagNode();
    $n->Content = igk_html_bind_content($ctrl, $content, $entries);
    return $n;
}
///<summary>function igk_html_node_blocknode</summary>
/**
 * function igk_html_node_blocknode
 */
function &igk_html_node_blocknode()
{
    $n = igk_createXmlNode("igk-block-viewitem");
    $n->setCallback("getIsVisible", "return \$this->HasChilds ;");
    $n->setCallback("getIsRenderTagName", "return false;");
    return $n;
}
function igk_html_node_submit($name = null, $value = null, $type = "submit")
{
    $n = igk_createnode("input");
    $n["class"] = "igk-form-control clsubmit";
    $n["type"] = $type;
    if ($name) {
        $n->setAttribute("name", $name);
    }
    if (!$value) {
        $value = __("submit");
    }
    $n["value"] = $value;
    return $n;
}
///<summary></summary>
///<param name="listener"></param>
/**
 * 
 * @param mixed $listener
 */
function igk_html_node_bmcloginpage($listener)
{
    $bmc = igk_require_module("igk/BMC");
    $dv = igk_createnode("div");
    $dv["class"] = "disptable fit";
    $g = $dv->addDiv()->setClass("disptabc alignm alignc");
    $frm = $g->addDiv()->setClass("dispib alignl")->setStyle("width:300px;")->addForm();
    $frm["action"] = $listener->getAppUri("login");
    igk_html_form_initfield($frm);
    $shape = $frm->addBMCShape()->setClass('fitw')->setStyle("padding:20px");
    $shape->addNotifyHost(IGKEnvConst::NotifyLogin, false);
    $shape->addBMCtextfield("clLogin")->addBMCRipple();
    $shape->addBMCtextfield("clPwd", "password")->addBMCRipple();
    $shape->addBMCButton("btn.connect", 1)->Content = __("Connect");
    if (($redirect = base64_decode(igk_getr("q"))) || ($redirect = igk_getr("goodUri")))
        $shape->addInput("goodUri", "hidden", $redirect);
    $shape->addInput("badUri", "hidden", $listener->getAppUri("connect"));
    $frm->addBalafonJS()->Content = "igk.winui.form.validate(['clLogin', 'clPwd']);";
    return $dv;
}
///<summary>function igk_html_node_bodybox</summary>
/**
 * function igk_html_node_bodybox
 */
function igk_html_node_bodybox()
{
    $n = igk_createnode();
    $n->setClass("igk-bodybox fit igk-parentscroll igk-powered-viewer overflow-y-a");
    return $n;
}
///<summary>function igk_html_node_btn</summary>
///<param name="name"></param>
///<param name="value"></param>
///<param name="type"></param>
///<param name="attributes"></param>
/**
 * function igk_html_node_btn
 * @param mixed $name
 * @param mixed $value
 * @param mixed $type
 * @param mixed $attributes
 */
function igk_html_node_btn($name, $value, $type = "submit", $attributes = null)
{
    $btn = igk_createnode("input");
    $btn["id"] =
        $btn["name"] = $name;
    $btn["value"] = $value;
    $btn["type"] = $type;
    $btn["class"] = "cl" . $type;
    return $btn;
}
///<summary>build select node</summary>
/**
 * build select node
 */
function igk_html_node_buildselect($name, $rows, $idk, $callback = null, $selected = null)
{
    $sl = igk_createnode("select")->setId($name)->setClass("igk-form-control");
    foreach ($rows as  $v) {
        $opt = $sl->add("option");
        $opt["value"] = $v->$idk;
        $opt->Content = $callback ? $callback($v) : $v;
        if ($selected && ($v->$idk == $selected)) {
            $opt["selected"] = 1;
        }
    }
    return $sl;
}
///<summary>function igk_html_node_bullet</summary>
/**
 * function igk_html_node_bullet
 */
function igk_html_node_bullet()
{
    $n = igk_createnode();
    $n->setClass("igk-bullet");
    return $n;
}
///<summary>create a button </summary>
/**
 * create a button
 */
function igk_html_node_button($id = null, $buttontype = 0, $type = null)
{
    $n = new IGKHtmlItem("button");
    $n["class"] = "igk-btn";
    if ($type)
        $n["class"] = "+igk-btn-${type}";
    $n["type"] = $buttontype ? "submit" : "button";
    $n->setId($id);
    $n->setCallback('setUri', igk_create_expression_callback(
        <<<EOF
\$u= \$fc_args[0];\$n["onclick"]="javascript: document.location = '\$u'; return false;"; return \$n;
EOF,
        array("n" => $n)
    ));
    return $n;
}
///<summary>function igk_html_node_canvabalafonscript</summary>
///<param name="uri"></param>
/**
 * function igk_html_node_canvabalafonscript
 * @param mixed $uri
 */
function &igk_html_node_canvabalafonscript($uri = null)
{
    $n = igk_createnode();
    $n["class"] = "igk-canva-gkds-obj";
    $n["uri"] = $uri;
    $n["igk-canva-gkds-obj-data"] = "{uri:'{$uri}',init:function(ctx){ctx.strokeStyle = 'transparent';ctx.fillStyle = this.getComputedStyle('color'); }}";
    $n->Content = "&nbsp;";
    return $n;
}
///<summary>create a canva editor surface</summary>
/**
 * create a canva editor surface
 */
function igk_html_node_canvaeditorsurface()
{
    $n = igk_createnode();
    $n->setClass("igk-canva-editor");
    return $n;
}
///<summary>function igk_html_node_cardid</summary>
///<param name="src"></param>
///<param name="ctrl"></param>
/**
 * function igk_html_node_cardid
 * @param mixed $src
 * @param mixed $ctrl
 */
function igk_html_node_cardid($src = null, $ctrl = null)
{
    $n = igk_createnode("div");
    $n->setClass("igk-card-id");
    if ($src) {
        if (!IGKValidator::IsUri($src)) {
            if (file_exists($src)) {
                $src = new IGKHtmlRelativeUriValueAttribute(igk_io_baseRelativeUri($src));
            } else
                $src = new IGKHtmlRelativeUriValueAttribute(igk_io_baseRelativeUri(dirname($ctrl->getDeclaredFileName()) . "/" . $src));
        }
    }
    $n["igk:link"] = $src;
    return $n;
}
///<summary>function igk_html_node_cell</summary>
/**
 * function igk_html_node_cell
 */
function igk_html_node_cell()
{
    $n = igk_createnode();
    $n["class"] = "disptabc";
    return $n;
}
///<summary>function igk_html_node_cellrow</summary>
/**
 * function igk_html_node_cellrow
 */
function igk_html_node_cellrow()
{
    $n = igk_createnode();
    $n["class"] = "igk-cell-row";
    return $n;
}
///<summary>function igk_html_node_centerbox</summary>
///<param name="content"></param>
/**
 * function igk_html_node_centerbox
 * @param mixed $content
 */
function igk_html_node_centerbox($content = null)
{
    $n = igk_createnode('div');
    $n->setClass("igk-centerbox");
    $n->m_c = $n->add("div");
    $n->m_c->setClass("disptabc alignc alignm");
    $n->m_c->setParentHost($n);
    $n->m_c->Content = $content;
    $n->setCallback("getBox", "return \$this->m_c;");
    return $n;
}
///<summary>function igk_html_node_circlewaiter</summary>
/**
 * function igk_html_node_circlewaiter
 */
function igk_html_node_circlewaiter()
{
    $n = igk_createnode();
    $n->setClass("igk-circle-waiter");
    return $n;
}
function igk_html_node_boxdialog()
{
    $n = igk_createnode("div");
    $n->setClass("igk-dialog");
    return $n;
}
function igk_html_node_dialog_circle_waiter()
{
    $bar = igk_createnode("boxdialog")->setClass("igk-dialog");
    $bar->div()->setClass("flex fit flex-a-center")->circlewaiter();
    return $bar;
}
///<summary></summary>
/**
 * 
 */
function igk_html_node_clearboth()
{
    $n = igk_createNode("div");
    $n["style"] = "clear:both;";
    return $n;
}
///<summary>function igk_html_node_clearfloatbox</summary>
///<param name="t"></param>
/**
 * function igk_html_node_clearfloatbox
 * @param mixed $t
 */
function igk_html_node_clearfloatbox($t = 'b')
{
    $n = igk_createnode("br");
    $n->setClass("clear" . $t);
    return $n;
}
///<summary>function igk_html_node_cleartab</summary>
/**
 * function igk_html_node_cleartab
 */
function igk_html_node_cleartab()
{
    $n = igk_createnode();
    $n["class"] = "igk-cleartab";
    return $n;
}
///<summary>function igk_html_node_clonenode</summary>
///<param name="node"></param>
/**
 * function igk_html_node_clonenode
 * @param mixed $node
 */
function igk_html_node_clonenode($node)
{
    if (($node == null) || !is_object($node))
        igk_die("Can't clone node . {{node}} not valid");
    if (!is_subclass_of(get_class($node), IGKHtmlItemBase::class)) {
        throw new Exception("not a valid item");
    }
    $n = igk_createnode("igk-clone-node");
    $n->setParam("self::targetnode", $node);
    $n->setCallback("getIsRenderTagName", "return false;");
    $n->setCallback("getTargetNode", "return \$this->getParam('self::targetnode'); ");
    $n->setCallback("getIsVisible", "\$v =  \$this->getTargetNode() && \$this->getTargetNode()->IsVisible; return \$v;");
    $n->setCallback("GetRenderingChildren", "return array(\$this->getTargetNode()); ");
    return $n;
}
///<summary>create base php code</summary>
/**
 * create base php code
 */
function igk_html_node_code($type = 'php')
{
    $n = new IGKHtmlItem("code");
    $n["class"] = "igk-code";
    $n["igk-code"] = $type;
    return $n;
}
///<summary>function igk_html_node_col</summary>
///<param name="clname"></param>
/**
 * function igk_html_node_col
 * @param mixed $clname
 */
function igk_html_node_col($clname = null)
{
    if ($clname) {
        $clname = " " . $clname;
    }
    return igk_createnode("div")->setAttributes(array("class" => "igk-col" . $clname));
}
///<summary>column view item</summary>
/**
 * column view item
 */
function igk_html_node_colviewbox()
{
    $n = igk_createnode("div");
    $n->setClass("igk-col-view-box");
    return $n;
}
///<summary></summary>
///<param name="id">identify the node</param>
///<param name="tab">list o items</param>
///<param name="options" default="null"> options to manage the combobox</param>
/**
 * 
 * @param mixed $id
 * @param mixed $tab
 * @param mixed $options the default value is null
 */
function igk_html_node_combobox($id, $tab, $options = null)
{
    $n = igk_createnode("select")->setId($id);
    $n["class"] = "igk-winui-combobox";
    igk_html_build_select_option($n, $tab, $options ?? (object)[
        "valuekey" => "value",
        "displaykey" => "text"
    ]);
    return $n;
}
///<summary>function igk_html_node_communitylink</summary>
///<param name="name"></param>
///<param name="link"></param>
/**
 * function igk_html_node_communitylink
 * @param mixed $name
 * @param mixed $link
 */
function igk_html_node_communitylink($name, $link)
{
    $s = igk_createnode("div");
    $s["class"] = "igk-comm-lnk";
    $s["igk:title"] = $name;
    $s["href"] = $link;
    return $s;
}
///<summary>function igk_html_node_communitylinks</summary>
///<param name="tab"></param>
/**
 * function igk_html_node_communitylinks
 * @param mixed $tab
 */
function igk_html_node_communitylinks($tab)
{
    $ul = igk_createnode("ul")->setClass("igk-com-links");
    if ($tab) {
        foreach ($tab as $k => $v) {
            if (is_object($v) || is_array($v)) {
                $uri = igk_getv($v, "uri");
                if (($c = igk_getv($v, "auth")) && (is_callable($c) && (!$c()))) {
                    continue;
                }
            } else {
                $uri = $v;
            }
            $ul->add("li")->addA($uri)->setClass($k)->Content = igk_svg_use($k);
        }
    }
    return $ul;
}
///<summary>used to create component</summary>
/**
 * used to create component
 */
function igk_html_node_component($listener, $typename, $regName, $unregister = 0)
{
    if ($unregister) {
        $b = $listener->getParam(IGK_NAMED_NODE_PARAM);
        $h = igk_getv($b, $regName);
        if ($h) {
            $h->dispose();
            unset($b[$regName]);
            $listener->setParam(IGK_NAMED_NODE_PARAM, $b);
        }
    }
    return igk_html_node_livenodecallback($listener, $regName, function ($l, $n) use ($typename) {
        $c = igk_createnode($typename);
        $c->setComponentListener($l, $l->getParam("sys://component/params/{$n}"));
        return $c;
    });
}
///<summary>create a node that will only be visible on conditional callback is evaluted to true</summary>
/**
 * create a node that will only be visible on conditional callback is evaluted to true
 */
function igk_html_node_conditionalnode($conditioncallback)
{
    $n = igk_createnode(__FUNCTION__);
    $n->setCallback("getIsRenderTagName", "return false;");
    $n->setCallback("getIsVisible", "igk_html_visibleConditionalNode");
    return $n;
}
///<summary>function igk_html_node_container</summary>
/**
 * function igk_html_node_container
 */
function igk_html_node_container()
{
    $n = igk_createnode('div');
    $n["class"] = "igk-container";
    return $n;
}
///<summary>function igk_html_node_contextmenu</summary>
/**
 * function igk_html_node_contextmenu
 */
function igk_html_node_contextmenu()
{
    $n = igk_createnode();
    $n["class"] = "igk-context-menu";
    $n->setCallback("addItem", "igk_html_add_context_menu_item");
    return $n;
}
///<summary>function igk_html_node_cookiewarning</summary>
///<param name="warnurl"></param>
/**
 * function igk_html_node_cookiewarning
 * @param mixed $warnurl
 */
function igk_html_node_cookiewarning($warnurl = null)
{
    $n = igk_createnode("div");
    $n["class"] = "igk-cookie-warn";
    $n["igk-domain-ewarn"] = "local.com.ewarn";
    $n->Content = __("By using this site, you agree with cookies usage. you can erase or stop them in your navigation parameters." . "<a href=\"" . $warnurl . "\">" . __("For more infos") . "</a>");
    $n->addDiv()->setClass("close")->addABtn("#")->Content = igk_svg_use("drop");
    $n->setCallback("IsVisible", "return igk_get_global_cookie(\"local.com.warn\", 0)!=0;");
    return $n;
}
///<summary>function igk_html_node_copyright</summary>
///<param name="ctrl"></param>
/**
 * function igk_html_node_copyright
 * @param mixed $ctrl
 */
function igk_html_node_copyright($ctrl = null)
{
    $n = igk_createnode();
    $n->setClass("igk-copyright");
    $n->setCallback("getCopyright", igk_create_func_callback("igk_html_code_copyright_callback", [$ctrl]));
    $g = new IGKValueListener($n, "getCopyright");
    $n->Content = $g;
    return $n;
}
///<summary>function igk_html_node_csscomponentstyle</summary>
///<param name="file"></param>
///<param name="host"></param>
/**
 * function igk_html_node_csscomponentstyle
 * @param mixed $file
 * @param mixed $host
 */
function igk_html_node_csscomponentstyle($file, $host = null)
{
    $n = IGKCssComponentStyle::getInstance()->regFile($file, $host);
    return $n;
}

function igk_html_node_deferCssLink($href, $temp = 0)
{
    $p = igk_html_parent_node();
    $key = "sys://cssLink/" . __FUNCTION__;
    $b = $href && !is_string($href) ? igk_getv($href, "callback") : null;
    $uri = "";
    if (!($scriptLoading = $p->getParam($key))) {
        $scriptLoading = igk_html_node_onrendercallback(function ($options = null) use (&$p, $key) {
            $i = $p->getParam($key);
            $sc = $i->getParam("loadscript");
            $o = "";
            if ($tm = array_keys($sc)) {
                $o .= "<script type=\"text/javascript\">";
                $o .= "igk.ready(function(){ igk.css.loadLinks(" . json_encode($tm) . "); });";
                $o .= "</script>";
            }
            $i->Content = $o;
            return 1;
        });
        $p->setParam($key, $scriptLoading);
        $p->add($scriptLoading);
    }
    if (!($lm = $scriptLoading->getParam("loadscript"))) {
        $lm = [];
    }
    if (is_callable($b)) {
        $mtrep = igk_getv($href, "params");
        if ($uri = call_user_func_array($b, $mtrep)) {

            $s = $uri->getValue();
            $lm[$s] = $uri;
        }
    } else {
        $lm[$href] = $href;
    }
    $scriptLoading->setParam("loadscript", $lm);
    return null;
}
///<summary> add css link </summary>
///<param name="href">mixed : string, object(that implement getValue function and refName properties) or array</param>
/**
 *  add css link
 * @param mixed $hrefmixed : string, object(that implement getValue function and refName properties) or array
 */
function igk_html_node_csslink($href, $temp = 0, $defer = 0)
{
    $p = igk_html_parent_node();
    if ($p && (strtolower($p->TagName) !== "head"))
        igk_die("/!\\ can't add css link to non header. " . strtolower($p->TagName) . " " . get_class($p));
    $key = "sys://cssLink/" . __FUNCTION__;
    $g = null;
    $g = $p->getParam($key) ?? new IGKHtmlHeaderLinkHost();
    $key_ref = $href;
    $b = $href && !is_string($href) ? igk_getv($href, "callback") : null;
    if (is_callable($b)) {
        ($key_ref = igk_getv($href, "refName")) || igk_die(__("refName required"));
        $m = $g->getLink($key_ref);
        if ($m)
            return $m;
        $mtrep = igk_getv($href, "params");
        $m = new IGKHtmlItem("link");
        $mtrep[] = $m;
        $uri = call_user_func_array($b, $mtrep);
        $m->setAttribute("href", $uri);
        $m->setAttribute("rel", "stylesheet");
        if ($defer)
            $m->activate("defer");
        $g->add($key_ref, $m, $temp);
        $p->setParam($key, $g);
        return $m;
    } else {
        if (is_array($href)) {
            $key_ref = igk_getv($href, "refName") ?? igk_die(__("Css Link reference 'refName' not found in array"));
        } else if (is_object($href)) {
            $key_ref = igk_getv($href, "refName") ?? igk_die(__("Css Link reference 'refName' not found not found in object"));
        }
        $m = $g->getLink($key_ref);
        if ($m)
            return $m;
    }

    $m = new IGKHtmlItem("link");
    $m->setAttribute("href", new IGKHtmlRelativeUriValueAttribute($key_ref));
    $m->setAttribute("rel", "stylesheet");
    if ($defer)
        $m->activate("defer");
    $g->add($key_ref, $m, $temp);
    $p->setParam($key, $g);
    return $m;
}
///<summary>represent a css style element</summary>
/**
 * represent a css style element
 */
function igk_html_node_cssstyle($id, $minfile = 1)
{
    $o = igk_html_parent_node();
    $k = __FUNCTION__ . ":/{$id}";
    if ($o) {
        $g = $o->getParam($k);
        if ($g != null)
            return $g;
    }
    $s = igk_createnode("style");
    $s["type"] = "text/css";
    $s->minfile = $minfile;
    $s->setCallback("handleRender", "igk_html_handle_cssStyle");
    $o->setParam($k, $s);
    return $s;
}
///<summary>function igk_html_node_ctrlview</summary>
///<param name="view">view to show</param>
///<param name="ctrl">controller that will handle the view</param>
///<param name="params">params to pas to views</param>
/**
 * function igk_html_node_ctrlview
 * @param mixed $viewview to show
 * @param mixed $ctrlcontroller that will handle the view
 * @param mixed $paramsparams to pas to views
 */
function igk_html_node_ctrlview($view, $ctrl, $params = null)
{
    $n = igk_createnotagnode();
    $n->setCallback("AcceptRender", "igk_html_callback_ctrlview_acceptrender");
    $n->setParam("data", (object)array("view" => $view, "ctrl" => $ctrl, "params" => $params));
    return $n;
}
///<summary>function igk_html_node_dbdataschema</summary>
/**
 * function igk_html_node_dbdataschema
 */
function igk_html_node_dbdataschema()
{
    $rep = igk_createXmlNode(IGK_SCHEMA_TAGNAME);
    $rep["Date"] = date('Y-m-d');
    $rep["Version"] = "1.0";
    return $rep;
}
///<summary>function igk_html_node_dbentriescallback</summary>
///<param name="target"></param>
///<param name="callback"></param>
///<param name="queryResult"></param>
///<param name="fallback"></param>
/**
 * function igk_html_node_dbentriescallback
 * @param mixed $target
 * @param mixed $closurecallback
 * @param mixed $queryResult
 * @param mixed $fallback
 */
function igk_html_node_dbentriescallback($target, $callback, $queryResult, $fallback = null)
{
    if ($queryResult && ($queryResult->RowCount > 0)) {
        foreach ($queryResult->Rows as $v) {
            $target->add($callback($v));
        }
    } else {
        if ($fallback) {
            $c = $fallback();
            if ($c)
                $target->add($c);
        }
    }
    return $target;
}
///<summary>function igk_html_node_dbresult</summary>
///<param name="r"></param>
///<param name="max"></param>
/**
 * function igk_html_node_dbresult
 * @param mixed $r
 * @param mixed $max
 */
function igk_html_node_dbresult($r, $uri, $selected, $max = -1, $target = null)
{
    $n = igk_createnotagnode();
    if ($r)
        $n->add(igk_db_view_result_node($r, $uri, $selected, $max, $target));
    return $n;
}
///<summary>DataBase select component </summary>
/**
 * DataBase select component
 */
function igk_html_node_dbselect($name, $result, $callback = null, $valuekey = IGK_FD_ID)
{
    $n = new IGKHtmlItem("select");
    $n->setClass("clselect");
    $n->setId($name);
    $callback = $callback === null ? igk_get_env("sys://table/" . igk_getv(array_keys($result->getTables()), 0)) : $callback;
    foreach ($result->Rows as  $v) {
        $g = $n->add("option");
        $g->setAttribute("value", $v->$valuekey);
        if ($callback !== null) {
            if (is_callable($callback)) {
                $g->setContent($callback($v, $g));
                continue;
            }
        }
        $g->setContent(igk_display($v));
    }
    return $n;
}
///<summary>create a dialog host that will not being displayed</summary>
///<param name="title"></param>
/**
 *  create a dialog host that will not being displayed<
 * @param mixed $title
 */
function igk_html_node_dialog($title = null)
{
    $n = igk_createnode("igk-dialog");
    $n["class"] = "igk-dialog dispn";
    $n["igk:title"] = $title;
    return $n;
}
///<summary>create a dialog box</summary>
///<param name="title"></param>
/**
 * create a dialog box
 * @param mixed $title
 */
function igk_html_node_dialogbox($title)
{
    $n = igk_createnode("div");
    $n["class"] = "igk-winui-dialogbox";
    $t = $n->addDiv()->setClass("title");
    $t->addDiv()->setClass("cls")->addSvgSymbol("close_btn_2");
    $t->addSectionTitle(4)->Content = $title;
    $t->addDiv()->setClass("opts")->addSvgSymbol("v_dot_3");
    return $n;
}
///<summary>create a dialog box content</summary>
/**
 *  create a dialog box content
 */
function igk_html_node_dialogboxcontent()
{
    $n = igk_createnode("div");
    $n["class"] = "dialog-c";
    return $n;
}
///<summary>create dialogbox options node</summary>
/**
 * create dialogbox options node
 */
function igk_html_node_dialogboxoptions()
{
    $o = igk_html_parent_node();
    $k = "sys://component/" . __FUNCTION__;
    $s = $o->getParam($k);
    if ($s === null) {
        $n = igk_createnode("ul");
        $n["class"] = "d-opts dispn";
        $o->getParam($k, $n);
        return $n;
    }
    return $s;
}


function igk_html_node_actions($actionlist)
{
    $p = igk_html_parent_node() ?? igk_createnode("NoTagNode");
    $actionBar = $p->addActionBar();
    foreach ($actionlist as $k => $v) {
        $i = $actionBar->add("input");
        $i->setId($k);
        $t = igk_getv($v, "type", "button");
        $m = igk_getv($v, "value", $k);
        switch ($t) {
            default:
                $i["type"] = igk_getv($v, "type", "button");
                $i["class"] = "igk-btn";
                $i["value"] = $m;
                break;
        }
    }
    return $p;
}


///<summary>function igk_html_node_divcontainer</summary>
///<param name="attribs"></param>
/**
 * function igk_html_node_divcontainer
 * @param mixed $attribs
 */
function igk_html_node_divcontainer($attribs = null)
{
    $n = igk_createnode();
    $n->addContainer();
    return $n;
}
///<summary>function igk_html_node_domainlink</summary>
///<param name="src"></param>
/**
 * function igk_html_node_domainlink
 * @param mixed $src
 */
function igk_html_node_domainlink($src)
{
    $n = igk_createnode("a");
    $n->domainLink = 1;
    $n["href"] = $src;
    $n->setParam("lnk", $src);
    return $n;
}
///<summary>engine control editor</summary>
/**
 * engine control editor
 */
function igk_html_node_enginecontrol($name, $type)
{
    $p = igk_html_parent_node();
    $engine = igk_get_builder_engine($name, $p);
    $c = "add" . $type;
    call_user_func_array(array($engine, $c), array_slice(func_get_args(), 2));
    return $engine;
}
///<summary>function igk_html_node_error404</summary>
///<param name="title"></param>
///<param name="m"></param>
/**
 * function igk_html_node_error404
 * @param mixed $title
 * @param mixed $m
 */
function igk_html_node_error404($title, $m)
{
    $n = igk_createnode("div");
    $n["class"] = "error404";
    igk_html_title($n, $title);
    $box = $n->addPanelBox();
    $box->add($m);
    $n->Box = $box;
    return $n;
}
///<summary>function igk_html_node_expo</summary>
/**
 * function igk_html_node_expo
 */
function igk_html_node_expo()
{
    $n = igk_createnode("span");
    $n["class"] = "igk-expo";
    return $n;
}
///<summary> a BJS's class control. used to show on scroll visibility. </summary>
/**
 *  a BJS's class control. used to show on scroll visibility.
 */
function igk_html_node_fixedactionbar($targetid = "", $offset = 1)
{
    $n = igk_createnode("div");
    $n->setClass("igk-fixed-action-bar");
    $n->setAttribute("igk-offset", $offset);
    $n["igk-target"] = $targetid;
    return $n;
}
///<summary>create a font symbol.</summary>
/**
 * create a font symbol.
 */
function igk_html_node_fontsymbol($name, $code)
{
    $n = igk_createnode("span");
    $n["class"] = "+igk-font-symbol " . "ft-" . $name;
    $o = "";
    if (is_string($code)) {
        $code = trim($code);
        if (preg_match("/^(0x|#)/", $code)) {
            $code = preg_replace_callback(
                "/^(0x|#)/",
                function () {
                    return "";
                },
                $code
            );
        }
        $o = '&#x' . $code . ';';
    } else {
        $o = '&#x' . IGKNumber::ToBase($code, 16, 4) . ';';
    }
    $n->Content = $o;
    return $n;
}
///<summary>function igk_html_node_formactionbutton</summary>
///<param name="id"></param>
///<param name="value"></param>
///<param name="uri"></param>
///<param name="method"></param>
///<param name="text"></param>
/**
 * function igk_html_node_formactionbutton
 * @param mixed $id
 * @param mixed $value
 * @param mixed $uri
 * @param mixed $method
 * @param mixed $text
 */
function igk_html_node_formactionbutton($id, $value, $uri, $method = "GET", $text = null)
{
    $f = igk_createnode("form");
    $f["action"] = $uri;
    $f->addButton($id, 1)->Content = $text ?? __("btn." . $id);
    return $f;
}
///<summary></summary>
///<param name="formfields"></param>
///<param name="engine" default="null"></param>
/**
 * 
 * @param mixed $formfields
 * @param mixed $engine the default value is null
 */
function igk_html_node_formfields($formfields, $engine = null)
{
    $n = igk_html_node_notagnode();
    if ($engine == null) {
        $o = igk_html_utils_buildformfield($formfields);
        $n->addText($o);
    }
    return $n;
}
///<summary></summary>
/**
 * 
 */
function igk_html_node_formgroup()
{
    $n = igk_createnode('div');
    $n["class"] = "igk-form-group";
    return $n;
}
///<summary>function igk_html_node_formusagecondition</summary>
/**
 * function igk_html_node_formusagecondition
 */
function igk_html_node_formusagecondition()
{
    $dd = igk_createnode();
    $dd->setClass("disptable fitw");
    $dd->addDiv()->setClass("disptabc")->addInput("clAcceptCondition", "checkbox")->setAttribute("checked", 1);
    $dd->addDiv()->setClass("disptabc fitw")->addDiv()->add("label")->setAttribute("for", "clAcceptCondition")->setStyle("padding-left:10px")->Content = new IGKHtmlUsageCondition();
    return $dd;
}
///<summary>function igk_html_node_frame</summary>
/**
 * function igk_html_node_frame
 */
function igk_html_node_frame()
{
    return igk_createnode("div")->setAttributes(array("class" => "igk-ui-frame frame"));
}
///<summary>function igk_html_node_framedialog</summary>
///<param name="id"></param>
///<param name="ctrl"></param>
///<param name="closeuri"></param>
///<param name="reloadcallback"></param>
/**
 * function igk_html_node_framedialog
 * @param mixed $id
 * @param mixed $ctrl
 * @param mixed $closeuri
 * @param mixed $reloadcallback
 */
function igk_html_node_framedialog($id, $ctrl, $closeuri = ".", $reloadcallback = null)
{
    $frame = igk_getctrl(IGK_FRAME_CTRL)->createFrame($id, $ctrl, $closeuri, $reloadcallback);
    return $frame;
}
///<summary></summary>
///<param name="ctrl"></param>
///<param name="folder"></param>
///<param name="ignorethumb" default="1"></param>
/**
 * 
 * @param mixed $ctrl
 * @param mixed $folder
 * @param mixed $ignorethumb the default value is 1
 */
function igk_html_node_galleryfolder($ctrl, $folder, $ignorethumb = 1)
{
    $n = igk_createnode("div");
    $n["class"] = "igk-gallery-folder";
    if (is_dir($folder)) {
        $thumb = igk_html_uri($folder . "/.thumb");
        $resolver = IGKResourceUriResolver::getInstance();
        foreach (igk_io_getfiles($folder) as $img) {
            if ($ignorethumb && strstr($img, $thumb)) {
                continue;
            }
            $i = $n->addDiv()->setClass("igk-col no-overflow item")->setStyle("height: 210px; padding-bottom: 20px;")->addImg()->setSrc($img);
            $i["alt"] = basename($img);
            $i["class"] = "fit fitc";
        }
    }
    return $n;
}
function igk_html_node_headerbar()
{
    $n = igk_createnode("div");
    $n["class"] = "igk-header";
    return $n;
}
///<summary>build igk header bar</summary>
///<param name="title">title to show</param>
///<param name="baseuri">base uri</param>
/**
 * function igk_html_node_headerbar
 * @param string $title
 * @param mixed $baseuri
 */
function igk_html_node_igkheaderbar($title, $baseuri = null)
{
    $baseuri = $baseuri ? $baseuri : igk_io_baseDomainUri();
    $n = igk_createnode("div");
    $r = $n->addRow()->setClass("no-margin");
    $h1 = $r->addDiv()->setClass(" igk-col-lg-12-2 fith presentation");
    $t = $h1->addDiv()->addA($baseuri)->setClass("dispb no-decoration");
    $t->add("span")->setClass("dispib posr")->setStyle("left:10px; top:12px;")->Content = igk_web_get_config("company_name");
    $t->addDiv()->setClass("igk-title-4")->Content = $title;
    $n->m_Box = $r->addDiv();
    $n->m_Box->setClass("igk-col-lg-12-10 .ibox");
    return $n;
}
///<summary>function igk_html_node_hlineseparator</summary>
/**
 * function igk_html_node_hlineseparator
 */
function igk_html_node_hlineseparator()
{
    $n = igk_createnode("div");
    $n["class"] = "igk-hline-sep";
    return $n;
}
///<summary>function igk_html_node_horizontalpageview</summary>
/**
 * function igk_html_node_horizontalpageview
 */
function igk_html_node_horizontalpageview()
{
    $n = igk_createnode("div");
    $n["class"] = "igk-hpageview";
    return $n;
}
///<summary>Hosted object data. will pass the current node to callback as first argument</summary>
/**
 * Hosted object data. will pass the current node to callback as first argument
 */
function igk_html_node_hostobdata($callback, $host = null)
{
    $p = $q = igk_html_parent_node();
    $index = 1;
    if ($q === null) {
        $p = $host;
        $index = 2;
    }
    if ($p == null)
        igk_die("no parent to host");
    $tab = array_merge(array($p), array_slice(func_get_args(), $index));
    $n = igk_createnotagnode();
    IGKOb::Start();
    call_user_func_array($callback, $tab);
    $s = IGKOb::Content();
    IGKOb::Clear();
    $n->addText()->Content = $s;
    return $n;
}
///<summary>function igk_html_node_hscrollbar</summary>
/**
 * function igk_html_node_hscrollbar
 */
function igk_html_node_hscrollbar()
{
    $n = igk_createnode();
    $n["class"] = "igk-hscroll";
    return $n;
}
///<summary>function igk_html_node_hsep</summary>
/**
 * function igk_html_node_hsep
 */
function igk_html_node_hsep()
{
    return igk_html_node_Separator("horizontal");
}
///<summary>function igk_html_node_htmlnode</summary>
///<param name="tag"></param>
/**
 * function igk_html_node_htmlnode
 * @param mixed $tag
 */
function igk_html_node_htmlnode($tag)
{
    return new IGKHtmlItem($tag);
}
///<summary>used to render a pick a huebar value</summary>
/**
 * used to render a pick a huebar value
 */
function igk_html_node_huebar()
{
    $n = igk_createnode("div");
    $n["class"] = "igk-winui-huebar";
    $n->addDiv()->setClass("cur");
    $n->addBalafonJS()->Content = "igk.winui.huebar.init(); ";
    return $n;
}
///<summary>function igk_html_node_igkcopyright</summary>
/**
 * function igk_html_node_igkcopyright
 */
function igk_html_node_igkcopyright()
{
    $n = igk_createnode();
    $n->setClass("igk-copyright");
    $n->setCallback("getCopyright", "return igk_sys_copyright();");
    $g = new IGKValueListener($n, "getCopyright");
    $n->Content = $g;
    return $n;
}
///<summary>function igk_html_node_igkgloballangselector</summary>
/**
 * function igk_html_node_igkgloballangselector
 */
function igk_html_node_igkgloballangselector()
{
    $dv = igk_createnode("div");
    $sl = $dv->add("select")->setId("lang")->setClass("-igk-control -igk-form-control -clselect");
    $gt = igk_app()->Configs->default_lang;
    $uri = igk_ctrl_get_cmd_uri(igk_sys_ctrl(), "changeLang_ajx");
    $sl["onchange"] = " ns_igk.ajx.get('{$uri}/'+this.value, null, ns_igk.ajx.fn.replace_or_append_to_body); return false;";
    $sl->setCallback('AcceptRender', igk_io_get_script(IGK_LIB_DIR . "/Inc/html/globallang_accept_render.pinc"));
    return $dv;
}


///<summary>function igk_html_node_igkglobalthemeselector</summary>
/**
 * function igk_html_node_igkglobalthemeselector
 */
function igk_html_node_igkglobalthemeselector()
{
    $dv = igk_createnode("div");
    $sl = $dv->addSelect("theme")->setClass("-igk-control -clselect");
    $sl->setCallback('AcceptRender', "return igk_init_renderingtheme_callback(\$this); ");
    $uri = igk_ctrl_get_cmd_uri(igk_sys_ctrl(), "changeTheme");
    $sl["onchange"] = "ns_igk.css.changeTheme('{$uri}', this.value); return false;";
    return $dv;
}
///<summary>function igk_html_node_igksitemap</summary>
/**
 * function igk_html_node_igksitemap
 */
function igk_html_node_igksitemap()
{
    $n = igk_createXmlNode("urlset");
    $n["xmlns"] = "http://www.sitemaps.org/schemas/sitemap/0.9";
    $n["xmlns:sitemap"] = "http://www.sitemaps.org/schemas/sitemap/0.9";
    $n->setCallback("lUri", igk_create_func_callback("igk_site_map_add_uri", array($n)));
    return $n;
}
///<summary>function igk_html_node_imagenode</summary>
/**
 * function igk_html_node_imagenode
 */
function igk_html_node_imagenode()
{
    $n = igk_createXmlNode("img");
    return $n;
}
///<summary>function igk_html_node_imglnk</summary>
/**
 * function igk_html_node_imglnk
 */
function igk_html_node_imglnk()
{
    $n = igk_createnode();
    $n->img = $n->addImg();
    $n->setCallback("getAlt", "return \$this->img['alt'];");
    $n->setCallback("setAlt", "\$this->img['alt'] = \$value;");
    return $n;
}
///<summary>function igk_html_node_innerimg</summary>
/**
 * function igk_html_node_innerimg
 */
function igk_html_node_innerimg()
{
    $n = new IGKHtmlItem("igk-img");
    return $n;
}
///<summary></summary>
///<param name="text" default="'Jombotron'"></param>
/**
 * 
 * @param mixed $text the default value is 'Jombotron'
 */
function igk_html_node_jombotron($text = 'Jombotron')
{
    $n = igk_createNode("div");
    $col = $n->addContainer()->addRow()->addCol();
    $dv = $col->addDiv()->setClass("igk-jombotron");
    $dv->addSectionTitle(4)->Content = __("Welcome");
    $dv->addDiv()->Content = $text;
    return $n;
}
///<summary>function igk_html_node_jsaextern</summary>
///<param name="method"></param>
///<param name="args"></param>
/**
 * function igk_html_node_jsaextern
 * @param mixed $method
 * @param mixed $args
 */
function igk_html_node_jsaextern($method, $args = null)
{
    $n = igk_createnode("div");
    $n["class"] = "igk-winuin-jsa-ex dispn";
    if ($args)
        $args = ", args:'" . $args . "'";
    else
        $args = "";
    $n["igk:data"] = "{m:'{$method}' {$args}}";
    return $n;
}
///<summary>function igk_html_node_jsbtn</summary>
///<param name="script"></param>
///<param name="value"></param>
/**
 * function igk_html_node_jsbtn
 * @param mixed $script
 * @param mixed $value
 */
function igk_html_node_jsbtn($script, $value = null)
{
    $n = igk_createnode("input");
    $n["type"] = "button";
    $n["class"] = "igk-btn";
    $n["onclick"] = "javascript: " . $script . " return false";
    $n["value"] = $value;
    return $n;
}
///<summary>function igk_html_node_jsbtnshowdialog</summary>
///<param name="id"></param>
/**
 * function igk_html_node_jsbtnshowdialog
 * @param mixed $id
 */
function igk_html_node_jsbtnshowdialog($id)
{
    $n = igk_createnode();
    $n->setClass("igk-btn igk-js-btn-show-dialog");
    $n->setAttribute("igk:dialog-id", $id);
    return $n;
}
///<summary>function igk_html_node_jsbutton</summary>
///<param name="js"></param>
/**
 * function igk_html_node_jsbutton
 * @param mixed $js
 */
function igk_html_node_jsbutton($js)
{
    $n = igk_createnode("a");
    $n["href"] = "#";
    $n["class"] = "igk-btn igk-js-button";
    $n["igk:js-action"] = $js;
    return $n;
}
///<summary>function igk_html_node_jsclonenode</summary>
///<param name="node"></param>
/**
 * function igk_html_node_jsclonenode
 * @param mixed $node
 */
function igk_html_node_jsclonenode($node)
{
    if (($node == null) || !is_object($node))
        throw new Exception("Not valid");
    if (!is_subclass_of(get_class($node), IGKHtmlItemBase::class)) {
        throw new Exception("not a valid item");
    }
    $n = igk_createnode("igk-js-clone-node");
    $n["igk-js-cn"] = new IGKValueListener($n, 'getTargetId');
    $n->setParam("self::targetnode", $node);
    $n->setCallback("getIsRenderTagName", "return true;");
    $n->setCallback("getTargetId", "return \$this->getParam('self::targetnode'); ");
    return $n;
}
///<summary>function igk_html_node_jsclonetarget</summary>
///<param name="selector"></param>
///<param name="tag"></param>
/**
 * function igk_html_node_jsclonetarget
 * @param mixed $selector
 * @param mixed $tag
 */
function igk_html_node_jsclonetarget($selector, $tag = 'div')
{
    $n = igk_createnode($tag);
    $n["class"] = "igk-winui-clone-target";
    $n["igk-data"] = $selector;
    return $n;
}
///<summary>function igk_html_node_jslogger</summary>
/**
 * function igk_html_node_jslogger
 */
function igk_html_node_jslogger()
{
    $n = igk_createnode("div");
    $n["class"] = "igk-winui-js-logger";
    return $n;
}
///<summary>used to call ready invoke</summary>
/**
 * used to call ready invoke
 */
function igk_html_node_jsreadyscript($script)
{
    $n = igk_createnode("script");
    $n->Content = "if (window.ns_igk)ns_igk.readyinvoke('{$script}');";
    return $n;
}
///<summary>function igk_html_node_jsreplaceuri</summary>
///<param name="uri"></param>
/**
 * function igk_html_node_jsreplaceuri
 * @param mixed $uri
 */
function igk_html_node_jsreplaceuri($uri)
{
    $n = igk_createnode("balafonJS");
    $n->Content = "ns_igk.winui.history.replace('{$uri}', null);";
    return $n;
}
///<summary>used to load manually script tag</summary>
/**
 * used to load manually script tag
 */
function igk_html_node_jsscript($file, $minify = false)
{
    if (file_exists($file)) {
        $d = igk_createnode("script");
        $s = file_get_contents($file);
        // if($minify)
        //     $s=$s;
        $d->Content = $s;
        return $d;
    }
    return null;
}
///<summary>function igk_html_node_label</summary>
///<param name="for"></param>
///<param name="key"></param>
/**
 * function igk_html_node_label
 * @param mixed $for
 * @param mixed $key
 */
function igk_html_node_label($for = null, $key = null)
{
    $n = new IGKHtmlItem("label");
    $n["for"] = $for;
    $n["class"] = "cllabel";
    $n->Content = (($key == null) ? R::ngets("lb." . $for) : R::ngets($key));
    $n->setTempFlag("replaceContentLoading", 1);
    return $n;
}
///<summary>function igk_html_node_labelinput</summary>
///<param name="id"></param>
///<param name="text"></param>
///<param name="type"></param>
///<param name="value"></param>
///<param name="attributes">mixed. array|json_string</param>
///<param name="require"></param>
///<param name="description"></param>
/**
 * function igk_html_node_labelinput
 * @param mixed $id
 * @param mixed $text
 * @param mixed $type
 * @param mixed $value
 * @param mixed $attributes
 * @param mixed $require
 * @param mixed $description
 */
function igk_html_node_labelinput($id, $text, $type = "text", $value = null, $attributes = null, $require = false, $description = null)
{
    $o = igk_createnotagnode(); //igk:label-input");
    $o->setCallback("getIsRenderTagName", "return false;");
    $o->setCallback("getinput", "return \$this->input;");

    $i = $o->add("label");
    $i["for"] = $id;
    $i->Content = $text;
    if ($attributes && is_string($attributes)) {
        $attributes = (array)igk_json_parse($attributes);
    }
    if ($require) {
        $i["class"] = "clrequired";
    }

    $h = $o->addInput($id, $type, $value, $attributes);
    $h["class"] = "+igk-form-control";

    switch ($type) {
        case "checkbox":
        case "radio":
            if ($value) {
                $h["checked"] = "true";
            }
            break;
    }
    $desc = null;
    if ($description) {
        $desc = $o->add("span");
        $desc->Content = $description;
    }
    $o->input = $h;
    return $o;
}
///<summary>function igk_html_node_lborder</summary>
/**
 * function igk_html_node_lborder
 */
function igk_html_node_lborder()
{
    $n = igk_createnode();
    $n->setClass("igk-lborder");
    return $n;
}
///<summary>function igk_html_node_linewaiter</summary>
/**
 * function igk_html_node_linewaiter
 */
function igk_html_node_linewaiter()
{
    $n = igk_createnode();
    $n["class"] = "igk-line-waiter";
    $n["igk:count"] = "3";
    $n->addDiv()->setClass("igk-line-waiter-cur");
    $n->addDiv()->setClass("igk-line-waiter-cur");
    $n->addDiv()->setClass("igk-line-waiter-cur");
    $n->addBalafonJS()->Content = <<<EOF
ns_igk.readyinvoke('igk.winui.lineWaiter.init');
EOF;
    return $n;
}
///<summary>function igk_html_node_linkbtn</summary>
///<param name="uri"></param>
///<param name="img"></param>
///<param name="width"></param>
///<param name="height"></param>
/**
 * function igk_html_node_linkbtn
 * @param mixed $uri
 * @param mixed $img
 * @param mixed $width
 * @param mixed $height
 */
function igk_html_node_linkbtn($uri, $img, $width = 16, $height = 16)
{
    $n = igk_createnode("a");
    $img = $n->add("img");
    $n->setCallback("AcceptRender", "igk_html_callback_alinktn");
    $n->setCallback(
        "setUri",
        <<<EOF
\$this->getParam('data')->src=\$value;
EOF
    );
    $n->setParam("data", (object)array("img" => $img, "w" => $width, "h" => $height, "src" => $uri));
    return $n;
}
///<summary>function igk_html_node_componentnodecallback</summary>
///<param name="listener"></param>
///<param name="name"></param>
///<param name="callback"></param>
/**
 * function igk_html_node_componentnodecallback
 * @param mixed $listener
 * @param mixed $name
 * @param mixed $closurecallback
 */
function igk_html_node_livenodecallback($listener, $name, $callback)
{
    static $livenode = null;
    if ($livenode === null) {
        $livenode = [];
    }
    $f = null; //the settings
    $c = $listener->getParam(IGK_NAMED_NODE_PARAM, array());
    if (isset($c[$name])) {
        $f = $c[$name];
        // return $f;
    }
    if (!is_callable($callback)) {
        if (!is_string($callback) || (strtolower($callback) == "componentnodecallback"))
            igk_die("callback not valid");
        $hc = $callback;
        $callback = function ($listener, $name) use ($hc) {
            $t = igk_createnode($hc, null, array_slice(func_get_args(), 2));
            return $t;
        };
    }
    $args = array_merge(array($listener, $name), array_slice(func_get_args(), 3));
    $h = call_user_func_array($callback, $args);
    if ($h) {
        $c[$name] = []; // $h;
        $h->setParam(IGK_NAMED_ID_PARAM, $name);
        $listener->setParam(IGK_NAMED_NODE_PARAM, $c);
        return $h;
    }
    igk_die("failed to created component");
    return null;
}
///<summary></summary>
///<param name="expression"></param>
///<param name="data" default="null"></param>
/**
 * 
 * @param mixed $expression
 * @param mixed $data the default value is null
 */
function igk_html_node_localizabletext($expression, $data = null)
{
    $c = igk_html_initbindexpression($expression);
    $out = igk_str_format($expression, $data);
    $n = igk_createtextnode($out);
    return $n;
}
///<summary>represent the loremIpSum zone</summary>
///<param name="mode">verbose node</param>
/**
 * represent the loremIpSum zone
 * @param mixed $modeverbose node
 */
function igk_html_node_loremipsum($mode = 1)
{
    $n = igk_createnotagnode();
    switch ($mode) {
        default:
            $n->Content = <<<EOF
Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum
EOF;
            break;
    }
    return $n;
}
///<summary></summary>
///<param name="href"></param>
///<param name="text" default=""></param>
/**
 * 
 * @param mixed $href
 * @param mixed $text the default value is ""
 */
function igk_html_node_mailto($href, $text = "")
{
    $n = igk_createnode("a");
    $n["href"] = "mailto: {$href}";
    $n->Content = empty($text) ? $href : $text;
    return $n;
}
function igk_html_node_menulist($menuTab)
{
    $b = igk_createnotagnode();
    igk_html_build_menu($b, $menuTab);
    return $b;
}

///<summary>function igk_html_node_moreview</summary>
///<param name="hide"></param>
/**
 * function igk_html_node_moreview
 * @param mixed $hide
 */
function igk_html_node_moreview($hide = 1)
{
    $n = igk_createnode("span");
    $n["class"] = "igk-winui-more-view igk-hide";
    $n["igk:hide"] = $hide;
    $n->Content = "...";
    return $n;
}
///<summary>function igk_html_node_msdialog</summary>
///<param name="id"></param>
/**
 * function igk_html_node_msdialog
 * @param mixed $id
 */
function igk_html_node_msdialog($id = null)
{
    $n = igk_createnode();
    $n->setClass("igk-ms-dialog");
    $n->setId($id);
    $n->addA("#")->setClass("igk-btn-close");
    return $n;
}
///<summary>function igk_html_node_mstitle</summary>
///<param name="key"></param>
/**
 * function igk_html_node_mstitle
 * @param mixed $key
 */
function igk_html_node_mstitle($key)
{
    $n = igk_createnode();
    $n->setClass("igk-ms-dialog-title");
    $n->Content = R::ngets($key);
    return $n;
}
///<summary>function igk_html_node_navigationlink</summary>
///<param name="target"></param>
/**
 * function igk_html_node_navigationlink
 * @param mixed $target
 */
function igk_html_node_navigationlink($target)
{
    $n = igk_createXmlNode("a");
    $n->setAttribute("igk-nav-link", $target);
    return $n;
}
///<summary>function igk_html_node_newsletterregistration</summary>
///<param name="uri"></param>
///<param name="type"></param>
///<param name="ajx"></param>
/**
 * function igk_html_node_newsletterregistration
 * @param mixed $uri
 * @param mixed $type
 * @param mixed $ajx
 */
function igk_html_node_newsletterregistration($uri, $type = "email", $ajx = 1)
{
    $n = igk_createnode();
    $frm = $n->addForm();
    $frm["action"] = $uri;
    $frm["igk-ajx-form"] = $ajx;
    $frm->addInput("clEmail", $type)->setClass("igk-form-control")->setAttribute("placeholder", R::ngets("tip.yourmail"));
    $frm->addInput("btn.send", "submit")->setClass("igk-btn igk-btn-default");
    return $n;
}
///<summary>function igk_html_node_notagnode</summary>
/**
 * function igk_html_node_notagnode
 */
function igk_html_node_notagnode()
{
    $n = new IGKHtmlNoTagNodeItem();
    return $n;
}
///<summary>shortcut to create ObData node with noTag to display</summary>
/**
 * shortcut to create ObData node with noTag to display
 */
function igk_html_node_notagobdata($content)
{
    return igk_html_node_ObData($content, IGK_HTML_NOTAG_ELEMENT);
}
///<summary>used to add notification node</summary>
/**
 * used to add notification node
 */
function igk_html_node_notification($nodeType = "div", $notifyName = null)
{
    $n = igk_createnode($nodeType);
    igk_notify_sethost($n, $notifyName);
    return $n;
}
///<summary>used to bind notify global ctrl message</summary>
/**
 * used to bind notify global ctrl message
 */
function igk_html_node_notifyhost($name = "::global", $autohide = 1)
{
    $n = igk_createnode("div");
    $n["class"] = "igk-notify-host";
    if ($autohide) {
        $n["class"] = "+anim-autohide";
    }
    if (igk_environment()->is("DEV")) {
        $n["title"] = $name;
    }
    $c = igk_notifyctrl();
    $g = $c->setNotifyHost($n, $name);
    if ($g) {
        $g->setAutohide($autohide);
    }
    return $n;
}
///<summary>function igk_html_node_notifyhostbind</summary>
///<param name="name"></param>
///<param name="autohide"></param>
/**
 * function igk_html_node_notifyhostbind
 * @param mixed $name
 * @param mixed $autohide
 */
function igk_html_node_notifyhostbind($name = null, $autohide = 1)
{
    $o = igk_createnode('div');
    $o["class"] = "igk-notify-host-bind";
    $o->addOnRenderCallback(igk_create_func_callback("igk_notifyhostbind_callback", array($o, $name, $autohide)));
    return $o;
}
///<summary>function igk_html_node_notifyzone</summary>
///<param name="name"></param>
///<param name="autohide"></param>
///<param name="tag"></param>
/**
 * function igk_html_node_notifyzone
 * @param mixed $name
 * @param mixed $autohide
 * @param mixed $tag
 */
function igk_html_node_notifyzone($name = null, $autohide = 1, $tag = "div")
{
    $n = igk_createnode($tag);
    $n->setClass("igk-notify-z")->addNotifyhost($name, $autohide);
    return $n;
}
///<summary>used to add a node with buffer content</summary>
/**
 * used to add a node with buffer content
 */
function igk_html_node_obdata($data, $nodeType = "div")
{
    if ($nodeType == null)
        $nodeType = IGK_HTML_NOTAG_ELEMENT;
    $n = igk_createnode($nodeType);
    if (is_callable($data)) {
        IGKOb::Start();
        $s = $data($n);
        $g = IGKOb::Content();
        IGKOb::Clear();
        $s = $g;
    } else if (is_object($data) || is_array($data)) {
        if (igk_is_callback_obj($data)) {
            igk_invoke_callback_obj(null, $data);
        } else
            $s = igk_wln_ob_get($data);
    } else
        $s = $data;
    $t = new IGKHtmlSingleNodeViewer(igk_html_node_NoTagNode());
    $t->targetNode->Content = $s;
    $n->add($t);
    return $n;
}
///<summary> create node on callback. create a callback object to send to this </summary>
/**
 *  create node on callback. create a callback object to send to this
 */
function igk_html_node_onrendercallback($callbackObj)
{
    if (!igk_is_callable($callbackObj)) {
        return null;
    }
    $n = igk_createnotagnode();
    $n->__callback = $callbackObj;
    $n->setCallback("AcceptRender", igk_io_get_script(IGK_LIB_DIR . "/Inc/html/onrendercallbak.accept.render.pinc"));
    return $n;
}
///<summary>function igk_html_node_page</summary>
/**
 * function igk_html_node_page
 */
function igk_html_node_page()
{
    return igk_createnode("div")->setAttributes(array("class" => "igk-ui-page page"));
}
///<summary> build pagination settings</summary>
/**
 *  build pagination settings
 */
function igk_html_node_paginationview($baseuri, $total, $perpage, $selected = 1, $ajx = 0, $cookiepath = null, $target = "::")
{
    $e = "";
    if ($ajx)
        $e .= ", ajx:1";
    if ($cookiepath)
        $e .= ", cookie:'{$cookiepath}'";
    if ($selected)
        $e .= ", selected:'{$selected}'";
    $s_o = (object)["total" => 0, "selected" => 0, "maxButton" => 10];
    $settings = is_object($total) ? igk_create_filterObject($total, $s_o) : $s_o;
    $n = igk_createnode("div");
    $n["class"] = "igk-winui-pagination";
    $n["igk:data"] = "{baseuri:'{$baseuri}',min:1,max:10 {$e}, target:'" . $target . "'}";
    $n->addObData(
        function () use ($baseuri, $total, $perpage, $selected) {
            $min = 0;
            $cmax = ceil($total / ($perpage));
            $max = min(10, $cmax);
            if ($selected <= $cmax) {
                $min = max(0, $selected - 5);
                $max = min($cmax, $selected + 5);
                if (($max - $min) < 10) {
                    if ($min == 0) {
                    } else {
                        $max = $cmax;
                        $min = max(0, $cmax - 10);
                    }
                }
            }
            $s = igk_createnode("div");
            if ($min > 0)
                $s->add("span")->setClass("igk-btn")->Content = 1;
            if ($selected > 1)
                $s->add("span")->setAttribute("rol", "prev")->Content = __("Previous Page");
            for ($i = $min; $i < $max; $i++) {
                $p = ($i + 1);
                $st = $s->add("span");
                $st["class"] = "+igk-btn";
                if ($p == $selected) {
                    $st["class"] = "+igk-selected";
                }
                $st->Content = ($i + 1);
            }
            if ($selected != $cmax)
                $s->add("span")->setAttribute("rol", "next")->Content = __("Next Page");
            if ($max < $cmax)
                $s->add("span")->setClass("igk-btn")->Content = $cmax;
            $s->RenderAJX();
        },
        "div",
        array_slice(func_get_args(), 1)
    );
    $n->addBalafonJS()->Content = "igk.winui.paginationview.init()";
    return $n;
}
///<summary>function igk_html_node_panelbox</summary>
/**
 * function igk_html_node_panelbox
 */
function igk_html_node_panelbox()
{
    $n = igk_createnode("div");
    $n["class"] = "igk-panel-box";
    return $n;
}
///<summary>function igk_html_node_paneldialog</summary>
///<param name="title"></param>
///<param name="content"></param>
///<param name="settings"></param>
/**
 * function igk_html_node_paneldialog
 * @param mixed $title
 * @param mixed $content
 * @param mixed $settings
 */
function igk_html_node_paneldialog($title, $content = null, $settings = null)
{
    if (is_string($settings)) {
        $settings = igk_json_parse($settings);
    }


    $n = igk_createnode("div");
    $n["class"] = "igk-winui-panel-dialog";
    $box = $n->addDiv()->setClass("box");
    $tl = $box->addDiv()->setClass("igk-title");
    $tl->add("span")->Content = $title;
    $ctn = $box->addDiv()->setClass("igk-content");
    if ($content) {
        if (is_string($content))
            $ctn->load($content);
        else
            $ctn->add($content);
    }

    if ($settings) {
        if ($svgBtn = igk_getv($settings, "closeBtn")) {
            if (is_numeric($svgBtn)) {
                $svgBtn = "drop";
            }
            $tl->addABtn("#")->setClass("close")->Content = igk_svg_use($svgBtn);
        }

        if ($attribs = igk_getv($settings, "attribs")) {
            if ($cl = igk_getv($attribs, "class")) {
                $n["class"] = $cl;
            }
            unset($attribs["class"]);
            $n->setAttributes($attribs);
        }
    }
    return $n;
}
///<summary> parallax node view</summary>
///<exemple> $t->addParallaxNode(igk_html_resolv_img_uri($this->getDataDir()."/R/parallax/img1.jpg"))->addDiv()->setClass("slide_inside")->Content = "Page 1"; </exemple>
/**
 *  parallax node view
 */
function igk_html_node_parallaxnode($uri = null)
{
    $n = igk_createnode("div");
    $n["class"] = "igk-winui-parallax";
    $n->Attributes->Set("igk:param", "{$uri}");
    return $n;
}
///<summary>function igk_html_node_popupmenu</summary>
/**
 * function igk_html_node_popupmenu
 */
function igk_html_node_popupmenu()
{
    $n = igk_createnode("div");
    $n["class"] = "igk-winui-popup-menu";
    return $n;
}
///<summary>print button</summary>
/**
 * print button
 */
function igk_html_node_printbtn($uri = null)
{
    $s = igk_createnode("div");
    $s["class"] = "igk-btn igk-ptr-btn";
    $s["igk:data"] = $uri;
    return $s;
}
///<summary>function igk_html_node_progressbar</summary>
/**
 * function igk_html_node_progressbar
 */
function igk_html_node_progressbar()
{
    $n = igk_createnode();
    $n["class"] = "igk-progressbar";
    $n["data"] = "0";
    $n->m_cur = $n->addDiv()->setClass("igk-progressbar-cur igk-progress-0");
    return $n;
}

///<summary>function igk_html_node_readonlytextzone</summary>
///<param name="file"></param>
/**
 * function igk_html_node_readonlytextzone
 * @param mixed $file
 */
function igk_html_node_readonlytextzone($file)
{
    $n = igk_createnode();
    $n->setClass("igk-ro-txt-z");
    $c = $n->addTextArea()->setAttribute("readonly", true)->setClass("fitw fith")->setStyle("resize:none;white-space:pre;overflow-x:auto;word-wrap: break-word;")->setAttribute("onfocus", "javascript:event.preventDefault(); event.stopPropagation(); this.blur(); return false;")->Content = igk_create_func_callback("igk_file_content", array($file));
    $n->area = $c;
    return $n;
}
///<summary>function igk_html_node_registermailform</summary>
/**
 * function igk_html_node_registermailform
 */
function igk_html_node_registermailform()
{
    $n = igk_createnode("form");
    $n["action"] = igk_getctrl(IGK_MAIL_CTRL)->getUri("register");
    $n["method"] = "POST";
    igk_notify_sethost($n->addRow()->addCol()->addDiv(), "sys://mailregisterform");
    $n->addRow()->addCol()->addDiv()->addInput("clEmail", "text")->setAttribute("placeholder", R::ngets("lb.yourmail"));
    $n->addRow()->addCol()->addDiv()->addInput("clSubmit", "submit", R::ngets("btn.send"));
    $n->addInput("cref", "hidden", IGKApp::getInstance()->Session->getCRef());
    return $n;
}
///<summary>renderging Expression</summary>
/**
 * renderging Expression
 */
function igk_html_node_renderingexpression($callback)
{
    if (!igk_is_callable($callback))
        return null;
    $n = igk_createnotagnode();
    $n->__callback = $callback;
    $n->setCallback("AcceptRender", "igk_invoke_callback_obj(\$this, \$this->__callback,\$param);  return true;");
    return $n;
}
///<summary>function igk_html_node_repeatcontent</summary>
///<param name="number"></param>
/**
 * function igk_html_node_repeatcontent
 * @param mixed $number
 */
function igk_html_node_repeatcontent($number)
{
    $n = igk_createnode("div");
    $n["class"] = "igk-winui-rc";
    $n["igk-repeat"] = $number;
    return $n;
}
///<summary></summary>
/**
 * 
 */
function igk_html_node_replaceuri($uri = null)
{
    $c = igk_createnotagnode();
    $rp = $uri;
    if ($rp || ($rp = igk_get_env("replace_uri"))) {
        $c->addObData(function () use ($rp) {
            igk_ajx_replace_uri($rp);
        });
    }
    return $c;
}
///<summary>function igk_html_node_responsenode</summary>
/**
 * function igk_html_node_responsenode
 */
function igk_html_node_responsenode()
{
    $n = igk_createnode('div');
    $n["class"] = "igk-response";
    return $n;
}

function igk_html_node_tablehost()
{
    $n = igk_createnode("div");
    $n["class"] = "igk-table-host";
    return $n;
}
///<summary>function igk_html_node_rollin</summary>
/**
 * function igk_html_node_rollin
 */
function igk_html_node_rollin()
{
    $n = igk_createnode();
    $n["class"] = "igk-roll-in";
    return $n;
}
///<summary>function igk_html_node_roundbullet</summary>
/**
 * function igk_html_node_roundbullet
 */
function igk_html_node_roundbullet()
{
    $n = igk_createnode("span");
    $n->setClass("badge igk-rd-bullet");
    return $n;
}
///<summary>function igk_html_node_row</summary>
/**
 * function igk_html_node_row
 */
function igk_html_node_row()
{
    $n = igk_createnode('div');
    $n->setClass("igk-row");
    $n->setCallback(
        "addCell",
        <<<EOF
\$d = \$this->addDiv();
\$d->setClass("igk-row-cell");
return \$d;
EOF
    );
    $n->setCallback(
        "addCol",
        <<<EOF
\$v_n = igk_getv(\$param, 0);
return \$this->add(igk_html_node_RowColumn(\$v_n));
EOF
    );
    return $n;
}
///<summary> add a row column </summary>
///<param name="classLevel" > css classname that init the  column level</param>
/**
 *  add a row column
 * @param mixed $classLevel css classname that init the column level
 */
function igk_html_node_rowcolumn($classLevel = null)
{
    $n = igk_createnode("div");
    $n->setClass("igk-col" . (($classLevel) ? " " . $classLevel : ""));
    return $n;
}
///<summary>function igk_html_node_rowcontainer</summary>
/**
 * function igk_html_node_rowcontainer
 */
function &igk_html_node_rowcontainer()
{
    $n = igk_createnode();
    $n["class"] = "igk-row-container";
    return $n;
}
///<summary>function igk_html_node_scrollimg</summary>
///<param name="src"></param>
/**
 * function igk_html_node_scrollimg
 * @param mixed $src
 */
function igk_html_node_scrollimg($src)
{
    $n = igk_createnode("igk-img-js");
    $n["data"] = igk_create_attr_callback('igk_get_image_uri', array(null, $src));
    return $n;
}
///<summary>used to load scroll Loader Item</summary>
///<remark>if visible will be replaced</remark>
/**
 * used to load scroll Loader Item
 */
function igk_html_node_scrollloader($src)
{
    $n = igk_createnode("igk-scroll-loader");
    $n["data"] = $src;
    return $n;
}
///<summary>function igk_html_node_searchbutton</summary>
///<param name="uri"></param>
/**
 * function igk_html_node_searchbutton
 * @param mixed $uri
 */
function igk_html_node_searchbutton($uri)
{
    $n = igk_createnode("span");
    $n["class"] = "igk-winui-searchbtn";
    $n["igk:target-uri"] = $uri;
    $n->Content = igk_svg_use("search");
    return $n;
}
///<summary>function igk_html_node_sectiontitle</summary>
///<param name="level"></param>
/**
 * function igk_html_node_sectiontitle
 * @param mixed $level
 */
function igk_html_node_sectiontitle($level = null)
{
    $n = igk_createnode();
    $n["class"] = "igk-section-title";
    if ($level)
        $n->setClass("igk-title-" . $level);
    else
        $n->setClass("igk-title");
    return $n;
}
///<summary>function igk_html_node_separator</summary>
///<param name="type"></param>
/**
 * function igk_html_node_separator
 * @param mixed $type
 */
function igk_html_node_separator($type = 'horizontal')
{
    $n = igk_createnode();
    switch ($type) {
        case "horizontal":
            $n["class"] = "igk-horizontal-separator";
            break;
        case "vertical":
            $n["class"] = "igk-vertical-separator";
            break;
    }
    return $n;
}
///<summary></summary>
///<param name="menulist"></param>
/**
 * 
 * @param mixed $menulist
 */
function igk_html_node_sidemenunavigation($menulist)
{
    $ul = igk_createnode("ul")->setClass("side-navigation");
    foreach ($menulist as $c => $g) {
        $li = $ul->add("li")->setContent(__($c));
        if (!empty($g)) {
            $_ul = $li->add("ul");
            foreach ($g as $k => $v) {
                $_li = $_ul->add("li");
                $_a = $_li->addA($v["url"]);
                if (isset($v["target"]))
                    $_a->setAttribute("target", "__blank");
                $_a->Content = __($k);
            }
        }
    }
    return $ul;
}
///<summary>mixed create a shortcut to single node viewer</summary>
///<param name="mixed"> node tag name or html item</param>
/**
 * mixed create a shortcut to single node viewer
 * @param mixed  node tag name or html item
 */
function igk_html_node_singlenodeviewer($node = null)
{
    $s = 0;
    if ($node != null) {
        if (is_string($node)) {
            $s = igk_createnode($node);
        } else if (is_object($node)) {
            $s = $node;
        }
    } else
        $s = igk_html_node_noTagNode();
    return new IGKHtmlSingleNodeViewer($s);
}
///<summary>shortcut to call node->addRow()->addCol()-> and return the column</summary>
/**
 * shortcut to call node->addRow()->addCol()-> and return the column
 */
function igk_html_node_singlerowcol($col = null)
{
    $d = igk_html_parent_node();
    if ($d) {
        $n = $d->addRow()->addCol($col);
        igk_set_env("sys://xml/no_add", 1);
        return $n;
    }
    return null;
}
///<summary>function igk_html_node_slabelcheckbox</summary>
///<param name="id"></param>
///<param name="value"></param>
///<param name="attributes"></param>
///<param name="require"></param>
/**
 * function igk_html_node_slabelcheckbox
 * @param mixed $id
 * @param mixed $value
 * @param mixed $attributes
 * @param mixed $require
 */
function igk_html_node_slabelcheckbox($id, $value = false, $attributes = null, $require = false)
{
    $n = igk_html_node_NoTagNode();
    $tab = $n->addLabelInput($id, R::ngets("lb." . $id), $type = "checkbox", $value, $attributes, $require);
    if ($value) {
        $tab->input["checked"] = true;
    }
    return $n;
}
///<summary>function igk_html_node_slabelinput</summary>
///<param name="id"></param>
///<param name="type"></param>
///<param name="value"></param>
///<param name="attributes"></param>
///<param name="require"></param>
///<param name="description"></param>
/**
 * function igk_html_node_slabelinput
 * @param mixed $id
 * @param mixed $type
 * @param mixed $value
 * @param mixed $attributes
 * @param mixed $require
 * @param mixed $description
 */
function igk_html_node_slabelinput($id, $type = "text", $value = null, $attributes = null, $require = false, $description = null)
{
    return igk_html_node_labelinput($id, R::ngets("lb." . $id), $type, $value, $attributes, $require, $description);
}
///<summary>function igk_html_node_slabelselect</summary>
///<param name="id"></param>
///<param name="values"></param>
///<param name="valuekey"></param>
///<param name="defaultCallback"></param>
///<param name="required"></param>
/**
 * function igk_html_node_slabelselect
 * @param mixed $id
 * @param mixed $values
 * @param mixed $valuekey
 * @param mixed $defaultCallback
 * @param mixed $required
 */
function igk_html_node_slabelselect($id, $values, $valuekey = false, $defaultCallback = null, $required = false)
{
    $p = igk_html_parent_node();
    $i = $p->add("label");
    $i["for"] = $id;
    if ($required) {
        $i["class"] = "clrequired";
    }
    $i->Content = R::ngets("lb." . $id);
    $h = $p->add("select");
    $h->setId($id);
    if (is_array($values)) {
        foreach ($values as $k => $v) {
            $opt = $h->add("option");
            $opt["value"] = IGK_STR_EMPTY . $k;
            $opt->Content = $valuekey ? R::ngets("option." . $v) : $v;
            if (($defaultCallback) && $defaultCallback($k, $v))
                $opt["selected"] = true;
        }
    }
    return (object)array("label" => $i, "input" => $h);
}
///<summary>function igk_html_node_slabeltextarea</summary>
///<param name="id"></param>
///<param name="attributes"></param>
///<param name="require"></param>
///<param name="description"></param>
/**
 * function igk_html_node_slabeltextarea
 * @param mixed $id
 * @param mixed $attributes
 * @param mixed $require
 * @param mixed $description
 */
function igk_html_node_slabeltextarea($id, $attributes = null, $require = false, $description = null)
{
    $i = igk_createnode("label");
    $i["for"] = $id;
    $i->Content = R::ngets("lb." . $id);
    if ($require) {
        $i->setClass("clrequired");
    }
    $h = igk_html_node_TextArea($id);
    $h->AppendAttributes($attributes);
    $desc = null;
    if ($description) {
        $desc = $i->add("span");
        $desc->Content = $description;
    }
    return (object)array("label" => $i, "textarea" => $h, "desc" => $desc);
}
///<summary>function igk_html_node_spangroup</summary>
/**
 * function igk_html_node_spangroup
 */
function igk_html_node_spangroup()
{
    $n = igk_createnode("div");
    $n["class"] = "igk-winui-span-group";
    return $n;
}
///<summary>function igk_html_node_style</summary>
/**
 * function igk_html_node_style
 */
function igk_html_node_style()
{
    $s = new IGKHtmlItem("style");
    return $s;
}
///<summary>function igk_html_node_submitbtn</summary>
///<param name="name"></param>
///<param name="key"></param>
/**
 * function igk_html_node_submitbtn
 * @param mixed $name
 * @param mixed $key
 */
function igk_html_node_submitbtn($name = "btn_", $key = "btn.add")
{
    $n = igk_createnode("input");
    $n->setId($name);
    $n["value"] = R::ngets($key);
    $n["type"] = "submit";
    $n->setClass("igk-btn");
    return $n;
}
///<summary>function igk_html_node_svga</summary>
///<param name="uri"></param>
///<param name="svgname"></param>
/**
 * function igk_html_node_svga
 * @param mixed $uri
 * @param mixed $svgname
 */
function igk_html_node_svga($uri, $svgname)
{
    $n = igk_html_node_a($uri);
    $n->setClass("svg-a");
    $n->addSvgSymbol($svgname);
    return $n;
}
///<summary>function igk_html_node_svgajxformbtn</summary>
///<param name="uri"></param>
///<param name="svgname"></param>
/**
 * function igk_html_node_svgajxformbtn
 * @param mixed $uri
 * @param mixed $svgname
 */
function igk_html_node_svgajxformbtn($uri, $svgname)
{
    $n = igk_html_node_a($uri);
    $n->setClass("svg-a igk-from-sbtn-ajx");
    $n->addSvgSymbol($svgname);
    return $n;
}
///<summary>function igk_html_node_svglnkbtn</summary>
///<param name="uri"></param>
///<param name="svgname"></param>
/**
 * function igk_html_node_svglnkbtn
 * @param mixed $uri
 * @param mixed $svgname
 */
function igk_html_node_svglnkbtn($uri, $svgname)
{
    $n = igk_html_node_a($uri);
    $n->setClass("svg-a igk-from-sbtn");
    $n->addSvgSymbol($svgname);
    return $n;
}
///<summary>function igk_html_node_svgsymbol</summary>
///<param name="name"></param>
/**
 * function igk_html_node_svgsymbol
 * @param mixed $name
 */
function igk_html_node_svgsymbol($name = null)
{
    $n = igk_createnode("div");
    $n["class"] = "igk-svg-symbol";
    $n["igk:svg-name"] = $name;
    return $n;
}
///<summary>function igk_html_node_svguse</summary>
///<param name="name"></param>
/**
 * function igk_html_node_svguse
 * @param mixed $name
 */
function igk_html_node_svguse($name)
{
    $n = igk_html_node_NoTagNode();
    $n->Content = igk_svg_use($name);
    return $n;
}
///<summary>function igk_html_node_symbol</summary>
///<param name="code"></param>
///<param name="w"></param>
///<param name="h"></param>
///<param name="name"></param>
/**
 * function igk_html_node_symbol
 * @param mixed $code
 * @param mixed $w
 * @param mixed $h
 * @param mixed $name
 */
function igk_html_node_symbol($code, $w = 16, $h = 16, $name = 'default')
{
    $n = igk_createnode();
    $n["class"] = "igk-symbol";
    $n->Content = is_integer($code) ? "&#" . $code . ";" : $code;
    $g = $name == 'default' || ($name == null) ? '' : ", name:'$name'";
    $n["igk-symbol-data"] = "{w:'$w', h:'$h' $g}";
    return $n;
}
///<summary>used to add system article</summary>
/**
 * used to add system article
 */
function igk_html_node_sysarticle($name)
{
    $f = igk_io_get_article($name);
    $n = igk_createnode();
    igk_html_article(igk_sys_ctrl(), $f, $n);
    return $n;
}
///<summary>function igk_html_node_tabbutton</summary>
/**
 * function igk_html_node_tabbutton
 */
function igk_html_node_tabbutton()
{
    $n = igk_createnode("div");
    $n["class"] = "igk-tab-button";
    $n->setCallback('add', "igk_html__tabbutton_add");
    return $n;
}
///<summary>function igk_html_node_td</summary>
///<param name="for"></param>
///<param name="key"></param>
/**
 * function igk_html_node_td
 * @param mixed $for
 * @param mixed $key
 */
function igk_html_node_td($for = null, $key = null)
{
    $n = new IGKHtmlItem("td");
    return $n;
}
///<summary>use to add a template node</summary>
/**
 * use to add a template node
 */
function igk_html_node_template($ctrl, $name, $row = null)
{
    $d = igk_createnode();
    igk_html_binddata($ctrl, $d, $name, $row, false, true);
    return $d;
}
///<summary>function igk_html_node_textarea</summary>
///<param name="name"></param>
///<param name="content"></param>
///<param name="attributes"></param>
/**
 * function igk_html_node_textarea
 * @param mixed $name
 * @param mixed $content
 * @param mixed $attributes
 */
function igk_html_node_textarea($name = null, $content = null, $attributes = null)
{
    $tx = new IGKHtmlItem("textarea");
    if ($name)
        $tx->setId($name);
    $tx["class"] = "+cltextarea";
    $tx->setAttributes($attributes);
    $tx->setParam("p:_useWTiny", true);
    $tx->setParam("p:_escapeChar", false);
    $tx->setCallback("setContent", "igk_html_TextAreaV_Callback");
    if ($content == null) {
        $tx->Content = igk_getr($name);
    } else
        $tx->Content = $content;
    return $tx;
}
///<summary>represent a zone node for text edition</summary>
/**
 * represent a zone node for text edition
 */
function igk_html_node_textedit($id, $uri, $c = null)
{
    $n = igk_createnode("span");
    $n["class"] = "igk-textedit";
    $n["igk:data"] = "{uri:'{$uri}', id:'{$id}'}";
    $n->Content = $c;
    return $n;
}
///<summary>create a thumbnail document</summary>
/**
 * create a thumbnail document
 */
function igk_html_node_thumbnaildocument($id)
{
    $d = igk_get_document($id, 1);
    $d->body->setClass("+thumbnail-doc +thumbnail");
    return $d;
}
///<summary> represent a tip panel </summary>
/**
 *  represent a tip panel
 */
function igk_html_node_tip()
{
    $n = igk_createnode("p");
    $n["class"] = "igk-tip";
    return $n;
}
///<summary>function igk_html_node_titlelevel</summary>
///<param name="level"></param>
/**
 * function igk_html_node_titlelevel
 * @param mixed $level
 */
function igk_html_node_titlelevel($level = 1)
{
    $n = igk_createnode();
    $n["class"] = "igk-title-" . $level;
    return $n;
}
///<summary>function igk_html_node_titlenode</summary>
///<param name="class"></param>
///<param name="text"></param>
/**
 * function igk_html_node_titlenode
 * @param mixed $class
 * @param mixed $text
 */
function igk_html_node_titlenode($class, $text)
{
    if (!$class)
        $class = "igk-title";
    $n = igk_createnode("div");
    $n->setClass($class)->setContent($text);
    return $n;
}
///<summary>for toast message</summary>
/**
 * for toast message
 */
function igk_html_node_toast()
{
    $n = igk_createnode("div");
    $n["class"] = "igk-winui-toast";
    return $n;
}
///<summary>function igk_html_node_tooltip</summary>
/**
 * function igk_html_node_tooltip
 */
function igk_html_node_tooltip()
{
    $n = igk_createXmlNode("igk:tooltip")->setAttribute("style", "display:none;");
    return $n;
}
///<summary>function igk_html_node_topnavbar</summary>
/**
 * function igk_html_node_topnavbar
 */
function igk_html_node_topnavbar()
{
    $n = igk_createnode("div");
    $n["igk-top-nav-bar"] = "1";
    $n["class"] = "igk-navbar igk-top-nav-bar";
    return $n;
}
///<summary>function igk_html_node_trackbarnode</summary>
///<param name="id"></param>
///<param name="value"></param>
///<param name="min"></param>
///<param name="max"></param>
/**
 * function igk_html_node_trackbarnode
 * @param mixed $id
 * @param mixed $value
 * @param mixed $min
 * @param mixed $max
 */
function igk_html_node_trackbarnode($id, $value, $min = 0, $max = 100)
{
    $n = igk_createnode("input");
    $n->setId($id);
    $n["type"] = "range";
    $n["class"] = "igk-winui-trackbar";
    $n["min"] = $min;
    $n["max"] = $max;
    $n["value"] = $value;
    return $n;
}
///<summary> create a transition block node</summary>
/**
 *  create a transition block node
 */
function igk_html_node_transitionblock()
{
    $n = igk_createnode("div");
    $n["class"] = "igk-transition-block";
    return $n;
}
///<summary>function igk_html_node_underconstructionpage</summary>
/**
 * function igk_html_node_underconstructionpage
 */
function igk_html_node_underconstructionpage()
{
    $n = igk_createnode();
    $n->setCallback("getCommunityNode", "return null;");
    $n->setClass("fitw fith igk-under-construction");
    $src = igk_get_env("sys://underconstruction.bg");
    if (!$src)
        $src = igk_io_baseDir(igk_db_get_config("sys://underconstruct.bg", ""));
    if ($src) {
        $n->addBackgroundLayer()->addImg()->setSrc($src);
    }
    $c = $n->addContainer();
    $c->setStyle("padding-top: 64px; padding-bottom; ;");
    $r = $c->addRow();
    $r->addCol()->addDiv()->setStyle("font-size:3em;color:#eee")->Content = R::ngets("title.pageUnderConstruction");
    $c = $r->addCol()->addDiv();
    $c->Content = "Vous souhaitez être informer lors de l'ouverture du site";
    $r->addCol()->addDiv()->addRegisterMailForm();
    return $n;
}
///<summary>function igk_html_node_videofilestream</summary>
///<param name="location"></param>
///<param name="auth"></param>
/**
 * function igk_html_node_videofilestream
 * @param mixed $location
 * @param mixed $auth
 */
function igk_html_node_videofilestream($location, $auth = false)
{
    $n = igk_createnode("video");
    $n["controls"] = 1;
    $n["preload"] = "auto";
    $n["src"] = $location;
    return $n;
}
///<summary>used to evaluate the content. in xpthml file the content will be evaluated</summary>
/**
 * used to evaluate the content. in xpthml file the content will be evaluated
 */
function igk_html_node_viewcontent($listener, $data = null)
{
    $d = igk_html_node_noTagNode();
    $d->listener = $listener;
    $d->setCallback("AcceptRender", "igk_html_viewContentAcceptRender");
    return $d;
}
///<summary> add a visibility server node</summary>
///<param name="cond" type="mixed" >mixed callback or evaluable condition expression</param>
/**
 *  add a visibility server node
 * @param mixed cond mixed callback or evaluable condition expression
 */
function igk_html_node_visible($cond)
{
    $n = igk_html_node_noTagNode();
    if (igk_is_callable($cond))
        $f = $cond;
    else
        $f = "return {$cond}";
    $n->setCallback("getIsVisible", $f);
    return $n;
}
///<summary>function igk_html_node_vscrollbar</summary>
///<param name="cibling"></param>
///<param name="initTarget"></param>
/**
 * function igk_html_node_vscrollbar
 * @param mixed $cibling
 * @param mixed $initTarget
 */
function igk_html_node_vscrollbar($cibling = null, $initTarget = null)
{
    $n = igk_createnode();
    $n["class"] = "igk-vscroll";
    $n["igk:cibling"] = $cibling;
    $n["igk:target"] = $initTarget;
    return $n;
}
///<summary>function igk_html_node_vsep</summary>
/**
 * function igk_html_node_vsep
 */
function igk_html_node_vsep()
{
    return igk_html_node_Separator("vertical");
}
///<summary>function igk_html_node_webglgamesurface</summary>
///<param name="listener"></param>
/**
 * function igk_html_node_webglgamesurface
 * @param mixed $listener
 */
function igk_html_node_webglgamesurface($listener = null)
{
    $n = igk_createnode("div");
    $n["class"] = "igk-webgl-game-surface";
    if ($listener)
        $n["igk-webgl-game-attr-listener"] = $listener;
    return $n;
}
///<summary>function igk_html_node_webglscriptsurface</summary>
///<param name="scriptFile"></param>
///<param name="shaderFolder"></param>
/**
 * function igk_html_node_webglscriptsurface
 * @param mixed $scriptFile
 * @param mixed $shaderFolder
 */
function igk_html_node_webglscriptsurface($scriptFile, $shaderFolder = null)
{
    if (!igk_is_module_present("bge")) {
        igk_die("/!\\ module :  bge is required ");
    }
    $c = igk_createnode("script");
    $c["type"] = "balafon/bge-script";
    $c["language"] = "";
    $c["class"] = "igk-winui-bge-script";
    $c->Content = igk_bge_get_shaders($shaderFolder) . " " . (file_exists($scriptFile) ? file_get_contents($scriptFile) : null);
    return $c;
}
///<summary>create a node that will only be visible on webmaster mode context</summary>
/**
 * create a node that will only be visible on webmaster mode context
 */
function igk_html_node_webmasternode()
{
    $n = igk_createnode("webmaster-node");
    $n->setCallback("getIsRenderTagName", "return false;");
    $n->setCallback("getIsVisible", "igk_html_callback_is_webmaster");
    return $n;
}
///<summary>function igk_html_node_word</summary>
///<param name="v"></param>
///<param name="cl"></param>
/**
 * function igk_html_node_word
 * @param mixed $v
 * @param mixed $cl
 */
function igk_html_node_word($v, $cl)
{
    $n = igk_createnode("span");
    $n->Content = $v;
    $n["class"] = "wd w-" . $cl;
    return $n;
}
///<summary>function igk_html_node_wordcasesplitter</summary>
///<param name="v"></param>
///<param name="split"></param>
/**
 * function igk_html_node_wordcasesplitter
 * @param mixed $v
 * @param mixed $split
 */
function igk_html_node_wordcasesplitter($v, $split = 5)
{
    $n = igk_createnode();
    $n->setClass("igk-wc-splitter");
    if (is_string($v)) {
        $o = igk_str_explode_upperCase($v);
        $w = 1;
        foreach ($o as  $sv) {
            if (empty($sv))
                continue;
            $n->add("span")->setClass("w_" . $w)->setContent($sv);
            $w = (++$w % $split);
        }
    }
    return $n;
}
///<summary>function igk_html_node_wordsplitview</summary>
/**
 * function igk_html_node_wordsplitview
 */
function igk_html_node_wordsplitview()
{
    $n = igk_createnode("div");
    $n["class"] = "igk-ui-wplitview";
    return $n;
}
///<summary>function igk_html_node_xslt</summary>
///<param name="xml"></param>
///<param name="xslt"></param>
///<param name="global"></param>
///<param name="options"></param>
/**
 * function igk_html_node_xslt
 * @param mixed $xml
 * @param mixed $xslt
 * @param mixed $global
 * @param mixed $options
 */
function igk_html_node_xslt($xml, $xslt, $global = 0, $options = null)
{
    $o = $global ? $xslt : <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
xmlns="http://www.w3.org/1999/xhtml"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform" >
<xsl:template match="/">{$xslt}</xsl:template>
</xsl:stylesheet>
EOF;
    $n = igk_createnotagnode();
    $n->addObData("<!--" . $xml . "-->", 'div')->setClass("xml")->setStyle("display:none");
    $n->addObData("<!--" . $o . "-->", 'div')->setClass("xslt")->setAttribute("xslt:data", $options)->setStyle("display:none");
    $n->addBalafonJS()->Content = "igk.dom.xslt.initTransform();";
    return $n;
}
///<summary>function igk_html_node_xsltranform</summary>
///<param name="xmluri"></param>
///<param name="xsluri"></param>
///<param name="target"></param>
/**
 * function igk_html_node_xsltranform
 * @param mixed $xmluri
 * @param mixed $xsluri
 * @param mixed $target
 */
function igk_html_node_xsltranform($xmluri, $xsluri, $target = null)
{
    if (!isset($xmluri) || empty($xmluri))
        throw new Exception("xmluri not specified");
    if (!isset($xmluri) || empty($xmluri))
        throw new Exception("xsluri not specified");
    $n = igk_createnode('div');
    $n->setClass("igk-xsl-node");
    if ($target)
        $target = ", target:'$target'";
    $n->Attributes->Set("igk:xslt-data", "{xml:'$xmluri', xsl:'$xsluri'{$target} }");
    return $n;
}
///<summary>set placehost for input</summary>
/**
 * set placehost for input
 */
function igk_html_set_tooltip($n, $m)
{
    $n->input->setAttribute("placeholder", $m);
    return $n;
}
///<summary>function igk_html_textareav_callback</summary>
/**
 * function igk_html_textareav_callback
 */
function igk_html_textareav_callback()
{
    igk_die("value not implement");
    return;
}
///<summary>function igk_html_viewcontentacceptrender</summary>
///<param name="a"></param>
///<param name="b"></param>
/**
 * function igk_html_viewcontentacceptrender
 * @param mixed $a
 * @param mixed $b
 */
function igk_html_viewcontentacceptrender($a, $b)
{
    $a->clearChilds();
    if ($a->listener) {
        $a->add($a->listener);
        return 1;
    }
    return 0;
}
///<summary>function igk_html_visibleconditionalnode</summary>
/**
 * function igk_html_visibleconditionalnode
 */
function igk_html_visibleconditionalnode()
{
    return igk_is_conf_connected();
}
///<summary>function igk_init_renderinglang</summary>
///<param name="sl"></param>
/**
 * function igk_init_renderinglang
 * @param mixed $sl
 */
function igk_init_renderinglang($sl)
{
    $sl->clearChilds();
    $gt = R::GetCurrentLang();
    $v_csvc = igk_getctrl(IGK_CSVLANGUAGE_CTRL);
    $tab = array_filter(explode("|", $v_csvc->getLanguages()));
    if ($tab) {
        $tab = array_merge($tab);
        foreach ($tab as  $v) {
            $op = $sl->add("option");
            $op["value"] = $v;
            $op->Content = igk_lang_display($v);
            if ($v == $gt) {
                $op["selected"] = "true";
            }
        }
    }
}
///<summary>function igk_init_renderingtheme_callback</summary>
///<param name="n"></param>
/**
 * function igk_init_renderingtheme_callback
 * @param mixed $n
 */
function igk_init_renderingtheme_callback($n)
{
    $n->clearChilds();
    if (!$n->IsVisible) {
        return 0;
    }
    $gt = igk_web_get_config("globaltheme", 'default');
    foreach (igk_io_getfiles(igk_io_baseDir() . "/R/Themes/", "/\.theme$/i") as  $v) {
        $op = $n->add("option");
        $r = igk_io_basenamewithoutext($v);
        $op->Content = $r;
        if ($r == $gt) {
            $op["selected"] = "true";
        }
    }
    return 1;
}
///<summary>get language display utility</summary>
/**
 * get language display utility
 */
function igk_lang_display($v)
{
    static $display = null;
    if ($display == null) {
        $display = [];
        $display["fr"] = "Français";
        $display["en"] = "English";
        $display["nl"] = "Neederlands";
    }
    return igk_getv($display, $v, $v);
}
///<summary>function igk_min_script</summary>
///<param name="s"></param>
/**
 * function igk_min_script
 * @param mixed $s
 */
function igk_min_script($s)
{
    $s = preg_replace("/(\n|\t|\r)/i", "", $s);
    return $s;
}
///<summary>function igk_notifyhostbind_callback</summary>
///<param name="host"></param>
///<param name="name"></param>
///<param name="autohide"></param>
///<param name="options"></param>
///<param name="bind"></param>
/**
 * function igk_notifyhostbind_callback
 * @param mixed $host
 * @param mixed $name
 * @param mixed $autohide
 * @param mixed $options
 * @param mixed $bind
 */
function igk_notifyhostbind_callback($host, $name, $autohide, $options = null, $bind = null)
{
    // $bind=isset($this) ? $this: $bind;
    $c = igk_notifyctrl();
    $n = $c->getNotification($name) ?? $c->TargetNode;
    if ($n && $bind) {
        $bind->addObData(function () use ($n, $autohide) {
            $n->setAutohide($autohide);
            $n->renderAJX();
            $n->setAutohide(1);
        });
        return 1;
    }
    return 0;
}
///<summary>function igk_pic_zone</summary>
///<param name="n"></param>
///<param name="r"></param>
///<param name="c"></param>
///<param name="base"></param>
///<param name="tab"></param>
///<param name="offset"></param>
/**
 * function igk_pic_zone
 * @param mixed $n
 * @param mixed $r
 * @param mixed $c
 * @param mixed $base
 * @param mixed $tab
 * @param mixed $offset
 */
function igk_pic_zone($n, $r, $c, $base = 4, $tab = null, $offset = 0)
{
    $tr = $r;
    $ct = 0;
    while ($r > 0) {
        $r--;
        $t = $n->addRow();
        $j = $c;
        while ($j > 0) {
            $j--;
            $cl = $t->addCol();
            $cl->setClass("igk-col-{$base}-1");
            $cl->addDiv()->setClass("pic")->Content = igk_getv($tab, $ct, IGK_HTML_SPACE);
            $ct++;
        }
    }
}
///<summary>function igk_site_map_add_uri</summary>
///<param name="n"></param>
///<param name="uri"></param>
/**
 * function igk_site_map_add_uri
 * @param mixed $n
 * @param mixed $uri
 */
function igk_site_map_add_uri($n, $uri = null)
{
    $c = $n->addNode("url");
    $c->addNode("loc")->Content = igk_getv($uri, 0);
    $c->addNode("priority")->Content = 1;
}


function igk_html_node_formcref()
{
    $n = igk_createnotagnode();
    $n->addNoTagObData(function () {
        igk_html_form_init();
    });
    return $n;
}
function igk_html_node_select_options($optionsList, $options = null)
{
    ($p = igk_html_parent_node()) || igk_die("required a parent list");
    if (is_string($options)) {
        $options = igk_json_parse($options);
    }
    $options =  igk_createobj_filter(
        $options ? $options : [],
        [
            "isEmpty" => 1,
            "display" => "text",
            "selected" => 0,
            "value" => "value",
            "allowEmpty" => 0,
            "emptyValue" => 0
        ]
    );
    $s = $options->selected;
    if ($options->allowEmpty) {
        $o = $p->add("option");
        $v =  $options->emptyValue;
        $o["value"] = $v;
        $o->setContent("");
        if ($s == $v) {
            $o["class"] = "igk-active";
            $o->activate("selected");
        }
    }
    foreach ($optionsList as $m) {
        $o = $p->add("option");

        $t = igk_getv($m, $options->display);
        $v = igk_getv($m, $options->value);

        $o["value"] = $v;
        $o->setContent($t);
        if ($s == $v) {
            $o["class"] = "igk-active";
            $o->activate("selected");
        }
    }
    return null;
}

function igk_html_node_jsview()
{
    $n = igk_createnode("script");
    $n["type"] = "balafon/js-view";
    $n["class"] = "igk-balafon-js-view";
    return $n;
}

//engine use
function igk_html_node_attr_expression()
{
    // igk_die("create an expression node");
    if (!($c = igk_html_engine_parent_node()))
        igk_die("must be create on template loading context");
    // igk_wln_e("tag ", $c->tagName);
    $n = new IGKHtmlAttribExpressionNode($c);
    return $n;
}

function igk_html_node_fields()
{
    $o = igk_html_parent_node();
    if ((($c = func_num_args()) >= 1) && is_array($a = func_get_arg(0))) {
        $engine = $c > 1 ? func_get_arg(1) : null;
        $tag = $c > 2 ? func_get_arg(2) : null;
        $o->addObData(function () use ($a, $engine, $tag) {      
            igk_html_form_fields($a, 1, $engine, $tag);
        }, IGK_HTML_NOTAG_ELEMENT);
    }
    return $o;
}

function igk_html_node_tableheader($headers, $filter = null)
{
    $tr = igk_createnode("tr");
    foreach ($headers as $k) {
        $th = $tr->add("th");
        if (!$filter) {
            if (empty($k))
                $k = "&nbsp;";

            $th->Content = $k;
        } else
            $filter($k, $th);
    }
    return $tr;
}

function igk_html_node_dataschema()
{
    $n = new IGKXmlNode("dataschemas");
    return $n;
}

function igk_html_node_containerRowCol($style = "")
{
    $p = igk_html_parent_node();
    $n = $p->addContainer()->addRow()->addCol($style);
    return ["node" => $n];
}

///<summary></summary>
///<param name="raw"></param>
///<param name="ctrl" default="null"></param>
/**
 * 
 * @param mixed $raw
 * @param mixed $ctrl the default value is null
 */
function igk_html_node_expression_node($raw, $ctrl = null)
{
    if ($ctrl === null) {
        $g = igk_get_env("sys:://expression_context");
        $ctrl = $g->ctrl;
        // igk_wln_e(__FILE__.":".__LINE__, "loading context ", $g);
    }
    $n = new IGKHtmlExpressionNodeItem($raw, $ctrl);
    return $n;
}
function igk_html_node_nbsp()
{
    $c = igk_createtextnode("&nbsp;");
    if ($f = igk_html_parent_node()) {
        $f->add($c);
        return $f;
    }
    return $c;
}
function igk_html_node_actiongroup()
{
    $c = igk_createnode("div");
    $c["class"] = "igk-action-group";
    return $c;
}
//---------------------------------------------------------------------------------
// form tag extension
//

Factory::form("cref", function () {
    if ($f = igk_html_parent_node()) {
        $f->addObData("igk_html_form_cref", null);
    }
    return $f;
});



///<summary></summary>
///<param name="app"></param>
///<param name="baduri" default="null"></param>
///<param name="goodUri" default="null"></param>
/**
 * 
 * @param mixed $app
 * @param mixed $baduri the default value is null
 * @param mixed $goodUri the default value is null
 */
function igk_html_node_apploginform($app, $baduri = null, $goodUri = null)
{
    $n = igk_createNode("div");
    igk_app_login_form($app, $n, $baduri, $goodUri);
    return $n;
}
/**
 * host callable to 
 * @param callable $callback 
 * @return mixed 
 * @throws Exception 
 * @throws IGKException 
 */
function igk_html_node_host(callable $callback)
{
    if (!($p = igk_html_parent_node()))
        throw new IGKException("Parent Node not found");
    $callback($p);
    return $p;
}
///<summary>bind view callback to parent as text view node</summary>
/**
 * 
 * @param mixed $callback callback to call 
 * @return mixed 
 * @throws Exception 
 */
function igk_html_node_ViewCallback(callable $callback)
{
    $n = igk_html_parent_node();
    ob_start();
    $callback($n);
    $c = ob_get_contents();
    ob_end_clean();
    if (!empty($c)) {
        $n->addText($c);
    }
    return $n;
}

///<summary> center page document</summary>
function igk_html_node_pageCenterBox(callable $host = null)
{
    $box = null;
    $_o = null;
    if ($f = igk_html_parent_node()) {
        $dv = $f->div();
        $_o = $f;
    } else {
        $dv = igk_createnode("div");
        $_o = $dv;
    }
    $box = $dv->container()->row()->col("no-margin fitw")->div()->setClass("igk-page-center fitvh")->addCenterBox()->getBox();
    if ($host != null) {
        $host($box);
    }

    return $_o;
}


///<summary>pre tag with content</summary>
function igk_html_node_pre($c = null)
{
    $p = new IGKHtmlItem("pre");
    if ($c !== null) {
        if (is_callable($c)) {
            $p->Content = igk_ob_get_func($c);
        } else {
            ob_start();
            print_r($c);
            $p->Content = ob_get_clean();
        }
    }
    return $p;
}

function igk_html_node_hiddenFields(array $fields)
{
    if ($f = igk_html_parent_node()) {
        foreach ($fields as $k => $v) {
            $f->addInput($k, "hidden", $v);
        }
    }
    return $f;
}

/**
 * create a grid node
 */
function igk_html_node_grid()
{
    $n = igk_createnode("div");
    $n["class"] = "+igk-grid";
    return $n;
}

/**
 * add tab component
 */
function igk_html_node_ajxtabcomponent($host, $name)
{
    $n = igk_createnode(IGKHtmlComponents::Component, null, [
        $host, IGKHtmlComponents::AJXTabControl, $name
    ]);
    return $n;
}


///<summary>include local file as javascript</summary>
/**
 * include local file as javascript
 */
function igk_html_node_include_js($file)
{
    if ($f = igk_html_parent_node()) {
        $d = igk_createxmlnode("script");
        $d["type"] = "balafon/js-include";
        $d["class"] = "igk-winui-balafon-js-inc";

        $d->Content = implode("", [
            "//<![CDATA[", implode("", explode(
                "\n",
                file_get_contents($file)
            )), "]]>"
        ]);
        $f->add($d);
    }
    return $f;
}
/**
 * create a cdata node
 */
function igk_htlm_node_cdata($value=null){
    $v=(new IGKXmlNode("!CDATA"));
    $v->setContent($value);
    return $v;
}

///<summary>select tag node</summary>
/**
 * create a select tag node JS requirement
 * @param mixed $id 
 * @param mixed|null $data 
 * @param mixed|null $option array of settings show_tag|debug|click|click|require
 * @return object 
 * @throws ReflectionException 
 * @throws IGKException 
 */
function igk_html_node_selecttag($id, $data=null, $options=null){
    $n = igk_createnode("div");
    $n["class"] = "igk-winui-selecttag";
    $n["igk:id"] = $id;
    $n["igk:data"] = is_array($data)? htmlentities(json_encode($data)) : $data;
    $n["igk:options"] = is_array($options)? htmlentities(json_encode($options)) : $options;
    return $n;
}

//----------------------------------------------------------------
// + | REGISTER HTML FACTORY function
//----------------------------------------------------------------

Factory::form("initfield", function () {
    if ($f = igk_html_parent_node()) {
        igk_html_form_initfield($f);
    }
    return $f;
});
Factory::form("ajx", function ($target = null) {

    if ($f = igk_html_parent_node()) {
        $f["igk-ajx-form"] = 1;
        $f["igk-ajx-form-target"] = $target;
    }
    return $f;
});

Factory::form("multipart", function () {
    if ($f = igk_html_parent_node()) {
        $f["enctype"] = "multipart/form-data";
    }
    return $f;
});

Factory::form("hiddenFields", function (array $fields) {
    if ($f = igk_html_parent_node()) {

        $f->loop($fields)->host(function ($f, $v) {
            $f->addInput($v, "hidden", $v[1]);
        });
    }
    return $f;
});

Factory::table("header", function (...$header) {
    if ($f = igk_html_parent_node()) {
        $f->tr()->loop($header)->host(function ($n, $v) {
            if (empty($v)) {
                $n->th()->nbsp();
            } else {

                if (is_array($v)) {
                    $text = igk_getv($v, "text", igk_getv($v, 0));
                    $attribs = null;
                    foreach (["attribs", "attributes"] as $s) {
                        if (key_exists($s, $v)) {
                            $attribs = igk_getv($v, $s);
                            break;
                        }
                    }
                    $th = $n->th();
                    if ($attribs)
                        $th->setAttributes($attribs);
                    $th->Content = $text;
                    return;
                }
                $n->th()->Content = $v;
            }
        });
    }
    return $f;
});


function igk_html_node_definition($title, $def){
    $d = igk_createnode("dl");
    $d->dt()->Content = $title;
    $d->dd()->Content = $def;
    return $d;
}

function igk_html_node_definitions($args){
    if ($q = igk_html_parent_node()){

        foreach($args as $defs){            
            if (($t = igk_getv_fallback($defs, "t|title")) && 
               ($def = igk_getv_fallback($defs, "d|def"))){

                $q->definition($t, $def); 
            }
        }
    }
    return $q;
}