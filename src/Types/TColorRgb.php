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

	public function __toString() {
		return implode(', ', [
			$this->r,
			$this->g,
			$this->b,
		]);
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

	static function getFromHex($hex) {
		$hex = ltrim($hex, '#');

		if (strlen($hex) == 3) {
			$r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
			$g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
			$b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
		} else {
			$r = hexdec(substr($hex, 0, 2));
			$g = hexdec(substr($hex, 2, 2));
			$b = hexdec(substr($hex, 4, 2));
		}

		return new static($r, $g, $b);
	}

	public function getHex($prependHash = true) {
		return ($prependHash ? '#' : null) . implode([
			str_pad(dechex($this->r), 2, '0', STR_PAD_LEFT),
			str_pad(dechex($this->g), 2, '0', STR_PAD_LEFT),
			str_pad(dechex($this->b), 2, '0', STR_PAD_LEFT),
		]);
	}

}
