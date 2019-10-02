библиотека обработки изображений для Simba

Установка
composer require masterflash-ru/imglib

Билиотека предназначена для обработки изображений:
1. ресайз разными вариантами обработки
2. генерация альтернативных изображений, например, wbmp
3. наложение водных знаков
4. оптимизация

В комплект входят адаптеры для обработки:
1. Gd - базовая библиотека использует GD из PHP
2. Consoleimagick - использует консоль для обращения к библиотеке ImageMagick
3. Imagick расширение для PHP

Пока все на стадии разработки, не все методы обработки поддерживаются адаптерами!

Фильтр ImagLib\Filter\ImgResize
```php
//применение для ресайза:

/*предопределенные константы:
IMG_METHOD_SCALE_WH_CROP //точное вырезание
IMG_METHOD_SCALE_FIT_W   //точно по горизонатали, вертикаль пропорционально
IMG_METHOD_SCALE_FIT_H   //точно к вертикали, горизонталь пропорционально
IMG_METHOD_CROP          //просто вырезать из исходного часть

IMG_ALIGN_CENTER         //выравнивать по центру
IMG_ALIGN_LEFT          //выравнивать по левой части
IMG_ALIGN_RIGHT         //выравнивать по правой
IMG_ALIGN_TOP            //выравнивать по верху
IMG_ALIGN_BOTTOM        //выравнивать по низу
*/


$options = [                                  //это опции по умолчанию
		'width' => 700,                       //новая ширина
		'height' => 100,                      //новая высота
		'method' => IMG_METHOD_SCALE_FIT_W,   //метод обработки сторон
		'percent' => 0,                       //процент измненения
		'halign' => IMG_ALIGN_CENTER,         //центрирование по горизонтали вырезаемой области
		'valign' => IMG_ALIGN_CENTER,         //аналогично по вертикали
		'adapter'=>'Gd',                      //имя адаптера обработки
		'imagemagick_console_path' => "",     //путь к утилитам ImageMagick в вашей OS, если нужно
	];

//создаем экземпляр по аналогии с ZF3
$f=new ImgResize($options);

//собственно применение фильтра:
$rez=$f->filter([/*массив файлов для обработки*/]);


//применение для оптимизации, работает только с консолью, используя  jpegoptim и optipng, если их нет ничего не произойдет

$options = array(                           //опции по умолчанию
		'imagemagick_console_path' => "",	//путь к консольным программам ImageMagick
		'jpegoptim'=>85,					//для JPG
		'optipng'=>3	,					//для PNG
	);
$f=new ImgOptimize($options);
//собственно применение фильтра:
$rez=$f->filter([/*массив файлов для обработки*/]);



```


