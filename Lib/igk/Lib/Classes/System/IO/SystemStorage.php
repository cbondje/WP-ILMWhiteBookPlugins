<?php

namespace IGK\System\IO;

use IGKException;

/**
 * local system file application storage
 * @package IGK\System\IO
 */
class SystemStorage extends Storage{
    private $root_dir;
    public function __construct()
    {
        $this->root_dir = igk_io_applicationdatadir()."/storage";        
    }
    private function _full_path($path){
        return igk_io_combine($this->root_dir, $path); 
    }
    public function exists($path): bool { 
        return file_exists($this->_full_path($path));
    }   

    public function get($path): ?object { 
        return (object)[
            "fullpath"=>$this->_full_path($path),
            "exists"=>$this->exists($path)
        ];
    }   

    public function unlink($path) {
        if ($this->exists($path)){
            unlink($this->_full_path($path));
        }
    }
    /**
     * store to file
     * @param mixed $file 
     * @param mixed $data 
     * @return bool 
     * @throws IGKException 
     */
    public static function Store($file, $data){
        $n = new self;
        $path = $n->_full_path($file);
        return igk_io_w2file($path, $data);
    }
    public static function __callStatic($name, $arguments)
    {
        $instance = igk_environment()->GetClassInstance(static::class);
        if (count($arguments)>0){
            if (method_exists($instance, $fc= $arguments[0])){
                return $instance->$fc($name);
            }
        }
        return $instance->get($name);
    }
}