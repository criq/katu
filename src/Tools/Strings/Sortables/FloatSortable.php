<?php

namespace Katu\Tools\Strings\Sortables;

use Katu\Tools\Strings\Sortable;

class FloatSortable extends Sortable
{
	protected $precision;

	public function __construct(float $source, ?string $title = null)
	{
		$this->setSource($source);
		$this->setTitle($title);
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

	public function setPrecision(int $precision): FloatSortable
	{
		$this->precision = $precision;

		return $this;
	}

	public function getPrecision(): int
	{
		return $this->precision ?: 20;
	}

	public function getSortable(): string
	{
		if (is_infinite($this->getSource())) {
			return implode(".", [
				str_repeat(9, $this->getPrecision()),
				str_repeat(9, $this->getPrecision()),
			]);
		}

		list($a, $b) = array_pad(explode(".", $this->getSource()), 2, null);

		return implode(".", [
			str_pad($a, $this->getPrecision(), 0, \STR_PAD_LEFT),
			str_pad($b, $this->getPrecision(), 0, \STR_PAD_RIGHT),
		]);
	}
}
