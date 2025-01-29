<?php

namespace Katu\Tools\Tables;

class Row
{
	protected $cells;
	protected $index;

	public function __construct(int $index)
	{
		$this->setIndex($index);
	}

	public function setIndex(int $index): Row
	{
		$this->index = $index;

		return $this;
	}

	public function getIndex(): int
	{
		return $this->index;
	}

	public function getNumber(): int
	{
		return $this->getIndex() + 1;
	}

	public function getCells(): CellCollection
	{
		if (is_null($this->cells)) {
			$this->cells = new CellCollection;
		}

		return $this->cells;
	}

	public function addCell(Cell $cell): Row
	{
		$this->getCells()->addCell($cell);

		return $this;
	}
}
