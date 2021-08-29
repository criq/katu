<?php

namespace Katu\Utils;

class FileSize
{
	public $size;

	public function __construct($size)
	{
		$this->size = $size;
	}

	public function __toString()
	{
		return (string)$this->size;
	}

	public function inKB()
	{
		return $this->size / 1024;
	}

	public function inMB()
	{
		return $this->inKB() / 1024;
	}

	public function inGB()
	{
		return $this->inMB() / 1024;
	}

	public function getReadable()
	{
		if ($this->size < 1024) {
			return $this->size . ' B';
		} elseif ($this->inKB() < 1024) {
			return round($this->inKB()) . ' kB';
		} elseif ($this->inMB() < 1024) {
			return round($this->inMB(), 1) . ' MB';
		} else {
			return round($this->inGB(), 2) . ' GB';
		}
	}

	public static function createFromIni($string)
	{
		if (preg_match('#([0-9]+)M#', $string, $match)) {
			return new static($match[1] * 1024 * 1024);
		}

		return new static($string);
	}
}
