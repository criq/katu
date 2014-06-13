<?php

namespace Katu\Types;

class TColorRgb {

	public $r;
	public $g;
	public $b;

	public function __construct($r, $g, $b) {
		$this->r = $r;
		$this->g = $g;
		$this->b = $b;
	}

	static function getFromImageColor($color) {
		$r = ($color >> 16) & 0xFF;
		$g = ($color >> 8) & 0xFF;
		$b = $color & 0xFF;

		return new self($r, $g, $b);
	}

	public function getAverage() {
		return round(($this->r + $this->g + $this->b) / 3);
	}

}
