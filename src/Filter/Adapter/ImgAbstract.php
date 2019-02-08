<?php
/*
Абстрактный адаптер
*/

namespace Mf\Imglib\Filter\Adapter;
use Exception;

abstract class ImgAbstract
{

	protected $_options=[];

	public function __construct($options = [])
	 {		
		$this->setOptions($options);
	}
	

	public function setOptions($options)
	{
		$this->_options = $options;
	}
	
	/**
    * чтение изображения в память и возвращает BLOB строку
    * $value - имя файла
    */
protected function readImg($value)
{
    if (!file_exists($value)) {throw new Exception("Исходный файл ($value) для работы не найден");}
    if (file_exists($value) and !is_writable($value)) {throw new Exception("В файл $value нельзя записать");}
    $content = file_get_contents($value);
    if (!$content) {throw new Exception("Ошибка чтения файла $value");}
    return $content;
}

    
/**
* Запись BLOB строки в файл
* $value - имя файла
*/
protected function writeImg($value, $finalImage)
{
    $result = file_put_contents($value, $finalImage);
    if (!$result) {throw new Exception("В файл $value нельзя записать");}		
}

/**
* Calculate coordinates for crop method
*
* @param int $sourceWidth Width of source image
* @param int $sourceHeight Height of source image
* @return array
*/
	protected function __calculateCropCoord($sourceWidth, $sourceHeight)
	{
		if ( $this->_options['percent'] ) {
			$W = floor($this->_options['percent'] * $sourceWidth);
			$H = floor($this->_options['percent'] * $sourceHeight);
		} else {
			$W = $this->_options['width'];
			$H = $this->_options['height'];
		}
		
		$X = $this->__coord($this->_options['halign'], $sourceWidth, $W);// горизонтальное выравнивание
		$Y = $this->__coord($this->_options['valign'], $sourceHeight, $H);// вертикальное выравнивание 
		
		return array($X, $Y, $W, $H, $W, $H);
	}

	/**
* Calculate coordinates for Max scale method
*
* @param int $sourceWidth Width of source image
* @param int $sourceHeight Height of source image
* @return array
*/
	protected function __calculateScaleMaxCoord($sourceWidth, $sourceHeight)
	{
		if ( $this->_options['percent'] ) {
			$width = floor($this->_options['percent'] * $sourceWidth);
			$height = floor($this->_options['percent'] * $sourceHeight);
		} else {
            //новые размеры
            $width = $this->_options['width'];
            $height = $this->_options['height'];
            //приводит к горизонтальному размеру
            if ($this->_options['method']==IMG_METHOD_SCALE_FIT_W){
                $height=floor($sourceHeight * $width/$sourceWidth);//преобразовать пропорционально
            }
            //приводит к вертикальному размеру
            if ($this->_options['method']==IMG_METHOD_SCALE_FIT_H){
                $width=floor($sourceWidth * $height /$sourceHeight);//преобразовать пропорционально
            }
        }
        return array(0, 0, $sourceWidth, $sourceHeight, $width, $height);
	}
	
	/**
* Calculate coordinates for Min scale method
*
* @param int $sourceWidth Width of source image
* @param int $sourceHeight Height of source image
* @return array
*/
	protected function __calculateScaleMinCoord($sourceWidth, $sourceHeight)
	{
		$X = $Y = 0;//начальные координаты
		
		$W = $sourceWidth;	//изначальные размеры
		$H = $sourceHeight;
		
		if ( $this->_options['percent'] ){
            $width = floor($this->_options['percent'] * $W);
            $height = floor($this->_options['percent'] * $H);
        } else {
            $width = $this->_options['width'];//новые размеры
            $height = $this->_options['height'];
            $ratio=$W/$H;		//соотношение сторон исходный
            //вычислим  высоту пропорционально, ширину берем новую
            $hh=floor($width / $ratio);
			$ww=$width;
			if ($hh<$height){
                //новая высота меньше новой, поэтому расчитываем новую ширину, т.е. наоборот!
                $hh=$height;
                $ww=floor($height*$ratio);
            }
		}
		return array($X, $Y, $W, $H, $ww, $hh);
	}
	
	/**
* вычистление координаты левого верхнего края в зависимости от типа выравнивания
*
* @param int $align Align type
* @param int $src Source size
* @param int $dst Destination size
* @return int
*/
	protected function __coord($align, $src, $dst)
	{
		if ( $align < IMG_ALIGN_CENTER ) {
			$result = 0;
		} elseif ( $align > IMG_ALIGN_CENTER ) {
			$result = $src - $dst;
		} else {
			$result = ($src - $dst) >> 1;
		}
		return $result;
	}
	
	
}
