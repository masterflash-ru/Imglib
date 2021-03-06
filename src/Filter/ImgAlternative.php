<?php
/**
Ресайз изображений разными графическими библиотеками
 */
namespace Mf\Imglib\Filter;

use Laminas\Filter\FilterInterface;
use Exception;


class ImgAlternative  implements FilterInterface
{
    protected static $classMap = [
        'gd'                => 'Mf\Imglib\Filter\Adapter\Gd',
        'imagick'           => 'Mf\Imglib\Filter\Adapter\Imagick',
        'consoleimagick'    => 'Mf\Imglib\Filter\Adapter\Consoleimagick',
        ];

		
	protected $_adapter=null;


	protected $_options = [
		'adapter'=>'Gd',
		'imagemagick_console_path' => "",
        "formats" =>[
            "webp"
        ],
        "quality"=>0
	];

	
	public function __construct(array $options = [])
    {
		$this->setOptions($options);
	}
	
	public function filter($value)
	{
        if (!is_array($value)){
            throw new Exception("Для фильтра ImgAlternative на входе должен быть массив с ключем 'default' в котором полный путь к обрабатываемому файлу");
        }
        $value=$this->_adapter->alternative($value);
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

        $adapter = $this->_options['adapter'];
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
