<?php

namespace Katu\Tools\Tables;

class Cell
{
	protected $columnIndex;
	protected $headerCell;
	protected $rowIndex;
	protected $value;

	public function __construct(int $columnIndex, int $rowIndex, ?string $value)
	{
		$this->setColumnIndex($columnIndex);
		$this->setRowIndex($rowIndex);
		$this->setValue($value);
	}

	public function setColumnIndex(int $columnIndex): Cell
	{
		$this->columnIndex = $columnIndex;

		return $this;
	}

	public function getColumnIndex(): int
	{
		return $this->columnIndex;
	}

	public function setRowIndex(int $rowIndex): Cell
	{
		$this->rowIndex = $rowIndex;

		return $this;
	}

	public function getRowIndex(): int
	{
		return $this->rowIndex;
	}

	public function setValue(?string $value): Cell
	{
		$this->value = $value;

		return $this;
	}

	public function getValue(): ?string
	{
		return $this->value;
	}

	public function setHeaderCell(?Cell $headerCell): Cell
	{
		$this->headerCell = $headerCell;

		return $this;
	}

	public function getHeaderCell(): ?Cell
	{
		return $this->headerCell;
	}

	public function getHeader(): ?string
	{
		return $this->getHeaderCell() ? $this->getHeaderCell()->getValue() : null;
	}
}
