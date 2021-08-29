<?php

namespace Katu\Types;

class TColor {

	protected $color;

	public function __construct($color) {
		if ($color instanceof \MischiefCollective\ColorJizz\ColorJizz) {
			$this->color = $color;
		} elseif (preg_match('/^#?(?<r>[0-9a-f]{2})(?<g>[0-9a-f]{2})(?<b>[0-9a-f]{2})$/i', $color, $match)) {
			$this->color = new \MischiefCollective\ColorJizz\Formats\RGB(hexdec($match['r']), hexdec($match['g']), hexdec($match['b']));
		} else {
			throw new \Katu\Exceptions\InputErrorException("Invalid color input.");
		}
	}

	public function __get($name) {
		$result = $this->color->$name;

		return $result;
	}

	public function __call($name, $arguments) {
		$result = call_user_func_array([$this->color, $name], $arguments);
		if ($result instanceof \MischiefCollective\ColorJizz\ColorJizz) {
			return new static($result);
		}

		return $result;
	}

	public function __toString() {
		return (string)$this->color;
	}

	public function getColor() {
		return $this->color;
	}

	/*
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
	*/

}
