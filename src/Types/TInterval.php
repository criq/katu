<?php

namespace Katu\Types;

class TInterval
{
	public $min;
	public $max;

	public function __construct(int $min, int $max)
	{
		$this->setMin($min);
		$this->setMax($max);
	}

	public function setMin(int $number) : TInterval
	{
		$this->min = $number;

		return $this;
	}

	public function getMin() : int
	{
		return $this->min;
	}

	public function setMax(int $number) : TInterval
	{
		$this->max = $number;

		return $this;
	}

	public function getMax() : int
	{
		return $this->max;
	}

	public function getRange() : array
	{
		return range($this->getMin(), $this->getMax());
	}

	public function getCount() : int
	{
		return count($this->getRange());
	}

	public function getIsSingle() : bool
	{
		return $this->getCount() == 1;
	}

	public function getIsDouble() : bool
	{
		return $this->getCount() == 2;
	}
}
