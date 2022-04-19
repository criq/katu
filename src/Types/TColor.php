<?php

namespace Katu\Types;

class TColor
{
	protected $color;

	public function __construct($color)
	{
		if ($color instanceof \MischiefCollective\ColorJizz\ColorJizz) {
			$this->color = $color;
		} elseif (preg_match("/^#?(?<r>[0-9a-f]{2})(?<g>[0-9a-f]{2})(?<b>[0-9a-f]{2})$/i", $color, $match)) {
			$this->color = new \MischiefCollective\ColorJizz\Formats\RGB(hexdec($match["r"]), hexdec($match["g"]), hexdec($match["b"]));
		} else {
			throw new \Katu\Exceptions\InputErrorException("Invalid color input.");
		}
	}

	public function __call($name, $arguments)
	{
		$result = call_user_func_array([$this->color, $name], $arguments);
		if ($result instanceof \MischiefCollective\ColorJizz\ColorJizz) {
			return new static($result);
		}

		return $result;
	}

	public function __toString()
	{
		return (string)$this->color;
	}

	public function getColor()
	{
		return $this->color;
	}
}
