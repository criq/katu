<?php

namespace Katu\Tools\Strings;

abstract class Sortable
{
	protected $source;
	protected $title;

	abstract public function getSortable(): string;

	public function setTitle(?string $title): Sortable
	{
		$this->title = $title;

		return $this;
	}
}
