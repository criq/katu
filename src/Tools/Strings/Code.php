<?php

namespace Katu\Tools\Strings;

class Code
{
	protected $code;

	public function __construct(string $code)
	{
		$this->code = $code;
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

		return mb_strtoupper(preg_replace_callback("/[A-Z]/", function (array $match) {
			return "_{$match[0]}";
		}, $this->code));
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
}
