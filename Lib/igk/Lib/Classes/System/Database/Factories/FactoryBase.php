<?php

namespace IGK\System\Database\Factories;

use IGK\Models\ModelBase;

abstract class FactoryBase {
    protected $count;
    protected $model;
    
    public function __construct(ModelBase $model, $count=1){
        $this->count = $count;
        $this->model = $model; 
    }

    public function create(){ 
        for($i = 0; $i < $this->count; $i++){
            $def = $this->definition(); 
            $this->model::create($def);
        }    
    }
    abstract function definition();
}