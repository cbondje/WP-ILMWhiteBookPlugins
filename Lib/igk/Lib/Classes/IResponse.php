<?php

namespace IGK;

/**
 * sent the output reponse
 * @package IGK
 */
interface IResponse{
    /**
     * send output
     * @return mixed 
     */
    function output();
}