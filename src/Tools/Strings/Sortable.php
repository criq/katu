<?php

namespace Katu\Tools\Strings;

abstract class Sortable
{
	protected $source;

	abstract public function getSortable(): string;
}
