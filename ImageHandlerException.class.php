<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class ImageHandlerException extends Exception {

    public function  __construct($message, $code) {
        parent::__construct($message, $code);
    }
    
}

?>
