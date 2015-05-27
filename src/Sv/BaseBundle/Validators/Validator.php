<?php

namespace Sv\BaseBundle\Validators;

abstract class Validator
{
	private static $instances;

	abstract protected function isValueValid($value);

	public static function validate($value)
	{
		if (!isset(self::$instances[get_called_class()])) {
			self::$instances[get_called_class()] = new static();
		}

		return self::$instances[get_called_class()]->isValueValid($value);
	}

} 