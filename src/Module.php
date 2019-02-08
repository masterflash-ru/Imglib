<?php
/**
Библиотека работы с изображениями
 */

namespace Mf\Imglib;

class Module
{

public function init( $manager)
{
    /*методы обработки изображений*/
    define("IMG_METHOD_SCALE_WH_CROP",1);  //точное вырезание
	define("IMG_METHOD_SCALE_FIT_W",2);    //точно по горизонатали, вертикаль пропорционально
	define("IMG_METHOD_SCALE_FIT_H",3);    //точно к вертикали, горизонталь пропорционально
	define("IMG_METHOD_CROP", 4);          //просто вырезать из исходного часть
	
	define("IMG_ALIGN_CENTER", 0);         //выравнивать по центру
	define("IMG_ALIGN_LEFT", -1);          //выравнивать по левой части
	define("IMG_ALIGN_RIGHT", +1);         //выравнивать по правой
	define("IMG_ALIGN_TOP",-1);            //выравнивать по верху
	define("IMG_ALIGN_BOTTOM", +1);        //выравнивать по низу

}

}
