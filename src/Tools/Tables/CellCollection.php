<?php

namespace Katu\Tools\Tables;

class CellCollection extends \ArrayObject
{
	public function filterByColumnIndex(string $columnIndex): CellCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Cell $tableCell) use ($columnIndex) {
			return $tableCell->getColumnIndex() == $columnIndex;
		})));
	}

	public function filterByRowIndex(string $rowIndex): CellCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Cell $tableCell) use ($rowIndex) {
			return $tableCell->getRowIndex() == $rowIndex;
		})));
	}

	public function filterByHeader($header): CellCollection
	{
		if (is_string($header)) {
			$filter = [
				$header
			];
		} elseif (is_array($header)) {
			$filter = $header;
		} else {
			throw new \Katu\Exceptions\InputErrorException("Invalid filter.");
		}

		return new static(array_values(array_filter($this->getArrayCopy(), function (Cell $cell) use ($filter) {
			return in_array($cell->getHeader(), $filter);
		})));
	}

	public function addCell(Cell $cell): CellCollection
	{
		$this->append($cell);

		return $this;
	}

	public function getFirst(): ?Cell
	{
		return array_values($this->getArrayCopy())[0] ?? null;
	}

	public function getByHeader($header): ?Cell
	{
		return $this->filterByHeader($header)->getFirst();
	}

	public function getValue(): ?string
	{
		return $this->getFirst()->getValue();
	}
}
