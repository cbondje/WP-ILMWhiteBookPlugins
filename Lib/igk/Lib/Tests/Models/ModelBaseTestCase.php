<?php 

namespace IGK\Tests\Models;
 
use IGK\Tests\BaseTestCase;
use IGK\Tests\Utils;

abstract class ModelBaseTestCase extends BaseTestCase{
    // call before all launching test - and output is consider in return of the output string test.
    protected function setUp():void{ 
        parent::setUp();
    }
    protected function getDefaultModelName(){
        return null;
    }
    abstract protected function getControllerClass();

    protected function getModel($modelName=null){
        try{
            $controller = $this->CreateController($this->getControllerClass());
            if (
                $modelName = $modelName ?? $this->getDefaultModelName()){            
                $model = $controller->loader->model($modelName);
                return $model;
            }else {
                $model = $controller->getDb();
                return null;
            }
        } catch(\Exception $ex){
            $this->fail("model check failed: ".$ex->getMessage());
        }
    }

    ///<summary>check database schema</summary>
    public function test_db_schema(){
        Utils::CheckControllerDataBase($this, $this->getControllerClass());
    }

   
}