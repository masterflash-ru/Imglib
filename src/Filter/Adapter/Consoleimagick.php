<?php
/*
Адаптер работы с графикой через расширение Imagick через консоль
*/
namespace Mf\Imglib\Filter\Adapter;

use Exception;


class Consoleimagick extends ImgAbstract
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
		
		$imgsize=explode("x",shell_exec($this->_options['imagemagick_console_path']."identify -format \"%[fx:w]x%[fx:h]\"  '$value'"));

		$sourceWidth =(int)$imgsize[0];
		$sourceHeight = (int)$imgsize[1];

		if ($sourceWidth <= $this->_options['width'] && $sourceHeight <= $this->_options['height']) 
		{
			return $value;
		}
		

		
		switch ($this->_options['method'])
		 {
			case ImgAbstract::METHOD_CROP:
				list($X, $Y, $W, $H, $width, $height) = $this->__calculateCropCoord($sourceWidth, $sourceHeight);
				break;
			case	ImgAbstract::METHOD_SCALE_FIT_W:
			case	ImgAbstract::METHOD_SCALE_FIT_H:
				list($X, $Y, $W, $H, $width, $height) = $this->__calculateScaleMaxCoord($sourceWidth, $sourceHeight);
				break;
			case ImgAbstract::METHOD_SCALE_WH_CROP:
				list($X, $Y, $W, $H, $width, $height) = $this->__calculateScaleMinCoord($sourceWidth, $sourceHeight);
				break;
			default:throw new Exception("Не известный метод обработки картинок");	
		} 
		
		
		if ($this->_options['method'] == ImgAbstract::METHOD_CROP) 
			{
				shell_exec ($this->_options['imagemagick_console_path']."mogrify  -crop ".$width."x".$height."+$X+$Y '$value'");
			} 
		else 
			{
					shell_exec ($this->_options['imagemagick_console_path']."mogrify  -resize ".$width."X".$height."! '$value'"); 
			}
		
		
		
		if (ImgAbstract::METHOD_SCALE_WH_CROP==$this->_options['method']) 
			{
				$this->_options['method']=ImgAbstract::METHOD_CROP; 
				return $this->resize($value);
			}
		return $value;
	}
	
	
public function watermark($value)
{
	if ($this->_options['waterimage'])
	{
		$w=getcwd().DIRECTORY_SEPARATOR.$this->_options['waterimage'];
     	shell_exec ($this->_options['imagemagick_console_path']."composite -dissolve 100 -tile $w '$value' '$value'");
	}

}
	
}
?>