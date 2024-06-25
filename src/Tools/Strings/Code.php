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
		return $this->getConstantFormat();
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

		$code = preg_replace("/\./", "__", $this->code);
		$code = trim(mb_strtoupper(preg_replace_callback("/[A-Z]/", function (array $match) {
			return "_{$match[0]}";
		}, $code)), "_");

		return $code;
	}

	public function getIsCamelCaseFormat(): bool
	{
		return !$this->getIsConstantFormat();
	}

	public function getCamelCaseFormat(): string
	{
		if ($this->getIsCamelCaseFormat()) {
			return $this->code;
		}

		$code = preg_replace_callback("/_([a-z0-9])/", function (array $match) {
			return mb_strtoupper($match[1]);
		}, mb_strtolower($this->code));
		$code = preg_replace_callback("/_([A-Z0-9])/", function (array $match) {
			return "." . mb_strtolower($match[1]);
		}, $code);

		return $code;
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

	public function getIsEqualTo($code): bool
	{
		return $this->getCamelCaseFormat() === (new Code($code))->getCamelCaseFormat();
	}
}
