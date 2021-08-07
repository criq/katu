<?php

namespace Katu\Types;

class TFileSize
{
	private $amount;
	private $unit;

	public function __construct(float $amount, string $unit)
	{
		$this->amount = $amount;
		$this->unit = $unit;
	}

	public function __toString() : string
	{
		return implode(" ", [
			round($this->getAmount()),
			$this->getUnit(),
		]);
	}

	public function getAmount()
	{
		return $this->amount;
	}

	public function getUnit()
	{
		return $this->unit;
	}
}
