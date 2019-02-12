<?php
/**
Ресайз изображений разными графическими библиотеками
 */
namespace Mf\Imglib\Filter;

use Zend\Filter\FilterInterface;
use Exception;

class ImgOptimize  implements FilterInterface
{
//опции по умолчанию
	protected $_options = array(
		'imagemagick_console_path' => "",	//путь к консольным программам ImageMagick
		'jpegoptim'=>85,					//для JPG
		'optipng'=>3	,						//для PNG
	);

	
public function __construct($options = array())
	 {
		$this->setOptions($options);
	}
	
	
/**
собственно сам фильтр
*/
public function filter($value)
{
    if (!is_array($value)){
        throw new Exception("Для фильтра ImgOptimize на входе должен быть массив с ключем 'default' в котором полный путь к обрабатываемому файлу");
    }
    if (isset($value["default"])){
        $ext=array_reverse(explode(".",$value["default"]));
        $ext=strtolower($ext[0]);       //получить тип файла
        if ($ext=="png") {
            shell_exec ($this->_options['imagemagick_console_path']."optipng -o{$this->_options['optipng']} ".$value["default"]);
        }
         if ($ext=="jpg" || $ext=="jpeg"){
               shell_exec ($this->_options['imagemagick_console_path']."jpegoptim ".$value["default"]." -m{$this->_options['jpegoptim']} --strip-all");
         }
    }
	return $value;
}
	
	public function setOptions(array $options=null)
	{
		if (is_array($options)) {
			foreach ($options as $k => $v) {
				if (array_key_exists($k, $this->_options)) {
					$this->_options[$k] = $v;
				}
			}
		}
	}
}
