<?php
/** Basic operations for JPG files
** @author: Leonardo Molina lama_amx at hotmail dot com
** @version: 1.1
** @changelog:
	1.1 2013.04.10: Update
	1.0 2009.04.12: Initial release
*/
class ImageEncoder {
	const TYPE_JPG ='jpg';
	const TYPE_PNG ='png';
	
	public $file;
	public $fileType;
	public $imageData;

	public function __construct ($file){
		$this->fileType =self::TYPE_JPG;
		$this->file =$file;
	}

	//@deprecated
	public function saveFile ($path, $quality=80){ 
		$result =imagejpeg ($this->imageData, $path, $quality);
		imagedestroy ($this->imageData);
		return $result;
	}

	public function saveJPEG ($path, $quality=80){
		$result =imagejpeg ($this->imageData, $path, $quality);
		imagedestroy ($this->imageData);
		return $result;
	}

	public function savePNG ($path){
		$result =imagepng ($this->imageData, $path);
		imagedestroy ($this->imageData);
		return $result;
	}

	public function cropToSize ($width, $height){
		$imgOrig	=$this->getBitmapFromFile();
		$size		=getimagesize($this->file);
		$w	=$size[0];
		$h	=$size[1];
		$x	=0;
		$y	=0;
		if ($w > $h){
			$ht	=$height;
			$wt	=$ht/$h*$w;
			$x	=round (-($height-$wt));
		} else {
			$wt =$width;
			$ht =$wt/$w*$h;
			$y	=round (-($width-$ht));
		}
		$this->imageData =imagecreatetruecolor ($width, $height);
		imagecopyresampled  ($this->imageData, $imgOrig, 0, 0, $x, $y, $wt, $ht, $w, $h);
		imageinterlace ($this->imageData, 1);
	}

	public function fitToSize ($width, $height){
		$imgOrig	=$this->getBitmapFromFile();
		$size		=getimagesize($this->file);
		$w	=$size[0];
		$h	=$size[1];
		if ($w > $h){
			$ht	=$h >$height ? $height : $h;
			$wt	=$ht/$h*$w;
		} else {
			$wt	=$w >$width ? $width : $w;
			$ht =$wt/$w*$h;
		}
		$this->imageData =imagecreatetruecolor ($wt, $ht);
		imagecopyresampled  ($this->imageData, $imgOrig, 0, 0, 0, 0, $wt, $ht, $w, $h);
		imageinterlace ($this->imageData, 1);
	}
	
	public function rotate ($angle){
		if (empty ($this->imageData))
			$imgOrig	=$this->getBitmapFromFile();
		else 
			$imgOrig	=$this->imageData;
			
		$this->imageData =imagerotate ($imgOrig, $angle, 0);
		imageinterlace ($this->imageData, 1);
	}
	
	private function getBitmapFromFile () {
		$result =null;
		switch ($this->fileType){
			case self::TYPE_JPG:
				$result =imagecreatefromjpeg ($this->file);
				break;
			case self::TYPE_PNG:
				$result =imagecreatefrompng ($this->file);
				break;
		}
		return $result;
	}

}
?>