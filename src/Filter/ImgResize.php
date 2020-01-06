<?php
/**
Ресайз изображений разными графическими библиотеками
 */
namespace Mf\Imglib\Filter;

use Laminas\Filter\FilterInterface;
use Exception;


class ImgResize  implements FilterInterface
{
    protected static $classMap = [
        'gd'                => 'Mf\Imglib\Filter\Adapter\Gd',
        'imagick'           => 'Mf\Imglib\Filter\Adapter\Imagick',
        'consoleimagick'    => 'Mf\Imglib\Filter\Adapter\Consoleimagick',
        ];

		
	protected $_adapter=null;


	protected $_options = [
		'width' => 700,
		'height' => 100,
		'method' => IMG_METHOD_SCALE_FIT_W,
		'percent' => 0,
		'halign' => IMG_ALIGN_CENTER,
		'valign' => IMG_ALIGN_CENTER,
		'adapter'=>'Gd',
		'imagemagick_console_path' => "",
	];

	
	public function __construct(array $options = [])
    {
		$this->setOptions($options);
	}
	
	public function filter($value)
	{
        if (!is_array($value)){
            throw new Exception("Для фильтра ImgResize на входе должен быть массив в которых полные пути к обрабатываемому файлу");
        }

        $this->_adapter->resize($value);
		return $value;
	}
	
	public function setOptions(array $options=[])
	{
        if (!empty($options) && is_array($options)){
            foreach ($options as $k => $v) {
                if (array_key_exists($k, $this->_options)) {
                    $this->_options[$k] = $v;
                }
            }
        }

        $adapter =  $this->_options['adapter'];
        if (isset(static::$classMap[strtolower($adapter)])) {
            $adapter = static::$classMap[strtolower($adapter)];
        }
        if (! class_exists($adapter)) {
            throw new Exception(sprintf(
                '%s не допустимое имя адаптера: "%s"',
                __METHOD__,
                $adapter
            ));
        }
        
		$this->_adapter=new $adapter($this->_options);
		return $this;
	}
}
