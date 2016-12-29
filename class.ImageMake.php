<?php

/** 
* class.ImageMake.php
* ===================
* A class containing functions for image resize and thumbnail.
*
* @Developer 	: Ravikumar Chauhan
* @Twitter 		: https://twitter.com/rkchauhan01
* @GitHub 		: https://github.com/rkchauhan
* @CodePen 		: http://codepen.io/rkchauhan
*
* @Copyright 	: Copyright (c) 2016 Ravikumar Chauhan
* @License 		: MIT <http://opensource.org/licenses/MIT>
*/
class ImageMake
{
	private $imageName;

	private $imageWidth;

	private $imageHeight;

	private $imageMime;

	private $imageDirname;

	private $imageBasename;

	private $imageExtension;

	protected $imagePathInfo;

	protected $imageResource;

	public $newImage;

	protected $allowFormats = ['jpg', 'jpeg', 'png', 'gif'];

	protected $errorMessage = [
		'Image not found.',
		'Image file is missing or invalid.',
		'Sorry, only JPG, JPEG, PNG & GIF files are allowed.',
		'Sorry, image quality should be 0 to 100.',
	];

	function __construct($image = null)
	{
		if(empty($image)) {
	      die($this->errorMessage[0]);
	    } else if(!is_file($image)) {
	    	die($this->errorMessage[1]);
	    } else if(!in_array(pathinfo($image, PATHINFO_EXTENSION), $this->allowFormats)) {
	    	die($this->errorMessage[2]);
	    }

	    $path_info = pathinfo($image);
	    $image_info = getimagesize($image);

	    $this->imagePathInfo = $path_info;
	    $this->imageName = $path_info['filename'];
	    $this->imageDirname = $path_info['dirname'];
	    $this->imageBasename = $path_info['basename'];
	    $this->imageExtension = '.'. strtolower($path_info['extension']);

	    $this->imageWidth = $image_info[0];
	    $this->imageHeight = $image_info[1];
	    $this->imageMime = $image_info['mime'];

	    $this->imageResource = $this->imageResource($image);
	}

	private function imageResource($image)
	{
		$mime = $this->imageMime;

		$src = null;

		if($mime == 'image/gif') {
			$src = imagecreatefromgif($image);
		} else if($mime == 'image/png') {
			$src = imagecreatefrompng($image);
		} else {
			$src = imagecreatefromjpeg($image);
		}

		return $src;
	}

	public function thumbnailImage($new_width, $new_height)
	{
		$mime = $this->imageMime;

		$this->newImage = imagecreatetruecolor($new_width, $new_height);

		$src_aspect = $this->imageWidth / $this->imageHeight;
		$crop_aspect = $new_width / $new_height;

		if($src_aspect < $crop_aspect) {
			$scale  = $new_width / $this->imageWidth;
			$size_w = $new_width;
			$size_h = $new_width / $src_aspect;
			$pos_x  = 0;
			$pos_y  = ($this->imageHeight * $scale - $new_height) / $scale / 2;
		} else if($src_aspect > $crop_aspect) {
			$scale  = $new_height / $this->imageHeight;
			$size_w = $new_height * $src_aspect;
			$size_h = $new_height;
			$pos_x  = ($this->imageWidth * $scale - $new_width) / $scale / 2;
			$pos_y  = 0;
		} else {
			$size_w = $new_width;
			$size_h = $new_height;
			$pos_x  = 0;
			$pos_y  = 0;
		}

		$size_w = max($size_w, 1);
    	$size_h = max($size_h, 1);

		if($mime == 'image/gif' || $mime == 'image/png') {
			imagealphablending($this->newImage, false);
			imagesavealpha($this->newImage, true);
			$transparent = imagecolorallocatealpha($this->newImage, 255, 255, 255, 127);
			imagefilledrectangle($this->newImage, 0, 0, $new_width, $new_height, $transparent);
		}

		imagecopyresampled($this->newImage, $this->imageResource, 0, 0, $pos_x, $pos_y, $size_w, $size_h, $this->imageWidth, $this->imageHeight);
	}

	public function displayImage($image_quality = 100)
	{
		if(!is_numeric($image_quality) || $image_quality > 100 || $image_quality < 0) {
			die($this->errorMessage[3]);
		}

		$mime = $this->imageMime;

		$image = isset($this->newImage) ? $this->newImage : $this->imageResource;

		// Scale quality from 0-100 to 0-9 for png file
		$png_quality = round(($image_quality / 100) * 9);

		header("Content-Disposition: inline; filename=\"" . $this->imageName.$this->imageExtension . "\"");
		header("Content-Type: ". $mime);

		if($mime == 'image/gif') {
			imagegif($image);
		} else if($mime == 'image/png') {
			imagepng($image, null, $png_quality);
		} else {
			imagejpeg($image, null, $image_quality);
		}
	}

	public function destroy()
	{
		$image = isset($this->newImage) ? $this->newImage : $this->imageResource;
		
		imagedestroy($image);
	}

	public function __get($property)
	{
		return $this->{$property};
	}

	public function getImageName()
	{
		return $this->imageName;
	}

	public function getImageWidth()
	{
		return $this->imageWidth;
	}

	public function getImageHeight()
	{
		return $this->imageHeight;
	}

	public function getImageMime()
	{
		return $this->imageMime;
	}

	public function getImageDirname()
	{
		return $this->imageDirname;
	}

	public function getImageBasename()
	{
		return $this->imageBasename;
	}

	public function getImageExtension()
	{
		return $this->imageExtension;
	}
}

?>