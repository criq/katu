<?php

namespace Katu\Tools\Tables;

class Table extends \ArrayObject
{
	private $cells;
	private $rows;
	private $valueRows;
	protected $filename;
	protected $headerRowIndex = 1;
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

	public function setHeaderRowIndex(?string $headerRowIndex): Table
	{
		$this->headerRowIndex = $headerRowIndex;

		return $this;
	}

	public function getHeaderRowIndex(): ?string
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
		if (is_null($this->cells)) {
			$cells = new CellCollection(array_merge(...array_map(function (array $row, string $rowIndex) {
				return array_map(function (?string $value, string $columnIndex) use ($rowIndex) {
					return new Cell($columnIndex, $rowIndex, $value);
				}, $row, array_keys($row));
			}, $this->getArrayCopy(), array_keys($this->getArrayCopy()))));

			$headerCells = $cells->filterByRowIndex($this->getHeaderRowIndex());

			$cells = new CellCollection(array_map(function (Cell $cell) use ($headerCells) {
				return $cell->setHeaderCell($headerCells->filterByColumnIndex($cell->getColumnIndex())->getFirst());
			}, $cells->getArrayCopy()));

			$this->cells = $cells;
		}

		return $this->cells;
	}

	public function getRows(): RowCollection
	{
		if (is_null($this->rows)) {
			$this->rows = new RowCollection;

			array_map(function (Cell $cell) {
				$this->rows->getOrCreateRowByIndex($cell->getRowIndex())->addCell($cell);
			}, $this->getCells()->getArrayCopy());
		}

		return $this->rows;
	}

	public function getValueRows(): RowCollection
	{
		if (is_null($this->valueRows)) {
			$rows = $this->getRows();
			if (!is_null($this->getHeaderRowIndex())) {
				$rows = $rows->excludeRowIndex($this->getHeaderRowIndex());
			}

			$this->valueRows = $rows;
		}

		return $this->valueRows;
	}
}
