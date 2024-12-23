<?php

namespace Katu\Tools\Strings\Sortables;

use Katu\Tools\Strings\Sortable;

class FloatSortable extends Sortable
{
	public function __construct(float $source)
	{
		$this->setSource($source);
	}

	public function setSource(float $source)
	{
		$this->source = $source;

		return $this;
	}

	public function getSource(): float
	{
		return $this->source;
	}

	public function getPrecision(): int
	{
		return 10;
	}

	public function getSortable(): string
	{
		list($a, $b) = array_pad(explode(".", $this->getSource()), 2, null);

		return implode(".", [
			str_pad($a, $this->getPrecision(), 0, \STR_PAD_LEFT),
			str_pad($b, $this->getPrecision(), 0, \STR_PAD_RIGHT),
		]);
	}
}
