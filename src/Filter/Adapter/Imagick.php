<?php
/*
Адаптер работы с графикой через расширение Imagick
*/
namespace Mf\Imglib\Filter\Adapter;
use Exception;
use Imagick as PhpImagick;

class Imagick extends ImgAbstract
{
	
	
	/**
* Resize image
*
* @param $content Content of source imge
* @param $value Path to source file
* @return Content of resized image
*/
	public function resize($value)
	{
		$content=$this->readImg($value);
		$Imagick=new PhpImagick();
		if (!$Imagick->readImageBlob($content)) 
		{
			throw new Exception("Ошибка чтения файла $value");
		}
		
		$imgsize=$Imagick->getImagePage();
		
		$sourceWidth =$imgsize['width'];
		$sourceHeight = $imgsize['height'];

		if ($sourceWidth <= $this->_options['width'] && $sourceHeight <= $this->_options['height']) 
		{
			$Imagick->destroy ();
			return $value;
		}
		

		
		switch ($this->_options['method'])
		 {
			case IMG_METHOD_CROP:
				list($X, $Y, $W, $H, $width, $height) = $this->__calculateCropCoord($sourceWidth, $sourceHeight);
				break;
			case	IMG_METHOD_SCALE_FIT_W:
			case	IMG_METHOD_SCALE_FIT_H:
				list($X, $Y, $W, $H, $width, $height) = $this->__calculateScaleMaxCoord($sourceWidth, $sourceHeight);
				break;
			case IMG_METHOD_SCALE_WH_CROP:
				list($X, $Y, $W, $H, $width, $height) = $this->__calculateScaleMinCoord($sourceWidth, $sourceHeight);
				break;
			default:throw new Exception('Unknow resize method');	
		}
		
		
		if ($this->_options['method'] == IMG_METHOD_CROP) 
			{
				$Imagick->cropImage ($width,$height, $X, $Y);
			} 
		else 
			{
					$Imagick->resizeImage($width,$height, imagick::FILTER_LANCZOS, 0.9, true);
			}
		$final=$Imagick->getImagesBlob();
		$Imagick->destroy ();
		$this->writeImg($value, $final);
	if (IMG_METHOD_SCALE_WH_CROP==$this->_options['method']) {$this->_options['method']=IMG_METHOD_CROP; return $this->resize($value);}

		return $value;
	}
	
public function watermark($value)
{
	if ($this->_options['waterimage'])
	{
        $w=getcwd().DIRECTORY_SEPARATOR.$this->_options['waterimage'];
        $overlay = new PhpImagick($w);
        
        $image = new PhpImagick($value);
        $geo=$image->getImageGeometry(); 
        
        $geo_overlay=$overlay->getImageGeometry(); 
       
        $image->setImageColorspace($overlay->getImageColorspace() ); 
        $image->compositeImage($overlay, PhpImagick::COMPOSITE_DEFAULT, $geo['width']-$geo_overlay['width']-20, $geo['height']-$geo_overlay['height']-20);
        $image->writeImage($value); //replace original background

        $overlay->destroy();
        $image->destroy();
		
     	//shell_exec ($this->_options['imagemagick_console_path']."composite -dissolve 100 -tile $w '$value' '$value'");
	}

}

	
}
?>