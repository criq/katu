<?php

namespace Katu\Tools\Strings\Sortables;

use Katu\Tools\Strings\Sortable;

class IntSortable extends Sortable
{
	public function __construct(int $source, ?string $title = null)
	{
		$this->setSource($source);
		$this->setTitle($title);
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
		return 20;
	}

	public function getSortable(): string
	{
		if (is_infinite($this->getSource())) {
			return str_repeat(9, $this->getPrecision());
		}

		return str_pad($this->getSource(), $this->getPrecision(), 0, \STR_PAD_LEFT);
	}
}
