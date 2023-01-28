<?php

namespace Katu\Tools\Strings;

class CodeCollection extends \ArrayObject
{
	public static function createFromArray(array $codes): CodeCollection
	{
		return new static(array_map(function (string $string) {
			return new Code($string);
		}, array_values(array_filter(array_map("trim", $codes)))));
	}

	public static function createFromRequestParam(?string $param): CodeCollection
	{
		return static::createFromArray(array_values(array_unique(array_filter(preg_split("/[^A-Za-z0-9_]/", $param)))));
	}

	public function getCamelCaseArray(): array
	{
		return array_map(function (Code $code) {
			return $code->getCamelCaseFormat();
		}, $this->getArrayCopy());
	}

	public function getConstantArray(): array
	{
		return array_map(function (Code $code) {
			return $code->getConstantFormat();
		}, $this->getArrayCopy());
	}
}
