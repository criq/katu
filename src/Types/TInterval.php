<?php

namespace Katu\Types;

class TInterval
{
	public $min;
	public $max;

	public function __construct(float $min, float $max)
	{
		$this->setMin($min);
		$this->setMax($max);
	}

	public function setMin(float $number): TInterval
	{
		$this->min = $number;

		return $this;
	}

	public function getMin(): float
	{
		return $this->min;
	}

	public function setMax(float $number): TInterval
	{
		$this->max = $number;

		return $this;
	}

	public function getMax(): float
	{
		return $this->max;
	}

	public function getRange(): array
	{
		return range($this->getMin(), $this->getMax());
	}

	public function getCount(): float
	{
		return count($this->getRange());
	}

	public function getIsSingle(): bool
	{
		return $this->getCount() == 1;
	}

	public function getIsDouble(): bool
	{
		return $this->getCount() == 2;
	}
}
