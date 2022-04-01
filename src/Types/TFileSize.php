<?php

namespace Katu\Types;

class TFileSize
{
	protected $amount;
	protected $unit;

	public function __construct(float $amount, string $unit = "B")
	{
		$this->amount = $amount;
		$this->unit = $unit;
	}

	public function __toString(): string
	{
		return (string)$this->getInB()->getAmount();
	}

	public static function createFromShorthand(string $string): TFileSize
	{
		if (preg_match("/([0-9]+)K/", $string, $match)) {
			return new static($match[1], "kB");
		}

		if (preg_match("/([0-9]+)M/", $string, $match)) {
			return new static($match[1], "MB");
		}

		if (preg_match("/([0-9]+)G/", $string, $match)) {
			return new static($match[1], "GB");
		}

		return new static((int)$string, "B");
	}

	public function getAmount(): float
	{
		return $this->amount;
	}

	public function getUnit(): string
	{
		return $this->unit;
	}

	public function getInB(): TFileSize
	{
		switch ($this->getUnit()) {
			case "B":
				return new static($this->getAmount(), "B");
				break;
			case "kB":
				return new static($this->getAmount() * 1024, "B");
				break;
			case "MB":
				return new static($this->getAmount() * 1024 * 1024, "B");
				break;
			case "GB":
				return new static($this->getAmount() * 1024 * 1024 * 1024, "B");
				break;
		}

		return new static($this->getAmount(), "B");
	}

	public function getInKB(): TFileSize
	{
		return new static($this->getInB()->getAmount() / 1024, "kB");
	}

	public function getInMB(): TFileSize
	{
		return new static($this->getInB()->getAmount() / 1024 / 1024, "MB");
	}

	public function getInGB(): TFileSize
	{
		return new static($this->getInB()->getAmount() / 1024 / 1024 / 1024, "GB");
	}

	public function getReadable(): TFileSize
	{
		if ($this->getInGB()->getAmount() >= 1) {
			return $this->getInGB();
		} elseif ($this->getInMB()->getAmount() >= 1) {
			return $this->getInMB();
		} elseif ($this->getInKB()->getAmount() >= 1) {
			return $this->getInKB();
		}

		return $this->getInB();
	}
}
