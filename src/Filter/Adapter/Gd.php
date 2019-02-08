<?php
/*
Адаптер работы с графикой через GD
*/
namespace Mf\Imglib\Filter\Adapter;

use Exception;

class Gd extends ImgAbstract
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
		
		$sourceImage = imagecreatefromstring($content);
		if (!is_resource($sourceImage)) {	throw new Exception("Ошибка чтения файла $value");}
		
		$sourceWidth = imagesx($sourceImage);
		$sourceHeight = imagesy($sourceImage);

		if ($sourceWidth <= $this->_options['width'] && $sourceHeight <= $this->_options['height']) 
		{
			imagedestroy($sourceImage);
			return $value;
		}
		
		list( , , $imageType) = getimagesizefromstring($content);

		switch ($this->_options['method'])
		 {
			case ImgAbstract::METHOD_CROP://просто вырезать кусок
				list($X, $Y, $W, $H, $width, $height) = $this->__calculateCropCoord($sourceWidth, $sourceHeight);
				break;
			case	ImgAbstract::METHOD_SCALE_FIT_W:
			case	ImgAbstract::METHOD_SCALE_FIT_H:
				list($X, $Y, $W, $H, $width, $height) = $this->__calculateScaleMaxCoord($sourceWidth, $sourceHeight);
				break;
			case ImgAbstract::METHOD_SCALE_WH_CROP:
				list($X, $Y, $W, $H, $width, $height) = $this->__calculateScaleMinCoord($sourceWidth, $sourceHeight);
				
				
				break;
			default:
				throw new Exception('Unknow resize method');
		}
		
		// Create the target image
		if (function_exists('imagecreatetruecolor')) {
			$targetImage = ImageCreateTrueColor($width, $height);
		} else {
			$targetImage = ImageCreate($width, $height);
		}
		if (!is_resource($targetImage)) {
			throw new Exception('Cannot initialize new GD image stream');
		}
		
		// Copy the source image to the target image
		if ($this->_options['method'] == ImgAbstract::METHOD_CROP) {
			$result = ImageCopy($targetImage, $sourceImage, 0, 0, $X, $Y, $W, $H);
		} elseif (function_exists('imagecopyresampled')) {
			$result = ImageCopyResampled($targetImage, $sourceImage, 0, 0, $X, $Y, $width, $height, $W, $H);
		} else {
			$result = ImageCopyResized($targetImage, $sourceImage, 0, 0, $X, $Y, $width, $height, $W, $H);
		}
		ImageDestroy($sourceImage);
		if (!$result) {throw new Exception("Ошибка изменения размера");	}
		
		ob_start();
		switch ($imageType)
		{
			case IMAGETYPE_GIF:
				ImageGif($targetImage);
				break;
			case IMAGETYPE_JPEG:
				ImageJpeg($targetImage, null, 100); // best quality
				break;
			case IMAGETYPE_PNG:
				ImagePng($targetImage, null, 0); // no compression
				break;
			default:
				ob_end_clean();
				throw new Exception("Не известный метод обработки картинок");	
		}
		ImageDestroy($targetImage);
		$finalImage = ob_get_clean();
		
		$this->writeImg($value, $finalImage);
		
		//ЕСЛИ точно вырезаем, тогда рекурсивно обратиться с методом для вырезки
		if (ImgAbstract::METHOD_SCALE_WH_CROP==$this->_options['method']) 
			{
				$this->_options['method']=ImgAbstract::METHOD_CROP;
				return $this->resize($value);
			}
	

	return $value;
	}
	
	
	
	
}
?>