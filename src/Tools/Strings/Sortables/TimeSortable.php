<?php

namespace Katu\Tools\Strings\Sortables;

use Katu\Tools\Calendar\Time;
use Katu\Tools\Strings\Sortable;

class TimeSortable extends Sortable
{
	public function __construct(Time $source, ?string $title = null)
	{
		$this->setSource($source);
		$this->setTitle($title);
	}

	public function setSource(Time $source)
	{
		$this->source = $source;

		return $this;
	}

	public function getSource(): Time
	{
		return $this->source;
	}

	public function getSortable(): string
	{
		return $this->getSource()->format("YmdHisu");
	}
}
