<?php
/**
наложение водного знака на изображение разными графическими библиотеками
 */
namespace Mf\Imglib\Filter;

use Zend\Filter\FilterInterface;
use Exception;


class Watermark  implements FilterInterface
{
	protected $_adapter=NULL;			//объект адаптера который обрабатывает графику
    protected static $classMap = [
        'gd'                => 'Mf\Imglib\Filter\Adapter\Gd',
        'imagick'           => 'Mf\Imglib\Filter\Adapter\Imagick',
        'consoleimagick'    => 'Mf\Imglib\Filter\Adapter\Consoleimagick',
    ];


	protected $_options = array(
		'waterimage'=>"",					//файл который накладывается на картинку
		'imagemagick_console_path' => "",	//путь к консольным программам ImageMagick
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
    $this->_adapter->watermark($value);
    return $value;
}
	
public function setOptions(array $options)
	{
		if (!is_array($options)) {
			throw new Exception("Не допустимая опция, должен быть массив");	
		}
        
        $adapter = $options['adapter'];
        if (isset(static::$classMap[strtolower($adapter)])) {
            $adapter = static::$classMap[strtolower($adapter)];
        }
        if (! class_exists($adapter)) {
            throw new Exception(sprintf(
                '%s не допустимое имя класса для метода: "%s"',
                __METHOD__,
                $adapter
            ));
        }
        if (!empty($options)&& is_array($options)){
            foreach ($options as $k => $v) {
                    if (array_key_exists($k, $this->_options)) {$this->_options[$k] = $v;}
            }
        }

		$this->_adapter=new $adapter($this->_options);			//создаем адаптер
		return $this;
	}
}
	

