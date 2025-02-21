<?php

namespace Katu\Tools\Strings\Sortables;

use Katu\Tools\Strings\Sortable;

class StringSortable extends Sortable
{
	public function __construct(string $source, ?string $title = null)
	{
		$this->setSource($source);
		$this->setTitle($title);
	}

	public function setSource(string $source)
	{
		$this->source = $source;

		return $this;
	}

	public function getSource(): string
	{
		return $this->source;
	}

	public function getSortable(): string
	{
		return $this->getSource();
	}
}
