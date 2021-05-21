<?php
namespace IGK\System\IO\File;

use IGKHtmlItem;
use IGKIO;
use IGKObject;
use ReflectionClass;
use ReflectionMethod;

// author: C.A.D. BONDJE DOUE
// licence: IGKDEV - Balafon @ 2019
// desc: wsdl utility class

///<summary>used to generate file</summary>
/**
* used to generate file
*/
class WsdlFile extends IGKObject {
    private $m_attributes;
    private $m_binding;
    private $m_cservice;
    private $m_def;
    private $m_message;
    private $m_porttype;
    private $m_service;
    private $m_srv;
    private $m_uri;
    private $uri;
    ///<summary></summary>
    ///<param name="name"></param>
    ///<param name="uri"></param>
    ///<param name="attributes" default="null"></param>
    /**
    * 
    * @param mixed $name
    * @param mixed $uri
    * @param mixed $attributes the default value is null
    */
    public function __construct($name, $uri, $attributes=null){
        $this->m_uri=$uri;
        $this->m_attributes=$attributes;
        $this->m_def=new IGKHtmlItem("definitions");
        $this->m_def["name"]=$name;
        $this->m_def["targetNamespace"]=$this->TargetNS;
        $this->m_def["xmlns"]="http://schemas.xmlsoap.org/wsdl/";
        $this->m_def["xmlns:soap"]="http://schemas.xmlsoap.org/wsdl/soap/";
        $this->m_def["xmlns:xsd"]="http://www.w3.org/2001/XMLSchema";
        $this->m_def["xmlns:".$this->NSPrefix]=$this->getNSUri();
        $this->m_message=$this->m_def->addChildNodeView("message");
        $this->m_porttype=$this->m_def->addChildNodeView("portType");
        $this->m_binding=$this->m_def->addChildNodeView("binding");
        $this->m_service=$this->m_def->addChildNodeView("service");
    }
    ///<summary></summary>
    ///<param name="name"></param>
    ///<param name="style" default="rpc"></param>
    ///<param name="porttype" default="null"></param>
    ///<param name="enctype" default="'encoded'"></param>
    /**
    * 
    * @param mixed $name
    * @param mixed $style the default value is "rpc"
    * @param mixed $porttype the default value is null
    * @param mixed $enctype the default value is 'encoded'
    */
    public function addBindingService($name, $style="rpc", $porttype=null, $enctype='encoded'){
        $c=$this->m_binding->AddChild();
        $c["name"]=$name;
        $c["type"]=is_object($porttype) ? "igkns:".igk_getv($porttype, "name"): (is_string($porttype) ? $porttype: null);
        $c->addNode("soap:binding")->setAttribute("style", $style)->setAttribute("transport", "http://schemas.xmlsoap.org/soap/http");
        if($porttype){
            $this->addServiceOperation($c, "m1", $enctype, $porttype);
        }
        return $c;
    }
    ///<summary></summary>
    ///<param name="n"></param>
    ///<param name="input"></param>
    ///<param name="output" default="null"></param>
    ///<param name="porttype" default="null"></param>
    /**
    * 
    * @param mixed $n
    * @param mixed $input
    * @param mixed $output the default value is null
    * @param mixed $porttype the default value is null
    */
    public function addMethod($n, $input, $output=null, $porttype=null){
        $m=$this->m_message->AddChild();
        $m["name"]=$n."Request";
        if($input) foreach($input as $k=>$v){
            $p=$m->addNode("part");
            $p["name"]=$k;
            $p["type"]=$v;
        }
        $m=$this->m_message->AddChild();
        $m["name"]=$n."Response";
        if($output) foreach($output as $k=>$v){
            $p=$m->addNode("part");
            $p["name"]=$k;
            $p["type"]=$v;
        }
        if($porttype == null){
            $p=$this->m_porttype->AddChild();
            $p["name"]=$n."_porttype";
        }
        else
            $p=$porttype;
        $op=$p->addNode("operation");
        $op["name"]=$n;
        $op->addNode("input")->setAttribute("message", "igkns:".$n."Request");
        $op->addNode("output")->setAttribute("message", "igkns:".$n."Response");
    }
    ///<summary></summary>
    ///<param name="srvname"></param>
    ///<param name="doc"></param>
    ///<param name="srv"></param>
    ///<param name="loc"></param>
    /**
    * 
    * @param mixed $srvname
    * @param mixed $doc
    * @param mixed $srv
    * @param mixed $loc
    */
    public function addService($srvname, $doc, $srv, $loc){
        $d=$this->m_service->AddChild();
        $d["name"]=$srvname;
        $d->addNode("documentation")->Content=$doc;
        $p=$d->addNode("port");
        $p["name"]="port";
        $p["binding"]=is_object($srv) ? "igkns:".igk_getv($srv, "name"): $srv;
        $p->addNode("soap:address")->setAttribute("location", $loc);
        $this->m_cservice=$d;
        return $d;
    }
    ///<summary></summary>
    ///<param name="srv"></param>
    ///<param name="name"></param>
    ///<param name="type" default="encoded"></param>
    ///<param name="urn" default="sample:demo"></param>
    /**
    * 
    * @param mixed $srv
    * @param mixed $name
    * @param mixed $type the default value is "encoded"
    * @param mixed $urn the default value is "sample:demo"
    */
    protected function addServiceOperation($srv, $name, $type="encoded", $urn="sample:demo"){
        $op=$srv->addNode("operation");
        $op["name"]=$name;
        $op->addNode("soap:operation")->setAttribute("soapAction", $name);
        $input=$op->addXmlNode("input");
        $output=$op->addXmlNode("output");
        $input->Content=<<<EOF
            <soap:body
               encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
               namespace="urn:{$urn}"
               use="{$type}"/>
EOF;
        $output->Content=<<<EOF
            <soap:body
               encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
               namespace="urn:{$urn}"
               use="{$type}"/>
EOF;
        return $op;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getDocumentation(){
        return igk_getv($this->m_attributes, "doc", "service documentation");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getNSPrefix(){
        return igk_getv($this->m_attributes, "nsprefix", "igkns");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getNSUri(){
        return igk_getv($this->m_attributes, "nsuri", "http://www.igkdev.com");
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getTargetNS(){
        return igk_getv($this->m_attributes, "targetns", "http://www.igkdev.com");
    }
    ///<summary></summary>
    ///<param name="t"></param>
    /**
    * 
    * @param mixed $t
    */
    protected final function getXSDType($t){
        $v_rt="xsd:string";
        $args=array(
            "i1"=>"xsd:boolean",
            "i4"=>"xsd:int",
            "f4"=>"xsd:float",
            "sb"=>"xsd:byte",
            "b"=>"xsd:unsignedByte",
            "ub"=>"xsd:unsignedByte",
            "d1"=>"xsd:date",
            "t1"=>"xsd:time",
            "dt"=>"xsd:dateTime"
        );
        if(isset($args[$t]))
            $v_rt=$args[$t];
        return $v_rt;
    }
    ///<summary></summary>
    ///<param name="n"></param>
    ///<param name="attrs" default="null"></param>
    /**
    * 
    * @param mixed $n
    * @param mixed $attrs the default value is null
    */
    public function initService($n, $attrs=null){
        $this->m_srv=$this->addBindingService($n."_bindingService");
        $this->addService($n, igk_getv($attrs, "doc"), $this->m_srv, $this->m_uri);
    }
    ///<summary></summary>
    ///<param name="className"></param>
    ///<param name="srvName"></param>
    ///<param name="attrs" default="null"></param>
    /**
    * 
    * @param mixed $className
    * @param mixed $srvName
    * @param mixed $attrs the default value is null
    */
    public function registerClass($className, $srvName, $attrs=null){
        $cl=is_object($className) ? get_class($className): (class_exists($className) ? $className: null);
        if($cl == null)
            return;
        $r=new ReflectionClass($cl);
        if($r->isAbstract())
            return;
        $port=$this->m_porttype->AddChild();
        $port["name"]=$cl."_porttype";
        if($this->m_srv == null){
            $this->m_srv=$this->addBindingService($cl."_binding_service");
            $this->addService($srvName, igk_getv($attrs, "doc"), $this->m_srv, $this->m_uri);
        }
        $this->m_srv["type"]=$this->getNSPrefix().":".$port["name"];
        foreach(get_class_methods($cl) as  $n){
            $m=new ReflectionMethod($cl, $n);
            if($m->isPublic() && !$m->isStatic() && !$m->isConstructor()){
                $i=array();
                foreach($m->getParameters() as $p){
                    $i[$p->name]="xsd:string";
                }
                $o=array();
                $o[$n.
                "_result"]="xsd:string";
                $this->addMethod($n, $i, $o, $port);
                $this->addServiceOperation($this->m_srv, $n);
            }
        }
    }
    ///<summary>register methods </summary>
    ///<param name="classname" >class name</param>
    ///<param name="srvName" >service name </param>
    ///<param name="funclist" >array list of available functions</param>
    /**
    * register methods
    * @param mixed $classname class name
    * @param mixed $srvName service name
    * @param mixed $funclist array list of available functions
    */
    public function registerMethod($className, $srvName, $funclist){
        $cl=is_object($className) ? get_class($className): (class_exists($className) ? $className: null);
        if($cl == null)
            return;
        $r=new ReflectionClass($cl);
        if($r->isAbstract())
            return;
        $port=$this->m_porttype->AddChild();
        $port["name"]=$cl."_porttype";
        $attrs = [];
        if($this->m_srv == null){
            $this->m_srv=$this->addBindingService($cl."_binding_service");
            $this->addService($srvName, igk_getv($attrs, "doc"), $this->m_srv, $this->m_uri);
        }
        $this->m_srv["type"]=$this->getNSPrefix().":".$port["name"];
        $tlist="i4|f4|b";
        $v_match="/_(?P<type>(".$tlist."))$/";
        foreach($funclist as $n){
            $m=new ReflectionMethod($cl, $n);
            if($m->isPublic() && !$m->isStatic() && !$m->isConstructor()){
                $i=array();
                foreach($m->getParameters() as $p){
                    $v_rt="xsd:string";
                    if(preg_match_all($v_match, $p->name, $tab)){
                        $v_rt=$this->getXSDType($tab["type"][0]);
                    }
                    $i[$p->name]=$v_rt;
                }
                $o=array();
                $o[$n.
                "_result"]="xsd:string";
                $this->addMethod($n, $i, $o, $port);
                $this->addServiceOperation($this->m_srv, $n);
            }
        }
    }
    ///<summary></summary>
    ///<param name="f"></param>
    /**
    * 
    * @param mixed $f
    */
    public function Save($f){
        igk_set_env(IGK_ENV_NO_TRACE_KEY, 1);
        IGKIO::WriteToFileAsUtf8WBOM($f, igk_html_render_xml($this->m_def), true);
        igk_set_env(IGK_ENV_NO_TRACE_KEY, null);
    }
}