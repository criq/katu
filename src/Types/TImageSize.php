<?php

namespace Katu\Types;

class TImageSize
{
	public $x;
	public $y;

	public function __construct($x, $y)
	{
		$this->x = $x;
		$this->y = $y;
	}

	public function getSurfaceSize(): int
	{
		return $this->x * $this->y;
	}
}
