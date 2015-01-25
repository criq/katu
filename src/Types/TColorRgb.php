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

	public function getHex($prependHash = true) {
		return ($prependHash ? '#' : null) . implode([
			str_pad(dechex($this->r), 2, '0', STR_PAD_LEFT),
			str_pad(dechex($this->g), 2, '0', STR_PAD_LEFT),
			str_pad(dechex($this->b), 2, '0', STR_PAD_LEFT),
		]);
	}

	public function getPhpColor() {
		return new \phpColors\Color($this->getHex());
	}

}
