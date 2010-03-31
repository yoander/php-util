<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ImageHandler
 *
 * @author yoander
 */

class ImageHandler {

    private $file;

    private $newFile;

    private $width;

    private $height;

    private $newWidth;

    private $newHeight;

    private $types;

    private $loaders;

    private $creators;

    private $mime;

    private $extension;

    private $size;

    private $newSize;

    const BY_WIDTH = 'by_width';

    const BY_HEIGHT = 'by_height';

    public function  __construct() {
        $this->types = array('image/jpeg', 'image/png', 'image/gif');
        $this->loaders = array(
            'image/jpeg' => 'imagecreatefromjpeg',
            'image/png'  => 'imagecreatefrompng',
            'image/gif' => 'imagecreatefromgif'
        );
        $this->creators = array(
            'image/jpeg' => 'imagejpeg',
            'image/png'  => 'imagepng',
            'image/gif' => 'imagegif'
        );

    }


    public function getMime() {
        return $this->mime;
    }

    public function getExtension() {
        return $this->extension;
    }

    public function getWIdth() {
        return $this->width;
    }

    public function getNewWidth() {
        return $this->newWidth;
    }

    public function getHeight() {
        return $this->height;
    }

    public function getNewHeight() {
        return $this->newHeight;
    }

    public function getSize() {
        return $this->size;
    }

    public function getNewSize() {
        return $this->newSize;
    }

    /**
     * Manipula la imagen desde un flujo, por ejemplo una imagen recuperada desde la
     * base de datos;
     * @param <type> $image An image resource;
     * @param <type> $mime
     * @return <boolean>
     */
    public function loadData($image, $mime) {
        if ( !in_array($mime, $this->types) ) {
            throw new ImageHandlerException("Image mime type $mime not supported", 0);
        }

        if (!($this->file = @imagecreatefromstring($image))) {
            throw new ImageHandlerException('Could not load image from string', 0);
        }

        $this->width = imagesx($this->file);
        $this->height = imagesy($this->file);
        $this->mime = $mime;
        return true;
    }

    /**
     * Carga un fichero en memoria
     * @param <type> $path
     * @return <type>
     */
    public function loadFile($path) {
        $this->extension = pathinfo($path, PATHINFO_EXTENSION);
        if (!$dims = @getimagesize($path)) {
            throw new ImageHandlerException("Could not find image: $path", 0);
        }
        if (!in_array($dims['mime'], $this->types)) {
            throw new ImageHandlerException("Image MIME type  {$dims['mime']}  not supported", 0);
        }
        
        $loader = $this->loaders[$dims['mime']];
        $this->file = $loader($path);
        #print_r($this->file);
        $this->width = $dims[0];
        $this->height = $dims[1];
        $this->mime = $dims['mime'];
        if (!isset ($this->size))
            $this->size = filesize($path);
        return true;
       
    }

    /**
     * Redimensiona una imagen a un tamaño x
     * @param <type> $newWidth
     * @param <type> $newHeight
     * @param <type> $scale
     * @param <type> $inflate
     * @param <type> $by
     */
    public function _resize($newWidth, $newHeight, $scale = true, $inflate = true, $by = '') {
        if (!isset($newWidth) || !is_numeric($newWidth)) {
            $msg = 'Invalid width';
        }

        if (!isset($newHeight) || !is_numeric($newHeight)) {
            if (!isset($msg)) {
                $msg = 'Invalid height';
            } else {
                $msg .= ', height';
            }
        }

        if (isset($msg))
            throw new ImageHandlerException($msg, 0);

        switch ($by) {
            case self::BY_WIDTH:
                $cond1 = ($newWidth < $this->width);
                $cond2 = ($newHeight < $this->height);
                break;
            case self::BY_HEIGHT:
                $cond1 = false;
                $cond2 = ($newHeight < $this->height);
                break;
            default:
                $cond1 = ($this->width > $this->height);
                $cond2 =  ($this->width < $this->height);
        }
     
        $this->newWidth = $newWidth;
        $this->newHeight = $newHeight;

       if ($scale) {
            if ( $cond1 ) {
                $this->newWidth = $newWidth;
                $this->newHeight = floor( $this->height * ($newWidth / $this->width));
            } elseif ( $cond2 ) {
                $this->newHeight = $newHeight;
                $this->newWidth = floor($this->width * ($newHeight / $this->height));
            }
        }
        $this->newFile = imagecreatetruecolor($this->newWidth, $this->newHeight);

        if ( $this->width <= $this->newWidth && $this->height <= $this->newHeight &&  $inflate == false ) {
            $this->newFile = $this->file;
        } else {
            imagecopyresampled( $this->newFile, $this->file, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $this->width, $this->height );
        }

    }

    /**
     *
     *
     * @param <type> $newWidth
     * @param <type> $newHeight
     * @param <type> $scale
     * @param <type> $inflate
     */
    public function resize($newWidth, $newHeight, $scale = true, $inflate = true) {
        try {
             $this->_resize($newWidth, $newHeight, $scale, $inflate);
        } catch (ImageHandlerException $e) {
            throw new ImageHandlerException($e->getMessage(), $e->getCode());
        }
    }

    /**
     *
     *
     * @param <type> $newWidth
     * @param <type> $newHeight
     * @param <type> $scale
     * @param <type> $inflate
     */
    public function resizeByWidth($newWidth, $newHeight, $scale = true, $inflate = true) {
        try {
              $this->_resize($newWidth, $newHeight, $scale, $inflate, self::BY_WIDTH);
        } catch (ImageHandlerException $e) {
            throw new ImageHandlerException($e->getMessage(), $e->getCode());
        }
    }


    public function resizeByHeight($newWidth, $newHeight, $scale = true, $inflate = true) {
        try {
              $this->_resize($newWidth, $newHeight, $scale, $inflate, self::BY_HEIGHGT);
        } catch (ImageHandlerException $e) {
            throw new ImageHandlerException($e->getMessage(), $e->getCode());
        }
    }


    /**
     * Flush content to the browser
     * @return <type>
     */
    public function flush() {
        $creator = $this->creators[$this->mime];
    return $creator($this->newFile);
    }

    public function getRaw() {
        return $this->newFile;
    }

    public function save($path) {
        $creator = $this->creators[$this->mime];
        if (!$creator($this->newFile, $path))
            throw new ImageHandlerException('Image could not be saved', 1);
        $this->newSize = filesize($path);
        return true;
    }

    public function destroyNewImage() {
       /* $this->newHeight = 0;
        $this->newWeight = 0;
        $this->newWidth = 0;*/
        if (isset($this->newFile) && is_resource($this->newFile))
            return imagedestroy($this->newFile);
        return false;
    }


    public function destroy() {
       /* $this->height = 0;
        $this->weight = 0;
        $this->width = 0;*/
        if (isset($this->file) && is_resource($this->file))
            return imagedestroy($this->file);
        return false;
    }

    public function __destruct() {
        $this->destroyNewImage();
        $this->destroy();
    }

    public static function copy($source, $dest) {
        copy($source, $dest . ".{$this->extension}");
    }

    public static function getFileExtension($path) {
        $info = pathinfo($path);
        $this->extension = $info['extension'];
    }
}
?>
