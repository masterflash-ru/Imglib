<?php
/**
Ресайз изображений разными графическими библиотеками
 */
namespace Mf\Stdlib\Filter;

use Zend\Filter\FilterInterface;
use Exception;
use Mf\Storage\Filter\Service\ImgAbstract;

class ImgResize  implements FilterInterface
{
        protected static $classMap = [
        'gd'                => 'Mf\Storage\Filter\Service\Gd',
        'imagick'           => 'Mf\Storage\Filter\Service\Imagick',
        'consoleimagick'    => 'Mf\Storage\Filter\Service\Consoleimagick',
        ];

/**
*констатны параметров обработки указаны в ImgAbstract
ниже те же самые, для удобства задания параметров
*/
	const METHOD_SCALE_WH_CROP = 1;
	const METHOD_SCALE_FIT_W=2;		//пропорционально к указаной ширине
	const METHOD_SCALE_FIT_H=3;		//пропорционально к указаной высоте
	const METHOD_CROP = 4;				//просто вырезать кусок
	
	const ALIGN_CENTER = 0;
	const ALIGN_LEFT = -1;
	const ALIGN_RIGHT = +1;
	const ALIGN_TOP = -1;
	const ALIGN_BOTTOM = +1; 
		
	protected $_adapter=NULL;			//объект адаптера который обрабатывает графику
//опции по умолчанию

	protected $_options = array(
		'width' => 700,											 // новая ширина
		'height' => 100, 										// новая высота
		'method' => ImgAbstract::METHOD_SCALE_FIT_W, // метод ресайза, см константы
		'percent' => 0, 											// значение в процентах
		'halign' => ImgAbstract::ALIGN_CENTER, 				// горизонтальное выравнивание при вырезании
		'valign' => ImgAbstract::ALIGN_CENTER, 				// вертикальное выравнивание при вырезании
		'adapter'=>'Gd',										//адаптер обработки картинок
		'imagemagick_console_path' => "",			//путь к консольным программам ImageMagick
	);

	
/**
* Constructor
*
* @param array $options Filter options
*/
	public function __construct($options = array())
	 {
		$this->setOptions($options);
	}
	
	
	/**
* Resize the file $value with the defined settings
*
* @param string $value Full path of file to change
* @return string The filename which has been set, or false when there were errors
*/
	public function filter($value)
	{
        $this->_adapter->resize($value);
		return $value;
	}
	
	/**
* @return object
*/
	public function setOptions(array $options=null)
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
