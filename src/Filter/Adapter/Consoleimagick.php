<?php
/*
Адаптер работы с графикой через расширение Imagick через консоль
*/
namespace Mf\Imglib\Filter\Adapter;

use Exception;


class Consoleimagick extends ImgAbstract
{

    
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
        $imgsize=explode("x",shell_exec($this->_options['imagemagick_console_path']."identify -format \"%[fx:w]x%[fx:h]\"  '$value'"));
		$sourceWidth =(int)$imgsize[0];
		$sourceHeight = (int)$imgsize[1];

        if ($sourceWidth <= $this->_options['width'] && $sourceHeight <= $this->_options['height']) {
            return $value;
        }

		switch ($this->_options['method']) {
            case IMG_METHOD_CROP:
				list($X, $Y, $W, $H, $width, $height) = $this->__calculateCropCoord($sourceWidth, $sourceHeight);
				break;
			case IMG_METHOD_SCALE_FIT_W:
			case IMG_METHOD_SCALE_FIT_H:
				list($X, $Y, $W, $H, $width, $height) = $this->__calculateScaleMaxCoord($sourceWidth, $sourceHeight);
				break;
			case IMG_METHOD_SCALE_WH_CROP:
				list($X, $Y, $W, $H, $width, $height) = $this->__calculateScaleMinCoord($sourceWidth, $sourceHeight);
				break;
			default:throw new Exception("Не известный метод обработки картинок");	
		}
		
		if ($this->_options['method'] == IMG_METHOD_CROP) {
            shell_exec ($this->_options['imagemagick_console_path']."mogrify  -crop ".$width."x".$height."+$X+$Y '$value'");
        } else {
            shell_exec ($this->_options['imagemagick_console_path']."mogrify  -resize ".$width."X".$height."! '$value'");
        }
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
        $valueIn=$value["default"];
        $quality=$this->_options["quality"];

        $path_parts = pathinfo($valueIn);        
        foreach ($this->_options["formats"] as $format){
            switch ($format){
                case "webp":
                case "jpf":
                    $value[$format]=$path_parts["dirname"]."/".$path_parts["filename"].".".$format;
                    shell_exec ($this->_options['imagemagick_console_path']."convert  '".$valueIn."' -quality {$quality} "."'" .$value[$format] ."'");
                    /*проверим, появился ли файл, если нет, ошибка*/
                    if (!is_readable($value[$format])){
                        throw new Exception("Не удалось преобразрвание Формата {$format} в адаптере ".__CLASS__);
                    }
                    break;
                default:
                    throw new Exception("Формат {$format} не поддерживается адаптером ".__CLASS__);	
            }
        }
        return $value;
        
    }


/**
*наложение водного знака, пока как есть
*/
public function watermark($value)
{
	if ($this->_options['waterimage']) {
        $w=getcwd().DIRECTORY_SEPARATOR.$this->_options['waterimage'];
        shell_exec ($this->_options['imagemagick_console_path']."composite -dissolve 100 -tile $w '$value' '$value'");
    }
}
	
}
