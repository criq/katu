<?php

namespace Katu\Tools\Strings\Sortables;

use Katu\Tools\Strings\Sortable;

class BoolSortable extends Sortable
{
	public function __construct(bool $source, ?string $title = null)
	{
		$this->setSource($source);
		$this->setTitle($title);
	}

	public function setSource(bool $source)
	{
		$this->source = $source;

		return $this;
	}

	public function getSource(): bool
	{
		return $this->source;
	}

	public function getPrecision(): int
	{
		return 1;
	}

	public function getSortable(): string
	{
		return str_pad($this->getSource() ? "0" : "1", $this->getPrecision(), "0", \STR_PAD_LEFT);
	}
}
