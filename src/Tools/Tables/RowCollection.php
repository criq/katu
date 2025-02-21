<?php

namespace Katu\Tools\Tables;

class RowCollection extends \ArrayObject
{
	public function filterByRowIndex(int $index): RowCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Row $row) use ($index) {
			return $row->getIndex() == $index;
		})));
	}

	public function excludeRowIndex(int $rowIndex): RowCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Row $row) use ($rowIndex) {
			return $row->getIndex() != $rowIndex;
		})));
	}

	public function getOrCreateRowByIndex(int $index): Row
	{
		$row = $this->filterByRowIndex($index)->getFirst();
		if (!$row) {
			$row = new Row($index);
			$this->addRow($row);
		}

		return $row;
	}

	public function addRow(Row $row): RowCollection
	{
		$this->append($row);

		return $this;
	}

	public function getFirst(): ?Row
	{
		return array_values($this->getArrayCopy())[0] ?? null;
	}
}
