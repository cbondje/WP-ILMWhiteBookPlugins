<?php

namespace IGK\System\IO;

/**
 * represet abstract storage 
 * @package IGK\System\IO
 */
abstract class Storage{
    /**
     * check if storage content path
     * @param mixed $path 
     * @return mixed 
     */
    public abstract function exists($path) : bool;
    /**
     * get storage info
     * @param mixed $path 
     * @return mixed 
     */
    public abstract function get($path) : ?object;

    /**
     * unlink path in 
     * @return mixed 
     */
    public abstract function unlink($path);
}