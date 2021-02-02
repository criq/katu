<?php

namespace Katu\Files;

class Size
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

	public function getType()
	{
		if ($this->size < 1024) {
			return new \Katu\Types\TFileSize($this->size, 'B');
		} elseif ($this->size < 1024 * 1024) {
			return new \Katu\Types\TFileSize($this->inKb(), 'kB');
		} elseif ($this->size < 1024 * 1024 * 1024) {
			return new \Katu\Types\TFileSize($this->inMB(), 'MB');
		} else {
			return new \Katu\Types\TFileSize($this->inGB(), 'GB');
		}
	}

	public static function createFromINI($string)
	{
		if (preg_match('/([0-9]+)M/', $string, $match)) {
			return new static($match[1] * 1024 * 1024);
		}

		return new static($string);
	}
}
