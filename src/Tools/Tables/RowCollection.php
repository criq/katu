<?php

namespace Katu\Tools\Tables;

class RowCollection extends \ArrayObject
{
	public function offsetSet(mixed $key, mixed $value): void
	{
		parent::offsetSet($value->getIndex(), $value);
	}

	public function getByRowIndex(int $index): ?Row
	{
		return $this[$index] ?? null;
	}

	public function excludeRowIndex(int $rowIndex): RowCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Row $row) use ($rowIndex) {
			return $row->getIndex() != $rowIndex;
		})));
	}

	public function getOrCreateRowByIndex(int $index): Row
	{
		$row = $this->getByRowIndex($index);
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
