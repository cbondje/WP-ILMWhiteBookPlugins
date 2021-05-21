<?php

namespace IGK\System\Http;

class ResponseHtmlRenderer{
    private $m_node;
    private $m_obj;
    private $m_refs;
    public function __construct($node, $object){
        $this->m_node = $node;
        $this->m_obj = $object;
        
    }
    public function render(){
        $this->m_refs = [];
        $this->m_node->clearChilds();
        foreach($this->m_obj as $k=>$m){
            $this->m_node->addDt()->Content = "$k";
            $c = $this->m_node->addDD();
            $this->visitDd($c, $m);

        }
        // $this->m_node->addObData($this->m_obj);
        return $this->m_node->render();
    }
    public function visitDd($c, $m){
        $cp = [[$c, $m]];
        while($q = array_pop($cp)){
            
            $c = $q[0];
            $m = $q[1];
        if (is_object($m)){
            $id = spl_object_id($m);
            if ($id && isset($this->m_refs[$id])){
                $c->add("ul")->add("li")->Content = "ref:".$id;
                continue;
            }else {
                if ($id){
                    $this->m_refs[$id] = $m;
                }
            }

            $u = $c->add("ul");
            foreach($m as $k=>$v){
                $li = $u->add("li");
                $li->addLabel()->Content = $k;
                $li->addText(" ");
                if (is_object($v) || is_array($v)){
                    array_push($cp, [$li->add("dd"), $v]);
                }else {
                    $li->addSpan()->Content = $v;
                }
            }
        }  
        else {
            $c->Content = $m;
        }
    }
    }
}