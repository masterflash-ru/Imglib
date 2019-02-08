<?php
/**
Ресайз изображений разными графическими библиотеками
 */
namespace Mf\Imglib\Filter;

use Zend\Filter\FilterInterface;
use Exception;
use Mf\Storage\Filter\Service\ImgAbstract;

class ImgResize  implements FilterInterface
{
    protected static $classMap = [
        'gd'                => 'Mf\Imglib\Filter\Adapter\Gd',
        'imagick'           => 'Mf\Imglib\Filter\Adapter\Imagick',
        'consoleimagick'    => 'Mf\Imglib\Filter\Adapter\Consoleimagick',
        ];

/**
*констатны параметров для совместимости
*/
	const METHOD_SCALE_WH_CROP = IMG_METHOD_SCALE_WH_CROP;
	const METHOD_SCALE_FIT_W=IMG_METHOD_SCALE_FIT_W;
	const METHOD_SCALE_FIT_H=IMG_METHOD_SCALE_FIT_H;
	const METHOD_CROP = IMG_METHOD_CROP;
	
	const ALIGN_CENTER = IMG_ALIGN_CENTER;
	const ALIGN_LEFT = IMG_ALIGN_LEFT;
	const ALIGN_RIGHT = IMG_ALIGN_RIGHT;
	const ALIGN_TOP = IMG_ALIGN_TOP;
	const ALIGN_BOTTOM = IMG_ALIGN_BOTTOM; 
		
	protected $_adapter=null;


	protected $_options = array(
		'width' => 700,
		'height' => 100,
		'method' => IMG_METHOD_SCALE_FIT_W,
		'percent' => 0,
		'halign' => IMG_ALIGN_CENTER,
		'valign' => IMG_ALIGN_CENTER,
		'adapter'=>'Gd',
		'imagemagick_console_path' => "",
	);

	
	public function __construct($options = array())
	 {
		$this->setOptions($options);
	}
	
	public function filter($value)
	{
        $this->_adapter->resize($value);
		return $value;
	}
	
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

        
		$this->_adapter=new $adapter($this->_options);
		return $this;
	}
}
