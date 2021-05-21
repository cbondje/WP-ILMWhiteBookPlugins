<?php

namespace IGK\System\Http;

use Closure;
use IGK\Models\Users;
use Exception;
use IGK\Actions\Dispatcher;
use IGK\Controllers\BaseController;
use IGK\System\Exceptions\ArgumentTypeNotValidException;
use IGKException;

/**
 * route action handler
 * @package IGK\System\Http
 */
class RouteActionHandler
{
    /**
     * name for searching
     * @var mixed
     */
    protected $name;
    /**
     * route type
     * @var mixed
     */
    private $type;
    /**
     * route path
     * @var mixed
     */
    protected $path;
    /**
     * store def access
     * @var mixed
     */
    protected $classBind;

    /**
     * stored expression
     * @var mixed
     */
    protected $m_expressions;
    /**
     * get verbs
     * @var array
     */
    protected $verbs = [];

    /**
     * autorisation string
     * @var string|array
     */
    protected $auth;

    /**
     * auth requirement 
     * @var bool
     */
    protected $auth_requirement;

    /**
     * get the attached model user
     * @var mixed
     */
    protected $user;

    /**
     * routing info - updated after match
     * @var mixed
     */
    protected $info;

    /**
     * bool get if this route require ajx request
     * @var mixed
     */
    protected $ajx;
    /**
     * set the route
     */
   protected $route;
    /**
     * 
     * @param string $path path 
     * @param mixed $handleClass 
     * @param string $verb 
     * @return void 
     */
    public function __construct($path, $handleClass, $type = "action", $verb = "GET, POST")
    {
        if (!is_string($path))
            throw new ArgumentTypeNotValidException("path");
        $this->path = $path;
        $this->classBind = $handleClass;
        $this->type = $type;
        $this->verbs = is_string($verb) ? array_map("trim", explode(",", $verb)) : (is_array($verb) ? $verb : ['*']);
        $this->ajx = false;
    }
    /**
     * get uri by name
     * @param mixed $name 
     * @return void 
     */
    public function getUri($path = null)
    {
        if ($this->info){
            return igk_getv($this->info, "ruri");
        } 
        return  null;
    }
    
    /**
     * set roting property object
     * @param object $info 
     * @return void 
     */
    public function setRoutingInfo(object $info)
    {
        if ($info == null) {
            $this->info = $info;
            return $this;
        }
        $this->info = igk_get_robjs("ruri", 0, $info);
        return $this;
    }
    public function getRoutingInfo($name=null)
    {
        if ($name!==null && $this->info){
            return igk_getv($this->info, $name);
        }
        return $this->info;
    }
    public static function uri($name)
    {
        if ($route = Route::GetRouteByName($name)) {
            return $route->getUri();
        }
        return null;
    }
    protected function setRoute($route)
    {
        $this->route = $route;
        return $this;
    }
    public function getRoute()
    {
        return $this->route;
    }
    public function getPath()
    {
        return $this->path;
    }
    public function getVerbs()
    {
        return $this->verbs;
    }
    /**
     * return the selected user auth
     * @return mixed 
     */
    public function getUserAuth()
    {
        if ($u = $this->user) {
            return $u->{"::auth"};
        }
        return;
    }
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }
    /**
     * return the selected use
     * @return mixed 
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
     * return the name
     */
    public function getName()
    {
        return $this->name;
    }
    /** 
     * return this type
    */
    public function getType(){
        return $this->type;
    }


    /**
     * retrieve pattern regex expression
     * @return string 
     * @throws Exception 
     */
    protected function getPatternRegex()
    {
        $croute = "/" . ltrim($this->path, "/");
        if (preg_match_all("/(\{\\s*(?P<name>" . IGK_IDENTIFIER_PATTERN . ")(?P<option>\\*)?\\s*\})/i", $croute, $tab)) {
            $count = 0;
            foreach ($tab["name"] as $i) {
                $c = trim($i);
                $s = $tab[0][$count];
                $opt = igk_getv($tab["option"], $count) == "*";
                // print_r($tab["option"]);
                if ($g = igk_getv($this->m_expressions, $c)) {
                    if ($opt) {
                        $g = "(/{$g}(/)?)?";
                        $s = "/" . rtrim($s, "/"); 
                    }
                    $croute = str_replace($s, "(".$g.")", $croute);
                }
                $count++;
            } 
        }
        return "#^" . $croute . "$#";
    }
    public function getPathUri(){
        $croute = "/" . ltrim($this->path, "/");
        $croute = preg_replace("/(\{\\s*(?P<name>" . IGK_IDENTIFIER_PATTERN . ")(?P<option>\\*)?\\s*\})/i", "",  $croute);
        if ($pos = strpos($croute, "//")){
            $croute = substr($croute, 0, $pos+1);
        }        
        return $croute;
    }
    /**
     * check if user is allowed agains this auth
     * @param Users $user 
     * @return mixed 
     */
    public function isAuth(Users $user)
    {
        if ($user && !empty($this->auth)) {
            $r = $user->auth($this->auth, $this->auth_requirement);
            return $r;
        }
        return true;
    }
    /**
     * get if match with the verbs
     * @param mixed $path 
     * @param string $verb 
     * @return int|false 
     * @throws Exception 
     */
    public function match($path, $verb = 'GET')
    {

        if (!in_array($verb, $this->verbs)) {
            return false;
        }
        $regex = $this->getPatternRegex(); 
        if ($r = preg_match($regex, "/" . ltrim($path, "/"))) {
            if ($this->ajx && !igk_is_ajx_demand()){                
                throw new  RequestException(400);                                
            }
            $this->setRoute($path);
        }
        return $r;
    }
    /**
     * add expression
     * @param mixed $name 
     * @param mixed $expression 
     * @return $this 
     */
    private function addExpression($name, $expression)
    {
        $this->m_expressions[$name] = $expression;
        return $this;
    }
    /**
     * set the shorcut key name
     */
    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * set autorisation key name
     * @param mixed $name string|array of autorisation
     * @param bool $strict autorisation requirement
     * @return RouteActionHandler 
     */
    public function auth($name, bool $strict=false)
    {
        $this->auth = $name;
        $this->auth_requirement = $strict;
        return $this;
    }
    /**
     * add where condition expression
     */
    public function where($id, $pattern)
    {
        return $this->addExpression($id, $pattern);
    }
    /**
     * set ajx route pattern requirement
     * @param bool $value 
     * @return $this 
     */
    public function ajx(bool $value =  true){
        $this->ajx = $value;
        return $this;
    }

    /**
     * process this action
     * @param BaseController $controller 
     * @param mixed $args 
     * @return mixed 
     * @throws IGKException 
     */
    public function process(BaseController $controller, ...$args)
    {
        $type = 0;
        $cl = "";
        $func_name = null;
        if (is_array($this->classBind)) {
            if (is_callable($this->classBind)) {
                //call static
                $type = 1;
                $cl = $this->classBind[0];
                $func_name = $this->classBind[1];
            } else {
                $cl = $this->classBind[0];
                $func_name = $this->classBind[1];
                if (method_exists($cl, $func_name)) {
                    $type = 3;
                }
            }
        } else {
            if (!is_string($this->classBind) || !class_exists($this->classBind)) {
                throw new IGKException("Process failed : not class Found :: " . $this->classBind);
            }
            $type = 2;
        } 
        switch ($type) {
            case 2:

                $cl = $this->classBind;
                $cl = new $cl($controller, $this);
                $name = array_shift($args);
                if (empty($name)) {
                    $name = "index";
                }
                if ($fc = closure::fromCallable([$cl, $name])->bindTo($cl)) {
                    return Dispatcher::Dispatch($fc, ...$args);
                }
                return $cl->$name(...$args);
            case 3:
                $g = new $cl($controller, $this);
                if ($fc = closure::fromCallable([$g, $func_name])->bindTo($g)){
                    array_shift($args);
                    return Dispatcher::Dispatch($fc, ...$args);
                }
                return call_user_func_array([$g, $func_name], $args);
            case 1:
                $g = new $cl($controller, $this);
                if ($fc = closure::fromCallable([$g, $func_name])->bindTo($g)){
                    array_shift($args);
                    return Dispatcher::Dispatch($fc, ...$args);
                }
                return call_user_func_array([$g, $func_name], $args);
                break;                
        }
    }
    /**
     * set allowed verb
     * @param array $verb 
     * @return $this 
     */
    public function setVerb(array $verb)
    {
        $this->verbs = $verb;
        return $this;
    }
    /**
     * shortcut function
     * @param array $verb 
     * @return mixed 
     */
    public function verbs(array $verb)
    {
        return $this->setVerb($verb);
    }
    public static function GetRouteUri(RouteActionHandler $route, BaseController $controller, $path=null){
        $t = $route->gettype();
        $c = "";
        if (class_exists($t)){
            $bname = basename(igk_io_dir($t));
            $c = strtolower(igk_preg_match("/^(?P<name>(.)+)(Action)$/", $bname, "name",0));                                
            if (!empty($c)){
                $c = $c.$route->getPathUri();
                if (!empty($path)){
                    $c = rtrim($c, "/");
                }
                return $controller->getAppUri(implode("/", array_filter([$c,$path])));
            }
        }
        return null;
    }
}
