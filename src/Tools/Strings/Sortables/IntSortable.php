<?php

namespace Katu\Tools\Strings\Sortables;

use Katu\Tools\Strings\Sortable;

class IntSortable extends Sortable
{
	public function __construct(int $source)
	{
		$this->setSource($source);
	}

	public function setSource(int $source)
	{
		$this->source = $source;

		return $this;
	}

	public function getSource(): int
	{
		return $this->source;
	}

	public function getPrecision(): int
	{
		return 10;
	}

	public function getSortable(): string
	{
		return str_pad($this->getSource(), $this->getPrecision(), 0, \STR_PAD_LEFT);
	}
}
