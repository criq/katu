<?php

namespace Katu\Tools\Tables;

class Row
{
	protected $cells;
	protected $index;

	public function __construct(string $index)
	{
		$this->setIndex($index);
	}

	public function setIndex(string $index): Row
	{
		$this->index = $index;

		return $this;
	}

	public function getIndex(): string
	{
		return $this->index;
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

	public function getArray(): array
	{
		$array = [];
		foreach ($this->getCells() as $cell) {
			$array[$cell->getHeader()] = $cell->getValue();
		}

		return $array;
	}
}
