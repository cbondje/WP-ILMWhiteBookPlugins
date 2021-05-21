<?php

/**
 * internaly used for non session handling.
 */
class IGKDummySetting{
    public function __get($name){
        return null;
    }
    public function __set($name, $value){

    }
    public function __call($name, $value){

    }
}