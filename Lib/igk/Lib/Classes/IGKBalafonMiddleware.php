<?php
// @file: IGKBalafonMiddleware.php
// @author: C.A.D. BONDJE DOUE
// @copyright: igkdev © 2019
// @license: Microsoft MIT License. For more information read license.txt
// @company: IGKDEV
// @mail: bondje.doue@igkdev.com
// @url: https://www.igkdev.com

///<summary>Represente class: IGKBalafonMiddleware</summary>
/**
* Represente IGKBalafonMiddleware class
*/
abstract class IGKBalafonMiddleware{
    private $_next;
    private static $sm_manager;
    ///<summary></summary>
    /**
    * 
    */
    protected function __construct(){}
    ///<summary> attach the middleware</summary>
    ///<param name="middle">the middleware to attach</summary>
    ///<param name="service">application service to initialize</param>
    ///<param name="wherelist"> list that store the all middleware for chain list</param>
    /**
    *  attach the middleware
    * @param mixed $middlethe middleware to attach
    * @param mixed $serviceapplication service to initialize
    * @param mixed $wherelist list that store the all middleware for chain list
    */
    public static function Attach($middle, $service){
        if($c=$service->GetLastMiddleware()){
            $c->_next=$middle;
        }
        $service->Attach($middle);
        $middle->initialize($middle);
    }
    ///<summary></summary>
    ///<param name="name"></param>
    ///<param name="args" default="null"></param>
    ///<param name="service" default="null"></param>
    /**
    * 
    * @param mixed $name
    * @param mixed $args
    * @param mixed $service
    */
    public static function CreateMiddleware($name, $args=null, $service=null){
        if($name === __CLASS__)
            return null;
        if(class_exists($cl=$name) || (class_exists($cl="IGK".$name."Middleware"))){
            if(is_subclass_of($cl, __CLASS__)){
                $_ref=new ReflectionClass($cl);
                $cp=0;
                $middle=null;
                if(($ctr=$_ref->getConstructor()) && (($cp=$ctr->getNumberOfRequiredParameters()) > 0)){
                    $middle=$_ref->newInstanceArgs($args);
                }
                else
                    $middle=new $cl();
                self::Attach($middle, $service);
                return $middle;
            }
        }
        return null;
    }
    ///<summary></summary>
    /**
    * 
    */
    public static function GetManager(){
        if(count($c=self::$sm_manager) > 0){
            return self::$sm_manager[0];
        }
        return null;
    }
    ///<summary></summary>
    /**
    * 
    */
    public function getService(){
        return self::GetManager();
    }
    ///<summary>initialize the middleware </summary>
    ///<param name="service">IIGKBalafonApplicationMiddlewareService instance</summary>
    /**
    * initialize the middleware
    * @param mixed $serviceIIGKBalafonApplicationMiddlewareService instance
    */
    protected function initialize($service){}
    ///<summary></summary>
    /**
    * 
    */
    public function invoke(){
        $this->next();
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function next(){
        if($this->_next){
            $this->_next->invoke();
        }
    }
    ///<summary></summary>
    ///<param name="service"></param>
    ///<param name="wherelist"></param>
    /**
    * 
    * @param mixed $service
    * @param mixed $wherelist
    */
    public static function Process($service, $wherelist){
        if(self::$sm_manager == null)
            self::
        $sm_manager=array();
        array_unshift(self::$sm_manager, $service);
        if(count($wherelist) > 0){
            $wherelist[0]->invoke();
        }
        array_shift(self::$sm_manager);
    }
    ///<summary></summary>
    /**
    * 
    */
    protected function stopChain(){
        $this->chainFlag=1;
    }
}
