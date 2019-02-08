<?php
/*
помощник view для вывода из хранилища фото имени файла фото, готового для вставки в HTML
*/

namespace Mf\Stdlib\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * помощник - генератор меню админки
 */
class ImageStorage extends AbstractHelper 
{
	protected $ImagesLib;


public function __invoke($razdel,$razdel_id,$item_name,$default_image=NULL)
{
	$image=$this->ImagesLib->loadImage($razdel,$razdel_id,$item_name);
	if (empty($image)) {return $default_image;}
    $view=$this->getView();
	return $view->basePath($image);
}



public function __construct ($ImagesLib)
{
	$this->ImagesLib=$ImagesLib;
}

}
