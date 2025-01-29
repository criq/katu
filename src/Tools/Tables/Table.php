<?php

namespace Katu\Tools\Tables;

class Table extends \ArrayObject
{
	protected $filename;
	protected $headerRowIndex = 0;
	protected $title;

	public function __construct(?string $title = null)
	{
		$this->setTitle($title);
	}

	public function setTitle(string $title): Table
	{
		$this->title = $title;

		return $this;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function setFilename(string $filename): Table
	{
		$this->filename = $filename;

		return $this;
	}

	public function getFilename(): ?string
	{
		return $this->filename;
	}

	public function getHeaderRowIndex(): ?int
	{
		return $this->headerRowIndex;
	}

	/**
	 * @deprecated
	 */
	public function getWithHeader(): Table
	{
		$array = array_values($this->getArrayCopy());
		$header = $array[0];
		$data = array_map(function (array $values) use ($header) {
			return array_filter(array_combine($header, $values));
		}, array_slice($array, 1));

		$table = clone $this;
		$table->exchangeArray($data);

		return $table;
	}

	public function getCells(): CellCollection
	{
		$cells = new CellCollection(array_merge(...array_map(function (array $row, int $rowIndex) {
			return array_map(function (?string $value, int $columnIndex) use ($rowIndex) {
				return new Cell($columnIndex, $rowIndex, $value);
			}, $row, array_keys($row));
		}, $this->getArrayCopy(), array_keys($this->getArrayCopy()))));

		$headerCells = $cells->filterByRowIndex($this->getHeaderRowIndex());

		return new CellCollection(array_map(function (Cell $cell) use ($headerCells) {
			return $cell->setHeaderCell($headerCells->filterByColumnIndex($cell->getColumnIndex())->getFirst());
		}, $cells->getArrayCopy()));
	}

	public function getRows(): RowCollection
	{
		$rows = new RowCollection;

		array_map(function (Cell $cell) use (&$rows) {
			$rows->getOrCreateRowByIndex($cell->getRowIndex())->addCell($cell);
		}, $this->getCells()->getArrayCopy());

		return $rows;
	}

	public function getValueRows(): RowCollection
	{
		$rows = $this->getRows();
		if (!is_null($this->getHeaderRowIndex())) {
			$rows = $rows->excludeRowIndex($this->getHeaderRowIndex());
		}

		return $rows;
	}
}
