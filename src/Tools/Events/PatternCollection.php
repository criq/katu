<?php

namespace Katu\Tools\Events;

class PatternCollection extends \ArrayObject
{
	public static function createFromString(string $string): PatternCollection
	{
		return new static(array_map(function (string $string) {
			return new Pattern($string);
		}, array_values(array_filter(array_map("trim", preg_split("/[\s,]+/", $string))))));
	}
}
