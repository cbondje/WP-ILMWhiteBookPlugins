<?php

namespace IGK\Tests;

use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase{
    // call before all launching test - and output is consider in return of the output string test.
    protected function setUp():void{ 
    }
    protected function CreateController($classname){
        return Utils::CreateController($classname);
    }
    public function wln(){
        ob_start();
        $fc = "igk_wln";
        $args = func_get_args();
        $fc(...$args);
        $w = ob_get_clean();
        fwrite(STDOUT, $w);
    }
}