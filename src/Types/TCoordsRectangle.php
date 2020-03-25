<?php

namespace Katu\Types;

class TCoordsRectangle
{
	public $xa;
	public $ya;
	public $xb;
	public $yb;

	public function __construct($xa, $ya, $xb, $yb)
	{
		$this->xa = $xa;
		$this->ya = $ya;
		$this->xb = $xb;
		$this->yb = $yb;
	}
}
