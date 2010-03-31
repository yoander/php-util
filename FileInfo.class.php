<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FileInfo
 *
 * @author yoander
 */
 class FileInfoException extends Exception {
     public function __construct($message, $code) {
         parent::__construct($message, $code);
     }
 }


class FileInfo {

    private $width;

    private $height;

    private $mime;

    private $size;

    private $extension;

    private $name;

    private $dir;


    public function __construct() {;}

    public function getWidth() {
        return $this->width;
    }

    public function getHeight() {
        return $this->height;
    }

    public function getMime() {
        return $this->mime;
    }

    public function getSize() {
        return $this->size;
    }

    public function getExtension() {
        return $this->extension;
    }

    public function getName() {
        return $this->name;
    }

    public function getDir() {
        return $this->dir;
    }

    public function loadInfo($fileName) {
        if (!file_exists($fileName) || !is_file($fileName))
            throw new FileInfoException('Invalid file name', 0);
        if (!is_readable($fileName))
             throw new FileInfoException('File could not be read', 1);

       $info = pathinfo($fileName);
       $this->dir = $info['dirname'];
       $this->name = $info['basename'];
       if (key_exists('extension', $info)) {
            $this->extension = $info['extension'];
       }

       $dims = @getimagesize($fileName);
       $this->width = $dims[0];
       $this->height = $dims[1];
       $this->mime = $dims['mime'];

       $this->size = filesize($fileName);
        
    }

    public static function getFileExtension($fileName) {
        if (!is_string($fileName))
            throw new FileInfoException('Invalid file name', 0);
        return pathinfo($fileName, PATHINFO_EXTENSION);
    }
}
?>
