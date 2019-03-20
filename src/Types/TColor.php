<?php

namespace Katu\Types;

class TColor {

	private $color;

	public function __construct($color) {
		if ($color instanceof \SSNepenthe\ColorUtils\Colors\Color) {
			$this->color = $color;
		} elseif (class_exists('\\SSNepenthe\\ColorUtils\\Colors\\Color')) {
			$this->color = \SSNepenthe\ColorUtils\color($color);
		} else {
			$this->color = $color;
		}
	}

	public function __toString() {
		return $this->getHex();
	}

	static function createFromInt($int) {
		$red   = ($int >> 16) & 0xFF;
		$green = ($int >> 8) & 0xFF;
		$blue  = $int & 0xFF;

		return new static([
			'red' => $red,
			'green' => $green,
			'blue' => $blue,
		]);
	}

	public function getHex($prependHash = true) {
		return ($prependHash ? '#' : null) . implode([
			str_pad(dechex(\SSNepenthe\ColorUtils\red($this->color)), 2, '0', STR_PAD_LEFT),
			str_pad(dechex(\SSNepenthe\ColorUtils\green($this->color)), 2, '0', STR_PAD_LEFT),
			str_pad(dechex(\SSNepenthe\ColorUtils\blue($this->color)), 2, '0', STR_PAD_LEFT),
		]);
	}

	public function getRgbString() {
		return implode(', ', [
			\SSNepenthe\ColorUtils\red($this->color),
			\SSNepenthe\ColorUtils\green($this->color),
			\SSNepenthe\ColorUtils\blue($this->color),
		]);
	}

	public function setHue($value) {
		return new static(\SSNepenthe\ColorUtils\color([
			'hue' => $value,
			'saturation' => \SSNepenthe\ColorUtils\saturation($this->color),
			'lightness' => \SSNepenthe\ColorUtils\lightness($this->color),
		]));
	}

	public function setSaturation($value) {
		return new static(\SSNepenthe\ColorUtils\color([
			'hue' => \SSNepenthe\ColorUtils\hue($this->color),
			'saturation' => $value,
			'lightness' => \SSNepenthe\ColorUtils\lightness($this->color),
		]));
	}

	public function setLightness($value) {
		return new static(\SSNepenthe\ColorUtils\color([
			'hue' => \SSNepenthe\ColorUtils\hue($this->color),
			'saturation' => \SSNepenthe\ColorUtils\saturation($this->color),
			'lightness' => $value,
		]));
	}

}
