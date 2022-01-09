<?php

namespace Katu\Tools\Strings;

class Enclosure
{
	protected $start;
	protected $end;

	public function __construct(string $start, string $end)
	{
		$this->start = $start;
		$this->end = $end;
	}
}
