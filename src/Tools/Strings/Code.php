<?php

namespace Katu\Tools\Strings;

class Code
{
	protected $code;

	public function __construct($code)
	{
		$this->code = (string)$code;
	}

	public function __toString()
	{
		return $this->code;
	}

	public function getIsConstantFormat(): bool
	{
		return preg_match("/^[A-Z0-9_]+$/", $this->code);
	}

	public function getConstantFormat(): string
	{
		if ($this->getIsConstantFormat()) {
			return $this->code;
		}

		return trim(mb_strtoupper(preg_replace_callback("/[A-Z]/", function (array $match) {
			return "_{$match[0]}";
		}, $this->code)), "_");
	}

	public function getCamelCaseFormat(): string
	{
		if (!$this->getIsConstantFormat()) {
			return $this->code;
		}

		return preg_replace_callback("/_([a-z0-9])/", function (array $match) {
			return mb_strtoupper($match[1]);
		}, mb_strtolower($this->code));
	}

	public function getUpperCamelCaseFormat(): string
	{
		return ucfirst($this->getCamelCaseFormat());
	}

	public function getStylized(): string
	{
		$delimiter = "-";
		$code = $this->getConstantFormat();

		if (mb_strlen($code) > 5) {
			if (!(mb_strlen($code) % 3)) {
				return implode($delimiter, str_split($code, 3));
			} elseif (!(mb_strlen($code) % 4)) {
				return implode($delimiter, str_split($code, 4));
			} elseif (!(mb_strlen($code) % 5)) {
				return implode($delimiter, str_split($code, 5));
			} else {
				if (mb_strlen($code) % 4 == 1) {
					$chunkLength = 5;
				} elseif (mb_strlen($code) % 3 == 1) {
					$chunkLength = 4;
				} else {
					$chunkLength = 3;
				}
				return implode($delimiter, str_split($code, $chunkLength));
			}
		}

		return $code;
	}
}
