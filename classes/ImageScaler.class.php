<?php
/**
 *
 * This file is part of the php-util package.
 * (c) 2010 Yoander Valdés Rodríguez <yoander.valdes@gmail.com>
 * 
 *
 * For the full copyright and license information, please view the LICENSE
 * img that was distributed with this source code.
 * 
 * @author yoander
 *
 * This class allows you to scale an image given from the Img System or as
 * stream
 *
 */

class ImageScalerException extends Exception {}

class ImageScaler {
    
    	// Original image
	private $img;

    	// Scaled image
	private $newImg;

    	// Original width
	private $width;

    	// Original height
	private $height;

    	// Scaled width
	private $newWidth;

    	// Scaled height 
	private $newHeight;

    	// Supported types: png, jpeg, gif 
	private $types;

    	/* 
	*
	* Loaders for supported types: imagecreatefromjpeg, imagecreatefrompng
        * imagecreatefromgif
	*/
	private $loaders;

        // creators for supported types: imagejpeg, imagepng, imagegif
        private $creators;

    	private $mime;

    	private $imgName;
	
	private $extension;

    	// Original img size in bytes
	private $size;

    	// Scaled img size en bytes
	private $newSize;

    	const SCALE_BY_WIDTH = 0;

    	const SCALE_BY_HEIGHT = 1;

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

	/**
	*
	* @return string Image mime type	
	*/
	public function getMime() {
		return $this->mime;
	}

	/*
	*
	* @return string Image extension
	*/
	public function getExtension() {
		return $this->extension;
	}

	/*
	*
	* @return numeric Original image width
	*/
	public function getWidth() {
		return $this->width;
	}

	/*
	*
	* @return numeric Scaled image width
	*/
	public function getNewWidth() {
		return $this->newWidth;
	}

	/**
	*
	* @return numeric Original image height
	*/
	public function getHeight() {
		return $this->height;
	}

	/*
	*
	* @return numeric Sclaed image height 
	*/
	public function getNewHeight() {
		return $this->newHeight;
	}

	/**
	*
	* @return Original image size in bytes 
	*/
	public function getSize() {
		return $this->size;
	}

	/**
	*
	* @return Scaled image size in bytes 
	*/
	public function getNewSize() {
		return $this->newSize;
	}

	/**
	* 
	* Get image properties from the image stream
	* 
	* @param string $image An image resource;
	* @param string $mime
	* @return boolean true on success
	* @thow ImageScalerException 
	*/
	public function loadFromStream($image, $mime = 'image/png') {
		if ( !in_array($mime, $this->types) ) {
		    	throw new ImageScalerException("Mime type: $mime not supported");
		}

		if (!($this->img = @imagecreatefromstring($image))) {
		    	throw new ImageScalerException('Could not load image from stream');
		}

		$this->width = imagesx($this->img);
		$this->height = imagesy($this->img);
		$this->mime = $mime;
		return true;
	}

	/**
	* 
	* Load an image from the File System
	* @param string $imgPath Full path to the image
	* @return boolean
	*/
	public function load($imgPath) {
		if (!is_readable($imgPath)) {
			throw new ImageScalerException("Image file: $imgPath is missing or is unreadable!");
		}
		
		$this->extension = pathinfo($imgPath, PATHINFO_EXTENSION);
		
		if (!$dims = @getimagesize($imgPath)) {
		    	throw new ImageScalerException("Image file: $imgPath could not be read");
		}
		
		if (!in_array($dims['mime'], $this->types)) {
		    	throw new ImageScalerException("Mime type: {$dims['mime']}  not supported");
		}

		$loader = $this->loaders[$dims['mime']];
		$this->img = $loader($imgPath);
		$this->width = $dims[0];
		$this->height = $dims[1];
		$this->mime = $dims['mime'];
		
		if (!isset ($this->size))
		    	$this->size = filesize($imgPath);
		return true;
	}

    /**
     * Scale an image 
     * @param integer $newWidth
     * @param integer $newHeight
     * @param boolean $scaleDown
     * @param boolean $inflate
     * @param integer $scaleBy
     * @return boolean
     */
    public function scale($newWidth, $newHeight, $scaleBy = self::SCALE_BY_WIDTH, $scaleDown = true, $inflate = true) {
        if (!isset($newWidth)) {
        	throw new ImageScalerException('New width is mandatory');
	}
        
	if (!is_numeric($newWidth)) {
        	throw new ImageScalerException("Invalid new width: $newWidth");
	}

        if (!isset($newHeight)) {
        	throw new ImageScalerException('New height is mandatory');
	}
        
	if (!is_numeric($newHeight)) {
        	throw new ImageScalerException("Invalid new height: $newHeight");
	}

        switch ($scaleBy) {
            case self::SCALE_BY_WIDTH:
                $cond1 = ($newWidth < $this->width);
                $cond2 = ($newHeight < $this->height);
                break;
            case self::SCALE_BY_HEIGHT:
                $cond1 = false;
                $cond2 = ($newHeight < $this->height);
                break;
            default:
                $cond1 = ($this->width > $this->height);
                $cond2 =  ($this->width < $this->height);
        }
     
        $this->newWidth = $newWidth;
        $this->newHeight = $newHeight;

       if ($scaleDown) {
            if ( $cond1 ) {
                $this->newWidth = $newWidth;
                $this->newHeight = floor( $this->height * ($newWidth / $this->width));
            } elseif ( $cond2 ) {
                $this->newHeight = $newHeight;
                $this->newWidth = floor($this->width * ($newHeight / $this->height));
            }
        }
        $this->newImg = @imagecreatetruecolor($this->newWidth, $this->newHeight);
	if (!$this->newImg) {
		throw new ImageScalerException('Cannot Initialize new GD image stream');
	}

        if ( $this->width <= $this->newWidth && $this->height <= $this->newHeight &&  $inflate == false ) {
            $this->newImg = $this->img;
        } else {
            $ok = @imagecopyresampled($this->newImg, $this->img, 0, 0, 0, 0, 
	    				$this->newWidth, $this->newHeight, $this->width, $this->height);
	    if (!$ok) {
	    	throw new ImageScalerException('The image could not be scaled');
	    }
	    
        }
	return true;

    }

    /**
    *
    * Save the scaled image to a file
    * @param string path Destination dir
    * @return boolean 
    */
    public function save($path) {
	$dir = dirname($path);
        if (!file_exists($dir)) {
            throw new ImageScalerException("$dir does not exists"); 
	}  

	if (!is_dir($dir)) {
            throw new ImageScalerException("$dir must be a dir"); 
	}
	
	if (!is_writeable($dir)) {
            throw new ImageScalerException("$dir must be writeable"); 
	}
	
	$creator = $this->creators[$this->mime];
        if (!$creator($this->newImg, $path)) {
            throw new ImageScalerException('Image could not be saved'); 
	}
        $this->newSize = filesize($path);
        return true;
    }
    
    public function __destruct() {
        if (isset($this->newImg) && is_resource($this->newImg)) { @imagedestroy($this->newImg); }
        if (isset($this->img) && is_resource($this->img)) { @imagedestroy($this->img); } 
    }

}
?>
