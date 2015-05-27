<?php

namespace Sv\BaseBundle\Utils;

class Utils
{
	static private $cur;
	static private $lat;

	static function getPlural($number, $one = 'one', $four = 'four', $many = 'many')
	{
		if ($number == 0) {
			return $many;
		}
		$number = trim($number);
		$lastChar = $number[strlen($number) - 1];
		$lastTwoChars = (strlen($number) > 1 ? $number[strlen($number) - 2] . $number[strlen($number) - 1] : 0);

		if ($lastTwoChars > 10 && $lastTwoChars < 20) {
			return $many;
		} else if ($lastChar == 1) {
			return $one;
		} else if ($lastChar == 0 || $lastChar > 4) {
			return $many;
		} else {
			return $four;
		}
	}

	static function transliterate($input)
	{
		if (!static::$cur) {
			static::$cur = array('а','б','в','г','д','e','ж','з','и','й','к','л','м','н','о','п','р','с','т','у',
				'ф','х','ц','ч','ш','щ','ъ','ь', 'ю','я','А','Б','В','Г','Д','Е','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У',
				'Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ь', 'Ю','Я', 'ы', 'Ы');
		}
		if (!static::$lat) {
			static::$lat = array( 'a','b','v','g','d','e','zh','z','i','y','k','l','m','n','o','p','r','s','t','u',
				'f' ,'h' ,'ts' ,'ch','sh' ,'sht' ,'a' ,'y' ,'yu' ,'ya','A','B','V','G','D','E','Zh',
				'Z','I','Y','K','L','M','N','O','P','R','S','T','U',
				'F' ,'H' ,'Ts' ,'Ch','Sh' ,'Sht' ,'A' ,'Y' ,'Yu' ,'Ya', 'i', 'I');
		}

		return str_replace(static::$cur, static::$lat, $input);
	}

}
