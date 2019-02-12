<?php
/*
Адаптер работы с графикой через GD
*/
namespace Mf\Imglib\Filter\Adapter;

use Exception;

class Gd extends ImgAbstract
{

    protected static $support=[
        "webp"=>"WebP Support"
    ];
/**
* ресайз изображений
*
* @param $value массив путей к файлу+имя имена
* @return возвращает массив без изменений, но файлы уже преобразованы
*/
	public function resize($value)
	{
        foreach ($value as $valueItem){
            $this->resizeItem($valueItem);
        }
	return $value;
	}

/**
* внутренняя для обработки одного элемента
* $value - строка полного имени файла
*/    
	protected function resizeItem($value)
	{
		$content=$this->readImg($value);
		
		$sourceImage = imagecreatefromstring($content);
		if (!is_resource($sourceImage)) {throw new Exception("Ошибка чтения файла $value");}
		
		$sourceWidth = imagesx($sourceImage);
		$sourceHeight = imagesy($sourceImage);

		if ($sourceWidth <= $this->_options['width'] && $sourceHeight <= $this->_options['height']) {
            imagedestroy($sourceImage);
            return $value;
		}
		
		list( , , $imageType) = getimagesizefromstring($content);

		switch ($this->_options['method']) {
            case IMG_METHOD_CROP://просто вырезать кусок
				list($X, $Y, $W, $H, $width, $height) = $this->__calculateCropCoord($sourceWidth, $sourceHeight);
				break;
			case IMG_METHOD_SCALE_FIT_W:
			case IMG_METHOD_SCALE_FIT_H:
				list($X, $Y, $W, $H, $width, $height) = $this->__calculateScaleMaxCoord($sourceWidth, $sourceHeight);
				break;
			case IMG_METHOD_SCALE_WH_CROP:
				list($X, $Y, $W, $H, $width, $height) = $this->__calculateScaleMinCoord($sourceWidth, $sourceHeight);				
				break;
			default:
				throw new Exception('Неизвестный метод обработки изображения');
		}
		
		// Create the target image
		if (function_exists('imagecreatetruecolor')) {
			$targetImage = ImageCreateTrueColor($width, $height);
		} else {
			$targetImage = ImageCreate($width, $height);
		}
		if (!is_resource($targetImage)) {
			throw new Exception('Невозможно создать ресурс GD');
		}
		
		if ($this->_options['method'] == IMG_METHOD_CROP) {
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
				ImageJpeg($targetImage, null, 100);
				break;
			case IMAGETYPE_PNG:
				ImagePng($targetImage, null, 0);
				break;
			case IMAGETYPE_WEBP:
				imagewebp($targetImage, null, 0);
				break;

			default:
				ob_end_clean();
				throw new Exception("Неизвестный тип изображения: ".$imageType);	
		}
		ImageDestroy($targetImage);
		$finalImage = ob_get_clean();
		
		$this->writeImg($value, $finalImage);
		
		//ЕСЛИ точно вырезаем, тогда рекурсивно обратиться с методом для вырезки
		if (IMG_METHOD_SCALE_WH_CROP==$this->_options['method']) {
            $this->_options['method']=IMG_METHOD_CROP;
            return $this->resizeItem($value);
        }
	return $value;
	}



/**
* генерирует альтернативные изображения из исходного
* на входе строка  к исходному файлу
* на выходе массив 
*/
    public function alternative($value)
    {
        $support=gd_info();
        $path_parts = pathinfo($value);        
        foreach ($this->_options["formats"] as $format){
            $content=$this->readImg($value);
            $sourceImage = imagecreatefromstring($content);
            if (!is_resource($sourceImage)) {throw new Exception("Ошибка чтения файла $value");}
            ob_start();
            $value=[];
            switch ($format){
                case "webp":
                    if (!$support[static::$support[$format]]) {
                        throw new Exception("библиотека GD не поддерживает работу с форматом {$format}");
                    }
                    imagewebp($sourceImage,null,95);
                    $finalImage = ob_get_clean();
                    $value["webp"]=$path_parts["dirname"]."/".$path_parts["filename"].".webp";
                    $this->writeImg($value["webp"], $finalImage);
                    break;
                default:
                    ob_end_clean();
                    throw new Exception("Формат {$format} не поддерживается адаптером ".__CLASS__);	
            }
            ImageDestroy($sourceImage);
        }
        return $value;
        
    }
}